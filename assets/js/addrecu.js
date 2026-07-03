document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addrecu').forEach(function (e) {
        document.querySelector('h3#recuTitle').innerHTML = `FAIRE RECU`;

        let infosrecu = document.querySelector('#recu_infos');
        if (infosrecu !== null)
            infosrecu.onclick = () => {
                //verification code du ticket
                let httpRequestRecu;
                
                if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                    httpRequestRecu = new XMLHttpRequest();
                } else if (window.ActiveXObject) { // IE 6 and older
                    httpRequestRecu = new ActiveXObject("Microsoft.XMLHTTP");
                }
                
                
                var codrecu = document.querySelector("#codeclientprecu").value;
                var gdrecu = document.querySelector("#gareconnectrecu").value;
                httpRequestRecu.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/verifcoderecu/${gdrecu}/${codrecu}`, true);
                httpRequestRecu.onload = () => {
                    const donneesrecu = JSON.parse(httpRequestRecu.responseText);
                    if (donneesrecu == null) {
                        
                        document.querySelector('#validrecu').style.display = 'none';
                        document.querySelector('#billetrecu').style.display = 'block';
                        document.querySelector('#billetSmsrecu').innerHTML = `Ce client a déjà pris son reçu merci!`;
                        document.querySelector('#nomclprecu').innerHTML = ``;
                        document.querySelector('#prenomclprecu').innerHTML = ``;
                        document.querySelector('#contactclprecu').innerHTML = ``;
                        document.querySelector('#refclprecu').innerHTML = ``;
                        document.querySelector('#directionclprecu').innerHTML = ``;
                        document.querySelector('#codeclprecu').innerHTML = ``;
                        document.querySelector('#heureclprecu').innerHTML = ``;
                        document.querySelector('#passerprecu').value = '';
                        document.querySelector('#idclpasseridrecu').value = '';
                        document.querySelector('#client_idprecu').value = '';
                        document.querySelector('#pasnomprecu').value = '';
                        document.querySelector('#pasprenomprecu').value = '';
                        document.querySelector('#pascontactprecu').value = '';
                        document.querySelector('#pascnibprecu').value = '';
                        document.querySelector('#pasdateprecu').value = '';
                        document.querySelector('#delivrelierecu').value = '';
                        document.querySelector('#codetamponrecus').value = '';
                        document.querySelector('#passaxeprecu').value = '';
                        document.querySelector('#prixrecu').value = '';
                        document.querySelector('#codenonprecu').value = '';
                                    

                    } else 
                    {
                               
                            if (Object.entries(donneesrecu).length >= 1){
                                    document.querySelector('#billetrecu').style.display = 'none';
                                    document.querySelector('#billetSmsrecu').style.display = 'block';
                                    document.querySelector('#validrecu').style.display = 'block';
                                    document.querySelector('#nomclprecu').innerHTML = `NOM: ${donneesrecu.nom_client}`;
                                    document.querySelector('#prenomclprecu').innerHTML = `PRENOM: ${donneesrecu.prenom_client}`;
                                    document.querySelector('#contactclprecu').innerHTML = `CONTACT: ${donneesrecu.contact_client}`;
                                    document.querySelector('#refclprecu').innerHTML = `REFERENCE CNIB: ${donneesrecu.num_CNIB}`;
                                    document.querySelector('#directionclprecu').innerHTML = `AXE: ${donneesrecu.nom_ligne}`;
                                    document.querySelector('#codeclprecu').innerHTML = `CODE TICKET: ${donneesrecu.code_passager}`;
                                    document.querySelector('#heureclprecu').innerHTML = `HEURE: ${donneesrecu.heure} SIEGE :${donneesrecu.num_siege_categorie}`;
                                    document.querySelector('#passerprecu').value = `${donneesrecu.code_ticket}`;
                                    document.querySelector('#codenonprecu').value = `${donneesrecu.codeticket}`;
                                    document.querySelector('#idclpasseridrecu').value = `${donneesrecu.ligne_id}`;
                                    document.querySelector('#client_idprecu').value = `${donneesrecu.id_client_pass}`;
                                    document.querySelector('#pasnomprecu').value = `${donneesrecu.nom_client}`;
                                    document.querySelector('#pasprenomprecu').value = `${donneesrecu.prenom_client}`;
                                    document.querySelector('#pascontactprecu').value = `${donneesrecu.contact_client}`;
                                    document.querySelector('#pascnibprecu').value = `${donneesrecu.num_CNIB}`;
                                    document.querySelector('#pasdateprecu').value = `${donneesrecu.date_delivre}`;
                                    document.querySelector('#delivrelierecu').value = `${donneesrecu.lieu_delivre}`;
                                    document.querySelector('#codetamponrecus').value = `${donneesrecu.tamponcod}`;
                                    document.querySelector('#passaxeprecu').value = `${donneesrecu.nom_ligne}`;
                                    document.querySelector('#prixrecu').value = `${donneesrecu.prixvente}`;
                                    

                            } 
                            
                    }
                };
                httpRequestRecu.setRequestHeader('Content-Type', 'application/json');
                httpRequestRecu.send();
            };
        
            
        e.onclick = function () {
            let recuForm = document.querySelector('#recuForm');
            recuForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/recuclient/${e.dataset.cle_compagnie}`);
        }
    })
});