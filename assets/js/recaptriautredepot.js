document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.recaptriautredepot').forEach(function (e) 
    {
        document.querySelector('h3#recaptTitleautre').innerHTML = `TRI AUTRES DEPOTS`;

        let gpinfrecapt = document.querySelector('#recapttypeautredepot');
        
        if (gpinfrecapt !== null) 
        gpinfrecapt.onchange = () => 
        {
                document.querySelector('#recaptgenreautredepot').options.length = 1;
                document.querySelector('#recaptnomautredepot').options.length = 1;
                    let httpInfostypinforecapt;
                    if (window.XMLHttpRequest) {
                        httpInfostypinforecapt = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfostypinforecapt = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var recaptverificationtypinfo = document.querySelector('#recapttypeautredepot')
                    .options[document.querySelector('#recapttypeautredepot').options.selectedIndex].value;
                    httpInfostypinforecapt.open('GET', window.location.origin + `${APP_ROOT}/depots/autrelistegenre/${recaptverificationtypinfo}`, true);
                    httpInfostypinforecapt.onload = () => {
                        const resprecapt = JSON.parse(httpInfostypinforecapt.responseText);
        
                            if(resprecapt == null){
                                document.querySelector('#recaptgenreautredepot').value = "";
        
                            } 
                            if (Object.entries(resprecapt).length >= 1) {
                        
                                for (let key in Object.entries(resprecapt)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resprecapt[key].genre_depot}`;
                                    opt.innerHTML = `${resprecapt[key].genre_depot}`;
                                    document.querySelector('#recaptgenreautredepot').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#recaptgenreautredepot').options.length = 1;
                            }
        
                        };
                        
                        httpInfostypinforecapt.setRequestHeader('Content-Type', 'application/json');
                        httpInfostypinforecapt.send();
    
                };
            
                let typorecapt = document.querySelector('#recaptgenreautredepot');
        
        if (typorecapt !== null) 
        typorecapt.onchange = () => 
        {
                    let Infostypinforecapt;
                    if (window.XMLHttpRequest) {
                        Infostypinforecapt = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        Infostypinforecapt = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    document.querySelector('#recaptnomautredepot').options.length = 1;
                    var recapttypedepchoisi = document.querySelector('#recapttypeautredepot')
                    .options[document.querySelector('#recapttypeautredepot').options.selectedIndex].value;

                    var recaptficationtypinfo = document.querySelector('#recaptgenreautredepot').
                    options[document.querySelector('#recaptgenreautredepot').options.selectedIndex].value;
                    Infostypinforecapt.open('GET', window.location.origin + `${APP_ROOT}/depots/autrelistenom/${recapttypedepchoisi}/${recaptficationtypinfo}`, true);
                    Infostypinforecapt.onload = () => {
                        const recaptrespe = JSON.parse(Infostypinforecapt.responseText);
        
                            if(recaptrespe == null){
                                document.querySelector('#recaptnomautredepot').value = "";
        
                            } 
                            if (Object.entries(recaptrespe).length >= 1) {
                                for (let key in Object.entries(recaptrespe)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${recaptrespe[key].nom_pre}`;
                                    opt.innerHTML = `${recaptrespe[key].nom_pre}`;
                                    document.querySelector('#recaptnomautredepot').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#recaptnomautredepot').options.length = 1;
                            }
        
                        };
                        
                        Infostypinforecapt.setRequestHeader('Content-Type', 'application/json');
                        Infostypinforecapt.send();
    
                };
        e.onclick = function () {
        let recaptlisteautredepot = document.querySelector('#recaptautredepotForm');
        recaptlisteautredepot.setAttribute('action', `${APP_ROOT}/Rapport/recaptautredepot/${e.dataset.ekey}`);
        }

    })
});