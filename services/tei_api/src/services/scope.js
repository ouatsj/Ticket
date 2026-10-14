'use strict';

const { query } = require('../db');
const config = require('../config');

/**
 * Un décideur doit avoir attributions_role.userole = 21 (Decideur)
 * actif (activer_role = 0, activeattrib = 1) et compte_user.activer = 0.
 */
async function loadDecideurProfile(cpuserId) {
  // Convention Ticket : activer=0 / activer_role=0 = actif.
  const rows = await query(
    `SELECT cu.cpuser_id, cu.username, cu.activer,
            ar.roleattribut, ar.userole, ar.activeattrib, ar.activer_role
     FROM compte_user cu
     JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
     JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
     WHERE cu.cpuser_id = :id
       AND ar.userole = :role
       AND IFNULL(ar.activer_role, 0) = 0
     LIMIT 1`,
    { id: cpuserId, role: config.roleDecideurId }
  );
  return rows[0] || null;
}

async function findDecideurByUsername(username) {
  const rows = await query(
    `SELECT cu.cpuser_id, cu.username, cu.upassword, cu.activer,
            ar.roleattribut, ar.userole
     FROM compte_user cu
     JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
     JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
     WHERE cu.username = :username
       AND ar.userole = :role
       AND IFNULL(ar.activer_role, 0) = 0
     LIMIT 1`,
    { username, role: config.roleDecideurId }
  );
  return rows[0] || null;
}

/**
 * Périmètre : tei_decideur_scope. Si aucune ligne, refuse (pas d'accès implicite).
 * cle_compagnie NULL = toutes les compagnies de l'ekey.
 */
async function loadScopes(cpuserId) {
  const scopes = await query(
    `SELECT ekey, cle_compagnie
     FROM tei_decideur_scope
     WHERE cpuser_id = :id AND actif = 1`,
    { id: cpuserId }
  );
  const byEkey = {};
  for (const s of scopes) {
    const k = String(s.ekey);
    if (!byEkey[k]) byEkey[k] = { ekey: s.ekey, companies: [], all: false };
    if (s.cle_compagnie === null || s.cle_compagnie === undefined) {
      byEkey[k].all = true;
      byEkey[k].companies = [];
    } else if (!byEkey[k].all) {
      byEkey[k].companies.push(Number(s.cle_compagnie));
    }
  }
  return Object.values(byEkey);
}

function assertEkeyAllowed(scopes, ekey) {
  const e = Number(ekey);
  const hit = scopes.find((s) => Number(s.ekey) === e);
  if (!hit) {
    const err = new Error('Entreprise non autorisée');
    err.status = 403;
    throw err;
  }
  return hit;
}

function companyFilterClause(scope, companyParam, aliasDest = 'dest') {
  const requested = companyParam !== undefined && companyParam !== null && companyParam !== ''
    ? Number(companyParam)
    : null;
  if (requested !== null) {
    if (!scope.all && !scope.companies.includes(requested)) {
      const err = new Error('Compagnie non autorisée');
      err.status = 403;
      throw err;
    }
    return { sql: ` AND ${aliasDest}.id_compaga = :company `, params: { company: requested } };
  }
  if (scope.all) {
    return { sql: '', params: {} };
  }
  if (!scope.companies.length) {
    return { sql: ' AND 1=0 ', params: {} };
  }
  const placeholders = scope.companies.map((_, i) => `:cie${i}`).join(',');
  const params = {};
  scope.companies.forEach((c, i) => {
    params[`cie${i}`] = c;
  });
  return {
    sql: ` AND ${aliasDest}.id_compaga IN (${placeholders}) `,
    params,
  };
}

module.exports = {
  loadDecideurProfile,
  findDecideurByUsername,
  loadScopes,
  assertEkeyAllowed,
  companyFilterClause,
};
