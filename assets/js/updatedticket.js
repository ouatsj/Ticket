document.addEventListener('DOMContentLoaded', () => {
    const appRoot = () => (typeof APP_ROOT !== 'undefined' ? APP_ROOT : '');
    let programmesCache = [];
    let currentWantSiege = '';
    let currentWantCat = '';

    function rowsFromJson(data) {
        if (!data) {
            return [];
        }
        if (Array.isArray(data)) {
            return data.filter(Boolean);
        }
        if (typeof data === 'object') {
            return Object.keys(data)
                .filter((k) => /^\d+$/.test(k) || data[k])
                .map((k) => data[k])
                .filter((row) => row && typeof row === 'object');
        }
        return [];
    }

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

    function setVal(sel, val) {
        const el = document.querySelector(sel);
        if (el) {
            el.value = val == null ? '' : String(val);
        }
    }

    function ensureCurrentSiegeOption(siegeEl) {
        if (!siegeEl || !currentWantSiege) {
            return;
        }
        const want = String(currentWantSiege);
        for (let i = 0; i < siegeEl.options.length; i++) {
            if (String(siegeEl.options[i].value).split('/')[0] === want) {
                siegeEl.selectedIndex = i;
                return;
            }
        }
        const cat = currentWantCat || '0';
        const opt = document.createElement('option');
        opt.value = `${want}/${cat}`;
        opt.textContent = want + ' (actuel)';
        siegeEl.add(opt);
        siegeEl.value = opt.value;
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

        const httpMeta = new XMLHttpRequest();
        httpMeta.open(
            'GET',
            window.location.origin + `${appRoot()}/reprogrammes/siegdispo/${encodeURIComponent(codePro)}`,
            true
        );
        httpMeta.onload = () => {
            let meta = null;
            try {
                meta = JSON.parse(httpMeta.responseText);
            } catch (err) {
                meta = null;
            }
            const rows = rowsFromJson(meta);
            let d1 = 1;
            let d2 = 70;
            if (rows.length > 0) {
                const row = rows[0];
                d1 = parseInt(row.intervalle1, 10) || 1;
                d2 = parseInt(row.intervalle2, 10) || 70;
                setVal('#pfinvendabl', d2);
                setVal('#siegfinvendabl', d1);
                setVal('#directreserv', row.nom_ligne || '');
                setVal('#reserveheur', row.heure || '');
                setVal('#datereserv', row.date_progr || '');
                setVal('#categbuse', row.categori || '');
                if (row.nom_gadest) {
                    setVal('#uarrivee', row.nom_gadest);
                    setVal('#gare_arrivee_label', row.nom_gadest);
                }
            }

            const httpSieges = new XMLHttpRequest();
            httpSieges.open(
                'GET',
                window.location.origin
                    + `${appRoot()}/programmes/siegdisponibletrans/${encodeURIComponent(codePro)}/${d1}/${d2}`,
                true
            );
            httpSieges.onload = () => {
                let donbis = null;
                try {
                    donbis = JSON.parse(httpSieges.responseText);
                } catch (err) {
                    donbis = null;
                }
                resetSelect(siegeEl, 'Choisir le siège');
                rowsFromJson(donbis).forEach((row) => {
                    if (!row || row.siege_num == null) {
                        return;
                    }
                    const opt = document.createElement('option');
                    opt.value = `${row.siege_num}/${row.idcat_bus || row.categori || '0'}`;
                    opt.textContent = String(row.siege_num);
                    siegeEl.add(opt);
                });
                ensureCurrentSiegeOption(siegeEl);
            };
            httpSieges.send();
        };
        httpMeta.send();
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
                if (p.nom_gadest) {
                    opt.dataset.arrivee = String(p.nom_gadest);
                }
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
            const opt = heureEl.options[heureEl.selectedIndex];
            if (opt && opt.dataset.arrivee) {
                setVal('#uarrivee', opt.dataset.arrivee);
                setVal('#gare_arrivee_label', opt.dataset.arrivee);
            }
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
                `${appRoot()}/Historique_Passagers/modifadmin/${e.dataset.cle_compagnie}/${e.dataset.passagecod}/${e.dataset.codticket || e.dataset.ticketcod}`
            );

            const jambe = e.dataset.jambe || '1';
            const title = document.querySelector('h3#mtickTitle');
            if (title) {
                title.innerHTML = `MODIFICATION TICKET : ${e.dataset.nom || ''} ${e.dataset.prenom || ''} (jambe ${jambe})`;
            }
            const meta = document.querySelector('#umodifmeta');
            if (meta) {
                meta.textContent = `Axe : ${e.dataset.axe || ''} · Code : ${e.dataset.passagecod || ''} / ${e.dataset.codticket || e.dataset.ticketcod || ''}`;
            }

            currentWantSiege = e.dataset.siege || '';
            currentWantCat = e.dataset.numcat || '';

            setVal('#anciensieg', e.dataset.siege);
            setVal('#ancien', e.dataset.ancdepart);
            setVal('#ancienprog', e.dataset.codepro);
            setVal('#sousgr', e.dataset.departsousg);
            setVal('#identifyclientid', e.dataset.id_client);
            setVal('#identifycontactid', e.dataset.contact);
            setVal('#uclient_contact', e.dataset.contact);
            setVal('#uclient', e.dataset.nom);
            setVal('#uprnclient', e.dataset.prenom);
            setVal('#ucnib', e.dataset.cni);
            setVal('#udate_cnib', e.dataset.cnideliver);
            setVal('#ulieudelivre', e.dataset.cnideliverzone);
            setVal('#uprixticket', e.dataset.prix);
            setVal('#uarrivee', e.dataset.arrivee);
            setVal('#gare_arrivee_label', e.dataset.arrivee);

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

            const httpQ = new XMLHttpRequest();
            httpQ.open(
                'GET',
                window.location.origin + `${appRoot()}/confirmation/verifconfquart/${encodeURIComponent(idlg)}`,
                true
            );
            httpQ.onload = () => {
                let qdata = null;
                try {
                    qdata = JSON.parse(httpQ.responseText);
                } catch (err) {
                    qdata = null;
                }
                const qEl = document.querySelector('#idquartier');
                resetSelect(qEl, 'Choisir le quartier');
                rowsFromJson(qdata).forEach((row) => {
                    if (!row || !row.nom_quartier) {
                        return;
                    }
                    const opt = document.createElement('option');
                    opt.value = row.nom_quartier;
                    opt.textContent = row.nom_quartier;
                    qEl.add(opt);
                });
                if (e.dataset.quartier) {
                    if (!setSelectValue(qEl, e.dataset.quartier)) {
                        const opt = document.createElement('option');
                        opt.value = e.dataset.quartier;
                        opt.textContent = e.dataset.quartier;
                        qEl.add(opt);
                        qEl.value = e.dataset.quartier;
                    }
                }
            };
            httpQ.send();

            const httpP = new XMLHttpRequest();
            httpP.open(
                'GET',
                window.location.origin + `${appRoot()}/programmes/verifprogrammes/${encodeURIComponent(idlg)}`,
                true
            );
            httpP.onload = () => {
                let dataAxe = null;
                try {
                    dataAxe = JSON.parse(httpP.responseText);
                } catch (err) {
                    dataAxe = null;
                }
                programmesCache = rowsFromJson(dataAxe);

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
            httpP.send();
        });
    });

    const dateEl = document.querySelector('#dateclient');
    if (dateEl) {
        dateEl.addEventListener('change', function () {
            // Nouveau créneau → ne pas forcer l'ancien siège.
            currentWantSiege = '';
            currentWantCat = '';
            fillHeures(dateEl.value, '', '');
        });
    }

    const heureEl = document.querySelector('#departclient');
    if (heureEl) {
        heureEl.addEventListener('change', function () {
            currentWantSiege = '';
            currentWantCat = '';
            const opt = heureEl.options[heureEl.selectedIndex];
            if (opt && opt.dataset.arrivee) {
                setVal('#uarrivee', opt.dataset.arrivee);
                setVal('#gare_arrivee_label', opt.dataset.arrivee);
            }
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
            // Siège actuel du ticket ouvert : autorisé sans contrôle conflict.
            if (currentWantSiege && String(siegeVal).split('/')[0] === String(currentWantSiege)) {
                if (messieg) {
                    messieg.style.display = 'none';
                }
                return;
            }
            const req = new XMLHttpRequest();
            req.open(
                'GET',
                window.location.origin
                    + `${appRoot()}/programmes/verifisieges/${encodeURIComponent(codePro)}/${encodeURIComponent(siegeVal)}`,
                true
            );
            req.onload = () => {
                let occupied = null;
                try {
                    occupied = JSON.parse(req.responseText);
                } catch (err) {
                    occupied = null;
                }
                const empty =
                    occupied == '' ||
                    occupied === null ||
                    (Array.isArray(occupied) && occupied.length === 0) ||
                    (typeof occupied === 'object' && Object.keys(occupied).length === 0);
                if (empty) {
                    if (messieg) {
                        messieg.style.display = 'none';
                    }
                    return;
                }
                siegeEl.value = '';
                if (messieg) {
                    messieg.style.display = 'block';
                }
                const err = document.querySelector('#erreurmessieg');
                if (err) {
                    err.innerHTML = 'Siège déjà utilisé.';
                }
            };
            req.send();
        });
    }
});
