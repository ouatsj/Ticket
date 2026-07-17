<?php
/**
 * Migration additive SuperAdmin.
 *
 * Aperçu : php scripts/db/migrate_super_admin.php --allow-remote
 * Application : php scripts/db/migrate_super_admin.php --allow-remote --apply
 */

require __DIR__ . '/_bootstrap.php';

$apply = in_array('--apply', $argv, TRUE);
$db = db_script_connect($argv);

$statements = array(
    "CREATE TABLE IF NOT EXISTS super_admin_accounts (
        cpuser_id INT NOT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        must_change_password TINYINT(1) NOT NULL DEFAULT 1,
        created_by INT NULL,
        created_at DATETIME NOT NULL,
        password_changed_at DATETIME NULL,
        PRIMARY KEY (cpuser_id),
        KEY idx_super_admin_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

    "CREATE TABLE IF NOT EXISTS app_permissions (
        permission_code VARCHAR(80) NOT NULL,
        module_name VARCHAR(80) NOT NULL,
        permission_label VARCHAR(160) NOT NULL,
        display_order INT NOT NULL DEFAULT 0,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (permission_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

    "CREATE TABLE IF NOT EXISTS user_permissions (
        cpuser_id INT NOT NULL,
        permission_code VARCHAR(80) NOT NULL,
        is_allowed TINYINT(1) NOT NULL DEFAULT 0,
        granted_by INT NOT NULL,
        granted_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        PRIMARY KEY (cpuser_id, permission_code),
        KEY idx_user_permission_code (permission_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

    "CREATE TABLE IF NOT EXISTS super_admin_settings (
        setting_key VARCHAR(80) NOT NULL,
        setting_value VARCHAR(255) NOT NULL,
        updated_by INT NULL,
        updated_at DATETIME NOT NULL,
        PRIMARY KEY (setting_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

    "CREATE TABLE IF NOT EXISTS super_admin_audit_log (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        actor_cpuser_id INT NULL,
        target_cpuser_id INT NULL,
        action_code VARCHAR(80) NOT NULL,
        details_json TEXT NULL,
        ip_address VARCHAR(45) NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY idx_super_admin_audit_actor (actor_cpuser_id),
        KEY idx_super_admin_audit_target (target_cpuser_id),
        KEY idx_super_admin_audit_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
);

$permissions = array(
    array('audit.view', 'Audit', 'Voir les rapports d’audit', 10),
    array('audit.generate', 'Audit', 'Générer un rapport d’audit', 20),
    array('documentation.view', 'Documentation', 'Consulter la documentation et les QCM', 30),
    array('documentation.answers', 'Documentation', 'Voir et imprimer les corrigés des QCM', 40),
);

if (!$apply) {
    echo "APERÇU : 5 tables additives seront créées et 4 permissions seront enregistrées.\n";
    echo "Aucune table existante ne sera modifiée. Relancez avec --apply.\n";
    exit(0);
}

foreach ($statements as $sql) {
    if (!$db->query($sql)) {
        fwrite(STDERR, "Migration échouée : {$db->error}\n");
        exit(1);
    }
}

$stmt = $db->prepare(
    "INSERT INTO app_permissions
        (permission_code, module_name, permission_label, display_order, is_active)
     VALUES (?, ?, ?, ?, 1)
     ON DUPLICATE KEY UPDATE
        module_name = VALUES(module_name),
        permission_label = VALUES(permission_label),
        display_order = VALUES(display_order),
        is_active = 1"
);
foreach ($permissions as $permission) {
    $stmt->bind_param('sssi', $permission[0], $permission[1], $permission[2], $permission[3]);
    if (!$stmt->execute()) {
        fwrite(STDERR, "Insertion permission échouée : {$stmt->error}\n");
        exit(1);
    }
}

$now = date('Y-m-d H:i:s');
$stmt = $db->prepare(
    "INSERT INTO super_admin_settings (setting_key, setting_value, updated_by, updated_at)
     VALUES ('permission_enforcement_enabled', '1', NULL, ?)
     ON DUPLICATE KEY UPDATE setting_key = VALUES(setting_key)"
);
$stmt->bind_param('s', $now);
$stmt->execute();

echo "OK : migration SuperAdmin appliquée sans modification de table existante.\n";

