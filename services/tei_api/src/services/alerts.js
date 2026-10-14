'use strict';

const { query } = require('../db');
const { listDepartures } = require('./departures');
const { financeSales } = require('./finance');
const { sendToTokens } = require('./fcm');

async function loadThresholds(ekey) {
  const rows = await query(
    `SELECT * FROM tei_alert_thresholds
     WHERE actif = 1 AND (ekey IS NULL OR ekey = :ekey)
     ORDER BY (ekey IS NULL) ASC, id ASC
     LIMIT 1`,
    { ekey }
  );
  const t = rows[0] || {};
  return {
    fill_orange_pct: Number(t.fill_orange_pct) || 80,
    fill_priority_pct: Number(t.fill_priority_pct) || 90,
    low_fill_pct: Number(t.low_fill_pct) || 40,
    minutes_before_depart: Number(t.minutes_before_depart) || 60,
  };
}

/**
 * Détermine les seuils franchis pour un départ (du plus bas au plus haut).
 * Une alerte par seuil : anti-doublon via tei_alert_dedupe.alert_key.
 */
function thresholdsCrossed(dep, th) {
  const taux = dep.taux_remplissage;
  if (taux === null || taux === undefined) return [];
  const out = [];
  if (taux >= th.fill_orange_pct) {
    out.push({
      level: 'vigilance',
      seuil: th.fill_orange_pct,
      key: `occupancy:${dep.code_progr}:${th.fill_orange_pct}:${dep.date}`,
    });
  }
  if (taux >= th.fill_priority_pct) {
    out.push({
      level: 'critique',
      seuil: th.fill_priority_pct,
      key: `occupancy:${dep.code_progr}:${th.fill_priority_pct}:${dep.date}`,
    });
  }
  if (taux >= 100 || dep.places_disponibles === 0) {
    out.push({
      level: 'critique',
      seuil: 100,
      key: `occupancy:${dep.code_progr}:100:${dep.date}`,
    });
  }
  return out;
}

async function alreadyEmitted(alertKey) {
  const rows = await query(
    `SELECT alert_key FROM tei_alert_dedupe WHERE alert_key = :k LIMIT 1`,
    { k: alertKey }
  );
  return rows.length > 0;
}

async function markEmitted(alertKey, ekey) {
  await query(
    `INSERT INTO tei_alert_dedupe (alert_key, ekey, first_emitted_at, last_emitted_at)
     VALUES (:k, :ekey, NOW(), NOW())
     ON DUPLICATE KEY UPDATE last_emitted_at = NOW()`,
    { k: alertKey, ekey }
  );
}

async function persistAlert(row) {
  await query(
    `INSERT INTO tei_alerts_emises
      (alert_key, ekey, cle_compagnie, code_progr, niveau, categorie, titre, corps, payload_json, emitted_at)
     VALUES
      (:alert_key, :ekey, :cie, :code, :niveau, :categorie, :titre, :corps, :payload, NOW())`,
    row
  );
}

async function devicesForEkey(ekey, categorie, niveau) {
  // Décideurs avec scope sur ekey + préférences
  const rows = await query(
    `SELECT DISTINCT d.fcm_token, d.cpuser_id, p.cat_depart, p.cat_finance,
            p.cat_courrier, p.cat_bagages, p.cat_systeme, p.urgentes_seulement
     FROM tei_devices d
     JOIN tei_decideur_scope s ON s.cpuser_id = d.cpuser_id AND s.ekey = :ekey AND s.actif = 1
     LEFT JOIN tei_notification_prefs p ON p.cpuser_id = d.cpuser_id
     WHERE d.actif = 1`,
    { ekey }
  );
  return rows.filter((r) => {
    if (r.urgentes_seulement && niveau !== 'critique') return false;
    const map = {
      depart: r.cat_depart,
      finance: r.cat_finance,
      courrier: r.cat_courrier,
      bagages: r.cat_bagages,
      systeme: r.cat_systeme,
    };
    const pref = map[categorie];
    if (pref === null || pref === undefined) return true; // défaut = activé
    return Number(pref) === 1;
  });
}

async function emitAndPush({ ekey, dep, crossed }) {
  const titre =
    crossed.seuil >= 100
      ? `COMPLET — ${dep.heure} ${dep.ligne}`
      : crossed.seuil >= 90
        ? `Remplissage ${crossed.seuil} % — ${dep.heure}`
        : `Vigilance ${crossed.seuil} % — ${dep.heure}`;
  const corps =
    `${dep.compagnie} · ${dep.gare_depart} → ${dep.destination}. ` +
    `${dep.places_occupees}/${dep.capacite} places (${dep.taux_remplissage} %).`;

  const payload = {
    type: 'depart_occupancy',
    code_progr: dep.code_progr,
    ekey: String(ekey),
    company: String(dep.cle_compagnie),
    seuil: String(crossed.seuil),
    date: dep.date,
  };

  await persistAlert({
    alert_key: crossed.key,
    ekey,
    cie: dep.cle_compagnie,
    code: dep.code_progr,
    niveau: crossed.level,
    categorie: 'depart',
    titre,
    corps,
    payload: JSON.stringify(payload),
  });
  await markEmitted(crossed.key, ekey);

  const devices = await devicesForEkey(ekey, 'depart', crossed.level);
  const push = await sendToTokens(
    devices.map((d) => d.fcm_token),
    { title: titre, body: corps, data: payload }
  );
  return { alert_key: crossed.key, titre, push };
}

async function emitGenericAndPush({
  ekey,
  alertKey,
  niveau,
  categorie,
  titre,
  corps,
  cleCompagnie,
  codeProgr,
  payload,
}) {
  if (await alreadyEmitted(alertKey)) return null;
  await persistAlert({
    alert_key: alertKey,
    ekey,
    cie: cleCompagnie || null,
    code: codeProgr || null,
    niveau,
    categorie,
    titre,
    corps,
    payload: JSON.stringify(payload || {}),
  });
  await markEmitted(alertKey, ekey);
  const devices = await devicesForEkey(ekey, categorie, niveau);
  const push = await sendToTokens(
    devices.map((d) => d.fcm_token),
    { title: titre, body: corps, data: payload || {} }
  );
  return { alert_key: alertKey, titre, push };
}

/**
 * Scanne départs (remplissage) + écart caisse période du mois.
 */
async function scanOccupancyAlerts(scope, ekey, dateYmd) {
  const th = await loadThresholds(ekey);
  const { departures } = await listDepartures(scope, ekey, null, dateYmd);
  const emitted = [];
  for (const dep of departures) {
    const crossedList = thresholdsCrossed(dep, th);
    for (const crossed of crossedList) {
      if (await alreadyEmitted(crossed.key)) continue;
      const result = await emitAndPush({ ekey, dep, crossed });
      emitted.push(result);
    }
  }

  // Écart ventes ↔ caisse (mois jusqu'à date)
  const monthStart = `${dateYmd.slice(0, 8)}01`;
  const fin = await financeSales(scope, ekey, null, monthStart, dateYmd);
  const ca = Number(fin.ca_billets) || 0;
  const ecart = Number(fin.ecart_caisse) || 0;
  if (ca > 0 && Math.abs(ecart) / ca >= 0.2) {
    const key = `finance:ecart:${ekey}:${monthStart}:${dateYmd}`;
    const result = await emitGenericAndPush({
      ekey,
      alertKey: key,
      niveau: 'critique',
      categorie: 'finance',
      titre: 'Écart ventes / caisse',
      corps:
        `CA billets ${ca} F vs recettes caisse ${fin.recettes_caisse} F ` +
        `(écart ${ecart} F, ${((ecart / ca) * 100).toFixed(1)} %). Vérification recommandée.`,
      payload: {
        type: 'finance_ecart',
        ekey: String(ekey),
        from: monthStart,
        to: dateYmd,
        ca_billets: String(ca),
        ecart: String(ecart),
      },
    });
    if (result) emitted.push(result);
  }

  return {
    date: dateYmd,
    thresholds: th,
    scanned: departures.length,
    emitted: emitted.length,
    alerts: emitted,
  };
}

async function listNotifications({ ekey, categorie, niveau, limit }) {
  const params = { ekey, lim: Math.min(Number(limit) || 50, 200) };
  let sql = `
    SELECT id, alert_key, ekey, cle_compagnie, code_progr, niveau, categorie,
           titre, corps, payload_json, emitted_at
    FROM tei_alerts_emises
    WHERE ekey = :ekey`;
  if (categorie && categorie !== 'toutes') {
    sql += ' AND categorie = :cat';
    params.cat = categorie;
  }
  if (niveau === 'urgentes' || niveau === 'critique') {
    sql += " AND niveau = 'critique'";
  } else if (niveau && niveau !== 'toutes') {
    sql += ' AND niveau = :niv';
    params.niv = niveau;
  }
  sql += ' ORDER BY emitted_at DESC LIMIT :lim';
  // mysql2 named placeholders + LIMIT sometimes picky — use interpolate lim safely
  sql = sql.replace('LIMIT :lim', `LIMIT ${params.lim}`);
  delete params.lim;
  return query(sql, params);
}

async function getPrefs(cpuserId) {
  const rows = await query(
    `SELECT * FROM tei_notification_prefs WHERE cpuser_id = :id LIMIT 1`,
    { id: cpuserId }
  );
  if (rows[0]) return rows[0];
  return {
    cpuser_id: cpuserId,
    cat_depart: 1,
    cat_finance: 1,
    cat_courrier: 1,
    cat_bagages: 1,
    cat_systeme: 1,
    urgentes_seulement: 0,
  };
}

async function savePrefs(cpuserId, body) {
  const b = body || {};
  await query(
    `INSERT INTO tei_notification_prefs
      (cpuser_id, cat_depart, cat_finance, cat_courrier, cat_bagages, cat_systeme, urgentes_seulement)
     VALUES (:id, :d, :f, :c, :b, :s, :u)
     ON DUPLICATE KEY UPDATE
       cat_depart = VALUES(cat_depart),
       cat_finance = VALUES(cat_finance),
       cat_courrier = VALUES(cat_courrier),
       cat_bagages = VALUES(cat_bagages),
       cat_systeme = VALUES(cat_systeme),
       urgentes_seulement = VALUES(urgentes_seulement)`,
    {
      id: cpuserId,
      d: b.cat_depart !== undefined ? (b.cat_depart ? 1 : 0) : 1,
      f: b.cat_finance !== undefined ? (b.cat_finance ? 1 : 0) : 1,
      c: b.cat_courrier !== undefined ? (b.cat_courrier ? 1 : 0) : 1,
      b: b.cat_bagages !== undefined ? (b.cat_bagages ? 1 : 0) : 1,
      s: b.cat_systeme !== undefined ? (b.cat_systeme ? 1 : 0) : 1,
      u: b.urgentes_seulement ? 1 : 0,
    }
  );
  return getPrefs(cpuserId);
}

module.exports = {
  scanOccupancyAlerts,
  listNotifications,
  getPrefs,
  savePrefs,
  loadThresholds,
};
