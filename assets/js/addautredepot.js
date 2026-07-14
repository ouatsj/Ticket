document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addautredepot').forEach(function (e) 
    {
        document.querySelector('h3#potTitle').innerHTML = `ENREGISTREMENT DES DEPOTS CAISSE`;

        let depotinformation = document.querySelector('#genredepot');
        
        if (depotinformation !== null) 
        depotinformation.onchange = () => 
        {
                document.querySelector('#prenomnomident').options.length = 1;
                    let httpInfosdepot;
                    if (window.XMLHttpRequest) {
                        httpInfosdepot = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfosdepot = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var verifierdepot = document.querySelector('#genredepot')
                    .options[document.querySelector('#genredepot').options.selectedIndex].value;
                    httpInfosdepot.open('GET', window.location.origin + `${APP_ROOT}/depots/depot_genre/${verifierdepot}`, true);
                    httpInfosdepot.onload = () => {
                        const resultedepot = JSON.parse(httpInfosdepot.responseText);
        
                            if(resultedepot == null){
                                document.querySelector('#prenomnomident').value = "";
        
                            }else
                            { 
                                if (Object.entries(resultedepot).length >= 1) {
                            
                                    for (let key in Object.entries(resultedepot)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${resultedepot[key].nomprenom_perso}`;
                                        opt.innerHTML = `${resultedepot[key].nomprenom_perso}`;
                                        document.querySelector('#prenomnomident').add(opt);
                                        
                                    }
                                } else {
                                    document.querySelector('#prenomnomident').options.length = 1;
                                }
                            }
        
                        };
                        
                        httpInfosdepot.setRequestHeader('Content-Type', 'application/json');
                        httpInfosdepot.send();
    
                };

                verifdepo = function () {
                    
                    var depot = parseInt(document.querySelector('#autredepotmontant').value);
                    var depo = document.querySelector('#autredepotmontant').value;
                    var soldedp = parseInt(document.querySelector('#soldeautre').value);
                        
                            if (soldedp < depot) 
                            {
        
                                document.querySelector('#autresmsdepot').style.display = 'block';
                                document.querySelector('#autredepotsms').innerHTML = `le montant que vous aviez saisi dépasse le solde de votre caisse`;
                                
                                document.querySelector('#autredepotmontant').value = 'VERIFIER SOLDE';
                            } 
                            else{

                                document.querySelector('#autresmsdepot').style.display = 'none';

                                document.querySelector('#autredepotmontant').value = depo ;
                            }
                    };
            
        e.onclick = function () {
        let listeautre = document.querySelector('#autredepotForm');
        listeautre.setAttribute('action', `${APP_ROOT}/Depots/addsous/${e.dataset.cle_compagnie}`);
        }

    })
});