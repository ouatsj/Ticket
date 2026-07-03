document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addconfirmadmintran').forEach(function (e) {
        
        document.querySelector('h3#admincTitletran').innerHTML = `CONFIRMATION`;

        let c = document.querySelector('#adminconfirme_infotran');
        if (c !== null)
        c.onclick = () => {
            
            //verification code de confirmation
            let Request;
            
            if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                Request = new XMLHttpRequest();
            } else if (window.ActiveXObject) { // IE 6 and older
                Request = new ActiveXObject("Microsoft.XMLHTTP");
            }
            
            var codes = document.querySelector("#admincodeconfirmtran").value;
            document.querySelector('#axeconfirmtran').options.length = 1;
            document.querySelector('#depargarestran').options.length = 1;
            Request.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifcodeconftran/${codes}`, true);
            Request.onload = () => {
                const dons = JSON.parse(Request.responseText);
                    if (dons == null) {
                        document.querySelector('#adminmessageptran').style.display = 'block';
                        document.querySelector('#adminerreurMessageptran').innerHTML = `Cet ticket ne peut pas être confirmé ici.`;
                        document.querySelector('#adminheuredtran').style.display = 'none';
                        document.querySelector('#admindepsiegtran').style.display = 'none';
                        document.querySelector('#adminquartconftran').style.display = 'none';
                        document.querySelector('#adminnomptran').innerText = ``;
                        document.querySelector('#adminprenomptran').innerText = ``;
                        document.querySelector('#admincontactptran').innerHTML = ``;
                        document.querySelector('#adminrefptran').innerHTML = ``;
                        document.querySelector('#admindirectionptran').innerHTML = ``;
                        document.querySelector('#admincodecptran').innerHTML = ``;
                        document.querySelector('#axeconfirmtran').style.display = 'none';
                        document.querySelector('#ligneconflgtran').value = '';
                    }
                    else 
                    {
                        
                        if (Object.entries(dons).length >= 1){
                            document.querySelector('#adminerreurMessageptran').innerHTML = '';
                            document.querySelector('#adminheuredtran').style.display = 'block';
                            document.querySelector('#admindepsiegtran').style.display = 'block';
                            document.querySelector('#adminquartconftran').style.display = 'block';
                            document.querySelector('#axeconfirmtran').style.display = 'block';
                            document.querySelector('#adminnomptran').innerText = `NOM: ${dons.nom_client}`;
                            document.querySelector('#adminprenomptran').innerText = `PRENOM: ${dons.prenom_client}`;
                            document.querySelector('#admincontactptran').innerHTML = `CONTACT: ${dons.contact_client}`;
                            document.querySelector('#adminrefptran').innerHTML = `REFERENCE CNIB: ${dons.num_CNIB}`;
                            document.querySelector('#admindirectionptran').innerHTML = `AXE: ${dons.nom_ligne}`;
                            document.querySelector('#admincodecptran').innerHTML = `CODE VENTE: ${dons.code_non_pass}`;
                            document.querySelector('#adminpasseptran').value = `${dons.code_non_pass}`;
                            document.querySelector('#adminpascodeticktran').value = `${dons.codeticket}`;
                            document.querySelector('#adminclientidptran').value = `${dons.id_client_npass}`;
                            document.querySelector('#adminpasnomptran').value = `${dons.nom_client}`;
                            document.querySelector('#adminpasprenomptran').value = `${dons.prenom_client}`;
                            document.querySelector('#adminpascontactptran').value = `${dons.contact_client}`;
                            document.querySelector('#adminpascnibptran').value = `${dons.num_CNIB}`;
                            document.querySelector('#adminpasdateptran').value = `${dons.date_delivre}`;
                            document.querySelector('#adcommentclienttran').value = `${dons.comment_client}`;
                            document.querySelector('#adminlieutran').value = `${dons.lieu_delivre}`;
                            document.querySelector('#admimtypetran').value = `${dons.type_client}`;
                            document.querySelector('#dateventeconftran').value = `${dons.datevente}`;
                            document.querySelector('#axeligneconftran').value = `${dons.id_ligne_pass}`;
                            document.querySelector('#ligneconflgtran').value = `${dons.nom_ligne}`;
                            document.querySelector('#admincodecpastran').value = `${dons.code_non_pass}`;
                            document.querySelector('#adlignehconftran').value = `${dons.id_ligneheure}`;
                            document.querySelector('#admincodeconfitran').value = `${dons.tamponcod}`;


                        } 
                        else 
                        {
                            document.querySelector('#adminheuredtran').style.display = 'none';
                            document.querySelector('#admindepsiegtran').style.display = 'none';
                            document.querySelector('#adminquartconftran').style.display = 'none';
                            document.querySelector('#axeconfirmtran').style.display = 'none';
                        }
                        
                            let Requestslg = new XMLHttpRequest();
                            const confirheurelg = document.querySelector('#ligneconflgtran').value;
                            var postmob = confirheurelg.split('-');
                            var avmob = postmob[0];
                            var apmob = postmob[1];
                            Requestslg.open('GET', window.location.origin + `${APP_ROOT}/confirmation/veriflignelg/${apmob}-${avmob}`, true);
                            Requestslg.onload = () => {
                                const datas2lg = JSON.parse(Requestslg.responseText);
                                if (Object.entries(datas2lg).length >= 1) {
                                    for (let key in Object.entries(datas2lg)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${datas2lg.ident_ligne}`;
                                        opt.innerHTML = `${datas2lg.nom_ligne}`;
                                        document.querySelector('#axeconfirmtran').add(opt);   
                                    }
                                }else{
                                    document.querySelector('#axeconfirmtran').options.length = 1;
                                }
                            };
                            Requestslg.setRequestHeader('Content-Type', 'application/json');
                            Requestslg.send();
                       
                            
                                            
                            let axeselectconf = document.querySelector('#axeconfirmtran');
                            if (axeselectconf !== null)
                                axeselectconf.onchange = () => 
                                {
                               
                                    var datdepart = document.querySelector('#dateventeconftran').value;
                                    var datdepartactu = document.querySelector('#datactutran').value;
                                    var date1  = new Date(datdepart);
                                    var date2 = new Date(datdepartactu);
                                    // différence des heures
                                    var time_diff = date2.getTime() - date1.getTime();
                                        // différence de jours
                                    const days_Diff = time_diff / (1000 * 3600 * 24);
                                    if(days_Diff < 28 || days_Diff < 29 || days_Diff < 30 || days_Diff < 31)    
                                    {
                                        const heureaxeconf = document.querySelector('#axeconfirmtran').options[document.querySelector('#axeconfirmtran').options.selectedIndex].value;
                            
                                        let Requests = new XMLHttpRequest();
                                        let Requests1 = new XMLHttpRequest();
                                        const confirheure = document.querySelector('#axeconfirmtran').
                                        options[document.querySelector('#axeconfirmtran').options.selectedIndex].value;
                                        
                                        var postmobt = confirheure.split('-');
                                        var confirh = postmobt[0];
                                        var apmobt = postmobt[1];
                                        var dateactuel = document.querySelector('#datactutran').value;
                                    
                                        Requests.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfprog/${confirheure}/${dateactuel}`, true);
                                        Requests.onload = () => {
                                            const datas2 = JSON.parse(Requests.responseText);
                                            if (Object.entries(datas2).length >= 1) {
                                                for (let key in Object.entries(datas2)) {
                                                    let opt = document.createElement('option');
                                                    opt.value = `${datas2[key].code_progr}/${datas2[key].typetarif}`;
                                                    opt.innerHTML = `${datas2[key].heure}/${datas2[key].date_progr}`;
                                                    document.querySelector('#adminheuredtran').add(opt);  
                                                }
                                            }else{
                                                document.querySelector('#adminheuredtran').options.length = 1;
                                            }
                                        };
                                        Requests.setRequestHeader('Content-Type', 'application/json');
                                        Requests.send();
                                        
                                        Requests1.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifsoug/${confirh}`, true);
                                        Requests1.onload = () => {

                                        const datasg2 = JSON.parse(Requests1.responseText);
                                            if (Object.entries(datasg2).length >= 1) {
        
                                                for (let key in Object.entries(datasg2)) {
                                                    let opt1 = document.createElement('option');
                                                    opt1.value = `${datasg2[key].code_gaexp}/${datasg2[key].idsousgare}`;
                                                    opt1.innerHTML = `${datasg2[key].nom_gaep}/${datasg2[key].nomsousgare}`;
                                                    document.querySelector('#depargarestran').add(opt1); 
                                                }
                                            }else{
                                                
                                                document.querySelector('#depargarestran').options.length = 1;
                                            }
                                        };
                                        Requests1.setRequestHeader('Content-Type', 'application/json');
                                        Requests1.send();
                                        
                                        let httpRequetesquart = new XMLHttpRequest();
                                            httpRequetesquart.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfquart/${heureaxeconf}`, true);
                                        httpRequetesquart.onload = () => {
                                            const dataq = JSON.parse(httpRequetesquart.responseText);
                                            if(dataq == ''){
                                                document.querySelector('#adminquartconftran').options.length = 1;
                                            }else{
                                                if (Object.entries(dataq).length >= 1) {
                                                            
                                                    for (let key in Object.entries(dataq)) {
                                                        let opt = document.createElement('option');
                                                        opt.value = `${dataq[key].nom_quartier}`;
                                                        opt.innerHTML = `${dataq[key].nom_quartier}`;
                                                        document.querySelector('#adminquartconftran').add(opt);
                                                    }
                                                } else {
                                                    document.querySelector('#adminquartconftran').options.length = 1;
                                                }
                                            }
                                                
                                                    
                                        };
                                        httpRequetesquart.setRequestHeader('Content-Type', 'application/json');
                                        httpRequetesquart.send();
                                    }
                                    else
                                    {
                                        document.querySelector('#adminheuredtran').style.display = 'none';
                                        document.querySelector('#admindepsiegtran').style.display = 'none';
                                        document.querySelector('#adminquartconftran').style.display = 'none';
                                        document.querySelector('#adminnomptran').innerText = ``;
                                        document.querySelector('#adminprenomptran').innerText = ``;
                                        document.querySelector('#admincontactptran').innerHTML = ``;
                                        document.querySelector('#adminrefptran').innerHTML = ``;
                                        document.querySelector('#admindirectionptran').innerHTML = ``;
                                        document.querySelector('#admincodecptran').innerHTML = ``;
                                        document.querySelector('#billettran').style.display = 'block';
                                        document.querySelector('#billetSmstran').innerHTML = `Billet non valable, la durée de validité est dépassée.`;
    
                                    }
                                };
                                        
                                            
                    }
               
            };
            Request.setRequestHeader('Content-Type', 'application/json');
            Request.send(); 
        };

        let heurdeprt = document.querySelector('#adminheuredtran');
        if (heurdeprt !== null)
            heurdeprt.onchange = () => {
                
                document.querySelector('#admindepsiegtran').options.length = 1;
                const Requeste = new XMLHttpRequest();
                const selectorp = document.querySelector('#adminheuredtran').options[document.querySelector('#adminheuredtran').
                options.selectedIndex].value;
                var selectorp1 = selectorp.split('/');
                var selectorp2 = selectorp1[0];
                var selectorp3 = selectorp1[1];
                Requeste.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/siegdispo/${selectorp2}`, true);
                Requeste.onload = () => {
                    const datasgc = JSON.parse(Requeste.responseText);
                    if (Object.entries(datasgc).length >= 1) {
                        for (let key in Object.entries(datasgc)) {
                            
                            document.querySelector('#adcaissepvend_tran').value = `${datasgc[key].intervalle1}`;
                            document.querySelector('#adcaissedpvend_tran').value = `${datasgc[key].intervalle2}`;
                            document.querySelector('#addirectidtran').value = `${datasgc[key].nom_ligne}`;
                            document.querySelector('#adconfheuretran').value = `${datasgc[key].heure}`;
                            document.querySelector('#addateconfirmetran').value = `${datasgc[key].date_progr}`;
                            document.querySelector('#adcategotran').value = `${datasgc[key].categori}`;
                            document.querySelector('#adlignehconftran').value = `${datasgc[key].id_ligneheure}`;
                            document.querySelector('#adprogramconftran').value = `${datasgc[key].code_progr}`;
                        }
                    } 
                    const Requestbis = new XMLHttpRequest();
                    const pldebut = document.querySelector('#adcaissepvend_tran').value;
                    const plfin = document.querySelector('#adcaissedpvend_tran').value;
                    const cfdir = document.querySelector('#addirectidtran').value;
                    const hconfir = document.querySelector('#adconfheuretran').value;
                    const dconfirme = document.querySelector('#addateconfirmetran').value;
                    Requestbis.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${selectorp2}/${dconfirme}/${cfdir}/${hconfir}/${pldebut}/${plfin}`, true);
                    Requestbis.onload = () => {
                        const datasgcbis = JSON.parse(Requestbis.responseText);
                        if (Object.entries(datasgcbis).length >= 1) {
                            for (let key in Object.entries(datasgcbis)) {
                                let opt = document.createElement('option');
                                opt.value = `${datasgcbis[key].siege_num}`;
                                opt.innerHTML = `${datasgcbis[key].siege_num}`;
                                document.querySelector('#admindepsiegtran').add(opt);
                            }
                        } else {
                            document.querySelector('#admindepsiegtran').options.length = 1;
                        }
                    };
                    Requestbis.setRequestHeader('Content-Type', 'application/json');
                    Requestbis.send();
                };
                Requeste.setRequestHeader('Content-Type', 'application/json');
                Requeste.send();
            };

            let depsiegconf = document.querySelector('#admindepsiegtran');
            if (depsiegconf !== null)
            depsiegconf.onchange = () => {
                    
                    let Requestsiegevenduconf;
                    
                    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                        Requestsiegevenduconf = new XMLHttpRequest();
                    } else if (window.ActiveXObject) { // IE 6 and older
                        Requestsiegevenduconf = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                    const dp_progconf = document.querySelector('#adprogramconftran').value;
                    const dp_siegeconf = document.querySelector('#admindepsiegtran').options[document.querySelector('#admindepsiegtran').options.selectedIndex].value;
                    Requestsiegevenduconf.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${dp_progconf}/${dp_siegeconf}`, true);
                    Requestsiegevenduconf.onload = () => 
                    {
                        
                            const confdonsieg = JSON.parse(Requestsiegevenduconf.responseText);
                            if (confdonsieg == '')
                                    {
                                        let httpSiegsconf;
                                        httpSiegsconf = new XMLHttpRequest();

                                        httpSiegsconf.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${dp_progconf}/${dp_siegeconf}`, true);
                                        httpSiegsconf.onload = () => 
                                        {
                                            const dongconf= JSON.parse(httpSiegsconf.responseText);
                                            document.querySelector('#adminmessconftran').style.display = 'none';
                                            if (Object.entries(dongconf).length >= 1)
                                        {
                                            for (let key in Object.entries(dongconf)) {
                                                document.querySelector('#adminidtampoconftran').value = `${dongconf[key].idtamp}`;                    
                                                document.querySelector('#adminsiegselectconftran').value = `${dongconf[key].numsieg}`;
                                            }

                                        }
                                        
                                        };
                                        httpSiegsconf.setRequestHeader('Content-Type', 'application/json');
                                        httpSiegsconf.send();
                                    }
                                    else {
                                        document.querySelector('#admindepsiegtran').value = '';     
                                        if (Object.entries(confdonsieg).length >= 1)
                                        {
                                            for (let key in Object.entries(confdonsieg)) {
                                                document.querySelector('#adminidtampoconftran').value = `${confdonsieg[key].idtamp}`;                    
                                                document.querySelector('#adminsiegselectconftran').value = `${confdonsieg[key].numsieg}`;
                                            }

                                        }
                                        document.querySelector('#adminmessconftran').style.display = 'block';
                                        document.querySelector('#adminerreurMessconftran').innerHTML = `Siege déjà utilisé.`; 
                                    }
                    };
                    Requestsiegevenduconf.setRequestHeader('content-Type', 'text/json');
                    Requestsiegevenduconf.send();
                };
            //bouton annuler
                butoncliconf = document.querySelector('#adminconfresettran');
                if (butoncliconf !== null) {
                    butoncliconf.onclick = () => 
                    {
                        let httpSiegeselectconf;
                        httpSiegeselectconf = new XMLHttpRequest();
                        const siegselectconf = document.querySelector('#adminsiegselectconftran').value;
                        const idtapconf = document.querySelector('#adminidtampoconftran').value;
                        httpSiegeselectconf.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtapconf}/${siegselectconf}`, true);
                        httpSiegeselectconf.onload = () => 
                        {
                            const donselectconf = JSON.parse(httpSiegeselectconf.responseText);
                            console.debug(`${typeof donselectconf} - ${donselectconf.attributes}`, console.memory);
                            document.querySelector('#adminmessconftran').style.display = 'none';
                            
                        };
                        httpSiegeselectconf.setRequestHeader('Content-Type', 'application/json');
                        httpSiegeselectconf.send();
    
                    
                    };
                }       
                       
        e.onclick = function () {
            let adcForm = document.querySelector('#admincFormtran');
            adcForm.setAttribute('action', `${APP_ROOT}/Confirmation/adminconfirmetran/${e.dataset.ckey}`);
        }
    })
});