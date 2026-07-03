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