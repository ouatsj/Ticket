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