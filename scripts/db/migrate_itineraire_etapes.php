<?php
/**
 * Migration essaiticket : segments → composition d'itinéraires (lignes).
 * Usage: php7.2 scripts/db/migrate_itineraire_etapes.php
 */
define('BASEPATH', true);
define('ENVIRONMENT', 'development');
require __DIR__ . '/../../application/config/database.php';
$d = $db['default'];
$m = new mysqli($d['hostname'], $d['username'], $d['password'], $d['database']);
$m->set_charset('utf8');
$m->query("CREATE TABLE IF NOT EXISTS itineraire_etapes (
  id_etape INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_lignes VARCHAR(64) NOT NULL,
  ident_ligne_etape VARCHAR(64) NOT NULL,
  ordre_etape TINYINT UNSIGNED NOT NULL DEFAULT 1,
  actif_etape TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_etape),
  KEY idx_parent_ordre (id_lignes, ordre_etape)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$m->query("DELETE FROM itineraire_etapes");
$q = $m->query("SELECT il.id_lignes, i.code_itineraires, il.actifint
 FROM itineraire_lignes il
 JOIN itineraires i ON i.id_itineraire = il.ident_itineraires
 WHERE i.actiftine = 1
 ORDER BY il.id_lignes, il.id_tabitinligne");
$ordre = [];
$ins = $m->prepare("INSERT INTO itineraire_etapes (id_lignes, ident_ligne_etape, ordre_etape, actif_etape) VALUES (?,?,?,?)");
$n = 0;
while ($row = $q->fetch_assoc()) {
  $p = $row['id_lignes'];
  if (!isset($ordre[$p])) $ordre[$p] = 0;
  $ordre[$p]++;
  $a = (int)$row['actifint'];
  $o = $ordre[$p];
  $ins->bind_param('ssii', $p, $row['code_itineraires'], $o, $a);
  $ins->execute();
  $n++;
}
echo "Migrated $n etapes\n";
