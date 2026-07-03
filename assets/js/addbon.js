document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addbon').forEach(function (e) {
        document.querySelector('h3#bonTitle').innerHTML = `ENREGISTREMENT BON MILITAIRE`;

            //recherche d'information du client
        let idcontact = document.querySelector('#idcontactbon');
        if (idcontact !== null)
        idcontact.onkeyup = () => {
                let httpInfosrequestbon;
                if (window.XMLHttpRequest) {
                    httpInfosrequestbon = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfosrequestbon = new ActiveXObject("Microsoft.XMLHTTP");
                }
                var verifictbon = document.querySelector('#idcontactbon').value;
                httpInfosrequestbon.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifinfos/${verifictbon}`, true);
                httpInfosrequestbon.onload = () => {
                    const infosreqbon = JSON.parse(httpInfosrequestbon.responseText);
                    if (infosreqbon == null) {
                        document.querySelector('#idnombon').value = "";
                                document.querySelector('#idprenombon').value = "";
                                document.querySelector('#bon').value = "";
                                document.querySelector('#date_carte').value = "";
                                document.querySelector('#lieudelivre_cart').value = "";
                                document.querySelector('#clientbonid').value = "";
                    } else {
                        if (Object.entries(infosreqbon).length > 1) {
                            
                            if (infosreqbon.contact_client == verifictbon) {
                                document.querySelector('#idnombon').value = `${infosreqbon.nom_client}`;
                                document.querySelector('#idprenombon').value = `${infosreqbon.prenom_client}`;
                                document.querySelector('#bon').value = `${infosreqbon.num_CNIB}`;
                                document.querySelector('#date_carte').value = `${infosreqbon.date_delivre}`;
                                document.querySelector('#lieudelivre_cart').value = `${infosreqbon.lieu_delivre}`;
                                document.querySelector('#clientbonid').value = `${infosreqbon.id_client}`;

                                document.querySelector('#pasnompbon').value = `${infosreqbon.nom_client}`;
                                document.querySelector('#pasprenompbon').value = `${infosreqbon.prenom_client}`;
                                document.querySelector('#pascnibpbon').value = `${infosreqbon.num_CNIB}`;
                                document.querySelector('#pasdatepbon').value = `${infosreqbon.date_delivre}`;
                                document.querySelector('#lieubon').value = `${infosreqbon.lieu_delivre}`;
                            } else {
                                document.querySelector('#idnombon').value = "";
                                document.querySelector('#idprenombon').value = "";
                                document.querySelector('#bon').value = "";
                                document.querySelector('#date_carte').value = "";
                                document.querySelector('#lieudelivre_cart').value = "";
                                document.querySelector('#clientbonid').value = "";
                            }
                        }
                    }
                };
                httpInfosrequestbon.setRequestHeader('Content-Type', 'application/json');
                httpInfosrequestbon.send();
            };
        e.onclick = function () {
            let bonForm = document.querySelector('#bonForm');
            bonForm.setAttribute('action', `${APP_ROOT}/Bon_Millitaire/addbon/${e.dataset.cle_compagnie}`);
        }
    })
});