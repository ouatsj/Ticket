document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.gareaddreprogramme').forEach(function (e) {
        document.querySelector('h3#garerTitle').innerHTML = `REPROGRAMMATION`;

        let gareinfos = document.querySelector('#garereprogrammer_infos');
        if (gareinfos !== null)
            gareinfos.onclick = () => {
                //verification code de reprogrammation
                let httpRequestRepgare;
                
                if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                    httpRequestRepgare = new XMLHttpRequest();
                } else if (window.ActiveXObject) { // IE 6 and older
                    httpRequestRepgare = new ActiveXObject("Microsoft.XMLHTTP");
                }
                
                
                var garecocl = document.querySelector("#garecodeclientp").value;
                httpRequestRepgare.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/verifcodeclgare/${garecocl}`, true);
                httpRequestRepgare.onload = () => {
                    const garedonnees = JSON.parse(httpRequestRepgare.responseText);
                    if (garedonnees == null) {
                        
                        document.querySelector('#garesmsp').style.display = 'block';
                        document.querySelector('#gareerreurSmsp').innerHTML = `Cet ticket ne peut pas être reprogrammé ici.`;
                        document.querySelector('#garenomclp').innerHTML = ``;
                        document.querySelector('#gareprenomclp').innerHTML = ``;
                        document.querySelector('#garecontactclp').innerHTML = ``;
                        document.querySelector('#garerefclp').innerHTML = ``;
                        document.querySelector('#garedirectionclp').innerHTML = ``;
                        document.querySelector('#garecodeclp').innerHTML = ``;
                        document.querySelector('#gareheureclp').innerHTML = ``;
                    } else 
                    {
                        if (Object.entries(garedonnees).length > 1) {
                            
                            if (garedonnees.code_gaexp == garedonnees.gaexp_lg) {
                                document.querySelector('#garesmsp').style.display = 'none';
                                document.querySelector('#gareheuredepartp').style.display = 'block';
                                document.querySelector('#garenumsiegep').style.display = 'block';       
                                document.querySelector('#garenomclp').innerHTML = `NOM: ${garedonnees.nom_client}`;
                                document.querySelector('#gareprenomclp').innerHTML = `PRENOM: ${garedonnees.prenom_client}`;
                                document.querySelector('#garecontactclp').innerHTML = `CONTACT: ${garedonnees.contact_client}`;
                                document.querySelector('#garerefclp').innerHTML = `REFERENCE CNIB: ${garedonnees.num_CNIB}`;
                                document.querySelector('#garedirectionclp').innerHTML = `AXE: ${garedonnees.nom_ligne}`;
                                document.querySelector('#garecodeclp').innerHTML = `CODE TICKET: ${garedonnees.code_passager}`;
                                document.querySelector('#gareheureclp').innerHTML = `HEURE: ${garedonnees.heure} SIEGE: ${garedonnees.num_siege_categorie}`;
                                document.querySelector('#garepasserp').value = `${garedonnees.code_passager}`;
                                document.querySelector('#gareidclpasserid').value = `${garedonnees.ligne_id}`;
                                document.querySelector('#gareclient_idp').value = `${garedonnees.id_client_pass}`;
                                document.querySelector('#garepasnomp').value = `${garedonnees.nom_client}`;
                                document.querySelector('#garepasprenomp').value = `${garedonnees.prenom_client}`;
                                document.querySelector('#garepascontactp').value = `${garedonnees.contact_client}`;
                                document.querySelector('#garepascnibp').value = `${garedonnees.num_CNIB}`;
                                document.querySelector('#garepasdatep').value = `${garedonnees.date_delivre}`;
                                document.querySelector('#garensiegep').value = `${garedonnees.num_siege_categorie}`;
                                document.querySelector('#garedelivrelie').value = `${garedonnees.lieu_delivre}`;
                                document.querySelector('#garedepold').value = `${garedonnees.code_pro}`;
                                document.querySelector('#garecodeid').value = `${garedonnees.code_passager}`;
                                document.querySelector('#garecodetickets').value = `${garedonnees.tamponcod}`;
                                document.querySelector('#garecodenonp').value = `${garedonnees.code_non_pass}`;
                                document.querySelector('#gareprogramrep').value = `${garedonnees.code_progr}`;
                                document.querySelector('#garedepgid').value = `${garedonnees.gaexp_lg}`;

                            } else {
                                document.querySelector('#gareheuredepartp').style.display = 'none';
                                document.querySelector('#garenumsiegep').style.display = 'none';
                            }
                            const garehdpaxep = `${garedonnees.ligne_id}`;
                            const garehcl = `${garedonnees.code_progr}`;
                            const gareligneheure =`${garedonnees.heure_identif}`;
                            let httpRequestewsgare;
                            if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                                httpRequestewsgare = new XMLHttpRequest();
                            } else if (window.ActiveXObject) { // IE 6 and older
                                httpRequestewsgare = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            httpRequestewsgare.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/hdepartprepro/${garehdpaxep}/${garehcl}/${gareligneheure}`, true);
                            
                            httpRequestewsgare.onload = () => {
                                const garedata2 = JSON.parse(httpRequestewsgare.responseText);
                                console.debug(`${typeof garedata2} - ${garedata2.attributes}`, console.memory);
                                if (Object.entries(garedata2).length >= 1) {
                                    for (let key in Object.entries(garedata2)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${garedata2[key].code_progr}/${garedata2[key].id_ligneheure}`;
                                        opt.innerHTML = `${garedata2[key].heure}/${garedata2[key].date_progr}`;
                                        document.querySelector('#gareheuredepartp').add(opt);
                                        
                                    }
                                } else {
                                    document.querySelector('#gareheuredepartp').options.length = 1;
                                }
                            };
                            httpRequestewsgare.setRequestHeader('Content-Type', 'application/json');
                            httpRequestewsgare.send();
                        }
                    }
                };
                httpRequestRepgare.setRequestHeader('Content-Type', 'application/json');
                httpRequestRepgare.send();
            };
        
            let heurdepgare = document.querySelector('#gareheuredepartp');
            if (heurdepgare !== null) {
                heurdepgare.onchange = () => {
                    document.querySelector('#garenumsiegep').options.length = 1;
                    
                const httpRequerstgare = new XMLHttpRequest();
                const selectortsgare = document.querySelector('#gareheuredepartp').
                    options[document.querySelector('#gareheuredepartp').options.selectedIndex].value;
					
					var post_lhgare = selectortsgare.split('/');
					var selhgare = post_lhgare[0];
					var lignehselgare = post_lhgare[1];
					var vrgare = lignehselgare;
                httpRequerstgare.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/siegdispo/${selhgare}`, true);
                httpRequerstgare.onload = () => {
                        const datagare = JSON.parse(httpRequerstgare.responseText);
                        console.debug(`${typeof datagare} - ${datagare.attributes}`, console.memory);
                        if (Object.entries(datagare).length > 0) {
                            for (let key in Object.entries(datagare)) {
    
                                document.querySelector('#gareplacevendu').value = `${datagare[key].intervalle1}`;
                                document.querySelector('#garedplacevendu').value = `${datagare[key].intervalle2}`;
                                document.querySelector('#garereplign').value = `${datagare[key].nom_ligne}`;
                                document.querySelector('#garerepher').value = `${datagare[key].heure}`;
                                document.querySelector('#garedatereprogramme').value = `${datagare[key].date_progr}`;
                                document.querySelector('#garecatreprogramme').value = `${datagare[key].categori}`;
                                }
                            } 
                            
                            
                            const httpRequetterepgare = new XMLHttpRequest();
                                const pldgare = document.querySelector('#gareplacevendu').value;
                                const plfgare = document.querySelector('#garedplacevendu').value;
                                const lgrgare = document.querySelector('#garereplign').value;
                                const rephgare = document.querySelector('#garerepher').value;
                                const dtrepgare = document.querySelector('#garedatereprogramme').value;
                            httpRequetterepgare.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${selhgare}/${dtrepgare}/${lgrgare}/${rephgare}/${pldgare}/${plfgare}`, true);
                            httpRequetterepgare.onload = () => {
                            const dattasgare = JSON.parse(httpRequetterepgare.responseText);
                            console.debug(`${typeof dattasgare} - ${dattasgare.attributes}`, console.memory);
                            if (Object.entries(dattasgare).length >= 1) {
                               
                                for (let key in Object.entries(dattasgare)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${dattasgare[key].siege_num}`;
                                    opt.innerHTML = `${dattasgare[key].siege_num}`;
                                    document.querySelector('#garenumsiegep').add(opt);
                                    console.debug(`${dattasgare[key].siege_num}`, console.memory)
                                }
                            } else {
                                document.querySelector('#garenumsiegep').options.length = 1;
                                
                            }
                    };
                    httpRequetterepgare.setRequestHeader('Content-Type', 'application/json');
                    httpRequetterepgare.send();
                    };
                    httpRequerstgare.setRequestHeader('Content-Type', 'application/json');
                    httpRequerstgare.send();
                };
           
            }

            let numsiegegare = document.querySelector('#garenumsiegep');
            if (numsiegegare !== null)
            numsiegegare.onchange = () => {
                    
                    let Requestsiegevendugare;
                    
                    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                        Requestsiegevendugare = new XMLHttpRequest();
                    } else if (window.ActiveXObject) { // IE 6 and older
                        Requestsiegevendugare = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                    const dp_progrepgare = document.querySelector('#gareprogramrep').value;
                    const dp_siegerepgare = document.querySelector('#garenumsiegep').options[document.querySelector('#garenumsiegep').options.selectedIndex].value;
                    Requestsiegevendugare.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${dp_progrepgare}/${dp_siegerepgare}`, true);
                    Requestsiegevendugare.onload = () => 
                    {
                        
                            const donsieggare = JSON.parse(Requestsiegevendugare.responseText);
                            if (donsieggare == '')
                                    {
                                        let httpSiegsrepgare;
                                        httpSiegsrepgare = new XMLHttpRequest();

                                        httpSiegsrepgare.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${dp_progrepgare}/${dp_siegerepgare}`, true);
                                        httpSiegsrepgare.onload = () => 
                                        {
                                            const dongrepgare= JSON.parse(httpSiegsrepgare.responseText);
                                            document.querySelector('#gareerreursieg').style.display = 'none';
                                            if (Object.entries(dongrepgare).length >= 1)
                                            {
                                                for (let key in Object.entries(dongrepgare)) {
                                                    document.querySelector('#gareidtamporep').value = `${dongrepgare[key].idtamp}`;                    
                                                    document.querySelector('#garesiegselectrep').value = `${dongrepgare[key].numsieg}`;
                                                }

                                            }
                                        };
                                        httpSiegsrepgare.setRequestHeader('Content-Type', 'application/json');
                                        httpSiegsrepgare.send();
                                    }
                                    else {
                                        document.querySelector('#garenumsiegep').value = '';     
                                        if (Object.entries(donsieggare).length >= 1)
                                        {
                                            for (let key in Object.entries(donsieggare)) {
                                                document.querySelector('#gareidtamporep').value = `${donsieggare[key].idtamp}`;                    
                                                document.querySelector('#garesiegselectrep').value = `${donsieggare[key].numsieg}`;
                                            }

                                        }
                                        document.querySelector('#gareerreursieg').style.display = 'block';
                                        document.querySelector('#gareerreurSiege').innerHTML = `Siege déjà utilisé.`; 
                                    }
                    };
                    Requestsiegevendugare.setRequestHeader('content-Type', 'text/json');
                    Requestsiegevendugare.send();
                };

                butonclicrepgare= document.querySelector('#garerese');
            if (butonclicrepgare !== null) {
                butonclicrepgare.onclick = () => 
                {
                    let httpSiegeselectrepgare;
                    httpSiegeselectrepgare = new XMLHttpRequest();
                    const siegselectrepgare = document.querySelector('#garesiegselectrep').value;
                    const idtaprepgaregare = document.querySelector('#gareidtamporep').value;
                    httpSiegeselectrepgare.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtaprepgaregare}/${siegselectrepgare}`, true);
                    httpSiegeselectrepgare.onload = () => 
                    {
                        const donselectrepgare = JSON.parse(httpSiegeselectrepgare.responseText);
                        console.debug(`${typeof donselectrepgare} - ${donselectrepgare.attributes}`, console.memory);
                        document.querySelector('#gareerreursieg').style.display = 'none';
                        
                    };
                    httpSiegeselectrepgare.setRequestHeader('Content-Type', 'application/json');
                    httpSiegeselectrepgare.send();

                
                };
            }

            
        e.onclick = function () {
            let garerForm = document.querySelector('#garerForm');
            garerForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/update/${e.dataset.cle_compagnie}`);
        }
    })
});