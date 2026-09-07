<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Documentation métier + fiches QCM fin de formation (Paramètres).
 */
class Documentation extends MY_Controller
{
    public $property = array(
        'title' => 'Documentation',
        'UPDATE_SUCCESS' => FALSE,
        'INSERT_SUCCESS' => FALSE,
    );

    public $company;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('documentation_formation');
        setlocale(LC_TIME, 'fr_FR', 'fra');
        $this->property['pagetitle'] = utf8_encode(strftime('%d %b %G', now()));
    }

    protected function _require_staff_access()
    {
        if (!$this->session->userdata('agent') || !$this->session->userdata('company')) {
            redirect('login/ins');
            exit;
        }

        $role = (string) $this->session->agent->userole;
        // Admin / superviseur : accès complet. Autres rôles : lecture autorisée.
        if ($role === '') {
            redirect('login/ins');
            exit;
        }
    }

    protected function _can_print_corrige()
    {
        $role = (string) $this->session->agent->userole;

        return in_array($role, array('1', '2'), true);
    }

    protected function _require_qcm_editor()
    {
        $this->_require_staff_access();
        if (!$this->_can_print_corrige()) {
            show_error('Modification des QCM réservée aux administrateurs / superviseurs.', 403);
            return false;
        }
        return true;
    }

    public function index($ckey)
    {
        $this->_require_staff_access();
        $this->company = $this->m_entreprises->get_key($ckey);
        $this->property['roles_doc'] = documentation_formation_roles();
        $this->property['doc_generale'] = documentation_generale_cas_utilisation();
        $this->property['active_tab'] = 'generale';
        $this->property['can_corrige'] = $this->_can_print_corrige();
        $this->property['can_edit_qcm'] = $this->_can_print_corrige();
        $this->property['pagetitle'] .= ' • Documentation & formation • <strong>'
            . $this->company->nom_entreprise . '</strong>';

        return $this->layout->view('_documentation/index', $this->property);
    }

    /**
     * Onglet / page Documentation générale (cas d'utilisation pour décideurs).
     */
    public function generale($ckey)
    {
        $this->_require_staff_access();
        $this->company = $this->m_entreprises->get_key($ckey);
        $this->property['roles_doc'] = documentation_formation_roles();
        $this->property['doc_generale'] = documentation_generale_cas_utilisation();
        $this->property['active_tab'] = 'generale';
        $this->property['can_corrige'] = $this->_can_print_corrige();
        $this->property['can_edit_qcm'] = $this->_can_print_corrige();
        $this->property['pagetitle'] .= ' • Documentation générale • <strong>'
            . $this->company->nom_entreprise . '</strong>';

        return $this->layout->view('_documentation/index', $this->property);
    }

    /**
     * Raccourci vers l'onglet Formation par rôle.
     */
    public function roles($ckey)
    {
        $this->_require_staff_access();
        $this->company = $this->m_entreprises->get_key($ckey);
        $this->property['roles_doc'] = documentation_formation_roles();
        $this->property['doc_generale'] = documentation_generale_cas_utilisation();
        $this->property['active_tab'] = 'roles';
        $this->property['can_corrige'] = $this->_can_print_corrige();
        $this->property['can_edit_qcm'] = $this->_can_print_corrige();
        $this->property['pagetitle'] .= ' • Formation par rôle • <strong>'
            . $this->company->nom_entreprise . '</strong>';

        return $this->layout->view('_documentation/index', $this->property);
    }

    public function manuel($ckey, $role_code = 'general')
    {
        $this->_require_staff_access();
        $this->company = $this->m_entreprises->get_key($ckey);
        $meta = documentation_formation_role_meta($role_code);
        $manuel = documentation_formation_manuel($role_code);
        $fiche_poste = documentation_formation_fiche_poste_simple($role_code);
        if (!$meta || !$manuel || !$fiche_poste) {
            show_404();
            return;
        }

        $this->property['role_meta'] = $meta;
        $this->property['manuel'] = $manuel;
        $this->property['fiche_poste'] = $fiche_poste;
        $this->property['role_code'] = $role_code;
        $this->property['pagetitle'] .= ' • Fiche de poste & manuel • <strong>' . $meta['titre'] . '</strong>';

        return $this->layout->view('_documentation/manuel', $this->property);
    }

    public function qcm($ckey, $role_code = '4')
    {
        $this->_require_staff_access();
        $this->company = $this->m_entreprises->get_key($ckey);
        $meta = documentation_formation_role_meta($role_code);
        $qcm = documentation_formation_qcm($role_code);
        if (!$meta || !$qcm) {
            show_404();
            return;
        }

        $this->property['role_meta'] = $meta;
        $this->property['qcm'] = $qcm;
        $this->property['role_code'] = $role_code;
        $this->property['show_answers'] = FALSE;
        $this->property['can_corrige'] = $this->_can_print_corrige();
        $this->property['can_edit_qcm'] = $this->_can_print_corrige();
        $this->property['pagetitle'] .= ' • QCM • <strong>' . $meta['titre'] . '</strong>';

        return $this->layout->view('_documentation/qcm', $this->property);
    }

    public function qcm_corrige($ckey, $role_code = '4')
    {
        $this->_require_staff_access();
        if (!$this->_can_print_corrige()) {
            show_error('Accès réservé aux administrateurs / superviseurs.', 403);
            return;
        }

        $this->company = $this->m_entreprises->get_key($ckey);
        $meta = documentation_formation_role_meta($role_code);
        $qcm = documentation_formation_qcm($role_code);
        if (!$meta || !$qcm) {
            show_404();
            return;
        }

        $this->property['role_meta'] = $meta;
        $this->property['qcm'] = $qcm;
        $this->property['role_code'] = $role_code;
        $this->property['show_answers'] = TRUE;
        $this->property['can_corrige'] = TRUE;
        $this->property['can_edit_qcm'] = TRUE;
        $this->property['pagetitle'] .= ' • Corrigé QCM • <strong>' . $meta['titre'] . '</strong>';

        return $this->layout->view('_documentation/qcm', $this->property);
    }

    /**
     * Édition des questions QCM (admin / superviseur).
     */
    public function qcm_edit($ckey, $role_code = '4')
    {
        if (!$this->_require_qcm_editor()) {
            return;
        }
        $this->company = $this->m_entreprises->get_key($ckey);
        $meta = documentation_formation_role_meta($role_code);
        $qcm = documentation_formation_qcm($role_code);
        if (!$meta || !$qcm) {
            show_404();
            return;
        }

        $this->property['role_meta'] = $meta;
        $this->property['qcm'] = $qcm;
        $this->property['role_code'] = $role_code;
        $this->property['can_edit_qcm'] = TRUE;
        $this->property['flash_ok'] = $this->session->flashdata('qcm_ok');
        $this->property['flash_err'] = $this->session->flashdata('qcm_err');
        $this->property['pagetitle'] .= ' • Modifier QCM • <strong>' . $meta['titre'] . '</strong>';

        return $this->layout->view('_documentation/qcm_edit', $this->property);
    }

    public function qcm_save($ckey, $role_code = '4')
    {
        if (!$this->_require_qcm_editor()) {
            return;
        }
        $this->company = $this->m_entreprises->get_key($ckey);
        if (!$this->input->post()) {
            redirect('documentation/' . $ckey . '/qcm_edit/' . rawurlencode($role_code));
            return;
        }

        $questions = array();
        $qs = $this->input->post('q');
        $answers = $this->input->post('answer');
        $tips = $this->input->post('tip');
        $choice_a = $this->input->post('choice_a');
        $choice_b = $this->input->post('choice_b');
        $choice_c = $this->input->post('choice_c');
        $choice_d = $this->input->post('choice_d');

        if (!is_array($qs)) {
            $qs = array();
        }
        foreach ($qs as $i => $enonce) {
            $enonce = trim((string) $enonce);
            if ($enonce === '') {
                continue;
            }
            $questions[] = array(
                'q' => $enonce,
                'choices' => array(
                    'A' => isset($choice_a[$i]) ? trim((string) $choice_a[$i]) : '',
                    'B' => isset($choice_b[$i]) ? trim((string) $choice_b[$i]) : '',
                    'C' => isset($choice_c[$i]) ? trim((string) $choice_c[$i]) : '',
                    'D' => isset($choice_d[$i]) ? trim((string) $choice_d[$i]) : '',
                ),
                'answer' => isset($answers[$i]) ? (string) $answers[$i] : 'A',
                'tip' => isset($tips[$i]) ? trim((string) $tips[$i]) : '',
            );
        }

        $raw = array(
            'titre' => trim((string) $this->input->post('titre')),
            'duree' => trim((string) $this->input->post('duree')),
            'bareme' => trim((string) $this->input->post('bareme')),
            'questions' => $questions,
        );

        $out = documentation_formation_qcm_save_override($role_code, $raw);
        if (empty($out['ok'])) {
            $this->session->set_flashdata('qcm_err', 'Enregistrement impossible (' . (isset($out['error']) ? $out['error'] : 'erreur') . ').');
        } else {
            $this->session->set_flashdata('qcm_ok', 'QCM enregistré — les questions modifiées sont actives immédiatement.');
        }
        redirect('documentation/' . $ckey . '/qcm_edit/' . rawurlencode($role_code));
    }

    public function qcm_export($ckey, $role_code = '4')
    {
        if (!$this->_require_qcm_editor()) {
            return;
        }
        $this->company = $this->m_entreprises->get_key($ckey);
        $payload = documentation_formation_qcm_export_payload($role_code);
        if (!$payload) {
            show_404();
            return;
        }

        $filename = 'qcm_role_' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $role_code) . '_' . date('Ymd_His') . '.json';
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_header('Content-Disposition: attachment; filename="' . $filename . '"')
            ->set_output($json);
    }

    public function qcm_import($ckey, $role_code = '4')
    {
        if (!$this->_require_qcm_editor()) {
            return;
        }
        $this->company = $this->m_entreprises->get_key($ckey);

        if (empty($_FILES['qcm_file']['tmp_name']) || !is_uploaded_file($_FILES['qcm_file']['tmp_name'])) {
            $this->session->set_flashdata('qcm_err', 'Fichier JSON manquant.');
            redirect('documentation/' . $ckey . '/qcm_edit/' . rawurlencode($role_code));
            return;
        }

        $json = @file_get_contents($_FILES['qcm_file']['tmp_name']);
        $decoded = json_decode((string) $json, true);
        if (!is_array($decoded)) {
            $this->session->set_flashdata('qcm_err', 'JSON invalide.');
            redirect('documentation/' . $ckey . '/qcm_edit/' . rawurlencode($role_code));
            return;
        }

        // Accepte soit le payload exporté, soit un QCM nu.
        if (isset($decoded['questions'])) {
            $raw = $decoded;
        } else {
            $this->session->set_flashdata('qcm_err', 'Structure QCM absente (clé questions).');
            redirect('documentation/' . $ckey . '/qcm_edit/' . rawurlencode($role_code));
            return;
        }

        $out = documentation_formation_qcm_save_override($role_code, $raw);
        if (empty($out['ok'])) {
            $this->session->set_flashdata('qcm_err', 'Import impossible (' . (isset($out['error']) ? $out['error'] : 'erreur') . ').');
        } else {
            $this->session->set_flashdata('qcm_ok', 'Import réussi — QCM mis à jour.');
        }
        redirect('documentation/' . $ckey . '/qcm_edit/' . rawurlencode($role_code));
    }

    public function qcm_reset($ckey, $role_code = '4')
    {
        if (!$this->_require_qcm_editor()) {
            return;
        }
        $this->company = $this->m_entreprises->get_key($ckey);
        documentation_formation_qcm_delete_override($role_code);
        $this->session->set_flashdata('qcm_ok', 'Version personnalisée supprimée — retour au QCM standard.');
        redirect('documentation/' . $ckey . '/qcm_edit/' . rawurlencode($role_code));
    }
}
