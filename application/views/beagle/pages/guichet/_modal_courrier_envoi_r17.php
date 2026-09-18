<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Modale unique envoi courrier (rôle 17) :
 * cases à cocher + formulaires intégrés (pas de redirection).
 */
$dep_lab = !empty($escale_depart_label) ? (string) $escale_depart_label : '';
if ($dep_lab === '' && !empty($garedeparts) && is_array($garedeparts)) {
    $dep0 = $garedeparts[0];
    $dep_lab = $dep0->nom_gaep . '/' . $dep0->nomsousgare;
}
?>
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="courrier-envoi-r17" style="perspective: none;">
    <div class="modal-content r17-vente-modal r17-courrier-modal">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">Envoi courrier</h3>
            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true">
                <span class="mdi mdi-close text-white"></span>
            </button>
        </div>
        <div class="modal-body">
            <p class="mb-2" style="font-size:0.85rem;color:#475569;">
                Choisissez le type, puis remplissez étape par étape (Suivant → Valider).
            </p>
            <div class="r17-exp-types" id="r17ExpTypes">
                <label class="r17-exp-check">
                    <input type="checkbox" name="r17_exp_type[]" value="ordinaire" data-panel="r17-panel-ordinaire" checked>
                    <span>Expédition ordinaire</span>
                </label>
                <label class="r17-exp-check">
                    <input type="checkbox" name="r17_exp_type[]" value="personnel" data-panel="r17-panel-personnel">
                    <span>Expédition personnel</span>
                </label>
                <label class="r17-exp-check">
                    <input type="checkbox" name="r17_exp_type[]" value="partenaire" data-panel="r17-panel-partenaire">
                    <span>Expédition partenaire</span>
                </label>
            </div>
            <?php if ($dep_lab !== ''): ?>
                <div class="r17-depart-chip is-fixed mt-2 mb-2">
                    Départ escale : <strong><?= htmlspecialchars($dep_lab, ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
            <?php endif; ?>
            <p id="r17ExpHint" class="text-danger small mt-2 mb-0" style="display:none;">
                Cochez au moins un type d’expédition.
            </p>

            <div class="r17-exp-panels">
                <div class="r17-exp-panel is-active" id="r17-panel-ordinaire" data-type="ordinaire">
                    <?php $this->load->view('beagle/pages/guichet/_partial_courordescal_form'); ?>
                </div>
                <div class="r17-exp-panel" id="r17-panel-personnel" data-type="personnel" hidden>
                    <?php $this->load->view('beagle/pages/guichet/_partial_courpersoescal_form'); ?>
                </div>
                <div class="r17-exp-panel" id="r17-panel-partenaire" data-type="partenaire" hidden>
                    <?php $this->load->view('beagle/pages/guichet/_partial_courpartescal_form'); ?>
                </div>
            </div>
        </div>
        <div class="modal-footer r17-modal-footer-outer">
            <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">FERMER</button>
        </div>
    </div>
</div>
<style>
#courrier-envoi-r17.modal-container {
    max-width: 520px;
    width: 96%;
}
#courrier-envoi-r17 .r17-courrier-modal {
    max-width: 520px;
    max-height: 94vh;
    overflow: auto;
}
.r17-exp-types {
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
    margin-bottom: 0.75rem;
}
.r17-exp-check {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    min-height: 48px;
    padding: 0.55rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    -webkit-tap-highlight-color: transparent;
}
.r17-exp-check input {
    width: 1.25rem;
    height: 1.25rem;
    flex: 0 0 auto;
}
.r17-exp-check:has(input:checked) {
    border-color: #0d6efd;
    background: #eff6ff;
    color: #1e3a8a;
}
.r17-exp-panel {
    margin-top: 0.5rem;
    padding-top: 0.35rem;
    border-top: 1px solid #e2e8f0;
}
.r17-exp-panel[hidden] {
    display: none !important;
}
#courrier-envoi-r17 .card-header-divider {
    display: none;
}
#courrier-envoi-r17 .form-group {
    margin-bottom: 0.65rem;
    float: none !important;
    width: 100% !important;
    max-width: 100% !important;
    flex: 0 0 100% !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
}
#courrier-envoi-r17 .row {
    display: block;
    margin: 0;
}
#courrier-envoi-r17 .form-control {
    min-height: 44px;
    font-size: 16px; /* évite zoom iOS */
}
#courrier-envoi-r17 .r17-wiz-step[hidden] {
    display: none !important;
}
#courrier-envoi-r17 .r17-wiz-step-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 0.75rem;
}
#courrier-envoi-r17 .r17-wiz-nav {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    width: 100%;
    margin-top: 0.35rem;
}
#courrier-envoi-r17 .r17-wiz-nav .btn,
#courrier-envoi-r17 .r17-wiz-nav input.btn {
    flex: 1 1 45%;
    min-height: 52px;
    font-weight: 700;
    font-size: 1rem;
}
#courrier-envoi-r17 .r17-wiz-nav .r17-wiz-submit {
    flex: 1 1 100%;
}
#courrier-envoi-r17 .modal-footer {
    border-top: none;
    padding: 0.5rem 0 0;
}
#courrier-envoi-r17 .r17-modal-footer-outer {
    padding: 0.65rem 1rem 1rem;
}
#courrier-envoi-r17 .r17-modal-footer-outer .btn {
    min-height: 48px;
    width: 100%;
    font-weight: 700;
}
#courrier-envoi-r17 .r17-wiz-err {
    color: #b91c1c;
    font-size: 0.85rem;
    font-weight: 600;
    margin: 0 0 0.5rem;
}
</style>
<script>
(function () {
    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    function setPanelEnabled(panel, enabled) {
        if (!panel) return;
        var fields = panel.querySelectorAll('input, select, textarea, button');
        for (var i = 0; i < fields.length; i++) {
            if (enabled) {
                if (fields[i].getAttribute('data-r17-was-disabled') === '1') {
                    fields[i].disabled = false;
                    fields[i].removeAttribute('data-r17-was-disabled');
                }
            } else if (!fields[i].disabled) {
                fields[i].setAttribute('data-r17-was-disabled', '1');
                fields[i].disabled = true;
            }
        }
    }

    function syncRequiredForStep(form, activeStep) {
        var steps = form.querySelectorAll('.r17-wiz-step');
        for (var s = 0; s < steps.length; s++) {
            var on = steps[s] === activeStep;
            var fields = steps[s].querySelectorAll('input, select, textarea');
            for (var i = 0; i < fields.length; i++) {
                var el = fields[i];
                if (on) {
                    if (el.getAttribute('data-r17-req') === '1') {
                        el.setAttribute('required', 'required');
                    }
                } else {
                    if (el.hasAttribute('required')) {
                        el.setAttribute('data-r17-req', '1');
                        el.removeAttribute('required');
                    }
                }
            }
        }
    }

    function validateStep(step) {
        if (!step) return true;
        var fields = step.querySelectorAll('input, select, textarea');
        for (var i = 0; i < fields.length; i++) {
            var el = fields[i];
            if (el.disabled || el.type === 'hidden') continue;
            if (el.offsetParent === null && el.getAttribute('style') && el.style.display === 'none') continue;
            if (typeof el.checkValidity === 'function' && !el.checkValidity()) {
                try { el.reportValidity(); } catch (e) {}
                el.focus();
                return false;
            }
            if ((el.hasAttribute('required') || el.getAttribute('data-r17-req') === '1')
                && String(el.value || '').trim() === '') {
                el.focus();
                return false;
            }
        }
        return true;
    }

    function showWizardStep(form, index) {
        var steps = form.querySelectorAll('.r17-wiz-step');
        if (!steps.length) return;
        if (index < 0) index = 0;
        if (index >= steps.length) index = steps.length - 1;
        form._r17WizIndex = index;
        for (var i = 0; i < steps.length; i++) {
            var on = i === index;
            steps[i].hidden = !on;
            steps[i].classList.toggle('is-active', on);
        }
        syncRequiredForStep(form, steps[index]);
        var prev = form.querySelector('.r17-wiz-prev');
        var next = form.querySelector('.r17-wiz-next');
        var submit = form.querySelector('.r17-wiz-submit');
        if (prev) prev.hidden = index === 0;
        if (next) next.hidden = index >= steps.length - 1;
        if (submit) submit.hidden = index < steps.length - 1;
    }

    function initWizard(form) {
        if (!form || form.getAttribute('data-r17-wiz') === '1') return;
        form.setAttribute('data-r17-wiz', '1');
        form.setAttribute('novalidate', 'novalidate');
        form._r17WizIndex = 0;

        var steps = form.querySelectorAll('.r17-wiz-step');
        for (var i = 0; i < steps.length; i++) {
            var fields = steps[i].querySelectorAll('[required]');
            for (var j = 0; j < fields.length; j++) {
                fields[j].setAttribute('data-r17-req', '1');
            }
        }

        var prev = form.querySelector('.r17-wiz-prev');
        var next = form.querySelector('.r17-wiz-next');
        if (prev) {
            prev.addEventListener('click', function () {
                showWizardStep(form, (form._r17WizIndex || 0) - 1);
            });
        }
        if (next) {
            next.addEventListener('click', function () {
                var stepsNow = form.querySelectorAll('.r17-wiz-step');
                var cur = stepsNow[form._r17WizIndex || 0];
                if (!validateStep(cur)) return;
                showWizardStep(form, (form._r17WizIndex || 0) + 1);
            });
        }
        form.addEventListener('submit', function (ev) {
            var stepsNow = form.querySelectorAll('.r17-wiz-step');
            for (var i = 0; i < stepsNow.length; i++) {
                // Réactiver temporairement les required pour contrôle final
                var fields = stepsNow[i].querySelectorAll('[data-r17-req="1"]');
                for (var j = 0; j < fields.length; j++) {
                    fields[j].setAttribute('required', 'required');
                }
                if (!validateStep(stepsNow[i])) {
                    ev.preventDefault();
                    showWizardStep(form, i);
                    return false;
                }
            }
        });

        showWizardStep(form, 0);
    }

    function syncPanels() {
        var boxes = document.querySelectorAll('#r17ExpTypes input[type="checkbox"]');
        var hint = document.getElementById('r17ExpHint');
        var any = false;
        for (var i = 0; i < boxes.length; i++) {
            var panelId = boxes[i].getAttribute('data-panel');
            var panel = panelId ? document.getElementById(panelId) : null;
            var on = boxes[i].checked;
            if (on) any = true;
            if (panel) {
                if (on) {
                    panel.hidden = false;
                    panel.classList.add('is-active');
                    setPanelEnabled(panel, true);
                    var form = panel.querySelector('form.r17-wiz-form');
                    if (form) {
                        initWizard(form);
                        showWizardStep(form, 0);
                    }
                } else {
                    panel.hidden = true;
                    panel.classList.remove('is-active');
                    setPanelEnabled(panel, false);
                }
            }
        }
        if (hint) {
            hint.style.display = any ? 'none' : 'block';
        }
    }

    ready(function () {
        var root = document.getElementById('r17ExpTypes');
        if (!root) return;
        root.addEventListener('change', syncPanels);
        syncPanels();

        // Frais d'expédition : saisie libre (≥ 500 F), jamais liés au prix ticket.
        function bindFraisMin(id, minF) {
            var el = document.getElementById(id);
            if (!el) return;
            el.setAttribute('min', String(minF));
            el.setAttribute('required', 'required');
            el.addEventListener('input', function () {
                var n = Number(el.value);
                if (el.value !== '' && (!isFinite(n) || n < minF)) {
                    el.setCustomValidity('Minimum ' + minF + ' F CFA');
                } else {
                    el.setCustomValidity('');
                }
            });
        }
        bindFraisMin('fraisexesc', 500);
        bindFraisMin('fraisexpartoesc', 500);

        // Heures = programmes de la ligne attribuée (écrase le handler OD générique).
        function loadHeuresLigne(dateEl, heureEl, ligneId) {
            if (!dateEl || !heureEl || !ligneId) return;
            heureEl.options.length = 1;
            var d = dateEl.value;
            if (!d) return;
            var url = window.location.origin + (typeof APP_ROOT !== 'undefined' ? APP_ROOT : '')
                + '/programmes/verifheure1/' + encodeURIComponent(ligneId) + '/' + encodeURIComponent(d);
            var xhr = new XMLHttpRequest();
            xhr.open('GET', url, true);
            xhr.onload = function () {
                try {
                    var rows = JSON.parse(xhr.responseText);
                    if (!rows) return;
                    var list = Array.isArray(rows) ? rows : Object.keys(rows).map(function (k) { return rows[k]; });
                    list.forEach(function (row) {
                        if (!row || !row.id_ligneheure) return;
                        var opt = document.createElement('option');
                        opt.value = row.id_ligneheure;
                        opt.textContent = row.heure || row.id_ligneheure;
                        heureEl.appendChild(opt);
                    });
                } catch (err) {}
            };
            xhr.send();
        }

        function bindHeuresLigne(dateId, heureId, destId) {
            var dateEl = document.getElementById(dateId);
            var heureEl = document.getElementById(heureId);
            var destEl = document.getElementById(destId);
            if (!dateEl || !heureEl || !destEl) return;
            var ligne = destEl.getAttribute('data-role17-ligne') || '';
            if (!ligne) return;
            // Après adcourescale.js (DOMContentLoaded) : prendre la main.
            setTimeout(function () {
                dateEl.onchange = function () {
                    loadHeuresLigne(dateEl, heureEl, ligne);
                };
            }, 0);
        }

        bindHeuresLigne('date_depheurecourexesc', 'hdepcouresc', 'arrscouresc');
        bindHeuresLigne('date_depheurecourexpersoesc', 'hdepcourpersoesc', 'arrscourpersoesc');
        bindHeuresLigne('date_depheurecourexpartoesc', 'hdepcourpartoesc', 'arrscourpartoesc');
    });
})();
</script>
