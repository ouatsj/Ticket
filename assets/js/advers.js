document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.advers').forEach(function (e) 
    {
        document.querySelector('h3#caiTitle').innerHTML = `TRI DES ETATS DE VERSEMENT PAR AXE`;

        let infgares = document.querySelector('#encaisgar');
        
        if (infgares !== null) 
        infgares.onchange = () => {
            let httpInfoss;
            if (window.XMLHttpRequest) {
                httpInfoss = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfoss = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeuse').options.length = 1;

                    var verificatgares = document.querySelector('#encaisgar').value;
                    
                    httpInfoss.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trioperateur/${verificatgares}`, true);
                    httpInfoss.onload = () => {
                        const infoss = JSON.parse(httpInfoss.responseText);
                        
                        if (Object.entries(infoss).length > 0) {                            
                        
                                for (let key in Object.entries(infoss)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infoss[key].roleattribut}`;
                                    opt.innerHTML = `${infoss[key].username}`;
                                    document.querySelector('#idvendeuse').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idvendeuse').options.length = 1;
                        }
                        
                    };
                    httpInfoss.setRequestHeader('Content-Type', 'application/json');
                    httpInfoss.send();
                };
        e.onclick = function () {
        let encaisForms = document.querySelector('#encaisForms');
            encaisForms.setAttribute('action', `${APP_ROOT}/Rapport/triencaissement/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});