'use strict';

/**
 * Smoke sans login : vérifie health + qu'une requête CA ekey 1000 répond
 * (nécessite .env). Usage :
 *   node scripts/smoke_dashboard.js
 */
const http = require('http');
const config = require('../src/config');
const { buildSummary } = require('../src/services/dashboard');
const { getPool } = require('../src/db');

function get(path) {
  return new Promise((resolve, reject) => {
    http
      .get({ host: '127.0.0.1', port: config.port, path }, (res) => {
        let b = '';
        res.on('data', (c) => (b += c));
        res.on('end', () => resolve({ status: res.statusCode, body: b }));
      })
      .on('error', reject);
  });
}

async function main() {
  const health = await get('/v1/health');
  if (health.status !== 200) throw new Error('health fail ' + health.status);
  console.log('health OK');

  const scope = { ekey: 1000, all: true, companies: [] };
  const summary = await buildSummary(scope, 1000, null, null);
  console.log(
    JSON.stringify(
      {
        date: summary.date,
        ca_jour: summary.ca_jour,
        billets_jour: summary.billets_jour,
        ecart_caisse_jour: summary.ecart_caisse_jour,
        companies: summary.companies.length,
      },
      null,
      2
    )
  );
  await getPool().end();
}

main().catch(async (e) => {
  console.error(e.message || e);
  try {
    await getPool().end();
  } catch (_) {}
  process.exit(1);
});
