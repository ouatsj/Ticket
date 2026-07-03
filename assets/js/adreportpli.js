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