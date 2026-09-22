/* Bundle guichet role=14 — genere par scripts/build_guichet_bundles.php */
/* --- adreportgl.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adreportgl').forEach(function (e) 
    {
        document.querySelector('h3#Titlereps').innerHTML = `ETAT GLOBAL TICKET GUICHETIER`;

        let infgars = document.querySelector('#garidentifs');
        
        if (infgars !== null) 
        infgars.onchange = () => {
            let httpInfosgars;
            if (window.XMLHttpRequest) {
                httpInfosgars = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgars = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idscaissier').options.length = 1;

                    var verificatgars = document.querySelector('#garidentifs').value;
                    
                    httpInfosgars.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgars}`, true);
                    httpInfosgars.onload = () => {
                        const infosgars = JSON.parse(httpInfosgars.responseText);
                        
                        if (Object.entries(infosgars).length > 0) {                            
                        
                                for (let key in Object.entries(infosgars)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgars[key].roleattribut}`;
                                    opt.innerHTML = `${infosgars[key].username}`;
                                    document.querySelector('#idscaissier').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idscaissier').options.length = 1;
                        }
                        
                    };
                    httpInfosgars.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgars.send();
                };
        e.onclick = function () {
        let tickForms = document.querySelector('#tickForms');
            tickForms.setAttribute('action', `${APP_ROOT}/Rapport/reports/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })

    if (!window.__recapGlTkSousgareBound) {
        window.__recapGlTkSousgareBound = true;
        document.querySelectorAll('select[name="sousgaretkt"]').forEach(function (sousSel) {
            var form = sousSel.closest('form');
            if (!form) return;
            var gareSel = form.querySelector('select[name="departgar"]');
            if (!gareSel) return;

            function resetSousGare() {
                sousSel.options.length = 0;
                var allOpt = document.createElement('option');
                allOpt.value = '';
                allOpt.innerHTML = 'Toutes';
                sousSel.add(allOpt);
            }

            gareSel.addEventListener('change', function () {
                var gid = gareSel.value;
                resetSousGare();
                if (!gid) return;
                var http = new XMLHttpRequest();
                http.open(
                    'GET',
                    window.location.origin + `${APP_ROOT}/programmes/verifsousgares/` + encodeURIComponent(gid),
                    true
                );
                http.onload = function () {
                    var rows = null;
                    try { rows = JSON.parse(http.responseText); } catch (err) { rows = null; }
                    resetSousGare();
                    if (!rows) return;
                    Object.keys(rows).forEach(function (key) {
                        var row = rows[key];
                        if (!row || row.idsousgare == null) return;
                        var opt = document.createElement('option');
                        opt.value = row.idsousgare;
                        opt.innerHTML = row.nomsousgare;
                        sousSel.add(opt);
                    });
                };
                http.send();
            });
        });
    }
});
;
/* --- adreportglesc.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adreportglesc').forEach(function (e) 
    {
        document.querySelector('h3#Titlerepsesc').innerHTML = `ETAT GLOBAL TICKET GUICHETIER ESCAL`;

        let infgars = document.querySelector('#garidentifsesc');
        
        if (infgars !== null) 
        infgars.onchange = () => {
            let httpInfosgars;
            if (window.XMLHttpRequest) {
                httpInfosgars = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgars = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idscaissieresc').options.length = 1;

                    var verificatgars = document.querySelector('#garidentifsesc').value;
                    
                    httpInfosgars.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeusesesc/${verificatgars}`, true);
                    httpInfosgars.onload = () => {
                        const infosgars = JSON.parse(httpInfosgars.responseText);
                        
                        if (Object.entries(infosgars).length > 0) {                            
                        
                                for (let key in Object.entries(infosgars)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgars[key].roleattribut}`;
                                    opt.innerHTML = `${infosgars[key].username}`;
                                    document.querySelector('#idscaissieresc').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idscaissieresc').options.length = 1;
                        }
                        
                    };
                    httpInfosgars.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgars.send();
                };
        e.onclick = function () {
        let tickForms = document.querySelector('#tickFormsesc');
            tickForms.setAttribute('action', `${APP_ROOT}/Rapport/reportsesc/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
;
/* --- adreportglcours.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adreportglcours').forEach(function (e) 
    {
        document.querySelector('h3#Titlexpglobg').innerHTML = `ETAT GLOBAL COURRIER GUICHETIER`;

        let expinfosg = document.querySelector('#garesg');
        
        if (expinfosg !== null) 
        expinfosg.onchange = () => {
            let httpInforsgexpg;
            if (window.XMLHttpRequest) {
                httpInforsgexpg = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInforsgexpg = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idcaisseg').options.length = 1;

                    var expeverifivendg = document.querySelector('#garesg').value;
                    
                    httpInforsgexpg.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${expeverifivendg}`, true);
                    httpInforsgexpg.onload = () => {
                        const exinfosgsg = JSON.parse(httpInforsgexpg.responseText);
                        
                        if (Object.entries(exinfosgsg).length > 0) {                            
                        
                                for (let key in Object.entries(exinfosgsg)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${exinfosgsg[key].roleattribut}/${exinfosgsg[key].first_name} ${exinfosgsg[key].last_name}`;
                                    opt.innerHTML = `${exinfosgsg[key].username}`;
                                    document.querySelector('#idcaisseg').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idcaisseg').options.length = 1;
                        }
                        
                    };
                    httpInforsgexpg.setRequestHeader('Content-Type', 'application/json');
                    httpInforsgexpg.send();
                };
        e.onclick = function () {
            let expglobFormsg = document.querySelector('#expglobFormsg');
            expglobFormsg.setAttribute('action', `${APP_ROOT}/Rapport/etatsglcourrier/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })

    if (!window.__recapGlCrSousgareBound) {
        window.__recapGlCrSousgareBound = true;
        document.querySelectorAll('select[name="sousgarecrgl"]').forEach(function (sousSel) {
            var form = sousSel.closest('form');
            if (!form) return;
            var gareSel = form.querySelector('select[name="departgarcrgl"]');
            if (!gareSel) return;

            function resetSousGare() {
                sousSel.options.length = 0;
                var allOpt = document.createElement('option');
                allOpt.value = '';
                allOpt.innerHTML = 'Toutes';
                sousSel.add(allOpt);
            }

            gareSel.addEventListener('change', function () {
                var gid = gareSel.value;
                resetSousGare();
                if (!gid) return;
                var http = new XMLHttpRequest();
                http.open(
                    'GET',
                    window.location.origin + `${APP_ROOT}/programmes/verifsousgares/` + encodeURIComponent(gid),
                    true
                );
                http.onload = function () {
                    var rows = null;
                    try { rows = JSON.parse(http.responseText); } catch (err) { rows = null; }
                    resetSousGare();
                    if (!rows) return;
                    Object.keys(rows).forEach(function (key) {
                        var row = rows[key];
                        if (!row || row.idsousgare == null) return;
                        var opt = document.createElement('option');
                        opt.value = row.idsousgare;
                        opt.innerHTML = row.nomsousgare;
                        sousSel.add(opt);
                    });
                };
                http.send();
            });
        });
    }
});
;
/* --- adreportglcoursesc.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adreportglcoursesc').forEach(function (e) 
    {
        document.querySelector('h3#Titlexpglobgesc').innerHTML = `ETAT GLOBAL COURRIERESCAL GUICHETIER`;

        let expinfosg = document.querySelector('#garesgesc');
        
        if (expinfosg !== null) 
        expinfosg.onchange = () => {
            let httpInforsgexpg;
            if (window.XMLHttpRequest) {
                httpInforsgexpg = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInforsgexpg = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idcaissegesc').options.length = 1;

                    var expeverifivendg = document.querySelector('#garesgesc').value;
                    
                    httpInforsgexpg.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${expeverifivendg}`, true);
                    httpInforsgexpg.onload = () => {
                        const exinfosgsg = JSON.parse(httpInforsgexpg.responseText);
                        
                        if (Object.entries(exinfosgsg).length > 0) {                            
                        
                                for (let key in Object.entries(exinfosgsg)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${exinfosgsg[key].roleattribut}/${exinfosgsg[key].first_name} ${exinfosgsg[key].last_name}`;
                                    opt.innerHTML = `${exinfosgsg[key].username}`;
                                    document.querySelector('#idcaissegesc').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idcaissegesc').options.length = 1;
                        }
                        
                    };
                    httpInforsgexpg.setRequestHeader('Content-Type', 'application/json');
                    httpInforsgexpg.send();
                };
        e.onclick = function () {
            let expglobFormsg = document.querySelector('#expglobFormsgesc');
            expglobFormsg.setAttribute('action', `${APP_ROOT}/Rapport/etatsglcourrieresc/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
;
/* --- adreportgldepcour.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adreportgldepcour').forEach(function (e) 
    {
        document.querySelector('h3#Titlerepscourdep').innerHTML = `RECAP DEPENSE COURRIER`;

        let infgarscrdep = document.querySelector('#garidentifscourdep');
        
        if (infgarscrdep !== null) 
        infgarscrdep.onchange = () => {
            let httpInfosgarscrdep;
            if (window.XMLHttpRequest) {
                httpInfosgarscrdep = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgarscrdep = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idscaissiercourdep').options.length = 1;

                    var verificatgarscrdep = document.querySelector('#garidentifscourdep').value;
                    
                    httpInfosgarscrdep.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgarscrdep}`, true);
                    httpInfosgarscrdep.onload = () => {
                        const infosgarscrdep = JSON.parse(httpInfosgarscrdep.responseText);
                        
                        if (Object.entries(infosgarscrdep).length > 0) {                            
                        
                                for (let key in Object.entries(infosgarscrdep)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgarscrdep[key].roleattribut}`;
                                    opt.innerHTML = `${infosgarscrdep[key].username}`;
                                    document.querySelector('#idscaissiercourdep').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idscaissiercourdep').options.length = 1;
                        }
                        
                    };
                    httpInfosgarscrdep.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgarscrdep.send();
                };
        e.onclick = function () {
        let tickFormscrdep = document.querySelector('#tickFormscourdep');
            tickFormscrdep.setAttribute('action', `${APP_ROOT}/Rapport/tridepensescour/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
;
/* --- adreportversgljs.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adreportversgljs').forEach(function (e)
    {
        const title = document.querySelector('h3#Titlerepversgl');
        if (title) {
            title.innerHTML = `TRI REPORT GLOBAL DES RECETTES`;
        }
        e.onclick = function () {
            const tickversForm = document.querySelector('#tickversglForm');
            if (tickversForm) {
                tickversForm.setAttribute('action', `${APP_ROOT}/Rapport/exoreportsversgl/${e.dataset.ekey}/${e.dataset.idgares}`);
            }
        };
    });
});

;
/* --- adtrio.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtrio').forEach(function (e)
    {
        const title = document.querySelector('h3#caisTitle');
        if (title) {
            title.innerHTML = `VERSEMENT TICKET GUICHETIER`;
        }
        e.onclick = function () {
            const encaisForm = document.querySelector('#encaismentForm');
            if (encaisForm) {
                encaisForm.setAttribute('action', `${APP_ROOT}/Rapport/triencaissements/${e.dataset.ekey}/${e.dataset.idsgare}`);
            }
        };
    });
});

;
/* --- adtriocour.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtriocour').forEach(function (e)
    {
        const title = document.querySelector('h3#caisTitlecour');
        if (title) {
            title.innerHTML = `VERSEMENT COURRIER GUICHETIER`;
        }
        e.onclick = function () {
            const encaisForm = document.querySelector('#encaismentFormcour');
            if (encaisForm) {
                encaisForm.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementscour/${e.dataset.ekey}/${e.dataset.idsgare}`);
            }
        };
    });
});

;
/* --- adtriobag.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtriobag').forEach(function (e)
    {
        const title = document.querySelector('h3#caisTitlebag');
        if (title) {
            title.innerHTML = `VERSEMENT BAGAGES`;
        }
        e.onclick = function () {
            const encaisForm = document.querySelector('#encaismentFormbag');
            if (encaisForm) {
                encaisForm.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementsbag/${e.dataset.ekey}/${e.dataset.idsgare}`);
            }
        };
    });
});

;
/* --- adverssg.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adverssg').forEach(function (e)
    {
        const title = document.querySelector('h3#caiTitlesg');
        if (title) {
            title.innerHTML = `RECETTE GLOBALE TICKET PAR GARE`;
        }
        e.onclick = function () {
            const encaisForms = document.querySelector('#encaisFormssg');
            if (encaisForms) {
                encaisForms.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementsg/${e.dataset.ekey}/${e.dataset.idsgare}/${e.dataset.idsggare}`);
            }
        };
    });
});

;
/* --- etats-fusion-choix.js --- */
document.addEventListener('DOMContentLoaded', () => {
    // Retirer les options dont le déclencheur n’existe pas sur la page.
    ['choix-recette-type', 'choix-versement-type', 'choix-modif-versement-type'].forEach((selectId) => {
        const select = document.getElementById(selectId);
        if (!select) {
            return;
        }
        Array.from(select.options).forEach((opt) => {
            if (opt.value && !document.getElementById(opt.value)) {
                opt.remove();
            }
        });
    });

    function closeChoixModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) {
            return;
        }
        const closer = modal.querySelector('.modal-close');
        if (closer) {
            closer.click();
        } else {
            modal.classList.remove('modal-show');
        }
    }

    function openTrigger(triggerId) {
        const trig = document.getElementById(triggerId);
        if (!trig) {
            return;
        }
        window.setTimeout(() => {
            trig.click();
        }, 180);
    }

    document.querySelectorAll('[data-choix-go]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const selectId = btn.getAttribute('data-choix-go');
            const modalId = btn.getAttribute('data-choix-modal');
            const select = document.getElementById(selectId);
            if (!select || !select.value) {
                return;
            }
            closeChoixModal(modalId);
            openTrigger(select.value);
        });
    });
});

;
/* --- tri-filtre-dynamique.js --- */
/**
 * Cascade tri états : COMPAGNIE → GARE → DATES → lignes / guichetiers.
 */
(function () {
    function appRoot() {
        return (typeof APP_ROOT !== 'undefined' && APP_ROOT) ? APP_ROOT : '';
    }

    function jsonGet(url, onOk, seqHolder, seq) {
        const http = window.XMLHttpRequest
            ? new XMLHttpRequest()
            : new ActiveXObject('Microsoft.XMLHTTP');
        http.open('GET', url, true);
        http.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        http.setRequestHeader('Accept', 'application/json');
        http.onload = () => {
            if (seqHolder && seqHolder.seq !== seq) {
                return;
            }
            let data = [];
            try {
                const raw = (http.responseText || '').trim();
                if (raw.charAt(0) === '[' || raw.charAt(0) === '{') {
                    data = JSON.parse(raw);
                }
            } catch (e) {
                data = [];
            }
            if (Array.isArray(data)) {
                onOk(data);
            } else if (data && typeof data === 'object') {
                onOk(Object.keys(data).map((k) => data[k]).filter((v) => v && typeof v === 'object'));
            } else {
                onOk([]);
            }
        };
        http.onerror = () => {
            if (seqHolder && seqHolder.seq !== seq) {
                return;
            }
            onOk([]);
        };
        http.send();
    }

    function resetSelect(select, keepLabel) {
        if (!select) {
            return;
        }
        const firstText = keepLabel
            || (select.options.length ? select.options[0].text : '');
        const firstVal = select.options.length ? select.options[0].value : '';
        select.options.length = 0;
        const keep = document.createElement('option');
        keep.value = firstVal === undefined || firstVal === null ? '' : firstVal;
        keep.text = firstText || '';
        select.add(keep);
    }

    function fillUsers(select, rows) {
        if (!select) {
            return;
        }
        const prev = String(select.value || '').trim();
        const placeholder = select.options.length ? select.options[0].text : 'Tous';
        resetSelect(select, placeholder);
        (rows || []).forEach((row) => {
            if (!row) {
                return;
            }
            const val = row.roleattribut != null ? String(row.roleattribut) : '';
            if (val === '') {
                return;
            }
            const opt = document.createElement('option');
            opt.value = val;
            opt.text = row.username || val;
            select.add(opt);
        });
        if (prev) {
            select.value = prev;
            if (select.value !== prev) {
                // Valeur précédente absente de la nouvelle liste : la réinjecter.
                const keep = document.createElement('option');
                keep.value = prev;
                keep.text = prev;
                keep.selected = true;
                select.add(keep);
            }
        }
    }

    function fillLignes(select, rows) {
        if (!select) {
            return;
        }
        resetSelect(select, 'Toutes lignes');
        (rows || []).forEach((row) => {
            if (!row) {
                return;
            }
            const val = row.ident_ligne != null ? String(row.ident_ligne) : '';
            if (val === '') {
                return;
            }
            const opt = document.createElement('option');
            opt.value = val;
            opt.text = row.nom_ligne || val;
            select.add(opt);
        });
    }

    function fillGares(select, rows) {
        if (!select) {
            return;
        }
        resetSelect(select, 'Choisir une gare');
        (rows || []).forEach((row) => {
            if (!row) {
                return;
            }
            const val = row.code_gaexp != null ? String(row.code_gaexp) : '';
            if (val === '') {
                return;
            }
            const opt = document.createElement('option');
            opt.value = val;
            const label = row.nom_gaep || val;
            opt.text = label;
            opt.setAttribute('data-garesid', row.garesid || '');
            select.add(opt);
        });
    }

    function q(root, sel) {
        if (!root || !sel) {
            return null;
        }
        return root.querySelector(sel);
    }

    function wire(cfg) {
        const root = document.querySelector(cfg.root);
        if (!root) {
            return;
        }
        const compEl = cfg.comp ? q(root, cfg.comp) : null;
        const gareEl = q(root, cfg.gare);
        const duEl = q(root, cfg.du);
        const auEl = q(root, cfg.au);
        const usersEl = cfg.users ? q(root, cfg.users) : null;
        const lignesEl = cfg.lignes ? q(root, cfg.lignes) : null;
        const userType = cfg.userType || 'ticket';
        const seqUsers = { seq: 0 };
        const seqLignes = { seq: 0 };
        const seqGares = { seq: 0 };

        function compVal() {
            return compEl ? String(compEl.value || '').trim() : '';
        }

        function gareVal() {
            return gareEl ? String(gareEl.value || '').trim() : '';
        }

        function clearDownstreamFromComp() {
            if (gareEl) {
                resetSelect(gareEl, 'Choisir une gare');
            }
            if (usersEl) {
                resetSelect(usersEl, usersEl.options.length ? usersEl.options[0].text : '');
            }
            if (lignesEl) {
                resetSelect(lignesEl, 'Toutes lignes');
            }
        }

        function reloadGares() {
            if (!gareEl) {
                return;
            }
            const comp = compVal();
            if (!comp) {
                clearDownstreamFromComp();
                return;
            }
            const seq = ++seqGares.seq;
            const url = `${window.location.origin}${appRoot()}/utilisateurs/trigares?comp=${encodeURIComponent(comp)}`;
            jsonGet(url, (rows) => {
                fillGares(gareEl, rows);
                if (usersEl) {
                    resetSelect(usersEl, usersEl.options.length ? usersEl.options[0].text : '');
                }
                if (lignesEl) {
                    resetSelect(lignesEl, 'Toutes lignes');
                }
            }, seqGares, seq);
        }

        function reloadUsers() {
            if (!usersEl || !gareEl) {
                return;
            }
            const gare = gareVal();
            const comp = compVal();
            if (!gare || (compEl && !comp)) {
                resetSelect(usersEl, usersEl.options.length ? usersEl.options[0].text : '');
                return;
            }
            const du = duEl ? String(duEl.value || '').trim() : '';
            const au = auEl ? String(auEl.value || '').trim() : '';
            const seq = ++seqUsers.seq;
            let url = `${window.location.origin}${appRoot()}/utilisateurs/triactifs?gare=${encodeURIComponent(gare)}&type=${encodeURIComponent(userType)}`;
            if (comp) {
                url += `&comp=${encodeURIComponent(comp)}`;
            }
            if (du && au) {
                url += `&du=${encodeURIComponent(du)}&au=${encodeURIComponent(au)}`;
            }
            jsonGet(url, (rows) => fillUsers(usersEl, rows), seqUsers, seq);
        }

        function reloadLignes() {
            if (!lignesEl || !gareEl) {
                return;
            }
            const gare = gareVal();
            const comp = compVal();
            if (!gare || (compEl && !comp)) {
                resetSelect(lignesEl, 'Toutes lignes');
                return;
            }
            const seq = ++seqLignes.seq;
            let url = `${window.location.origin}${appRoot()}/utilisateurs/trilignes?gare=${encodeURIComponent(gare)}`;
            if (comp) {
                url += `&comp=${encodeURIComponent(comp)}`;
            }
            jsonGet(url, (rows) => fillLignes(lignesEl, rows), seqLignes, seq);
        }

        function onGareOrDates() {
            reloadUsers();
            reloadLignes();
        }

        if (compEl) {
            compEl.addEventListener('change', reloadGares);
            // Au chargement : vider les gares préremplies (toutes compagnies)
            // pour forcer Compagnie → Gare.
            clearDownstreamFromComp();
        }
        if (gareEl) {
            gareEl.addEventListener('change', onGareOrDates);
        }
        if (duEl) {
            duEl.addEventListener('change', reloadUsers);
            duEl.addEventListener('input', reloadUsers);
        }
        if (auEl) {
            auEl.addEventListener('change', reloadUsers);
            auEl.addEventListener('input', reloadUsers);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        wire({
            root: '#tickversglForm',
            comp: '[name="_compagversgl"]',
            gare: '[name="departgarversgl"]',
            du: '[name="datedebutversgl"]',
            au: '[name="datefinversgl"]',
            users: '#idcaissiersversgl',
            lignes: '#ligneaxeversgl',
            userType: 'ticket',
        });

        wire({
            root: '#encaisForms',
            comp: '[name="_compag"]',
            gare: '[name="departgar"]',
            du: '[name="dated"]',
            au: '[name="datef"]',
            users: 'select[name="vendeuseid"]',
            userType: 'op',
        });

        wire({
            root: '#encaisFormssg',
            comp: '[name="_compagsg"]',
            gare: '[name="departgarsg"]',
            du: '[name="datedsg"]',
            au: '[name="datefsg"]',
            users: '#idvendeusesg',
            userType: 'op',
        });

        wire({
            root: '#encaismentForm',
            comp: '[name="_compag"]',
            gare: '[name="departgar"]',
            du: '[name="dated"]',
            au: '[name="datef"]',
            users: 'select[name="vendeuseid"]',
            userType: 'ticket',
        });

        wire({
            root: '#encaismentFormbag',
            comp: '[name="_compagbag"]',
            gare: '[name="departgarbag"]',
            du: '[name="datedbag"]',
            au: '[name="datefbag"]',
            users: '#idvendeusesbag',
            userType: 'ticket',
        });

        wire({
            root: '#encaismentFormcour',
            comp: '[name="_compagcour"]',
            gare: '[name="departgarcour"]',
            du: '[name="datedcour"]',
            au: '[name="datefcour"]',
            users: '#idvendeusescour',
            userType: 'ticket',
        });

        // Avant envoi : recopier l’opérateur choisi dans ivend (filet si vendeuseid est perdu).
        ['#encaisForms', '#encaismentForm'].forEach((formSel) => {
            const form = document.querySelector(formSel);
            if (!form) {
                return;
            }
            form.addEventListener('submit', () => {
                const sel = form.querySelector('select[name="vendeuseid"]');
                const hid = form.querySelector('input[name="ivend"]');
                if (sel && hid) {
                    hid.value = String(sel.value || '').trim();
                }
            });
        });
    });
})();

;
/* --- recaptbagglop.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.recaptbagglop').forEach(function (e) 
    {
        document.querySelector('h3#optitlegl').innerHTML = `ETAT GLOBAL BAGAGE OPERATEUR`;

        let infgars = document.querySelector('#departgardpbagopgl');
        
        if (infgars !== null) 
        infgars.onchange = () => {
            let httpInfosgars;
            if (window.XMLHttpRequest) {
                httpInfosgars = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgars = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeuseopgl').options.length = 1;

                    var verificatgars = document.querySelector('#departgardpbagopgl').value;
                    
                    httpInfosgars.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeusesop/${verificatgars}`, true);
                    httpInfosgars.onload = () => {
                        const infosgars = JSON.parse(httpInfosgars.responseText);
                        
                        if (Object.entries(infosgars).length > 0) {                            
                        
                                for (let key in Object.entries(infosgars)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgars[key].roleattribut}`;
                                    opt.innerHTML = `${infosgars[key].username}`;
                                    document.querySelector('#idvendeuseopgl').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idvendeuseopgl').options.length = 1;
                        }
                        
                    };
                    httpInfosgars.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgars.send();
                };
        e.onclick = function () {
        let tickFormsgl = document.querySelector('#tickFormopgl');
            tickFormsgl.setAttribute('action', `${APP_ROOT}/Rapport/reportbaggl/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
;
/* --- recaptbagglopesc.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.recaptbagglopesc').forEach(function (e) 
    {
        document.querySelector('h3#optitleglesc').innerHTML = `ETAT GLOBAL BAGAGEESCAL OPERATEUR`;

        let infgars = document.querySelector('#departgardpbagopglesc');
        
        if (infgars !== null) 
        infgars.onchange = () => {
            let httpInfosgars;
            if (window.XMLHttpRequest) {
                httpInfosgars = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgars = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeuseopglesc').options.length = 1;

                    var verificatgars = document.querySelector('#departgardpbagopglesc').value;
                    
                    httpInfosgars.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeusesesc/${verificatgars}`, true);
                    httpInfosgars.onload = () => {
                        const infosgars = JSON.parse(httpInfosgars.responseText);
                        
                        if (Object.entries(infosgars).length > 0) {                            
                        
                                for (let key in Object.entries(infosgars)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgars[key].roleattribut}`;
                                    opt.innerHTML = `${infosgars[key].username}`;
                                    document.querySelector('#idvendeuseopglesc').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idvendeuseopglesc').options.length = 1;
                        }
                        
                    };
                    httpInfosgars.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgars.send();
                };
        e.onclick = function () {
        let tickFormsgl = document.querySelector('#tickFormopglesc');
            tickFormsgl.setAttribute('action', `${APP_ROOT}/Rapport/reportbagglesc/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
