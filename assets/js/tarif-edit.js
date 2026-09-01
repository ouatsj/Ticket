document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('tarif-edit-0');
    if (!modal) {
        return;
    }

    var form = modal.querySelector('form');
    var titleEl = modal.querySelector('.modal-title');
    var tarifSel = modal.querySelector('[name="tarifbase"]');
    var clientSel = modal.querySelector('[name="typeclient"]');
    var itinSel = modal.querySelector('[name="itineraire"]');
    var montantEl = modal.querySelector('[name="montanttarif"]');

    document.querySelectorAll('.js-tarif-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!form) {
                return;
            }

            var ekey = btn.getAttribute('data-ekey') || '';
            var id = btn.getAttribute('data-id') || '';
            var typeTarif = btn.getAttribute('data-type_tarif') || '';
            var typetarifId = btn.getAttribute('data-typetarif_id') || '';
            var nomType = btn.getAttribute('data-nom_type') || '';
            var typeclientId = btn.getAttribute('data-typeclient_id') || '';
            var lhId = btn.getAttribute('data-ligne_heure_id') || '';
            var ligneId = btn.getAttribute('data-ligne_id') || '';
            var nomLigne = btn.getAttribute('data-nom_ligne') || '';
            var heure = btn.getAttribute('data-heure') || '';
            var prix = btn.getAttribute('data-prix') || '';

            if (titleEl) {
                titleEl.textContent = 'MODIFICATION SUR ' + typeTarif;
            }
            if (tarifSel) {
                tarifSel.value = typetarifId;
            }
            if (clientSel) {
                clientSel.value = typeclientId;
            }
            if (itinSel) {
                itinSel.innerHTML = '<option value="' + lhId + '.' + ligneId + '">' +
                    nomLigne + '/' + heure + '</option>';
                itinSel.value = lhId + '.' + ligneId;
            }
            if (montantEl) {
                montantEl.value = prix;
            }

            form.setAttribute('action', (window.APP_ROOT || '') + '/Tarifs/edit_/' + ekey + '/' + id);
        });
    });
});
