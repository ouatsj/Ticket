document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adreportjsesc').forEach(function (e) 
    {
        document.querySelector('h3#Titlerepesc').innerHTML = `EXERCICE MENSUEL TICKET GUICHETIER ESCAL`;

        let infgar = document.querySelector('#departgaridentifesc');
        
        if (infgar !== null) 
        infgar.onchange = () => {
            let httpInfosgar;
            if (window.XMLHttpRequest) {
                httpInfosgar = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgar = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idcaissiersesc').options.length = 1;

                    var verificatgar = document.querySelector('#departgaridentifesc').value;
                    
                    httpInfosgar.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeusesesc/${verificatgar}`, true);
                    httpInfosgar.onload = () => {
                        const infosgar = JSON.parse(httpInfosgar.responseText);
                        
                        if (Object.entries(infosgar).length > 0) {                            
                        
                                for (let key in Object.entries(infosgar)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgar[key].roleattribut}`;
                                    opt.innerHTML = `${infosgar[key].username}`;
                                    document.querySelector('#idcaissiersesc').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idcaissiersesc').options.length = 1;
                        }
                        
                    };
                    httpInfosgar.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgar.send();
                };
        e.onclick = function () {
        let tickForm = document.querySelector('#tickFormesc');
            tickForm.setAttribute('action', `${APP_ROOT}/Rapport/exoreportsesc/${e.dataset.ekey}/${e.dataset.idgares}`);
        }

    })
});