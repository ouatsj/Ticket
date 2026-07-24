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
