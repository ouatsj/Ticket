document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addtrirecettecr').forEach(function (e) 
    {
        document.querySelector('h3#cetTitlecr').innerHTML = `RECETTES COURRIER`;

        let typinf = document.querySelector('#choisirtypecr');
        
        if (typinf !== null) 
        typinf.onchange = () => 
        {
                document.querySelector('#idgenrerecetcr').options.length = 1;
                document.querySelector('#idnomrecetcr').options.length = 1;
                    let httpInfostypinf;
                    if (window.XMLHttpRequest) {
                        httpInfostypinf = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfostypinf = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var verificationtypinf = document.querySelector('#choisirtypecr')
                    .options[document.querySelector('#choisirtypecr').options.selectedIndex].value;
                    httpInfostypinf.open('GET', window.location.origin + `${APP_ROOT}/recettes/listegenre/${verificationtypinf}`, true);
                    httpInfostypinf.onload = () => {
                        const resulte = JSON.parse(httpInfostypinf.responseText);
        
                            if(resulte == null){
                                document.querySelector('#idgenrerecetcr').value = "";
        
                            } 
                            if (Object.entries(resulte).length >= 1) {
                        
                                for (let key in Object.entries(resulte)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resulte[key].type_personnel}`;
                                    opt.innerHTML = `${resulte[key].type_personnel}`;

                                    document.querySelector('#idgenrerecetcr').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#idgenrerecetcr').options.length = 1;
                            }
        
                        };
                        
                        httpInfostypinf.setRequestHeader('Content-Type', 'application/json');
                        httpInfostypinf.send();
    
                };
            
                let typ = document.querySelector('#idgenrerecetcr');
        
        if (typ !== null) 
        typ.onchange = () => 
        {
                    let Infostypinf;
                    if (window.XMLHttpRequest) {
                        Infostypinf = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        Infostypinf = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    document.querySelector('#idnomrecetcr').options.length = 1;
                    var idcai = document.querySelector('#idcaissrcr').value;
                    var typerecetchoisi = document.querySelector('#choisirtypecr')
                    .options[document.querySelector('#choisirtypecr').options.selectedIndex].value;
                    var ficationtypinf = document.querySelector('#idgenrerecetcr').
                    options[document.querySelector('#idgenrerecetcr').options.selectedIndex].value;
                    Infostypinf.open('GET', window.location.origin + `${APP_ROOT}/recettes/listenom/${idcai}/${typerecetchoisi}/${ficationtypinf}`, true);
                    Infostypinf.onload = () => {
                        const resul = JSON.parse(Infostypinf.responseText);
        
                            if(resul == null){
                                document.querySelector('#idnomrecetcr').value = "";
        
                            } 
                            if (Object.entries(resul).length >= 1) {
                        console.log(ficationtypinf);
                                for (let key in Object.entries(resul)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resul[key].nom}`;
                                    opt.innerHTML = `${resul[key].nom}`;
                                    document.querySelector('#idnomrecetcr').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#idnomrecetcr').options.length = 1;
                            }
        
                        };
                        
                        Infostypinf.setRequestHeader('Content-Type', 'application/json');
                        Infostypinf.send();
    
                };
        e.onclick = function () {
        let listerecettecr = document.querySelector('#recetFormcr');
        listerecettecr.setAttribute('action', `${APP_ROOT}/Rapport/recettecr/${e.dataset.cle_compagnie}`);
        }

    })
});