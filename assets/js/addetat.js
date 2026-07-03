document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addetat').forEach(function (e) 
    {
        document.querySelector('h3#etatTitle').innerHTML = `ETAT TICKETS`;

            let gd = document.querySelector('#garesid');
            if (gd !== null)
                gd.onchange = () => {
                    let httpgares;
                    if (window.XMLHttpRequest) {
                        httpgares = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpgares = new ActiveXObject("Microsoft.XMLHTTP");
                    }

                    document.querySelector('#venteid').options.length = 1;
                    var dt = document.querySelector('#garesid').value;
                    const idgd = document.querySelector('#garesid')
                    .options[document.querySelector('#garesid').options.selectedIndex].value;
                    
                    httpgares.open('GET', window.location.origin + `${APP_ROOT}/programmes/vente/${idgd}`, true);
                    httpgares.onload = () => {
                        const resul = JSON.parse(httpgares.responseText);
                        if(resul == null){

                            
                        
                        } else {
                            if (Object.entries(resul).length >= 1) 
                            {
                                
                                for (let key in Object.entries(resul)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${resul[key].roleattribut}`;
                                        opt.innerHTML = `${resul[key].username}`;
                                        document.querySelector('#venteid').add(opt);
                                    }
                            } else {
                                document.querySelector('#venteid').options.length = 1;
                            }
                            
                        }
                    };
                    httpgares.setRequestHeader('Content-Type', 'application/json');
                    httpgares.send();
                                         
            };
        
        
        e.onclick = function () {
        let Forms = document.querySelector('#Forms');
        Forms.setAttribute('action', `${APP_ROOT}/Rapport/etatpassagers/${e.dataset.cle_compagnie}`);
        }

    })
});