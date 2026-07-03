document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adbagescale').forEach(function (e) 
    {
        
        let baginfos = document.querySelector('#infocodeticketesc');
        if (baginfos !== null)
            baginfos.onclick = () => {


            let httpRequestBag;
            
            if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                httpRequestBag = new XMLHttpRequest();
            } else if (window.ActiveXObject) { // IE 6 and older
                httpRequestBag = new ActiveXObject("Microsoft.XMLHTTP");
            }
           
            
            var bagcocl = document.querySelector("#codeticketbagesc").value;
            var baggid = document.querySelector("#codebaggidesc").value;
            var bagsgid = document.querySelector("#codebagsousgidesc").value;
            httpRequestBag.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/codeclientverifesc/${bagcocl}/${baggid}/${bagsgid}`, true);
            httpRequestBag.onload = () => {

                const donneesbag = JSON.parse(httpRequestBag.responseText);
                
                if (donneesbag == null) {
                    
                        document.querySelector('#pascontactbagsansescbg').value = '';
                        document.querySelector('#rclientcpescalbag').value = '';
                        document.querySelector('#nclientcpescalbag').value = '';
                        document.querySelector('#prnclientcpescalbag').value = '';
                        document.querySelector('#id_lgeheurescalbag').value = '';
                        document.querySelector('#codtickbagsansesc').value = '';
                        document.querySelector('#idcompagaescbag').value = '';
                        document.querySelector('#lignescalbag').value = '';
                        document.querySelector('#quartpasseesc').value = '';
                        document.querySelector('#infobagasansesc').value = '';
                } else
                {

                
                    if (Object.entries(donneesbag).length >= 1){

                    rclientcpescalbag
                        document.querySelector('#pascontactbagsansescbg').value = `${donneesbag.contact_client}`;
                        document.querySelector('#rclientcpescalbag').value = `${donneesbag.clientescal}`;
                        document.querySelector('#nclientcpescalbag').value = `${donneesbag.nom_client}`;
                        document.querySelector('#prnclientcpescalbag').value = `${donneesbag.prenom_client}`;
                        document.querySelector('#id_lgeheurescalbag').value = `${donneesbag.id_ligneheure}`;
                        document.querySelector('#codtickbagsansesc').value = `${donneesbag.idclescal}`;
                        document.querySelector('#idcompagaescbag').value = `${donneesbag.id_compaga}`;
                        document.querySelector('#lignescalbag').value = `${donneesbag.ident_ligne}`;
                        document.querySelector('#quartpasseesc').value = `${donneesbag.quartier_escal}`;
                        document.querySelector('#infobagasansesc').value = `${donneesbag.nom_client} ${donneesbag.prenom_client}  ${donneesbag.nom_gadest}  ${donneesbag.quartier_escal} ${donneesbag.heure}`;
                    } 
                }
            };
            httpRequestBag.setRequestHeader('Content-Type', 'application/json');
            httpRequestBag.send();
        };
        
        updateContenu = function () 
        {
            // Récupérer le champ "Contenu"
            var contenuField = document.querySelector('textarea[name="naturebagagesansesc"]');
            
            // Récupérer toutes les cases à cocher (checkbox)
            var checkboxes = document.querySelectorAll('input[name="types_bagsansesc[]"]:checked');
            
            // Créer un tableau pour stocker les valeurs des cases cochées
            var selectedValues = [];
            
            // Parcourir les cases cochées et récupérer leur valeur
            checkboxes.forEach(function(checkbox) {
                selectedValues.push(checkbox.value);
            });
            
            // Mettre à jour le contenu du champ avec les cases sélectionnées
            contenuField.value = selectedValues.join(', '); // Séparer par des virgules
        }
        e.onclick = function () {   
            let bagsansForm = document.querySelector('#escalFormbag');
            
            bagsansForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/savebagesc/${e.dataset.cle_compagnie}`);   
        }
        
        var clique = true;

            $('#bottonbagesc').click(function(event) 
            {
                if(clique) 
                {
                    clique = false;
                    return true;
                }
                else return false;
            })       
    })    

});