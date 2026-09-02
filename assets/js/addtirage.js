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
