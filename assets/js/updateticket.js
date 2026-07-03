document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.updateticket').forEach(function (e) {
        
        e.onclick = function () {
            let mtaForm = document.querySelector('#mtaForm');
            mtaForm.setAttribute('action', `${APP_ROOT}/Programmes/update/${e.dataset.cle_compagnie}/${e.dataset.id_client}/${e.dataset.tamponcod}/${e.dataset.cdligneh}`);
            document.querySelector('h3#mtaTitle').innerHTML = `MODIFICATION SUR LE TICKET DE : ${e.dataset.nom}`;
            $('#uclient_contact').val(`${e.dataset.contact}`);
            $('#uclient').val(`${e.dataset.nom}`);
            $('#uprnclient').val(`${e.dataset.prenom}`);
            $('#ucnib').val(`${e.dataset.cni}`);
            $('#udate_cnib').val(`${e.dataset.cnideliver}`);
            $('#ulieudelivre').val(`${e.dataset.cnideliverzone}`);

        }
    })
});