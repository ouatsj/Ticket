<?php
/**
 * Migration additive des contrôles de prix de vente (EssaiTicket).
 *
 * Aperçu : php scripts/db/migrate_sales_price_controls.php --allow-remote
 * Application : php scripts/db/migrate_sales_price_controls.php --allow-remote --apply
 */

require __DIR__ . '/_bootstrap.php';

$apply = in_array('--apply', $argv, TRUE);
$db = db_script_connect($argv);

$statements = array(
    "CREATE TABLE IF NOT EXISTS app_settings (
        company_ekey INT NOT NULL,
        setting_key VARCHAR(100) NOT NULL,
        setting_value VARCHAR(500) NOT NULL,
        updated_by INT NULL,
        updated_at DATETIME NOT NULL,
        PRIMARY KEY (company_ekey, setting_key),
        KEY idx_app_settings_key (setting_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

    "CREATE TABLE IF NOT EXISTS sale_approval_requests (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        company_ekey INT NOT NULL,
        requester_cpuser_id INT NOT NULL,
        approver_cpuser_id INT NULL,
        request_type VARCHAR(50) NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        programme_code VARCHAR(100) NULL,
        normal_price DECIMAL(12,2) NOT NULL,
        requested_price DECIMAL(12,2) NOT NULL,
        discount_percent DECIMAL(7,2) NOT NULL DEFAULT 0,
        reason VARCHAR(500) NOT NULL,
        context_json TEXT NULL,
        requested_at DATETIME NOT NULL,
        decided_at DATETIME NULL,
        decision_reason VARCHAR(500) NULL,
        PRIMARY KEY (id),
        KEY idx_sale_approval_status (company_ekey, status, requested_at),
        KEY idx_sale_approval_requester (requester_cpuser_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

    "CREATE TABLE IF NOT EXISTS ticket_pricing_snapshot (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        company_ekey INT NOT NULL,
        code_passager VARCHAR(150) NOT NULL,
        code_ticket VARCHAR(150) NOT NULL,
        segment_type VARCHAR(30) NOT NULL DEFAULT 'aller',
        programme_code VARCHAR(100) NULL,
        normal_price DECIMAL(12,2) NOT NULL,
        sold_price DECIMAL(12,2) NOT NULL,
        sale_nature VARCHAR(20) NOT NULL,
        authorization_type VARCHAR(30) NOT NULL DEFAULT 'divers',
        reason VARCHAR(500) NULL,
        seller_cpuser_id INT NOT NULL,
        approval_request_id BIGINT UNSIGNED NULL,
        travel_card_id VARCHAR(150) NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uq_ticket_pricing_segment (code_passager, code_ticket, segment_type),
        KEY idx_ticket_pricing_report (company_ekey, sale_nature, created_at),
        KEY idx_ticket_pricing_seller (seller_cpuser_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

    "CREATE TABLE IF NOT EXISTS ticket_print_events (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        company_ekey INT NOT NULL,
        code_passager VARCHAR(150) NOT NULL,
        code_ticket VARCHAR(150) NOT NULL,
        printed_by INT NOT NULL,
        printed_at DATETIME NOT NULL,
        print_type VARCHAR(30) NOT NULL DEFAULT 'initial',
        PRIMARY KEY (id),
        KEY idx_ticket_first_print (company_ekey, code_passager, code_ticket, printed_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

    "CREATE TABLE IF NOT EXISTS ticket_audit_log (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        company_ekey INT NOT NULL,
        code_passager VARCHAR(150) NOT NULL,
        code_ticket VARCHAR(150) NOT NULL,
        actor_cpuser_id INT NOT NULL,
        action_code VARCHAR(80) NOT NULL,
        old_values_json TEXT NULL,
        new_values_json TEXT NULL,
        reason VARCHAR(500) NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY idx_ticket_audit_ticket (company_ekey, code_passager, code_ticket),
        KEY idx_ticket_audit_actor (actor_cpuser_id, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

    "CREATE TABLE IF NOT EXISTS travel_card_usage (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        company_ekey INT NOT NULL,
        travel_card_id VARCHAR(150) NOT NULL,
        card_number VARCHAR(150) NOT NULL,
        code_passager VARCHAR(150) NOT NULL,
        code_ticket VARCHAR(150) NOT NULL,
        segment_type VARCHAR(30) NOT NULL DEFAULT 'aller',
        programme_code VARCHAR(100) NULL,
        seller_cpuser_id INT NOT NULL,
        seller_roleattribut INT NULL,
        normal_price DECIMAL(12,2) NOT NULL DEFAULT 0,
        sold_price DECIMAL(12,2) NOT NULL DEFAULT 0,
        usage_status VARCHAR(20) NOT NULL DEFAULT 'confirmed',
        used_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uq_travel_card_ticket (code_passager, code_ticket, segment_type),
        KEY idx_travel_card_usage_card (company_ekey, travel_card_id, used_at),
        KEY idx_travel_card_usage_seller (seller_cpuser_id, used_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

    "CREATE TABLE IF NOT EXISTS cash_closure_audit (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        company_ekey INT NOT NULL,
        closure_type VARCHAR(30) NOT NULL DEFAULT 'ticket',
        legacy_closure_id BIGINT UNSIGNED NULL,
        roleattribut INT NOT NULL,
        company_code VARCHAR(100) NULL,
        gare_id VARCHAR(128) NULL,
        sousgare_id INT NULL,
        declared_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
        expected_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
        recorded_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
        difference_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
        reason VARCHAR(500) NULL,
        review_status VARCHAR(20) NOT NULL DEFAULT 'clear',
        created_by_cpuser_id INT NOT NULL,
        reviewed_by_cpuser_id INT NULL,
        reviewed_at DATETIME NULL,
        review_reason VARCHAR(500) NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY idx_closure_audit_review (company_ekey, review_status, created_at),
        KEY idx_closure_audit_role (roleattribut, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

    "CREATE TABLE IF NOT EXISTS fraud_control_events (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        company_ekey INT NOT NULL,
        event_code VARCHAR(80) NOT NULL,
        severity VARCHAR(20) NOT NULL DEFAULT 'info',
        actor_cpuser_id INT NULL,
        roleattribut INT NULL,
        entity_type VARCHAR(50) NULL,
        entity_id VARCHAR(150) NULL,
        details_json TEXT NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY idx_fraud_event_company (company_ekey, severity, created_at),
        KEY idx_fraud_event_actor (actor_cpuser_id, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

    "CREATE TABLE IF NOT EXISTS sale_request_idempotency (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        company_ekey INT NOT NULL,
        actor_cpuser_id INT NOT NULL,
        nonce_hash CHAR(64) NOT NULL,
        request_status VARCHAR(20) NOT NULL DEFAULT 'pending',
        created_at DATETIME NOT NULL,
        completed_at DATETIME NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uq_sale_request_nonce (company_ekey, actor_cpuser_id, nonce_hash),
        KEY idx_sale_request_status (company_ekey, request_status, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
);

$permissions = array(
    array('sales.price.free', 'Ventes', 'Saisir un prix différent du tarif normal', 100),
    array('sales.discount.approve', 'Ventes', 'Valider une réduction dépassant le seuil', 110),
    array('sales.ticket.edit_after_print', 'Ventes', 'Modifier un billet après impression', 120),
    array('sales.misc.approve', 'Ventes', 'Valider une vente de type Divers', 130),
    array('sales.card.zero_fare', 'Cartes voyage', 'Émettre un billet gratuit avec une carte valide', 140),
    array('sales.card.manage_expiry', 'Cartes voyage', 'Gérer la péremption des cartes de voyage', 150),
    array('sales.settings.manage', 'Ventes', 'Gérer les réglages des ventes à prix libre', 160),
    array('sales.card.quick_create', 'Cartes voyage', 'Créer une carte depuis le formulaire de vente', 170),
    array('cashdesk.closure.review', 'Caisse', 'Examiner et valider les écarts d’arrêt', 180),
);

$settings = array(
    'sales.free_price_enabled' => '1',
    'sales.discount_threshold_percent' => '20',
    'sales.discount_requires_approval' => '1',
    'sales.misc_requires_approval' => '1',
    'sales.valid_card_zero_fare_enabled' => '1',
    'sales.card_expiry_required' => '1',
    'sales.post_print_edit_enabled' => '0',
    'sales.card_daily_zero_fare_limit' => '0',
    'cashdesk.closure_difference_tolerance' => '0',
    'cashdesk.closure_large_difference_threshold' => '5000',
    'cashdesk.closure_reason_required' => '1',
);

if (!$apply) {
    echo "APERÇU : " . count($statements) . " tables additives, "
        . count($permissions) . " permissions et " . count($settings)
        . " réglages seront créés.\n";
    echo "Aucune donnée de vente existante ne sera modifiée. Relancez avec --apply.\n";
    exit(0);
}

foreach ($statements as $sql) {
    if (!$db->query($sql)) {
        fwrite(STDERR, "Migration échouée : {$db->error}\n");
        exit(1);
    }
}

$permissionStmt = $db->prepare(
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
    $permissionStmt->bind_param('sssi', $permission[0], $permission[1], $permission[2], $permission[3]);
    if (!$permissionStmt->execute()) {
        fwrite(STDERR, "Insertion permission échouée : {$permissionStmt->error}\n");
        exit(1);
    }
}

$settingStmt = $db->prepare(
    "INSERT INTO app_settings
        (company_ekey, setting_key, setting_value, updated_by, updated_at)
     VALUES (?, ?, ?, NULL, ?)
     ON DUPLICATE KEY UPDATE setting_key = VALUES(setting_key)"
);
$companyRows = $db->query("SELECT ekey FROM entreprise")->fetch_all(MYSQLI_ASSOC);
$now = date('Y-m-d H:i:s');
foreach ($companyRows as $companyRow) {
    $companyEkey = (int) $companyRow['ekey'];
    foreach ($settings as $key => $value) {
        $settingStmt->bind_param('isss', $companyEkey, $key, $value, $now);
        if (!$settingStmt->execute()) {
            fwrite(STDERR, "Insertion réglage échouée : {$settingStmt->error}\n");
            exit(1);
        }
    }
}

echo "OK : contrôles de prix installés sans modification des ventes existantes.\n";
