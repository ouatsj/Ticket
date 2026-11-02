document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addversebank').forEach(function (e)
    {
        document.querySelector('h3#bankTitle').innerHTML = `ENREGISTREMENT DES VERSEMENTS BANCAIRE`;

        verseverif = function () {
            var champ = document.querySelector('#versmontant');
            var n = (champ.value || '').toString().replace(/\s/g, '');
            var m = parseInt(n, 10);
            var solde = parseInt(document.querySelector('#soldecaisse').value, 10);
            if (isNaN(solde)) {
                solde = 0;
            }
            if (isNaN(m)) {
                document.querySelector('#smsverser').style.display = 'none';
                return false;
            }
            if (solde < m) {
                document.querySelector('#smsverser').style.display = 'block';
                document.querySelector('#versementsms').innerHTML = `le montant que vous aviez saisi dépasse le solde de votre caisse`;
                champ.value = '';
                return false;
            }
            document.querySelector('#smsverser').style.display = 'none';
            champ.value = n;
            return true;
        };

        e.onclick = function () {
            let banq = document.querySelector('#verseFormbank');
            banq.setAttribute('action', `${APP_ROOT}/Caisses/addbank/${e.dataset.cle_compagnie}`);
        };

    });

    var formBank = document.querySelector('#verseFormbank');
    if (formBank) {
        formBank.addEventListener('submit', function (ev) {
            if (typeof verseverif === 'function' && verseverif() === false) {
                ev.preventDefault();
                return false;
            }
            var m = parseInt((document.querySelector('#versmontant').value || '').toString().replace(/\s/g, ''), 10);
            var solde = parseInt(document.querySelector('#soldecaisse').value, 10);
            if (isNaN(m) || m <= 0 || isNaN(solde) || solde < m) {
                ev.preventDefault();
                document.querySelector('#smsverser').style.display = 'block';
                document.querySelector('#versementsms').innerHTML = `le montant que vous aviez saisi dépasse le solde de votre caisse`;
                return false;
            }
        });
    }
});
