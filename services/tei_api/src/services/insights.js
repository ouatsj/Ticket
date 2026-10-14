'use strict';

const { listDepartures } = require('./departures');
const { financeSales } = require('./finance');
const { buildSummary } = require('./dashboard');
const { loadThresholds } = require('./alerts');

/**
 * Conseiller intelligent — règles déterministes uniquement.
 * Aucun chiffre inventé : chaque recommandation cite des agrégats calculés.
 */
async function buildInsights(scope, ekey, companyParam, dateYmd) {
  const th = await loadThresholds(ekey);
  const day = dateYmd;
  // période précédente = 7 jours avant (même longueur 1 jour pour variation)
  const [y, m, d] = day.split('-').map(Number);
  const dt = new Date(Date.UTC(y, m - 1, d));
  const prev = new Date(dt);
  prev.setUTCDate(prev.getUTCDate() - 7);
  const prevYmd = prev.toISOString().slice(0, 10);
  const monthStart = `${day.slice(0, 8)}01`;

  const [deps, finance, financePrev, summary] = await Promise.all([
    listDepartures(scope, ekey, companyParam, day),
    financeSales(scope, ekey, companyParam, monthStart, day),
    financeSales(scope, ekey, companyParam, prevYmd, prevYmd),
    buildSummary(scope, ekey, companyParam, day),
  ]);

  const recommendations = [];
  const periode = { from: monthStart, to: day, reference_day: day };

  // R1 — départs complets / quasi complets
  for (const dep of deps.departures) {
    if (dep.taux_remplissage === null) continue;
    if (dep.taux_remplissage >= 100 || dep.places_disponibles === 0) {
      recommendations.push({
        id: `full-${dep.code_progr}`,
        priorite: 'haute',
        categorie: 'depart',
        probleme: `Le départ de ${dep.heure} (${dep.ligne}) est complet.`,
        chiffres: {
          heure: dep.heure,
          ligne: dep.ligne,
          compagnie: dep.compagnie,
          places_occupees: dep.places_occupees,
          capacite: dep.capacite,
          taux_remplissage: dep.taux_remplissage,
        },
        periode: { date: day },
        action: 'Vérifiez la disponibilité d’un véhicule ou départ supplémentaire.',
        resultat_attendu: null,
        lien: {
          ecran: 'departures',
          params: { date: day, code_progr: dep.code_progr },
        },
      });
    } else if (dep.taux_remplissage >= th.fill_priority_pct) {
      recommendations.push({
        id: `prio-${dep.code_progr}`,
        priorite: 'haute',
        categorie: 'depart',
        probleme: `Le départ de ${dep.heure} atteint ${dep.taux_remplissage} % de remplissage.`,
        chiffres: {
          heure: dep.heure,
          ligne: dep.ligne,
          compagnie: dep.compagnie,
          places_occupees: dep.places_occupees,
          capacite: dep.capacite,
          taux_remplissage: dep.taux_remplissage,
          seuil: th.fill_priority_pct,
        },
        periode: { date: day },
        action: 'Surveillez les ventes restantes et préparez une capacité de secours.',
        resultat_attendu: null,
        lien: {
          ecran: 'departures',
          params: { date: day, code_progr: dep.code_progr },
        },
      });
    }
  }

  // R2 — faible remplissage (sous objectif)
  const faibles = deps.departures.filter(
    (dep) =>
      dep.taux_remplissage !== null &&
      dep.capacite > 0 &&
      dep.taux_remplissage < th.low_fill_pct
  );
  if (faibles.length >= 3) {
    const avg =
      faibles.reduce((s, x) => s + x.taux_remplissage, 0) / faibles.length;
    recommendations.push({
      id: `low-fill-${day}`,
      priorite: 'moyenne',
      categorie: 'depart',
      probleme: `${faibles.length} départs du jour sont sous l’objectif de remplissage (${th.low_fill_pct} %).`,
      chiffres: {
        nb_departs_faibles: faibles.length,
        taux_moyen: Number(avg.toFixed(1)),
        seuil: th.low_fill_pct,
        exemples: faibles.slice(0, 3).map((x) => ({
          heure: x.heure,
          ligne: x.ligne,
          taux: x.taux_remplissage,
        })),
      },
      periode: { date: day },
      action: 'Analysez les horaires et lignes concernés ; ajustez l’offre si le pattern se répète.',
      resultat_attendu: null,
      lien: { ecran: 'departures', params: { date: day } },
    });
  }

  // R3 — baisse CA vs même jour semaine précédente
  if (financePrev.ca_billets > 0) {
    const delta =
      ((summary.ca_jour - financePrev.ca_billets) / financePrev.ca_billets) * 100;
    if (delta <= -15) {
      recommendations.push({
        id: `ca-drop-${day}`,
        priorite: 'haute',
        categorie: 'finance',
        probleme: `Les recettes billets du jour baissent de ${Math.abs(delta).toFixed(1)} % par rapport au même jour de la semaine précédente.`,
        chiffres: {
          ca_jour: summary.ca_jour,
          ca_jour_semaine_prec: financePrev.ca_billets,
          variation_pct: Number(delta.toFixed(1)),
        },
        periode: { date: day, compare_to: prevYmd },
        action: 'Consultez le détail des ventes par ligne et compagnie.',
        resultat_attendu: null,
        lien: {
          ecran: 'finance',
          params: { from: day, to: day },
        },
      });
    }
  }

  // R4 — écart caisse significatif
  const ecart = Number(finance.ecart_caisse) || 0;
  const ca = Number(finance.ca_billets) || 0;
  if (ca > 0 && Math.abs(ecart) / ca >= 0.2) {
    recommendations.push({
      id: `ecart-caisse-${monthStart}-${day}`,
      priorite: 'haute',
      categorie: 'finance',
      probleme:
        'Écart important entre les ventes enregistrées et les recettes caisse sur la période.',
      chiffres: {
        ca_billets: ca,
        recettes_caisse: finance.recettes_caisse,
        ecart_caisse: ecart,
        ratio_ecart_pct: Number(((ecart / ca) * 100).toFixed(1)),
      },
      periode,
      action:
        'Vérifiez les arrêts de compte non effectués et le rapprochement caisse. Les ventes ne sont pas automatiquement des encaissements.',
      resultat_attendu: null,
      lien: {
        ecran: 'finance',
        params: { from: monthStart, to: day },
      },
    });
  }

  // R5 — courrier : volume sans recettes (si données)
  if (
    finance.nb_colis !== null &&
    finance.ca_courrier !== null &&
    finance.nb_colis > 0 &&
    finance.ca_courrier === 0
  ) {
    recommendations.push({
      id: `courrier-zero-${monthStart}-${day}`,
      priorite: 'moyenne',
      categorie: 'courrier',
      probleme:
        'Des colis sont enregistrés alors que les recettes courrier calculées sont nulles sur la période.',
      chiffres: {
        nb_colis: finance.nb_colis,
        ca_courrier: finance.ca_courrier,
      },
      periode,
      action: 'Vérifiez les tarifs et la facturation des envois.',
      resultat_attendu: null,
      lien: { ecran: 'finance', params: { from: monthStart, to: day } },
    });
  }

  // R6 — meilleures compagnies (info, pas alerte)
  if (finance.by_company && finance.by_company.length > 1) {
    const top = finance.by_company[0];
    recommendations.push({
      id: `top-cie-${monthStart}-${day}`,
      priorite: 'basse',
      categorie: 'finance',
      probleme: `Compagnie la plus performante sur la période : ${top.nom_compagnie}.`,
      chiffres: {
        cle_compagnie: top.cle_compagnie,
        nom_compagnie: top.nom_compagnie,
        ca: top.ca,
        billets: top.billets,
      },
      periode,
      action: 'Comparer avec les autres compagnies du groupe (écran Finances).',
      resultat_attendu: null,
      lien: { ecran: 'finance', params: { from: monthStart, to: day } },
    });
  }

  const prioOrder = { haute: 0, moyenne: 1, basse: 2 };
  recommendations.sort(
    (a, b) => (prioOrder[a.priorite] ?? 9) - (prioOrder[b.priorite] ?? 9)
  );

  return {
    as_of: new Date().toISOString(),
    methode: 'regles_deterministes',
    avertissement:
      'Chaque conseil s’appuie uniquement sur des agrégats calculés. Aucune hypothèse n’est présentée comme une certitude. Le conseiller ne modifie jamais prix, billets ni dépenses.',
    periode,
    seuils: th,
    count: recommendations.length,
    recommendations,
  };
}


module.exports = { buildInsights };
