document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addperso').forEach(function (e) 
    {
        document.querySelector('h3#persoTitle').innerHTML = `ENREGISTREMENT DES PERSONNELS`;

       
        let persoinf = document.querySelector('#idpersonnel');
        
        if (persoinf !== null) 
            persoinf.onchange = () => 
            {   
                    
                    document.querySelector('#matperso').style.display = 'none';
                    document.querySelector('#idmatperso').style.display = 'none';
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
                    document.querySelector('#nomclientid').style.display = 'none';
                    document.querySelector('#idnomclient').style.display = 'none';
                    document.querySelector('#idpren').style.display = 'none';
                    document.querySelector('#prenid').style.display = 'none';
                    document.querySelector('#lieucl').style.display = 'none';
                    document.querySelector('#cl_lieu').style.display = 'none';
					document.querySelector('#idtypper').style.display = 'none';
                    document.querySelector('#persid').style.display = 'none';
                    document.querySelector('#tel_num').style.display = 'none';
                    document.querySelector('#num_tel').style.display = 'none';

                    var infoperso = document.querySelector('#idpersonnel')
                    .options[document.querySelector('#idpersonnel').options.selectedIndex].value;

                    if(infoperso === 'perso'){
                        document.querySelector('#matperso').style.display = 'block';
                        document.querySelector('#idmatperso').style.display = 'block';
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
                        document.querySelector('#cnibid').style.display = 'block';
                        document.querySelector('#idcnidel').style.display = 'block';
                        document.querySelector('#cnibidle').style.display = 'block';
                        document.querySelector('#idexpir').style.display = 'block';
                        document.querySelector('#expirid').style.display = 'block';
						document.querySelector('#idtypper').style.display = 'block';
						document.querySelector('#persid').style.display = 'block';
                        document.querySelector('#nomclientid').style.display = 'none';
                        document.querySelector('#idnomclient').style.display = 'none';
                        document.querySelector('#idpren').style.display = 'none';
                        document.querySelector('#prenid').style.display = 'none';
                        document.querySelector('#tel_num').style.display = 'none';
                        document.querySelector('#num_tel').style.display = 'none';
                        document.querySelector('#lieucl').style.display = 'none';
                        document.querySelector('#cl_lieu').style.display = 'none';
                    }
                    //client 
                    if(infoperso === 'client'){
                            document.querySelector('#tel_num').style.display = 'block';
                            document.querySelector('#num_tel').style.display = 'block';
                            document.querySelector('#nomclientid').style.display = 'block';
                            document.querySelector('#idnomclient').style.display = 'block';
                            document.querySelector('#idpren').style.display = 'block';
                            document.querySelector('#prenid').style.display = 'block';
                            document.querySelector('#idnom').style.display = 'none';
                            document.querySelector('#idprenom').style.display = 'none';
                            document.querySelector('#lieucl').style.display = 'block';
                            document.querySelector('#cl_lieu').style.display = 'block';
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
                            document.querySelector('#matperso').style.display = 'none';
                            document.querySelector('#idmatperso').style.display = 'none';
							document.querySelector('#idtypper').style.display = 'none';
							document.querySelector('#persid').style.display = 'none';
                            
                    }
                    

                    if(infoperso === 'autrepersonnel'){
                            document.querySelector('#tel_num').style.display = 'block';
                            document.querySelector('#num_tel').style.display = 'block';
                            document.querySelector('#nomclientid').style.display = 'block';
                            document.querySelector('#idnomclient').style.display = 'block';
                            document.querySelector('#idpren').style.display = 'block';
                            document.querySelector('#prenid').style.display = 'block';
                            document.querySelector('#lieucl').style.display = 'block';
                            document.querySelector('#cl_lieu').style.display = 'block';
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
                            document.querySelector('#matperso').style.display = 'none';
                            document.querySelector('#idmatperso').style.display = 'none';
							document.querySelector('#idtypper').style.display = 'none';
							document.querySelector('#persid').style.display = 'none';
                            
                    }
            };
        e.onclick = function () {
        let listeperso = document.querySelector('#persoForm');
        listeperso.setAttribute('action', `${APP_ROOT}/Personnels/add/${e.dataset.cle_compagnie}`);
        }

    })
});