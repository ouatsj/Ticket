<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$bundle_js = isset($bundle_js) && is_array($bundle_js) ? $bundle_js : array();
$bundle_datatables = !empty($bundle_datatables);
?>
<script src="<?= base_url('assets/lib/jquery/jquery.min.js'); ?>" type="text/javascript"></script>
<script src="<?= base_url('assets/lib/perfect-scrollbar/js/perfect-scrollbar.min.js'); ?>" type="text/javascript"></script>
<script src="<?= base_url('assets/lib/bootstrap/dist/js/bootstrap.bundle.min.js'); ?>" type="text/javascript"></script>
<script src="<?= base_url('assets/js/app.js'); ?>" type="text/javascript"></script>
<script src="<?= base_url('assets/lib/datetimepicker/js/bootstrap-datetimepicker.min.js'); ?>" type="text/javascript"></script>
<script src="<?= base_url('assets/lib/jquery.niftymodals/js/jquery.niftymodals.js'); ?>" type="text/javascript"></script>
<script type="application/javascript" src="<?= base_url('assets/lib/fa/js/all.min.js'); ?>"></script>
<script type="application/javascript" src="<?= base_url('assets/lib/sweetalert2/sweetalert2.min.js'); ?>"></script>
<script type="application/javascript" src="<?= base_url('assets/lib/mprogress/js/mprogress.min.js'); ?>"></script>
<?php if ($bundle_datatables): ?>
<script src="<?= base_url('assets/lib/datatables/datatables.net/js/jquery.dataTables.js'); ?>" type="text/javascript" defer></script>
<script src="<?= base_url('assets/lib/datatables/datatables.net-bs4/js/dataTables.bootstrap4.js'); ?>" type="text/javascript" defer></script>
<script src="<?= base_url('assets/lib/datatables/datatables.net-buttons/js/dataTables.buttons.min.js'); ?>" type="text/javascript" defer></script>
<script src="<?= base_url('assets/lib/datatables/datatables.net-buttons/js/buttons.colVis.min.js'); ?>" type="text/javascript" defer></script>
<script src="<?= base_url('assets/lib/datatables/datatables.net-buttons/js/buttons.print.min.js'); ?>" type="text/javascript" defer></script>
<?php endif; ?>

<script type="application/javascript" src="<?= base_url('assets/js/ligne_option.js'); ?>"></script>
<script type="application/javascript" src="<?= base_url('assets/js/retour.js'); ?>"></script>
<script type="application/javascript" src="<?= base_url('assets/js/request-guard.js'); ?>"></script>
<?php foreach ($bundle_js as $js): ?>
<?php
$js_relative_path = 'assets/js/' . $js;
$js_file_path = FCPATH . $js_relative_path;
$js_version = is_file($js_file_path) ? (string) filemtime($js_file_path) : '';
$js_url = base_url($js_relative_path) . ($js_version !== '' ? '?v=' . rawurlencode($js_version) : '');
?>
<script type="application/javascript" src="<?= htmlspecialchars($js_url, ENT_QUOTES, 'UTF-8'); ?>"></script>
<?php endforeach; ?>

<script type="text/javascript">
    $.fn.niftyModal('setDefaults', {
        overlaySelector: '.modal-overlay',
        contentSelector: '.modal-content',
        closeSelector: '.modal-close',
        classAddAfterOpen: 'modal-show'
    });

    $(document).ready(function () {
        App.init();
        App.uiSweetalert2();
        if (<?= $bundle_datatables ? 'true' : 'false'; ?> && $.fn.dataTable && typeof App.dataTables === 'function') {
            App.dataTables();
        }
        if (typeof PerfectScrollbar !== 'undefined') {
            new PerfectScrollbar('.be-content', {
                wheelSpeed: 2,
                wheelPropagation: true,
                minScrollbarLength: 20
            });
        }

        // Message d'échec vente (redirect silencieux addpassager)
        (function () {
            var msg = null;
            try {
                var params = new URLSearchParams(window.location.search);
                msg = params.get('sale_error');
                if (msg) {
                    params.delete('sale_error');
                    var clean = window.location.pathname + (params.toString() ? ('?' + params.toString()) : '') + window.location.hash;
                    if (window.history && window.history.replaceState) {
                        window.history.replaceState({}, document.title, clean);
                    }
                }
            } catch (e) {}
            <?php
            $sale_error_flash = '';
            if (isset($this) && isset($this->session) && method_exists($this->session, 'flashdata')) {
                $sale_error_flash = (string) $this->session->flashdata('sale_error');
            }
            if ($sale_error_flash !== ''):
            ?>
            if (!msg) {
                msg = <?= json_encode($sale_error_flash, JSON_UNESCAPED_UNICODE); ?>;
            }
            <?php endif; ?>
            if (msg && typeof swal === 'function') {
                swal({
                    type: 'error',
                    title: 'Vente non enregistrée',
                    text: msg,
                    confirmButtonText: 'OK',
                    showCloseButton: true
                });
            } else if (msg && typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                Swal.fire({
                    type: 'error',
                    title: 'Vente non enregistrée',
                    text: msg,
                    confirmButtonText: 'OK'
                });
            } else if (msg) {
                alert(msg);
            }
        })();
    });
</script>
