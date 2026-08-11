document.addEventListener('DOMContentLoaded', function () {
    function applyDepartFilter(compSelect) {
        if (!compSelect) return;
        var targetId = compSelect.getAttribute('data-target-depart');
        if (!targetId) return;
        var departSelect = document.getElementById(targetId);
        if (!departSelect) return;

        var cle = String(compSelect.value || '');
        var keepValue = departSelect.value;
        var options = departSelect.querySelectorAll('option[data-compagnie]');
        var firstVisible = null;

        for (var i = 0; i < options.length; i++) {
            var opt = options[i];
            var match = cle !== '' && String(opt.getAttribute('data-compagnie') || '') === cle;
            opt.hidden = !match;
            opt.disabled = !match;
            if (match && !firstVisible) {
                firstVisible = opt;
            }
        }

        if (cle === '') {
            departSelect.value = '';
            return;
        }

        var selected = departSelect.options[departSelect.selectedIndex];
        var selectedOk = selected
            && selected.getAttribute('data-compagnie')
            && String(selected.getAttribute('data-compagnie')) === cle
            && !selected.disabled;
        if (!selectedOk) {
            departSelect.value = keepValue && firstVisible && keepValue === firstVisible.value
                ? keepValue
                : '';
            // Si la valeur conservée n'est plus visible, reset
            selected = departSelect.options[departSelect.selectedIndex];
            if (!selected || selected.disabled || selected.hidden) {
                departSelect.value = '';
            }
        }
    }

    function bindFiltreCompagnie(root) {
        root = root || document;
        root.querySelectorAll('.js-filtre-compagnie-arrivee').forEach(function (sel) {
            if (sel.getAttribute('data-filtre-bound') === '1') return;
            sel.setAttribute('data-filtre-bound', '1');
            sel.addEventListener('change', function () {
                applyDepartFilter(sel);
            });
            applyDepartFilter(sel);
        });
    }

    /**
     * Prefill compagnie + départ (édition programme).
     * @param {string} departSelectId
     * @param {string} departValue
     */
    window.__syncDepartCompagnie = function (departSelectId, departValue) {
        var departSelect = document.getElementById(departSelectId);
        if (!departSelect) return;
        var compSelect = document.querySelector(
            '.js-filtre-compagnie-arrivee[data-target-depart="' + departSelectId + '"]'
        );
        if (!compSelect) {
            if (departValue) {
                departSelect.value = departValue;
            }
            return;
        }
        var opt = null;
        if (departValue) {
            for (var i = 0; i < departSelect.options.length; i++) {
                if (departSelect.options[i].value === departValue) {
                    opt = departSelect.options[i];
                    break;
                }
            }
        }
        if (opt) {
            var cle = opt.getAttribute('data-compagnie') || '';
            compSelect.value = cle;
            applyDepartFilter(compSelect);
            departSelect.value = departValue;
        } else {
            applyDepartFilter(compSelect);
        }
    };

    bindFiltreCompagnie(document);
    window.__bindFiltreDepartCompagnie = bindFiltreCompagnie;
});
