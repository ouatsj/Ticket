document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addtriautredepot').forEach(function (e) 
    {
        document.querySelector('h3#Titleautre').innerHTML = `TRI AUTRES DEPOTS`;

        let gpinf = document.querySelector('#typeautredepot');
        
        if (gpinf !== null) 
        gpinf.onchange = () => 
        {
                document.querySelector('#genreautredepot').options.length = 1;
                document.querySelector('#nomautredepot').options.length = 1;
                    let httpInfostypinfo;
                    if (window.XMLHttpRequest) {
                        httpInfostypinfo = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfostypinfo = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var verificationtypinfo = document.querySelector('#typeautredepot')
                    .options[document.querySelector('#typeautredepot').options.selectedIndex].value;
                    httpInfostypinfo.open('GET', window.location.origin + `${APP_ROOT}/depots/autrelistegenre/${verificationtypinfo}`, true);
                    httpInfostypinfo.onload = () => {
                        const resp = JSON.parse(httpInfostypinfo.responseText);
        
                            if(resp == null){
                                document.querySelector('#genreautredepot').value = "";
        
                            } 
                            if (Object.entries(resp).length >= 1) {
                        
                                for (let key in Object.entries(resp)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resp[key].genre_depot}`;
                                    opt.innerHTML = `${resp[key].genre_depot}`;
                                    document.querySelector('#genreautredepot').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#genreautredepot').options.length = 1;
                            }
        
                        };
                        
                        httpInfostypinfo.setRequestHeader('Content-Type', 'application/json');
                        httpInfostypinfo.send();
    
                };
            
                let typo = document.querySelector('#genreautredepot');
        
        if (typo !== null) 
        typo.onchange = () => 
        {
                    let Infostypinfo;
                    if (window.XMLHttpRequest) {
                        Infostypinfo = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        Infostypinfo = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    document.querySelector('#nomautredepot').options.length = 1;
                    var typedepchoisi = document.querySelector('#typeautredepot')
                    .options[document.querySelector('#typeautredepot').options.selectedIndex].value;

                    var ficationtypinfo = document.querySelector('#genreautredepot').
                    options[document.querySelector('#genreautredepot').options.selectedIndex].value;
                    Infostypinfo.open('GET', window.location.origin + `${APP_ROOT}/depots/autrelistenom/${typedepchoisi}/${ficationtypinfo}`, true);
                    Infostypinfo.onload = () => {
                        const respe = JSON.parse(Infostypinfo.responseText);
        
                            if(respe == null){
                                document.querySelector('#nomautredepot').value = "";
        
                            } 
                            if (Object.entries(respe).length >= 1) {
                                for (let key in Object.entries(respe)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${respe[key].nom_pre}`;
                                    opt.innerHTML = `${respe[key].nom_pre}`;
                                    document.querySelector('#nomautredepot').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#nomautredepot').options.length = 1;
                            }
        
                        };
                        
                        Infostypinfo.setRequestHeader('Content-Type', 'application/json');
                        Infostypinfo.send();
    
                };
        e.onclick = function () {
        let listeautredepot = document.querySelector('#autredepotForm');
        listeautredepot.setAttribute('action', `${APP_ROOT}/Rapport/autredepot/${e.dataset.cle_compagnie}`);
        }

    })
});