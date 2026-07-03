document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addreprogrammetransit').forEach(function (e) {
        document.querySelector('h3#rTitletransit').innerHTML = `REPROGRAMMATION`;

    
        let infos = document.querySelector('#reprogrammer_infostransit');
        if (infos !== null)
            infos.onclick = () => {
                //verification code de reprogrammation
                let httpRequestRep;
                
                if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                    httpRequestRep = new XMLHttpRequest();
                } else if (window.ActiveXObject) { // IE 6 and older
                    httpRequestRep = new ActiveXObject("Microsoft.XMLHTTP");
                }
                
                var cocl = document.querySelector("#codeclientptransit").value;
                
                httpRequestRep.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/verifcodetransit/${cocl}`, true);
                httpRequestRep.onload = () => {
                    const donnees = JSON.parse(httpRequestRep.responseText);
                    if (donnees == null) {
                        
                        document.querySelector('#smsptransit').style.display = 'block';
                        document.querySelector('#erreurSmsptransit').innerHTML = `Cet ticket ne peut pas être reprogrammé ici.`;
                        document.querySelector('#nomclptransit').innerHTML = ``;
                        document.querySelector('#prenomclptransit').innerHTML = ``;
                        document.querySelector('#contactclptransit').innerHTML = ``;
                        document.querySelector('#refclptransit').innerHTML = ``;
                        document.querySelector('#directionclptransit').innerHTML = ``;
                        document.querySelector('#codeclptransit').innerHTML = ``;
                        document.querySelector('#heureclptransit').innerHTML = ``;
                        document.querySelector('#heuredepartptransit').style.display = 'none';
                        document.querySelector('#numsiegeptransit').style.display = 'none';
                        document.querySelector('#heuredepartptransit').options.length = 1;
                    } else 
                    {
                           
                        if (Object.entries(donnees).length >= 1){
                                document.querySelector('#smsptransit').style.display = 'none';
                                document.querySelector('#heuredepartptransit').style.display = 'block';
                                document.querySelector('#numsiegeptransit').style.display = 'block';       
                                document.querySelector('#nomclptransit').innerHTML = `NOM: ${donnees.nom_client}`;
                                document.querySelector('#prenomclptransit').innerHTML = `PRENOM: ${donnees.prenom_client}`;
                                document.querySelector('#contactclptransit').innerHTML = `CONTACT: ${donnees.contact_client}`;
                                document.querySelector('#refclptransit').innerHTML = `REFERENCE CNIB: ${donnees.num_CNIB}`;
                                document.querySelector('#directionclptransit').innerHTML = `AXE: ${donnees.nom_ligne}`;
                                document.querySelector('#codeclptransit').innerHTML = `CODE TICKET: ${donnees.code_passager} DATE : ${donnees.date_progr}`;
                                document.querySelector('#heureclptransit').innerHTML = `HEURE: ${donnees.heure} SIEGE :${donnees.num_siege_categorie}`;
                                document.querySelector('#passerptransit').value = `${donnees.code_passager}`;
                                document.querySelector('#idclpasseridtransit').value = `${donnees.ligne_id}`;
                                document.querySelector('#client_idptransit').value = `${donnees.id_client_pass}`;
                                document.querySelector('#pasnomptransit').value = `${donnees.nom_client}`;
                                document.querySelector('#pasprenomptransit').value = `${donnees.prenom_client}`;
                                document.querySelector('#pascontactptransit').value = `${donnees.contact_client}`;
                                document.querySelector('#pascnibptransit').value = `${donnees.num_CNIB}`;
                                document.querySelector('#pasdateptransit').value = `${donnees.date_delivre}`;
                                document.querySelector('#nsiegeptransit').value = `${donnees.num_siege_categorie}`;
                                document.querySelector('#delivrelietransit').value = `${donnees.lieu_delivre}`;
                                document.querySelector('#depoldtransit').value = `${donnees.code_pro}`;
                                document.querySelector('#id_compagatr').value = `${donnees.id_compaga}`;
                                document.querySelector('#codeidtransit').value = `${donnees.code_passager}`;
                                document.querySelector('#codeticketstransit').value = `${donnees.tamponcod}`;
                                document.querySelector('#lgcodeticketstransit').value = `${donnees.tamponcodtr}`;
                                document.querySelector('#codenonptransit').value = `${donnees.code_non_pass}`;
                                document.querySelector('#statconftransit').value = `${donnees.statut_confirme}`;
                                document.querySelector('#statreptransit').value = `${donnees.statut_reprog}`;
                                document.querySelector('#programreptransit').value = `${donnees.code_progr}`;
                                document.querySelector('#depgidtransit').value = `${donnees.gaexp_lg}`;
                                document.querySelector('#dateventereptransit').value = `${donnees.datep_create}`;
                                document.querySelector('#gareidentiftransit').value = `${donnees.gareidentif}`;
                                document.querySelector('#departclientidgare').value = `${donnees.departclient_idgare}`;

                        } else {
                            document.querySelector('#heuredepartptransit').style.display = 'none';
                            document.querySelector('#numsiegeptransit').style.display = 'none';
                        }
                        var datdepartrep = document.querySelector('#dateventereptransit').value;
                        var daterepactu = document.querySelector('#actueldatereptransit').value;
                        var daterep1 = new Date(datdepartrep);
                        var daterep2 = new Date(daterepactu);
                        // différence des heures
                        var time_diff = daterep2.getTime() - daterep1.getTime();
                            // différence de jours
                        const days_Diff = time_diff / (1000 * 3600 * 24);

                        if(days_Diff < 28 || days_Diff < 29 || days_Diff < 30 || days_Diff < 31)    
                        {

                            //const dates = document.querySelector('#datereptransit').value;
                            const hdpaxep = `${donnees.ligne_id}`;
                            const hcl = `${donnees.code_progr}`;
                            const compag = `${donnees.id_compaga}`;

                            const prx = `${donnees.prixvente}`;
                            const ligneheure =`${donnees.heure_identif}`;
                            
                            const compagn = document.querySelector('#id_compagatr').value;

                                if(compagn === '5001' || compagn === '5002'){
                                    let httpRequestews;
                                    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                                        httpRequestews = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) { // IE 6 and older
                                        httpRequestews = new ActiveXObject("Microsoft.XMLHTTP");
                                    }

                                    httpRequestews.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/hdepartpreprotrt/${hdpaxep}/${compag}/${prx}`, true);
       
                                    httpRequestews.onload = () => {
                                        const data2 = JSON.parse(httpRequestews.responseText);
                                
                                        if (Object.entries(data2).length >= 1) {
                                            for (let key in Object.entries(data2)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${data2[key].code_progr}/${data2[key].id_ligneheure}/${data2[key].typetarif}`;
                                                opt.innerHTML = `${data2[key].heure}/${data2[key].date_progr}`;
                                                document.querySelector('#heuredepartptransit').add(opt);
                                            }
                                        } else {
                                            document.querySelector('#heuredepartptransit').options.length = 1;
                                        }
                                    };
                                    httpRequestews.setRequestHeader('Content-Type', 'application/json');
                                    httpRequestews.send();
                                }else
                                {
                                    
                                    console.debug(`${ compag}-${compag.attributes}`, console.memory);
                                    let httpRequestewst;
                                    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                                        httpRequestewst = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) { // IE 6 and older
                                        httpRequestewst = new ActiveXObject("Microsoft.XMLHTTP");
                                    }
                                    httpRequestewst.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/hdepartpreprotr/${hdpaxep}/${hcl}`, true);
                                    
                                    httpRequestewst.onload = () => {
                                        const datat2 = JSON.parse(httpRequestewst.responseText);
                                    
                                        if (Object.entries(datat2).length >= 1) {
                                            for (let key in Object.entries(datat2)) {
                                                let opt1 = document.createElement('option');
                                                opt1.value = `${datat2[key].code_progr}/${datat2[key].id_ligneheure}/${datat2[key].typetarif}`;
                                                opt1.innerHTML = `${datat2[key].heure}/${datat2[key].date_progr}`;
                                                document.querySelector('#heuredepartptransit').add(opt1);
                                            }
                                        } else {
                                            document.querySelector('#heuredepartptransit').options.length = 1;
                                        }
                                    };
                                    httpRequestewst.setRequestHeader('Content-Type', 'application/json');
                                    httpRequestewst.send();
                                }
                        }
                        else
                        {
                            document.querySelector('#nomclptransit').innerHTML = ``;
                            document.querySelector('#prenomclptransit').innerHTML = ``;
                            document.querySelector('#contactclptransit').innerHTML = ``;
                            document.querySelector('#refclptransit').innerHTML = ``;
                            document.querySelector('#directionclptransit').innerHTML = ``;
                            document.querySelector('#codeclptransit').innerHTML = ``;
                            document.querySelector('#heureclptransit').innerHTML = ``;
                            document.querySelector('#heuredepartptransit').style.display = 'none';
                            document.querySelector('#numsiegeptransit').style.display = 'none';
                            document.querySelector('#billetreptransit').style.display = 'block';
                            document.querySelector('#billetSmsreptransit').innerHTML = `Billet non valable, la durée de validité est dépassée.`;
                        }
                
                    }

                };
                httpRequestRep.setRequestHeader('Content-Type', 'application/json');
                httpRequestRep.send();
            };
        
            let heurdep = document.querySelector('#heuredepartptransit');
            if (heurdep !== null) {
                heurdep.onchange = () => {
                    document.querySelector('#numsiegeptransit').options.length = 1;
                    
                const httpRequerst = new XMLHttpRequest();
                const selectorts = document.querySelector('#heuredepartptransit').
                    options[document.querySelector('#heuredepartptransit').options.selectedIndex].value;
					
					var post_lh = selectorts.split('/');
					var selh = post_lh[0];
					var lignehsel = post_lh[1];
					
                    var post_lh1 = lignehsel.split('/');
                    var selh1 = post_lh1[0];
                    var lignehsel1 = post_lh1[1];
                    var vr = selh1;
                httpRequerst.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/siegdispo/${selh}`, true);
                httpRequerst.onload = () => {
                        const data = JSON.parse(httpRequerst.responseText);
                        console.debug(`${typeof data} - ${data.attributes}`, console.memory);
                        if (Object.entries(data).length > 0) {
                            for (let key in Object.entries(data)) {
                                document.querySelector('#placevendutransit').value = `${data[key].intervalle1}`;
                                document.querySelector('#dplacevendutransit').value = `${data[key].intervalle2}`;
                                document.querySelector('#repligntransit').value = `${data[key].nom_ligne}`;
                                document.querySelector('#rephertransit').value = `${data[key].heure}`;
                                document.querySelector('#datereprogrammetransit').value = `${data[key].date_progr}`;
                                document.querySelector('#catreprogrammetransit').value = `${data[key].categori}`;
                                document.querySelector('#idrepligntransit').value = `${data[key].ligne_id}`;
                                document.querySelector('#compgcftransit').value = `${data[key].id_compaga}`;
                            }
                        } 
                            
                            
                            const httpRequetterep = new XMLHttpRequest();
                                const pld = document.querySelector('#placevendutransit').value;
                                const plf = document.querySelector('#dplacevendutransit').value;
                                const lgr = document.querySelector('#repligntransit').value;
                                const reph = document.querySelector('#rephertransit').value;
                                const dtrep = document.querySelector('#datereprogrammetransit').value;
                            httpRequetterep.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${selh}/${dtrep}/${lgr}/${reph}/${pld}/${plf}`, true);
                            httpRequetterep.onload = () => {
                            const dattas = JSON.parse(httpRequetterep.responseText);
                            console.debug(`${typeof dattas} - ${dattas.attributes}`, console.memory);
                            if (Object.entries(dattas).length >= 1) {
                               
                                for (let key in Object.entries(dattas)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${dattas[key].siege_num}`;
                                    opt.innerHTML = `${dattas[key].siege_num}`;
                                    document.querySelector('#numsiegeptransit').add(opt);
                                    console.debug(`${dattas[key].siege_num}`, console.memory)
                                }
                            } else {
                                document.querySelector('#numsiegeptransit').options.length = 1;
                                
                            }
                    };
                    httpRequetterep.setRequestHeader('Content-Type', 'application/json');
                    httpRequetterep.send();
                    };
                    httpRequerst.setRequestHeader('Content-Type', 'application/json');
                    httpRequerst.send();
                };
           
            }

            let numsiege = document.querySelector('#numsiegeptransit');
            if (numsiege !== null)
            numsiege.onchange = () => {
                    
                    let Requestsiegevendu;
                    
                    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                        Requestsiegevendu = new XMLHttpRequest();
                    } else if (window.ActiveXObject) { // IE 6 and older
                        Requestsiegevendu = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                    const dp_progrep = document.querySelector('#programreptransit').value;
                    const dp_siegerep = document.querySelector('#numsiegeptransit').options[document.querySelector('#numsiegeptransit').options.selectedIndex].value;
                    Requestsiegevendu.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${dp_progrep}/${dp_siegerep}`, true);
                    Requestsiegevendu.onload = () => 
                    {
                        
                            const donsieg = JSON.parse(Requestsiegevendu.responseText);
                            if (donsieg == '')
                                    {
                                        let httpSiegsrep;
                                        httpSiegsrep = new XMLHttpRequest();

                                        httpSiegsrep.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${dp_progrep}/${dp_siegerep}`, true);
                                        httpSiegsrep.onload = () => 
                                        {
                                            const dongrep= JSON.parse(httpSiegsrep.responseText);
                                            document.querySelector('#erreursiegtransit').style.display = 'none';
                                            if (Object.entries(dongrep).length >= 1)
                                            {
                                                for (let key in Object.entries(dongrep)) {
                                                    document.querySelector('#idtamporeptransit').value = `${dongrep[key].idtamp}`;                    
                                                    document.querySelector('#siegselectreptransit').value = `${dongrep[key].numsieg}`;
                                                }

                                            }
                                        };
                                        httpSiegsrep.setRequestHeader('Content-Type', 'application/json');
                                        httpSiegsrep.send();
                                    }
                                    else {
                                        document.querySelector('#numsiegeptransit').value = '';     
                                        if (Object.entries(donsieg).length >= 1)
                                        {
                                            for (let key in Object.entries(donsieg)) {
                                                document.querySelector('#idtamporeptransit').value = `${donsieg[key].idtamp}`;                    
                                                document.querySelector('#siegselectreptransit').value = `${donsieg[key].numsieg}`;
                                            }

                                        }
                                        document.querySelector('#erreursiegtransit').style.display = 'block';
                                        document.querySelector('#erreurSiegetransit').innerHTML = `Siege déjà utilisé.`; 
                                    }
                    };
                    Requestsiegevendu.setRequestHeader('content-Type', 'text/json');
                    Requestsiegevendu.send();
                };

                butonclicrep = document.querySelector('#resetransit');
            if (butonclicrep !== null) {
                butonclicrep.onclick = () => 
                {
                    let httpSiegeselectrep;
                    httpSiegeselectrep = new XMLHttpRequest();
                    const siegselectrep= document.querySelector('#siegselectreptransit').value;
                    const idtaprep = document.querySelector('#idtamporeptransit').value;
                    httpSiegeselectrep.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtaprep}/${siegselectrep}`, true);
                    httpSiegeselectrep.onload = () => 
                    {
                        const donselectrep= JSON.parse(httpSiegeselectrep.responseText);
                        console.debug(`${typeof donselectrep} - ${donselectrep.attributes}`, console.memory);
                        document.querySelector('#erreursiegtransit').style.display = 'none';
                        
                    };
                    httpSiegeselectrep.setRequestHeader('Content-Type', 'application/json');
                    httpSiegeselectrep.send();

                
                };
            }

            
        e.onclick = function () {
            let rForm = document.querySelector('#rFormtransit');
            rForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/updatetransit/${e.dataset.cle_compagnie}`);
        }
    })
});