<!DOCTYPE html>
<html lang="fr">

<? $this->load->view('_layouts/head'); ?>

<body class="be-animate">

	<div class="be-wrapper be-collapsible-sidebar be-collapsible-sidebar-hide-logo be-collapsible-sidebar-collapsed">
	    
	    <? $this->load->view('_layouts/navbar'); ?>
	    
	    <? $this->load->view('_layouts/lsidebar'); ?>

	    <div class="be-content">

	        <div class="main-content container-fluid">
	            
	            <?= $cfl; ?>
	            
	        </div>

	    </div>

	</div>

	<!-- BEGIN BASE JS -->
	<? $this->load->view('_layouts/scripts'); ?>
	<script type="text/javascript">var APP_ROOT = '';</script>
</body>

</html>