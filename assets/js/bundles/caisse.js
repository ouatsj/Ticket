/* Bundle caisse — genere par scripts/build_module_bundles.php */
/* --- addrecette.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addrecette').forEach(function (e) 
    {
        document.querySelector('h3#recetteTitle').innerHTML = `ENREGISTREMENT DES RECETTES`;

        let typinf = document.querySelector('#idgenre');
        
        if (typinf !== null) 
            typinf.onchange = () => 
            {
                document.querySelector('#idnomprenom').options.length = 1;
                    let httpInfostypinf;
                    if (window.XMLHttpRequest) {
                        httpInfostypinf = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfostypinf = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var verificationtypinf = document.querySelector('#idgenre')
                    .options[document.querySelector('#idgenre').options.selectedIndex].value;
                    httpInfostypinf.open('GET', window.location.origin + `${APP_ROOT}/recettes/nom_genre/${verificationtypinf}`, true);
                    httpInfostypinf.onload = () => {
                        const resulte = JSON.parse(httpInfostypinf.responseText);
        
                            if(resulte == null){
                                document.querySelector('#idnomprenom').value = "";
        
                            } 
                            else
                            {

                                
                                if (Object.entries(resulte).length >= 1) {
                            
                                    for (let key in Object.entries(resulte)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${resulte[key].nomprenom_perso}`;
                                        opt.innerHTML = `${resulte[key].nomprenom_perso}`;
                                        document.querySelector('#idnomprenom').add(opt);
                                        
                                    }
                                } else {
                                    document.querySelector('#idnomprenom').options.length = 1;
                                }
                            }
                        };
                        
                        httpInfostypinf.setRequestHeader('Content-Type', 'application/json');
                        httpInfostypinf.send();
    
            };
            
        let persoinf = document.querySelector('#idpersonnel');
        
        if (persoinf !== null) 
            persoinf.onchange = () => 
            {   
                document.querySelector('#num_matric').value = "";
                document.querySelector('#idnompersonneclient').value = "";
                document.querySelector('#idnompersoclient').value = "";
                document.querySelector('#idnompersonneclient').style.display = 'none';
                document.querySelector('#idnompersoclient').style.display = 'none';

                var infoperso = document.querySelector('#idpersonnel')
                    .options[document.querySelector('#idpersonnel').options.selectedIndex].value;
                    if(infoperso === 'perso'){
                        document.querySelector('#matric_num').style.display = 'block';
                        document.querySelector('#num_matric').style.display = 'block';

                        let infpers = document.querySelector('#num_matric');
                        if (infpers !== null)
                        {

                            infpers.onkeyup = () => {
                                let httpInfosperso;
                                if (window.XMLHttpRequest) {
                                    httpInfosperso = new XMLHttpRequest();
                                } else if (window.ActiveXObject) {
                                    httpInfosperso = new ActiveXObject("Microsoft.XMLHTTP");
                                }
                                var verificatmat = document.querySelector('#num_matric').value;
                                
                                httpInfosperso.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifinfos/${verificatmat}`, true);
                                httpInfosperso.onload = () => {
                                    const infospers = JSON.parse(httpInfosperso.responseText);
                                    if (infospers == null) {
                                        document.querySelector('#idcomp').style.display = 'block';
                                        document.querySelector('#compid').style.display = 'block';
                                        document.querySelector('#idnom').style.display = 'block';
                                        document.querySelector('#idprenom').style.display = 'block';
                                        document.querySelector('#idadres').style.display = 'block';
                                        document.querySelector('#idadresse').style.display = 'block';
                                        document.querySelector('#idcont').style.display = 'block';
                                        document.querySelector('#contid').style.display = 'block';
                                        document.querySelector('#idsecond').style.display = 'block';
                                        document.querySelector('#secondid').style.display = 'block';
                                        document.querySelector('#idpermis').style.display = 'block';
                                        document.querySelector('#permisid').style.display = 'block';
                                        document.querySelector('#idcat').style.display = 'block';
                                        document.querySelector('#catid').style.display = 'block';
                                        document.querySelector('#iddel').style.display = 'block';
                                        document.querySelector('#delid').style.display = 'block';
                                        document.querySelector('#idexp').style.display = 'block';
                                        document.querySelector('#expid').style.display = 'block';
                                        document.querySelector('#idcnib').style.display = 'block';
                                        document.querySelector('#idnompersonneclient').style.display = 'none';
                                        document.querySelector('#cnibid').style.display = 'block';
                                        document.querySelector('#idcnidel').style.display = 'block';
                                        document.querySelector('#cnibidle').style.display = 'block';
                                        document.querySelector('#idexpir').style.display = 'block';
                                        document.querySelector('#expirid').style.display = 'block';
                                        document.querySelector('#idnompersonneclient').value = "";
                                                
                                    } else {
                                        if (Object.entries(infospers).length >= 1) {
                                            
                                            if (infospers.matricule == verificatmat)
                                                document.querySelector('#idnompersonneclient').style.display = 'block';
                                                document.querySelector('#idnompersonneclient').value = `${infospers.nomprenom_perso}`;
                                                document.querySelector('#idcomp').style.display = 'none';
                                                document.querySelector('#compid').style.display = 'none';
                                                document.querySelector('#idnom').style.display = 'none';
                                                document.querySelector('#idprenom').style.display = 'none';
                                                document.querySelector('#idadres').style.display = 'none';
                                                document.querySelector('#idadresse').style.display = 'none';
                                                document.querySelector('#idcont').style.display = 'none';
                                                document.querySelector('#contid').style.display = 'none';
                                                document.querySelector('#idsecond').style.display = 'none';
                                                document.querySelector('#secondid').style.display = 'none';
                                                document.querySelector('#idpermis').style.display = 'none';
                                                document.querySelector('#permisid').style.display = 'none';
                                                document.querySelector('#idcat').style.display = 'none';
                                                document.querySelector('#catid').style.display = 'none';
                                                document.querySelector('#iddel').style.display = 'none';
                                                document.querySelector('#delid').style.display = 'none';
                                                document.querySelector('#idexp').style.display = 'none';
                                                document.querySelector('#expid').style.display = 'none';
                                                document.querySelector('#idcnib').style.display = 'none';
                                                document.querySelector('#cnibid').style.display = 'none';
                                                document.querySelector('#idcnidel').style.display = 'none';
                                                document.querySelector('#cnibidle').style.display = 'none';
                                                document.querySelector('#idexpir').style.display = 'none';
                                                document.querySelector('#expirid').style.display = 'none';
                                                
                                        }
                                    }
                                };
                                httpInfosperso.setRequestHeader('Content-Type', 'application/json');
                                httpInfosperso.send();
                            };

                        }
                    }
                    //client 
                    if(infoperso === 'autrepersonnel'){
                        document.querySelector('#matric_num').style.display = 'block';
                        document.querySelector('#num_matric').style.display = 'block';

                        //recherche d'information du client
                        let inf = document.querySelector('#num_matric');
                        if (inf !== null)
                        inf.onkeyup = () => {
                            let httpInfos;
                            if (window.XMLHttpRequest) {
                                httpInfos = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfos = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            var verificat = document.querySelector('#num_matric').value;
                            
                            httpInfos.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifinfosbis/${verificat}`, true);
                            httpInfos.onload = () => {
                                const infos = JSON.parse(httpInfos.responseText);
                                if (infos == null) {
                                    document.querySelector('#nomclientid').style.display = 'block';
                                    document.querySelector('#idnomclient').style.display = 'block';
                                    document.querySelector('#idpren').style.display = 'block';
                                    document.querySelector('#prenid').style.display = 'block';
                                    document.querySelector('#idnompersoclient').style.display = 'none';
                                    document.querySelector('#lieucl').style.display = 'block';
                                    document.querySelector('#cl_lieu').style.display = 'block';
                                    document.querySelector('#idnompersoclient').value = "";
                                            
                                } else {
                                    if (Object.entries(infos).length >= 1) {
                                        
                                        if (infos.contact_client == verificat)
                                            document.querySelector('#idnompersoclient').style.display = 'block';
                                            document.querySelector('#idnompersoclient').value = `${infos.nom_client}  ${infos.prenom_client}`;
                                            document.querySelector('#nomclientid').style.display = 'none';
                                            document.querySelector('#idnomclient').style.display = 'none';
                                            document.querySelector('#idpren').style.display = 'none';
                                            document.querySelector('#prenid').style.display = 'none';
                                            document.querySelector('#lieucl').style.display = 'none';                                           
                                            document.querySelector('#cl_lieu').style.display = 'none';
                                        
                                    }
                                }
                            };
                            httpInfos.setRequestHeader('Content-Type', 'application/json');
                            httpInfos.send();
                        };

                    }

                    if(infoperso === 'client'){
                        document.querySelector('#matric_num').style.display = 'block';
                        document.querySelector('#num_matric').style.display = 'block';

                        //recherche d'information du client
                        let infclt = document.querySelector('#num_matric');
                        if (infclt !== null)
                        infclt.onkeyup = () => {
                            let httpInfosclt;
                            if (window.XMLHttpRequest) {
                                httpInfosclt = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosclt = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            var verificatclt = document.querySelector('#num_matric').value;
                            
                            httpInfosclt.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifinfosbis/${verificatclt}`, true);
                            httpInfosclt.onload = () => {
                                const infosclt = JSON.parse(httpInfosclt.responseText);
                                if (infosclt == null) {
                                    document.querySelector('#nomclientid').style.display = 'block';
                                    document.querySelector('#idnomclient').style.display = 'block';
                                    document.querySelector('#idpren').style.display = 'block';
                                    document.querySelector('#prenid').style.display = 'block';
                                    document.querySelector('#idnompersoclient').style.display = 'none';
                                    document.querySelector('#lieucl').style.display = 'block';
                                    document.querySelector('#cl_lieu').style.display = 'block';
                                    document.querySelector('#idnompersoclient').value = "";
                                            
                                } else {
                                    if (Object.entries(infosclt).length >= 1) {
                                        
                                        if (infosclt.contact_client == verificatclt)
                                            document.querySelector('#idnompersoclient').style.display = 'block';
                                            document.querySelector('#idnompersoclient').value = `${infosclt.nom_client}  ${infosclt.prenom_client}`;
                                            document.querySelector('#nomclientid').style.display = 'none';
                                            document.querySelector('#idnomclient').style.display = 'none';
                                            document.querySelector('#idpren').style.display = 'none';
                                            document.querySelector('#prenid').style.display = 'none';
                                            document.querySelector('#lieucl').style.display = 'none';                                           
                                            document.querySelector('#cl_lieu').style.display = 'none';
                                        
                                    }
                                }
                            };
                            httpInfosclt.setRequestHeader('Content-Type', 'application/json');
                            httpInfosclt.send();
                        };

                    }
            };
        e.onclick = function () {
        let listerecette = document.querySelector('#recetteForm');
        listerecette.setAttribute('action', `${APP_ROOT}/Recettes/add/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- adddepense.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adddepense').forEach(function (e) 
    {
        document.querySelector('h3#depenseTitle').innerHTML = `ENREGISTREMENT DES DEPENSES`;


        let persoinf = document.querySelector('#personnel_id');
        
        if (persoinf !== null) 
            persoinf.onchange = () => 
            {
                document.querySelector('#nummatric_').value = "";
                document.querySelector('#idnompersonneclient').value = "";
                document.querySelector('#idnompersoclient').value = "";
                document.querySelector('#idnompersonneclient').style.display = 'none';
                document.querySelector('#idnompersoclient').style.display = 'none';
                var infoperso = document.querySelector('#personnel_id')
                    .options[document.querySelector('#personnel_id').options.selectedIndex].value;

                    if(infoperso === 'perso'){
                        document.querySelector('#_nummatric').style.display = 'block';
                        document.querySelector('#nummatric_').style.display = 'block';

                        let infpers = document.querySelector('#nummatric_');
                        if (infpers !== null)
                        {

                            infpers.onkeyup = () => {
                                let httpInfosperso;
                                if (window.XMLHttpRequest) {
                                    httpInfosperso = new XMLHttpRequest();
                                } else if (window.ActiveXObject) {
                                    httpInfosperso = new ActiveXObject("Microsoft.XMLHTTP");
                                }
                                var verificatmat = document.querySelector('#nummatric_').value;
                                
                                httpInfosperso.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifinfos/${verificatmat}`, true);
                                httpInfosperso.onload = () => {
                                    const infospers = JSON.parse(httpInfosperso.responseText);
                                    if (infospers == null) {
                                        document.querySelector('#idcomp').style.display = 'block';
                                        document.querySelector('#compid').style.display = 'block';
                                        document.querySelector('#idnom').style.display = 'block';
                                        document.querySelector('#idprenom').style.display = 'block';
                                        document.querySelector('#idadres').style.display = 'block';
                                        document.querySelector('#idadresse').style.display = 'block';
                                        document.querySelector('#idcont').style.display = 'block';
                                        document.querySelector('#contid').style.display = 'block';
                                        document.querySelector('#idsecond').style.display = 'block';
                                        document.querySelector('#secondid').style.display = 'block';
                                        document.querySelector('#idpermis').style.display = 'block';
                                        document.querySelector('#permisid').style.display = 'block';
                                        document.querySelector('#idcat').style.display = 'block';
                                        document.querySelector('#catid').style.display = 'block';
                                        document.querySelector('#iddel').style.display = 'block';
                                        document.querySelector('#delid').style.display = 'block';
                                        document.querySelector('#idexp').style.display = 'block';
                                        document.querySelector('#expid').style.display = 'block';
                                        document.querySelector('#idcnib').style.display = 'block';
                                        document.querySelector('#idnompersonneclient').style.display = 'none';
                                        document.querySelector('#cnibid').style.display = 'block';
                                        document.querySelector('#idcnidel').style.display = 'block';
                                        document.querySelector('#cnibdelid').style.display = 'block';
                                        document.querySelector('#idexpir').style.display = 'block';
                                        document.querySelector('#expirid').style.display = 'block';
                                        document.querySelector('#idnompersonneclient').value = "";
                                                
                                    } else {
                                        if (Object.entries(infospers).length >= 1) {
                                            
                                            if (infospers.matricule == verificatmat)
                                                document.querySelector('#idnompersonneclient').style.display = 'block';
                                                document.querySelector('#idnompersonneclient').value = `${infospers.nomprenom_perso}`;
                                                document.querySelector('#idcomp').style.display = 'none';
                                                document.querySelector('#compid').style.display = 'none';
                                                document.querySelector('#idnom').style.display = 'none';
                                                document.querySelector('#idprenom').style.display = 'none';
                                                document.querySelector('#idadres').style.display = 'none';
                                                document.querySelector('#idadresse').style.display = 'none';
                                                document.querySelector('#idcont').style.display = 'none';
                                                document.querySelector('#contid').style.display = 'none';
                                                document.querySelector('#idsecond').style.display = 'none';
                                                document.querySelector('#secondid').style.display = 'none';
                                                document.querySelector('#idpermis').style.display = 'none';
                                                document.querySelector('#permisid').style.display = 'none';
                                                document.querySelector('#idcat').style.display = 'none';
                                                document.querySelector('#catid').style.display = 'none';
                                                document.querySelector('#iddel').style.display = 'none';
                                                document.querySelector('#delid').style.display = 'none';
                                                document.querySelector('#idexp').style.display = 'none';
                                                document.querySelector('#expid').style.display = 'none';
                                                document.querySelector('#idcnib').style.display = 'none';
                                                document.querySelector('#cnibid').style.display = 'none';
                                                document.querySelector('#idcnidel').style.display = 'none';
                                                document.querySelector('#cnibdelid').style.display = 'none';
                                                document.querySelector('#idexpir').style.display = 'none';
                                                document.querySelector('#expirid').style.display = 'none';
                                                
                                        }
                                    }
                                };
                                httpInfosperso.setRequestHeader('Content-Type', 'application/json');
                                httpInfosperso.send();
                            };

                        }
                    }
                    //client 
                    if(infoperso === 'autrepersonnel'){

                        document.querySelector('#_nummatric').style.display = 'block';
                        document.querySelector('#nummatric_').style.display = 'block';

                        //recherche d'information du client
                        let inf = document.querySelector('#nummatric_');
                        if (inf !== null)
                        inf.onkeyup = () => {
                            let httpInfos;
                            if (window.XMLHttpRequest) {
                                httpInfos = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfos = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            var verificat = document.querySelector('#nummatric_').value;
                            
                            httpInfos.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifinfosbis/${verificat}`, true);
                            httpInfos.onload = () => {
                                const infos = JSON.parse(httpInfos.responseText);
                                if (infos == null) {
                                    document.querySelector('#nomclientid').style.display = 'block';
                                    document.querySelector('#idnomclient').style.display = 'block';
                                    document.querySelector('#idpren').style.display = 'block';
                                    document.querySelector('#prenid').style.display = 'block';
                                    document.querySelector('#lieucl').style.display = 'block';
                                    document.querySelector('#cl_lieu').style.display = 'block';
                                    document.querySelector('#idnompersoclient').style.display = 'none';
                                    document.querySelector('#idnompersoclient').value = "";
                                        
                                            
                                } else {
                                    if (Object.entries(infos).length >= 1) {
                                        
                                        if (infos.contact_client == verificat)
                                            document.querySelector('#idnompersoclient').style.display = 'block';
                                            document.querySelector('#idnompersoclient').value = `${infos.nom_client}  ${infos.prenom_client}`;
                                            document.querySelector('#nomclientid').style.display = 'none';
                                            document.querySelector('#idnomclient').style.display = 'none';
                                            document.querySelector('#idpren').style.display = 'none';
                                            document.querySelector('#prenid').style.display = 'none';
                                            document.querySelector('#lieucl').style.display = 'none';
                                            document.querySelector('#cl_lieu').style.display = 'none';
                                        
                                    }
                                }
                            };
                            httpInfos.setRequestHeader('Content-Type', 'application/json');
                            httpInfos.send();
                        };

                    }

                    if(infoperso === 'client'){
                        document.querySelector('#_nummatric').style.display = 'block';
                        document.querySelector('#nummatric_').style.display = 'block';

                        //recherche d'information du client
                        let infcl = document.querySelector('#nummatric_');
                        if (infcl !== null)
                        infcl.onkeyup = () => {
                            let httpInfoscl;
                            if (window.XMLHttpRequest) {
                                httpInfoscl = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfoscl = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            var verificatcl = document.querySelector('#nummatric_').value;
                            
                            httpInfoscl.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifinfosbis/${verificatcl}`, true);
                            httpInfoscl.onload = () => {
                                const infoscl = JSON.parse(httpInfoscl.responseText);
                                if (infoscl == null) {
                                    document.querySelector('#nomclientid').style.display = 'block';
                                    document.querySelector('#idnomclient').style.display = 'block';
                                    document.querySelector('#idpren').style.display = 'block';
                                    document.querySelector('#prenid').style.display = 'block';
                                    document.querySelector('#lieucl').style.display = 'block';
                                    document.querySelector('#cl_lieu').style.display = 'block';
                                    document.querySelector('#idnompersoclient').style.display = 'none';
                                    document.querySelector('#idnompersoclient').value = "";
                                        
                                            
                                } else {
                                    if (Object.entries(infoscl).length >= 1) {
                                        
                                        if (infoscl.contact_client == verificatcl)
                                            document.querySelector('#idnompersoclient').style.display = 'block';
                                            document.querySelector('#idnompersoclient').value = `${infoscl.nom_client}  ${infoscl.prenom_client}`;
                                            document.querySelector('#nomclientid').style.display = 'none';
                                            document.querySelector('#idnomclient').style.display = 'none';
                                            document.querySelector('#idpren').style.display = 'none';
                                            document.querySelector('#prenid').style.display = 'none';
                                            document.querySelector('#lieucl').style.display = 'none';                                            
                                            document.querySelector('#cl_lieu').style.display = 'none';
                                        
                                    }
                                }
                            };
                            httpInfoscl.setRequestHeader('Content-Type', 'application/json');
                            httpInfoscl.send();
                        };

                    }

            };
                
            verif = function () 
            {
                var m = parseInt(document.querySelector('#depensemontant').value);
                    var n = document.querySelector('#depensemontant').value;
                    var sold = parseInt(document.querySelector('#monttcaisse').value);
                        
                if(sold < m) 
                {
                    document.querySelector('#smsmt').style.display = 'block';
                    document.querySelector('#smsmontant').innerHTML = `le montant que vous aviez saisi dépasse le solde de votre caisse`;
                    
                    document.querySelector('#depensemontant').value = 'VERIFIER SOLDE';  
                } 
                else
                {

                    document.querySelector('#smsmt').style.display = 'none';

                    document.querySelector('#depensemontant').value = n ;
                    
                }
            };
            
        
        e.onclick = function () {
        let listedepense = document.querySelector('#depenseForm');
        listedepense.setAttribute('action', `${APP_ROOT}/Depenses/add/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- addtrirecette.js --- */
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
;
/* --- addtrirecettecr.js --- */
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
;
/* --- addtridepense.js --- */
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
                    document.querySelector('#recaptgnom').options.length = 1;
                    
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
                                for (let key in Object.entries(resperecapt)) {
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
;
/* --- addtriautredepense.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addtriautredepense').forEach(function (e) 
    {
        document.querySelector('h3#autredepTitle').innerHTML = `TRI AUTRES DEPENSES`;

        let gpinf = document.querySelector('#autredtype');
        
        if (gpinf !== null) 
        gpinf.onchange = () => 
        {
                document.querySelector('#autregtype').options.length = 1;
                document.querySelector('#autregnom').options.length = 1;
                    let httpInfostypinfo;
                    if (window.XMLHttpRequest) {
                        httpInfostypinfo = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfostypinfo = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var verificationtypinfo = document.querySelector('#autredtype')
                    .options[document.querySelector('#autredtype').options.selectedIndex].value;
                    httpInfostypinfo.open('GET', window.location.origin + `${APP_ROOT}/depenses/autrelistegenre/${verificationtypinfo}`, true);
                    httpInfostypinfo.onload = () => {
                        const resp = JSON.parse(httpInfostypinfo.responseText);
        
                            if(resp == null){
                                document.querySelector('#autregtype').value = "";
        
                            } 
                            if (Object.entries(resp).length >= 1) {
                        
                                for (let key in Object.entries(resp)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resp[key].genre_depens}`;
                                    opt.innerHTML = `${resp[key].genre_depens}`;
                                    document.querySelector('#autregtype').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#autregtype').options.length = 1;
                            }
        
                        };
                        
                        httpInfostypinfo.setRequestHeader('Content-Type', 'application/json');
                        httpInfostypinfo.send();
    
                };
            
                let typo = document.querySelector('#autregtype');
        
        if (typo !== null) 
        typo.onchange = () => 
        {
                    let Infostypinfo;
                    if (window.XMLHttpRequest) {
                        Infostypinfo = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        Infostypinfo = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    document.querySelector('#autregnom').options.length = 1;
                    var autredepensechoisi = document.querySelector('#autredtype')
                    .options[document.querySelector('#autredtype').options.selectedIndex].value;

                    var ficationtypinfo = document.querySelector('#autregtype').
                    options[document.querySelector('#autregtype').options.selectedIndex].value;
                    Infostypinfo.open('GET', window.location.origin + `${APP_ROOT}/depenses/autrelistenom/${autredepensechoisi}/${ficationtypinfo}`, true);
                    Infostypinfo.onload = () => {
                        const respe = JSON.parse(Infostypinfo.responseText);
        
                            if(respe == null){
                                document.querySelector('#autregnom').value = "";
        
                            } 
                            if (Object.entries(respe).length >= 1) {
                                for (let key in Object.entries(respe)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${respe[key].nom_perso}`;
                                    opt.innerHTML = `${respe[key].nom_perso}`;
                                    document.querySelector('#autregnom').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#autregnom').options.length = 1;
                            }
        
                        };
                        
                        Infostypinfo.setRequestHeader('Content-Type', 'application/json');
                        Infostypinfo.send();
    
                };
        e.onclick = function () {
        let listedepense = document.querySelector('#autredpForm');
        listedepense.setAttribute('action', `${APP_ROOT}/Rapport/autredepense/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- addtridepot.js --- */
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
;
/* --- addtriautredepot.js --- */
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
;
/* --- addautredepense.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addautredepense').forEach(function (e) 
    {
        document.querySelector('h3#depTitle').innerHTML = `ENREGISTREMENT DES AUTRES DEPENSES`;
        

                verifautre = function () {
                    
                    var m = parseInt(document.querySelector('#autredepmontant').value);
                    var n = document.querySelector('#autredepmontant').value;
                    var solde = parseInt(document.querySelector('#autremontcaisse').value);
                        
                            if (solde < m) 
                            {
        
                                document.querySelector('#autresms').style.display = 'block';
                                document.querySelector('#smsmontant').innerHTML = `le montant que vous aviez saisi dépasse le solde de votre caisse`;
                                
                                document.querySelector('#autredepmontant').value = 'VERIFIER SOLDE';
                            } 
                            else{

                                document.querySelector('#autresms').style.display = 'none';

                                document.querySelector('#autredepmontant').value = n ;
                            }
                };
            
        e.onclick = function () {
        let listedepens = document.querySelector('#depensForm');
        listedepens.setAttribute('action', `${APP_ROOT}/Depenses/addautre/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- addepot.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addepot').forEach(function (e) 
    {
        document.querySelector('h3#depotTitle').innerHTML = `ENREGISTREMENT DES DEPOTS BANCAIRE`;

                depotverif = function () {
                    
                    var depot = parseInt(document.querySelector('#depotmontant').value);
                    var depo = document.querySelector('#depotmontant').value;
                    var soldedp = parseInt(document.querySelector('#soldecaisse').value);
                        
                            if (soldedp < depot) 
                            {
        
                                document.querySelector('#smsdepot').style.display = 'block';
                                document.querySelector('#depotsms').innerHTML = `le montant que vous aviez saisi dépasse le solde de votre caisse`;
                                
                                document.querySelector('#depotmontant').value = 'VERIFIER SOLDE';
                            } 
                            else{

                                document.querySelector('#smsdepot').style.display = 'none';

                                document.querySelector('#depotmontant').value = depo ;
                            }
                    };
            
        e.onclick = function () {
        let listedepot = document.querySelector('#depotForm');
        listedepot.setAttribute('action', `${APP_ROOT}/Depots/adddepot/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- addautredepot.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addautredepot').forEach(function (e) 
    {
        document.querySelector('h3#potTitle').innerHTML = `ENREGISTREMENT DES DEPOTS CAISSE`;

        let depotinformation = document.querySelector('#genredepot');
        
        if (depotinformation !== null) 
        depotinformation.onchange = () => 
        {
                document.querySelector('#prenomnomident').options.length = 1;
                    let httpInfosdepot;
                    if (window.XMLHttpRequest) {
                        httpInfosdepot = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfosdepot = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var verifierdepot = document.querySelector('#genredepot')
                    .options[document.querySelector('#genredepot').options.selectedIndex].value;
                    httpInfosdepot.open('GET', window.location.origin + `${APP_ROOT}/depots/depot_genre/${verifierdepot}`, true);
                    httpInfosdepot.onload = () => {
                        const resultedepot = JSON.parse(httpInfosdepot.responseText);
        
                            if(resultedepot == null){
                                document.querySelector('#prenomnomident').value = "";
        
                            }else
                            { 
                                if (Object.entries(resultedepot).length >= 1) {
                            
                                    for (let key in Object.entries(resultedepot)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${resultedepot[key].nomprenom_perso}`;
                                        opt.innerHTML = `${resultedepot[key].nomprenom_perso}`;
                                        document.querySelector('#prenomnomident').add(opt);
                                        
                                    }
                                } else {
                                    document.querySelector('#prenomnomident').options.length = 1;
                                }
                            }
        
                        };
                        
                        httpInfosdepot.setRequestHeader('Content-Type', 'application/json');
                        httpInfosdepot.send();
    
                };

                verifdepo = function () {
                    
                    var depot = parseInt(document.querySelector('#autredepotmontant').value);
                    var depo = document.querySelector('#autredepotmontant').value;
                    var soldedp = parseInt(document.querySelector('#soldeautre').value);
                        
                            if (soldedp < depot) 
                            {
        
                                document.querySelector('#autresmsdepot').style.display = 'block';
                                document.querySelector('#autredepotsms').innerHTML = `le montant que vous aviez saisi dépasse le solde de votre caisse`;
                                
                                document.querySelector('#autredepotmontant').value = 'VERIFIER SOLDE';
                            } 
                            else{

                                document.querySelector('#autresmsdepot').style.display = 'none';

                                document.querySelector('#autredepotmontant').value = depo ;
                            }
                    };
            
        e.onclick = function () {
        let listeautre = document.querySelector('#autredepotForm');
        listeautre.setAttribute('action', `${APP_ROOT}/Depots/addsous/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- autreadddepot.js --- */
document.addEventListener('DOMContentLoaded', () => {
        
    document.querySelectorAll('.autreadddepot').forEach(function (e) 
    {
        document.querySelector('h3#addautreTitle').innerHTML = `ENREGISTREMENT DES DEPOTS CLIENT`;

let typcl = document.querySelector('#client_ident');
        
        if (typcl !== null) 
        typcl.onchange = () => 
        {
            let Infostypinfcl;
            if (window.XMLHttpRequest) {
                Infostypinfcl = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                Infostypinfcl = new ActiveXObject("Microsoft.XMLHTTP");
            }
            document.querySelector('#prenomnomident').options.length = 1;
            var typerecetchoisicl = document.querySelector('#client_ident')
            .options[document.querySelector('#client_ident').options.selectedIndex].value;

            Infostypinfcl.open('GET', window.location.origin + `${APP_ROOT}/depenses/listesnom/${typerecetchoisicl}`, true);
            Infostypinfcl.onload = () => {
                const resulcl = JSON.parse(Infostypinfcl.responseText);

                    if(resulcl == null){
                        document.querySelector('#prenomnomident').value = "";

                    } 
                    if (Object.entries(resulcl).length >= 1) {

                        for (let key in Object.entries(resulcl)) {
                            let opt = document.createElement('option');
                            opt.value = `${resulcl[key].nom_client} ${resulcl[key].prenom_client}`;
                            opt.innerHTML = `${resulcl[key].nom_client} ${resulcl[key].prenom_client}`;
                            document.querySelector('#prenomnomident').add(opt);
                            
                        }
                    } else {
                        document.querySelector('#prenomnomident').options.length = 1;
                    }

                };
                
                Infostypinfcl.setRequestHeader('Content-Type', 'application/json');
                Infostypinfcl.send();

        };
                depverif = function () {
                    
                    var depos = parseInt(document.querySelector('#depotautremontant').value);
                    var depo = document.querySelector('#depotautremontant').value;
                    var solddp = parseInt(document.querySelector('#soldeautrecaisse').value);
                        
                            if (solddp < depos) 
                            {
        
                                document.querySelector('#smsautredepot').style.display = 'block';
                                document.querySelector('#depotautresms').innerHTML = `le montant que vous aviez saisi dépasse le solde de votre caisse`;
                                
                                document.querySelector('#depotautremontant').value = 'VERIFIER SOLDE';
                            } 
                            else{

                                document.querySelector('#smsautredepot').style.display = 'none';

                                document.querySelector('#depotautremontant').value = depo ;
                            }
                    };
            
        e.onclick = function () {
        let autrelistedepot = document.querySelector('#depautredepot');
        autrelistedepot.setAttribute('action', `${APP_ROOT}/Depots/addautre/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- addversebank.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addversebank').forEach(function (e) 
    {
        document.querySelector('h3#bankTitle').innerHTML = `ENREGISTREMENT DES VERSEMENTS BANCAIRE`;

        verseverif = function () {
                    
                    var m = parseInt(document.querySelector('#versmontant').value);
                    var n = document.querySelector('#versmontant').value;
                    var solde = parseInt(document.querySelector('#soldecaisse').value);
                        
                            if (solde < m) 
                            {
        
                                document.querySelector('#smsverser').style.display = 'block';
                                document.querySelector('#versementsms').innerHTML = `le montant que vous aviez saisi dépasse le solde de votre caisse`;
                                
                                document.querySelector('#versmontant').value = 'VERIFIER SOLDE';
                            } 
                            else{

                                document.querySelector('#smsverser').style.display = 'none';

                                document.querySelector('#versmontant').value = n ;
                            }
                    };
            
        e.onclick = function () {
        let banq = document.querySelector('#verseFormbank');
        banq.setAttribute('action', `${APP_ROOT}/Caisses/addbank/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- addverseautre.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addverseautre').forEach(function (e) 
    {
        document.querySelector('h3#autrevserseTitle').innerHTML = `ENREGISTREMENT DES VERSEMENTS CLIENT`;

    let typcl = document.querySelector('#client_ident');
        
        if (typcl !== null) 
        typcl.onchange = () => 
        {
            let Infostypinfcl;
            if (window.XMLHttpRequest) {
                Infostypinfcl = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                Infostypinfcl = new ActiveXObject("Microsoft.XMLHTTP");
            }
            document.querySelector('#prenomnomident').options.length = 1;
            var typerecetchoisicl = document.querySelector('#client_ident')
            .options[document.querySelector('#client_ident').options.selectedIndex].value;

            Infostypinfcl.open('GET', window.location.origin + `${APP_ROOT}/depenses/listesnom/${typerecetchoisicl}`, true);
            Infostypinfcl.onload = () => {
                const resulcl = JSON.parse(Infostypinfcl.responseText);

                    if(resulcl == null){
                        document.querySelector('#prenomnomident').value = "";

                    } 
                    if (Object.entries(resulcl).length >= 1) {

                        for (let key in Object.entries(resulcl)) {
                            let opt = document.createElement('option');
                            opt.value = `${resulcl[key].nom_client} ${resulcl[key].prenom_client}`;
                            opt.innerHTML = `${resulcl[key].nom_client} ${resulcl[key].prenom_client}`;
                            document.querySelector('#prenomnomident').add(opt);
                            
                        }
                    } else {
                        document.querySelector('#prenomnomident').options.length = 1;
                    }

                };
                
                Infostypinfcl.setRequestHeader('Content-Type', 'application/json');
                Infostypinfcl.send();

        };
        verseverif = function () {
                    
                    var m = parseInt(document.querySelector('#autreversmontant').value);
                    var n = document.querySelector('#autreversmontant').value;
                    var solde = parseInt(document.querySelector('#soldecaisse').value);
                        
                            if (solde < m) 
                            {
        
                                document.querySelector('#autresmsverser').style.display = 'block';
                                document.querySelector('#autreversementsms').innerHTML = `le montant que vous aviez saisi dépasse le solde de votre caisse`;
                                
                                document.querySelector('#autreversmontant').value = 'VERIFIER SOLDE';
                            } 
                            else{

                                document.querySelector('#autresmsverser').style.display = 'none';

                                document.querySelector('#autreversmontant').value = n ;
                            }
                    };
            
        e.onclick = function () {
        let autre = document.querySelector('#verseFormautre');
        autre.setAttribute('action', `${APP_ROOT}/Caisses/addverseautre/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- addversefour.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addversefour').forEach(function (e) 
    {
        document.querySelector('h3#autrevserseTitle').innerHTML = `ENREGISTREMENT DES VERSEMENTS FOURNISSEUR`;

        let typf = document.querySelector('#fourni_id');
        
        if (typf !== null) 
        typf.onchange = () => 
        {
            let Infostypinff;
            if (window.XMLHttpRequest) {
                Infostypinff = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                Infostypinff = new ActiveXObject("Microsoft.XMLHTTP");
            }
            document.querySelector('#nomprenomf').options.length = 1;
            var typerecetchoisif = document.querySelector('#fourni_id')
            .options[document.querySelector('#fourni_id').options.selectedIndex].value;

            Infostypinff.open('GET', window.location.origin + `${APP_ROOT}/depenses/listesnom/${typerecetchoisif}`, true);
            Infostypinff.onload = () => {
                const resulf = JSON.parse(Infostypinff.responseText);

                    if(resulf == null){
                        document.querySelector('#nomprenomf').value = "";

                    } 
                    if (Object.entries(resulf).length >= 1) {

                        for (let key in Object.entries(resulf)) {
                            let opt = document.createElement('option');
                            opt.value = `${resulf[key].nom_client} ${resulf[key].prenom_client}`;
                            opt.innerHTML = `${resulf[key].nom_client} ${resulf[key].prenom_client}`;
                            document.querySelector('#nomprenomf').add(opt);
                            
                        }
                    } else {
                        document.querySelector('#nomprenomf').options.length = 1;
                    }

                };
                
                Infostypinff.setRequestHeader('Content-Type', 'application/json');
                Infostypinff.send();

        };
        verseverif = function () {
                    
                    var m = parseInt(document.querySelector('#autreversmontant').value);
                    var n = document.querySelector('#autreversmontant').value;
                    var solde = parseInt(document.querySelector('#soldecaisse').value);
                        
                            if (solde < m) 
                            {
        
                                document.querySelector('#autresmsverser').style.display = 'block';
                                document.querySelector('#autreversementsms').innerHTML = `le montant que vous aviez saisi dépasse le solde de votre caisse`;
                                
                                document.querySelector('#autreversmontant').value = 'VERIFIER SOLDE';
                            } 
                            else{

                                document.querySelector('#autresmsverser').style.display = 'none';

                                document.querySelector('#autreversmontant').value = n ;
                            }
                    };
            
        e.onclick = function () {
        let autre = document.querySelector('#verseFormautre');
        autre.setAttribute('action', `${APP_ROOT}/Caisses/addverseautrefour/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- addversementcaisse.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addversementcaisse').forEach(function (e) 
    {
        document.querySelector('h3#verseTitle').innerHTML = `ENREGISTREMENT DES VERSEMENTS CAISSE`;
        
        let versinformation = document.querySelector('#caissegenredepot');
        
        if (versinformation !== null) 
        versinformation.onchange = () => 
        {
                document.querySelector('#prenomident').options.length = 1;
                    let httpInfosverse;
                    if (window.XMLHttpRequest) {
                        httpInfosverse = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfosverse = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var verifiegenreversem = document.querySelector('#caissegenredepot')
                    .options[document.querySelector('#caissegenredepot').options.selectedIndex].value;
                    httpInfosverse.open('GET', window.location.origin + `${APP_ROOT}/depots/depot_genre/${verifiegenreversem}`, true);
                    httpInfosverse.onload = () => {
                        const resulteverse = JSON.parse(httpInfosverse.responseText);
        
                            if(resulteverse == null){
                                document.querySelector('#prenomident').value = "";
        
                            }else
                            { 
                                if (Object.entries(resulteverse).length >= 1) {
                            
                                    for (let key in Object.entries(resulteverse)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${resulteverse[key].nomprenom_perso}`;
                                        opt.innerHTML = `${resulteverse[key].nomprenom_perso}`;
                                        document.querySelector('#prenomident').add(opt);
                                        
                                    }
                                } else {
                                    document.querySelector('#prenomident').options.length = 1;
                                }
                            }
        
                        };
                        
                        httpInfosverse.setRequestHeader('Content-Type', 'application/json');
                        httpInfosverse.send();
    
                };

        verseverif = function () {
                    
                    var m = parseInt(document.querySelector('#autreversmontan').value);
                    var n = document.querySelector('#autreversmontan').value;
                    var solde = parseInt(document.querySelector('#soldecaiss').value);
                        
                            if (solde < m) 
                            {
        
                                document.querySelector('#autresmsverse').style.display = 'block';
                                document.querySelector('#autreversementsm').innerHTML = `le montant que vous aviez saisi dépasse le solde de votre caisse`;
                                
                                document.querySelector('#autreversmontan').value = 'VERIFIER SOLDE';
                            } 
                            else{

                                document.querySelector('#autresmsverse').style.display = 'none';

                                document.querySelector('#autreversmontan').value = n ;
                            }
        };
            
        e.onclick = function () {
        let autre = document.querySelector('#verseFormcaisse');
        autre.setAttribute('action', `${APP_ROOT}/Caisses/adverscaisse/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- upversement.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.upversement').forEach(function (e) {
        
        e.onclick = function () {
            let mtaForm = document.querySelector('#verForm');
            mtaForm.setAttribute('action', `${APP_ROOT}/Caisses/upautreversment/${e.dataset.cle_compagnie}/${e.dataset.ID}`);
            document.querySelector('h3#Titleverse').innerHTML = `MODIFICATION SUR LE VERSEMENT DE : ${e.dataset.nombeneficiaire}`;
            $('#vesreinterneid').val(`${e.dataset.type_versement}`);
            $('#caissegenreversid').val(`${e.dataset.id_genre_versement}`);
            $('#prenomidentif').val(`${e.dataset.nombeneficiaire}`);
            $('#personnelsid').val(`${e.dataset.typpersonnel}`);
            $('#autremontantversemid').val(`${e.dataset.montant_verser}`);
            $('#autrebordereauid').val(`${e.dataset.bordereau_verser}`);
            $('#autrecommentverseid').val(`${e.dataset.commentaire}`);
            $('#autredateversementsid').val(`${e.dataset.dat}`);

        }
    })
});
;
/* --- adversementcaisse.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adversementcaisse').forEach(function (e) 
    {
        document.querySelector('h3#versTitle').innerHTML = `ENREGISTREMENT DES VERSEMENTS CAISSE`;
        
        let versinformation = document.querySelector('#caissgenredepot');
        
        if (versinformation !== null) 
        versinformation.onchange = () => 
        {
                document.querySelector('#prenomiden').options.length = 1;
                    let httpInfosverse;
                    if (window.XMLHttpRequest) {
                        httpInfosverse = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfosverse = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var verifiegenreversem = document.querySelector('#caissgenredepot')
                    .options[document.querySelector('#caissgenredepot').options.selectedIndex].value;
                    httpInfosverse.open('GET', window.location.origin + `${APP_ROOT}/depots/depot_genre/${verifiegenreversem}`, true);
                    httpInfosverse.onload = () => {
                        const resulteverse = JSON.parse(httpInfosverse.responseText);
        
                            if(resulteverse == null){
                                document.querySelector('#prenomiden').value = "";
        
                            }else
                            { 
                                if (Object.entries(resulteverse).length >= 1) {
                            
                                    for (let key in Object.entries(resulteverse)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${resulteverse[key].nomprenom_perso}`;
                                        opt.innerHTML = `${resulteverse[key].nomprenom_perso}`;
                                        document.querySelector('#prenomiden').add(opt);
                                        
                                    }
                                } else {
                                    document.querySelector('#prenomident').options.length = 1;
                                }
                            }
        
                        };
                        
                        httpInfosverse.setRequestHeader('Content-Type', 'application/json');
                        httpInfosverse.send();
    
                };

        verseverif = function () {
                    
                    var m = parseInt(document.querySelector('#autrversmontan').value);
                    var n = document.querySelector('#autrversmontan').value;
                    var solde = parseInt(document.querySelector('#soldescaiss').value);
                        
                            if (solde < m) 
                            {
        
                                document.querySelector('#autrsmsverse').style.display = 'block';
                                document.querySelector('#autrversementsm').innerHTML = `le montant que vous aviez saisi dépasse le solde de votre caisse`;
                                
                                document.querySelector('#autrversmontan').value = 'VERIFIER SOLDE';
                            } 
                            else{

                                document.querySelector('#autrsmsverse').style.display = 'none';

                                document.querySelector('#autrversmontan').value = n ;
                            }
        };
            
        e.onclick = function () {
        let autr = document.querySelector('#verseForcaisse');
        autr.setAttribute('action', `${APP_ROOT}/Caisses/adverscaisse/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- addtriversement.js --- */
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
;
/* --- addtriclientversement.js --- */
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
;
/* --- addepotfour.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addepotfour').forEach(function (e) 
    {
        document.querySelector('h3#fourTitle').innerHTML = `ENREGISTREMENT DES DEPOTS FOURNISSEUR`;

let typcl = document.querySelector('#client_ident');
        
        if (typcl !== null) 
        typcl.onchange = () => 
        {
            let Infostypinfcl;
            if (window.XMLHttpRequest) {
                Infostypinfcl = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                Infostypinfcl = new ActiveXObject("Microsoft.XMLHTTP");
            }
            document.querySelector('#prenomnomident').options.length = 1;
            var typerecetchoisicl = document.querySelector('#client_ident')
            .options[document.querySelector('#client_ident').options.selectedIndex].value;

            Infostypinfcl.open('GET', window.location.origin + `${APP_ROOT}/depenses/listesnom/${typerecetchoisicl}`, true);
            Infostypinfcl.onload = () => {
                const resulcl = JSON.parse(Infostypinfcl.responseText);

                    if(resulcl == null){
                        document.querySelector('#prenomnomident').value = "";

                    } 
                    if (Object.entries(resulcl).length >= 1) {

                        for (let key in Object.entries(resulcl)) {
                            let opt = document.createElement('option');
                            opt.value = `${resulcl[key].nom_client} ${resulcl[key].prenom_client}`;
                            opt.innerHTML = `${resulcl[key].nom_client} ${resulcl[key].prenom_client}`;
                            document.querySelector('#prenomnomident').add(opt);
                            
                        }
                    } else {
                        document.querySelector('#prenomnomident').options.length = 1;
                    }

                };
                
                Infostypinfcl.setRequestHeader('Content-Type', 'application/json');
                Infostypinfcl.send();

        };
                depverif = function () {
                    
                    var depos = parseInt(document.querySelector('#depotautremontant').value);
                    var depo = document.querySelector('#depotautremontant').value;
                    var solddp = parseInt(document.querySelector('#soldeautrecaisse').value);
                        
                            if (solddp < depos) 
                            {
        
                                document.querySelector('#smsautredepot').style.display = 'block';
                                document.querySelector('#depotautresms').innerHTML = `le montant que vous aviez saisi dépasse le solde de votre caisse`;
                                
                                document.querySelector('#depotautremontant').value = 'VERIFIER SOLDE';
                            } 
                            else{

                                document.querySelector('#smsautredepot').style.display = 'none';

                                document.querySelector('#depotautremontant').value = depo ;
                            }
                    };
            
        e.onclick = function () {
        let frlistedepot = document.querySelector('#frdepot');
            frlistedepot.setAttribute('action', `${APP_ROOT}/Depots/addepotfour/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- adautreupdatedepense.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    verifautredepense = function () {
                    
                    var mt = parseInt(document.querySelector('#autremontantidentif').value);
                    var nt = document.querySelector('#autremontantidentif').value;
                    var soldet = parseInt(document.querySelector('#autresoldecaisse').value);
                        
                            if (soldet < mt) 
                            {
        
                                document.querySelector('#autresmsmt').style.display = 'block';
                                document.querySelector('#smsmontantdep').innerHTML = `le montant que vous aviez saisi dépasse le solde de votre caisse`;
                                
                                document.querySelector('#autremontantidentif').value = 'VERIFIER SOLDE';
                            } 
                            else{

                                document.querySelector('#autresmsmt').style.display = 'none';

                                document.querySelector('#autremontantidentif').value = nt ;
                            }
                };
    
});
;
/* --- recaptrirecette.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.recaptrirecette').forEach(function (e) 
    {
        document.querySelector('h3#recapTitle').innerHTML = `TRI RECETTES`;

        let recapttypinf = document.querySelector('#recaptchoisirtype');
        
        if (recapttypinf !== null) 
        recapttypinf.onchange = () => 
        {
                document.querySelector('#recaptidgenrerecet').options.length = 1;
                document.querySelector('#recaptidnomrecet').options.length = 1;
                    let httpInfostypinfrecapt;
                    if (window.XMLHttpRequest) {
                        httpInfostypinfrecapt = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfostypinfrecapt = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var verificationtypinfrecapt = document.querySelector('#recaptchoisirtype')
                    .options[document.querySelector('#recaptchoisirtype').options.selectedIndex].value;
                    httpInfostypinfrecapt.open('GET', window.location.origin + `${APP_ROOT}/recettes/listegenre/${verificationtypinfrecapt}`, true);
                    httpInfostypinfrecapt.onload = () => {
                        const resulterecapt = JSON.parse(httpInfostypinfrecapt.responseText);
        
                            if(resulterecapt == null){
                                document.querySelector('#recaptidgenrerecet').value = "";
        
                            } 
                            if (Object.entries(resulterecapt).length >= 1) {
                        
                                for (let key in Object.entries(resulterecapt)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resulterecapt[key].type_personnel}`;
                                    opt.innerHTML = `${resulterecapt[key].type_personnel}`;

                                    document.querySelector('#recaptidgenrerecet').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#recaptidgenrerecet').options.length = 1;
                            }
        
                        };
                        
                        httpInfostypinfrecapt.setRequestHeader('Content-Type', 'application/json');
                        httpInfostypinfrecapt.send();
    
                };
            
                let typrecapt = document.querySelector('#recaptidgenrerecet');
        
        if (typrecapt !== null) 
        typrecapt.onchange = () => 
        {
                    let Infostypinfrecapt;
                    if (window.XMLHttpRequest) {
                        Infostypinfrecapt = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        Infostypinfrecapt = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    document.querySelector('#recaptidnomrecet').options.length = 1;
                    var typerecetchoisirecapt = document.querySelector('#recaptchoisirtype')
                    .options[document.querySelector('#recaptchoisirtype').options.selectedIndex].value;
                    var ficationtypinfrecapt = document.querySelector('#recaptidgenrerecet').
                    options[document.querySelector('#recaptidgenrerecet').options.selectedIndex].value;
                    Infostypinfrecapt.open('GET', window.location.origin + `${APP_ROOT}/recettes/listenom/${typerecetchoisirecapt}/${ficationtypinfrecapt}`, true);
                    Infostypinfrecapt.onload = () => {
                        const resulrecapt = JSON.parse(Infostypinfrecapt.responseText);
        
                            if(resulrecapt == null){
                                document.querySelector('#recaptidnomrecet').value = "";
        
                            } 
                            if (Object.entries(resulrecapt).length >= 1) {
                        console.log(ficationtypinfrecapt);
                                for (let key in Object.entries(resulrecapt)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resulrecapt[key].nom}`;
                                    opt.innerHTML = `${resulrecapt[key].nom}`;
                                    document.querySelector('#recaptidnomrecet').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#recaptidnomrecet').options.length = 1;
                            }
        
                        };
                        
                        Infostypinfrecapt.setRequestHeader('Content-Type', 'application/json');
                        Infostypinfrecapt.send();
    
                };
        e.onclick = function () {
        let listerecetterecapt = document.querySelector('#recaptrecetForm');
        listerecetterecapt.setAttribute('action', `${APP_ROOT}/Rapport/recaptrecette/${e.dataset.ckey}`);
        }

    })
});
;
/* --- recaptridepense.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.recaptridepense').forEach(function (e) 
    {
        document.querySelector('h3#recaptdepTitle').innerHTML = `TRI DEPENSES`;

        let gpinfrecapt = document.querySelector('#recaptdtype');
        
        if (gpinfrecapt !== null) 
        gpinfrecapt.onchange = () => 
        {
                document.querySelector('#recaptgtype').options.length = 1;
                document.querySelector('#recaptgnom').options.length = 1;
                    let httpInfostypinforecapt;
                    if (window.XMLHttpRequest) {
                        httpInfostypinforecapt = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfostypinforecapt = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var recaptverificationtypinfo = document.querySelector('#recaptdtype')
                    .options[document.querySelector('#recaptdtype').options.selectedIndex].value;
                    httpInfostypinforecapt.open('GET', window.location.origin + `${APP_ROOT}/depenses/listegenre/${recaptverificationtypinfo}`, true);
                    httpInfostypinforecapt.onload = () => {
                        const resprecapt = JSON.parse(httpInfostypinforecapt.responseText);
        
                            if(resprecapt == null){
                                document.querySelector('#recaptgtype').value = "";
        
                            } 
                            if (Object.entries(resprecapt).length >= 1) {
                        
                                for (let key in Object.entries(resprecapt)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resprecapt[key].type_personnel}`;
                                    opt.innerHTML = `${resprecapt[key].type_personnel}`;
                                    document.querySelector('#recaptgtype').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#recaptgtype').options.length = 1;
                            }
        
                        };
                        
                        httpInfostypinforecapt.setRequestHeader('Content-Type', 'application/json');
                        httpInfostypinforecapt.send();
    
                };
            
                let typorecapt = document.querySelector('#recaptgtype');
        
        if (typorecapt !== null) 
        typorecapt.onchange = () => 
        {
                    let Infostypinforecapt;
                    if (window.XMLHttpRequest) {
                        Infostypinforecapt = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        Infostypinforecapt = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    document.querySelector('#gnom').options.length = 1;
                    var recapttypedepchoisi = document.querySelector('#recaptdtype')
                    .options[document.querySelector('#recaptdtype').options.selectedIndex].value;

                    var recaptficationtypinfo = document.querySelector('#recaptgtype').
                    options[document.querySelector('#recaptgtype').options.selectedIndex].value;
                    Infostypinforecapt.open('GET', window.location.origin + `${APP_ROOT}/depenses/listenom/${recapttypedepchoisi}/${recaptficationtypinfo}`, true);
                    Infostypinforecapt.onload = () => {
                        const resperecapt = JSON.parse(Infostypinforecapt.responseText);
        
                            if(resperecapt == null){
                                document.querySelector('#recaptgnom').value = "";
        
                            } 
                            if (Object.entries(resperecapt).length >= 1) {
                                for (let key in Object.entries(resperecapt)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resperecapt[key].nom_perso}`;
                                    opt.innerHTML = `${resperecapt[key].nom_perso}`;
                                    document.querySelector('#recaptgnom').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#recaptgnom').options.length = 1;
                            }
        
                        };
                        
                        Infostypinforecapt.setRequestHeader('Content-Type', 'application/json');
                        Infostypinforecapt.send();
    
                };
        e.onclick = function () {
        let recaptlistedepense = document.querySelector('#recaptdpForm');
        recaptlistedepense.setAttribute('action', `${APP_ROOT}/Rapport/recaptdepense/${e.dataset.ckey}`);
        }

    })
});
;
/* --- recaptautretridepense.js --- */
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
;
/* --- recaptridepot.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.recaptridepot').forEach(function (e) 
    {
        document.querySelector('h3#recaptTitledepot').innerHTML = `TRI DEPOTS`;

        let gpinfrecapt = document.querySelector('#recapttypedepot');
        
        if (gpinfrecapt !== null) 
        gpinfrecapt.onchange = () => 
        {
                document.querySelector('#recaptgenredepot').options.length = 1;
                document.querySelector('#recaptnomdepot').options.length = 1;
                    let httpInfostypinforecapt;
                    if (window.XMLHttpRequest) {
                        httpInfostypinforecapt = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfostypinforecapt = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var recaptverificationtypinfo = document.querySelector('#recapttypedepot')
                    .options[document.querySelector('#recapttypedepot').options.selectedIndex].value;
                    httpInfostypinforecapt.open('GET', window.location.origin + `${APP_ROOT}/depots/listegenre/${recaptverificationtypinfo}`, true);
                    httpInfostypinforecapt.onload = () => {
                        const resprecapt = JSON.parse(httpInfostypinforecapt.responseText);
        
                            if(resprecapt == null){
                                document.querySelector('#recaptgenredepot').value = "";
        
                            } 
                            if (Object.entries(resprecapt).length >= 1) {
                        
                                for (let key in Object.entries(resprecapt)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resprecapt[key].type_personnel}`;
                                    opt.innerHTML = `${resprecapt[key].type_personnel}`;
                                    document.querySelector('#recaptgenredepot').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#recaptgenredepot').options.length = 1;
                            }
        
                        };
                        
                        httpInfostypinforecapt.setRequestHeader('Content-Type', 'application/json');
                        httpInfostypinforecapt.send();
    
                };
            
                let typorecapt = document.querySelector('#recaptgenredepot');
        
        if (typorecapt !== null) 
        typorecapt.onchange = () => 
        {
                    let Infostypinforecapt;
                    if (window.XMLHttpRequest) {
                        Infostypinforecapt = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        Infostypinforecapt = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    document.querySelector('#recaptnomdepot').options.length = 1;
                    var typedepchoisirecapt = document.querySelector('#recapttypedepot')
                    .options[document.querySelector('#recapttypedepot').options.selectedIndex].value;

                    var ficationtypinforecapt = document.querySelector('#recaptgenredepot').
                    options[document.querySelector('#recaptgenredepot').options.selectedIndex].value;
                    Infostypinforecapt.open('GET', window.location.origin + `${APP_ROOT}/depots/listenom/${typedepchoisirecapt}/${ficationtypinforecapt}`, true);
                    Infostypinforecapt.onload = () => {
                        const resperecapt = JSON.parse(Infostypinforecapt.responseText);
        
                            if(resperecapt == null){
                                document.querySelector('#recaptnomdepot').value = "";
        
                            } 
                            if (Object.entries(resperecapt).length >= 1) {
                                for (let key in Object.entries(respe)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resperecapt[key].nom_pre}`;
                                    opt.innerHTML = `${resperecapt[key].nom_pre}`;
                                    document.querySelector('#recaptnomdepot').add(opt);
                                    
                                }
                            } else {
                                document.querySelector('#recaptnomdepot').options.length = 1;
                            }
        
                        };
                        
                        Infostypinforecapt.setRequestHeader('Content-Type', 'application/json');
                        Infostypinforecapt.send();
    
                };
        e.onclick = function () {
        let recaptlistedepot = document.querySelector('#recaptdepotForm');
        recaptlistedepot.setAttribute('action', `${APP_ROOT}/Rapport/recaptdepot/${e.dataset.ckey}`);
        }

    })
});
;
/* --- recaptriautredepot.js --- */
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
        recaptlisteautredepot.setAttribute('action', `${APP_ROOT}/Rapport/recaptautredepot/${e.dataset.ckey}`);
        }

    })
});
;
/* --- adreportjs.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adreportjs').forEach(function (e) 
    {
        document.querySelector('h3#Titlerep').innerHTML = `EXERCICE MENSUEL TICKET GUICHETIER`;

        let infgar = document.querySelector('#departgaridentif');
        
        if (infgar !== null) 
        infgar.onchange = () => {
            let httpInfosgar;
            if (window.XMLHttpRequest) {
                httpInfosgar = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgar = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idcaissiers').options.length = 1;

                    var verificatgar = document.querySelector('#departgaridentif').value;
                    
                    httpInfosgar.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgar}`, true);
                    httpInfosgar.onload = () => {
                        const infosgar = JSON.parse(httpInfosgar.responseText);
                        
                        if (Object.entries(infosgar).length > 0) {                            
                        
                                for (let key in Object.entries(infosgar)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgar[key].roleattribut}`;
                                    opt.innerHTML = `${infosgar[key].username}`;
                                    document.querySelector('#idcaissiers').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idcaissiers').options.length = 1;
                        }
                        
                    };
                    httpInfosgar.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgar.send();
                };
        e.onclick = function () {
        let tickForm = document.querySelector('#tickForm');
            tickForm.setAttribute('action', `${APP_ROOT}/Rapport/exoreports/${e.dataset.ekey}/${e.dataset.idgares}`);
        }

    })
});
;
/* --- adreportversjs.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adreportversjs').forEach(function (e) 
    {
        document.querySelector('h3#Titlerepvers').innerHTML = `TRI REPORT DES RECETTES`;

        let infgarvers = document.querySelector('#departgaridentifvers');
        
        if (infgarvers !== null) 
        infgarvers.onchange = () => {
            let httpInfosgarvers;
            if (window.XMLHttpRequest) {
                httpInfosgarvers = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgarvers = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idcaissiersvers').options.length = 1;

                    var verificatgarvers = document.querySelector('#departgaridentifvers').value;
                    
                    httpInfosgarvers.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgarvers}`, true);
                    httpInfosgarvers.onload = () => {
                        const infosgarvers = JSON.parse(httpInfosgarvers.responseText);
                        
                        if (Object.entries(infosgarvers).length > 0) {                            
                        
                                for (let key in Object.entries(infosgarvers)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgarvers[key].roleattribut}`;
                                    opt.innerHTML = `${infosgarvers[key].username}`;
                                    document.querySelector('#idcaissiersvers').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idcaissiersvers').options.length = 1;
                        }
                        
                    };
                    httpInfosgarvers.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgarvers.send();
                };
        e.onclick = function () {
        let tickversForm = document.querySelector('#tickversForm');
            tickversForm.setAttribute('action', `${APP_ROOT}/Rapport/exoreportsvers/${e.dataset.ekey}/${e.dataset.idgares}`);
        }

    })
});
;
/* --- advers.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.advers').forEach(function (e) 
    {
        document.querySelector('h3#caiTitle').innerHTML = `TRI DES ETATS DE VERSEMENT PAR AXE`;

        let infgares = document.querySelector('#encaisgar');
        
        if (infgares !== null) 
        infgares.onchange = () => {
            let httpInfoss;
            if (window.XMLHttpRequest) {
                httpInfoss = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfoss = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeuse').options.length = 1;

                    var verificatgares = document.querySelector('#encaisgar').value;
                    
                    httpInfoss.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trioperateur/${verificatgares}`, true);
                    httpInfoss.onload = () => {
                        const infoss = JSON.parse(httpInfoss.responseText);
                        
                        if (Object.entries(infoss).length > 0) {                            
                        
                                for (let key in Object.entries(infoss)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infoss[key].roleattribut}`;
                                    opt.innerHTML = `${infoss[key].username}`;
                                    document.querySelector('#idvendeuse').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idvendeuse').options.length = 1;
                        }
                        
                    };
                    httpInfoss.setRequestHeader('Content-Type', 'application/json');
                    httpInfoss.send();
                };
        e.onclick = function () {
        let encaisForms = document.querySelector('#encaisForms');
            encaisForms.setAttribute('action', `${APP_ROOT}/Rapport/triencaissement/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
