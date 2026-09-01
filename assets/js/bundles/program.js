/* Bundle program — genere */
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
                        var pcat = document.querySelector('#idcategoriebus')
                            .options[document.querySelector('#idcategoriebus').options.selectedIndex].value;

                        document.querySelector('#choisirheure').options.length = 1;
                        document.querySelector('#idprog').options.length = 1;
                        document.querySelector('#infosms').style.display = 'none';

                        var heureUrl = window.location.origin
                            + `${APP_ROOT}/programmes/verifheure/${encodeURIComponent(idligne)}/${encodeURIComponent(verifdate)}`;
                        if (pcat) {
                            heureUrl += `?cat=${encodeURIComponent(pcat)}`;
                        }
                        httpInfoheure.open('GET', heureUrl, true);
                        httpInfoheure.onload = () => {
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

                        document.querySelector('#idprog').options.length = 1;
                        document.querySelector('#infosms').style.display = 'none';

                        var veridate = document.querySelector('#choisirdate').value;
                        var verifheu = document.querySelector('#choisirheure')
                                .options[document.querySelector('#choisirheure').options.selectedIndex].value;

                                var pcat = document.querySelector('#idcategoriebus')
                                .options[document.querySelector('#idcategoriebus').options.selectedIndex].value;
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
                        if (!resultheur || resultheur === '') {
                            return;
                        }
                        if (Array.isArray(resultheur) && resultheur.length >= 1) {
                            resultheur.forEach(function (row) {
                                let opt = document.createElement('option');
                                opt.value = `${row.heure_identif}/${row.heure}`;
                                opt.innerHTML = `${row.heure}`;
                                document.querySelector('#choisirheureliste').add(opt);
                            });
                        } else {
                            document.querySelector('#choisirheureliste').options.length = 1;
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

    document.querySelectorAll('.addgprogramme').forEach(function (e) {
        
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
            $('#progdate').val(`${e.dataset.pdate}`);
            var ouotAncien = document.querySelector('#ouotafinancien');
            var ouotNouveau = document.querySelector('#ouotafinnouveau');
            if (ouotAncien) ouotAncien.value = `${e.dataset.categnbplace}`;
            if (ouotNouveau) ouotNouveau.value = `${e.dataset.categnbplace}`;
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
        
            let typcat = document.querySelector('#idcateg');
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
    })
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
        var hintEl = block.querySelector('.js-quota-hint');
        var isEditBlock = block.getAttribute('data-quota-mode') === 'edit';

        var state = block._quotaState || {
            nbrPlace: 0,
            sold: {},
            reco: {},
            recoMode: false,
            rangeDebut: 0,
            rangeFin: 0,
            editMode: isEditBlock,
            reverting: false
        };
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
         * Libération : sièges vendus décochés (en reconduction : seulement parmi les alloués).
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
            if (!state.recoMode && state.editMode) {
                // Édition normale : trous entre min/max cochés aussi candidats
                var nums = checked || sortedNums(Array.from(grid.querySelectorAll('.js-quota-siege')));
                if (nums.length) {
                    var d = nums[0];
                    var f = nums[nums.length - 1];
                    var seen = {};
                    out.forEach(function (n) { seen[n] = true; });
                    for (var n = d; n <= f; n++) {
                        var cb = grid.querySelector('.js-quota-siege[value="' + n + '"]');
                        if (cb && !cb.disabled && !cb.checked && !seen[n]) {
                            out.push(n);
                            seen[n] = true;
                        }
                    }
                }
            }
            return out.sort(function (a, b) { return a - b; });
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
                if (debutEl) debutEl.value = '';
                if (finEl) finEl.value = '';
                setSummary('Quota : —');
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
            var soldN = Object.keys(state.sold).length;
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
            if (lib.length) {
                parts.push(lib.length + ' libéré(s) → revendable(s)');
            }
            setSummary(parts.join(' · '));
            return true;
        }

        function renderGrid(from, to, checkedFrom, checkedTo, sold, recoList) {
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
                var checked;
                if (state.recoMode) {
                    checked = isReco; // uniquement les sièges reconduits
                } else {
                    checked = (n >= checkedFrom && n <= checkedTo) || isSold;
                }
                var disabled = state.recoMode && !isReco;
                var wrapStyle;
                var labelExtra = '';
                if (isSold) {
                    wrapStyle = 'background:#fff3cd;border:1px solid #ffc107;border-radius:4px;padding:4px 6px;display:block;';
                    labelExtra = ' <span style="color:#856404;font-size:11px;font-weight:600;">VENDU</span>';
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
                    + (isReco && state.recoMode ? ' js-quota-siege-reco' : '')
                    + '" value="' + n + '"'
                    + (checked ? ' checked' : '')
                    + (disabled ? ' disabled' : '')
                    + (isSold ? ' data-sold="1"' : '')
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
                setHint('Fond bleu = siège reconduit (' + recoCount() + '). Jaune = déjà vendu. Gris = hors reconduction (non vendable ici).');
            }
            syncHidden();
        }

        function renderAll(nbrPlace, rangeDebut, rangeFin, sold, recoList) {
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
            renderGrid(1, nbrPlace, d, f, sold, recoList || null);
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
                return;
            }

            // Édition / reconduction : trous autorisés (= libération)
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
                return loadFromCategory(categ, inter1, inter2, fallback);
            }
            grid.innerHTML = '<div class="col-12"><small class="text-muted">Chargement du plan…</small></div>';
            setSummary('Chargement…');
            var url = siteBase() + '/Programmes/apercu_quota/' + encodeURIComponent(ekey) + '/' + encodeURIComponent(codeProgr);
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
                    if (!data || !data.ok) {
                        return loadFromCategory(categ, inter1, inter2, sold);
                    }
                    var recoList = null;
                    if (data.is_reconduction_cible && Array.isArray(data.sieges_reconduits) && data.sieges_reconduits.length) {
                        recoList = data.sieges_reconduits;
                    }
                    renderAll(
                        parseInt(data.nbr_place, 10) || 0,
                        parseInt(data.intervalle1, 10) || parseInt(inter1, 10) || 1,
                        parseInt(data.intervalle2, 10) || parseInt(inter2, 10) || 0,
                        sold,
                        recoList
                    );
                    var categSel = findCategSelect(block);
                    if (categSel && data.categori && !categSel.value) {
                        categSel.value = data.categori;
                    }
                })
                .catch(function () {
                    state.editMode = true;
                    return loadFromCategory(categ, inter1, inter2, fallback);
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

