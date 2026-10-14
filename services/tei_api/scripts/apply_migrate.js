'use strict';

const fs = require('fs');
const path = require('path');
const { getPool } = require('../src/db');

const FILES = [
  'migrate_tei_phase2_additive.sql',
  'migrate_tei_phase5_additive.sql',
];

async function runFile(conn, sqlPath) {
  const raw = fs.readFileSync(sqlPath, 'utf8');
  const statements = raw
    .split(/;\s*\n/)
    .map((s) => s.replace(/--[^\n]*/g, '').trim())
    .filter((s) => s.length > 0);
  for (const st of statements) {
    await conn.query(st);
    process.stdout.write('.');
  }
}

async function main() {
  const dbDir = path.join(__dirname, '..', '..', '..', 'scripts', 'db');
  const pool = getPool();
  const conn = await pool.getConnection();
  try {
    for (const f of FILES) {
      const p = path.join(dbDir, f);
      if (!fs.existsSync(p)) {
        console.warn('skip missing', f);
        continue;
      }
      process.stdout.write(`\n${f} `);
      await runFile(conn, p);
    }
    process.stdout.write('\nOK migrations TEI\n');
  } finally {
    conn.release();
    await pool.end();
  }
}

main().catch((e) => {
  console.error(e.message || e);
  process.exit(1);
});
