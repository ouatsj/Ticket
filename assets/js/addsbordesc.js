document.addEventListener('DOMContentLoaded', () => {
   

    document.querySelectorAll('.addsbordesc').forEach(function (e) 
    {
        document.querySelector('h3#bordsTitleesc').innerHTML = `TIRAGE BORDEREAU PAR LIGNE`;

        let arcourr = document.querySelector('#deptscouridligneesc');
            if (arcourr !== null)
            arcourr.onchange = () => {
                
                document.querySelector('#courdeptidprogesc').options.length = 1;
                document.querySelector('#courdeptquartieridesc').options.length = 1;
                const lidlignecr = document.querySelector('#deptscouridligneesc')
                .options[document.querySelector('#deptscouridligneesc').options.selectedIndex].value;
                var ligne = parseLigneOption(lidlignecr);
                if (!ligne.gareDest) {
                    return;
                }
                let httptypequartr;
                httptypequartr = new XMLHttpRequest();
                
                httptypequartr.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifquart/${encodeURIComponent(ligne.gareDest)}`, true);
                httptypequartr.onload = () => 
                {
                    const courquar = JSON.parse(httptypequartr.responseText);
                    if (courquar == '') {
                        document.querySelector('#courdeptquartieridesc').options.length = 1;
                    }
                    else{
                        if (Object.entries(courquar).length >= 1) {
                                        
                            for (let key in Object.entries(courquar)) {
                                let opt = document.createElement('option');
                                opt.value = `${courquar[key].nom_quartier}`;
                                opt.innerHTML = `${courquar[key].nom_quartier}/${courquar[key].code_quart}`;
                                document.querySelector('#courdeptquartieridesc').add(opt);
                            }
                        } else {
                            document.querySelector('#courdeptquartieridesc').options.length = 1;
                        }
                    }
                    

                };
                httptypequartr.setRequestHeader('Content-Type', 'application/json');
                httptypequartr.send();
            };
            let infoligne = document.querySelector('#courdeptchoisirdateesc');
            if (infoligne !== null)
            infoligne.onchange = () => {
                let httpInfoprog;
                if (window.XMLHttpRequest) {
                    httpInfoprog = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfoprog = new ActiveXObject("Microsoft.XMLHTTP");
                }

                
                const lidligne = document.querySelector('#deptscouridligneesc')
                .options[document.querySelector('#deptscouridligneesc').options.selectedIndex].value;
                var ligne = parseLigneOption(lidligne);
                var verifidate = document.querySelector('#courdeptchoisirdateesc').value;
                document.querySelector('#courdeptidprogesc').options.length = 1;
                if (!ligne.ident || !verifidate) {
                    return;
                }
                httpInfoprog.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheure/${encodeURIComponent(ligne.ident)}/${verifidate}`, true);
                httpInfoprog.onload = () => {
                    const resultp = JSON.parse(httpInfoprog.responseText);
                    if(resultp == null){
                    
                    } else {
                        if (Object.entries(resultp).length >= 1) 
                        {
                           
                            for (let key in Object.entries(resultp)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resultp[key].id_ligneheure}`;
                                    opt.innerHTML = `${resultp[key].heure}`;
                                    document.querySelector('#courdeptidprogesc').add(opt);
                                }
                        } else {
                            document.querySelector('#courdeptidprogesc').options.length = 1;
                        }
                        
                    }
                };
                httpInfoprog.setRequestHeader('Content-Type', 'application/json');
                httpInfoprog.send();
                                     
            };
                    let infchaufbords = document.querySelector('#courstyppersoidesc');
                    if (infchaufbords !== null)
                    infchaufbords.onchange = () => 
                    {
                        document.querySelector('#coursidchaufesc').options.length = 1;
                        const chauffesbords = document.querySelector('#courstyppersoidesc')
                            .options[document.querySelector('#courstyppersoidesc').options.selectedIndex].value;

                        if(chauffesbords === 'chauffeur')
                        {
                            let httpInfosinfochaufbords;
                            if (window.XMLHttpRequest) {
                                httpInfosinfochaufbords = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfochaufbords = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfochaufbords.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifpersonne/${chauffesbords}`, true);
                            httpInfosinfochaufbords.onload = () => {
                                const resultchauffsbords = JSON.parse(httpInfosinfochaufbords.responseText);
                                
                                    if (Object.entries(resultchauffsbords).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultchauffsbords)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultchauffsbords[key].nomprenom_perso}`;
                                                opt.innerHTML = `${resultchauffsbords[key].nomprenom_perso}`;
                                                document.querySelector('#coursidchaufesc').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#coursidchaufesc').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfochaufbords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfochaufbords.send();   
                        }
                        if(chauffesbords === 'autrepersonnel')
                        {
                            let httpInfosinfopersobords;
                            if (window.XMLHttpRequest) {
                                httpInfosinfopersobords = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfopersobords = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfopersobords.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifclient/${chauffesbords}`, true);
                            httpInfosinfopersobords.onload = () => {
                                const resultpersobords = JSON.parse(httpInfosinfopersobords.responseText);
                                
                                    if (Object.entries(resultpersobords).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultpersobords)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultpersobords[key].nom_client} ${resultpersobords[key].prenom_client}`;
                                                opt.innerHTML = `${resultpersobords[key].nom_client} ${resultpersobords[key].prenom_client}`;
                                                document.querySelector('#coursidchaufesc').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#coursidchaufesc').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfopersobords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfopersobords.send();   
                        }
                        
                    };

                    let infconvoibords = document.querySelector('#courstyppersoid1esc');
                    if (infconvoibords !== null)
                    infconvoibords.onchange = () => 
                    {
                        document.querySelector('#couridconvoiesc').options.length = 1;
                        const convoisbords = document.querySelector('#courstyppersoid1esc')
                            .options[document.querySelector('#courstyppersoid1esc').options.selectedIndex].value;

                        if(convoisbords === 'convoyeur')
                        {
                            let httpInfosinfoconvbords;
                            if (window.XMLHttpRequest) {
                                httpInfosinfoconvbords = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfoconvbords = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfoconvbords.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifconvoi/${convoisbords}`, true);
                            httpInfosinfoconvbords.onload = () => {
                                const resultconvbords = JSON.parse(httpInfosinfoconvbords.responseText);
                                
                                    if (Object.entries(resultconvbords).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultconvbords)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultconvbords[key].nomprenom_perso}`;
                                                opt.innerHTML = `${resultconvbords[key].nomprenom_perso}`;
                                                document.querySelector('#couridconvoiesc').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#couridconvoiesc').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfoconvbords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfoconvbords.send();   
                        }
                        if(convoisbords === 'autrepersonnel')
                        {
                            let httpInfosinfopersosbords;
                            if (window.XMLHttpRequest) {
                                httpInfosinfopersosbords = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfopersosbords = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfopersosbords.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifclient/${convoisbords}`, true);
                            httpInfosinfopersosbords.onload = () => {
                                const resultpersosbords = JSON.parse(httpInfosinfopersosbords.responseText);
                                
                                    if (Object.entries(resultpersosbords).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultpersosbords)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultpersosbords[key].nom_client} ${resultpersosbords[key].prenom_client}`;
                                                opt.innerHTML = `${resultpersosbords[key].nom_client} ${resultpersosbords[key].prenom_client}`;
                                                document.querySelector('#couridconvoiesc').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#couridconvoiesc').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfopersosbords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfopersosbords.send();   
                        }
                    };
        e.onclick = function (){
            let bordesForm = document.querySelector('#bordesFormesc');
            bordesForm.setAttribute('action', `${APP_ROOT}/Rapport/listescourriersesc/${e.dataset.cle_compagnie}`);
        }

    })
});