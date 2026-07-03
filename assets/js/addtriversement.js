document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addtriversement').forEach(function (e) 
    {
        document.querySelector('h3#verTitle').innerHTML = `TRI VERSEMENT BANQUE`;

        let gpinfvers = document.querySelector('#vtype');
        
        if (gpinfvers !== null) 
        gpinfvers.onchange = () => 
        {
                document.querySelector('#gtype').options.length = 1;
                document.querySelector('#gnom').options.length = 1;
                    let httpInfostypinfovers;
                    if (window.XMLHttpRequest) {
                        httpInfostypinfovers = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfostypinfovers = new ActiveXObject("Microsoft.XMLHTTP");
                    }

                    var gard = document.querySelector('#gareconnect').value;
                    var verificationtypinfoverse = document.querySelector('#vtype')
                    .options[document.querySelector('#vtype').options.selectedIndex].value;
                    httpInfostypinfovers.open('GET', window.location.origin + `${APP_ROOT}/depots/versetribank/${gard}/${verificationtypinfoverse}`, true);
                    httpInfostypinfovers.onload = () => {
                        const resp = JSON.parse(httpInfostypinfovers.responseText);
        
                            if(resp == null){
                                document.querySelector('#gtype').value = "";
        
                            } 
                            if (Object.entries(resp).length >= 1) {
                        
                                for (let key in Object.entries(resp)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resp[key].genre_depot}`;
                                    opt.innerHTML = `${resp[key].genre_depot}`;
                                    document.querySelector('#gtype').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#gtype').options.length = 1;
                            }
        
                        };
                        
                        httpInfostypinfovers.setRequestHeader('Content-Type', 'application/json');
                        httpInfostypinfovers.send();
    
                };
            
                let typverse = document.querySelector('#gtype');
        
        if (typverse !== null) 
        typverse.onchange = () => 
        {
                    let Infostypinfovers;
                    if (window.XMLHttpRequest) {
                        Infostypinfovers = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        Infostypinfovers = new ActiveXObject("Microsoft.XMLHTTP");
                    }

                    var gard = document.querySelector('#gareconnect').value;
                    document.querySelector('#gnom').options.length = 1;
                    var typedepchoisivers = document.querySelector('#vtype')
                    .options[document.querySelector('#vtype').options.selectedIndex].value;

                    var ficationtypinfovers = document.querySelector('#gtype').
                    options[document.querySelector('#gtype').options.selectedIndex].value;
                    Infostypinfovers.open('GET', window.location.origin + `${APP_ROOT}/depots/banknom/${gard}/${typedepchoisivers}/${ficationtypinfovers}`, true);
                    Infostypinfovers.onload = () => {
                        const respe = JSON.parse(Infostypinfovers.responseText);
        
                            if(respe == null){
                                document.querySelector('#gnom').value = "";
        
                            } 
                            if (Object.entries(respe).length >= 1) {
                                for (let key in Object.entries(respe)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${respe[key].nom_beneficiaire}`;
                                    opt.innerHTML = `${respe[key].nom_beneficiaire}`;
                                    document.querySelector('#gnom').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#gnom').options.length = 1;
                            }
        
                        };
                        
                        Infostypinfovers.setRequestHeader('Content-Type', 'application/json');
                        Infostypinfovers.send();
    
                };
        e.onclick = function () {
        let listeverse = document.querySelector('#verForm');
        listeverse.setAttribute('action', `${APP_ROOT}/Rapport/versementbanq/${e.dataset.cle_compagnie}`);
        }

    })
});