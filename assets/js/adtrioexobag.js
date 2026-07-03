document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtrioexobag').forEach(function (e) 
    {
        document.querySelector('h3#Titlexpglobversbg').innerHTML = `BROUILLARD(EXERCICE) BAGAGES`;
        let infgaresb = document.querySelector('#departgarexobge');
        
        if (infgaresb !== null) 
        infgaresb.onchange = () => {
            let httpInfossb;
            if (window.XMLHttpRequest) {
                httpInfossb = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfossb = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#dvendeuseidexobg').options.length = 1;

                    var verificatgaresb = document.querySelector('#departgarexobge').value;
                    
                    httpInfossb.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgaresb}`, true);
                    httpInfossb.onload = () => {
                        const infossb = JSON.parse(httpInfossb.responseText);
                        
                        if (Object.entries(infossb).length > 0) {                            
                        
                                for (let key in Object.entries(infossb)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infossb[key].roleattribut}/${infossb[key].first_name} ${infossb[key].last_name}`;
                                    opt.innerHTML = `${infossb[key].username}`;
                                    document.querySelector('#dvendeuseidexobg').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#dvendeuseidexobg').options.length = 1;
                        }
                        
                    };
                    httpInfossb.setRequestHeader('Content-Type', 'application/json');
                    httpInfossb.send();
                };
        e.onclick = function () {
        let encaisFormvb = document.querySelector('#expglobFormsversbg');
            encaisFormvb.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementsexobag/${e.dataset.ekey}/${e.dataset.idgare}`);
        }

    })
});