document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addversementcaisse').forEach(function (e) 
    {
        document.querySelector('h3#verseTitle').innerHTML = `ENREGISTREMENT DES VERSEMENTS CAISSE`;
        
        let versinformation = document.querySelector('#caissegenredepot');
        
        if (versinformation !== null) 
        versinformation.onchange = () => 
        {
                document.querySelector('#prenomident').options.length = 1;
                    let httpInfosverse;
                    if (window.XMLHttpRequest) {
                        httpInfosverse = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfosverse = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var verifiegenreversem = document.querySelector('#caissegenredepot')
                    .options[document.querySelector('#caissegenredepot').options.selectedIndex].value;
                    httpInfosverse.open('GET', window.location.origin + `${APP_ROOT}/depots/depot_genre/${verifiegenreversem}`, true);
                    httpInfosverse.onload = () => {
                        const resulteverse = JSON.parse(httpInfosverse.responseText);
        
                            if(resulteverse == null){
                                document.querySelector('#prenomident').value = "";
        
                            }else
                            { 
                                if (Object.entries(resulteverse).length >= 1) {
                            
                                    for (let key in Object.entries(resulteverse)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${resulteverse[key].nomprenom_perso}`;
                                        opt.innerHTML = `${resulteverse[key].nomprenom_perso}`;
                                        document.querySelector('#prenomident').add(opt);
                                        
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
                    
                    var m = parseInt(document.querySelector('#autreversmontan').value);
                    var n = document.querySelector('#autreversmontan').value;
                    var solde = parseInt(document.querySelector('#soldecaiss').value);
                        
                            if (solde < m) 
                            {
        
                                document.querySelector('#autresmsverse').style.display = 'block';
                                document.querySelector('#autreversementsm').innerHTML = `le montant que vous aviez saisi dépasse le solde de votre caisse`;
                                
                                document.querySelector('#autreversmontan').value = 'VERIFIER SOLDE';
                            } 
                            else{

                                document.querySelector('#autresmsverse').style.display = 'none';

                                document.querySelector('#autreversmontan').value = n ;
                            }
        };
            
        e.onclick = function () {
        let autre = document.querySelector('#verseFormcaisse');
        autre.setAttribute('action', `${APP_ROOT}/Caisses/adverscaisse/${e.dataset.cle_compagnie}`);
        }

    })
});