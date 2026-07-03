document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.recaptrirecette').forEach(function (e) 
    {
        document.querySelector('h3#recapTitle').innerHTML = `TRI RECETTES`;

        let recapttypinf = document.querySelector('#recaptchoisirtype');
        
        if (recapttypinf !== null) 
        recapttypinf.onchange = () => 
        {
                document.querySelector('#recaptidgenrerecet').options.length = 1;
                document.querySelector('#recaptidnomrecet').options.length = 1;
                    let httpInfostypinfrecapt;
                    if (window.XMLHttpRequest) {
                        httpInfostypinfrecapt = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfostypinfrecapt = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var verificationtypinfrecapt = document.querySelector('#recaptchoisirtype')
                    .options[document.querySelector('#recaptchoisirtype').options.selectedIndex].value;
                    httpInfostypinfrecapt.open('GET', window.location.origin + `${APP_ROOT}/recettes/listegenre/${verificationtypinfrecapt}`, true);
                    httpInfostypinfrecapt.onload = () => {
                        const resulterecapt = JSON.parse(httpInfostypinfrecapt.responseText);
        
                            if(resulterecapt == null){
                                document.querySelector('#recaptidgenrerecet').value = "";
        
                            } 
                            if (Object.entries(resulterecapt).length >= 1) {
                        
                                for (let key in Object.entries(resulterecapt)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resulterecapt[key].type_personnel}`;
                                    opt.innerHTML = `${resulterecapt[key].type_personnel}`;

                                    document.querySelector('#recaptidgenrerecet').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#recaptidgenrerecet').options.length = 1;
                            }
        
                        };
                        
                        httpInfostypinfrecapt.setRequestHeader('Content-Type', 'application/json');
                        httpInfostypinfrecapt.send();
    
                };
            
                let typrecapt = document.querySelector('#recaptidgenrerecet');
        
        if (typrecapt !== null) 
        typrecapt.onchange = () => 
        {
                    let Infostypinfrecapt;
                    if (window.XMLHttpRequest) {
                        Infostypinfrecapt = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        Infostypinfrecapt = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    document.querySelector('#recaptidnomrecet').options.length = 1;
                    var typerecetchoisirecapt = document.querySelector('#recaptchoisirtype')
                    .options[document.querySelector('#recaptchoisirtype').options.selectedIndex].value;
                    var ficationtypinfrecapt = document.querySelector('#recaptidgenrerecet').
                    options[document.querySelector('#recaptidgenrerecet').options.selectedIndex].value;
                    Infostypinfrecapt.open('GET', window.location.origin + `${APP_ROOT}/recettes/listenom/${typerecetchoisirecapt}/${ficationtypinfrecapt}`, true);
                    Infostypinfrecapt.onload = () => {
                        const resulrecapt = JSON.parse(Infostypinfrecapt.responseText);
        
                            if(resulrecapt == null){
                                document.querySelector('#recaptidnomrecet').value = "";
        
                            } 
                            if (Object.entries(resulrecapt).length >= 1) {
                        console.log(ficationtypinfrecapt);
                                for (let key in Object.entries(resulrecapt)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resulrecapt[key].nom}`;
                                    opt.innerHTML = `${resulrecapt[key].nom}`;
                                    document.querySelector('#recaptidnomrecet').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#recaptidnomrecet').options.length = 1;
                            }
        
                        };
                        
                        Infostypinfrecapt.setRequestHeader('Content-Type', 'application/json');
                        Infostypinfrecapt.send();
    
                };
        e.onclick = function () {
        let listerecetterecapt = document.querySelector('#recaptrecetForm');
        listerecetterecapt.setAttribute('action', `${APP_ROOT}/Rapport/recaptrecette/${e.dataset.ckey}`);
        }

    })
});