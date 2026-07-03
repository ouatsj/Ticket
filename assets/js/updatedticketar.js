document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.updatedticketar').forEach(function (e) {
        
        e.onclick = function () {
            let mtdForm = document.querySelector('#mtarForm');
            mtdForm.setAttribute('action', `${APP_ROOT}/Programmes/modifier/${e.dataset.cle_compagnie}/${e.dataset.id_client}/${e.dataset.tamponcod}/${e.dataset.passagecod}/${e.dataset.cdligneh}`);
            document.querySelector('h3#mdtarTitle').innerHTML = `MODIFICATION SUR LE TICKET DE : ${e.dataset.nom}`;
            $('#aruclient_contact').val(`${e.dataset.contact}`);
            $('#aruclient').val(`${e.dataset.nom}`);
            $('#aruprnclient').val(`${e.dataset.prenom}`);
            $('#arucnib').val(`${e.dataset.cni}`);
            $('#arudate_cnib').val(`${e.dataset.cnideliver}`);
            $('#arulieudelivre').val(`${e.dataset.cnideliverzone}`);
            $('#arpasieges').val(`${e.dataset.num}`);
            $('#artypclt').val(`${e.dataset.type}`);
            $('#arh_depart').val(`${e.dataset.hsieg}`);
            $('#ardatdepheure').val(`${e.dataset.prdate}`);
            $('#arcatego').val(`${e.dataset.cat}`);
        }
    })
});