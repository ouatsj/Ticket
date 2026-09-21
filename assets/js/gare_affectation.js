/**
 * Formulaire gare départ / arrivée — option B :
 * compagnie d’abord, puis Affecter (lieu physique existant) ou Créer.
 */
(function () {
    function syncForm(form) {
        if (!form) {
            return;
        }
        var modeInput = form.querySelector('input[name="mode_affectation"]:checked');
        var mode = modeInput ? modeInput.value : 'affecter';
        var isAffecter = mode === 'affecter';

        var blockAffect = form.querySelectorAll('.js-mode-affecter');
        var blockCreer = form.querySelectorAll('.js-mode-creer');
        var i;

        for (i = 0; i < blockAffect.length; i++) {
            blockAffect[i].style.display = isAffecter ? '' : 'none';
        }
        for (i = 0; i < blockCreer.length; i++) {
            blockCreer[i].style.display = isAffecter ? 'none' : '';
        }

        var gareSelect = form.querySelector('.js-gare-physique');
        if (gareSelect) {
            gareSelect.required = isAffecter;
            if (!isAffecter) {
                gareSelect.value = '';
            }
        }

        var nom = form.querySelector('.js-gare-nom');
        if (nom) {
            nom.required = !isAffecter;
            if (isAffecter) {
                nom.removeAttribute('required');
            } else {
                nom.setAttribute('required', 'required');
            }
        }

        var ville = form.querySelector('.js-gare-ville');
        if (ville) {
            ville.required = !isAffecter;
            if (isAffecter) {
                ville.removeAttribute('required');
            } else {
                ville.setAttribute('required', 'required');
            }
        }
    }

    function bindForm(form) {
        if (!form || form.getAttribute('data-gare-aff-bound') === '1') {
            return;
        }
        form.setAttribute('data-gare-aff-bound', '1');
        var radios = form.querySelectorAll('input[name="mode_affectation"]');
        for (var i = 0; i < radios.length; i++) {
            radios[i].addEventListener('change', function () {
                syncForm(form);
            });
        }
        syncForm(form);
    }

    function initAll() {
        var forms = document.querySelectorAll('form.js-gare-affectation');
        for (var i = 0; i < forms.length; i++) {
            bindForm(forms[i]);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
