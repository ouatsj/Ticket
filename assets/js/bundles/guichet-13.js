/* Bundle guichet role=13 — genere par scripts/build_guichet_bundles.php */
/* --- addetat.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addetat').forEach(function (e) 
    {
        document.querySelector('h3#etatTitle').innerHTML = `ETAT TICKETS`;

            let gd = document.querySelector('#garesid');
            if (gd !== null)
                gd.onchange = () => {
                    let httpgares;
                    if (window.XMLHttpRequest) {
                        httpgares = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpgares = new ActiveXObject("Microsoft.XMLHTTP");
                    }

                    document.querySelector('#venteid').options.length = 1;
                    var dt = document.querySelector('#garesid').value;
                    const idgd = document.querySelector('#garesid')
                    .options[document.querySelector('#garesid').options.selectedIndex].value;
                    
                    httpgares.open('GET', window.location.origin + `${APP_ROOT}/programmes/vente/${idgd}`, true);
                    httpgares.onload = () => {
                        const resul = JSON.parse(httpgares.responseText);
                        if(resul == null){

                            
                        
                        } else {
                            if (Object.entries(resul).length >= 1) 
                            {
                                
                                for (let key in Object.entries(resul)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${resul[key].roleattribut}`;
                                        opt.innerHTML = `${resul[key].username}`;
                                        document.querySelector('#venteid').add(opt);
                                    }
                            } else {
                                document.querySelector('#venteid').options.length = 1;
                            }
                            
                        }
                    };
                    httpgares.setRequestHeader('Content-Type', 'application/json');
                    httpgares.send();
                                         
            };
        
        
        e.onclick = function () {
        let Forms = document.querySelector('#Forms');
        Forms.setAttribute('action', `${APP_ROOT}/Rapport/etatpassagers/${e.dataset.cle_compagnie}`);
        }

    })
});
;
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
        document.querySelector('h3#Titlerepversgl').innerHTML = `TRI REPORT GLOBAL DES RECETTES`;

        let infgarvers = document.querySelector('#departgaridentifversgl');
        
        if (infgarvers !== null) 
        infgarvers.onchange = () => {
            let httpInfosgarvers;
            if (window.XMLHttpRequest) {
                httpInfosgarvers = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgarvers = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idcaissiersversgl').options.length = 1;

                    var verificatgarvers = document.querySelector('#departgaridentifversgl').value;
                    
                    httpInfosgarvers.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgarvers}`, true);
                    httpInfosgarvers.onload = () => {
                        const infosgarvers = JSON.parse(httpInfosgarvers.responseText);
                        
                        if (Object.entries(infosgarvers).length > 0) {                            
                        
                                for (let key in Object.entries(infosgarvers)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgarvers[key].roleattribut}`;
                                    opt.innerHTML = `${infosgarvers[key].username}`;
                                    document.querySelector('#idcaissiersversgl').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idcaissiersversgl').options.length = 1;
                        }
                        
                    };
                    httpInfosgarvers.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgarvers.send();
                };
        e.onclick = function () {
        let tickversForm = document.querySelector('#tickversglForm');
            tickversForm.setAttribute('action', `${APP_ROOT}/Rapport/exoreportsversgl/${e.dataset.ekey}/${e.dataset.idgares}`);
        }

    })
});
;
/* --- adtrio.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtrio').forEach(function (e) 
    {
        document.querySelector('h3#caisTitle').innerHTML = `VERSEMENT TICKET GUICHETIER`;
        let infgares = document.querySelector('#encaisgars');
        
        if (infgares !== null) 
        infgares.onchange = () => {
            let httpInfoss;
            if (window.XMLHttpRequest) {
                httpInfoss = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfoss = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeuses').options.length = 1;

                    var verificatgares = document.querySelector('#encaisgars').value;
                    
                    httpInfoss.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trioperateur/${verificatgares}`, true);
                    httpInfoss.onload = () => {
                        const infoss = JSON.parse(httpInfoss.responseText);
                        
                        if (Object.entries(infoss).length > 0) {                            
                        
                                for (let key in Object.entries(infoss)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infoss[key].roleattribut}`;
                                    opt.innerHTML = `${infoss[key].username}`;
                                    document.querySelector('#idvendeuses').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idvendeuses').options.length = 1;
                        }
                        
                    };
                    httpInfoss.setRequestHeader('Content-Type', 'application/json');
                    httpInfoss.send();
                };
        e.onclick = function () {
        let encaisForm = document.querySelector('#encaismentForm');
            encaisForm.setAttribute('action', `${APP_ROOT}/Rapport/triencaissements/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
;
/* --- adtriocour.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtriocour').forEach(function (e) 
    {
        document.querySelector('h3#caisTitlecour').innerHTML = `VERSEMENT COURRIER GUICHETIER`;
        let infgarescr = document.querySelector('#encaisgarscour');
        
        if (infgarescr !== null) 
        infgarescr.onchange = () => {
            let httpInfosscr;
            if (window.XMLHttpRequest) {
                httpInfosscr = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosscr = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeusescour').options.length = 1;

                    var verificatgarescr = document.querySelector('#encaisgarscour').value;
                    
                    httpInfosscr.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgarescr}`, true);
                    httpInfosscr.onload = () => {
                        const infosscr = JSON.parse(httpInfosscr.responseText);
                        
                        if (Object.entries(infosscr).length > 0) {                            
                    
                                for (let key in Object.entries(infosscr)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosscr[key].roleattribut}`;
                                    opt.innerHTML = `${infosscr[key].username}`;
                                    document.querySelector('#idvendeusescour').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idvendeusescour').options.length = 1;
                        }
                        
                    };
                    httpInfosscr.setRequestHeader('Content-Type', 'application/json');
                    httpInfosscr.send();
                };
        e.onclick = function () {
        let encaisFormcr = document.querySelector('#encaismentFormcour');
            encaisFormcr.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementscour/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
;
/* --- adtriobag.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtriobag').forEach(function (e) 
    {
        document.querySelector('h3#caisTitlebag').innerHTML = `VERSEMENT BAGAGES`;
        let infgares = document.querySelector('#encaisgarsbag');
        
        if (infgares !== null) 
        infgares.onchange = () => {
            let httpInfoss;
            if (window.XMLHttpRequest) {
                httpInfoss = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfoss = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeusesbag').options.length = 1;

                    var verificatgares = document.querySelector('#encaisgarsbag').value;
                    
                    httpInfoss.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeusesop/${verificatgares}`, true);
                    httpInfoss.onload = () => {
                        const infoss = JSON.parse(httpInfoss.responseText);
                        
                        if (Object.entries(infoss).length > 0) {                            
                        
                                for (let key in Object.entries(infoss)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infoss[key].roleattribut}`;
                                    opt.innerHTML = `${infoss[key].username}`;
                                    document.querySelector('#idvendeusesbag').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idvendeusesbag').options.length = 1;
                        }
                        
                    };
                    httpInfoss.setRequestHeader('Content-Type', 'application/json');
                    httpInfoss.send();
                };
        e.onclick = function () {
        let encaisForm = document.querySelector('#encaismentFormbag');
            encaisForm.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementsbag/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
;
/* --- adverssg.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adverssg').forEach(function (e) 
    {
        document.querySelector('h3#caiTitlesg').innerHTML = `RECETTE GLOBALE TICKET PAR GARE`;

        let infgares = document.querySelector('#encaisgarsg');
        
        if (infgares !== null) 
        infgares.onchange = () => {
            let httpInfoss;
            if (window.XMLHttpRequest) {
                httpInfoss = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfoss = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeusesg').options.length = 1;

                    var verificatgares = document.querySelector('#encaisgarsg').value;
                    
                    httpInfoss.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trioperateur/${verificatgares}`, true);
                    httpInfoss.onload = () => {
                        const infoss = JSON.parse(httpInfoss.responseText);
                        
                        if (Object.entries(infoss).length > 0) {                            
                        
                                for (let key in Object.entries(infoss)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infoss[key].roleattribut}`;
                                    opt.innerHTML = `${infoss[key].username}`;
                                    document.querySelector('#idvendeusesg').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idvendeusesg').options.length = 1;
                        }
                        
                    };
                    httpInfoss.setRequestHeader('Content-Type', 'application/json');
                    httpInfoss.send();
                };
        e.onclick = function () {
        let encaisForms = document.querySelector('#encaisFormssg');
            encaisForms.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementsg/${e.dataset.ekey}/${e.dataset.idsgare}/${e.dataset.idsggare}`);
        }

    })
});
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
