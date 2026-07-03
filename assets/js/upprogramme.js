document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.upprogramme').forEach(function (e) 
    {
        
            e.onclick = function () {
            let mtaFormup = document.querySelector('#formprogup');
            mtaFormup.setAttribute('action', `${APP_ROOT}/Programmes/updateprogramme/${e.dataset.cle_compagnie}/${e.dataset.codebus}`);
            document.querySelector('h3#Titleprogbus').innerHTML = `MODIFICATION DES PROGRAMMES DE BUS : ${e.dataset.codebus}`;
            $('#idcategup').val(`${e.dataset.categorie}`);
            $('#busidup').val(`${e.dataset.busimat}`);
            $('#lignedepbusidup').val(`${e.dataset.lignebus}`);
            $('#ligneheureupid').val(`${e.dataset.heurelignebus}`);
            $('#datedepartsidup').val(`${e.dataset.pdatebus}`);
            $('#upchauff').val(`${e.dataset.chauff}`);
            $('#upconvoyeur').val(`${e.dataset.convo}`);

        }

            let prinfligne = document.querySelector('#lignedepbusidup');
                if (prinfligne !== null)
                    prinfligne.onchange = () => 
                    {
                        let httpInfosinfobusidpr;
                        if (window.XMLHttpRequest) {
                            httpInfosinfobusidpr = new XMLHttpRequest();
                        } else if (window.ActiveXObject) {
                            httpInfosinfobusidpr = new ActiveXObject("Microsoft.XMLHTTP");
                        }

                        document.querySelector('#proghup').options.length = 1;
                        
                        var verificatligne = document.querySelector('#lignedepbusidup').value;
                        
                        httpInfosinfobusidpr.open('GET', window.location.origin + `${APP_ROOT}/Programmes/verifieligneheure/${verificatligne}`, true);
                        httpInfosinfobusidpr.onload = () => 
                        {
                            const infosbupr = JSON.parse(httpInfosinfobusidpr.responseText);
                            
                                    if (Object.entries(infosbupr).length >= 1) 
                                    {
                                        for (let key in Object.entries(infosbupr)) {
                                            let opt = document.createElement('option');
                                            opt.value = `${infosbupr[key].id_ligneheure}/${infosbupr[key].ligne_id}/${infosbupr.code_gadest}/${infosbupr.heure}`;
                                            opt.innerHTML = `${infosbupr[key].nom_ligne}/${infosbupr[key].heure}`;

                                            document.querySelector('#proghup').add(opt);
                                           
                                        }

                                    } else {
                                        document.querySelector('#proghup').options.length = 1;
                                    }
                            
                                
                        };
                        httpInfosinfobusidpr.setRequestHeader('Content-Type', 'application/json');
                        httpInfosinfobusidpr.send();       
                    };
                   let prinfchauf = document.querySelector('#typpersoidup');
                    if (prinfchauf !== null)
                    prinfchauf.onchange = () => 
                    {
                        document.querySelector('#idchaufup').options.length = 1;
                        document.querySelector('#upchauff').value = "";
                        const prchauffs = document.querySelector('#typpersoidup')
                            .options[document.querySelector('#typpersoidup').options.selectedIndex].value;

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
                                    document.querySelector('#upchauff').value = "";
                                    if (Object.entries(resultchauffspr).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultchauffspr)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultchauffspr[key].nomprenom_perso}`;
                                                opt.innerHTML = `${resultchauffspr[key].nomprenom_perso}`;
                                                document.querySelector('#idchaufup').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#idchaufup').options.length = 1;
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
                                    document.querySelector('#upchauff').value = "";
                                    if (Object.entries(resultpersopr).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultpersopr)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultpersopr[key].nom_client} ${resultpersopr[key].prenom_client}`;
                                                opt.innerHTML = `${resultpersopr[key].nom_client} ${resultpersopr[key].prenom_client}`;
                                                document.querySelector('#idchaufup').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#idchaufup').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfopersopr.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfopersopr.send();   
                        }
                        
                    };

                    let prinfconvoi = document.querySelector('#typpersoid1up');
                    if (prinfconvoi !== null)
                    prinfconvoi.onchange = () => 
                    {
                        document.querySelector('#idconvoiup').options.length = 1;
                        document.querySelector('#upconvoyeur').value = "";
                        const prconvois = document.querySelector('#typpersoid1up')
                            .options[document.querySelector('#typpersoid1up').options.selectedIndex].value;


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
                                document.querySelector('#upconvoyeur').value = "";
                                    if (Object.entries(resultconvpr).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultconvpr)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultconvpr[key].nomprenom_perso}`;
                                                opt.innerHTML = `${resultconvpr[key].nomprenom_perso}`;
                                                document.querySelector('#idconvoiup').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#idconvoiup').options.length = 1;
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
                                    document.querySelector('#upconvoyeur').value = "";
                                    if (Object.entries(resultpersospr).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultpersospr)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultpersospr[key].nom_client} ${resultpersospr[key].prenom_client}`;
                                                opt.innerHTML = `${resultpersospr[key].nom_client} ${resultpersospr[key].prenom_client}`;
                                                document.querySelector('#idconvoiup').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#idconvoiup').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfopersospr.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfopersospr.send();   
                        }
                    };

    })
});