document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.recaptridepot').forEach(function (e) 
    {
        document.querySelector('h3#recaptTitledepot').innerHTML = `TRI DEPOTS`;

        let gpinfrecapt = document.querySelector('#recapttypedepot');
        
        if (gpinfrecapt !== null) 
        gpinfrecapt.onchange = () => 
        {
                document.querySelector('#recaptgenredepot').options.length = 1;
                document.querySelector('#recaptnomdepot').options.length = 1;
                    let httpInfostypinforecapt;
                    if (window.XMLHttpRequest) {
                        httpInfostypinforecapt = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfostypinforecapt = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var recaptverificationtypinfo = document.querySelector('#recapttypedepot')
                    .options[document.querySelector('#recapttypedepot').options.selectedIndex].value;
                    httpInfostypinforecapt.open('GET', window.location.origin + `${APP_ROOT}/depots/listegenre/${recaptverificationtypinfo}`, true);
                    httpInfostypinforecapt.onload = () => {
                        const resprecapt = JSON.parse(httpInfostypinforecapt.responseText);
        
                            if(resprecapt == null){
                                document.querySelector('#recaptgenredepot').value = "";
        
                            } 
                            if (Object.entries(resprecapt).length >= 1) {
                        
                                for (let key in Object.entries(resprecapt)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resprecapt[key].type_personnel}`;
                                    opt.innerHTML = `${resprecapt[key].type_personnel}`;
                                    document.querySelector('#recaptgenredepot').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#recaptgenredepot').options.length = 1;
                            }
        
                        };
                        
                        httpInfostypinforecapt.setRequestHeader('Content-Type', 'application/json');
                        httpInfostypinforecapt.send();
    
                };
            
                let typorecapt = document.querySelector('#recaptgenredepot');
        
        if (typorecapt !== null) 
        typorecapt.onchange = () => 
        {
                    let Infostypinforecapt;
                    if (window.XMLHttpRequest) {
                        Infostypinforecapt = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        Infostypinforecapt = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    document.querySelector('#recaptnomdepot').options.length = 1;
                    var typedepchoisirecapt = document.querySelector('#recapttypedepot')
                    .options[document.querySelector('#recapttypedepot').options.selectedIndex].value;

                    var ficationtypinforecapt = document.querySelector('#recaptgenredepot').
                    options[document.querySelector('#recaptgenredepot').options.selectedIndex].value;
                    Infostypinforecapt.open('GET', window.location.origin + `${APP_ROOT}/depots/listenom/${typedepchoisirecapt}/${ficationtypinforecapt}`, true);
                    Infostypinforecapt.onload = () => {
                        const resperecapt = JSON.parse(Infostypinforecapt.responseText);
        
                            if(resperecapt == null){
                                document.querySelector('#recaptnomdepot').value = "";
        
                            } 
                            if (Object.entries(resperecapt).length >= 1) {
                                for (let key in Object.entries(resperecapt)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resperecapt[key].nom_pre}`;
                                    opt.innerHTML = `${resperecapt[key].nom_pre}`;
                                    document.querySelector('#recaptnomdepot').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#recaptnomdepot').options.length = 1;
                            }
        
                        };
                        
                        Infostypinforecapt.setRequestHeader('Content-Type', 'application/json');
                        Infostypinforecapt.send();
    
                };
        e.onclick = function () {
        let recaptlistedepot = document.querySelector('#recaptdepotForm');
        recaptlistedepot.setAttribute('action', `${APP_ROOT}/Rapport/recaptdepot/${e.dataset.ckey}`);
        }

    })
});