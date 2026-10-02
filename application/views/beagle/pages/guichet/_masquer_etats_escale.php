<?php defined('BASEPATH') OR exit('No direct script access allowed');
$escale_seule = !empty($vue_escale_seule)
    || trim((string) $this->input->get('escale')) !== ''
    || trim((string) $this->input->get('escale_nom')) !== '';
$escale_nom_vue = trim((string) $this->input->get('escale_nom'));
$escale_valeur_vue = trim((string) $this->input->get('escale'));
$escale_ops_vue = trim((string) $this->input->get('escale_ops'));
$profils_apercu = array(
    '2' => 'Superviseur',
    '7' => 'Comptable',
    '13' => 'Superviseur d\'agence',
    '14' => 'Superviseur de site',
);
$profil_apercu = trim((string) $this->input->get('escale_profil'));
$libelle_apercu = isset($profils_apercu[$profil_apercu]) ? $profils_apercu[$profil_apercu] : '';
?>
<?php if ($escale_seule): ?>
<script>
(function () {
    var champs = {
        escale: <?= json_encode($escale_valeur_vue); ?>,
        escale_nom: <?= json_encode($escale_nom_vue); ?>,
        escale_ops: <?= json_encode($escale_ops_vue); ?>
    };
    var ouvrir = XMLHttpRequest.prototype.open;
    XMLHttpRequest.prototype.open = function (method, url, async, user, password) {
        if (typeof url === 'string' && url.indexOf('/utilisateurs/trigares') !== -1) {
            Object.keys(champs).forEach(function (cle) {
                if (champs[cle] && url.indexOf(cle + '=') === -1) {
                    url += (url.indexOf('?') === -1 ? '?' : '&') + cle + '=' + encodeURIComponent(champs[cle]);
                }
            });
        }
        return ouvrir.call(this, method, url, async, user, password);
    };
})();
</script>
<?php endif; ?>
<?php if ($escale_seule): ?>
<p class="ml-3 mb-2">
    <?php if (!empty($retour_escale_agents)): ?>
        <a href="<?= htmlspecialchars($retour_escale_agents, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-space btn-secondary">
            <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR A L’ESCALE&nbsp;
        </a>
    <?php endif; ?>
    <strong>Escale<?= $escale_nom_vue !== '' ? ' : ' . htmlspecialchars($escale_nom_vue, ENT_QUOTES, 'UTF-8') : ''; ?></strong>
    <?php if ($libelle_apercu !== ''): ?>
        <span class="badge badge-primary">Aperçu <?= htmlspecialchars($libelle_apercu, ENT_QUOTES, 'UTF-8'); ?></span>
    <?php endif; ?>
</p>
<?php endif; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var escaleSeule = <?= $escale_seule ? 'true' : 'false'; ?>;
    var champs = {
        escale: <?= json_encode($escale_valeur_vue); ?>,
        escale_nom: <?= json_encode($escale_nom_vue); ?>,
        escale_ops: <?= json_encode($escale_ops_vue); ?>
    };
    var noeuds = document.querySelectorAll('button, a.btn, a.btn-secondary');
    noeuds.forEach(function (el) {
        if (el.closest('.modal-container, .modal, .modal-content, .modal-footer')) {
            return;
        }
        var texte = (el.textContent || '').toUpperCase();
        if (texte.indexOf('RETOUR') !== -1) {
            return;
        }
        var estEscal = texte.indexOf('ESCAL') !== -1;
        if (escaleSeule) {
            if (!estEscal) {
                el.style.display = 'none';
            }
        } else if (estEscal) {
            el.style.display = 'none';
        }
    });
    if (!escaleSeule) {
        return;
    }
    function estGareDepart(sel) {
        var nom = (sel.getAttribute('name') || '');
        return nom.indexOf('departgar') === 0 || nom.indexOf('nomgare') === 0 || nom.indexOf('deptgare') === 0;
    }
    function figerGareEscale(sel) {
        if (sel._escaleFix) {
            return;
        }
        var utiles = [];
        var i;
        for (i = 0; i < sel.options.length; i++) {
            if (sel.options[i].value) {
                utiles.push(sel.options[i]);
            }
        }
        if (utiles.length !== 1) {
            return;
        }
        sel._escaleFix = true;
        try {
            for (i = sel.options.length - 1; i >= 0; i--) {
                if (!sel.options[i].value) {
                    sel.remove(i);
                }
            }
            if (sel.value !== utiles[0].value) {
                sel.value = utiles[0].value;
                sel.dispatchEvent(new Event('change', { bubbles: true }));
            }
        } finally {
            sel._escaleFix = false;
        }
    }
    document.querySelectorAll('select').forEach(function (sel) {
        if (!estGareDepart(sel)) {
            return;
        }
        figerGareEscale(sel);
        var obs = new MutationObserver(function () {
            figerGareEscale(sel);
        });
        obs.observe(sel, { childList: true });
    });
    document.querySelectorAll('form').forEach(function (form) {
        Object.keys(champs).forEach(function (nom) {
            if (!champs[nom] || form.querySelector('input[name="' + nom + '"]')) {
                return;
            }
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = nom;
            input.value = champs[nom];
            form.appendChild(input);
        });
    });
});
</script>
