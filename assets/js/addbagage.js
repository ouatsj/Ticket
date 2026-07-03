document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.addbagage').forEach(function (e) 
    {
        
        let baginfos = document.querySelector('#confirme_infocodeticket');
        if (baginfos !== null)
        baginfos.onclick = () => {

            let httpRequestBag;
            
            if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                httpRequestBag = new XMLHttpRequest();
            } else if (window.ActiveXObject) { // IE 6 and older
                httpRequestBag = new ActiveXObject("Microsoft.XMLHTTP");
            }
           
            var bagcocl = document.querySelector("#codeticketbag").value;
            var baggid = document.querySelector("#codebaggid").value;
            var bagsgid = document.querySelector("#codebagsousgid").value;
            httpRequestBag.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/codeclientverif/${bagcocl}/${baggid}/${bagsgid}`, true);
            httpRequestBag.onload = () => {

                const donneesbag = JSON.parse(httpRequestBag.responseText);
                
                if (donneesbag == null) {
                    document.querySelector('#pascontactbagsans').value = '';
                    document.querySelector('#rclientcpbagsans').value = '';
                    document.querySelector('#nclbagasans').value = '';
                    document.querySelector('#prnclientcpbagsans').value = '';
                    document.querySelector('#programbagsans').value = '';
                    document.querySelector('#siegebagsans').value = '';
                    document.querySelector('#codebusbagsans').value = '';
                    document.querySelector('#codtickbagsans').value = '';
                    document.querySelector('#lgcodtickbagsans').value = '';
                    document.querySelector('#siegebagasans').value = '';
                    document.querySelector('#idcompaga').value = '';
                    document.querySelector('#lignespasse').value = '';
                    document.querySelector('#quartpasse').value = '';
                    document.querySelector('#lgecodtickbagsanstr').value = '';
                    document.querySelector('#lgecodtickbagsanstrenr').value = '';
                } else
                {
                    if (Object.entries(donneesbag).length >= 1){

                        document.querySelector('#pascontactbagsans').value = `${String(donneesbag.contact_client)}`;
                        document.querySelector('#rclientcpbagsans').value = `${donneesbag.id_client_pass}`;
                        document.querySelector('#nclbagasans').value = `${donneesbag.nom_client}`;
                        document.querySelector('#prnclientcpbagsans').value = `${donneesbag.prenom_client}`;
                        document.querySelector('#programbagsans').value = `${donneesbag.code_pro}`;
                        document.querySelector('#siegebagsans').value = `${donneesbag.num_siege_categorie}`;
                        document.querySelector('#codebusbagsans').value = `${donneesbag.depart_code}`;
                        document.querySelector('#codtickbagsans').value = `${String(donneesbag.code_ticket)}`;
                        document.querySelector('#lgcodtickbagsans').value = `${donneesbag.code_passager}`;
                        document.querySelector('#siegebagasans').value = `SIEGE : ${donneesbag.num_siege_categorie}  ${donneesbag.nom_gadest}  ${donneesbag.quart} ${donneesbag.heure} Bus : ${donneesbag.depart_code}`;
                        document.querySelector('#idcompaga').value = `${donneesbag.id_compaga}`;
                        document.querySelector('#lgecodtickbagsanstr').value = `${donneesbag.tamponcodtr}`;
                        document.querySelector('#lgecodtickbagsanstrenr').value = `${donneesbag.tamponcodtr}`;
                    } 
                }
            };
            httpRequestBag.setRequestHeader('Content-Type', 'application/json');
            httpRequestBag.send();
        };

        let baginfostr = document.querySelector('#confirme_infocdticket');
        if (baginfostr !== null)
        baginfostr.onclick = () => {

            let httpRequestBagtr;
            
            if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                httpRequestBagtr = new XMLHttpRequest();
            } else if (window.ActiveXObject) { // IE 6 and older
                httpRequestBagtr = new ActiveXObject("Microsoft.XMLHTTP");
            }
           
            var bagcocltr = document.querySelector("#lgecodtickbagsanstr").value;
            var baggid = document.querySelector("#codebaggid").value;
            var bagsgid = document.querySelector("#codebagsousgid").value;

            httpRequestBagtr.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/codeclientveriftr/${bagcocltr}`, true);
            httpRequestBagtr.onload = () => {

                const donneesbagtr = JSON.parse(httpRequestBagtr.responseText);
                
                if (Object.entries(donneesbagtr).length == 1) {
                    
                    for (let item of donneesbagtr) {
                        document.querySelector('#lignespasse').value = `${item.ident_ligne}/${item.idgaresdest}`;
                        document.querySelector('#quartpasse').value = item.quart;
                    }
                    const tbody = document.getElementById("table-body");

                    donneesbagtr.forEach(item => {
                        const tr = document.createElement("tr");

                        tr.innerHTML = `
                            <td>${item.ident_ligne}</td>
                            <td>${item.quart}</td>
                        `;

                        tbody.appendChild(tr);
                    });
                } 
                else
                {
                    if (Object.entries(donneesbagtr).length > 1) {

                        let httpRequestBagtr2;
                        httpRequestBagtr2 = new XMLHttpRequest();
                        httpRequestBagtr2.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/codeclientveriftr2/${bagcocltr}`, true);
                        httpRequestBagtr2.onload = () => {

                            const donneesbagtr2 = JSON.parse(httpRequestBagtr2.responseText);
                
                            if (Object.entries(donneesbagtr2).length == 0) {

                                for (let [key, value] of Object.entries(donneesbagtr)) {
                                    document.querySelector('#lignespasse').value = `${value.ident_ligne}/${value.idgaresdest}`;
                                    document.querySelector('#quartpasse').value = value.quart;
                                }

                                const tbody = document.getElementById("table-body");

                                donneesbagtr.forEach(item => {
                                    const tr = document.createElement("tr");

                                    tr.innerHTML = `
                                        <td>${item.ident_ligne}</td>
                                        <td>${item.quart}</td>
                                    `;

                                    tbody.appendChild(tr);
                                });
                            }
                            else{
                        
                                for (let item of donneesbagtr2) {
                                    document.querySelector('#lignespasse').value = `${item.ident_ligne}/${item.idgaresdest}`;
                                    document.querySelector('#quartpasse').value = item.quart;
                                }
                                const tbody = document.getElementById("table-body");

                                donneesbagtr2.forEach(item => {
                                    const tr = document.createElement("tr");

                                    tr.innerHTML = `
                                        <td>${item.ident_ligne}</td>
                                        <td>${item.quart}</td>
                                    `;

                                    tbody.appendChild(tr);
                                });
                            }
                        };

                        httpRequestBagtr2.setRequestHeader('Content-Type', 'application/json');
                        httpRequestBagtr2.send();
                    }
                }
            };

            httpRequestBagtr.setRequestHeader('Content-Type', 'application/json');
            httpRequestBagtr.send();
        };
        updateContenu = function () 
        {
            // Récupérer le champ "Contenu"
            var contenuField = document.querySelector('textarea[name="naturebagagesans"]');
            
            // Récupérer toutes les cases à cocher (checkbox)
            var checkboxes = document.querySelectorAll('input[name="types_bagsans[]"]:checked');
            
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
            let bagsansForm = document.querySelector('#bagsansForm');
            
            bagsansForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/savebag/${e.dataset.cle_compagnie}`);   
        }
        
        var clique = true;

        $('#bottonbag').click(function(event) 
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