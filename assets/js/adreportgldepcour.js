document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adreportgldepcour').forEach(function (e) 
    {
        document.querySelector('h3#Titlerepscourdep').innerHTML = `RECAP DEPENSE COURRIER`;

        let infgarscrdep = document.querySelector('#garidentifscourdep');
        
        if (infgarscrdep !== null) 
        infgarscrdep.onchange = () => {
            let httpInfosgarscrdep;
            if (window.XMLHttpRequest) {
                httpInfosgarscrdep = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgarscrdep = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idscaissiercourdep').options.length = 1;

                    var verificatgarscrdep = document.querySelector('#garidentifscourdep').value;
                    
                    httpInfosgarscrdep.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgarscrdep}`, true);
                    httpInfosgarscrdep.onload = () => {
                        const infosgarscrdep = JSON.parse(httpInfosgarscrdep.responseText);
                        
                        if (Object.entries(infosgarscrdep).length > 0) {                            
                        
                                for (let key in Object.entries(infosgarscrdep)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgarscrdep[key].roleattribut}`;
                                    opt.innerHTML = `${infosgarscrdep[key].username}`;
                                    document.querySelector('#idscaissiercourdep').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idscaissiercourdep').options.length = 1;
                        }
                        
                    };
                    httpInfosgarscrdep.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgarscrdep.send();
                };
        e.onclick = function () {
        let tickFormscrdep = document.querySelector('#tickFormscourdep');
            tickFormscrdep.setAttribute('action', `${APP_ROOT}/Rapport/tridepensescour/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});