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