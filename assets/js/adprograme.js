document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adprograme').forEach(function (e) 
    {
        document.querySelector('h3#progTitle').innerHTML = `ENREGISTREMENTS DES PROGRAMMES DE BUS`;
        
        let busidligne = document.querySelector('#busid');
        if (busidligne !== null)
        busidligne.onkeyup = () => 
        {
            let httpInfosinfobusids;
            if (window.XMLHttpRequest) {
                httpInfosinfobusids = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosinfobusids = new ActiveXObject("Microsoft.XMLHTTP");
            }
                var verificatbus = document.querySelector('#busid').value;
                        document.querySelector('#iddatedeparts').value = "";
                httpInfosinfobusids.open('GET', window.location.origin + `${APP_ROOT}/bus/verificategoriebis/${verificatbus}`, true);
                httpInfosinfobusids.onload = () => 
                {
                    const infosbus = JSON.parse(httpInfosinfobusids.responseText);
                    if (infosbus == null) {
                       document.querySelector('#categorie').value = "";
                    } else 
                    {

                        if(Object.entries(infosbus).length > 1) 
                        {

                                            
                            document.querySelector('#categorie').value = `${infosbus.categorie}`;
                                
                        }else
                        {
                            document.querySelector('#categorie').value = "";
                        }
                    }
                        
                };
                httpInfosinfobusids.setRequestHeader('Content-Type', 'application/json');
                httpInfosinfobusids.send();       
            };
            

            let prinfligne = document.querySelector('#nameligneid');
            if (prinfligne !== null)
            prinfligne.onchange = () => 
            {
                let httpInfosinfobusidpr;
                if (window.XMLHttpRequest) {
                    httpInfosinfobusidpr = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfosinfobusidpr = new ActiveXObject("Microsoft.XMLHTTP");
                }

                document.querySelector('#nameligneheurid').options.length = 1;

                
                var verificatligne = document.querySelector('#nameligneid').value;
                
                httpInfosinfobusidpr.open('GET', window.location.origin + `${APP_ROOT}/Programmes/verifieligneheure/${verificatligne}`, true);
                httpInfosinfobusidpr.onload = () => 
                {
                    const infosbupr = JSON.parse(httpInfosinfobusidpr.responseText);
                    
                            if (Object.entries(infosbupr).length >= 1) 
                            {
                                for (let key in Object.entries(infosbupr)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosbupr[key].id_ligneheure}/${infosbupr[key].code_gadest}/${infosbupr[key].heure}`;
                                    opt.innerHTML = `${infosbupr[key].nom_ligne}/${infosbupr[key].heure}`;

                                    document.querySelector('#nameligneheurid').add(opt);
                                   
                                }

                            } else {
                                document.querySelector('#nameligneheurid').options.length = 1;
                            }
                    
                        
                };
                httpInfosinfobusidpr.setRequestHeader('Content-Type', 'application/json');
                httpInfosinfobusidpr.send();       
            };

            let prinflignehr = document.querySelector('#nameligneheurid');
            if (prinflignehr !== null)
            prinflignehr.onchange = () => 
            {
                let httpInfosinfobusidprog;
                if (window.XMLHttpRequest) {
                    httpInfosinfobusidprog = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfosinfobusidprog = new ActiveXObject("Microsoft.XMLHTTP");
                }

                document.querySelector('#idcodeprog').options.length = 1;
                
                var verificatligneog = document.querySelector('#nameligneheurid')
            .options[document.querySelector('#nameligneheurid').options.selectedIndex].value;

                var verif = verificatligneog.split('/');
                var seltcde = verif[0];
                var seltcde1 = verif[1];
                var seltcde2 = verif[2];
                var gdepart = document.querySelector('#bustop').value;
                var dt = document.querySelector('#iddatedeparts').value;
                httpInfosinfobusidprog.open('GET', window.location.origin + `${APP_ROOT}/Programmes/verifcodeprogramme/${gdepart}/${seltcde2}/${dt}`, true);
                httpInfosinfobusidprog.onload = () => 
                {
                    const infosbuprog = JSON.parse(httpInfosinfobusidprog.responseText);
                    
                            if (Object.entries(infosbuprog).length >= 1) 
                            {
                                for (let key in Object.entries(infosbuprog)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosbuprog[key].depart_code}`;
                                    opt.innerHTML = `${infosbuprog[key].depart_code}`;

                                    document.querySelector('#idcodeprog').add(opt);
                                   
                                }

                            } else {
                                document.querySelector('#idcodeprog').options.length = 1;
                            }
                    
                        
                };
                httpInfosinfobusidprog.setRequestHeader('Content-Type', 'application/json');
                httpInfosinfobusidprog.send();       
            };
           let prinfchauf = document.querySelector('#prtyppersoid');
            if (prinfchauf !== null)
            prinfchauf.onchange = () => 
            {
                document.querySelector('#pridchauf').options.length = 1;
                const prchauffs = document.querySelector('#prtyppersoid')
                    .options[document.querySelector('#prtyppersoid').options.selectedIndex].value;

               if(prchauffs === 'chauffeur')
               {
                    let httpInfosinfochaufpr;
                    if (window.XMLHttpRequest) {
                        httpInfosinfochaufpr = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfosinfochaufpr = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                
                    httpInfosinfochaufpr.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifpersonne/${prchauffs}`, true);
                    httpInfosinfochaufpr.onload = () => {
                        const resultchauffspr = JSON.parse(httpInfosinfochaufpr.responseText);
                        
                            if (Object.entries(resultchauffspr).length >= 1) 
                            {
                                for (let key in Object.entries(resultchauffspr)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${resultchauffspr[key].nomprenom_perso}`;
                                        opt.innerHTML = `${resultchauffspr[key].nomprenom_perso}`;
                                        document.querySelector('#pridchauf').add(opt);
                                    }
                            } else {
                                document.querySelector('#pridchauf').options.length = 1;
                            }
                            
                        
                    };
                    httpInfosinfochaufpr.setRequestHeader('Content-Type', 'application/json');
                    httpInfosinfochaufpr.send();   
                }
                if(prchauffs === 'autrepersonnel')
                {
                    let httpInfosinfopersopr;
                    if (window.XMLHttpRequest) {
                        httpInfosinfopersopr = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfosinfopersopr = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                    
                    httpInfosinfopersopr.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifclient/${prchauffs}`, true);
                    httpInfosinfopersopr.onload = () => {
                        const resultpersopr = JSON.parse(httpInfosinfopersopr.responseText);
                        
                            if (Object.entries(resultpersopr).length >= 1) 
                            {
                                for (let key in Object.entries(resultpersopr)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${resultpersopr[key].nom_client} ${resultpersopr[key].prenom_client}`;
                                        opt.innerHTML = `${resultpersopr[key].nom_client} ${resultpersopr[key].prenom_client}`;
                                        document.querySelector('#pridchauf').add(opt);
                                    }
                            } else {
                                document.querySelector('#pridchauf').options.length = 1;
                            }
                            
                        
                    };
                    httpInfosinfopersopr.setRequestHeader('Content-Type', 'application/json');
                    httpInfosinfopersopr.send();   
                }
                
            };

            let prinfconvoi = document.querySelector('#prtyppersoid1');
            if (prinfconvoi !== null)
            prinfconvoi.onchange = () => 
            {
                document.querySelector('#pridconvoi').options.length = 1;
                const prconvois = document.querySelector('#prtyppersoid1')
                    .options[document.querySelector('#prtyppersoid1').options.selectedIndex].value;


                if(prconvois === 'convoyeur')
                {
                    let httpInfosinfoconvpr;
                    if (window.XMLHttpRequest) {
                        httpInfosinfoconvpr = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfosinfoconvpr = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                
                    httpInfosinfoconvpr.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifconvoi/${prconvois}`, true);
                    httpInfosinfoconvpr.onload = () => {
                        const resultconvpr = JSON.parse(httpInfosinfoconvpr.responseText);
                        
                            if (Object.entries(resultconvpr).length >= 1) 
                            {
                                for (let key in Object.entries(resultconvpr)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${resultconvpr[key].nomprenom_perso}`;
                                        opt.innerHTML = `${resultconvpr[key].nomprenom_perso}`;
                                        document.querySelector('#pridconvoi').add(opt);
                                    }
                            } else {
                                document.querySelector('#pridconvoi').options.length = 1;
                            }
                            
                        
                    };
                    httpInfosinfoconvpr.setRequestHeader('Content-Type', 'application/json');
                    httpInfosinfoconvpr.send();   
                }
                if(prconvois === 'autrepersonnel')
                {
                    let httpInfosinfopersospr;
                    if (window.XMLHttpRequest) {
                        httpInfosinfopersospr = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfosinfopersospr = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                
                    httpInfosinfopersospr.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifclient/${prconvois}`, true);
                    httpInfosinfopersospr.onload = () => {
                        const resultpersospr = JSON.parse(httpInfosinfopersospr.responseText);
                        
                            if (Object.entries(resultpersospr).length >= 1) 
                            {
                                for (let key in Object.entries(resultpersospr)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${resultpersospr[key].nom_client} ${resultpersospr[key].prenom_client}`;
                                        opt.innerHTML = `${resultpersospr[key].nom_client} ${resultpersospr[key].prenom_client}`;
                                        document.querySelector('#pridconvoi').add(opt);
                                    }
                            } else {
                                document.querySelector('#pridconvoi').options.length = 1;
                            }
                            
                        
                    };
                    httpInfosinfopersospr.setRequestHeader('Content-Type', 'application/json');
                    httpInfosinfopersospr.send();   
                }
            };
        e.onclick = function (){
            let listeFormpr = document.querySelector('#progForm');
            listeFormpr.setAttribute('action', `${APP_ROOT}/Programmes/add/${e.dataset.cle_compagnie}`);
        }

    })
});