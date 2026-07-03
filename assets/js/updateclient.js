document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.updateclient').forEach(function (e) {
        
        e.onclick = function () {
            let mtaFormp = document.querySelector('#mtaFormp');
            mtaFormp.setAttribute('action', `${APP_ROOT}/Programmes/updatep/${e.dataset.cle_compagnie}/${e.dataset.tamponcodp}/${e.dataset.cdlignehp}/${e.dataset.ticketcodp}/${e.dataset.ticketcodnp}`);
            document.querySelector('h3#mtaTitlep').innerHTML = `MODIFICATION DU CLIENT : ${e.dataset.nomp}`;
            $('#uclient_contactp').val(`${e.dataset.contactp}`);
            $('#uclientp').val(`${e.dataset.nomp}`);
            $('#uprnclientp').val(`${e.dataset.prenomp}`);
            $('#ucnibp').val(`${e.dataset.cnip}`);
            $('#udate_cnibp').val(`${e.dataset.cnideliverp}`);
            $('#ulieudelivrep').val(`${e.dataset.cnideliverzonep}`);

        }

        //recherche d'information du client depart principal
        let infp = document.querySelector('#uclient_contactp');
        if (infp !== null)
            infp.onkeyup = () => {
                let httpInfosmd;
                if (window.XMLHttpRequest) {
                    httpInfosmd = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfosmd = new ActiveXObject("Microsoft.XMLHTTP");
                }
                var verificatp = document.querySelector('#uclient_contactp').value;
                
                httpInfosmd.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifinfos/${verificatp}`, true);
                httpInfosmd.onload = () => {
                    const infosp = JSON.parse(httpInfosmd.responseText);
                    if (infosp == null) {
                        document.querySelector('#uclientp').value = "";
                                document.querySelector('#uprnclientp').value = "";
                                document.querySelector('#ucnibp').value = "";
                                document.querySelector('#udate_cnibp').value = "";
                                document.querySelector('#ulieudelivrep').value = "";
                                document.querySelector('#identifyclientid').value = "";
                                document.querySelector('#identifycontactid').value = "";
                    } else {
                        if (Object.entries(infosp).length > 1) {
                            
                            if (infosp.contact_client == verificatp) {
                                document.querySelector('#uclientp').value = `${infosp.nom_client}`;
                                document.querySelector('#uprnclientp').value = `${infosp.prenom_client}`;
                                document.querySelector('#ucnibp').value = `${infosp.num_CNIB}`;
                                document.querySelector('#udate_cnibp').value = `${infosp.date_delivre}`;
                                document.querySelector('#ulieudelivrep').value = `${infosp.lieu_delivre}`;
                                document.querySelector('#identifyclientid').value = `${infosp.id_client}`;
                                document.querySelector('#identifycontactid').value = `${infosp.contact_client}`;
                            } else {
                                document.querySelector('#uclientp').value = "";
                                document.querySelector('#uprnclientp').value = "";
                                document.querySelector('#ucnibp').value = "";
                                document.querySelector('#udate_cnibp').value = "";
                                document.querySelector('#ulieudelivrep').value = "";
                                document.querySelector('#identifyclientid').value = "";
                                document.querySelector('#identifycontactid').value = "";
                            }
                        }
                    }
                };
                httpInfosmd.setRequestHeader('Content-Type', 'application/json');
                httpInfosmd.send();
            };
    })
});