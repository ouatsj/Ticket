<?php defined('BASEPATH') OR exit('No direct script access allowed');
if (trim((string) $this->input->get('escale')) !== '') {
    return;
}
?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var noeuds = document.querySelectorAll('button, a.btn, a.btn-secondary');
    noeuds.forEach(function (el) {
        var texte = (el.textContent || '').toUpperCase();
        if (texte.indexOf('ESCAL') !== -1) {
            el.style.display = 'none';
        }
    });
});
</script>
