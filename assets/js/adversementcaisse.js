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

        verseverif = function () {
                    
                    var m = parseInt(document.querySelector('#autrversmontan').value);
                    var n = document.querySelector('#autrversmontan').value;
                    var solde = parseInt(document.querySelector('#soldescaiss').value);
                        
                            if (solde < m) 
                            {
        
                                document.querySelector('#autrsmsverse').style.display = 'block';
                                document.querySelector('#autrversementsm').innerHTML = `le montant que vous aviez saisi dépasse le solde de votre caisse`;
                                
                                document.querySelector('#autrversmontan').value = 'VERIFIER SOLDE';
                            } 
                            else{

                                document.querySelector('#autrsmsverse').style.display = 'none';

                                document.querySelector('#autrversmontan').value = n ;
                            }
        };
            
        e.onclick = function () {
        let autr = document.querySelector('#verseForcaisse');
        autr.setAttribute('action', `${APP_ROOT}/Caisses/adverscaisse/${e.dataset.cle_compagnie}`);
        }

    })
});