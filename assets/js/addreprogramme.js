document.addEventListener('DOMContentLoaded', () => {
    const rTitle = document.querySelector('h3#rTitle');
    if (rTitle) rTitle.innerHTML = 'REPROGRAMMATION';

    function repRows(data) {
        if (!data) return [];
        return Array.isArray(data) ? data : Object.values(data);
    }

    const infos = document.querySelector('#reprogrammer_infos');
    if (infos !== null) {
        infos.onclick = () => {
            const cocl = document.querySelector('#codeclientp').value;
            const httpRequestRep = new XMLHttpRequest();
            httpRequestRep.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/verifcodecl/${cocl}`, true);
            httpRequestRep.onload = () => {
                const donnees = JSON.parse(httpRequestRep.responseText);
                const heuredepartp = document.querySelector('#heuredepartp');
                const numsiegep = document.querySelector('#numsiegep');

                if (donnees == null) {
                    document.querySelector('#smsp').style.display = 'block';
                    document.querySelector('#erreurSmsp').innerHTML = 'Cet ticket ne peut pas être reprogrammé ici.';
                    document.querySelector('#nomclp').innerHTML = '';
                    document.querySelector('#prenomclp').innerHTML = '';
                    document.querySelector('#contactclp').innerHTML = '';
                    document.querySelector('#refclp').innerHTML = '';
                    document.querySelector('#directionclp').innerHTML = '';
                    document.querySelector('#codeclp').innerHTML = '';
                    document.querySelector('#heureclp').innerHTML = '';
                    if (heuredepartp) {
                        heuredepartp.style.display = 'none';
                        heuredepartp.options.length = 1;
                    }
                    if (numsiegep) numsiegep.style.display = 'none';
                    document.querySelector('#billetrep').style.display = 'none';
                    return;
                }

                if (Object.entries(donnees).length >= 1) {
                    document.querySelector('#smsp').style.display = 'none';
                    document.querySelector('#billetrep').style.display = 'none';
                    if (heuredepartp) {
                        heuredepartp.style.display = 'block';
                        heuredepartp.options.length = 1;
                    }
                    if (numsiegep) {
                        numsiegep.style.display = 'block';
                        numsiegep.options.length = 1;
                    }
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
                    if (heuredepartp) heuredepartp.style.display = 'none';
                    if (numsiegep) numsiegep.style.display = 'none';
                    return;
                }

                const datdepartrep = document.querySelector('#dateventerep').value;
                const daterepactu = document.querySelector('#actueldaterep').value;
                const daterep1 = new Date(datdepartrep);
                const daterep2 = new Date(daterepactu);
                const daysDiff = (daterep2.getTime() - daterep1.getTime()) / (1000 * 3600 * 24);

                if (daysDiff < 31) {
                    const hdpaxep = `${donnees.ligne_id}`;
                    const hcl = `${donnees.code_progr}`;
                    const ligneheure = `${donnees.heure_identif}`;
                    const httpRequestews = new XMLHttpRequest();
                    httpRequestews.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/hdepartprepro/${hdpaxep}/${hcl}/${ligneheure}`, true);
                    httpRequestews.onload = () => {
                        const data2 = JSON.parse(httpRequestews.responseText);
                        if (!heuredepartp) return;
                        heuredepartp.options.length = 1;
                        repRows(data2).forEach(function (row) {
                            if (!row || row.code_progr == null) return;
                            const opt = document.createElement('option');
                            opt.value = `${row.code_progr}/${row.id_ligneheure}/${row.typetarif}`;
                            opt.innerHTML = `${row.heure}/${row.date_progr}`;
                            heuredepartp.add(opt);
                        });
                    };
                    httpRequestews.setRequestHeader('Content-Type', 'application/json');
                    httpRequestews.send();
                } else {
                    document.querySelector('#nomclp').innerHTML = '';
                    document.querySelector('#prenomclp').innerHTML = '';
                    document.querySelector('#contactclp').innerHTML = '';
                    document.querySelector('#refclp').innerHTML = '';
                    document.querySelector('#directionclp').innerHTML = '';
                    document.querySelector('#codeclp').innerHTML = '';
                    document.querySelector('#heureclp').innerHTML = '';
                    if (heuredepartp) heuredepartp.style.display = 'none';
                    if (numsiegep) numsiegep.style.display = 'none';
                    document.querySelector('#billetrep').style.display = 'block';
                    document.querySelector('#billetSmsrep').innerHTML = 'Billet non valable, la durée de validité est dépassée.';
                }
            };
            httpRequestRep.setRequestHeader('Content-Type', 'application/json');
            httpRequestRep.send();
        };
    }

    const heurdep = document.querySelector('#heuredepartp');
    if (heurdep !== null) {
        heurdep.onchange = () => {
            document.querySelector('#numsiegep').options.length = 1;

            const selectorts = document.querySelector('#heuredepartp')
                .options[document.querySelector('#heuredepartp').options.selectedIndex].value;
            if (!selectorts) return;

            const postLh = selectorts.split('/');
            const selh = postLh[0];
            const httpRequerst = new XMLHttpRequest();
            httpRequerst.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/siegdispo/${selh}`, true);
            httpRequerst.onload = () => {
                const data = JSON.parse(httpRequerst.responseText);
                repRows(data).forEach(function (row) {
                    if (!row) return;
                    document.querySelector('#placevendu').value = `${row.intervalle1}`;
                    document.querySelector('#dplacevendu').value = `${row.intervalle2}`;
                    document.querySelector('#replign').value = `${row.nom_ligne}`;
                    document.querySelector('#repher').value = `${row.heure}`;
                    document.querySelector('#datereprogramme').value = `${row.date_progr}`;
                    document.querySelector('#catreprogramme').value = `${row.categori}`;
                });

                const pld = document.querySelector('#placevendu').value;
                const plf = document.querySelector('#dplacevendu').value;
                const lgr = document.querySelector('#replign').value;
                const reph = document.querySelector('#repher').value;
                const dtrep = document.querySelector('#datereprogramme').value;
                const httpRequetterep = new XMLHttpRequest();
                httpRequetterep.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${selh}/${dtrep}/${lgr}/${reph}/${pld}/${plf}`, true);
                httpRequetterep.onload = () => {
                    const dattas = JSON.parse(httpRequetterep.responseText);
                    const numsiegep = document.querySelector('#numsiegep');
                    if (!numsiegep) return;
                    numsiegep.options.length = 1;
                    repRows(dattas).forEach(function (row) {
                        if (!row || row.siege_num == null) return;
                        const opt = document.createElement('option');
                        opt.value = `${row.siege_num}`;
                        opt.innerHTML = `${row.siege_num}`;
                        numsiegep.add(opt);
                    });
                };
                httpRequetterep.setRequestHeader('Content-Type', 'application/json');
                httpRequetterep.send();
            };
            httpRequerst.setRequestHeader('Content-Type', 'application/json');
            httpRequerst.send();
        };
    }

    const numsiege = document.querySelector('#numsiegep');
    if (numsiege !== null) {
        numsiege.onchange = () => {
            const dpProgrep = document.querySelector('#programrep').value;
            const dpSiegerep = document.querySelector('#numsiegep')
                .options[document.querySelector('#numsiegep').options.selectedIndex].value;
            const Requestsiegevendu = new XMLHttpRequest();
            Requestsiegevendu.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${dpProgrep}/${dpSiegerep}`, true);
            Requestsiegevendu.onload = () => {
                const donsieg = JSON.parse(Requestsiegevendu.responseText);
                if (donsieg == '') {
                    const httpSiegsrep = new XMLHttpRequest();
                    httpSiegsrep.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${dpProgrep}/${dpSiegerep}`, true);
                    httpSiegsrep.onload = () => {
                        const dongrep = JSON.parse(httpSiegsrep.responseText);
                        document.querySelector('#erreursieg').style.display = 'none';
                        repRows(dongrep).forEach(function (row) {
                            if (!row) return;
                            document.querySelector('#idtamporep').value = `${row.idtamp}`;
                            document.querySelector('#siegselectrep').value = `${row.numsieg}`;
                        });
                    };
                    httpSiegsrep.setRequestHeader('Content-Type', 'application/json');
                    httpSiegsrep.send();
                } else {
                    document.querySelector('#numsiegep').value = '';
                    repRows(donsieg).forEach(function (row) {
                        if (!row) return;
                        document.querySelector('#idtamporep').value = `${row.idtamp}`;
                        document.querySelector('#siegselectrep').value = `${row.numsieg}`;
                    });
                    document.querySelector('#erreursieg').style.display = 'block';
                    document.querySelector('#erreurSiege').innerHTML = 'Siege déjà utilisé.';
                }
            };
            Requestsiegevendu.setRequestHeader('content-Type', 'text/json');
            Requestsiegevendu.send();
        };
    }

    const butonclicrep = document.querySelector('#rese');
    if (butonclicrep !== null) {
        butonclicrep.onclick = () => {
            const httpSiegeselectrep = new XMLHttpRequest();
            const siegselectrep = document.querySelector('#siegselectrep').value;
            const idtaprep = document.querySelector('#idtamporep').value;
            httpSiegeselectrep.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtaprep}/${siegselectrep}`, true);
            httpSiegeselectrep.onload = () => {
                document.querySelector('#erreursieg').style.display = 'none';
            };
            httpSiegeselectrep.setRequestHeader('Content-Type', 'application/json');
            httpSiegeselectrep.send();
        };
    }

    document.querySelectorAll('.addreprogramme').forEach(function (e) {
        e.addEventListener('click', function () {
            const rForm = document.querySelector('#rForm');
            if (rForm) rForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/update/${e.dataset.cle_compagnie}`);
        });
    });
});
