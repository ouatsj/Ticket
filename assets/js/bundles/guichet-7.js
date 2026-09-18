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
                                    opt.value = `${exinfosgs[key].roleattribut}/${exinfosgs[key].first_name} ${exinfosgs[key].last_name}`;
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
                                    opt.value = `${exinfosgs[key].roleattribut}/${exinfosgs[key].first_name} ${exinfosgs[key].last_name}`;
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
            expglobForms.setAttribute('action', `${APP_ROOT}/Rapport/etatsplis1/${e.dataset.ekey}/${e.dataset.idgares}`);
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
                                    opt.value = `${infoss[key].roleattribut}/${infoss[key].first_name} ${infoss[key].last_name}`;
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
                                    opt.value = `${infoss[key].roleattribut}/${infoss[key].first_name} ${infoss[key].last_name}`;
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
                                    opt.value = `${infossb[key].roleattribut}/${infossb[key].first_name} ${infossb[key].last_name}`;
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
                                    opt.value = `${infossbe[key].roleattribut}/${infossbe[key].first_name} ${infossbe[key].last_name}`;
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
