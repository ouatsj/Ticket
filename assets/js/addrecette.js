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