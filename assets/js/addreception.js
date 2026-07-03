document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addreception').forEach(function (e) {
        document.querySelector('h3#reTitle').innerHTML = `RECEPTION`;

        let infosrecept = document.querySelector('#confirmer_infocode');
        if (infosrecept !== null)
            infosrecept.onclick = () => {
                let httpRequestRecep;
                
                if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                    httpRequestRecep = new XMLHttpRequest();
                } else if (window.ActiveXObject) { // IE 6 and older
                    httpRequestRecep = new ActiveXObject("Microsoft.XMLHTTP");
                }
                
                var cdcour = document.querySelector("#codecourrier").value;
                var gdar = document.querySelector("#gdidar").value;
                var sgdar = document.querySelector("#sgdiar").value;
                httpRequestRecep.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifcodecourrier/${cdcour}/${gdar}/${sgdar}`, true);
                httpRequestRecep.onload = () => {
                    const donnees = JSON.parse(httpRequestRecep.responseText);
                    if (donnees == null) {
                        
                        document.querySelector('#smscr').style.display = 'block';
                        document.querySelector('#erreurSmscour').innerHTML = `Veuillez vérifier le code saisi, ou ce courrier n'est pas encore arrivé.`;
                        document.querySelector('#nomexpt').innerHTML = ``;
                        document.querySelector('#prenomexpt').innerHTML = ``;
                        document.querySelector('#contactexpt').innerHTML = ``;
                        document.querySelector('#nomrecept').innerHTML = ``;
                        document.querySelector('#prenomrecept').innerHTML = ``;
                        document.querySelector('#contactrecept').innerHTML = ``;
                        document.querySelector('#refcourr').innerHTML = ``;
                        document.querySelector('#directioncour').innerHTML = ``;
                        document.querySelector('#codecou').innerHTML = ``;
                        document.querySelector('#heurecour').innerHTML = ``;
                        document.querySelector('#receptidentifedclidct').value = ``;
                        document.querySelector('#receptidentifedclidcttype').value = ``;

                    } else 
                    {
                               
                        if (Object.entries(donnees).length >= 1){
                                document.querySelector('#smscr').style.display = 'none';
                                document.querySelector('#refcourr').innerHTML = `LIBELLE : ${donnees.nombrecolis} ${donnees.naturecoli} ${donnees.naturecourrier}`;
                                document.querySelector('#heurecour').innerHTML = `LIGNE: ${donnees.nom_ligne} DATE : ${donnees.date_progr} HEURE: ${donnees.heure}`;
                                document.querySelector('#iddatevalid').innerHTML = `DATE DE VALIDATION: ${donnees.datevalider}`;
                                document.querySelector('#codecou').innerHTML = `REFERENCE: ${donnees.courrierexpid}`;
                                document.querySelector('#destident').value = `${donnees.receptid}`;
                                document.querySelector('#destclient').value = `${donnees.client_recept}`;
                                document.querySelector('#perdestclient').value = `${donnees.persorecep}`;
                                document.querySelector('#destnom').value = `${donnees.nom_client}`;
                                document.querySelector('#destprenom').value = `${donnees.prenom_client}`;
                                document.querySelector('#contdest').value = `${donnees.contact_client}`;
                                document.querySelector('#cnibdest').value = `${donnees.num_CNIB}`;
                                document.querySelector('#delivredest').value = `${donnees.date_delivre}`;
                                document.querySelector('#lieudest').value = `${donnees.lieu_delivre}`;
                                document.querySelector('#receptidentifedclidct').value = `${donnees.contact_client}`;
                                document.querySelector('#receptidentifedclidcttype').value = `${donnees.type_client}`;

                        } else {
                            
                        }
                    }       
                };
                httpRequestRecep.setRequestHeader('Content-Type', 'application/json');
                httpRequestRecep.send();
            };
            
           let inforp1 = document.querySelector('#contdest');
        if (inforp1 !== null)
            inforp1.onkeyup = () => {
                let httpInfosmd3;
                if (window.XMLHttpRequest) {
                    httpInfosmd3 = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfosmd3 = new ActiveXObject("Microsoft.XMLHTTP");
                }
                var verificatp3 = document.querySelector('#contdest').value;
                
                httpInfosmd3.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifinfos/${verificatp3}`, true);
                httpInfosmd3.onload = () => {
                    const infosp3 = JSON.parse(httpInfosmd3.responseText);
                    if (infosp3 == null) {

                                document.querySelector('#destnom').value = "";
                                document.querySelector('#destprenom').value = "";
                                document.querySelector('#cnibdest').value = "";
                                document.querySelector('#delivredest').value = "";
                                document.querySelector('#lieudest').value = "";
                                document.querySelector('#receptidentifedclid').value = "";
                    } else {
                        if (Object.entries(infosp3).length > 1) {
                            
                            if (infosp3.contact_client == verificatp3) {
                                document.querySelector('#destnom').value = `${infosp3.nom_client}`;
                                document.querySelector('#destprenom').value = `${infosp3.prenom_client}`;
                                document.querySelector('#cnibdest').value = `${infosp3.num_CNIB}`;
                                document.querySelector('#delivredest').value = `${infosp3.date_delivre}`;
                                document.querySelector('#lieudest').value = `${infosp3.lieu_delivre}`;
                                document.querySelector('#receptidentifedclid').value = `${infosp3.id_client}`;
                                
                            } else {
                                document.querySelector('#destnom').value = "";
                                document.querySelector('#destprenom').value = "";
                                document.querySelector('#cnibdest').value = "";
                                document.querySelector('#delivredest').value = "";
                                document.querySelector('#lieudest').value = "";
                                document.querySelector('#receptidentifedclid').value = "";
                            }
                        }
                    }
                };
                httpInfosmd3.setRequestHeader('Content-Type', 'application/json');
                httpInfosmd3.send();
            };
        e.onclick = function () {
            let recepForm = document.querySelector('#receptForm');
            recepForm.setAttribute('action', `${APP_ROOT}/Confirmation/updatedrecept/${e.dataset.cle_compagnie}`);
        }
    })
});