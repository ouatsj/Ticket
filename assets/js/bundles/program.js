/* Bundle program — genere par scripts/build_module_bundles.php */
/* --- addtirage.js --- */
document.addEventListener('DOMContentLoaded', () => {

    function __tirageResetDownstreamFromLigne() {
        var heure = document.querySelector('#choisirheure');
        if (heure) heure.options.length = 1;
        var prog = document.querySelector('#idprog');
        if (prog) prog.options.length = 1;
        var cat = document.querySelector('#idcategoriebus');
        if (cat) cat.options.length = 1;
        var mess = document.querySelector('#infosms');
        if (mess) mess.style.display = 'none';
    }

    function __tirageLoadCategorieFromBus() {
        var httpInfosinfobusid;
        if (window.XMLHttpRequest) {
            httpInfosinfobusid = new XMLHttpRequest();
        } else if (window.ActiveXObject) {
            httpInfosinfobusid = new ActiveXObject('Microsoft.XMLHTTP');
        }

        var catSel = document.querySelector('#idcategoriebus');
        if (catSel) catSel.options.length = 1;

        var verificatbu = document.querySelector('#busid');
        if (!verificatbu || !String(verificatbu.value || '').trim()) {
            return;
        }

        httpInfosinfobusid.open(
            'GET',
            window.location.origin + `${APP_ROOT}/bus/verificategorie/${encodeURIComponent(verificatbu.value)}`,
            true
        );
        httpInfosinfobusid.onload = () => {
            try {
                const infosbu = JSON.parse(httpInfosinfobusid.responseText);
                if (infosbu && Object.entries(infosbu).length >= 1 && infosbu.categorie) {
                    let opt = document.createElement('option');
                    opt.value = `${infosbu.categorie}`;
                    opt.innerHTML = `${infosbu.categorie}`;
                    if (catSel) catSel.add(opt);
                }
            } catch (e) {}
        };
        httpInfosinfobusid.setRequestHeader('Content-Type', 'application/json');
        httpInfosinfobusid.send();
    }
    
    document.querySelectorAll('.addtirage').forEach(function (e) 
    {
        document.querySelector('h3#ltTitle').innerHTML = `TIRAGE DE LISTE DES PASSAGERS`;

                let infCompagnie = document.querySelector('#compagnie_tirage');
                if (infCompagnie !== null && !infCompagnie.dataset.tirageBound) {
                    infCompagnie.dataset.tirageBound = '1';
                    infCompagnie.addEventListener('change', function () {
                        __tirageResetDownstreamFromLigne();
                        var ligne = document.querySelector('#idligne');
                        if (ligne) ligne.value = '';
                    });
                }

                let infligne = document.querySelector('#idligne');
                if (infligne !== null && !infligne.dataset.tirageBound) {
                    infligne.dataset.tirageBound = '1';
                    infligne.addEventListener('change', function () {
                        __tirageResetDownstreamFromLigne();
                        if (infligne.value) {
                            __tirageLoadCategorieFromBus();
                        }
                    });
                }

                    let infidcategoriebus = document.querySelector('#idcategoriebus');
                    if (infidcategoriebus !== null && !infidcategoriebus.dataset.tirageBound) {
                    infidcategoriebus.dataset.tirageBound = '1';
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
                        var pcat = document.querySelector('#idcategoriebus')
                            .options[document.querySelector('#idcategoriebus').options.selectedIndex].value;

                        document.querySelector('#choisirheure').options.length = 1;
                        document.querySelector('#idprog').options.length = 1;
                        document.querySelector('#infosms').style.display = 'none';

                        if (!idligne || !verifdate) {
                            return;
                        }

                        var heureUrl = window.location.origin
                            + `${APP_ROOT}/programmes/verifheure/${encodeURIComponent(idligne)}/${encodeURIComponent(verifdate)}`;
                        if (pcat) {
                            heureUrl += `?cat=${encodeURIComponent(pcat)}`;
                        }
                        httpInfoheure.open('GET', heureUrl, true);
                        httpInfoheure.onload = () => {
                            try {
                                const resultheure = JSON.parse(httpInfoheure.responseText);
                                if (!resultheure || resultheure === '') {
                                    return;
                                }
                                if (Array.isArray(resultheure) && resultheure.length >= 1) {
                                    resultheure.forEach(function (row) {
                                        let opt = document.createElement('option');
                                        opt.value = `${row.heure_identif}/${row.heure}`;
                                        opt.innerHTML = `${row.heure}`;
                                        document.querySelector('#choisirheure').add(opt);
                                    });
                                } else {
                                    document.querySelector('#choisirheure').options.length = 1;
                                }
                            } catch (eH) {
                                document.querySelector('#choisirheure').options.length = 1;
                            }
                        };
                        httpInfoheure.setRequestHeader('Content-Type', 'application/json');
                        httpInfoheure.send();
                                            
                    };
                    }
                
                    let infaxe = document.querySelector('#choisirheure');
                    
                    if (infaxe !== null && !infaxe.dataset.tirageBound) {
                    infaxe.dataset.tirageBound = '1';
                    infaxe.onchange = () => 
                    {
                    
                        let httpInfoscodedep;
                        if (window.XMLHttpRequest) {
                            httpInfoscodedep = new XMLHttpRequest();
                        } else if (window.ActiveXObject) {
                            httpInfoscodedep = new ActiveXObject("Microsoft.XMLHTTP");
                        }

                        document.querySelector('#idprog').options.length = 1;
                        document.querySelector('#infosms').style.display = 'none';

                        var veridate = document.querySelector('#choisirdate').value;
                        var verifheu = document.querySelector('#choisirheure')
                                .options[document.querySelector('#choisirheure').options.selectedIndex].value;

                                var pcat = document.querySelector('#idcategoriebus')
                                .options[document.querySelector('#idcategoriebus').options.selectedIndex].value;
                        if (!verifheu) {
                            return;
                        }
                        var post_verifheu = verifheu.split('/');
                        var heu = post_verifheu[0];
                        const idlign = document.querySelector('#idligne')
                        .options[document.querySelector('#idligne').options.selectedIndex].value;
                        httpInfoscodedep.open(
                            'GET',
                            window.location.origin
                                + `${APP_ROOT}/programmes/verificodeprogramme`
                                + `?date=${encodeURIComponent(veridate)}`
                                + `&heure=${encodeURIComponent(heu)}`
                                + `&cat=${encodeURIComponent(pcat)}`
                                + `&ligne=${encodeURIComponent(idlign)}`,
                            true
                        );
                        httpInfoscodedep.onload = () => 
                        {
                            try {
                                const codeinfos = JSON.parse(httpInfoscodedep.responseText);
                                var vide = !codeinfos || codeinfos === ''
                                    || (Array.isArray(codeinfos) && codeinfos.length === 0);
                                if (vide) {
                                    document.querySelector('#infosms').style.display = 'block';
                                    document.querySelector('#erreurinfo').innerHTML = `Il n'y a pas de programme pour ce bus`;
                                    document.querySelector('#idprog').options.length = 1;
                                } else 
                                {
                                    document.querySelector('#infosms').style.display = 'none';
                                    if (Array.isArray(codeinfos) && codeinfos.length >= 1) {
                                        codeinfos.forEach(function (row) {
                                            let opt = document.createElement('option');
                                            opt.value = `${row.depart_code}/${row.code_progr}`;
                                            opt.innerHTML = `${row.depart_code}`;
                                            document.querySelector('#idprog').add(opt);
                                        });
                                    } else {
                                        document.querySelector('#idprog').options.length = 1;
                                    }
                                }
                            } catch (eC) {
                                document.querySelector('#infosms').style.display = 'block';
                                document.querySelector('#erreurinfo').innerHTML = `Impossible de charger les programmes`;
                                document.querySelector('#idprog').options.length = 1;
                            }
                        };
                        httpInfoscodedep.setRequestHeader('Content-Type', 'application/json');
                        httpInfoscodedep.send();
                    };
                    }
                    
                   let infchauf = document.querySelector('#typpersoid');
                    if (infchauf !== null && !infchauf.dataset.tirageBound) {
                    infchauf.dataset.tirageBound = '1';
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
                    }

                    let infconvoi = document.querySelector('#typpersoid1');
                    if (infconvoi !== null && !infconvoi.dataset.tirageBound) {
                    infconvoi.dataset.tirageBound = '1';
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
                    }

        e.onclick = function () {
            let listeForm = document.querySelector('#listeForm');
            if (!listeForm) return;
            listeForm.setAttribute('action', `${APP_ROOT}/Ticket/liste/${e.dataset.cle_compagnie}`);
            listeForm.setAttribute('method', 'post');
            listeForm.setAttribute('target', '_blank');
        };

    });

    var listeFormEl = document.querySelector('#listeForm');
    if (listeFormEl && !listeFormEl.dataset.tirageSubmitBound) {
        listeFormEl.dataset.tirageSubmitBound = '1';
        listeFormEl.addEventListener('submit', function (ev) {
            var action = listeFormEl.getAttribute('action') || '';
            if (!action || action === '' || action === window.location.href) {
                var btn = document.querySelector('.addtirage');
                if (btn && btn.dataset.cle_compagnie) {
                    listeFormEl.setAttribute(
                        'action',
                        `${APP_ROOT}/Ticket/liste/${btn.dataset.cle_compagnie}`
                    );
                    listeFormEl.setAttribute('method', 'post');
                    listeFormEl.setAttribute('target', '_blank');
                } else {
                    ev.preventDefault();
                    alert('Impossible de lancer le tirage : action du formulaire manquante.');
                    return false;
                }
            }
            var compagnie = document.querySelector('#compagnie_tirage');
            var ligne = document.querySelector('#idligne');
            if (compagnie && !compagnie.value) {
                ev.preventDefault();
                alert('Choisissez la compagnie d\'arrivée.');
                compagnie.focus();
                return false;
            }
            if (ligne && !ligne.value) {
                ev.preventDefault();
                alert('Choisissez la ligne.');
                ligne.focus();
                return false;
            }
            return true;
        });
    }
});

;
/* --- listetirage.js --- */
document.addEventListener('DOMContentLoaded', () => {

    function __listeResetHeures() {
        var heure = document.querySelector('#choisirheureliste');
        if (heure) heure.options.length = 1;
    }

    function __listeLoadHeures() {
        var heureSel = document.querySelector('#choisirheureliste');
        if (heureSel) heureSel.options.length = 1;
        var dtEl = document.querySelector('#choisirdateliste');
        var lgEl = document.querySelector('#idligneliste');
        if (!dtEl || !lgEl) return;
        var dt = dtEl.value;
        var idlignes = lgEl.value;
        if (!dt || !idlignes) return;

        var httpInfosheure;
        if (window.XMLHttpRequest) {
            httpInfosheure = new XMLHttpRequest();
        } else if (window.ActiveXObject) {
            httpInfosheure = new ActiveXObject('Microsoft.XMLHTTP');
        }
        httpInfosheure.open(
            'GET',
            window.location.origin + `${APP_ROOT}/programmes/verifheure/${encodeURIComponent(idlignes)}/${encodeURIComponent(dt)}`,
            true
        );
        httpInfosheure.onload = () => {
            try {
                const resultheur = JSON.parse(httpInfosheure.responseText);
                if (!resultheur || resultheur === '') return;
                if (Array.isArray(resultheur) && resultheur.length >= 1) {
                    resultheur.forEach(function (row) {
                        let opt = document.createElement('option');
                        opt.value = `${row.heure_identif}/${row.heure}`;
                        opt.innerHTML = `${row.heure}`;
                        if (heureSel) heureSel.add(opt);
                    });
                }
            } catch (err) {}
        };
        httpInfosheure.setRequestHeader('Content-Type', 'application/json');
        httpInfosheure.send();
    }
    
    document.querySelectorAll('.listetirage').forEach(function (e) 
    {
        document.querySelector('h3#listeTitle').innerHTML = `LISTE DES PASSAGERS`;

            let compagnieListe = document.querySelector('#compagnie_liste');
            if (compagnieListe !== null && !compagnieListe.dataset.listeBound) {
                compagnieListe.dataset.listeBound = '1';
                compagnieListe.addEventListener('change', function () {
                    var ligne = document.querySelector('#idligneliste');
                    if (ligne) ligne.value = '';
                    __listeResetHeures();
                });
            }

            let ligneListe = document.querySelector('#idligneliste');
            if (ligneListe !== null && !ligneListe.dataset.listeBound) {
                ligneListe.dataset.listeBound = '1';
                ligneListe.addEventListener('change', function () {
                    __listeLoadHeures();
                });
            }

            let infoligne = document.querySelector('#choisirdateliste');
            if (infoligne !== null && !infoligne.dataset.listeBound) {
                infoligne.dataset.listeBound = '1';
                infoligne.onchange = function () {
                    __listeLoadHeures();
                };
            }
        
        
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

            let compagnieVoir = document.querySelector('#compagnie_voir');
            if (compagnieVoir !== null && !compagnieVoir.dataset.voirBound) {
                compagnieVoir.dataset.voirBound = '1';
                compagnieVoir.addEventListener('change', function () {
                    var ligne = document.querySelector('#idlign');
                    if (ligne) ligne.value = '';
                    var prog = document.querySelector('#idprogr');
                    if (prog) prog.options.length = 1;
                });
            }

              //heure
            let infoligne = document.querySelector('#idlign');
            if (infoligne !== null && !infoligne.dataset.voirBound)
            {
                infoligne.dataset.voirBound = '1';
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
                    var progSel = document.querySelector('#idprogr');
                    if (progSel) progSel.options.length = 1;
                    if (!lidligne || !verifidate) return;
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
            }
        
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
            if (typeof window.__syncDepartCompagnie === 'function') {
                window.__syncDepartCompagnie('progh', e.dataset.eure);
            } else {
                $('#progh').val(`${e.dataset.eure}`);
            }
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
/* --- adprograme.js --- */
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
;
/* --- addgprogramme.js --- */
document.addEventListener('DOMContentLoaded', () => {

    function parseVentesSgs(raw) {
        var map = {};
        String(raw || '').split(',').forEach(function (part) {
            part = String(part || '').trim();
            if (!part) return;
            var bits = part.split(':');
            var id = String(bits[0] || '').trim();
            var nb = parseInt(bits[1], 10) || 0;
            if (id && nb > 0) map[id] = nb;
        });
        return map;
    }

    function applyVentesLock(box, ventes) {
        box.querySelectorAll('.js-sg-check').forEach(function (c) {
            var nb = ventes[String(c.value)] || 0;
            var badge = c.parentNode ? c.parentNode.querySelector('.js-sg-ventes') : null;
            var wrap = c.closest('.form-group') || c.parentNode;
            if (nb > 0) {
                c.setAttribute('data-locked', '1');
                c.checked = true;
                if (wrap) wrap.style.opacity = '0.65';
                if (badge) {
                    badge.style.display = '';
                    badge.textContent = ' — ' + nb + ' vente' + (nb > 1 ? 's' : '');
                }
            } else {
                c.setAttribute('data-locked', '0');
                if (wrap) wrap.style.opacity = '';
                if (badge) {
                    badge.style.display = 'none';
                    badge.textContent = '';
                }
            }
        });
    }

    function openEditProgramme(e) {
        var prForm = document.querySelector('#formprog');
        if (!prForm) return;

        var titleEl = document.querySelector('h3#Titleprog');
        if (titleEl) {
            titleEl.innerHTML = 'MODIFICATION DU PROGRAMME';
        }
        $('#idcateg').val(String(e.dataset.categorie || ''));
        $('#typetaf').val(String(e.dataset.typtarif || ''));
        if (typeof window.__syncDepartCompagnie === 'function') {
            window.__syncDepartCompagnie('progh', e.dataset.eure);
        } else {
            $('#progh').val(String(e.dataset.eure || ''));
        }
        $('#progdate').val(String(e.dataset.pdate || ''));
        var ouotAncien = document.querySelector('#ouotafinancien');
        var ouotNouveau = document.querySelector('#ouotafinnouveau');
        if (ouotAncien) ouotAncien.value = String(e.dataset.categnbplace || '');
        if (ouotNouveau) ouotNouveau.value = String(e.dataset.categnbplace || '');
        var portee = (e.dataset.porteeSgs || '').trim();
        var ids = portee ? portee.split(',').map(function (x) { return String(x).trim(); }).filter(Boolean) : [];
        var ventes = parseVentesSgs(e.dataset.ventesSgs);
        var box = document.querySelector('#portee_sousgares_box_edit');
        if (box) {
            var radioGare = box.querySelector('.js-scope-mode[value="gare"]');
            var radioSg = box.querySelector('.js-scope-mode[value="sousgare"]');
            var checks = box.querySelectorAll('.js-sg-check');
            applyVentesLock(box, ventes);
            if (ids.length === 0) {
                if (radioGare) radioGare.checked = true;
                checks.forEach(function (c) { c.checked = true; });
            } else {
                if (radioSg) radioSg.checked = true;
                checks.forEach(function (c) {
                    var locked = c.getAttribute('data-locked') === '1';
                    c.checked = locked || ids.indexOf(String(c.value)) !== -1;
                });
            }
            if (typeof window.__applyPorteeScopeMode === 'function') {
                window.__applyPorteeScopeMode(box);
            }
            if (ids.length > 0) {
                checks.forEach(function (c) {
                    var locked = c.getAttribute('data-locked') === '1';
                    c.checked = locked || ids.indexOf(String(c.value)) !== -1;
                    c.disabled = locked ? true : !(radioSg && radioSg.checked);
                });
            } else {
                checks.forEach(function (c) {
                    c.checked = true;
                    c.disabled = true;
                });
            }
        }

        if (window.ProgQuotaSieges && typeof window.ProgQuotaSieges.loadEditForForm === 'function') {
            var soldAttr = (e.dataset.siegesOccupes || '').split(',').map(function (x) {
                return parseInt(String(x).trim(), 10);
            }).filter(function (n) { return !isNaN(n) && n > 0; });
            window.ProgQuotaSieges.loadEditForForm(
                'formprog',
                e.dataset.code,
                e.dataset.cle_compagnie,
                e.dataset.inter1,
                e.dataset.inter2,
                e.dataset.categorie,
                soldAttr
            );
        }

        var typcat = document.querySelector('#idcateg');
        if (typcat !== null) {
            typcat.onchange = () => {
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
                        var ouotFinNouveau = document.querySelector('#ouotafinnouveau');
                        if (ouotFinNouveau) {
                            ouotFinNouveau.value = `${rescat.nbr_place}`;
                        }
                    }
                };
                Infoscateg.setRequestHeader('Content-Type', 'application/json');
                Infoscateg.send();
            };
        }
        prForm.setAttribute('action', `${APP_ROOT}/Programmes/editgare_/${e.dataset.cle_compagnie}/${e.dataset.code}/${e.dataset.departcd}`);
    }

    document.addEventListener('click', function (ev) {
        var btn = ev.target.closest ? ev.target.closest('.addgprogramme') : null;
        if (!btn) return;
        openEditProgramme(btn);
    });
});

;
/* --- prog_quota_sieges.js --- */
(function (global) {
    'use strict';

    function siteBase() {
        if (typeof global.__SITE_BASE === 'string' && global.__SITE_BASE) {
            return global.__SITE_BASE.replace(/\/$/, '');
        }
        var root = (typeof global.APP_ROOT !== 'undefined') ? String(global.APP_ROOT) : '';
        if (root && root.charAt(0) !== '/') {
            root = '/' + root;
        }
        return (global.location.origin + root).replace(/\/$/, '');
    }

    function sortedNums(checkboxes) {
        return checkboxes
            .filter(function (c) { return c.checked && !c.disabled; })
            .map(function (c) { return parseInt(c.value, 10); })
            .filter(function (n) { return !isNaN(n); })
            .sort(function (a, b) { return a - b; });
    }

    function isContiguous(nums) {
        if (!nums.length) {
            return false;
        }
        for (var i = 1; i < nums.length; i++) {
            if (nums[i] - nums[i - 1] > 1) {
                return false;
            }
        }
        return true;
    }

    function fetchJson(url) {
        return fetch(url, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(function (r) {
            return r.text().then(function (t) {
                try {
                    return JSON.parse(t);
                } catch (e) {
                    throw new Error('Réponse non JSON');
                }
            });
        });
    }

    function findCategSelect(block) {
        if (!block) {
            return null;
        }
        if (block.dataset.categSelect) {
            var byId = document.getElementById(block.dataset.categSelect);
            if (byId) {
                return byId;
            }
        }
        var form = block.closest('form');
        return form ? form.querySelector('select[name="categorie"]') : null;
    }

    function bindBlock(block) {
        if (!block) {
            return null;
        }
        if (block.getAttribute('data-quota-bound') !== '1') {
            block.setAttribute('data-quota-bound', '1');
        }

        var form = block.closest('form');
        var debutEl = block.querySelector('.js-quota-debut-field');
        var finEl = block.querySelector('.js-quota-fin-field');
        var grid = block.querySelector('.js-quota-sieges-grid');
        var summary = block.querySelector('.js-quota-summary');
        var libererFields = block.querySelector('.js-quota-liberer-fields');
        var bloqueFields = block.querySelector('.js-quota-bloque-fields');
        var hintEl = block.querySelector('.js-quota-hint');
        var bloqueAlert = block.querySelector('.js-quota-bloque-alert');
        var isEditBlock = block.getAttribute('data-quota-mode') === 'edit';

        var state = block._quotaState || {
            nbrPlace: 0,
            sold: {},
            blocked: {},
            tampon: {},
            reco: {},
            recoMode: false,
            rangeDebut: 0,
            rangeFin: 0,
            editMode: isEditBlock,
            reverting: false
        };
        if (!state.tampon) {
            state.tampon = {};
        }
        block._quotaState = state;
        if (isEditBlock) {
            state.editMode = true;
        }

        function setSummary(text) {
            if (summary) {
                summary.textContent = text;
            }
        }

        function setHint(text) {
            if (hintEl && text) {
                hintEl.textContent = text;
            }
        }

        function recoCount() {
            return Object.keys(state.reco).length;
        }

        function isRecoSeat(n) {
            return !!state.reco[String(n)];
        }

        /**
         * Libération : uniquement les sièges déjà vendus décochés
         * (en reconduction : seulement parmi les alloués).
         * Les trous libres → sieges_bloques[], pas sieges_liberer[].
         */
        function getToLiberate(checked) {
            if (!grid) {
                return [];
            }
            var out = [];
            grid.querySelectorAll('.js-quota-siege[data-sold="1"]').forEach(function (cb) {
                if (cb.disabled || cb.checked) {
                    return;
                }
                var n = parseInt(cb.value, 10);
                if (isNaN(n) || n <= 0) {
                    return;
                }
                if (state.recoMode && !isRecoSeat(n)) {
                    return;
                }
                out.push(n);
            });
            return out.sort(function (a, b) { return a - b; });
        }

        function quotaRange() {
            var d = debutEl ? parseInt(debutEl.value, 10) : 0;
            var f = finEl ? parseInt(finEl.value, 10) : 0;
            if (!(d > 0 && f >= d) && state.rangeDebut > 0 && state.rangeFin >= state.rangeDebut) {
                d = state.rangeDebut;
                f = state.rangeFin;
            }
            return { d: d, f: f };
        }

        function syncBloquesHidden() {
            if (!bloqueFields || !grid) {
                return;
            }
            bloqueFields.innerHTML = '';
            var range = quotaRange();
            var d = range.d;
            var f = range.f;
            // Ne poster que les trous dans [debut, fin] — hors intervalle = hors quota, pas en base.
            if (!(d > 0 && f >= d)) {
                return;
            }
            grid.querySelectorAll('.js-quota-siege').forEach(function (cb) {
                if (cb.checked || cb.disabled || cb.getAttribute('data-sold') === '1'
                    || cb.getAttribute('data-tampon') === '1') {
                    return;
                }
                var n = parseInt(cb.value, 10);
                if (isNaN(n) || n < d || n > f) {
                    return;
                }
                var inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'sieges_bloques[]';
                inp.value = String(n);
                bloqueFields.appendChild(inp);
            });
        }

        function setBloqueAlert(blockedN, tamponN) {
            if (!bloqueAlert) {
                return;
            }
            var parts = [];
            if (blockedN > 0) {
                parts.push(blockedN + ' siège(s) bloqué(s) hors vente (décochés volontairement).');
            }
            if (tamponN > 0) {
                parts.push(tamponN + ' siège(s) en tampon (vente guichet en cours, TTL 45 min).');
            }
            if (!parts.length) {
                bloqueAlert.classList.add('d-none');
                bloqueAlert.textContent = '';
                return;
            }
            bloqueAlert.classList.remove('d-none');
            bloqueAlert.textContent = parts.join(' ');
        }

        function syncLibererHidden(lib) {
            if (!libererFields) {
                return;
            }
            libererFields.innerHTML = '';
            (lib || []).forEach(function (n) {
                var inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'sieges_liberer[]';
                inp.value = String(n);
                libererFields.appendChild(inp);
            });
        }

        function syncHidden() {
            var boxes = grid ? Array.from(grid.querySelectorAll('.js-quota-siege')) : [];
            var nums = sortedNums(boxes);
            if (!nums.length) {
                syncLibererHidden([]);
                syncBloquesHidden();
                if (debutEl) debutEl.value = '';
                if (finEl) finEl.value = '';
                setSummary('Quota : —');
                if (typeof setBloqueAlert === 'function') {
                    setBloqueAlert(0, Object.keys(state.tampon || {}).length);
                }
                return false;
            }
            var lib = getToLiberate(nums);
            var d;
            var f;
            if (state.recoMode && state.rangeDebut > 0 && state.rangeFin >= state.rangeDebut) {
                // Reconduction : conserver la plage programme (vente filtrée par liste)
                d = state.rangeDebut;
                f = state.rangeFin;
            } else {
                d = nums[0];
                f = nums[nums.length - 1];
                lib.forEach(function (n) {
                    if (n < d) d = n;
                    if (n > f) f = n;
                });
            }
            if (debutEl) debutEl.value = String(d);
            if (finEl) finEl.value = String(f);
            syncLibererHidden(lib);
            syncBloquesHidden();
            var soldN = Object.keys(state.sold).length;
            var blockedN = 0;
            if (grid) {
                grid.querySelectorAll('.js-quota-siege').forEach(function (cb) {
                    var n = parseInt(cb.value, 10);
                    if (!cb.checked && !cb.disabled && cb.getAttribute('data-sold') !== '1'
                        && cb.getAttribute('data-tampon') !== '1'
                        && !isNaN(n) && n >= d && n <= f) {
                        blockedN++;
                    }
                });
            }
            var tamponN = Object.keys(state.tampon || {}).length;
            var parts = [];
            if (state.recoMode) {
                parts.push('Reconduit : ' + recoCount() + ' siège(s)');
                parts.push('cochés ' + nums.length);
            } else {
                parts.push('Quota : ' + d + ' → ' + f + ' (' + nums.length + ' siège(s))');
            }
            if (soldN > 0) {
                parts.push(soldN + ' vendu(s)');
            }
            if (tamponN > 0) {
                parts.push(tamponN + ' tampon(s)');
            }
            if (lib.length) {
                parts.push(lib.length + ' libéré(s) → revendable(s)');
            }
            if (blockedN > 0) {
                parts.push(blockedN + ' bloqué(s) → hors vente');
            }
            setSummary(parts.join(' · '));
            if (typeof setBloqueAlert === 'function') {
                setBloqueAlert(blockedN, tamponN);
            }
            return true;
        }

        function renderGrid(from, to, checkedFrom, checkedTo, sold, recoList, blockedList, tamponList) {
            if (!grid) {
                return;
            }
            state.sold = {};
            (sold || []).forEach(function (n) {
                var num = parseInt(n, 10);
                if (!isNaN(num) && num > 0) {
                    state.sold[String(num)] = true;
                }
            });
            state.blocked = {};
            (blockedList || []).forEach(function (n) {
                var num = parseInt(n, 10);
                if (!isNaN(num) && num > 0) {
                    state.blocked[String(num)] = true;
                }
            });
            state.tampon = {};
            (tamponList || []).forEach(function (n) {
                var num = parseInt(n, 10);
                if (!isNaN(num) && num > 0 && !state.sold[String(num)]) {
                    state.tampon[String(num)] = true;
                }
            });
            state.reco = {};
            state.recoMode = false;
            if (Array.isArray(recoList) && recoList.length) {
                state.recoMode = true;
                recoList.forEach(function (n) {
                    var num = parseInt(n, 10);
                    if (!isNaN(num) && num > 0) {
                        state.reco[String(num)] = true;
                    }
                });
            }

            var html = '';
            for (var n = from; n <= to; n++) {
                var isReco = state.recoMode ? isRecoSeat(n) : true;
                var isSold = !!state.sold[String(n)] && isReco;
                var isTampon = !isSold && !!state.tampon[String(n)];
                var checked;
                if (state.recoMode) {
                    // Reconduction : sièges reconduits cochés, sauf bloqués admin.
                    checked = isReco;
                } else {
                    checked = (n >= checkedFrom && n <= checkedTo) || isSold || isTampon;
                }
                // Toujours respecter les bloqués (édition + reconduction).
                if (state.blocked[String(n)] && !isSold && !isTampon) {
                    checked = false;
                }
                var disabled = state.recoMode && !isReco;
                var isBlocked = !checked && !isSold && !isTampon && !disabled
                    && !!state.blocked[String(n)];
                var wrapStyle;
                var labelExtra = '';
                if (isSold) {
                    wrapStyle = 'background:#fff3cd;border:1px solid #ffc107;border-radius:4px;padding:4px 6px;display:block;';
                    labelExtra = ' <span style="color:#856404;font-size:11px;font-weight:600;">VENDU</span>';
                } else if (isTampon) {
                    wrapStyle = 'background:#ffe5d0;border:1px solid #fd7e14;border-radius:4px;padding:4px 6px;display:block;';
                    labelExtra = ' <span style="color:#9a3412;font-size:11px;font-weight:600;">TAMPON</span>';
                } else if (isBlocked) {
                    wrapStyle = 'background:#e2e3e5;border:1px solid #6c757d;border-radius:4px;padding:4px 6px;display:block;opacity:0.75;';
                    labelExtra = ' <span style="color:#495057;font-size:11px;font-weight:600;">BLOQUÉ</span>';
                } else if (state.recoMode && isReco) {
                    wrapStyle = 'background:#d1ecf1;border:1px solid #17a2b8;border-radius:4px;padding:4px 6px;display:block;';
                    labelExtra = ' <span style="color:#0c5460;font-size:11px;font-weight:600;">RECONDUIT</span>';
                } else if (disabled) {
                    wrapStyle = 'background:#f8f9fa;border:1px solid #dee2e6;border-radius:4px;padding:4px 6px;display:block;opacity:0.55;';
                    labelExtra = ' <span style="color:#6c757d;font-size:10px;">hors</span>';
                } else {
                    wrapStyle = 'padding:4px 6px;display:block;';
                }
                html += '<div class="col-3 col-md-2 mb-1"><label style="font-weight:400;'
                    + (disabled ? 'cursor:not-allowed;' : 'cursor:pointer;')
                    + wrapStyle + '">'
                    + '<input type="checkbox" class="js-quota-siege'
                    + (isSold ? ' js-quota-siege-sold' : '')
                    + (isTampon ? ' js-quota-siege-tampon' : '')
                    + (isReco && state.recoMode ? ' js-quota-siege-reco' : '')
                    + '" value="' + n + '"'
                    + (checked ? ' checked' : '')
                    + (disabled ? ' disabled' : '')
                    + (isSold ? ' data-sold="1"' : '')
                    + (isTampon ? ' data-tampon="1"' : '')
                    + (state.recoMode && isReco ? ' data-reco="1"' : '')
                    + '> <strong>' + n + '</strong>'
                    + labelExtra
                    + '</label></div>';
            }
            grid.innerHTML = html;
            grid.querySelectorAll('.js-quota-siege:not([disabled])').forEach(function (cb) {
                cb.addEventListener('change', onToggle);
            });
            if (state.recoMode) {
                setHint('Fond bleu = siège reconduit (' + recoCount() + '). Jaune = déjà vendu. Orange = tampon. Gris = hors reconduction.');
            }
            syncHidden();
        }

        function renderAll(nbrPlace, rangeDebut, rangeFin, sold, recoList, blockedList, tamponList) {
            state.nbrPlace = nbrPlace;
            var d = rangeDebut > 0 ? rangeDebut : 1;
            var f = rangeFin > 0 ? rangeFin : nbrPlace;
            if (f > nbrPlace) {
                f = nbrPlace;
            }
            if (d < 1) {
                d = 1;
            }
            state.rangeDebut = d;
            state.rangeFin = f;
            renderGrid(1, nbrPlace, d, f, sold, recoList || null, blockedList || null, tamponList || null);
        }

        function onToggle(ev) {
            if (state.reverting) {
                return;
            }
            var cb = ev.target;
            if (cb.disabled) {
                return;
            }
            var boxes = Array.from(grid.querySelectorAll('.js-quota-siege'));
            var nums = sortedNums(boxes);

            if (!nums.length) {
                state.reverting = true;
                cb.checked = true;
                state.reverting = false;
                setSummary('Au moins un siège requis.');
                syncLibererHidden([]);
                syncBloquesHidden();
                return;
            }

            // Édition / reconduction : trous autorisés (= libération ou blocage)
            if (state.editMode || state.recoMode) {
                if (cb.getAttribute('data-sold') === '1' && !cb.checked) {
                    var lab = cb.closest('label');
                    if (lab) {
                        lab.style.background = '#f8d7da';
                        lab.style.borderColor = '#dc3545';
                        var tag = lab.querySelector('span');
                        if (tag) {
                            tag.textContent = 'LIBÉRÉ';
                            tag.style.color = '#721c24';
                        }
                    }
                } else if (cb.getAttribute('data-sold') === '1' && cb.checked) {
                    var lab2 = cb.closest('label');
                    if (lab2) {
                        lab2.style.background = '#fff3cd';
                        lab2.style.borderColor = '#ffc107';
                        var tag2 = lab2.querySelector('span');
                        if (tag2) {
                            tag2.textContent = 'VENDU';
                            tag2.style.color = '#856404';
                        }
                    }
                } else if (!cb.checked && cb.getAttribute('data-sold') !== '1'
                    && cb.getAttribute('data-tampon') !== '1') {
                    state.blocked[String(cb.value)] = true;
                    var labB = cb.closest('label');
                    if (labB) {
                        labB.style.background = '#e2e3e5';
                        labB.style.borderColor = '#6c757d';
                        labB.style.opacity = '0.75';
                        var tagB = labB.querySelector('span');
                        if (tagB) {
                            tagB.textContent = 'BLOQUÉ';
                            tagB.style.color = '#495057';
                        }
                    }
                } else if (cb.checked && cb.getAttribute('data-sold') !== '1'
                    && cb.getAttribute('data-tampon') !== '1') {
                    delete state.blocked[String(cb.value)];
                    var labOk = cb.closest('label');
                    if (labOk && cb.getAttribute('data-reco') !== '1') {
                        labOk.style.background = '';
                        labOk.style.borderColor = '';
                        labOk.style.opacity = '';
                        var tagOk = labOk.querySelector('span');
                        if (tagOk && tagOk.textContent === 'BLOQUÉ') {
                            tagOk.textContent = '';
                        }
                    }
                } else if (cb.getAttribute('data-reco') === '1' && cb.checked) {
                    var lab3 = cb.closest('label');
                    if (lab3 && cb.getAttribute('data-sold') !== '1') {
                        lab3.style.background = '#d1ecf1';
                        lab3.style.borderColor = '#17a2b8';
                        var tag3 = lab3.querySelector('span');
                        if (tag3) {
                            tag3.textContent = 'RECONDUIT';
                            tag3.style.color = '#0c5460';
                        }
                    }
                }
                syncHidden();
                return;
            }

            if (!isContiguous(nums)) {
                state.reverting = true;
                cb.checked = !cb.checked;
                state.reverting = false;
                setSummary('Plage contiguë requise.');
                return;
            }
            syncHidden();
        }

        function loadFromCategory(categ, inter1, inter2, sold) {
            if (!grid) {
                return Promise.resolve();
            }
            state.recoMode = false;
            state.reco = {};
            if (!categ) {
                grid.innerHTML = '<div class="col-12"><small class="text-muted">Choisissez une catégorie de bus.</small></div>';
                state.sold = {};
                syncHidden();
                return Promise.resolve();
            }
            grid.innerHTML = '<div class="col-12"><small class="text-muted">Chargement du plan…</small></div>';
            setSummary('Chargement…');
            var url = siteBase() + '/categories/getnbrplace/' + encodeURIComponent(categ);
            return fetchJson(url)
                .then(function (res) {
                    var n = res && res.nbr_place ? parseInt(res.nbr_place, 10) : 0;
                    if (n <= 0) {
                        grid.innerHTML = '<div class="col-12"><small class="text-muted">Catégorie sans plan de sièges.</small></div>';
                        state.sold = {};
                        setSummary('Quota : —');
                        return;
                    }
                    var d = parseInt(inter1, 10) || 1;
                    var f = parseInt(inter2, 10) || n;
                    renderAll(n, d, f, sold || [], null);
                })
                .catch(function () {
                    grid.innerHTML = '<div class="col-12"><small class="text-danger">Impossible de charger le plan de sièges.</small></div>';
                    setSummary('Erreur de chargement');
                });
        }

        function loadForEdit(codeProgr, ekey, inter1, inter2, categ, soldFallback) {
            state.editMode = true;
            var fallback = Array.isArray(soldFallback) ? soldFallback : [];
            if (!codeProgr || !ekey) {
                grid.innerHTML = '<div class="col-12"><small class="text-danger">Impossible de charger le plan : code programme manquant.</small></div>';
                setSummary('Erreur de chargement');
                return Promise.resolve();
            }
            grid.innerHTML = '<div class="col-12"><small class="text-muted">Chargement du plan…</small></div>';
            setSummary('Chargement…');
            // Minuscule : aligné routes.php (programmes/apercu_quota/…)
            var url = siteBase() + '/programmes/apercu_quota/' + encodeURIComponent(ekey) + '/' + encodeURIComponent(codeProgr);
            return fetchJson(url)
                .then(function (data) {
                    state.editMode = true;
                    var sold = fallback.slice();
                    if (data && data.ok && Array.isArray(data.sieges_occupes)) {
                        data.sieges_occupes.forEach(function (n) {
                            var num = parseInt(n, 10);
                            if (!isNaN(num) && num > 0 && sold.indexOf(num) === -1) {
                                sold.push(num);
                            }
                        });
                    }
                    // Édition : ne jamais basculer sur un plan « tout coché » sans bloqués.
                    if (!data || !data.ok) {
                        grid.innerHTML = '<div class="col-12"><small class="text-danger">Impossible de charger les sièges bloqués — fermez et rouvrez l’édition.</small></div>';
                        setSummary('Erreur de chargement');
                        return;
                    }
                    var recoList = null;
                    // Toujours passer la liste (même vide) pour distinguer « aucun bloqué » d’un échec.
                    var blockedList = Array.isArray(data.sieges_bloques) ? data.sieges_bloques : [];
                    var tamponList = null;
                    if (data.is_reconduction_cible && Array.isArray(data.sieges_reconduits) && data.sieges_reconduits.length) {
                        recoList = data.sieges_reconduits;
                    }
                    if (Array.isArray(data.sieges_tampon) && data.sieges_tampon.length) {
                        tamponList = data.sieges_tampon;
                    }
                    renderAll(
                        parseInt(data.nbr_place, 10) || 0,
                        parseInt(data.intervalle1, 10) || parseInt(inter1, 10) || 1,
                        parseInt(data.intervalle2, 10) || parseInt(inter2, 10) || 0,
                        sold,
                        recoList,
                        blockedList,
                        tamponList
                    );
                    var categSel = findCategSelect(block);
                    if (categSel && data.categori && !categSel.value) {
                        categSel.value = data.categori;
                    }
                })
                .catch(function () {
                    state.editMode = true;
                    grid.innerHTML = '<div class="col-12"><small class="text-danger">Impossible de charger le plan de sièges (réseau). Réessayez.</small></div>';
                    setSummary('Erreur de chargement');
                });
        }

        if (!block.getAttribute('data-quota-ui-bound')) {
            block.setAttribute('data-quota-ui-bound', '1');
            var btnAll = block.querySelector('.js-quota-check-all');
            if (btnAll) {
                btnAll.addEventListener('click', function () {
                    if (state.nbrPlace <= 0) {
                        return;
                    }
                    grid.querySelectorAll('.js-quota-siege').forEach(function (cb) {
                        if (cb.disabled) {
                            return;
                        }
                        if (state.recoMode && cb.getAttribute('data-reco') !== '1') {
                            return;
                        }
                        cb.checked = true;
                    });
                    syncHidden();
                });
            }
            var btnNone = block.querySelector('.js-quota-uncheck-all');
            if (btnNone) {
                btnNone.addEventListener('click', function () {
                    grid.querySelectorAll('.js-quota-siege').forEach(function (cb) {
                        if (!cb.disabled) {
                            cb.checked = false;
                        }
                    });
                    syncHidden();
                    setSummary('Au moins un siège requis.');
                });
            }
            if (form) {
                form.addEventListener('submit', function (ev) {
                    if (!grid.querySelector('.js-quota-siege')) {
                        return;
                    }
                    var nums = sortedNums(Array.from(grid.querySelectorAll('.js-quota-siege')));
                    if (!nums.length) {
                        ev.preventDefault();
                        setSummary('Sélectionnez au moins un siège.');
                        return false;
                    }
                    if (!state.editMode && !state.recoMode && !isContiguous(nums)) {
                        ev.preventDefault();
                        setSummary('Sélectionnez une plage de sièges contiguë.');
                        return false;
                    }
                    syncHidden();
                });
            }
        }

        block._quotaLoadEdit = loadForEdit;
        block._quotaLoadCategory = loadFromCategory;
        return block;
    }

    function loadCategoryForSelect(sel) {
        if (!sel || sel.name !== 'categorie') {
            return Promise.resolve();
        }
        var form = sel.closest('form');
        if (!form) {
            return Promise.resolve();
        }
        var block = form.querySelector('.js-quota-sieges-block');
        if (!block) {
            return Promise.resolve();
        }
        bindBlock(block);
        var debutEl = block.querySelector('.js-quota-debut-field');
        var finEl = block.querySelector('.js-quota-fin-field');
        var d = debutEl && debutEl.value ? parseInt(debutEl.value, 10) : 0;
        var f = finEl && finEl.value ? parseInt(finEl.value, 10) : 0;
        var sold = block._quotaState && block._quotaState.sold
            ? Object.keys(block._quotaState.sold).map(function (k) { return parseInt(k, 10); })
            : [];
        if (typeof block._quotaLoadCategory === 'function') {
            return block._quotaLoadCategory(sel.value, d, f, sold);
        }
        return Promise.resolve();
    }

    function init() {
        document.querySelectorAll('.js-quota-sieges-block').forEach(bindBlock);
    }

    function loadEditForForm(formId, codeProgr, ekey, inter1, inter2, categ, soldFallback) {
        var form = document.getElementById(formId);
        if (!form) {
            return Promise.resolve();
        }
        var block = form.querySelector('.js-quota-sieges-block');
        if (!block) {
            return Promise.resolve();
        }
        bindBlock(block);
        if (block._quotaState) {
            block._quotaState.editMode = true;
        }
        if (typeof block._quotaLoadEdit === 'function') {
            return block._quotaLoadEdit(codeProgr, ekey, inter1, inter2, categ, soldFallback || []);
        }
        return Promise.resolve();
    }

    document.addEventListener('change', function (ev) {
        var t = ev.target;
        if (t && t.tagName === 'SELECT' && t.name === 'categorie') {
            loadCategoryForSelect(t);
        }
    });

    global.ProgQuotaSieges = {
        init: init,
        loadEditForForm: loadEditForForm,
        loadCategoryForSelect: loadCategoryForSelect
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}(window));

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
;
/* --- filtre_depart_compagnie.js --- */
document.addEventListener('DOMContentLoaded', function () {
    function applyDepartFilter(compSelect) {
        if (!compSelect) return;
        var targetId = compSelect.getAttribute('data-target-depart');
        if (!targetId) return;
        var departSelect = document.getElementById(targetId);
        if (!departSelect) return;

        var cle = String(compSelect.value || '');
        var keepValue = departSelect.value;
        var options = departSelect.querySelectorAll('option[data-compagnie]');
        var firstVisible = null;

        for (var i = 0; i < options.length; i++) {
            var opt = options[i];
            var match = cle !== '' && String(opt.getAttribute('data-compagnie') || '') === cle;
            opt.hidden = !match;
            opt.disabled = !match;
            if (match && !firstVisible) {
                firstVisible = opt;
            }
        }

        if (cle === '') {
            departSelect.value = '';
            return;
        }

        var selected = departSelect.options[departSelect.selectedIndex];
        var selectedOk = selected
            && selected.getAttribute('data-compagnie')
            && String(selected.getAttribute('data-compagnie')) === cle
            && !selected.disabled;
        if (!selectedOk) {
            departSelect.value = keepValue && firstVisible && keepValue === firstVisible.value
                ? keepValue
                : '';
            // Si la valeur conservée n'est plus visible, reset
            selected = departSelect.options[departSelect.selectedIndex];
            if (!selected || selected.disabled || selected.hidden) {
                departSelect.value = '';
            }
        }
    }

    function bindFiltreCompagnie(root) {
        root = root || document;
        root.querySelectorAll('.js-filtre-compagnie-arrivee').forEach(function (sel) {
            if (sel.getAttribute('data-filtre-bound') === '1') return;
            sel.setAttribute('data-filtre-bound', '1');
            sel.addEventListener('change', function () {
                applyDepartFilter(sel);
            });
            applyDepartFilter(sel);
        });
    }

    /**
     * Prefill compagnie + départ (édition programme).
     * @param {string} departSelectId
     * @param {string} departValue
     */
    window.__syncDepartCompagnie = function (departSelectId, departValue) {
        var departSelect = document.getElementById(departSelectId);
        if (!departSelect) return;
        var compSelect = document.querySelector(
            '.js-filtre-compagnie-arrivee[data-target-depart="' + departSelectId + '"]'
        );
        if (!compSelect) {
            if (departValue) {
                departSelect.value = departValue;
            }
            return;
        }
        var opt = null;
        if (departValue) {
            for (var i = 0; i < departSelect.options.length; i++) {
                if (departSelect.options[i].value === departValue) {
                    opt = departSelect.options[i];
                    break;
                }
            }
        }
        if (opt) {
            var cle = opt.getAttribute('data-compagnie') || '';
            compSelect.value = cle;
            applyDepartFilter(compSelect);
            departSelect.value = departValue;
        } else {
            applyDepartFilter(compSelect);
        }
    };

    bindFiltreCompagnie(document);
    window.__bindFiltreDepartCompagnie = bindFiltreCompagnie;
});

