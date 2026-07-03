document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.updateticketar').forEach(function (e) {
        
        e.onclick = function () {
            let mtarForm = document.querySelector('#mtarForm');
            mtarForm.setAttribute('action', `${APP_ROOT}/Programmes/updatear/${e.dataset.cle_compagnie}/${e.dataset.id_client}/${e.dataset.tamponcod}/${e.dataset.nonpassagecod}/${e.dataset.cdligneh}`);
            document.querySelector('h3#mtarTitle').innerHTML = `MODIFICATION SUR LE TICKET DE : ${e.dataset.nom}`;
            $('#uclient_contactar').val(`${e.dataset.contact}`);
            $('#uclientar').val(`${e.dataset.nom}`);
            $('#uprnclientar').val(`${e.dataset.prenom}`);
            $('#ucnibar').val(`${e.dataset.cni}`);
            $('#udate_cnibar').val(`${e.dataset.cnideliver}`);
            $('#ulieudelivrear').val(`${e.dataset.cnideliverzone}`);
            $('#tyclientar').val(`${e.dataset.type}`);

        }
    })
});