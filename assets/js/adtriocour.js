document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtriocour').forEach(function (e) 
    {
        document.querySelector('h3#caisTitlecour').innerHTML = `VERSEMENT COURRIER GUICHETIER`;
        let infgarescr = document.querySelector('#encaisgarscour');
        
        if (infgarescr !== null) 
        infgarescr.onchange = () => {
            let httpInfosscr;
            if (window.XMLHttpRequest) {
                httpInfosscr = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosscr = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeusescour').options.length = 1;

                    var verificatgarescr = document.querySelector('#encaisgarscour').value;
                    
                    httpInfosscr.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgarescr}`, true);
                    httpInfosscr.onload = () => {
                        const infosscr = JSON.parse(httpInfosscr.responseText);
                        
                        if (Object.entries(infosscr).length > 0) {                            
                    
                                for (let key in Object.entries(infosscr)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosscr[key].roleattribut}`;
                                    opt.innerHTML = `${infosscr[key].username}`;
                                    document.querySelector('#idvendeusescour').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idvendeusescour').options.length = 1;
                        }
                        
                    };
                    httpInfosscr.setRequestHeader('Content-Type', 'application/json');
                    httpInfosscr.send();
                };
        e.onclick = function () {
        let encaisFormcr = document.querySelector('#encaismentFormcour');
            encaisFormcr.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementscour/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});