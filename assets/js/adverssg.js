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