document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addtriclientversement').forEach(function (e) 
    {
        document.querySelector('h3#autredepTitle').innerHTML = `TRI AUTRE VERSEMENT`;

        let gpinfversem = document.querySelector('#autrevtype');
        
        if (gpinfversem !== null) 
        gpinfversem.onchange = () => 
        {
                document.querySelector('#autregtypeverse').options.length = 1;
                document.querySelector('#autregbeneficenom').options.length = 1;
                    let httpInfostypinfoversm;
                    if (window.XMLHttpRequest) {
                        httpInfostypinfoversm = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfostypinfoversm = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var verificationtypinfoversem = document.querySelector('#autrevtype')
                    .options[document.querySelector('#autrevtype').options.selectedIndex].value;
                    httpInfostypinfoversm.open('GET', window.location.origin + `${APP_ROOT}/depenses/versetrifour/${verificationtypinfoversem}`, true);
                    httpInfostypinfoversm.onload = () => {
                        const resps = JSON.parse(httpInfostypinfoversm.responseText);
        
                            if(resps == null){
                                document.querySelector('#autregtypeverse').value = "";
        
                            } 
                            if (Object.entries(resps).length >= 1) {
                        
                                for (let key in Object.entries(resps)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resps[key].genre_depens}`;
                                    opt.innerHTML = `${resps[key].genre_depens}`;
                                    document.querySelector('#autregtypeverse').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#autregtypeverse').options.length = 1;
                            }
        
                        };
                        
                        httpInfostypinfoversm.setRequestHeader('Content-Type', 'application/json');
                        httpInfostypinfoversm.send();
    
                };
            
                let typverses = document.querySelector('#autregtypeverse');
        
        if (typverses !== null) 
        typverses.onchange = () => 
        {
                    let Infostypinfoversm;
                    if (window.XMLHttpRequest) {
                        Infostypinfoversm = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        Infostypinfoversm = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    document.querySelector('#autregbeneficenom').options.length = 1;
                    var typedepchoisiversm = document.querySelector('#autrevtype')
                    .options[document.querySelector('#autrevtype').options.selectedIndex].value;

                    var ficationtypinfoversm = document.querySelector('#autregtypeverse').
                    options[document.querySelector('#autregtypeverse').options.selectedIndex].value;
                    Infostypinfoversm.open('GET', window.location.origin + `${APP_ROOT}/depenses/fournom/${typedepchoisiversm}/${ficationtypinfoversm}`, true);
                    Infostypinfoversm.onload = () => {
                        const respem = JSON.parse(Infostypinfoversm.responseText);
        
                            if(respem == null){
                                document.querySelector('#autregbeneficenom').value = "";
        
                            } 
                            if (Object.entries(respem).length >= 1) {
                                for (let key in Object.entries(respem)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${respem[key].nom_beneficiaire}`;
                                    opt.innerHTML = `${respem[key].nom_beneficiaire}`;
                                    document.querySelector('#autregbeneficenom').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#autregbeneficenom').options.length = 1;
                            }
        
                        };
                        
                        Infostypinfoversm.setRequestHeader('Content-Type', 'application/json');
                        Infostypinfoversm.send();
    
                };
        e.onclick = function () {
        let listeversefour = document.querySelector('#autreverseForm');
        listeversefour.setAttribute('action', `${APP_ROOT}/Rapport/versementfour/${e.dataset.cle_compagnie}`);
        }

    })
});