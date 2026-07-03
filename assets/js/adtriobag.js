document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtriobag').forEach(function (e) 
    {
        document.querySelector('h3#caisTitlebag').innerHTML = `VERSEMENT BAGAGES`;
        let infgares = document.querySelector('#encaisgarsbag');
        
        if (infgares !== null) 
        infgares.onchange = () => {
            let httpInfoss;
            if (window.XMLHttpRequest) {
                httpInfoss = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfoss = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeusesbag').options.length = 1;

                    var verificatgares = document.querySelector('#encaisgarsbag').value;
                    
                    httpInfoss.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeusesop/${verificatgares}`, true);
                    httpInfoss.onload = () => {
                        const infoss = JSON.parse(httpInfoss.responseText);
                        
                        if (Object.entries(infoss).length > 0) {                            
                        
                                for (let key in Object.entries(infoss)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infoss[key].roleattribut}`;
                                    opt.innerHTML = `${infoss[key].username}`;
                                    document.querySelector('#idvendeusesbag').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idvendeusesbag').options.length = 1;
                        }
                        
                    };
                    httpInfoss.setRequestHeader('Content-Type', 'application/json');
                    httpInfoss.send();
                };
        e.onclick = function () {
        let encaisForm = document.querySelector('#encaismentFormbag');
            encaisForm.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementsbag/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});