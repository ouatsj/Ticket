'use strict';

const { verifyAccess } = require('../lib/tokens');
const { loadScopes } = require('../services/scope');

async function requireAuth(req, res, next) {
  try {
    const hdr = req.headers.authorization || '';
    const m = hdr.match(/^Bearer\s+(.+)$/i);
    if (!m) {
      return res.status(401).json({ error: 'jeton manquant' });
    }
    const decoded = verifyAccess(m[1]);
    const scopes = await loadScopes(decoded.sub);
    req.user = {
      cpuser_id: Number(decoded.sub),
      username: decoded.username,
      roleattribut: decoded.roleattribut,
      scopes,
    };
    return next();
  } catch (e) {
    return res.status(401).json({ error: 'jeton invalide ou expiré' });
  }
}

module.exports = { requireAuth };
