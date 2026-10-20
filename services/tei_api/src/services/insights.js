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

  const minutesAvant = (dep) => {
    const hm = String(dep.heure || '').match(/^(\d{1,2}):(\d{2})/);
    const ymdParts = day.match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (!hm || !ymdParts) return null;
    const when = new Date(
      Number(ymdParts[1]),
      Number(ymdParts[2]) - 1,
      Number(ymdParts[3]),
      Number(hm[1]),
      Number(hm[2]),
      0
    );
    return (when.getTime() - Date.now()) / 60000;
  };
  const connus = deps.departures.filter(
    (dep) => dep.capacite > 0 && dep.taux_remplissage !== null
  );
  const fenetre = Number(th.minutes_before_depart) || 60;

  // Un car complet loin de l'heure n'est pas un problème. On ne le dit que s'il part bientôt.
  const completsProches = connus.filter((dep) => {
    const mins = minutesAvant(dep);
    const plein = dep.taux_remplissage >= 100 || dep.places_disponibles === 0;
    return plein && dep.places_occupees > 0 && mins !== null && mins <= fenetre && mins >= -20;
  });
  const parLigne = new Map();
  for (const dep of completsProches) {
    const cle = `${dep.compagnie}|${dep.ligne}`;
    if (!parLigne.has(cle)) parLigne.set(cle, []);
    parLigne.get(cle).push(dep);
  }
  for (const [cle, liste] of parLigne) {
    const encoreOuverts = connus.filter(
      (dep) =>
        `${dep.compagnie}|${dep.ligne}` === cle &&
        (dep.places_disponibles || 0) > 0 &&
        (minutesAvant(dep) || 0) > 0
    );
    if (liste.length >= 2 && encoreOuverts.length === 0) {
      recommendations.push({
        id: `full-line-${day}-${cle}`,
        priorite: 'haute',
        categorie: 'depart',
        probleme: `${liste.length} départs de ${liste[0].ligne} (${liste[0].compagnie}) sont complets et partent dans l'heure, sans autre car de cette ligne encore ouvert.`,
        chiffres: {
          ligne: liste[0].ligne,
          compagnie: liste[0].compagnie,
          heures: liste.map((x) => x.heure),
        },
        periode: { date: day },
        action: 'Regardez s\'il reste un véhicule pour un départ supplémentaire sur cette ligne seulement.',
        resultat_attendu: null,
        lien: { ecran: 'departures', params: { date: day } },
      });
    } else if (liste.length === 1) {
      const dep = liste[0];
      recommendations.push({
        id: `full-${dep.code_progr}`,
        priorite: 'basse',
        categorie: 'depart',
        probleme: `Le départ de ${dep.heure} ${dep.ligne} (${dep.gare_depart} → ${dep.destination}) est complet : ${dep.places_occupees}/${dep.capacite} places.`,
        chiffres: {
          heure: dep.heure,
          ligne: dep.ligne,
          compagnie: dep.compagnie,
          agence: dep.gare_depart,
          places_occupees: dep.places_occupees,
          capacite: dep.capacite,
        },
        periode: { date: day },
        action: encoreOuverts.length
          ? `Il reste ${encoreOuverts.length} départ(s) ouvert(s) sur la même ligne. Un car complet est une vente normale, pas un motif d'ajouter un véhicule.`
          : 'Il n\'y a plus de place à vendre sur ce départ. N\'ajoutez un car que si la file au guichet continue.',
        resultat_attendu: null,
        lien: { ecran: 'departures', params: { date: day, code_progr: dep.code_progr } },
      });
    }
  }

  // Places vides seulement si le car est déjà parti ou part dans 30 minutes.
  const faibles = connus.filter((dep) => {
    const mins = minutesAvant(dep);
    return mins !== null && mins <= 30 && dep.taux_remplissage < th.low_fill_pct;
  });
  if (faibles.length >= 2) {
    const avg = faibles.reduce((s, x) => s + x.taux_remplissage, 0) / faibles.length;
    recommendations.push({
      id: `low-fill-${day}`,
      priorite: 'moyenne',
      categorie: 'depart',
      probleme: `${faibles.length} cars partis ou sur le point de partir ont encore beaucoup de places vides (moins de ${th.low_fill_pct} %).`,
      chiffres: {
        nb_departs_faibles: faibles.length,
        taux_moyen: Number(avg.toFixed(1)),
        seuil: th.low_fill_pct,
        exemples: faibles.slice(0, 4).map((x) => ({
          heure: x.heure,
          ligne: x.ligne,
          agence: x.gare_depart,
          taux: x.taux_remplissage,
          vendus: x.billets_vendus,
          capacite: x.capacite,
        })),
      },
      periode: { date: day },
      action: 'Ce n\'est pas un car du soir encore en vente. Regardez si ces horaires se répètent vides plusieurs jours avant de retirer un départ.',
      resultat_attendu: null,
      lien: { ecran: 'departures', params: { date: day } },
    });
  }

  const heureLocale = new Date().getHours();
  if (financePrev.ca_billets > 0 && heureLocale >= 16) {
    const delta =
      ((summary.ca_jour - financePrev.ca_billets) / financePrev.ca_billets) * 100;
    if (delta <= -20) {
      recommendations.push({
        id: `ca-drop-${day}`,
        priorite: 'haute',
        categorie: 'finance',
        probleme: `En fin de journée, les billets vendus sont inférieurs de ${Math.abs(delta).toFixed(0)} % au même jour de la semaine précédente.`,
        chiffres: {
          ca_jour: summary.ca_jour,
          ca_jour_semaine_prec: financePrev.ca_billets,
          variation_pct: Number(delta.toFixed(1)),
        },
        periode: { date: day, compare_to: prevYmd },
        action: 'Comparez les mêmes lignes, pas le total brut : une ligne sans programme ce jour-là suffit à faire baisser le chiffre.',
        resultat_attendu: null,
        lien: { ecran: 'finance', params: { from: day, to: day } },
      });
    }
  }

  const ecart = Number(finance.ecart_caisse) || 0;
  const ca = Number(finance.ca_billets) || 0;
  const hier = new Date();
  hier.setDate(hier.getDate() - 1);
  const hierYmd = hier.toISOString().slice(0, 10);
  if (ca > 0 && day < hierYmd && ecart < 0 && Math.abs(ecart) / ca >= 0.35) {
    recommendations.push({
      id: `ecart-caisse-${monthStart}-${day}`,
      priorite: 'moyenne',
      categorie: 'finance',
      probleme: 'Sur une période déjà passée, la caisse enregistrée reste nettement en dessous des billets vendus.',
      chiffres: {
        ca_billets: ca,
        recettes_caisse: finance.recettes_caisse,
        ecart_caisse: ecart,
        ratio_ecart_pct: Number(((ecart / ca) * 100).toFixed(1)),
      },
      periode,
      action: 'Les ventes du jour ne sont pas encore des recettes de caisse tant que l\'arrêt n\'est pas validé. Vérifiez les arrêts encore ouverts, pas un manque de caisse du jour même.',
      resultat_attendu: null,
      lien: { ecran: 'finance', params: { from: monthStart, to: day } },
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

  const prioOrder = { haute: 0, moyenne: 1, basse: 2 };
  recommendations.sort(
    (a, b) => (prioOrder[a.priorite] ?? 9) - (prioOrder[b.priorite] ?? 9)
  );

  // Série 14 jours pour courbes de comparaison (jour inclus)
  const seriesFrom = (() => {
    const x = new Date(Date.UTC(y, m - 1, d));
    x.setUTCDate(x.getUTCDate() - 13);
    return x.toISOString().slice(0, 10);
  })();
  const financeWindow = await financeSales(scope, ekey, companyParam, seriesFrom, day);
  const series = financeWindow.series || [];
  const caSeries = series.map((p) => p.ca);
  const avgCa =
    caSeries.length > 0 ? caSeries.reduce((a, b) => a + b, 0) / caSeries.length : 0;

  return {
    as_of: new Date().toISOString(),
    methode: 'regles_deterministes',
    avertissement:
      'Chaque conseil s’appuie uniquement sur des agrégats calculés. Aucune hypothèse n’est présentée comme une certitude. Le conseiller ne modifie jamais prix, billets ni dépenses.',
    periode,
    seuils: th,
    compare: {
      jour: {
        date: day,
        ca: summary.ca_jour,
        billets: summary.billets_jour,
        bagages: summary.ca_bagages_jour,
        courrier: summary.ca_courrier_jour,
      },
      veille: {
        date: (() => {
          const x = new Date(Date.UTC(y, m - 1, d));
          x.setUTCDate(x.getUTCDate() - 1);
          return x.toISOString().slice(0, 10);
        })(),
        ca: summary.ca_veille,
      },
      meme_jour_semaine_prec: {
        date: prevYmd,
        ca: financePrev.ca_billets,
        billets: financePrev.billets,
      },
      mois: {
        from: monthStart,
        to: day,
        ca: finance.ca_billets,
        billets: finance.billets,
      },
      moyenne_14j: {
        from: seriesFrom,
        to: day,
        ca: Number(avgCa.toFixed(0)),
      },
    },
    series,
    composition: {
      billets: finance.ca_billets,
      bagages: finance.ca_bagages,
      courrier: finance.ca_courrier,
      by_company: (finance.by_company || []).slice(0, 6),
    },
    count: recommendations.length,
    recommendations,
  };
}


module.exports = { buildInsights };
