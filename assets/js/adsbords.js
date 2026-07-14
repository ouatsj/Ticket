document.addEventListener('DOMContentLoaded', () => {
   

    document.querySelectorAll('.adsbords').forEach(function (e) 
    {
        document.querySelector('h3#bordsTitlebg').innerHTML = `TIRAGE DE SUIVI`;

        function loadProgrammesBord() {
            var selectLigne = document.querySelector('#deptscouridlignebg');
            var selectDate = document.querySelector('#courdeptchoisirdatebg');
            var selectProg = document.querySelector('#courdeptidprogbg');
            if (!selectLigne || !selectDate || !selectProg) {
                return;
            }

            var ligne = parseLigneOption(selectLigne.options[selectLigne.selectedIndex].value);
            var verifidate = selectDate.value;
            selectProg.options.length = 1;

            if (!ligne.ident || !verifidate) {
                return;
            }

            var httpInfoprog = new XMLHttpRequest();
            httpInfoprog.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifprogramm/${encodeURIComponent(ligne.ident)}/${verifidate}`, true);
            httpInfoprog.onload = function () {
                var resultp;
                try {
                    resultp = JSON.parse(httpInfoprog.responseText);
                } catch (err) {
                    return;
                }

                if (!resultp || !resultp.length) {
                    selectProg.options.length = 1;
                    return;
                }

                resultp.forEach(function (item) {
                    var opt = document.createElement('option');
                    opt.value = `${item.code_progr}/${item.heure}/${item.id_ligneheure}/${item.depart_code}`;
                    opt.innerHTML = `${item.code_progr}/${item.heure}`;
                    selectProg.add(opt);
                });
            };
            httpInfoprog.send();
        }

        let arcourr = document.querySelector('#deptscouridlignebg');
            if (arcourr !== null)
            arcourr.onchange = () => {
                
                document.querySelector('#courdeptidprogbg').options.length = 1;
                document.querySelector('#courdeptquartieridbg').options.length = 1;
                const lidlignecr = document.querySelector('#deptscouridlignebg')
                .options[document.querySelector('#deptscouridlignebg').options.selectedIndex].value;
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
                        document.querySelector('#courdeptquartieridbg').options.length = 1;
                    }
                    else{
                        if (Object.entries(courquar).length >= 1) {
                                        
                            for (let key in Object.entries(courquar)) {
                                let opt = document.createElement('option');
                                opt.value = `${courquar[key].nom_quartier}`;
                                opt.innerHTML = `${courquar[key].nom_quartier}/${courquar[key].code_quart}`;
                                document.querySelector('#courdeptquartieridbg').add(opt);
                            }
                        } else {
                            document.querySelector('#courdeptquartieridbg').options.length = 1;
                        }
                    }
                    

                };
                httptypequartr.setRequestHeader('Content-Type', 'application/json');
                httptypequartr.send();
                loadProgrammesBord();
            };
            let infoligne = document.querySelector('#courdeptchoisirdatebg');
            if (infoligne !== null)
            infoligne.onchange = () => {
                loadProgrammesBord();
                                     
            };
                    let infchaufbords = document.querySelector('#courstyppersoidbg');
                    if (infchaufbords !== null)
                    infchaufbords.onchange = () => 
                    {
                        document.querySelector('#coursidchaufbg').options.length = 1;
                        const chauffesbords = document.querySelector('#courstyppersoidbg')
                            .options[document.querySelector('#courstyppersoidbg').options.selectedIndex].value;

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
                                                document.querySelector('#coursidchaufbg').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#coursidchaufbg').options.length = 1;
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
                                                document.querySelector('#coursidchaufbg').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#coursidchaufbg').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfopersobords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfopersobords.send();   
                        }
                        
                    };

                    let infconvoibords = document.querySelector('#courstyppersoid1bg');
                    if (infconvoibords !== null)
                    infconvoibords.onchange = () => 
                    {
                        document.querySelector('#couridconvoibg').options.length = 1;
                        const convoisbords = document.querySelector('#courstyppersoid1bg')
                            .options[document.querySelector('#courstyppersoid1bg').options.selectedIndex].value;

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
                                                document.querySelector('#couridconvoibg').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#couridconvoibg').options.length = 1;
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
                                                document.querySelector('#couridconvoibg').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#couridconvoibg').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfopersosbords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfopersosbords.send();   
                        }
                    };
        e.onclick = function (){
            let bordesForm = document.querySelector('#bordesFormbg');
            bordesForm.setAttribute('action', `${APP_ROOT}/Rapport/listesbagages/${e.dataset.cle_compagnie}`);
        }

    })
});