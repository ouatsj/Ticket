'use strict';

const { query } = require('../db');
const { companyFilterClause } = require('./scope');
const { ymd } = require('./dashboard');

const TICKET_JOINS = `
  FROM passager p
  JOIN programme pr ON p.code_pro = pr.code_progr
  JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
  JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
  JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
  JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
  JOIN entreprise e ON c.id_entrep = e.id_entreprise
`;

const TICKET_WHERE = `
  WHERE e.ekey = :ekey
    AND p.prixvente IS NOT NULL
    AND COALESCE(p.statut_confirme, '') NOT IN ('confirm','catconfirm','confirmcarte')
    AND p.statut_code = 'vendu'
    AND IFNULL(p.statutvente, 0) <> 2
    AND p.datep_create >= :d1 AND p.datep_create < DATE_ADD(:d2, INTERVAL 1 DAY)
`;

function cieInSql(column, ids, all, prefix) {
  if (all) return { sql: '', params: {} };
  if (!ids.length) return { sql: ' AND 1=0 ', params: {} };
  const params = {};
  const ph = ids.map((c, i) => {
    params[`${prefix}${i}`] = c;
    return `:${prefix}${i}`;
  });
  return { sql: ` AND ${column} IN (${ph.join(',')}) `, params };
}

function resolveIds(scope, companyParam) {
  if (companyParam !== undefined && companyParam !== null && companyParam !== '') {
    return { ids: [Number(companyParam)], all: false };
  }
  if (scope.all) return { ids: [], all: true };
  return { ids: scope.companies.slice(), all: false };
}

async function financeSales(scope, ekey, companyParam, fromYmd, toYmd) {
  const cie = companyFilterClause(scope, companyParam, 'dest');
  const { ids, all } = resolveIds(scope, companyParam);

  const [tickets, byCie, byLigne, bag, cour, recettes] = await Promise.all([
    query(
      `SELECT COALESCE(SUM(p.prixvente),0) AS ca, COUNT(p.code_passager) AS billets
       ${TICKET_JOINS} ${TICKET_WHERE} ${cie.sql}`,
      { ekey, d1: fromYmd, d2: toYmd, ...cie.params }
    ),
    query(
      `SELECT dest.id_compaga AS cle_compagnie, c.nom_compagnie,
              COALESCE(SUM(p.prixvente),0) AS ca, COUNT(p.code_passager) AS billets
       ${TICKET_JOINS} ${TICKET_WHERE} ${cie.sql}
       GROUP BY dest.id_compaga, c.nom_compagnie
       ORDER BY ca DESC`,
      { ekey, d1: fromYmd, d2: toYmd, ...cie.params }
    ),
    query(
      `SELECT COALESCE(NULLIF(TRIM(p.ident_ligne_vendu),''), lg.ident_ligne) AS ident_ligne,
              COALESCE(NULLIF(TRIM(p.lignetineraire_vendu),''), lg.nom_ligne) AS nom_ligne,
              COALESCE(SUM(p.prixvente),0) AS ca, COUNT(p.code_passager) AS billets
       ${TICKET_JOINS} ${TICKET_WHERE} ${cie.sql}
       GROUP BY ident_ligne, nom_ligne
       ORDER BY ca DESC
       LIMIT 30`,
      { ekey, d1: fromYmd, d2: toYmd, ...cie.params }
    ),
    (async () => {
      const c = cieInSql('dest.id_compaga', ids, all, 'bag');
      try {
        const rows = await query(
          `SELECT COALESCE(SUM(bg.prix_bagage),0) AS ca, COUNT(bg.id_bagage) AS n
           FROM bagages bg
           JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
           JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
           JOIN compagnies cie ON dest.id_compaga = cie.cle_compagnie
           JOIN entreprise e ON cie.id_entrep = e.id_entreprise
           WHERE e.ekey = :ekey AND IFNULL(bg.annulebag,0)=0
             AND bg.date_create >= :d1 AND bg.date_create < DATE_ADD(:d2, INTERVAL 1 DAY)
             ${c.sql}`,
          { ekey, d1: fromYmd, d2: toYmd, ...c.params }
        );
        return rows[0];
      } catch (_) {
        return { ca: null, n: null };
      }
    })(),
    (async () => {
      const c = cieInSql('dest.id_compaga', ids, all, 'cour');
      try {
        const rows = await query(
          `SELECT COALESCE(SUM(cx.prixcolis),0) AS ca, COUNT(cx.courrierexpid) AS n
           FROM courriers_exp cx
           JOIN code_courriers cd ON cx.id_codecourrier = cd.codecolisid
           JOIN lignes lg ON cd.idlignes = lg.ident_ligne
           JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
           JOIN compagnies cie ON dest.id_compaga = cie.cle_compagnie
           JOIN entreprise e ON cie.id_entrep = e.id_entreprise
           WHERE e.ekey = :ekey AND cx.prixcolis IS NOT NULL AND IFNULL(cx.actif_cour,0)=0
             AND cx.dateenvoi >= :d1 AND cx.dateenvoi < DATE_ADD(:d2, INTERVAL 1 DAY)
             ${c.sql}`,
          { ekey, d1: fromYmd, d2: toYmd, ...c.params }
        );
        return rows[0];
      } catch (_) {
        return { ca: null, n: null };
      }
    })(),
    (async () => {
      const c = cieInSql('r.compkey_recet', ids, all, 'rec');
      const rows = await query(
        `SELECT COALESCE(SUM(r.montant_recet),0) AS ca
         FROM recette r
         JOIN compagnies cie ON r.compkey_recet = cie.cle_compagnie
         JOIN entreprise e ON cie.id_entrep = e.id_entreprise
         WHERE e.ekey = :ekey
           AND IFNULL(r.actif_rect,1)=1 AND IFNULL(r.active_recet,1)=1
           AND r.date_recet >= :d1 AND r.date_recet < DATE_ADD(:d2, INTERVAL 1 DAY)
           ${c.sql}`,
        { ekey, d1: fromYmd, d2: toYmd, ...c.params }
      );
      return rows[0];
    })(),
  ]);

  const caTickets = Number(tickets[0].ca) || 0;
  const caBag = bag.ca === null ? null : Number(bag.ca) || 0;
  const caCour = cour.ca === null ? null : Number(cour.ca) || 0;
  const caCaisse = Number(recettes.ca) || 0;

  return {
    as_of: new Date().toISOString(),
    source: 'ventes',
    from: fromYmd,
    to: toYmd,
    ca_billets: caTickets,
    billets: Number(tickets[0].billets) || 0,
    ca_bagages: caBag,
    nb_bagages: bag.n === null ? null : Number(bag.n) || 0,
    ca_courrier: caCour,
    nb_colis: cour.n === null ? null : Number(cour.n) || 0,
    recettes_caisse: caCaisse,
    ecart_caisse: Number((caTickets - caCaisse).toFixed(2)),
    by_company: byCie.map((r) => ({
      cle_compagnie: Number(r.cle_compagnie),
      nom_compagnie: r.nom_compagnie,
      ca: Number(r.ca) || 0,
      billets: Number(r.billets) || 0,
    })),
    by_ligne: byLigne.map((r) => ({
      ident_ligne: r.ident_ligne,
      nom_ligne: r.nom_ligne,
      ca: Number(r.ca) || 0,
      billets: Number(r.billets) || 0,
    })),
    notes: [
      'CA billets = passager.prixvente (id_compaga).',
      'Écart caisse = CA billets − recettes ledger (arrêt non synchrone possible).',
      'Remboursements : non disponibles dans Ticket.',
    ],
  };
}

module.exports = { financeSales, ymd };
