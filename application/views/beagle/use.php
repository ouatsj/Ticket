<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="fr">

<?php
$head_extra = array(
    'title' => isset($title) ? $title : '',
    'bundle_datatables' => !empty($bundle_datatables),
    'no_cache' => !empty($layout_guichet_banner),
);
$this->load->view('_layouts/head', $head_extra);
?>

<body class="be-animate"<?php if (!empty($app_retour_url)): ?> data-app-retour-url="<?= htmlspecialchars($app_retour_url, ENT_QUOTES, 'UTF-8'); ?>"<?php endif; ?><?php if (!empty($layout_guichet_banner) && $this->session->userdata('agent')): ?> data-agent-id="<?= (int) $this->session->agent->cpuser_id; ?>" data-whoami-url="<?= htmlspecialchars(site_url('login/whoami'), ENT_QUOTES, 'UTF-8'); ?>"<?php endif; ?>>

	<div class="be-wrapper be-collapsible-sidebar be-collapsible-sidebar-hide-logo be-collapsible-sidebar-collapsed<?= !empty($layout_minimal) ? ' be-minimal-chrome' : ''; ?>">
	    
	    <? $this->load->view('_layouts/navbar'); ?>

	    <?php if (!empty($layout_guichet_banner) && $this->session->userdata('agent')) :
	        $identity = function_exists('auth_session_identity_context') ? auth_session_identity_context() : null;
	    ?>
	    <div class="auth-guichet-banner alert alert-warning mb-0 rounded-0 text-center py-2" role="status">
	        <strong>Connecté en tant que :</strong>
	        <?= htmlspecialchars($identity ? $identity['username'] : $this->session->agent->username, ENT_QUOTES, 'UTF-8'); ?>
	        <?php if ($identity && $identity['type_rols'] !== '') : ?>
	        <span class="text-muted">(<?= htmlspecialchars($identity['type_rols'], ENT_QUOTES, 'UTF-8'); ?>)</span>
	        <?php endif; ?>
	        <?php if ($identity && $identity['garenom'] !== '') : ?>
	        <span class="text-muted">— gare <?= htmlspecialchars($identity['garenom'], ENT_QUOTES, 'UTF-8'); ?></span>
	        <?php endif; ?>
	        — Ce poste est personnel : déconnectez-vous avant de le quitter.
	        <a class="btn btn-sm btn-danger ml-2"
	           href="<?= site_url('Login/lout/' . $this->session->session_id . '/' . $this->session->agent->cpuser_id); ?>">
	            Déconnexion
	        </a>
	    </div>
	    <?php endif; ?>
	    
	    <? if (empty($layout_minimal)) : ?>
	    <? $this->load->view('_layouts/lsidebar'); ?>
	    <? endif; ?>

	    <div class="be-content">

	        <div class="main-content container-fluid">
	            <?php
	            $roleattribut_guard_notice = $this->session->flashdata('roleattribut_guard_notice');
	            if (!empty($roleattribut_guard_notice)) :
	            ?>
	            <div class="alert alert-warning alert-dismissible" role="alert">
	                <?= htmlspecialchars((string) $roleattribut_guard_notice, ENT_QUOTES, 'UTF-8'); ?>
	                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
	                    <span aria-hidden="true">&times;</span>
	                </button>
	            </div>
	            <?php endif; ?>
	            
	            <?= $cfl; ?>
	            
	        </div>

	    </div>

	</div>

	<!-- BEGIN BASE JS -->
	<?php $this->load->view('_layouts/' . (isset($scripts_layout) ? $scripts_layout : 'scripts_bundle'), array(
		'bundle_js' => isset($bundle_js) ? $bundle_js : array(),
		'bundle_datatables' => !empty($bundle_datatables),
	)); ?>
	<script type="text/javascript">var APP_ROOT = '';</script>
	<?php if (!empty($layout_guichet_banner)) : ?>
	<script type="text/javascript">
	(function () {
	    var body = document.body;
	    var expectedId = body.getAttribute('data-agent-id');
	    var whoamiUrl = body.getAttribute('data-whoami-url');
	    if (!expectedId || !whoamiUrl) {
	        return;
	    }

	    function checkSessionAgent() {
	        fetch(whoamiUrl, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
	            .then(function (r) { return r.ok ? r.json() : null; })
	            .then(function (data) {
	                if (!data || String(data.cpuser_id) !== String(expectedId)) {
	                    window.location.reload();
	                }
	            })
	            .catch(function () {});
	    }

	    document.addEventListener('visibilitychange', function () {
	        if (document.visibilityState === 'visible') {
	            checkSessionAgent();
	        }
	    });
	    window.addEventListener('pageshow', function (ev) {
	        if (ev.persisted) {
	            checkSessionAgent();
	        }
	    });
	})();
	</script>
	<?php endif; ?>
</body>

</html>
