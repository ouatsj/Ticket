document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addtricette').forEach(function (e) 
    {
        document.querySelector('h3#etTitle').innerHTML = `TRI RECETTES`;

        let typinf = document.querySelector('#choisitype');
        
        if (typinf !== null) 
        typinf.onchange = () => 
        {
                document.querySelector('#idgenrrecet').options.length = 1;
                document.querySelector('#idnmrecet').options.length = 1;
                    let httpInfostypinf;
                    if (window.XMLHttpRequest) {
                        httpInfostypinf = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfostypinf = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var verificationtypinf = document.querySelector('#choisitype')
                    .options[document.querySelector('#choisitype').options.selectedIndex].value;
                    httpInfostypinf.open('GET', window.location.origin + `${APP_ROOT}/recettes/listegenre/${verificationtypinf}`, true);
                    httpInfostypinf.onload = () => {
                        const resulte = JSON.parse(httpInfostypinf.responseText);
        
                            if(resulte == null){
                                document.querySelector('#idgenrrecet').value = "";
        
                            } 
                            if (Object.entries(resulte).length >= 1) {
                        
                                for (let key in Object.entries(resulte)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resulte[key].type_personnel}`;
                                    opt.innerHTML = `${resulte[key].type_personnel}`;

                                    document.querySelector('#idgenrrecet').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#idgenrrecet').options.length = 1;
                            }
        
                        };
                        
                        httpInfostypinf.setRequestHeader('Content-Type', 'application/json');
                        httpInfostypinf.send();
    
                };
            
                let typ = document.querySelector('#idgenrrecet');
        
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
                    var typerecetchoisi = document.querySelector('#choisitype')
                    .options[document.querySelector('#choisitype').options.selectedIndex].value;
                    var ficationtypinf = document.querySelector('#idgenrrecet').
                    options[document.querySelector('#idgenrrecet').options.selectedIndex].value;
                    Infostypinf.open('GET', window.location.origin + `${APP_ROOT}/recettes/listenom/${typerecetchoisi}/${ficationtypinf}`, true);
                    Infostypinf.onload = () => {
                        const resul = JSON.parse(Infostypinf.responseText);
        
                            if(resul == null){
                                document.querySelector('#idnmrecet').value = "";
        
                            } 
                            if (Object.entries(resul).length >= 1) {
                        console.log(ficationtypinf);
                                for (let key in Object.entries(resul)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resul[key].nom}`;
                                    opt.innerHTML = `${resul[key].nom}`;
                                    document.querySelector('#idnmrecet').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#idnmrecet').options.length = 1;
                            }
        
                        };
                        
                        Infostypinf.setRequestHeader('Content-Type', 'application/json');
                        Infostypinf.send();
    
                };
        e.onclick = function () {
        let listerecette = document.querySelector('#ecetForm');
        listerecette.setAttribute('action', `${APP_ROOT}/Rapport/recette/${e.dataset.cle_compagnie}`);
        }

    })
});