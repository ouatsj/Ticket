'use strict';

const fs = require('fs');
const path = require('path');
const config = require('../config');

let messaging = null;
let mode = 'disabled';

function initFcm() {
  if (messaging || mode === 'mock' || mode === 'live') return;
  const saPath = process.env.FIREBASE_SERVICE_ACCOUNT;
  if (!saPath) {
    mode = process.env.FCM_MOCK === '1' ? 'mock' : 'disabled';
    return;
  }
  try {
    // eslint-disable-next-line import/no-extraneous-dependencies, global-require
    const admin = require('firebase-admin');
    const abs = path.isAbsolute(saPath) ? saPath : path.join(__dirname, '..', '..', saPath);
    if (!fs.existsSync(abs)) {
      mode = 'disabled';
      // eslint-disable-next-line no-console
      console.warn('FCM: fichier service account introuvable — push désactivé');
      return;
    }
    const cred = require(abs);
    if (!admin.apps.length) {
      admin.initializeApp({ credential: admin.credential.cert(cred) });
    }
    messaging = admin.messaging();
    mode = 'live';
    // eslint-disable-next-line no-console
    console.log('FCM: mode live');
  } catch (e) {
    mode = process.env.FCM_MOCK === '1' ? 'mock' : 'disabled';
    // eslint-disable-next-line no-console
    console.warn('FCM: init échouée —', e.message);
  }
}

function getFcmStatus() {
  initFcm();
  return { mode, configured: mode === 'live' || mode === 'mock' };
}

/**
 * Envoie une notification à une liste de tokens FCM.
 * En mode mock : journalise sans appeler Firebase.
 * En mode disabled : no-op (alertes restent en base).
 */
async function sendToTokens(tokens, { title, body, data }) {
  initFcm();
  const list = (tokens || []).filter(Boolean);
  if (!list.length) return { sent: 0, failed: 0, mode };

  if (mode === 'mock') {
    // eslint-disable-next-line no-console
    console.log('[FCM mock]', title, '→', list.length, 'appareils', data);
    return { sent: list.length, failed: 0, mode: 'mock' };
  }
  if (mode !== 'live' || !messaging) {
    return { sent: 0, failed: 0, mode: 'disabled' };
  }

  const res = await messaging.sendEachForMulticast({
    tokens: list,
    notification: { title, body },
    data: Object.fromEntries(
      Object.entries(data || {}).map(([k, v]) => [k, String(v)])
    ),
    android: { priority: 'high' },
    apns: { payload: { aps: { sound: 'default' } } },
  });
  return { sent: res.successCount, failed: res.failureCount, mode: 'live' };
}

module.exports = { initFcm, getFcmStatus, sendToTokens };
