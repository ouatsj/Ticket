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
                return array('ok' => false, 'error' => 'La carte de voyage est inconnue, inactive ou expirée.');
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
