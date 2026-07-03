document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addtriautredepense').forEach(function (e) 
    {
        document.querySelector('h3#autredepTitle').innerHTML = `TRI AUTRES DEPENSES`;

        let gpinf = document.querySelector('#autredtype');
        
        if (gpinf !== null) 
        gpinf.onchange = () => 
        {
                document.querySelector('#autregtype').options.length = 1;
                document.querySelector('#autregnom').options.length = 1;
                    let httpInfostypinfo;
                    if (window.XMLHttpRequest) {
                        httpInfostypinfo = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfostypinfo = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var verificationtypinfo = document.querySelector('#autredtype')
                    .options[document.querySelector('#autredtype').options.selectedIndex].value;
                    httpInfostypinfo.open('GET', window.location.origin + `${APP_ROOT}/depenses/autrelistegenre/${verificationtypinfo}`, true);
                    httpInfostypinfo.onload = () => {
                        const resp = JSON.parse(httpInfostypinfo.responseText);
        
                            if(resp == null){
                                document.querySelector('#autregtype').value = "";
        
                            } 
                            if (Object.entries(resp).length >= 1) {
                        
                                for (let key in Object.entries(resp)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resp[key].genre_depens}`;
                                    opt.innerHTML = `${resp[key].genre_depens}`;
                                    document.querySelector('#autregtype').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#autregtype').options.length = 1;
                            }
        
                        };
                        
                        httpInfostypinfo.setRequestHeader('Content-Type', 'application/json');
                        httpInfostypinfo.send();
    
                };
            
                let typo = document.querySelector('#autregtype');
        
        if (typo !== null) 
        typo.onchange = () => 
        {
                    let Infostypinfo;
                    if (window.XMLHttpRequest) {
                        Infostypinfo = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        Infostypinfo = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    document.querySelector('#autregnom').options.length = 1;
                    var autredepensechoisi = document.querySelector('#autredtype')
                    .options[document.querySelector('#autredtype').options.selectedIndex].value;

                    var ficationtypinfo = document.querySelector('#autregtype').
                    options[document.querySelector('#autregtype').options.selectedIndex].value;
                    Infostypinfo.open('GET', window.location.origin + `${APP_ROOT}/depenses/autrelistenom/${autredepensechoisi}/${ficationtypinfo}`, true);
                    Infostypinfo.onload = () => {
                        const respe = JSON.parse(Infostypinfo.responseText);
        
                            if(respe == null){
                                document.querySelector('#autregnom').value = "";
        
                            } 
                            if (Object.entries(respe).length >= 1) {
                                for (let key in Object.entries(respe)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${respe[key].nom_perso}`;
                                    opt.innerHTML = `${respe[key].nom_perso}`;
                                    document.querySelector('#autregnom').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#autregnom').options.length = 1;
                            }
        
                        };
                        
                        Infostypinfo.setRequestHeader('Content-Type', 'application/json');
                        Infostypinfo.send();
    
                };
        e.onclick = function () {
        let listedepense = document.querySelector('#autredpForm');
        listedepense.setAttribute('action', `${APP_ROOT}/Rapport/autredepense/${e.dataset.cle_compagnie}`);
        }

    })
});