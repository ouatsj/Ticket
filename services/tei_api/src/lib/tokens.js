'use strict';

const crypto = require('crypto');
const jwt = require('jsonwebtoken');
const { v4: uuidv4 } = require('uuid');
const config = require('../config');

function hashToken(raw) {
  return crypto.createHash('sha256').update(String(raw), 'utf8').digest('hex');
}

function signAccess(payload) {
  const jti = uuidv4();
  const token = jwt.sign(
    { ...payload, typ: 'access', jti },
    config.jwt.accessSecret,
    { expiresIn: config.jwt.accessTtlSec }
  );
  return { token, jti, expiresIn: config.jwt.accessTtlSec };
}

function signRefresh(payload) {
  const jti = uuidv4();
  const token = jwt.sign(
    { ...payload, typ: 'refresh', jti },
    config.jwt.refreshSecret,
    { expiresIn: config.jwt.refreshTtlSec }
  );
  return { token, jti, expiresIn: config.jwt.refreshTtlSec };
}

function verifyAccess(token) {
  const decoded = jwt.verify(token, config.jwt.accessSecret);
  if (decoded.typ !== 'access') throw new Error('type invalide');
  return decoded;
}

function verifyRefresh(token) {
  const decoded = jwt.verify(token, config.jwt.refreshSecret);
  if (decoded.typ !== 'refresh') throw new Error('type invalide');
  return decoded;
}

module.exports = {
  hashToken,
  signAccess,
  signRefresh,
  verifyAccess,
  verifyRefresh,
};
