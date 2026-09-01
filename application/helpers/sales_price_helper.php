<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('sales_price_controls_enabled')) {
    function sales_price_controls_enabled()
    {
        $CI =& get_instance();
        return (bool) $CI->config->item('sales_price_controls_enabled')
            && $CI->db->table_exists('ticket_pricing_snapshot')
            && $CI->db->table_exists('app_settings');
    }
}

/**
 * Mode antifraude : off | observe | enforce.
 * observe = écritures d'audit sans bloquer l'usage métier existant.
 */
if (!function_exists('fraud_controls_mode')) {
    function fraud_controls_mode()
    {
        $CI =& get_instance();
        $mode = strtolower(trim((string) $CI->config->item('fraud_controls_mode')));
        if (!in_array($mode, array('off', 'observe', 'enforce'), true)) {
            return 'off';
        }
        if ($mode !== 'off' && !sales_price_controls_enabled()) {
            return 'off';
        }
        return $mode;
    }
}

if (!function_exists('fraud_controls_enabled')) {
    function fraud_controls_enabled()
    {
        return fraud_controls_mode() !== 'off';
    }
}

if (!function_exists('fraud_controls_enforce')) {
    function fraud_controls_enforce()
    {
        return fraud_controls_mode() === 'enforce';
    }
}

if (!function_exists('fraud_control_actor_roleattribut')) {
    function fraud_control_actor_roleattribut()
    {
        $CI =& get_instance();
        if (isset($CI->session->agent) && !empty($CI->session->agent->roleattribut)) {
            return (int) $CI->session->agent->roleattribut;
        }
        return null;
    }
}

if (!function_exists('fraud_control_event_record')) {
    function fraud_control_event_record($eventCode, $severity, $entityType, $entityId, array $details = array())
    {
        if (!fraud_controls_enabled()) {
            return false;
        }

        $CI =& get_instance();
        if (!$CI->db->table_exists('fraud_control_events')) {
            return false;
        }

        $agent = isset($CI->session->agent) ? $CI->session->agent : null;
        $roleAttribut = fraud_control_actor_roleattribut();

        try {
            return (bool) $CI->db->insert('fraud_control_events', array(
                'company_ekey' => isset($CI->session->company->ekey)
                    ? (int) $CI->session->company->ekey
                    : 0,
                'event_code' => (string) $eventCode,
                'severity' => (string) $severity,
                'actor_cpuser_id' => $agent && isset($agent->cpuser_id)
                    ? (int) $agent->cpuser_id
                    : null,
                'roleattribut' => $roleAttribut !== null ? (int) $roleAttribut : null,
                'entity_type' => $entityType !== null ? (string) $entityType : null,
                'entity_id' => $entityId !== null ? (string) $entityId : null,
                'details_json' => $details ? json_encode($details) : null,
                'created_at' => date('Y-m-d H:i:s'),
            ));
        } catch (Exception $e) {
            log_message('error', 'fraud_control_event_record: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('sale_idempotency_hash')) {
    function sale_idempotency_hash($nonce)
    {
        return hash('sha256', (string) $nonce);
    }
}

if (!function_exists('sale_idempotency_is_done')) {
    function sale_idempotency_is_done($nonce)
    {
        if (!fraud_controls_enabled()) {
            return false;
        }
        $CI =& get_instance();
        if (!$CI->db->table_exists('sale_request_idempotency')
            || !isset($CI->session->company->ekey, $CI->session->agent->cpuser_id)
        ) {
            return false;
        }
        $nonce = trim((string) $nonce);
        if ($nonce === '') {
            return false;
        }
        $row = $CI->db
            ->select('request_status')
            ->from('sale_request_idempotency')
            ->where('company_ekey', (int) $CI->session->company->ekey)
            ->where('actor_cpuser_id', (int) $CI->session->agent->cpuser_id)
            ->where('nonce_hash', sale_idempotency_hash($nonce))
            ->limit(1)
            ->get()
            ->row();
        return $row && (string) $row->request_status === 'completed';
    }
}

if (!function_exists('sale_idempotency_begin')) {
    function sale_idempotency_begin($nonce)
    {
        if (!fraud_controls_enabled()) {
            return false;
        }
        $CI =& get_instance();
        if (!$CI->db->table_exists('sale_request_idempotency')
            || !isset($CI->session->company->ekey, $CI->session->agent->cpuser_id)
        ) {
            return false;
        }
        $nonce = trim((string) $nonce);
        if ($nonce === '') {
            return false;
        }
        // INSERT IGNORE : ne bloque jamais une vente en cours / retry.
        $sql = "INSERT IGNORE INTO sale_request_idempotency
            (company_ekey, actor_cpuser_id, nonce_hash, request_status, created_at)
            VALUES (?, ?, ?, 'pending', ?)";
        return (bool) $CI->db->query($sql, array(
            (int) $CI->session->company->ekey,
            (int) $CI->session->agent->cpuser_id,
            sale_idempotency_hash($nonce),
            date('Y-m-d H:i:s'),
        ));
    }
}

if (!function_exists('sale_idempotency_complete')) {
    function sale_idempotency_complete($nonce)
    {
        if (!fraud_controls_enabled()) {
            return false;
        }
        $CI =& get_instance();
        if (!$CI->db->table_exists('sale_request_idempotency')
            || !isset($CI->session->company->ekey, $CI->session->agent->cpuser_id)
        ) {
            return false;
        }
        $nonce = trim((string) $nonce);
        if ($nonce === '') {
            return false;
        }
        return (bool) $CI->db
            ->where('company_ekey', (int) $CI->session->company->ekey)
            ->where('actor_cpuser_id', (int) $CI->session->agent->cpuser_id)
            ->where('nonce_hash', sale_idempotency_hash($nonce))
            ->update('sale_request_idempotency', array(
                'request_status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
            ));
    }
}

if (!function_exists('sale_idempotency_release')) {
    function sale_idempotency_release($nonce)
    {
        if (!fraud_controls_enabled()) {
            return false;
        }
        $CI =& get_instance();
        if (!$CI->db->table_exists('sale_request_idempotency')
            || !isset($CI->session->company->ekey, $CI->session->agent->cpuser_id)
        ) {
            return false;
        }
        $nonce = trim((string) $nonce);
        if ($nonce === '') {
            return false;
        }
        return (bool) $CI->db
            ->where('company_ekey', (int) $CI->session->company->ekey)
            ->where('actor_cpuser_id', (int) $CI->session->agent->cpuser_id)
            ->where('nonce_hash', sale_idempotency_hash($nonce))
            ->where('request_status', 'pending')
            ->delete('sale_request_idempotency');
    }
}

if (!function_exists('sales_setting')) {
    function sales_setting($key, $default = null, $companyEkey = null)
    {
        if (!sales_price_controls_enabled()) {
            return $default;
        }

        $CI =& get_instance();
        if ($companyEkey === null && isset($CI->session->company->ekey)) {
            $companyEkey = $CI->session->company->ekey;
        }
        $row = $CI->db
            ->select('setting_value')
            ->from('app_settings')
            ->where('company_ekey', (int) $companyEkey)
            ->where('setting_key', (string) $key)
            ->limit(1)
            ->get()
            ->row();

        return $row ? $row->setting_value : $default;
    }
}

if (!function_exists('sales_setting_bool')) {
    function sales_setting_bool($key, $default = false, $companyEkey = null)
    {
        $value = sales_setting($key, $default ? '1' : '0', $companyEkey);
        return (string) $value === '1';
    }
}

if (!function_exists('sales_valid_travel_card')) {
    function sales_valid_travel_card($cardNumber)
    {
        $cardNumber = trim((string) $cardNumber);
        if ($cardNumber === '') {
            return null;
        }

        $CI =& get_instance();
        return $CI->db->query(
            "SELECT cv.id_carte, cv.num_carte, cv.idcarte_client,
                    cv.date_valide, cv.date_expire
             FROM carte_passager cv
             WHERE BINARY cv.num_carte = ?
             AND cv.actif_validite = 0
             AND cv.date_valide <= ?
             AND cv.date_expire >= ?
             LIMIT 1",
            array($cardNumber, date('Y-m-d'), date('Y-m-d'))
        )->row();
    }
}

if (!function_exists('sales_price_validate')) {
    function sales_price_validate($programmeCode, $rawPrice, array $options = array())
    {
        $raw = str_replace(array(' ', ','), array('', '.'), trim((string) $rawPrice));
        if ($raw === '' || !is_numeric($raw)) {
            return array('ok' => false, 'error' => 'Le montant de vente doit être numérique.');
        }

        $soldPrice = round((float) $raw, 2);
        if ($soldPrice < 0 || $soldPrice > 9999999999.99) {
            return array('ok' => false, 'error' => 'Le montant de vente est invalide.');
        }

        $normalPrice = round((float) ticket_prix_depuis_programme($programmeCode, null), 2);
        $isFreePrice = abs($soldPrice - $normalPrice) >= 0.01;
        $reason = trim((string) (isset($options['reason']) ? $options['reason'] : ''));
        $authorizationType = strtolower(trim((string) (
            isset($options['authorization_type']) ? $options['authorization_type'] : 'divers'
        )));
        if (!in_array($authorizationType, array('divers', 'carte_voyage'), TRUE)) {
            return array('ok' => false, 'error' => 'Le type d’autorisation est invalide.');
        }

        if ($isFreePrice && !sales_setting_bool('sales.free_price_enabled', false)) {
            return array('ok' => false, 'error' => 'Les ventes à prix libre sont désactivées.');
        }
        if ($isFreePrice && !super_admin_can('sales.price.free')) {
            return array('ok' => false, 'error' => 'Vous n’avez pas la permission de modifier le tarif normal.');
        }
        if ($isFreePrice && $reason === '') {
            return array('ok' => false, 'error' => 'Le motif est obligatoire lorsque le prix diffère du tarif normal.');
        }

        $card = null;
        if ($soldPrice === 0.0 && $authorizationType === 'carte_voyage') {
            if (!sales_setting_bool('sales.valid_card_zero_fare_enabled', false)
                || !super_admin_can('sales.card.zero_fare')
            ) {
                return array('ok' => false, 'error' => 'La vente gratuite par carte n’est pas autorisée.');
            }
            $card = sales_valid_travel_card(isset($options['card_number']) ? $options['card_number'] : '');
            if (!$card) {
                fraud_control_event_record(
                    'travel_card_rejected',
                    'warning',
                    'travel_card',
                    isset($options['card_number']) ? $options['card_number'] : '',
                    array('programme_code' => $programmeCode)
                );
                return array('ok' => false, 'error' => 'La carte de voyage est inconnue, inactive ou expirée.');
            }
            $dailyLimit = (int) sales_setting('sales.card_daily_zero_fare_limit', '0');
            $CI =& get_instance();
            if ($dailyLimit > 0
                && fraud_controls_enabled()
                && $CI->db->table_exists('travel_card_usage')
            ) {
                $dailyUsage = (int) $CI->db
                    ->where('company_ekey', isset($CI->session->company->ekey)
                        ? (int) $CI->session->company->ekey
                        : 0)
                    ->where('travel_card_id', (string) $card->id_carte)
                    ->where('usage_status', 'confirmed')
                    ->where('used_at >=', date('Y-m-d 00:00:00'))
                    ->where('used_at <=', date('Y-m-d 23:59:59'))
                    ->count_all_results('travel_card_usage');
                if ($dailyUsage >= $dailyLimit) {
                    fraud_control_event_record(
                        'travel_card_daily_limit',
                        'warning',
                        'travel_card',
                        $card->id_carte,
                        array('daily_limit' => $dailyLimit, 'programme_code' => $programmeCode)
                    );
                    if (fraud_controls_enforce()) {
                        return array('ok' => false, 'error' => 'La limite quotidienne de cette carte est atteinte.');
                    }
                }
            }
        } elseif ($soldPrice === 0.0 && empty($options['zero_confirmed'])) {
            return array('ok' => false, 'error' => 'La vente à 0 F doit être confirmée explicitement.');
        }

        $discountPercent = 0.0;
        if ($normalPrice > 0 && $soldPrice < $normalPrice) {
            $discountPercent = round((($normalPrice - $soldPrice) / $normalPrice) * 100, 2);
        }
        $threshold = (float) sales_setting('sales.discount_threshold_percent', '20');
        if ($discountPercent > $threshold
            && sales_setting_bool('sales.discount_requires_approval', true)
            && !super_admin_can('sales.discount.approve')
        ) {
            return array(
                'ok' => false,
                'error' => 'Cette réduction dépasse le seuil de validation responsable.'
            );
        }
        if ($isFreePrice
            && $authorizationType === 'divers'
            && sales_setting_bool('sales.misc_requires_approval', true)
            && !super_admin_can('sales.misc.approve')
        ) {
            return array('ok' => false, 'error' => 'La vente Divers doit être validée par un responsable.');
        }

        $nature = 'plein';
        if ($soldPrice === 0.0) {
            $nature = 'gratuit';
        } elseif ($soldPrice < $normalPrice) {
            $nature = 'reduit';
        } elseif ($isFreePrice) {
            $nature = 'prix_libre';
        }

        return array(
            'ok' => true,
            'sold_price' => $soldPrice,
            'normal_price' => $normalPrice,
            'is_free_price' => $isFreePrice,
            'nature' => $nature,
            'reason' => $reason,
            'authorization_type' => $authorizationType,
            'discount_percent' => $discountPercent,
            'travel_card_id' => $card ? (string) $card->id_carte : null,
        );
    }
}

if (!function_exists('sales_price_validate_or_fail')) {
    function sales_price_validate_or_fail($programmeCode, $rawPrice, array $options = array())
    {
        $result = sales_price_validate($programmeCode, $rawPrice, $options);
        if (empty($result['ok'])) {
            show_error($result['error'], 422, 'Vente refusée');
            exit;
        }
        return $result;
    }
}

if (!function_exists('sales_price_snapshot_record')) {
    function sales_price_snapshot_record(array $ticketData, array $pricing)
    {
        if (!sales_price_controls_enabled() || empty($pricing['ok'])) {
            return false;
        }

        $CI =& get_instance();
        $agent = isset($CI->session->agent) ? $CI->session->agent : null;
        if (!$agent || empty($ticketData['code_passager']) || empty($ticketData['code_ticket'])) {
            return false;
        }

        $row = array(
            'company_ekey' => (int) $CI->session->company->ekey,
            'code_passager' => (string) $ticketData['code_passager'],
            'code_ticket' => (string) $ticketData['code_ticket'],
            'segment_type' => isset($ticketData['segment_type']) ? $ticketData['segment_type'] : 'aller',
            'programme_code' => isset($ticketData['code_pro']) ? $ticketData['code_pro'] : null,
            'normal_price' => $pricing['normal_price'],
            'sold_price' => $pricing['sold_price'],
            'sale_nature' => $pricing['nature'],
            'authorization_type' => $pricing['authorization_type'],
            'reason' => $pricing['reason'] !== '' ? $pricing['reason'] : null,
            'seller_cpuser_id' => (int) $agent->cpuser_id,
            'approval_request_id' => null,
            'travel_card_id' => $pricing['travel_card_id'],
            'created_at' => date('Y-m-d H:i:s'),
        );

        $inserted = $CI->db->insert('ticket_pricing_snapshot', $row);
        if ($inserted && $CI->db->table_exists('ticket_print_events')) {
            $CI->db->insert('ticket_print_events', array(
                'company_ekey' => (int) $CI->session->company->ekey,
                'code_passager' => (string) $ticketData['code_passager'],
                'code_ticket' => (string) $ticketData['code_ticket'],
                'printed_by' => (int) $agent->cpuser_id,
                'printed_at' => date('Y-m-d H:i:s'),
                'print_type' => 'emission',
            ));
        }

        if ($inserted
            && fraud_controls_enabled()
            && $pricing['authorization_type'] === 'carte_voyage'
            && (float) $pricing['sold_price'] === 0.0
            && !empty($pricing['travel_card_id'])
            && $CI->db->table_exists('travel_card_usage')
        ) {
            $card = $CI->db
                ->select('num_carte')
                ->where('id_carte', (string) $pricing['travel_card_id'])
                ->limit(1)
                ->get('carte_passager')
                ->row();
            $roleAttribut = fraud_control_actor_roleattribut();
            $CI->db->insert('travel_card_usage', array(
                'company_ekey' => (int) $CI->session->company->ekey,
                'travel_card_id' => (string) $pricing['travel_card_id'],
                'card_number' => $card ? (string) $card->num_carte : '',
                'code_passager' => (string) $ticketData['code_passager'],
                'code_ticket' => (string) $ticketData['code_ticket'],
                'segment_type' => isset($ticketData['segment_type'])
                    ? $ticketData['segment_type']
                    : 'aller',
                'programme_code' => isset($ticketData['code_pro']) ? $ticketData['code_pro'] : null,
                'seller_cpuser_id' => (int) $agent->cpuser_id,
                'seller_roleattribut' => $roleAttribut !== null ? (int) $roleAttribut : null,
                'normal_price' => $pricing['normal_price'],
                'sold_price' => $pricing['sold_price'],
                'usage_status' => 'confirmed',
                'used_at' => date('Y-m-d H:i:s'),
            ));
            fraud_control_event_record(
                'travel_card_used',
                'info',
                'travel_card',
                $pricing['travel_card_id'],
                array(
                    'code_passager' => $ticketData['code_passager'],
                    'code_ticket' => $ticketData['code_ticket'],
                    'sold_price' => $pricing['sold_price'],
                )
            );
        } elseif ($inserted && !empty($pricing['is_free_price'])) {
            fraud_control_event_record(
                'free_price_sale',
                'info',
                'ticket',
                isset($ticketData['code_ticket']) ? $ticketData['code_ticket'] : null,
                array(
                    'code_passager' => $ticketData['code_passager'],
                    'normal_price' => $pricing['normal_price'],
                    'sold_price' => $pricing['sold_price'],
                    'nature' => $pricing['nature'],
                    'reason' => $pricing['reason'],
                )
            );
        }

        return $inserted;
    }
}

if (!function_exists('sales_closure_totals_prepare')) {
    function sales_closure_totals_prepare($companyEkey, $roleAttributionId)
    {
        static $totals = array();
        $CI =& get_instance();
        $key = (int) $companyEkey . ':' . (int) $roleAttributionId;
        $totals[$key] = array();

        if (!sales_price_controls_enabled()) {
            return;
        }

        $rows = $CI->db->query(
            "SELECT x.company_code, SUM(x.amount) AS total_amount
             FROM (
                SELECT c.cle_compagnie AS company_code, COALESCE(p.prixvente, 0) AS amount
                FROM passager p
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_dest gd ON lg.gadest_lg = gd.code_gadest
                JOIN compagnies c ON gd.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = ?
                AND p.idcptuser = ?
                AND p.statutvente = 0
                AND p.statut_code = 'vendu'
                UNION ALL
                SELECT c.cle_compagnie AS company_code, COALESCE(np.prixretour, 0) AS amount
                FROM non_passager np
                JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                JOIN gare_dest gd ON lg.gadest_lg = gd.code_gadest
                JOIN compagnies c ON gd.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = ?
                AND np.cptus = ?
                AND np.statvente = 0
             ) x
             GROUP BY x.company_code",
            array(
                (int) $companyEkey,
                (int) $roleAttributionId,
                (int) $companyEkey,
                (int) $roleAttributionId,
            )
        )->result();

        foreach ($rows as $row) {
            $totals[$key][(string) $row->company_code] = round((float) $row->total_amount, 2);
        }

        $GLOBALS['sales_closure_totals'] = $totals;
    }
}

if (!function_exists('sales_closure_total')) {
    function sales_closure_total($companyEkey, $roleAttributionId, $companyCode, $fallback)
    {
        if (!sales_price_controls_enabled() || empty($GLOBALS['sales_closure_totals'])) {
            return $fallback;
        }
        $key = (int) $companyEkey . ':' . (int) $roleAttributionId;
        $companyCode = (string) $companyCode;
        if (!isset($GLOBALS['sales_closure_totals'][$key])
            || !array_key_exists($companyCode, $GLOBALS['sales_closure_totals'][$key])
        ) {
            return 0.0;
        }
        return $GLOBALS['sales_closure_totals'][$key][$companyCode];
    }
}

/**
 * Prépare le contrôle d'écart à l'arrêt.
 * En mode observe : ne bloque jamais (retourne le contrôle + journal).
 * En mode enforce : exige un motif si l'écart dépasse la tolérance.
 *
 * @return array|false false uniquement en enforce si motif manquant
 */
if (!function_exists('sales_closure_control_prepare')) {
    function sales_closure_control_prepare($companyEkey, array $data)
    {
        if (!fraud_controls_enabled()) {
            return null;
        }

        $declared = isset($data['montcomtpte']) ? round((float) $data['montcomtpte'], 2) : 0.0;
        $expected = sales_closure_total(
            $companyEkey,
            isset($data['idusercompt']) ? $data['idusercompt'] : 0,
            isset($data['comp']) ? $data['comp'] : '',
            $declared
        );
        $expected = round((float) $expected, 2);
        $difference = round($declared - $expected, 2);
        $CI =& get_instance();
        $reason = trim((string) $CI->input->post('motif_ecart_arret'));
        $tolerance = max(0, (float) sales_setting(
            'cashdesk.closure_difference_tolerance',
            '0',
            $companyEkey
        ));
        $threshold = max(0, (float) sales_setting(
            'cashdesk.closure_large_difference_threshold',
            '5000',
            $companyEkey
        ));

        if (abs($difference) > $tolerance
            && sales_setting_bool('cashdesk.closure_reason_required', true, $companyEkey)
            && $reason === ''
        ) {
            fraud_control_event_record(
                'closure_difference_without_reason',
                'warning',
                'roleattribut',
                isset($data['idusercompt']) ? $data['idusercompt'] : null,
                array(
                    'declared' => $declared,
                    'expected' => $expected,
                    'difference' => $difference,
                )
            );
            if (fraud_controls_enforce()) {
                show_error(
                    'Un motif est obligatoire car le montant transmis diffère du montant recalculé.',
                    422,
                    'Arrêt refusé'
                );
                return false;
            }
        }

        return array(
            'declared_amount' => $declared,
            'expected_amount' => $expected,
            'recorded_amount' => $expected,
            'difference_amount' => $difference,
            'reason' => $reason !== '' ? $reason : null,
            'review_status' => abs($difference) > max($tolerance, $threshold)
                ? 'requires_review'
                : 'clear',
        );
    }
}

if (!function_exists('sales_closure_audit_record')) {
    function sales_closure_audit_record($companyEkey, $closureId, array $data, array $control)
    {
        if (!fraud_controls_enabled()) {
            return false;
        }

        $CI =& get_instance();
        if (!$CI->db->table_exists('cash_closure_audit')) {
            return false;
        }

        $agent = isset($CI->session->agent) ? $CI->session->agent : null;
        try {
            $inserted = $CI->db->insert('cash_closure_audit', array(
                'company_ekey' => (int) $companyEkey,
                'closure_type' => 'ticket',
                'legacy_closure_id' => (int) $closureId,
                'roleattribut' => isset($data['idusercompt']) ? (int) $data['idusercompt'] : 0,
                'company_code' => isset($data['comp']) ? (string) $data['comp'] : null,
                'gare_id' => isset($data['idgarecompt']) ? (string) $data['idgarecompt'] : null,
                'sousgare_id' => isset($data['idsousga']) ? (int) $data['idsousga'] : null,
                'declared_amount' => $control['declared_amount'],
                'expected_amount' => $control['expected_amount'],
                'recorded_amount' => $control['recorded_amount'],
                'difference_amount' => $control['difference_amount'],
                'reason' => $control['reason'],
                'review_status' => $control['review_status'],
                'created_by_cpuser_id' => $agent && isset($agent->cpuser_id)
                    ? (int) $agent->cpuser_id
                    : 0,
                'created_at' => date('Y-m-d H:i:s'),
            ));
        } catch (Exception $e) {
            log_message('error', 'sales_closure_audit_record: ' . $e->getMessage());
            return false;
        }

        if ($inserted && $control['review_status'] === 'requires_review') {
            fraud_control_event_record(
                'closure_large_difference',
                'critical',
                'cash_closure',
                $closureId,
                $control
            );
        } elseif ($inserted) {
            fraud_control_event_record(
                'cash_closure_recorded',
                'info',
                'cash_closure',
                $closureId,
                array(
                    'declared' => $control['declared_amount'],
                    'expected' => $control['expected_amount'],
                    'difference' => $control['difference_amount'],
                )
            );
        }

        return $inserted;
    }
}
