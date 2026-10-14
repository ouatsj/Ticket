'use strict';

const crypto = require('crypto');
const bcrypt = require('bcryptjs');

function isLegacySha1(stored) {
  return /^[0-9a-f]{40}$/i.test(String(stored || ''));
}

/**
 * Aligné sur passwordhash_helper.php (bcrypt + SHA-1 legacy).
 */
function checkPassword(plain, stored) {
  plain = String(plain || '');
  stored = String(stored || '');
  if (!stored) return false;
  if (isLegacySha1(stored)) {
    const digest = crypto.createHash('sha1').update(plain, 'utf8').digest('hex');
    if (stored.toLowerCase() === digest.toLowerCase()) return true;
    const double = crypto.createHash('sha1').update(digest, 'utf8').digest('hex');
    return stored.toLowerCase() === double.toLowerCase();
  }
  return bcrypt.compareSync(plain, stored);
}

module.exports = { checkPassword, isLegacySha1 };
