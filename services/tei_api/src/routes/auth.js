'use strict';

const express = require('express');
const { query } = require('../db');
const config = require('../config');
const { checkPassword } = require('../lib/password');
const { hashToken, signAccess, signRefresh, verifyRefresh } = require('../lib/tokens');
const { findDecideurByUsername, loadScopes } = require('../services/scope');
const { requireAuth } = require('../middleware/auth');

const router = express.Router();

async function issueTokens(user, req) {
  const scopes = await loadScopes(user.cpuser_id);
  if (!scopes.length) {
    const err = new Error('Aucun périmètre TEI configuré (tei_decideur_scope)');
    err.status = 403;
    throw err;
  }
  const payload = {
    sub: user.cpuser_id,
    username: user.username,
    roleattribut: user.roleattribut,
    role: 'Decideur',
  };
  const access = signAccess(payload);
  const refresh = signRefresh(payload);
  const exp = new Date(Date.now() + config.jwt.refreshTtlSec * 1000);
  await query(
    `INSERT INTO tei_tokens (cpuser_id, token_hash, access_jti, expires_at, user_agent, ip_addr)
     VALUES (:uid, :th, :jti, :exp, :ua, :ip)`,
    {
      uid: user.cpuser_id,
      th: hashToken(refresh.token),
      jti: access.jti,
      exp: exp.toISOString().slice(0, 19).replace('T', ' '),
      ua: (req.headers['user-agent'] || '').slice(0, 255),
      ip: (req.ip || '').slice(0, 45),
    }
  );
  return {
    access_token: access.token,
    refresh_token: refresh.token,
    expires_in: access.expiresIn,
    token_type: 'Bearer',
    user: {
      cpuser_id: user.cpuser_id,
      username: user.username,
      role: 'Decideur',
      scopes,
    },
  };
}

router.post('/login', async (req, res) => {
  try {
    const username = String((req.body && req.body.username) || '').trim();
    const password = String((req.body && req.body.password) || '');
    if (!username || !password) {
      return res.status(400).json({ error: 'username et password requis' });
    }
    const user = await findDecideurByUsername(username);
    if (!user || Number(user.activer) !== 0) {
      return res.status(401).json({ error: 'identifiants invalides' });
    }
    if (!checkPassword(password, user.upassword)) {
      return res.status(401).json({ error: 'identifiants invalides' });
    }
    const tokens = await issueTokens(user, req);
    return res.json(tokens);
  } catch (e) {
    const status = e.status || 500;
    return res.status(status).json({ error: e.message || 'erreur login' });
  }
});

router.post('/refresh', async (req, res) => {
  try {
    const refreshToken = String((req.body && req.body.refresh_token) || '');
    if (!refreshToken) {
      return res.status(400).json({ error: 'refresh_token requis' });
    }
    const decoded = verifyRefresh(refreshToken);
    const rows = await query(
      `SELECT id, cpuser_id, revoked_at, expires_at
       FROM tei_tokens WHERE token_hash = :th LIMIT 1`,
      { th: hashToken(refreshToken) }
    );
    const row = rows[0];
    if (!row || row.revoked_at || new Date(row.expires_at) < new Date()) {
      return res.status(401).json({ error: 'refresh invalide' });
    }
    await query(`UPDATE tei_tokens SET revoked_at = NOW() WHERE id = :id`, { id: row.id });
    const user = {
      cpuser_id: Number(decoded.sub),
      username: decoded.username,
      roleattribut: decoded.roleattribut,
    };
    const tokens = await issueTokens(user, req);
    return res.json(tokens);
  } catch (e) {
    return res.status(401).json({ error: 'refresh invalide' });
  }
});

router.post('/logout', requireAuth, async (req, res) => {
  try {
    const refreshToken = String((req.body && req.body.refresh_token) || '');
    if (refreshToken) {
      await query(
        `UPDATE tei_tokens SET revoked_at = NOW()
         WHERE token_hash = :th AND cpuser_id = :uid`,
        { th: hashToken(refreshToken), uid: req.user.cpuser_id }
      );
    }
    return res.status(204).end();
  } catch (e) {
    return res.status(500).json({ error: 'erreur logout' });
  }
});

module.exports = router;
