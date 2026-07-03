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