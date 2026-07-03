document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adreportventegljs').forEach(function (e) 
    {
        document.querySelector('h3#Titlerepventegl').innerHTML = `TRI REPORT GLOBAL DES VENTES`;

        let infgarvers = document.querySelector('#departgaridentifventegl');
        
        if (infgarvers !== null) 
        infgarvers.onchange = () => {
            let httpInfosgarvers;
            if (window.XMLHttpRequest) {
                httpInfosgarvers = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgarvers = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idcaissiersventegl').options.length = 1;

                    var verificatgarvers = document.querySelector('#departgaridentifventegl').value;
                    
                    httpInfosgarvers.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgarvers}`, true);
                    httpInfosgarvers.onload = () => {
                        const infosgarvers = JSON.parse(httpInfosgarvers.responseText);
                        
                        if (Object.entries(infosgarvers).length > 0) {                            
                        
                                for (let key in Object.entries(infosgarvers)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgarvers[key].roleattribut}`;
                                    opt.innerHTML = `${infosgarvers[key].username}`;
                                    document.querySelector('#idcaissiersventegl').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idcaissiersventegl').options.length = 1;
                        }
                        
                    };
                    httpInfosgarvers.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgarvers.send();
                };
        e.onclick = function () {
        let tickversForm = document.querySelector('#tickventeglForm');
            tickversForm.setAttribute('action', `${APP_ROOT}/Rapport/exoreportsventegl/${e.dataset.ekey}/${e.dataset.idgares}`);
        }

    })
});