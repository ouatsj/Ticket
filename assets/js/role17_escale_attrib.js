/**
 * Admin : cascade Compagnie → Ligne → Escale pour attribution rôle 17 (Venteescal).
 */
(function () {
    function appRoot() {
        if (typeof APP_ROOT === 'string') {
            return APP_ROOT.replace(/\/$/, '');
        }
        return '';
    }

    function roleSelect(form) {
        return form.querySelector('select[name="fonction"]');
    }

    function wrap(form) {
        return form.querySelector('[data-role17-wrap]');
    }

    function fillSelect(sel, rows, valueKey, labelKey, placeholder, selected) {
        if (!sel) return;
        sel.innerHTML = '';
        var ph = document.createElement('option');
        ph.value = '';
        ph.textContent = placeholder;
        sel.appendChild(ph);
        (rows || []).forEach(function (row) {
            var opt = document.createElement('option');
            opt.value = row[valueKey];
            opt.textContent = row[labelKey];
            if (selected && String(selected) === String(row[valueKey])) {
                opt.selected = true;
            }
            sel.appendChild(opt);
        });
    }

    function resetSelect(sel, placeholder, disabled) {
        if (!sel) return;
        sel.innerHTML = '';
        var ph = document.createElement('option');
        ph.value = '';
        ph.textContent = placeholder;
        sel.appendChild(ph);
        sel.disabled = !!disabled;
        sel.value = '';
    }

    function syncHidden(form) {
        var w = wrap(form);
        if (!w) return;
        var ligneSel = w.querySelector('[data-role17-ligne]');
        var escaleSel = w.querySelector('[data-role17-escale]');
        var hLigne = w.querySelector('[data-role17-ligne-hidden]');
        var hValue = w.querySelector('[data-role17-value-hidden]');
        var hLabel = w.querySelector('[data-role17-label]');
        if (hLigne && ligneSel) hLigne.value = ligneSel.value || '';
        if (hValue && escaleSel) hValue.value = escaleSel.value || '';
        if (hLabel && escaleSel) {
            var opt = escaleSel.options[escaleSel.selectedIndex];
            hLabel.value = (opt && opt.value) ? opt.textContent : '';
        }
    }

    function clearHidden(form) {
        var w = wrap(form);
        if (!w) return;
        var hLigne = w.querySelector('[data-role17-ligne-hidden]');
        var hValue = w.querySelector('[data-role17-value-hidden]');
        var hLabel = w.querySelector('[data-role17-label]');
        if (hLigne) hLigne.value = '';
        if (hValue) hValue.value = '';
        if (hLabel) hLabel.value = '';
    }

    function setVisible(form, show) {
        var w = wrap(form);
        if (!w) return;
        w.style.display = show ? '' : 'none';
        var compagnie = w.querySelector('[data-role17-compagnie]');
        var ligne = w.querySelector('[data-role17-ligne]');
        var escale = w.querySelector('[data-role17-escale]');
        if (compagnie) compagnie.required = !!show;
        if (ligne) ligne.required = !!show;
        if (escale) escale.required = !!show;
        if (!show) {
            if (compagnie) compagnie.value = '';
            resetSelect(ligne, 'Choisir d’abord une compagnie…', true);
            resetSelect(escale, 'Choisir d’abord une ligne…', true);
            clearHidden(form);
        }
    }

    function loadCompagnies(form) {
        var w = wrap(form);
        if (!w) return;
        var ekey = w.getAttribute('data-ekey') || '';
        var gare = w.getAttribute('data-gare') || '';
        var compSel = w.querySelector('[data-role17-compagnie]');
        var selected = w.getAttribute('data-selected-compagnie') || '';
        if (!ekey || !gare || !compSel) return;

        var url = window.location.origin + appRoot()
            + '/utilisateurs/ajax_compagnies_gare/'
            + encodeURIComponent(ekey) + '/'
            + encodeURIComponent(gare);
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.onload = function () {
            var rows = [];
            try { rows = JSON.parse(xhr.responseText) || []; } catch (e) { rows = []; }
            if (!Array.isArray(rows)) rows = [];
            fillSelect(compSel, rows, 'cle_compagnie', 'nom_compagnie', 'Choisir une compagnie…', selected);

            var selectedLigne = w.getAttribute('data-selected-ligne') || '';
            if (!selected && selectedLigne) {
                // Édition : retrouver la compagnie de la ligne déjà affectée.
                var urlL = window.location.origin + appRoot()
                    + '/utilisateurs/ajax_lignes_gare/'
                    + encodeURIComponent(ekey) + '/'
                    + encodeURIComponent(gare);
                var xhrL = new XMLHttpRequest();
                xhrL.open('GET', urlL, true);
                xhrL.onload = function () {
                    var lignes = [];
                    try { lignes = JSON.parse(xhrL.responseText) || []; } catch (e2) { lignes = []; }
                    var match = (lignes || []).filter(function (r) {
                        return String(r.ident_ligne) === String(selectedLigne);
                    })[0];
                    if (match && match.cle_compagnie) {
                        w.setAttribute('data-selected-compagnie', match.cle_compagnie);
                        compSel.value = match.cle_compagnie;
                        loadLignes(form, match.cle_compagnie);
                    }
                };
                xhrL.send();
                return;
            }

            if (selected) {
                loadLignes(form, selected);
            }
        };
        xhr.send();
    }

    function loadLignes(form, compagnie) {
        var w = wrap(form);
        if (!w) return;
        var ekey = w.getAttribute('data-ekey') || '';
        var gare = w.getAttribute('data-gare') || '';
        var ligneSel = w.querySelector('[data-role17-ligne]');
        var escaleSel = w.querySelector('[data-role17-escale]');
        var selected = w.getAttribute('data-selected-ligne') || '';
        if (!ekey || !gare || !ligneSel) return;

        if (!compagnie) {
            resetSelect(ligneSel, 'Choisir d’abord une compagnie…', true);
            resetSelect(escaleSel, 'Choisir d’abord une ligne…', true);
            syncHidden(form);
            return;
        }

        ligneSel.disabled = false;
        var url = window.location.origin + appRoot()
            + '/utilisateurs/ajax_lignes_gare/'
            + encodeURIComponent(ekey) + '/'
            + encodeURIComponent(gare)
            + '?compagnie=' + encodeURIComponent(compagnie);
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.onload = function () {
            var rows = [];
            try { rows = JSON.parse(xhr.responseText) || []; } catch (e) { rows = []; }
            if (!Array.isArray(rows)) rows = [];
            fillSelect(ligneSel, rows, 'ident_ligne', 'label', 'Choisir une ligne…', selected);
            syncHidden(form);
            if (selected) {
                loadEscales(form, selected);
            } else {
                resetSelect(escaleSel, 'Choisir d’abord une ligne…', true);
            }
        };
        xhr.send();
    }

    function loadEscales(form, identLigne) {
        var w = wrap(form);
        if (!w) return;
        var ekey = w.getAttribute('data-ekey') || '';
        var escaleSel = w.querySelector('[data-role17-escale]');
        var selected = w.getAttribute('data-selected-value') || '';
        if (!ekey || !escaleSel) return;

        if (!identLigne) {
            resetSelect(escaleSel, 'Choisir d’abord une ligne…', true);
            syncHidden(form);
            return;
        }

        escaleSel.disabled = false;
        var url = window.location.origin + appRoot()
            + '/utilisateurs/ajax_escales_ligne/'
            + encodeURIComponent(ekey) + '/'
            + encodeURIComponent(identLigne);
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.onload = function () {
            var rows = [];
            try { rows = JSON.parse(xhr.responseText) || []; } catch (e) { rows = []; }
            if (!Array.isArray(rows)) rows = [];
            fillSelect(escaleSel, rows, 'value', 'label', 'Choisir une escale…', selected);
            syncHidden(form);
        };
        xhr.send();
    }

    function syncRole(form) {
        var sel = roleSelect(form);
        if (!sel) return;
        var is17 = String(sel.value) === '17';
        setVisible(form, is17);
        if (is17) {
            loadCompagnies(form);
        }
    }

    function bindForm(form) {
        if (!form || form.dataset.role17Bound === '1') return;
        if (!wrap(form) || !roleSelect(form)) return;
        form.dataset.role17Bound = '1';

        var sel = roleSelect(form);
        sel.addEventListener('change', function () {
            syncRole(form);
        });

        var w = wrap(form);
        var compSel = w.querySelector('[data-role17-compagnie]');
        var ligneSel = w.querySelector('[data-role17-ligne]');
        var escaleSel = w.querySelector('[data-role17-escale]');

        if (compSel) {
            compSel.addEventListener('change', function () {
                w.setAttribute('data-selected-compagnie', compSel.value || '');
                w.setAttribute('data-selected-ligne', '');
                w.setAttribute('data-selected-value', '');
                clearHidden(form);
                loadLignes(form, compSel.value);
            });
        }
        if (ligneSel) {
            ligneSel.addEventListener('change', function () {
                w.setAttribute('data-selected-ligne', ligneSel.value || '');
                w.setAttribute('data-selected-value', '');
                syncHidden(form);
                var hValue = w.querySelector('[data-role17-value-hidden]');
                var hLabel = w.querySelector('[data-role17-label]');
                if (hValue) hValue.value = '';
                if (hLabel) hLabel.value = '';
                loadEscales(form, ligneSel.value);
            });
        }
        if (escaleSel) {
            escaleSel.addEventListener('change', function () {
                w.setAttribute('data-selected-value', escaleSel.value || '');
                syncHidden(form);
            });
        }

        form.addEventListener('submit', function () {
            var is17 = String(sel.value) === '17';
            if (is17) {
                syncHidden(form);
            } else {
                clearHidden(form);
            }
        });

        syncRole(form);
    }

    function boot() {
        document.querySelectorAll('form.modal-body, form').forEach(bindForm);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
