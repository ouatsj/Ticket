'use strict';

const express = require('express');
const { query } = require('../db');
const { requireAuth } = require('../middleware/auth');
const { assertEkeyAllowed } = require('../services/scope');
const { buildSummary, listCompanies, ymd } = require('../services/dashboard');
const { listDepartures } = require('../services/departures');
const { financeSales } = require('../services/finance');
const {
  scanOccupancyAlerts,
  listNotifications,
  getPrefs,
  savePrefs,
} = require('../services/alerts');
const { buildInsights } = require('../services/insights');
const { getFcmStatus } = require('../services/fcm');
const authRoutes = require('./auth');

const router = express.Router();

router.use('/auth', authRoutes);

router.get('/health', (_req, res) => {
  res.json({
    ok: true,
    service: 'tei-api',
    version: '1.1.0',
    fcm: getFcmStatus(),
  });
});

router.get('/me', requireAuth, (req, res) => {
  res.json({
    cpuser_id: req.user.cpuser_id,
    username: req.user.username,
    role: 'Decideur',
    scopes: req.user.scopes,
  });
});

router.get('/companies', requireAuth, async (req, res) => {
  try {
    const ekey = Number(req.query.ekey);
    if (!ekey) return res.status(400).json({ error: 'ekey requis' });
    const scope = assertEkeyAllowed(req.user.scopes, ekey);
    const companies = await listCompanies(scope);
    res.json({ companies });
  } catch (e) {
    res.status(e.status || 500).json({ error: e.message || 'erreur' });
  }
});

router.get('/dashboard/summary', requireAuth, async (req, res) => {
  try {
    const ekey = Number(req.query.ekey);
    if (!ekey) return res.status(400).json({ error: 'ekey requis' });
    const scope = assertEkeyAllowed(req.user.scopes, ekey);
    const summary = await buildSummary(scope, ekey, req.query.company, req.query.date);
    res.json(summary);
  } catch (e) {
    res.status(e.status || 500).json({ error: e.message || 'erreur dashboard' });
  }
});

router.get('/departures', requireAuth, async (req, res) => {
  try {
    const ekey = Number(req.query.ekey);
    if (!ekey) return res.status(400).json({ error: 'ekey requis' });
    const scope = assertEkeyAllowed(req.user.scopes, ekey);
    const date = req.query.date ? ymd(req.query.date) : ymd(new Date());
    const data = await listDepartures(scope, ekey, req.query.company, date);
    res.json(data);
  } catch (e) {
    res.status(e.status || 500).json({ error: e.message || 'erreur départs' });
  }
});

router.get('/finance/sales', requireAuth, async (req, res) => {
  try {
    const ekey = Number(req.query.ekey);
    if (!ekey) return res.status(400).json({ error: 'ekey requis' });
    if (!req.query.from || !req.query.to) {
      return res.status(400).json({ error: 'from et to requis (YYYY-MM-DD)' });
    }
    const scope = assertEkeyAllowed(req.user.scopes, ekey);
    const data = await financeSales(
      scope,
      ekey,
      req.query.company,
      ymd(req.query.from),
      ymd(req.query.to)
    );
    res.json(data);
  } catch (e) {
    res.status(e.status || 500).json({ error: e.message || 'erreur finance' });
  }
});

router.get('/insights', requireAuth, async (req, res) => {
  try {
    const ekey = Number(req.query.ekey);
    if (!ekey) return res.status(400).json({ error: 'ekey requis' });
    const scope = assertEkeyAllowed(req.user.scopes, ekey);
    const date = req.query.date ? ymd(req.query.date) : ymd(new Date());
    const data = await buildInsights(scope, ekey, req.query.company, date);
    res.json(data);
  } catch (e) {
    res.status(e.status || 500).json({ error: e.message || 'erreur insights' });
  }
});

router.get('/notifications', requireAuth, async (req, res) => {
  try {
    const ekey = Number(req.query.ekey);
    if (!ekey) return res.status(400).json({ error: 'ekey requis' });
    assertEkeyAllowed(req.user.scopes, ekey);
    const items = await listNotifications({
      ekey,
      categorie: req.query.categorie || 'toutes',
      niveau: req.query.niveau || 'toutes',
      limit: req.query.limit || 50,
    });
    res.json({
      as_of: new Date().toISOString(),
      count: items.length,
      notifications: items.map((n) => ({
        ...n,
        payload: n.payload_json ? JSON.parse(n.payload_json) : null,
        payload_json: undefined,
      })),
    });
  } catch (e) {
    res.status(e.status || 500).json({ error: e.message || 'erreur notifications' });
  }
});

router.post('/alerts/scan', requireAuth, async (req, res) => {
  try {
    const ekey = Number((req.body && req.body.ekey) || req.query.ekey);
    if (!ekey) return res.status(400).json({ error: 'ekey requis' });
    const scope = assertEkeyAllowed(req.user.scopes, ekey);
    const date = ymd((req.body && req.body.date) || req.query.date || new Date());
    const result = await scanOccupancyAlerts(scope, ekey, date);
    res.json(result);
  } catch (e) {
    res.status(e.status || 500).json({ error: e.message || 'erreur scan' });
  }
});

router.get('/notifications/prefs', requireAuth, async (req, res) => {
  try {
    const prefs = await getPrefs(req.user.cpuser_id);
    res.json(prefs);
  } catch (e) {
    res.status(500).json({ error: e.message || 'erreur prefs' });
  }
});

router.put('/notifications/prefs', requireAuth, async (req, res) => {
  try {
    const prefs = await savePrefs(req.user.cpuser_id, req.body);
    res.json(prefs);
  } catch (e) {
    res.status(500).json({ error: e.message || 'erreur prefs' });
  }
});

router.post('/devices/register', requireAuth, async (req, res) => {
  try {
    const fcm = String((req.body && req.body.fcm_token) || '').trim();
    const platform = String((req.body && req.body.platform) || 'other');
    const appVersion = (req.body && req.body.app_version) || null;
    if (!fcm) return res.status(400).json({ error: 'fcm_token requis' });
    const plat = ['android', 'ios', 'other'].includes(platform) ? platform : 'other';
    await query(
      `INSERT INTO tei_devices (cpuser_id, platform, fcm_token, app_version, last_seen_at, actif)
       VALUES (:uid, :plat, :fcm, :ver, NOW(), 1)
       ON DUPLICATE KEY UPDATE
         cpuser_id = VALUES(cpuser_id),
         platform = VALUES(platform),
         app_version = VALUES(app_version),
         last_seen_at = NOW(),
         actif = 1`,
      { uid: req.user.cpuser_id, plat, fcm, ver: appVersion }
    );
    res.json({ ok: true, fcm: getFcmStatus() });
  } catch (e) {
    res.status(500).json({ error: e.message || 'erreur device' });
  }
});

module.exports = router;
