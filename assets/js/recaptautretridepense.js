document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.recaptautretridepense').forEach(function (e) 
    {
        document.querySelector('h3#recaptautredepTitle').innerHTML = `TRI AUTRES DEPENSES`;

        let gpinfrecapt = document.querySelector('#recaptautredtype');
        
        if (gpinfrecapt !== null) 
        gpinfrecapt.onchange = () => 
        {
                document.querySelector('#recaptautregtype').options.length = 1;
                document.querySelector('#recaptautregnom').options.length = 1;
                    let httpInfostypinforecapt;
                    if (window.XMLHttpRequest) {
                        httpInfostypinforecapt = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfostypinforecapt = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var verificationtypinforecapt = document.querySelector('#recaptautredtype')
                    .options[document.querySelector('#recaptautredtype').options.selectedIndex].value;
                    httpInfostypinforecapt.open('GET', window.location.origin + `${APP_ROOT}/depenses/autrelistegenre/${verificationtypinforecapt}`, true);
                    httpInfostypinforecapt.onload = () => {
                        const recaptresp = JSON.parse(httpInfostypinforecapt.responseText);
        
                            if(recaptresp == null){
                                document.querySelector('#recaptautregtype').value = "";
        
                            } 
                            if (Object.entries(recaptresp).length >= 1) {
                        
                                for (let key in Object.entries(recaptresp)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${recaptresp[key].genre_depens}`;
                                    opt.innerHTML = `${recaptresp[key].genre_depens}`;
                                    document.querySelector('#recaptautregtype').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#recaptautregtype').options.length = 1;
                            }
        
                        };
                        
                        httpInfostypinforecapt.setRequestHeader('Content-Type', 'application/json');
                        httpInfostypinforecapt.send();
    
                };
            
                let recapttypo = document.querySelector('#recaptautregtype');
        
        if (recapttypo !== null) 
        recapttypo.onchange = () => 
        {
                    let Infostypinforecapt;
                    if (window.XMLHttpRequest) {
                        Infostypinforecapt = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        Infostypinforecapt = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    document.querySelector('#recaptautregnom').options.length = 1;
                    var autredepensechoisirecapt = document.querySelector('#recaptautredtype')
                    .options[document.querySelector('#recaptautredtype').options.selectedIndex].value;

                    var ficationtypinforecapt = document.querySelector('#recaptautregtype').
                    options[document.querySelector('#recaptautregtype').options.selectedIndex].value;
                    Infostypinforecapt.open('GET', window.location.origin + `${APP_ROOT}/depenses/autrelistenom/${autredepensechoisirecapt}/${ficationtypinforecapt}`, true);
                    Infostypinforecapt.onload = () => {
                        const recaptrespe = JSON.parse(Infostypinforecapt.responseText);
        
                            if(recaptrespe == null){
                                document.querySelector('#recaptautregnom').value = "";
        
                            } 
                            if (Object.entries(recaptrespe).length >= 1) {
                                for (let key in Object.entries(recaptrespe)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${recaptrespe[key].nom_perso}`;
                                    opt.innerHTML = `${recaptrespe[key].nom_perso}`;
                                    document.querySelector('#recaptautregnom').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#recaptautregnom').options.length = 1;
                            }
        
                        };
                        
                        Infostypinforecapt.setRequestHeader('Content-Type', 'application/json');
                        Infostypinforecapt.send();
    
                };
        e.onclick = function () {
        let listedepenserecapt = document.querySelector('#recaptautredpForm');
        listedepenserecapt.setAttribute('action', `${APP_ROOT}/Rapport/recaptautredepense/${e.dataset.ckey}`);
        }

    })
});