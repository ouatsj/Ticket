document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addreprogadmin').forEach(function (e) {
        document.querySelector('h3#adminrTitle').innerHTML = `REPROGRAMMATION`;

        let admininfos = document.querySelector('#adminreprogrammer_infos');
        if (admininfos !== null)
            admininfos.onclick = () => {
                //verification code de reprogrammation
                let httpRequestRep;
                
                if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                    httpRequestRep = new XMLHttpRequest();
                } else if (window.ActiveXObject) { // IE 6 and older
                    httpRequestRep = new ActiveXObject("Microsoft.XMLHTTP");
                }
               
                
                    var admincocl = document.querySelector("#admincodeclientp").value;
                httpRequestRep.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/adminverifcodecl/${admincocl}`, true);
                httpRequestRep.onload = () => {
                    const donnees = JSON.parse(httpRequestRep.responseText);
                    if (donnees == null) {
                        
                        document.querySelector('#adminsmsp').style.display = 'block';
                        document.querySelector('#adminerreurSmsp').innerHTML = `Cet ticket ne peut pas être reprogrammé ici.`;
                        document.querySelector('#adminnomclp').innerHTML = ``;
                        document.querySelector('#adminprenomclp').innerHTML = ``;
                        document.querySelector('#admincontactclp').innerHTML = ``;
                        document.querySelector('#adminrefclp').innerHTML = ``;
                        document.querySelector('#admindirectionclp').innerHTML = ``;
                        document.querySelector('#admincodeclp').innerHTML = ``;
                        document.querySelector('#adminheureclp').innerHTML = ``;
                        document.querySelector('#adminheuredepartp').style.display = 'none';
                        document.querySelector('#adminnumsiegep').style.display = 'none';
                        document.querySelector('#adminheuredepartp').options.length = 1;
                        document.querySelector('#admincdpassager').value = ``;

                        
                    } else
                    {

                    
                        if (Object.entries(donnees).length >= 1){

                        
                            document.querySelector('#adminsmsp').style.display = 'none';
                            document.querySelector('#adminheuredepartp').style.display = 'block';
                            document.querySelector('#adminnumsiegep').style.display = 'block';       
                            document.querySelector('#adminnomclp').innerHTML = `NOM: ${donnees.nom_client}`;
                            document.querySelector('#adminprenomclp').innerHTML = `PRENOM: ${donnees.prenom_client}`;
                            document.querySelector('#admincontactclp').innerHTML = `CONTACT: ${donnees.contact_client}`;
                            document.querySelector('#adminrefclp').innerHTML = `REFERENCE CNIB: ${donnees.num_CNIB}`;
                            document.querySelector('#admindirectionclp').innerHTML = `AXE: ${donnees.nom_ligne}`;
                            document.querySelector('#admincodeclp').innerHTML = `CODE TICKET: ${donnees.code_passager}`;
                            document.querySelector('#adminheureclp').innerHTML = `HEURE: ${donnees.heure} SIEGE: ${donnees.num_siege_categorie}`;
                            document.querySelector('#adminpasserp').value = `${donnees.code_passager}`;
                            document.querySelector('#adminidclpasserid').value = `${donnees.ligne_id}`;
                            document.querySelector('#adminclient_idp').value = `${donnees.id_client_pass}`;
                            document.querySelector('#adminpasnomp').value = `${donnees.nom_client}`;
                            document.querySelector('#adminpasprenomp').value = `${donnees.prenom_client}`;
                            document.querySelector('#adminpascontactp').value = `${donnees.contact_client}`;
                            document.querySelector('#adminpascnibp').value = `${donnees.num_CNIB}`;
                            document.querySelector('#adminpasdatep').value = `${donnees.date_delivre}`;
                            document.querySelector('#adminnsiegep').value = `${donnees.num_siege_categorie}`;
                            document.querySelector('#admindelivrelie').value = `${donnees.lieu_delivre}`;
                            document.querySelector('#admindepold').value = `${donnees.code_pro}`;
                            document.querySelector('#admincodeid').value = `${donnees.code_passager}`;
                            document.querySelector('#admincodetickets').value = `${donnees.tamponcod}`;
                            document.querySelector('#admincodenonp').value = `${donnees.code_non_pass}`;
                            document.querySelector('#adminstatconf').value = `${donnees.statut_confirme}`;
                            document.querySelector('#adminstatrep').value = `${donnees.statut_reprog}`;
                            document.querySelector('#adminprogramrep').value = `${donnees.code_progr}`;
                            document.querySelector('#admindepgid').value = `${donnees.gaexp_lg}`;
                            document.querySelector('#dateventerep').value = `${donnees.datep_create}`;
                            document.querySelector('#admincdpassager').value = `${donnees.code_ticket}`;

                        } else {
                            document.querySelector('#adminheuredepartp').style.display = 'none';
                            document.querySelector('#adminnumsiegep').style.display = 'none';
                        }       
                            var addatdepartrep = document.querySelector('#dateventerep').value;
                            var addaterepactu = document.querySelector('#actueldaterep').value;
                            var addaterep1  = new Date(addatdepartrep);
                            var addaterep2 = new Date(addaterepactu);
                            // différence des heures
                            var time_diff = addaterep2.getTime() - addaterep1.getTime();
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
                                            document.querySelector('#adminheuredepartp').add(opt);
                                            
                                        }
                                    } else {
                                        document.querySelector('#adminheuredepartp').options.length = 1;
                                    }
                                };
                                httpRequestews.setRequestHeader('Content-Type', 'application/json');
                                httpRequestews.send();
                            }
                            else{

                                document.querySelector('#adminnomclp').innerHTML = ``;
                                document.querySelector('#adminprenomclp').innerHTML = ``;
                                document.querySelector('#admincontactclp').innerHTML = ``;
                                document.querySelector('#adminrefclp').innerHTML = ``;
                                document.querySelector('#admindirectionclp').innerHTML = ``;
                                document.querySelector('#admincodeclp').innerHTML = ``;
                                document.querySelector('#adminheureclp').innerHTML = ``;
                                document.querySelector('#adminheuredepartp').style.display = 'none';
                                document.querySelector('#adminnumsiegep').style.display = 'none';
                                document.querySelector('#adbilletrep').style.display = 'block';
                                document.querySelector('#adbilletSmsrep').innerHTML = `Billet non valable, la durée de validité est dépassée.`;
                            }
                    }
                };
                httpRequestRep.setRequestHeader('Content-Type', 'application/json');
                httpRequestRep.send();
            };
                
        
            let heurdep = document.querySelector('#adminheuredepartp');
            if (heurdep !== null) {
                heurdep.onchange = () => {
                    document.querySelector('#adminnumsiegep').options.length = 1;
                    
                const httpRequerst = new XMLHttpRequest();
                const selectorts = document.querySelector('#adminheuredepartp').
                    options[document.querySelector('#adminheuredepartp').options.selectedIndex].value;
					
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
                        if (Object.entries(data).length > 0)
                            for (let key in Object.entries(data)) {
    
                                document.querySelector('#adminplacevendu').value = `${data[key].intervalle1}`;
                                document.querySelector('#admindplacevendu').value = `${data[key].intervalle2}`;
                                document.querySelector('#adminreplign').value = `${data[key].nom_ligne}`;
                                document.querySelector('#adminrepher').value = `${data[key].heure}`;
                                document.querySelector('#admindatereprogramme').value = `${data[key].date_progr}`;
                                document.querySelector('#admincatreprogramme').value = `${data[key].categori}`;
                                }
                            
                            const httpRequetterep = new XMLHttpRequest();
                                const pld = document.querySelector('#adminplacevendu').value;
                                const plf = document.querySelector('#admindplacevendu').value;
                                const lgr = document.querySelector('#adminreplign').value;
                                const reph = document.querySelector('#adminrepher').value;
                                const dtrep = document.querySelector('#admindatereprogramme').value;

                            httpRequetterep.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${selh}/${dtrep}/${lgr}/${reph}/${pld}/${plf}`, true);
                            httpRequetterep.onload = () => {
                            const dattas = JSON.parse(httpRequetterep.responseText);
                            console.debug(`${typeof dattas} - ${dattas.attributes}`, console.memory);
                            if (Object.entries(dattas).length >= 1)
                            {
                               
                                for (let key in Object.entries(dattas)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${dattas[key].siege_num}`;
                                    opt.innerHTML = `${dattas[key].siege_num}`;
                                    document.querySelector('#adminnumsiegep').add(opt);
                                    console.debug(`${dattas[key].siege_num}`, console.memory)
                                }
                            } else {
                                document.querySelector('#adminnumsiegep').options.length = 1;
                                
                            }
                    };
                    httpRequetterep.setRequestHeader('Content-Type', 'application/json');
                    httpRequetterep.send();
                    };
                    httpRequerst.setRequestHeader('Content-Type', 'application/json');
                    httpRequerst.send();
                };
           
            }

            let numsiege = document.querySelector('#adminnumsiegep');
            if (numsiege !== null)
            numsiege.onchange = () => {
                    
                    let Requestsiegevendu;
                    
                    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                        Requestsiegevendu = new XMLHttpRequest();
                    } else if (window.ActiveXObject) { // IE 6 and older
                        Requestsiegevendu = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                    const dp_progrep = document.querySelector('#adminprogramrep').value;
                    const dp_siegerep = document.querySelector('#adminnumsiegep').options[document.querySelector('#numsiegep').options.selectedIndex].value;
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
                                            document.querySelector('#adminerreursieg').style.display = 'none';
                                            if (Object.entries(dongrep).length >= 1)
                                            {
                                                for (let key in Object.entries(dongrep)) {
                                                    document.querySelector('#adminidtamporep').value = `${dongrep[key].idtamp}`;                    
                                                    document.querySelector('#adminsiegselectrep').value = `${dongrep[key].numsieg}`;
                                                }

                                            }
                                        };
                                        httpSiegsrep.setRequestHeader('Content-Type', 'application/json');
                                        httpSiegsrep.send();
                                    }
                                    else {
                                        document.querySelector('#adminnumsiegep').value = '';     
                                        if (Object.entries(donsieg).length >= 1)
                                        {
                                            for (let key in Object.entries(donsieg)) {
                                                document.querySelector('#adminidtamporep').value = `${donsieg[key].idtamp}`;                    
                                                document.querySelector('#adminsiegselectrep').value = `${donsieg[key].numsieg}`;
                                            }

                                        }
                                        document.querySelector('#adminerreursieg').style.display = 'block';
                                        document.querySelector('#adminerreurSiege').innerHTML = `Siege déjà utilisé.`; 
                                    }
                    };
                    Requestsiegevendu.setRequestHeader('content-Type', 'text/json');
                    Requestsiegevendu.send();
                };

                butonclicrep = document.querySelector('#adminrese');
            if (butonclicrep !== null) {
                butonclicrep.onclick = () => 
                {
                    let httpSiegeselectrep;
                    httpSiegeselectrep = new XMLHttpRequest();
                    const siegselectrep= document.querySelector('#adminsiegselectrep').value;
                    const idtaprep = document.querySelector('#adminidtamporep').value;
                    httpSiegeselectrep.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtaprep}/${siegselectrep}`, true);
                    httpSiegeselectrep.onload = () => 
                    {
                        const donselectrep= JSON.parse(httpSiegeselectrep.responseText);
                        console.debug(`${typeof donselectrep} - ${donselectrep.attributes}`, console.memory);
                        document.querySelector('#adminerreursieg').style.display = 'none';
                        
                    };
                    httpSiegeselectrep.setRequestHeader('Content-Type', 'application/json');
                    httpSiegeselectrep.send();

                
                };
            }

            
        e.onclick = function () {
            let rForm = document.querySelector('#adminrForm');
            rForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/adupdate/${e.dataset.cle_compagnie}`);
        }
    })
});