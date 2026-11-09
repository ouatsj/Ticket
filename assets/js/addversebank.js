document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addversebank').forEach(function (e)
    {
        document.querySelector('h3#bankTitle').innerHTML = `ENREGISTREMENT DES VERSEMENTS BANCAIRE`;

        function parseMontantSaisi(raw) {
            if (raw == null) {
                return NaN;
            }
            var cleaned = String(raw).replace(/\s/g, '').replace(',', '.');
            if (cleaned === '') {
                return NaN;
            }
            return parseFloat(cleaned);
        }

        function formatFcfa(n) {
            return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        }

        function soldeCumulGare() {
            var el = document.querySelector('#soldecaisse');
            return el ? parseFloat(el.value) || 0 : 0;
        }

        function afficherRejet(montant, solde) {
            var box = document.querySelector('#smsverser');
            var msg = document.querySelector('#versementsms');
            if (!box || !msg) {
                return;
            }
            box.style.display = 'block';
            msg.innerHTML =
                'Versement bancaire rejeté : le montant saisi (' + formatFcfa(montant) +
                ' F) dépasse le solde net de la gare (' +
                formatFcfa(solde) + ' F, déficits déjà déduits).';
        }

        window.verseverif = function () {
            var champ = document.querySelector('#versmontant');
            if (!champ) {
                return true;
            }
            var m = parseMontantSaisi(champ.value);
            var solde = soldeCumulGare();
            var box = document.querySelector('#smsverser');

            if (!isNaN(m) && m > solde) {
                afficherRejet(m, solde);
                return false;
            }
            if (box) {
                box.style.display = 'none';
            }
            return true;
        };

        e.onclick = function () {
            let banq = document.querySelector('#verseFormbank');
            banq.setAttribute('action', `${APP_ROOT}/Caisses/addbank/${e.dataset.cle_compagnie}`);
        };

    });

    var formBank = document.querySelector('#verseFormbank');
    if (formBank && !formBank.dataset.soldeGuard) {
        formBank.dataset.soldeGuard = '1';
        formBank.addEventListener('submit', function (ev) {
            var m = parseFloat(String(document.querySelector('#versmontant').value || '')
                .replace(/\s/g, '').replace(',', '.'));
            var solde = parseFloat(document.querySelector('#soldecaisse').value) || 0;
            if (isNaN(m) || m <= 0) {
                ev.preventDefault();
                var box = document.querySelector('#smsverser');
                var msg = document.querySelector('#versementsms');
                if (box && msg) {
                    box.style.display = 'block';
                    msg.innerHTML = 'Versement bancaire rejeté : le montant doit être supérieur à 0 F.';
                }
                return false;
            }
            if (m > solde) {
                ev.preventDefault();
                var box2 = document.querySelector('#smsverser');
                var msg2 = document.querySelector('#versementsms');
                if (box2 && msg2) {
                    box2.style.display = 'block';
                    msg2.innerHTML =
                        'Versement bancaire rejeté : le montant saisi (' +
                        Math.round(m).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') +
                        ' F) dépasse le solde net de la gare (' +
                        Math.round(solde).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') +
                        ' F, déficits déjà déduits).';
                }
                return false;
            }
            return true;
        });
    }
});
