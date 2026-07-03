document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addtrirecette').forEach(function (e) 
    {
        document.querySelector('h3#cetTitle').innerHTML = `TRI RECETTES`;

        let typinf = document.querySelector('#choisirtype');
        
        if (typinf !== null) 
        typinf.onchange = () => 
        {
                document.querySelector('#idgenrerecet').options.length = 1;
                document.querySelector('#idnomrecet').options.length = 1;
                    let httpInfostypinf;
                    if (window.XMLHttpRequest) {
                        httpInfostypinf = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfostypinf = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var verificationtypinf = document.querySelector('#choisirtype')
                    .options[document.querySelector('#choisirtype').options.selectedIndex].value;
                    httpInfostypinf.open('GET', window.location.origin + `${APP_ROOT}/recettes/listegenre/${verificationtypinf}`, true);
                    httpInfostypinf.onload = () => {
                        const resulte = JSON.parse(httpInfostypinf.responseText);
        
                            if(resulte == null){
                                document.querySelector('#idgenrerecet').value = "";
        
                            } 
                            if (Object.entries(resulte).length >= 1) {
                        
                                for (let key in Object.entries(resulte)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resulte[key].type_personnel}`;
                                    opt.innerHTML = `${resulte[key].type_personnel}`;

                                    document.querySelector('#idgenrerecet').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#idgenrerecet').options.length = 1;
                            }
        
                        };
                        
                        httpInfostypinf.setRequestHeader('Content-Type', 'application/json');
                        httpInfostypinf.send();
    
                };
            
                let typ = document.querySelector('#idgenrerecet');
        
        if (typ !== null) 
        typ.onchange = () => 
        {
                    let Infostypinf;
                    if (window.XMLHttpRequest) {
                        Infostypinf = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        Infostypinf = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    document.querySelector('#idnomrecet').options.length = 1;
                    var idcai = document.querySelector('#idcaissr').value;
                    var typerecetchoisi = document.querySelector('#choisirtype')
                    .options[document.querySelector('#choisirtype').options.selectedIndex].value;
                    var ficationtypinf = document.querySelector('#idgenrerecet').
                    options[document.querySelector('#idgenrerecet').options.selectedIndex].value;
                    Infostypinf.open('GET', window.location.origin + `${APP_ROOT}/recettes/listenom/${idcai}/${typerecetchoisi}/${ficationtypinf}`, true);
                    Infostypinf.onload = () => {
                        const resul = JSON.parse(Infostypinf.responseText);
        
                            if(resul == null){
                                document.querySelector('#idnomrecet').value = "";
        
                            } 
                            if (Object.entries(resul).length >= 1) {
                        console.log(ficationtypinf);
                                for (let key in Object.entries(resul)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resul[key].nom}`;
                                    opt.innerHTML = `${resul[key].nom}`;
                                    document.querySelector('#idnomrecet').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#idnomrecet').options.length = 1;
                            }
        
                        };
                        
                        Infostypinf.setRequestHeader('Content-Type', 'application/json');
                        Infostypinf.send();
    
                };
        e.onclick = function () {
        let listerecette = document.querySelector('#recetForm');
        listerecette.setAttribute('action', `${APP_ROOT}/Rapport/recette/${e.dataset.cle_compagnie}`);
        }

    })
});