document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addreprogramme').forEach(function (e) {
        document.querySelector('h3#rTitle').innerHTML = `REPROGRAMMATION`;

        let infos = document.querySelector('#reprogrammer_infos');
        if (infos !== null)
            infos.onclick = () => {
                //verification code de reprogrammation
                let httpRequestRep;
                
                if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                    httpRequestRep = new XMLHttpRequest();
                } else if (window.ActiveXObject) { // IE 6 and older
                    httpRequestRep = new ActiveXObject("Microsoft.XMLHTTP");
                }
                
                
                var cocl = document.querySelector("#codeclientp").value;
                httpRequestRep.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/verifcodecl/${cocl}`, true);
                httpRequestRep.onload = () => {
                    const donnees = JSON.parse(httpRequestRep.responseText);
                    if (donnees == null) {
                        
                        document.querySelector('#smsp').style.display = 'block';
                        document.querySelector('#erreurSmsp').innerHTML = `Cet ticket ne peut pas être reprogrammé ici.`;
                        document.querySelector('#nomclp').innerHTML = ``;
                        document.querySelector('#prenomclp').innerHTML = ``;
                        document.querySelector('#contactclp').innerHTML = ``;
                        document.querySelector('#refclp').innerHTML = ``;
                        document.querySelector('#directionclp').innerHTML = ``;
                        document.querySelector('#codeclp').innerHTML = ``;
                        document.querySelector('#heureclp').innerHTML = ``;
                        document.querySelector('#heuredepartp').style.display = 'none';
                        document.querySelector('#numsiegep').style.display = 'none';
                        document.querySelector('#heuredepartp').options.length = 1;

                    } else 
                    {
                               
                            if (Object.entries(donnees).length >= 1){
                                    document.querySelector('#smsp').style.display = 'none';
                                    document.querySelector('#heuredepartp').style.display = 'block';
                                    document.querySelector('#numsiegep').style.display = 'block';       
                                    document.querySelector('#nomclp').innerHTML = `NOM: ${donnees.nom_client}`;
                                    document.querySelector('#prenomclp').innerHTML = `PRENOM: ${donnees.prenom_client}`;
                                    document.querySelector('#contactclp').innerHTML = `CONTACT: ${donnees.contact_client}`;
                                    document.querySelector('#refclp').innerHTML = `REFERENCE CNIB: ${donnees.num_CNIB}`;
                                    document.querySelector('#directionclp').innerHTML = `AXE: ${donnees.nom_ligne}`;
                                    document.querySelector('#codeclp').innerHTML = `CODE TICKET: ${donnees.code_passager}`;
                                    document.querySelector('#heureclp').innerHTML = `HEURE: ${donnees.heure} SIEGE :${donnees.num_siege_categorie}`;
                                    document.querySelector('#passerp').value = `${donnees.code_passager}`;
                                    document.querySelector('#idclpasserid').value = `${donnees.ligne_id}`;
                                    document.querySelector('#client_idp').value = `${donnees.id_client_pass}`;
                                    document.querySelector('#pasnomp').value = `${donnees.nom_client}`;
                                    document.querySelector('#pasprenomp').value = `${donnees.prenom_client}`;
                                    document.querySelector('#pascontactp').value = `${donnees.contact_client}`;
                                    document.querySelector('#pascnibp').value = `${donnees.num_CNIB}`;
                                    document.querySelector('#pasdatep').value = `${donnees.date_delivre}`;
                                    document.querySelector('#nsiegep').value = `${donnees.num_siege_categorie}`;
                                    document.querySelector('#delivrelie').value = `${donnees.lieu_delivre}`;
                                    document.querySelector('#depold').value = `${donnees.code_pro}`;
                                    document.querySelector('#codeid').value = `${donnees.code_passager}`;
                                    document.querySelector('#codetickets').value = `${donnees.tamponcod}`;
                                    document.querySelector('#codenonp').value = `${donnees.code_non_pass}`;
                                    document.querySelector('#statconf').value = `${donnees.statut_confirme}`;
                                    document.querySelector('#statrep').value = `${donnees.statut_reprog}`;
                                    document.querySelector('#programrep').value = `${donnees.code_progr}`;
                                    document.querySelector('#depgid').value = `${donnees.gaexp_lg}`;
                                    document.querySelector('#dateventerep').value = `${donnees.datep_create}`;


                            } else {
                                document.querySelector('#heuredepartp').style.display = 'none';
                                document.querySelector('#numsiegep').style.display = 'none';
                            }
                            var datdepartrep = document.querySelector('#dateventerep').value;
                            var daterepactu = document.querySelector('#actueldaterep').value;
                            var daterep1  = new Date(datdepartrep);
                            var daterep2 = new Date(daterepactu);
                            // différence des heures
                            var time_diff = daterep2.getTime() - daterep1.getTime();
                                // différence de jours
                            const days_Diff = time_diff / (1000 * 3600 * 24);

                            if(days_Diff < 28 || days_Diff < 29 || days_Diff < 30 || days_Diff < 31)    
                            {
                                const hdpaxep = `${donnees.ligne_id}`;
                                const hcl = `${donnees.code_progr}`;
                                const ligneheure =`${donnees.heure_identif}`;
                                let httpRequestews;
                                if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                                    httpRequestews = new XMLHttpRequest();
                                } else if (window.ActiveXObject) { // IE 6 and older
                                    httpRequestews = new ActiveXObject("Microsoft.XMLHTTP");
                                }
                                httpRequestews.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/hdepartprepro/${hdpaxep}/${hcl}/${ligneheure}`, true);
                                
                                httpRequestews.onload = () => {
                                    const data2 = JSON.parse(httpRequestews.responseText);
                                    console.debug(`${typeof data2} - ${data2.attributes}`, console.memory);
                                    if (Object.entries(data2).length >= 1) {
                                        for (let key in Object.entries(data2)) {
                                            let opt = document.createElement('option');
                                            opt.value = `${data2[key].code_progr}/${data2[key].id_ligneheure}/${data2[key].typetarif}`;
                                            opt.innerHTML = `${data2[key].heure}/${data2[key].date_progr}`;
                                            document.querySelector('#heuredepartp').add(opt);
                                            
                                        }
                                    } else {
                                        document.querySelector('#heuredepartp').options.length = 1;
                                    }
                                };
                                httpRequestews.setRequestHeader('Content-Type', 'application/json');
                                httpRequestews.send();
                            }
                            else
                            {
                                document.querySelector('#nomclp').innerHTML = ``;
                                document.querySelector('#prenomclp').innerHTML = ``;
                                document.querySelector('#contactclp').innerHTML = ``;
                                document.querySelector('#refclp').innerHTML = ``;
                                document.querySelector('#directionclp').innerHTML = ``;
                                document.querySelector('#codeclp').innerHTML = ``;
                                document.querySelector('#heureclp').innerHTML = ``;
                                document.querySelector('#heuredepartp').style.display = 'none';
                                document.querySelector('#numsiegep').style.display = 'none';
                                document.querySelector('#billetrep').style.display = 'block';
                                document.querySelector('#billetSmsrep').innerHTML = `Billet non valable, la durée de validité est dépassée.`;
                            }
        
                    }
                };
                httpRequestRep.setRequestHeader('Content-Type', 'application/json');
                httpRequestRep.send();
            };
        
            let heurdep = document.querySelector('#heuredepartp');
            if (heurdep !== null) {
                heurdep.onchange = () => {
                    document.querySelector('#numsiegep').options.length = 1;
                    
                const httpRequerst = new XMLHttpRequest();
                const selectorts = document.querySelector('#heuredepartp').
                    options[document.querySelector('#heuredepartp').options.selectedIndex].value;
					
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
    
                                document.querySelector('#placevendu').value = `${data[key].intervalle1}`;
                                document.querySelector('#dplacevendu').value = `${data[key].intervalle2}`;
                                document.querySelector('#replign').value = `${data[key].nom_ligne}`;
                                document.querySelector('#repher').value = `${data[key].heure}`;
                                document.querySelector('#datereprogramme').value = `${data[key].date_progr}`;
                                document.querySelector('#catreprogramme').value = `${data[key].categori}`;
                                }
                            } 
                            
                            
                            const httpRequetterep = new XMLHttpRequest();
                                const pld = document.querySelector('#placevendu').value;
                                const plf = document.querySelector('#dplacevendu').value;
                                const lgr = document.querySelector('#replign').value;
                                const reph = document.querySelector('#repher').value;
                                const dtrep = document.querySelector('#datereprogramme').value;
                            httpRequetterep.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${selh}/${dtrep}/${lgr}/${reph}/${pld}/${plf}`, true);
                            httpRequetterep.onload = () => {
                            const dattas = JSON.parse(httpRequetterep.responseText);
                            console.debug(`${typeof dattas} - ${dattas.attributes}`, console.memory);
                            if (Object.entries(dattas).length >= 1) {
                               
                                for (let key in Object.entries(dattas)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${dattas[key].siege_num}`;
                                    opt.innerHTML = `${dattas[key].siege_num}`;
                                    document.querySelector('#numsiegep').add(opt);
                                    console.debug(`${dattas[key].siege_num}`, console.memory)
                                }
                            } else {
                                document.querySelector('#numsiegep').options.length = 1;
                                
                            }
                    };
                    httpRequetterep.setRequestHeader('Content-Type', 'application/json');
                    httpRequetterep.send();
                    };
                    httpRequerst.setRequestHeader('Content-Type', 'application/json');
                    httpRequerst.send();
                };
           
            }

            let numsiege = document.querySelector('#numsiegep');
            if (numsiege !== null)
            numsiege.onchange = () => {
                    
                    let Requestsiegevendu;
                    
                    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                        Requestsiegevendu = new XMLHttpRequest();
                    } else if (window.ActiveXObject) { // IE 6 and older
                        Requestsiegevendu = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                    const dp_progrep = document.querySelector('#programrep').value;
                    const dp_siegerep = document.querySelector('#numsiegep').options[document.querySelector('#numsiegep').options.selectedIndex].value;
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
                                            document.querySelector('#erreursieg').style.display = 'none';
                                            if (Object.entries(dongrep).length >= 1)
                                            {
                                                for (let key in Object.entries(dongrep)) {
                                                    document.querySelector('#idtamporep').value = `${dongrep[key].idtamp}`;                    
                                                    document.querySelector('#siegselectrep').value = `${dongrep[key].numsieg}`;
                                                }

                                            }
                                        };
                                        httpSiegsrep.setRequestHeader('Content-Type', 'application/json');
                                        httpSiegsrep.send();
                                    }
                                    else {
                                        document.querySelector('#numsiegep').value = '';     
                                        if (Object.entries(donsieg).length >= 1)
                                        {
                                            for (let key in Object.entries(donsieg)) {
                                                document.querySelector('#idtamporep').value = `${donsieg[key].idtamp}`;                    
                                                document.querySelector('#siegselectrep').value = `${donsieg[key].numsieg}`;
                                            }

                                        }
                                        document.querySelector('#erreursieg').style.display = 'block';
                                        document.querySelector('#erreurSiege').innerHTML = `Siege déjà utilisé.`; 
                                    }
                    };
                    Requestsiegevendu.setRequestHeader('content-Type', 'text/json');
                    Requestsiegevendu.send();
                };

                butonclicrep = document.querySelector('#rese');
            if (butonclicrep !== null) {
                butonclicrep.onclick = () => 
                {
                    let httpSiegeselectrep;
                    httpSiegeselectrep = new XMLHttpRequest();
                    const siegselectrep= document.querySelector('#siegselectrep').value;
                    const idtaprep = document.querySelector('#idtamporep').value;
                    httpSiegeselectrep.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtaprep}/${siegselectrep}`, true);
                    httpSiegeselectrep.onload = () => 
                    {
                        const donselectrep= JSON.parse(httpSiegeselectrep.responseText);
                        console.debug(`${typeof donselectrep} - ${donselectrep.attributes}`, console.memory);
                        document.querySelector('#erreursieg').style.display = 'none';
                        
                    };
                    httpSiegeselectrep.setRequestHeader('Content-Type', 'application/json');
                    httpSiegeselectrep.send();

                
                };
            }

            
        e.onclick = function () {
            let rForm = document.querySelector('#rForm');
            rForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/update/${e.dataset.cle_compagnie}`);
        }
    })
});