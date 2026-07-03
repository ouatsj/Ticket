document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addtridepense').forEach(function (e) 
    {
        document.querySelector('h3#depTitle').innerHTML = `TRI DEPENSES`;

        let gpinf = document.querySelector('#dtype');
        
        if (gpinf !== null) 
        gpinf.onchange = () => 
        {
                document.querySelector('#gnom').options.length = 1;
                    let httpInfostypinfo;
                    if (window.XMLHttpRequest) {
                        httpInfostypinfo = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfostypinfo = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var verificationtypinfo = document.querySelector('#dtype')
                    .options[document.querySelector('#dtype').options.selectedIndex].value;
                    httpInfostypinfo.open('GET', window.location.origin + `${APP_ROOT}/depenses/listegenre/${verificationtypinfo}`, true);
                    httpInfostypinfo.onload = () => {
                        const resp = JSON.parse(httpInfostypinfo.responseText);
        
                            if(resp == null){
                                document.querySelector('#gtype').value = "";
        
                            } 
                            if (Object.entries(resp).length >= 1) {
                        
                                for (let key in Object.entries(resp)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resp[key].genre_depens}`;
                                    opt.innerHTML = `${resp[key].genre_depens}`;
                                    document.querySelector('#gtype').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#gtype').options.length = 1;
                            }
        
                        };
                        
                        httpInfostypinfo.setRequestHeader('Content-Type', 'application/json');
                        httpInfostypinfo.send();
    
                };
            
                let typo = document.querySelector('#gtype');
        
        if (typo !== null) 
        typo.onchange = () => 
        {
                    let Infostypinfo;
                    if (window.XMLHttpRequest) {
                        Infostypinfo = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        Infostypinfo = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    document.querySelector('#gnom').options.length = 1;
                    
                    var idcaid = document.querySelector('#idcaiss').value;
                    var typedepchoisi = document.querySelector('#dtype')
                    .options[document.querySelector('#dtype').options.selectedIndex].value;

                    var ficationtypinfo = document.querySelector('#gtype').
                    options[document.querySelector('#gtype').options.selectedIndex].value;
                    Infostypinfo.open('GET', window.location.origin + `${APP_ROOT}/depenses/listenom/${idcaid}/${typedepchoisi}/${ficationtypinfo}`, true);
                    Infostypinfo.onload = () => {
                        const respe = JSON.parse(Infostypinfo.responseText);
        
                            if(respe == null){
                                document.querySelector('#gnom').value = "";
        
                            } 
                            if (Object.entries(respe).length >= 1) {
                                for (let key in Object.entries(respe)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${respe[key].nom_perso}`;
                                    opt.innerHTML = `${respe[key].nom_perso}`;
                                    document.querySelector('#gnom').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#gnom').options.length = 1;
                            }
        
                        };
                        
                        Infostypinfo.setRequestHeader('Content-Type', 'application/json');
                        Infostypinfo.send();
    
                };
        e.onclick = function () {
        let listedepense = document.querySelector('#dpForm');
        listedepense.setAttribute('action', `${APP_ROOT}/Rapport/depense/${e.dataset.cle_compagnie}`);
        }

    })
});