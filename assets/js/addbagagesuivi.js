document.addEventListener('DOMContentLoaded', () => { 
    document.querySelectorAll('.addbagagesuivi').forEach(function (e) 
    {
        let axeselectsbag = document.querySelector('#bagligne');
        if (axeselectsbag !== null)
        axeselectsbag.onchange = () => {
            
            document.querySelector('#quartierbag').options.length = 1;
           
            const lignessbag = document.querySelector('#bagligne').options[document.querySelector('#bagligne').options.selectedIndex].value;
                var post_arlgsbag = lignessbag.split('/');
                var seltarsbag = post_arlgsbag[0];
                var sougidarr1sbag = post_arlgsbag[1];

                var post_arlg1sbag = sougidarr1sbag.split('/');
                var seltar1sbag = post_arlg1sbag[0];
                var sougidarr2sbag = post_arlg1sbag[1];
    
            let httpRequetesquartsbag = new XMLHttpRequest();
            httpRequetesquartsbag.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/verifconfquart/${seltarsbag}`, true);
            httpRequetesquartsbag.onload = () => {
                const dataqsbag = JSON.parse(httpRequetesquartsbag.responseText);
                if(dataqsbag == ''){
                    document.querySelector('#quartierbag').options.length = 1;
                }else{
                    if (Object.entries(dataqsbag).length >= 1) {
                        for (let key in Object.entries(dataqsbag)) {
                            let opt = document.createElement('option');
                            opt.value = `${dataqsbag[key].nom_quartier}`;
                            opt.innerHTML = `${dataqsbag[key].nom_quartier}`;
                            document.querySelector('#quartierbag').add(opt);
                        }
                    } else {
                        document.querySelector('#quartierbag').options.length = 1;
                    }
                }          
            };
            httpRequetesquartsbag.setRequestHeader('Content-Type', 'application/json');
            httpRequetesquartsbag.send();         
        };

        updateContenu = function () 
        {
            // Récupérer le champ "Contenu"
            var contenuField = document.querySelector('textarea[name="naturebagage"]');
            
            // Récupérer toutes les cases à cocher (checkbox)
            var checkboxes = document.querySelectorAll('input[name="types_bagage[]"]:checked');
            
            // Créer un tableau pour stocker les valeurs des cases cochées
            var selectedValues = [];
            
            // Parcourir les cases cochées et récupérer leur valeur
            checkboxes.forEach(function(checkbox) {
                selectedValues.push(checkbox.value);
            });
            
            // Mettre à jour le contenu du champ avec les cases sélectionnées
            contenuField.value = selectedValues.join(', '); // Séparer par des virgules
        }

        let infmobbag = document.querySelector('#rnclient_contactbag');
        if (infmobbag !== null)
            infmobbag.onkeyup = () => {
                let httpInfosmobbag;
                if (window.XMLHttpRequest) {
                    httpInfosmobbag = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfosmobbag = new ActiveXObject("Microsoft.XMLHTTP");
                }
                var verificatmobbag = document.querySelector('#rnclient_contactbag').value;
                
                httpInfosmobbag.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/verifinfos/${verificatmobbag}`, true);
                httpInfosmobbag.onload = () => {
                    const infosmobbag = JSON.parse(httpInfosmobbag.responseText);
                    if (infosmobbag == null) {
                        document.querySelector('#rclientbag').value = "";
                        document.querySelector('#prnclientbag').value = "";
                        document.querySelector('#pascompagniebag').value = "";
                        document.querySelector('#typesmobbag').value = "";
                    } else {
                        if (Object.entries(infosmobbag).length > 1) {
                            
                            if (infosmobbag.contact_client == verificatmobbag) {
                                document.querySelector('#rclientbag').value = `${infosmobbag.nom_client}`;
                                document.querySelector('#prnclientbag').value = `${infosmobbag.prenom_client}`;
                                document.querySelector('#pascompagniebag').value = `${infosmobbag.id_client}`;
                                document.querySelector('#rclientcpbag').value = `${infosmobbag.nom_client}`;
                                document.querySelector('#prnclientcpbag').value = `${infosmobbag.prenom_client}`;
                                document.querySelector('#typesmobbag').value = `${infosmobbag.type_client}`;
                            } else {
                                document.querySelector('#rclientbag').value = "";
                                document.querySelector('#prnclientbag').value = "";
                                document.querySelector('#pascompagniebag').value = "";
                                document.querySelector('#typesmobbag').value = "";
                            }
                        }
                    }
                };
                httpInfosmobbag.setRequestHeader('Content-Type', 'application/json');
                httpInfosmobbag.send();
            };
         
        e.onclick = function (){

            let bagFormsuivi = document.querySelector('#bagFormsuivi');
            
            bagFormsuivi.setAttribute('action', `${APP_ROOT}/Reprogrammes/savebagsuivi/${e.dataset.cle_compagnie}`);   
        }

        var clique = true;

            $('#bottonsuiv').click(function(event) 
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