/* Bundle historique — genere par scripts/build_module_bundles.php */
/* --- addetat.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addetat').forEach(function (e) 
    {
        document.querySelector('h3#etatTitle').innerHTML = `ETAT TICKETS`;

            let gd = document.querySelector('#garesid');
            if (gd !== null)
                gd.onchange = () => {
                    let httpgares;
                    if (window.XMLHttpRequest) {
                        httpgares = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpgares = new ActiveXObject("Microsoft.XMLHTTP");
                    }

                    document.querySelector('#venteid').options.length = 1;
                    var dt = document.querySelector('#garesid').value;
                    const idgd = document.querySelector('#garesid')
                    .options[document.querySelector('#garesid').options.selectedIndex].value;
                    
                    httpgares.open('GET', window.location.origin + `${APP_ROOT}/programmes/vente/${idgd}`, true);
                    httpgares.onload = () => {
                        const resul = JSON.parse(httpgares.responseText);
                        if(resul == null){

                            
                        
                        } else {
                            if (Object.entries(resul).length >= 1) 
                            {
                                
                                for (let key in Object.entries(resul)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${resul[key].roleattribut}`;
                                        opt.innerHTML = `${resul[key].username}`;
                                        document.querySelector('#venteid').add(opt);
                                    }
                            } else {
                                document.querySelector('#venteid').options.length = 1;
                            }
                            
                        }
                    };
                    httpgares.setRequestHeader('Content-Type', 'application/json');
                    httpgares.send();
                                         
            };
        
        
        e.onclick = function () {
        let Forms = document.querySelector('#Forms');
        Forms.setAttribute('action', `${APP_ROOT}/Rapport/etatpassagers/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- updateticket.js --- */
document.addEventListener('DOMContentLoaded', () => {
    // Bouton unique « Modifier infos client » (ex orange + rouge unifiés).
    document.querySelectorAll('.updateticket').forEach(function (e) {
        e.onclick = function () {
            let mtaForm = document.querySelector('#mtaForm');
            if (!mtaForm) {
                return;
            }

            const ekey = e.dataset.cle_compagnie || '';
            const codePassager = e.dataset.tamponcod || e.dataset.passagecod || '';
            const ligneHeure = e.dataset.cdligneh || '';
            const codeTicket = e.dataset.ticketcod || '';
            const codeTicketNp = e.dataset.ticketcodnp || '';
            const idClient = e.dataset.id_client || '';

            let action = `${APP_ROOT}/Programmes/updatep/${ekey}/${codePassager}/${ligneHeure}/${codeTicket}`;
            if (codeTicketNp) {
                action += `/${codeTicketNp}`;
            }
            mtaForm.setAttribute('action', action);

            const title = document.querySelector('h3#mtaTitle');
            if (title) {
                title.innerHTML = `MODIFICATION INFOS CLIENT : ${e.dataset.nom || ''}`;
            }

            const setVal = (sel, val) => {
                const el = document.querySelector(sel);
                if (el) {
                    el.value = val == null ? '' : String(val);
                }
            };

            setVal('#uclient_contact', e.dataset.contact);
            setVal('#uclient', e.dataset.nom);
            setVal('#uprnclient', e.dataset.prenom);
            setVal('#ucnib', e.dataset.cni);
            setVal('#udate_cnib', e.dataset.cnideliver);
            setVal('#ulieudelivre', e.dataset.cnideliverzone);
            setVal('#identifyclientid', idClient);
            setVal('#identifycontactid', e.dataset.contact || '');
            setVal('#force_create_client', '0');

            const wrap = document.querySelector('#force_create_client_wrap');
            if (wrap) {
                wrap.style.display = 'none';
            }
            const chk = document.querySelector('#force_create_client_chk');
            if (chk) {
                chk.checked = false;
            }
        };
    });

    const inf = document.querySelector('#uclient_contact');
    if (inf !== null) {
        inf.onkeyup = () => {
            let httpInfos;
            if (window.XMLHttpRequest) {
                httpInfos = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfos = new ActiveXObject('Microsoft.XMLHTTP');
            } else {
                return;
            }

            const verificat = document.querySelector('#uclient_contact').value;
            const wrap = document.querySelector('#force_create_client_wrap');
            const forceHidden = document.querySelector('#force_create_client');
            const chk = document.querySelector('#force_create_client_chk');
            const idEl = document.querySelector('#identifyclientid');
            const ctEl = document.querySelector('#identifycontactid');

            httpInfos.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifinfos/${encodeURIComponent(verificat)}`, true);
            httpInfos.onload = () => {
                let infos = null;
                try {
                    infos = JSON.parse(httpInfos.responseText);
                } catch (err) {
                    infos = null;
                }

                if (infos == null || typeof infos !== 'object') {
                    if (idEl) { idEl.value = ''; }
                    if (ctEl) { ctEl.value = ''; }
                    if (wrap) { wrap.style.display = verificat ? 'block' : 'none'; }
                    if (forceHidden) { forceHidden.value = (chk && chk.checked) ? '1' : '0'; }
                    return;
                }

                if (Object.entries(infos).length > 1 && infos.contact_client == verificat) {
                    const setVal = (sel, val) => {
                        const el = document.querySelector(sel);
                        if (el) { el.value = val == null ? '' : String(val); }
                    };
                    setVal('#uclient', infos.nom_client);
                    setVal('#uprnclient', infos.prenom_client);
                    setVal('#ucnib', infos.num_CNIB);
                    setVal('#udate_cnib', infos.date_delivre);
                    setVal('#ulieudelivre', infos.lieu_delivre);
                    if (idEl) { idEl.value = `${infos.id_client}`; }
                    if (ctEl) { ctEl.value = `${infos.contact_client}`; }
                    if (wrap) { wrap.style.display = 'none'; }
                    if (chk) { chk.checked = false; }
                    if (forceHidden) { forceHidden.value = '0'; }
                } else {
                    if (idEl) { idEl.value = ''; }
                    if (ctEl) { ctEl.value = ''; }
                    if (wrap) { wrap.style.display = 'block'; }
                }
            };
            httpInfos.setRequestHeader('Content-Type', 'application/json');
            httpInfos.send();
        };
    }

    const chk = document.querySelector('#force_create_client_chk');
    if (chk) {
        chk.addEventListener('change', function () {
            const forceHidden = document.querySelector('#force_create_client');
            if (forceHidden) {
                forceHidden.value = chk.checked ? '1' : '0';
            }
        });
    }
});
;
/* --- updateclient.js (unifié, no-op) --- */
document.addEventListener('DOMContentLoaded', () => {});
;
/* --- updatedticket.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.updatedticket').forEach(function (e) {
        
        e.onclick = function () {
            let mtForm = document.querySelector('#mdtickForm');
            mtForm.setAttribute('action', `${APP_ROOT}/Historique_Passagers/modifdepart/${e.dataset.cle_compagnie}/${e.dataset.passagecod}/${e.dataset.codticket}`);
            document.querySelector('h3#mtickTitle').innerHTML = `MODIFICATION SUR LE TICKET DE : ${e.dataset.nom}`;
            $('#anciensieg').val(`${e.dataset.siege}`);
            $('#ancien').val(`${e.dataset.ancdepart}`);
            $('#ancienprog').val(`${e.dataset.codepro}`);
            $('#sousgr').val(`${e.dataset.departsousg}`); 

            var idlg = document.querySelector('#ancien').value;
            let httpRequetesq = new XMLHttpRequest();
            httpRequetesq.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfquart/${idlg}`, true);
            httpRequetesq.onload = () => {
            const qdata = JSON.parse(httpRequetesq.responseText);
            if(qdata == ''){
                document.querySelector('#idquartier').options.length = 1;
            }else{
                if (Object.entries(qdata).length >= 1) {
                            
                    for (let key in Object.entries(qdata)) {
                        let opt = document.createElement('option');
                        opt.value = `${qdata[key].nom_quartier}`;
                        opt.innerHTML = `${qdata[key].nom_quartier}`;
                        document.querySelector('#idquartier').add(opt);
                    }
                } else {
                    document.querySelector('#idquartier').options.length = 1;
                }
            }
                
                    
            };
            httpRequetesq.setRequestHeader('Content-Type', 'application/json');
            httpRequetesq.send();
            let httpRequetes = new XMLHttpRequest();
            httpRequetes.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifprogrammes/${idlg}`, true);
            httpRequetes.onload = () => {
                const dataAxe = JSON.parse(httpRequetes.responseText);
                
                
                    if (Object.entries(dataAxe).length >= 1) {
                            
                            for (let key in Object.entries(dataAxe)) {
                                let opt = document.createElement('option');
                                opt.value = `${dataAxe[key].code_progr}`;
                                opt.innerHTML = `${dataAxe[key].heure}/${dataAxe[key].date_progr}`;
                                document.querySelector('#departclient').add(opt);
                            }
                        } else {
                            document.querySelector('#departclient').options.length = 1;
                        }
            };
            httpRequetes.setRequestHeader('Content-Type', 'application/json');
            httpRequetes.send();

            let hrdepart = document.querySelector('#departclient');
            if (hrdepart !== null) {
                hrdepart.onchange = () => {
                    document.querySelector('#siegeclient').options.length = 1;
                    const httpRequest = new XMLHttpRequest();
                    const sel = document.querySelector('#departclient')
                        .options[document.querySelector('#departclient').options.selectedIndex].value;
                    httpRequest.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/siegdispo/${sel}`, true);
                    httpRequest.onload = () => {
                        const don = JSON.parse(httpRequest.responseText);
                        console.debug(`${typeof don} - ${don.attributes}`, console.memory);
                        if (Object.entries(don).length > 0) {
                            for (let key in Object.entries(don)) {
                                document.querySelector('#pfinvendabl').value = `${don[key].intervalle2}`;
                                document.querySelector('#siegfinvendabl').value = `${don[key].intervalle1}`;
                                document.querySelector('#directreserv').value = `${don[key].nom_ligne}`;
                                document.querySelector('#reserveheur').value = `${don[key].heure}`;
                                document.querySelector('#datereserv').value = `${don[key].date_progr}`;
                                document.querySelector('#categbuse').value=`${don[key].categori}`;

                                console.debug(`${don[key].intervalle1} - ${don[key].intervalle2}`, console.memory)
                                
                            }
                        }

                    

                        const httpRequestbis = new XMLHttpRequest();
    
                        const lp = document.querySelector('#pfinvendabl').value;
                        const dbpl = document.querySelector('#siegfinvendabl').value;
                        const direc = document.querySelector('#directreserv').value;
                        const he = document.querySelector('#reserveheur').value;
                        const datres = document.querySelector('#datereserv').value;
    
                        httpRequestbis.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${sel}/${datres}/${direc}/${he}/${dbpl}/${lp}`, true);
                        httpRequestbis.onload = () => {
                            const donbis = JSON.parse(httpRequestbis.responseText);
                            console.debug(`${typeof donbis} - ${donbis.attributes}`, console.memory);
                            if (Object.entries(donbis).length >= 1) {
                                for (let key in Object.entries(donbis)) {
                                    
                                    let opt = document.createElement('option');
                                    opt.value = `${donbis[key].siege_num}/${donbis[key].idcat_bus}`;
                                    opt.innerHTML = `${donbis[key].siege_num}`;
                                    document.querySelector('#siegeclient').add(opt);
                            
                                }
                                
                            } else {
                                document.querySelector('#siegeclient').options.length = 1;
                            }
                            
                        };
                        httpRequestbis.setRequestHeader('Content-Type', 'application/json');
                        httpRequestbis.send();
                          
                    };
                    httpRequest.setRequestHeader('Content-Type', 'application/json');
                    httpRequest.send();
                };
           
            }

            let depsiegreserve = document.querySelector('#siegeclient');
            if (depsiegreserve !== null)
            depsiegreserve.onchange = () => {
                    
                    let Requestsiegereserve;
                    
                    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                        Requestsiegereserve = new XMLHttpRequest();
                    } else if (window.ActiveXObject) { // IE 6 and older
                        Requestsiegereserve = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                    const dp_progreserv = document.querySelector('#departclient').options[document.querySelector('#departclient').options.selectedIndex].value;
                    const dp_siegereserv = document.querySelector('#siegeclient').options[document.querySelector('#siegeclient').options.selectedIndex].value;
                                       
                    Requestsiegereserve.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${dp_progreserv}/${dp_siegereserv}`, true);
                    Requestsiegereserve.onload = () => 
                    {
                        
                            const reservdonsieg = JSON.parse(Requestsiegereserve.responseText);
                            if (reservdonsieg == '')
                                    {
                                        let httpSiegsreserv;
                                        httpSiegsreserv = new XMLHttpRequest();
                                        const dp_progconf = document.querySelector('#departclient').options[document.querySelector('#departclient').options.selectedIndex].value;
                                        const dp_siegeconf = document.querySelector('#siegeclient').options[document.querySelector('#siegeclient').options.selectedIndex].value;
                                        httpSiegsreserv.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${dp_progconf}/${dp_siegeconf}`, true);
                                        httpSiegsreserv.onload = () => 
                                        {
                                            const dongreserv= JSON.parse(httpSiegsreserv.responseText);
                                            document.querySelector('#messieg').style.display = 'none';
                                            if (Object.entries(dongreserv).length >= 1)
                                            {
                                                for (let key in Object.entries(dongreserv)) {
                                                    document.querySelector('#idtamposelect').value = `${dongreserv[key].idtamp}`;                    
                                                    document.querySelector('#siegselect').value = `${dongreserv[key].numsieg}`;
                                                }
                                            }
                                        
                                        };
                                        httpSiegsreserv.setRequestHeader('Content-Type', 'application/json');
                                        httpSiegsreserv.send();
                                    }
                                    else {
                                        document.querySelector('#siegeclient').value = '';     
                                        if (Object.entries(reservdonsieg).length >= 1)
                                        {
                                            for (let key in Object.entries(reservdonsieg)) {
                                                document.querySelector('#idtamposelect').value = `${reservdonsieg[key].idtamp}`;                    
                                                document.querySelector('#siegselect').value = `${reservdonsieg[key].numsieg}`;
                                            }

                                        }
                                        document.querySelector('#messieg').style.display = 'block';
                                        document.querySelector('#erreurmessieg').innerHTML = `Siege déjà utilisé.`; 
                                    }
                    };
                    Requestsiegereserve.setRequestHeader('content-Type', 'text/json');
                    Requestsiegereserve.send();
                };
                
        }

    })
});
