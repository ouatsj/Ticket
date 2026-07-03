document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adreceptperso').forEach(function (e) {
        document.querySelector('h3#reTitleperso').innerHTML = `RECEPTION PERSONNEL`;

        let infosreceptperso = document.querySelector('#confirmer_infocodeperso');
        if (infosreceptperso !== null)
            infosreceptperso.onclick = () => {
                let httpRequestRecepperso;
                
                if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                    httpRequestRecepperso = new XMLHttpRequest();
                } else if (window.ActiveXObject) { // IE 6 and older
                    httpRequestRecepperso = new ActiveXObject("Microsoft.XMLHTTP");
                }
                
                var cdcourperso = document.querySelector("#codecourrierperso").value;
                var gdarperso = document.querySelector("#gdidarperso").value;
                var sgdarperso = document.querySelector("#sgdiarperso").value;
                httpRequestRecepperso.open('GET', window.location.origin + `${APP_ROOT}/courrier/verifcodecourrierperso/${cdcourperso}/${gdarperso}/${sgdarperso}`, true);
                httpRequestRecepperso.onload = () => {
                    const donneesperso = JSON.parse(httpRequestRecepperso.responseText);
                    if (donneesperso == null) {
                        
                        document.querySelector('#smscrperso').style.display = 'block';
                        document.querySelector('#erreurSmscourperso').innerHTML = `Veuillez vérifier le code saisi, ou ce courrier n'est pas encore arrivé.`;
                        document.querySelector('#nomexptperso').innerHTML = ``;
                        document.querySelector('#contactexptperso').innerHTML = ``;
                        document.querySelector('#nomreceptperso').innerHTML = ``;
                        document.querySelector('#contactreceptperso').innerHTML = ``;
                        document.querySelector('#refcourrperso').innerHTML = ``;
                        document.querySelector('#directioncourperso').innerHTML = ``;
                        document.querySelector('#codecouperso').innerHTML = ``;
                        document.querySelector('#heurecourperso').innerHTML = ``;


                    } else 
                    {
                               
                        if (Object.entries(donneesperso).length >= 1){
                                document.querySelector('#smscrperso').style.display = 'none';
                                document.querySelector('#refcourrperso').innerHTML = `LIBELLE : ${donneesperso.nombrecolis} ${donneesperso.naturecoli} ${donneesperso.naturecourrier}`;
                                document.querySelector('#heurecourperso').innerHTML = `LIGNE: ${donneesperso.nom_ligne} DATE : ${donneesperso.date_progr} HEURE: ${donneesperso.heure}`;
                                document.querySelector('#iddatevalidperso').innerHTML = `DATE DE VALIDATION: ${donneesperso.datevalider}`;
                                document.querySelector('#codecouperso').innerHTML = `REFERENCE: ${donneesperso.courrierexpid}`;
                                document.querySelector('#destidentperso').value = `${donneesperso.receptid}`;
                                document.querySelector('#destclientperso').value = `${donneesperso.persorecep}`;
                                document.querySelector('#perdestclientperso').value = `${donneesperso.persorecep}`;
                                document.querySelector('#destnomperso').value = `${donneesperso.nomprenom_perso}`;
                                document.querySelector('#contdestperso').value = `${donneesperso.contact_perso}`;
                        } else {
                            
                        }
                    }       
                };
                httpRequestRecepperso.setRequestHeader('Content-Type', 'application/json');
                httpRequestRecepperso.send();
            };
                    

        e.onclick = function () {
            let recepFormperso = document.querySelector('#receptFormperso');
            recepFormperso.setAttribute('action', `${APP_ROOT}/Confirmation/updatedreceptperso/${e.dataset.cle_compagnie}`);
        }
    })
});