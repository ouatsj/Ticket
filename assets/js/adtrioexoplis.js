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