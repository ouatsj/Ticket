/* Bundle guichet role=7 — genere par scripts/build_guichet_bundles.php */
/* --- adreportjs.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adreportjs').forEach(function (e) 
    {
        document.querySelector('h3#Titlerep').innerHTML = `EXERCICE MENSUEL TICKET GUICHETIER`;

        let infgar = document.querySelector('#departgaridentif');
        
        if (infgar !== null) 
        infgar.onchange = () => {
            let httpInfosgar;
            if (window.XMLHttpRequest) {
                httpInfosgar = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgar = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idcaissiers').options.length = 1;

                    var verificatgar = document.querySelector('#departgaridentif').value;
                    
                    httpInfosgar.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgar}`, true);
                    httpInfosgar.onload = () => {
                        const infosgar = JSON.parse(httpInfosgar.responseText);
                        
                        if (Object.entries(infosgar).length > 0) {                            
                        
                                for (let key in Object.entries(infosgar)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgar[key].roleattribut}`;
                                    opt.innerHTML = `${infosgar[key].username}`;
                                    document.querySelector('#idcaissiers').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idcaissiers').options.length = 1;
                        }
                        
                    };
                    httpInfosgar.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgar.send();
                };
        e.onclick = function () {
        let tickForm = document.querySelector('#tickForm');
            tickForm.setAttribute('action', `${APP_ROOT}/Rapport/exoreports/${e.dataset.ekey}/${e.dataset.idgares}`);
        }

    })
});
;
/* --- adreportjsesc.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adreportjsesc').forEach(function (e) 
    {
        document.querySelector('h3#Titlerepesc').innerHTML = `EXERCICE MENSUEL TICKET GUICHETIER ESCAL`;

        let infgar = document.querySelector('#departgaridentifesc');
        
        if (infgar !== null) 
        infgar.onchange = () => {
            let httpInfosgar;
            if (window.XMLHttpRequest) {
                httpInfosgar = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgar = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idcaissiersesc').options.length = 1;

                    var verificatgar = document.querySelector('#departgaridentifesc').value;
                    
                    httpInfosgar.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeusesesc/${verificatgar}`, true);
                    httpInfosgar.onload = () => {
                        const infosgar = JSON.parse(httpInfosgar.responseText);
                        
                        if (Object.entries(infosgar).length > 0) {                            
                        
                                for (let key in Object.entries(infosgar)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgar[key].roleattribut}`;
                                    opt.innerHTML = `${infosgar[key].username}`;
                                    document.querySelector('#idcaissiersesc').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idcaissiersesc').options.length = 1;
                        }
                        
                    };
                    httpInfosgar.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgar.send();
                };
        e.onclick = function () {
        let tickForm = document.querySelector('#tickFormesc');
            tickForm.setAttribute('action', `${APP_ROOT}/Rapport/exoreportsesc/${e.dataset.ekey}/${e.dataset.idgares}`);
        }

    })
});
;
/* --- adreportpli.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adreportpli').forEach(function (e) 
    {
        document.querySelector('h3#Titlexpglob').innerHTML = `EXERCICE MENSUEL COURRIER GUICHETIER`;

        let expinfos = document.querySelector('#gares');
        
        if (expinfos !== null) 
        expinfos.onchange = () => {
            let httpInforsgexp;
            if (window.XMLHttpRequest) {
                httpInforsgexp = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInforsgexp = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idcaisse').options.length = 1;

                    var expeverifivend = document.querySelector('#gares').value;
                    
                    httpInforsgexp.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${expeverifivend}`, true);
                    httpInforsgexp.onload = () => {
                        const exinfosgs = JSON.parse(httpInforsgexp.responseText);
                        
                        if (Object.entries(exinfosgs).length > 0) {                            
                        
                                for (let key in Object.entries(exinfosgs)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${exinfosgs[key].roleattribut}/${exinfosgs[key].username}`;
                                    opt.innerHTML = `${exinfosgs[key].username}`;
                                    document.querySelector('#idcaisse').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idcaisse').options.length = 1;
                        }
                        
                    };
                    httpInforsgexp.setRequestHeader('Content-Type', 'application/json');
                    httpInforsgexp.send();
                };
        e.onclick = function () {
            let expglobForms = document.querySelector('#expglobForms');
            expglobForms.setAttribute('action', `${APP_ROOT}/Rapport/etatsplis1/${e.dataset.ekey}/${e.dataset.idgares}`);
        }

    })
});
;
/* --- adreportpliesc.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adreportpliesc').forEach(function (e) 
    {
        document.querySelector('h3#Titlexpglobesc').innerHTML = `EXERCICE MENSUEL COURRIERESCAL GUICHETIER`;

        let expinfos = document.querySelector('#garesesc');
        
        if (expinfos !== null) 
        expinfos.onchange = () => {
            let httpInforsgexp;
            if (window.XMLHttpRequest) {
                httpInforsgexp = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInforsgexp = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idcaisseesc').options.length = 1;

                    var expeverifivend = document.querySelector('#garesesc').value;
                    
                    httpInforsgexp.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${expeverifivend}`, true);
                    httpInforsgexp.onload = () => {
                        const exinfosgs = JSON.parse(httpInforsgexp.responseText);
                        
                        if (Object.entries(exinfosgs).length > 0) {                            
                        
                                for (let key in Object.entries(exinfosgs)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${exinfosgs[key].roleattribut}/${exinfosgs[key].username}`;
                                    opt.innerHTML = `${exinfosgs[key].username}`;
                                    document.querySelector('#idcaisseesc').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idcaisseesc').options.length = 1;
                        }
                        
                    };
                    httpInforsgexp.setRequestHeader('Content-Type', 'application/json');
                    httpInforsgexp.send();
                };
        e.onclick = function () {
            let expglobForms = document.querySelector('#expglobFormsesc');
            expglobForms.setAttribute('action', `${APP_ROOT}/Rapport/etatsplis1esc/${e.dataset.ekey}/${e.dataset.idgares}`);
        }

    })
});
;
/* --- adtrioexo.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtrioexo').forEach(function (e) 
    {
        document.querySelector('h3#caisTitleexo').innerHTML = `BROUILLARD(EXERCICE) TICKET`;
        let infgares = document.querySelector('#encaisgarsexo');
        
        if (infgares !== null) 
        infgares.onchange = () => {
            let httpInfoss;
            if (window.XMLHttpRequest) {
                httpInfoss = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfoss = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeusesexo').options.length = 1;

                    var verificatgares = document.querySelector('#encaisgarsexo').value;
                    
                    httpInfoss.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgares}`, true);
                    httpInfoss.onload = () => {
                        const infoss = JSON.parse(httpInfoss.responseText);
                        
                        if (Object.entries(infoss).length > 0) {                            
                        
                                for (let key in Object.entries(infoss)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infoss[key].roleattribut}`;
                                    opt.innerHTML = `${infoss[key].username}`;
                                    document.querySelector('#idvendeusesexo').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idvendeusesexo').options.length = 1;
                        }
                        
                    };
                    httpInfoss.setRequestHeader('Content-Type', 'application/json');
                    httpInfoss.send();
                };
        e.onclick = function () {
        let encaisForm = document.querySelector('#encaismentFormexo');
            encaisForm.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementsexo/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
;
/* --- adtrioexoesc.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtrioexoesc').forEach(function (e) 
    {
        document.querySelector('h3#caisTitleexoesc').innerHTML = `BROUILLARD(EXERCICE)TICKET ESCAL`;
        let infgares = document.querySelector('#encaisgarsexoesc');
        
        if (infgares !== null) 
        infgares.onchange = () => {
            let httpInfoss;
            if (window.XMLHttpRequest) {
                httpInfoss = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfoss = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeusesexoesc').options.length = 1;

                    var verificatgares = document.querySelector('#encaisgarsexoesc').value;
                    
                    httpInfoss.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgares}`, true);
                    httpInfoss.onload = () => {
                        const infoss = JSON.parse(httpInfoss.responseText);
                        
                        if (Object.entries(infoss).length > 0) {                            
                        
                                for (let key in Object.entries(infoss)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infoss[key].roleattribut}`;
                                    opt.innerHTML = `${infoss[key].username}`;
                                    document.querySelector('#idvendeusesexoesc').add(opt);
                                }
                        } 
                        else {
                            document.querySelector('#idvendeusesexoesc').options.length = 1;
                        }
                        
                    };
                    httpInfoss.setRequestHeader('Content-Type', 'application/json');
                    httpInfoss.send();
                };
        e.onclick = function () {
        let encaisForm = document.querySelector('#encaismentFormexoesc');
            encaisForm.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementsexoesc/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
;
/* --- adtrioexoplis.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtrioexoplis').forEach(function (e) 
    {
        document.querySelector('h3#Titlexpglobvers').innerHTML = `BROUILLARD(EXERCICE) COURRIER`;
        let infgares = document.querySelector('#garesvers');
        
        if (infgares !== null) 
        infgares.onchange = () => {
            let httpInfoss;
            if (window.XMLHttpRequest) {
                httpInfoss = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfoss = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idcaissevers').options.length = 1;

                    var verificatgares = document.querySelector('#garesvers').value;
                    
                    httpInfoss.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgares}`, true);
                    httpInfoss.onload = () => {
                        const infoss = JSON.parse(httpInfoss.responseText);
                        
                        if (Object.entries(infoss).length > 0) {                            
                        
                                for (let key in Object.entries(infoss)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infoss[key].roleattribut}/${infoss[key].username}`;
                                    opt.innerHTML = `${infoss[key].username}`;
                                    document.querySelector('#idcaissevers').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idcaissevers').options.length = 1;
                        }
                        
                    };
                    httpInfoss.setRequestHeader('Content-Type', 'application/json');
                    httpInfoss.send();
                };
        e.onclick = function () {
        let encaisFormv = document.querySelector('#expglobFormsvers');
            encaisFormv.setAttribute('action', `${APP_ROOT}/Rapport/etatsverseplis/${e.dataset.ekey}/${e.dataset.idgare}`);
        }

    })
});
;
/* --- adtrioexoplisesc.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtrioexoplisesc').forEach(function (e) 
    {
        document.querySelector('h3#Titlexpglobversesc').innerHTML = `BROUILLARD(EXERCICE) COURRIERESCAL`;
        let infgares = document.querySelector('#garesversesc');
        
        if (infgares !== null) 
        infgares.onchange = () => {
            let httpInfoss;
            if (window.XMLHttpRequest) {
                httpInfoss = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfoss = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idcaisseversesc').options.length = 1;

                    var verificatgares = document.querySelector('#garesversesc').value;
                    
                    httpInfoss.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgares}`, true);
                    httpInfoss.onload = () => {
                        const infoss = JSON.parse(httpInfoss.responseText);
                        
                        if (Object.entries(infoss).length > 0) {                            
                        
                                for (let key in Object.entries(infoss)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infoss[key].roleattribut}/${infoss[key].username}`;
                                    opt.innerHTML = `${infoss[key].username}`;
                                    document.querySelector('#idcaisseversesc').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idcaisseversesc').options.length = 1;
                        }
                        
                    };
                    httpInfoss.setRequestHeader('Content-Type', 'application/json');
                    httpInfoss.send();
                };
        e.onclick = function () {
        let encaisFormv = document.querySelector('#expglobFormsversesc');
            encaisFormv.setAttribute('action', `${APP_ROOT}/Rapport/etatsverseplisesc/${e.dataset.ekey}/${e.dataset.idgare}`);
        }

    })
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
/* --- adtrioexobag.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtrioexobag').forEach(function (e) 
    {
        document.querySelector('h3#Titlexpglobversbg').innerHTML = `BROUILLARD(EXERCICE) BAGAGES`;
        let infgaresb = document.querySelector('#departgarexobge');
        
        if (infgaresb !== null) 
        infgaresb.onchange = () => {
            let httpInfossb;
            if (window.XMLHttpRequest) {
                httpInfossb = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfossb = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#dvendeuseidexobg').options.length = 1;

                    var verificatgaresb = document.querySelector('#departgarexobge').value;
                    
                    httpInfossb.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgaresb}`, true);
                    httpInfossb.onload = () => {
                        const infossb = JSON.parse(httpInfossb.responseText);
                        
                        if (Object.entries(infossb).length > 0) {                            
                        
                                for (let key in Object.entries(infossb)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infossb[key].roleattribut}/${infossb[key].username}`;
                                    opt.innerHTML = `${infossb[key].username}`;
                                    document.querySelector('#dvendeuseidexobg').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#dvendeuseidexobg').options.length = 1;
                        }
                        
                    };
                    httpInfossb.setRequestHeader('Content-Type', 'application/json');
                    httpInfossb.send();
                };
        e.onclick = function () {
        let encaisFormvb = document.querySelector('#expglobFormsversbg');
            encaisFormvb.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementsexobag/${e.dataset.ekey}/${e.dataset.idgare}`);
        }

    })
});
;
/* --- adtrioexobagesc.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtrioexobagesc').forEach(function (e) 
    {
        document.querySelector('h3#Titlexpglobversbgesc').innerHTML = `BROUILLARD(EXERCICE) BAGAGESESCAL`;
        let infgaresbe = document.querySelector('#departgarexobgeesc');
        
        if (infgaresbe !== null) 
        infgaresbe.onchange = () => {
            let httpInfossbe;
            if (window.XMLHttpRequest) {
                httpInfossbe = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfossbe = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#dvendeuseidexobgesc').options.length = 1;

                    var verificatgaresbe = document.querySelector('#departgarexobgeesc').value;
                    
                    httpInfossbe.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgaresbe}`, true);
                    httpInfossbe.onload = () => {
                        const infossbe = JSON.parse(httpInfossbe.responseText);
                        
                        if (Object.entries(infossbe).length > 0) {                            
                        
                                for (let key in Object.entries(infossbe)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infossbe[key].roleattribut}/${infossbe[key].username}`;
                                    opt.innerHTML = `${infossbe[key].username}`;
                                    document.querySelector('#dvendeuseidexobgesc').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#dvendeuseidexobgesc').options.length = 1;
                        }
                        
                    };
                    httpInfossbe.setRequestHeader('Content-Type', 'application/json');
                    httpInfossbe.send();
                };
        e.onclick = function () {
        let encaisFormvbe = document.querySelector('#expglobFormsversbgesc');
            encaisFormvbe.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementsexobagesc/${e.dataset.ekey}/${e.dataset.idgare}`);
        }

    })
});
;
/* --- tri-filtre-dynamique.js --- */
/**
 * Cascade tri états : COMPAGNIE → GARE (trigares) → LIGNES (trilignes) / guichetiers (triactifs).
 * Même logique que la recette globale pour tous les états branchés.
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
        select.options.length = 0;
        const keep = document.createElement('option');
        keep.value = '';
        keep.text = firstText || '';
        select.add(keep);
    }

    function fillUsers(select, rows, valueMode) {
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
            const ra = row.roleattribut != null ? String(row.roleattribut) : '';
            if (ra === '') {
                return;
            }
            const label = row.username || ra;
            const opt = document.createElement('option');
            opt.value = (valueMode === 'slash') ? (ra + '/' + label) : ra;
            opt.text = label;
            select.add(opt);
        });
        if (prev) {
            select.value = prev;
            if (select.value !== prev) {
                const keep = document.createElement('option');
                keep.value = prev;
                keep.text = prev.indexOf('/') >= 0 ? prev.split('/').slice(1).join('/') : prev;
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

    function fillGares(select, rows, placeholder) {
        if (!select) {
            return;
        }
        resetSelect(select, placeholder || 'Choisir une gare');
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
            opt.text = row.nom_gaep || val;
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
        const gareEl = cfg.gare ? q(root, cfg.gare) : null;
        const duEl = cfg.du ? q(root, cfg.du) : null;
        const auEl = cfg.au ? q(root, cfg.au) : null;
        const usersEl = cfg.users ? q(root, cfg.users) : null;
        const lignesEl = cfg.lignes ? q(root, cfg.lignes) : null;
        const userType = cfg.userType || 'ticket';
        const userValueMode = cfg.userValueMode || 'id';
        const garePlaceholder = cfg.garePlaceholder || 'Choisir une gare';
        const seqUsers = { seq: 0 };
        const seqLignes = { seq: 0 };
        const seqGares = { seq: 0 };

        // Neutralise les anciens handlers .onchange (trivendeuses) sur la gare.
        if (gareEl) {
            gareEl.onchange = null;
        }

        function compVal() {
            return compEl ? String(compEl.value || '').trim() : '';
        }

        function gareVal() {
            return gareEl ? String(gareEl.value || '').trim() : '';
        }

        function clearDownstreamFromComp() {
            if (gareEl) {
                resetSelect(gareEl, garePlaceholder);
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
                fillGares(gareEl, rows, garePlaceholder);
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
            jsonGet(url, (rows) => fillUsers(usersEl, rows, userValueMode), seqUsers, seq);
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
            // pour forcer Compagnie → Gare (comme recette globale).
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
        // --- Recette / versements (référence) ---
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

        wire({
            root: '#encaismentFormexo',
            comp: '[name="_compagexo"]',
            gare: '[name="departgarexo"]',
            du: '[name="datedexo"]',
            au: '[name="datefexo"]',
            users: '#idvendeusesexo',
            userType: 'ticket',
        });

        wire({
            root: '#encaismentFormexoesc',
            comp: '[name="_compagexoesc"]',
            gare: '[name="departgarexoesc"]',
            du: '[name="datedexoesc"]',
            au: '[name="datefexoesc"]',
            users: '#idvendeusesexoesc',
            userType: 'ticket',
        });

        // --- États ticket / escale ---
        wire({
            root: '#tickForms',
            comp: '[name="_compag"]',
            gare: '[name="departgar"]',
            du: '[name="datedebut"]',
            au: '[name="datefin"]',
            users: '#idscaissier',
            lignes: '#ligneaxe',
            userType: 'ticket',
        });

        wire({
            root: '#tickFormsesc',
            comp: '[name="_compagesc"]',
            gare: '[name="departgaresc"]',
            du: '[name="datedebutesc"]',
            au: '[name="datefinesc"]',
            users: '#idscaissieresc',
            lignes: '#ligneaxesc',
            userType: 'ticket',
        });

        wire({
            root: '#tickForm',
            comp: '[name="_compag"]',
            gare: '[name="departgar"]',
            du: '[name="datedebut"]',
            au: '[name="datefin"]',
            users: '#idcaissiers',
            lignes: '#ligneaxe',
            userType: 'ticket',
        });

        wire({
            root: '#tickFormesc',
            comp: '[name="_compagesc"]',
            gare: '[name="departgaresc"]',
            du: '[name="datedebutesc"]',
            au: '[name="datefinesc"]',
            users: '#idcaissiersesc',
            lignes: '#ligneaxeesc',
            userType: 'ticket',
        });

        wire({
            root: '#form_reporticket',
            comp: '[name="_compag"]',
            gare: '[name="departgar"]',
            du: '[name="datedebut"]',
            au: '[name="datefin"]',
            lignes: '[name="axeligne"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });

        wire({
            root: '#form_reporticketesc',
            comp: '[name="_compagesc"]',
            gare: '[name="departgaresc"]',
            du: '[name="datedebutesc"]',
            au: '[name="datefinesc"]',
            lignes: '[name="axeligneesc"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });

        // --- Bagages ---
        wire({
            root: '#tickFormop',
            comp: '[name="_compagbagop"]',
            gare: '[name="departgarbagop"]',
            du: '[name="datedebutbagop"]',
            au: '[name="datefinbagop"]',
            users: '#idvendeuseop',
            lignes: '[name="axelignebagop"]',
            userType: 'op',
        });

        wire({
            root: '#tickFormopesc',
            comp: '[name="_compagbagopesc"]',
            gare: '[name="departgarbagopesc"]',
            du: '[name="datedebutbagopesc"]',
            au: '[name="datefinbagopesc"]',
            users: '#idvendeuseopesc',
            lignes: '[name="axelignebagopesc"]',
            userType: 'op',
        });

        wire({
            root: '#tickFormopgl',
            comp: '[name="_compagbagopgl"]',
            gare: '[name="departgarbagopgl"]',
            du: '[name="datedebutbagopgl"]',
            au: '[name="datefinbagopgl"]',
            users: '#idvendeuseopgl',
            lignes: '[name="axelignebagopgl"]',
            userType: 'op',
        });

        wire({
            root: '#tickFormopglesc',
            comp: '[name="_compagbagopglesc"]',
            gare: '[name="departgarbagopglesc"]',
            du: '[name="datedebutbagopglesc"]',
            au: '[name="datefinbagopglesc"]',
            users: '#idvendeuseopglesc',
            lignes: '[name="axelignebagopglesc"]',
            userType: 'op',
        });

        wire({
            root: '#form_reportbag',
            comp: '[name="_compagbg"]',
            gare: '[name="departgarbg"]',
            du: '[name="datedebutbg"]',
            au: '[name="datefinbg"]',
            lignes: '[name="axelignebg"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });

        wire({
            root: '#form_reportbagesc',
            comp: '[name="_compagbgesc"]',
            gare: '[name="departgarbgesc"]',
            du: '[name="datedebutbgesc"]',
            au: '[name="datefinbgesc"]',
            lignes: '[name="axelignebgesc"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });

        wire({
            root: '#expglobFormsversbg',
            comp: '[name="_compagexobg"]',
            gare: '[name="departgarexobg"]',
            du: '[name="datedexobg"]',
            au: '[name="datefexobg"]',
            users: '[name="vendeuseidexobg"]',
            userType: 'ticket',
            userValueMode: 'slash',
        });

        wire({
            root: '#expglobFormsversbgesc',
            comp: '[name="_compagexobgesc"]',
            gare: '[name="departgarexobgesc"]',
            du: '[name="datedexobgesc"]',
            au: '[name="datefexobgesc"]',
            users: '[name="vendeuseidexobgesc"]',
            userType: 'ticket',
            userValueMode: 'slash',
        });

        // --- Courrier ---
        wire({
            root: '#form_recaptglcourrier',
            comp: '[name="_compagcrgl"]',
            gare: '[name="departgarcrgl"]',
            du: '[name="datedebutcrgl"]',
            au: '[name="datefincrgl"]',
            lignes: '[name="axelignecrgl"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });

        wire({
            root: '#form_recaptglcourrieresc',
            comp: '[name="_compagcrglesc"]',
            gare: '[name="departgarcrglesc"]',
            du: '[name="datedebutcrglesc"]',
            au: '[name="datefincrglesc"]',
            lignes: '[name="axelignecrglesc"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });

        wire({
            root: '#expglobFormsg',
            comp: '[name="_compagnplig"]',
            gare: '[name="deptgaresidplig"]',
            du: '[name="datesdebutsplig"]',
            au: '[name="datesfinsplig"]',
            users: '[name="caissesidplig"]',
            lignes: '[name="axelignesplig"]',
            userType: 'ticket',
            userValueMode: 'slash',
        });

        wire({
            root: '#expglobFormsgesc',
            comp: '[name="_compagnpligesc"]',
            gare: '[name="deptgaresidpligesc"]',
            du: '[name="datesdebutspligesc"]',
            au: '[name="datesfinspligesc"]',
            users: '[name="caissesidpligesc"]',
            lignes: '[name="axelignespligesc"]',
            userType: 'ticket',
            userValueMode: 'slash',
        });

        wire({
            root: '#expglobForms',
            comp: '[name="_compagnpli"]',
            gare: '[name="deptgaresidpli"]',
            du: '[name="datesdebutspli"]',
            au: '[name="datesfinspli"]',
            users: '[name="caissesidpli"]',
            lignes: '[name="axelignespli"]',
            userType: 'ticket',
            userValueMode: 'slash',
        });

        wire({
            root: '#expglobFormsesc',
            comp: '[name="_compagnpliesc"]',
            gare: '[name="deptgaresidpliesc"]',
            du: '[name="datesdebutspliesc"]',
            au: '[name="datesfinspliesc"]',
            users: '[name="caissesidpliesc"]',
            lignes: '[name="axelignespliesc"]',
            userType: 'ticket',
            userValueMode: 'slash',
        });

        wire({
            root: '#expglobFormsvers',
            comp: '[name="_compagnplivers"]',
            gare: '[name="deptgaresidplivers"]',
            du: '[name="datesdebutsplivers"]',
            au: '[name="datesfinsplivers"]',
            users: '[name="caissesidplivers"]',
            userType: 'ticket',
            userValueMode: 'slash',
        });

        wire({
            root: '#expglobFormsversesc',
            comp: '[name="_compagnpliversesc"]',
            gare: '[name="deptgaresidpliversesc"]',
            du: '[name="datesdebutspliversesc"]',
            au: '[name="datesfinspliversesc"]',
            users: '[name="caissesidpliversesc"]',
            userType: 'ticket',
            userValueMode: 'slash',
        });

        wire({
            root: '#tickFormscourdep',
            comp: '[name="_compagcourdep"]',
            gare: '[name="departgarcourdep"]',
            du: '[name="datedebutcourdep"]',
            au: '[name="datefincourdep"]',
            users: '#idscaissiercourdep',
            userType: 'ticket',
        });

        // --- Récaps ex mensuels compagnie (ticket / bagage / courrier) ---
        wire({
            root: '#form-recapt-0',
            comp: '[name="_compag"]',
            gare: '[name="departgar"]',
            du: '[name="datedebut"]',
            au: '[name="datefin"]',
            lignes: '[name="axeligne"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });

        wire({
            root: '#form-recaptes-0',
            comp: '[name="_compages"]',
            gare: '[name="departgares"]',
            du: '[name="datedebutes"]',
            au: '[name="datefines"]',
            lignes: '[name="axelignees"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });

        wire({
            root: '#form-recaptbg-0',
            comp: '[name="_compagbag"]',
            gare: '[name="departgarbag"]',
            du: '[name="datedebutbag"]',
            au: '[name="datefinbag"]',
            lignes: '[name="axelignebag"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });

        wire({
            root: '#form-recaptbgesc-0',
            comp: '[name="_compagbagesc"]',
            gare: '[name="departgarbagesc"]',
            du: '[name="datedebutbagesc"]',
            au: '[name="datefinbagesc"]',
            lignes: '[name="axelignebagesc"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });

        wire({
            root: '#form-recaptcr-0',
            comp: '[name="_compagcr"]',
            gare: '[name="departgarcr"]',
            du: '[name="datedebutcr"]',
            au: '[name="datefincr"]',
            lignes: '[name="axelignecr"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });

        wire({
            root: '#form-recaptcresc-0',
            comp: '[name="_compagcresc"]',
            gare: '[name="departgarcresc"]',
            du: '[name="datedebutcresc"]',
            au: '[name="datefincresc"]',
            lignes: '[name="axelignecresc"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });

        // --- Déclarations (clarer + états déclarés) ---
        wire({
            root: '#form-clarrecapt-0',
            comp: '[name="_compagdc"]',
            gare: '[name="departgardc"]',
            du: '[name="datedebutdc"]',
            au: '[name="datefindc"]',
            lignes: '[name="axelignedc"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });
        wire({
            root: '#form-declarrecapt-0',
            comp: '[name="_compagd"]',
            gare: '[name="departgard"]',
            du: '[name="datedebutd"]',
            au: '[name="datefind"]',
            lignes: '[name="axeligned"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });
        wire({
            root: '#form-clarrecapes-0',
            comp: '[name="_compagdces"]',
            gare: '[name="departgardces"]',
            du: '[name="datedebutdces"]',
            au: '[name="datefindces"]',
            lignes: '[name="axelignedces"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });
        wire({
            root: '#form-declarrecaptes-0',
            comp: '[name="_compagdes"]',
            gare: '[name="departgardes"]',
            du: '[name="datedebutdes"]',
            au: '[name="datefindes"]',
            lignes: '[name="axelignedes"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });
        wire({
            root: '#form-clarrecaptbg-0',
            comp: '[name="_compagdcbg"]',
            gare: '[name="departgardcbg"]',
            du: '[name="datedebutdcbg"]',
            au: '[name="datefindcbg"]',
            lignes: '[name="axelignedcbg"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });
        wire({
            root: '#form-declarrecaptbg-0',
            comp: '[name="_compagdbg"]',
            gare: '[name="departgardbg"]',
            du: '[name="datedebutdbg"]',
            au: '[name="datefindbg"]',
            lignes: '[name="axelignedbg"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });
        wire({
            root: '#form-clarrecaptbgesc-0',
            comp: '[name="_compagdcbgesc"]',
            gare: '[name="departgardcbgesc"]',
            du: '[name="datedebutdcbgesc"]',
            au: '[name="datefindcbgesc"]',
            lignes: '[name="axelignedcbgesc"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });
        wire({
            root: '#form-declarrecaptbgesc-0',
            comp: '[name="_compagdbgesc"]',
            gare: '[name="departgardbgesc"]',
            du: '[name="datedebutdbgesc"]',
            au: '[name="datefindbgesc"]',
            lignes: '[name="axelignedbgesc"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });
        wire({
            root: '#form-clarrecaptcr-0',
            comp: '[name="_compagcrcl"]',
            gare: '[name="departgarcrcl"]',
            du: '[name="datedebutcrcl"]',
            au: '[name="datefincrcl"]',
            lignes: '[name="axelignecrcl"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });
        wire({
            root: '#form-declarrecaptcr-0',
            comp: '[name="_compagcrcld"]',
            gare: '[name="departgarcrcld"]',
            du: '[name="datedebutcrcld"]',
            au: '[name="datefincrcld"]',
            lignes: '[name="axelignecrcld"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });
        wire({
            root: '#form-clarrecaptcresc-0',
            comp: '[name="_compagcrclesc"]',
            gare: '[name="departgarcrclesc"]',
            du: '[name="datedebutcrclesc"]',
            au: '[name="datefincrclesc"]',
            lignes: '[name="axelignecrclesc"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });
        wire({
            root: '#form-declarrecaptcresc-0',
            comp: '[name="_compagcrcldesc"]',
            gare: '[name="departgarcrcldesc"]',
            du: '[name="datedebutcrcldesc"]',
            au: '[name="datefincrcldesc"]',
            lignes: '[name="axelignecrcldesc"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });

        // --- Manifests hebdomadaires ---
        wire({
            root: '#form-nifestheb-0',
            comp: '[name="_compag"]',
            gare: '[name="departgar"]',
            du: '[name="datedebut"]',
            au: '[name="datefin"]',
            lignes: '[name="axeligne"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });
        wire({
            root: '#form-nifesthebesc-0',
            comp: '[name="_compagesc"]',
            gare: '[name="departgaresc"]',
            du: '[name="datedebutesc"]',
            au: '[name="datefinesc"]',
            lignes: '[name="axeligneesc"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });
        wire({
            root: '#form-recaptheb-0',
            comp: '[name="_compagheb"]',
            gare: '[name="departgarheb"]',
            du: '[name="datedebutheb"]',
            au: '[name="datefinheb"]',
            lignes: '[name="axeligneheb"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });
        wire({
            root: '#form-recapthebesc-0',
            comp: '[name="_compaghebesc"]',
            gare: '[name="departgarhebesc"]',
            du: '[name="datedebuthebesc"]',
            au: '[name="datefinhebesc"]',
            lignes: '[name="axelignehebesc"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });
        wire({
            root: '#form-recaptbgheb-0',
            comp: '[name="_compaghebbg"]',
            gare: '[name="departgarhebbg"]',
            du: '[name="datedebuthebbg"]',
            au: '[name="datefinhebbg"]',
            lignes: '[name="axelignehebbg"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });
        wire({
            root: '#form-recaptbgescheb-0',
            comp: '[name="_compaghebbge"]',
            gare: '[name="departgarhebbge"]',
            du: '[name="datedebuthebbge"]',
            au: '[name="datefinhebbge"]',
            lignes: '[name="axelignehebbge"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });

        // --- Listes passagers (exercice / global ±escal) ---
        wire({
            root: '#exopassagers-0',
            comp: '[name="nomcomps"]',
            gare: '[name="nomgares"]',
            du: '[name="dateps1"]',
            au: '[name="dateps2"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });
        wire({
            root: '#exopassagersesc-0',
            comp: '[name="nomcompsesc"]',
            gare: '[name="nomgaresesc"]',
            du: '[name="dateps1esc"]',
            au: '[name="dateps2esc"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });
        wire({
            root: '#exopassagersgl-0',
            comp: '[name="nomcomps"]',
            gare: '[name="nomgares"]',
            du: '[name="dateps1"]',
            au: '[name="dateps2"]',
            garePlaceholder: 'Toutes',
            userType: 'ticket',
        });
        wire({
            root: '#exopassagersglesc-0',
            comp: '[name="nomcompsesc"]',
            gare: '[name="nomgaresesc"]',
            du: '[name="dateps1esc"]',
            au: '[name="dateps2esc"]',
            garePlaceholder: 'Toutes',
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
/* --- recaptbagexop.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.recaptbagexop').forEach(function (e) 
    {
        document.querySelector('h3#optitle').innerHTML = `EXERCICE MENSUEL BAGAGE OPERATEUR`;

        let infgars = document.querySelector('#departgardpbagop');
        
        if (infgars !== null) 
        infgars.onchange = () => {
            let httpInfosgars;
            if (window.XMLHttpRequest) {
                httpInfosgars = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgars = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeuseop').options.length = 1;

                    var verificatgars = document.querySelector('#departgardpbagop').value;
                    
                    httpInfosgars.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeusesop/${verificatgars}`, true);
                    httpInfosgars.onload = () => {
                        const infosgars = JSON.parse(httpInfosgars.responseText);
                        
                        if (Object.entries(infosgars).length > 0) {                            
                        
                                for (let key in Object.entries(infosgars)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgars[key].roleattribut}`;
                                    opt.innerHTML = `${infosgars[key].username}`;
                                    document.querySelector('#idvendeuseop').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idvendeuseop').options.length = 1;
                        }
                        
                    };
                    httpInfosgars.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgars.send();
                };
        e.onclick = function () {
        let tickFormsgl = document.querySelector('#tickFormop');
            tickFormsgl.setAttribute('action', `${APP_ROOT}/Rapport/exercicesbagop/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
;
/* --- recaptbagexopesc.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.recaptbagexopesc').forEach(function (e) 
    {
        document.querySelector('h3#optitleesc').innerHTML = `EXERCICE MENSUEL BAGAGEESCAL OPERATEUR`;

        let infgars = document.querySelector('#departgardpbagopesc');
        
        if (infgars !== null) 
        infgars.onchange = () => {
            let httpInfosgars;
            if (window.XMLHttpRequest) {
                httpInfosgars = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgars = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeuseopesc').options.length = 1;

                    var verificatgars = document.querySelector('#departgardpbagopesc').value;
                    
                    httpInfosgars.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeusesesc/${verificatgars}`, true);
                    httpInfosgars.onload = () => {
                        const infosgars = JSON.parse(httpInfosgars.responseText);
                        
                        if (Object.entries(infosgars).length > 0) {                            
                        
                                for (let key in Object.entries(infosgars)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgars[key].roleattribut}`;
                                    opt.innerHTML = `${infosgars[key].username}`;
                                    document.querySelector('#idvendeuseopesc').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idvendeuseopesc').options.length = 1;
                        }
                        
                    };
                    httpInfosgars.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgars.send();
                };
        e.onclick = function () {
        let tickFormsgl = document.querySelector('#tickFormopesc');
            tickFormsgl.setAttribute('action', `${APP_ROOT}/Rapport/exercicesbagopesc/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
