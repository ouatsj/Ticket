'use strict';

const { query } = require('../db');
const { companyFilterClause } = require('./scope');

/**
 * Jointure CA ticket alignée Rapport::reporticket :
 * passager → programme → ligne_heure → lignes → gare_dest.id_compaga
 * Exclusions : statutvente=2, confirmations gratuites, prix NULL.
 */
const TICKET_JOINS = `
  FROM passager p
  JOIN programme pr ON p.code_pro = pr.code_progr
  JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
  JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
  JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
  JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
  JOIN entreprise e ON c.id_entrep = e.id_entreprise
`;

const TICKET_WHERE_BASE = `
  WHERE e.ekey = :ekey
    AND p.prixvente IS NOT NULL
    AND COALESCE(p.statut_confirme, '') NOT IN ('confirm','catconfirm','confirmcarte')
    AND p.statut_code = 'vendu'
    AND IFNULL(p.statutvente, 0) <> 2
`;

function ymd(d) {
  if (d === null || d === undefined || d === '') {
    return ymd(new Date());
  }
  if (typeof d === 'string' && /^\d{4}-\d{2}-\d{2}/.test(d)) {
    return d.slice(0, 10);
  }
  const x = d instanceof Date ? d : new Date(d);
  if (Number.isNaN(x.getTime())) {
    throw new Error('date invalide: ' + d);
  }
  const y = x.getFullYear();
  const m = String(x.getMonth() + 1).padStart(2, '0');
  const day = String(x.getDate()).padStart(2, '0');
  return `${y}-${m}-${day}`;
}

function addDays(ymdStr, n) {
  const [y, m, d] = ymdStr.split('-').map(Number);
  const dt = new Date(Date.UTC(y, m - 1, d));
  dt.setUTCDate(dt.getUTCDate() + n);
  return ymd(dt);
}

function monthStart(ymdStr) {
  return ymdStr.slice(0, 8) + '01';
}

function resolveCompanyIds(scope, companyParam) {
  if (companyParam !== undefined && companyParam !== null && companyParam !== '') {
    return { ids: [Number(companyParam)], all: false };
  }
  if (scope.all) return { ids: [], all: true };
  return { ids: scope.companies.slice(), all: false };
}

async function sumTickets(ekey, fromYmd, toYmdInclusive, cieClause) {
  const sql = `
    SELECT
      COALESCE(SUM(p.prixvente), 0) AS ca,
      COUNT(p.code_passager) AS billets
    ${TICKET_JOINS}
    ${TICKET_WHERE_BASE}
    AND p.datep_create >= :d1 AND p.datep_create < DATE_ADD(:d2, INTERVAL 1 DAY)
    ${cieClause.sql}
  `;
  const rows = await query(sql, {
    ekey,
    d1: fromYmd,
    d2: toYmdInclusive,
    ...cieClause.params,
  });
  return {
    ca: Number(rows[0].ca) || 0,
    billets: Number(rows[0].billets) || 0,
  };
}

async function caByCompany(ekey, fromYmd, toYmdInclusive, cieClause) {
  const sql = `
    SELECT
      dest.id_compaga AS cle_compagnie,
      c.nom_compagnie,
      COALESCE(SUM(p.prixvente), 0) AS ca_jour
    ${TICKET_JOINS}
    ${TICKET_WHERE_BASE}
    AND p.datep_create >= :d1 AND p.datep_create < DATE_ADD(:d2, INTERVAL 1 DAY)
    ${cieClause.sql}
    GROUP BY dest.id_compaga, c.nom_compagnie
    ORDER BY ca_jour DESC
  `;
  return query(sql, {
    ekey,
    d1: fromYmd,
    d2: toYmdInclusive,
    ...cieClause.params,
  });
}

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

async function sumBagages(ekey, fromYmd, toYmdInclusive, ids, all) {
  const cie = cieInSql('dest.id_compaga', ids, all, 'bag');
  try {
    const rows = await query(
      `SELECT COALESCE(SUM(bg.prix_bagage), 0) AS ca
       FROM bagages bg
       JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
       JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
       JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
       JOIN entreprise e ON c.id_entrep = e.id_entreprise
       WHERE e.ekey = :ekey
         AND IFNULL(bg.annulebag, 0) = 0
         AND bg.date_create >= :d1 AND bg.date_create < DATE_ADD(:d2, INTERVAL 1 DAY)
         ${cie.sql}`,
      { ekey, d1: fromYmd, d2: toYmdInclusive, ...cie.params }
    );
    return Number(rows[0].ca) || 0;
  } catch (_) {
    return null;
  }
}

async function sumCourrier(ekey, fromYmd, toYmdInclusive, ids, all) {
  const cie = cieInSql('dest.id_compaga', ids, all, 'cour');
  try {
    const rows = await query(
      `SELECT COALESCE(SUM(cx.prixcolis), 0) AS ca
       FROM courriers_exp cx
       JOIN code_courriers cd ON cx.id_codecourrier = cd.codecolisid
       JOIN lignes lg ON cd.idlignes = lg.ident_ligne
       JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
       JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
       JOIN entreprise e ON c.id_entrep = e.id_entreprise
       WHERE e.ekey = :ekey
         AND cx.prixcolis IS NOT NULL
         AND IFNULL(cx.actif_cour, 0) = 0
         AND cx.dateenvoi >= :d1 AND cx.dateenvoi < DATE_ADD(:d2, INTERVAL 1 DAY)
         ${cie.sql}`,
      { ekey, d1: fromYmd, d2: toYmdInclusive, ...cie.params }
    );
    return Number(rows[0].ca) || 0;
  } catch (_) {
    return null;
  }
}

async function sumRecettesCaisse(ekey, fromYmd, toYmdInclusive, ids, all) {
  const cie = cieInSql('r.compkey_recet', ids, all, 'rec');
  const rows = await query(
    `SELECT COALESCE(SUM(r.montant_recet), 0) AS ca
     FROM recette r
     JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
     JOIN entreprise e ON c.id_entrep = e.id_entreprise
     WHERE e.ekey = :ekey
       AND IFNULL(r.actif_rect, 1) = 1
       AND IFNULL(r.active_recet, 1) = 1
       AND r.date_recet >= :d1 AND r.date_recet < DATE_ADD(:d2, INTERVAL 1 DAY)
       ${cie.sql}`,
    { ekey, d1: fromYmd, d2: toYmdInclusive, ...cie.params }
  );
  return Number(rows[0].ca) || 0;
}

async function buildSummary(scope, ekey, companyParam, dateYmd) {
  const cie = companyFilterClause(scope, companyParam, 'dest');
  const { ids, all } = resolveCompanyIds(scope, companyParam);
  const day = dateYmd || ymd(new Date());
  const veille = addDays(day, -1);
  const semainePrecStart = addDays(day, -14);
  const semainePrecEnd = addDays(day, -8);
  const moisDeb = monthStart(day);

  const [jour, mois, veilleAgg, semPrec, byCie, caBagagesJour, caCourrierJour, recettesCaisseJour] =
    await Promise.all([
      sumTickets(ekey, day, day, cie),
      sumTickets(ekey, moisDeb, day, cie),
      sumTickets(ekey, veille, veille, cie),
      sumTickets(ekey, semainePrecStart, semainePrecEnd, cie),
      caByCompany(ekey, day, day, cie),
      sumBagages(ekey, day, day, ids, all),
      sumCourrier(ekey, day, day, ids, all),
      sumRecettesCaisse(ekey, day, day, ids, all),
    ]);

  const notes = [
    'CA principal = ventes billets (passager.prixvente), partition id_compaga.',
    'Écart caisse = CA ventes jour − SUM(recette) jour (non synchrone si arrêt non fait).',
    'Clones report (statutvente=2) et confirmations gratuites exclus.',
  ];
  if (caBagagesJour === null) notes.push('CA bagages indisponible (schéma / jointure).');
  if (caCourrierJour === null) notes.push('CA courrier indisponible (schéma / jointure).');

  const totalActivite =
    jour.ca + (caBagagesJour || 0) + (caCourrierJour || 0);

  return {
    as_of: new Date().toISOString(),
    timezone: process.env.TZ || 'Africa/Ouagadougou',
    source: 'ventes',
    date: day,
    ca_jour: jour.ca,
    ca_mois: mois.ca,
    ca_veille: veilleAgg.ca,
    ca_semaine_prec: semPrec.ca,
    variation_veille_pct:
      veilleAgg.ca > 0 ? Number((((jour.ca - veilleAgg.ca) / veilleAgg.ca) * 100).toFixed(1)) : null,
    billets_jour: jour.billets,
    ca_bagages_jour: caBagagesJour,
    ca_courrier_jour: caCourrierJour,
    recettes_caisse_jour: recettesCaisseJour,
    ecart_caisse_jour: Number((jour.ca - recettesCaisseJour).toFixed(2)),
    total_activite_jour: Number(totalActivite.toFixed(2)),
    details: {
      ca_jour: {
        titre: 'Chiffre d’affaires billets du jour',
        definition: 'Somme de passager.prixvente (vendus, hors clones/remboursements) pour la date choisie.',
        composition: [
          { label: 'Billets vendus', valeur: jour.billets, unite: 'billets' },
          { label: 'CA billets', valeur: jour.ca, unite: 'F' },
          { label: 'CA veille', valeur: veilleAgg.ca, unite: 'F' },
        ],
      },
      ca_mois: {
        titre: 'CA billets du mois',
        definition: `Ventes du ${moisDeb} au ${day}.`,
        composition: [
          { label: 'CA mois', valeur: mois.ca, unite: 'F' },
          { label: 'CA jour', valeur: jour.ca, unite: 'F' },
        ],
      },
      billets_jour: {
        titre: 'Billets vendus du jour',
        definition: 'Nombre de passagers statut vendu (hors statutvente=2 et confirmations gratuites).',
        composition: [{ label: 'Billets', valeur: jour.billets, unite: 'billets' }],
      },
      ca_bagages_jour: {
        titre: 'CA bagages du jour',
        definition: 'Somme bagages.prix_bagage (non annulés) du jour.',
        composition: [{ label: 'CA bagages', valeur: caBagagesJour, unite: 'F' }],
      },
      ca_courrier_jour: {
        titre: 'CA courrier du jour',
        definition: 'Somme courriers_exp.prixcolis du jour.',
        composition: [{ label: 'CA courrier', valeur: caCourrierJour, unite: 'F' }],
      },
      ecart_caisse_jour: {
        titre: 'Écart caisse du jour',
        definition: 'CA billets − recettes caisse (ledger). Un écart ≠ 0 peut indiquer un arrêt non synchronisé.',
        composition: [
          { label: 'CA billets', valeur: jour.ca, unite: 'F' },
          { label: 'Recettes caisse', valeur: recettesCaisseJour, unite: 'F' },
          { label: 'Écart', valeur: Number((jour.ca - recettesCaisseJour).toFixed(2)), unite: 'F' },
        ],
      },
    },
    companies: byCie.map((r) => ({
      cle_compagnie: Number(r.cle_compagnie),
      nom_compagnie: r.nom_compagnie,
      ca_jour: Number(r.ca_jour) || 0,
    })),
    notes,
  };
}

async function listCompanies(scope) {
  if (scope.all) {
    return query(
      `SELECT c.cle_compagnie, c.nom_compagnie, e.ekey
       FROM compagnies c
       JOIN entreprise e ON c.id_entrep = e.id_entreprise
       WHERE e.ekey = :ekey
       ORDER BY c.nom_compagnie`,
      { ekey: scope.ekey }
    );
  }
  if (!scope.companies.length) return [];
  const params = { ekey: scope.ekey };
  const ph = scope.companies.map((c, i) => {
    params[`c${i}`] = c;
    return `:c${i}`;
  });
  return query(
    `SELECT c.cle_compagnie, c.nom_compagnie, e.ekey
     FROM compagnies c
     JOIN entreprise e ON c.id_entrep = e.id_entreprise
     WHERE e.ekey = :ekey AND c.cle_compagnie IN (${ph.join(',')})
     ORDER BY c.nom_compagnie`,
    params
  );
}

module.exports = { buildSummary, listCompanies, ymd };
