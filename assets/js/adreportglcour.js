document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adreportglcour').forEach(function (e) 
    {
        document.querySelector('h3#Titlerepscour').innerHTML = `ETATS GLOBAL DES RECETTES COURRIER`;

        let infgarscr = document.querySelector('#garidentifscour');
        
        if (infgarscr !== null) 
        infgarscr.onchange = () => {
            let httpInfosgarscr;
            if (window.XMLHttpRequest) {
                httpInfosgarscr = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgarscr = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idscaissiercour').options.length = 1;

                    var verificatgarscr = document.querySelector('#garidentifscour').value;
                    
                    httpInfosgarscr.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgarscr}`, true);
                    httpInfosgarscr.onload = () => {
                        const infosgarscr = JSON.parse(httpInfosgarscr.responseText);
                        
                        if (Object.entries(infosgarscr).length > 0) {                            
                        
                                for (let key in Object.entries(infosgarscr)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgarscr[key].roleattribut}`;
                                    opt.innerHTML = `${infosgarscr[key].username}`;
                                    document.querySelector('#idscaissiercour').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idscaissiercour').options.length = 1;
                        }
                        
                    };
                    httpInfosgarscr.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgarscr.send();
                };
        e.onclick = function () {
        let tickFormscr = document.querySelector('#tickFormscour');
            tickFormscr.setAttribute('action', `${APP_ROOT}/Rapport/reportscour/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});