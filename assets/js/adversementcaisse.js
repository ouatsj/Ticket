document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adversementcaisse').forEach(function (e) 
    {
        document.querySelector('h3#versTitle').innerHTML = `ENREGISTREMENT DES VERSEMENTS CAISSE`;
        
        let versinformation = document.querySelector('#caissgenredepot');
        
        if (versinformation !== null) 
        versinformation.onchange = () => 
        {
                document.querySelector('#prenomiden').options.length = 1;
                    let httpInfosverse;
                    if (window.XMLHttpRequest) {
                        httpInfosverse = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfosverse = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var verifiegenreversem = document.querySelector('#caissgenredepot')
                    .options[document.querySelector('#caissgenredepot').options.selectedIndex].value;
                    httpInfosverse.open('GET', window.location.origin + `${APP_ROOT}/depots/depot_genre/${verifiegenreversem}`, true);
                    httpInfosverse.onload = () => {
                        const resulteverse = JSON.parse(httpInfosverse.responseText);
        
                            if(resulteverse == null){
                                document.querySelector('#prenomiden').value = "";
        
                            }else
                            { 
                                if (Object.entries(resulteverse).length >= 1) {
                            
                                    for (let key in Object.entries(resulteverse)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${resulteverse[key].nomprenom_perso}`;
                                        opt.innerHTML = `${resulteverse[key].nomprenom_perso}`;
                                        document.querySelector('#prenomiden').add(opt);
                                        
                                    }
                                } else {
                                    document.querySelector('#prenomident').options.length = 1;
                                }
                            }
        
                        };
                        
                        httpInfosverse.setRequestHeader('Content-Type', 'application/json');
                        httpInfosverse.send();
    
                };

        function parseMontantSaisi(raw) {
            if (raw == null) {
                return NaN;
            }
            var cleaned = String(raw).replace(/\s/g, '').replace(',', '.');
            if (cleaned === '' || cleaned.toUpperCase() === 'VERIFIER SOLDE') {
                return NaN;
            }
            return parseFloat(cleaned);
        }

        function formatFcfa(n) {
            return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        }

        function soldeCumulSousGares() {
            var el = document.querySelector('#soldescaiss');
            return el ? parseFloat(el.value) || 0 : 0;
        }

        function afficherRejetSolde(montant, solde) {
            var box = document.querySelector('#autrsmsverse');
            var msg = document.querySelector('#autrversementsm');
            if (!box || !msg) {
                return;
            }
            box.style.display = 'block';
            msg.innerHTML =
                'Versement rejeté : le montant saisi (' + formatFcfa(montant) +
                ' F) dépasse le solde net de la gare (' +
                formatFcfa(solde) + ' F, déficits déjà déduits).';
        }

        window.verseverif = function () {
            var input = document.querySelector('#autrversmontan');
            if (!input) {
                return true;
            }
            var m = parseMontantSaisi(input.value);
            var solde = soldeCumulSousGares();
            var box = document.querySelector('#autrsmsverse');

            if (!isNaN(m) && m > solde) {
                afficherRejetSolde(m, solde);
                return false;
            }
            if (box) {
                box.style.display = 'none';
            }
            return true;
        };

        e.onclick = function () {
            let autr = document.querySelector('#verseForcaisse');
            autr.setAttribute('action', `${APP_ROOT}/Caisses/adverscaisse/${e.dataset.cle_compagnie}`);
        };

        var formVerse = document.querySelector('#verseForcaisse');
        if (formVerse && !formVerse.dataset.soldeGuard) {
            formVerse.dataset.soldeGuard = '1';
            formVerse.addEventListener('submit', function (ev) {
                var input = document.querySelector('#autrversmontan');
                var m = parseMontantSaisi(input ? input.value : '');
                var solde = soldeCumulSousGares();
                if (isNaN(m) || m <= 0) {
                    ev.preventDefault();
                    var box = document.querySelector('#autrsmsverse');
                    var msg = document.querySelector('#autrversementsm');
                    if (box && msg) {
                        box.style.display = 'block';
                        msg.innerHTML = 'Versement rejeté : le montant doit être supérieur à 0 F.';
                    }
                    return false;
                }
                if (m > solde) {
                    ev.preventDefault();
                    afficherRejetSolde(m, solde);
                    return false;
                }
                return true;
            });
        }

    })
});