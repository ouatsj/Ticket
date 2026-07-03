document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.addbagagenfact').forEach(function (e) 
    {
        let baginfosn = document.querySelector('#confirme_infocodeticketn');
        if (baginfosn !== null)
            baginfosn.onclick = () => {

            let httpRequestBagn;
            
            if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                httpRequestBagn = new XMLHttpRequest();
            } else if (window.ActiveXObject) { // IE 6 and older
                httpRequestBagn = new ActiveXObject("Microsoft.XMLHTTP");
            }
           
            
            var bagcocln = document.querySelector("#codeticketbagn").value;
            var baggidn = document.querySelector("#codebaggidn").value;
            var bagsgidn = document.querySelector("#codebagsousgidn").value;
            httpRequestBagn.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/codeclientverif/${bagcocln}/${baggidn}/${bagsgidn}`, true);
            httpRequestBagn.onload = () => {

                const donneesbagn = JSON.parse(httpRequestBagn.responseText);
                
                if (donneesbagn == null) {
                    
                        document.querySelector('#pascontactbagsansn').value = '';
                        document.querySelector('#rclientcpbagsansn').value = '';
                        document.querySelector('#nclbagasansn').value = '';
                        document.querySelector('#prnclientcpbagsansn').value = '';
                        document.querySelector('#programbagsansn').value = '';
                        document.querySelector('#siegebagsansn').value = '';
                        document.querySelector('#codebusbagsansn').value = '';
                        document.querySelector('#codtickbagsansn').value = '';
                        document.querySelector('#lgcodtickbagsansn').value = '';
                        document.querySelector('#lgcodtickbagsansntr').value = '';
                        document.querySelector('#siegebagasansn').value = '';
                        document.querySelector('#lignespassen').value = '';
                        document.querySelector('#quartpassen').value = '';
                        document.querySelector('#lgcodtickbagsansntrenr').value = '';
                } else
                {

                
                    if (Object.entries(donneesbagn).length >= 1){

                    
                        document.querySelector('#pascontactbagsansn').value = `${donneesbagn.contact_client}`;
                        document.querySelector('#rclientcpbagsansn').value = `${donneesbagn.id_client_pass}`;
                        document.querySelector('#nclbagasansn').value = `${donneesbagn.nom_client}`;
                        document.querySelector('#prnclientcpbagsansn').value = `${donneesbagn.prenom_client}`;
                        document.querySelector('#programbagsansn').value = `${donneesbagn.code_pro}`;
                        document.querySelector('#siegebagsansn').value = `${donneesbagn.num_siege_categorie}`;
                        document.querySelector('#codebusbagsansn').value = `${donneesbagn.depart_code}`;
                        document.querySelector('#codtickbagsansn').value = `${donneesbagn.code_ticket}`;
                        document.querySelector('#lgcodtickbagsansn').value = `${donneesbagn.code_passager}`;
                        document.querySelector('#siegebagasansn').value = `SIEGE : ${donneesbagn.num_siege_categorie}  ${donneesbagn.nom_gadest}  ${donneesbagn.quart} ${donneesbagn.heure} Bus : ${donneesbagn.depart_code}`;
                        document.querySelector('#lgcodtickbagsansntr').value = `${donneesbagn.tamponcodtr}`;
                        document.querySelector('#lgcodtickbagsansntrenr').value = `${donneesbagn.tamponcodtr}`;
                    } 
                }
            };
            httpRequestBagn.setRequestHeader('Content-Type', 'application/json');
            httpRequestBagn.send();
        };

        let baginfostr = document.querySelector('#confirme_infocdticketsn');
        if (baginfostr !== null)
        baginfostr.onclick = () => {

            let httpRequestBagtr;
            
            if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                httpRequestBagtr = new XMLHttpRequest();
            } else if (window.ActiveXObject) { // IE 6 and older
                httpRequestBagtr = new ActiveXObject("Microsoft.XMLHTTP");
            }
            
            var baggidtr = document.querySelector("#codebaggidn").value;

            var bagcoclsntr = document.querySelector("#lgcodtickbagsansntr").value;

            httpRequestBagtr.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/codeclientveriftr/${bagcoclsntr}`, true);
            httpRequestBagtr.onload = () => {

                const donneesbagtr = JSON.parse(httpRequestBagtr.responseText);
                
                if (Object.entries(donneesbagtr).length == 1) {
                    
                    for (let item of donneesbagtr) {
                        document.querySelector('#lignespassen').value = `${item.ident_ligne}/${item.idgaresdest}`;
                        document.querySelector('#quartpassen').value = item.quart;
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
                                    document.querySelector('#lignespassen').value = `${value.ident_ligne}/${value.idgaresdest}`;
                                    document.querySelector('#quartpassen').value = value.quart;
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
                                    document.querySelector('#lignespassen').value = `${item.ident_ligne}/${item.idgaresdest}`;
                                    document.querySelector('#quartpassen').value = item.quart;
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
            var contenuField = document.querySelector('textarea[name="naturebagagesansn"]');
            
            // Récupérer toutes les cases à cocher (checkbox)
            var checkboxes = document.querySelectorAll('input[name="types_bagsansn[]"]:checked');
            
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
            let bagsansnForm = document.querySelector('#bagsansnForm');
            
            bagsansnForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/savebagnfact/${e.dataset.cle_compagnie}`);   
        }
        
        var clique = true;

            $('#bottonbagnf').click(function(event) 
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