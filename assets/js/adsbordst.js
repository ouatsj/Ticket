document.addEventListener('DOMContentLoaded', () => {
   

    document.querySelectorAll('.adsbordst').forEach(function (e) 
    {
        document.querySelector('h3#bordsTitlebgt').innerHTML = `TIRAGE DE SUIVI TPE`;

        let arcourr = document.querySelector('#deptscouridlignebgt');
            if (arcourr !== null)
            arcourr.onchange = () => {
                
                document.querySelector('#courdeptidprogbgt').options.length = 1;
                document.querySelector('#courdeptquartieridbgt').options.length = 1;
                const lidlignecr = document.querySelector('#deptscouridlignebgt')
                .options[document.querySelector('#deptscouridlignebgt').options.selectedIndex].value;
                var lidlignecr1 = lidlignecr.split('/');
                var lidlignecr2 = lidlignecr1[0];
                var qart = lidlignecr2.split('-');
                var lidlignecr3 = qart[0];
                var lidlignecr4 = qart[1];
                let httptypequartr;
                httptypequartr = new XMLHttpRequest();
                
                httptypequartr.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifquart/${lidlignecr4}`, true);
                httptypequartr.onload = () => 
                {
                    const courquar = JSON.parse(httptypequartr.responseText);
                    if (courquar == '') {
                        document.querySelector('#courdeptquartieridbgt').options.length = 1;
                    }
                    else{
                        if (Object.entries(courquar).length >= 1) {
                                        
                            for (let key in Object.entries(courquar)) {
                                let opt = document.createElement('option');
                                opt.value = `${courquar[key].nom_quartier}`;
                                opt.innerHTML = `${courquar[key].nom_quartier}/${courquar[key].code_quart}`;
                                document.querySelector('#courdeptquartieridbgt').add(opt);
                            }
                        } else {
                            document.querySelector('#courdeptquartieridbgt').options.length = 1;
                        }
                    }
                    

                };
                httptypequartr.setRequestHeader('Content-Type', 'application/json');
                httptypequartr.send();
            };
            let infoligne = document.querySelector('#courdeptchoisirdatebgt');
            if (infoligne !== null)
            infoligne.onchange = () => {
                let httpInfoprog;
                if (window.XMLHttpRequest) {
                    httpInfoprog = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfoprog = new ActiveXObject("Microsoft.XMLHTTP");
                }
                const lidligne = document.querySelector('#deptscouridlignebgt')
                .options[document.querySelector('#deptscouridlignebgt').options.selectedIndex].value;
                var lidligne1 = lidligne.split('/');
                var lidligne2 = lidligne1[0];
                var lidligne3 = lidligne1[1];
                var verifidate = document.querySelector('#courdeptchoisirdatebgt').value;
                httpInfoprog.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifprogramm/${lidligne2}/${verifidate}`, true);
                httpInfoprog.onload = () => {
                    const resultp = JSON.parse(httpInfoprog.responseText);
                    if(resultp == null){

                        
                    
                    } else {
                        if (Object.entries(resultp).length >= 1) 
                        {
                           
                            for (let key in Object.entries(resultp)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resultp[key].code_progr}/${resultp[key].heure}/${resultp[key].id_ligneheure}/${resultp[key].depart_code}`;
                                    opt.innerHTML = `${resultp[key].code_progr}/${resultp[key].heure}`;
                                    document.querySelector('#courdeptidprogbgt').add(opt);
                                }
                        } else {
                            document.querySelector('#courdeptidprogbgt').options.length = 1;
                        }
                        
                    }
                };
                httpInfoprog.setRequestHeader('Content-Type', 'application/json');
                httpInfoprog.send();
                                     
            };
                    let infchaufbords = document.querySelector('#courstyppersoidbgt');
                    if (infchaufbords !== null)
                    infchaufbords.onchange = () => 
                    {
                        document.querySelector('#coursidchaufbgt').options.length = 1;
                        const chauffesbords = document.querySelector('#courstyppersoidbgt')
                            .options[document.querySelector('#courstyppersoidbgt').options.selectedIndex].value;

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
                                                document.querySelector('#coursidchaufbgt').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#coursidchaufbgt').options.length = 1;
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
                                                document.querySelector('#coursidchaufbgt').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#coursidchaufbgt').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfopersobords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfopersobords.send();   
                        }
                        
                    };

                    let infconvoibords = document.querySelector('#courstyppersoid1bgt');
                    if (infconvoibords !== null)
                    infconvoibords.onchange = () => 
                    {
                        document.querySelector('#couridconvoibgt').options.length = 1;
                        const convoisbords = document.querySelector('#courstyppersoid1bgt')
                            .options[document.querySelector('#courstyppersoid1bgt').options.selectedIndex].value;

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
                                                document.querySelector('#couridconvoibgt').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#couridconvoibgt').options.length = 1;
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
                                                document.querySelector('#couridconvoibgt').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#couridconvoibgt').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfopersosbords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfopersosbords.send();   
                        }
                    };
        e.onclick = function (){
            let bordesForm = document.querySelector('#bordesFormbgt');
            
            bordesForm.setAttribute('action', `${APP_ROOT}/Historique_Passagers/listesbagagestpe/${e.dataset.cle_compagnie}`);
        }

    })
});