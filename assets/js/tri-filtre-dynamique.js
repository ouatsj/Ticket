/**
 * Cascade tri états : COMPAGNIE → GARE → DATES → lignes / guichetiers.
 */
(function () {
    function appRoot() {
        return (typeof APP_ROOT !== 'undefined' && APP_ROOT) ? APP_ROOT : '';
    }

    function jsonGet(url, onOk, seqHolder, seq) {
        const http = window.XMLHttpRequest
            ? new XMLHttpRequest()
            : new ActiveXObject('Microsoft.XMLHTTP');
        http.open('GET', url, true);
        http.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        http.setRequestHeader('Accept', 'application/json');
        http.onload = () => {
            if (seqHolder && seqHolder.seq !== seq) {
                return;
            }
            let data = [];
            try {
                const raw = (http.responseText || '').trim();
                if (raw.charAt(0) === '[' || raw.charAt(0) === '{') {
                    data = JSON.parse(raw);
                }
            } catch (e) {
                data = [];
            }
            if (Array.isArray(data)) {
                onOk(data);
            } else if (data && typeof data === 'object') {
                onOk(Object.keys(data).map((k) => data[k]).filter((v) => v && typeof v === 'object'));
            } else {
                onOk([]);
            }
        };
        http.onerror = () => {
            if (seqHolder && seqHolder.seq !== seq) {
                return;
            }
            onOk([]);
        };
        http.send();
    }

    function resetSelect(select, keepLabel) {
        if (!select) {
            return;
        }
        const firstText = keepLabel
            || (select.options.length ? select.options[0].text : '');
        const firstVal = select.options.length ? select.options[0].value : '';
        select.options.length = 0;
        const keep = document.createElement('option');
        keep.value = firstVal === undefined || firstVal === null ? '' : firstVal;
        keep.text = firstText || '';
        select.add(keep);
    }

    function fillUsers(select, rows) {
        if (!select) {
            return;
        }
        const prev = String(select.value || '').trim();
        const placeholder = select.options.length ? select.options[0].text : 'Tous';
        resetSelect(select, placeholder);
        (rows || []).forEach((row) => {
            if (!row) {
                return;
            }
            const val = row.roleattribut != null ? String(row.roleattribut) : '';
            if (val === '') {
                return;
            }
            const opt = document.createElement('option');
            opt.value = val;
            opt.text = row.username || val;
            select.add(opt);
        });
        if (prev) {
            select.value = prev;
            if (select.value !== prev) {
                // Valeur précédente absente de la nouvelle liste : la réinjecter.
                const keep = document.createElement('option');
                keep.value = prev;
                keep.text = prev;
                keep.selected = true;
                select.add(keep);
            }
        }
    }

    function fillLignes(select, rows) {
        if (!select) {
            return;
        }
        resetSelect(select, 'Toutes lignes');
        (rows || []).forEach((row) => {
            if (!row) {
                return;
            }
            const val = row.ident_ligne != null ? String(row.ident_ligne) : '';
            if (val === '') {
                return;
            }
            const opt = document.createElement('option');
            opt.value = val;
            opt.text = row.nom_ligne || val;
            select.add(opt);
        });
    }

    function fillGares(select, rows) {
        if (!select) {
            return;
        }
        resetSelect(select, 'Choisir une gare');
        (rows || []).forEach((row) => {
            if (!row) {
                return;
            }
            const val = row.code_gaexp != null ? String(row.code_gaexp) : '';
            if (val === '') {
                return;
            }
            const opt = document.createElement('option');
            opt.value = val;
            const label = row.nom_gaep || val;
            opt.text = label;
            opt.setAttribute('data-garesid', row.garesid || '');
            select.add(opt);
        });
    }

    function q(root, sel) {
        if (!root || !sel) {
            return null;
        }
        return root.querySelector(sel);
    }

    function wire(cfg) {
        const root = document.querySelector(cfg.root);
        if (!root) {
            return;
        }
        const compEl = cfg.comp ? q(root, cfg.comp) : null;
        const gareEl = q(root, cfg.gare);
        const duEl = q(root, cfg.du);
        const auEl = q(root, cfg.au);
        const usersEl = cfg.users ? q(root, cfg.users) : null;
        const lignesEl = cfg.lignes ? q(root, cfg.lignes) : null;
        const userType = cfg.userType || 'ticket';
        const seqUsers = { seq: 0 };
        const seqLignes = { seq: 0 };
        const seqGares = { seq: 0 };

        function compVal() {
            return compEl ? String(compEl.value || '').trim() : '';
        }

        function gareVal() {
            return gareEl ? String(gareEl.value || '').trim() : '';
        }

        function clearDownstreamFromComp() {
            if (gareEl) {
                resetSelect(gareEl, 'Choisir une gare');
            }
            if (usersEl) {
                resetSelect(usersEl, usersEl.options.length ? usersEl.options[0].text : '');
            }
            if (lignesEl) {
                resetSelect(lignesEl, 'Toutes lignes');
            }
        }

        function reloadGares() {
            if (!gareEl) {
                return;
            }
            const comp = compVal();
            if (!comp) {
                clearDownstreamFromComp();
                return;
            }
            const seq = ++seqGares.seq;
            const url = `${window.location.origin}${appRoot()}/utilisateurs/trigares?comp=${encodeURIComponent(comp)}`;
            jsonGet(url, (rows) => {
                fillGares(gareEl, rows);
                if (usersEl) {
                    resetSelect(usersEl, usersEl.options.length ? usersEl.options[0].text : '');
                }
                if (lignesEl) {
                    resetSelect(lignesEl, 'Toutes lignes');
                }
            }, seqGares, seq);
        }

        function reloadUsers() {
            if (!usersEl || !gareEl) {
                return;
            }
            const gare = gareVal();
            const comp = compVal();
            if (!gare || (compEl && !comp)) {
                resetSelect(usersEl, usersEl.options.length ? usersEl.options[0].text : '');
                return;
            }
            const du = duEl ? String(duEl.value || '').trim() : '';
            const au = auEl ? String(auEl.value || '').trim() : '';
            const seq = ++seqUsers.seq;
            let url = `${window.location.origin}${appRoot()}/utilisateurs/triactifs?gare=${encodeURIComponent(gare)}&type=${encodeURIComponent(userType)}`;
            if (comp) {
                url += `&comp=${encodeURIComponent(comp)}`;
            }
            if (du && au) {
                url += `&du=${encodeURIComponent(du)}&au=${encodeURIComponent(au)}`;
            }
            jsonGet(url, (rows) => fillUsers(usersEl, rows), seqUsers, seq);
        }

        function reloadLignes() {
            if (!lignesEl || !gareEl) {
                return;
            }
            const gare = gareVal();
            const comp = compVal();
            if (!gare || (compEl && !comp)) {
                resetSelect(lignesEl, 'Toutes lignes');
                return;
            }
            const seq = ++seqLignes.seq;
            let url = `${window.location.origin}${appRoot()}/utilisateurs/trilignes?gare=${encodeURIComponent(gare)}`;
            if (comp) {
                url += `&comp=${encodeURIComponent(comp)}`;
            }
            jsonGet(url, (rows) => fillLignes(lignesEl, rows), seqLignes, seq);
        }

        function onGareOrDates() {
            reloadUsers();
            reloadLignes();
        }

        if (compEl) {
            compEl.addEventListener('change', reloadGares);
            // Au chargement : vider les gares préremplies (toutes compagnies)
            // pour forcer Compagnie → Gare.
            clearDownstreamFromComp();
        }
        if (gareEl) {
            gareEl.addEventListener('change', onGareOrDates);
        }
        if (duEl) {
            duEl.addEventListener('change', reloadUsers);
            duEl.addEventListener('input', reloadUsers);
        }
        if (auEl) {
            auEl.addEventListener('change', reloadUsers);
            auEl.addEventListener('input', reloadUsers);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        wire({
            root: '#tickversglForm',
            comp: '[name="_compagversgl"]',
            gare: '[name="departgarversgl"]',
            du: '[name="datedebutversgl"]',
            au: '[name="datefinversgl"]',
            users: '#idcaissiersversgl',
            lignes: '#ligneaxeversgl',
            userType: 'ticket',
        });

        wire({
            root: '#encaisForms',
            comp: '[name="_compag"]',
            gare: '[name="departgar"]',
            du: '[name="dated"]',
            au: '[name="datef"]',
            users: 'select[name="vendeuseid"]',
            userType: 'op',
        });

        wire({
            root: '#encaisFormssg',
            comp: '[name="_compagsg"]',
            gare: '[name="departgarsg"]',
            du: '[name="datedsg"]',
            au: '[name="datefsg"]',
            users: '#idvendeusesg',
            userType: 'op',
        });

        wire({
            root: '#encaismentForm',
            comp: '[name="_compag"]',
            gare: '[name="departgar"]',
            du: '[name="dated"]',
            au: '[name="datef"]',
            users: 'select[name="vendeuseid"]',
            userType: 'ticket',
        });

        wire({
            root: '#encaismentFormbag',
            comp: '[name="_compagbag"]',
            gare: '[name="departgarbag"]',
            du: '[name="datedbag"]',
            au: '[name="datefbag"]',
            users: '#idvendeusesbag',
            userType: 'ticket',
        });

        wire({
            root: '#encaismentFormcour',
            comp: '[name="_compagcour"]',
            gare: '[name="departgarcour"]',
            du: '[name="datedcour"]',
            au: '[name="datefcour"]',
            users: '#idvendeusescour',
            userType: 'ticket',
        });

        // Avant envoi : recopier l’opérateur choisi dans ivend (filet si vendeuseid est perdu).
        ['#encaisForms', '#encaismentForm'].forEach((formSel) => {
            const form = document.querySelector(formSel);
            if (!form) {
                return;
            }
            form.addEventListener('submit', () => {
                const sel = form.querySelector('select[name="vendeuseid"]');
                const hid = form.querySelector('input[name="ivend"]');
                if (sel && hid) {
                    hid.value = String(sel.value || '').trim();
                }
            });
        });
    });
})();
