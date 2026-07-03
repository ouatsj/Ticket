document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.recaptbagexopesc').forEach(function (e) 
    {
        document.querySelector('h3#optitleesc').innerHTML = `EXERCICE MENSUEL BAGAGEESCAL OPERATEUR`;

        let infgars = document.querySelector('#departgardpbagopesc');
        
        if (infgars !== null) 
        infgars.onchange = () => {
            let httpInfosgars;
            if (window.XMLHttpRequest) {
                httpInfosgars = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgars = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeuseopesc').options.length = 1;

                    var verificatgars = document.querySelector('#departgardpbagopesc').value;
                    
                    httpInfosgars.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeusesesc/${verificatgars}`, true);
                    httpInfosgars.onload = () => {
                        const infosgars = JSON.parse(httpInfosgars.responseText);
                        
                        if (Object.entries(infosgars).length > 0) {                            
                        
                                for (let key in Object.entries(infosgars)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgars[key].roleattribut}`;
                                    opt.innerHTML = `${infosgars[key].username}`;
                                    document.querySelector('#idvendeuseopesc').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idvendeuseopesc').options.length = 1;
                        }
                        
                    };
                    httpInfosgars.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgars.send();
                };
        e.onclick = function () {
        let tickFormsgl = document.querySelector('#tickFormopesc');
            tickFormsgl.setAttribute('action', `${APP_ROOT}/Rapport/exercicesbagopesc/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});