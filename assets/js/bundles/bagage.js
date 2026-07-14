/* Bundle bagage — genere par scripts/build_module_bundles.php */
/* --- addbagage.js --- */
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
;
/* --- addbagagesuivi.js --- */
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
;
/* --- addbagagenfact.js --- */
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
;
/* --- adautrfactbag.js --- */
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
;
/* --- adbagescale.js --- */
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
;
/* --- adventeescale.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adventeescale').forEach(function (e) 
    {
            let escalgar= document.querySelector('#depargareescal');
            if (escalgar !== null)
            escalgar.onchange = () => {
                document.querySelector('#prix_axeescal').value = '';
                document.querySelector('#date_depheureescal').value = '';
                document.querySelector('#arrsgareescal').value = '';
                document.querySelector('#hdepartescal').options.length = 1;
                document.querySelector('#quartierescal').options.length = 1;
                document.querySelector('#typesescal').value = '';
                  
            };
            let arescal = document.querySelector('#arrsgareescal');
            if (arescal !== null)
            arescal.onchange = () => {
                document.querySelector('#prix_axeescal').value = '';
                document.querySelector('#date_depheureescal').value = '';
                document.querySelector('#hdepartescal').options.length = 1;
                document.querySelector('#quartierescal').options.length = 1;
                document.querySelector('#typesescal').value = '';
                  
                    const typgareescal = document.querySelector('#arrsgareescal')
                    .options[document.querySelector('#arrsgareescal').options.selectedIndex].value;
                    let httptypequartescal;
                    httptypequartescal = new XMLHttpRequest();
                    
                    httptypequartescal.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${typgareescal}`, true);
                    httptypequartescal.onload = () => 
                    {
                        const donquaescal = JSON.parse(httptypequartescal.responseText);
                        if (donquaescal == '') {
                            document.querySelector('#quartierescal').options.length = 1;
                        }
                        else{
                            if (Object.entries(donquaescal).length >= 1) {
                                            
                                for (let key in Object.entries(donquaescal)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${donquaescal[key].nom_quartier}`;
                                    opt.innerHTML = `${donquaescal[key].nom_quartier}`;
                                    document.querySelector('#quartierescal').add(opt);
                                }
                            } else {
                                document.querySelector('#quartierescal').options.length = 1;
                            }
                        }
                        
                    };
                    httptypequartescal.setRequestHeader('Content-Type', 'application/json');
                    httptypequartescal.send();
            };
            
            let daescal = document.querySelector('#date_depheureescal');
            if (daescal !== null){
                daescal.onchange = () => 
                {
                    
                    document.querySelector('#hdepartescal').options.length = 1;
                    
                    let httpRequetesescal;
                    
                    if (window.XMLHttpRequest) {
                        httpRequetesescal = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpRequetesescal = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                        var depaescal = document.querySelector('#depargareescal').value;
                        var arrescal = document.querySelector('#arrsgareescal').value;
                        var datedepartescal = document.querySelector('#date_depheureescal').value;
                        var dateactuescal = document.querySelector('#actuescal').value;
                                         
                        var post_lhdepescal = depaescal.split('/');
                        var seltdepescal = post_lhdepescal[0];
                        var sougidescal = post_lhdepescal[1];
                        var dest_lhdepescal = arrescal.split('/');
                        var seltdestescal = dest_lhdepescal[0];
                        var sougesescal = dest_lhdepescal[1];
                        if(datedepartescal >= dateactuescal)
                        {
                            
                            httpRequetesescal.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheure1/${seltdepescal}-${seltdestescal}/${datedepartescal}`, true);
                            httpRequetesescal.onload = () => {
                                const dataAxeescal = JSON.parse(httpRequetesescal.responseText);
                                
                                    if (dataAxeescal == '') {
                                        
                                        document.querySelector('#smsdtescal').style.display = 'none';
                                        document.querySelector('#date_depheureescal').style.color = "black";
                                        document.querySelector('#date_depheureescal').style.border = "1px solid";
                                        
                                    } 
                                    else 
                                    {       
                                        
                                        document.querySelector('#smsdtescal').style.display = 'none';
                                        document.querySelector('#date_depheureescal').style.color = "black";
                                        document.querySelector('#date_depheureescal').style.border = "1px solid";
                                        if (Object.entries(dataAxeescal).length >= 1) 
                                        {
                                                
                                            
                                            for (let key in Object.entries(dataAxeescal)) {
                                                    let opt = document.createElement('option');
                                                    opt.value = `${dataAxeescal[key].id_ligneheure}/${dataAxeescal[key].heure}`;
                                                    opt.innerHTML = `${dataAxeescal[key].heure}`;
                                                    document.querySelector('#hdepartescal').add(opt);
                                                }
                                        } else {
                                            document.querySelector('#hdepartescal').options.length = 1;
                                        }
                                    }
                                                                               
                            };
                            httpRequetesescal.setRequestHeader('Content-Type', 'application/json');
                            httpRequetesescal.send();

                            let hrdepartescal = document.querySelector('#hdepartescal');
                            if (hrdepartescal !== null) {
                                hrdepartescal.onchange = () => 
                                {       
                                    const seleescal = document.querySelector('#hdepartescal')
                                        .options[document.querySelector('#hdepartescal').options.selectedIndex].value;

                                        var post_lhescal = seleescal.split('/');
                                        var selescal = post_lhescal[0];
                                        var lhselescal = post_lhescal[1];

                                    var tfbsescal = document.querySelector('#tarifattribescal').value;
                                    const httpPrixescal = new XMLHttpRequest();
                                    httpPrixescal.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifpriesc/${selescal}/${tfbsescal}/${seltdepescal}`, true);
                                    httpPrixescal.onload = () => 
                                    {

                                        const donprixescal = JSON.parse(httpPrixescal.responseText);
                                        console.debug(`${typeof donprixescal}-${donprixescal.attributes}`, console.memory);
                                        if (Object.entries(donprixescal).length >= 1) {
                                            for (let key in Object.entries(donprixescal)) 
                                            {
                                                document.querySelector('#prix_axeescal').value = `${donprixescal[key].prix}`;

                                            }
                                        }
                                    };
                                    httpPrixescal.setRequestHeader('Content-Type', 'application/json');
                                    httpPrixescal.send();
                                };
                            }

                        }
                        else
                        {
                            document.querySelector('#date_depheureescal').style.color = "#FF0000";
                            document.querySelector('#date_depheureescal').style.border = "2px solid #FF0000";
                            document.querySelector('#smsdtescal').style.display = 'block';
                            document.querySelector('#erreurSmsdtescal').innerHTML = `Date non valide.`;
                        }
                    
                }; 
            }
            
        //recherche d'information du client depart principal
        let infescal = document.querySelector('#rnclient_contactescal');
        if (infescal !== null)
            infescal.onkeyup = () => {
                let httpInfosescal;
                if (window.XMLHttpRequest) {
                    httpInfosescal = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfosescal = new ActiveXObject("Microsoft.XMLHTTP");
                }
                var verificatescal = document.querySelector('#rnclient_contactescal').value;
                
                httpInfosescal.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifinfos/${verificatescal}`, true);
                httpInfosescal.onload = () => {
                    const infosescal = JSON.parse(httpInfosescal.responseText);
                    if (infosescal == null) {
                        document.querySelector('#rclientescal').value = "";
                        document.querySelector('#prnclientescal').value = "";
                        document.querySelector('#pascompagnieescal').value = "";
                        document.querySelector('#typesescal').value = "";
                        document.querySelector('#cnibclescal').value = "";
                        document.querySelector('#dateclescal').value = "";
                        document.querySelector('#lieuclescal').value = "";
                    
                    } else {
                        if (Object.entries(infosescal).length > 1) {
                            
                            if (infosescal.contact_client == verificatescal) {
                                document.querySelector('#rclientescal').value = `${infosescal.nom_client}`;
                                document.querySelector('#prnclientescal').value = `${infosescal.prenom_client}`;
                                document.querySelector('#pascompagnieescal').value = `${infosescal.id_client}`;
                                document.querySelector('#rclientcpescal').value = `${infosescal.nom_client}`;
                                document.querySelector('#prnclientcpescal').value = `${infosescal.prenom_client}`;
                                document.querySelector('#typesescal').value = `${infosescal.type_client}`;
                                document.querySelector('#cnibclescal').value = `${infosescal.num_CNIB}`;
                                document.querySelector('#dateclescal').value = `${infosescal.date_delivre}`;
                                document.querySelector('#lieuclescal').value = `${infosescal.lieu_delivre}`;
                    
                            } else {
                                document.querySelector('#rclientescal').value = "";
                                document.querySelector('#prnclientescal').value = "";
                                document.querySelector('#pascompagnieescal').value = "";
                                document.querySelector('#typesescal').value = "";
                                document.querySelector('#cnibclescal').value = "";
                                document.querySelector('#dateclescal').value = "";
                                document.querySelector('#lieuclescal').value = "";
                    
                            }
                        }
                    }
                };
                httpInfosescal.setRequestHeader('Content-Type', 'application/json');
                httpInfosescal.send();
            };
            
                e.onclick = function () {   
                    let escalForm = document.querySelector('#escalForm');
                    
                    escalForm.setAttribute('action', `${APP_ROOT}/Ventescales/passagerescal/${e.dataset.cle_compagnie}`);   
                }

                var clique = true;

                $('#bottonescal').click(function(event) 
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
;
/* --- adcourescale.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adcourescale').forEach(function (e) 
    {       

            let arcour = document.querySelector('#arrscouresc');
            if (arcour !== null)
            arcour.onchange = () => {
                document.querySelector('#date_depheurecourexesc').value = '';
                document.querySelector('#hdepcouresc').options.length = 1;
                document.querySelector('#quartiercouresc').options.length = 1;
                document.querySelector('#statenvoiesc').value = 'nonpatterns';
                const garedepartcour = document.querySelector('#deparcouresc').value;
                const garearriv = document.querySelector('#arrscouresc').value;
                var post_ar = garearriv.split('/');
                var seltar = post_ar[0];
                var sougidarr = post_ar[1];
                let httptypequart;
                httptypequart = new XMLHttpRequest();
                
                httptypequart.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${seltar}`, true);
                httptypequart.onload = () => 
                {
                    const courqua = JSON.parse(httptypequart.responseText);
                    if (courqua == '') {
                        document.querySelector('#quartiercouresc').options.length = 1;
                    }
                    else{
                        if (Object.entries(courqua).length >= 1) {
                                        
                            for (let key in Object.entries(courqua)) {
                                let opt = document.createElement('option');
                                opt.value = `${courqua[key].code_quart}/${courqua[key].nom_quartier}`;
                                opt.innerHTML = `${courqua[key].nom_quartier}/${courqua[key].code_quart}`;
                                document.querySelector('#quartiercouresc').add(opt);
                            }
                        } else {
                            document.querySelector('#quartiercouresc').options.length = 1;
                        }
                    }
                    

                };
                httptypequart.setRequestHeader('Content-Type', 'application/json');
                httptypequart.send();
            };
            let dpcourex = document.querySelector('#date_depheurecourexesc');
            if (dpcourex !== null)
               dpcourex.onchange = () => {

                    const dateactuex = document.querySelector('#dateactesc').value;
                    const progdepartex = document.querySelector('#date_depheurecourexesc').value;
                    document.querySelector('#hdepcouresc').options.length = 1;
                    if(progdepartex >= dateactuex)
                    {
                        
                            const garearrive1 = document.querySelector('#arrscouresc').value;
                            const garedepartcour1 = document.querySelector('#deparcouresc').value;
                            const progdepart1 = document.querySelector('#date_depheurecourexesc').value;
                            document.querySelector('#hdepcouresc').options.length = 1;
                            var post_lhdep1 = garedepartcour1.split('/');
                            var seltdep1 = post_lhdep1[0];
                            var sougid1 = post_lhdep1[1];
                            var post_arr1 = garearrive1.split('/');
                            var seltarr1 = post_arr1[0];
                            var sougidar1 = post_arr1[1];
                            
                            let httpRequetesescal;
                            httpRequetesescal = new XMLHttpRequest();
                
                            httpRequetesescal.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheure1/${seltdep1}-${seltarr1}/${progdepart1}`, true);
                            httpRequetesescal.onload = () => {
                                const dataAxeescal = JSON.parse(httpRequetesescal.responseText);
                                
                                    if (dataAxeescal == '') {
                                        
                                        document.querySelector('#smsdtcresc').style.display = 'none';
                                        document.querySelector('#date_depheurecourexesc').style.color = "black";
                                        document.querySelector('#date_depheurecourexesc').style.border = "1px solid";
                                        
                                    } 
                                    else 
                                    {
                                        document.querySelector('#smsdtcresc').style.display = 'none';
                                        document.querySelector('#date_depheurecourexesc').style.color = "black";
                                        document.querySelector('#date_depheurecourexesc').style.border = "1px solid";
                                        if (Object.entries(dataAxeescal).length >= 1) 
                                        {
                                            for (let key in Object.entries(dataAxeescal)) {
                                                    let opt = document.createElement('option');
                                                    opt.value = `${dataAxeescal[key].id_ligneheure}`;
                                                    opt.innerHTML = `${dataAxeescal[key].heure}`;
                                                    document.querySelector('#hdepcouresc').add(opt);
                                                }
                                        } else {
                                            document.querySelector('#hdepcouresc').options.length = 1;
                                        }
                                    }
                                                                               
                            };
                            httpRequetesescal.setRequestHeader('Content-Type', 'application/json');
                            httpRequetesescal.send();
                    }
                    else
                    {
                        document.querySelector('#date_depheurecourexesc').style.color = "#FF0000";
                        document.querySelector('#date_depheurecourexesc').style.border = "2px solid #FF0000";
                        document.querySelector('#smsdtcresc').style.display = 'block';
                        document.querySelector('#erreurSmsdtcresc').innerHTML = `Date non valide.`;
                    }
                };

                
               
               let typers = document.querySelector('#type_personesc');
                if (typers !== null)
                typers.onchange = () => {

                    var typersopers = document.querySelector('#type_personesc').
                        options[document.querySelector('#type_personesc').options.selectedIndex].value;
                        var typerso1pers = typersopers.split('/');
                        var typerso2pers = typerso1pers[0];
                        var typerso3pers = typerso1pers[1];

                        document.querySelector('#types_courriersesc').options.length = 1;
                        document.querySelector('#exp_nomesc').value = "";
                        document.querySelector('#exp_prenomesc').value = "";
                        document.querySelector('#cnib_expesc').value = "";
                        document.querySelector('#iddate_cnibesc').value = "";
                        document.querySelector('#lieudelexpesc').value = "";
                        document.querySelector('#passcompagnieesc').value = "";
                        document.querySelector('#rclientcpexpesc').value = "";
                        document.querySelector('#prnclientcpexpesc').value = "";
                        document.querySelector('#cnibcpexpesc').value = "";
                        document.querySelector('#date_cnibcpexpesc').value = "";
                        document.querySelector('#lieudelivrecpexpesc').value = "";
                        document.querySelector('#idclientypeexpesc').value = "";
                        

                            let httpRequesttespersos1;
                            httpRequesttespersos1 = new XMLHttpRequest();

                                
                            httpRequesttespersos1.open('GET', window.location.origin + `${APP_ROOT}/confirmation/fetch_typecourriers`, true);
                            httpRequesttespersos1.onload = () => {
                                const datapersos1 = JSON.parse(httpRequesttespersos1.responseText);
                                     if (Object.entries(datapersos1).length >= 1) {
                                   
                                        for (let key in Object.entries(datapersos1)) {
                                            let opt = document.createElement('option');
                                            opt.value = `${datapersos1[key].id_cat}/${datapersos1[key].categ}/${datapersos1[key].indicatif}`;
                                            opt.innerHTML = `${datapersos1[key].categ}`;
                                            document.querySelector('#types_courriersesc').add(opt);
                                        }
                                    } else {
                                        document.querySelector('#types_courriersesc').options.length = 1;
                                    }
                            };
                    
                            httpRequesttespersos1.setRequestHeader('Content-Type', 'application/json');
                            httpRequesttespersos1.send();
                };

                
                let inf = document.querySelector('#exp_contactesc');
                if (inf !== null)
                inf.onkeyup = () => {
                    let httpInfos;
                    if (window.XMLHttpRequest) {
                        httpInfos = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfos = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var verificat = document.querySelector('#exp_contactesc').value;
                    
                    httpInfos.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfos/${verificat}`, true);
                    httpInfos.onload = () => {
                        const infos = JSON.parse(httpInfos.responseText);
                        if (infos == null) {
                            document.querySelector('#exp_nomesc').value = "";
                            document.querySelector('#exp_prenomesc').value = "";
                            document.querySelector('#cnib_expesc').value = "";
                            document.querySelector('#iddate_cnibesc').value = "";
                            document.querySelector('#lieudelexpesc').value = "";
                            document.querySelector('#passcompagnieesc').value = "";
                            document.querySelector('#rclientcpexpesc').value = "";
                            document.querySelector('#prnclientcpexpesc').value = "";
                            document.querySelector('#cnibcpexpesc').value = "";
                            document.querySelector('#date_cnibcpexpesc').value = "";
                            document.querySelector('#lieudelivrecpexpesc').value = "";
                            document.querySelector('#idclientypeexpesc').value = "";
                          
                        } else 
                        {
                            if (Object.entries(infos).length > 1) {
                                
                                if (infos.contact_client == verificat) {
                                    document.querySelector('#exp_nomesc').value = `${infos.nom_client}`;
                                    document.querySelector('#exp_prenomesc').value = `${infos.prenom_client}`;
                                    document.querySelector('#cnib_expesc').value = `${infos.num_CNIB}`;
                                    document.querySelector('#iddate_cnibesc').value = `${infos.date_delivre}`;
                                    document.querySelector('#lieudelexpesc').value = `${infos.lieu_delivre}`;
                                    document.querySelector('#passcompagnieesc').value = `${infos.id_client}`;
                                    document.querySelector('#rclientcpexpesc').value = `${infos.nom_client}`;
                                    document.querySelector('#prnclientcpexpesc').value = `${infos.prenom_client}`;
                                    document.querySelector('#cnibcpexpesc').value = `${infos.num_CNIB}`;
                                    document.querySelector('#date_cnibcpexpesc').value = `${infos.date_delivre}`;
                                    document.querySelector('#lieudelivrecpexpesc').value = `${infos.lieu_delivre}`;
                                    document.querySelector('#idclientypeexpesc').value = `${infos.type_client}`;
                          
                                } else {
                                    document.querySelector('#exp_nomesc').value = "";
                                    document.querySelector('#exp_prenomesc').value = "";
                                    document.querySelector('#cnib_expesc').value = "";
                                    document.querySelector('#iddate_cnibesc').value = "";
                                    document.querySelector('#lieudelexpesc').value = "";
                                    document.querySelector('#passcompagnieesc').value = "";
                                    document.querySelector('#rclientcpexpesc').value = "";
                                    document.querySelector('#prnclientcpexpesc').value = "";
                                    document.querySelector('#cnibcpexpesc').value = "";
                                    document.querySelector('#date_cnibcpexpesc').value = "";
                                    document.querySelector('#lieudelivrecpexpesc').value = "";
                                    document.querySelector('#idclientypeexpesc').value = "";
                          
                                }
                            }
                        }
                    };
                    httpInfos.setRequestHeader('Content-Type', 'application/json');
                    httpInfos.send();
                };
                

                let infopersos = document.querySelector('#idtypeesc');
                if (infopersos !== null) 
                infopersos.onchange = () => 
                {
                    document.querySelector('#contactidesc').style.display = 'none';
                    document.querySelector('#idcontesc').style.display = 'none';
                    document.querySelector('#sonnelesc').style.display = 'none';
                    document.querySelector('#idsonnelsesc').style.display = 'none';
                    document.querySelector('#idpartesesc').options.length = 1;
                    document.querySelector('#membrepartoidesc').options.length = 1;
                    document.querySelector('#contactidesc').value = '';
                    document.querySelector('#membrepartoesc').style.display = 'none';
                    document.querySelector('#membrepartoidesc').style.display = 'none';
                           
                    var personns = document.querySelector('#idtypeesc')
                        .options[document.querySelector('#idtypeesc').options.selectedIndex].value;
                        if(personns === 'personnel')
                        {
                    
                            document.querySelector('#sonnelesc').style.display = 'block';
                            document.querySelector('#idsonnelsesc').style.display = 'block';
                            document.querySelector('#contactidesc').style.display = 'none';
                            document.querySelector('#idcontesc').style.display = 'none';
                            document.querySelector('#partcontesc').style.display = 'none';
                            document.querySelector('#idpartesesc').style.display = 'none';
                            document.querySelector('#membrepartoesc').style.display = 'none';
                            document.querySelector('#membrepartoidesc').style.display = 'none';
                                
                                    let httppersosdest;
                            if (window.XMLHttpRequest) {
                                httppersosdest = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httppersosdest = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                            httppersosdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectperso/${personns}`, true);
                            httppersosdest.onload = () => 
                            {

                                const infospersdest = JSON.parse(httppersosdest.responseText);

                                if (Object.entries(infospersdest).length >= 1) 
                                {


                                    for (let key in Object.entries(infospersdest))
                                    {

                                        let opt = document.createElement('option');
                                        opt.value = `${infospersdest[key].matricule}`;
                                        opt.innerHTML = `${infospersdest[key].nomprenom_perso}`;
                                        document.querySelector('#idsonnelsesc').add(opt);
                                    }
                                 
                                }
                                else 
                                {
                                    document.querySelector('#idsonnelsesc').options.length = 1;
                                }

                            };
                            httppersosdest.setRequestHeader('Content-Type', 'application/json');
                            httppersosdest.send();

                            let infopersosdest = document.querySelector('#idsonnels');
        
                            if (infopersosdest !== null) 
                            infopersosdest.onchange = () => 
                            {

                            
                                let httpInfospersdest;
                                if (window.XMLHttpRequest) {
                                    httpInfospersdest = new XMLHttpRequest();
                                } else if (window.ActiveXObject) {
                                    httpInfospersdest = new ActiveXObject("Microsoft.XMLHTTP");
                                }

                                document.querySelector('#contactidesc').style.display = 'none';
                                document.querySelector('#idcontesc').style.display = 'none';
                                document.querySelector('#compagniepassdestesc').value = '';
                                var idverifidest = document.querySelector('#idsonnelsesc').options[document.querySelector('#idsonnelsesc').options.selectedIndex].value;
                    
                                httpInfospersdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoperso/${idverifidest}`, true);
                                httpInfospersdest.onload = () => {
                                    const infosperdest = JSON.parse(httpInfospersdest.responseText);
                                    
                                    if (Object.entries(infosperdest).length >= 1) {
                                        
                               
                                        var typepersosdest = `${infosperdest.nomprenom_perso}`;
                                        var typer1persosdest = typepersosdest.split(' ');
                                        var typer2persosdest = typer1persosdest[0];
                                        var typer3persosdest = typer1persosdest[1];
                                        var typer4persosdest = typer1persosdest[2];
                                        if(typer4persosdest === undefined){
                                            var typer5persosdest = `${typer3persosdest}`;
                                        }
                                        else
                                        {
                                            var typer5persosdest = `${typer3persosdest} ${typer4persosdest}`;
                                        }
                                        document.querySelector('#nomdestidesc').value = `${typer2persosdest}`;
                                        document.querySelector('#prenomdestidesc').value = `${typer5persosdest}`;
                                        document.querySelector('#persodestcompagnieesc').value = `${infosperdest.matricule}`;
                                        document.querySelector('#rclientcpexpesc').value = `${typer2persosdest}`;
                                        document.querySelector('#prnclientcpexpesc').value = `${typer5persosdest}`;
                                        document.querySelector('#idclientypedestesc').value = 'personnel';
                                        
                                    } 
                                    else
                                    {
                                        document.querySelector('#nomdestidesc').value = "";
                                        document.querySelector('#prenomdestidesc').value = "";
                                        document.querySelector('#persodestcompagnieesc').value = "";
                                        document.querySelector('#rclientcpdestesc').value = "";
                                        document.querySelector('#prnclientcpdestesc').value = "";
                                        document.querySelector('#idclientypedestesc').value = "";
                                    }
                                                                
                                };
                                httpInfospersdest.setRequestHeader('Content-Type', 'application/json');
                                httpInfospersdest.send();
                            };
                        }
                        else
                        {
                            document.querySelector('#membrepartoesc').style.display = 'none';
                            document.querySelector('#membrepartoidesc').style.display = 'none';
                            document.querySelector('#sonnelesc').style.display = 'none';
                            document.querySelector('#idsonnelsesc').style.display = 'none';
                            document.querySelector('#partcontesc').style.display = 'none';
                            document.querySelector('#idpartesesc').style.display = 'none';
                            document.querySelector('#idcontesc').style.display = 'block';
                            document.querySelector('#contactidesc').style.display = 'block';
                            document.querySelector('#nomdestidesc').value = "";
                            document.querySelector('#prenomdestidesc').value = "";
                            document.querySelector('#compagniepassdestesc').value = "";
                            document.querySelector('#rclientcpdestesc').value = "";
                            document.querySelector('#prnclientcpdestesc').value = "";
                            document.querySelector('#idclientypedestesc').value = "";
                            let infdest = document.querySelector('#contactidesc');
                            if (infdest !== null)
                                infdest.onkeyup = () => {
                                    let httpInfosdest;
                                    if (window.XMLHttpRequest) {
                                        httpInfosdest = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) {
                                        httpInfosdest = new ActiveXObject("Microsoft.XMLHTTP");
                                    }

                                    document.querySelector('#nomdestidesc').value = "";
                                    document.querySelector('#prenomdestidesc').value = "";
                                    document.querySelector('#compagniepassdestesc').value = "";
                                    document.querySelector('#rclientcpdestesc').value = "";
                                    document.querySelector('#prnclientcpdestesc').value = "";
                                    document.querySelector('#idclientypedestesc').value = "";
                                    var verificatdest = document.querySelector('#contactidesc').value;
                                    document.querySelector('#persodestcompagnieesc').value = "";

                                    httpInfosdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfos/${verificatdest}`, true);
                                    httpInfosdest.onload = () => {
                                        const infosdest = JSON.parse(httpInfosdest.responseText);
                                        if (infosdest == null) {
                                            document.querySelector('#nomdestidesc').value = "";
                                            document.querySelector('#prenomdestidesc').value = "";
                                            document.querySelector('#compagniepassdestesc').value = "";
                                            document.querySelector('#rclientcpdestesc').value = "";
                                            document.querySelector('#prnclientcpdestesc').value = "";
                                            document.querySelector('#idclientypedestesc').value = "";
                                            document.querySelector('#date_cnibdestidesc').value = "";
                                            
                                        } else 
                                        {
                                            if (Object.entries(infosdest).length > 1) {
                                                
                                                if (infosdest.contact_client == verificatdest) {
                                                    document.querySelector('#nomdestidesc').value = `${infosdest.nom_client}`;
                                                    document.querySelector('#prenomdestidesc').value = `${infosdest.prenom_client}`;
                                                    document.querySelector('#compagniepassdestesc').value = `${infosdest.id_client}`;
                                                    document.querySelector('#idclientypedestesc').value = `${infosdest.type_client}`;
                                                    document.querySelector('#rclientcpdestesc').value = `${infosdest.nom_client}`;
                                                    document.querySelector('#prnclientcpdestesc').value = `${infosdest.prenom_client}`;
                                                    document.querySelector('#date_cnibdestidesc').value = `${infosdest.date_delivre}`;
                                                    
                                                } else {
                                                    document.querySelector('#nomdestidesc').value = "";
                                                    document.querySelector('#prenomdestidesc').value = "";
                                                    document.querySelector('#compagniepassdestesc').value = "";
                                                    document.querySelector('#rclientcpdestesc').value = "";
                                                    document.querySelector('#prnclientcpdestesc').value = "";
                                                    document.querySelector('#idclientypedestesc').value = "";
                                                    document.querySelector('#date_cnibdestidesc').value = "";
                                                    
                                                }
                                            }
                                        }
                                    };
                                    httpInfosdest.setRequestHeader('Content-Type', 'application/json');
                                    httpInfosdest.send();
                                };
                        }
                        if(personns === 'membre'){

                                document.querySelector('#membrepartoesc').style.display = 'block';
                                document.querySelector('#membrepartoidesc').style.display = 'block';
                                document.querySelector('#sonnelesc').style.display = 'none';
                                document.querySelector('#idsonnelsesc').style.display = 'none';
                                document.querySelector('#idcontesc').style.display = 'none';
                                document.querySelector('#contactidesc').style.display = 'none';
                                document.querySelector('#partcontesc').style.display = 'none';
                                document.querySelector('#idpartesesc').style.display = 'none';
                                
                                
                        
                                let httppaternesdestm;
                                    if (window.XMLHttpRequest) {
                                        httppaternesdestm = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) {
                                        httppaternesdestm = new ActiveXObject("Microsoft.XMLHTTP");
                                    }
                                    
                                    httppaternesdestm.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectpartenaire/${personns}`, true);
                                    httppaternesdestm.onload = () => {
                                        const infospartenedestm = JSON.parse(httppaternesdestm.responseText);

                                        if (Object.entries(infospartenedestm).length >= 1) 
                                        {

                                            for (let key in Object.entries(infospartenedestm))
                                            {

                                                let opt = document.createElement('option');
                                                opt.value = `${infospartenedestm[key].id_client}`;
                                                opt.innerHTML = `${infospartenedestm[key].nom_client} ${infospartenedestm[key].prenom_client}`;
                                                document.querySelector('#membrepartoidesc').add(opt);
                                            }
                                                
                                        }
                                        else 
                                        {
                                            document.querySelector('#membrepartoidesc').options.length = 1;
                                        }

                                    };
                                    httppaternesdestm.setRequestHeader('Content-Type', 'application/json');
                                    httppaternesdestm.send();

                                let paternstscdestin2m = document.querySelector('#membrepartoidesc');
                                if (paternstscdestin2m !== null)
                                paternstscdestin2m.onchange = () => {
                                    let httpInfospersdestin2m;
                                        httpInfospersdestin2m = new XMLHttpRequest();
                                    document.querySelector('#persodestcompagnieesc').value = '';
                                    document.querySelector('#contactidesc').style.display = 'none';
                                    document.querySelector('#idcontesc').style.display = 'none';
                                    document.querySelector('#contactidesc').value = '';
                                        var ternsdest2m = document.querySelector('#membrepartoidesc').
                                            options[document.querySelector('#membrepartoidesc').options.selectedIndex].value;
                                        httpInfospersdestin2m.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest2m}`, true);
                                    httpInfospersdestin2m.onload = () => {
                                        const infosperdestin2m = JSON.parse(httpInfospersdestin2m.responseText);
                                        
                                        if (Object.entries(infosperdestin2m).length >= 1) {
                                            
                                   
                                            
                                            document.querySelector('#nomdestidesc').value = `${infosperdestin2m.nom_client}`;
                                            document.querySelector('#prenomdestidesc').value = `${infosperdestin2m.prenom_client}`;
                                            document.querySelector('#compagniepassdestesc').value = `${infosperdestin2m.id_client}`;
                                            document.querySelector('#idclientypedestesc').value = `${infosperdestin2m.type_client}`;
                                            document.querySelector('#rclientcpdestesc').value = `${infosperdestin2m.nom_client}`;
                                            document.querySelector('#prnclientcpdestesc').value = `${infosperdestin2m.prenom_client}`;
                                            document.querySelector('#date_cnibdestidesc').value = `${infosperdestin2m.date_delivre}`;
                                        } 
                                        else
                                        {
                                            document.querySelector('#nomdestidesc').value = "";
                                            document.querySelector('#prenomdestidesc').value = "";
                                            document.querySelector('#compagniepassdestesc').value = "";
                                            document.querySelector('#rclientcpdestesc').value = "";
                                            document.querySelector('#prnclientcpdestesc').value = "";
                                            document.querySelector('#idclientypedestesc').value = "";
                                            document.querySelector('#date_cnibdestidesc').value = "";
                                        }
                                                                    
                                    };
                                    httpInfospersdestin2m.setRequestHeader('Content-Type', 'application/json');
                                    httpInfospersdestin2m.send();
                                };
                   
                            
                        }

                        if(personns === 'partenaire_client' || personns === 'partenaire_simple'){
                            document.querySelector('#partcontesc').style.display = 'block';
                            document.querySelector('#idpartesesc').style.display = 'block';
                            document.querySelector('#sonnelesc').style.display = 'none';
                            document.querySelector('#idsonnelsesc').style.display = 'none';
                            document.querySelector('#contactidesc').style.display = 'none';
                            document.querySelector('#idcontesc').style.display = 'none';
                            document.querySelector('#nomdestidesc').value = '';
                            document.querySelector('#prenomdestidesc').value = '';
                            document.querySelector('#compagniepassdestesc').value = '';
                            document.querySelector('#idclientypedestesc').value = '';
                            document.querySelector('#rclientcpdestesc').value = '';
                            document.querySelector('#prnclientcpdestesc').value = '';
                            document.querySelector('#contactidesc').value = '';
                            document.querySelector('#membrepartoesc').style.display = 'none';
                            document.querySelector('#membrepartoidesc').style.display = 'none';
                            let httppaternsdest;
                                if (window.XMLHttpRequest) {
                                    httppaternsdest = new XMLHttpRequest();
                                } else if (window.ActiveXObject) {
                                    httppaternsdest = new ActiveXObject("Microsoft.XMLHTTP");
                                }
                                
                                httppaternsdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectpartenaire/${personns}`, true);
                                httppaternsdest.onload = () => {
                                    const infospartendest = JSON.parse(httppaternsdest.responseText);

                                    if (Object.entries(infospartendest).length >= 1) 
                                    {

                                        for (let key in Object.entries(infospartendest))
                                        {

                                            let opt = document.createElement('option');
                                            opt.value = `${infospartendest[key].id_client}`;
                                            opt.innerHTML = `${infospartendest[key].nom_client} ${infospartendest[key].prenom_client}`;
                                            document.querySelector('#idpartesesc').add(opt);
                                        }
                                            
                                    }
                                    else 
                                    {
                                        document.querySelector('#idpartesesc').options.length = 1;
                                    }

                                };
                                httppaternsdest.setRequestHeader('Content-Type', 'application/json');
                                httppaternsdest.send();

                                let paternstscdestin = document.querySelector('#idpartesesc');
                            if (paternstscdestin !== null)
                            paternstscdestin.onchange = () => {
                                let httpInfospersdestin;
                                    httpInfospersdestin = new XMLHttpRequest();
                                document.querySelector('#persodestcompagnieesc').value = '';
                                document.querySelector('#contactidesc').style.display = 'none';
                                document.querySelector('#idcontesc').style.display = 'none';
                                document.querySelector('#contactidesc').value = '';
                                var ternsdest= document.querySelector('#idpartesesc').
                                    options[document.querySelector('#idpartesesc').options.selectedIndex].value;
                                httpInfospersdestin.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest}`, true);
                                httpInfospersdestin.onload = () => {
                                    const infosperdestin = JSON.parse(httpInfospersdestin.responseText);
                                    
                                    if (Object.entries(infosperdestin).length >= 1) {
                                        
                               
                                        
                                        document.querySelector('#nomdestidesc').value = `${infosperdestin.nom_client}`;
                                        document.querySelector('#prenomdestidesc').value = `${infosperdestin.prenom_client}`;
                                        document.querySelector('#compagniepassdestesc').value = `${infosperdestin.id_client}`;
                                        document.querySelector('#idclientypedestesc').value = `${infosperdestin.type_client}`;
                                        document.querySelector('#rclientcpdestesc').value = `${infosperdestin.nom_client}`;
                                        document.querySelector('#prnclientcpdestesc').value = `${infosperdestin.prenom_client}`;
                                        document.querySelector('#date_cnibdestidesc').value = `${infosperdestin.date_delivre}`;
                                        
                                    } 
                                    else
                                    {
                                        document.querySelector('#nomdestidesc').value = "";
                                        document.querySelector('#prenomdestidesc').value = "";
                                        document.querySelector('#compagniepassdestesc').value = "";
                                        document.querySelector('#rclientcpdestesc').value = "";
                                        document.querySelector('#prnclientcpdestesc').value = "";
                                        document.querySelector('#idclientypedestesc').value = "";
                                        document.querySelector('#date_cnibdestidesc').value = "";
                                    }
                                                                
                                };
                                httpInfospersdestin.setRequestHeader('Content-Type', 'application/json');
                                httpInfospersdestin.send();
                            };
 
                        }
                        else
                        {


                            if(personns === 'partenaire_specifique'){

                                document.querySelector('#partcontesc').style.display = 'block';
                                document.querySelector('#idpartesesc').style.display = 'block';
                                document.querySelector('#sonnelesc').style.display = 'none';
                                document.querySelector('#idsonnelsesc').style.display = 'none';
                                document.querySelector('#idcontesc').style.display = 'none';
                                document.querySelector('#contactidesc').style.display = 'none';
                                document.querySelector('#membrepartoesc').style.display = 'none';
                                document.querySelector('#membrepartoidesc').style.display = 'none';
                                
                        
                                let httppaternesdest;
                                    if (window.XMLHttpRequest) {
                                        httppaternesdest = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) {
                                        httppaternesdest = new ActiveXObject("Microsoft.XMLHTTP");
                                    }
                                    
                                    httppaternesdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectpartenaire/${personns}`, true);
                                    httppaternesdest.onload = () => {
                                        const infospartenedest = JSON.parse(httppaternesdest.responseText);

                                        if (Object.entries(infospartenedest).length >= 1) 
                                        {

                                            for (let key in Object.entries(infospartenedest))
                                            {

                                                let opt = document.createElement('option');
                                                opt.value = `${infospartenedest[key].id_client}`;
                                                opt.innerHTML = `${infospartenedest[key].nom_client} ${infospartenedest[key].prenom_client}`;
                                                document.querySelector('#idpartesesc').add(opt);
                                            }
                                                
                                        }
                                        else 
                                        {
                                            document.querySelector('#idpartesesc').options.length = 1;
                                        }

                                    };
                                    httppaternesdest.setRequestHeader('Content-Type', 'application/json');
                                    httppaternesdest.send();

                                let paternstscdestin2 = document.querySelector('#idpartesesc');
                                if (paternstscdestin2 !== null)
                                paternstscdestin2.onchange = () => {
                                    let httpInfospersdestin2;
                                        httpInfospersdestin2 = new XMLHttpRequest();
                                    document.querySelector('#persodestcompagnieesc').value = '';
                                    document.querySelector('#contactidesc').style.display = 'none';
                                    document.querySelector('#idcontesc').style.display = 'none';
                                    document.querySelector('#contactidesc').value = '';
                                        var ternsdest2 = document.querySelector('#idpartesesc').
                                            options[document.querySelector('#idpartesesc').options.selectedIndex].value;
                                        httpInfospersdestin2.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest2}`, true);
                                    httpInfospersdestin2.onload = () => {
                                        const infosperdestin2 = JSON.parse(httpInfospersdestin2.responseText);
                                        
                                        if (Object.entries(infosperdestin2).length >= 1) {
                                            
                                   
                                            
                                            document.querySelector('#nomdestidesc').value = `${infosperdestin2.nom_client}`;
                                            document.querySelector('#prenomdestidesc').value = `${infosperdestin2.prenom_client}`;
                                            document.querySelector('#compagniepassdestesc').value = `${infosperdestin2.id_client}`;
                                            document.querySelector('#idclientypedestesc').value = `${infosperdestin2.type_client}`;
                                            document.querySelector('#rclientcpdestesc').value = `${infosperdestin2.nom_client}`;
                                            document.querySelector('#prnclientcpdestesc').value = `${infosperdestin2.prenom_client}`;
                                            document.querySelector('#date_cnibdestidesc').value = `${httpInfospersdestin2.date_delivre}`;
                                        } 
                                        else
                                        {
                                            document.querySelector('#nomdestidesc').value = "";
                                            document.querySelector('#prenomdestidesc').value = "";
                                            document.querySelector('#compagniepassdestesc').value = "";
                                            document.querySelector('#rclientcpdestesc').value = "";
                                            document.querySelector('#prnclientcpdestesc').value = "";
                                            document.querySelector('#idclientypedestesc').value = "";
                                            document.querySelector('#date_cnibdestidesc').value = "";
                                        }
                                                                    
                                    };
                                    httpInfospersdestin2.setRequestHeader('Content-Type', 'application/json');
                                    httpInfospersdestin2.send();
                                };
                   
                            
                            }
                            
                        }
                        
                }
        e.onclick = function () {
            let coordForm = document.querySelector('#coordFormesc');
            coordForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/addordesc/${e.dataset.cle_compagnie}`);
        }

            var clique = true;

            $('#bottonesc').click(function(event) 
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
;
/* --- adpartcoursescale.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adpartcoursescale').forEach(function (e) 
    {       

            let arcour = document.querySelector('#arrscourpartoesc');
            if (arcour !== null)
            arcour.onchange = () => {
                document.querySelector('#date_depheurecourexpartoesc').value = '';
                document.querySelector('#hdepcourpartoesc').options.length = 1;
                document.querySelector('#quartiercourpartoesc').options.length = 1;
                const garedepartcour = document.querySelector('#deparcourpartoesc').value;
                const garearriv = document.querySelector('#arrscourpartoesc').value;
                var post_ar = garearriv.split('/');
                var seltar = post_ar[0];
                var sougidarr = post_ar[1];
                let httptypequart;
                httptypequart = new XMLHttpRequest();
                
                httptypequart.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${seltar}`, true);
                httptypequart.onload = () => 
                {
                    const courqua = JSON.parse(httptypequart.responseText);
                    if (courqua == '') {
                        document.querySelector('#quartiercourpartoesc').options.length = 1;
                    }
                    else{
                        if (Object.entries(courqua).length >= 1) {
                                        
                            for (let key in Object.entries(courqua)) {
                                let opt = document.createElement('option');
                                opt.value = `${courqua[key].code_quart}/${courqua[key].nom_quartier}`;
                                opt.innerHTML = `${courqua[key].nom_quartier}/${courqua[key].code_quart}`;
                                document.querySelector('#quartiercourpartoesc').add(opt);
                            }
                        } else {
                            document.querySelector('#quartiercourpartoesc').options.length = 1;
                        }
                    }
                    

                };
                httptypequart.setRequestHeader('Content-Type', 'application/json');
                httptypequart.send();
            };
            let dpcourex = document.querySelector('#date_depheurecourexpartoesc');
            if (dpcourex !== null)
               dpcourex.onchange = () => {

                    const dateactuex = document.querySelector('#dateactpartoesc').value;
                    const garearriveex = document.querySelector('#arrscourpartoesc').value;
                    const progdepartex = document.querySelector('#date_depheurecourexpartoesc').value;
                    const garedepartcourex = document.querySelector('#deparcourpartoesc').value;
                    document.querySelector('#hdepcourpartoesc').options.length = 1;
                    var post_lhdepex = garedepartcourex.split('/');
                    var seltdepex = post_lhdepex[0];
                    var sougidex = post_lhdepex[1];
                    var post_arrex = garearriveex.split('/');
                    var seltarrex = post_arrex[0];
                    var sougidarex = post_arrex[1];
                    if(progdepartex >= dateactuex)
                    {

                            let httpRequtcourex;

                                if (window.XMLHttpRequest) {
                                    httpRequtcourex = new XMLHttpRequest();
                                } else if (window.ActiveXObject) {
                                    httpRequtcourex = new ActiveXObject("Microsoft.XMLHTTP");
                                }

                            const reponsecourex = httpRequtcourex.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheure1/${seltdepex}-${seltarrex}/${progdepartex}`, true);
                            httpRequtcourex.onload = () => 
                            {
                                const infoscourex = JSON.parse(httpRequtcourex.responseText);
                                    document.querySelector('#smsdtcrpartoesc').style.display = 'none';
                                    document.querySelector('#date_depheurecourexpartoesc').style.color = "black";
                                    document.querySelector('#date_depheurecourexpartoesc').style.border = "1px solid"; 

                                if(infoscourex == '')
                                {
                                

                                }
                                else
                                {
                                    if (Object.entries(infoscourex).length >= 1) {
                                   
                                        for (let key in Object.entries(infoscourex)) {
                                            let opt = document.createElement('option');
                                            opt.value = `${infoscourex[key].id_ligneheure}`;
                                            opt.innerHTML = `${infoscourex[key].heure}`;
                                            document.querySelector('#hdepcourpartoesc').add(opt);
                                        }
                                    } else {
                                        document.querySelector('#hdepcourpartoesc').options.length = 1;
                                    }
                                } 
                                
                            };
                            httpRequtcourex.setRequestHeader('Content-Type', 'application/json');
                            httpRequtcourex.send();
                
                    }
                    else
                    {
                        document.querySelector('#date_depheurecourexpartoesc').style.color = "#FF0000";
                        document.querySelector('#date_depheurecourexpartoesc').style.border = "2px solid #FF0000";
                        document.querySelector('#smsdtcrpartoesc').style.display = 'block';
                        document.querySelector('#erreurSmsdtcrpartoesc').innerHTML = `Date non valide.`;
                    }
                };

               let typers = document.querySelector('#type_personpartoesc');
                if (typers !== null)
                typers.onchange = () => {

                    var typersopers = document.querySelector('#type_personpartoesc').
                        options[document.querySelector('#type_personpartoesc').options.selectedIndex].value;
                        var typerso1pers = typersopers.split('/');
                        var typerso2pers = typerso1pers[0];
                        var typerso3pers = typerso1pers[1];

                        document.querySelector('#types_courrierspartoesc').options.length = 1;
                        document.querySelector('#partenairespartoesc').options.length = 1;      
                        document.querySelector('#idvalepartoesc').style.display = 'block';
                        document.querySelector('#valeur1partoesc').style.display = 'block';
                        document.querySelector('#idfraispartoesc').style.display = 'block';
                        document.querySelector('#fraisexpartoesc').style.display = 'block';
                        document.querySelector('#exp_nompartoesc').value = "";
                        document.querySelector('#exp_prenompartoesc').value = "";
                        document.querySelector('#cnib_exppartoesc').value = "";
                        document.querySelector('#iddate_cnibpartoesc').value = "";
                        document.querySelector('#lieudelexppartoesc').value = "";
                        document.querySelector('#passcompagniepartoesc').value = "";
                        document.querySelector('#rclientcpexppartoesc').value = "";
                        document.querySelector('#prnclientcpexppartoesc').value = "";
                        document.querySelector('#cnibcpexppartoesc').value = "";
                        document.querySelector('#date_cnibcpexppartoesc').value = "";
                        document.querySelector('#lieudelivrecpexppartoesc').value = "";
                        document.querySelector('#idclientypeexppartoesc').value = "";
                    
                    if((typerso3pers === 'partenaire_specifique') || (typerso3pers === 'partenaire_client') || (typerso3pers === 'partenaire_simple'))
                    {


                        document.querySelector('#partidpartoesc').style.display = 'block';
                        document.querySelector('#partenairespartoesc').style.display = 'block';
                        document.querySelector('#idvalepartoesc').style.display = 'block';
                        document.querySelector('#valeur1partoesc').style.display = 'block';
                        document.querySelector('#idfraispartoesc').style.display = 'block';
                        document.querySelector('#fraisexpartoesc').style.display = 'block';
                        document.querySelector('#fraisexpartoesc').value = '';
                        
                        let httppaterns;
                            if (window.XMLHttpRequest) {
                                httppaterns = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httppaterns = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                            httppaterns.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectpartenaire/${typerso3pers}`, true);
                            httppaterns.onload = () => {
                                const infosparten = JSON.parse(httppaterns.responseText);

                                if (Object.entries(infosparten).length >= 1) 
                                {

                                    for (let key in Object.entries(infosparten))
                                    {

                                        let opt = document.createElement('option');
                                        opt.value = `${infosparten[key].id_client}`;
                                        opt.innerHTML = `${infosparten[key].nom_client} ${infosparten[key].prenom_client}`;
                                        document.querySelector('#partenairespartoesc').add(opt);
                                    }
                                        
                                }
                                else 
                                {
                                    document.querySelector('#partenairespartoesc').options.length = 1;
                                }

                            };
                            httppaterns.setRequestHeader('Content-Type', 'application/json');
                            httppaterns.send();
                        
                    }
                    
                    
                };
                let paternstscd = document.querySelector('#partenairespartoesc');
                if (paternstscd !== null)
                paternstscd.onchange = () => {
                    let httpRequesttespers;
                        httpRequesttespers = new XMLHttpRequest();
                        document.querySelector('#types_courrierspartoesc').options.length = 1;
                        var terns= document.querySelector('#partenairespartoesc').
                        options[document.querySelector('#partenairespartoesc').options.selectedIndex].value;
                        httpRequesttespers.open('GET', window.location.origin + `${APP_ROOT}/confirmation/fetch_typecourrier/${terns}`, true);
                        httpRequesttespers.onload = () => {
                            const dataperso = JSON.parse(httpRequesttespers.responseText);
                            if(dataperso == ''){
                                
                                let httpRequesttesperso;
                                httpRequesttesperso = new XMLHttpRequest();

                                    
                                httpRequesttesperso.open('GET', window.location.origin + `${APP_ROOT}/confirmation/fetch_typecourriers`, true);
                                httpRequesttesperso.onload = () => {
                                    const datapersos = JSON.parse(httpRequesttesperso.responseText);
                                         if (Object.entries(datapersos).length >= 1) {
                                       
                                            for (let key in Object.entries(datapersos)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${datapersos[key].id_cat}/${datapersos[key].categ}/${datapersos[key].indicatif}`;
                                                opt.innerHTML = `${datapersos[key].categ}`;
                                                document.querySelector('#types_courrierspartoesc').add(opt);
                                            }
                                        } else {
                                            document.querySelector('#types_courrierspartoesc').options.length = 1;
                                        }
                                };
                        
                                httpRequesttesperso.setRequestHeader('Content-Type', 'application/json');
                                httpRequesttesperso.send();
                                        
                            }else
                            {
                                if (Object.entries(dataperso).length >= 1) {
                               
                                    for (let key in Object.entries(dataperso)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${dataperso[key].id_cat}/${dataperso[key].categ}/${dataperso[key].indicatif}`;
                                        opt.innerHTML = `${dataperso[key].categ}`;
                                        document.querySelector('#types_courrierspartoesc').add(opt);
                                    }
                                } else {
                                    document.querySelector('#types_courrierspartoesc').options.length = 1;
                                }
                            }
                        };
                
                        httpRequesttespers.setRequestHeader('Content-Type', 'application/json');
                        httpRequesttespers.send();
                };

                
                let tscd = document.querySelector('#types_courrierspartoesc');
                if (tscd !== null)
                tscd.onchange = () => {
                        let httpRequesttes;
    
                        if (window.XMLHttpRequest) {
                            httpRequesttes = new XMLHttpRequest();
                        } else if (window.ActiveXObject) {
                            httpRequesttes = new ActiveXObject("Microsoft.XMLHTTP");
                        }

                        var typerso = document.querySelector('#type_personpartoesc').
                        options[document.querySelector('#type_personpartoesc').options.selectedIndex].value;
                        var typerso1 = typerso.split('/');
                        var typerso2 = typerso1[0];
                        var typerso3 = typerso1[1];

                        var selectorscd = document.querySelector('#types_courrierspartoesc').
                        options[document.querySelector('#types_courrierspartoesc').options.selectedIndex].value;
                        
                        var nat = selectorscd.split('/');
                        var natid = nat[0];
                        var natu = nat[1];

                        var natid1 = natu.split('/');
                        var natu1 = natid1[0];
                        var natu2 = natid1[0];

                        const departdirectioncour = document.querySelector('#deparcourpartoesc').value;
                        var post_lhcour = departdirectioncour.split('/');
                        var seltdepcour = post_lhcour[0];
                        var sougidcour = post_lhcour[1];
                        const directioncour = document.querySelector('#arrscourpartoesc').value;
                        var directar = directioncour.split('/');
                        var directarg = directar[0];
                        var directarcd = directar[1];
                        httpRequesttes.open('GET', window.location.origin + `${APP_ROOT}/confirmation/fetch_mont/${natid}/${seltdepcour}-${directarg}/${typerso2}`, true);
                        httpRequesttes.onload = () => {
                            const data = JSON.parse(httpRequesttes.responseText);
                            if(data == null){
                                
        
                            }else
                            {
                                if (Object.entries(data).length >= 1) {
                                
                                    document.querySelector('#val1partoesc').value = `${data.val1}`;
                                    document.querySelector('#val2partoesc').value = `${data.val2}`;
                                    document.querySelector('#montantpartoesc').value = `${data.montant}`;
                                    document.querySelector('#intervpartoesc').value = `${data.id_inter}`

                                } 
                            }
                        };
                
                        httpRequesttes.setRequestHeader('Content-Type', 'application/json');
                        httpRequesttes.send();
     

                        if(typerso3 === 'partenaire_client' || typerso3 === 'partenaire_simple'){
                            document.querySelector('#idvalepartoesc').style.display = 'block';
                            document.querySelector('#valeur1partoesc').style.display = 'block';
                            document.querySelector('#idfraispartoesc').style.display = 'block';
                            document.querySelector('#fraisexpartoesc').style.display = 'block';
                            document.querySelector('#exp_prenompartoesc').value = "";
                            document.querySelector('#cnib_exppartoesc').value = "";
                            document.querySelector('#iddate_cnibpartoesc').value = "";
                            document.querySelector('#lieudelexppartoesc').value = "";
                            document.querySelector('#passcompagniepartoesc').value = "";
                            document.querySelector('#rclientcpexppartoesc').value = "";
                            document.querySelector('#prnclientcpexppartoesc').value = "";
                            document.querySelector('#cnibcpexppartoesc').value = "";
                            document.querySelector('#date_cnibcpexppartoesc').value = "";
                            document.querySelector('#lieudelivrecpexppartoesc').value = "";
                            document.querySelector('#idclientypeexppartoesc').value = "";
                            let httpInfoscl;
                            if (window.XMLHttpRequest) {
                                httpInfoscl = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfoscl = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            var idclit = document.querySelector('#partenairespartoesc').options[document.querySelector('#partenairespartoesc').options.selectedIndex].value;
                
                            httpInfoscl.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${idclit}`, true);
                            httpInfoscl.onload = () => {
                                const infosc = JSON.parse(httpInfoscl.responseText);
                                
                                    if (Object.entries(infosc).length >= 1) {
                                        
                                        document.querySelector('#exp_nompartoesc').value = `${infosc.nom_client}`;
                                        document.querySelector('#exp_prenompartoesc').value = `${infosc.prenom_client}`;
                                        document.querySelector('#cnib_exppartoesc').value = `${infosc.num_CNIB}`;
                                        document.querySelector('#iddate_cnibpartoesc').value = `${infosc.date_delivre}`;
                                        document.querySelector('#lieudelexppartoesc').value = `${infosc.lieu_delivre}`;
                                        document.querySelector('#passcompagniepartoesc').value = `${infosc.id_client}`;
                                        document.querySelector('#rclientcpexppartoesc').value = `${infosc.nom_client}`;
                                        document.querySelector('#prnclientcpexppartoesc').value = `${infosc.prenom_client}`;
                                        document.querySelector('#cnibcpexppartoesc').value = `${infosc.num_CNIB}`;
                                        document.querySelector('#date_cnibcpexppartoesc').value = `${infosc.date_delivre}`;
                                        document.querySelector('#lieudelivrecpexppartoesc').value = `${infosc.lieu_delivre}`;
                                        document.querySelector('#idclientypeexppartoesc').value = `${infosc.type_client}`;
                                      
                                        
                                    } 
                                    else
                                    {
                                        document.querySelector('#exp_nompartoesc').value = "";
                                        document.querySelector('#exp_prenompartoesc').value = "";
                                        document.querySelector('#cnib_exppartoesc').value = "";
                                        document.querySelector('#iddate_cnibpartoesc').value = "";
                                        document.querySelector('#lieudelexppartoesc').value = "";
                                        document.querySelector('#passcompagniepartoesc').value = "";
                                        document.querySelector('#rclientcpexppartoesc').value = "";
                                        document.querySelector('#prnclientcpexppartoesc').value = "";
                                        document.querySelector('#cnibcpexppartoesc').value = "";
                                        document.querySelector('#date_cnibcpexppartoesc').value = "";
                                        document.querySelector('#lieudelivrecpexppartoesc').value = "";
                                        document.querySelector('#idclientypeexppartoesc').value = "";
                                      
                                        
                                    }
                                                            
                            };
                            httpInfoscl.setRequestHeader('Content-Type', 'application/json');
                            httpInfoscl.send();
                            
                        }

                        if(typerso3 === 'partenaire_specifique'){
                            document.querySelector('#idvalepartoesc').style.display = 'none';
                            document.querySelector('#valeur1partoesc').style.display = 'none';
                            document.querySelector('#idfraispartoesc').style.display = 'none';
                            document.querySelector('#fraisexpartoesc').style.display = 'none';
                            document.querySelector('#fraisexpartoesc').value = 0;
                            document.querySelector('#exp_prenompartoesc').value = "";
                            document.querySelector('#cnib_exppartoesc').value = "";
                            document.querySelector('#iddate_cnibpartoesc').value = "";
                            document.querySelector('#lieudelexppartoesc').value = "";
                            document.querySelector('#passcompagniepartoesc').value = "";
                            document.querySelector('#rclientcpexppartoesc').value = "";
                            document.querySelector('#prnclientcpexppartoesc').value = "";
                            document.querySelector('#cnibcpexppartoesc').value = "";
                            document.querySelector('#date_cnibcpexppartoesc').value = "";
                            document.querySelector('#lieudelivrecpexppartoesc').value = "";
                            document.querySelector('#idclientypeexppartoesc').value = "";
                            let httpInfoscl2;
                            if (window.XMLHttpRequest) {
                                httpInfoscl2 = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfoscl2 = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            var idclit2 = document.querySelector('#partenairespartoesc').options[document.querySelector('#partenairespartoesc').options.selectedIndex].value;
                
                            httpInfoscl2.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${idclit2}`, true);
                            httpInfoscl2.onload = () => {
                                const infosc2 = JSON.parse(httpInfoscl2.responseText);
                                
                                    if (Object.entries(infosc2).length >= 1) {
                                        
                                        document.querySelector('#exp_nompartoesc').value = `${infosc2.nom_client}`;
                                        document.querySelector('#exp_prenompartoesc').value = `${infosc2.prenom_client}`;
                                        document.querySelector('#cnib_exppartoesc').value = `${infosc2.num_CNIB}`;
                                        document.querySelector('#iddate_cnibpartoesc').value = `${infosc2.date_delivre}`;
                                        document.querySelector('#lieudelexppartoesc').value = `${infosc2.lieu_delivre}`;
                                        document.querySelector('#passcompagniepartoesc').value = `${infosc2.id_client}`;
                                        document.querySelector('#rclientcpexppartoesc').value = `${infosc2.nom_client}`;
                                        document.querySelector('#prnclientcpexppartoesc').value = `${infosc2.prenom_client}`;
                                        document.querySelector('#cnibcpexppartoesc').value = `${infosc2.num_CNIB}`;
                                        document.querySelector('#date_cnibcpexppartoesc').value = `${infosc2.date_delivre}`;
                                        document.querySelector('#lieudelivrecpexppartoesc').value = `${infosc2.lieu_delivre}`;
                                        document.querySelector('#idclientypeexppartoesc').value = `${infosc2.type_client}`;
                                      
                                        
                                    } 
                                    else
                                    {
                                        document.querySelector('#exp_nompartoesc').value = "";
                                        document.querySelector('#exp_prenompartoesc').value = "";
                                        document.querySelector('#cnib_exppartoesc').value = "";
                                        document.querySelector('#iddate_cnibpartoesc').value = "";
                                        document.querySelector('#lieudelexppartoesc').value = "";
                                        document.querySelector('#passcompagniepartoesc').value = "";
                                        document.querySelector('#rclientcpexppartoesc').value = "";
                                        document.querySelector('#prnclientcpexppartoesc').value = "";
                                        document.querySelector('#cnibcpexppartoesc').value = "";
                                        document.querySelector('#date_cnibcpexppartoesc').value = "";
                                        document.querySelector('#lieudelivrecpexppartoesc').value = "";
                                        document.querySelector('#idclientypeexppartoesc').value = "";
                                      
                                        
                                    }
                                                            
                            };
                            httpInfoscl2.setRequestHeader('Content-Type', 'application/json');
                            httpInfoscl2.send();
                            
                        }
                        
                };

                   
                let infopersos = document.querySelector('#idtypepartoesc');
        
                if (infopersos !== null) 
                infopersos.onchange = () => 
                {
                
                    document.querySelector('#contactidpartoesc').style.display = 'none';
                    document.querySelector('#idcontpartoesc').style.display = 'none';
                    document.querySelector('#sonnelpartoesc').style.display = 'none';
                    document.querySelector('#idsonnelspartoesc').style.display = 'none';
                    document.querySelector('#idpartespartoesc').options.length = 1;
                    document.querySelector('#contactidpartoesc').value = '';
                    document.querySelector('#partcontpartoesc').style.display = 'none';
                            
                    var personns = document.querySelector('#idtypepartoesc')
                        .options[document.querySelector('#idtypepartoesc').options.selectedIndex].value;
                        if(personns === 'personnel')
                        {
                            document.querySelector('#membrepartcontesc').style.display = 'none';
                            document.querySelector('#idmembrenameesc').style.display = 'none';
                            document.querySelector('#sonnelpartoesc').style.display = 'block';
                            document.querySelector('#idsonnelspartoesc').style.display = 'block';
                            document.querySelector('#contactidpartoesc').style.display = 'none';
                            document.querySelector('#idcontpartoesc').style.display = 'none';
                            document.querySelector('#partcontpartoesc').style.display = 'none';
                            document.querySelector('#idpartespartoesc').style.display = 'none';
                            
                                    let httppersosdest;
                            if (window.XMLHttpRequest) {
                                httppersosdest = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httppersosdest = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                            httppersosdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectperso/${personns}`, true);
                            httppersosdest.onload = () => 
                            {

                                const infospersdest = JSON.parse(httppersosdest.responseText);

                                if (Object.entries(infospersdest).length >= 1) 
                                {


                                    for (let key in Object.entries(infospersdest))
                                    {

                                        let opt = document.createElement('option');
                                        opt.value = `${infospersdest[key].matricule}`;
                                        opt.innerHTML = `${infospersdest[key].nomprenom_perso}`;
                                        document.querySelector('#idsonnelspartoesc').add(opt);
                                    }
                                 
                                }
                                else 
                                {
                                    document.querySelector('#idsonnelspartoesc').options.length = 1;
                                }

                            };
                            httppersosdest.setRequestHeader('Content-Type', 'application/json');
                            httppersosdest.send();

                            let infopersosdest = document.querySelector('#idsonnelspartoesc');
        
                            if (infopersosdest !== null) 
                            infopersosdest.onchange = () => 
                            {

                            
                                let httpInfospersdest;
                                if (window.XMLHttpRequest) {
                                    httpInfospersdest = new XMLHttpRequest();
                                } else if (window.ActiveXObject) {
                                    httpInfospersdest = new ActiveXObject("Microsoft.XMLHTTP");
                                }

                                document.querySelector('#contactidpartoesc').style.display = 'none';
                                document.querySelector('#idcontpartoesc').style.display = 'none';
                                document.querySelector('#compagniepassdestpartoesc').value = '';
                                var idverifidest = document.querySelector('#idsonnelspartoesc').options[document.querySelector('#idsonnelspartoesc').options.selectedIndex].value;
                    
                                httpInfospersdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoperso/${idverifidest}`, true);
                                httpInfospersdest.onload = () => {
                                    const infosperdest = JSON.parse(httpInfospersdest.responseText);
                                    
                                    if (Object.entries(infosperdest).length >= 1) {
                                        
                               
                                        var typepersosdest = `${infosperdest.nomprenom_perso}`;
                                        var typer1persosdest = typepersosdest.split(' ');
                                        var typer2persosdest = typer1persosdest[0];
                                        var typer3persosdest = typer1persosdest[1];
                                        var typer4persosdest = typer1persosdest[2];
                                        if(typer4persosdest === undefined){
                                            var typer5persosdest = `${typer3persosdest}`;
                                        }
                                        else{
                                            var typer5persosdest = `${typer3persosdest} ${typer4persosdest}`;
                                        }
                                        document.querySelector('#nomdestidpartoesc').value = `${typer2persosdest}`;
                                        document.querySelector('#prenomdestidpartoesc').value = `${typer5persosdest}`;
                                        document.querySelector('#persodestcompagniepartoesc').value = `${infosperdest.matricule}`;
                                        document.querySelector('#rclientcpexppartoesc').value = `${typer2persosdest}`;
                                        document.querySelector('#prnclientcpexppartoesc').value = `${typer5persosdest}`;
                                        document.querySelector('#idclientypedestpartoesc').value = 'personnel';
                                        
                                    } 
                                    else
                                    {
                                        document.querySelector('#nomdestidpartoesc').value = "";
                                        document.querySelector('#prenomdestidpartoesc').value = "";
                                        document.querySelector('#persodestcompagniepartoesc').value = "";
                                        document.querySelector('#rclientcpdestpartoesc').value = "";
                                        document.querySelector('#prnclientcpdestpartoesc').value = "";
                                        document.querySelector('#idclientypedestpartoesc').value = "";
                                    }
                                                                
                                };
                                httpInfospersdest.setRequestHeader('Content-Type', 'application/json');
                                httpInfospersdest.send();
                            };
                        }
                        else
                        {
                            document.querySelector('#sonnelpartoesc').style.display = 'none';
                            document.querySelector('#idsonnelspartoesc').style.display = 'none';
                            document.querySelector('#partcontpartoesc').style.display = 'none';
                            document.querySelector('#idpartespartoesc').style.display = 'none';
                            document.querySelector('#idcontpartoesc').style.display = 'block';
                            document.querySelector('#contactidpartoesc').style.display = 'block';
                            document.querySelector('#idmatripartoesc').style.display = 'none';
                            document.querySelector('#matri_destpartoesc').style.display = 'none';
                            document.querySelector('#nomdestidpartoesc').value = "";
                            document.querySelector('#prenomdestidpartoesc').value = "";
                            document.querySelector('#compagniepassdestpartoesc').value = "";
                            document.querySelector('#rclientcpdestpartoesc').value = "";
                            document.querySelector('#prnclientcpdestpartoesc').value = "";
                            document.querySelector('#idclientypedestpartoesc').value = "";
                            document.querySelector('#idclientcontdestpartoesc').value = "";
                            document.querySelector('#membrepartcontesc').style.display = 'none';
                            document.querySelector('#idmembrenameesc').style.display = 'none';
                            
                            let infdest = document.querySelector('#contactidpartoesc');
                            if (infdest !== null)
                                infdest.onkeyup = () => {
                                    let httpInfosdest;
                                    if (window.XMLHttpRequest) {
                                        httpInfosdest = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) {
                                        httpInfosdest = new ActiveXObject("Microsoft.XMLHTTP");
                                    }

                                    document.querySelector('#nomdestidpartoesc').value = "";
                                    document.querySelector('#prenomdestidpartoesc').value = "";
                                    document.querySelector('#compagniepassdestpartoesc').value = "";
                                    document.querySelector('#rclientcpdestpartoesc').value = "";
                                    document.querySelector('#prnclientcpdestpartoesc').value = "";
                                    document.querySelector('#idclientypedestpartoesc').value = "";
                                    document.querySelector('#idclientcontdestpartoesc').value = "";
                                    document.querySelector('#date_cnibdestidpartoesc').value = "";
                                    var verificatdest = document.querySelector('#contactidpartoesc').value;
                                    document.querySelector('#persodestcompagniepartoesc').value = "";

                                    httpInfosdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfos/${verificatdest}`, true);
                                    httpInfosdest.onload = () => {
                                        const infosdest = JSON.parse(httpInfosdest.responseText);
                                        if (infosdest == null) {
                                            document.querySelector('#nomdestidpartoesc').value = "";
                                            document.querySelector('#prenomdestidpartoesc').value = "";
                                            document.querySelector('#compagniepassdestpartoesc').value = "";
                                            document.querySelector('#rclientcpdestpartoesc').value = "";
                                            document.querySelector('#prnclientcpdestpartoesc').value = "";
                                            document.querySelector('#idclientypedestpartoesc').value = "";
                                            document.querySelector('#idclientcontdestpartoesc').value = "";
                                            document.querySelector('#date_cnibdestidpartoesc').value = "";
                                            
                                        } else 
                                        {
                                            if (Object.entries(infosdest).length > 1) {
                                                
                                                if (infosdest.contact_client == verificatdest) {
                                                    document.querySelector('#nomdestidpartoesc').value = `${infosdest.nom_client}`;
                                                    document.querySelector('#prenomdestidpartoesc').value = `${infosdest.prenom_client}`;
                                                    document.querySelector('#compagniepassdestpartoesc').value = `${infosdest.id_client}`;
                                                    document.querySelector('#idclientypedestpartoesc').value = `${infosdest.type_client}`;
                                                    document.querySelector('#rclientcpdestpartoesc').value = `${infosdest.nom_client}`;
                                                    document.querySelector('#prnclientcpdestpartoesc').value = `${infosdest.prenom_client}`;
                                                    document.querySelector('#idclientcontdestpartoesc').value = `${infosdest.contact_client}`;
                                                    document.querySelector('#date_cnibdestidpartoesc').value = `${infosdest.date_delivre}`;
                                                } else {
                                                    document.querySelector('#nomdestidpartoesc').value = "";
                                                    document.querySelector('#prenomdestidpartoesc').value = "";
                                                    document.querySelector('#compagniepassdestpartoesc').value = "";
                                                    document.querySelector('#rclientcpdestpartoesc').value = "";
                                                    document.querySelector('#prnclientcpdestpartoesc').value = "";
                                                    document.querySelector('#idclientypedestpartoesc').value = "";
                                                    document.querySelector('#idclientcontdestpartoesc').value = "";
                                                    document.querySelector('#date_cnibdestidpartoesc').value = "";
                                                }
                                            }
                                        }
                                    };
                                    httpInfosdest.setRequestHeader('Content-Type', 'application/json');
                                    httpInfosdest.send();
                                };
                        }

                        if(personns === 'membre'){

                                document.querySelector('#membrepartcontesc').style.display = 'block';
                                document.querySelector('#idmembrenameesc').style.display = 'block';
                                document.querySelector('#sonnelpartoesc').style.display = 'none';
                                document.querySelector('#idsonnelspartoesc').style.display = 'none';
                                document.querySelector('#idcontpartoesc').style.display = 'none';
                                document.querySelector('#contactidpartoesc').style.display = 'none';
                                document.querySelector('#partcontpartoesc').style.display = 'none';
                                document.querySelector('#idpartespartoesc').style.display = 'none';
                                
                                
                        
                                let httppaternesdestm;
                                    if (window.XMLHttpRequest) {
                                        httppaternesdestm = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) {
                                        httppaternesdestm = new ActiveXObject("Microsoft.XMLHTTP");
                                    }
                                    
                                    httppaternesdestm.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectpartenaire/${personns}`, true);
                                    httppaternesdestm.onload = () => {
                                        const infospartenedestm = JSON.parse(httppaternesdestm.responseText);

                                        if (Object.entries(infospartenedestm).length >= 1) 
                                        {

                                            for (let key in Object.entries(infospartenedestm))
                                            {

                                                let opt = document.createElement('option');
                                                opt.value = `${infospartenedestm[key].id_client}`;
                                                opt.innerHTML = `${infospartenedestm[key].nom_client} ${infospartenedestm[key].prenom_client}`;
                                                document.querySelector('#idmembrenameesc').add(opt);
                                            }
                                                
                                        }
                                        else 
                                        {
                                            document.querySelector('#idmembrenameesc').options.length = 1;
                                        }

                                    };
                                    httppaternesdestm.setRequestHeader('Content-Type', 'application/json');
                                    httppaternesdestm.send();

                                let paternstscdestin2m = document.querySelector('#idmembrenameesc');
                                if (paternstscdestin2m !== null)
                                paternstscdestin2m.onchange = () => {
                                    let httpInfospersdestin2m;
                                        httpInfospersdestin2m = new XMLHttpRequest();
                                    document.querySelector('#persodestcompagniepartoesc').value = '';
                                    document.querySelector('#contactidpartoesc').style.display = 'none';
                                    document.querySelector('#idcontpartoesc').style.display = 'none';
                                    document.querySelector('#contactidpartoesc').value = '';
                                        var ternsdest2m = document.querySelector('#idmembrenameesc').
                                            options[document.querySelector('#idmembrenameesc').options.selectedIndex].value;
                                        httpInfospersdestin2m.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest2m}`, true);
                                    httpInfospersdestin2m.onload = () => {
                                        const infosperdestin2m = JSON.parse(httpInfospersdestin2m.responseText);
                                        
                                        if (Object.entries(infosperdestin2m).length >= 1) {
                                            
                                
                                            document.querySelector('#nomdestidpartoesc').value = `${infosperdestin2m.nom_client}`;
                                            document.querySelector('#prenomdestidpartoesc').value = `${infosperdestin2m.prenom_client}`;
                                            document.querySelector('#compagniepassdestpartoesc').value = `${infosperdestin2m.id_client}`;
                                            document.querySelector('#idclientypedestpartoesc').value = `${infosperdestin2m.type_client}`;
                                            document.querySelector('#rclientcpdestpartoesc').value = `${infosperdestin2m.nom_client}`;
                                            document.querySelector('#prnclientcpdestpartoesc').value = `${infosperdestin2m.prenom_client}`;
                                            document.querySelector('#date_cnibdestidpartoesc').value = `${infosperdestin2m.date_delivre}`;
                                        } 
                                        else
                                        {
                                            document.querySelector('#nomdestidpartoesc').value = "";
                                            document.querySelector('#prenomdestidpartoesc').value = "";
                                            document.querySelector('#compagniepassdestpartoesc').value = "";
                                            document.querySelector('#rclientcpdestpartoesc').value = "";
                                            document.querySelector('#prnclientcpdestpartoesc').value = "";
                                            document.querySelector('#idclientypedestpartoesc').value = "";
                                            document.querySelector('#date_cnibdestidpartoesc').value = "";
                                        }
                                                                    
                                    };
                                    httpInfospersdestin2m.setRequestHeader('Content-Type', 'application/json');
                                    httpInfospersdestin2m.send();
                                };
                     
                        }

                        if(personns === 'partenaire_client' || personns === 'partenaire_simple'){
                            document.querySelector('#membrepartcontesc').style.display = 'none';
                            document.querySelector('#idmembrenameesc').style.display = 'none';
                            document.querySelector('#partcontpartoesc').style.display = 'block';
                            document.querySelector('#idpartespartoesc').style.display = 'block';
                            document.querySelector('#sonnelpartoesc').style.display = 'none';
                            document.querySelector('#idsonnelspartoesc').style.display = 'none';
                            document.querySelector('#contactidpartoesc').style.display = 'none';
                            document.querySelector('#idcontpartoesc').style.display = 'none';
                            document.querySelector('#nomdestidpartoesc').value = '';
                            document.querySelector('#prenomdestidpartoesc').value = '';
                            document.querySelector('#compagniepassdestpartoesc').value = '';
                            document.querySelector('#idclientypedestpartoesc').value = '';
                            document.querySelector('#rclientcpdestpartoesc').value = '';
                            document.querySelector('#prnclientcpdestpartoesc').value = '';
                            document.querySelector('#contactidpartoesc').value = '';
                            document.querySelector('#idclientcontdestpartoesc').value = '';
                            document.querySelector('#date_cnibdestidpartoesc').value = "";
                            let httppaternsdest;
                                if (window.XMLHttpRequest) {
                                    httppaternsdest = new XMLHttpRequest();
                                } else if (window.ActiveXObject) {
                                    httppaternsdest = new ActiveXObject("Microsoft.XMLHTTP");
                                }
                                
                                httppaternsdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectpartenaire/${personns}`, true);
                                httppaternsdest.onload = () => {
                                    const infospartendest = JSON.parse(httppaternsdest.responseText);

                                    if (Object.entries(infospartendest).length >= 1) 
                                    {

                                        for (let key in Object.entries(infospartendest))
                                        {

                                            let opt = document.createElement('option');
                                            opt.value = `${infospartendest[key].id_client}`;
                                            opt.innerHTML = `${infospartendest[key].nom_client} ${infospartendest[key].prenom_client}`;
                                            document.querySelector('#idpartespartoesc').add(opt);
                                        }
                                            
                                    }
                                    else 
                                    {
                                        document.querySelector('#idpartespartoesc').options.length = 1;
                                    }

                                };
                                httppaternsdest.setRequestHeader('Content-Type', 'application/json');
                                httppaternsdest.send();

                                let paternstscdestin = document.querySelector('#idpartespartoesc');
                            if (paternstscdestin !== null)
                            paternstscdestin.onchange = () => {
                                let httpInfospersdestin;
                                    httpInfospersdestin = new XMLHttpRequest();
                                document.querySelector('#persodestcompagniepartoesc').value = '';
                                document.querySelector('#contactidpartoesc').style.display = 'none';
                                document.querySelector('#idcontpartoesc').style.display = 'none';
                                document.querySelector('#contactidpartoesc').value = '';
                                var ternsdest= document.querySelector('#idpartespartoesc').
                                    options[document.querySelector('#idpartespartoesc').options.selectedIndex].value;
                                httpInfospersdestin.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest}`, true);
                                httpInfospersdestin.onload = () => {
                                    const infosperdestin = JSON.parse(httpInfospersdestin.responseText);
                                    
                                    if (Object.entries(infosperdestin).length >= 1) {
                                        
                               
                                        
                                        document.querySelector('#nomdestidpartoesc').value = `${infosperdestin.nom_client}`;
                                        document.querySelector('#prenomdestidpartoesc').value = `${infosperdestin.prenom_client}`;
                                        document.querySelector('#compagniepassdestpartoesc').value = `${infosperdestin.id_client}`;
                                        document.querySelector('#idclientypedestpartoesc').value = `${infosperdestin.type_client}`;
                                        document.querySelector('#rclientcpdestpartoesc').value = `${infosperdestin.nom_client}`;
                                        document.querySelector('#prnclientcpdestpartoesc').value = `${infosperdestin.prenom_client}`;
                                        document.querySelector('#date_cnibdestidpartoesc').value = `${infosperdestin.date_delivre}`;
                                    } 
                                    else
                                    {
                                        document.querySelector('#nomdestidpartoesc').value = "";
                                        document.querySelector('#prenomdestidpartoesc').value = "";
                                        document.querySelector('#compagniepassdestpartoesc').value = "";
                                        document.querySelector('#rclientcpdestpartoesc').value = "";
                                        document.querySelector('#prnclientcpdestpartoesc').value = "";
                                        document.querySelector('#idclientypedestpartoesc').value = "";
                                        document.querySelector('#date_cnibdestidpartoesc').value = "";
                                    }
                                                                
                                };
                                httpInfospersdestin.setRequestHeader('Content-Type', 'application/json');
                                httpInfospersdestin.send();
                            };
 
                        }
                        else
                        {
                            if(personns === 'partenaire_specifique'){

                                document.querySelector('#partcontpartoesc').style.display = 'block';
                                document.querySelector('#idpartespartoesc').style.display = 'block';
                                document.querySelector('#sonnelpartoesc').style.display = 'none';
                                document.querySelector('#idsonnelspartoesc').style.display = 'none';
                                document.querySelector('#idcontpartoesc').style.display = 'none';
                                document.querySelector('#contactidpartoesc').style.display = 'none';
                                document.querySelector('#membrepartcontesc').style.display = 'none';
                                document.querySelector('#idmembrenameesc').style.display = 'none';
                            
                                let httppaternesdest;
                                    if (window.XMLHttpRequest) {
                                        httppaternesdest = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) {
                                        httppaternesdest = new ActiveXObject("Microsoft.XMLHTTP");
                                    }
                                    
                                    httppaternesdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectpartenaire/${personns}`, true);
                                    httppaternesdest.onload = () => {
                                        const infospartenedest = JSON.parse(httppaternesdest.responseText);

                                        if (Object.entries(infospartenedest).length >= 1) 
                                        {

                                            for (let key in Object.entries(infospartenedest))
                                            {

                                                let opt = document.createElement('option');
                                                opt.value = `${infospartenedest[key].id_client}`;
                                                opt.innerHTML = `${infospartenedest[key].nom_client} ${infospartenedest[key].prenom_client}`;
                                                document.querySelector('#idpartespartoesc').add(opt);
                                            }
                                                
                                        }
                                        else 
                                        {
                                            document.querySelector('#idpartespartoesc').options.length = 1;
                                        }

                                    };
                                    httppaternesdest.setRequestHeader('Content-Type', 'application/json');
                                    httppaternesdest.send();

                                let paternstscdestin2 = document.querySelector('#idpartespartoesc');
                                if (paternstscdestin2 !== null)
                                paternstscdestin2.onchange = () => {
                                    let httpInfospersdestin2;
                                        httpInfospersdestin2 = new XMLHttpRequest();
                                    document.querySelector('#persodestcompagniepartoesc').value = '';
                                    document.querySelector('#contactidpartoesc').style.display = 'none';
                                    document.querySelector('#idcontpartoesc').style.display = 'none';
                                    document.querySelector('#contactidpartoesc').value = '';
                                        var ternsdest2 = document.querySelector('#idpartespartoesc').
                                            options[document.querySelector('#idpartespartoesc').options.selectedIndex].value;
                                        httpInfospersdestin2.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest2}`, true);
                                    httpInfospersdestin2.onload = () => {
                                        const infosperdestin2 = JSON.parse(httpInfospersdestin2.responseText);
                                        
                                        if (Object.entries(infosperdestin2).length >= 1) {
                                
                                            document.querySelector('#nomdestidpartoesc').value = `${infosperdestin2.nom_client}`;
                                            document.querySelector('#prenomdestidpartoesc').value = `${infosperdestin2.prenom_client}`;
                                            document.querySelector('#compagniepassdestpartoesc').value = `${infosperdestin2.id_client}`;
                                            document.querySelector('#idclientypedestpartoesc').value = `${infosperdestin2.type_client}`;
                                            document.querySelector('#rclientcpdestpartoesc').value = `${infosperdestin2.nom_client}`;
                                            document.querySelector('#prnclientcpdestpartoesc').value = `${infosperdestin2.prenom_client}`;
                                            document.querySelector('#date_cnibdestidpartoesc').value = `${infosperdestin2.date_delivre}`;
                                        } 
                                        else
                                        {
                                            document.querySelector('#nomdestidpartoesc').value = "";
                                            document.querySelector('#prenomdestidpartoesc').value = "";
                                            document.querySelector('#compagniepassdestpartoesc').value = "";
                                            document.querySelector('#rclientcpdestpartoesc').value = "";
                                            document.querySelector('#prnclientcpdestpartoesc').value = "";
                                            document.querySelector('#idclientypedestpartoesc').value = "";
                                            document.querySelector('#date_cnibdestidpartoesc').value = "";
                                        }
                                                                    
                                    };
                                    httpInfospersdestin2.setRequestHeader('Content-Type', 'application/json');
                                    httpInfospersdestin2.send();
                                };
                   
                            }   
                        }
                    }
        e.onclick = function () {
            let copartoForm = document.querySelector('#copartoFormesc');
            copartoForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/addpartoesc/${e.dataset.cle_compagnie}`);
        }

        var clique = true;

            $('#bottonpartoesc').click(function(event) 
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
;
/* --- adperscoursescale.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adperscoursescale').forEach(function (e) 
    {
       
            let arcourpers = document.querySelector('#arrscourpersoesc');
            if (arcourpers !== null)
            arcourpers.onchange = () => {
                document.querySelector('#date_depheurecourexpersoesc').value = '';
                document.querySelector('#hdepcourpersoesc').options.length = 1;
                document.querySelector('#quartiercourpersoesc').options.length = 1;
                const garedepartcourpers = document.querySelector('#deparcourpersoesc').value;
                const garearrivpers = document.querySelector('#arrscourpersoesc').value;
                var post_arpers = garearrivpers.split('/');
                var seltarpers = post_arpers[0];
                var sougidarrpers = post_arpers[1];
                let httptypequartpers;
                httptypequartpers = new XMLHttpRequest();
                
                httptypequartpers.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${seltarpers}`, true);
                httptypequartpers.onload = () => 
                {
                    const courquapers = JSON.parse(httptypequartpers.responseText);
                    if (courquapers == '') {
                        document.querySelector('#quartiercourpersoesc').options.length = 1;
                    }
                    else{
                        if (Object.entries(courquapers).length >= 1) {
                                        
                            for (let key in Object.entries(courquapers)) {
                                let opt = document.createElement('option');
                                opt.value = `${courquapers[key].code_quart}/${courquapers[key].nom_quartier}`;
                                opt.innerHTML = `${courquapers[key].nom_quartier}/${courquapers[key].code_quart}`;
                                document.querySelector('#quartiercourpersoesc').add(opt);
                            }
                        } else {
                            document.querySelector('#quartiercourpersoesc').options.length = 1;
                        }
                    }
                    

                };
                httptypequartpers.setRequestHeader('Content-Type', 'application/json');
                httptypequartpers.send();
            };
            let dpcourexpers = document.querySelector('#date_depheurecourexpersoesc');
            if (dpcourexpers !== null)
               dpcourexpers.onchange = () => {

                    const dateactuexpers = document.querySelector('#dateactpersoesc').value;
                    const garearriveexpers = document.querySelector('#arrscourpersoesc').value;
                    const progdepartexpers = document.querySelector('#date_depheurecourexpersoesc').value;
                    const garedepartcourexpers = document.querySelector('#deparcourpersoesc').value;
                    document.querySelector('#hdepcourpersoesc').options.length = 1;
                    var post_lhdepexpers = garedepartcourexpers.split('/');
                    var seltdepexpers = post_lhdepexpers[0];
                    var sougidexpers = post_lhdepexpers[1];
                    var post_arrexpers = garearriveexpers.split('/');
                    var seltarrexpers = post_arrexpers[0];
                    var sougidarexpers = post_arrexpers[1];
                    if(progdepartexpers >= dateactuexpers)
                    {
                                                 
                        
                        let Requestitinespers;
                        Requestitinespers = new XMLHttpRequest();
                        Requestitinespers.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheure1/${seltdepexpers}-${seltarrexpers}/${progdepartexpers}`, true);
                            Requestitinespers.onload = () => 
                            {
                                const infoscourrspers = JSON.parse(Requestitinespers.responseText);
                                    document.querySelector('#smsdtcrpersoesc').style.display = 'none';
                                    
                                if(infoscourrspers == ''){

                                }
                                else
                                {
                                    if (Object.entries(infoscourrspers).length >= 1) {
                                   
                                        for (let key in Object.entries(infoscourrspers)) {
                                            let opt = document.createElement('option');
                                            opt.value = `${infoscourrspers[key].id_ligneheure}`;
                                            opt.innerHTML = `${infoscourrspers[key].heure}`;
                                            document.querySelector('#hdepcourpersoesc').add(opt);
                                        }
                                    } else {
                                        document.querySelector('#hdepcourpersoesc').options.length = 1;
                                    }
                                }
                                        
                            };
                            Requestitinespers.setRequestHeader('Content-Type', 'application/json');
                            Requestitinespers.send();
                
                    }
                    else
                    {
                        document.querySelector('#date_depheurecourexpersoesc').style.color = "#FF0000";
                        document.querySelector('#date_depheurecourexpersoesc').style.border = "2px solid #FF0000";
                        document.querySelector('#smsdtcrpersoesc').style.display = 'block';
                        document.querySelector('#erreurSmsdtcrpersoesc').innerHTML = `Date non valide.`;
                    }
                };
               
               let typerspers = document.querySelector('#type_personpersoesc');
                if (typerspers !== null)
                typerspers.onchange = () => {

                    var typersoperspers = document.querySelector('#type_personpersoesc').
                        options[document.querySelector('#type_personpersoesc').options.selectedIndex].value;
                        var typerso1perspers = typersoperspers.split('/');
                        var typerso2perspers = typerso1perspers[0];
                        var typerso3perspers = typerso1perspers[1];

                        document.querySelector('#types_courrierspersoesc').options.length = 1;
                        document.querySelector('#personidpersoesc').options.length = 1;
                        document.querySelector('#exp_nompersoesc').value = "";
                        document.querySelector('#exp_prenompersoesc').value = "";
                        document.querySelector('#cnib_exppersoesc').value = "";
                        document.querySelector('#iddate_cnibpersoesc').value = "";
                        document.querySelector('#lieudelexppersoesc').value = "";
                        document.querySelector('#rclientcpexppersoesc').value = "";
                        document.querySelector('#prnclientcpexppersoesc').value = "";
                        document.querySelector('#cnibcpexppersoesc').value = "";
                        document.querySelector('#date_cnibcpexppersoesc').value = "";
                        document.querySelector('#lieudelivrecpexppersoesc').value = "";
                        document.querySelector('#idclientypeexppersoesc').value = "";
                    
                    
                        let httppersospers;
                            if (window.XMLHttpRequest) {
                                httppersospers = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httppersospers = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                            httppersospers.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectperso/${typerso3perspers}`, true);
                            httppersospers.onload = () => 
                            {

                                const infosperspers = JSON.parse(httppersospers.responseText);

                                if (Object.entries(infosperspers).length >= 1) 
                                {


                                    for (let key in Object.entries(infosperspers))
                                    {

                                        let opt = document.createElement('option');
                                        opt.value = `${infosperspers[key].matricule}`;
                                        opt.innerHTML = `${infosperspers[key].nomprenom_perso}`;
                                        document.querySelector('#personidpersoesc').add(opt);
                                    }
                                       
                                       let httpRequesttesperso2pers;
                                        httpRequesttesperso2pers = new XMLHttpRequest();

                                            
                                        httpRequesttesperso2pers.open('GET', window.location.origin + `${APP_ROOT}/confirmation/fetch_typecourriers`, true);
                                        httpRequesttesperso2pers.onload = () => {
                                            const datapersos2pers = JSON.parse(httpRequesttesperso2pers.responseText);
                                                 if (Object.entries(datapersos2pers).length >= 1) {
                                               
                                                    for (let key in Object.entries(datapersos2pers)) {
                                                        let opt = document.createElement('option');
                                                        opt.value = `${datapersos2pers[key].id_cat}/${datapersos2pers[key].categ}/${datapersos2pers[key].indicatif}`;
                                                        opt.innerHTML = `${datapersos2pers[key].categ}`;
                                                        document.querySelector('#types_courrierspersoesc').add(opt);
                                                    }
                                                } else {
                                                    document.querySelector('#types_courrierspersoesc').options.length = 1;
                                                }
                                        };
                        
                                        httpRequesttesperso2pers.setRequestHeader('Content-Type', 'application/json');
                                        httpRequesttesperso2pers.send(); 
                                }
                                else 
                                {
                                    document.querySelector('#personidpersoesc').options.length = 1;
                                }

                            };
                            httppersospers.setRequestHeader('Content-Type', 'application/json');
                            httppersospers.send();
                    
                };
                
                let tscdpers = document.querySelector('#types_courrierspersoesc');
                if (tscdpers !== null)
                tscdpers.onchange = () => {
                        let httpRequesttespers;
    
                        if (window.XMLHttpRequest) {
                            httpRequesttespers = new XMLHttpRequest();
                        } else if (window.ActiveXObject) {
                            httpRequesttespers = new ActiveXObject("Microsoft.XMLHTTP");
                        }

                        var typersopers = document.querySelector('#type_personpersoesc').
                        options[document.querySelector('#type_personpersoesc').options.selectedIndex].value;
                        var typerso1pers = typersopers.split('/');
                        var typerso2pers = typerso1pers[0];
                        var typerso3pers = typerso1pers[1];

                        
                            document.querySelector('#exp_nompersoesc').value = "";
                            document.querySelector('#exp_prenompersoesc').value = "";
                            document.querySelector('#cnib_exppersoesc').value = "";
                            document.querySelector('#iddate_cnibpersoesc').value = "";
                            document.querySelector('#lieudelexppersoesc').value = "";
                            document.querySelector('#rclientcpexppersoesc').value = "";
                            document.querySelector('#prnclientcpexppersoesc').value = "";
                            document.querySelector('#cnibcpexppersoesc').value = "";
                            document.querySelector('#date_cnibcpexppersoesc').value = "";
                            document.querySelector('#lieudelivrecpexppersoesc').value = "";
                            document.querySelector('#idclientypeexppersoesc').value = "";
                            let httpInfosperspers;
                            if (window.XMLHttpRequest) {
                                httpInfosperspers = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosperspers = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            var idverifipers = document.querySelector('#personidpersoesc').options[document.querySelector('#personidpersoesc').options.selectedIndex].value;
                
                            httpInfosperspers.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoperso/${idverifipers}`, true);
                            httpInfosperspers.onload = () => {
                                const infosperpers = JSON.parse(httpInfosperspers.responseText);
                                
                                    if (Object.entries(infosperpers).length >= 1) {
                                        
                               
                                        var typepersospers = `${infosperpers.nomprenom_perso}`;
                                        var typer1persospers = typepersospers.split(' ');
                                        var typer2persospers = typer1persospers[0];
                                        var typer3persospers = typer1persospers[1];
                                        var typer4persospers = typer1persospers[2];
                                        if(typer4persospers === undefined){
                                            var typer5persospers = `${typer3persospers}`;
                                        }
                                        else{
                                            var typer5persospers = `${typer3persospers} ${typer4persospers}`;
                                        }
                                        document.querySelector('#exp_nompersoesc').value = `${typer2persospers}`;
                                        document.querySelector('#exp_prenompersoesc').value = `${typer5persospers}`;
                                        document.querySelector('#cnib_exppersoesc').value = `${infosperpers.pieces2}`;
                                        document.querySelector('#iddate_cnibpersoesc').value = `${infosperpers.date_delivre2}`;
                                        document.querySelector('#persocompagniepersoesc').value = `${infosperpers.matricule}`;
                                        document.querySelector('#rclientcpexppersoesc').value = `${typer2persospers}`;
                                        document.querySelector('#prnclientcpexppersoesc').value = `${typer5persospers}`;
                                        document.querySelector('#cnibcpexppersoesc').value = `${infosperpers.pieces2}`;
                                        document.querySelector('#date_cnibcpexppersoesc').value = `${infosperpers.date_delivre2}`;
                                        document.querySelector('#idclientypeexppersoesc').value = 'personnel';
                                        
                                    } 
                                                           
                            };
                            httpInfosperspers.setRequestHeader('Content-Type', 'application/json');
                            httpInfosperspers.send();
                            
                };

                   
                let infopersospers = document.querySelector('#idtypepersoesc');
        
                if (infopersospers !== null) 
                infopersospers.onchange = () => 
                {
                
                    document.querySelector('#contactidpersoesc').style.display = 'none';
                    document.querySelector('#idcontpersoesc').style.display = 'none';
                    document.querySelector('#sonnelpersoesc').style.display = 'none';
                    document.querySelector('#idsonnelspersoesc').style.display = 'none';
                    document.querySelector('#idpartespersoesc').options.length = 1;
                    document.querySelector('#contactidpersoesc').value = '';
                    document.querySelector('#partcontpersoesc').style.display = 'none';
                            
                    var personns = document.querySelector('#idtypepersoesc')
                        .options[document.querySelector('#idtypepersoesc').options.selectedIndex].value;
                        if(personns === 'personnel')
                        {
                    
                            document.querySelector('#sonnelpersoesc').style.display = 'block';
                            document.querySelector('#idsonnelspersoesc').style.display = 'block';
                            document.querySelector('#contactidpersoesc').style.display = 'none';
                            document.querySelector('#idcontpersoesc').style.display = 'none';
                            document.querySelector('#partcontpersoesc').style.display = 'none';
                            document.querySelector('#idpartespersoesc').style.display = 'none';
                            document.querySelector('#sonnelpersomemesc').style.display = 'none';
                            document.querySelector('#idsonnelspersomemesc').style.display = 'none';
                            document.querySelector('#idsonnelspersomemesc').options.length = 1;
                            document.querySelector('#idpartespersoesc').options.length = 1;


                                
                            let httppersosdestpers;
                            if (window.XMLHttpRequest) {
                                httppersosdestpers = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httppersosdestpers = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                            httppersosdestpers.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectperso/${personns}`, true);
                            httppersosdestpers.onload = () => 
                            {

                                const infospersdestpers = JSON.parse(httppersosdestpers.responseText);

                                if (Object.entries(infospersdestpers).length >= 1) 
                                {


                                    for (let key in Object.entries(infospersdestpers))
                                    {

                                        let opt = document.createElement('option');
                                        opt.value = `${infospersdestpers[key].matricule}`;
                                        opt.innerHTML = `${infospersdestpers[key].nomprenom_perso}`;
                                        document.querySelector('#idsonnelspersoesc').add(opt);
                                    }
                                 
                                }
                                else 
                                {
                                    document.querySelector('#idsonnelspersoesc').options.length = 1;
                                }

                            };
                            httppersosdestpers.setRequestHeader('Content-Type', 'application/json');
                            httppersosdestpers.send();

                            let infopersosdestpers = document.querySelector('#idsonnelspersoesc');
        
                            if (infopersosdestpers !== null) 
                            infopersosdestpers.onchange = () => 
                            {

                            
                                let httpInfospersdest;
                                if (window.XMLHttpRequest) {
                                    httpInfospersdest = new XMLHttpRequest();
                                } else if (window.ActiveXObject) {
                                    httpInfospersdest = new ActiveXObject("Microsoft.XMLHTTP");
                                }

                                document.querySelector('#contactidpersoesc').style.display = 'none';
                                document.querySelector('#idcontpersoesc').style.display = 'none';
                                document.querySelector('#compagniepassdestpersoesc').value = '';
                                var idverifidest = document.querySelector('#idsonnelspersoesc').options[document.querySelector('#idsonnelspersoesc').options.selectedIndex].value;
                    
                                httpInfospersdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoperso/${idverifidest}`, true);
                                httpInfospersdest.onload = () => {
                                    const infosperdest = JSON.parse(httpInfospersdest.responseText);
                                    
                                    if (Object.entries(infosperdest).length >= 1) {
                                        
                               
                                        var typepersosdest = `${infosperdest.nomprenom_perso}`;
                                        var typer1persosdest = typepersosdest.split(' ');
                                        var typer2persosdest = typer1persosdest[0];
                                        var typer3persosdest = typer1persosdest[1];
                                        var typer4persosdest = typer1persosdest[2];
                                        if(typer4persosdest === undefined){
                                            var typer5persosdest = `${typer3persosdest}`;
                                        }
                                        else{
                                            var typer5persosdest = `${typer3persosdest} ${typer4persosdest}`;
                                        }
                                        document.querySelector('#nomdestidpersoesc').value = `${typer2persosdest}`;
                                        document.querySelector('#prenomdestidpersoesc').value = `${typer5persosdest}`;
                                        document.querySelector('#persodestcompagniepersoesc').value = `${infosperdest.matricule}`;
                                        document.querySelector('#rclientcpexppersoesc').value = `${typer2persosdest}`;
                                        document.querySelector('#prnclientcpexppersoesc').value = `${typer5persosdest}`;
                                        document.querySelector('#idclientypedestpersoesc').value = 'personnel';
                                        document.querySelector('#idclientcontpersoesc').value = "";
                                    } 
                                    else
                                    {
                                        document.querySelector('#nomdestidpersoesc').value = "";
                                        document.querySelector('#prenomdestidpersoesc').value = "";
                                        document.querySelector('#persodestcompagniepersoesc').value = "";
                                        document.querySelector('#rclientcpdestpersoesc').value = "";
                                        document.querySelector('#prnclientcpdestpersoesc').value = "";
                                        document.querySelector('#idclientypedestpersoesc').value = "";
                                        document.querySelector('#idclientcontpersoesc').value = "";
                                    }
                                                                
                                };
                                httpInfospersdest.setRequestHeader('Content-Type', 'application/json');
                                httpInfospersdest.send();
                            };
                        }
                        else
                        {
                            document.querySelector('#sonnelpersomemesc').style.display = 'none';
                            document.querySelector('#idsonnelspersomemesc').style.display = 'none';
                            document.querySelector('#idsonnelspersomemesc').options.length = 1;
                            document.querySelector('#sonnelpersoesc').style.display = 'none';
                            document.querySelector('#idsonnelspersoesc').style.display = 'none';
                            document.querySelector('#partcontpersoesc').style.display = 'none';
                            document.querySelector('#idpartespersoesc').style.display = 'none';
                            document.querySelector('#idsonnelspersoesc').options.length = 1;
                            document.querySelector('#idpartespersoesc').options.length = 1;
                            document.querySelector('#idcontpersoesc').style.display = 'block';
                            document.querySelector('#contactidpersoesc').style.display = 'block';
                            document.querySelector('#idmatripersoesc').style.display = 'none';
                            document.querySelector('#matri_destpersoesc').style.display = 'none';
                            document.querySelector('#nomdestidpersoesc').value = "";
                            document.querySelector('#prenomdestidpersoesc').value = "";
                            document.querySelector('#compagniepassdestpersoesc').value = "";
                            document.querySelector('#rclientcpdestpersoesc').value = "";
                            document.querySelector('#prnclientcpdestpersoesc').value = "";
                            document.querySelector('#idclientypedestpersoesc').value = "";
                            document.querySelector('#idclientcontpersoesc').value = "";
                            let infdest = document.querySelector('#contactidpersoesc');
                            if (infdest !== null)
                                infdest.onkeyup = () => {
                                    let httpInfosdest;
                                    if (window.XMLHttpRequest) {
                                        httpInfosdest = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) {
                                        httpInfosdest = new ActiveXObject("Microsoft.XMLHTTP");
                                    }

                                    document.querySelector('#nomdestidpersoesc').value = "";
                                    document.querySelector('#prenomdestidpersoesc').value = "";
                                    document.querySelector('#compagniepassdestpersoesc').value = "";
                                    document.querySelector('#rclientcpdestpersoesc').value = "";
                                    document.querySelector('#prnclientcpdestpersoesc').value = "";
                                    document.querySelector('#idclientypedestpersoesc').value = "";
                                    document.querySelector('#idclientcontpersoesc').value = "";
                                    var verificatdest = document.querySelector('#contactidpersoesc').value;
                                    document.querySelector('#persodestcompagniepersoesc').value = "";

                                    httpInfosdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfos/${verificatdest}`, true);
                                    httpInfosdest.onload = () => {
                                        const infosdest = JSON.parse(httpInfosdest.responseText);
                                        if (infosdest == null) {
                                            document.querySelector('#nomdestidpersoesc').value = "";
                                            document.querySelector('#prenomdestidpersoesc').value = "";
                                            document.querySelector('#compagniepasspersoesc').value = "";
                                            document.querySelector('#rclientcpdestpersoesc').value = "";
                                            document.querySelector('#prnclientcpdestpersoesc').value = "";
                                            document.querySelector('#idclientypedestpersoesc').value = "";
                                            document.querySelector('#idclientcontpersoesc').value = "";
                                        } else 
                                        {
                                            if (Object.entries(infosdest).length > 1) {
                                                
                                                if (infosdest.contact_client == verificatdest) {
                                                    document.querySelector('#nomdestidpersoesc').value = `${infosdest.nom_client}`;
                                                    document.querySelector('#prenomdestidpersoesc').value = `${infosdest.prenom_client}`;
                                                    document.querySelector('#compagniepassdestpersoesc').value = `${infosdest.id_client}`;
                                                    document.querySelector('#idclientypedestpersoesc').value = `${infosdest.type_client}`;
                                                    document.querySelector('#rclientcpdestpersoesc').value = `${infosdest.nom_client}`;
                                                    document.querySelector('#prnclientcpdestpersoesc').value = `${infosdest.prenom_client}`;
                                                    document.querySelector('#idclientcontpersoesc').value = `${infosdest.contact_client}`;
                                                    document.querySelector('#date_cnibdestidpersoesc').value = `${infosdest.date_delivre}`;
                                                } else {
                                                    document.querySelector('#idclientcontpersoesc').value = "";
                                                    document.querySelector('#nomdestidpersoesc').value = "";
                                                    document.querySelector('#prenomdestidpersoesc').value = "";
                                                    document.querySelector('#compagniepassdestpersoesc').value = "";
                                                    document.querySelector('#rclientcpdestpersoesc').value = "";
                                                    document.querySelector('#prnclientcpdestpersoesc').value = "";
                                                    document.querySelector('#idclientypedestpersoesc').value = "";
                                                    document.querySelector('#date_cnibdestidpersoesc').value = "";
                                            
                                                }
                                            }
                                        }
                                    };
                                    httpInfosdest.setRequestHeader('Content-Type', 'application/json');
                                    httpInfosdest.send();
                                };
                        }

                        if(personns === 'membre'){

                                document.querySelector('#sonnelpersomemesc').style.display = 'block';
                                document.querySelector('#idsonnelspersomemesc').style.display = 'block';
                                document.querySelector('#idcontpersoesc').style.display = 'none';
                                document.querySelector('#contactidpersoesc').style.display = 'none';
                                document.querySelector('#sonnelpersoesc').style.display = 'none';
                                document.querySelector('#idsonnelspersoesc').style.display = 'none';
                                document.querySelector('#idsonnelspersoesc').options.length = 1;
                                
                        
                                let httppaternesdestm;
                                    if (window.XMLHttpRequest) {
                                        httppaternesdestm = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) {
                                        httppaternesdestm = new ActiveXObject("Microsoft.XMLHTTP");
                                    }
                                    
                                    httppaternesdestm.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectpartenaire/${personns}`, true);
                                    httppaternesdestm.onload = () => {
                                        const infospartenedestm = JSON.parse(httppaternesdestm.responseText);

                                        if (Object.entries(infospartenedestm).length >= 1) 
                                        {

                                            for (let key in Object.entries(infospartenedestm))
                                            {

                                                let opt = document.createElement('option');
                                                opt.value = `${infospartenedestm[key].id_client}`;
                                                opt.innerHTML = `${infospartenedestm[key].nom_client} ${infospartenedestm[key].prenom_client}`;
                                                document.querySelector('#idsonnelspersomemesc').add(opt);
                                            }
                                                
                                        }
                                        else 
                                        {
                                            document.querySelector('#idsonnelspersomemesc').options.length = 1;
                                        }

                                    };
                                    httppaternesdestm.setRequestHeader('Content-Type', 'application/json');
                                    httppaternesdestm.send();

                                let paternstscdestin2m = document.querySelector('#idsonnelspersomemesc');
                                if (paternstscdestin2m !== null)
                                paternstscdestin2m.onchange = () => {
                                    let httpInfospersdestin2m;
                                        httpInfospersdestin2m = new XMLHttpRequest();
                                    document.querySelector('#persodestcompagniepersoesc').value = '';
                                    document.querySelector('#contactidpersoesc').style.display = 'none';
                                    document.querySelector('#idcontpersoesc').style.display = 'none';
                                    document.querySelector('#contactidpersoesc').value = '';
                                        var ternsdest2m = document.querySelector('#idsonnelspersomemesc').
                                            options[document.querySelector('#idsonnelspersomemesc').options.selectedIndex].value;
                                        httpInfospersdestin2m.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest2m}`, true);
                                    httpInfospersdestin2m.onload = () => {
                                        const infosperdestin2m = JSON.parse(httpInfospersdestin2m.responseText);
                                        
                                        if (Object.entries(infosperdestin2m).length >= 1) {
                                            
                                            document.querySelector('#nomdestidpersoesc').value = `${infosperdestin2m.nom_client}`;
                                            document.querySelector('#prenomdestidpersoesc').value = `${infosperdestin2m.prenom_client}`;
                                            document.querySelector('#compagniepassdestpersoesc').value = `${infosperdestin2m.id_client}`;
                                            document.querySelector('#idclientypedestpersoesc').value = `${infosperdestin2m.type_client}`;
                                            document.querySelector('#rclientcpdestpersoesc').value = `${infosperdestin2m.nom_client}`;
                                            document.querySelector('#prnclientcpdestpersoesc').value = `${infosperdestin2m.prenom_client}`;
                                            document.querySelector('#date_cnibdestidpersoesc').value = `${infosperdestin2m.date_delivre}`;
                                            
                                        } 
                                        else
                                        {
                                            document.querySelector('#nomdestidpersoesc').value = "";
                                            document.querySelector('#prenomdestidpersoesc').value = "";
                                            document.querySelector('#compagniepassdestpersoesc').value = "";
                                            document.querySelector('#rclientcpdestpersoesc').value = "";
                                            document.querySelector('#prnclientcpdestpersoesc').value = "";
                                            document.querySelector('#idclientypedestpersoesc').value = "";
                                            document.querySelector('#date_cnibdestidpersoesc').value = "";
                                        }
                                                                    
                                    };
                                    httpInfospersdestin2m.setRequestHeader('Content-Type', 'application/json');
                                    httpInfospersdestin2m.send();
                                };
                   
                            
                            }
                        if(personns === 'partenaire_client' || personns === 'partenaire_simple'){
                            document.querySelector('#sonnelpersomemesc').style.display = 'none';
                            document.querySelector('#idsonnelspersomemesc').style.display = 'none';
                            document.querySelector('#idsonnelspersomemesc').options.length = 1;
                            document.querySelector('#partcontpersoesc').style.display = 'block';
                            document.querySelector('#idpartespersoesc').style.display = 'block';
                            document.querySelector('#sonnelpersoesc').style.display = 'none';
                            document.querySelector('#idsonnelspersoesc').style.display = 'none';
                            document.querySelector('#idsonnelspersoesc').options.length = 1;
                            document.querySelector('#contactidpersoesc').style.display = 'none';
                            document.querySelector('#idcontpersoesc').style.display = 'none';
                            document.querySelector('#nomdestidpersoesc').value = '';
                            document.querySelector('#prenomdestidpersoesc').value = '';
                            document.querySelector('#compagniepassdestpersoesc').value = '';
                            document.querySelector('#idclientypedestpersoesc').value = '';
                            document.querySelector('#rclientcpdestpersoesc').value = '';
                            document.querySelector('#prnclientcpdestpersoesc').value = '';
                            document.querySelector('#contactidpersoesc').value = '';
                            document.querySelector('#idclientcontpersoesc').value = '';
                            
                            let httppaternsdest;
                                if (window.XMLHttpRequest) {
                                    httppaternsdest = new XMLHttpRequest();
                                } else if (window.ActiveXObject) {
                                    httppaternsdest = new ActiveXObject("Microsoft.XMLHTTP");
                                }
                                
                                httppaternsdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectpartenaire/${personns}`, true);
                                httppaternsdest.onload = () => {
                                    const infospartendest = JSON.parse(httppaternsdest.responseText);

                                    if (Object.entries(infospartendest).length >= 1) 
                                    {

                                        for (let key in Object.entries(infospartendest))
                                        {

                                            let opt = document.createElement('option');
                                            opt.value = `${infospartendest[key].id_client}`;
                                            opt.innerHTML = `${infospartendest[key].nom_client} ${infospartendest[key].prenom_client}`;
                                            document.querySelector('#idpartespersoesc').add(opt);
                                        }
                                            
                                    }
                                    else 
                                    {
                                        document.querySelector('#idpartespersoesc').options.length = 1;
                                    }

                                };
                                httppaternsdest.setRequestHeader('Content-Type', 'application/json');
                                httppaternsdest.send();

                                let paternstscdestin = document.querySelector('#idpartespersoesc');
                            if (paternstscdestin !== null)
                            paternstscdestin.onchange = () => {
                                let httpInfospersdestin;
                                    httpInfospersdestin = new XMLHttpRequest();
                                document.querySelector('#persodestcompagniepersoesc').value = '';
                                document.querySelector('#contactidpersoesc').style.display = 'none';
                                document.querySelector('#idcontpersoesc').style.display = 'none';
                                document.querySelector('#contactidpersoesc').value = '';
                                var ternsdest= document.querySelector('#idpartespersoesc').
                                    options[document.querySelector('#idpartespersoesc').options.selectedIndex].value;
                                httpInfospersdestin.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest}`, true);
                                httpInfospersdestin.onload = () => {
                                    const infosperdestin = JSON.parse(httpInfospersdestin.responseText);
                                    
                                    if (Object.entries(infosperdestin).length >= 1) {
                                        
                                        document.querySelector('#nomdestidpersoesc').value = `${infosperdestin.nom_client}`;
                                        document.querySelector('#prenomdestidpersoesc').value = `${infosperdestin.prenom_client}`;
                                        document.querySelector('#compagniepassdestpersoesc').value = `${infosperdestin.id_client}`;
                                        document.querySelector('#idclientypedestpersoesc').value = `${infosperdestin.type_client}`;
                                        document.querySelector('#rclientcpdestpersoesc').value = `${infosperdestin.nom_client}`;
                                        document.querySelector('#prnclientcpdestpersoesc').value = `${infosperdestin.prenom_client}`;
                                        document.querySelector('#date_cnibdestidpersoesc').value = `${infosperdestin.date_delivre}`;
                                    } 
                                    else
                                    {
                                        document.querySelector('#nomdestidpersoesc').value = "";
                                        document.querySelector('#prenomdestidpersoesc').value = "";
                                        document.querySelector('#compagniepassdestpersoesc').value = "";
                                        document.querySelector('#rclientcpdestpersoesc').value = "";
                                        document.querySelector('#prnclientcpdestpersoesc').value = "";
                                        document.querySelector('#idclientypedestpersoesc').value = "";
                                        document.querySelector('#date_cnibdestidpersoesc').value = "";
                                    }
                                                                
                                };
                                httpInfospersdestin.setRequestHeader('Content-Type', 'application/json');
                                httpInfospersdestin.send();
                            };
 
                        }
                        else
                        {


                            if(personns === 'partenaire_specifique'){

                                document.querySelector('#partcontpersoesc').style.display = 'block';
                                document.querySelector('#idpartespersoesc').style.display = 'block';
                                document.querySelector('#sonnelpersoesc').style.display = 'none';
                                document.querySelector('#idsonnelspersoesc').style.display = 'none';
                                document.querySelector('#idcontpersoesc').style.display = 'none';
                                document.querySelector('#contactidpersoesc').style.display = 'none';
                                document.querySelector('#sonnelpersomemesc').style.display = 'none';
                                document.querySelector('#idsonnelspersomemesc').style.display = 'none';
                                document.querySelector('#idsonnelspersomemesc').options.length = 1;
                                document.querySelector('#idsonnelspersoesc').options.length = 1;
                                
                                let httppaternesdest;
                                    if (window.XMLHttpRequest) {
                                        httppaternesdest = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) {
                                        httppaternesdest = new ActiveXObject("Microsoft.XMLHTTP");
                                    }
                                    
                                    httppaternesdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectpartenaire/${personns}`, true);
                                    httppaternesdest.onload = () => {
                                        const infospartenedest = JSON.parse(httppaternesdest.responseText);

                                        if (Object.entries(infospartenedest).length >= 1) 
                                        {

                                            for (let key in Object.entries(infospartenedest))
                                            {

                                                let opt = document.createElement('option');
                                                opt.value = `${infospartenedest[key].id_client}`;
                                                opt.innerHTML = `${infospartenedest[key].nom_client} ${infospartenedest[key].prenom_client}`;
                                                document.querySelector('#idpartespersoesc').add(opt);
                                            }
                                                
                                        }
                                        else 
                                        {
                                            document.querySelector('#idpartespersoesc').options.length = 1;
                                        }

                                    };
                                    httppaternesdest.setRequestHeader('Content-Type', 'application/json');
                                    httppaternesdest.send();

                                let paternstscdestin2 = document.querySelector('#idpartespersoesc');
                                if (paternstscdestin2 !== null)
                                paternstscdestin2.onchange = () => {
                                    let httpInfospersdestin2;
                                        httpInfospersdestin2 = new XMLHttpRequest();
                                    document.querySelector('#persodestcompagniepersoesc').value = '';
                                    document.querySelector('#contactidpersoesc').style.display = 'none';
                                    document.querySelector('#idcontpersoesc').style.display = 'none';
                                    document.querySelector('#contactidpersoesc').value = '';
                                        var ternsdest2 = document.querySelector('#idpartespersoesc').
                                            options[document.querySelector('#idpartespersoesc').options.selectedIndex].value;
                                        httpInfospersdestin2.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest2}`, true);
                                    httpInfospersdestin2.onload = () => {
                                        const infosperdestin2 = JSON.parse(httpInfospersdestin2.responseText);
                                        
                                        if (Object.entries(infosperdestin2).length >= 1) {
                                            
                                            document.querySelector('#nomdestidpersoesc').value = `${infosperdestin2.nom_client}`;
                                            document.querySelector('#prenomdestidpersoesc').value = `${infosperdestin2.prenom_client}`;
                                            document.querySelector('#compagniepassdestpersoesc').value = `${infosperdestin2.id_client}`;
                                            document.querySelector('#idclientypedestpersoesc').value = `${infosperdestin2.type_client}`;
                                            document.querySelector('#rclientcpdestpersoesc').value = `${infosperdestin2.nom_client}`;
                                            document.querySelector('#prnclientcpdestpersoesc').value = `${infosperdestin2.prenom_client}`;
                                            document.querySelector('#date_cnibdestidpersoesc').value = `${infosperdestin2.date_delivre}`;
                                            
                                        } 
                                        else
                                        {
                                            document.querySelector('#nomdestidpersoesc').value = "";
                                            document.querySelector('#prenomdestidpersoesc').value = "";
                                            document.querySelector('#compagniepassdestpersoesc').value = "";
                                            document.querySelector('#rclientcpdestpersoesc').value = "";
                                            document.querySelector('#prnclientcpdestpersoesc').value = "";
                                            document.querySelector('#idclientypedestpersoesc').value = "";
                                            document.querySelector('#date_cnibdestidpersoesc').value = "";
                                        }
                                                                    
                                    };
                                    httpInfospersdestin2.setRequestHeader('Content-Type', 'application/json');
                                    httpInfospersdestin2.send();
                                };
                   
                            
                            }
                            
                        }
                        
                        
                    }
        e.onclick = function () {
            let copersoForm = document.querySelector('#copersoFormesc');
            copersoForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/addpersoesc/${e.dataset.cle_compagnie}`);
        }

        var clique = true;

            $('#bottonpersoesc').click(function(event) 
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
;
/* --- adsbords.js --- */
document.addEventListener('DOMContentLoaded', () => {
   

    document.querySelectorAll('.adsbords').forEach(function (e) 
    {
        document.querySelector('h3#bordsTitlebg').innerHTML = `TIRAGE DE SUIVI`;

        function loadProgrammesBord() {
            var selectLigne = document.querySelector('#deptscouridlignebg');
            var selectDate = document.querySelector('#courdeptchoisirdatebg');
            var selectProg = document.querySelector('#courdeptidprogbg');
            if (!selectLigne || !selectDate || !selectProg) {
                return;
            }

            var ligne = parseLigneOption(selectLigne.options[selectLigne.selectedIndex].value);
            var verifidate = selectDate.value;
            selectProg.options.length = 1;

            if (!ligne.ident || !verifidate) {
                return;
            }

            var httpInfoprog = new XMLHttpRequest();
            httpInfoprog.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifprogramm/${encodeURIComponent(ligne.ident)}/${verifidate}`, true);
            httpInfoprog.onload = function () {
                var resultp;
                try {
                    resultp = JSON.parse(httpInfoprog.responseText);
                } catch (err) {
                    return;
                }

                if (!resultp || !resultp.length) {
                    selectProg.options.length = 1;
                    return;
                }

                resultp.forEach(function (item) {
                    var opt = document.createElement('option');
                    opt.value = `${item.code_progr}/${item.heure}/${item.id_ligneheure}/${item.depart_code}`;
                    opt.innerHTML = `${item.code_progr}/${item.heure}`;
                    selectProg.add(opt);
                });
            };
            httpInfoprog.send();
        }

        let arcourr = document.querySelector('#deptscouridlignebg');
            if (arcourr !== null)
            arcourr.onchange = () => {
                
                document.querySelector('#courdeptidprogbg').options.length = 1;
                document.querySelector('#courdeptquartieridbg').options.length = 1;
                const lidlignecr = document.querySelector('#deptscouridlignebg')
                .options[document.querySelector('#deptscouridlignebg').options.selectedIndex].value;
                var ligne = parseLigneOption(lidlignecr);
                if (!ligne.gareDest) {
                    return;
                }
                let httptypequartr;
                httptypequartr = new XMLHttpRequest();
                
                httptypequartr.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifquart/${encodeURIComponent(ligne.gareDest)}`, true);
                httptypequartr.onload = () => 
                {
                    const courquar = JSON.parse(httptypequartr.responseText);
                    if (courquar == '') {
                        document.querySelector('#courdeptquartieridbg').options.length = 1;
                    }
                    else{
                        if (Object.entries(courquar).length >= 1) {
                                        
                            for (let key in Object.entries(courquar)) {
                                let opt = document.createElement('option');
                                opt.value = `${courquar[key].nom_quartier}`;
                                opt.innerHTML = `${courquar[key].nom_quartier}/${courquar[key].code_quart}`;
                                document.querySelector('#courdeptquartieridbg').add(opt);
                            }
                        } else {
                            document.querySelector('#courdeptquartieridbg').options.length = 1;
                        }
                    }
                    

                };
                httptypequartr.setRequestHeader('Content-Type', 'application/json');
                httptypequartr.send();
                loadProgrammesBord();
            };
            let infoligne = document.querySelector('#courdeptchoisirdatebg');
            if (infoligne !== null)
            infoligne.onchange = () => {
                loadProgrammesBord();
                                     
            };
                    let infchaufbords = document.querySelector('#courstyppersoidbg');
                    if (infchaufbords !== null)
                    infchaufbords.onchange = () => 
                    {
                        document.querySelector('#coursidchaufbg').options.length = 1;
                        const chauffesbords = document.querySelector('#courstyppersoidbg')
                            .options[document.querySelector('#courstyppersoidbg').options.selectedIndex].value;

                        if(chauffesbords === 'chauffeur')
                        {
                            let httpInfosinfochaufbords;
                            if (window.XMLHttpRequest) {
                                httpInfosinfochaufbords = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfochaufbords = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfochaufbords.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifpersonne/${chauffesbords}`, true);
                            httpInfosinfochaufbords.onload = () => {
                                const resultchauffsbords = JSON.parse(httpInfosinfochaufbords.responseText);
                                
                                    if (Object.entries(resultchauffsbords).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultchauffsbords)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultchauffsbords[key].nomprenom_perso}`;
                                                opt.innerHTML = `${resultchauffsbords[key].nomprenom_perso}`;
                                                document.querySelector('#coursidchaufbg').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#coursidchaufbg').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfochaufbords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfochaufbords.send();   
                        }
                        if(chauffesbords === 'autrepersonnel')
                        {
                            let httpInfosinfopersobords;
                            if (window.XMLHttpRequest) {
                                httpInfosinfopersobords = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfopersobords = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfopersobords.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifclient/${chauffesbords}`, true);
                            httpInfosinfopersobords.onload = () => {
                                const resultpersobords = JSON.parse(httpInfosinfopersobords.responseText);
                                
                                    if (Object.entries(resultpersobords).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultpersobords)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultpersobords[key].nom_client} ${resultpersobords[key].prenom_client}`;
                                                opt.innerHTML = `${resultpersobords[key].nom_client} ${resultpersobords[key].prenom_client}`;
                                                document.querySelector('#coursidchaufbg').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#coursidchaufbg').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfopersobords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfopersobords.send();   
                        }
                        
                    };

                    let infconvoibords = document.querySelector('#courstyppersoid1bg');
                    if (infconvoibords !== null)
                    infconvoibords.onchange = () => 
                    {
                        document.querySelector('#couridconvoibg').options.length = 1;
                        const convoisbords = document.querySelector('#courstyppersoid1bg')
                            .options[document.querySelector('#courstyppersoid1bg').options.selectedIndex].value;

                        if(convoisbords === 'convoyeur')
                        {
                            let httpInfosinfoconvbords;
                            if (window.XMLHttpRequest) {
                                httpInfosinfoconvbords = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfoconvbords = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfoconvbords.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifconvoi/${convoisbords}`, true);
                            httpInfosinfoconvbords.onload = () => {
                                const resultconvbords = JSON.parse(httpInfosinfoconvbords.responseText);
                                
                                    if (Object.entries(resultconvbords).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultconvbords)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultconvbords[key].nomprenom_perso}`;
                                                opt.innerHTML = `${resultconvbords[key].nomprenom_perso}`;
                                                document.querySelector('#couridconvoibg').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#couridconvoibg').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfoconvbords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfoconvbords.send();   
                        }
                        if(convoisbords === 'autrepersonnel')
                        {
                            let httpInfosinfopersosbords;
                            if (window.XMLHttpRequest) {
                                httpInfosinfopersosbords = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfopersosbords = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfopersosbords.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifclient/${convoisbords}`, true);
                            httpInfosinfopersosbords.onload = () => {
                                const resultpersosbords = JSON.parse(httpInfosinfopersosbords.responseText);
                                
                                    if (Object.entries(resultpersosbords).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultpersosbords)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultpersosbords[key].nom_client} ${resultpersosbords[key].prenom_client}`;
                                                opt.innerHTML = `${resultpersosbords[key].nom_client} ${resultpersosbords[key].prenom_client}`;
                                                document.querySelector('#couridconvoibg').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#couridconvoibg').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfopersosbords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfopersosbords.send();   
                        }
                    };
        e.onclick = function (){
            let bordesForm = document.querySelector('#bordesFormbg');
            bordesForm.setAttribute('action', `${APP_ROOT}/Rapport/listesbagages/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- adsbordst.js --- */
document.addEventListener('DOMContentLoaded', () => {
   

    document.querySelectorAll('.adsbordst').forEach(function (e) 
    {
        document.querySelector('h3#bordsTitlebgt').innerHTML = `TIRAGE DE SUIVI TPE`;

        function loadProgrammesBordT() {
            var selectLigne = document.querySelector('#deptscouridlignebgt');
            var selectDate = document.querySelector('#courdeptchoisirdatebgt');
            var selectProg = document.querySelector('#courdeptidprogbgt');
            if (!selectLigne || !selectDate || !selectProg) {
                return;
            }

            var ligne = parseLigneOption(selectLigne.options[selectLigne.selectedIndex].value);
            var verifidate = selectDate.value;
            selectProg.options.length = 1;

            if (!ligne.ident || !verifidate) {
                return;
            }

            var httpInfoprog = new XMLHttpRequest();
            httpInfoprog.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifprogramm/${encodeURIComponent(ligne.ident)}/${verifidate}`, true);
            httpInfoprog.onload = function () {
                var resultp;
                try {
                    resultp = JSON.parse(httpInfoprog.responseText);
                } catch (err) {
                    return;
                }

                if (!resultp || !resultp.length) {
                    selectProg.options.length = 1;
                    return;
                }

                resultp.forEach(function (item) {
                    var opt = document.createElement('option');
                    opt.value = `${item.code_progr}/${item.heure}/${item.id_ligneheure}/${item.depart_code}`;
                    opt.innerHTML = `${item.code_progr}/${item.heure}`;
                    selectProg.add(opt);
                });
            };
            httpInfoprog.send();
        }

        let arcourr = document.querySelector('#deptscouridlignebgt');
            if (arcourr !== null)
            arcourr.onchange = () => {
                
                document.querySelector('#courdeptidprogbgt').options.length = 1;
                document.querySelector('#courdeptquartieridbgt').options.length = 1;
                const lidlignecr = document.querySelector('#deptscouridlignebgt')
                .options[document.querySelector('#deptscouridlignebgt').options.selectedIndex].value;
                var ligne = parseLigneOption(lidlignecr);
                if (!ligne.gareDest) {
                    return;
                }
                let httptypequartr;
                httptypequartr = new XMLHttpRequest();
                
                httptypequartr.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifquart/${encodeURIComponent(ligne.gareDest)}`, true);
                httptypequartr.onload = () => 
                {
                    const courquar = JSON.parse(httptypequartr.responseText);
                    if (courquar == '') {
                        document.querySelector('#courdeptquartieridbgt').options.length = 1;
                    }
                    else{
                        if (Object.entries(courquar).length >= 1) {
                                        
                            for (let key in Object.entries(courquar)) {
                                let opt = document.createElement('option');
                                opt.value = `${courquar[key].nom_quartier}`;
                                opt.innerHTML = `${courquar[key].nom_quartier}/${courquar[key].code_quart}`;
                                document.querySelector('#courdeptquartieridbgt').add(opt);
                            }
                        } else {
                            document.querySelector('#courdeptquartieridbgt').options.length = 1;
                        }
                    }
                    

                };
                httptypequartr.setRequestHeader('Content-Type', 'application/json');
                httptypequartr.send();
                loadProgrammesBordT();
            };
            let infoligne = document.querySelector('#courdeptchoisirdatebgt');
            if (infoligne !== null)
            infoligne.onchange = () => {
                loadProgrammesBordT();
                                     
            };
                    let infchaufbords = document.querySelector('#courstyppersoidbgt');
                    if (infchaufbords !== null)
                    infchaufbords.onchange = () => 
                    {
                        document.querySelector('#coursidchaufbgt').options.length = 1;
                        const chauffesbords = document.querySelector('#courstyppersoidbgt')
                            .options[document.querySelector('#courstyppersoidbgt').options.selectedIndex].value;

                        if(chauffesbords === 'chauffeur')
                        {
                            let httpInfosinfochaufbords;
                            if (window.XMLHttpRequest) {
                                httpInfosinfochaufbords = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfochaufbords = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfochaufbords.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifpersonne/${chauffesbords}`, true);
                            httpInfosinfochaufbords.onload = () => {
                                const resultchauffsbords = JSON.parse(httpInfosinfochaufbords.responseText);
                                
                                    if (Object.entries(resultchauffsbords).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultchauffsbords)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultchauffsbords[key].nomprenom_perso}`;
                                                opt.innerHTML = `${resultchauffsbords[key].nomprenom_perso}`;
                                                document.querySelector('#coursidchaufbgt').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#coursidchaufbgt').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfochaufbords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfochaufbords.send();   
                        }
                        if(chauffesbords === 'autrepersonnel')
                        {
                            let httpInfosinfopersobords;
                            if (window.XMLHttpRequest) {
                                httpInfosinfopersobords = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfopersobords = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfopersobords.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifclient/${chauffesbords}`, true);
                            httpInfosinfopersobords.onload = () => {
                                const resultpersobords = JSON.parse(httpInfosinfopersobords.responseText);
                                
                                    if (Object.entries(resultpersobords).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultpersobords)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultpersobords[key].nom_client} ${resultpersobords[key].prenom_client}`;
                                                opt.innerHTML = `${resultpersobords[key].nom_client} ${resultpersobords[key].prenom_client}`;
                                                document.querySelector('#coursidchaufbgt').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#coursidchaufbgt').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfopersobords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfopersobords.send();   
                        }
                        
                    };

                    let infconvoibords = document.querySelector('#courstyppersoid1bgt');
                    if (infconvoibords !== null)
                    infconvoibords.onchange = () => 
                    {
                        document.querySelector('#couridconvoibgt').options.length = 1;
                        const convoisbords = document.querySelector('#courstyppersoid1bgt')
                            .options[document.querySelector('#courstyppersoid1bgt').options.selectedIndex].value;

                        if(convoisbords === 'convoyeur')
                        {
                            let httpInfosinfoconvbords;
                            if (window.XMLHttpRequest) {
                                httpInfosinfoconvbords = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfoconvbords = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfoconvbords.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifconvoi/${convoisbords}`, true);
                            httpInfosinfoconvbords.onload = () => {
                                const resultconvbords = JSON.parse(httpInfosinfoconvbords.responseText);
                                
                                    if (Object.entries(resultconvbords).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultconvbords)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultconvbords[key].nomprenom_perso}`;
                                                opt.innerHTML = `${resultconvbords[key].nomprenom_perso}`;
                                                document.querySelector('#couridconvoibgt').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#couridconvoibgt').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfoconvbords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfoconvbords.send();   
                        }
                        if(convoisbords === 'autrepersonnel')
                        {
                            let httpInfosinfopersosbords;
                            if (window.XMLHttpRequest) {
                                httpInfosinfopersosbords = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfopersosbords = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfopersosbords.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifclient/${convoisbords}`, true);
                            httpInfosinfopersosbords.onload = () => {
                                const resultpersosbords = JSON.parse(httpInfosinfopersosbords.responseText);
                                
                                    if (Object.entries(resultpersosbords).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultpersosbords)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultpersosbords[key].nom_client} ${resultpersosbords[key].prenom_client}`;
                                                opt.innerHTML = `${resultpersosbords[key].nom_client} ${resultpersosbords[key].prenom_client}`;
                                                document.querySelector('#couridconvoibgt').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#couridconvoibgt').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfopersosbords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfopersosbords.send();   
                        }
                    };
        e.onclick = function (){
            let bordesForm = document.querySelector('#bordesFormbgt');
            
            bordesForm.setAttribute('action', `${APP_ROOT}/Historique_Passagers/listesbagagestpe/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- addbordt.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addbordt').forEach(function (e) 
    {
        document.querySelector('h3#bordTitlet').innerHTML = `TIRAGE BORDEREAU`;


                    
            let arcourr = document.querySelector('#couridlignedeptt');
            if (arcourr !== null)
            arcourr.onchange = () => {
                
                document.querySelector('#choisirheurecourdeptt').options.length = 1;
                document.querySelector('#quartieridbgt').options.length = 1;
                const lidlignecr = document.querySelector('#couridlignedeptt')
                .options[document.querySelector('#couridlignedeptt').options.selectedIndex].value;
                var ligne = parseLigneOption(lidlignecr);
                if (!ligne.gareDest) {
                    return;
                }
                let httptypequartr;
                httptypequartr = new XMLHttpRequest();
                
                httptypequartr.open('GET', window.location.origin + `${APP_ROOT}/Confirmation/verifquart/${ligne.gareDest}`, true);
                httptypequartr.onload = () => 
                {
                    const courquar = JSON.parse(httptypequartr.responseText);
                    if (courquar == '') {
                        document.querySelector('#quartieridbgt').options.length = 1;
                    }
                    else{
                        if (Object.entries(courquar).length >= 1) {
                                        
                            for (let key in Object.entries(courquar)) {
                                let opt = document.createElement('option');
                                opt.value = `${courquar[key].nom_quartier}`;
                                opt.innerHTML = `${courquar[key].nom_quartier}/${courquar[key].code_quart}`;
                                document.querySelector('#quartieridbgt').add(opt);
                            }
                        } else {
                            document.querySelector('#quartieridbgt').options.length = 1;
                        }
                    }
                    

                };
                httptypequartr.setRequestHeader('Content-Type', 'application/json');
                httptypequartr.send();
            };
                    let infdatecour = document.querySelector('#choisirdatecourdeptt');
                    
                    if (infdatecour !== null) 
                    infdatecour.onchange = () => 
                    {
                    
                        let httpInfoscodebordereau;
                        if (window.XMLHttpRequest) {
                            httpInfoscodebordereau = new XMLHttpRequest();
                        } else if (window.ActiveXObject) {
                            httpInfoscodebordereau = new ActiveXObject("Microsoft.XMLHTTP");
                        }
                            document.querySelector('#choisirheurecourdeptt').options.length = 1;
                            document.querySelector('#idprogcourdeptt').options.length = 1;

                        var verifdatebord = document.querySelector('#choisirdatecourdeptt').value;
                        const veriflignebord = document.querySelector('#couridlignedeptt')
                                .options[document.querySelector('#couridlignedeptt').options.selectedIndex].value;

                        
                        httpInfoscodebordereau.open('GET', window.location.origin + `${APP_ROOT}/Confirmation/verifitiragedepart/${veriflignebord}/${verifdatebord}`, true);
                        httpInfoscodebordereau.onload = () => 
                        {
                            const heurebord = JSON.parse(httpInfoscodebordereau.responseText);
                            if(heurebord == ''){
                                document.querySelector('#infosmsheuret').style.display = 'block';
                                document.querySelector('#erreurinfoheuret').innerHTML = `Il n'y a pas de programme pour le moment`;
                            } else
                            {
                                if (Object.entries(heurebord).length >= 1) 
                                {
                                        document.querySelector('#infosmsheuret').style.display = 'none';

                                        for (let key in Object.entries(heurebord)) {
                                            document.querySelector('#chaufdeptt').value = `${heurebord[key].chauff}`;
                                            document.querySelector('#convoideptt').value = `${heurebord[key].convoy}`;
                                            document.querySelector('#ligndeptt').value = `${heurebord[key].nom_ligne}`;
                                            document.querySelector('#datedeptt').value = `${heurebord[key].datedepart_bus}`;
                                            document.querySelector('#progdeptt').value = `${heurebord[key].depart_code}`;

                                                let opt = document.createElement('option');
                                                opt.value = `${heurebord[key].id_ligneheure}/${heurebord[key].heure}`;
                                                opt.innerHTML = `${heurebord[key].heure}`;
                                                document.querySelector('#choisirheurecourdeptt').add(opt);

                                                
                                            }
                                } else {

                                    document.querySelector('#choisirheurecourdeptt').options.length = 1;

                                }
                            }   
                        };
                        httpInfoscodebordereau.setRequestHeader('Content-Type', 'application/json');
                        httpInfoscodebordereau.send();
                    };
                   
                
                let hrcour = document.querySelector('#choisirheurecourdeptt');
                    
                    if (hrcour !== null) 
                    hrcour.onchange = () => 
                    {
                    
                        let httpprog;
                        if (window.XMLHttpRequest) {
                            httpprog = new XMLHttpRequest();
                        } else if (window.ActiveXObject) {
                            httpprog = new ActiveXObject("Microsoft.XMLHTTP");
                        }
                            document.querySelector('#idprogcourdeptt').options.length = 1;

                        var verifhrcour = document.querySelector('#choisirdatecourdeptt').value;
                        const veriflignehrcour = document.querySelector('#couridlignedeptt')
                                .options[document.querySelector('#couridlignedeptt').options.selectedIndex].value;

                            const veriflignehr1 = document.querySelector('#choisirheurecourdeptt')
                                .options[document.querySelector('#choisirheurecourdeptt').options.selectedIndex].value;

                                var veriflignehr2 = veriflignehr1.split('/');
                            var hrex1 = veriflignehr2[0];
                            var hrex2 = veriflignehr2[1];

                        httpprog.open('GET', window.location.origin + `${APP_ROOT}/Confirmation/verifitiragedeparth/${veriflignehrcour}/${verifhrcour}/${hrex1}`, true);
                        httpprog.onload = () => 
                        {
                            const hprog = JSON.parse(httpprog.responseText);
                            if(hprog == ''){
                                

                            } else 
                            {
                                if (Object.entries(hprog).length >= 1) 
                                {

                                        for (let key in Object.entries(hprog)) {
                                            
                                                let opt = document.createElement('option');
                                                opt.value = `${hprog[key].code_progr}/${hprog[key].depart_code}/${hprog[key].chauff}/${hprog[key].convoy}`;
                                                opt.innerHTML = `${hprog[key].code_progr}/${hprog[key].depart_code}`;
                                                document.querySelector('#idprogcourdeptt').add(opt);

                                            }
                                } else {
                                    
                                    document.querySelector('#idprogcourdeptt').options.length = 1;

                                }
                            }   
                        };
                        httpprog.setRequestHeader('Content-Type', 'application/json');
                        httpprog.send();
                    };
        e.onclick = function (){
            let bordeForm = document.querySelector('#bordeFormt');
            bordeForm.setAttribute('action', `${APP_ROOT}/Rapport/listebisep/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- adsuivis.js --- */
document.addEventListener('DOMContentLoaded', () => {
   

    document.querySelectorAll('.adsuivis').forEach(function (e) 
    {
        document.querySelector('h3#suiviTitlebg').innerHTML = `ENREGISTREMENT BAGAGES`;

            function loadProgrammesSuiviLegacy() {
                var selectLigne = document.querySelector('#deptscouridlignesuivi');
                var selectDate = document.querySelector('#courdeptchoisirdatesuivi');
                var selectProg = document.querySelector('#courdeptidprogsuivi');
                if (!selectLigne || !selectDate || !selectProg) {
                    return;
                }

                var ligne = parseLigneOption(selectLigne.options[selectLigne.selectedIndex].value);
                var verifidate = selectDate.value;
                selectProg.options.length = 1;

                if (!ligne.ident || !verifidate) {
                    return;
                }

                var httpInfoprog = new XMLHttpRequest();
                httpInfoprog.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifprogramm/${encodeURIComponent(ligne.ident)}/${verifidate}`, true);
                httpInfoprog.onload = function () {
                    var resultp;
                    try {
                        resultp = JSON.parse(httpInfoprog.responseText);
                    } catch (err) {
                        return;
                    }

                    if (!resultp || !resultp.length) {
                        selectProg.options.length = 1;
                        return;
                    }

                    resultp.forEach(function (item) {
                        var opt = document.createElement('option');
                        opt.value = `${item.code_progr}/${item.heure}/${item.id_ligneheure}/${item.depart_code}`;
                        opt.innerHTML = `${item.code_progr}/${item.heure}`;
                        selectProg.add(opt);
                    });
                };
                httpInfoprog.send();
            }

            let infolignes = document.querySelector('#deptscouridlignesuivi');
            if (infolignes !== null) {
                infolignes.onchange = () => {
                    loadProgrammesSuiviLegacy();
                };
            }

            let infoligne = document.querySelector('#courdeptchoisirdatesuivi');
            if (infoligne !== null)
            infoligne.onchange = () => {
                loadProgrammesSuiviLegacy();
                                     
            };
            let infrecubag = document.querySelector('#numcoderecu');
        if (infrecubag !== null)
            infrecubag.onkeyup = () => {
                let httpInfosbag;
                if (window.XMLHttpRequest) {
                    httpInfosbag = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfosbag = new ActiveXObject("Microsoft.XMLHTTP");
                }

                var verificatbag = document.querySelector('#numcoderecu').value;
                
                const lidlignes = document.querySelector('#deptscouridlignesuivi')
                .options[document.querySelector('#deptscouridlignesuivi').options.selectedIndex].value;
                var lidlignes2 = parseLigneOption(lidlignes).ident;
                httpInfosbag.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinforecus/${verificatbag}/${lidlignes2}`, true);
                httpInfosbag.onload = () => {
                    
                    const infosbag = JSON.parse(httpInfosbag.responseText);
                    if (infosbag == null) {

                        document.querySelector('#smsmbg').style.display = 'block';
                        document.querySelector('#smsmvfbg').innerHTML = `Verifier le code de bagage saisi!`;
                        document.querySelector('#idbagenv').value = "";
                        document.querySelector('#gddeptsuiviid').value = "";
                        document.querySelector('#sousgddeptsuiviid').value = "";
                        document.querySelector('#typbagid').value = "";
                        document.querySelector('#nombrebgsuiviid').value = "";
                        document.querySelector('#contenubgsuiviid').value = "";
                        document.querySelector('#idgarbag').value = "";
                    } else {
                        if (Object.entries(infosbag).length > 1) {
                            
                            if (infosbag.id_bagage == verificatbag && infosbag.ident_ligne == lidlignes2){

                                console.debug(`${infosbag.id_bagage}-${verificatbag}-${infosbag.ident_ligne}-${lidlignes2}`, console.memory);
                                document.querySelector('#idbagenv').value = `${infosbag.id_bagage}`;
                                document.querySelector('#gddeptsuiviid').value = `${infosbag.idgarebag}`;
                                document.querySelector('#sousgddeptsuiviid').value = `${infosbag.idsgarebag}`;
                                document.querySelector('#typbagid').value = `${infosbag.typebagages}`;
                                document.querySelector('#nombrebgsuiviid').value = `${infosbag.nombrebagage}`;
                                document.querySelector('#contenubgsuiviid').value = `${infosbag.contenubagage}`;
                                document.querySelector('#idgarbag').value = `${infosbag.gidarrbag}`;
                                document.querySelector('#smsmbg').style.display = 'none';
                            } else {
                                document.querySelector('#idbagenv').value = "";
                                document.querySelector('#gddeptsuiviid').value = "";
                                document.querySelector('#sousgddeptsuiviid').value = "";
                                document.querySelector('#typbagid').value = "";
                                document.querySelector('#nombrebgsuiviid').value = "";
                                document.querySelector('#contenubgsuiviid').value = "";
                                document.querySelector('#idgarbag').value = "";
                                document.querySelector('#smsmbg').style.display = 'block';
                                document.querySelector('#smsmvfbg').innerHTML = `Verifier le code de bagage saisi!`;
                    
                            }
                        }
                    }
                };
                httpInfosbag.setRequestHeader('Content-Type', 'application/json');
                httpInfosbag.send();
            };

            verifnb = function () 
            {
                var entree = parseInt(document.querySelector('#nombreenvid').value);
                    var n = document.querySelector('#nombreenvid').value;
                    var exist = parseInt(document.querySelector('#nombrebgsuiviid').value);
                        
                if(entree > exist) 
                {
                    document.querySelector('#smsmtbg').style.display = 'block';
                    document.querySelector('#smsmontantbg').innerHTML = `le mombre que vous aviez saisi dépasse le nombre de bagage`;
                    
                    document.querySelector('#nombreenvid').value = 'VERIFIER NOMBRE';  
                } 
                else
                {

                    document.querySelector('#smsmtbg').style.display = 'none';

                    document.querySelector('#nombreenvid').value = n ;
                    
                }
            };

        e.onclick = function (){
            let bordesForm = document.querySelector('#bordesFormsuivi');
            bordesForm.setAttribute('action', `${APP_ROOT}/Confirmation/enregbagages/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- sadsuivis.js --- */
document.addEventListener('DOMContentLoaded', () => {
   
    document.querySelectorAll('.sadsuivis').forEach(function (e) 
    {
        document.querySelector('h3#ssuiviTitlebg').innerHTML = `ENREGISTREMENT BAGAGES`;

            function loadProgrammesSuivi() {
                var selectLigne = document.querySelector('#sdeptscouridlignesuivi');
                var selectDate = document.querySelector('#scourdeptchoisirdatesuivi');
                var selectProg = document.querySelector('#scourdeptidprogsuivi');
                if (!selectLigne || !selectDate || !selectProg) {
                    return;
                }

                var ligne = parseLigneOption(selectLigne.options[selectLigne.selectedIndex].value);
                var verifidate = selectDate.value;
                selectProg.options.length = 1;

                if (!ligne.ident || !verifidate) {
                    return;
                }

                var httpInfoprog = new XMLHttpRequest();
                httpInfoprog.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifprogramm/${encodeURIComponent(ligne.ident)}/${verifidate}`, true);
                httpInfoprog.onload = function () {
                    var resultp;
                    try {
                        resultp = JSON.parse(httpInfoprog.responseText);
                    } catch (err) {
                        return;
                    }

                    if (!resultp || !resultp.length) {
                        selectProg.options.length = 1;
                        return;
                    }

                    resultp.forEach(function (item) {
                        var opt = document.createElement('option');
                        opt.value = `${item.code_progr}/${item.ident_ligne}/${item.heure}/${item.id_ligneheure}/${item.depart_code}`;
                        opt.innerHTML = `${item.code_progr}/${item.heure}`;
                        selectProg.add(opt);
                    });
                };
                httpInfoprog.send();
            }

            let infoligner = document.querySelector('#sdeptscouridlignesuivi');
            if (infoligner !== null)
            infoligner.onchange = () => {
                document.querySelector('#scourdeptidprogsuivi').options.length = 1;
                document.querySelector('#quartieridbgsuivi').options.length = 1;
                document.querySelector('#snumcoderecu').value = '';
                document.querySelector('#snombreenvid').value = '';
                let httptypequartrbg;

                    const lidlignes = document.querySelector('#sdeptscouridlignesuivi')
                    .options[document.querySelector('#sdeptscouridlignesuivi').options.selectedIndex].value;
                    var ligne = parseLigneOption(lidlignes);
                    if (!ligne.gareDest) {
                        return;
                    }
                    httptypequartrbg = new XMLHttpRequest();
                    
                    httptypequartrbg.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifquart/${encodeURIComponent(ligne.gareDest)}`, true);
                    httptypequartrbg.onload = () => 
                    {
                        const courquarbg = JSON.parse(httptypequartrbg.responseText);
                        if (courquarbg == '') {
                            document.querySelector('#quartieridbgsuivi').options.length = 1;
                        }
                        else{
                            if (Object.entries(courquarbg).length >= 1) {
                                            
                                for (let key in Object.entries(courquarbg)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${courquarbg[key].nom_quartier}`;
                                    opt.innerHTML = `${courquarbg[key].nom_quartier}`;
                                    document.querySelector('#quartieridbgsuivi').add(opt);
                                }
                            } else {
                                document.querySelector('#quartieridbgsuivi').options.length = 1;
                            }
                        }
                    };
                    httptypequartrbg.setRequestHeader('Content-Type', 'application/json');
                    httptypequartrbg.send();

                    loadProgrammesSuivi();
            }
            let infoligne = document.querySelector('#scourdeptchoisirdatesuivi');
            if (infoligne !== null)
            infoligne.onchange = () => {
                document.querySelector('#snumcoderecu').value = '';
                document.querySelector('#snombreenvid').value = '';
                loadProgrammesSuivi();
            };

            /*let infrecubag = document.querySelector('#snumcoderecu');
            if (infrecubag !== null)
            infrecubag.onkeyup = () => {
                let httpInfosbag;
                if (window.XMLHttpRequest) {
                    httpInfosbag = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfosbag = new ActiveXObject("Microsoft.XMLHTTP");
                }

                var anencrbag = document.querySelector('#idanencourenv').value;

                var verificatbag = document.querySelector('#snumcoderecu').value;

                 const verife = `"${verificatbag}${anencrbag}"`;

                const lidlignes = document.querySelector('#sdeptscouridlignesuivi')
                .options[document.querySelector('#sdeptscouridlignesuivi').options.selectedIndex].value;
                var lidlignes1 = lidlignes.split('/');
                var lidlignes2 = lidlignes1[0];
                httpInfosbag.open('GET', window.location.origin + `${APP_ROOT}/confirmation/sverifinforecus/${verificatbag}${anencrbag}`, true);
                httpInfosbag.onload = () => {
                    
                    const infosbag = JSON.parse(httpInfosbag.responseText);
                    if (infosbag == null) {
                        document.querySelector('#ssmsmbg').style.display = 'block';
                        document.querySelector('#ssmsmvfbg').innerHTML = `Verifier le code de bagage saisi!`;
                        document.querySelector('#sidbagenv').value = "";
                        document.querySelector('#sgddeptsuiviid').value = "";
                        document.querySelector('#ssousgddeptsuiviid').value = "";
                        document.querySelector('#stypbagid').value = "";
                        document.querySelector('#snombrebgsuiviid').value = "";
                        document.querySelector('#scontenubgsuiviid').value = "";
                        document.querySelector('#sidgarbag').value = "";
                        document.querySelector('#lgidbagages').value = "";
                    } else {
                        if (Object.entries(infosbag).length > 1) {
                            
                            if (String(infosbag.id_bagage) == String(verife)) {
                                console.debug(`${infosbag.id_bagage}-${verife}-${infosbag.ident_ligne}-${lidlignes2}`, console.memory);
                                document.querySelector('#sidbagenv').value = `${infosbag.id_bagage}`;
                                document.querySelector('#sgddeptsuiviid').value = `${infosbag.idgarebag}`;
                                document.querySelector('#ssousgddeptsuiviid').value = `${infosbag.idsgarebag}`;
                                document.querySelector('#stypbagid').value = `${infosbag.typebagages}`;
                                document.querySelector('#snombrebgsuiviid').value = `${infosbag.nombrebagage}`;
                                document.querySelector('#scontenubgsuiviid').value = `${infosbag.contenubagage}`;
                                document.querySelector('#sidgarbag').value = `${infosbag.gidarrbag}`;
                                document.querySelector('#lgidbagages').value = `${infosbag.lgidbagage}`;
                                document.querySelector('#ssmsmbg').style.display = 'none';
                            } else {
                                console.debug(`${verife}`, console.memory);
                                
                                document.querySelector('#sidbagenv').value = "";
                                document.querySelector('#sgddeptsuiviid').value = "";
                                document.querySelector('#ssousgddeptsuiviid').value = "";
                                document.querySelector('#stypbagid').value = "";
                                document.querySelector('#snombrebgsuiviid').value = "";
                                document.querySelector('#scontenubgsuiviid').value = "";
                                document.querySelector('#sidgarbag').value = "";
                                document.querySelector('#lgidbagages').value = "";
                                document.querySelector('#ssmsmbg').style.display = 'block';
                                document.querySelector('#ssmsmvfbg').innerHTML = `Verifier le code bagage saisi!`;
                            }
                        }
                    }
                };
                httpInfosbag.setRequestHeader('Content-Type', 'application/json');
                httpInfosbag.send();
            };*/
            let infrecubag = document.querySelector('#snumcoderecu');
                let timerBag = null;

                if (infrecubag !== null) {
                    infrecubag.onkeyup = () => {

                        // ⛔ annule l'exécution précédente
                        clearTimeout(timerBag);

                        // ⏱ attend que l'utilisateur ait fini de taper
                        timerBag = setTimeout(() => {

                            let httpInfosbag;
                            if (window.XMLHttpRequest) {
                                httpInfosbag = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosbag = new ActiveXObject("Microsoft.XMLHTTP");
                            }

                            var anencrbag = document.querySelector('#idanencourenv').value;
                            var verificatbag = document.querySelector('#snumcoderecu').value;

                            // 🔒 sécurité minimale : pas de requête si vide
                            if (!verificatbag || !anencrbag) return;

                            const verife = `${verificatbag}${anencrbag}`;

                            const lidlignes = document.querySelector('#sdeptscouridlignesuivi')
                                .options[document.querySelector('#sdeptscouridlignesuivi').options.selectedIndex].value;
                            var lidlignes1 = lidlignes.split('/');
                            var lidlignes2 = lidlignes1[0];

                            httpInfosbag.open(
                                'GET',
                                window.location.origin + `${APP_ROOT}/confirmation/sverifinforecus/${verificatbag}${anencrbag}`,
                                true
                            );

                            httpInfosbag.onload = () => {

                                const infosbag = JSON.parse(httpInfosbag.responseText);

                                if (infosbag == null) {
                                    document.querySelector('#ssmsmbg').style.display = 'block';
                                    document.querySelector('#ssmsmvfbg').innerHTML = `Verifier le code de bagage saisi!`;

                                    document.querySelector('#sidbagenv').value = "";
                                    document.querySelector('#sgddeptsuiviid').value = "";
                                    document.querySelector('#ssousgddeptsuiviid').value = "";
                                    document.querySelector('#stypbagid').value = "";
                                    document.querySelector('#snombrebgsuiviid').value = "";
                                    document.querySelector('#scontenubgsuiviid').value = "";
                                    document.querySelector('#sidgarbag').value = "";
                                    document.querySelector('#lgidbagages').value = "";
                                } else {

                                    if (Object.entries(infosbag).length > 1) {

                                        if (infosbag.id_bagage === verife) {

                                            document.querySelector('#sidbagenv').value = infosbag.id_bagage;
                                            document.querySelector('#sgddeptsuiviid').value = infosbag.idgarebag;
                                            document.querySelector('#ssousgddeptsuiviid').value = infosbag.idsgarebag;
                                            document.querySelector('#stypbagid').value = infosbag.typebagages;
                                            document.querySelector('#snombrebgsuiviid').value = infosbag.nombrebagage;
                                            document.querySelector('#scontenubgsuiviid').value = infosbag.contenubagage;
                                            document.querySelector('#sidgarbag').value = infosbag.gidarrbag;
                                            document.querySelector('#lgidbagages').value = infosbag.lgidbagage;
                                            document.querySelector('#ssmsmbg').style.display = 'none';
                                        } else {
                                            document.querySelector('#sidbagenv').value = "";
                                            document.querySelector('#sgddeptsuiviid').value = "";
                                            document.querySelector('#ssousgddeptsuiviid').value = "";
                                            document.querySelector('#stypbagid').value = "";
                                            document.querySelector('#snombrebgsuiviid').value = "";
                                            document.querySelector('#scontenubgsuiviid').value = "";
                                            document.querySelector('#sidgarbag').value = "";
                                            document.querySelector('#lgidbagages').value = "";
                                            //document.querySelector('#slgidbagages').value = `${verificatbag}${anencrbag} ${infosbag.id_bagage}`;
                                            document.querySelector('#ssmsmbg').style.display = 'block';
                                            document.querySelector('#ssmsmvfbg').innerHTML = `Verifier le code bagage saisi!`;
                                        }
                                    }
                                }
                            };

                            httpInfosbag.send();

                        }, 600); // ⏱ 600ms = fin de saisie
                    };
                }


            let infolignep = document.querySelector('#scourdeptidprogsuivi');
            if (infolignep !== null)
            infolignep.onchange = () => {
                var verifintbag = document.querySelector('#lgidbagages').value;

                   const slidlignes = document.querySelector('#sdeptscouridlignesuivi')
                    .options[document.querySelector('#sdeptscouridlignesuivi').options.selectedIndex].value;
                    var slidlignes2 = parseLigneOption(slidlignes).ident;

                let httpRequestitines;
                httpRequestitines = new XMLHttpRequest();
                httpRequestitines.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifitine/${verifintbag}/${slidlignes2}`, true);
                httpRequestitines.onload = () => {
                const donitiness = JSON.parse(httpRequestitines.responseText);
                    if((donitiness.length > 0 ) || (verifintbag === slidlignes2))
                    {   
                        document.getElementById("snombreenvid").disabled = false;
                    }
                    else
                    {   
                        document.querySelector('#ssmlg').style.display = 'block';
                        document.querySelector('#ssmsmlg').innerHTML = `Verifiez la ligne choisi et comparer avec celui du recu`;
                        document.getElementById("snombreenvid").disabled = true;
                    }
                };
                httpRequestitines.setRequestHeader('Content-Type', 'application/json');
                httpRequestitines.send();
            };

            sverifnb = function () 
                        {
                var entree = parseInt(document.querySelector('#snombreenvid').value);
                    var n = document.querySelector('#snombreenvid').value;
                    var exist = parseInt(document.querySelector('#snombrebgsuiviid').value);
                        
                if(entree > exist) 
                {
                    document.querySelector('#ssmsmtbg').style.display = 'block';
                    document.querySelector('#ssmsmontantbg').innerHTML = `le mombre que vous aviez saisi dépasse le nombre de bagage`;
                    
                    document.querySelector('#snombreenvid').value = 'VERIFIER NOMBRE';  
                } 
                else
                {

                    document.querySelector('#ssmsmtbg').style.display = 'none';

                    document.querySelector('#snombreenvid').value = n ;
                    
                }
            };

        e.onclick = function (){
            let bordesForm = document.querySelector('#sbordesFormsuivi');
            bordesForm.setAttribute('action', `${APP_ROOT}/Confirmation/senregbagages/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- adreportjsesc.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adreportjsesc').forEach(function (e) 
    {
        document.querySelector('h3#Titlerepesc').innerHTML = `EXERCICE MENSUEL TICKET GUICHETIER ESCAL`;

        let infgar = document.querySelector('#departgaridentifesc');
        
        if (infgar !== null) 
        infgar.onchange = () => {
            let httpInfosgar;
            if (window.XMLHttpRequest) {
                httpInfosgar = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgar = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idcaissiersesc').options.length = 1;

                    var verificatgar = document.querySelector('#departgaridentifesc').value;
                    
                    httpInfosgar.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeusesesc/${verificatgar}`, true);
                    httpInfosgar.onload = () => {
                        const infosgar = JSON.parse(httpInfosgar.responseText);
                        
                        if (Object.entries(infosgar).length > 0) {                            
                        
                                for (let key in Object.entries(infosgar)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgar[key].roleattribut}`;
                                    opt.innerHTML = `${infosgar[key].username}`;
                                    document.querySelector('#idcaissiersesc').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idcaissiersesc').options.length = 1;
                        }
                        
                    };
                    httpInfosgar.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgar.send();
                };
        e.onclick = function () {
        let tickForm = document.querySelector('#tickFormesc');
            tickForm.setAttribute('action', `${APP_ROOT}/Rapport/exoreportsesc/${e.dataset.ekey}/${e.dataset.idgares}`);
        }

    })
});
;
/* --- adreportglesc.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adreportglesc').forEach(function (e) 
    {
        document.querySelector('h3#Titlerepsesc').innerHTML = `ETAT GLOBAL TICKET GUICHETIER ESCAL`;

        let infgars = document.querySelector('#garidentifsesc');
        
        if (infgars !== null) 
        infgars.onchange = () => {
            let httpInfosgars;
            if (window.XMLHttpRequest) {
                httpInfosgars = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgars = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idscaissieresc').options.length = 1;

                    var verificatgars = document.querySelector('#garidentifsesc').value;
                    
                    httpInfosgars.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeusesesc/${verificatgars}`, true);
                    httpInfosgars.onload = () => {
                        const infosgars = JSON.parse(httpInfosgars.responseText);
                        
                        if (Object.entries(infosgars).length > 0) {                            
                        
                                for (let key in Object.entries(infosgars)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgars[key].roleattribut}`;
                                    opt.innerHTML = `${infosgars[key].username}`;
                                    document.querySelector('#idscaissieresc').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idscaissieresc').options.length = 1;
                        }
                        
                    };
                    httpInfosgars.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgars.send();
                };
        e.onclick = function () {
        let tickForms = document.querySelector('#tickFormsesc');
            tickForms.setAttribute('action', `${APP_ROOT}/Rapport/reportsesc/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
