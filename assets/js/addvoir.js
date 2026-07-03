document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addvoir').forEach(function (e) 
    {
        document.querySelector('h3#lisTitle').innerHTML = `Liste des passagers`;

              //heure
            let infoligne = document.querySelector('#idlign');
            if (infoligne !== null)
                infoligne.onchange = () => {
                    let httpInfoprog;
                    if (window.XMLHttpRequest) {
                        httpInfoprog = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfoprog = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    const lidligne = document.querySelector('#idlign')
                    .options[document.querySelector('#idlign').options.selectedIndex].value;
                    var verifidate = document.querySelector('#choixdate').value;
                    httpInfoprog.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifprogramm/${lidligne}/${verifidate}`, true);
                    httpInfoprog.onload = () => {
                        const resultp = JSON.parse(httpInfoprog.responseText);
                        if(resultp == null){

                            
                        
                        } else {
                            if (Object.entries(resultp).length >= 1) 
                            {
                               
                                for (let key in Object.entries(resultp)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${resultp[key].depart_code}/${resultp[key].heure}/${resultp[key].heure_identif}`;
                                        opt.innerHTML = `${resultp[key].code_progr}/${resultp[key].heure}`;
                                        document.querySelector('#idprogr').add(opt);
                                    }
                            } else {
                                document.querySelector('#idprogr').options.length = 1;
                            }
                            
                        }
                    };
                    httpInfoprog.setRequestHeader('Content-Type', 'application/json');
                    httpInfoprog.send();
                                         
            };
        
        e.onclick = function () {
        
        let listForm = document.querySelector('#listForm');
        listForm.setAttribute('action', `${APP_ROOT}/Ticket/voirliste/${e.dataset.cle_compagnie}`);
        }

    })
});