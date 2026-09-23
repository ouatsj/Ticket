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
            // Toujours préremplir l'id client lié au ticket (évite création silencieuse).
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
                    if (idEl) {
                        idEl.value = '';
                    }
                    if (ctEl) {
                        ctEl.value = '';
                    }
                    if (wrap) {
                        wrap.style.display = verificat ? 'block' : 'none';
                    }
                    if (forceHidden) {
                        forceHidden.value = (chk && chk.checked) ? '1' : '0';
                    }
                    return;
                }

                if (Object.entries(infos).length > 1 && infos.contact_client == verificat) {
                    const setVal = (sel, val) => {
                        const el = document.querySelector(sel);
                        if (el) {
                            el.value = val == null ? '' : String(val);
                        }
                    };
                    setVal('#uclient', infos.nom_client);
                    setVal('#uprnclient', infos.prenom_client);
                    setVal('#ucnib', infos.num_CNIB);
                    setVal('#udate_cnib', infos.date_delivre);
                    setVal('#ulieudelivre', infos.lieu_delivre);
                    if (idEl) {
                        idEl.value = `${infos.id_client}`;
                    }
                    if (ctEl) {
                        ctEl.value = `${infos.contact_client}`;
                    }
                    if (wrap) {
                        wrap.style.display = 'none';
                    }
                    if (chk) {
                        chk.checked = false;
                    }
                    if (forceHidden) {
                        forceHidden.value = '0';
                    }
                } else {
                    if (idEl) {
                        idEl.value = '';
                    }
                    if (ctEl) {
                        ctEl.value = '';
                    }
                    if (wrap) {
                        wrap.style.display = 'block';
                    }
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
/* --- updateclient.js --- */
/**
 * Ancien bouton rouge « MODIFIER CLIENT » — unifié dans updateticket.js.
 * Conservé vide pour ne pas casser le chargement scripts.php / bundles.
 */
document.addEventListener('DOMContentLoaded', () => {
    // no-op : utiliser .updateticket (Modifier infos client)
});

;
/* --- updatedticket.js --- */
document.addEventListener('DOMContentLoaded', () => {
    const appRoot = () => (typeof APP_ROOT !== 'undefined' ? APP_ROOT : '');
    let programmesCache = [];

    function resetSelect(sel, placeholder) {
        if (!sel) {
            return;
        }
        sel.options.length = 0;
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = placeholder || '';
        sel.add(opt);
    }

    function setSelectValue(sel, value) {
        if (!sel || value == null || value === '') {
            return false;
        }
        const v = String(value);
        for (let i = 0; i < sel.options.length; i++) {
            if (String(sel.options[i].value) === v) {
                sel.selectedIndex = i;
                return true;
            }
        }
        return false;
    }

    function loadSiegesForProgramme(codePro) {
        const siegeEl = document.querySelector('#siegeclient');
        const messieg = document.querySelector('#messieg');
        if (messieg) {
            messieg.style.display = 'none';
        }
        resetSelect(siegeEl, 'Choisir le siège');
        if (!codePro) {
            return;
        }

        const httpRequest = new XMLHttpRequest();
        httpRequest.open(
            'GET',
            window.location.origin + `${appRoot()}/reprogrammes/siegdispo/${encodeURIComponent(codePro)}`,
            true
        );
        httpRequest.onload = () => {
            let don = null;
            try {
                don = JSON.parse(httpRequest.responseText);
            } catch (err) {
                don = null;
            }
            if (don && Object.entries(don).length > 0) {
                for (let key in Object.entries(don)) {
                    const row = don[key];
                    if (!row) {
                        continue;
                    }
                    const map = {
                        '#pfinvendabl': row.intervalle2,
                        '#siegfinvendabl': row.intervalle1,
                        '#directreserv': row.nom_ligne,
                        '#reserveheur': row.heure,
                        '#datereserv': row.date_progr,
                        '#categbuse': row.categori,
                    };
                    Object.keys(map).forEach((sel) => {
                        const el = document.querySelector(sel);
                        if (el) {
                            el.value = map[sel] != null ? map[sel] : '';
                        }
                    });
                }
            }

            const lp = (document.querySelector('#pfinvendabl') || {}).value || '';
            const dbpl = (document.querySelector('#siegfinvendabl') || {}).value || '';
            const direc = (document.querySelector('#directreserv') || {}).value || '';
            const he = (document.querySelector('#reserveheur') || {}).value || '';
            const datres = (document.querySelector('#datereserv') || {}).value || '';

            const httpRequestbis = new XMLHttpRequest();
            httpRequestbis.open(
                'GET',
                window.location.origin
                    + `${appRoot()}/programmes/siegdisponible/${encodeURIComponent(codePro)}/`
                    + `${encodeURIComponent(datres)}/${encodeURIComponent(direc)}/`
                    + `${encodeURIComponent(he)}/${encodeURIComponent(dbpl)}/${encodeURIComponent(lp)}`,
                true
            );
            httpRequestbis.onload = () => {
                let donbis = null;
                try {
                    donbis = JSON.parse(httpRequestbis.responseText);
                } catch (err) {
                    donbis = null;
                }
                resetSelect(siegeEl, 'Choisir le siège');
                if (donbis && Object.entries(donbis).length >= 1) {
                    for (let key in Object.entries(donbis)) {
                        const row = donbis[key];
                        if (!row || row.siege_num == null) {
                            continue;
                        }
                        const opt = document.createElement('option');
                        opt.value = `${row.siege_num}/${row.idcat_bus}`;
                        opt.textContent = String(row.siege_num);
                        siegeEl.add(opt);
                    }
                }
                const wantSiege = document.querySelector('#anciensieg');
                if (wantSiege && wantSiege.value) {
                    for (let i = 0; i < siegeEl.options.length; i++) {
                        if (String(siegeEl.options[i].value).split('/')[0] === String(wantSiege.value)) {
                            siegeEl.selectedIndex = i;
                            break;
                        }
                    }
                }
            };
            httpRequestbis.setRequestHeader('Content-Type', 'application/json');
            httpRequestbis.send();
        };
        httpRequest.setRequestHeader('Content-Type', 'application/json');
        httpRequest.send();
    }

    function fillHeures(dateVal, preferCodePro, preferHeure) {
        const heureEl = document.querySelector('#departclient');
        resetSelect(heureEl, "Choisir l'heure");
        resetSelect(document.querySelector('#siegeclient'), 'Choisir le siège');
        if (!dateVal) {
            return;
        }
        programmesCache
            .filter((p) => p && String(p.date_progr) === String(dateVal))
            .forEach((p) => {
                const opt = document.createElement('option');
                opt.value = String(p.code_progr);
                opt.textContent = String(p.heure || '');
                opt.dataset.heure = String(p.heure || '');
                heureEl.add(opt);
            });

        let selected = false;
        if (preferCodePro) {
            selected = setSelectValue(heureEl, preferCodePro);
        }
        if (!selected && preferHeure) {
            for (let i = 0; i < heureEl.options.length; i++) {
                const h = heureEl.options[i].dataset.heure || heureEl.options[i].textContent;
                if (String(h) === String(preferHeure)) {
                    heureEl.selectedIndex = i;
                    selected = true;
                    break;
                }
            }
        }
        if (selected && heureEl.value) {
            loadSiegesForProgramme(heureEl.value);
        }
    }

    document.querySelectorAll('.updatedticket').forEach(function (e) {
        e.addEventListener('click', function () {
            const mtForm = document.querySelector('#mdtickForm');
            if (!mtForm) {
                return;
            }
            mtForm.setAttribute(
                'action',
                `${appRoot()}/Historique_Passagers/modifdepart/${e.dataset.cle_compagnie}/${e.dataset.passagecod}/${e.dataset.codticket}`
            );
            const title = document.querySelector('h3#mtickTitle');
            if (title) {
                title.innerHTML = `MODIFICATION DÉPART / SIÈGE : ${e.dataset.nom || ''}`;
            }

            const setHid = (sel, val) => {
                const el = document.querySelector(sel);
                if (el) {
                    el.value = val == null ? '' : String(val);
                }
            };
            setHid('#anciensieg', e.dataset.siege);
            setHid('#ancien', e.dataset.ancdepart);
            setHid('#ancienprog', e.dataset.codepro);
            setHid('#sousgr', e.dataset.departsousg);

            const sgares = document.querySelector('#sgares');
            if (sgares && e.dataset.departsousg) {
                setSelectValue(sgares, e.dataset.departsousg);
            }

            resetSelect(document.querySelector('#idquartier'), 'Choisir le quartier');
            resetSelect(document.querySelector('#dateclient'), 'Choisir la date');
            resetSelect(document.querySelector('#departclient'), "Choisir l'heure");
            resetSelect(document.querySelector('#siegeclient'), 'Choisir le siège');
            const messieg = document.querySelector('#messieg');
            if (messieg) {
                messieg.style.display = 'none';
            }

            const idlg = e.dataset.ancdepart || '';
            if (!idlg) {
                return;
            }

            const httpRequetesq = new XMLHttpRequest();
            httpRequetesq.open(
                'GET',
                window.location.origin + `${appRoot()}/confirmation/verifconfquart/${encodeURIComponent(idlg)}`,
                true
            );
            httpRequetesq.onload = () => {
                let qdata = null;
                try {
                    qdata = JSON.parse(httpRequetesq.responseText);
                } catch (err) {
                    qdata = null;
                }
                const qEl = document.querySelector('#idquartier');
                resetSelect(qEl, 'Choisir le quartier');
                if (qdata && Object.entries(qdata).length >= 1) {
                    for (let key in Object.entries(qdata)) {
                        const row = qdata[key];
                        if (!row || !row.nom_quartier) {
                            continue;
                        }
                        const opt = document.createElement('option');
                        opt.value = row.nom_quartier;
                        opt.textContent = row.nom_quartier;
                        qEl.add(opt);
                    }
                }
                if (e.dataset.quartier) {
                    setSelectValue(qEl, e.dataset.quartier);
                }
            };
            httpRequetesq.setRequestHeader('Content-Type', 'application/json');
            httpRequetesq.send();

            const httpRequetes = new XMLHttpRequest();
            httpRequetes.open(
                'GET',
                window.location.origin + `${appRoot()}/programmes/verifprogrammes/${encodeURIComponent(idlg)}`,
                true
            );
            httpRequetes.onload = () => {
                let dataAxe = null;
                try {
                    dataAxe = JSON.parse(httpRequetes.responseText);
                } catch (err) {
                    dataAxe = null;
                }
                programmesCache = [];
                if (dataAxe && Object.entries(dataAxe).length >= 1) {
                    for (let key in Object.entries(dataAxe)) {
                        if (dataAxe[key]) {
                            programmesCache.push(dataAxe[key]);
                        }
                    }
                }

                const dateEl = document.querySelector('#dateclient');
                resetSelect(dateEl, 'Choisir la date');
                const seenDates = {};
                programmesCache.forEach((p) => {
                    const d = p && p.date_progr != null ? String(p.date_progr) : '';
                    if (!d || seenDates[d]) {
                        return;
                    }
                    seenDates[d] = true;
                    const opt = document.createElement('option');
                    opt.value = d;
                    opt.textContent = d;
                    dateEl.add(opt);
                });

                const preferDate = e.dataset.dateprogr || '';
                const preferCode = e.dataset.codepro || '';
                const preferHeure = e.dataset.heure || '';
                if (preferDate && setSelectValue(dateEl, preferDate)) {
                    fillHeures(preferDate, preferCode, preferHeure);
                } else if (preferCode) {
                    const match = programmesCache.find((p) => p && String(p.code_progr) === String(preferCode));
                    if (match && match.date_progr && setSelectValue(dateEl, match.date_progr)) {
                        fillHeures(match.date_progr, preferCode, preferHeure);
                    }
                }
            };
            httpRequetes.setRequestHeader('Content-Type', 'application/json');
            httpRequetes.send();
        });
    });

    const dateEl = document.querySelector('#dateclient');
    if (dateEl) {
        dateEl.addEventListener('change', function () {
            fillHeures(dateEl.value, '', '');
        });
    }

    const heureEl = document.querySelector('#departclient');
    if (heureEl) {
        heureEl.addEventListener('change', function () {
            loadSiegesForProgramme(heureEl.value);
        });
    }

    const siegeEl = document.querySelector('#siegeclient');
    if (siegeEl) {
        siegeEl.addEventListener('change', function () {
            const codePro = (document.querySelector('#departclient') || {}).value || '';
            const siegeVal = siegeEl.value || '';
            const messieg = document.querySelector('#messieg');
            if (!codePro || !siegeVal) {
                return;
            }
            const Requestsiegereserve = new XMLHttpRequest();
            Requestsiegereserve.open(
                'GET',
                window.location.origin
                    + `${appRoot()}/programmes/verifisieges/${encodeURIComponent(codePro)}/${encodeURIComponent(siegeVal)}`,
                true
            );
            Requestsiegereserve.onload = () => {
                let reservdonsieg = null;
                try {
                    reservdonsieg = JSON.parse(Requestsiegereserve.responseText);
                } catch (err) {
                    reservdonsieg = null;
                }
                const empty =
                    reservdonsieg == '' ||
                    reservdonsieg === null ||
                    (Array.isArray(reservdonsieg) && reservdonsieg.length === 0) ||
                    (typeof reservdonsieg === 'object' && Object.keys(reservdonsieg).length === 0);

                if (empty) {
                    const httpSiegsreserv = new XMLHttpRequest();
                    httpSiegsreserv.open(
                        'GET',
                        window.location.origin
                            + `${appRoot()}/programmes/creersiege/${encodeURIComponent(codePro)}/${encodeURIComponent(siegeVal)}`,
                        true
                    );
                    httpSiegsreserv.onload = () => {
                        let dongreserv = null;
                        try {
                            dongreserv = JSON.parse(httpSiegsreserv.responseText);
                        } catch (err) {
                            dongreserv = null;
                        }
                        if (messieg) {
                            messieg.style.display = 'none';
                        }
                        if (dongreserv && Object.entries(dongreserv).length >= 1) {
                            for (let key in Object.entries(dongreserv)) {
                                const row = dongreserv[key];
                                if (!row) continue;
                                const idt = document.querySelector('#idtamposelect');
                                const sg = document.querySelector('#siegselect');
                                if (idt) idt.value = row.idtamp != null ? row.idtamp : '';
                                if (sg) sg.value = row.numsieg != null ? row.numsieg : '';
                            }
                        }
                    };
                    httpSiegsreserv.setRequestHeader('Content-Type', 'application/json');
                    httpSiegsreserv.send();
                } else {
                    siegeEl.value = '';
                    if (messieg) {
                        messieg.style.display = 'block';
                    }
                    const err = document.querySelector('#erreurmessieg');
                    if (err) {
                        err.innerHTML = 'Siège déjà utilisé.';
                    }
                }
            };
            Requestsiegereserve.setRequestHeader('Content-Type', 'application/json');
            Requestsiegereserve.send();
        });
    }
});

