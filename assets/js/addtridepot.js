document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addtridepot').forEach(function (e) 
    {
        document.querySelector('h3#Titledepot').innerHTML = `TRI DEPOTS`;

        let gpinf = document.querySelector('#typedepot');
        
        if (gpinf !== null) 
        gpinf.onchange = () => 
        {
                document.querySelector('#genredepot').options.length = 1;
                document.querySelector('#nomdepot').options.length = 1;
                    let httpInfostypinfo;
                    if (window.XMLHttpRequest) {
                        httpInfostypinfo = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfostypinfo = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var verificationtypinfo = document.querySelector('#typedepot')
                    .options[document.querySelector('#typedepot').options.selectedIndex].value;
                    httpInfostypinfo.open('GET', window.location.origin + `${APP_ROOT}/depots/listegenre/${verificationtypinfo}`, true);
                    httpInfostypinfo.onload = () => {
                        const resp = JSON.parse(httpInfostypinfo.responseText);
        
                            if(resp == null){
                                document.querySelector('#genredepot').value = "";
        
                            } 
                            if (Object.entries(resp).length >= 1) {
                        
                                for (let key in Object.entries(resp)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resp[key].type_personnel}`;
                                    opt.innerHTML = `${resp[key].type_personnel}`;
                                    document.querySelector('#genredepot').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#genredepot').options.length = 1;
                            }
        
                        };
                        
                        httpInfostypinfo.setRequestHeader('Content-Type', 'application/json');
                        httpInfostypinfo.send();
    
                };
            
                let typo = document.querySelector('#genredepot');
        
        if (typo !== null) 
        typo.onchange = () => 
        {
                    let Infostypinfo;
                    if (window.XMLHttpRequest) {
                        Infostypinfo = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        Infostypinfo = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    document.querySelector('#nomdepot').options.length = 1;
                    var typedepchoisi = document.querySelector('#typedepot')
                    .options[document.querySelector('#typedepot').options.selectedIndex].value;

                    var ficationtypinfo = document.querySelector('#genredepot').
                    options[document.querySelector('#genredepot').options.selectedIndex].value;
                    Infostypinfo.open('GET', window.location.origin + `${APP_ROOT}/depots/listenom/${typedepchoisi}/${ficationtypinfo}`, true);
                    Infostypinfo.onload = () => {
                        const respe = JSON.parse(Infostypinfo.responseText);
        
                            if(respe == null){
                                document.querySelector('#nomdepot').value = "";
        
                            } 
                            if (Object.entries(respe).length >= 1) {
                                for (let key in Object.entries(respe)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${respe[key].nom_pre}`;
                                    opt.innerHTML = `${respe[key].nom_pre}`;
                                    document.querySelector('#nomdepot').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#nomdepot').options.length = 1;
                            }
        
                        };
                        
                        Infostypinfo.setRequestHeader('Content-Type', 'application/json');
                        Infostypinfo.send();
    
                };
        e.onclick = function () {
        let listedepot = document.querySelector('#depotForm');
        listedepot.setAttribute('action', `${APP_ROOT}/Rapport/depot/${e.dataset.cle_compagnie}`);
        }

    })
});