document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtrioexobagesc').forEach(function (e) 
    {
        document.querySelector('h3#Titlexpglobversbgesc').innerHTML = `BROUILLARD(EXERCICE) BAGAGESESCAL`;
        let infgaresbe = document.querySelector('#departgarexobgeesc');
        
        if (infgaresbe !== null) 
        infgaresbe.onchange = () => {
            let httpInfossbe;
            if (window.XMLHttpRequest) {
                httpInfossbe = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfossbe = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#dvendeuseidexobgesc').options.length = 1;

                    var verificatgaresbe = document.querySelector('#departgarexobgeesc').value;
                    
                    httpInfossbe.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgaresbe}`, true);
                    httpInfossbe.onload = () => {
                        const infossbe = JSON.parse(httpInfossbe.responseText);
                        
                        if (Object.entries(infossbe).length > 0) {                            
                        
                                for (let key in Object.entries(infossbe)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infossbe[key].roleattribut}/${infossbe[key].first_name} ${infossbe[key].last_name}`;
                                    opt.innerHTML = `${infossbe[key].username}`;
                                    document.querySelector('#dvendeuseidexobgesc').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#dvendeuseidexobgesc').options.length = 1;
                        }
                        
                    };
                    httpInfossbe.setRequestHeader('Content-Type', 'application/json');
                    httpInfossbe.send();
                };
        e.onclick = function () {
        let encaisFormvbe = document.querySelector('#expglobFormsversbgesc');
            encaisFormvbe.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementsexobagesc/${e.dataset.ekey}/${e.dataset.idgare}`);
        }

    })
});