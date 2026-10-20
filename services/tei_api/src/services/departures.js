'use strict';

const { query } = require('../db');
const { companyFilterClause } = require('./scope');

/**
 * Départs du jour + remplissage, enrichis pour affichage
 * Compagnie → Agence → Gare → Escale (destination).
 */
async function listDepartures(scope, ekey, companyParam, dateYmd) {
  const cie = companyFilterClause(scope, companyParam, 'dest');
  const rows = await query(
    `SELECT
       pr.code_progr,
       DATE_FORMAT(pr.date_progr, '%Y-%m-%d') AS date_progr,
       pr.categori,
       pr.gareidentif,
       pr.statut_prog,
       pr.actif_prog,
       TIME_FORMAT(h.heure, '%H:%i') AS heure,
       lg.ident_ligne,
       lg.nom_ligne,
       dest.id_compaga AS cle_compagnie,
       c.nom_compagnie,
       ge.code_gaexp AS code_gare,
       ge.nom_gaep AS gare_depart,
       dest.code_gadest AS code_destination,
       dest.nom_gadest AS gare_arrivee,
       cat.nbr_place AS capacite,
       (
         SELECT GROUP_CONCAT(sg.nomsousgare ORDER BY sg.nomsousgare SEPARATOR ', ')
         FROM programme_sousgare ps
         JOIN sousgare sg ON sg.idsousgare = ps.idsousgare
         WHERE ps.code_progr = pr.code_progr
       ) AS agences,
       (
         SELECT GROUP_CONCAT(ie.nom_escale ORDER BY ie.ordre_escale SEPARATOR ' → ')
         FROM itineraire_escales ie
         WHERE ie.id_lignes = lg.ident_ligne
           AND IFNULL(ie.actif_escale, 1) = 1
       ) AS escales_itineraire
     FROM programme pr
     JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
     JOIN heures h ON lh.heure_identif = h.id_heure
     JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
     JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
     JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
     JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
     JOIN entreprise e ON c.id_entrep = e.id_entreprise
     LEFT JOIN categorie cat ON pr.categori = cat.categorie
     WHERE e.ekey = :ekey
       AND pr.date_progr = :d
       AND IFNULL(pr.actif_prog, 0) = 0
       ${cie.sql}
     ORDER BY c.nom_compagnie, ge.nom_gaep, dest.nom_gadest, h.heure, lg.nom_ligne`,
    { ekey, d: dateYmd, ...cie.params }
  );

  const codes = rows.map((r) => r.code_progr).filter(Boolean);
  const occMap = {};
  const venduMap = {};
  if (codes.length) {
    const params = {};
    const ph = codes.map((code, i) => {
      params[`c${i}`] = code;
      return `:c${i}`;
    });
    const occRows = await query(
      `SELECT p.code_pro AS code,
              COUNT(DISTINCT p.num_siege_categorie) AS n
       FROM passager p
       WHERE p.code_pro IN (${ph.join(',')})
         AND p.num_siege_categorie IS NOT NULL
         AND p.num_siege_categorie > 0
         AND IFNULL(p.actif_pas, 0) = 0
       GROUP BY p.code_pro`,
      params
    );
    for (const o of occRows) occMap[o.code] = Number(o.n) || 0;

    const venduRows = await query(
      `SELECT p.code_pro AS code, COUNT(p.code_passager) AS n
       FROM passager p
       WHERE p.code_pro IN (${ph.join(',')})
         AND p.statut_code = 'vendu'
         AND IFNULL(p.statutvente, 0) <> 2
         AND IFNULL(p.actif_pas, 0) = 0
       GROUP BY p.code_pro`,
      params
    );
    for (const o of venduRows) venduMap[o.code] = Number(o.n) || 0;
  }

  const out = rows.map((r) => {
    const capacite = Number(r.capacite) || 0;
    const placesOccupees = occMap[r.code_progr] || 0;
    const billetsVendus = venduMap[r.code_progr] || 0;
    // Remplissage métier = max(sièges distincts, billets vendus) pour coller à la réalité guichet
    const placesReference = Math.max(placesOccupees, billetsVendus);
    const disponibles = capacite > 0 ? Math.max(capacite - placesReference, 0) : null;
    const taux =
      capacite > 0 ? Number(((placesReference / capacite) * 100).toFixed(1)) : null;

    // Alertes uniquement si capacité connue et activité réelle (évite faux positifs)
    let alerte = null;
    if (taux !== null && capacite > 0 && placesReference > 0) {
      if (taux >= 100 || disponibles === 0) alerte = 'COMPLET';
      else if (taux >= 90) alerte = 'prioritaire';
      else if (taux >= 80) alerte = 'vigilance';
    }

    const agence =
      (r.agences && String(r.agences).trim()) ||
      `${r.gare_depart} (gare principale)`;

    return {
      code_progr: r.code_progr,
      date: r.date_progr,
      heure: r.heure,
      compagnie: r.nom_compagnie,
      cle_compagnie: Number(r.cle_compagnie),
      agence,
      gare: r.gareidentif,
      code_gare: r.code_gare,
      gare_depart: r.gare_depart,
      destination: r.gare_arrivee,
      code_destination: r.code_destination,
      escale: r.gare_arrivee,
      escales: r.escales_itineraire || '',
      ligne: r.nom_ligne,
      ident_ligne: r.ident_ligne,
      categorie: r.categori,
      capacite,
      places_occupees: placesReference,
      sieges_distincts: placesOccupees,
      billets_vendus: billetsVendus,
      places_disponibles: disponibles,
      taux_remplissage: taux,
      statut: r.statut_prog,
      alerte,
    };
  });

  // Arbre prêt à l'affichage mobile
  const tree = buildDepartureTree(out);
  const cartes = buildCompanyCards(out);

  return {
    date: dateYmd,
    as_of: new Date().toISOString(),
    count: out.length,
    departures: out,
    tree,
    cartes,
  };
}

/**
 * Affichage décideur : compagnie → agence (la gare) → départs du jour.
 */
function buildCompanyCards(items) {
  const byCie = new Map();
  for (const d of items) {
    const cie = d.compagnie || '—';
    if (!byCie.has(cie)) {
      byCie.set(cie, { cle: d.cle_compagnie, agences: new Map() });
    }
    const agence = d.gare_depart || d.code_gare || '—';
    const bucket = byCie.get(cie);
    if (!bucket.agences.has(agence)) bucket.agences.set(agence, []);
    bucket.agences.get(agence).push(d);
  }

  return [...byCie.entries()].map(([compagnie, bucket]) => {
    const agences = [...bucket.agences.entries()].map(([nom, departures]) => {
      const vendus = departures.reduce((s, x) => s + (Number(x.billets_vendus) || 0), 0);
      const complets = departures.filter((x) => x.alerte === 'COMPLET').length;
      return {
        agence: nom,
        code_gare: departures[0]?.code_gare || '',
        count: departures.length,
        billets: vendus,
        complets,
        departures,
      };
    });
    agences.sort((a, b) => b.count - a.count || a.agence.localeCompare(b.agence, 'fr'));
    const count = agences.reduce((s, a) => s + a.count, 0);
    return {
      compagnie,
      cle_compagnie: bucket.cle,
      count,
      agences: agences.length,
      lieux: agences,
    };
  }).sort((a, b) => b.count - a.count || a.compagnie.localeCompare(b.compagnie, 'fr'));
}

function buildDepartureTree(items) {
  const byCie = new Map();
  for (const d of items) {
    const cie = d.compagnie || '—';
    if (!byCie.has(cie)) byCie.set(cie, new Map());
    const byAgence = byCie.get(cie);
    const agence = d.agence || '—';
    if (!byAgence.has(agence)) byAgence.set(agence, new Map());
    const byGare = byAgence.get(agence);
    const gare = d.gare_depart || d.gare || '—';
    if (!byGare.has(gare)) byGare.set(gare, new Map());
    const byEscale = byGare.get(gare);
    const escale = d.escale || d.destination || '—';
    if (!byEscale.has(escale)) byEscale.set(escale, []);
    byEscale.get(escale).push(d);
  }

  return [...byCie.entries()].map(([compagnie, agences]) => ({
    compagnie,
    count: countNested(agences),
    agences: [...agences.entries()].map(([agence, gares]) => ({
      agence,
      count: countNested(gares),
      gares: [...gares.entries()].map(([gare, escales]) => ({
        gare,
        count: countNested(escales),
        escales: [...escales.entries()].map(([escale, departures]) => ({
          escale,
          count: departures.length,
          escales_itineraire: departures[0]?.escales || '',
          departures,
        })),
      })),
    })),
  }));
}

function countNested(mapOfMaps) {
  let n = 0;
  for (const v of mapOfMaps.values()) {
    if (v instanceof Map) n += countNested(v);
    else if (Array.isArray(v)) n += v.length;
  }
  return n;
}

module.exports = { listDepartures };
