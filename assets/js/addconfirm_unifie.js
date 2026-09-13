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
