/* Bundle program — genere par scripts/build_module_bundles.php */
/* --- addtirage.js --- */
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
;
/* --- listetirage.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.listetirage').forEach(function (e) 
    {
        document.querySelector('h3#listeTitle').innerHTML = `LISTE DES PASSAGERS`;

            let infoligne = document.querySelector('#choisirdateliste');
            if (infoligne !== null)
                infoligne.onchange = () => {
                    let httpInfosheure;
                    if (window.XMLHttpRequest) {
                        httpInfosheure = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfosheure = new ActiveXObject("Microsoft.XMLHTTP");
                    }

                    document.querySelector('#choisirheureliste').options.length = 1;
                    var dt = document.querySelector('#choisirdateliste').value;
                    const idlignes = document.querySelector('#idligneliste')
                    .options[document.querySelector('#idligneliste').options.selectedIndex].value;
                    
                    httpInfosheure.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheure/${idlignes}/${dt}`, true);
                    httpInfosheure.onload = () => {
                        const resultheur = JSON.parse(httpInfosheure.responseText);
                        if(resultheur == null){

                            
                        
                        } else {
                            if (Object.entries(resultheur).length >= 1) 
                            {
                                
                                for (let key in Object.entries(resultheur)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${resultheur[key].heure_identif}/${resultheur[key].heure}`;
                                        opt.innerHTML = `${resultheur[key].heure}`;
                                        document.querySelector('#choisirheureliste').add(opt);
                                    }
                            } else {
                                document.querySelector('#choisirheureliste').options.length = 1;
                            }
                            
                        }
                    };
                    httpInfosheure.setRequestHeader('Content-Type', 'application/json');
                    httpInfosheure.send();
                                         
            };
        
        
        e.onclick = function () {
        let Formliste = document.querySelector('#Formliste');
        Formliste.setAttribute('action', `${APP_ROOT}/Ticket/listechefguichet/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- addvoir.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addvoir').forEach(function (e) 
    {
        document.querySelector('h3#lisTitle').innerHTML = `Liste des passagers`;

              //heure
            let infoligne = document.querySelector('#idlign');
            if (infoligne !== null)
                infoligne.onchange = () => {
                    let httpInfoprog;
                    if (window.XMLHttpRequest) {
                        httpInfoprog = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfoprog = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    const lidligne = document.querySelector('#idlign')
                    .options[document.querySelector('#idlign').options.selectedIndex].value;
                    var verifidate = document.querySelector('#choixdate').value;
                    httpInfoprog.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifprogramm/${lidligne}/${verifidate}`, true);
                    httpInfoprog.onload = () => {
                        const resultp = JSON.parse(httpInfoprog.responseText);
                        if(resultp == null){

                            
                        
                        } else {
                            if (Object.entries(resultp).length >= 1) 
                            {
                               
                                for (let key in Object.entries(resultp)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${resultp[key].depart_code}/${resultp[key].heure}/${resultp[key].heure_identif}`;
                                        opt.innerHTML = `${resultp[key].code_progr}/${resultp[key].heure}`;
                                        document.querySelector('#idprogr').add(opt);
                                    }
                            } else {
                                document.querySelector('#idprogr').options.length = 1;
                            }
                            
                        }
                    };
                    httpInfoprog.setRequestHeader('Content-Type', 'application/json');
                    httpInfoprog.send();
                                         
            };
        
        e.onclick = function () {
        
        let listForm = document.querySelector('#listForm');
        listForm.setAttribute('action', `${APP_ROOT}/Ticket/voirliste/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- addprogramme.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addprogramme').forEach(function (e) {
        
        e.onclick = function () {
            let prForm = document.querySelector('#formprog');
            document.querySelector('h3#Titleprog').innerHTML = `MODIFICATION DU PROGRAMME`;
            $('#idcateg').val(`${e.dataset.categorie}`);
            $('#typetaf').val(`${e.dataset.typtarif}`);
            $('#progh').val(`${e.dataset.eure}`);
            $('#ouotdebut').val(`${e.dataset.inter1}`);
            $('#ouotfin').val(`${e.dataset.inter2}`);
            $('#prodate').val(`${e.dataset.pdate}`);
            $('#ouotfinancien').val(`${e.dataset.categnbplace}`);
            
        
                let typcat = document.querySelector('#idcateg');
                
                if (typcat !== null) 
                typcat.onchange = () => 
                {
                    let Infoscateg;
                    if (window.XMLHttpRequest) {
                        Infoscateg = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        Infoscateg = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var categchoisi = document.querySelector('#idcateg')
                    .options[document.querySelector('#idcateg').options.selectedIndex].value;
                    Infoscateg.open('GET', window.location.origin + `${APP_ROOT}/categories/getnbrplace/${categchoisi}`, true);
                    Infoscateg.onload = () => {
                        const rescat = JSON.parse(Infoscateg.responseText);
        
                            if (Object.entries(rescat).length >= 1) {
                                  
                                    document.querySelector('#ouotfin').value = `${rescat.nbr_place}`;
                                    document.querySelector('#ouotafinnew').value = `${rescat.nbr_place}`;

                            } 
        
                        };
                        
                        Infoscateg.setRequestHeader('Content-Type', 'application/json');
                        Infoscateg.send();
    
                };
            prForm.setAttribute('action', `${APP_ROOT}/Programmes/edit_/${e.dataset.cle_compagnie}/${e.dataset.code}/${e.dataset.departcd}`);

        }
    })
});
;
/* --- addgprogramme.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addgprogramme').forEach(function (e) {
        
        e.onclick = function () {
            let prForm = document.querySelector('#formprog');
            document.querySelector('h3#Titleprog').innerHTML = `MODIFICATION DU PROGRAMME`;
            $('#idcateg').val(`${e.dataset.categorie}`);
            $('#typetaf').val(`${e.dataset.typtarif}`);
            $('#progh').val(`${e.dataset.eure}`);
            $('#ouotadebut').val(`${e.dataset.inter1}`);
            $('#ouotafin').val(`${e.dataset.inter2}`);
            $('#progdate').val(`${e.dataset.pdate}`);
            $('#ouotafinancien').val(`${e.dataset.categnbplace}`);
        
                let typcat = document.querySelector('#idcateg');
                
                if (typcat !== null) 
                typcat.onchange = () => 
                {
                    let Infoscateg;
                    if (window.XMLHttpRequest) {
                        Infoscateg = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        Infoscateg = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var categchoisi = document.querySelector('#idcateg')
                    .options[document.querySelector('#idcateg').options.selectedIndex].value;
                    Infoscateg.open('GET', window.location.origin + `${APP_ROOT}/categories/getnbrplace/${categchoisi}`, true);
                    Infoscateg.onload = () => {
                        const rescat = JSON.parse(Infoscateg.responseText);
        
                            if (Object.entries(rescat).length >= 1) {
                                  
                                    document.querySelector('#ouotafin').value = `${rescat.nbr_place}`;
                                    document.querySelector('#ouotafinnouveau').value = `${rescat.nbr_place}`;
                                
                            } 
        
                        };
                        
                        Infoscateg.setRequestHeader('Content-Type', 'application/json');
                        Infoscateg.send();
    
                };
            prForm.setAttribute('action', `${APP_ROOT}/Programmes/editgare_/${e.dataset.cle_compagnie}/${e.dataset.code}/${e.dataset.departcd}`);

        }
    })
});
;
/* --- upprogramme.js --- */
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
