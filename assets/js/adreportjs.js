document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adreportjs').forEach(function (e) 
    {
        document.querySelector('h3#Titlerep').innerHTML = `EXERCICE MENSUEL TICKET GUICHETIER`;

        let infgar = document.querySelector('#departgaridentif');
        
        if (infgar !== null) 
        infgar.onchange = () => {
            let httpInfosgar;
            if (window.XMLHttpRequest) {
                httpInfosgar = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgar = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idcaissiers').options.length = 1;

                    var verificatgar = document.querySelector('#departgaridentif').value;
                    
                    httpInfosgar.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgar}`, true);
                    httpInfosgar.onload = () => {
                        const infosgar = JSON.parse(httpInfosgar.responseText);
                        
                        if (Object.entries(infosgar).length > 0) {                            
                        
                                for (let key in Object.entries(infosgar)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgar[key].roleattribut}`;
                                    opt.innerHTML = `${infosgar[key].username}`;
                                    document.querySelector('#idcaissiers').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idcaissiers').options.length = 1;
                        }
                        
                    };
                    httpInfosgar.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgar.send();
                };
        e.onclick = function () {
        let tickForm = document.querySelector('#tickForm');
            tickForm.setAttribute('action', `${APP_ROOT}/Rapport/exoreports/${e.dataset.ekey}/${e.dataset.idgares}`);
        }

    })
});