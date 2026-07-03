document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adreportgl').forEach(function (e) 
    {
        document.querySelector('h3#Titlereps').innerHTML = `ETAT GLOBAL TICKET GUICHETIER`;

        let infgars = document.querySelector('#garidentifs');
        
        if (infgars !== null) 
        infgars.onchange = () => {
            let httpInfosgars;
            if (window.XMLHttpRequest) {
                httpInfosgars = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgars = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idscaissier').options.length = 1;

                    var verificatgars = document.querySelector('#garidentifs').value;
                    
                    httpInfosgars.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgars}`, true);
                    httpInfosgars.onload = () => {
                        const infosgars = JSON.parse(httpInfosgars.responseText);
                        
                        if (Object.entries(infosgars).length > 0) {                            
                        
                                for (let key in Object.entries(infosgars)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgars[key].roleattribut}`;
                                    opt.innerHTML = `${infosgars[key].username}`;
                                    document.querySelector('#idscaissier').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idscaissier').options.length = 1;
                        }
                        
                    };
                    httpInfosgars.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgars.send();
                };
        e.onclick = function () {
        let tickForms = document.querySelector('#tickForms');
            tickForms.setAttribute('action', `${APP_ROOT}/Rapport/reports/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});