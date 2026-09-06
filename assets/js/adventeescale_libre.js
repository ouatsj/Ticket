document.addEventListener('DOMContentLoaded', () => {
    function bindVenteEscaleLibre(root) {
        if (!root || root.dataset.escaleLibreBound === '1') {
            return;
        }
        root.dataset.escaleLibreBound = '1';

        const form = root.querySelector('#escalLibreForm') || document.querySelector('#escalLibreForm');
        const selDepart = root.querySelector('#escale_depart') || document.querySelector('#escale_depart');
        const selDest = root.querySelector('#id_escale_dest') || document.querySelector('#id_escale_dest');
        const prixInput = root.querySelector('#prix_axeescal') || document.querySelector('#prix_axeescal');
        const prixHint = root.querySelector('#prix_escale_hint') || document.querySelector('#prix_escale_hint');
        const contact = root.querySelector('#rnclient_contactescal') || document.querySelector('#rnclient_contactescal');
        const nom = root.querySelector('#rclientescal') || document.querySelector('#rclientescal');
        const prenom = root.querySelector('#prnclientescal') || document.querySelector('#prnclientescal');
        const clientId = root.querySelector('#pascompagnieescal') || document.querySelector('#pascompagnieescal');
        const nomRef = root.querySelector('#rclientcpescal') || document.querySelector('#rclientcpescal');
        const prenomRef = root.querySelector('#prnclientcpescal') || document.querySelector('#prnclientcpescal');
        const cle = root.getAttribute('data-cle-compagnie')
            || root.getAttribute('data-cle_compagnie')
            || root.dataset.cleCompagnie
            || root.dataset.cle_compagnie
            || '';
        const codeGaexp = root.getAttribute('data-code-gaexp')
            || root.dataset.codeGaexp
            || '';
        const departFixe = root.getAttribute('data-depart-fixe')
            || root.dataset.departFixe
            || '';

        let destRequestSeq = 0;

        function resetDest() {
            if (!selDest) return;
            selDest.options.length = 1;
            if (prixInput) prixInput.value = '';
            if (prixHint) prixHint.textContent = '';
        }

        function clearClient() {
            if (nom) nom.value = '';
            if (prenom) prenom.value = '';
            if (clientId) clientId.value = '';
            if (nomRef) nomRef.value = '';
            if (prenomRef) prenomRef.value = '';
        }

        function fillDepartOptions(rows) {
            if (!selDepart || selDepart.tagName !== 'SELECT') return;
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Choisir l\'escale…';
            selDepart.innerHTML = '';
            selDepart.appendChild(placeholder);
            const seen = {};
            (rows || []).forEach(function (row) {
                if (!row || !row.value || seen[row.value]) return;
                seen[row.value] = true;
                const opt = document.createElement('option');
                opt.value = row.value;
                opt.textContent = row.label;
                selDepart.appendChild(opt);
            });
        }

        function fillDestOptions(rows) {
            if (!selDest) return;
            resetDest();
            const seen = {};
            (rows || []).forEach(function (row) {
                if (!row) return;
                const val = row.value || ('escale~' + row.id_escale);
                if (!val || seen[val]) return;
                seen[val] = true;
                const opt = document.createElement('option');
                opt.value = val;
                opt.textContent = row.label;
                opt.setAttribute('data-prix', row.prix_escale);
                selDest.appendChild(opt);
            });
            if ((!rows || !rows.length) && prixHint) {
                prixHint.textContent = 'Aucune destination sur cet itinéraire.';
            }
        }

        function loadDestinations(departValue) {
            if (!departValue || !selDest) return;
            const seq = ++destRequestSeq;
            const http = new XMLHttpRequest();
            http.open('GET', window.location.origin + APP_ROOT + '/programmes/verifescalesdestvente/' + encodeURIComponent(departValue), true);
            http.onload = function () {
                if (seq !== destRequestSeq) return;
                let rows = [];
                try { rows = JSON.parse(http.responseText) || []; } catch (err) { rows = []; }
                fillDestOptions(Array.isArray(rows) ? rows : []);
            };
            http.onerror = function () {
                if (seq !== destRequestSeq) return;
                if (prixHint) prixHint.textContent = 'Erreur chargement destinations.';
            };
            http.send();
        }

        function loadDepartPoints(force) {
            if (departFixe) {
                loadDestinations(departFixe);
                return;
            }
            if (!selDepart || selDepart.tagName !== 'SELECT') return;
            if (!codeGaexp) {
                if (prixHint) {
                    prixHint.textContent = 'Code gare manquant — contactez l\'administrateur.';
                }
                return;
            }
            if (!force && selDepart.options.length > 1) return;
            const http = new XMLHttpRequest();
            http.open('GET', window.location.origin + APP_ROOT + '/programmes/verifescalesdepart/' + encodeURIComponent(codeGaexp), true);
            http.onload = function () {
                let rows = [];
                try { rows = JSON.parse(http.responseText) || []; } catch (err) { rows = []; }
                fillDepartOptions(Array.isArray(rows) ? rows : []);
                if ((!rows || !rows.length) && prixHint) {
                    prixHint.textContent = 'Aucune escale / ligne pour cette gare.';
                }
            };
            http.onerror = function () {
                if (prixHint) prixHint.textContent = 'Erreur chargement des escales.';
            };
            http.send();
        }

        if (selDepart && selDepart.tagName === 'SELECT') {
            selDepart.onchange = function () {
                resetDest();
                const val = selDepart.value;
                if (!val) return;
                loadDestinations(val);
            };
        }

        if (selDest) {
            selDest.onchange = function () {
                const opt = selDest.options[selDest.selectedIndex];
                const prix = opt ? opt.getAttribute('data-prix') : '';
                if (prixInput) prixInput.value = prix || '';
                if (prixHint) {
                    prixHint.textContent = (prix !== '' && prix != null)
                        ? ('Prix : ' + Number(prix).toLocaleString('fr-FR') + ' FCFA')
                        : '';
                }
            };
        }

        if (contact) {
            contact.onkeyup = function () {
                const verificat = contact.value.trim();
                if (verificat.length < 6) {
                    clearClient();
                    return;
                }
                const httpInfos = new XMLHttpRequest();
                httpInfos.open('GET', window.location.origin + APP_ROOT + '/programmes/verifinfos/' + encodeURIComponent(verificat), true);
                httpInfos.onload = function () {
                    let infos = null;
                    try { infos = JSON.parse(httpInfos.responseText); } catch (err) { infos = null; }
                    if (!infos || !infos.contact_client) {
                        clearClient();
                        return;
                    }
                    if (nom) nom.value = infos.nom_client || '';
                    if (prenom) prenom.value = infos.prenom_client || '';
                    if (clientId) clientId.value = infos.id_client || '';
                    if (nomRef) nomRef.value = infos.nom_client || '';
                    if (prenomRef) prenomRef.value = infos.prenom_client || '';
                };
                httpInfos.send();
            };
        }

        function setFormAction() {
            if (form && cle) {
                form.setAttribute('action', APP_ROOT + '/Ventescales/passagerescal_libre/' + cle);
            }
        }

        if (form) {
            form.onsubmit = setFormAction;
        }
        const btn = root.querySelector('#bottonescal_libre') || document.querySelector('#bottonescal_libre');
        if (btn) {
            btn.onclick = setFormAction;
        }

        loadDepartPoints(false);
        root._escaleLibreReload = function () { loadDepartPoints(true); };
    }

    document.querySelectorAll('.adventeescale-libre').forEach(bindVenteEscaleLibre);

    document.querySelectorAll('.addventeescalelibre').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const modal = document.querySelector('#ticketescal-0');
            if (!modal) return;
            const card = modal.querySelector('.adventeescale-libre');
            if (!card) return;
            bindVenteEscaleLibre(card);
            if (typeof card._escaleLibreReload === 'function') {
                card._escaleLibreReload();
            }
        });
    });
});
