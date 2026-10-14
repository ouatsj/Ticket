'use strict';

/**
 * Cron / manuel : scanne les alertes de remplissage et pousse FCM si configuré.
 * Usage :
 *   node scripts/scan_alerts.js [ekey] [YYYY-MM-DD]
 * Ex. :
 *   FCM_MOCK=1 node scripts/scan_alerts.js 1000 2026-09-13
 */
require('dotenv').config({ path: require('path').join(__dirname, '..', '.env') });
process.env.FCM_MOCK = process.env.FCM_MOCK || '1';

const { scanOccupancyAlerts } = require('../src/services/alerts');
const { getPool } = require('../src/db');

async function main() {
  const ekey = Number(process.argv[2] || 1000);
  const date = process.argv[3] || null;
  const { ymd } = require('../src/services/dashboard');
  const day = date || ymd(new Date());
  const scope = { ekey, all: true, companies: [] };
  const result = await scanOccupancyAlerts(scope, ekey, day);
  console.log(JSON.stringify(result, null, 2));
  await getPool().end();
}

main().catch(async (e) => {
  console.error(e);
  try {
    await getPool().end();
  } catch (_) {}
  process.exit(1);
});
