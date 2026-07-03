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