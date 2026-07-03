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