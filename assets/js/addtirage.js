document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addtirage').forEach(function (e) 
    {
        document.querySelector('h3#ltTitle').innerHTML = `TIRAGE DE LISTE DES PASSAGERS`;

            /*let infobus = document.querySelector('#busid');
            if (infobus !== null)
            infobus.onkeyup = () => {
                document.querySelector('#choisirdate').value = '';
                document.querySelector('#idprog').options.length = 1;
                document.querySelector('#idligne').options.length = 1;
                document.querySelector('#idcategoriebus').options.length = 1;
                document.querySelector('#choisirheure').options.length = 1;
            };*/
                let infligne = document.querySelector('#idligne');
                if (infligne !== null)
                    infligne.onchange = () => 
                    {
                        let httpInfosinfobusid;
                        if (window.XMLHttpRequest) {
                            httpInfosinfobusid = new XMLHttpRequest();
                        } else if (window.ActiveXObject) {
                            httpInfosinfobusid = new ActiveXObject("Microsoft.XMLHTTP");
                        }

                        document.querySelector('#choisirheure').options.length = 1;

                        
                        var verificatbu = document.querySelector('#busid').value;
                        
                        httpInfosinfobusid.open('GET', window.location.origin + `${APP_ROOT}/bus/verificategorie/${verificatbu}`, true);
                        httpInfosinfobusid.onload = () => 
                        {
                            const infosbu = JSON.parse(httpInfosinfobusid.responseText);
                            
                                    if (Object.entries(infosbu).length >= 1) 
                                    {

                                                    let opt = document.createElement('option');
                                                    opt.value = `${infosbu.categorie}`;
                                                    opt.innerHTML = `${infosbu.categorie}`;
                                                    document.querySelector('#idcategoriebus').add(opt);
                                            
                                    } else {
                                        document.querySelector('#idcategoriebus').options.length = 1;
                                    }
                            
                                
                        };
                        httpInfosinfobusid.setRequestHeader('Content-Type', 'application/json');
                        httpInfosinfobusid.send();       
                    };

                    let infidcategoriebus = document.querySelector('#idcategoriebus');
                    if (infidcategoriebus !== null)
                    infidcategoriebus.onchange = () => {
                        let httpInfoheure;
                        if (window.XMLHttpRequest) {
                            httpInfoheure = new XMLHttpRequest();
                        } else if (window.ActiveXObject) {
                            httpInfoheure = new ActiveXObject("Microsoft.XMLHTTP");
                        }
                        var verifdate = document.querySelector('#choisirdate').value;
                        const idligne = document.querySelector('#idligne')
                        .options[document.querySelector('#idligne').options.selectedIndex].value;

                        httpInfoheure.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheure/${idligne}/${verifdate}`, true);
                        httpInfoheure.onload = () => {
                            const resultheure = JSON.parse(httpInfoheure.responseText);
                            if(resultheure == null){

                                
                            
                            } else {
                                if (Object.entries(resultheure).length >= 1) 
                                {
                                    for (let key in Object.entries(resultheure)) {
                                            let opt = document.createElement('option');
                                            opt.value = `${resultheure[key].heure_identif}/${resultheure[key].heure}`;
                                            opt.innerHTML = `${resultheure[key].heure}`;
                                            document.querySelector('#choisirheure').add(opt);
                                        }
                                } else {
                                    document.querySelector('#choisirheure').options.length = 1;
                                }
                                
                            }
                        };
                        httpInfoheure.setRequestHeader('Content-Type', 'application/json');
                        httpInfoheure.send();
                                            
                    };
                
                    let infaxe = document.querySelector('#choisirheure');
                    
                    if (infaxe !== null) 
                    infaxe.onchange = () => 
                    {
                    
                        let httpInfoscodedep;
                        if (window.XMLHttpRequest) {
                            httpInfoscodedep = new XMLHttpRequest();
                        } else if (window.ActiveXObject) {
                            httpInfoscodedep = new ActiveXObject("Microsoft.XMLHTTP");
                        }

                        var veridate = document.querySelector('#choisirdate').value;
                        var verifheu = document.querySelector('#choisirheure')
                                .options[document.querySelector('#choisirheure').options.selectedIndex].value;

                                var pcat = document.querySelector('#idcategoriebus')
                                .options[document.querySelector('#idcategoriebus').options.selectedIndex].value;
                        var post_verifheu = verifheu.split('/');
                        var heu = post_verifheu[0];
                        const idlign = document.querySelector('#idligne')
                        .options[document.querySelector('#idligne').options.selectedIndex].value;
                        httpInfoscodedep.open('GET', window.location.origin + `${APP_ROOT}/programmes/verificodeprogramme/${veridate}/${heu}/${pcat}/${idlign}`, true);
                        httpInfoscodedep.onload = () => 
                        {
                            const codeinfos = JSON.parse(httpInfoscodedep.responseText);
                            if(codeinfos == ''){
                                document.querySelector('#infosms').style.display = 'block';
                                document.querySelector('#erreurinfo').innerHTML = `Il n'y a pas de programme pour ce bus`;
                            } else 
                            {
                                if (Object.entries(codeinfos).length >= 1) 
                                {
                                        document.querySelector('#infosms').style.display = 'none';

                                        for (let key in Object.entries(codeinfos)) {

                                                let opt = document.createElement('option');
                                                opt.value = `${codeinfos[key].depart_code}/${codeinfos[key].code_progr}`;
                                                opt.innerHTML = `${codeinfos[key].depart_code}`;
                                                document.querySelector('#idprog').add(opt);
                                                console.debug(`${typeof codeinfos[key].depart_code}`, console.memory);
                                            }
                                } else {
                                    document.querySelector('#idprog').options.length = 1;
                                }
                            }   
                        };
                        httpInfoscodedep.setRequestHeader('Content-Type', 'application/json');
                        httpInfoscodedep.send();
                    };
                    
                   let infchauf = document.querySelector('#typpersoid');
                    if (infchauf !== null)
                    infchauf.onchange = () => 
                    {
                        document.querySelector('#idchauf').options.length = 1;
                        const chauffs = document.querySelector('#typpersoid')
                            .options[document.querySelector('#typpersoid').options.selectedIndex].value;

                       if(chauffs === 'chauffeur')
                       {
                            let httpInfosinfochauf;
                            if (window.XMLHttpRequest) {
                                httpInfosinfochauf = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfochauf = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfochauf.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifpersonne/${chauffs}`, true);
                            httpInfosinfochauf.onload = () => {
                                const resultchauffs = JSON.parse(httpInfosinfochauf.responseText);
                                
                                    if (Object.entries(resultchauffs).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultchauffs)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultchauffs[key].nomprenom_perso}`;
                                                opt.innerHTML = `${resultchauffs[key].nomprenom_perso}`;
                                                document.querySelector('#idchauf').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#idchauf').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfochauf.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfochauf.send();   
                        }
                        if(chauffs === 'autrepersonnel')
                        {
                            let httpInfosinfoperso;
                            if (window.XMLHttpRequest) {
                                httpInfosinfoperso = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfoperso = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                            
                            httpInfosinfoperso.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifclient/${chauffs}`, true);
                            httpInfosinfoperso.onload = () => {
                                const resultperso = JSON.parse(httpInfosinfoperso.responseText);
                                
                                    if (Object.entries(resultperso).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultperso)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultperso[key].nom_client} ${resultperso[key].prenom_client}`;
                                                opt.innerHTML = `${resultperso[key].nom_client} ${resultperso[key].prenom_client}`;
                                                document.querySelector('#idchauf').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#idchauf').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfoperso.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfoperso.send();   
                        }
                        
                    };

                    let infconvoi = document.querySelector('#typpersoid1');
                    if (infconvoi !== null)
                    infconvoi.onchange = () => 
                    {
                        document.querySelector('#idconvoi').options.length = 1;
                        const convois = document.querySelector('#typpersoid1')
                            .options[document.querySelector('#typpersoid1').options.selectedIndex].value;


                        if(convois === 'convoyeur')
                        {
                            let httpInfosinfoconv;
                            if (window.XMLHttpRequest) {
                                httpInfosinfoconv = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfoconv = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfoconv.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifconvoi/${convois}`, true);
                            httpInfosinfoconv.onload = () => {
                                const resultconv = JSON.parse(httpInfosinfoconv.responseText);
                                
                                    if (Object.entries(resultconv).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultconv)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultconv[key].nomprenom_perso}`;
                                                opt.innerHTML = `${resultconv[key].nomprenom_perso}`;
                                                document.querySelector('#idconvoi').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#idconvoi').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfoconv.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfoconv.send();   
                        }
                        if(convois === 'autrepersonnel')
                        {
                            let httpInfosinfopersos;
                            if (window.XMLHttpRequest) {
                                httpInfosinfopersos = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfopersos = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfopersos.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifclient/${convois}`, true);
                            httpInfosinfopersos.onload = () => {
                                const resultpersos = JSON.parse(httpInfosinfopersos.responseText);
                                
                                    if (Object.entries(resultpersos).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultpersos)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultpersos[key].nom_client} ${resultpersos[key].prenom_client}`;
                                                opt.innerHTML = `${resultpersos[key].nom_client} ${resultpersos[key].prenom_client}`;
                                                document.querySelector('#idconvoi').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#idconvoi').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfopersos.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfopersos.send();   
                        }
                    };
        e.onclick = function (){
            let listeForm = document.querySelector('#listeForm');
            listeForm.setAttribute('action', `${APP_ROOT}/Ticket/liste/${e.dataset.cle_compagnie}`);
        }

    })
});