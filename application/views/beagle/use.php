<!DOCTYPE html>
<html lang="fr">

<? $this->load->view('_layouts/head', array(
    'title' => isset($title) ? $title : '',
    'bundle_datatables' => !empty($bundle_datatables),
)); ?>

<body class="be-animate"<?php if (!empty($app_retour_url)): ?> data-app-retour-url="<?= htmlspecialchars($app_retour_url, ENT_QUOTES, 'UTF-8'); ?>"<?php endif; ?>>

	<div class="be-wrapper be-collapsible-sidebar be-collapsible-sidebar-hide-logo be-collapsible-sidebar-collapsed<?= !empty($layout_minimal) ? ' be-minimal-chrome' : ''; ?>">
	    
	    <? $this->load->view('_layouts/navbar'); ?>
	    
	    <? if (empty($layout_minimal)) : ?>
	    <? $this->load->view('_layouts/lsidebar'); ?>
	    <? endif; ?>

	    <div class="be-content">

	        <div class="main-content container-fluid">
	            
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
</body>

</html>