document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.recaptbagexop').forEach(function (e) 
    {
        document.querySelector('h3#optitle').innerHTML = `EXERCICE MENSUEL BAGAGE OPERATEUR`;

        let infgars = document.querySelector('#departgardpbagop');
        
        if (infgars !== null) 
        infgars.onchange = () => {
            let httpInfosgars;
            if (window.XMLHttpRequest) {
                httpInfosgars = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgars = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeuseop').options.length = 1;

                    var verificatgars = document.querySelector('#departgardpbagop').value;
                    
                    httpInfosgars.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeusesop/${verificatgars}`, true);
                    httpInfosgars.onload = () => {
                        const infosgars = JSON.parse(httpInfosgars.responseText);
                        
                        if (Object.entries(infosgars).length > 0) {                            
                        
                                for (let key in Object.entries(infosgars)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgars[key].roleattribut}`;
                                    opt.innerHTML = `${infosgars[key].username}`;
                                    document.querySelector('#idvendeuseop').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idvendeuseop').options.length = 1;
                        }
                        
                    };
                    httpInfosgars.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgars.send();
                };
        e.onclick = function () {
        let tickFormsgl = document.querySelector('#tickFormop');
            tickFormsgl.setAttribute('action', `${APP_ROOT}/Rapport/exercicesbagop/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});