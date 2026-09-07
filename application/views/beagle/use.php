<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="fr">

<?php
$head_extra = array(
    'title' => isset($title) ? $title : '',
    'bundle_datatables' => !empty($bundle_datatables),
    'no_cache' => !empty($layout_guichet_banner),
    'viewport_touch' => !empty($layout_minimal),
);
$this->load->view('_layouts/head', $head_extra);
?>

<body class="be-animate"<?php if (!empty($layout_guichet_banner) && $this->session->userdata('agent')): ?> data-agent-id="<?= (int) $this->session->agent->cpuser_id; ?>" data-whoami-url="<?= htmlspecialchars(site_url('login/whoami'), ENT_QUOTES, 'UTF-8'); ?>"<?php endif; ?>>
<style>
@media print {
	.auth-guichet-banner,
	.be-top-header,
	.be-left-sidebar,
	.be-navbar-header,
	.navbar { display: none !important; }
}
<?php if (!empty($layout_minimal)): ?>
.be-minimal-chrome .be-content { margin-left: 0 !important; }
.be-minimal-chrome .be-content .main-content.container-fluid {
	padding-left: 0.5rem !important;
	padding-right: 0.5rem !important;
}
/* Bandeau session compact (TPE / layout minimal) */
.auth-guichet-banner--compact {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 0.5rem;
	padding: 0.28rem 0.55rem !important;
	font-size: 0.78rem;
	line-height: 1.2;
}
.auth-guichet-banner--compact .agb-id {
	min-width: 0;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}
.auth-guichet-banner--compact .agb-logout {
	flex: 0 0 auto;
	padding: 0.2rem 0.55rem;
	font-size: 0.72rem;
	line-height: 1.2;
	margin: 0 !important;
}
@media (max-width: 575.98px) {
	.be-minimal-chrome .be-top-header {
		min-height: 42px !important;
	}
	.be-minimal-chrome .be-top-header .page-title {
		max-width: 34vw;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
		font-size: 0.7rem;
	}
	.be-minimal-chrome .be-top-header .user-name {
		max-width: 4.5rem;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
		display: inline-block;
		vertical-align: middle;
		font-size: 0.72rem;
	}
	.be-minimal-chrome .be-top-header .be-user-nav .fas.fa-user {
		font-size: 1rem !important;
	}
	.be-minimal-chrome .be-content .main-content.container-fluid {
		padding-left: 0.3rem !important;
		padding-right: 0.3rem !important;
		padding-top: 0.35rem !important;
	}
}
<?php endif; ?>
</style>

	<?php
	$__layout_minimal = !empty($layout_minimal);
	$__wrapper_class = $__layout_minimal
	    ? 'be-wrapper be-nosidebar-left be-minimal-chrome'
	    : 'be-wrapper be-collapsible-sidebar be-collapsible-sidebar-hide-logo be-collapsible-sidebar-collapsed';
	?>
	<div class="<?= $__wrapper_class; ?>">
	    
	    <? $this->load->view('_layouts/navbar'); ?>

	    <?php if (!empty($layout_guichet_banner) && $this->session->userdata('agent')) :
	        $identity = function_exists('auth_session_identity_context') ? auth_session_identity_context() : null;
	        $banner_name = htmlspecialchars($identity ? $identity['username'] : $this->session->agent->username, ENT_QUOTES, 'UTF-8');
	        $banner_role = ($identity && $identity['type_rols'] !== '')
	            ? htmlspecialchars($identity['type_rols'], ENT_QUOTES, 'UTF-8')
	            : '';
	        $lout_url = site_url('Login/lout/' . $this->session->session_id . '/' . $this->session->agent->cpuser_id);
	        /* Rôle 17 / TPE : bandeau compact (sinon trop d'espace vertical). */
	        if (!empty($layout_minimal)) :
	    ?>
	    <div class="auth-guichet-banner auth-guichet-banner--compact alert alert-warning mb-0 rounded-0" role="status">
	        <span class="agb-id">
	            <strong><?= $banner_name; ?></strong><?php if ($banner_role !== ''): ?>
	            <span class="text-muted">(<?= $banner_role; ?>)</span><?php endif; ?>
	        </span>
	        <a class="btn btn-sm btn-danger agb-logout" href="<?= $lout_url; ?>">Déconnexion</a>
	    </div>
	    <?php else: ?>
	    <div class="auth-guichet-banner alert alert-warning mb-0 rounded-0 text-center py-2" role="status">
	        <strong>Connecté en tant que :</strong>
	        <?= $banner_name; ?>
	        <?php if ($banner_role !== '') : ?>
	        <span class="text-muted">(<?= $banner_role; ?>)</span>
	        <?php endif; ?>
	        <?php if ($identity && $identity['garenom'] !== '') : ?>
	        <span class="text-muted">— gare <?= htmlspecialchars($identity['garenom'], ENT_QUOTES, 'UTF-8'); ?></span>
	        <?php endif; ?>
	        — Ce poste est personnel : déconnectez-vous avant de le quitter.
	        <a class="btn btn-sm btn-danger ml-2" href="<?= $lout_url; ?>">
	            Déconnexion
	        </a>
	    </div>
	    <?php endif; ?>
	    <?php endif; ?>
	    
	    <? if (!$__layout_minimal) : ?>
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
	<?php
	$__app_root_path = parse_url(site_url(''), PHP_URL_PATH);
	$__app_root_path = $__app_root_path ? rtrim($__app_root_path, '/') : '';
	?>
	<script type="text/javascript">var APP_ROOT = <?= json_encode($__app_root_path); ?>;</script>
	<?php $this->load->view('_layouts/' . (isset($scripts_layout) ? $scripts_layout : 'scripts_bundle'), array(
		'bundle_js' => isset($bundle_js) ? $bundle_js : array(),
		'bundle_optional_js' => isset($bundle_optional_js) ? $bundle_optional_js : array(),
		'bundle_datatables' => !empty($bundle_datatables),
	)); ?>
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
