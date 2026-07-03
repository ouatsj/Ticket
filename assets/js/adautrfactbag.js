document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.adautrfactbag').forEach(function (e) {
        let Requests;
        if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
            Requests = new XMLHttpRequest();
        } else if (window.ActiveXObject) { // IE 6 and older
            Requests = new ActiveXObject("Microsoft.XMLHTTP");
        }
        let axeselect = document.querySelector('#auaxeconf');
        if (axeselect !== null)
        axeselect.onchange = () => {
            
            document.querySelector('#auquartierbag').options.length = 1;
           
            const heureaxep = document.querySelector('#auaxeconf').options[document.querySelector('#auaxeconf').options.selectedIndex].value;
            
            var tpost_arlgsbag = heureaxep.split('/');
                var tseltarsbag = tpost_arlgsbag[0];
                var tsougidarr1sbag = tpost_arlgsbag[1];

                var tpost_arlg1sbag = tsougidarr1sbag.split('/');
                var tseltar1sbag = tpost_arlg1sbag[0];
                var tsougidarr2sbag = tpost_arlg1sbag[1];

            let httpRequetesquart = new XMLHttpRequest();

                httpRequetesquart.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfquart/${tseltarsbag}`, true);
                        httpRequetesquart.onload = () => {
                        const dataq = JSON.parse(httpRequetesquart.responseText);
                        if(dataq == ''){
                            document.querySelector('#auquartierbag').options.length = 1;
                        }else
                        {
                            if (Object.entries(dataq).length >= 1) {
                                        
                                for (let key in Object.entries(dataq)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${dataq[key].nom_quartier}`;
                                    opt.innerHTML = `${dataq[key].nom_quartier}`;
                                    document.querySelector('#auquartierbag').add(opt);
                                }
                            } else {
                                document.querySelector('#auquartierbag').options.length = 1;
                            }
                        }
                            
                            
                };
                httpRequetesquart.setRequestHeader('Content-Type', 'application/json');
                httpRequetesquart.send();

        };
        
        updateContenu = function () 
        {
            // Récupérer le champ "Contenu"
            var contenuField = document.querySelector('textarea[name="aunaturebagagesans"]');
            
            // Récupérer toutes les cases à cocher (checkbox)
            var checkboxes = document.querySelectorAll('input[name="autypes_bagsans[]"]:checked');
            
            // Créer un tableau pour stocker les valeurs des cases cochées
            var selectedValues = [];
            
            // Parcourir les cases cochées et récupérer leur valeur
            checkboxes.forEach(function(checkbox) {
                selectedValues.push(checkbox.value);
            });
            
            // Mettre à jour le contenu du champ avec les cases sélectionnées
            contenuField.value = selectedValues.join(', '); // Séparer par des virgules
        }
        
            //recherche d'information du client depart principal
        let infcontact = document.querySelector('#aupascontactpconf');
        if (infcontact !== null)
        infcontact.onkeyup = () => {
                let httpInfosrequest;
                if (window.XMLHttpRequest) {
                    httpInfosrequest = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfosrequest = new ActiveXObject("Microsoft.XMLHTTP");
                }
                var verifict = document.querySelector('#aupascontactpconf').value;
                httpInfosrequest.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/verifinfos/${verifict}`, true);
                httpInfosrequest.onload = () => {
                    const infosreq = JSON.parse(httpInfosrequest.responseText);
                    if (infosreq == null) {
                        document.querySelector('#aupasnompconf').value = "";
                        document.querySelector('#aupasprenompconf').value = "";
                        document.querySelector('#pascnibpconf').value = "";
                        document.querySelector('#aupasdatepconf').value = "";
                        document.querySelector('#audelivrelieu').value = "";
                        document.querySelector('#auclientconfirmeid').value = "";
                    } else {
                        if (Object.entries(infosreq).length > 1) {
                            
                            if (infosreq.contact_client == verifict) {
                                document.querySelector('#aupasnompconf').value = `${infosreq.nom_client}`;
                                document.querySelector('#aupasprenompconf').value = `${infosreq.prenom_client}`;
                                document.querySelector('#pascnibpconf').value = `${infosreq.num_CNIB}`;
                                document.querySelector('#aupasdatepconf').value = `${infosreq.date_delivre}`;
                                document.querySelector('#audelivrelieu').value = `${infosreq.lieu_delivre}`;
                                document.querySelector('#auclientconfirmeid').value = `${infosreq.id_client}`;

                                document.querySelector('#aupasnompconfcp').value = `${infosreq.nom_client}`;
                                document.querySelector('#aupasprenompconfcp').value = `${infosreq.prenom_client}`;
                                document.querySelector('#aupascnibpconfcp').value = `${infosreq.num_CNIB}`;
                                document.querySelector('#aupasdatepconfcp').value = `${infosreq.date_delivre}`;
                                document.querySelector('#aulieucnibconf').value = `${infosreq.lieu_delivre}`;
                            } else {
                                document.querySelector('#aupasnompconf').value = "";
                                document.querySelector('#aupasprenompconf').value = "";
                                document.querySelector('#pascnibpconf').value = "";
                                document.querySelector('#aupasdatepconf').value = "";
                                document.querySelector('#audelivrelieu').value = "";
                                document.querySelector('#auclientconfirmeid').value = "";
                            }
                        }
                    }
                };
                httpInfosrequest.setRequestHeader('Content-Type', 'application/json');
                httpInfosrequest.send();
            };
        e.onclick = function () {
            let autrForm = document.querySelector('#autrForm');
            autrForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/autresave/${e.dataset.cle_compagnie}`);
        }

        var clique = true;

            $('#auvalidep').click(function(event) 
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