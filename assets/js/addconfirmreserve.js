document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addconfirmreserve').forEach(function (e) {
        e.onclick = function () 
        {
                let confForm = document.querySelector('#confForm');
            
                confForm.setAttribute('action', `${APP_ROOT}/Reserves/valideconfirm/${e.dataset.cle_compagnie}/${e.dataset.id_client}/${e.dataset.code_pass}/${e.dataset.gareident}/${e.dataset.code_p}/${e.dataset.cdlignh}/${e.dataset.tfb}`);
            
                document.querySelector('h3#reconfTitle').innerHTML = `CONFIRMER RESERVATION AVEC TICKET ${e.dataset.rnom}`;

                $('#ridcontact').val(`${e.dataset.contac}`);
                $('#ridnom').val(`${e.dataset.rnom}`);
                $('#ridprenom').val(`${e.dataset.pren}`);
                $('#ridcontact').val(`${e.dataset.contac}`);
                $('#numsieg').val(`${e.dataset.numsie}`);
                $('#lges').val(`${e.dataset.lge}`);
                $('#nomlg').val(`${e.dataset.nomlge}`);
                $('#catbuslg').val(`${e.dataset.catbuslge}`);
                $('#idcnibcf').val(`${e.dataset.num_CNIB}`);
                $('#dateidcf').val(`${e.dataset.date_delivre}`);
                $('#lieucf').val(`${e.dataset.lieu_delivre}`);
                let cfcod = document.querySelector('#confirme_infos');
                if (cfcod !== null)
                cfcod.onclick = () => {
                    
                    //verification code de confirmation
                    let confRequest;
                    
                    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                        confRequest = new XMLHttpRequest();
                    } else if (window.ActiveXObject) { // IE 6 and older
                        confRequest = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                    var cfconfir = document.querySelector("#confirmcode").value;

                    confRequest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verificationcode/${cfconfir}`, true);
                    confRequest.onload = () => {
                        const cfdata = JSON.parse(confRequest.responseText);
                        if (cfdata == null) {
                                document.querySelector('#boutonsubmit').style.display = 'block';
                                document.querySelector('#messageconf').style.display = 'none';
                                document.querySelector('#epsonsubmit').style.display = 'block';
                                

                        } else {
                            if (Object.entries(cfdata).length > 1) {
                                
                                document.querySelector('#messageconf').style.display = 'block';
                                document.querySelector('#erreurMessageconf').innerHTML = `Cet ticket ne peut pas être confirmé.`;
                                document.querySelector('#boutonsubmit').style.display = 'none';
                                document.querySelector('#epsonsubmit').style.display = 'none';
                                
                            }
                            
                        }
                    };
                    confRequest.setRequestHeader('Content-Type', 'application/json');
                    confRequest.send();
                };

        }
    })
});