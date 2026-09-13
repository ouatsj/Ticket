/* Bundle guichet role=8 — genere par scripts/build_guichet_bundles.php */
/* --- filtre_arrivee_compagnie.js --- */
/**
 * Filtre les selects gares d'arrivée par checkboxes compagnies.
 * Une seule compagnie à la fois : seules ses gares apparaissent dans Arrivée.
 * Par défaut : CBT cochée.
 */
document.addEventListener('DOMContentLoaded', function () {
    function isCbtCompany(nom) {
        var n = String(nom || '').trim().toUpperCase();
        if (!n) return false;
        if (n === 'CBT' || n.indexOf('CBT_') === 0 || n.indexOf('CBT ') === 0) return true;
        return /(^|[^A-Z0-9])CBT([^A-Z0-9]|$)/.test(n);
    }

    /** Garantit exactement une compagnie cochée (évite liste Arrivée vide). */
    function ensureOneCompanyChecked(box) {
        if (!box) return;
        var checks = box.querySelectorAll('.js-filtre-compagnie-check');
        if (!checks.length) return;
        var checked = box.querySelectorAll('.js-filtre-compagnie-check:checked');
        if (checked.length === 1) return;
        if (checked.length > 1) {
            for (var i = 1; i < checked.length; i++) {
                checked[i].checked = false;
            }
            return;
        }
        // Aucune cochée : préférer CBT, sinon la première.
        var pick = null;
        for (var j = 0; j < checks.length; j++) {
            if (isCbtCompany(checks[j].getAttribute('data-nom-compagnie'))) {
                pick = checks[j];
                break;
            }
        }
        if (!pick) pick = checks[0];
        pick.checked = true;
    }

    function uniqueCompanies(arriveeSelect) {
        var map = {};
        var order = [];
        arriveeSelect.querySelectorAll('option[data-compagnie]').forEach(function (opt) {
            var cle = String(opt.getAttribute('data-compagnie') || '');
            if (!cle || map[cle]) return;
            var nom = opt.getAttribute('data-nom-compagnie')
                || (opt.parentNode && opt.parentNode.label)
                || cle;
            map[cle] = nom;
            order.push(cle);
        });
        return { map: map, order: order };
    }

    /**
     * Snapshot du select : placeholder + groupes {cle, nom, options:[{value,text,attrs}]}
     */
    function snapshotArrivee(arriveeSelect) {
        var placeholder = null;
        var groups = [];
        var groupMap = {};

        Array.prototype.forEach.call(arriveeSelect.children, function (child) {
            if (child.tagName === 'OPTION') {
                if (!child.getAttribute('data-compagnie')) {
                    if (!placeholder) {
                        placeholder = {
                            value: child.value,
                            text: child.textContent,
                            html: child.outerHTML
                        };
                    }
                }
                return;
            }
            if (child.tagName === 'OPTGROUP') {
                var cle = String(child.getAttribute('data-compagnie') || '');
                var nom = child.getAttribute('label') || cle;
                if (!cle) {
                    var first = child.querySelector('option[data-compagnie]');
                    if (first) cle = String(first.getAttribute('data-compagnie') || '');
                }
                if (!cle) return;
                if (!groupMap[cle]) {
                    groupMap[cle] = { cle: cle, nom: nom, options: [] };
                    groups.push(groupMap[cle]);
                }
                Array.prototype.forEach.call(child.querySelectorAll('option'), function (opt) {
                    groupMap[cle].options.push({
                        value: opt.value,
                        text: opt.textContent,
                        compagnie: String(opt.getAttribute('data-compagnie') || cle),
                        nomCompagnie: opt.getAttribute('data-nom-compagnie') || nom
                    });
                });
            }
        });

        // Options hors optgroup avec data-compagnie
        arriveeSelect.querySelectorAll(':scope > option[data-compagnie]').forEach(function (opt) {
            var cle = String(opt.getAttribute('data-compagnie') || '');
            if (!cle) return;
            if (!groupMap[cle]) {
                var nom = opt.getAttribute('data-nom-compagnie') || cle;
                groupMap[cle] = { cle: cle, nom: nom, options: [] };
                groups.push(groupMap[cle]);
            }
            groupMap[cle].options.push({
                value: opt.value,
                text: opt.textContent,
                compagnie: cle,
                nomCompagnie: opt.getAttribute('data-nom-compagnie') || groupMap[cle].nom
            });
        });

        return { placeholder: placeholder, groups: groups };
    }

    function rebuildArrivee(arriveeSelect, snap, activeCle) {
        var prev = arriveeSelect.value;
        arriveeSelect.innerHTML = '';

        var ph = document.createElement('option');
        ph.value = snap.placeholder ? snap.placeholder.value : '';
        ph.textContent = snap.placeholder && snap.placeholder.text
            ? snap.placeholder.text
            : 'Choisissez l\'arrivée';
        arriveeSelect.appendChild(ph);

        // Jamais de liste vide : si pas de compagnie active, prendre le 1er groupe.
        if (!activeCle && snap.groups && snap.groups.length) {
            activeCle = String(snap.groups[0].cle);
        }
        if (!activeCle) {
            arriveeSelect.value = '';
            return prev !== '';
        }

        var kept = false;
        snap.groups.forEach(function (g) {
            if (String(g.cle) !== String(activeCle)) return;
            var og = document.createElement('optgroup');
            og.label = g.nom;
            og.setAttribute('data-compagnie', g.cle);
            g.options.forEach(function (o) {
                var opt = document.createElement('option');
                opt.value = o.value;
                opt.textContent = o.text;
                opt.setAttribute('data-compagnie', o.compagnie);
                opt.setAttribute('data-nom-compagnie', o.nomCompagnie);
                og.appendChild(opt);
                if (o.value === prev) kept = true;
            });
            arriveeSelect.appendChild(og);
        });

        if (kept) {
            arriveeSelect.value = prev;
            return false;
        }
        arriveeSelect.value = '';
        return prev !== '';
    }

    function activeCleFromBox(box) {
        var checked = box.querySelector('.js-filtre-compagnie-check:checked');
        return checked ? String(checked.value) : '';
    }

    function applyArriveeFilter(box) {
        if (!box || !box._snap) return;
        // Toujours le select lié à cette boîte (évite collision d'id #arrsgare dupliqués).
        var arriveeSelect = box._arriveeSelect;
        if (!arriveeSelect) {
            var targetId = box.getAttribute('data-target-arrivee');
            arriveeSelect = targetId ? document.getElementById(targetId) : null;
        }
        if (!arriveeSelect) return;

        ensureOneCompanyChecked(box);
        var cleared = rebuildArrivee(arriveeSelect, box._snap, activeCleFromBox(box));
        if (cleared) {
            if (typeof window.jQuery !== 'undefined') {
                window.jQuery(arriveeSelect).trigger('change');
            } else {
                arriveeSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    }

    function syncFiltreVisibility(arriveeSelect, box) {
        if (!arriveeSelect || !box) return;
        // Ancré sous le choix ticket : toujours visible (indépendant du masquage Arrivée en transit)
        if (box.parentNode && box.parentNode.getAttribute('data-compagnies-arrivee-for')) {
            box.style.display = '';
            return;
        }
        var disp = arriveeSelect.style.display;
        if (disp === 'none') {
            box.style.display = 'none';
        } else if (disp) {
            box.style.display = disp;
        } else {
            box.style.display = '';
        }
    }

    function placeCompanyBox(box, arriveeSelect) {
        var targetId = arriveeSelect.id;
        var scope = arriveeSelect.closest('.modal-container, form, .card-body, .card') || document;
        var slot = scope.querySelector('[data-compagnies-arrivee-for="' + targetId + '"]');
        if (!slot) {
            slot = document.querySelector('[data-compagnies-arrivee-for="' + targetId + '"]');
        }
        if (slot) {
            slot.innerHTML = '';
            slot.appendChild(box);
            box.style.marginTop = '0.25rem';
            box.style.marginBottom = '0.5rem';
            return;
        }

        // Fallback ventes : barre pleine largeur au-dessus de la ligne Départ/Arrivée
        var row = arriveeSelect.closest('.row');
        if (row && row.parentNode) {
            var wrap = document.createElement('div');
            wrap.className = 'px-3 pb-2 col-12';
            wrap.setAttribute('data-compagnies-arrivee-for', targetId);
            wrap.appendChild(box);
            row.parentNode.insertBefore(wrap, row);
            return;
        }

        arriveeSelect.parentNode.insertBefore(box, arriveeSelect);
    }

    function enhanceArriveeSelect(arriveeSelect) {
        if (!arriveeSelect || arriveeSelect.getAttribute('data-filtre-arrivee-ready') === '1') {
            return;
        }
        // Hors ventes : formulaires admin lignes
        if (arriveeSelect.name === 'garearrivee') {
            return;
        }
        if (!arriveeSelect.querySelector('option[data-compagnie]')) {
            return;
        }

        var companies = uniqueCompanies(arriveeSelect);
        if (!companies.order.length) {
            return;
        }

        var snap = snapshotArrivee(arriveeSelect);

        arriveeSelect.setAttribute('data-filtre-arrivee-ready', '1');
        arriveeSelect.classList.add('js-arrivee-filtre');

        var targetId = arriveeSelect.id || ('arrivee-auto-' + Math.random().toString(36).slice(2, 9));
        if (!arriveeSelect.id) {
            arriveeSelect.id = targetId;
        }

        var box = document.createElement('div');
        box.className = 'js-filtre-compagnie-arrivee-vente mb-2';
        box.setAttribute('data-target-arrivee', targetId);
        box.setAttribute('aria-label', 'Compagnies d\'arrivée');
        box.style.cssText = 'display:flex;flex-wrap:wrap;gap:0.35rem 1rem;align-items:center;';
        box._arriveeSelect = arriveeSelect;
        box._snap = snap;

        var title = document.createElement('small');
        title.className = 'text-muted w-100 mb-0';
        title.textContent = 'Compagnies d\'arrivée';
        title.style.flexBasis = '100%';
        box.appendChild(title);

        companies.order.forEach(function (cle) {
            var nom = companies.map[cle];
            var label = document.createElement('label');
            label.className = 'mb-0';
            label.style.cssText = 'font-weight:400;cursor:pointer;white-space:nowrap;';

            var input = document.createElement('input');
            input.type = 'checkbox';
            input.className = 'js-filtre-compagnie-check';
            input.value = cle;
            input.checked = isCbtCompany(nom);
            input.style.marginRight = '0.35rem';
            input.setAttribute('data-nom-compagnie', nom);

            label.appendChild(input);
            label.appendChild(document.createTextNode(nom));
            box.appendChild(label);
        });

        ensureOneCompanyChecked(box);

        placeCompanyBox(box, arriveeSelect);

        box.addEventListener('change', function (e) {
            var t = e.target;
            if (!t || !t.classList.contains('js-filtre-compagnie-check')) return;

            // Exclusif : cocher une compagnie décoche les autres
            if (t.checked) {
                box.querySelectorAll('.js-filtre-compagnie-check').forEach(function (c) {
                    if (c !== t) c.checked = false;
                });
            } else {
                // Interdire de tout décocher → liste Arrivée vide
                ensureOneCompanyChecked(box);
            }

            applyArriveeFilter(box);
        });

        applyArriveeFilter(box);
        syncFiltreVisibility(arriveeSelect, box);

        if (window.MutationObserver) {
            var mo = new MutationObserver(function () {
                syncFiltreVisibility(arriveeSelect, box);
            });
            mo.observe(arriveeSelect, { attributes: true, attributeFilter: ['style'] });
        }
    }

    function bindAll(root) {
        root = root || document;
        root.querySelectorAll('select').forEach(function (sel) {
            if (sel.closest && sel.closest('.js-filtre-compagnie-arrivee-vente')) return;
            if (sel.querySelector('option[data-compagnie]')) {
                enhanceArriveeSelect(sel);
            }
        });
    }

    bindAll(document);
    window.__bindFiltreArriveeCompagnie = bindAll;
});

;
/* --- addconfirm_unifie.js --- */
/**
 * Confirmation unifiée — lot 1 (direct) + lot 2 (transit) + lot 3 (code d’ailleurs).
 */
(function () {
    'use strict';

    window.__confirmState = {
        rows: [],
        chemins: [],
        segData: {},
        pathMode: 'direct',
        ok: false,
        allowExterne: false,
        printUrl: '',
        codes: [],
        nbrJambes: 1,
        axes: [],
        etapesRetour: [],
        loadSeq: 0,
        cheminsSeq: 0
    };

    function __cQ(id) {
        return document.getElementById(id);
    }

    function __cSetVal(id, v) {
        var el = __cQ(id);
        if (el) el.value = v == null ? '' : String(v);
    }

    function __cShowErr(msg) {
        var w = __cQ('confirm_sms_wrap');
        var e = __cQ('confirm_sms_err');
        if (w) w.style.display = 'block';
        if (e) e.textContent = msg || '';
    }

    function __cHideErr() {
        var w = __cQ('confirm_sms_wrap');
        if (w) w.style.display = 'none';
    }

    function __cResetSelect(sel, placeholder) {
        if (!sel) return;
        sel.innerHTML = '';
        var o = document.createElement('option');
        o.value = '';
        o.textContent = placeholder || '—';
        sel.add(o);
    }

    function __cXhrGet(url, after) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState !== 4) return;
            var data = null;
            try {
                data = JSON.parse(xhr.responseText);
            } catch (err) {
                data = null;
            }
            if (after) after(data);
        };
        xhr.send();
    }

    function __cXhrPostForm(url, formEl, after) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onreadystatechange = function () {
            if (xhr.readyState !== 4) return;
            var data = null;
            try {
                data = JSON.parse(xhr.responseText);
            } catch (err) {
                data = null;
            }
            if (after) after(data);
        };
        xhr.send(new FormData(formEl));
    }

    function __cHhmm(h) {
        if (!h) return '';
        var s = String(h);
        var m = s.match(/(\d{1,2}):(\d{2})/);
        if (!m) return s.trim();
        return (m[1].length === 1 ? '0' + m[1] : m[1]) + ':' + m[2];
    }

    function __cCieName(row) {
        return row.nom_compagnie_arrivee || row.nom_compagnie || row.nom_compagnie_depart || '';
    }

    function __cCieKey(row) {
        if (!row) return '';
        return String(row.id_compaga || row.cle_compagnie_arrivee || __cCieName(row) || '').trim();
    }

    function __cRowsArray(data) {
        if (Array.isArray(data)) return data;
        if (data && typeof data === 'object') {
            return Object.keys(data).map(function (k) { return data[k]; });
        }
        return [];
    }

    function __cNormalizeEtapes(raw) {
        var arr = __cRowsArray(raw);
        return arr.filter(Boolean);
    }

    function __cLigneId(et) {
        if (!et) return '';
        if (et.code_itineraires) return String(et.code_itineraires).trim();
        if (et.ident_ligne) return String(et.ident_ligne).trim();
        if (et.ligne_id) return String(et.ligne_id).trim();
        if (et.gaexp_lg && et.gadest_lg) {
            return String(et.gaexp_lg + '-' + et.gadest_lg).trim();
        }
        return '';
    }

    function __cNormDate(ymd) {
        return String(ymd || '').trim().slice(0, 10);
    }

    function __cFilterByDate(dateYmd) {
        var d = __cNormDate(dateYmd);
        if (!d) return [];
        return (window.__confirmState.rows || []).filter(function (r) {
            return r && __cNormDate(r.date_progr) === d;
        });
    }

    function __cClearEscale(resetSelect) {
        __cSetVal('id_escale_vente_confirm', '');
        __cSetVal('code_gadest_vente_confirm', '');
        __cSetVal('nom_dest_vente_confirm', '');
        if (resetSelect === false) {
            var selKeep = __cQ('confirm_escale_select');
            if (selKeep) selKeep.value = '';
            return;
        }
        var sel = __cQ('confirm_escale_select');
        __cResetSelect(sel, "Choisissez l'escale");
        var help = __cQ('confirm_escale_help');
        if (help) {
            help.textContent = 'Escales de la ligne (dernière jambe en correspondance). Confirmation gratuite.';
            help.classList.remove('text-danger');
        }
    }

    function __cEscaleChecked() {
        var c = __cQ('confirm_escale_check');
        return !!(c && c.checked);
    }

    function __cEscaleLigneCible() {
        // Transit : dernière jambe sélectionnée ; sinon ligne OD retour.
        var n = parseInt((__cQ('confirm_nbr_seg') || {}).value || '0', 10) || 0;
        if (window.__confirmState.pathMode === 'transit' && n >= 2) {
            var last = (__cQ('confirm_seg_ligne_' + (n - 1)) || {}).value || '';
            if (last) return last;
            var chemins = window.__confirmState.chemins || [];
            var sel = __cQ('confirm_itineraire_select');
            var idx = sel ? parseInt(sel.value, 10) : -1;
            if (idx >= 0 && chemins[idx]) {
                var et = __cNormalizeEtapes(chemins[idx].etapes || chemins[idx].legs);
                if (et.length) return __cLigneId(et[et.length - 1]);
            }
        }
        return (__cQ('confirm_ident_ligne') || {}).value || '';
    }

    function __cFillEscaleSelect(list) {
        var sel = __cQ('confirm_escale_select');
        var help = __cQ('confirm_escale_help');
        var prev = (__cQ('id_escale_vente_confirm') || {}).value || '';
        __cResetSelect(sel, "Choisissez l'escale");
        var arr = __cRowsArray(list);
        arr.forEach(function (e) {
            if (!e) return;
            var id = e.id_escale != null ? String(e.id_escale) : '';
            if (!id) return;
            var o = document.createElement('option');
            o.value = id;
            o.setAttribute('data-code', e.code_gadest || '');
            o.setAttribute('data-nom', e.nom_escale || e.arrivee_escale || '');
            o.textContent = (e.nom_escale || e.arrivee_escale || e.code_gadest || id);
            sel.add(o);
        });
        if (help) {
            if (!arr.length) {
                help.textContent = 'Aucune escale active sur cette ligne.';
                help.classList.add('text-danger');
            } else {
                help.textContent = arr.length + ' escale(s) — confirmation gratuite (0 F).';
                help.classList.remove('text-danger');
            }
        }
        if (prev && sel) {
            sel.value = prev;
            if (sel.value === prev) {
                __cOnEscaleSelectChange();
            } else {
                __cClearEscale(false);
            }
        } else {
            __cClearEscale(false);
        }
        __cUpdateOkBtn();
    }

    function __cLoadEscales() {
        if (!__cEscaleChecked()) {
            __cClearEscale();
            return;
        }
        var fields = __cQ('confirm_escale_fields');
        if (fields) fields.style.display = 'block';
        var help = __cQ('confirm_escale_help');
        if (help) {
            help.textContent = 'Chargement des escales…';
            help.classList.remove('text-danger');
        }
        var ligne = __cEscaleLigneCible();
        var ga = (__cQ('confirm_gaexp') || {}).value || '';
        var gd = (__cQ('confirm_gadest') || {}).value || '';
        var url = '';
        if (ligne) {
            url = window.location.origin + APP_ROOT
                + '/programmes/verifescales/' + encodeURIComponent(ligne);
        } else if (ga && gd) {
            url = window.location.origin + APP_ROOT
                + '/programmes/verifescalesod/'
                + encodeURIComponent(ga) + '/' + encodeURIComponent(gd);
        } else {
            __cFillEscaleSelect([]);
            if (help) {
                help.textContent = 'Choisissez d’abord la ligne / l’itinéraire.';
                help.classList.add('text-danger');
            }
            return;
        }
        __cXhrGet(url, function (data) {
            if (!__cEscaleChecked()) return;
            __cFillEscaleSelect(data);
        });
    }

    function __cOnEscaleCheckChange() {
        var fields = __cQ('confirm_escale_fields');
        if (__cEscaleChecked()) {
            if (fields) fields.style.display = 'block';
            __cLoadEscales();
            // Recharger les directs filtrés sur l’escale (si déjà une date).
            var dateYmd = __cNormDate((__cQ('date_confirm_unifie') || {}).value || '');
            if (dateYmd && window.__confirmState.pathMode !== 'transit') {
                // Attendre la sélection d’escale pour filtrer ; sinon liste complète.
            }
        } else {
            if (fields) fields.style.display = 'none';
            __cClearEscale();
            var dateYmd2 = __cNormDate((__cQ('date_confirm_unifie') || {}).value || '');
            if (dateYmd2) __cLoadHeures(dateYmd2);
        }
        __cUpdateOkBtn();
    }

    function __cOnEscaleSelectChange() {
        var sel = __cQ('confirm_escale_select');
        if (!sel || !sel.value) {
            __cClearEscale(false);
            __cUpdateOkBtn();
            return;
        }
        var opt = sel.options[sel.selectedIndex];
        __cSetVal('id_escale_vente_confirm', sel.value);
        __cSetVal('code_gadest_vente_confirm', opt ? (opt.getAttribute('data-code') || '') : '');
        __cSetVal('nom_dest_vente_confirm', opt ? (opt.getAttribute('data-nom') || '') : '');
        var dateYmd = __cNormDate((__cQ('date_confirm_unifie') || {}).value || '');
        if (dateYmd && window.__confirmState.pathMode !== 'transit') {
            __cLoadHeures(dateYmd);
        }
        __cUpdateOkBtn();
    }

    function __cClearSegHiddens() {
        for (var i = 0; i < 4; i++) {
            __cSetVal('confirm_seg_prog_' + i, '');
            __cSetVal('confirm_seg_ligne_' + i, '');
            __cSetVal('confirm_seg_siege_' + i, '');
            __cSetVal('confirm_seg_compaga_' + i, '');
            __cSetVal('confirm_seg_cat_' + i, '');
            __cSetVal('confirm_seg_date_' + i, '');
            __cSetVal('confirm_seg_heure_' + i, '');
        }
        __cSetVal('confirm_nbr_seg', '0');
    }

    function __cSetPathMode(mode) {
        window.__confirmState.pathMode = mode;
        __cSetVal('confirm_path_mode', mode);
        var dWrap = __cQ('confirm_direct_fields_wrap');
        var tWrap = __cQ('confirm_transit_wrap');
        if (dWrap) dWrap.style.display = (mode === 'direct' || mode === 'both') ? '' : 'none';
        if (tWrap) tWrap.style.display = (mode === 'transit' || mode === 'both') ? '' : 'none';
    }

    function __cAllowMultiChecked() {
        var el = __cQ('confirm_allow_multi');
        return !!(el && el.checked);
    }

    function __cSyncAllowMultiWrap(hasDirect) {
        var wrap = __cQ('confirm_allow_multi_wrap');
        var cb = __cQ('confirm_allow_multi');
        if (!wrap) return;
        wrap.style.display = hasDirect ? '' : 'none';
        if (!hasDirect && cb) cb.checked = false;
    }

    function __cResetDepartUi() {
        __cResetSelect(__cQ('heure_confirm_unifie'), "Choisissez l'heure");
        __cResetSelect(__cQ('cie_confirm_unifie'), 'Choisissez la compagnie');
        __cResetSelect(__cQ('siege_confirm_unifie'), 'Choisissez le siège');
        var cieWrap = __cQ('confirm_cie_wrap');
        if (cieWrap) {
            cieWrap.style.display = 'none';
            cieWrap.setAttribute('hidden', 'hidden');
        }
        __cResetSelect(__cQ('confirm_itineraire_select'), 'Choisissez un itinéraire');
        var box = __cQ('confirm_segments_box');
        if (box) box.innerHTML = '';
        window.__confirmState.segData = {};
        window.__confirmState.chemins = [];
        __cSetVal('confirm_code_pro', '');
        __cClearSegHiddens();
        __cSetPathMode('direct');
        var hint = __cQ('confirm_transit_hint');
        if (hint) {
            hint.style.display = 'none';
            hint.textContent = '';
        }
        var btn = __cQ('confirm_ok_btn');
        if (btn) btn.disabled = true;
    }

    function __cUpdateOkBtn() {
        var btn = __cQ('confirm_ok_btn');
        if (!btn) return;
        if (__cEscaleChecked()) {
            var idEsc = (__cQ('id_escale_vente_confirm') || {}).value || '';
            if (!idEsc) {
                btn.disabled = true;
                return;
            }
        }
        if (window.__confirmState.pathMode === 'transit') {
            var n = parseInt((__cQ('confirm_nbr_seg') || {}).value || '0', 10) || 0;
            var ok = n >= 2;
            for (var i = 0; i < n; i++) {
                var p = (__cQ('confirm_seg_prog_' + i) || {}).value || '';
                var s = (__cQ('confirm_seg_siege_' + i) || {}).value || '';
                if (!p || !s || s === '0' || s === '00') ok = false;
            }
            btn.disabled = !ok;
            return;
        }
        var sDirect = (__cQ('siege_confirm_unifie') || {}).value || '';
        var prog = (__cQ('confirm_code_pro') || {}).value || '';
        btn.disabled = !(sDirect && prog);
    }

    function __cLoadHeures(dateYmd) {
        var nom = (__cQ('confirm_nom_ligne') || {}).value || '';
        var ga = (__cQ('confirm_gaexp') || {}).value || '';
        var gd = (__cQ('confirm_gadest') || {}).value || '';
        var gare = (__cQ('confirm_gareconnect_code') || {}).value || ga;
        dateYmd = __cNormDate(dateYmd);
        if (!nom || !dateYmd) return;
        var seq = ++window.__confirmState.loadSeq;
        var qs = [
            'nom_ligne=' + encodeURIComponent(nom),
            'gare=' + encodeURIComponent(gare),
            'date=' + encodeURIComponent(dateYmd)
        ];
        var axes = window.__confirmState.axes || [];
        if (axes.length) {
            qs.push('axes=' + encodeURIComponent(axes.join(',')));
        }
        var idEsc = (__cQ('id_escale_vente_confirm') || {}).value || '';
        if (idEsc && __cEscaleChecked()) {
            qs.push('id_escale=' + encodeURIComponent(idEsc));
        }
        // 3ᵉ segment obligatoire (CI) : '' disparaît de l’URL → exception « 2 args ».
        var excludeSeg = '0';
        __cXhrGet(
            window.location.origin + APP_ROOT
                + '/reprogrammes/heures_unifie/'
                + encodeURIComponent(gare || ga) + '/'
                + encodeURIComponent(gd || '0') + '/'
                + encodeURIComponent(excludeSeg)
                + '?' + qs.join('&'),
            function (data) {
                if (seq !== window.__confirmState.loadSeq) return;
                var current = __cNormDate((__cQ('date_confirm_unifie') || {}).value || '');
                if (current && current !== dateYmd) return;
                window.__confirmState.rows = __cRowsArray(data);
                __cOnDateReady(dateYmd);
            }
        );
    }

    function __cCheminsFromEtapesRetour() {
        var et = window.__confirmState.etapesRetour || [];
        // Multi-codes transit uniquement (≥2 jambes vendues).
        if (et.length < 2) return [];
        var label = et.map(function (e) {
            return (e && (e.nom_ligne || e.nom_itineraires || e.code_itineraires)) || '';
        }).filter(Boolean).join(' → ');
        if (!label) label = 'Retour transit';
        return [{
            source: 'retour_transit',
            id: 'retour-transit-inverse',
            label: label + ' (' + et.length + ' segments)',
            etapes: et,
            nb_jambes: et.length
        }];
    }

    function __cFetchChemins(dateYmd) {
        var nom = (__cQ('confirm_nom_ligne') || {}).value || '';
        var ga = (__cQ('confirm_gaexp') || {}).value || '';
        var gd = (__cQ('confirm_gadest') || {}).value || '';
        var gare = (__cQ('confirm_gareconnect_code') || {}).value || ga;
        dateYmd = __cNormDate(dateYmd);
        var axe = (gare || ga) + '-' + gd;
        var fromCodes = __cCheminsFromEtapesRetour();
        var hint = __cQ('confirm_transit_hint');
        var seq = ++window.__confirmState.cheminsSeq;

        function stillCurrent() {
            if (seq !== window.__confirmState.cheminsSeq) return false;
            var current = __cNormDate((__cQ('date_confirm_unifie') || {}).value || '');
            return !current || !dateYmd || current === dateYmd;
        }

        function mergeAndFill(extra) {
            if (!stillCurrent()) return;
            var merged = fromCodes.slice();
            var seen = {};
            function sigCh(c) {
                if (!c) return '';
                if (c.codes && c.codes.length) return String(c.codes.join('>'));
                var et = __cNormalizeEtapes(c.etapes || c.legs);
                return et.map(function (e) {
                    return e.code_itineraires || e.ident_ligne || e.nom_ligne || '';
                }).join('>');
            }
            function prio(c) {
                if (!c) return -1;
                if (typeof c.priority === 'number') return c.priority;
                var s = c.source || '';
                if (s === 'hub_lie') return 100;
                if (s === 'programmes') return 80;
                if (s === 'programmes_aval') return 70;
                if (s === 'graphe_gare' || s === 'gare_composition') return 60;
                if (s === 'graphe') return 40;
                if (s === 'declaratif' || s === 'graphe_declaratif') return 20;
                return 30;
            }
            fromCodes.forEach(function (c) {
                seen[sigCh(c) || String(c.label || c.id || '')] = true;
            });
            (extra || []).forEach(function (c) {
                if (!c) return;
                var et = __cNormalizeEtapes(c.etapes || c.legs);
                if (et.length < 2) return;
                if (c.source === 'declaratif') return;
                var key = sigCh(c) || String(c.label || c.id || et.map(function (e) {
                    return e.nom_ligne || '';
                }).join('|'));
                if (seen[key]) return;
                seen[key] = true;
                merged.push({
                    source: c.source || 'programmes',
                    priority: typeof c.priority === 'number' ? c.priority : prio(c),
                    id: c.id || ('c-' + merged.length),
                    label: c.label || (et.map(function (e) {
                        return e.nom_ligne || e.nom_itineraires || '';
                    }).filter(Boolean).join(' → ') + ' (' + et.length + ' segments)'),
                    etapes: et,
                    codes: c.codes || et.map(function (e) {
                        return e.code_itineraires || e.ident_ligne || '';
                    }),
                    nb_jambes: c.nb_jambes || et.length,
                    score: typeof c.score === 'number' ? c.score : 0
                });
            });
            merged.sort(function (a, b) {
                var pa = prio(a);
                var pb = prio(b);
                if (pa !== pb) return pb - pa;
                var na = a.nb_jambes || 99;
                var nb = b.nb_jambes || 99;
                if (na !== nb) return na - nb;
                return (b.score || 0) - (a.score || 0);
            });
            __cFillItineraireSelect(merged);
        }

        if (!dateYmd) {
            mergeAndFill([]);
            return;
        }
        if (fromCodes.length) {
            mergeAndFill([]);
        } else if (hint) {
            hint.style.display = 'block';
            hint.textContent = 'Recherche d’itinéraires sur les programmes de départ…';
        }

        // 1) Programmes réels de la gare d'opération + hubs / dérivés / aval.
        var urlProg = window.location.origin + APP_ROOT
            + '/confirmation/chemins_programmes?'
            + 'gaexp=' + encodeURIComponent(gare || ga)
            + '&gadest=' + encodeURIComponent(gd)
            + '&date=' + encodeURIComponent(dateYmd)
            + '&gare=' + encodeURIComponent(gare || ga);
        if (nom) urlProg += '&nom_ligne=' + encodeURIComponent(nom);

        __cXhrGet(urlProg, function (payloadProg) {
            if (!stillCurrent()) return;
            var fromProg = [];
            if (payloadProg && Array.isArray(payloadProg.chemins)) {
                fromProg = payloadProg.chemins;
            }
            mergeAndFill(fromProg);

            // 2) Enrichir via verifchemins (même moteur programmes + graphe), gare opération.
            var axeOp = (gare || ga) + '-' + gd;
            if (!gd || !(gare || ga)) return;
            var urlGraphe = window.location.origin + APP_ROOT
                + '/programmes/verifchemins/'
                + encodeURIComponent(axeOp) + '/'
                + encodeURIComponent(dateYmd) + '/0/1'
                + '?gare=' + encodeURIComponent(gare || ga);
            if (nom) {
                urlGraphe += '&reprog=1&nom_ligne=' + encodeURIComponent(nom);
            }
            var axes = window.__confirmState.axes || [];
            if (axes.length) {
                urlGraphe += '&axes=' + encodeURIComponent(axes.join(','));
            }
            __cXhrGet(urlGraphe, function (payload) {
                if (!stillCurrent()) return;
                var chemins = [];
                if (payload) {
                    if (Array.isArray(payload.chemins)) chemins = payload.chemins;
                    else if (Array.isArray(payload)) chemins = payload;
                }
                mergeAndFill(fromProg.concat(chemins));
            });
        });
    }

    function __cFillItineraireSelect(chemins) {
        window.__confirmState.chemins = chemins || [];
        var sel = __cQ('confirm_itineraire_select');
        var hint = __cQ('confirm_transit_hint');
        var msg = __cQ('confirm_transit_msg');
        __cResetSelect(sel, 'Choisissez un itinéraire');
        if (!chemins.length) {
            if (hint) {
                hint.style.display = 'block';
                hint.textContent = 'Aucune correspondance trouvée pour cette date vers la destination.';
            }
            if (msg) msg.textContent = 'Aucun itinéraire multi disponible.';
            return;
        }
        if (hint) hint.style.display = 'none';
        if (msg) {
            msg.textContent = chemins.length + ' itinéraire(s) — chaque segment : date, heure, siège.';
        }
        chemins.forEach(function (ch, idx) {
            var et = __cNormalizeEtapes(ch.etapes || ch.legs);
            var label = ch.label || et.map(function (e) {
                return e.nom_itineraires || e.nom_ligne || e.code_itineraires || '';
            }).filter(Boolean).join(' → ');
            if (label.indexOf('segment') === -1 && label.indexOf('jambe') === -1) {
                label += ' (' + et.length + ' segments)';
            }
            var o = document.createElement('option');
            o.value = String(idx);
            o.textContent = label;
            sel.add(o);
        });
        if (sel.options.length === 2) {
            sel.selectedIndex = 1;
            sel.dispatchEvent(new Event('change'));
        }
    }

    function __cOrdinalFr(n) {
        var i = parseInt(n, 10) || 0;
        if (i <= 1) return '1ER';
        return i + 'ème';
    }

    function __cHubLabel(row) {
        if (!row) return 'normal';
        var lab = String(row.hub_label || '').trim();
        if (lab) return lab;
        var role = String(row.hub_role || '').trim();
        if (role === 'derive') return 'dérivé';
        if (role === 'suite') return 'hub/suite';
        if (role === 'principal') return 'hub/principal';
        return 'normal';
    }

    /**
     * Remplit Heure : 1 option = 1 programme de la date.
     * Même HH:MM → 1ER, 2ème… (+ compagnie / hub).
     */
    function __cFillHeuresForDate(dateYmd) {
        var heureSel = __cQ('heure_confirm_unifie');
        var cieWrap = __cQ('confirm_cie_wrap');
        var cieSel = __cQ('cie_confirm_unifie');
        __cResetSelect(heureSel, "Choisissez l'heure");
        __cResetSelect(cieSel, 'Choisissez la compagnie');
        __cResetSelect(__cQ('siege_confirm_unifie'), 'Choisissez le siège');
        __cSetVal('confirm_code_pro', '');
        if (cieWrap) {
            cieWrap.style.display = 'none';
            cieWrap.setAttribute('hidden', 'hidden');
        }
        if (!heureSel) return 0;

        var rows = __cFilterByDate(dateYmd).slice().sort(function (a, b) {
            var ha = __cHhmm(a.heure);
            var hb = __cHhmm(b.heure);
            if (ha !== hb) return ha < hb ? -1 : 1;
            var ca = String(a.code_progr || '');
            var cb = String(b.code_progr || '');
            return ca < cb ? -1 : (ca > cb ? 1 : 0);
        });

        var countByHh = {};
        rows.forEach(function (row) {
            var hh = __cHhmm(row.heure);
            if (!hh) return;
            countByHh[hh] = (countByHh[hh] || 0) + 1;
        });
        var idxByHh = {};

        rows.forEach(function (row) {
            var hh = __cHhmm(row.heure);
            if (!hh || !row.code_progr) return;
            idxByHh[hh] = (idxByHh[hh] || 0) + 1;
            var multi = (countByHh[hh] || 0) > 1;
            var hub = __cHubLabel(row);
            var cie = __cCieName(row) || 'Compagnie';
            var ligne = row.nom_ligne || '';
            var o = document.createElement('option');
            o.value = String(row.code_progr);
            o.setAttribute('data-heure', hh);
            o.setAttribute('data-code-progr', row.code_progr || '');
            o.setAttribute('data-id-lh', row.id_ligneheure || '');
            o.setAttribute('data-tarif', row.typetarif || '');
            o.setAttribute('data-cat', row.categori || '');
            o.setAttribute('data-cie', row.id_compaga || '');
            o.setAttribute('data-i1', row.intervalle1 || '');
            o.setAttribute('data-i2', row.intervalle2 || '');
            o.setAttribute('data-hub-label', hub);
            o._row = row;
            var parts = [hh];
            if (multi) parts.push(__cOrdinalFr(idxByHh[hh]));
            parts.push(cie);
            parts.push(hub);
            if (ligne) parts.push(ligne);
            o.textContent = parts.join(' — ');
            heureSel.add(o);
        });
        return rows.length;
    }

    function __cOnDateReady(dateYmd) {
        __cResetDepartUi();
        var rows = __cFilterByDate(dateYmd);
        var hint = __cQ('confirm_transit_hint');
        var allowMulti = __cAllowMultiChecked();
        __cSyncAllowMultiWrap(rows.length > 0);
        // Règle : directs seuls s'il y en a, sinon correspondance.
        // Case cochée : directs + multi-segments.
        if (rows.length && allowMulti) {
            __cSetPathMode('both');
            if (hint) {
                hint.style.display = 'block';
                hint.textContent = 'Multi activé : directs et correspondances proposés.';
            }
            __cFillHeuresForDate(dateYmd);
            __cFetchChemins(dateYmd);
            return;
        }
        if (rows.length) {
            __cSetPathMode('direct');
            if (hint) hint.style.display = 'none';
            __cFillHeuresForDate(dateYmd);
            return;
        }
        // Pas de direct → transit / correspondance depuis la gare de confirmation.
        __cSetPathMode('transit');
        if (hint) {
            hint.style.display = 'block';
            hint.textContent = 'Aucun direct : chargement des correspondances…';
        }
        __cFetchChemins(dateYmd);
    }

    function __cBuildSegments(etapes) {
        var box = __cQ('confirm_segments_box');
        if (!box) return;
        box.innerHTML = '';
        window.__confirmState.segData = {};
        __cClearSegHiddens();
        __cSetVal('confirm_nbr_seg', String(etapes.length));
        var dateRoot = (__cQ('date_confirm_unifie') || {}).value || '';

        etapes.forEach(function (etape, idx) {
            var ligneId = __cLigneId(etape);
            var ligneNom = etape.nom_itineraires || etape.nom_ligne || ligneId || ('Segment ' + (idx + 1));
            var dateDef = dateRoot;
            if (etape && etape._graphe_date_progr) {
                dateDef = String(etape._graphe_date_progr).slice(0, 10);
            }
            var wrap = document.createElement('div');
            wrap.className = 'confirm-seg';
            wrap.innerHTML =
                '<h6>Segment ' + (idx + 1) + ' — ' + ligneNom + '</h6>'
                + '<div class="form-row">'
                + '<div class="form-group col-md-6 col-lg-3 mb-2">'
                + '<label class="small mb-0">Ligne</label>'
                + '<input class="form-control form-control-sm" type="text" readonly value="'
                + String(ligneNom).replace(/"/g, '&quot;') + '">'
                + '</div>'
                + '<div class="form-group col-md-6 col-lg-3 mb-2" style="display:none" hidden>'
                + '<label class="small mb-0">Compagnie</label>'
                + '<select class="form-control form-control-sm" id="confirm_ui_cie_' + idx + '">'
                + '<option value="">Choisissez</option></select></div>'
                + '<div class="form-group col-md-6 col-lg-3 mb-2">'
                + '<label class="small mb-0">Date</label>'
                + '<input class="form-control form-control-sm" type="date" id="confirm_ui_date_' + idx
                + '" value="' + String(dateDef).replace(/"/g, '&quot;') + '"></div>'
                + '<div class="form-group col-md-6 col-lg-3 mb-2">'
                + '<label class="small mb-0">Heure (départs)</label>'
                + '<select class="form-control form-control-sm" id="confirm_ui_heure_' + idx + '">'
                + '<option value="">Choisissez l\'heure</option></select></div>'
                + '<div class="form-group col-md-6 col-lg-3 mb-2">'
                + '<label class="small mb-0">Siège</label>'
                + '<select class="form-control form-control-sm" id="confirm_ui_siege_' + idx + '">'
                + '<option value="">Choisissez le siège</option></select></div>'
                + '</div>'
                + '<p class="text-danger small mb-0" id="confirm_seg_err_' + idx + '" style="display:none"></p>';
            box.appendChild(wrap);

            window.__confirmState.segData[idx] = {
                etape: etape,
                ligneId: ligneId,
                ligneNom: ligneNom,
                byCie: {},
                byCieHour: {},
                rows: []
            };
            __cSetVal('confirm_seg_ligne_' + idx, ligneId);
            __cSetVal('confirm_seg_date_' + idx, dateDef);

            var dateEl = __cQ('confirm_ui_date_' + idx);
            if (dateEl) {
                dateEl.onchange = function () {
                    __cSetVal('confirm_seg_date_' + idx, dateEl.value || '');
                    __cLoadSegCompanies(idx);
                };
            }
            __cLoadSegCompanies(idx);
        });
        if (__cEscaleChecked()) {
            __cLoadEscales();
        }
        __cUpdateOkBtn();
    }

    function __cSegErr(idx, msg) {
        var el = __cQ('confirm_seg_err_' + idx);
        if (!el) return;
        if (!msg) {
            el.style.display = 'none';
            el.textContent = '';
            return;
        }
        el.style.display = 'block';
        el.textContent = msg;
    }

    function __cLoadSegCompanies(idx) {
        var seg = window.__confirmState.segData[idx];
        if (!seg || !seg.ligneId) {
            __cSegErr(idx, 'Ligne du segment introuvable.');
            return;
        }
        var dateYmd = (__cQ('confirm_ui_date_' + idx) || {}).value
            || (__cQ('date_confirm_unifie') || {}).value || '';
        if (!dateYmd) {
            __cSegErr(idx, 'Choisissez une date.');
            return;
        }
        var url = window.location.origin + APP_ROOT
            + '/reprogrammes/seg_progs/'
            + encodeURIComponent(seg.ligneId) + '/'
            + encodeURIComponent(dateYmd);
        var gadest = '';
        if (seg.etape) {
            gadest = String(seg.etape.code_gadest || seg.etape.gadest_lg || '').trim();
        }
        if (gadest) url += '?gadest=' + encodeURIComponent(gadest);

        __cSegErr(idx, 'Chargement…');
        __cXhrGet(url, function (data) {
            var rows = __cRowsArray(data);
            seg.rows = rows;
            seg.byCie = {};
            seg.byCieHour = {};
            rows.forEach(function (r) {
                if (!r || !r.code_progr) return;
                var cie = __cCieKey(r) || '_';
                var hh = __cHhmm(r.heure);
                if (!hh) return;
                if (!seg.byCie[cie]) {
                    seg.byCie[cie] = { key: cie, label: __cCieName(r) || cie, rows: [] };
                }
                seg.byCie[cie].rows.push(r);
                if (!seg.byCieHour[cie]) seg.byCieHour[cie] = {};
                if (!seg.byCieHour[cie][hh]) seg.byCieHour[cie][hh] = [];
                seg.byCieHour[cie][hh].push(r);
            });
            var cieSel = __cQ('confirm_ui_cie_' + idx);
            var heureSel = __cQ('confirm_ui_heure_' + idx);
            var siegeSel = __cQ('confirm_ui_siege_' + idx);
            __cResetSelect(cieSel, 'Choisissez');
            __cResetSelect(heureSel, "Choisissez l'heure");
            __cResetSelect(siegeSel, 'Choisissez le siège');
            __cSetVal('confirm_seg_prog_' + idx, '');
            __cSetVal('confirm_seg_siege_' + idx, '');
            if (!rows.length) {
                __cSegErr(idx, 'Aucun programme pour ce segment le ' + dateYmd + '.');
                __cUpdateOkBtn();
                return;
            }
            __cSegErr(idx, '');
            // 1 option Heure = 1 programme (1ER/2ème si même HH:MM).
            var sorted = rows.slice().filter(function (r) {
                return r && r.code_progr && __cHhmm(r.heure);
            }).sort(function (a, b) {
                var ha = __cHhmm(a.heure);
                var hb = __cHhmm(b.heure);
                if (ha !== hb) return ha < hb ? -1 : 1;
                return String(a.code_progr).localeCompare(String(b.code_progr));
            });
            var countByHh = {};
            sorted.forEach(function (r) {
                var hh = __cHhmm(r.heure);
                countByHh[hh] = (countByHh[hh] || 0) + 1;
            });
            var idxByHh = {};
            sorted.forEach(function (r) {
                var hh = __cHhmm(r.heure);
                idxByHh[hh] = (idxByHh[hh] || 0) + 1;
                var multi = (countByHh[hh] || 0) > 1;
                var o = document.createElement('option');
                o.value = String(r.code_progr);
                o.setAttribute('data-heure', hh);
                o.setAttribute('data-code-progr', r.code_progr || '');
                o.setAttribute('data-cie', r.id_compaga || __cCieKey(r) || '');
                o.setAttribute('data-cat', r.categori || '');
                o.setAttribute('data-i1', r.intervalle1 || '');
                o.setAttribute('data-i2', r.intervalle2 || '');
                o._row = r;
                var parts = [hh];
                if (multi) parts.push(__cOrdinalFr(idxByHh[hh]));
                parts.push(__cCieName(r) || 'Compagnie');
                parts.push(__cHubLabel(r));
                o.textContent = parts.join(' — ');
                heureSel.add(o);
            });
            // Compat : remplir cie cachée si une seule compagnie.
            var keys = Object.keys(seg.byCie);
            keys.sort().forEach(function (k) {
                var o = document.createElement('option');
                o.value = k;
                o.textContent = seg.byCie[k].label || k;
                if (cieSel) cieSel.add(o);
            });
            if (heureSel) heureSel.onchange = function () { __cOnSegHeure(idx); };
            if (siegeSel) siegeSel.onchange = function () { __cOnSegSiege(idx); };
            if (sorted.length === 1) {
                heureSel.selectedIndex = 1;
                heureSel.dispatchEvent(new Event('change'));
            }
            __cUpdateOkBtn();
        });
    }

    function __cOnSegCie(idx) {
        // Compagnie retirée : le choix se fait dans Heure.
        __cOnSegHeure(idx);
    }

    function __cOnSegHeure(idx) {
        var seg = window.__confirmState.segData[idx];
        var heureSel = __cQ('confirm_ui_heure_' + idx);
        var siegeSel = __cQ('confirm_ui_siege_' + idx);
        var cieSel = __cQ('confirm_ui_cie_' + idx);
        __cResetSelect(siegeSel, 'Choisissez le siège');
        __cSetVal('confirm_seg_prog_' + idx, '');
        __cSetVal('confirm_seg_siege_' + idx, '');
        if (!seg || !heureSel || !heureSel.value) {
            __cUpdateOkBtn();
            return;
        }
        var opt = heureSel.options[heureSel.selectedIndex];
        var row = (opt && opt._row) || null;
        if (!row && seg.rows) {
            for (var i = 0; i < seg.rows.length; i++) {
                if (seg.rows[i] && String(seg.rows[i].code_progr) === String(heureSel.value)) {
                    row = seg.rows[i];
                    break;
                }
            }
        }
        if (!row) {
            __cUpdateOkBtn();
            return;
        }
        var hh = (opt && opt.getAttribute('data-heure')) || __cHhmm(row.heure);
        var cie = (opt && opt.getAttribute('data-cie')) || row.id_compaga || __cCieKey(row) || '';
        if (cieSel && cie) {
            cieSel.value = cie;
            if (!cieSel.value) {
                // clé compagnie peut différer : tenter __cCieKey
                var k = __cCieKey(row);
                if (k) cieSel.value = k;
            }
        }
        __cSetVal('confirm_seg_prog_' + idx, row.code_progr || '');
        __cSetVal('confirm_seg_cat_' + idx, row.categori || (opt && opt.getAttribute('data-cat')) || '');
        __cSetVal('confirm_seg_heure_' + idx, hh);
        __cSetVal('confirm_seg_compaga_' + idx, row.id_compaga || cie);
        // Comme reprog : siegdisponibletrans (code + intervalles), sans date/ligne/heure.
        var i1 = row.intervalle1 != null && row.intervalle1 !== '' ? row.intervalle1 : '1';
        var i2 = row.intervalle2 != null && row.intervalle2 !== '' ? row.intervalle2 : '50';
        if (!row.code_progr) {
            __cUpdateOkBtn();
            return;
        }
        var url = window.location.origin + APP_ROOT
            + '/programmes/siegdisponibletrans/'
            + encodeURIComponent(row.code_progr) + '/'
            + encodeURIComponent(i1) + '/'
            + encodeURIComponent(i2);
        __cXhrGet(url, function (sieges) {
            var arr = __cRowsArray(sieges);
            arr.forEach(function (s) {
                var num = '';
                if (s && typeof s === 'object') {
                    num = s.siege_num != null ? s.siege_num
                        : (s.num_siege_categorie != null ? s.num_siege_categorie : '');
                } else {
                    num = s;
                }
                num = String(num).trim();
                if (!num || num === '0' || num === '00') return;
                var o = document.createElement('option');
                o.value = num;
                o.textContent = num;
                siegeSel.add(o);
            });
            __cUpdateOkBtn();
        });
    }

    function __cOnSegSiege(idx) {
        var siegeSel = __cQ('confirm_ui_siege_' + idx);
        __cSetVal('confirm_seg_siege_' + idx, (siegeSel && siegeSel.value) || '');
        __cUpdateOkBtn();
    }

    function __cOnItineraireChange() {
        var sel = __cQ('confirm_itineraire_select');
        var box = __cQ('confirm_segments_box');
        if (box) box.innerHTML = '';
        __cClearSegHiddens();
        if (!sel || sel.value === '') {
            __cUpdateOkBtn();
            return;
        }
        var ch = window.__confirmState.chemins[parseInt(sel.value, 10)];
        if (!ch) {
            __cUpdateOkBtn();
            return;
        }
        __cBuildSegments(__cNormalizeEtapes(ch.etapes || ch.legs));
        if (__cEscaleChecked()) {
            __cLoadEscales();
        }
    }

    function __cOnHeureChange() {
        var heureSel = __cQ('heure_confirm_unifie');
        var cieSel = __cQ('cie_confirm_unifie');
        var siegeSel = __cQ('siege_confirm_unifie');
        __cResetSelect(cieSel, 'Choisissez la compagnie');
        __cResetSelect(siegeSel, 'Choisissez le siège');
        __cSetVal('confirm_code_pro', '');
        __cUpdateOkBtn();
        if (!heureSel || !heureSel.value) return;
        var opt = heureSel.options[heureSel.selectedIndex];
        if (!opt) return;
        var row = opt._row || {};
        var codePro = opt.getAttribute('data-code-progr') || row.code_progr || heureSel.value || '';
        var i1 = opt.getAttribute('data-i1') || row.intervalle1 || '1';
        var i2 = opt.getAttribute('data-i2') || row.intervalle2 || '50';
        var cie = opt.getAttribute('data-cie') || row.id_compaga || '';
        // Remplir le select compagnie caché (submit / legacy).
        if (cieSel && codePro) {
            var o = document.createElement('option');
            o.value = '0';
            o.textContent = __cCieName(row) || 'Compagnie';
            o.setAttribute('data-code-progr', codePro);
            o.setAttribute('data-id-lh', opt.getAttribute('data-id-lh') || '');
            o.setAttribute('data-tarif', opt.getAttribute('data-tarif') || '');
            o.setAttribute('data-cat', opt.getAttribute('data-cat') || '');
            o.setAttribute('data-cie', cie);
            o.setAttribute('data-i1', i1);
            o.setAttribute('data-i2', i2);
            o._row = row;
            cieSel.add(o);
            cieSel.selectedIndex = 1;
        }
        __cSetVal('confirm_code_pro', codePro);
        __cSetVal('confirm_id_ligneheure', opt.getAttribute('data-id-lh') || row.id_ligneheure || '');
        __cSetVal('confirm_typetarif', opt.getAttribute('data-tarif') || row.typetarif || '');
        __cSetVal('confirm_categori', opt.getAttribute('data-cat') || row.categori || '');
        __cSetVal('confirm_id_compaga', cie);
        __cSetVal('confirm_intervalle1', i1);
        __cSetVal('confirm_intervalle2', i2);
        if (!codePro) return;
        var url = window.location.origin + APP_ROOT
            + '/programmes/siegdisponibletrans/'
            + encodeURIComponent(codePro) + '/'
            + encodeURIComponent(i1) + '/'
            + encodeURIComponent(i2);
        __cXhrGet(url, function (sieges) {
            __cRowsArray(sieges).forEach(function (s) {
                var num = '';
                if (s && typeof s === 'object') {
                    num = s.siege_num != null ? s.siege_num
                        : (s.num_siege_categorie != null ? s.num_siege_categorie : '');
                } else {
                    num = s;
                }
                num = String(num).trim();
                if (!num || num === '0' || num === '00') return;
                var o2 = document.createElement('option');
                o2.value = num;
                o2.textContent = num;
                siegeSel.add(o2);
            });
            __cUpdateOkBtn();
        });
    }

    function __cOnCieChange() {
        // Compagnie retirée : le programme est choisi dans Heure.
        __cUpdateOkBtn();
    }

    function __cOnSiegeChange() {
        __cUpdateOkBtn();
    }

    function __cTodayYmd() {
        var t = new Date();
        var mm = String(t.getMonth() + 1);
        if (mm.length === 1) mm = '0' + mm;
        var dd = String(t.getDate());
        if (dd.length === 1) dd = '0' + dd;
        return t.getFullYear() + '-' + mm + '-' + dd;
    }

    function __cShowDepartAndLoad() {
        __cQ('confirm_depart_wrap').style.display = 'block';
        var g2 = __cQ('confirm_gratis_hint');
        if (g2) g2.style.display = 'block';
        var dateEl = __cQ('date_confirm_unifie');
        if (dateEl && !dateEl.value) {
            dateEl.value = __cTodayYmd();
        }
        if (__cEscaleChecked()) {
            __cLoadEscales();
        }
        if (dateEl && dateEl.value) {
            __cLoadHeures(dateEl.value);
        }
    }

    function __cStartExterneForm(code) {
        __cHideErr();
        __cSetVal('confirm_mode_unifie', 'externe');
        __cSetVal('confirm_code_ticket', code || '');
        __cSetVal('confirm_client_id', '');
        __cSetVal('confirm_nom_ligne', '');
        __cSetVal('confirm_ident_ligne', '');
        __cSetVal('confirm_gaexp', (__cQ('confirm_gareconnect_code') || {}).value || '');
        __cSetVal('confirm_gadest', '');
        __cSetVal('confirm_depart_gid', (__cQ('confirm_gareconnect_code') || {}).value || '');
        var iw = __cQ('confirm_infos_wrap');
        if (iw) iw.style.display = 'none';
        var dw = __cQ('confirm_depart_wrap');
        if (dw) dw.style.display = 'none';
        var g = __cQ('confirm_gratis_hint');
        if (g) g.style.display = 'none';
        var ew = __cQ('confirm_externe_wrap');
        if (ew) ew.style.display = 'block';
        var odFields = __cQ('confirm_externe_od_fields');
        if (odFields) odFields.style.display = 'block';
        var odErr = __cQ('ext_od_err');
        if (odErr) {
            odErr.style.display = 'none';
            odErr.textContent = '';
        }
        var msg = __cQ('ext_client_msg');
        if (msg) msg.textContent = '';
        if (typeof window.__bindFiltreArriveeCompagnie === 'function') {
            window.__bindFiltreArriveeCompagnie(ew || document);
        }
        var tMsg = __cQ('confirm_transit_msg');
        if (tMsg) {
            tMsg.textContent = 'Aucun direct : choisissez un itinéraire depuis la gare de confirmation vers l’arrivée choisie.';
        }
    }

    function __cExterneOdContinue() {
        var odErr = __cQ('ext_od_err');
        function showOdErr(m) {
            if (!odErr) return;
            odErr.style.display = 'block';
            odErr.textContent = m || '';
        }
        if (odErr) {
            odErr.style.display = 'none';
            odErr.textContent = '';
        }
        var tel = String((__cQ('ext_tel_confirm') || {}).value || '').trim();
        var nom = String((__cQ('ext_nom_confirm') || {}).value || '').trim();
        var prenom = String((__cQ('ext_prenom_confirm') || {}).value || '').trim();
        var arrSel = __cQ('arrsgare_confirm');
        var gadest = String((arrSel && arrSel.value) || '').trim();
        if (gadest.indexOf('/') !== -1) {
            gadest = gadest.split('/')[0].trim();
        }
        var gaexp = String((__cQ('confirm_gareconnect_code') || {}).value || '').trim();
        if (!tel || !nom || !prenom) {
            showOdErr('Téléphone, nom et prénom obligatoires.');
            return;
        }
        if (!gaexp || !gadest) {
            showOdErr('Choisissez la gare d’arrivée.');
            return;
        }
        __cSetVal('confirm_code_ticket', String((__cQ('code_lookup_confirm') || {}).value || '').trim());
        __cXhrGet(
            window.location.origin + APP_ROOT
                + '/confirmation/od_externe?gaexp=' + encodeURIComponent(gaexp)
                + '&gadest=' + encodeURIComponent(gadest),
            function (od) {
                if (!od || !od.ok) {
                    showOdErr((od && od.reason) || 'Trajet introuvable.');
                    return;
                }
                window.__confirmState.ok = true;
                __cSetVal('confirm_mode_unifie', 'externe');
                __cSetVal('confirm_nom_ligne', od.nom_ligne || '');
                __cSetVal('confirm_ident_ligne', od.ident_ligne || '');
                __cSetVal('confirm_gaexp', od.gaexp || gaexp);
                __cSetVal('confirm_gadest', od.gadest || gadest);
                __cSetVal('confirm_depart_gid', od.gaexp || gaexp);

                __cQ('confirm_nom_cl').textContent = 'NOM: ' + nom;
                __cQ('confirm_prenom_cl').textContent = 'PRÉNOM: ' + prenom;
                __cQ('confirm_contact_cl').textContent = 'CONTACT: ' + tel;
                __cQ('confirm_direction_cl').textContent = 'DIRECTION: '
                    + (od.gaexp || gaexp) + ' → ' + (od.gadest || gadest);
                __cQ('confirm_ligne_cl').textContent = 'LIGNE: ' + (od.nom_ligne || '—');
                __cQ('confirm_code_cl').textContent = 'CODE EXTERNE: '
                    + ((__cQ('confirm_code_ticket') || {}).value || '—');
                __cQ('confirm_prix_info_cl').textContent = 'Confirmation externe : 0 F';
                __cQ('confirm_infos_wrap').style.display = 'grid';

                var odFields = __cQ('confirm_externe_od_fields');
                if (odFields) odFields.style.display = 'none';

                __cShowDepartAndLoad();
            }
        );
    }

    function __cApplyLookup(donnees) {
        __cHideErr();
        if (!donnees || donnees.ok === false) {
            if (donnees && donnees.error === 'need_codes') {
                __cShowExtraCodeFields(donnees);
                __cShowErr(donnees.reason || 'Saisissez les autres codes du ticket transit.');
                __cQ('confirm_infos_wrap').style.display = 'none';
                __cQ('confirm_depart_wrap').style.display = 'none';
                var gNeed = __cQ('confirm_gratis_hint');
                if (gNeed) gNeed.style.display = 'none';
                return;
            }
            __cHideExtraCodeFields();
            __cQ('confirm_infos_wrap').style.display = 'none';
            __cQ('confirm_depart_wrap').style.display = 'none';
            var g = __cQ('confirm_gratis_hint');
            if (g) g.style.display = 'none';
            var extCb = __cQ('mode_externe_confirm');
            var canExt = window.__confirmState.allowExterne
                || (donnees && donnees.allow_externe);
            if (extCb && extCb.checked && canExt && donnees && donnees.error === 'inconnu') {
                __cStartExterneForm((__cQ('code_lookup_confirm') || {}).value || '');
                return;
            }
            __cShowErr((donnees && donnees.reason) || 'Ticket introuvable.');
            var ewHide = __cQ('confirm_externe_wrap');
            if (ewHide) ewHide.style.display = 'none';
            return;
        }
        __cHideExtraCodeFields(true);
        window.__confirmState.ok = true;
        window.__confirmState.codes = Array.isArray(donnees.codes) && donnees.codes.length
            ? donnees.codes.slice()
            : [donnees.codeticket || ''];
        window.__confirmState.nbrJambes = parseInt(donnees.nbr_jambes, 10) || window.__confirmState.codes.length || 1;
        window.__confirmState.axes = Array.isArray(donnees.axes) ? donnees.axes.slice() : [];
        window.__confirmState.etapesRetour = Array.isArray(donnees.etapes_retour)
            ? donnees.etapes_retour.slice()
            : [];
        __cSetVal('confirm_mode_unifie', 'retour');
        __cSetVal('confirm_code_ticket', donnees.codeticket || '');
        __cSetVal('confirm_code_non_pass', donnees.code_non_pass || '');
        __cSetVal('confirm_codes_json', JSON.stringify(window.__confirmState.codes));
        __cSetVal('confirm_client_id', donnees.id_client || '');
        __cSetVal('confirm_nom_ligne', donnees.nom_ligne || '');
        __cSetVal('confirm_ident_ligne', donnees.ident_ligne || '');
        __cSetVal('confirm_gaexp', donnees.gaexp_lg || '');
        __cSetVal('confirm_gadest', donnees.gadest_lg || '');
        __cSetVal('confirm_depart_gid', (__cQ('confirm_gareconnect_code') || {}).value || '');

        __cQ('confirm_nom_cl').textContent = 'NOM: ' + (donnees.nom_client || '—');
        __cQ('confirm_prenom_cl').textContent = 'PRÉNOM: ' + (donnees.prenom_client || '—');
        __cQ('confirm_contact_cl').textContent = 'CONTACT: ' + (donnees.contact_client || '—');
        __cQ('confirm_direction_cl').textContent = 'DIRECTION: '
            + (donnees.gaexp_lg || '') + ' → ' + (donnees.gadest_lg || '');
        __cQ('confirm_ligne_cl').textContent = 'LIGNE RETOUR: ' + (donnees.nom_ligne || '—');
        var codesLbl = window.__confirmState.codes.filter(Boolean).join(' + ');
        __cQ('confirm_code_cl').textContent = 'CODE'
            + (window.__confirmState.nbrJambes > 1 ? 'S' : '') + ': '
            + (codesLbl || donnees.codeticket || '—');
        __cQ('confirm_prix_info_cl').textContent = 'Prix retour (déjà payé à l’aller): '
            + (donnees.prixretour != null ? donnees.prixretour : '—')
            + ' — confirmation: 0 F';

        __cQ('confirm_infos_wrap').style.display = 'grid';
        var ew2 = __cQ('confirm_externe_wrap');
        if (ew2) ew2.style.display = 'none';
        var tMsg = __cQ('confirm_transit_msg');
        if (tMsg) {
            tMsg.textContent = 'Aucun direct : choisissez un itinéraire depuis la gare de confirmation vers la destination du retour.';
        }
        var gOk = __cQ('confirm_gratis_hint');
        if (gOk) gOk.style.display = 'block';
        __cShowDepartAndLoad();
    }

    function __cShowExtraCodeFields(donnees) {
        var wrap = __cQ('confirm_extra_codes_wrap');
        var box = __cQ('confirm_extra_codes_box');
        var msg = __cQ('confirm_extra_codes_msg');
        if (!wrap || !box) return;
        var jambes = Array.isArray(donnees.jambes) ? donnees.jambes : [];
        var nbr = parseInt(donnees.nbr_jambes, 10) || jambes.length || 2;
        window.__confirmState.nbrJambes = nbr;
        if (msg) {
            msg.textContent = 'Ticket transit ' + nbr + ' jambes : vérifiez chaque code avec son bouton.';
        }
        // Conserver les valeurs déjà tapées avant rebuild.
        var prevVals = {};
        document.querySelectorAll('#confirm_extra_codes_box .confirm-extra-code').forEach(function (el) {
            var ord = el.getAttribute('data-ord');
            if (ord) prevVals[ord] = String(el.value || '').trim();
        });
        box.innerHTML = '';
        // Codes déjà validés (jambes saisies) en hidden.
        jambes.forEach(function (j) {
            if (!j || !j.saisi || !j.codeticket) return;
            var h = document.createElement('input');
            h.type = 'hidden';
            h.className = 'confirm-extra-known';
            h.setAttribute('data-ord', String(j.ord || ''));
            h.value = j.codeticket;
            box.appendChild(h);
            var okRow = document.createElement('p');
            okRow.className = 'small text-success mb-2';
            okRow.textContent = 'OK — ' + (j.ord || '') + 'ᵉ code : ' + j.codeticket
                + (j.nom_ligne ? ' (' + j.nom_ligne + ')' : '');
            box.appendChild(okRow);
        });
        jambes.forEach(function (j) {
            if (!j || j.saisi) return;
            var ord = j.ord || 2;
            var prev = prevVals[String(ord)] || '';
            var row = document.createElement('div');
            row.className = 'form-row align-items-end mb-2';
            row.id = 'confirm_extra_row_' + ord;
            row.innerHTML =
                '<div class="form-group col-md-6 mb-2">'
                + '<label class="small mb-0">' + ord + '<sup>e</sup> code'
                + (j.nom_ligne ? ' — ' + j.nom_ligne : '')
                + '</label>'
                + '<input class="form-control form-control-sm confirm-extra-code" type="text"'
                + ' id="code_lookup_confirm_' + ord + '"'
                + ' data-ord="' + ord + '" autocomplete="off"'
                + ' placeholder="Code de la ' + ord + 'ᵉ jambe"'
                + (prev ? (' value="' + String(prev).replace(/"/g, '&quot;') + '"') : '')
                + '>'
                + '</div>'
                + '<div class="form-group col-md-6 mb-2">'
                + '<span class="btn btn-outline-success btn-sm btn-block" type="button"'
                + ' id="confirm_infos_btn_' + ord + '">Vérifier le ' + ord + '<sup>e</sup> code</span>'
                + '<p class="small text-success mb-0 mt-1" id="confirm_leg_ok_' + ord
                + '" style="display:none">Vérifié</p>'
                + '</div>';
            box.appendChild(row);
            (function (legNum) {
                var btn = __cQ('confirm_infos_btn_' + legNum);
                if (btn) {
                    btn.onclick = function () {
                        __cVerifyExtraLeg(legNum);
                    };
                }
            })(ord);
        });
        wrap.style.display = 'block';
    }

    function __cHideExtraCodeFields(keepKnown) {
        var wrap = __cQ('confirm_extra_codes_wrap');
        var box = __cQ('confirm_extra_codes_box');
        if (wrap) wrap.style.display = 'none';
        if (box && !keepKnown) box.innerHTML = '';
        if (box && keepKnown) {
            box.innerHTML = '';
        }
    }

    function __cCollectLookupCodes() {
        var codes = [];
        var c1 = String((__cQ('code_lookup_confirm') || {}).value || '').trim();
        if (c1) codes.push(c1);
        document.querySelectorAll('#confirm_extra_codes_box .confirm-extra-known').forEach(function (el) {
            var v = String(el.value || '').trim();
            if (v) codes.push(v);
        });
        document.querySelectorAll('#confirm_extra_codes_box .confirm-extra-code').forEach(function (el) {
            var v = String(el.value || '').trim();
            if (v) codes.push(v);
        });
        var uniq = [];
        var seen = {};
        codes.forEach(function (c) {
            var k = c.toUpperCase();
            if (seen[k]) return;
            seen[k] = true;
            uniq.push(c);
        });
        return uniq;
    }

    function __cLookup() {
        var code = String((__cQ('code_lookup_confirm') || {}).value || '').trim();
        var gare = String((__cQ('confirm_gareconnect_code') || {}).value || '').trim();
        if (!code) {
            __cShowErr('Saisissez le 1er code ticket.');
            return;
        }
        // 1er bouton = uniquement le 1er code (les autres ont leur propre bouton).
        __cHideErr();
        __cHideExtraCodeFields();
        __cXhrGet(
            window.location.origin + APP_ROOT
                + '/confirmation/lookup_unifie?code=' + encodeURIComponent(code)
                + '&gare=' + encodeURIComponent(gare),
            __cApplyLookup
        );
    }

    function __cVerifyExtraLeg(legNum) {
        var code1 = String((__cQ('code_lookup_confirm') || {}).value || '').trim();
        if (!code1) {
            __cShowErr('Vérifiez d’abord le 1er code.');
            return;
        }
        var input = __cQ('code_lookup_confirm_' + legNum);
        var codeN = String((input && input.value) || '').trim();
        if (!codeN) {
            __cShowErr('Saisissez le ' + legNum + 'ᵉ code.');
            return;
        }
        if (codeN.toUpperCase() === code1.toUpperCase()) {
            __cShowErr('Le ' + legNum + 'ᵉ code doit être différent du 1er.');
            return;
        }
        // Codes déjà validés + celui qu’on vérifie maintenant.
        var codes = [code1];
        document.querySelectorAll('#confirm_extra_codes_box .confirm-extra-known').forEach(function (el) {
            var v = String(el.value || '').trim();
            if (v) codes.push(v);
        });
        codes.push(codeN);
        // Inclure les autres champs déjà remplis (jambes 3+) sans les exiger.
        document.querySelectorAll('#confirm_extra_codes_box .confirm-extra-code').forEach(function (el) {
            var ord = parseInt(el.getAttribute('data-ord'), 10) || 0;
            if (ord === legNum) return;
            var v = String(el.value || '').trim();
            if (v) codes.push(v);
        });
        var uniq = [];
        var seen = {};
        codes.forEach(function (c) {
            var k = c.toUpperCase();
            if (seen[k]) return;
            seen[k] = true;
            uniq.push(c);
        });
        var gare = String((__cQ('confirm_gareconnect_code') || {}).value || '').trim();
        __cHideErr();
        var qs = 'code=' + encodeURIComponent(uniq[0])
            + '&gare=' + encodeURIComponent(gare);
        for (var i = 1; i < uniq.length; i++) {
            qs += '&code' + (i + 1) + '=' + encodeURIComponent(uniq[i]);
        }
        qs += '&codes=' + encodeURIComponent(uniq.join(','));
        __cXhrGet(
            window.location.origin + APP_ROOT + '/confirmation/lookup_unifie?' + qs,
            function (donnees) {
                if (donnees && donnees.ok) {
                    var okEl = __cQ('confirm_leg_ok_' + legNum);
                    if (okEl) {
                        okEl.style.display = 'block';
                        okEl.textContent = 'OK — ' + codeN;
                    }
                }
                __cApplyLookup(donnees);
            }
        );
    }

    function __cSubmit() {
        var form = __cQ('cFormUnifie');
        var btn = __cQ('confirm_ok_btn');
        if (!form) return;
        if (window.__confirmState.pathMode === 'direct') {
            __cSetVal('confirm_nbr_seg', '1');
            __cSetVal('confirm_seg_prog_0', (__cQ('confirm_code_pro') || {}).value || '');
            __cSetVal('confirm_seg_siege_0', (__cQ('siege_confirm_unifie') || {}).value || '');
            __cSetVal('confirm_seg_cat_0', (__cQ('confirm_categori') || {}).value || '');
            __cSetVal('confirm_seg_compaga_0', (__cQ('confirm_id_compaga') || {}).value || '');
            __cSetVal('confirm_seg_date_0', (__cQ('date_confirm_unifie') || {}).value || '');
            var hOpt = (__cQ('heure_confirm_unifie') || {}).options;
            var hSel = __cQ('heure_confirm_unifie');
            var hhPost = '';
            if (hSel && hSel.selectedIndex > 0 && hOpt && hOpt[hSel.selectedIndex]) {
                hhPost = hOpt[hSel.selectedIndex].getAttribute('data-heure') || '';
            }
            if (!hhPost && hSel) hhPost = __cHhmm(hSel.value) || '';
            __cSetVal('confirm_seg_heure_0', hhPost);
            __cSetVal('confirm_seg_ligne_0', (__cQ('confirm_ident_ligne') || {}).value || '');
        }
        if (btn) btn.disabled = true;
        __cXhrPostForm(
            window.location.origin + APP_ROOT + '/confirmation/submit_unifie',
            form,
            function (res) {
                if (!res || !res.ok) {
                    __cShowErr((res && res.reason) || 'Échec de la confirmation.');
                    if (btn) btn.disabled = false;
                    return;
                }
                __cHideErr();
                __cQ('confirm_lookup_wrap').style.display = 'none';
                __cQ('confirm_depart_wrap').style.display = 'none';
                var ew = __cQ('confirm_externe_wrap');
                if (ew) ew.style.display = 'none';
                var isExt = res.mode === 'externe';
                var title = __cQ('confirm_result_title');
                if (title) {
                    title.textContent = isExt
                        ? 'Ticket externe — codes à conserver'
                        : 'À noter sur le ticket';
                }
                window.__confirmState.printUrl = res.print_url || '';
                var printBtn = __cQ('confirm_result_print_btn');
                if (printBtn) {
                    printBtn.style.display = (isExt && res.print_url) ? 'inline-block' : 'none';
                }
                // Code d’ailleurs : ouvrir immédiatement le ticket (format vente, 0 F).
                if (isExt && res.print_url) {
                    window.open(res.print_url, '_blank');
                }
                var body = __cQ('confirm_result_body');
                if (body) {
                    var html = '';
                    if (isExt) {
                        html += '<strong>Code externe (saisi) :</strong> <strong>'
                            + (res.code_externe || res.code_ticket || '') + '</strong><br>'
                            + '<strong>Nouveau code système :</strong> <strong>'
                            + (res.code_passager || '') + '</strong><br>';
                    } else {
                        html += '<strong>Notez sur le ticket :</strong><br>'
                            + 'Code ticket : <strong>' + (res.code_ticket || '') + '</strong><br>';
                    }
                    if (res.jambes && res.jambes.length > 1) {
                        html += 'Correspondance (' + res.jambes.length + ' jambes) :<br><ul class="mb-1 pl-3">';
                        res.jambes.forEach(function (j, i) {
                            html += '<li>Jambe ' + (i + 1) + ' — code <strong>' + (j.code_passager || '')
                                + '</strong> · ' + (j.nom_ligne || '') + ' · '
                                + (j.date_progr || '') + ' ' + __cHhmm(j.heure)
                                + ' · siège <strong>' + (j.num_siege || '') + '</strong>'
                                + (j.compagnie ? (' · ' + j.compagnie) : '')
                                + '</li>';
                        });
                        html += '</ul>';
                    } else if (!isExt || !(res.jambes && res.jambes.length > 1)) {
                        if (!isExt) {
                            html += 'Nouveau code passager : <strong>' + (res.code_passager || '') + '</strong><br>';
                        }
                        html += 'Ligne : ' + (res.nom_ligne || '') + ' (' + (res.od || '') + ')<br>'
                            + 'Compagnie : ' + (res.compagnie || '') + '<br>'
                            + 'Date : ' + (res.date_progr || '') + ' — Heure : ' + __cHhmm(res.heure) + '<br>'
                            + 'Siège : <strong>' + (res.num_siege || '') + '</strong><br>';
                    }
                    html += 'Prix confirmation : <strong>0 F</strong> <em>(non facturable)</em>';
                    body.innerHTML = html;
                }
                __cQ('confirm_result_wrap').style.display = 'block';
            }
        );
    }

    function __cCloseToHome() {
        var closeBtn = document.querySelector('#confirm-unifie-0 .modal-close');
        if (closeBtn) closeBtn.click();
        else window.location.reload();
    }

    function __cResetModal() {
        window.__confirmState.rows = [];
        window.__confirmState.chemins = [];
        window.__confirmState.segData = {};
        window.__confirmState.ok = false;
        window.__confirmState.printUrl = '';
        window.__confirmState.codes = [];
        window.__confirmState.nbrJambes = 1;
        window.__confirmState.axes = [];
        window.__confirmState.etapesRetour = [];
        window.__confirmState.loadSeq = 0;
        window.__confirmState.cheminsSeq = 0;
        __cSetVal('code_lookup_confirm', '');
        __cSetVal('confirm_codes_json', '');
        __cHideExtraCodeFields();
        __cSetVal('confirm_mode_unifie', 'retour');
        ['confirm_client_id', 'confirm_code_ticket', 'confirm_code_non_pass', 'confirm_nom_ligne',
            'confirm_ident_ligne', 'confirm_gaexp', 'confirm_gadest', 'confirm_code_pro'].forEach(function (id) {
            __cSetVal(id, '');
        });
        __cSetVal('ext_tel_confirm', '');
        __cSetVal('ext_nom_confirm', '');
        __cSetVal('ext_prenom_confirm', '');
        var arrSel = __cQ('arrsgare_confirm');
        if (arrSel) arrSel.value = '';
        var extMsg = __cQ('ext_client_msg');
        if (extMsg) extMsg.textContent = '';
        var odErr = __cQ('ext_od_err');
        if (odErr) {
            odErr.style.display = 'none';
            odErr.textContent = '';
        }
        var odFields = __cQ('confirm_externe_od_fields');
        if (odFields) odFields.style.display = 'block';
        __cHideErr();
        __cResetDepartUi();
        var escCheck = __cQ('confirm_escale_check');
        if (escCheck) escCheck.checked = false;
        var escFields = __cQ('confirm_escale_fields');
        if (escFields) escFields.style.display = 'none';
        __cClearEscale();
        var iw = __cQ('confirm_infos_wrap');
        if (iw) iw.style.display = 'none';
        var dw = __cQ('confirm_depart_wrap');
        if (dw) dw.style.display = 'none';
        var rw = __cQ('confirm_result_wrap');
        if (rw) rw.style.display = 'none';
        var printBtn = __cQ('confirm_result_print_btn');
        if (printBtn) printBtn.style.display = 'none';
        var lw = __cQ('confirm_lookup_wrap');
        if (lw) lw.style.display = 'block';
        var ew = __cQ('confirm_externe_wrap');
        if (ew) ew.style.display = 'none';
        var g = __cQ('confirm_gratis_hint');
        if (g) g.style.display = 'none';
        var extCb = __cQ('mode_externe_confirm');
        if (extCb) extCb.checked = false;
    }

    function __cBindExtClientLookup() {
        var tel = __cQ('ext_tel_confirm');
        if (!tel) return;
        tel.addEventListener('blur', function () {
            var raw = String(tel.value || '').trim();
            var msg = __cQ('ext_client_msg');
            if (!raw) return;
            __cXhrGet(
                window.location.origin + APP_ROOT + '/programmes/verifinfos/' + encodeURIComponent(raw),
                function (infos) {
                    if (infos && infos.id_client) {
                        __cSetVal('confirm_client_id', infos.id_client);
                        if (__cQ('ext_nom_confirm')) __cQ('ext_nom_confirm').value = infos.nom_client || '';
                        if (__cQ('ext_prenom_confirm')) __cQ('ext_prenom_confirm').value = infos.prenom_client || '';
                        if (msg) msg.textContent = 'Client trouvé — id ' + infos.id_client;
                    } else {
                        __cSetVal('confirm_client_id', '');
                        if (msg) msg.textContent = 'Nouveau client : sera créé à la validation.';
                    }
                }
            );
        });
    }

    document.querySelectorAll('.addconfirm_unifie').forEach(function (btn) {
        btn.addEventListener('click', function () {
            __cResetModal();
            var modal = __cQ('confirm-unifie-0');
            if (modal) {
                window.__confirmState.allowExterne = modal.getAttribute('data-allow-externe') === '1';
            }
        });
    });

    var infosBtn = __cQ('confirm_infos_btn');
    if (infosBtn) infosBtn.addEventListener('click', __cLookup);

    var dateEl = __cQ('date_confirm_unifie');
    if (dateEl) {
        var onDatePick = function () {
            var v = __cNormDate(dateEl.value);
            if (v) __cLoadHeures(v);
        };
        dateEl.addEventListener('change', onDatePick);
        dateEl.addEventListener('input', onDatePick);
    }
    var confirmAllowMultiEl = __cQ('confirm_allow_multi');
    if (confirmAllowMultiEl && !confirmAllowMultiEl.dataset.bound) {
        confirmAllowMultiEl.dataset.bound = '1';
        confirmAllowMultiEl.addEventListener('change', function () {
            var v = __cNormDate((__cQ('date_confirm_unifie') || {}).value || '');
            if (v) __cOnDateReady(v);
        });
    }
    var heureEl = __cQ('heure_confirm_unifie');
    if (heureEl) heureEl.addEventListener('change', __cOnHeureChange);
    var cieEl = __cQ('cie_confirm_unifie');
    if (cieEl) cieEl.addEventListener('change', __cOnCieChange);
    var siegeEl = __cQ('siege_confirm_unifie');
    if (siegeEl) siegeEl.addEventListener('change', __cOnSiegeChange);
    var itinEl = __cQ('confirm_itineraire_select');
    if (itinEl) itinEl.addEventListener('change', __cOnItineraireChange);
    var escCheck = __cQ('confirm_escale_check');
    if (escCheck) escCheck.addEventListener('change', __cOnEscaleCheckChange);
    var escSel = __cQ('confirm_escale_select');
    if (escSel) escSel.addEventListener('change', __cOnEscaleSelectChange);
    var okBtn = __cQ('confirm_ok_btn');
    if (okBtn) okBtn.addEventListener('click', __cSubmit);
    var resOk = __cQ('confirm_result_ok_btn');
    if (resOk) resOk.addEventListener('click', __cCloseToHome);
    var resPrint = __cQ('confirm_result_print_btn');
    if (resPrint) {
        resPrint.addEventListener('click', function () {
            var url = window.__confirmState.printUrl;
            if (url) window.open(url, '_blank');
        });
    }
    var extOdBtn = __cQ('confirm_externe_od_btn');
    if (extOdBtn) extOdBtn.addEventListener('click', __cExterneOdContinue);

    __cBindExtClientLookup();
})();

;
/* --- addreserve.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addreserve').forEach(function (e) 
    {
        document.querySelector('h3#reTitle').innerHTML = `RESERVATION`;
            
            let da = document.querySelector('#axereserve');
            if (da !== null){
                da.onchange = () => {
                
                document.querySelector('#heuredepart').options.length = 1;
                document.querySelector('#passgsieges').options.length = 1;
                document.querySelector('#tarifattribtime').value = '';
                let httpRequetes;
                
                if (window.XMLHttpRequest) {
                    httpRequetes = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpRequetes = new ActiveXObject("Microsoft.XMLHTTP");
                }
                
                var ax = document.querySelector('#axereserve').value;
                 var datedepart = document.querySelector('#actueldate').value;
                   
                            let httpRequetesq = new XMLHttpRequest();
                            httpRequetesq.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfquart/${ax}`, true);
                            httpRequetesq.onload = () => {
                            const qdata = JSON.parse(httpRequetesq.responseText);
                            if(qdata == ''){
                                document.querySelector('#quartreser').options.length = 1;
                            }else{
                                if (Object.entries(qdata).length >= 1) {
                                            
                                    for (let key in Object.entries(qdata)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${qdata[key].nom_quartier}`;
                                        opt.innerHTML = `${qdata[key].nom_quartier}`;
                                        document.querySelector('#quartreser').add(opt);
                                    }
                                } else {
                                    document.querySelector('#quartreser').options.length = 1;
                                }
                            }
                                
                                    
                            };
                            httpRequetesq.setRequestHeader('Content-Type', 'application/json');
                            httpRequetesq.send();
                        httpRequetes.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfprog/${ax}/${datedepart}`, true);
                        httpRequetes.onload = () => {
                            const dataAxe = JSON.parse(httpRequetes.responseText);
                            
                                
                                    if (Object.entries(dataAxe).length >= 1) {
                                            
                                            for (let key in Object.entries(dataAxe)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${dataAxe[key].code_progr}`;
                                                opt.innerHTML = `${dataAxe[key].heure}/${dataAxe[key].date_progr}`;
                                                document.querySelector('#heuredepart').add(opt);
                                            }
                                        } else {
                                            document.querySelector('#heuredepart').options.length = 1;
                                        }
                            };
                            httpRequetes.setRequestHeader('Content-Type', 'application/json');
                            httpRequetes.send();
                    
                };
                
            }
            let hrdepart = document.querySelector('#heuredepart');
            if (hrdepart !== null) {
                hrdepart.onchange = () => {
                    document.querySelector('#passgsieges').options.length = 1;
                    const httpRequest = new XMLHttpRequest();
                    const sel = document.querySelector('#heuredepart')
                        .options[document.querySelector('#heuredepart').options.selectedIndex].value;
                    httpRequest.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/siegdispo/${sel}`, true);
                    httpRequest.onload = () => {
                        const don = JSON.parse(httpRequest.responseText);
                        console.debug(`${typeof don} - ${don.attributes}`, console.memory);
                        if (Object.entries(don).length > 0) {
                            for (let key in Object.entries(don)) {
                                document.querySelector('#pfinvendable').value = `${don[key].intervalle2}`;
                                document.querySelector('#siegfinvendable').value = `${don[key].intervalle1}`;
                                document.querySelector('#reservetime').value = `${don[key].code_progr}`;
                                document.querySelector('#tarifattribtime').value = `${don[key].typetarif}`;
                                document.querySelector('#timeaxeid').value = `${don[key].ident_ligne}`;
                                document.querySelector('#directreserve').value = `${don[key].nom_ligne}`;
                                document.querySelector('#reserveheure').value = `${don[key].heure}`;
                                document.querySelector('#gareid_reserve').value = `${don[key].gaexp_lg}`;
                                document.querySelector('#datereserve').value = `${don[key].date_progr}`;
                                document.querySelector('#lhreserve').value = `${don[key].id_heur}`;
                                document.querySelector('#categbus').value=`${don[key].categori}`;

                                console.debug(`${don[key].intervalle1} - ${don[key].intervalle2}`, console.memory)
                                
                            }
                        }

                        const httpPrixres = new XMLHttpRequest();
                        const selh = document.querySelector('#lhreserve').value;
                        const selhtfb = document.querySelector('#tarifattribtime').value;
                        
                        httpPrixres.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifpriprg/${selh}/${selhtfb}`, true);
                        httpPrixres.onload = () => 
                        {
                            const donpr = JSON.parse(httpPrixres.responseText);
                            console.debug(`${typeof donpr}-${donpr.attributes}`, console.memory);
                            if (Object.entries(donpr).length >= 1) {
                                for (let key in Object.entries(donpr)) {
                                    document.querySelector('#prixtick').value = `${donpr[key].prix}`;

                                }
                            }
                        };
                        httpPrixres.setRequestHeader('Content-Type', 'application/json');
                        httpPrixres.send();

                        const httpRequestbis = new XMLHttpRequest();
    
                        const lp = document.querySelector('#pfinvendable').value;
                        const dbpl = document.querySelector('#siegfinvendable').value;
                        const direc = document.querySelector('#directreserve').value;
                        const he = document.querySelector('#reserveheure').value;
                        const datres = document.querySelector('#datereserve').value;
    
                        httpRequestbis.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${sel}/${datres}/${direc}/${he}/${dbpl}/${lp}`, true);
                        httpRequestbis.onload = () => {
                            const donbis = JSON.parse(httpRequestbis.responseText);
                            console.debug(`${typeof donbis} - ${donbis.attributes}`, console.memory);
                            if (Object.entries(donbis).length >= 1) {
                                for (let key in Object.entries(donbis)) {
                                    
                                    let opt = document.createElement('option');
                                    opt.value = `${donbis[key].siege_num}`;
                                    opt.innerHTML = `${donbis[key].siege_num}`;
                                    document.querySelector('#passgsieges').add(opt);
                            
                                }
                                
                            } else {
                                document.querySelector('#passgsieges').options.length = 1;
                            }
                            
                        };
                        httpRequestbis.setRequestHeader('Content-Type', 'application/json');
                        httpRequestbis.send();
                          
                    };
                    httpRequest.setRequestHeader('Content-Type', 'application/json');
                    httpRequest.send();
                };
           
            }

            let depsiegreserve = document.querySelector('#passgsieges');
            if (depsiegreserve !== null)
            depsiegreserve.onchange = () => {
                    
                    let Requestsiegereserve;
                    
                    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                        Requestsiegereserve = new XMLHttpRequest();
                    } else if (window.ActiveXObject) { // IE 6 and older
                        Requestsiegereserve = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                    const dp_progreserv = document.querySelector('#reservetime').value;
                    const dp_siegereserv = document.querySelector('#passgsieges').options[document.querySelector('#passgsieges').options.selectedIndex].value;
                                       
                    Requestsiegereserve.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${dp_progreserv}/${dp_siegereserv}`, true);
                    Requestsiegereserve.onload = () => 
                    {
                        
                            const reservdonsieg = JSON.parse(Requestsiegereserve.responseText);
                            if (reservdonsieg == '')
                                    {
                                        let httpSiegsreserv;
                                        httpSiegsreserv = new XMLHttpRequest();
                                        const dp_progconf = document.querySelector('#reservetime').value;
                                        const dp_siegeconf = document.querySelector('#passgsieges').options[document.querySelector('#passgsieges').options.selectedIndex].value;
                                        httpSiegsreserv.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${dp_progconf}/${dp_siegeconf}`, true);
                                        httpSiegsreserv.onload = () => 
                                        {
                                            const dongreserv= JSON.parse(httpSiegsreserv.responseText);
                                            document.querySelector('#messreserv').style.display = 'none';
                                            if (Object.entries(dongreserv).length >= 1)
                                            {
                                                for (let key in Object.entries(dongreserv)) {
                                                    document.querySelector('#idtamporeserve').value = `${dongreserv[key].idtamp}`;                    
                                                    document.querySelector('#siegselectreserve').value = `${dongreserv[key].numsieg}`;
                                                }
                                            }
                                        
                                        };
                                        httpSiegsreserv.setRequestHeader('Content-Type', 'application/json');
                                        httpSiegsreserv.send();
                                    }
                                    else {
                                        document.querySelector('#passgsieges').value = '';     
                                        if (Object.entries(reservdonsieg).length >= 1)
                                        {
                                            for (let key in Object.entries(reservdonsieg)) {
                                                document.querySelector('#idtamporeserve').value = `${reservdonsieg[key].idtamp}`;                    
                                                document.querySelector('#siegselectreserve').value = `${reservdonsieg[key].numsieg}`;
                                            }

                                        }
                                        document.querySelector('#messreserv').style.display = 'block';
                                        document.querySelector('#erreurMessreserv').innerHTML = `Siege déjà utilisé.`; 
                                    }
                    };
                    Requestsiegereserve.setRequestHeader('content-Type', 'text/json');
                    Requestsiegereserve.send();
                };
//bouton annuler
                butonclireserv = document.querySelector('#idreserv');
                if (butonclireserv !== null) {
                    butonclireserv.onclick = () => 
                    {
                        let httpSiegeselectreserve;
                        httpSiegeselectreserve = new XMLHttpRequest();
                        const siegselectres = document.querySelector('#siegselectreserve').value;
                        const idtapres = document.querySelector('#idtamporeserve').value;
                        httpSiegeselectreserve.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtapres}/${siegselectres}`, true);
                        httpSiegeselectreserve.onload = () => 
                        {
                            const donselectconf = JSON.parse(httpSiegeselectreserve.responseText);
                            console.debug(`${typeof donselectconf} - ${donselectconf.attributes}`, console.memory);
                            document.querySelector('#messreserv').style.display = 'none';
                            
                        };
                        httpSiegeselectreserve.setRequestHeader('Content-Type', 'application/json');
                        httpSiegeselectreserve.send();
    
                    
                    };
                }
    
            let inform = document.querySelector('#idcontactcl');
            if (inform !== null)
                inform.onkeyup = () => {
                    let httpInfosre;
                    if (window.XMLHttpRequest) {
                        httpInfosre = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfosre = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var verificatre = document.querySelector('#idcontactcl').value;
                    httpInfosre.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifinfos/${verificatre}`, true);
                    httpInfosre.onload = () => {
                        const infosre = JSON.parse(httpInfosre.responseText);
                        if (infosre == null) {
                            document.querySelector('#idnomcl').value = "";
                            document.querySelector('#idprenomcl').value = "";
                            document.querySelector('#idclientcomp').value = "";
                        } else {
                            if (Object.entries(infosre).length > 1) {
                                
                                if (infosre.contact_client == verificatre) {
                                    document.querySelector('#idnomcl').value = `${infosre.nom_client}`;
                                    document.querySelector('#idprenomcl').value = `${infosre.prenom_client}`;
                                    document.querySelector('#idclientcomp').value = `${infosre.id_client}`;
                                    document.querySelector('#cpidnomcl').value = `${infosre.nom_client}`;
                                    document.querySelector('#cpidprenomcl').value = `${infosre.prenom_client}`;
                                } else {
                                    document.querySelector('#idnomcl').value = "";
                                    document.querySelector('#idprenomcl').value = "";
                                    document.querySelector('#idclientcomp').value = "";
                                }
                            }
                        }
                    };
                    httpInfosre.setRequestHeader('Content-Type', 'application/json');
                    httpInfosre.send();
                };
            e.onclick = function () {   
                let reForm = document.querySelector('#reForm');

                reForm.setAttribute('action', `${APP_ROOT}/Reserves/addreserve/${e.dataset.cle_compagnie}`);   
            }
        
    })

});
;
/* --- addconfirmreserve.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addconfirmreserve').forEach(function (e) {
        e.onclick = function () 
        {
                let confForm = document.querySelector('#confForm');
            
                confForm.setAttribute('action', `${APP_ROOT}/Reserves/valideconfirm/${e.dataset.cle_compagnie}/${e.dataset.id_client}/${e.dataset.code_pass}/${e.dataset.gareident}/${e.dataset.code_p}/${e.dataset.cdlignh}/${e.dataset.tfb}`);
            
                document.querySelector('h3#reconfTitle').innerHTML = `CONFIRMER RESERVATION AVEC TICKET ${e.dataset.rnom}`;

                $('#ridcontact').val(`${e.dataset.contac}`);
                $('#ridnom').val(`${e.dataset.rnom}`);
                $('#ridprenom').val(`${e.dataset.pren}`);
                $('#ridcontact').val(`${e.dataset.contac}`);
                $('#numsieg').val(`${e.dataset.numsie}`);
                $('#lges').val(`${e.dataset.lge}`);
                $('#nomlg').val(`${e.dataset.nomlge}`);
                $('#catbuslg').val(`${e.dataset.catbuslge}`);
                $('#idcnibcf').val(`${e.dataset.num_CNIB}`);
                $('#dateidcf').val(`${e.dataset.date_delivre}`);
                $('#lieucf').val(`${e.dataset.lieu_delivre}`);
                let cfcod = document.querySelector('#confirme_infos');
                if (cfcod !== null)
                cfcod.onclick = () => {
                    
                    //verification code de confirmation
                    let confRequest;
                    
                    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                        confRequest = new XMLHttpRequest();
                    } else if (window.ActiveXObject) { // IE 6 and older
                        confRequest = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                    var cfconfir = document.querySelector("#confirmcode").value;

                    confRequest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verificationcode/${cfconfir}`, true);
                    confRequest.onload = () => {
                        const cfdata = JSON.parse(confRequest.responseText);
                        if (cfdata == null) {
                                document.querySelector('#boutonsubmit').style.display = 'block';
                                document.querySelector('#messageconf').style.display = 'none';
                                document.querySelector('#epsonsubmit').style.display = 'block';
                                

                        } else {
                            if (Object.entries(cfdata).length > 1) {
                                
                                document.querySelector('#messageconf').style.display = 'block';
                                document.querySelector('#erreurMessageconf').innerHTML = `Cet ticket ne peut pas être confirmé.`;
                                document.querySelector('#boutonsubmit').style.display = 'none';
                                document.querySelector('#epsonsubmit').style.display = 'none';
                                
                            }
                            
                        }
                    };
                    confRequest.setRequestHeader('Content-Type', 'application/json');
                    confRequest.send();
                };

        }
    })
});
;
/* --- addventeticketfi.js --- */
document.addEventListener('DOMContentLoaded', () => {

    /** Autre vente FI : prix saisis à la main (0 = ticket gratuit), jamais écrasés par le tarif programme. */
    window.__venteFiPrixManuel = true;

    /** OD jambe (gaexp/gadest métier) — même logique que addventeticket.js. */
    function __venteFiOdFromEtapeOrCode(etape, codeFallback) {
        if (typeof window.__venteOdFromEtapeOrCode === 'function') {
            return window.__venteOdFromEtapeOrCode(etape, codeFallback);
        }
        var ga = '';
        var gd = '';
        if (etape) {
            ga = String(etape.gaexp_lg || etape.code_gaexp || etape.gaexp || '').trim();
            gd = String(etape.gadest_lg || etape.code_gadest || etape.gadest || '').trim();
        }
        var code = String(codeFallback || '').trim();
        if ((!ga || !gd) && code) {
            var i = code.indexOf('-');
            if (i > 0) {
                if (!ga) ga = code.slice(0, i).trim();
                if (!gd) gd = code.slice(i + 1).trim();
            }
        }
        return { gaexp: ga, gadest: gd };
    }
    function __venteFiEtapeAt(idx) {
        var et = window.__venteFiCheminEtapes || window.__venteCheminEtapes;
        if (!et || !et.length) return null;
        return et[idx] || null;
    }

    /**
     * Helpers jambe 1 si addventeticket.js absent (rôles FI seuls).
     * Filtre J ≥ ancre + présélection par HH:MM.
     */
    (function __venteFiEnsureHeureItineHelpers() {
        if (typeof window.__venteFillHeureItineSelect === 'function'
            && typeof window.__venteSelectHourInSelect === 'function') {
            return;
        }
        function _min(h) {
            if (h == null || h === '') return null;
            var parts = String(h).trim().split(/[:hH]/);
            if (!parts || !parts.length) return null;
            var hh = parseInt(parts[0], 10);
            if (isNaN(hh)) return null;
            var mm = (parts[1] != null && parts[1] !== '') ? parseInt(parts[1], 10) : 0;
            if (isNaN(mm)) mm = 0;
            return (hh * 60) + mm;
        }
        function _hhmm(h) {
            var m = _min(h);
            if (m == null || m < 0) return '';
            var hh = Math.floor(m / 60) % 24;
            var mm = m % 60;
            return (hh < 10 ? '0' : '') + hh + ':' + (mm < 10 ? '0' : '') + mm;
        }
        function _fromPre(pre) {
            if (!pre) return '';
            if (pre.heure) return String(pre.heure);
            if (pre.value) {
                var p = String(pre.value).split('/');
                if (p.length >= 2) return p[1] || '';
            }
            return '';
        }
        function _fmtDate(ymd) {
            if (!ymd || String(ymd).length < 10) return '';
            var p = String(ymd).slice(0, 10).split('-');
            return (p.length === 3) ? (p[2] + '/' + p[1]) : String(ymd).slice(0, 10);
        }
        function _multiDays(rows) {
            var seen = {}, n = 0;
            for (var i = 0; i < rows.length; i++) {
                var d = rows[i] && rows[i].date_progr ? String(rows[i].date_progr).slice(0, 10) : '';
                if (!d || seen[d]) continue;
                seen[d] = 1;
                n++;
                if (n > 1) return true;
            }
            return false;
        }
        function _label(heure, dateProgr, voyageDate, forceDate) {
            var label = String(heure || '');
            var dprog = dateProgr ? String(dateProgr).slice(0, 10) : '';
            var vDate = voyageDate ? String(voyageDate).slice(0, 10) : '';
            if ((!!forceDate || (dprog && vDate && dprog !== vDate)) && dprog) {
                var short = _fmtDate(dprog);
                if (short) label = label + ' — ' + short;
            }
            return label;
        }
        function _select(selectEl, hour, preferDate) {
            if (!hour) return;
            var sel = typeof selectEl === 'string' ? document.querySelector(selectEl) : selectEl;
            if (!sel || !sel.options) return;
            if (!preferDate) {
                var dateEl = document.querySelector('#date_depheurefid') || document.querySelector('#date_depheure');
                preferDate = dateEl ? String(dateEl.value || '').slice(0, 10) : '';
            } else {
                preferDate = String(preferDate).slice(0, 10);
            }
            var targetRaw = _fromPre(hour);
            var targetHhmm = _hhmm(targetRaw);
            var targetMin = _min(targetRaw);
            var exactIdx = -1, sameDayHhmmIdx = -1, anyHhmmIdx = -1, sameDayGeIdx = -1;
            for (var i = 0; i < sel.options.length; i++) {
                var opt = sel.options[i];
                if (!opt) continue;
                if (i === 0 && (!opt.value || opt.value === '')) continue;
                if (hour.value && opt.value === hour.value) { exactIdx = i; break; }
                var optH = opt.getAttribute('data-heure') || (String(opt.value).split('/')[1] || '');
                var optHhmm = _hhmm(optH);
                var optDate = opt.getAttribute('data-date-progr') ? String(opt.getAttribute('data-date-progr')).slice(0, 10) : '';
                var optMin = _min(optH);
                if (targetHhmm && optHhmm === targetHhmm) {
                    if (preferDate && optDate === preferDate) sameDayHhmmIdx = i;
                    else if (anyHhmmIdx < 0) anyHhmmIdx = i;
                }
                if (sameDayGeIdx < 0 && preferDate && optDate === preferDate && targetMin != null && optMin != null && optMin >= targetMin) {
                    sameDayGeIdx = i;
                }
            }
            var pick = exactIdx >= 0 ? exactIdx
                : (sameDayHhmmIdx >= 0 ? sameDayHhmmIdx
                    : (anyHhmmIdx >= 0 ? anyHhmmIdx : sameDayGeIdx));
            if (pick >= 0) sel.selectedIndex = pick;
        }
        function _fill(selectEl, rows, preselectHour) {
            var sel = typeof selectEl === 'string' ? document.querySelector(selectEl) : selectEl;
            if (!sel) return;
            sel.options.length = 1;
            if (!rows) return;
            var list = Array.isArray(rows) ? rows
                : (typeof rows === 'object' ? Object.keys(rows).map(function (k) { return rows[k]; }) : []);
            var dateEl = document.querySelector('#date_depheurefid') || document.querySelector('#date_depheure');
            var voyageDate = dateEl ? String(dateEl.value || '').slice(0, 10) : '';
            var anchorRaw = _fromPre(preselectHour);
            var bySlot = {}, order = [];
            for (var i = 0; i < list.length; i++) {
                var row = list[i];
                if (!row || row.id_ligneheure == null || row.heure == null || row.heure === '') continue;
                var dprog = row.date_progr ? String(row.date_progr).slice(0, 10) : '';
                var slot = dprog + '|' + String(row.heure).trim();
                if (!bySlot[slot]) { bySlot[slot] = row; order.push(slot); }
            }
            order.sort(function (a, b) {
                var ra = bySlot[a], rb = bySlot[b];
                var da = String(ra.date_progr || '').slice(0, 10);
                var db = String(rb.date_progr || '').slice(0, 10);
                if (da < db) return -1;
                if (da > db) return 1;
                return (_min(ra.heure) || 0) - (_min(rb.heure) || 0);
            });
            var slotRows = order.map(function (k) { return bySlot[k]; });
            var forceDate = _multiDays(slotRows);
            for (var j = 0; j < order.length; j++) {
                var r = bySlot[order[j]];
                var opt = document.createElement('option');
                var dprogOpt = r.date_progr ? String(r.date_progr).slice(0, 10) : '';
                opt.value = String(r.id_ligneheure) + '/' + String(r.heure);
                if (dprogOpt) opt.setAttribute('data-date-progr', dprogOpt);
                opt.setAttribute('data-heure', String(r.heure || ''));
                opt.innerHTML = _label(r.heure, r.date_progr, voyageDate, forceDate);
                sel.add(opt);
            }
            if (preselectHour && (preselectHour.value || preselectHour.heure || anchorRaw)) {
                try { _select(sel, preselectHour, voyageDate); } catch (e1) {}
                if (sel.selectedIndex > 0 && typeof sel.onchange === 'function') {
                    try { sel.onchange(); } catch (e2) {}
                }
            }
        }
        if (typeof window.__venteSelectHourInSelect !== 'function') {
            window.__venteSelectHourInSelect = _select;
        }
        if (typeof window.__venteFillHeureItineSelect !== 'function') {
            window.__venteFillHeureItineSelect = _fill;
        }
        if (typeof window.__venteNormalizeHhmm !== 'function') {
            window.__venteNormalizeHhmm = _hhmm;
        }
    })();

    function __venteFiShouldSkipAutoPrix() {
        return window.__venteFiPrixManuel !== false;
    }

    function __venteFiClearTransitPrixFields() {
        ['prix_axetransfid', 'prix_axetransitfid', 'prix_axetransit1fid', 'prix_axetransit2fid'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.value = '';
        });
    }

    function __venteFiValidateTransitPrixBeforeSubmit() {
        if (!__venteFiShouldSkipAutoPrix()) return true;
        var tran = document.getElementById('tranfid');
        if (!tran || tran.style.display === 'none') return true;
        var checks = [
            { id: 'prix_axetransfid', label: 'Correspondance 1' },
            { id: 'prix_axetransitfid', label: 'Correspondance 2' },
            { id: 'prix_axetransit1fid', label: 'Correspondance 3' },
            { id: 'prix_axetransit2fid', label: 'Correspondance 4' }
        ];
        for (var i = 0; i < checks.length; i++) {
            var px = document.getElementById(checks[i].id);
            if (!px || px.style.display === 'none') continue;
            if (String(px.value).trim() === '') {
                var mess = document.querySelector('#messfid');
                var err = document.querySelector('#erreurMessfid');
                if (mess) mess.style.display = 'block';
                if (err) err.innerHTML = 'Saisissez le prix pour ' + checks[i].label + ' (0 = gratuit).';
                px.focus();
                return false;
            }
        }
        return true;
    }
    
    function __venteFiProgListFromResponse(don) {
        if (don == null || don === '') return [];
        if (Array.isArray(don)) return don.filter(Boolean);
        if (typeof don === 'object') {
            return Object.keys(don).map(function (k) { return don[k]; }).filter(Boolean);
        }
        return [];
    }

    function __venteFiHideProgSelect() {
        var box = document.getElementById('selprog_box_fid');
        var sel = document.getElementById('selprogfid');
        if (box) box.style.display = 'none';
        if (sel) {
            sel.options.length = 1;
            sel.value = '';
            sel.onchange = null;
        }
    }

    function __venteFiLabelProg(p) {
        if (!p) return '';
        var parts = [];
        if (p.code_progr) parts.push(String(p.code_progr));
        if (p.depart_code) parts.push(String(p.depart_code));
        if (p.categori) parts.push(String(p.categori));
        if (p.intervalle1 != null && p.intervalle2 != null) {
            parts.push('s.' + p.intervalle1 + '-' + p.intervalle2);
        }
        return parts.join(' · ');
    }

    function __venteFiApplyProgFields(p) {
        if (!p) return;
        var set = function (id, val) {
            var el = document.querySelector(id);
            if (el) el.value = val == null ? '' : String(val);
        };
        set('#programfid', p.code_progr);
        set('#dateprfid', p.date_progr);
        set('#deplignefid', p.gareidentif);
        set('#inter1fid', p.intervalle1);
        set('#inter2fid', p.intervalle2);
        set('#lignfid', p.ident_ligne);
        set('#nomitinfid', p.nom_ligne);
        set('#herfid', p.heure);
        set('#catefid', p.categori);
    }

    function __venteFiLoadSieges(dptDate) {
        var ps = document.querySelector('#psiegesfid');
        if (ps) ps.options.length = 1;
        var cdprog = document.querySelector('#programfid') ? document.querySelector('#programfid').value : '';
        var db = document.querySelector('#inter1fid') ? document.querySelector('#inter1fid').value : '';
        var fn = document.querySelector('#inter2fid') ? document.querySelector('#inter2fid').value : '';
        var lg = document.querySelector('#nomitinfid') ? document.querySelector('#nomitinfid').value : '';
        var tim = document.querySelector('#herfid') ? document.querySelector('#herfid').value : '';
        if (!cdprog) return;
        var httpRequettefi = new XMLHttpRequest();
        httpRequettefi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${cdprog}/${dptDate}/${lg}/${tim}/${db}/${fn}`, true);
        httpRequettefi.onload = function () {
            try {
                var dattafi = JSON.parse(httpRequettefi.responseText);
                if (ps) ps.options.length = 1;
                if (Object.entries(dattafi).length >= 1) {
                    for (var key in Object.entries(dattafi)) {
                        var opt = document.createElement('option');
                        opt.value = `${dattafi[key].siege_num}`;
                        opt.innerHTML = `${dattafi[key].siege_num}`;
                        if (ps) ps.add(opt);
                    }
                }
            } catch (e) {
                if (ps) ps.options.length = 1;
            }
        };
        httpRequettefi.setRequestHeader('Content-Type', 'application/json');
        httpRequettefi.send();
    }

    function __venteFiHandleProgList(don, dptDate, preferCode) {
        var list = __venteFiProgListFromResponse(don);
        __venteFiHideProgSelect();
        var ps = document.querySelector('#psiegesfid');
        if (ps) ps.options.length = 1;
        if (list.length === 0) return false;
        var pick = list[0];
        if (preferCode) {
            var want = String(preferCode);
            for (var i = 0; i < list.length; i++) {
                if (list[i] && String(list[i].code_progr || '') === want) {
                    pick = list[i];
                    break;
                }
            }
        }
        __venteFiApplyProgFields(pick);
        __venteFiLoadSieges(dptDate);
        return true;
    }

    function __venteFiHideProgSelectAny(boxId, selId) {
        var box = document.getElementById(boxId);
        var sel = document.getElementById(selId);
        if (box) box.style.display = 'none';
        if (sel) {
            sel.options.length = 1;
            sel.value = '';
            sel.onchange = null;
            sel.style.display = '';
        }
    }

    function __venteFiLabelProg(p) {
        if (!p) return '';
        var parts = [];
        if (p.code_progr) parts.push(String(p.code_progr));
        if (p.depart_code) parts.push(String(p.depart_code));
        if (p.categori) parts.push(String(p.categori));
        if (p.intervalle1 != null && p.intervalle2 != null) {
            parts.push('s.' + p.intervalle1 + '-' + p.intervalle2);
        }
        return parts.join(' · ');
    }

    var __venteFiCheminLegCfg = {
        tr2: {
            heur: 'idcheminsheurfid', progBox: 'selprog_box_tr2fid', progSel: 'selprog_tr2fid',
            sieges: 'psiegesitines1fid', prix: 'prix_axetransitfid', cate: 'catetransitfid',
            gid: 'gidtransfid', nom: 'nomitintrans1fid', lign: 'ligntrans1fid', depGare: 'transitedepargare2fid'
        },
        tr3: {
            heur: 'idcheminsheur1fid', progBox: 'selprog_box_tr3fid', progSel: 'selprog_tr3fid',
            sieges: 'psiegesitines2fid', prix: 'prix_axetransit1fid', cate: 'catetransit1fid',
            gid: 'gidtrans1fid', nom: 'nomitintrans2fid', lign: 'ligntrans2fid', depGare: 'transitedepargare3fid'
        },
        tr4: {
            heur: 'idcheminsheur2fid', progBox: 'selprog_box_tr4fid', progSel: 'selprog_tr4fid',
            sieges: 'psiegesitines3fid', prix: 'prix_axetransit2fid', cate: 'catetransit2fid',
            gid: 'gidtrans2fid', nom: 'nomitintrans3fid', lign: 'ligntrans3fid', depGare: 'transitedepargare4fid'
        }
    };

    function __venteFiCheminRowValue(row) {
        if (!row) return '';
        return String(row.code_progr) + '/' + row.intervalle1 + '/' + row.intervalle2 + '/'
            + row.id_ligneheure + '/' + (row.prix != null ? row.prix : '');
    }


    var __VENTE_FI_TRANSIT_MARGE_MIN = 30;

    function __venteFiHeureToMinutes(h) {
        if (h == null || h === '') return null;
        var parts = String(h).trim().split(/[:hH]/);
        if (!parts || !parts.length) return null;
        var hh = parseInt(parts[0], 10);
        if (isNaN(hh)) return null;
        var mm = (parts[1] != null && parts[1] !== '') ? parseInt(parts[1], 10) : 0;
        if (isNaN(mm)) mm = 0;
        return (hh * 60) + mm;
    }

    function __venteFiFormatDateShort(ymd) {
        if (!ymd || String(ymd).length < 10) return '';
        var p = String(ymd).slice(0, 10).split('-');
        return (p.length === 3) ? (p[2] + '/' + p[1]) : String(ymd).slice(0, 10);
    }

    function __venteFiHeureOptionLabel(heure, dateProgr, voyageDate, forceDate) {
        var label = String(heure || '');
        var dprog = dateProgr ? String(dateProgr).slice(0, 10) : '';
        var vDate = voyageDate ? String(voyageDate).slice(0, 10) : '';
        var showDate = !!forceDate || (dprog && vDate && dprog !== vDate);
        if (showDate && dprog) {
            var short = __venteFiFormatDateShort(dprog);
            if (short) label = label + ' — ' + short;
        }
        return label;
    }


    function __venteFiClearDownstreamCheminHeures() {
        ['idcheminsheurfid', 'idcheminsheur1fid', 'idcheminsheur2fid'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.options.length = 1;
        });
        ['psiegesitines1fid', 'psiegesitines2fid', 'psiegesitines3fid'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.options.length = 1;
        });
    }

    function __venteFiGetPrevTransitAnchor(nextLegKey) {
        var voyageDate = document.querySelector('#date_depheurefid')
            ? String(document.querySelector('#date_depheurefid').value || '').slice(0, 10) : '';
        var out = { date: voyageDate, heure: '', minutes: null, marge: __VENTE_FI_TRANSIT_MARGE_MIN };

        function fromCheminSelect(heurId) {
            var hs = document.getElementById(heurId);
            if (!hs || hs.selectedIndex < 1) return false;
            var opt = hs.options[hs.selectedIndex];
            var date = opt.getAttribute('data-date-progr') || '';
            var heure = opt.getAttribute('data-heure') || '';
            var gkey = opt.getAttribute('data-group-key') || '';
            var groups = (window.__venteFiCheminGroups && window.__venteFiCheminGroups[heurId]) || {};
            var g = groups[gkey] || groups[opt.value] || null;
            if (g && g.rows && g.rows.length) {
                if (!date && g.rows[0].date_progr) date = String(g.rows[0].date_progr).slice(0, 10);
                if (!heure && g.rows[0].heure) heure = String(g.rows[0].heure);
            }
            if ((!date || !heure) && String(opt.value).indexOf('/') !== -1) {
                var code = String(opt.value).split('/')[0];
                Object.keys(groups).forEach(function (k) {
                    if (date && heure) return;
                    var rows = groups[k] && groups[k].rows ? groups[k].rows : [];
                    for (var i = 0; i < rows.length; i++) {
                        if (String(rows[i].code_progr) === code) {
                            date = String(rows[i].date_progr || '').slice(0, 10);
                            heure = String(rows[i].heure || '');
                            break;
                        }
                    }
                });
            }
            if (!date) date = voyageDate;
            if (!heure) return false;
            out.date = date;
            out.heure = heure;
            out.minutes = __venteFiHeureToMinutes(heure);
            return out.minutes != null;
        }

        if (nextLegKey === 'tr2') {
            var dEl = document.querySelector('#dateprtransfid');
            var hEl = document.querySelector('#hertransfid');
            var date = (dEl && dEl.value) ? String(dEl.value).slice(0, 10) : voyageDate;
            var heure = (hEl && hEl.value) ? String(hEl.value) : '';
            if (!heure) {
                var hs1 = document.getElementById('hdepartitinefid');
                if (hs1 && hs1.selectedIndex > 0) {
                    var parts = String(hs1.options[hs1.selectedIndex].value || '').split('/');
                    if (parts[1]) heure = parts[1];
                }
            }
            out.date = date || voyageDate;
            out.heure = heure;
            out.minutes = __venteFiHeureToMinutes(heure);
            return out;
        }
        if (nextLegKey === 'tr3') { fromCheminSelect('idcheminsheurfid'); return out; }
        if (nextLegKey === 'tr4') { fromCheminSelect('idcheminsheur1fid'); return out; }
        return out;
    }

    function __venteFiRowIsAfterPrev(row, prev) {
        if (!prev || prev.minutes == null || !prev.date) return true;
        var rd = row && row.date_progr ? String(row.date_progr).slice(0, 10) : '';
        var rm = __venteFiHeureToMinutes(row && row.heure);
        if (!rd || rm == null) return false;
        if (rd > prev.date) return true;
        if (rd < prev.date) return false;
        var marge = (prev.marge != null) ? prev.marge : __VENTE_FI_TRANSIT_MARGE_MIN;
        return rm >= (prev.minutes + marge);
    }


    function __venteFiFillCheminHeures(selectId, rows, legKey) {
        var sel = document.getElementById(selectId);
        if (!sel) return;
        sel.options.length = 1;
        var list = Array.isArray(rows) ? rows
            : (rows && typeof rows === 'object' ? Object.keys(rows).map(function (k) { return rows[k]; }) : []);
        var prev = legKey ? __venteFiGetPrevTransitAnchor(legKey) : null;
        if (prev && prev.minutes != null && prev.date) {
            list = list.filter(function (row) { return __venteFiRowIsAfterPrev(row, prev); });
        }
        var voyageDate = document.querySelector('#date_depheurefid')
            ? String(document.querySelector('#date_depheurefid').value || '').slice(0, 10) : '';
        var groups = {};
        var order = [];
        for (var i = 0; i < list.length; i++) {
            var row = list[i];
            if (!row || row.code_progr == null || row.code_progr === '') continue;
            var lh = String(row.id_ligneheure != null ? row.id_ligneheure : '');
            if (!lh) continue;
            var dprog = row.date_progr ? String(row.date_progr).slice(0, 10) : '';
            var hh = String(row.heure || '').trim();
            var gkey = dprog + '|' + hh;
            if (!groups[gkey]) {
                groups[gkey] = {
                    heure: row.heure || '',
                    date_progr: dprog,
                    minutes: __venteFiHeureToMinutes(row.heure),
                    rows: []
                };
                order.push(gkey);
            }
            var exists = false;
            for (var j = 0; j < groups[gkey].rows.length; j++) {
                if (String(groups[gkey].rows[j].code_progr) === String(row.code_progr)) { exists = true; break; }
            }
            if (!exists) groups[gkey].rows.push(row);
        }
        order.sort(function (a, b) {
            var ga = groups[a], gb = groups[b];
            var da = ga.date_progr || '', db = gb.date_progr || '';
            if (da < db) return -1;
            if (da > db) return 1;
            return (ga.minutes != null ? ga.minutes : 0) - (gb.minutes != null ? gb.minutes : 0);
        });
        if (!window.__venteFiCheminGroups) window.__venteFiCheminGroups = {};
        window.__venteFiCheminGroups[selectId] = groups;
        var forceDate = false;
        var seenDates = {};
        var nDates = 0;
        for (var sd = 0; sd < order.length; sd++) {
            var gd = groups[order[sd]].date_progr || '';
            if (!gd || seenDates[gd]) continue;
            seenDates[gd] = 1;
            nDates++;
            if (nDates > 1) { forceDate = true; break; }
        }
        for (var k = 0; k < order.length; k++) {
            var key = order[k];
            var g = groups[key];
            var opt = document.createElement('option');
            opt.value = key;
            opt.setAttribute('data-group-key', key);
            opt.setAttribute('data-date-progr', g.date_progr || '');
            opt.setAttribute('data-heure', g.heure || '');
            var label = __venteFiHeureOptionLabel(g.heure || key, g.date_progr, voyageDate, forceDate);
            if (g.rows.length > 1) label = label + ' (' + g.rows.length + ' départs)';
            opt.innerHTML = label;
            sel.add(opt);
        }
        if (legKey) __venteFiWireCheminHeur(selectId, legKey);
        if (window.__venteFiCheminCascadeStarted && legKey) {
            __venteFiPreselectCheminHeurFromEtape(sel, __venteFiCheminEtapeForLeg(legKey), legKey);
            __venteFiAdvanceCheminCascade(legKey);
        }
    }

    function __venteFiCheminEtapeForLeg(legKey) {
        var etapes = window.__venteFiCheminEtapes;
        if (!etapes || !etapes.length) return null;
        if (legKey === 'tr2') return etapes[1] || null;
        if (legKey === 'tr3') return etapes[2] || null;
        if (legKey === 'tr4') return etapes[3] || null;
        return null;
    }

    function __venteFiPreselectCheminHeurFromEtape(heurSel, etape, legKey) {
        var sel = typeof heurSel === 'string' ? document.getElementById(heurSel) : heurSel;
        if (!sel || !legKey) return false;
        var cfg = __venteFiCheminLegCfg[legKey];
        if (!cfg) return false;
        var targetCode = (etape && etape._graphe_code_progr != null) ? String(etape._graphe_code_progr) : '';
        var targetLh = (etape && etape._graphe_id_ligneheure != null) ? String(etape._graphe_id_ligneheure) : '';
        var targetHeure = (etape && etape._graphe_heure != null) ? String(etape._graphe_heure) : '';
        var targetDate = (etape && etape._graphe_date_progr) ? String(etape._graphe_date_progr).slice(0, 10) : '';
        var groups = (window.__venteFiCheminGroups && window.__venteFiCheminGroups[sel.id]) || {};
        for (var idx = 1; idx < sel.options.length; idx++) {
            var opt = sel.options[idx];
            var g = groups[opt.value] || groups[opt.getAttribute('data-group-key')];
            if (!g || !g.rows || !g.rows.length) continue;
            var pickRow = null;
            for (var r = 0; r < g.rows.length; r++) {
                var row = g.rows[r];
                if (targetCode && String(row.code_progr) === targetCode) {
                    pickRow = row;
                    break;
                }
                if (!pickRow && targetLh && String(row.id_ligneheure) === targetLh) {
                    if (!targetHeure || String(row.heure) === targetHeure) pickRow = row;
                }
            }
            if (pickRow && targetDate && String(pickRow.date_progr || '').slice(0, 10) !== targetDate) {
                pickRow = null;
            }
            if (!pickRow) continue;
            sel.selectedIndex = idx;
            if (g.rows.length === 1) {
                __venteFiLoadSiegesChemin(cfg, pickRow);
            } else {
                __venteFiOnCheminHeurChange(legKey);
                var selProg = document.getElementById(cfg.progSel);
                if (selProg && targetCode) {
                    for (var pi = 1; pi < selProg.options.length; pi++) {
                        if (g.rows[pi - 1] && String(g.rows[pi - 1].code_progr) === targetCode) {
                            selProg.selectedIndex = pi;
                            if (typeof selProg.onchange === 'function') selProg.onchange();
                            break;
                        }
                    }
                }
            }
            return true;
        }
        if (sel.options.length > 1) {
            sel.selectedIndex = 1;
            __venteFiOnCheminHeurChange(legKey);
            return true;
        }
        return false;
    }

    function __venteFiAdvanceCheminCascade(completedLegKey) {
        if (!window.__venteFiCheminCascadeStarted) return;
        var etapes = window.__venteFiCheminEtapes;
        if (!etapes || etapes.length < 2) return;
        if (completedLegKey === 'tr2' && etapes.length >= 3 && etapes[2]) {
            __venteFiSetCheminLigneOption('#idchemins1fid', etapes[2].code_itineraires, etapes[2].nom_itineraires);
        } else if (completedLegKey === 'tr3' && etapes.length >= 4 && etapes[3]) {
            __venteFiSetCheminLigneOption('#idchemins2fid', etapes[3].code_itineraires, etapes[3].nom_itineraires);
        }
    }

    function __venteFiStartDownstreamCheminLegs(donitines) {
        donitines = (typeof __venteFiNormalizeEtapes === 'function')
            ? __venteFiNormalizeEtapes(donitines) : donitines;
        if (!donitines || donitines.length < 2 || !donitines[1]) return;
        window.__venteFiCheminEtapes = donitines;
        window.__venteFiCheminCascadeStarted = true;
        __venteFiSetCheminLigneOption('#idcheminsfid', donitines[1].code_itineraires, donitines[1].nom_itineraires);
    }

    function __venteFiMaybeStartCheminCascade() {
        if (window.__venteFiCheminEtapes && window.__venteFiCheminEtapes.length >= 2 && !window.__venteFiCheminCascadeStarted) {
            __venteFiStartDownstreamCheminLegs(window.__venteFiCheminEtapes);
        }
    }


    function __venteFiLoadSiegesChemin(cfg, row) {
        var ps = document.getElementById(cfg.sieges);
        if (ps) ps.options.length = 1;
        if (!row || !row.code_progr) return;
        if (!__venteFiShouldSkipAutoPrix() && cfg.prix && row.prix != null) {
            var px = document.getElementById(cfg.prix);
            if (px) px.value = String(row.prix);
        }
        var heur = document.getElementById(cfg.heur);
        if (heur && heur.selectedIndex >= 0) {
            heur.options[heur.selectedIndex].value = __venteFiCheminRowValue(row);
        }
        var httpMeta = new XMLHttpRequest();
        httpMeta.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${encodeURIComponent(row.code_progr)}`, true);
        httpMeta.onload = function () {
            try {
                var meta = JSON.parse(httpMeta.responseText);
                if (Object.entries(meta).length >= 1) {
                    for (var key in Object.entries(meta)) {
                        var map = [
                            [cfg.cate, meta[key].categori],
                            [cfg.gid, meta[key].gareidentif],
                            [cfg.nom, meta[key].nom_ligne],
                            [cfg.lign, meta[key].ident_ligne]
                        ];
                        for (var m = 0; m < map.length; m++) {
                            var el = document.getElementById(map[m][0]);
                            if (el) el.value = map[m][1] != null ? String(map[m][1]) : '';
                        }
                        if (cfg.depGare && meta[key].gareidentif) {
                            __venteFiFillTransitDepart('#' + cfg.depGare, meta[key].gareidentif);
                        }
                    }
                }
            } catch (e) {}
            var httpS = new XMLHttpRequest();
            httpS.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${encodeURIComponent(row.code_progr)}/${row.intervalle1}/${row.intervalle2}`, true);
            httpS.onload = function () {
                try {
                    var dat = JSON.parse(httpS.responseText);
                    if (ps) ps.options.length = 1;
                    if (Object.entries(dat).length >= 1) {
                        for (var k2 in Object.entries(dat)) {
                            var opt = document.createElement('option');
                            opt.value = `${dat[k2].siege_num}`;
                            opt.innerHTML = `${dat[k2].siege_num}`;
                            if (ps) ps.add(opt);
                        }
                    }
                } catch (e2) { if (ps) ps.options.length = 1; }
            };
            httpS.setRequestHeader('Content-Type', 'application/json');
            httpS.send();
        };
        httpMeta.setRequestHeader('Content-Type', 'application/json');
        httpMeta.send();
    }

    function __venteFiOnCheminHeurChange(legKey) {
        var cfg = __venteFiCheminLegCfg[legKey];
        if (!cfg) return;
        var heur = document.getElementById(cfg.heur);
        if (!heur) return;
        __venteFiHideProgSelectAny(cfg.progBox, cfg.progSel);
        var ps = document.getElementById(cfg.sieges);
        if (ps) ps.options.length = 1;
        var idLh = heur.value;
        if (!idLh) return;
        if (String(idLh).indexOf('/') !== -1) {
            var parts = String(idLh).split('/');
            __venteFiLoadSiegesChemin(cfg, {
                code_progr: parts[0], intervalle1: parts[1], intervalle2: parts[2],
                id_ligneheure: parts[3], prix: parts[4]
            });
            return;
        }
        var groups = (window.__venteFiCheminGroups && window.__venteFiCheminGroups[cfg.heur]) || {};
        var g = groups[idLh];
        var list = (g && g.rows) ? g.rows : [];
        if (!list.length) return;
        if (list.length === 1) {
            __venteFiLoadSiegesChemin(cfg, list[0]);
            return;
        }
        var box = document.getElementById(cfg.progBox);
        var sel = document.getElementById(cfg.progSel);
        if (!sel) {
            __venteFiLoadSiegesChemin(cfg, list[0]);
            return;
        }
        if (box) box.style.display = 'block';
        if (sel) sel.style.display = 'block';
        sel.options.length = 1;
        for (var i = 0; i < list.length; i++) {
            var opt = document.createElement('option');
            opt.value = String(i);
            opt.innerHTML = __venteFiLabelProg(list[i]);
            sel.add(opt);
        }
        sel.onchange = function () {
            if (ps) ps.options.length = 1;
            var idx = parseInt(sel.value, 10);
            if (isNaN(idx) || !list[idx]) return;
            __venteFiLoadSiegesChemin(cfg, list[idx]);
        };
    }

    function __venteFiWireCheminHeur(heurId, legKey) {
        var heur = document.getElementById(heurId);
        if (!heur) return;
        heur.onchange = function () { __venteFiOnCheminHeurChange(legKey); };
    }

    /** Remplit un select départ correspondance FI (sans option vide sélectionnée). */
    function __venteFiFillTransitDepart(selectSel, gareIdentif) {
        var sel = document.querySelector(selectSel);
        if (!sel) return;
        // length=1 sur un select vide crée une option blanche qui reste sélectionnée
        // et fait échouer la vente (transitedepargare*fid posté vide).
        sel.options.length = 0;
        if (gareIdentif == null || gareIdentif === '') return;
        var http = new XMLHttpRequest();
        http.open(
            'GET',
            window.location.origin + `${APP_ROOT}/programmes/verifsousgares/` + encodeURIComponent(gareIdentif),
            true
        );
        http.onload = function () {
            var rows = null;
            try { rows = JSON.parse(http.responseText); } catch (e) { rows = null; }
            sel.options.length = 0;
            if (!rows || Object.entries(rows).length < 1) return;
            for (var key in Object.entries(rows)) {
                var opt = document.createElement('option');
                opt.value = `${rows[key].idsousgare}`;
                opt.innerHTML = `${rows[key].nomsousgare}`;
                sel.add(opt);
            }
            if (sel.options.length > 0) sel.selectedIndex = 0;
        };
        http.setRequestHeader('Content-Type', 'application/json');
        http.send();
    }

    function __venteFiResetMainEscaleUi() {
        var ck = document.querySelector('#escale_vente_check_fid');
        if (ck) ck.checked = false;
        ['#id_escale_ventefid', '#code_gadest_ventefid', '#nom_dest_ventefid'].forEach(function (s) {
            var el = document.querySelector(s);
            if (el) el.value = '';
        });
        var fields = document.querySelector('#escale_dest_fields_fid');
        if (fields) fields.style.display = 'none';
        var sel = document.querySelector('#escale_dest_select_fid');
        if (sel) sel.value = '';
    }

    function __venteFiSetMainEscaleVisible(visible) {
        var wrap = document.querySelector('#escale_dest_wrap_fid');
        if (!wrap) return;
        if (!visible) {
            __venteFiResetMainEscaleUi();
            wrap.style.display = 'none';
        } else {
            wrap.style.display = '';
        }
    }
    window.__venteFiSetMainEscaleVisible = __venteFiSetMainEscaleVisible;

    function __venteFiReleaseTamponSiege(idtampoId, siegselectId) {
        return new Promise(function (resolve) {
            var idEl = document.getElementById(idtampoId);
            var sigEl = document.getElementById(siegselectId);
            if (!idEl || !sigEl) {
                resolve();
                return;
            }
            var idv = String(idEl.value || '').trim();
            var sv = String(sigEl.value || '').trim();
            if (!idv || !sv) {
                idEl.value = '';
                sigEl.value = '';
                resolve();
                return;
            }
            var http = new XMLHttpRequest();
            http.open(
                'GET',
                window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/` + encodeURIComponent(idv) + '/' + encodeURIComponent(sv),
                true
            );
            http.onload = function () {
                idEl.value = '';
                sigEl.value = '';
                resolve();
            };
            http.onerror = function () {
                idEl.value = '';
                sigEl.value = '';
                resolve();
            };
            http.setRequestHeader('Content-Type', 'application/json');
            http.send();
        });
    }

    var __venteFiTamponSiegePairs = [
        ['idtampofid', 'siegselectfid'],
        ['idtampotransfid', 'siegselecttransfid'],
        ['idtampo1fid', 'siegselect1fid'],
        ['idtampo2fid', 'siegselect2fid'],
        ['idtampo3fid', 'siegselect3fid']
    ];

    function __venteFiReleaseAllTamponSieges() {
        var chain = Promise.resolve();
        __venteFiTamponSiegePairs.forEach(function (p) {
            chain = chain.then(function () {
                return __venteFiReleaseTamponSiege(p[0], p[1]);
            });
        });
        return chain;
    }

    function __venteFiFlushTamponsSync() {
        __venteFiTamponSiegePairs.forEach(function (p) {
            var idEl = document.getElementById(p[0]);
            var sigEl = document.getElementById(p[1]);
            if (!idEl || !sigEl) return;
            var idv = String(idEl.value || '').trim();
            var sv = String(sigEl.value || '').trim();
            if (!idv || !sv) return;
            try {
                var http = new XMLHttpRequest();
                http.open(
                    'GET',
                    window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/` + encodeURIComponent(idv) + '/' + encodeURIComponent(sv),
                    false
                );
                http.send();
            } catch (e) {}
            idEl.value = '';
            sigEl.value = '';
        });
    }

    function __venteFiWireTamponLifecycle() {
        if (window.__venteFiTamponLifecycleWired) return;
        window.__venteFiTamponLifecycleWired = true;
        window.addEventListener('pagehide', __venteFiFlushTamponsSync);
        window.addEventListener('beforeunload', __venteFiFlushTamponsSync);
        setInterval(function () {
            var touches = [
                ['idtampofid', 'siegselectfid', '#programfid'],
                ['idtampotransfid', 'siegselecttransfid', '#programtransfid']
            ];
            touches.forEach(function (t) {
                var idEl = document.getElementById(t[0]);
                var sigEl = document.getElementById(t[1]);
                var pr = document.querySelector(t[2]);
                if (!idEl || !sigEl || !pr) return;
                var idv = String(idEl.value || '').trim();
                var sv = String(sigEl.value || '').trim();
                var prog = String(pr.value || '').trim();
                if (!idv || !sv || !prog) return;
                try {
                    var http = new XMLHttpRequest();
                    http.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/` + encodeURIComponent(prog) + '/' + encodeURIComponent(sv), true);
                    http.send();
                } catch (e2) {}
            });
        }, 10 * 60 * 1000);
    }
    __venteFiWireTamponLifecycle();

    function __venteFiResetSaleUiAfterCancel() {
        window.__venteFiHasTransit = false;
        window.__venteFiLastHeuresVente = [];
        window.__venteSelectedHour = null;
        if (typeof window.__venteClearTransitAnchor === 'function') window.__venteClearTransitAnchor();
        window.__venteFiCheminGroups = {};
        window.__venteFiCheminEtapes = null;
        window.__venteFiCheminCascadeStarted = false;

        if (typeof __venteFiHideCheminSelector === 'function') __venteFiHideCheminSelector();
        if (typeof __venteFiResetTransitFieldsBeforeApply === 'function') __venteFiResetTransitFieldsBeforeApply();
        if (typeof __venteFiShowDirectHourUi === 'function') __venteFiShowDirectHourUi();
        if (typeof __venteFiResetMainEscaleUi === 'function') __venteFiResetMainEscaleUi();
        if (typeof __venteFiHideProgSelect === 'function') __venteFiHideProgSelect();
        if (typeof __venteFiClearTransitPrixFields === 'function') __venteFiClearTransitPrixFields();

        ['#hdepartfid', '#psiegesfid', '#quartierfid'].forEach(function (s) {
            var el = document.querySelector(s);
            if (el && el.options) {
                el.options.length = 1;
                el.selectedIndex = 0;
                el.value = '';
                el.onchange = null;
            }
        });

        var mess = document.querySelector('#messfid');
        if (mess) mess.style.display = 'none';
        var err = document.querySelector('#erreurMessfid');
        if (err) err.innerHTML = '';

        var form = document.getElementById('tafiForm');
        if (form) form.reset();
    }

    function __venteFiCancelSale(ev) {
        if (ev && ev.preventDefault) ev.preventDefault();
        __venteFiReleaseAllTamponSieges().then(function () {
            __venteFiResetSaleUiAfterCancel();
        });
    }

    function __venteFiWireCancelButton(btnId) {
        var btn = document.getElementById(btnId);
        if (!btn || btn.dataset.venteCancelWired === '1') return;
        btn.dataset.venteCancelWired = '1';
        btn.type = 'button';
        btn.addEventListener('click', __venteFiCancelSale);
    }

    window.__venteFiHasTransit = false;
    window.__venteFiLastHeuresVente = [];
    window.__venteFiApplyTransitLegs = null;

    function __venteFiOrdinalFr(n) {
        var i = parseInt(n, 10) || 0;
        if (i <= 1) return '1ER';
        return i + 'ème';
    }

    function __venteFiAllowMultiChecked() {
        var el = document.querySelector('#vente_fi_allow_multi');
        return !!(el && el.checked);
    }

    function __venteFiSyncAllowMultiWrap(hasAnyDirect, hasTransit) {
        var wrap = document.querySelector('#vente_fi_allow_multi_wrap');
        var cb = document.querySelector('#vente_fi_allow_multi');
        if (!wrap) return;
        var show = !!(hasAnyDirect && hasTransit);
        wrap.style.display = show ? '' : 'none';
        if (!show && cb) cb.checked = false;
    }

    function __venteFiFillHeuresVente(heures) {
        var hSel = document.querySelector('#hdepartfid');
        if (!hSel) return;
        hSel.options.length = 1;
        var list = Array.isArray(heures) ? heures.slice() : [];
        var hasTransit = !!window.__venteFiHasTransit;
        // Règle : directs seuls s'il y en a, sinon créneaux correspondance.
        // Case cochée : directs + créneaux multi.
        var hasAnyDirect = list.some(function (hr) {
            return hr && (hr.has_programme === true || hr.has_programme === 1 || hr.has_programme === '1');
        });
        var allowMulti = __venteFiAllowMultiChecked();
        __venteFiSyncAllowMultiWrap(hasAnyDirect, hasTransit);
        if (hasAnyDirect && allowMulti && hasTransit) {
            // garder toute la liste
        } else if (hasAnyDirect) {
            list = list.filter(function (hr) {
                return hr && (hr.has_programme === true || hr.has_programme === 1 || hr.has_programme === '1');
            });
        } else if (!hasTransit) {
            list = [];
        } else {
            list = list.filter(function (hr) {
                return hr && !(hr.has_programme === true || hr.has_programme === 1 || hr.has_programme === '1');
            });
        }
        list.sort(function (a, b) {
            var ha = String((a && a.heure) || '');
            var hb = String((b && b.heure) || '');
            if (ha !== hb) return ha < hb ? -1 : 1;
            var ca = String((a && a.code_progr) || '');
            var cb = String((b && b.code_progr) || '');
            return ca < cb ? -1 : (ca > cb ? 1 : 0);
        });
        var countByHh = {};
        list.forEach(function (hr) {
            if (!hr || !hr.has_programme) return;
            var hh = String(hr.heure || '');
            if (!hh) return;
            countByHh[hh] = (countByHh[hh] || 0) + 1;
        });
        var idxByHh = {};
        for (var i = 0; i < list.length; i++) {
            var hr = list[i];
            if (!hr || hr.id_ligneheure == null || hr.id_ligneheure === '') continue;
            var hasProg = !!(hr.has_programme === true || hr.has_programme === 1 || hr.has_programme === '1');
            var code = hasProg && hr.code_progr ? String(hr.code_progr) : '';
            var opt = document.createElement('option');
            opt.value = String(hr.id_ligneheure) + '/' + String(hr.heure)
                + (code ? ('/' + code) : '');
            opt.setAttribute('data-has-programme', hasProg ? '1' : '0');
            opt.setAttribute('data-heure', String(hr.heure || ''));
            if (code) opt.setAttribute('data-code-progr', code);
            var label;
            if (hasProg) {
                var hh = String(hr.heure || '');
                idxByHh[hh] = (idxByHh[hh] || 0) + 1;
                var multi = (countByHh[hh] || 0) > 1;
                label = multi
                    ? (hh + ' — ' + __venteFiOrdinalFr(idxByHh[hh]))
                    : hh;
            } else {
                label = String(hr.heure) + (hasTransit ? ' (correspondance)' : '');
            }
            opt.innerHTML = label;
            hSel.add(opt);
        }
        __venteFiHideProgSelect();
    }

    /** Affiche l'UI heures/siège directe FI ; cache le panneau transit. Champs FI (P/O…) inchangés. */
    function __venteFiShowDirectHourUi() {
        var hideIds = [
            'depitin1fid','depargareitine1fid','iddeptrans1fid','transitedepargare1fid',
            'iddeptrans2fid','transitedepargare2fid','iddeptrans3fid','transitedepargare3fid',
            'iddeptrans4fid','transitedepargare4fid','arritin1fid','arrsgareitine1fid',
            'heureitin1fid','hdepartitine1fid','lignesitinerairefid','ligne1fid',
            'siegitine1fid','psiegesitines1fid','depitin2fid','depargareitine2fid',
            'arritin2fid','arrsgareitine2fid','heureitin2fid','hdepartitine2fid',
            'siegitine2fid','psiegesitines2fid','depitin3fid','depargareitine3fid',
            'arritin3fid','arrsgareitine3fid','heureitin3fid','hdepartitine3fid',
            'siegitine3fid','psiegesitines3fid','quartier1fid','quartier2fid','quartier3fid',
            'idquart1fid','idquart2fid','idquart3fid','prix_axetransfid','prix_axetransfid1',
            'prix_axetransitfid1','prix_axetransitfid','prix_axetransit1fid1','prix_axetransit1fid',
            'prix_axetransit2fid1','prix_axetransit2fid','heureitinfid','hdepartitinefid',
            'siegitinefid','psiegesitinesfid','idcheminsfid','idcheminsheurfid',
            'idchemins1fid','idcheminsheur1fid','idchemins2fid','idcheminsheur2fid'
        ];
        for (var i = 0; i < hideIds.length; i++) {
            var el = document.getElementById(hideIds[i]);
            if (el) el.style.display = 'none';
        }
        var tran = document.querySelector('#tranfid');
        if (tran) tran.style.display = 'none';
        if (typeof __venteFiSetMainEscaleVisible === 'function') __venteFiSetMainEscaleVisible(true);
        ['hridfid','hdepartfid','sigidfid','psiegesfid','iddepfid','depargarefid',
         'arridfid','arrsgarefid','prix_axefid1','prix_axefid','idquartfid','quartierfid'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.style.display = 'block';
        });
        __venteFiHideProgSelect();
        __venteFiHideProgSelectAny('selprog_box_tr1fid', 'selprog_tr1fid');
        __venteFiHideProgSelectAny('selprog_box_tr2fid', 'selprog_tr2fid');
        __venteFiHideProgSelectAny('selprog_box_tr3fid', 'selprog_tr3fid');
        __venteFiHideProgSelectAny('selprog_box_tr4fid', 'selprog_tr4fid');
    }

    function __venteFiEnsureCheminSelector() {
        var existing = document.getElementById('selchemin_box_fid');
        if (existing) {
            // Remonter hors de #tranfid si une ancienne version l’y avait placé.
            var tranfid = document.getElementById('tranfid');
            if (tranfid && tranfid.contains(existing) && tranfid.parentNode) {
                tranfid.parentNode.insertBefore(existing, tranfid);
            }
            return existing;
        }
        var box = document.createElement('div');
        box.className = 'form-group col-sm-12';
        box.id = 'selchemin_box_fid';
        box.style.display = 'none';
        box.innerHTML = ''
            + '<label id="selchemin_label_fid">Itinéraire de correspondance</label>'
            + '<select class="form-control form-control-sm" id="selchemin_transit_fid">'
            + '<option value="">Choisissez l\'itinéraire</option>'
            + '</select>'
            + '<small class="form-text text-muted" id="selchemin_hint_fid"></small>';
        // Ancrer sur l’heure OD visible — jamais dans #tranfid (display:none).
        var anchor = document.getElementById('hdepartfid')
            || document.getElementById('date_depheurefid');
        var tranfid = document.getElementById('tranfid');
        if (anchor) {
            var fg = anchor.closest ? anchor.closest('.form-group') : null;
            if (fg && fg.parentNode) {
                if (tranfid && tranfid.parentNode === fg.parentNode) {
                    fg.parentNode.insertBefore(box, tranfid);
                } else {
                    fg.parentNode.insertBefore(box, fg.nextSibling);
                }
                return box;
            }
            if (anchor.parentNode) {
                anchor.parentNode.insertBefore(box, anchor.nextSibling);
                return box;
            }
        }
        if (tranfid && tranfid.parentNode) {
            tranfid.parentNode.insertBefore(box, tranfid);
            return box;
        }
        document.body.appendChild(box);
        return box;
    }

    function __venteFiHideCheminSelector() {
        var box = document.getElementById('selchemin_box_fid');
        var sel = document.getElementById('selchemin_transit_fid');
        var hint = document.getElementById('selchemin_hint_fid');
        if (box) box.style.display = 'none';
        if (sel) { sel.options.length = 1; sel.value = ''; sel.onchange = null; }
        if (hint) hint.textContent = '';
    }

    function __venteFiFormatAttenteLabel(chemin) {
        if (!chemin) return '';
        if (chemin.attente_totale_label) return 'Attente totale : ' + chemin.attente_totale_label;
        if (chemin.attente_totale_min != null) {
            var m = parseInt(chemin.attente_totale_min, 10) || 0;
            var h = Math.floor(m / 60);
            var mm = m % 60;
            return 'Attente totale : ' + (h > 0 ? (h + ' h' + (mm ? (' ' + (mm < 10 ? '0' : '') + mm) : '')) : (mm + ' min'));
        }
        return chemin.source === 'declaratif' ? 'Composition déclarée' : '';
    }


    function __venteFiNormalizeEtapes(etapes) {
        if (!etapes) return [];
        if (Array.isArray(etapes)) return etapes;
        if (typeof etapes === 'object') {
            return Object.keys(etapes).map(function (k) { return etapes[k]; }).filter(Boolean);
        }
        return [];
    }

    /**
     * Correspondance 2/3/4 — ligne : propose la ligne du chemin, sans la sélectionner.
     */
    function __venteFiSetCheminLigneOption(selectSel, code, nom, fireChange) {
        var sel = typeof selectSel === 'string' ? document.querySelector(selectSel) : selectSel;
        if (!sel) return;
        sel.disabled = false;
        sel.removeAttribute('disabled');
        sel.options.length = 1;
        sel.selectedIndex = 0;
        if (code == null || code === '') return;
        var opt = document.createElement('option');
        opt.value = String(code);
        opt.innerHTML = nom != null ? String(nom) : String(code);
        if (nom != null) opt.setAttribute('data-nom', String(nom));
        sel.add(opt);
        sel.selectedIndex = 1;
        if (fireChange !== false && typeof sel.onchange === 'function') {
            sel.onchange();
        }
    }

    function __venteFiEnsureLigne1LockedInput() {
        var el = document.getElementById('lignesitinerairefid');
        if (!el) return null;
        if (el.tagName === 'INPUT') {
            el.disabled = true;
            el.setAttribute('disabled', 'disabled');
            el.readOnly = true;
            return el;
        }
        var inp = document.createElement('input');
        inp.type = 'text';
        inp.id = 'lignesitinerairefid';
        inp.name = el.getAttribute('name') || 'lignesitinerairesfid';
        inp.className = el.className || 'form-control form-control-sm';
        inp.disabled = true;
        inp.setAttribute('disabled', 'disabled');
        inp.readOnly = true;
        if (el.parentNode) el.parentNode.replaceChild(inp, el);
        return inp;
    }

    function __venteFiFillLigne1Locked(etape0, onPick) {
        if (!etape0) return;
        var code = etape0.code_itineraires || '';
        var nom = etape0.nom_itineraires || code;
        var el = __venteFiEnsureLigne1LockedInput();
        if (el) el.value = nom;
        var itc = document.querySelector('#itinecodefid');
        var ltn = document.querySelector('#lignetinerairefid');
        if (itc) itc.value = code;
        if (ltn) ltn.value = nom;
        if (typeof onPick === 'function') onPick(code, nom);
    }

    function __venteFiResetTransitFieldsBeforeApply() {
        [
            'arritin1fid','idcheminsfid','heureitin1fid','idcheminsheurfid','siegitine1fid','psiegesitines1fid',
            'arritin2fid','idchemins1fid','heureitin2fid','idcheminsheur1fid','siegitine2fid','psiegesitines2fid',
            'arritin3fid','idchemins2fid','heureitin3fid','idcheminsheur2fid','siegitine3fid','psiegesitines3fid',
            'quartier1fid','quartier2fid','quartier3fid','idquart1fid','idquart2fid','idquart3fid',
            'iddeptrans1fid','transitedepargare1fid','iddeptrans2fid','transitedepargare2fid',
            'iddeptrans3fid','transitedepargare3fid','iddeptrans4fid','transitedepargare4fid',
            'tranfid','heureitinfid','hdepartitinefid','lignesitinerairefid','ligne1fid','siegitinefid','psiegesitinesfid'
        ].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });
        [
            '#idcheminsfid','#idchemins1fid','#idchemins2fid',
            '#idcheminsheurfid','#idcheminsheur1fid','#idcheminsheur2fid',
            '#hdepartitinefid','#psiegesitinesfid','#psiegesitines1fid','#psiegesitines2fid','#psiegesitines3fid',
            '#quartier1fid','#quartier2fid','#quartier3fid'
        ].forEach(function (s) {
            var el = document.querySelector(s);
            if (el && el.options) { el.options.length = 1; el.value = ''; el.onchange = null; }
        });
        ['#transitedepargare1fid','#transitedepargare2fid','#transitedepargare3fid','#transitedepargare4fid'].forEach(function (s) {
            var el = document.querySelector(s);
            if (el && el.options) el.options.length = 0;
        });
        ['#itinecodefid','#itinecodesfid','#lignetinerairefid','#lignesitinerairefid','#nbrtransfid',
         '#idcompgfid','#idcompg1fid','#idcompg2fid','#idcompg3fid'].forEach(function (s) {
            var el = document.querySelector(s);
            if (el) el.value = '';
        });
        if (typeof __venteFiClearTransitPrixFields === 'function') __venteFiClearTransitPrixFields();
        window.__venteFiCheminEtapes = null;
        window.__venteFiCheminCascadeStarted = false;
    }

    function __venteFiShowCheminSelector(chemins, onPick) {
        __venteFiEnsureCheminSelector();
        var box = document.getElementById('selchemin_box_fid');
        var sel = document.getElementById('selchemin_transit_fid');
        var hint = document.getElementById('selchemin_hint_fid');
        if (!box || !sel) {
            var et0 = chemins && chemins[0] ? __venteFiNormalizeEtapes(chemins[0].etapes) : [];
            if (typeof window.__venteFiApplyTransitLegs === 'function') window.__venteFiApplyTransitLegs(et0);
            else if (typeof onPick === 'function') onPick(et0);
            return;
        }
        sel.options.length = 1;
        for (var i = 0; i < chemins.length; i++) {
            var opt = document.createElement('option');
            opt.value = String(i);
            opt.textContent = chemins[i].label || ('Chemin ' + (i + 1));
            sel.add(opt);
        }
        box.style.display = 'block';
        var applyIdx = function (idx) {
            var ch = chemins[idx];
            if (hint) hint.textContent = __venteFiFormatAttenteLabel(ch);
            var etapes = __venteFiNormalizeEtapes(ch && ch.etapes);
            if (typeof window.__venteFiApplyTransitLegs === 'function') window.__venteFiApplyTransitLegs(etapes);
            else if (typeof onPick === 'function') onPick(etapes);
        };
        sel.onchange = function () {
            var idx = parseInt(sel.value, 10);
            if (isNaN(idx) || !chemins[idx]) {
                if (hint) hint.textContent = '';
                if (typeof window.__venteFiApplyTransitLegs === 'function') window.__venteFiApplyTransitLegs([]);
                else if (typeof onPick === 'function') onPick([]);
                return;
            }
            applyIdx(idx);
        };
        var defaultIdx = 0;
        if (typeof window.__venteDefaultCheminIndex === 'function') {
            defaultIdx = window.__venteDefaultCheminIndex(chemins, window.__venteSelectedHour);
        } else {
            // Même ranking que guichet : hub_lie > programmes > court > déclaratif.
            function prioFi(c) {
                if (!c) return -1;
                if (typeof c.priority === 'number') return c.priority;
                var s = c.source || '';
                if (s === 'hub_lie') return 100;
                if (s === 'programmes') return 80;
                if (s === 'programmes_aval') return 70;
                if (s === 'graphe_gare') return 60;
                if (s === 'gare_composition') return 55;
                if (s === 'graphe') return 40;
                if (s === 'declaratif' || s === 'graphe_declaratif') return 20;
                if (s === 'direct') return 10;
                return 30;
            }
            function nbFi(c) {
                var n = parseInt(c && c.nb_jambes, 10);
                if (!isNaN(n) && n > 0) return n;
                return (c && c.etapes && c.etapes.length) || 99;
            }
            var bestP = prioFi(chemins[0]);
            var bestN = nbFi(chemins[0]);
            for (var i = 1; i < chemins.length; i++) {
                var p = prioFi(chemins[i]);
                var n = nbFi(chemins[i]);
                if (p > bestP || (p === bestP && n < bestN)) {
                    defaultIdx = i;
                    bestP = p;
                    bestN = n;
                }
            }
            if (window.__venteSelectedHour && !window.__venteSelectedHour.hasProg
                && chemins[defaultIdx] && chemins[defaultIdx].source === 'direct') {
                for (var j = 0; j < chemins.length; j++) {
                    if (chemins[j].source !== 'direct') { defaultIdx = j; break; }
                }
            }
        }
        sel.selectedIndex = defaultIdx + 1;
        applyIdx(defaultIdx);
    }

    function __venteFiRequestTransitLegs(seltdep, arr, datedepart, sougid, force, onDone) {
        var sg = (sougid != null && sougid !== '') ? sougid : '0';
        var forceFlag = force ? '1' : '0';
        var done = function (etapes) {
            if (typeof onDone === 'function') onDone(etapes);
            else if (typeof window.__venteFiApplyTransitLegs === 'function') window.__venteFiApplyTransitLegs(etapes);
        };
        var url = window.location.origin + `${APP_ROOT}/programmes/verifchemins/`
            + encodeURIComponent(seltdep + '-' + arr) + '/'
            + encodeURIComponent(datedepart) + '/'
            + encodeURIComponent(sg) + '/'
            + forceFlag;
        var hour = window.__venteSelectedHour;
        if (hour && hour.heure) {
            url += '?heure=' + encodeURIComponent(hour.heure);
        }
        var httpRequestitinefi = new XMLHttpRequest();
        httpRequestitinefi.open('GET', url, true);
        httpRequestitinefi.onload = function () {
            var payload = null;
            try { payload = JSON.parse(httpRequestitinefi.responseText); } catch (e) { payload = null; }
            if (Array.isArray(payload)) { __venteFiHideCheminSelector(); done(payload); return; }
            if (!payload || typeof payload !== 'object') { __venteFiHideCheminSelector(); done([]); return; }
            if (payload.mode === 'direct' || payload.mode === 'none') { __venteFiHideCheminSelector(); done([]); return; }
            var chemins = Array.isArray(payload.chemins) ? payload.chemins : [];
            chemins = chemins.filter(function (c) { return c && c.source !== 'direct'; });
            if (chemins.length >= 1) {
                __venteFiShowCheminSelector(chemins, done);
                return;
            }
            __venteFiHideCheminSelector();
            if (payload.etapes && (Array.isArray(payload.etapes) ? payload.etapes.length : Object.keys(payload.etapes).length)) {
                done(payload.etapes); return;
            }
            done([]);
        };
        httpRequestitinefi.setRequestHeader('Content-Type', 'application/json');
        httpRequestitinefi.send();
    }


    function __venteFiApplyTransit1Fields(p) {
        if (!p) return;
        var set = function (id, val) {
            var el = document.querySelector(id);
            if (el) el.value = val == null ? '' : String(val);
        };
        set('#programtransfid', p.code_progr);
        // Défaut tarif 1 si absent — sinon verifpriprg / prixtransfid ne partent jamais.
        var tf = (p.typetarif != null && String(p.typetarif).trim() !== '') ? p.typetarif : '1';
        set('#tarifattribfid', tf);
        set('#dateprtransfid', p.date_progr);
        set('#deplignetransfid', p.gareidentif);
        set('#intertrans1fid', p.intervalle1);
        set('#intertrans2fid', p.intervalle2);
        set('#ligntransfid', p.ident_ligne);
        set('#nomitintransfid', p.nom_ligne);
        set('#hertransfid', p.heure);
        set('#catetransfid', p.categori);
        if (!__venteFiShouldSkipAutoPrix() && p.prix != null && String(p.prix).trim() !== '') {
            set('#prix_axetransfid', p.prix);
        }
        __venteFiClearDownstreamCheminHeures();
        __venteFiMaybeStartCheminCascade();
    }

    function __venteFiLoadSiegesTransit1(idLh, dptDate) {
        var ps = document.querySelector('#psiegesitinesfid');
        if (ps) ps.options.length = 1;
        var tfEl = document.querySelector('#tarifattribfid');
        var tfbs = tfEl && String(tfEl.value || '').trim() !== '' ? String(tfEl.value).trim() : '1';
        if (tfEl && String(tfEl.value || '').trim() === '') tfEl.value = tfbs;
        if (idLh && !__venteFiShouldSkipAutoPrix()) {
            var httpPrix = new XMLHttpRequest();
            httpPrix.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifpriprg/${idLh}/${tfbs}`, true);
            httpPrix.onload = function () {
                try {
                    var donprix = JSON.parse(httpPrix.responseText);
                    if (Object.entries(donprix).length >= 1) {
                        for (var key in Object.entries(donprix)) {
                            var px = document.querySelector('#prix_axetransfid');
                            if (px) px.value = `${donprix[key].prix}`;
                        }
                    }
                } catch (e) {}
            };
            httpPrix.setRequestHeader('Content-Type', 'application/json');
            httpPrix.send();
        }
        var cd = document.querySelector('#programtransfid') ? document.querySelector('#programtransfid').value : '';
        var db = document.querySelector('#intertrans1fid') ? document.querySelector('#intertrans1fid').value : '';
        var fn = document.querySelector('#intertrans2fid') ? document.querySelector('#intertrans2fid').value : '';
        var lg = document.querySelector('#nomitintransfid') ? document.querySelector('#nomitintransfid').value : '';
        var tim = document.querySelector('#hertransfid') ? document.querySelector('#hertransfid').value : '';
        if (!cd) return;
        var http = new XMLHttpRequest();
        http.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${cd}/${dptDate}/${lg}/${tim}/${db}/${fn}`, true);
        http.onload = function () {
            try {
                var dat = JSON.parse(http.responseText);
                if (ps) ps.options.length = 1;
                if (Object.entries(dat).length >= 1) {
                    for (var key in Object.entries(dat)) {
                        var opt = document.createElement('option');
                        opt.value = `${dat[key].siege_num}`;
                        opt.innerHTML = `${dat[key].siege_num}`;
                        if (ps) ps.add(opt);
                    }
                }
            } catch (e) { if (ps) ps.options.length = 1; }
        };
        http.setRequestHeader('Content-Type', 'application/json');
        http.send();
    }

    function __venteFiHandleTransit1ProgList(don, idLh, dptDate) {
        var list = __venteFiProgListFromResponse(don);
        __venteFiHideProgSelectAny('selprog_box_tr1fid', 'selprog_tr1fid');
        var ps = document.querySelector('#psiegesitinesfid');
        if (ps) ps.options.length = 1;
        if (!list.length) return false;
        if (list.length === 1) {
            __venteFiApplyTransit1Fields(list[0]);
            __venteFiLoadSiegesTransit1(idLh, dptDate);
            return true;
        }
        var box = document.getElementById('selprog_box_tr1fid');
        var sel = document.getElementById('selprog_tr1fid');
        if (!sel) {
            __venteFiApplyTransit1Fields(list[0]);
            __venteFiLoadSiegesTransit1(idLh, dptDate);
            return true;
        }
        if (box) box.style.display = 'block';
        if (sel) sel.style.display = 'block';
        sel.options.length = 1;
        for (var i = 0; i < list.length; i++) {
            var opt = document.createElement('option');
            opt.value = String(i);
            opt.innerHTML = __venteFiLabelProg(list[i]);
            sel.add(opt);
        }
        sel.onchange = function () {
            if (ps) ps.options.length = 1;
            var idx = parseInt(sel.value, 10);
            if (isNaN(idx) || !list[idx]) return;
            __venteFiApplyTransit1Fields(list[idx]);
            __venteFiLoadSiegesTransit1(idLh, dptDate);
        };
        return true;
    }

    document.querySelectorAll('.addventeticketfi').forEach(function (e) 
    {
        document.querySelector('h3#tafiTitle').innerHTML = `VENTE DE FIDELITE`;

            let arfi= document.querySelector('#arrsgarefid');
            if (arfi !== null)
            arfi.onchange = () => {
                document.querySelector('#prix_axefid').value = '';
                document.querySelector('#prix_axefid').value = '';
                document.querySelector('#date_depheurefid').value = '';
                document.querySelector('#hdepartfid').options.length = 1;
                document.querySelector('#quartierfid').options.length = 1;
                document.querySelector('#psiegesfid').options.length = 1;
                __venteFiHideProgSelect();
                __venteFiHideProgSelectAny('selprog_box_tr1fid', 'selprog_tr1fid');
                __venteFiHideProgSelectAny('selprog_box_tr2fid', 'selprog_tr2fid');
                __venteFiHideProgSelectAny('selprog_box_tr3fid', 'selprog_tr3fid');
                __venteFiHideProgSelectAny('selprog_box_tr4fid', 'selprog_tr4fid');
                document.querySelector('#hdepartitinefid').options.length = 1;
                document.querySelector('#psiegesitinesfid').options.length = 1;
                document.querySelector('#idcheminsheurfid').options.length = 1;
                document.querySelector('#transitedepargare1fid').options.length = 0;
                document.querySelector('#transitedepargare2fid').options.length = 0;
                document.querySelector('#transitedepargare3fid').options.length = 0;
                document.querySelector('#transitedepargare4fid').options.length = 0;
                document.querySelector('#idcheminsfid').options.length = 1;
                document.querySelector('#idchemins1fid').options.length = 1;
                document.querySelector('#idchemins2fid').options.length = 1;
                document.querySelector('#psiegesitines1fid').options.length = 1;
                document.querySelector('#idcheminsheur1fid').options.length = 1;
                document.querySelector('#psiegesitines2fid').options.length = 1;
                document.querySelector('#idcheminsheur2fid').options.length = 1;
                document.querySelector('#psiegesitines3fid').options.length = 1;
                document.querySelector('#quartier1fid').options.length = 1;
                document.querySelector('#quartier2fid').options.length = 1;
                document.querySelector('#quartier3fid').options.length = 1;
                    const typgarefi = document.querySelector('#arrsgarefid').value;
                    let httptypequartfi;
                    httptypequartfi = new XMLHttpRequest();
                    
                    httptypequartfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${typgarefi}`, true);
                    httptypequartfi.onload = () => 
                    {
                        const donquafi = JSON.parse(httptypequartfi.responseText);
                        if (donquafi == '') {
                            document.querySelector('#quartierfid').options.length = 1;
                        }
                        else{
                            if (Object.entries(donquafi).length >= 1) {
                                            
                                for (let key in Object.entries(donquafi)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${donquafi[key].nom_quartier}`;
                                    opt.innerHTML = `${donquafi[key].nom_quartier}`;
                                    document.querySelector('#quartierfid').add(opt);
                                }
                            } else {
                                document.querySelector('#quartierfid').options.length = 1;
                            }
                        }
                        

                    };
                    httptypequartfi.setRequestHeader('Content-Type', 'application/json');
                    httptypequartfi.send();
            };
            
            let dafi = document.querySelector('#date_depheurefid');
            if (dafi !== null){
                dafi.onchange = () => 
                {
                    
                    document.querySelector('#hdepartfid').options.length = 1;
                    document.querySelector('#psiegesfid').options.length = 1;
                    document.querySelector('#hdepartitinefid').options.length = 1;
                    document.querySelector('#psiegesitinesfid').options.length = 1;
                    document.querySelector('#idcheminsheurfid').options.length = 1;
                    //document.querySelector('#lignesitinerairefid').value = '';
                    document.querySelector('#transitedepargare1fid').options.length = 0;
                    document.querySelector('#transitedepargare2fid').options.length = 0;
                    document.querySelector('#transitedepargare3fid').options.length = 0;
                    document.querySelector('#transitedepargare4fid').options.length = 0;
                    document.querySelector('#idcheminsfid').options.length = 1;
                    document.querySelector('#idchemins1fid').options.length = 1;
                    document.querySelector('#idchemins2fid').options.length = 1;
                    document.querySelector('#psiegesitines1fid').options.length = 1;
                    document.querySelector('#idcheminsheur1fid').options.length = 1;
                    document.querySelector('#psiegesitines2fid').options.length = 1;
                    document.querySelector('#idcheminsheur2fid').options.length = 1;
                    document.querySelector('#psiegesitines3fid').options.length = 1;
                    document.querySelector('#quartier1fid').options.length = 1;
                    document.querySelector('#quartier2fid').options.length = 1;
                    document.querySelector('#quartier3fid').options.length = 1;


                    let httpRequetesfid;
                    
                    if (window.XMLHttpRequest) {
                        httpRequetesfid = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpRequetesfid = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                        var depafi = document.querySelector('#depargarefid').value;
                        var arrfi = document.querySelector('#arrsgarefid').value;
                        var datedepartfi = document.querySelector('#date_depheurefid').value;
                        var dateactufi = document.querySelector('#actufid').value;
                                         
                        var post_lhdepfi = depafi.split('/');
                        var seltdepfi = post_lhdepfi[0];
                        var sougidfi = post_lhdepfi[1];
                        if(datedepartfi >= dateactufi)
                        {
                            let httpRequetesfi;
                            httpRequetesfi = new XMLHttpRequest();
                            httpRequetesfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheuresvente/${seltdepfi}-${arrfi}/${datedepartfi}/${sougidfi || '0'}`, true);
                            httpRequetesfi.onload = () => {
                                var payloadHvFi = {};
                                try { payloadHvFi = JSON.parse(httpRequetesfi.responseText) || {}; } catch (eHvFi) { payloadHvFi = {}; }
                                var heuresHvFi = Array.isArray(payloadHvFi.heures) ? payloadHvFi.heures : [];
                                window.__venteFiHasTransit = !!payloadHvFi.has_transit;
                                window.__venteFiLastHeuresVente = heuresHvFi;

                                document.querySelector('#smsdtfid').style.display = 'none';
                                document.querySelector('#date_depheurefid').style.color = "black";
                                document.querySelector('#date_depheurefid').style.border = "1px solid";

                                // Aligné guichet : lister les heures à la date ; transit seulement au choix d'une heure sans départ.
                                __venteFiShowDirectHourUi();
                                __venteFiFillHeuresVente(heuresHvFi);

                                window.__venteFiApplyTransitLegs = function (donitinesfi) {
                                                    donitinesfi = (typeof __venteFiNormalizeEtapes === 'function')
                                                        ? __venteFiNormalizeEtapes(donitinesfi) : donitinesfi;
                                                    if(donitinesfi === null || donitinesfi === '' || (typeof donitinesfi === 'object' && !Object.keys(donitinesfi).length))
                                                    {
                                                        document.querySelector('#depitin1fid').style.display = 'none';
                                                        document.querySelector('#depargareitine1fid').style.display = 'none';
                                                        document.querySelector('#iddeptrans1fid').style.display = 'none';
                                                        document.querySelector('#transitedepargare1fid').style.display = 'none';
                                                        document.querySelector('#iddeptrans2fid').style.display = 'none';
                                                        document.querySelector('#transitedepargare2fid').style.display = 'none';
                                                        document.querySelector('#iddeptrans3fid').style.display = 'none';
                                                        document.querySelector('#transitedepargare3fid').style.display = 'none';
                                                        document.querySelector('#iddeptrans4fid').style.display = 'none';
                                                        document.querySelector('#transitedepargare4fid').style.display = 'none';
                                                        document.querySelector('#arritin1fid').style.display = 'none';
                                                        document.querySelector('#arrsgareitine1fid').style.display = 'none';
                                                        document.querySelector('#arritin1fid').style.display = 'none';
                                                        document.querySelector('#arrsgareitine1fid').style.display = 'none';
                                                        document.querySelector('#heureitin1fid').style.display = 'none';
                                                        document.querySelector('#hdepartitine1fid').style.display = 'none';
                                                        document.querySelector('#lignesitinerairefid').style.display = 'none';
                                                        document.querySelector('#ligne1fid').style.display = 'none';
                                                        document.querySelector('#siegitine1fid').style.display = 'none';
                                                        document.querySelector('#psiegesitines1fid').style.display = 'none';
                                                        document.querySelector('#depitin2fid').style.display = 'none';
                                                        document.querySelector('#depargareitine2fid').style.display = 'none';
                                                        document.querySelector('#arritin2fid').style.display = 'none';
                                                        document.querySelector('#arrsgareitine2fid').style.display = 'none';
                                                        document.querySelector('#heureitin2fid').style.display = 'none';
                                                        document.querySelector('#hdepartitine2fid').style.display = 'none';
                                                        document.querySelector('#siegitine2fid').style.display = 'none';
                                                        document.querySelector('#psiegesitines2fid').style.display = 'none';
                                                        document.querySelector('#depitin3fid').style.display = 'none';
                                                        document.querySelector('#depargareitine3fid').style.display = 'none';
                                                        document.querySelector('#arritin3fid').style.display = 'none';
                                                        document.querySelector('#arrsgareitine3fid').style.display = 'none';
                                                        document.querySelector('#heureitin3fid').style.display = 'none';
                                                        document.querySelector('#hdepartitine3fid').style.display = 'none';
                                                        document.querySelector('#siegitine3fid').style.display = 'none';
                                                        document.querySelector('#psiegesitines3fid').style.display = 'none';
                                                        document.querySelector('#quartier1fid').style.display = 'none';
                                                        document.querySelector('#quartier2fid').style.display = 'none';
                                                        document.querySelector('#quartier3fid').style.display = 'none';
                                                        document.querySelector('#idquart1fid').style.display = 'none';
                                                        document.querySelector('#idquart2fid').style.display = 'none';
                                                        document.querySelector('#idquart3fid').style.display = 'none';

                                                        document.querySelector('#prix_axetransfid').style.display = 'none';
                                                        document.querySelector('#prix_axetransfid1').style.display = 'none';
                                                        document.querySelector('#prix_axetransitfid1').style.display = 'none';
                                                        document.querySelector('#prix_axetransitfid').style.display = 'none';
                                                        document.querySelector('#prix_axetransit1fid1').style.display = 'none';
                                                        document.querySelector('#prix_axetransit1fid').style.display = 'none';
                                                        document.querySelector('#prix_axetransit2fid1').style.display = 'none';
                                                        document.querySelector('#prix_axetransit2fid').style.display = 'none';
                                                        document.querySelector('#tranfid').style.display = 'none'; if (typeof __venteFiSetMainEscaleVisible === 'function') __venteFiSetMainEscaleVisible(true);
                                                        document.querySelector('#heureitinfid').style.display = 'none';
                                                        document.querySelector('#hdepartitinefid').style.display = 'none';
                                                        document.querySelector('#siegitinefid').style.display = 'none';
                                                        document.querySelector('#psiegesitinesfid').style.display = 'none';
                                                        document.querySelector('#hridfid').style.display = 'block';
                                                        document.querySelector('#hdepartfid').style.display = 'block';
                                                        document.querySelector('#sigidfid').style.display = 'block';
                                                        document.querySelector('#psiegesfid').style.display = 'block';
                                                        document.querySelector('#iddepfid').style.display = 'block';
                                                        document.querySelector('#depargarefid').style.display = 'block';
                                                        document.querySelector('#arridfid').style.display = 'block';
                                                        document.querySelector('#arrsgarefid').style.display = 'block';
                                                        document.querySelector('#prix_axefid1').style.display = 'block';
                                                        document.querySelector('#prix_axefid').style.display = 'block';
                                                    }
                                                    else
                                                    {
                                                        if (typeof __venteFiResetTransitFieldsBeforeApply === 'function') __venteFiResetTransitFieldsBeforeApply();
                                                        window.__venteFiCheminEtapes = donitinesfi;
                                                        window.__venteFiCheminCascadeStarted = false;
                                                        __venteFiClearTransitPrixFields();
                                                        if (Object.entries(donitinesfi).length >= 1) 
                                                        {
                                                            var i = Object.entries(donitinesfi).length;
                                                            
                                                            for (let key in Object.entries(donitinesfi)) 
                                                            {
                                                                
                                                                document.querySelector('#nbrtransfid').value = Object.entries(donitinesfi).length;;
                                                                if(i === 2){
                                                                    document.querySelector('#arritin1fid').style.display = 'block';
                                                                    document.querySelector('#idcheminsfid').style.display = 'block';
                                                                    document.querySelector('#heureitin1fid').style.display = 'block';
                                                                    document.querySelector('#idcheminsheurfid').style.display = 'block';
                                                                    document.querySelector('#siegitine1fid').style.display = 'block';
                                                                    document.querySelector('#psiegesitines1fid').style.display = 'block';
                                                                    document.querySelector('#quartier1fid').style.display = 'block';
                                                                    document.querySelector('#idquart1fid').style.display = 'block';
                                                                    document.querySelector('#iddeptrans1fid').style.display = 'block';
                                                                    document.querySelector('#transitedepargare1fid').style.display = 'block';
                                                                    document.querySelector('#iddeptrans2fid').style.display = 'block';
                                                                    document.querySelector('#transitedepargare2fid').style.display = 'block';
                                                                    document.querySelector('#prix_axetransfid').style.display = 'block';
                                                                    document.querySelector('#prix_axetransfid1').style.display = 'block';
                                                                    document.querySelector('#prix_axetransitfid1').style.display = 'block';
                                                                    document.querySelector('#prix_axetransitfid').style.display = 'block';
                                                                    
                                                                }
                                                                
                                                                if(i === 3){
                                                                    document.querySelector('#iddeptrans1fid').style.display = 'block';
                                                                    document.querySelector('#transitedepargare1fid').style.display = 'block';
                                                                    document.querySelector('#iddeptrans2fid').style.display = 'block';
                                                                    document.querySelector('#transitedepargare2fid').style.display = 'block';
                                                                    document.querySelector('#iddeptrans3fid').style.display = 'block';
                                                                    document.querySelector('#transitedepargare3fid').style.display = 'block';
                                                                    document.querySelector('#arritin1fid').style.display = 'block';
                                                                    document.querySelector('#idcheminsfid').style.display = 'block';
                                                                    document.querySelector('#heureitin1fid').style.display = 'block';
                                                                    document.querySelector('#idcheminsheurfid').style.display = 'block';
                                                                    document.querySelector('#siegitine1fid').style.display = 'block';
                                                                    document.querySelector('#psiegesitines1fid').style.display = 'block';
                                                                    document.querySelector('#idquart1fid').style.display = 'block';
                                                                    document.querySelector('#idquart2fid').style.display = 'block';
                                                                                                                 document.querySelector('#arritin2fid').style.display = 'block';
                                                                    document.querySelector('#idchemins1fid').style.display = 'block';
                                                                    document.querySelector('#heureitin2fid').style.display = 'block';
                                                                    document.querySelector('#idcheminsheur1fid').style.display = 'block';
                                                                    document.querySelector('#siegitine2fid').style.display = 'block';
                                                                    document.querySelector('#psiegesitines2fid').style.display = 'block';
                                                                    document.querySelector('#quartier1fid').style.display = 'block';
                                                                    document.querySelector('#quartier2fid').style.display = 'block';
                                                                    
                                                                    document.querySelector('#prix_axetransfid').style.display = 'block';
                                                                    document.querySelector('#prix_axetransfid1').style.display = 'block';
                                                                    document.querySelector('#prix_axetransitfid1').style.display = 'block';
                                                                    document.querySelector('#prix_axetransitfid').style.display = 'block';
                                                                    document.querySelector('#prix_axetransit1fid1').style.display = 'block';
                                                                    document.querySelector('#prix_axetransit1fid').style.display = 'block';
                                                                    }if(i === 4){
                                                                    
                                                                    document.querySelector('#iddeptrans1fid').style.display = 'block';
                                                                    document.querySelector('#transitedepargare1fid').style.display = 'block';
                                                                    document.querySelector('#iddeptrans2fid').style.display = 'block';
                                                                    document.querySelector('#transitedepargare2fid').style.display = 'block';
                                                                    document.querySelector('#iddeptrans3fid').style.display = 'block';
                                                                    document.querySelector('#transitedepargare3fid').style.display = 'block';
                                                                    document.querySelector('#iddeptrans4fid').style.display = 'block';
                                                                    document.querySelector('#transitedepargare4fid').style.display = 'block';
                                                                    document.querySelector('#arritin1fid').style.display = 'block';
                                                                    document.querySelector('#idcheminsfid').style.display = 'block';
                                                                    document.querySelector('#heureitin1fid').style.display = 'block';
                                                                    document.querySelector('#idcheminsheurfid').style.display = 'block';
                                                                    document.querySelector('#siegitine1fid').style.display = 'block';
                                                                    document.querySelector('#psiegesitines1fid').style.display = 'block';
                                                                    document.querySelector('#arritin2fid').style.display = 'block';
                                                                    document.querySelector('#idchemins1fid').style.display = 'block';
                                                                    document.querySelector('#heureitin2fid').style.display = 'block';
                                                                    document.querySelector('#idcheminsheur1fid').style.display = 'block';
                                                                    document.querySelector('#siegitine2fid').style.display = 'block';
                                                                    document.querySelector('#psiegesitines2fid').style.display = 'block';
                                                                    document.querySelector('#arritin3fid').style.display = 'block';
                                                                    document.querySelector('#idchemins2fid').style.display = 'block';
                                                                    document.querySelector('#heureitin3fid').style.display = 'block';
                                                                    document.querySelector('#idcheminsheur2fid').style.display = 'block';
                                                                    document.querySelector('#siegitine3fid').style.display = 'block';
                                                                    document.querySelector('#psiegesitines3fid').style.display = 'block';
                                                                    document.querySelector('#quartier1fid').style.display = 'block';
                                                                    document.querySelector('#quartier2fid').style.display = 'block';
                                                                    document.querySelector('#quartier3fid').style.display = 'block';
                                                                    document.querySelector('#idquart1fid').style.display = 'block';
                                                                    document.querySelector('#idquart2fid').style.display = 'block';
                                                                    document.querySelector('#idquart3fid').style.display = 'block';
                                                                    document.querySelector('#prix_axetransfid').style.display = 'block';
                                                                    document.querySelector('#prix_axetransfid1').style.display = 'block';
                                                                    document.querySelector('#prix_axetransitfid1').style.display = 'block';
                                                                    document.querySelector('#prix_axetransitfid').style.display = 'block';
                                                                    document.querySelector('#prix_axetransit1fid1').style.display = 'block';
                                                                    document.querySelector('#prix_axetransit1fid').style.display = 'block';
                                                                    document.querySelector('#prix_axetransit2fid1').style.display = 'block';
                                                                    document.querySelector('#prix_axetransit2fid').style.display = 'block';
                                                                

                                                                }
                                                                document.querySelector('#tranfid').style.display = 'block'; if (typeof __venteFiSetMainEscaleVisible === 'function') __venteFiSetMainEscaleVisible(false);
                                                                document.querySelector('#heureitinfid').style.display = 'block';
                                                                document.querySelector('#hdepartitinefid').style.display = 'block';
                                                                document.querySelector('#lignesitinerairefid').style.display = 'block';
                                                                document.querySelector('#ligne1fid').style.display = 'block';
                                                                document.querySelector('#siegitinefid').style.display = 'block';
                                                                document.querySelector('#psiegesitinesfid').style.display = 'block';
                                                                document.querySelector('#hridfid').style.display = 'none';
                                                                document.querySelector('#hdepartfid').style.display = 'none';
                                                                document.querySelector('#sigidfid').style.display = 'none';
                                                                document.querySelector('#psiegesfid').style.display = 'none';
                                                                document.querySelector('#iddepfid').style.display = 'none';
                                                                document.querySelector('#depargarefid').style.display = 'none';
                                                                document.querySelector('#arridfid').style.display = 'none';
                                                                document.querySelector('#arrsgarefid').style.display = 'none';

                                                                document.querySelector('#prix_axefid1').style.display = 'none';
                                                                document.querySelector('#prix_axefid').style.display = 'none';
                                                                var __hourAnchorLeg1Fi = (typeof window.__venteGetTransitAnchorHour === 'function'
                                                                    ? window.__venteGetTransitAnchorHour()
                                                                    : null) || window.__venteSelectedHour;
                                                                if ((!__hourAnchorLeg1Fi || !__hourAnchorLeg1Fi.heure) && donitinesfi[0] && donitinesfi[0]._graphe_heure) {
                                                                    __hourAnchorLeg1Fi = {
                                                                        value: (donitinesfi[0]._graphe_id_ligneheure != null
                                                                            ? String(donitinesfi[0]._graphe_id_ligneheure) + '/' + String(donitinesfi[0]._graphe_heure)
                                                                            : ''),
                                                                        heure: String(donitinesfi[0]._graphe_heure),
                                                                        hasProg: false
                                                                    };
                                                                }
                                                                if (__hourAnchorLeg1Fi && typeof window.__venteSetTransitAnchorFromHour === 'function') {
                                                                    window.__venteSetTransitAnchorFromHour(__hourAnchorLeg1Fi);
                                                                    __hourAnchorLeg1Fi = window.__venteGetTransitAnchorHour() || __hourAnchorLeg1Fi;
                                                                }
                                                                __venteFiFillLigne1Locked(donitinesfi[0], function (codeSel) {
                                                                    if (!codeSel) return;
                                                                    var hd = document.querySelector('#hdepartitinefid');
                                                                    if (hd) {
                                                                        hd.disabled = false;
                                                                        hd.removeAttribute('disabled');
                                                                        hd.options.length = 1;
                                                                    }
                                                                    var datedepart = document.querySelector('#date_depheurefid')
                                                                        ? document.querySelector('#date_depheurefid').value
                                                                        : (document.querySelector('#date_depheure') ? document.querySelector('#date_depheure').value : '');
                                                                    var anchorHhmmFi = '';
                                                                    if (__hourAnchorLeg1Fi) {
                                                                        anchorHhmmFi = (typeof window.__venteNormalizeHhmm === 'function')
                                                                            ? window.__venteNormalizeHhmm(__hourAnchorLeg1Fi.heure || __hourAnchorLeg1Fi.hhmm || '')
                                                                            : String(__hourAnchorLeg1Fi.heure || '');
                                                                    }
                                                                    var httpH = new XMLHttpRequest();
                                                                    var urlBaseFi = window.location.origin + `${APP_ROOT}/programmes/verifheureitine/${encodeURIComponent(codeSel)}/${encodeURIComponent(datedepart)}`;
                                                                    function __venteFiApplyLeg1Hours(infositin) {
                                                                        var anchorFi = (typeof window.__venteGetTransitAnchorHour === 'function'
                                                                            ? window.__venteGetTransitAnchorHour()
                                                                            : null) || __hourAnchorLeg1Fi || window.__venteSelectedHour;
                                                                        if (typeof window.__venteFillHeureItineSelect === 'function') {
                                                                            window.__venteFillHeureItineSelect(hd, infositin, anchorFi);
                                                                        } else if (hd && infositin && Object.entries(infositin).length >= 1) {
                                                                            hd.options.length = 1;
                                                                            for (var key in Object.entries(infositin)) {
                                                                                var opt = document.createElement('option');
                                                                                opt.value = `${infositin[key].id_ligneheure}/${infositin[key].heure}`;
                                                                                opt.setAttribute('data-heure', String(infositin[key].heure || ''));
                                                                                if (infositin[key].date_progr) opt.setAttribute('data-date-progr', String(infositin[key].date_progr).slice(0, 10));
                                                                                opt.innerHTML = `${infositin[key].heure}`;
                                                                                hd.add(opt);
                                                                            }
                                                                            if (typeof window.__venteSelectHourInSelect === 'function') {
                                                                                window.__venteSelectHourInSelect(hd, anchorFi, datedepart);
                                                                            }
                                                                        }
                                                                    }
                                                                    function __venteFiParseHourRows(txt) {
                                                                        var raw = JSON.parse(txt);
                                                                        if (Array.isArray(raw)) return raw;
                                                                        if (raw && typeof raw === 'object') {
                                                                            return Object.keys(raw).map(function (k) { return raw[k]; });
                                                                        }
                                                                        return [];
                                                                    }
                                                                    function __venteFiRowsNonEmpty(rows) {
                                                                        if (!rows) return false;
                                                                        if (Array.isArray(rows)) return rows.length > 0;
                                                                        if (typeof rows === 'object') return Object.keys(rows).length > 0;
                                                                        return false;
                                                                    }
                                                                    var urlH = urlBaseFi;
                                                                    if (anchorHhmmFi) urlH += '?heure=' + encodeURIComponent(anchorHhmmFi);
                                                                    httpH.open('GET', urlH, true);
                                                                    httpH.onload = function () {
                                                                        try {
                                                                            var infositin = __venteFiParseHourRows(httpH.responseText);
                                                                            if (anchorHhmmFi && !__venteFiRowsNonEmpty(infositin)) {
                                                                                var httpRetryFi = new XMLHttpRequest();
                                                                                httpRetryFi.open('GET', urlBaseFi, true);
                                                                                httpRetryFi.onload = function () {
                                                                                    try {
                                                                                        __venteFiApplyLeg1Hours(__venteFiParseHourRows(httpRetryFi.responseText));
                                                                                    } catch (eRFi) {}
                                                                                };
                                                                                httpRetryFi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpRetryFi.send();
                                                                                return;
                                                                            }
                                                                            __venteFiApplyLeg1Hours(infositin);
                                                                        } catch (eH) {}
                                                                    };
                                                                    httpH.setRequestHeader('Content-Type', 'application/json');
                                                                    httpH.send();
                                                                });
                                                            }
                                                            
                                                
                                                            if(i === 2)
                                                            {
                                                                document.querySelector('#itinecodesfid').value = `${donitinesfi[0].id_lignes}`;
                                                                    

                                                                var typgare1fi = (donitinesfi[0] && donitinesfi[0].code_itineraires) ? String(donitinesfi[0].code_itineraires) : (document.querySelector('#itinecodefid').value || '');
                                                                var odLeg1fi = __venteFiOdFromEtapeOrCode(donitinesfi[0], typgare1fi);
                                                                var seltypgare1fi = odLeg1fi.gaexp;
                                                                var typgareselfi = odLeg1fi.gadest;
                                                                    let httptypequart1fi;
                                                                    httptypequart1fi = new XMLHttpRequest();
                                                                    
                                                                    httptypequart1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgareselfi}`, true);
                                                                    httptypequart1fi.onload = () => 
                                                                    {
                                                                        const donqua1fi = JSON.parse(httptypequart1fi.responseText);
                                                                        if (donqua1fi == '') {
                                                                            document.querySelector('#quartier1fid').options.length = 1;
                                                                        }
                                                                        else{
                                                                            if (Object.entries(donqua1fi).length >= 1) {
                                                                                            
                                                                                for (let key in Object.entries(donqua1fi)) {
                                                                                    let optq = document.createElement('option');
                                                                                    optq.value = `${donqua1fi[key].nom_quartier}`;
                                                                                    optq.innerHTML = `${donqua1fi[key].nom_quartier}`;
                                                                                    document.querySelector('#quartier1fid').add(optq);
                                                                                }
                                                                            } else {
                                                                                document.querySelector('#quartier1fid').options.length = 1;
                                                                            }
                                                                        }
                                                                        

                                                                    };
                                                                    httptypequart1fi.setRequestHeader('Content-Type', 'application/json');
                                                                    httptypequart1fi.send();

                                                                let hrdepartinefi = document.querySelector('#hdepartitinefid');
                                                                if (hrdepartinefi !== null) {
                                                                    hrdepartinefi.onchange = () => 
                                                                    {
                                                                        __venteFiFillTransitDepart('#transitedepargare1fid', seltypgare1fi);

                                                                        document.querySelector('#psiegesitinesfid').options.length = 1;
                                                                        const httpRequestitfi = new XMLHttpRequest();
                                                                        const seleitinefi = document.querySelector('#hdepartitinefid')
                                                                            .options[document.querySelector('#hdepartitinefid').options.selectedIndex].value;

                                                                            var post_lhitinefi = seleitinefi.split('/');
                                                                            var selitinefi = post_lhitinefi[0];
                                                                            var lhselitinefi = post_lhitinefi[1];

                                                                            const dpt_dateitinefi = document.querySelector('#date_depheurefid').value;
                                                                            var itinproitfi = document.querySelector('#itinecodefid').value;
                                                                        httpRequestitfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifprog/${itinproitfi}/${dpt_dateitinefi}/${selitinefi}`, true);
                                                                        httpRequestitfi.onload = () => 
                                                                        {
                                                                            const donitfi = JSON.parse(httpRequestitfi.responseText);
                                                                                console.debug(`${typeof donitfi} - ${donitfi.attributes}`, console.memory);

                                                                                if (__venteFiHandleTransit1ProgList(donitfi, selitinefi, dpt_dateitinefi)) { return; }
                                                                                if (donitfi == '') 
                                                                                {
                                                                                    
                                                                                        let opt = document.createElement('option');
                                                                                        opt.value = '';                                                             
                                                                                    
                                                                                } 
                                                                                else 
                                                                                {       
                                                                                    if (Object.entries(donitfi).length >= 1) {
                                                                                        for (let key in Object.entries(donitfi)) {
                                                                                            document.querySelector('#programtransfid').value = `${donitfi[key].code_progr}`;
                                                                                            document.querySelector('#dateprtransfid').value = `${donitfi[key].date_progr}`;
                                                                                            document.querySelector('#deplignetransfid').value = `${donitfi[key].gareidentif}`;
                                                                                            document.querySelector('#intertrans1fid').value = `${donitfi[key].intervalle1}`;
                                                                                            document.querySelector('#intertrans2fid').value = `${donitfi[key].intervalle2}`;
                                                                                            document.querySelector('#ligntransfid').value = `${donitfi[key].ident_ligne}`;
                                                                                            document.querySelector('#nomitintransfid').value = `${donitfi[key].nom_ligne}`;
                                                                                            document.querySelector('#hertransfid').value = `${donitfi[key].heure}`;
                                                                                            document.querySelector('#catetransfid').value = `${donitfi[key].categori}`;

                                                                                        }
                                                                                        __venteFiMaybeStartCheminCascade();
                                                                                    } 
                                                                                    
                                                                                    
                                                                                    const seleitinefi = document.querySelector('#hdepartitinefid')
                                                                                    .options[document.querySelector('#hdepartitinefid').options.selectedIndex].value;

                                                                                    var post_lhitinefi = seleitinefi.split('/');
                                                                                    var selitinefi = post_lhitinefi[0];
                                                                                    var lhselitinefi = post_lhitinefi[1];
                                                                                    /*const httpPrixitfi = new XMLHttpRequest();
                                                                                    httpPrixitfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifpriprg/${selitinefi}/${(document.querySelector('#tarifattribfid') && document.querySelector('#tarifattribfid').value) || '1'}`, true);
                                                                                    httpPrixitfi.onload = () => 
                                                                                    {

                                                                                        const donprixitfi = JSON.parse(httpPrixitfi.responseText);
                                                                                        console.debug(`${typeof donprixitfi}-${donprixitfi.attributes}`, console.memory);
                                                                                        if (Object.entries(donprixitfi).length >= 1) {
                                                                                            for (let key in Object.entries(donprixitfi)) 
                                                                                            {
                                                                                                document.querySelector('#prix_axetransfid').value = `${donprixitfi[key].prix}`;
                                    
                                                                                            }
                                                                                        }
                                                                                    };
                                                                                    httpPrixitfi.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpPrixitfi.send();*/
                                                                                    
                                                                                    
                                                                                    
                                                                                    const httpRequetteitfi = new XMLHttpRequest();
                                                                                    const cdprogitfi = document.querySelector('#programtransfid').value;
                                                                                    const dbitfi = document.querySelector('#intertrans1fid').value;
                                                                                    const fnitfi = document.querySelector('#intertrans2fid').value;
                                                                                    const lgitfi = document.querySelector('#nomitintransfid').value;
                                                                                    const timitfi = document.querySelector('#hertransfid').value;
                                                                                    const dpt_dateitinefi = document.querySelector('#date_depheurefid').value;
                                                                                        httpRequetteitfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${cdprogitfi}/${dpt_dateitinefi}/${lgitfi}/${timitfi}/${dbitfi}/${fnitfi}`, true);
                                                                                    httpRequetteitfi.onload = () => {
                                                                                        const dattaitfi = JSON.parse(httpRequetteitfi.responseText);
                                                                                        console.debug(`${typeof dattaitfi} - ${dattaitfi.attributes}`, console.memory);
                                                                                        if (Object.entries(dattaitfi).length >= 1) {
                                                                                            for (let key in Object.entries(dattaitfi)) {
                                                                                                
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dattaitfi[key].siege_num}`;
                                                                                                opt.innerHTML = `${dattaitfi[key].siege_num}`;
                                                                                                document.querySelector('#psiegesitinesfid').add(opt);
                                                                                                
                                                                                            }
                                                                                            
                                                                                        } else {
                                                                                            document.querySelector('#psiegesitinesfid').options.length = 1;
                                                                                        }
                                                                                    };
                                                                                    httpRequetteitfi.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpRequetteitfi.send();

                                                                                }  
                                                                                
                                                                        };
                                                                        httpRequestitfi.setRequestHeader('Content-Type', 'application/json');
                                                                        httpRequestitfi.send();
                                                                         
                                                                    };
                                                                    
                                                            
                                                                }
                                                                progsiegestransfi = document.querySelector('#psiegesitinesfid');
                                                                if (progsiegestransfi !== null) {
                                                                    progsiegestransfi.onchange = () => 
                                                                    {

                                                                        gareidentiftransfi = document.querySelector('#deplignetransfid').value;
                                                                            __venteFiFillTransitDepart('#transitedepargare1fid', gareidentiftransfi);
                                                                        let httpSiegestransfi;
                                                                        httpSiegestransfi = new XMLHttpRequest();
                                                                        const sigstransfi = document.querySelector('#psiegesitinesfid')
                                                                        .options[document.querySelector('#psiegesitinesfid').options.selectedIndex].value;
                                                                        const prostransfi = document.querySelector('#programtransfid').value;

                                                                        httpSiegestransfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${prostransfi}/${sigstransfi}`, true);
                                                                        httpSiegestransfi.onload = () => 
                                                                        {
                                                                            const donsgetransfi = JSON.parse(httpSiegestransfi.responseText);
                                                                            console.debug(`${typeof donsgetransfi} - ${donsgetransfi.attributes}`, console.memory);
                                                                            if(donsgetransfi == '')
                                                                            {
                                                                                let httpSiegstransfi;
                                                                                httpSiegstransfi = new XMLHttpRequest();

                                                                                httpSiegstransfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${prostransfi}/${sigstransfi}`, true);
                                                                                httpSiegstransfi.onload = () => 
                                                                                {
                                                                                    const dongtransfi = JSON.parse(httpSiegstransfi.responseText);
                                                                                    document.querySelector('#messfid').style.display = 'none';
                                                                                    if (Object.entries(dongtransfi).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtransfi)) {
                                                                                                document.querySelector('#idtampotransfid').value = `${dongtransfi[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselecttransfid').value = `${dongtransfi[key].numsieg}`;
                                                                                            }
                                                                                        }
                                                                                };
                                                                                httpSiegstransfi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSiegstransfi.send();
                                                                            }
                                                                            else {
                                                                                document.querySelector('#psiegesitinesfid').value = '';     
                                                                                if (Object.entries(donsgetransfi).length >= 1)
                                                                                {
                                                                                    for (let key in Object.entries(donsgetransfi)) {
                                                                                        document.querySelector('#idtampotransfid').value = `${donsgetransfi[key].idtamp}`;                    
                                                                                        document.querySelector('#siegselecttransfid').value = `${donsgetransfi[key].numsieg}`;
                                                                                    }

                                                                                }
                                                                                document.querySelector('#messfid').style.display = 'block';
                                                                                document.querySelector('#erreurMessfid').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                        };
                                                                        httpSiegestransfi.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegestransfi.send();

                                                                    
                                                                    };
                                                                }

                                                                let progcheminfi = document.querySelector('#idcheminsfid');
                                                                if (progcheminfi !== null) 
                                                                {
                                                                    progcheminfi.onchange = () => 
                                                                    {
                                                                        document.querySelector('#idcheminsheurfid').options.length = 1;
                                                                        document.querySelector('#psiegesitines1fid').options.length = 1;
                                                                        
                                                                        let httpSiegescheminfi;
                                                                        httpSiegescheminfi = new XMLHttpRequest();
                                                                        
                                                                        const prostranscheminfi = document.querySelector('#idcheminsfid')
                                                                        .options[document.querySelector('#idcheminsfid').options.selectedIndex].value;

                                                                        var odLeg2fi = __venteFiOdFromEtapeOrCode(__venteFiEtapeAt(1), prostranscheminfi);
                                                                        var seltypgare2fi = odLeg2fi.gaexp;
                                                                        var typgaresel1fi = odLeg2fi.gadest;
 
                                                                        var datedepartfi = document.querySelector('#date_depheurefid').value;
                                                                        httpSiegescheminfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranscheminfi}/${datedepartfi}`, true);
                                                                        httpSiegescheminfi.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschemfi = JSON.parse(httpSiegescheminfi.responseText);
                                                                                    __venteFiFillCheminHeures('idcheminsheurfid', dongtranschemfi, 'tr2');
                                                                        };
                                                                        httpSiegescheminfi.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegescheminfi.send();

                                                                    };
                                                                        let prochemintrafi = document.querySelector('#idcheminsheurfid');
                                                                    if (prochemintrafi !== null)
                                                                        __venteFiWireCheminHeur('idcheminsheurfid', 'tr2'); if (false) prochemintrafi.onchange = () => 
                                                                        {  
                                                                            
                                                                            document.querySelector('#psiegesitines1fid').options.length = 1;

                                                                            const httpPrixittransitefi = new XMLHttpRequest();
                                                                                const transselitinefi = document.querySelector('#idcheminsheurfid')
                                                                            .options[document.querySelector('#idcheminsheurfid').options.selectedIndex].value;
                                                                                var post_transfi = transselitinefi.split('/');
                                                                            var itinetrasfi = post_transfi[0];
                                                                            var dbitrafi = post_transfi[1];
                                                                            var fnitrafi = post_transfi[2];
                                                                            var lhertrafi = post_transfi[3];
                                                                            var prixtrafi = post_transfi[4];

                                                                                httpPrixittransitefi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetrasfi}`, true);
                                                                                httpPrixittransitefi.onload = () => 
                                                                                {
                                                                                    const donprixitranfi = JSON.parse(httpPrixittransitefi.responseText);
                                                                                    console.debug(`${typeof donprixitranfi}-${donprixitranfi.attributes}`, console.memory);
                                                                                    if (Object.entries(donprixitranfi).length >= 1) {
                                                                                        for (let key in Object.entries(donprixitranfi)) 
                                                                                        {
                                                                                            document.querySelector('#catetransitfid').value = `${donprixitranfi[key].categori}`;
                                                                                            document.querySelector('#gidtransfid').value =  `${donprixitranfi[key].gareidentif}`;
                                                                                            document.querySelector('#nomitintrans1fid').value = `${donprixitranfi[key].nom_ligne}`;
                                                                                            document.querySelector('#ligntrans1fid').value = `${donprixitranfi[key].ident_ligne}`;

                                                                                        }
                                                                                    }
                                                                                };
                                                                                httpPrixittransitefi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpPrixittransitefi.send();
                                                                                
                                                                                      
                                                                                    
                                                                                const httpRequetteitrafi = new XMLHttpRequest();
                                                                        
                                                                                    httpRequetteitrafi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetrasfi}/${dbitrafi}/${fnitrafi}`, true);
                                                                                httpRequetteitrafi.onload = () => {
                                                                                    const dattaitrafi = JSON.parse(httpRequetteitrafi.responseText);
                                                                                    console.debug(`${typeof dattaitrafi} - ${dattaitrafi.attributes}`, console.memory);
                                                                                    if (Object.entries(dattaitrafi).length >= 1) {
                                                                                        for (let key in Object.entries(dattaitrafi)) {
                                                                                            
                                                                                            let opt = document.createElement('option');
                                                                                            opt.value = `${dattaitrafi[key].siege_num}`;
                                                                                            opt.innerHTML = `${dattaitrafi[key].siege_num}`;
                                                                                            document.querySelector('#psiegesitines1fid').add(opt);
                                                                                            
                                                                                        }
                                                                                        
                                                                                    } else {
                                                                                        document.querySelector('#psiegesitines1fid').options.length = 1;
                                                                                    }
                                                                                };
                                                                                httpRequetteitrafi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpRequetteitrafi.send();
                                                                        };

                                                                        progsieges1fi = document.querySelector('#psiegesitines1fid');
                                                                        if (progsieges1fi !== null) 
                                                                        {
                                                                            progsieges1fi.onchange = () => 
                                                                            {
                                                                                

                                                                                const transselitine1fi = document.querySelector('#idcheminsheurfid')
                                                                                .options[document.querySelector('#idcheminsheurfid').options.selectedIndex].value;
                                                                                var post_trans1fi = transselitine1fi.split('/');
                                                                                var itinetras1fi = post_trans1fi[0];
                                                                                
                                                                                gareidentiftrans2fi = document.querySelector('#gidtransfid').value;
                                                                                __venteFiFillTransitDepart('#transitedepargare2fid', gareidentiftrans2fi);
                                                                              
                                                                                let httpSieges1fi;
                                                                                httpSieges1fi = new XMLHttpRequest();
                                                                                const sigs1fi = document.querySelector('#psiegesitines1fid')
                                                                                .options[document.querySelector('#psiegesitines1fid').options.selectedIndex].value;
                                                                                //const pros1 = document.querySelector('#program').value;

                                                                                httpSieges1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetras1fi}/${sigs1fi}`, true);
                                                                                httpSieges1fi.onload = () => 
                                                                                {
                                                                                    const donsge1fi = JSON.parse(httpSieges1fi.responseText);
                                                                                    console.debug(`${typeof donsge1fi} - ${donsge1fi.attributes}`, console.memory);
                                                                                    if(donsge1fi == '')
                                                                                    {
                                                                                        let httpSiegs1fi;
                                                                                        httpSiegs1fi = new XMLHttpRequest();

                                                                                        httpSiegs1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetras1fi}/${sigs1fi}`, true);
                                                                                        httpSiegs1fi.onload = () => 
                                                                                        {
                                                                                            const dong1fi = JSON.parse(httpSiegs1fi.responseText);
                                                                                            document.querySelector('#messfid').style.display = 'none';
                                                                                            if (Object.entries(dong1fi).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(dong1fi)) {
                                                                                                        document.querySelector('#idtampo1fid').value = `${dong1fi[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselect1fid').value = `${dong1fi[key].numsieg}`;
                                                                                                    }
                                                                                                }
                                                                                        };
                                                                                        httpSiegs1fi.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegs1fi.send();
                                                                                    }
                                                                                    else {
                                                                                        document.querySelector('#psiegesitines1fid').value = '';     
                                                                                        if (Object.entries(donsge1fi).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(donsge1fi)) {
                                                                                                document.querySelector('#idtampo1fid').value = `${donsge1fi[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselect1fid').value = `${donsge1fi[key].numsieg}`;
                                                                                            }

                                                                                        }
                                                                                        document.querySelector('#messfid').style.display = 'block';
                                                                                        document.querySelector('#erreurMessfid').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                                };
                                                                                httpSieges1fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSieges1fi.send();

                                                                            };
                                                                        }
                                                                }               
                                                            }
                                                            //second itineraire
                                                            if(i === 3)
                                                            {

                                                                document.querySelector('#itinecodesfid').value = `${donitinesfi[0].id_lignes}`;
                                                               

                                                                var typgare1fi = (donitinesfi[0] && donitinesfi[0].code_itineraires) ? String(donitinesfi[0].code_itineraires) : (document.querySelector('#itinecodefid').value || '');
                                                                var odLeg1fi = __venteFiOdFromEtapeOrCode(donitinesfi[0], typgare1fi);
                                                                var seltypgare1fi = odLeg1fi.gaexp;
                                                                var typgareselfi = odLeg1fi.gadest;
                                                                    let httptypequart1fi;
                                                                    httptypequart1fi = new XMLHttpRequest();
                                                                    
                                                                    httptypequart1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgareselfi}`, true);
                                                                    httptypequart1fi.onload = () => 
                                                                    {
                                                                        const donqua1fi = JSON.parse(httptypequart1fi.responseText);
                                                                        if (donqua1fi == '') {
                                                                            document.querySelector('#quartier1fid').options.length = 1;
                                                                        }
                                                                        else{
                                                                            if (Object.entries(donqua1fi).length >= 1) {
                                                                                            
                                                                                for (let key in Object.entries(donqua1fi)) {
                                                                                    let optq = document.createElement('option');
                                                                                    optq.value = `${donqua1fi[key].nom_quartier}`;
                                                                                    optq.innerHTML = `${donqua1fi[key].nom_quartier}`;
                                                                                    document.querySelector('#quartier1fid').add(optq);
                                                                                }
                                                                            } else {
                                                                                document.querySelector('#quartier1fid').options.length = 1;
                                                                            }
                                                                        }
                                                                        

                                                                    };
                                                                    httptypequart1fi.setRequestHeader('Content-Type', 'application/json');
                                                                    httptypequart1fi.send();

                                                                let hrdepartine1fi = document.querySelector('#hdepartitinefid');
                                                                if (hrdepartine1fi !== null) {
                                                                    hrdepartine1fi.onchange = () => 
                                                                    {
                                                                        document.querySelector('#psiegesitinesfid').options.length = 1;
                                                                        const httpRequestit1fi = new XMLHttpRequest();
                                                                        const seleitine1fi = document.querySelector('#hdepartitinefid')
                                                                            .options[document.querySelector('#hdepartitinefid').options.selectedIndex].value;

                                                                            var post_lhitine1fi = seleitine1fi.split('/');
                                                                            var selitine1fi = post_lhitine1fi[0];
                                                                            var lhselitine1fi = post_lhitine1fi[1];

                                                                            const dpt_dateitine1fi = document.querySelector('#date_depheurefid').value;
                                                                            var itinproit1fi = document.querySelector('#itinecodefid').value;
                                                                        httpRequestit1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifprog/${itinproit1fi}/${dpt_dateitine1fi}/${selitine1fi}`, true);
                                                                        httpRequestit1fi.onload = () => 
                                                                        {
                                                                            const donit1fi = JSON.parse(httpRequestit1fi.responseText);
                                                                                console.debug(`${typeof donit1fi} - ${donit1fi.attributes}`, console.memory);

                                                                                if (__venteFiHandleTransit1ProgList(donit1fi, selitine1fi, dpt_dateitine1fi)) { return; }
                                                                                if (donit1fi == '') 
                                                                                {
                                                                                    
                                                                                        let opt = document.createElement('option');
                                                                                        opt.value = '';                                                             
                                                                                   
                                                                                    
                                                                                    
                                                                                } 
                                                                                else 
                                                                                {       
                                                                                    if (Object.entries(donit1fi).length >= 1) {
                                                                                        for (let key in Object.entries(donit1fi)) {
                                                                                            document.querySelector('#programtransfid').value = `${donit1fi[key].code_progr}`;
                                                                                            document.querySelector('#dateprtransfid').value = `${donit1fi[key].date_progr}`;
                                                                                            document.querySelector('#deplignetransfid').value = `${donit1fi[key].gareidentif}`;
                                                                                            document.querySelector('#intertrans1fid').value = `${donit1fi[key].intervalle1}`;
                                                                                            document.querySelector('#intertrans2fid').value = `${donit1fi[key].intervalle2}`;
                                                                                            document.querySelector('#ligntransfid').value = `${donit1fi[key].ident_ligne}`;
                                                                                            document.querySelector('#nomitintransfid').value = `${donit1fi[key].nom_ligne}`;
                                                                                            document.querySelector('#hertransfid').value = `${donit1fi[key].heure}`;
                                                                                            document.querySelector('#catetransfid').value = `${donit1fi[key].categori}`;

                                                                                        }
                                                                                        __venteFiMaybeStartCheminCascade();
                                                                                    } 
                                                                                    
                                                                                    
                                                                                    const seleitinefi = document.querySelector('#hdepartitinefid')
                                                                                    .options[document.querySelector('#hdepartitinefid').options.selectedIndex].value;

                                                                                    var post_lhitinefi = seleitinefi.split('/');
                                                                                    var selitinefi = post_lhitinefi[0];
                                                                                    var lhselitinefi = post_lhitinefi[1];
                                                                                    
                                                                                    const httpRequetteitfi = new XMLHttpRequest();
                                                                                    const cdprogitfi = document.querySelector('#programtransfid').value;
                                                                                    const dbitfi = document.querySelector('#intertrans1fid').value;
                                                                                    const fnitfi = document.querySelector('#intertrans2fid').value;
                                                                                    const lgitfi = document.querySelector('#nomitintransfid').value;
                                                                                    const timitfi = document.querySelector('#hertransfid').value;
                                                                                    const dpt_dateitinefi = document.querySelector('#date_depheurefid').value;
                                                                                        httpRequetteitfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${cdprogitfi}/${dpt_dateitinefi}/${lgitfi}/${timitfi}/${dbitfi}/${fnitfi}`, true);
                                                                                    httpRequetteitfi.onload = () => {
                                                                                        const dattaitfi = JSON.parse(httpRequetteitfi.responseText);
                                                                                        console.debug(`${typeof dattaitfi} - ${dattaitfi.attributes}`, console.memory);
                                                                                        if (Object.entries(dattaitfi).length >= 1) {
                                                                                            for (let key in Object.entries(dattaitfi)) {
                                                                                                
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dattaitfi[key].siege_num}`;
                                                                                                opt.innerHTML = `${dattaitfi[key].siege_num}`;
                                                                                                document.querySelector('#psiegesitinesfid').add(opt);
                                                                                                
                                                                                            }
                                                                                            
                                                                                        } else {
                                                                                            document.querySelector('#psiegesitinesfid').options.length = 1;
                                                                                        }
                                                                                    };
                                                                                    httpRequetteitfi.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpRequetteitfi.send();

                                                                                }  
                                                                                
                                                                        };
                                                                        httpRequestit1fi.setRequestHeader('Content-Type', 'application/json');
                                                                        httpRequestit1fi.send();
                                                                         
                                                                    };
                                                                    
                                                            
                                                                }
                                                                let progsiegestransfi = document.querySelector('#psiegesitinesfid');
                                                                if (progsiegestransfi !== null) {
                                                                    progsiegestransfi.onchange = () => 
                                                                    {

                                                                        const gareidentiftrans1fi = document.querySelector('#deplignetransfid').value;
                                                                        __venteFiFillTransitDepart('#transitedepargare1fid', gareidentiftrans1fi);
                                                                        let httpSiegestrans1fi;
                                                                        httpSiegestrans1fi = new XMLHttpRequest();
                                                                        const sigstransfi = document.querySelector('#psiegesitinesfid')
                                                                        .options[document.querySelector('#psiegesitinesfid').options.selectedIndex].value;
                                                                        const prostransfi = document.querySelector('#programtransfid').value;

                                                                        httpSiegestrans1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${prostransfi}/${sigstransfi}`, true);
                                                                        httpSiegestrans1fi.onload = () => 
                                                                        {
                                                                            const donsgetransfi = JSON.parse(httpSiegestrans1fi.responseText);
                                                                            console.debug(`${typeof donsgetransfi} - ${donsgetransfi.attributes}`, console.memory);
                                                                            if(donsgetransfi == '')
                                                                            {
                                                                                let httpSiegstransfi;
                                                                                httpSiegstransfi = new XMLHttpRequest();

                                                                                httpSiegstransfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${prostransfi}/${sigstransfi}`, true);
                                                                                httpSiegstransfi.onload = () => 
                                                                                {
                                                                                    const dongtransfi = JSON.parse(httpSiegstransfi.responseText);
                                                                                    document.querySelector('#messfid').style.display = 'none';
                                                                                    if (Object.entries(dongtransfi).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtransfi)) {
                                                                                                document.querySelector('#idtampotransfid').value = `${dongtransfi[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselecttransfid').value = `${dongtransfi[key].numsieg}`;
                                                                                            }
                                                                                        }
                                                                                };
                                                                                httpSiegstransfi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSiegstransfi.send();
                                                                            }
                                                                            else {
                                                                                document.querySelector('#psiegesitinesfid').value = '';     
                                                                                if (Object.entries(donsgetransfi).length >= 1)
                                                                                {
                                                                                    for (let key in Object.entries(donsgetransfi)) {
                                                                                        document.querySelector('#idtampotransfid').value = `${donsgetransfi[key].idtamp}`;                    
                                                                                        document.querySelector('#siegselecttransfid').value = `${donsgetransfi[key].numsieg}`;
                                                                                    }

                                                                                }
                                                                                document.querySelector('#messfid').style.display = 'block';
                                                                                document.querySelector('#erreurMessfid').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                        };
                                                                        httpSiegestrans1fi.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegestrans1fi.send();

                                                                    
                                                                    };
                                                                }
                                                                //premier transite
                                                                let progcheminfi = document.querySelector('#idcheminsfid');
                                                                if (progcheminfi !== null) 
                                                                {
                                                                    progcheminfi.onchange = () => 
                                                                    {
                                                                        document.querySelector('#idcheminsheurfid').options.length = 1;
                                                                        document.querySelector('#psiegesitines1fid').options.length = 1;

                                                                        const prostranscheminfi = document.querySelector('#idcheminsfid')
                                                                        .options[document.querySelector('#idcheminsfid').options.selectedIndex].value;

                                                                        var odLeg2fi = __venteFiOdFromEtapeOrCode(__venteFiEtapeAt(1), prostranscheminfi);
                                                                        var seltypgare2fi = odLeg2fi.gaexp;
                                                                        var typgaresel1fi = odLeg2fi.gadest;
                                                                        let httptypequart2fi;
                                                                        httptypequart2fi = new XMLHttpRequest();
                                                                        
                                                                        httptypequart2fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgaresel1fi}`, true);
                                                                        httptypequart2fi.onload = () => 
                                                                        {
                                                                            const donqua2fi = JSON.parse(httptypequart2fi.responseText);
                                                                            if (donqua2fi == '') {
                                                                                document.querySelector('#quartier2fid').options.length = 1;
                                                                            }
                                                                            else{
                                                                                if (Object.entries(donqua2fi).length >= 1) {
                                                                                                
                                                                                    for (let key in Object.entries(donqua2fi)) {
                                                                                        let optq1 = document.createElement('option');
                                                                                        optq1.value = `${donqua2fi[key].nom_quartier}`;
                                                                                        optq1.innerHTML = `${donqua2fi[key].nom_quartier}`;
                                                                                        document.querySelector('#quartier2fid').add(optq1);
                                                                                    }
                                                                                } else {
                                                                                    document.querySelector('#quartier2fid').options.length = 1;
                                                                                }
                                                                            }
                                                                            

                                                                        };
                                                                        httptypequart2fi.setRequestHeader('Content-Type', 'application/json');
                                                                        httptypequart2fi.send();

                                                                        let httpSiegescheminfi;
                                                                        httpSiegescheminfi = new XMLHttpRequest();

                                                                        var datedepartfi = document.querySelector('#date_depheurefid').value;
                                                                        
                                                                        httpSiegescheminfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranscheminfi}/${datedepartfi}`, true);
                                                                        httpSiegescheminfi.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschemfi = JSON.parse(httpSiegescheminfi.responseText);
                                                                                    __venteFiFillCheminHeures('idcheminsheurfid', dongtranschemfi, 'tr2');
                                                                        };
                                                                        httpSiegescheminfi.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegescheminfi.send();

                                                                    };
                                                                       let prochemintrafi = document.querySelector('#idcheminsheurfid');
                                                                    if (prochemintrafi !== null)
                                                                        __venteFiWireCheminHeur('idcheminsheurfid', 'tr2'); if (false) prochemintrafi.onchange = () => 
                                                                        {  
                                                                           
                                                                            document.querySelector('#psiegesitines1fid').options.length = 1;

                                                                            const httpPrixittransitefi = new XMLHttpRequest();
                                                                                const transselitinefi = document.querySelector('#idcheminsheurfid')
                                                                            .options[document.querySelector('#idcheminsheurfid').options.selectedIndex].value;
                                                                                var post_transfi = transselitinefi.split('/');
                                                                            var itinetrasfi = post_transfi[0];
                                                                            var dbitrafi = post_transfi[1];
                                                                            var fnitrafi = post_transfi[2];
                                                                            var lhertrafi = post_transfi[3];
                                                                            var prixtrafi = post_transfi[4];

                                                                                httpPrixittransitefi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetrasfi}`, true);
                                                                                httpPrixittransitefi.onload = () => 
                                                                                {
                                                                                    const donprixitranfi = JSON.parse(httpPrixittransitefi.responseText);
                                                                                    console.debug(`${typeof donprixitranfi}-${donprixitranfi.attributes}`, console.memory);
                                                                                    if (Object.entries(donprixitranfi).length >= 1) {
                                                                                        for (let key in Object.entries(donprixitranfi)) 
                                                                                        {
                                                                                            document.querySelector('#catetransitfid').value = `${donprixitranfi[key].categori}`;
                                                                                            document.querySelector('#gidtransfid').value =  `${donprixitranfi[key].gareidentif}`;
                                                                                            document.querySelector('#nomitintrans1fid').value = `${donprixitranfi[key].nom_ligne}`; 
                                                                                        document.querySelector('#ligntrans1fid').value = `${donprixitranfi[key].ident_ligne}`;
                                                                                        }
                                                                                    }
                                                                                };
                                                                                httpPrixittransitefi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpPrixittransitefi.send();


                                                                                

                                                                                const httpRequetteitrafi = new XMLHttpRequest();
                                                                        
                                                                                    httpRequetteitrafi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetrasfi}/${dbitrafi}/${fnitrafi}`, true);
                                                                                httpRequetteitrafi.onload = () => {
                                                                                    const dattaitrafi = JSON.parse(httpRequetteitrafi.responseText);
                                                                                    console.debug(`${typeof dattaitrafi} - ${dattaitrafi.attributes}`, console.memory);
                                                                                    if (Object.entries(dattaitrafi).length >= 1) {
                                                                                        for (let key in Object.entries(dattaitrafi)) {
                                                                                            
                                                                                            let opt = document.createElement('option');
                                                                                            opt.value = `${dattaitrafi[key].siege_num}`;
                                                                                            opt.innerHTML = `${dattaitrafi[key].siege_num}`;
                                                                                            document.querySelector('#psiegesitines1fid').add(opt);
                                                                                            
                                                                                        }
                                                                                        
                                                                                    } else {
                                                                                        document.querySelector('#psiegesitines1fid').options.length = 1;
                                                                                    }
                                                                                };
                                                                                httpRequetteitrafi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpRequetteitrafi.send();
                                                                        };

                                                                        let progsieges1fi = document.querySelector('#psiegesitines1fid');
                                                                        if (progsieges1fi !== null) 
                                                                        {
                                                                            progsieges1fi.onchange = () => 
                                                                            {

                                                                              const  gareidentiftrans2fi = document.querySelector('#gidtransfid').value;
                                                                                    __venteFiFillTransitDepart('#transitedepargare2fid', gareidentiftrans2fi);
                                                                                 const transselitine1fi = document.querySelector('#idcheminsheurfid')
                                                                                .options[document.querySelector('#idcheminsheurfid').options.selectedIndex].value;
                                                                                var post_trans1fi = transselitine1fi.split('/');
                                                                                var itinetras1fi = post_trans1fi[0];
                                                                    
                                                                                

                                                                                let httpSieges1fi;
                                                                                httpSieges1fi = new XMLHttpRequest();
                                                                                const sigs1fi = document.querySelector('#psiegesitines1fid')
                                                                                .options[document.querySelector('#psiegesitines1fid').options.selectedIndex].value;

                                                                                httpSieges1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetras1fi}/${sigs1fi}`, true);
                                                                                httpSieges1fi.onload = () => 
                                                                                {
                                                                                    const donsge1fi = JSON.parse(httpSieges1fi.responseText);
                                                                                    console.debug(`${typeof donsge1fi} - ${donsge1fi.attributes}`, console.memory);
                                                                                    if(donsge1fi == '')
                                                                                    {
                                                                                        let httpSiegs1fi;
                                                                                        httpSiegs1fi = new XMLHttpRequest();

                                                                                        httpSiegs1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetras1fi}/${sigs1fi}`, true);
                                                                                        httpSiegs1fi.onload = () => 
                                                                                        {
                                                                                            const dong1fi = JSON.parse(httpSiegs1fi.responseText);
                                                                                            document.querySelector('#messfid').style.display = 'none';
                                                                                            if (Object.entries(dong1fi).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(dong1fi)) {
                                                                                                        document.querySelector('#idtampo1fid').value = `${dong1fi[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselect1fid').value = `${dong1fi[key].numsieg}`;
                                                                                                    }
                                                                                                }
                                                                                        };
                                                                                        httpSiegs1fi.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegs1fi.send();
                                                                                    }
                                                                                    else {
                                                                                        document.querySelector('#psiegesitines1fid').value = '';     
                                                                                        if (Object.entries(donsge1fi).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(donsge1fi)) {
                                                                                                document.querySelector('#idtampo1fid').value = `${donsge1fi[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselect1fid').value = `${donsge1fi[key].numsieg}`;
                                                                                            }

                                                                                        }
                                                                                        document.querySelector('#messfid').style.display = 'block';
                                                                                        document.querySelector('#erreurMessfid').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                                };
                                                                                httpSieges1fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSieges1fi.send();

                                                                            };
                                                                        }
                                                                }
                                                                let progchemin1fi = document.querySelector('#idchemins1fid');
                                                                if (progchemin1fi !== null) 
                                                                {
                                                                    progchemin1fi.onchange = () => 
                                                                    {
                                                                        document.querySelector('#idcheminsheur1fid').options.length = 1;
                                                                        document.querySelector('#psiegesitines2fid').options.length = 1;
                                                                       
                                                                        const prostranschemin32fi = document.querySelector('#idchemins1fid')
                                                                        .options[document.querySelector('#idchemins1fid').options.selectedIndex].value;

                                                                        var odLeg3fi = __venteFiOdFromEtapeOrCode(__venteFiEtapeAt(2), prostranschemin32fi);
                                                                        var seltypgare32fi = odLeg3fi.gaexp;
                                                                        var typgaresel31fi = odLeg3fi.gadest;
                                                                        
                                                                        let httpSiegeschemin1fi;
                                                                        httpSiegeschemin1fi = new XMLHttpRequest();

                                                                        var datedepartfi = document.querySelector('#date_depheurefid').value;
                                                                        const prostranschemin1fi = document.querySelector('#idchemins1fid')
                                                                        .options[document.querySelector('#idchemins1fid').options.selectedIndex].value;

                                                                        httpSiegeschemin1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranschemin1fi}/${datedepartfi}`, true);
                                                                        httpSiegeschemin1fi.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschem1fi = JSON.parse(httpSiegeschemin1fi.responseText);
                                                                                    __venteFiFillCheminHeures('idcheminsheur1fid', dongtranschem1fi, 'tr3');
                                                                        };
                                                                        httpSiegeschemin1fi.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegeschemin1fi.send();

                                                                    };
                                                                      let prochemintra1fi = document.querySelector('#idcheminsheur1fid');
                                                                    if (prochemintra1fi !== null)
                                                                        __venteFiWireCheminHeur('idcheminsheur1fid', 'tr3'); if (false) prochemintra1fi.onchange = () => 
                                                                        {  
                                                                           
                                                                            document.querySelector('#psiegesitines2fid').options.length = 1;
                                                                       

                                                                            const httpPrixittransite1fi = new XMLHttpRequest();
                                                                                const transselitine1fi = document.querySelector('#idcheminsheur1fid')
                                                                            .options[document.querySelector('#idcheminsheur1fid').options.selectedIndex].value;
                                                                                var post_trans1fi = transselitine1fi.split('/');
                                                                            var itinetras1fi = post_trans1fi[0];
                                                                            var dbitra1fi = post_trans1fi[1];
                                                                            var fnitra1fi = post_trans1fi[2];
                                                                            var lhertra1fi = post_trans1fi[3];
                                                                            var prixtra1fi = post_trans1fi[4];

                                                                                httpPrixittransite1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetras1fi}`, true);
                                                                                httpPrixittransite1fi.onload = () => 
                                                                                {
                                                                                    const donprixitran1fi = JSON.parse(httpPrixittransite1fi.responseText);
                                                                                    if (Object.entries(donprixitran1fi).length >= 1) {
                                                                                        for (let key in Object.entries(donprixitran1fi)) 
                                                                                        {
                                                                                            document.querySelector('#catetransit1fid').value = `${donprixitran1fi[key].categori}`;
                                                                                            document.querySelector('#gidtrans1fid').value =  `${donprixitran1fi[key].gareidentif}`;
                                                                                            document.querySelector('#nomitintrans2fid').value = `${donprixitran1fi[key].nom_ligne}`;
                                                                                            document.querySelector('#ligntrans2fid').value = `${donprixitran1fi[key].ident_ligne}`;
                                                                                        }
                                                                                    }
                                                                                };
                                                                                httpPrixittransite1fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpPrixittransite1fi.send();
                                                                      
                                                                              
                                                                               
                                                                                const httpRequetteitra1fi = new XMLHttpRequest();
                                                                        
                                                                                    httpRequetteitra1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetras1fi}/${dbitra1fi}/${fnitra1fi}`, true);
                                                                                httpRequetteitra1fi.onload = () => {
                                                                                    const dattaitra1fi = JSON.parse(httpRequetteitra1fi.responseText);
                                                                                    console.debug(`${typeof dattaitra1fi} - ${dattaitra1fi.attributes}`, console.memory);
                                                                                    if (Object.entries(dattaitra1fi).length >= 1) {
                                                                                        for (let key in Object.entries(dattaitra1fi)) {
                                                                                            
                                                                                            let opt = document.createElement('option');
                                                                                            opt.value = `${dattaitra1fi[key].siege_num}`;
                                                                                            opt.innerHTML = `${dattaitra1fi[key].siege_num}`;
                                                                                            document.querySelector('#psiegesitines2fid').add(opt);
                                                                                            
                                                                                        }
                                                                                        
                                                                                    } else {
                                                                                        document.querySelector('#psiegesitines2fid').options.length = 1;
                                                                                    }
                                                                                };
                                                                                httpRequetteitra1fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpRequetteitra1fi.send();
                                                                        };

                                                                        let progsieges2fi = document.querySelector('#psiegesitines2fid');
                                                                        if (progsieges2fi !== null) 
                                                                        {
                                                                            progsieges2fi.onchange = () => 
                                                                            {
                                                                                    const transselitine2fi = document.querySelector('#idcheminsheur1fid')
                                                                                .options[document.querySelector('#idcheminsheur1fid').options.selectedIndex].value;
                                                                                var post_trans2fi = transselitine2fi.split('/');
                                                                                var itinetras2fi = post_trans2fi[0];
                                                                                    
                                                                                    const gareidentiftrans4fi = document.querySelector('#gidtrans1fid').value;
                                                                                    __venteFiFillTransitDepart('#transitedepargare3fid', gareidentiftrans4fi);

                                                                                let httpSieges2fi;
                                                                                httpSieges2fi = new XMLHttpRequest();
                                                                                const sigs2fi = document.querySelector('#psiegesitines2fid')
                                                                                .options[document.querySelector('#psiegesitines2fid').options.selectedIndex].value;

                                                                                httpSieges2fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetras2fi}/${sigs2fi}`, true);
                                                                                httpSieges2fi.onload = () => 
                                                                                {
                                                                                    const donsge2fi = JSON.parse(httpSieges2fi.responseText);
                                                                                    if(donsge2fi == '')
                                                                                    {
                                                                                        let httpSiegs2fi;
                                                                                        httpSiegs2fi = new XMLHttpRequest();

                                                                                        httpSiegs2fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetras2fi}/${sigs2fi}`, true);
                                                                                        httpSiegs2fi.onload = () => 
                                                                                        {
                                                                                            const dong2fi = JSON.parse(httpSiegs2fi.responseText);
                                                                                            document.querySelector('#messfid').style.display = 'none';
                                                                                            if (Object.entries(dong2fi).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(dong2fi)) {
                                                                                                        document.querySelector('#idtampo2fid').value = `${dong2fi[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselect2fid').value = `${dong2fi[key].numsieg}`;
                                                                                                    }
                                                                                                }
                                                                                        };
                                                                                        httpSiegs2fi.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegs2fi.send();
                                                                                    }
                                                                                    else {
                                                                                        document.querySelector('#psiegesitines2fid').value = '';     
                                                                                        if (Object.entries(donsge2fi).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(donsge2fi)) {
                                                                                                document.querySelector('#idtampo2fid').value = `${donsge2fi[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselect2fid').value = `${donsge2fi[key].numsieg}`;
                                                                                            }

                                                                                        }
                                                                                        document.querySelector('#messfid').style.display = 'block';
                                                                                        document.querySelector('#erreurMessfid').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                                };
                                                                                httpSieges2fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSieges2fi.send();

                                                                            };
                                                                        }
                                                                }               
                                                            }

                                                            //troisieme itineraire
                                                            if(i === 4)
                                                            {
                                                                document.querySelector('#itinecodesfid').value = `${donitinesfi[0].id_lignes}`;

                                                                    var typgare1fi = (donitinesfi[0] && donitinesfi[0].code_itineraires) ? String(donitinesfi[0].code_itineraires) : (document.querySelector('#itinecodefid').value || '');
                                                                var odLeg1fi = __venteFiOdFromEtapeOrCode(donitinesfi[0], typgare1fi);
                                                                var seltypgare1fi = odLeg1fi.gaexp;
                                                                var typgareselfi = odLeg1fi.gadest;
                                                                    let httptypequart1fi;
                                                                    httptypequart1fi = new XMLHttpRequest();
                                                                    
                                                                    httptypequart1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgareselfi}`, true);
                                                                    httptypequart1fi.onload = () => 
                                                                    {
                                                                        const donqua1fi = JSON.parse(httptypequart1fi.responseText);
                                                                        if (donqua1fi == '') {
                                                                            document.querySelector('#quartier1fid').options.length = 1;
                                                                        }
                                                                        else{
                                                                            if (Object.entries(donqua1fi).length >= 1) {
                                                                                            
                                                                                for (let key in Object.entries(donqua1fi)) {
                                                                                    let optq = document.createElement('option');
                                                                                    optq.value = `${donqua1fi[key].nom_quartier}`;
                                                                                    optq.innerHTML = `${donqua1fi[key].nom_quartier}`;
                                                                                    document.querySelector('#quartier1fid').add(optq);
                                                                                }
                                                                            } else {
                                                                                document.querySelector('#quartier1fid').options.length = 1;
                                                                            }
                                                                        }
                                                                        

                                                                    };
                                                                    httptypequart1fi.setRequestHeader('Content-Type', 'application/json');
                                                                    httptypequart1fi.send();

                                                                let hrdepartine1fi = document.querySelector('#hdepartitinefid');
                                                                if (hrdepartine1fi !== null) {
                                                                    hrdepartine1fi.onchange = () => 
                                                                    {
                                                                        document.querySelector('#psiegesitinesfid').options.length = 1;
                                                                        const httpRequestit1fi = new XMLHttpRequest();
                                                                        const seleitine1fi = document.querySelector('#hdepartitinefid')
                                                                            .options[document.querySelector('#hdepartitinefid').options.selectedIndex].value;

                                                                            var post_lhitine1fi = seleitine1fi.split('/');
                                                                            var selitine1fi = post_lhitine1fi[0];
                                                                            var lhselitine1fi = post_lhitine1fi[1];

                                                                            const dpt_dateitine1fi = document.querySelector('#date_depheurefid').value;
                                                                            var itinproit1fi = document.querySelector('#itinecodefid').value;
                                                                        httpRequestit1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifprog/${itinproit1fi}/${dpt_dateitine1fi}/${selitine1fi}`, true);
                                                                        httpRequestit1fi.onload = () => 
                                                                        {
                                                                            const donit1fi = JSON.parse(httpRequestit1fi.responseText);
                                                                                console.debug(`${typeof donit1fi} - ${donit1fi.attributes}`, console.memory);

                                                                                if (__venteFiHandleTransit1ProgList(donit1fi, selitine1fi, dpt_dateitine1fi)) { return; }
                                                                                if (donit1fi == '') 
                                                                                {
                                                                                    
                                                                                        let opt = document.createElement('option');
                                                                                        opt.value = '';                                                             
                                                                                   
                                                                                    
                                                                                    
                                                                                } 
                                                                                else 
                                                                                {       
                                                                                    if (Object.entries(donit1fi).length >= 1) {
                                                                                        for (let key in Object.entries(donit1fi)) {
                                                                                            document.querySelector('#programtransfid').value = `${donit1fi[key].code_progr}`;
                                                                                            document.querySelector('#dateprtransfid').value = `${donit1fi[key].date_progr}`;
                                                                                            document.querySelector('#deplignetransfid').value = `${donit1fi[key].gareidentif}`;
                                                                                            document.querySelector('#intertrans1fid').value = `${donit1fi[key].intervalle1}`;
                                                                                            document.querySelector('#intertrans2fid').value = `${donit1fi[key].intervalle2}`;
                                                                                            document.querySelector('#ligntransfid').value = `${donit1fi[key].ident_ligne}`;
                                                                                            document.querySelector('#nomitintransfid').value = `${donit1fi[key].nom_ligne}`;
                                                                                            document.querySelector('#hertransfid').value = `${donit1fi[key].heure}`;
                                                                                            document.querySelector('#catetransfid').value = `${donit1fi[key].categori}`;

                                                                                        }
                                                                                        __venteFiMaybeStartCheminCascade();
                                                                                    } 
                                                                                    
                                                                                    
                                                                                    const seleitinefi = document.querySelector('#hdepartitinefid')
                                                                                    .options[document.querySelector('#hdepartitinefid').options.selectedIndex].value;

                                                                                    var post_lhitinefi = seleitinefi.split('/');
                                                                                    var selitinefi = post_lhitinefi[0];
                                                                                    var lhselitinefi = post_lhitinefi[1];

                                                                                    

                                                                                    

                                                                                    const httpRequetteitfi = new XMLHttpRequest();
                                                                                    const cdprogitfi = document.querySelector('#programtransfid').value;
                                                                                    const dbitfi = document.querySelector('#intertrans1fid').value;
                                                                                    const fnitfi = document.querySelector('#intertrans2fid').value;
                                                                                    const lgitfi = document.querySelector('#nomitintransfid').value;
                                                                                    const timitfi = document.querySelector('#hertransfid').value;
                                                                                    const dpt_dateitinefi = document.querySelector('#date_depheurefid').value;
                                                                                        httpRequetteitfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${cdprogitfi}/${dpt_dateitinefi}/${lgitfi}/${timitfi}/${dbitfi}/${fnitfi}`, true);
                                                                                    httpRequetteitfi.onload = () => {
                                                                                        const dattaitfi = JSON.parse(httpRequetteitfi.responseText);
                                                                                        console.debug(`${typeof dattaitfi} - ${dattaitfi.attributes}`, console.memory);
                                                                                        if (Object.entries(dattaitfi).length >= 1) {
                                                                                            for (let key in Object.entries(dattaitfi)) {
                                                                                                
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dattaitfi[key].siege_num}`;
                                                                                                opt.innerHTML = `${dattaitfi[key].siege_num}`;
                                                                                                document.querySelector('#psiegesitinesfid').add(opt);
                                                                                                
                                                                                            }
                                                                                            
                                                                                        } else {
                                                                                            document.querySelector('#psiegesitinesfid').options.length = 1;
                                                                                        }
                                                                                    };
                                                                                    httpRequetteitfi.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpRequetteitfi.send();

                                                                                }  
                                                                                
                                                                        };
                                                                        httpRequestit1fi.setRequestHeader('Content-Type', 'application/json');
                                                                        httpRequestit1fi.send();
                                                                         
                                                                    };
                                                                    
                                                            
                                                                }
                                                                let progsiegestransfi = document.querySelector('#psiegesitinesfid');
                                                                if (progsiegestransfi !== null) {
                                                                    progsiegestransfi.onchange = () => 
                                                                    {

                                                                       const gareidentiftrans1fi = document.querySelector('#deplignetransfid').value;
                                                                                    __venteFiFillTransitDepart('#transitedepargare1fid', gareidentiftrans1fi);
                                                                        let httpSiegestrans1fi;
                                                                        httpSiegestrans1fi = new XMLHttpRequest();
                                                                        const sigstransfi = document.querySelector('#psiegesitinesfid')
                                                                        .options[document.querySelector('#psiegesitinesfid').options.selectedIndex].value;
                                                                        const prostransfi = document.querySelector('#programtransfid').value;

                                                                        httpSiegestrans1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${prostransfi}/${sigstransfi}`, true);
                                                                        httpSiegestrans1fi.onload = () => 
                                                                        {
                                                                            const donsgetransfi = JSON.parse(httpSiegestrans1fi.responseText);
                                                                            console.debug(`${typeof donsgetransfi} - ${donsgetransfi.attributes}`, console.memory);
                                                                            if(donsgetransfi == '')
                                                                            {
                                                                                let httpSiegstransfi;
                                                                                httpSiegstransfi = new XMLHttpRequest();

                                                                                httpSiegstransfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${prostransfi}/${sigstransfi}`, true);
                                                                                httpSiegstransfi.onload = () => 
                                                                                {
                                                                                    const dongtransfi = JSON.parse(httpSiegstransfi.responseText);
                                                                                    document.querySelector('#messfid').style.display = 'none';
                                                                                    if (Object.entries(dongtransfi).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtransfi)) {
                                                                                                document.querySelector('#idtampotransfid').value = `${dongtransfi[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselecttransfid').value = `${dongtransfi[key].numsieg}`;
                                                                                            }
                                                                                        }
                                                                                };
                                                                                httpSiegstransfi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSiegstransfi.send();
                                                                            }
                                                                            else {
                                                                                document.querySelector('#psiegesitinesfid').value = '';     
                                                                                if (Object.entries(donsgetransfi).length >= 1)
                                                                                {
                                                                                    for (let key in Object.entries(donsgetransfi)) {
                                                                                        document.querySelector('#idtampotransfid').value = `${donsgetransfi[key].idtamp}`;                    
                                                                                        document.querySelector('#siegselecttransfid').value = `${donsgetransfi[key].numsieg}`;
                                                                                    }

                                                                                }
                                                                                document.querySelector('#messfid').style.display = 'block';
                                                                                document.querySelector('#erreurMessfid').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                        };
                                                                        httpSiegestrans1fi.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegestrans1fi.send();

                                                                    
                                                                    };
                                                                }
                                                                //premier transite
                                                                let progcheminfi = document.querySelector('#idcheminsfid');
                                                                if (progcheminfi !== null) 
                                                                {
                                                                    progcheminfi.onchange = () => 
                                                                    {

                                                                        document.querySelector('#idcheminsheurfid').options.length = 1;
                                                                        document.querySelector('#psiegesitines1fid').options.length = 1;
                                                                       

                                                                        var datedepartfi = document.querySelector('#date_depheurefid').value;
                                                                        
                                                                        const prostranscheminfi = document.querySelector('#idcheminsfid')
                                                                        .options[document.querySelector('#idcheminsfid').options.selectedIndex].value;

                                                                        var odLeg2fi = __venteFiOdFromEtapeOrCode(__venteFiEtapeAt(1), prostranscheminfi);
                                                                        var seltypgare2fi = odLeg2fi.gaexp;
                                                                        var typgaresel1fi = odLeg2fi.gadest;
                                                                        let httptypequart2fi;
                                                                        httptypequart2fi = new XMLHttpRequest();
                                                                        
                                                                        httptypequart2fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgaresel1fi}`, true);
                                                                        httptypequart2fi.onload = () => 
                                                                        {
                                                                            const donqua2fi = JSON.parse(httptypequart2fi.responseText);
                                                                            if (donqua2fi == '') {
                                                                                document.querySelector('#quartier2fid').options.length = 1;
                                                                            }
                                                                            else{
                                                                                if (Object.entries(donqua2fi).length >= 1) {
                                                                                                
                                                                                    for (let key in Object.entries(donqua2fi)) {
                                                                                        let optq1 = document.createElement('option');
                                                                                        optq1.value = `${donqua2fi[key].nom_quartier}`;
                                                                                        optq1.innerHTML = `${donqua2fi[key].nom_quartier}`;
                                                                                        document.querySelector('#quartier2fid').add(optq1);
                                                                                    }
                                                                                } else {
                                                                                    document.querySelector('#quartier2fid').options.length = 1;
                                                                                }
                                                                            }
                                                                            

                                                                        };
                                                                        httptypequart2fi.setRequestHeader('Content-Type', 'application/json');
                                                                        httptypequart2fi.send();
                                                                        
                                                                        let httpSiegescheminfi;
                                                                        httpSiegescheminfi = new XMLHttpRequest();
                                                                        
                                                                        httpSiegescheminfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranscheminfi}/${datedepartfi}`, true);
                                                                        httpSiegescheminfi.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschemfi = JSON.parse(httpSiegescheminfi.responseText);
                                                                                    __venteFiFillCheminHeures('idcheminsheurfid', dongtranschemfi, 'tr2');
                                                                        };
                                                                        httpSiegescheminfi.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegescheminfi.send();

                                                                    };
                                                                        let prochemintrafi = document.querySelector('#idcheminsheurfid');
                                                                        if (prochemintrafi !== null){
                                                                            __venteFiWireCheminHeur('idcheminsheurfid', 'tr2'); if (false) prochemintrafi.onchange = () => 
                                                                            {  
                                                                                
                                                                                document.querySelector('#psiegesitines1fid').options.length = 1;
                                                                                const httpPrixittransitefi = new XMLHttpRequest();
                                                                                    const transselitinefi = document.querySelector('#idcheminsheurfid')
                                                                                .options[document.querySelector('#idcheminsheurfid').options.selectedIndex].value;
                                                                                    var post_transfi = transselitinefi.split('/');
                                                                                var itinetrasfi = post_transfi[0];
                                                                                var dbitrafi = post_transfi[1];
                                                                                var fnitrafi = post_transfi[2];
                                                                                var lhertrafi = post_transfi[3];
                                                                                var prixtrafi = post_transfi[4];

                                                                                    httpPrixittransitefi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetrasfi}`, true);
                                                                                    httpPrixittransitefi.onload = () => 
                                                                                    {
                                                                                        const donprixitranfi = JSON.parse(httpPrixittransitefi.responseText);
                                                                                        console.debug(`${typeof donprixitranfi}-${donprixitranfi.attributes}`, console.memory);
                                                                                        if (Object.entries(donprixitranfi).length >= 1) {
                                                                                            for (let key in Object.entries(donprixitranfi)) 
                                                                                            {
                                                                                                document.querySelector('#catetransitfid').value = `${donprixitranfi[key].categori}`;
                                                                                                document.querySelector('#gidtransfid').value =  `${donprixitranfi[key].gareidentif}`;
                                                                                                document.querySelector('#nomitintrans1fid').value = `${donprixitranfi[key].nom_ligne}`;
                                                                                                document.querySelector('#ligntrans1fid').value = `${donprixitranfi[key].ident_ligne}`;
                                                                                            }
                                                                                        }
                                                                                    };
                                                                                    httpPrixittransitefi.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpPrixittransitefi.send();
                                                                          

                                                                                    
                                                                                    const httpRequetteitrafi = new XMLHttpRequest();
                                                                            
                                                                                        httpRequetteitrafi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetrasfi}/${dbitrafi}/${fnitrafi}`, true);
                                                                                    httpRequetteitrafi.onload = () => {
                                                                                        const dattaitrafi = JSON.parse(httpRequetteitrafi.responseText);
                                                                                        console.debug(`${typeof dattaitrafi} - ${dattaitrafi.attributes}`, console.memory);
                                                                                        if (Object.entries(dattaitrafi).length >= 1) {
                                                                                            for (let key in Object.entries(dattaitrafi)) {
                                                                                                
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dattaitrafi[key].siege_num}`;
                                                                                                opt.innerHTML = `${dattaitrafi[key].siege_num}`;
                                                                                                document.querySelector('#psiegesitines1fid').add(opt);
                                                                                                
                                                                                            }
                                                                                            
                                                                                        } else {
                                                                                            document.querySelector('#psiegesitines1fid').options.length = 1;
                                                                                        }
                                                                                    };
                                                                                    httpRequetteitrafi.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpRequetteitrafi.send();
                                                                            };
                                                                        }
                                                                        let progsieges1fi = document.querySelector('#psiegesitines1fid');
                                                                        if (progsieges1fi !== null) 
                                                                        {
                                                                            progsieges1fi.onchange = () => 
                                                                            {

                                                                               const gareidentiftrans2fi = document.querySelector('#gidtransfid').value;
                                                                                    __venteFiFillTransitDepart('#transitedepargare2fid', gareidentiftrans2fi);
                                                                                

                                                                                    const transselitine1fi = document.querySelector('#idcheminsheurfid')
                                                                                .options[document.querySelector('#idcheminsheurfid').options.selectedIndex].value;
                                                                                var post_trans1fi = transselitine1fi.split('/');
                                                                                var itinetras1fi = post_trans1fi[0];
                                                                    
                                                                                let httpSieges1fi;
                                                                                httpSieges1fi = new XMLHttpRequest();
                                                                                const sigs1fi = document.querySelector('#psiegesitines1fid')
                                                                                .options[document.querySelector('#psiegesitines1fid').options.selectedIndex].value;

                                                                                httpSieges1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetras1fi}/${sigs1fi}`, true);
                                                                                httpSieges1fi.onload = () => 
                                                                                {
                                                                                    const donsge1fi = JSON.parse(httpSieges1fi.responseText);
                                                                                    console.debug(`${typeof donsge1fi} - ${donsge1fi.attributes}`, console.memory);
                                                                                    if(donsge1fi == '')
                                                                                    {
                                                                                        let httpSiegs1fi;
                                                                                        httpSiegs1fi = new XMLHttpRequest();

                                                                                        httpSiegs1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetras1fi}/${sigs1fi}`, true);
                                                                                        httpSiegs1fi.onload = () => 
                                                                                        {
                                                                                            const dong1fi = JSON.parse(httpSiegs1fi.responseText);
                                                                                            document.querySelector('#messfid').style.display = 'none';
                                                                                            if (Object.entries(dong1fi).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(dong1fi)) {
                                                                                                        document.querySelector('#idtampo1fid').value = `${dong1fi[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselect1fid').value = `${dong1fi[key].numsieg}`;
                                                                                                    }
                                                                                                }
                                                                                        };
                                                                                        httpSiegs1fi.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegs1fi.send();
                                                                                    }
                                                                                    else {
                                                                                        document.querySelector('#psiegesitines1fid').value = '';     
                                                                                        if (Object.entries(donsge1fi).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(donsge1fi)) {
                                                                                                document.querySelector('#idtampo1fid').value = `${donsge1fi[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselect1fid').value = `${donsge1fi[key].numsieg}`;
                                                                                            }

                                                                                        }
                                                                                        document.querySelector('#messfid').style.display = 'block';
                                                                                        document.querySelector('#erreurMessfid').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                                };
                                                                                httpSieges1fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSieges1fi.send();

                                                                            };
                                                                        }
                                                                }
                                                                //deuxieme transite
                                                                let progchemin1fi = document.querySelector('#idchemins1fid');
                                                                if (progchemin1fi !== null) 
                                                                {
                                                                    progchemin1fi.onchange = () => 
                                                                    {
                                                                        document.querySelector('#idcheminsheur1fid').options.length = 1;
                                                                        document.querySelector('#psiegesitines2fid').options.length = 1;

                                                                        const prostranschemin32fi = document.querySelector('#idchemins1fid')
                                                                        .options[document.querySelector('#idchemins1fid').options.selectedIndex].value;

                                                                        var odLeg3fi = __venteFiOdFromEtapeOrCode(__venteFiEtapeAt(2), prostranschemin32fi);
                                                                        var seltypgare32fi = odLeg3fi.gaexp;
                                                                        var typgaresel31fi = odLeg3fi.gadest;
                                                                        let httptypequart32fi;
                                                                        httptypequart32fi = new XMLHttpRequest();
                                                                        
                                                                        httptypequart32fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgaresel31fi}`, true);
                                                                        httptypequart32fi.onload = () => 
                                                                        {
                                                                            const donqua32fi = JSON.parse(httptypequart32fi.responseText);
                                                                            if (donqua32fi == '') {
                                                                                document.querySelector('#quartier3fid').options.length = 1;
                                                                            }
                                                                            else{
                                                                                if (Object.entries(donqua32fi).length >= 1) {
                                                                                                
                                                                                    for (let key in Object.entries(donqua32fi)) {
                                                                                        let optq31 = document.createElement('option');
                                                                                        optq31.value = `${donqua32fi[key].nom_quartier}`;
                                                                                        optq31.innerHTML = `${donqua32fi[key].nom_quartier}`;
                                                                                        document.querySelector('#quartier3fid').add(optq31);
                                                                                    }
                                                                                } else {
                                                                                    document.querySelector('#quartier3fid').options.length = 1;
                                                                                }
                                                                            }
                                                                            

                                                                        };
                                                                        httptypequart32fi.setRequestHeader('Content-Type', 'application/json');
                                                                        httptypequart32fi.send();
                                                                        
                                                                        let httpSiegeschemin1fi;
                                                                        httpSiegeschemin1fi = new XMLHttpRequest();
                                                                        
                                                                        var datedepartfi = document.querySelector('#date_depheurefid').value;
                                                                        const prostranschemin1fi = document.querySelector('#idchemins1fid')
                                                                        .options[document.querySelector('#idchemins1fid').options.selectedIndex].value;

                                                                        httpSiegeschemin1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranschemin1fi}/${datedepartfi}`, true);
                                                                        httpSiegeschemin1fi.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschem1fi = JSON.parse(httpSiegeschemin1fi.responseText);
                                                                                    __venteFiFillCheminHeures('idcheminsheur1fid', dongtranschem1fi, 'tr3');
                                                                        };
                                                                        httpSiegeschemin1fi.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegeschemin1fi.send();

                                                                    };
                                                                       let prochemintra1fi = document.querySelector('#idcheminsheur1fid');
                                                                    if (prochemintra1fi !== null)
                                                                        __venteFiWireCheminHeur('idcheminsheur1fid', 'tr3'); if (false) prochemintra1fi.onchange = () => 
                                                                        {  
                                                                            
                                                                            document.querySelector('#psiegesitines2fid').options.length = 1;

                                                                            const httpPrixittransite1fi = new XMLHttpRequest();
                                                                                const transselitine1fi = document.querySelector('#idcheminsheur1fid')
                                                                            .options[document.querySelector('#idcheminsheur1fid').options.selectedIndex].value;
                                                                                var post_trans1fi = transselitine1fi.split('/');
                                                                            var itinetras1fi = post_trans1fi[0];
                                                                            var dbitra1fi = post_trans1fi[1];
                                                                            var fnitra1fi = post_trans1fi[2];
                                                                            var lhertra1fi = post_trans1fi[3];
                                                                            var prixtra1fi = post_trans1fi[4];

                                                                                httpPrixittransite1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetras1fi}`, true);
                                                                                httpPrixittransite1fi.onload = () => 
                                                                                {
                                                                                    const donprixitran1fi = JSON.parse(httpPrixittransite1fi.responseText);
                                                                                    if (Object.entries(donprixitran1fi).length >= 1) {
                                                                                        for (let key in Object.entries(donprixitran1fi)) 
                                                                                        {
                                                                                            document.querySelector('#catetransit1fid').value = `${donprixitran1fi[key].categori}`;
                                                                                            document.querySelector('#gidtrans1fid').value =  `${donprixitran1fi[key].gareidentif}`;
                                                                                            document.querySelector('#nomitintrans2fid').value = `${donprixitran1fi[key].nom_ligne}`;
                                                                                            document.querySelector('#ligntrans2fid').value = `${donprixitran1fi[key].ident_ligne}`;
                                                                                        }
                                                                                    }
                                                                                };
                                                                                httpPrixittransite1fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpPrixittransite1fi.send();
                                                                      
                                                                                

                                                                                const httpRequetteitra1fi = new XMLHttpRequest();
                                                                        
                                                                                    httpRequetteitra1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetras1fi}/${dbitra1fi}/${fnitra1fi}`, true);
                                                                                httpRequetteitra1fi.onload = () => {
                                                                                    const dattaitra1fi = JSON.parse(httpRequetteitra1fi.responseText);
                                                                                    if (Object.entries(dattaitra1fi).length >= 1) {
                                                                                        for (let key in Object.entries(dattaitra1fi)) {
                                                                                            
                                                                                            let opt = document.createElement('option');
                                                                                            opt.value = `${dattaitra1fi[key].siege_num}`;
                                                                                            opt.innerHTML = `${dattaitra1fi[key].siege_num}`;
                                                                                            document.querySelector('#psiegesitines2fid').add(opt);
                                                                                            
                                                                                        }
                                                                                        
                                                                                    } else {
                                                                                        document.querySelector('#psiegesitines2fid').options.length = 1;
                                                                                    }
                                                                                };
                                                                                httpRequetteitra1fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpRequetteitra1fi.send();
                                                                        };

                                                                       let progsieges2fi = document.querySelector('#psiegesitines2fid');
                                                                        if (progsieges2fi !== null) 
                                                                        {
                                                                            progsieges2fi.onchange = () => 
                                                                            {

                                                                               const gareidentiftrans4fi = document.querySelector('#gidtrans1fid').value;
                                                                                __venteFiFillTransitDepart('#transitedepargare3fid', gareidentiftrans4fi);
                                                                                    const transselitine2fi = document.querySelector('#idcheminsheur1fid')
                                                                                .options[document.querySelector('#idcheminsheur1fid').options.selectedIndex].value;
                                                                                var post_trans2fi = transselitine2fi.split('/');
                                                                                var itinetras2fi = post_trans2fi[0];
                                                                    
                                                                                let httpSieges2fi;
                                                                                httpSieges2fi = new XMLHttpRequest();
                                                                                const sigs2fi = document.querySelector('#psiegesitines2fid')
                                                                                .options[document.querySelector('#psiegesitines2fid').options.selectedIndex].value;

                                                                                httpSieges2fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetras2fi}/${sigs2fi}`, true);
                                                                                httpSieges2fi.onload = () => 
                                                                                {
                                                                                    const donsge2fi = JSON.parse(httpSieges2fi.responseText);
                                                                                    if(donsge2fi == '')
                                                                                    {
                                                                                        let httpSiegs2fi;
                                                                                        httpSiegs2fi = new XMLHttpRequest();

                                                                                        httpSiegs2fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetras2fi}/${sigs2fi}`, true);
                                                                                        httpSiegs2fi.onload = () => 
                                                                                        {
                                                                                            const dong2fi = JSON.parse(httpSiegs2fi.responseText);
                                                                                            document.querySelector('#messfid').style.display = 'none';
                                                                                            if (Object.entries(dong2fi).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(dong2fi)) {
                                                                                                        document.querySelector('#idtampo2fid').value = `${dong2fi[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselect2fid').value = `${dong2fi[key].numsieg}`;
                                                                                                    }
                                                                                                }
                                                                                        };
                                                                                        httpSiegs2fi.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegs2fi.send();
                                                                                    }
                                                                                    else {
                                                                                        document.querySelector('#psiegesitines2fid').value = '';     
                                                                                        if (Object.entries(donsge2fi).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(donsge2fi)) {
                                                                                                document.querySelector('#idtampo2fid').value = `${donsge2fi[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselect2fid').value = `${donsge2fi[key].numsieg}`;
                                                                                            }

                                                                                        }
                                                                                        document.querySelector('#messfid').style.display = 'block';
                                                                                        document.querySelector('#erreurMessfid').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                                };
                                                                                httpSieges2fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSieges2fi.send();

                                                                            };
                                                                        }
                                                                }   

                                                                //troisieme transite
                                                               let progchemin2fi = document.querySelector('#idchemins2fid');
                                                                if (progchemin2fi !== null) 
                                                                {
                                                                    progchemin2fi.onchange = () => 
                                                                    {
                                                                        document.querySelector('#idcheminsheur2fid').options.length = 1;
                                                                        document.querySelector('#psiegesitines3fid').options.length = 1;

                                                                        const prostranschemin42fi = document.querySelector('#idchemins2fid')
                                                                        .options[document.querySelector('#idchemins2fid').options.selectedIndex].value;

                                                                        var odLeg4fi = __venteFiOdFromEtapeOrCode(__venteFiEtapeAt(3), prostranschemin42fi);
                                                                        var seltypgare42fi = odLeg4fi.gaexp;
                                                                        var typgaresel41fi = odLeg4fi.gadest;

                                                                        // Jambe 4 FID : #quartierfid déjà chargé via arrivée — ne pas écraser la sélection.
                                                                        var qMain4fi = document.querySelector('#quartierfid');
                                                                        if (typgaresel41fi && qMain4fi && qMain4fi.options.length <= 1) {
                                                                            var httptypequart4fi = new XMLHttpRequest();
                                                                            httptypequart4fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgaresel41fi}`, true);
                                                                            httptypequart4fi.onload = () => {
                                                                                var donqua4fi = [];
                                                                                try { donqua4fi = JSON.parse(httptypequart4fi.responseText) || []; } catch (e4) { donqua4fi = []; }
                                                                                qMain4fi.options.length = 1;
                                                                                var keep4fi = qMain4fi.value || '';
                                                                                if (donqua4fi && Object.entries(donqua4fi).length >= 1) {
                                                                                    for (let key in Object.entries(donqua4fi)) {
                                                                                        let optq4 = document.createElement('option');
                                                                                        optq4.value = `${donqua4fi[key].nom_quartier}`;
                                                                                        optq4.innerHTML = `${donqua4fi[key].nom_quartier}`;
                                                                                        qMain4fi.add(optq4);
                                                                                    }
                                                                                }
                                                                                if (keep4fi) qMain4fi.value = keep4fi;
                                                                            };
                                                                            httptypequart4fi.setRequestHeader('Content-Type', 'application/json');
                                                                            httptypequart4fi.send();
                                                                        }

                                                                        let httpSiegeschemin2fi;
                                                                        httpSiegeschemin2fi = new XMLHttpRequest();
                                                                        const prostranschemin2fi = document.querySelector('#idchemins2fid')
                                                                        .options[document.querySelector('#idchemins2fid').options.selectedIndex].value;

                                                                        var datedepartfi = document.querySelector('#date_depheurefid').value;
                                                                        
                                                                        httpSiegeschemin2fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranschemin2fi}/${datedepartfi}`, true);
                                                                        httpSiegeschemin2fi.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschem2fi = JSON.parse(httpSiegeschemin2fi.responseText);
                                                                                    __venteFiFillCheminHeures('idcheminsheur2fid', dongtranschem2fi, 'tr4');
                                                                        };
                                                                        httpSiegeschemin2fi.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegeschemin2fi.send();

                                                                    };
                                                                      let prochemintra2fi = document.querySelector('#idcheminsheur2fid');
                                                                    if (prochemintra2fi !== null)
                                                                        __venteFiWireCheminHeur('idcheminsheur2fid', 'tr4'); if (false) prochemintra2fi.onchange = () => 
                                                                        {  
                                                                            
                                                                            document.querySelector('#psiegesitines3fid').options.length = 1;

                                                                            const httpPrixittransite2fi = new XMLHttpRequest();
                                                                                const transselitine2fi = document.querySelector('#idcheminsheur2fid')
                                                                            .options[document.querySelector('#idcheminsheur2fid').options.selectedIndex].value;
                                                                                var post_trans2fi = transselitine2fi.split('/');
                                                                            var itinetras2fi = post_trans2fi[0];
                                                                            var dbitra2fi = post_trans2fi[1];
                                                                            var fnitra2fi = post_trans2fi[2];
                                                                            var lhertra2fi = post_trans2fi[3];
                                                                            var prixtra2fi = post_trans2fi[4];

                                                                                httpPrixittransite2fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetras2fi}`, true);
                                                                                httpPrixittransite2fi.onload = () => 
                                                                                {
                                                                                    const donprixitran2fi = JSON.parse(httpPrixittransite2fi.responseText);
                                                                                    if (Object.entries(donprixitran2fi).length >= 1) {
                                                                                        for (let key in Object.entries(donprixitran2fi)) 
                                                                                        {
                                                                                            document.querySelector('#catetransit2fid').value = `${donprixitran2fi[key].categori}`;
                                                                                            document.querySelector('#gidtrans2fid').value =  `${donprixitran2fi[key].gareidentif}`;
                                                                                            document.querySelector('#nomitintrans3fid').value = `${donprixitran2fi[key].nom_ligne}`;
                                                                                            document.querySelector('#ligntrans3fid').value = `${donprixitran2fi[key].ident_ligne}`;
                                                                                        }
                                                                                    }
                                                                                };
                                                                                httpPrixittransite2fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpPrixittransite2fi.send();
                                                                      
                                                                                

                                                                                const httpRequetteitra2fi = new XMLHttpRequest();
                                                                        
                                                                                    httpRequetteitra2fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetras2fi}/${dbitra2fi}/${fnitra2fi}`, true);
                                                                                httpRequetteitra2fi.onload = () => {
                                                                                    const dattaitra2fi = JSON.parse(httpRequetteitra2fi.responseText);
                                                                                    console.debug(`${typeof dattaitra2fi} - ${dattaitra2fi.attributes}`, console.memory);
                                                                                    if (Object.entries(dattaitra2fi).length >= 1) {
                                                                                        for (let key in Object.entries(dattaitra2fi)) {
                                                                                            
                                                                                            let opt = document.createElement('option');
                                                                                            opt.value = `${dattaitra2fi[key].siege_num}`;
                                                                                            opt.innerHTML = `${dattaitra2fi[key].siege_num}`;
                                                                                            document.querySelector('#psiegesitines3fid').add(opt);
                                                                                            
                                                                                        }
                                                                                        
                                                                                    } else {
                                                                                        document.querySelector('#psiegesitines3fid').options.length = 1;
                                                                                    }
                                                                                };
                                                                                httpRequetteitra2fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpRequetteitra2fi.send();
                                                                        };

                                                                       let progsieges3fi = document.querySelector('#psiegesitines3fid');
                                                                        if (progsieges3fi !== null) 
                                                                        {
                                                                            progsieges3fi.onchange = () => 
                                                                            {

                                                                               const gareidentiftrans5fi = document.querySelector('#gidtrans2fid').value;
                                                                                __venteFiFillTransitDepart('#transitedepargare4fid', gareidentiftrans5fi);
                                                                                    const transselitine3fi = document.querySelector('#idcheminsheur2fid')
                                                                                .options[document.querySelector('#idcheminsheur2fid').options.selectedIndex].value;
                                                                                var post_trans3fi = transselitine3fi.split('/');
                                                                                var itinetras3fi = post_trans3fi[0];
                                                                    
                                                                                let httpSieges3fi;
                                                                                httpSieges3fi = new XMLHttpRequest();
                                                                                const sigs3fi = document.querySelector('#psiegesitines3fid')
                                                                                .options[document.querySelector('#psiegesitines3fid').options.selectedIndex].value;

                                                                                httpSieges3fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetras3fi}/${sigs3fi}`, true);
                                                                                httpSieges3fi.onload = () => 
                                                                                {
                                                                                    const donsge3fi = JSON.parse(httpSieges3fi.responseText);
                                                                                    if(donsge3fi == '')
                                                                                    {
                                                                                        let httpSiegs3fi;
                                                                                        httpSiegs3fi = new XMLHttpRequest();

                                                                                        httpSiegs3fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetras3fi}/${sigs3fi}`, true);
                                                                                        httpSiegs3fi.onload = () => 
                                                                                        {
                                                                                            const dong3fi = JSON.parse(httpSiegs3fi.responseText);
                                                                                            document.querySelector('#messfid').style.display = 'none';
                                                                                            if (Object.entries(dong3fi).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(dong3fi)) {
                                                                                                        document.querySelector('#idtampo3fid').value = `${dong3fi[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselect3fid').value = `${dong3fi[key].numsieg}`;
                                                                                                    }
                                                                                                }
                                                                                        };
                                                                                        httpSiegs3fi.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegs3fi.send();
                                                                                    }
                                                                                    else {
                                                                                        document.querySelector('#psiegesitines3fid').value = '';     
                                                                                        if (Object.entries(donsge3fi).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(donsge3fi)) {
                                                                                                document.querySelector('#idtampo3fid').value = `${donsge3fi[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselect3fid').value = `${donsge3fi[key].numsieg}`;
                                                                                            }

                                                                                        }
                                                                                        document.querySelector('#messfid').style.display = 'block';
                                                                                        document.querySelector('#erreurMessfid').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                                };
                                                                                httpSieges3fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSieges3fi.send();

                                                                            };
                                                                        }
                                                                }            
                                                            }
                                                                
                                                        }
                                                    }

                                        }; // fin __venteFiApplyTransitLegs

                                // Ne pas ouvrir le transit au clic date.

                                        let hrdepartfi = document.querySelector('#hdepartfid');
                                        if (hrdepartfi !== null) {
                                            hrdepartfi.onchange = () => 
                                            {
                                                document.querySelector('#psiegesfid').options.length = 1;
                                                document.querySelector('#typegarefid').value = '';
                                                __venteFiHideProgSelect();
                                                const hOptFi = document.querySelector('#hdepartfid').options[document.querySelector('#hdepartfid').options.selectedIndex];
                                                const selefi = hOptFi ? hOptFi.value : '';
                                                const hasProgHourFi = hOptFi && hOptFi.getAttribute('data-has-programme') === '1';

                                                // Heure sans départ → correspondances (comme vente guichet).
                                                if (selefi && !hasProgHourFi) {
                                                    var messElFi = document.querySelector('#messfid');
                                                    var errElFi = document.querySelector('#erreurMessfid');
                                                    if (window.__venteFiHasTransit) {
                                                        var postLhFi = selefi.split('/');
                                                        window.__venteSelectedHour = {
                                                            value: selefi,
                                                            idLh: postLhFi[0] || '',
                                                            heure: postLhFi[1] || '',
                                                            hasProg: false
                                                        };
                                                        if (typeof window.__venteSetTransitAnchorFromHour === 'function') {
                                                            window.__venteSetTransitAnchorFromHour(window.__venteSelectedHour);
                                                        }
                                                        if (messElFi) messElFi.style.display = 'block';
                                                        if (errElFi) errElFi.innerHTML = 'Pas de départ à cette heure — correspondances proposées.';
                                                        __venteFiRequestTransitLegs(seltdepfi, arrfi, datedepartfi, sougidfi, true);
                                                    } else {
                                                        __venteFiShowDirectHourUi();
                                                        if (messElFi) messElFi.style.display = 'block';
                                                        if (errElFi) errElFi.innerHTML = 'Aucun départ ni correspondance pour cette heure.';
                                                    }
                                                    return;
                                                }

                                                // Heure avec départ : vente directe FI (P/O et champs spécifiques conservés).
                                                __venteFiShowDirectHourUi();
                                                if (document.querySelector('#messfid')) document.querySelector('#messfid').style.display = 'none';
                                                const httpRequestfi = new XMLHttpRequest();

                                                    var post_lhfi = selefi.split('/');
                                                    var selfi = post_lhfi[0];
                                                    var lhselfi = post_lhfi[1];

                                                    const dpt_datefi = document.querySelector('#date_depheurefid').value;
                                                    var typgarefi = document.querySelector('#arrsgarefid').value;
                                                    const httptypegarefi = new XMLHttpRequest();
                                                    httptypegarefi.open('GET', window.location.origin + `${APP_ROOT}/programmes/gareprincipale/${typgarefi}/${lhselfi}`, true);
                                                    httptypegarefi.onload = () => 
                                                    {
                                                        const dongarefi = JSON.parse(httptypegarefi.responseText);
                                                        if (Object.entries(dongarefi).length >= 1)
                                                        for (let key in Object.entries(dongarefi)) 
                                                        document.querySelector('#typegarefid').value = `${dongarefi[key].typestatutgare}`;
                                                    };
                                                    httptypegarefi.setRequestHeader('Content-Type', 'application/json');
                                                    httptypegarefi.send();

                                                


                                                httpRequestfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifprog/${seltdepfi}-${arrfi}/${dpt_datefi}/${selfi}/${sougidfi || '0'}`, true);
                                                httpRequestfi.onload = () => 
                                                {
                                                    var typ_garefi = document.querySelector('#typegarefid').value;    
                                                    const donfi = JSON.parse(httpRequestfi.responseText);
                                                        if (__venteFiHandleProgList(donfi, dpt_datefi, (hOptFi && hOptFi.getAttribute('data-code-progr')) || (post_lhfi[2] || ''))) {
                                                            return;
                                                        }
                                                        if (donfi == '' || __venteFiProgListFromResponse(donfi).length === 0) 
                                                        {
                                                            if(typ_garefi == 'Principale'){
                                                                
                                                                    let opt = document.createElement('option');
                                                                    opt.value = 1;
                                                                    opt.innerHTML = 1;
                                                                    document.querySelector('#psiegesfid').add(opt);
                                                            
                                                                    departpsiegesfi = document.querySelector('#psiegesfid');
                                                                    if (departpsiegesfi !== null) {
                                                                        departpsiegesfi.onchange = () => 
                                                                        {
                                                                            let httpProgfi;
                                                                            httpProgfi = new XMLHttpRequest();
                                                                            httpProgfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/creedepart/${seltdepfi}/${dpt_datefi}/${selfi}/${lhselfi}`, true);
                                                                            httpProgfi.onload = () => 
                                                                            {
                                                                                const donsfi = JSON.parse(httpProgfi.responseText);
                                                                                if (Object.entries(donsfi).length >= 1) {
                                                                                    for (let key in Object.entries(donsfi)) {
                                                                                        document.querySelector('#programfid').value = `${donsfi[key].code_progr}`;
                                                                                        document.querySelector('#catefid').value = `${donsfi[key].categorie}`;
                                                                                        document.querySelector('#deplignefid').value = `${donsfi[key].gareidentif}`;
                                                                                        document.querySelector('#lignfid').value = `${donsfi[key].ident_ligne}`;
                                                                                        document.querySelector('#nomitinfid').value = `${donsfi[key].nom_ligne}`;
                                                                                    }
                                                                                        let httpSiegefi;
                                                                                        httpSiegefi = new XMLHttpRequest();
                                                                                        const sigfi = document.querySelector('#psiegesfid')
                                                                                        .options[document.querySelector('#psiegesfid').options.selectedIndex].value;
                                                                                        const profi = document.querySelector('#programfid').value;
                                                                                        httpSiegefi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${profi}/${sigfi}`, true);
                                                                                        httpSiegefi.onload = () => 
                                                                                        {
                                                                                            const donsgfi = JSON.parse(httpSiegefi.responseText);
                                                                                            console.debug(`${typeof donsgfi} - ${donsgfi.attributes}`, console.memory);
                                                                                            if(donsgfi == '')
                                                                                            {
                                                                                                let httpSiegfi;
                                                                                                httpSiegfi = new XMLHttpRequest();
                    
                                                                                                httpSiegfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${profi}/${sigfi}`, true);
                                                                                                httpSiegfi.onload = () => 
                                                                                                {
                                                                                                    const donsg2fi = JSON.parse(httpSiegfi.responseText);
                                                                                                    document.querySelector('#messfid').style.display = 'none';
                                                                                                    if (Object.entries(donsg2fi).length >= 1)
                                                                                                        {
                                                                                                            for (let key in Object.entries(donsg2fi)) {
                                                                                                                document.querySelector('#idtampofid').value = `${donsg2fi[key].idtamp}`;                    
                                                                                                                document.querySelector('#siegselectfid').value = `${donsg2fi[key].numsieg}`;
                                                                                                            }
                                                                                                        }
                                                                                                };
                                                                                                httpSiegfi.setRequestHeader('Content-Type', 'application/json');
                                                                                                httpSiegfi.send();
                                                                                            }
                                                                                            else 
                                                                                            {
                                                                                                document.querySelector('#psiegesfid').value = ''; 
                                                                                                if (Object.entries(donsgfi).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(donsgfi)) 
                                                                                                    {
                                                                                                        document.querySelector('#idtampofid').value = `${donsgfi[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselectfid').value = `${donsgfi[key].numsieg}`;
                                                                                                    }
        
                                                                                                }
                                                                                                document.querySelector('#messfid').style.display = 'block';
                                                                                                document.querySelector('#erreurMessfid').innerHTML = `Siege déjà utilisé.`;                   
                                                                                            }
                                                                                        };
                                                                                        httpSiegefi.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegefi.send();
                    
                                                                                   
                                                                                }
                                                                            };
                                                                            httpProgfi.setRequestHeader('Content-Type', 'application/json');
                                                                            httpProgfi.send();
        
                                                                            
                                                                        
                                                                        };
        
                                                                        
                                                                    }
                                                            }else{
                                                                let opt = document.createElement('option');
                                                                opt.value = '';                                                             
                                                            }
                                                            
                                                            
                                                        }  
                                                        
                                                    };
                                                    httpRequestfi.setRequestHeader('Content-Type', 'application/json');
                                                    httpRequestfi.send();
                                                     
                                                };
                                                
                                        
                                            }
                                };
                                httpRequetesfi.setRequestHeader('Content-Type', 'application/json');
                                httpRequetesfi.send();
                        }
                        else
                        {
                            document.querySelector('#date_depheurefid').style.color = "#FF0000";
                            document.querySelector('#date_depheurefid').style.border = "2px solid #FF0000";
                            document.querySelector('#smsdtfid').style.display = 'block';
                            document.querySelector('#erreurSmsdtfid').innerHTML = `Date non valide.`;
                        }
                    

                };
                
            }
            let progsiegesfi = document.querySelector('#psiegesfid');
            if (progsiegesfi !== null) {
                progsiegesfi.onchange = () => 
                {
                    let httpSiegesfi;
                    httpSiegesfi = new XMLHttpRequest();
                    const sigsfi = document.querySelector('#psiegesfid')
                    .options[document.querySelector('#psiegesfid').options.selectedIndex].value;
                    const prosfi = document.querySelector('#programfid').value;

                    httpSiegesfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${prosfi}/${sigsfi}`, true);
                    httpSiegesfi.onload = () => 
                    {
                        const donsgefi = JSON.parse(httpSiegesfi.responseText);
                        console.debug(`${typeof donsgefi} - ${donsgefi.attributes}`, console.memory);
                        if(donsgefi == '')
                        {
                            let httpSiegsfi;
                            httpSiegsfi = new XMLHttpRequest();

                            httpSiegsfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${prosfi}/${sigsfi}`, true);
                            httpSiegsfi.onload = () => 
                            {
                                const dongfi = JSON.parse(httpSiegsfi.responseText);
                                document.querySelector('#messfid').style.display = 'none';
                                if (Object.entries(dongfi).length >= 1)
                                    {
                                        for (let key in Object.entries(dongfi)) {
                                            document.querySelector('#idtampofid').value = `${dongfi[key].idtamp}`;                    
                                            document.querySelector('#siegselectfid').value = `${dongfi[key].numsieg}`;
                                        }
                                    }
                            };
                            httpSiegsfi.setRequestHeader('Content-Type', 'application/json');
                            httpSiegsfi.send();
                        }
                        else {
                            document.querySelector('#psiegesfid').value = '';     
                            if (Object.entries(donsgefi).length >= 1)
                            {
                                for (let key in Object.entries(donsgefi)) {
                                    document.querySelector('#idtampofid').value = `${donsgefi[key].idtamp}`;                    
                                    document.querySelector('#siegselectfid').value = `${donsgefi[key].numsieg}`;
                                }

                            }
                            document.querySelector('#messfid').style.display = 'block';
                            document.querySelector('#erreurMessfid').innerHTML = `Siege déjà utilisé.`;                                                                   }
                    };
                    httpSiegesfi.setRequestHeader('Content-Type', 'application/json');
                    httpSiegesfi.send();

                
                };
            }
           
            let infdocfi = document.querySelector('#cltypefid');
        if (infdocfi !== null)
            infdocfi.onchange = () => 
            {
                let httpDocsfi;
                if (window.XMLHttpRequest) {
                    httpDocsfi = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpDocsfi = new ActiveXObject("Microsoft.XMLHTTP");
                }
                var documfi = document.querySelector('#cltypefid').value;
                
                if (documfi == 'Adulte') {
                    document.querySelector('#motiffid').style.display = 'none';
                    document.querySelector('#motifrefusfid').style.display = 'none';
                    document.querySelector('#docfid').style.display = 'none';
                    document.querySelector('#docdelivrefid').style.display = 'none';
                    document.querySelector('#datedocdelfid').style.display = 'none';
                    document.querySelector('#num_docfid').style.display = 'none';
                    document.querySelector('#rclientfid').style.display = 'block';
                    document.querySelector('#prnclientfid').style.display = 'block';
                    document.querySelector('#cnibfid').style.display = 'block';
                    document.querySelector('#date_cnibfid').style.display = 'block';
                    document.querySelector('#lieudelivrefid').style.display = 'block';
                    console.debug(`${documfid}`, console.memory);

                } 
                    if (documfi == 'Etudiant') {
                        document.querySelector('#docfid').style.display = 'block';
                        document.querySelector('#num_docfid').style.display = 'block';
                        document.querySelector('#docdelivrefid').style.display = 'block';
                        document.querySelector('#datedocdelfid').style.display = 'block';
                        document.querySelector('#rclientfid').style.display = 'block';
                        document.querySelector('#prnclientfid').style.display = 'block';
                        document.querySelector('#cnibfid').style.display = 'none';
                        document.querySelector('#date_cnibfid').style.display = 'none';
                        document.querySelector('#lieudelivrefid').style.display = 'none';
                        console.debug(`${documfi}`, console.memory);

                    } 
                    if (documfi == 'Elève') {
                        document.querySelector('#docfid').style.display = 'block';
                        document.querySelector('#num_docfid').style.display = 'block';
                        document.querySelector('#docdelivrefid').style.display = 'block';
                        document.querySelector('#datedocdelfid').style.display = 'block';
                        document.querySelector('#rclientfid').style.display = 'block';
                        document.querySelector('#prnclientfid').style.display = 'block';
                        document.querySelector('#cnibfid').style.display = 'none';
                        document.querySelector('#date_cnibfid').style.display = 'none';
                        document.querySelector('#lieudelivrefid').style.display = 'none';
                        console.debug(`${documfi}`, console.memory);

                    } 
                    if (documfi == 'Enfant') {
                        document.querySelector('#docfidfid').style.display = 'block';
                        document.querySelector('#num_docfid').style.display = 'block';
                        document.querySelector('#docdelivrefid').style.display = 'block';
                        document.querySelector('#datedocdelfid').style.display = 'block';
                        document.querySelector('#rclientfid').style.display = 'block';
                        document.querySelector('#prnclientfid').style.display = 'block';
                        document.querySelector('#cnibfid').style.display = 'none';
                        document.querySelector('#date_cnibfid').style.display = 'none';
                        document.querySelector('#lieudelivrefid').style.display = 'none';
                        console.debug(`${documfi}`, console.memory);

                    } 
                    if (documfi == 'Autres') {
                        document.querySelector('#motiffid').style.display = 'block';
                        document.querySelector('#motifrefusfid').style.display = 'block';
                        document.querySelector('#rclientfid').style.display = 'block';
                        document.querySelector('#prnclientfid').style.display = 'block';
                        document.querySelector('#cnibfid').style.display = 'none';
                        document.querySelector('#date_cnibfid').style.display = 'none';
                        document.querySelector('#lieudelivrefid').style.display = 'none';
                        document.querySelector('#docfid').style.display = 'none';
                        document.querySelector('#num_docfid').style.display = 'none';
                        document.querySelector('#docdelivrefid').style.display = 'none';
                        document.querySelector('#datedocdelfid').style.display = 'none';
                        console.debug(`${documfi}`, console.memory);

                    } 
                    
            };

            
        //recherche d'information du client depart principal
        let inffi = document.querySelector('#rnclient_contactfid');
        if (inffi !== null && inffi.dataset.guarded !== '1') {
            inffi.dataset.guarded = '1';
            inffi.addEventListener('keyup', () => {
                const rawPhone = inffi.value.trim();
                const digits = AppRequestGuard.phoneDigits(rawPhone);
                if (digits.length < 7) {
                    return;
                }
                AppRequestGuard.debounce('verifinfosfi', () => {
                    AppRequestGuard.getJson(
                        window.location.origin + `${APP_ROOT}/programmes/verifinfos/${encodeURIComponent(rawPhone)}`,
                        'verifinfosfi',
                        (httpInfosfi) => {
                            let infosfi = null;
                            try {
                                infosfi = JSON.parse(httpInfosfi.responseText);
                            } catch (err) {
                                return;
                            }
                            if (infosfi == null || Object.keys(infosfi).length < 1) {
                                document.querySelector('#pascompagniefid').value = '';
                                return;
                            }
                            if (AppRequestGuard.phonesMatch(infosfi.contact_client, rawPhone)) {
                                document.querySelector('#rclientfid').value = `${infosfi.nom_client || ''}`;
                                document.querySelector('#prnclientfid').value = `${infosfi.prenom_client || ''}`;
                                document.querySelector('#cnibfid').value = `${infosfi.num_CNIB || ''}`;
                                document.querySelector('#date_cnibfid').value = `${infosfi.date_delivre || ''}`;
                                document.querySelector('#lieudelivrefid').value = `${infosfi.lieu_delivre || ''}`;
                                document.querySelector('#pascompagniefid').value = `${infosfi.id_client || ''}`;
                                document.querySelector('#rclientcpfid').value = `${infosfi.nom_client || ''}`;
                                document.querySelector('#prnclientcpfid').value = `${infosfi.prenom_client || ''}`;
                                document.querySelector('#cnibcpfid').value = `${infosfi.num_CNIB || ''}`;
                                document.querySelector('#date_cnibcpfid').value = `${infosfi.date_delivre || ''}`;
                                document.querySelector('#lieudelivrecpfid').value = `${infosfi.lieu_delivre || ''}`;
                            } else {
                                document.querySelector('#pascompagniefid').value = '';
                            }
                        }
                    );
                }, 400);
            });
        }
            
            __venteFiWireCancelButton('idresetfid');
            __venteFiWireCancelButton('idresetfi');
                
                e.onclick = function () {   
                    let taFormfi = document.querySelector('#tafiForm');
                    
                    taFormfi.setAttribute('action', `${APP_ROOT}/Programmes/addpassagerfi/${e.dataset.cle_compagnie}`);
                    AppRequestGuard.ensureNonce('#tafiForm', 'sale_nonce');
                    AppRequestGuard.guardForm('#tafiForm');
                }

                var tafiFormEl = document.querySelector('#tafiForm');
                if (tafiFormEl && !tafiFormEl.dataset.salePrepared) {
                    tafiFormEl.dataset.salePrepared = '1';
                    tafiFormEl.addEventListener('submit', function (ev) {
                        AppRequestGuard.ensureNonce('#tafiForm', 'sale_nonce');
                        if (!__venteFiValidateTransitPrixBeforeSubmit()) {
                            ev.preventDefault();
                            return false;
                        }
                    });
                }

                AppRequestGuard.guardForm('#tafiForm');
                AppRequestGuard.ensureNonce('#tafiForm', 'sale_nonce');

                var venteFiAllowMultiEl = document.querySelector('#vente_fi_allow_multi');
                if (venteFiAllowMultiEl && !venteFiAllowMultiEl.dataset.bound) {
                    venteFiAllowMultiEl.dataset.bound = '1';
                    venteFiAllowMultiEl.addEventListener('change', function () {
                        __venteFiFillHeuresVente(window.__venteFiLastHeuresVente || []);
                    });
                }
                
    })

});
;
/* --- vente-escale-itineraire.js --- */
/**
 * Vente guichet / fidélité sur escales (itineraire_escales).
 * Case « Vente escale » : destination partielle ; pas de quartier (escales sans quartier).
 * Supporte le formulaire guichet (ids standards) et le formulaire FI (*fid).
 */
(function () {
    'use strict';

    function formatPrix(val) {
        var n = Number(val);
        if (!val && val !== 0 && val !== '0') return '';
        if (isNaN(n)) return String(val);
        return n.toLocaleString('fr-FR');
    }

    function appRoot() {
        return (typeof APP_ROOT !== 'undefined') ? APP_ROOT : '';
    }

    function $(sel) {
        return document.querySelector(sel);
    }

    function codeFromSelect(selId) {
        var el = $(selId);
        if (!el || !el.value) {
            return '';
        }
        return String(el.value).split('/')[0].trim();
    }

    function isPanelVisible(sel) {
        var tran = $(sel);
        if (!tran) return false;
        var d = (tran.style && tran.style.display) || '';
        if (d === 'none') return false;
        if (d === 'block' || d === 'flex') return true;
        return !!(tran.offsetParent || (tran.offsetWidth + tran.offsetHeight > 0));
    }

    /**
     * Champ Prix visible (#prix_axe_affiche) :
     * - vente directe → prix_axe
     * - correspondance → somme des prix des jambes déjà remplies (selon #nbrtrans)
     */
    function syncGuichetPrixAffiche() {
        var dst = $('#prix_axe_affiche');
        if (!dst) return;

        var parseMontant = function (raw) {
            if (raw === null || raw === undefined) return null;
            var s = String(raw).trim().replace(/\s/g, '').replace(',', '.');
            if (s === '') return null;
            var n = Number(s);
            return isNaN(n) ? null : n;
        };

        if (isPanelVisible('#tran')) {
            var nbrEl = $('#nbrtrans');
            var nbr = nbrEl ? parseInt(nbrEl.value, 10) : 0;
            if (nbr >= 2) {
                var ids = [
                    '#prix_axetrans',
                    '#prix_axetransit',
                    '#prix_axetransit1',
                    '#prix_axetransit2'
                ];
                var total = 0;
                var any = false;
                var max = Math.min(nbr, ids.length);
                for (var i = 0; i < max; i++) {
                    var el = $(ids[i]);
                    var n = parseMontant(el ? el.value : '');
                    if (n === null) continue;
                    total += n;
                    any = true;
                }
                dst.value = any ? formatPrix(total) : '';
                return;
            }
        }

        var src = $('#prix_axe');
        var v = src ? String(src.value || '').trim() : '';
        var direct = parseMontant(v);
        dst.value = direct === null ? '' : formatPrix(direct);
    }

    window.__venteSyncPrixAffiche = syncGuichetPrixAffiche;

    /** Intercepte les affectations .value sur les champs prix pour maj immédiate de l'affiche. */
    function watchGuichetPrixInputs() {
        var proto = HTMLInputElement.prototype;
        var desc = Object.getOwnPropertyDescriptor(proto, 'value');
        if (!desc || !desc.set || !desc.get) return;

        var ids = [
            'prix_axe',
            'prix_axetrans',
            'prix_axetransit',
            'prix_axetransit1',
            'prix_axetransit2'
        ];

        ids.forEach(function (id) {
            var el = document.getElementById(id);
            if (!el || el._ventePrixWatch) return;
            el._ventePrixWatch = true;
            Object.defineProperty(el, 'value', {
                configurable: true,
                enumerable: desc.enumerable,
                get: function () {
                    return desc.get.call(this);
                },
                set: function (v) {
                    desc.set.call(this, v);
                    try {
                        syncGuichetPrixAffiche();
                    } catch (e) {}
                }
            });
            el.addEventListener('input', syncGuichetPrixAffiche);
            el.addEventListener('change', syncGuichetPrixAffiche);
        });

        var nbr = document.getElementById('nbrtrans');
        if (nbr && !nbr._ventePrixWatch) {
            nbr._ventePrixWatch = true;
            nbr.addEventListener('change', syncGuichetPrixAffiche);
            nbr.addEventListener('input', syncGuichetPrixAffiche);
        }

        // Passage direct ↔ correspondance (display #tran) → recalcul immédiat.
        var tran = document.getElementById('tran');
        if (tran && !tran._ventePrixWatch && typeof MutationObserver !== 'undefined') {
            tran._ventePrixWatch = true;
            var mo = new MutationObserver(function () {
                syncGuichetPrixAffiche();
            });
            mo.observe(tran, { attributes: true, attributeFilter: ['style', 'class', 'hidden'] });
        }
    }

    var forms = [
        {
            key: 'guichet',
            tran: '#tran',
            check: '#escale_vente_check',
            fields: '#escale_dest_fields',
            select: '#escale_dest_select',
            help: '#escale_dest_help',
            idEsc: '#id_escale_vente',
            codeEsc: '#code_gadest_vente',
            nomEsc: '#nom_dest_vente',
            prix: '#prix_axe',
            prixAffiche: '#prix_axe_affiche',
            depargare: '#depargare',
            arrsgare: '#arrsgare',
            lign: '#lign',
            nomitin: '#nomitin',
            date: '#date_depheure',
            heure: '#hdepart',
            quartier: '#quartier',
            quartierLabel: '#idquart',
            showQuartier: function () {
                if (typeof window.__venteShowMainQuartier === 'function') {
                    window.__venteShowMainQuartier();
                    return;
                }
                setQuartierVisibleRaw(this, true);
            },
            hideQuartier: function () {
                if (typeof window.__venteHideMainQuartier === 'function') {
                    window.__venteHideMainQuartier();
                    return;
                }
                setQuartierVisibleRaw(this, false);
            }
        },
        {
            key: 'fi',
            tran: '#tranfid',
            check: '#escale_vente_check_fid',
            fields: '#escale_dest_fields_fid',
            select: '#escale_dest_select_fid',
            help: '#escale_dest_help_fid',
            idEsc: '#id_escale_ventefid',
            codeEsc: '#code_gadest_ventefid',
            nomEsc: '#nom_dest_ventefid',
            prix: '#prix_axefid',
            prixAffiche: null,
            depargare: '#depargarefid',
            arrsgare: '#arrsgarefid',
            lign: '#lignfid',
            nomitin: '#nomitinfid',
            date: '#date_depheurefid',
            heure: '#hdepartfid',
            quartier: '#quartierfid',
            quartierLabel: '#idquartfid',
            showQuartier: function () {
                setQuartierVisibleRaw(this, true);
            },
            hideQuartier: function () {
                setQuartierVisibleRaw(this, false);
            }
        },
        {
            key: 'cf',
            tran: '#trancf',
            check: '#escale_vente_check_cf',
            fields: '#escale_dest_fields_cf',
            select: '#escale_dest_select_cf',
            help: '#escale_dest_help_cf',
            idEsc: '#id_escale_ventecf',
            codeEsc: '#code_gadest_ventecf',
            nomEsc: '#nom_dest_ventecf',
            prix: '#prix_axecf',
            prixAffiche: null,
            depargare: '#confirm-0 #depargare',
            arrsgare: null,
            lign: '#axeconf',
            nomitin: '#axeconf',
            date: '#actuel',
            heure: '#heured',
            quartier: '#quartconf',
            quartierLabel: null,
            showQuartier: function () { setQuartierVisibleRaw(this, true); },
            hideQuartier: function () { setQuartierVisibleRaw(this, false); }
        }
    ];

    function setQuartierVisibleRaw(form, visible) {
        var wrap = null;
        var q = $(form.quartier);
        if (q) wrap = q.closest('.form-group');
        var label = $(form.quartierLabel);
        var sel = q;
        if (!visible && sel && sel.style.display !== 'none') {
            if (form.key === 'guichet') {
                window.__venteSavedQuartierValue = sel.value;
            } else {
                window.__venteFiSavedQuartierValue = sel.value;
            }
        }
        if (wrap) wrap.style.display = visible ? '' : 'none';
        if (label) label.style.display = visible ? 'block' : 'none';
        if (sel) {
            sel.style.display = visible ? 'block' : 'none';
            var saved = form.key === 'guichet' ? window.__venteSavedQuartierValue : window.__venteFiSavedQuartierValue;
            if (visible && saved != null && saved !== '') {
                sel.value = saved;
            }
        }
    }

    function createMainController(form) {
        var lastKey = null;
        var lastCataloguePrix = '';
        var cache = {};

        function syncPrixAffiche() {
            if (!form.prixAffiche) return;
            if (form.key === 'guichet') {
                syncGuichetPrixAffiche();
                return;
            }
            var src = $(form.prix);
            var dst = $(form.prixAffiche);
            if (!dst) return;
            var v = src ? String(src.value || '').trim() : '';
            dst.value = v === '' ? '' : formatPrix(v);
        }

        function isEscaleMode() {
            if (isPanelVisible(form.tran)) return false;
            var ck = $(form.check);
            return !!(ck && ck.checked);
        }

        function clearEscaleFields() {
            var idEl = $(form.idEsc);
            var codeEl = $(form.codeEsc);
            var nomEl = $(form.nomEsc);
            if (idEl) idEl.value = '';
            if (codeEl) codeEl.value = '';
            if (nomEl) nomEl.value = '';
        }

        function applyCataloguePrix() {
            var prixEl = $(form.prix);
            if (prixEl && lastCataloguePrix !== '') {
                prixEl.value = lastCataloguePrix;
            }
        }

        function setHelp(text, isWarn) {
            var help = $(form.help);
            if (!help) return;
            help.textContent = text;
            help.className = isWarn ? 'form-text text-danger' : 'form-text text-muted';
        }

        function parseList(raw) {
            if (Array.isArray(raw)) return raw;
            if (raw && typeof raw === 'object') {
                return Object.keys(raw).map(function (k) { return raw[k]; });
            }
            return [];
        }

        function hasEscaleSelected() {
            var idEl = $(form.idEsc);
            return !!(idEl && String(idEl.value || '').trim() !== '');
        }

        function syncEscaleVisibility() {
            var fields = $(form.fields);
            var sel = $(form.select);
            if (!fields) return;

            if (isEscaleMode()) {
                fields.style.display = 'block';
                refresh(true);
                if (hasEscaleSelected()) {
                    form.hideQuartier();
                } else {
                    form.showQuartier();
                }
            } else {
                fields.style.display = 'none';
                if (sel) sel.value = '';
                clearEscaleFields();
                applyCataloguePrix();
                form.showQuartier();
            }
        }

        function onEscaleChange() {
            if (!isEscaleMode()) {
                clearEscaleFields();
                applyCataloguePrix();
                form.showQuartier();
                return;
            }
            var sel = $(form.select);
            if (!sel) return;
            var opt = sel.options[sel.selectedIndex];
            if (!opt || !opt.value) {
                clearEscaleFields();
                applyCataloguePrix();
                form.showQuartier();
                setHelp('Choisissez l\'escale demandée par le client.', false);
                return;
            }
            $(form.idEsc).value = opt.value;
            $(form.codeEsc).value = opt.getAttribute('data-code') || '';
            $(form.nomEsc).value = opt.getAttribute('data-nom') || '';
            var prix = opt.getAttribute('data-prix');
            if (prix !== null && $(form.prix)) {
                $(form.prix).value = prix;
            }
            form.hideQuartier();
            setHelp('Escale sélectionnée — prix ' + Number(prix).toLocaleString('fr-FR') + ' F (sans quartier).', false);
            syncPrixAffiche();
        }

        function rememberCataloguePrix() {
            var prixEl = $(form.prix);
            var idEsc = $(form.idEsc);
            if (!prixEl) return;
            if (idEsc && idEsc.value) return;
            if (prixEl.value !== '') {
                lastCataloguePrix = prixEl.value;
            }
        }

        function fillSelect(escales, ligneNom) {
            var sel = $(form.select);
            if (!sel) return;

            var prev = sel.value;
            sel.options.length = 0;
            var placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = ligneNom
                ? ('Choisissez l\'escale (' + ligneNom + ')')
                : 'Choisissez l\'escale';
            sel.add(placeholder);

            if (!escales || !escales.length) {
                clearEscaleFields();
                setHelp('Aucune escale configurée sur ce trajet parent.', true);
                return;
            }

            for (var i = 0; i < escales.length; i++) {
                var e = escales[i];
                var opt = document.createElement('option');
                opt.value = e.id_escale;
                opt.setAttribute('data-code', e.code_gadest || '');
                opt.setAttribute('data-nom', e.nom_escale || e.arrivee_escale || '');
                opt.setAttribute('data-prix', e.prix_escale);
                var label = e.nom_escale || e.arrivee_escale || e.code_gadest;
                opt.textContent = label + ' — ' + Number(e.prix_escale).toLocaleString('fr-FR') + ' F';
                sel.add(opt);
            }

            setHelp(escales.length + ' escale(s) disponible(s) — sans quartier.', false);

            if (prev) {
                sel.value = prev;
                if (sel.value === prev) {
                    onEscaleChange();
                } else {
                    clearEscaleFields();
                }
            } else {
                clearEscaleFields();
            }
        }

        function loadByOd(gaexp, gadest) {
            if (!gaexp || !gadest) {
                fillSelect([], '');
                setHelp('Choisissez d\'abord l\'arrivée finale (ex. BOBO).', false);
                return;
            }
            var key = 'od:' + gaexp + '>' + gadest;
            if (cache[key]) {
                var cached = cache[key];
                fillSelect(cached, (cached[0] && cached[0].nom_ligne) || '');
                return;
            }
            setHelp('Chargement des escales…', false);
            var xhr = new XMLHttpRequest();
            xhr.open(
                'GET',
                window.location.origin + appRoot() + '/programmes/verifescalesod/' +
                    encodeURIComponent(gaexp) + '/' + encodeURIComponent(gadest),
                true
            );
            xhr.onload = function () {
                var list = [];
                try {
                    list = parseList(JSON.parse(xhr.responseText));
                } catch (err) {
                    list = [];
                }
                cache[key] = list;
                fillSelect(list, (list[0] && list[0].nom_ligne) || '');
            };
            xhr.onerror = function () {
                setHelp('Impossible de charger les escales.', true);
            };
            xhr.send();
        }

        function loadByLigne(ligne) {
            if (!ligne) return;
            var key = 'lg:' + ligne;
            if (cache[key]) {
                fillSelect(cache[key], ($(form.nomitin) && $(form.nomitin).value) || ligne);
                return;
            }
            var xhr = new XMLHttpRequest();
            xhr.open(
                'GET',
                window.location.origin + appRoot() + '/programmes/verifescales/' + encodeURIComponent(ligne),
                true
            );
            xhr.onload = function () {
                var list = [];
                try {
                    list = parseList(JSON.parse(xhr.responseText));
                } catch (err) {
                    list = [];
                }
                cache[key] = list;
                fillSelect(list, ($(form.nomitin) && $(form.nomitin).value) || ligne);
            };
            xhr.send();
        }

        function refresh(force) {
            rememberCataloguePrix();
            syncPrixAffiche();

            if (!isEscaleMode()) {
                if ($(form.idEsc) && $(form.idEsc).value) {
                    clearEscaleFields();
                }
                return;
            }

            var gaexp = form.depargare ? codeFromSelect(form.depargare) : '';
            var gadest = form.arrsgare ? codeFromSelect(form.arrsgare) : '';
            var lignEl = $(form.lign);
            var ligne = lignEl ? String(lignEl.value || '').trim() : '';
            var key = gaexp + '|' + gadest + '|' + ligne;

            if (!force && key === lastKey) {
                if ($(form.idEsc) && $(form.idEsc).value) {
                    var sel = $(form.select);
                    if (sel && sel.value) {
                        var opt = sel.options[sel.selectedIndex];
                        if (opt && opt.getAttribute('data-prix') && $(form.prix)) {
                            $(form.prix).value = opt.getAttribute('data-prix');
                        }
                    }
                }
                return;
            }
            lastKey = key;
            lastCataloguePrix = lastCataloguePrix || (($(form.prix) && $(form.prix).value) || '');

            if (gaexp && gadest) {
                loadByOd(gaexp, gadest);
            } else if (ligne) {
                loadByLigne(ligne);
            } else {
                fillSelect([], '');
                setHelp('Choisissez d\'abord l\'arrivée finale (ex. BOBO).', false);
            }
        }

        function boot() {
            if (!$(form.check)) return;

            var ck = $(form.check);
            if (ck && !ck._escaleBound) {
                ck.addEventListener('change', syncEscaleVisibility);
                ck._escaleBound = true;
            }

            var sel = $(form.select);
            if (sel && !sel._escaleBound) {
                sel.addEventListener('change', onEscaleChange);
                sel._escaleBound = true;
            }

            [form.arrsgare, form.depargare, form.date, form.heure].forEach(function (s) {
                var el = $(s);
                if (el && !el._escaleBound) {
                    el.addEventListener('change', function () {
                        lastKey = null;
                        setTimeout(function () { refresh(true); }, 200);
                    });
                    el._escaleBound = true;
                }
            });

            syncEscaleVisibility();
            syncPrixAffiche();
            setInterval(function () { refresh(false); }, 600);
        }

        return { boot: boot };
    }

    function bootAll() {
        forms.forEach(function (f) {
            createMainController(f).boot();
        });
        watchGuichetPrixInputs();
        syncGuichetPrixAffiche();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootAll);
    } else {
        bootAll();
    }
})();


/**
 * Escales sur les jambes de transit / correspondances (guichet + FI).
 * Pas de quartier sur les escales.
 */
(function () {
    'use strict';

    function appRoot() {
        return (typeof APP_ROOT !== 'undefined') ? APP_ROOT : '';
    }
    function $(sel) { return document.querySelector(sel); }

    function isShown(el) {
        if (!el) return false;
        if (el.style && el.style.display === 'none') return false;
        try {
            var cs = window.getComputedStyle(el);
            if (cs.display === 'none' || cs.visibility === 'hidden') return false;
        } catch (e) {}
        return true;
    }

    function makeLegs(sfx) {
        var f = sfx || '';
        var conf = (f === 'cf');
        var fid = (f === 'fid');
        return [
            {
                n: 1,
                sfx: f,
                nbr: conf ? '#nbrtranscf' : (fid ? '#nbrtransfid' : '#nbrtrans'),
                tran: conf ? '#trancf' : (fid ? '#tranfid' : '#tran'),
                ligne: conf ? '#ligntranscf' : (fid ? '#ligntransfid' : '#ligntrans'),
                lineUi: conf ? ['#lignecf1', '#lignesitinerairecf'] : (fid ? ['#ligne1fid', '#lignesitinerairefid'] : ['#ligne1', '#lignesitineraire']),
                prix: conf ? '#prix_axetranscf' : (fid ? '#prix_axetransfid' : '#prix_axetrans'),
                quartier: conf ? '#quartiercf1' : (fid ? '#quartier1fid' : '#quartier1'),
                quartierLabel: conf ? '#idquartcf1' : (fid ? '#idquart1fid' : '#idquart1'),
                mainQuartier: conf ? '#quartconf' : (fid ? '#quartierfid' : '#quartier'),
                mainQuartierLabel: conf ? null : (fid ? '#idquartfid' : '#idquart'),
                wrap: '#escale_leg_wrap_tr1' + f,
                check: '#escale_vente_check_tr1' + f,
                fields: '#escale_dest_fields_tr1' + f,
                select: '#escale_dest_select_tr1' + f,
                idEsc: '#id_escale_vente_tr1' + f,
                codeEsc: '#code_gadest_vente_tr1' + f,
                nomEsc: '#nom_dest_vente_tr1' + f
            },
            {
                // Dernière jambe si nbr=2 : le formulaire n'affiche que quartier1 / cf1 / fid1
                n: 2,
                sfx: f,
                nbr: conf ? '#nbrtranscf' : (fid ? '#nbrtransfid' : '#nbrtrans'),
                tran: conf ? '#trancf' : (fid ? '#tranfid' : '#tran'),
                ligne: conf ? '#ligntranscf1' : (fid ? '#ligntrans1fid' : '#ligntrans1'),
                lineUi: conf ? ['#arritincf1', '#idcheminscf'] : (fid ? ['#arritin1fid', '#idcheminsfid'] : ['#arritin1', '#idchemins']),
                prix: conf ? '#prix_axetransitcf' : (fid ? '#prix_axetransitfid' : '#prix_axetransit'),
                quartier: conf ? '#quartiercf1' : (fid ? '#quartier1fid' : '#quartier1'),
                quartierLabel: conf ? '#idquartcf1' : (fid ? '#idquart1fid' : '#idquart1'),
                mainQuartier: conf ? '#quartconf' : (fid ? '#quartierfid' : '#quartier'),
                mainQuartierLabel: conf ? null : (fid ? '#idquartfid' : '#idquart'),
                wrap: '#escale_leg_wrap_tr2' + f,
                check: '#escale_vente_check_tr2' + f,
                fields: '#escale_dest_fields_tr2' + f,
                select: '#escale_dest_select_tr2' + f,
                idEsc: '#id_escale_vente_tr2' + f,
                codeEsc: '#code_gadest_vente_tr2' + f,
                nomEsc: '#nom_dest_vente_tr2' + f
            },
            {
                // Dernière jambe si nbr=3 : quartier2 / cf2 (pas quartier3 = « Quartier transite4 »)
                n: 3,
                sfx: f,
                nbr: conf ? '#nbrtranscf' : (fid ? '#nbrtransfid' : '#nbrtrans'),
                tran: conf ? '#trancf' : (fid ? '#tranfid' : '#tran'),
                ligne: conf ? '#ligntranscf2' : (fid ? '#ligntrans2fid' : '#ligntrans2'),
                lineUi: conf ? ['#arritincf2', '#idcheminscf1'] : (fid ? ['#arritin2fid', '#idchemins1fid'] : ['#arritin2', '#idchemins1']),
                prix: conf ? '#prix_axetransitcf1' : (fid ? '#prix_axetransit1fid' : '#prix_axetransit1'),
                quartier: conf ? '#quartiercf2' : (fid ? '#quartier2fid' : '#quartier2'),
                quartierLabel: conf ? '#idquartcf2' : (fid ? '#idquart2fid' : '#idquart2'),
                mainQuartier: conf ? '#quartconf' : (fid ? '#quartierfid' : '#quartier'),
                mainQuartierLabel: conf ? null : (fid ? '#idquartfid' : '#idquart'),
                wrap: '#escale_leg_wrap_tr3' + f,
                check: '#escale_vente_check_tr3' + f,
                fields: '#escale_dest_fields_tr3' + f,
                select: '#escale_dest_select_tr3' + f,
                idEsc: '#id_escale_vente_tr3' + f,
                codeEsc: '#code_gadest_vente_tr3' + f,
                nomEsc: '#nom_dest_vente_tr3' + f
            },
            {
                // Dernière jambe si nbr=4 : quartier3 / cf3 (« Quartier transite4 »)
                n: 4,
                sfx: f,
                nbr: conf ? '#nbrtranscf' : (fid ? '#nbrtransfid' : '#nbrtrans'),
                tran: conf ? '#trancf' : (fid ? '#tranfid' : '#tran'),
                ligne: conf ? '#ligntranscf3' : (fid ? '#ligntrans3fid' : '#ligntrans3'),
                lineUi: conf ? ['#arritincf3', '#idcheminscf2'] : (fid ? ['#arritin3fid', '#idchemins2fid'] : ['#arritin3', '#idchemins2']),
                prix: conf ? '#prix_axetransitcf2' : (fid ? '#prix_axetransit2fid' : '#prix_axetransit2'),
                quartier: conf ? '#quartiercf3' : (fid ? '#quartier3fid' : '#quartier3'),
                quartierLabel: conf ? '#idquartcf3' : (fid ? '#idquart3fid' : '#idquart3'),
                mainQuartier: conf ? '#quartconf' : (fid ? '#quartierfid' : '#quartier'),
                mainQuartierLabel: conf ? null : (fid ? '#idquartfid' : '#idquart'),
                wrap: '#escale_leg_wrap_tr4' + f,
                check: '#escale_vente_check_tr4' + f,
                fields: '#escale_dest_fields_tr4' + f,
                select: '#escale_dest_select_tr4' + f,
                idEsc: '#id_escale_vente_tr4' + f,
                codeEsc: '#code_gadest_vente_tr4' + f,
                nomEsc: '#nom_dest_vente_tr4' + f
            }
        ];
    }

    var cache = {};
    var legs = makeLegs('').concat(makeLegs('fid')).concat(makeLegs('cf'));
    var lastLigne = {};
    var cataloguePrix = {};
    var hasEscales = {};
    var loading = {};
    var savedLegQuartiers = {};

    function parseList(raw) {
        if (Array.isArray(raw)) return raw;
        if (raw && typeof raw === 'object') {
            return Object.keys(raw).map(function (k) { return raw[k]; });
        }
        return [];
    }

    function clearLeg(leg) {
        var idEl = $(leg.idEsc);
        var codeEl = $(leg.codeEsc);
        var nomEl = $(leg.nomEsc);
        var sel = $(leg.select);
        if (idEl) idEl.value = '';
        if (codeEl) codeEl.value = '';
        if (nomEl) nomEl.value = '';
        if (sel) sel.value = '';
    }

    function isTranVisible(leg) {
        return isShown($(leg.tran));
    }

    function lineUiVisible(leg) {
        if (!leg.lineUi || !leg.lineUi.length) return isTranVisible(leg);
        for (var i = 0; i < leg.lineUi.length; i++) {
            if (isShown($(leg.lineUi[i]))) return true;
        }
        return false;
    }

    function nbrTrans(leg) {
        var el = $(leg.nbr);
        var n = el ? parseInt(el.value, 10) : 0;
        return isNaN(n) ? 0 : n;
    }

    function isLastTransitLeg(leg) {
        var nbr = nbrTrans(leg);
        if (nbr < 1) return false;
        return leg.n === nbr;
    }

    function canShowEscaleLeg(leg) {
        return isTranVisible(leg) && lineUiVisible(leg) && isLastTransitLeg(leg);
    }

    function quartierTargets(leg) {
        var out = [];
        var nbr = nbrTrans(leg);
        if (nbr > 0 && leg.n === nbr) {
            out.push({ sel: leg.mainQuartier, label: leg.mainQuartierLabel });
        }
        if (leg.quartier) {
            out.push({ sel: leg.quartier, label: leg.quartierLabel });
        }
        var seen = {};
        return out.filter(function (t) {
            if (seen[t.sel]) return false;
            seen[t.sel] = true;
            return true;
        });
    }

    function hideQuartierForEscale(leg) {
        var targets = quartierTargets(leg);
        for (var i = 0; i < targets.length; i++) {
            var key = targets[i].sel;
            var q = $(key);
            var lab = targets[i].label ? $(targets[i].label) : null;
            var wrap = q ? q.closest('.form-group') : null;
            var visible = !(wrap && wrap.style.display === 'none') && !(q && q.style.display === 'none');
            if (q && visible) {
                savedLegQuartiers[key] = q.value;
                if (key === '#quartier') {
                    window.__venteSavedQuartierValue = q.value;
                }
                if (key === '#quartierfid') {
                    window.__venteFiSavedQuartierValue = q.value;
                }
            }
            if (q) q.style.display = 'none';
            if (lab) lab.style.display = 'none';
            if (wrap) wrap.style.display = 'none';
        }
        if (!leg.sfx && typeof window.__venteHideMainQuartier === 'function') {
            window.__venteHideMainQuartier();
        }
    }

    function showQuartierAfterEscale(leg) {
        var targets = quartierTargets(leg);
        for (var i = 0; i < targets.length; i++) {
            var key = targets[i].sel;
            var q = $(key);
            var lab = targets[i].label ? $(targets[i].label) : null;
            var wrap = q ? q.closest('.form-group') : null;
            if (q) {
                q.style.display = 'block';
                if (savedLegQuartiers[key] != null && savedLegQuartiers[key] !== '') {
                    q.value = savedLegQuartiers[key];
                }
            }
            if (lab) lab.style.display = 'block';
            if (wrap) wrap.style.display = '';
        }
        if (!leg.sfx && typeof window.__venteShowMainQuartier === 'function') {
            window.__venteShowMainQuartier();
        }
    }

    function showWrap(leg, show) {
        var wrap = $(leg.wrap);
        if (!wrap) return;
        wrap.style.display = show ? 'block' : 'none';
        if (!show) {
            var ck = $(leg.check);
            var fields = $(leg.fields);
            if (ck) ck.checked = false;
            if (fields) fields.style.display = 'none';
            clearLeg(leg);
        }
    }

    function applyCatalogue(leg) {
        var prixEl = $(leg.prix);
        var ck = leg.sfx + ':' + leg.n;
        if (prixEl && cataloguePrix[ck] !== undefined && cataloguePrix[ck] !== '') {
            prixEl.value = cataloguePrix[ck];
        }
    }

    function rememberPrix(leg) {
        var prixEl = $(leg.prix);
        var idEsc = $(leg.idEsc);
        var ck = leg.sfx + ':' + leg.n;
        if (!prixEl) return;
        if (idEsc && idEsc.value) return;
        if (prixEl.value !== '') {
            cataloguePrix[ck] = prixEl.value;
        }
    }

    function fillSelect(leg, escales) {
        var sel = $(leg.select);
        if (!sel) return;

        var prev = sel.value;
        sel.options.length = 0;
        var ph = document.createElement('option');
        ph.value = '';
        ph.textContent = 'Choisissez l\'escale';
        sel.add(ph);

        var hk = leg.sfx + ':' + leg.n;
        hasEscales[hk] = !!(escales && escales.length);

        if (!hasEscales[hk]) {
            showWrap(leg, false);
            return;
        }

        for (var i = 0; i < escales.length; i++) {
            var e = escales[i];
            var opt = document.createElement('option');
            opt.value = e.id_escale;
            opt.setAttribute('data-code', e.code_gadest || '');
            opt.setAttribute('data-nom', e.nom_escale || e.arrivee_escale || '');
            opt.setAttribute('data-prix', e.prix_escale);
            var label = e.nom_escale || e.arrivee_escale || e.code_gadest;
            opt.textContent = label + ' — ' + Number(e.prix_escale).toLocaleString('fr-FR') + ' F';
            sel.add(opt);
        }

        showWrap(leg, canShowEscaleLeg(leg));

        if (prev) {
            sel.value = prev;
            if (sel.value === prev) onSelect(leg);
        }
    }

    function loadEscales(leg, ligne) {
        if (!ligne) {
            fillSelect(leg, []);
            return;
        }
        if (cache[ligne]) {
            fillSelect(leg, cache[ligne]);
            return;
        }
        if (loading[ligne]) return;
        loading[ligne] = true;
        var xhr = new XMLHttpRequest();
        xhr.open('GET', window.location.origin + appRoot() + '/programmes/verifescales/' + encodeURIComponent(ligne), true);
        xhr.onload = function () {
            loading[ligne] = false;
            var list = [];
            try { list = parseList(JSON.parse(xhr.responseText)); } catch (e) { list = []; }
            cache[ligne] = list;
            fillSelect(leg, list);
        };
        xhr.onerror = function () { loading[ligne] = false; };
        xhr.send();
    }

    function onCheck(leg) {
        var ck = $(leg.check);
        var fields = $(leg.fields);
        if (!ck || !fields) return;
        if (ck.checked) {
            fields.style.display = 'block';
            onSelect(leg);
        } else {
            fields.style.display = 'none';
            clearLeg(leg);
            applyCatalogue(leg);
            showQuartierAfterEscale(leg);
        }
    }

    function onSelect(leg) {
        var ck = $(leg.check);
        if (!ck || !ck.checked) {
            clearLeg(leg);
            showQuartierAfterEscale(leg);
            return;
        }
        var sel = $(leg.select);
        if (!sel) return;
        var opt = sel.options[sel.selectedIndex];
        if (!opt || !opt.value) {
            clearLeg(leg);
            applyCatalogue(leg);
            showQuartierAfterEscale(leg);
            return;
        }
        var idEl = $(leg.idEsc);
        var codeEl = $(leg.codeEsc);
        var nomEl = $(leg.nomEsc);
        if (idEl) idEl.value = opt.value;
        if (codeEl) codeEl.value = opt.getAttribute('data-code') || '';
        if (nomEl) nomEl.value = opt.getAttribute('data-nom') || '';
        var prix = opt.getAttribute('data-prix');
        var prixEl = $(leg.prix);
        if (prix !== null && prixEl) prixEl.value = prix;
        hideQuartierForEscale(leg);
    }

    function tickLeg(leg) {
        rememberPrix(leg);
        var lignEl = $(leg.ligne);
        var ligne = lignEl ? String(lignEl.value || '').trim() : '';
        var wrap = $(leg.wrap);
        var visibleContext = canShowEscaleLeg(leg);
        var hk = leg.sfx + ':' + leg.n;
        var lk = leg.sfx + ':' + leg.n;

        if (!isLastTransitLeg(leg)) {
            showWrap(leg, false);
            return;
        }

        if (!visibleContext) {
            if (wrap) wrap.style.display = 'none';
            return;
        }

        if (ligne !== (lastLigne[lk] || '')) {
            lastLigne[lk] = ligne;
            cataloguePrix[hk] = '';
            hasEscales[hk] = false;
            clearLeg(leg);
            var ck = $(leg.check);
            if (ck) ck.checked = false;
            var fields = $(leg.fields);
            if (fields) fields.style.display = 'none';
            if (!ligne) {
                showWrap(leg, false);
                return;
            }
            loadEscales(leg, ligne);
            return;
        }

        if (ligne && hasEscales[hk]) {
            showWrap(leg, true);
            if ($(leg.check) && $(leg.check).checked) {
                if ($(leg.idEsc) && $(leg.idEsc).value) {
                    onSelect(leg);
                } else {
                    showQuartierAfterEscale(leg);
                }
            }
        } else if (ligne && cache[ligne]) {
            fillSelect(leg, cache[ligne]);
        } else if (ligne && !cache[ligne]) {
            loadEscales(leg, ligne);
        } else {
            showWrap(leg, false);
        }
    }

    function boot() {
        legs.forEach(function (leg) {
            if (!$(leg.wrap) && !$(leg.check)) return;
            var ck = $(leg.check);
            var sel = $(leg.select);
            if (ck && !ck._escaleTrBound) {
                ck.addEventListener('change', function () { onCheck(leg); });
                ck._escaleTrBound = true;
            }
            if (sel && !sel._escaleTrBound) {
                sel.addEventListener('change', function () { onSelect(leg); });
                sel._escaleTrBound = true;
            }
        });
        setInterval(function () {
            legs.forEach(tickLeg);
        }, 400);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();

