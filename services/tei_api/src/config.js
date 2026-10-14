'use strict';

const path = require('path');
require('dotenv').config({ path: path.join(__dirname, '..', '.env') });

function required(name, fallback) {
  const v = process.env[name];
  if (v !== undefined && v !== '') return v;
  if (fallback !== undefined) return fallback;
  throw new Error(`Variable d'environnement manquante: ${name}`);
}

const config = {
  port: parseInt(process.env.PORT || '8091', 10),
  tz: process.env.TZ || 'Europe/Berlin',
  db: {
    host: required('DB_HOST', '127.0.0.1'),
    port: parseInt(process.env.DB_PORT || '3306', 10),
    user: required('DB_USER', ''),
    password: process.env.DB_PASSWORD || '',
    database: required('DB_NAME', ''),
  },
  jwt: {
    accessSecret: required('JWT_ACCESS_SECRET', 'dev-access-change-me'),
    refreshSecret: required('JWT_REFRESH_SECRET', 'dev-refresh-change-me'),
    accessTtlSec: parseInt(process.env.ACCESS_TTL_SEC || '900', 10),
    refreshTtlSec: parseInt(process.env.REFRESH_TTL_SEC || String(30 * 24 * 3600), 10),
  },
  roleDecideurId: 21,
  corsOrigins: (process.env.CORS_ORIGINS || '*').split(',').map((s) => s.trim()),
};

module.exports = config;
