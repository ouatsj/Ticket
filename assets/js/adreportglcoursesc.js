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