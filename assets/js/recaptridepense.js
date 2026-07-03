document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.recaptridepense').forEach(function (e) 
    {
        document.querySelector('h3#recaptdepTitle').innerHTML = `TRI DEPENSES`;

        let gpinfrecapt = document.querySelector('#recaptdtype');
        
        if (gpinfrecapt !== null) 
        gpinfrecapt.onchange = () => 
        {
                document.querySelector('#recaptgtype').options.length = 1;
                document.querySelector('#recaptgnom').options.length = 1;
                    let httpInfostypinforecapt;
                    if (window.XMLHttpRequest) {
                        httpInfostypinforecapt = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfostypinforecapt = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var recaptverificationtypinfo = document.querySelector('#recaptdtype')
                    .options[document.querySelector('#recaptdtype').options.selectedIndex].value;
                    httpInfostypinforecapt.open('GET', window.location.origin + `${APP_ROOT}/depenses/listegenre/${recaptverificationtypinfo}`, true);
                    httpInfostypinforecapt.onload = () => {
                        const resprecapt = JSON.parse(httpInfostypinforecapt.responseText);
        
                            if(resprecapt == null){
                                document.querySelector('#recaptgtype').value = "";
        
                            } 
                            if (Object.entries(resprecapt).length >= 1) {
                        
                                for (let key in Object.entries(resprecapt)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resprecapt[key].type_personnel}`;
                                    opt.innerHTML = `${resprecapt[key].type_personnel}`;
                                    document.querySelector('#recaptgtype').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#recaptgtype').options.length = 1;
                            }
        
                        };
                        
                        httpInfostypinforecapt.setRequestHeader('Content-Type', 'application/json');
                        httpInfostypinforecapt.send();
    
                };
            
                let typorecapt = document.querySelector('#recaptgtype');
        
        if (typorecapt !== null) 
        typorecapt.onchange = () => 
        {
                    let Infostypinforecapt;
                    if (window.XMLHttpRequest) {
                        Infostypinforecapt = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        Infostypinforecapt = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    document.querySelector('#gnom').options.length = 1;
                    var recapttypedepchoisi = document.querySelector('#recaptdtype')
                    .options[document.querySelector('#recaptdtype').options.selectedIndex].value;

                    var recaptficationtypinfo = document.querySelector('#recaptgtype').
                    options[document.querySelector('#recaptgtype').options.selectedIndex].value;
                    Infostypinforecapt.open('GET', window.location.origin + `${APP_ROOT}/depenses/listenom/${recapttypedepchoisi}/${recaptficationtypinfo}`, true);
                    Infostypinforecapt.onload = () => {
                        const resperecapt = JSON.parse(Infostypinforecapt.responseText);
        
                            if(resperecapt == null){
                                document.querySelector('#recaptgnom').value = "";
        
                            } 
                            if (Object.entries(resperecapt).length >= 1) {
                                for (let key in Object.entries(resperecapt)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resperecapt[key].nom_perso}`;
                                    opt.innerHTML = `${resperecapt[key].nom_perso}`;
                                    document.querySelector('#recaptgnom').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#recaptgnom').options.length = 1;
                            }
        
                        };
                        
                        Infostypinforecapt.setRequestHeader('Content-Type', 'application/json');
                        Infostypinforecapt.send();
    
                };
        e.onclick = function () {
        let recaptlistedepense = document.querySelector('#recaptdpForm');
        recaptlistedepense.setAttribute('action', `${APP_ROOT}/Rapport/recaptdepense/${e.dataset.ckey}`);
        }

    })
});