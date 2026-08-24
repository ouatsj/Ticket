document.addEventListener('DOMContentLoaded', () => {

    function __venteSetTaTitle(txt) {
        var t = document.querySelector('h3#taTitle') || document.querySelector('#taTitle');
        if (t) t.textContent = txt || 'VENTE DE TICKET';
    }
    __venteSetTaTitle('VENTE DE TICKET');

    function __venteSetDisplay(id, display) {
        var el = document.querySelector('#' + id);
        if (el) el.style.display = display;
    }
    function __venteSafeReset(sel, keepOpts) {
        var el = document.querySelector(sel);
        if (!el) return;
        if (el.options) el.options.length = (typeof keepOpts === 'number') ? keepOpts : 1;
        else if ('value' in el) el.value = '';
    }
    /** Valeur quartier mémorisée avant masquage escale (restaurée à la désactivation). */
    window.__venteSavedQuartierValue = window.__venteSavedQuartierValue || null;

    /** Vrai seulement si une escale est réellement choisie (pas juste la case cochée). */
    function __venteIsEscaleSaleActive() {
        var idEsc = document.querySelector('#id_escale_vente');
        if (idEsc && String(idEsc.value || '').trim() !== '') return true;
        var nbrEl = document.querySelector('#nbrtrans');
        var nbr = nbrEl ? parseInt(nbrEl.value, 10) : 0;
        if (!isNaN(nbr) && nbr >= 2) {
            var idTr = document.querySelector('#id_escale_vente_tr' + nbr);
            if (idTr && String(idTr.value || '').trim() !== '') return true;
        }
        return false;
    }
    function __venteHideMainQuartier() {
        var q = document.querySelector('#quartier');
        var lab = document.querySelector('#idquart');
        var wrap = q && q.closest ? q.closest('.form-group') : null;
        var visible = true;
        if (wrap && wrap.style.display === 'none') visible = false;
        if (q && q.style.display === 'none') visible = false;
        // Mémoriser seulement si encore visible (ne pas écraser avec '').
        if (q && visible) {
            window.__venteSavedQuartierValue = q.value;
        }
        if (wrap) wrap.style.display = 'none';
        if (lab) lab.style.display = 'none';
        if (q) q.style.display = 'none';
        // Ne pas vider q.value — restauration à la désactivation.
    }
    function __venteShowMainQuartier() {
        // Vente escale active : rester masqué (sans vider).
        if (__venteIsEscaleSaleActive()) {
            __venteHideMainQuartier();
            return;
        }
        var q = document.querySelector('#quartier');
        var lab = document.querySelector('#idquart');
        var wrap = q && q.closest ? q.closest('.form-group') : null;
        if (wrap) wrap.style.display = '';
        if (lab) lab.style.display = 'block';
        if (q) {
            q.style.display = 'block';
            if (window.__venteSavedQuartierValue != null && window.__venteSavedQuartierValue !== '') {
                q.value = window.__venteSavedQuartierValue;
            }
        }
    }
    window.__venteIsEscaleSaleActive = __venteIsEscaleSaleActive;
    window.__venteHideMainQuartier = __venteHideMainQuartier;
    window.__venteShowMainQuartier = __venteShowMainQuartier;


    /** Marge min. correspondance (alignée graphe_correspondance_marge_min). */
    var __VENTE_TRANSIT_MARGE_MIN = 30;

    function __venteHeureToMinutes(h) {
        if (h == null || h === '') return null;
        var parts = String(h).trim().split(/[:hH]/);
        if (!parts || !parts.length) return null;
        var hh = parseInt(parts[0], 10);
        if (isNaN(hh)) return null;
        var mm = (parts[1] != null && parts[1] !== '') ? parseInt(parts[1], 10) : 0;
        if (isNaN(mm)) mm = 0;
        return (hh * 60) + mm;
    }

    function __venteFormatDateShort(ymd) {
        if (!ymd || String(ymd).length < 10) return '';
        var p = String(ymd).slice(0, 10).split('-');
        return (p.length === 3) ? (p[2] + '/' + p[1]) : String(ymd).slice(0, 10);
    }

    /** Remplit #hdepartitine avec J et J+1 (libellé date si ≠ date voyage). */
    function __venteFillHeureItineSelect(selectEl, rows) {
        var sel = typeof selectEl === 'string' ? document.querySelector(selectEl) : selectEl;
        if (!sel) return;
        sel.options.length = 1;
        if (!rows) return;
        var list = Array.isArray(rows) ? rows
            : (typeof rows === 'object' ? Object.keys(rows).map(function (k) { return rows[k]; }) : []);
        var dateEl = document.querySelector('#date_depheure') || document.querySelector('#date_depheurefid');
        var voyageDate = dateEl ? String(dateEl.value || '').slice(0, 10) : '';
        for (var i = 0; i < list.length; i++) {
            var row = list[i];
            if (!row || row.id_ligneheure == null || row.heure == null) continue;
            var opt = document.createElement('option');
            var dprog = row.date_progr ? String(row.date_progr).slice(0, 10) : '';
            opt.value = String(row.id_ligneheure) + '/' + String(row.heure);
            if (dprog) {
                opt.setAttribute('data-date-progr', dprog);
            }
            var label = String(row.heure);
            if (dprog && voyageDate && dprog !== voyageDate) {
                label = label + ' — ' + __venteFormatDateShort(dprog);
            }
            opt.innerHTML = label;
            sel.add(opt);
        }
    }
    window.__venteFillHeureItineSelect = __venteFillHeureItineSelect;

    function __venteClearDownstreamCheminHeures() {
        ['idcheminsheur', 'idcheminsheur1', 'idcheminsheur2'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.options.length = 1;
        });
        ['psiegesitines1', 'psiegesitines2', 'psiegesitines3'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.options.length = 1;
        });
    }

    /**
     * Ancre jambe précédente pour filtrer les heures de nextLegKey (tr2|tr3|tr4).
     * date + minutes + marge.
     */
    function __venteGetPrevTransitAnchor(nextLegKey) {
        var voyageDate = document.querySelector('#date_depheure')
            ? String(document.querySelector('#date_depheure').value || '').slice(0, 10) : '';
        var out = { date: voyageDate, heure: '', minutes: null, marge: __VENTE_TRANSIT_MARGE_MIN };

        function fromCheminSelect(heurId) {
            var hs = document.getElementById(heurId);
            if (!hs || hs.selectedIndex < 1) return false;
            var opt = hs.options[hs.selectedIndex];
            var date = opt.getAttribute('data-date-progr') || '';
            var heure = opt.getAttribute('data-heure') || '';
            var gkey = opt.getAttribute('data-group-key') || '';
            var groups = (window.__venteCheminGroups && window.__venteCheminGroups[heurId]) || {};
            var g = groups[gkey] || groups[opt.value] || null;
            if (g && g.rows && g.rows.length) {
                if (!date && g.rows[0].date_progr) date = String(g.rows[0].date_progr).slice(0, 10);
                if (!heure && g.rows[0].heure) heure = String(g.rows[0].heure);
            }
            // Après choix siège/prog : value = code/i1/i2/lh/prix — retrouver via code_progr.
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
            out.minutes = __venteHeureToMinutes(heure);
            return out.minutes != null;
        }

        if (nextLegKey === 'tr2') {
            var dEl = document.querySelector('#dateprtrans');
            var hEl = document.querySelector('#hertrans');
            var date = (dEl && dEl.value) ? String(dEl.value).slice(0, 10) : voyageDate;
            var heure = (hEl && hEl.value) ? String(hEl.value) : '';
            if (!heure) {
                var hs1 = document.getElementById('hdepartitine');
                if (hs1 && hs1.selectedIndex > 0) {
                    var parts = String(hs1.options[hs1.selectedIndex].value || '').split('/');
                    if (parts[1]) heure = parts[1];
                }
            }
            out.date = date || voyageDate;
            out.heure = heure;
            out.minutes = __venteHeureToMinutes(heure);
            return out;
        }
        if (nextLegKey === 'tr3') {
            fromCheminSelect('idcheminsheur');
            return out;
        }
        if (nextLegKey === 'tr4') {
            fromCheminSelect('idcheminsheur1');
            return out;
        }
        return out;
    }

    /** true si le programme row est après l'ancre (même jour + marge, ou jour suivant). */
    function __venteRowIsAfterPrev(row, prev) {
        if (!prev || prev.minutes == null || !prev.date) return true;
        var rd = row && row.date_progr ? String(row.date_progr).slice(0, 10) : '';
        var rm = __venteHeureToMinutes(row && row.heure);
        if (!rd || rm == null) return false;
        if (rd > prev.date) return true;
        if (rd < prev.date) return false;
        var marge = (prev.marge != null) ? prev.marge : __VENTE_TRANSIT_MARGE_MIN;
        return rm >= (prev.minutes + marge);
    }


    /** Remplit un select d'heures transit (chemintr), filtré vs jambe précédente (marge + J/J+1). */
    function __venteFillCheminHeures(selectSel, rows, legKey) {
        var sel = typeof selectSel === 'string' ? document.querySelector(selectSel) : selectSel;
        if (!sel) return;
        sel.options.length = 1;
        var list = Array.isArray(rows) ? rows
            : (rows && typeof rows === 'object' ? Object.keys(rows).map(function (k) { return rows[k]; }) : []);
        var prev = legKey ? __venteGetPrevTransitAnchor(legKey) : null;
        if (prev && prev.minutes != null && prev.date) {
            list = list.filter(function (row) { return __venteRowIsAfterPrev(row, prev); });
        }
        var voyageDate = document.querySelector('#date_depheure')
            ? String(document.querySelector('#date_depheure').value || '').slice(0, 10) : '';
        var groups = {};
        var order = [];
        for (var i = 0; i < list.length; i++) {
            var row = list[i];
            if (!row || row.code_progr == null || row.code_progr === '') continue;
            var lh = String(row.id_ligneheure != null ? row.id_ligneheure : '');
            if (!lh) continue;
            var dprog = row.date_progr ? String(row.date_progr).slice(0, 10) : '';
            // Clé date|id_lh : évite de fusionner le même créneau sur J et J+1.
            var gkey = dprog + '|' + lh;
            if (!groups[gkey]) {
                groups[gkey] = {
                    heure: row.heure || '',
                    date_progr: dprog,
                    minutes: __venteHeureToMinutes(row.heure),
                    rows: []
                };
                order.push(gkey);
            }
            var exists = false;
            for (var j = 0; j < groups[gkey].rows.length; j++) {
                if (String(groups[gkey].rows[j].code_progr) === String(row.code_progr)) {
                    exists = true;
                    break;
                }
            }
            if (!exists) groups[gkey].rows.push(row);
        }
        order.sort(function (a, b) {
            var ga = groups[a], gb = groups[b];
            var da = ga.date_progr || '', db = gb.date_progr || '';
            if (da < db) return -1;
            if (da > db) return 1;
            var ma = ga.minutes != null ? ga.minutes : 0;
            var mb = gb.minutes != null ? gb.minutes : 0;
            return ma - mb;
        });
        if (!window.__venteCheminGroups) window.__venteCheminGroups = {};
        window.__venteCheminGroups[sel.id] = groups;
        for (var k = 0; k < order.length; k++) {
            var key = order[k];
            var g = groups[key];
            var opt = document.createElement('option');
            opt.value = key;
            opt.setAttribute('data-group-key', key);
            opt.setAttribute('data-date-progr', g.date_progr || '');
            opt.setAttribute('data-heure', g.heure || '');
            var label = g.heure || key;
            if (g.date_progr && voyageDate && g.date_progr !== voyageDate) {
                label = (g.heure || '') + ' — ' + __venteFormatDateShort(g.date_progr);
            }
            if (g.rows.length > 1) {
                label = label + ' (' + g.rows.length + ' départs)';
            }
            opt.innerHTML = label;
            sel.add(opt);
        }
        if (legKey) {
            __venteWireCheminHeur(sel.id, legKey);
        }
    }


    window.__venteHasTransit = false;
    window.__venteLastHeuresVente = [];
    window.__venteApplyTransitLegs = null; // assigné après chargement des jambes (closures)

    function __venteResetMainEscaleUi() {
        var ck = document.querySelector('#escale_vente_check');
        if (ck) ck.checked = false;
        ['#id_escale_vente', '#code_gadest_vente', '#nom_dest_vente'].forEach(function (s) {
            var el = document.querySelector(s);
            if (el) el.value = '';
        });
        var sel = document.querySelector('#escale_dest_select');
        if (sel) {
            sel.innerHTML = '';
            var o = document.createElement('option');
            o.value = '';
            o.textContent = 'Choisir une escale…';
            sel.appendChild(o);
            sel.value = '';
        }
        var fields = document.querySelector('#escale_dest_fields');
        if (fields) fields.style.display = 'none';
    }

    /** Case « Vente escale » principale : utile en vente directe uniquement (pas en transit). */
    function __venteSetMainEscaleVisible(visible) {
        var wrap = document.querySelector('#escale_dest_wrap');
        if (!wrap) return;
        if (!visible) {
            __venteResetMainEscaleUi();
            wrap.style.display = 'none';
        } else {
            wrap.style.display = '';
        }
    }
    window.__venteSetMainEscaleVisible = __venteSetMainEscaleVisible;

    function __venteHideTransitPanel() {
        var tran = document.querySelector('#tran');
        if (tran) tran.style.display = 'none';
        ['#hdepartitine','#psiegesitines','#lignesitineraire','#ligne1','#siegitine',
         '#heureitin','#idchemins','#idcheminsheur','#psiegesitines1','#idchemins1',
         '#idcheminsheur1','#psiegesitines2','#idchemins2','#idcheminsheur2','#psiegesitines3',
         '#transitedepargare1','#transitedepargare2','#transitedepargare3','#transitedepargare4',
         '#arritin1','#arritin2','#arritin3','#heureitin1','#heureitin2','#heureitin3',
         '#siegitine1','#siegitine2','#siegitine3','#quartier1','#quartier2','#quartier3',
         '#idquart1','#idquart2','#idquart3','#iddeptrans1','#iddeptrans2','#iddeptrans3','#iddeptrans4',
         '#selprog_box_tr1','#selprog_box_tr2','#selprog_box_tr3','#selprog_box_tr4'
        ].forEach(function (s) {
            var el = document.querySelector(s);
            if (el) el.style.display = 'none';
        });
        __venteHideAllTransitProgSelects();
        ['depitin1','depargareitine1','arrsgareitine1','hdepartitine1',
         'depitin2','depargareitine2','arrsgareitine2','hdepartitine2',
         'depitin3','depargareitine3','arrsgareitine3','hdepartitine3'].forEach(function (id) {
            __venteSetDisplay(id, 'none');
        });
    }

    function __venteShowDirectHourUi() {
        __venteHideTransitPanel();
        ['#hrid','#hdepart','#sigid','#psieges','#iddep','#depargare','#arrid','#arrsgare'].forEach(function (s) {
            var el = document.querySelector(s);
            if (el) el.style.display = 'block';
        });
        __venteShowMainQuartier();
        __venteHideProgSelect();
        __venteSetMainEscaleVisible(true);
    }

    function __venteProgListFromResponse(don) {
        if (don == null || don === '') return [];
        if (Array.isArray(don)) return don.filter(Boolean);
        if (typeof don === 'object') {
            return Object.keys(don).map(function (k) { return don[k]; }).filter(Boolean);
        }
        return [];
    }

    function __venteHideProgSelect() {
        var box = document.getElementById('selprog_box');
        var sel = document.getElementById('selprog');
        if (box) box.style.display = 'none';
        if (sel) {
            sel.options.length = 1;
            sel.value = '';
            sel.onchange = null;
            sel.style.display = '';
        }
    }

    function __venteLabelProg(p) {
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

    function __venteApplyProgFields(p) {
        if (!p) return;
        var set = function (id, val) {
            var el = document.querySelector(id);
            if (el) el.value = val == null ? '' : String(val);
        };
        set('#program', p.code_progr);
        set('#tarifattrib', p.typetarif);
        set('#datepr', p.date_progr);
        set('#depligne', p.gareidentif);
        set('#inter1', p.intervalle1);
        set('#inter2', p.intervalle2);
        set('#lign', p.ident_ligne);
        set('#nomitin', p.nom_ligne);
        set('#her', p.heure);
        set('#cate', p.categori);
    }

    function __venteLoadSiegesDirect(idLigneheure, dptDate) {
        var ps = document.querySelector('#psieges');
        if (ps) ps.options.length = 1;
        var tfbsEl = document.querySelector('#tarifattrib');
        var tfbs = tfbsEl ? tfbsEl.value : '';
        if (idLigneheure && tfbs) {
            var httpPrix = new XMLHttpRequest();
            httpPrix.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifpriprg/${idLigneheure}/${tfbs}`, true);
            httpPrix.onload = function () {
                try {
                    var donprix = JSON.parse(httpPrix.responseText);
                    if (Object.entries(donprix).length >= 1) {
                        for (var key in Object.entries(donprix)) {
                            var px = document.querySelector('#prix_axe');
                            if (px) px.value = `${donprix[key].prix}`;
                        }
                    }
                } catch (e) {}
            };
            httpPrix.setRequestHeader('Content-Type', 'application/json');
            httpPrix.send();
        }
        var cdprog = document.querySelector('#program') ? document.querySelector('#program').value : '';
        var db = document.querySelector('#inter1') ? document.querySelector('#inter1').value : '';
        var fn = document.querySelector('#inter2') ? document.querySelector('#inter2').value : '';
        var lg = document.querySelector('#nomitin') ? document.querySelector('#nomitin').value : '';
        var tim = document.querySelector('#her') ? document.querySelector('#her').value : '';
        if (!cdprog) return;
        var httpRequette = new XMLHttpRequest();
        httpRequette.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${cdprog}/${dptDate}/${lg}/${tim}/${db}/${fn}`, true);
        httpRequette.onload = function () {
            try {
                var datta = JSON.parse(httpRequette.responseText);
                if (ps) ps.options.length = 1;
                if (Object.entries(datta).length >= 1) {
                    for (var key2 in Object.entries(datta)) {
                        var opt = document.createElement('option');
                        opt.value = `${datta[key2].siege_num}`;
                        opt.innerHTML = `${datta[key2].siege_num}`;
                        if (ps) ps.add(opt);
                    }
                }
            } catch (e2) {
                if (ps) ps.options.length = 1;
            }
        };
        httpRequette.setRequestHeader('Content-Type', 'application/json');
        httpRequette.send();
    }

    /**
     * Après verifprog : N=0 → false (creedepart) ; N=1 auto ; N>1 sélecteur.
     */
    function __venteHandleProgList(don, idLigneheure, dptDate) {
        var list = __venteProgListFromResponse(don);
        __venteHideProgSelect();
        var ps = document.querySelector('#psieges');
        if (ps) ps.options.length = 1;
        if (list.length === 0) {
            return false;
        }
        if (list.length === 1) {
            __venteApplyProgFields(list[0]);
            __venteLoadSiegesDirect(idLigneheure, dptDate);
            return true;
        }
        var box = document.getElementById('selprog_box');
        var sel = document.getElementById('selprog');
        if (!sel) {
            // Pas de UI multi : prendre le plus récent (comportement historique).
            __venteApplyProgFields(list[0]);
            __venteLoadSiegesDirect(idLigneheure, dptDate);
            return true;
        }
        if (box) box.style.display = 'block';
        if (sel) sel.style.display = 'block';
        sel.options.length = 1;
        for (var i = 0; i < list.length; i++) {
            var p = list[i];
            var opt = document.createElement('option');
            opt.value = String(i);
            opt.innerHTML = __venteLabelProg(p);
            sel.add(opt);
        }
        sel.onchange = function () {
            if (ps) ps.options.length = 1;
            var idx = parseInt(sel.value, 10);
            if (isNaN(idx) || !list[idx]) {
                __venteApplyProgFields({});
                return;
            }
            __venteApplyProgFields(list[idx]);
            __venteLoadSiegesDirect(idLigneheure, dptDate);
        };
        return true;
    }

    function __venteHideProgSelectAny(boxId, selId) {
        var box = document.getElementById(boxId);
        var sel = document.getElementById(selId);
        if (box) box.style.display = 'none';
        if (sel) {
            sel.options.length = 1;
            sel.value = '';
            sel.onchange = null;
            // ne pas forcer display:none sur le select : le box parent suffit
            sel.style.display = '';
        }
    }

    function __venteShowProgSelectAny(boxId, selId) {
        var box = document.getElementById(boxId);
        var sel = document.getElementById(selId);
        if (box) box.style.display = 'block';
        if (sel) sel.style.display = 'block';
    }

    function __venteHideAllTransitProgSelects() {
        [
            ['selprog_box_tr1', 'selprog_tr1'],
            ['selprog_box_tr2', 'selprog_tr2'],
            ['selprog_box_tr3', 'selprog_tr3'],
            ['selprog_box_tr4', 'selprog_tr4']
        ].forEach(function (pair) {
            __venteHideProgSelectAny(pair[0], pair[1]);
        });
    }

    var __venteCheminLegCfg = {
        tr2: {
            heur: 'idcheminsheur', progBox: 'selprog_box_tr2', progSel: 'selprog_tr2',
            sieges: 'psiegesitines1', prix: 'prix_axetransit', cate: 'catetransit',
            gid: 'gidtrans', nom: 'nomitintrans1', lign: 'ligntrans1', depGare: 'transitedepargare2',
            idtampo: 'idtampo1', siegselect: 'siegselect1'
        },
        tr3: {
            heur: 'idcheminsheur1', progBox: 'selprog_box_tr3', progSel: 'selprog_tr3',
            sieges: 'psiegesitines2', prix: 'prix_axetransit1', cate: 'catetransit1',
            gid: 'gidtrans1', nom: 'nomitintrans2', lign: 'ligntrans2', depGare: 'transitedepargare3',
            idtampo: 'idtampo2', siegselect: 'siegselect2'
        },
        tr4: {
            heur: 'idcheminsheur2', progBox: 'selprog_box_tr4', progSel: 'selprog_tr4',
            sieges: 'psiegesitines3', prix: 'prix_axetransit2', cate: 'catetransit2',
            gid: 'gidtrans2', nom: 'nomitintrans3', lign: 'ligntrans3', depGare: 'transitedepargare4',
            idtampo: 'idtampo3', siegselect: 'siegselect3'
        }
    };

    function __venteCheminRowValue(row) {
        if (!row) return '';
        return String(row.code_progr) + '/' + row.intervalle1 + '/' + row.intervalle2 + '/'
            + row.id_ligneheure + '/' + (row.prix != null ? row.prix : '');
    }

    function __venteApplyCheminRow(cfg, row) {
        if (!cfg || !row) return;
        var set = function (id, val) {
            var el = document.getElementById(id);
            if (el) el.value = val == null ? '' : String(val);
        };
        set(cfg.prix, row.prix != null ? row.prix : '');
        set(cfg.cate, row.categori != null ? row.categori : '');
        set(cfg.gid, row.gareidentif != null ? row.gareidentif : '');
        set(cfg.nom, row.nom_ligne != null ? row.nom_ligne : '');
        set(cfg.lign, row.ident_ligne != null ? row.ident_ligne : (row.ligne_id != null ? row.ligne_id : ''));
        var heur = document.getElementById(cfg.heur);
        if (heur && heur.selectedIndex >= 0) {
            heur.options[heur.selectedIndex].value = __venteCheminRowValue(row);
            heur.options[heur.selectedIndex].setAttribute('data-code-progr', String(row.code_progr));
        }
        if (cfg.depGare) {
            __venteFillTransitDepart('#' + cfg.depGare, row.gareidentif);
        }
    }

    function __venteLoadSiegesChemin(cfg, row) {
        var ps = document.getElementById(cfg.sieges);
        if (ps) ps.options.length = 1;
        if (!row || !row.code_progr) return;
        // enrichir meta (catégorie / gare) puis sièges
        var httpMeta = new XMLHttpRequest();
        httpMeta.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${encodeURIComponent(row.code_progr)}`, true);
        httpMeta.onload = function () {
            try {
                var meta = JSON.parse(httpMeta.responseText);
                if (Object.entries(meta).length >= 1) {
                    for (var key in Object.entries(meta)) {
                        if (cfg.cate) {
                            var c = document.getElementById(cfg.cate);
                            if (c) c.value = `${meta[key].categori}`;
                        }
                        if (cfg.gid) {
                            var g = document.getElementById(cfg.gid);
                            if (g) g.value = `${meta[key].gareidentif}`;
                        }
                        if (cfg.nom) {
                            var n = document.getElementById(cfg.nom);
                            if (n) n.value = `${meta[key].nom_ligne}`;
                        }
                        if (cfg.lign) {
                            var l = document.getElementById(cfg.lign);
                            if (l) l.value = `${meta[key].ident_ligne}`;
                        }
                        if (cfg.depGare) {
                            __venteFillTransitDepart('#' + cfg.depGare, meta[key].gareidentif);
                        }
                    }
                }
            } catch (e) {}
            var httpS = new XMLHttpRequest();
            httpS.open(
                'GET',
                window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${encodeURIComponent(row.code_progr)}/${row.intervalle1}/${row.intervalle2}`,
                true
            );
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
                } catch (e2) {
                    if (ps) ps.options.length = 1;
                }
            };
            httpS.setRequestHeader('Content-Type', 'application/json');
            httpS.send();
        };
        httpMeta.setRequestHeader('Content-Type', 'application/json');
        httpMeta.send();
        if (cfg.prix && row.prix != null) {
            var px = document.getElementById(cfg.prix);
            if (px) px.value = String(row.prix);
        }
    }

    function __venteOnCheminHeurChange(legKey) {
        var cfg = __venteCheminLegCfg[legKey];
        if (!cfg) return;
        var heur = document.getElementById(cfg.heur);
        if (!heur) return;
        __venteHideProgSelectAny(cfg.progBox, cfg.progSel);
        var ps = document.getElementById(cfg.sieges);
        if (ps) ps.options.length = 1;
        var idLh = heur.value;
        if (!idLh) return;
        // si déjà format historique code/i1/i2/lh/prix (après choix)
        if (String(idLh).indexOf('/') !== -1) {
            var parts = String(idLh).split('/');
            __venteLoadSiegesChemin(cfg, {
                code_progr: parts[0],
                intervalle1: parts[1],
                intervalle2: parts[2],
                id_ligneheure: parts[3],
                prix: parts[4]
            });
            return;
        }
        var groups = (window.__venteCheminGroups && window.__venteCheminGroups[cfg.heur]) || {};
        var g = groups[idLh];
        var list = (g && g.rows) ? g.rows : [];
        if (list.length === 0) return;
        if (list.length === 1) {
            __venteApplyCheminRow(cfg, list[0]);
            __venteLoadSiegesChemin(cfg, list[0]);
            return;
        }
        var box = document.getElementById(cfg.progBox);
        var sel = document.getElementById(cfg.progSel);
        if (!sel) {
            __venteApplyCheminRow(cfg, list[0]);
            __venteLoadSiegesChemin(cfg, list[0]);
            return;
        }
        if (box) {
            box.style.display = 'block';
        }
        if (sel) sel.style.display = 'block';
        // afficher le box même si les champs transit sont en display none sur le label heure
        sel.options.length = 1;
        for (var i = 0; i < list.length; i++) {
            var opt = document.createElement('option');
            opt.value = String(i);
            opt.innerHTML = __venteLabelProg(list[i]);
            sel.add(opt);
        }
        sel.onchange = function () {
            if (ps) ps.options.length = 1;
            var idx = parseInt(sel.value, 10);
            if (isNaN(idx) || !list[idx]) return;
            __venteApplyCheminRow(cfg, list[idx]);
            __venteLoadSiegesChemin(cfg, list[idx]);
        };
    }

    function __venteWireCheminHeur(heurId, legKey) {
        var heur = document.getElementById(heurId);
        if (!heur) return;
        heur.onchange = function () {
            __venteOnCheminHeurChange(legKey);
        };
    }

    function __venteApplyTransit1Fields(p) {
        if (!p) return;
        var set = function (id, val) {
            var el = document.querySelector(id);
            if (el) el.value = val == null ? '' : String(val);
        };
        set('#programtrans', p.code_progr);
        // Défaut tarif 1 si absent — sinon verifpriprg / prixtrans ne partent jamais.
        var tf = (p.typetarif != null && String(p.typetarif).trim() !== '') ? p.typetarif : '1';
        set('#tarifattrib', tf);
        set('#dateprtrans', p.date_progr);
        set('#deplignetrans', p.gareidentif);
        set('#intertrans1', p.intervalle1);
        set('#intertrans2', p.intervalle2);
        set('#ligntrans', p.ident_ligne);
        set('#nomitintrans', p.nom_ligne);
        set('#hertrans', p.heure);
        set('#catetrans', p.categori);
        if (p.prix != null && String(p.prix).trim() !== '') {
            set('#prix_axetrans', p.prix);
        }
        // Nouvelle ancre jambe 1 → invalider heures/sièges des correspondances suivantes.
        __venteClearDownstreamCheminHeures();
    }

    function __venteLoadSiegesTransit1(idLigneheure, dptDate) {
        var ps = document.querySelector('#psiegesitines');
        if (ps) ps.options.length = 1;
        var tfEl = document.querySelector('#tarifattrib');
        var tfbs = tfEl && String(tfEl.value || '').trim() !== '' ? String(tfEl.value).trim() : '1';
        if (tfEl && String(tfEl.value || '').trim() === '') {
            tfEl.value = tfbs;
        }
        if (idLigneheure) {
            var httpPrixit = new XMLHttpRequest();
            httpPrixit.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifpriprg/${idLigneheure}/${tfbs}`, true);
            httpPrixit.onload = function () {
                try {
                    var donprixit = JSON.parse(httpPrixit.responseText);
                    if (Object.entries(donprixit).length >= 1) {
                        for (var key in Object.entries(donprixit)) {
                            var px = document.querySelector('#prix_axetrans');
                            if (px) px.value = `${donprixit[key].prix}`;
                        }
                    }
                } catch (e) {}
            };
            httpPrixit.setRequestHeader('Content-Type', 'application/json');
            httpPrixit.send();
        }
        var cdprogit = document.querySelector('#programtrans') ? document.querySelector('#programtrans').value : '';
        var dbit = document.querySelector('#intertrans1') ? document.querySelector('#intertrans1').value : '';
        var fnit = document.querySelector('#intertrans2') ? document.querySelector('#intertrans2').value : '';
        var lgit = document.querySelector('#nomitintrans') ? document.querySelector('#nomitintrans').value : '';
        var timit = document.querySelector('#hertrans') ? document.querySelector('#hertrans').value : '';
        if (!cdprogit) return;
        var httpRequetteit = new XMLHttpRequest();
        httpRequetteit.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${cdprogit}/${dptDate}/${lgit}/${timit}/${dbit}/${fnit}`, true);
        httpRequetteit.onload = function () {
            try {
                var dattait = JSON.parse(httpRequetteit.responseText);
                if (ps) ps.options.length = 1;
                if (Object.entries(dattait).length >= 1) {
                    for (var key2 in Object.entries(dattait)) {
                        var opt = document.createElement('option');
                        opt.value = `${dattait[key2].siege_num}`;
                        opt.innerHTML = `${dattait[key2].siege_num}`;
                        if (ps) ps.add(opt);
                    }
                }
            } catch (e2) {
                if (ps) ps.options.length = 1;
            }
        };
        httpRequetteit.setRequestHeader('Content-Type', 'application/json');
        httpRequetteit.send();
    }

    /** Corr. 1 : multi-départs même heure. */
    function __venteHandleTransit1ProgList(don, idLigneheure, dptDate) {
        var list = __venteProgListFromResponse(don);
        __venteHideProgSelectAny('selprog_box_tr1', 'selprog_tr1');
        var ps = document.querySelector('#psiegesitines');
        if (ps) ps.options.length = 1;
        if (list.length === 0) return false;
        if (list.length === 1) {
            __venteApplyTransit1Fields(list[0]);
            __venteLoadSiegesTransit1(idLigneheure, dptDate);
            return true;
        }
        var box = document.getElementById('selprog_box_tr1');
        var sel = document.getElementById('selprog_tr1');
        if (!sel) {
            __venteApplyTransit1Fields(list[0]);
            __venteLoadSiegesTransit1(idLigneheure, dptDate);
            return true;
        }
        if (box) box.style.display = 'block';
        if (sel) sel.style.display = 'block';
        sel.options.length = 1;
        for (var i = 0; i < list.length; i++) {
            var opt = document.createElement('option');
            opt.value = String(i);
            opt.innerHTML = __venteLabelProg(list[i]);
            sel.add(opt);
        }
        sel.onchange = function () {
            if (ps) ps.options.length = 1;
            var idx = parseInt(sel.value, 10);
            if (isNaN(idx) || !list[idx]) return;
            __venteApplyTransit1Fields(list[idx]);
            __venteLoadSiegesTransit1(idLigneheure, dptDate);
        };
        return true;
    }

    function __venteFillHeuresVente(heures, hasTransit) {
        var hSel = document.querySelector('#hdepart');
        if (!hSel) return;
        hSel.options.length = 1;
        var list = Array.isArray(heures) ? heures : [];
        for (var i = 0; i < list.length; i++) {
            var hr = list[i];
            if (!hr || hr.id_ligneheure == null || hr.id_ligneheure === '') continue;
            var opt = document.createElement('option');
            opt.value = hr.id_ligneheure + '/' + hr.heure;
            var hasProg = !!(hr.has_programme === true || hr.has_programme === 1 || hr.has_programme === '1');
            opt.setAttribute('data-has-programme', hasProg ? '1' : '0');
            opt.innerHTML = hr.heure;
            hSel.add(opt);
        }
    }

    /** Remplit un select « départ correspondance » dès que gareIdentif est connu (reset inclus). */
    function __venteFillTransitDepart(selectSel, gareIdentif) {
        var sel = document.querySelector(selectSel);
        if (!sel) return;
        // length=1 sur un select vide crée une option blanche qui reste sélectionnée
        // et fait échouer la vente (transitedepargare* posté vide → redirect silencieux).
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
            // Une seule sous-gare (cas fréquent) : la sélectionner pour que le POST soit valide.
            if (sel.options.length === 1) {
                sel.selectedIndex = 0;
            } else if (sel.options.length > 1) {
                sel.selectedIndex = 0;
            }
        };
        http.setRequestHeader('Content-Type', 'application/json');
        http.send();
    }

    function __venteEnsureCheminSelector() {
        var existing = document.getElementById('selchemin_box');
        if (existing) return existing;
        var box = document.createElement('div');
        box.className = 'form-group col-sm-12';
        box.id = 'selchemin_box';
        box.style.display = 'none';
        box.innerHTML = ''
            + '<label id="selchemin_label">Itinéraire de correspondance</label>'
            + '<select class="form-control form-control-sm" id="selchemin_transit" name="selchemin_transit">'
            + '<option value="">Choisissez l\'itinéraire</option>'
            + '</select>'
            + '<small class="form-text text-muted" id="selchemin_hint"></small>';
        var anchor = document.getElementById('hdepartitine')
            || document.getElementById('idchemins')
            || document.getElementById('nbrtrans');
        if (anchor && anchor.parentNode && anchor.parentNode.parentNode) {
            anchor.parentNode.parentNode.insertBefore(box, anchor.parentNode);
        } else if (anchor && anchor.parentNode) {
            anchor.parentNode.insertBefore(box, anchor);
        } else {
            document.body.appendChild(box);
        }
        return box;
    }

    function __venteHideCheminSelector() {
        var box = document.getElementById('selchemin_box');
        var sel = document.getElementById('selchemin_transit');
        var hint = document.getElementById('selchemin_hint');
        if (box) box.style.display = 'none';
        if (sel) {
            sel.options.length = 1;
            sel.value = '';
            sel.onchange = null;
        }
        if (hint) hint.textContent = '';
        window.__venteCheminsCache = null;
    }

    function __venteFormatAttenteLabel(chemin) {
        if (!chemin) return '';
        if (chemin.attente_totale_label) return 'Attente totale : ' + chemin.attente_totale_label;
        if (chemin.attente_totale_min != null) {
            var m = parseInt(chemin.attente_totale_min, 10) || 0;
            var h = Math.floor(m / 60);
            var mm = m % 60;
            var s = h > 0 ? (h + ' h' + (mm ? (' ' + (mm < 10 ? '0' : '') + mm) : '')) : (mm + ' min');
            return 'Attente totale : ' + s;
        }
        return chemin.source === 'declaratif' ? 'Composition déclarée' : '';
    }

    /** Normalise etapes JSON (array ou objet indexé) en tableau. */
    function __venteNormalizeEtapes(etapes) {
        if (!etapes) return [];
        if (Array.isArray(etapes)) return etapes;
        if (typeof etapes === 'object') {
            return Object.keys(etapes).map(function (k) { return etapes[k]; }).filter(Boolean);
        }
        return [];
    }

    /**
     * Correspondance 2/3/4 — ligne : propose la ligne du chemin, sans la sélectionner.
     * L'opérateur choisit ; le chargement des heures part sur son onchange.
     */
    function __venteSetCheminLigneOption(selectSel, code, nom) {
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
        sel.selectedIndex = 0;
    }

    /** Assure que Correspondance 1 — ligne est un input texte (pas un select). */
    function __venteEnsureLigne1LockedInput() {
        var el = document.getElementById('lignesitineraire');
        if (!el) return null;
        if (el.tagName === 'INPUT') {
            el.disabled = true;
            el.setAttribute('disabled', 'disabled');
            el.readOnly = true;
            return el;
        }
        // Si un select avait remplacé l'input : revenir à un input figé.
        var inp = document.createElement('input');
        inp.type = 'text';
        inp.id = 'lignesitineraire';
        inp.name = el.getAttribute('name') || 'lignesitineraires';
        inp.className = el.className || 'form-control form-control-sm';
        inp.disabled = true;
        inp.setAttribute('disabled', 'disabled');
        inp.readOnly = true;
        if (el.parentNode) el.parentNode.replaceChild(inp, el);
        return inp;
    }

    /**
     * Correspondance 1 — ligne : prédéfinie et non modifiable.
     * Renseigne aussi #itinecode / #lignetineraire, puis appelle onPick(code, nom).
     */
    function __venteFillLigne1Locked(etape0, onPick) {
        if (!etape0) return;
        var code = etape0.code_itineraires || '';
        var nom = etape0.nom_itineraires || code;
        var el = __venteEnsureLigne1LockedInput();
        if (el) el.value = nom;
        var itc = document.querySelector('#itinecode');
        var ltn = document.querySelector('#lignetineraire');
        if (itc) itc.value = code;
        if (ltn) ltn.value = nom;
        if (typeof onPick === 'function') onPick(code, nom);
    }

    /**
     * Remise à zéro UI transit avant d'appliquer un autre chemin multi.
     * Sans ça, .add(opt) cumule les lignes et les champs restent sur l'ancien itinéraire.
     */
    function __venteResetTransitFieldsBeforeApply() {
        [
            'arritin1', 'idchemins', 'heureitin1', 'idcheminsheur', 'siegitine1', 'psiegesitines1',
            'arritin2', 'idchemins1', 'heureitin2', 'idcheminsheur1', 'siegitine2', 'psiegesitines2',
            'arritin3', 'idchemins2', 'heureitin3', 'idcheminsheur2', 'siegitine3', 'psiegesitines3',
            'quartier1', 'quartier2', 'quartier3', 'idquart1', 'idquart2', 'idquart3',
            'iddeptrans1', 'transitedepargare1', 'iddeptrans2', 'transitedepargare2',
            'iddeptrans3', 'transitedepargare3', 'iddeptrans4', 'transitedepargare4',
            'tran', 'heureitin', 'hdepartitine', 'lignesitineraire', 'ligne1', 'siegitine', 'psiegesitines'
        ].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });
        __venteSetDisplay('depitin1', 'none');
        __venteSetDisplay('depargareitine1', 'none');
        __venteSetDisplay('depitin2', 'none');
        __venteSetDisplay('depargareitine2', 'none');
        __venteSetDisplay('depitin3', 'none');
        __venteSetDisplay('depargareitine3', 'none');
        __venteSetDisplay('arrsgareitine1', 'none');
        __venteSetDisplay('arrsgareitine2', 'none');
        __venteSetDisplay('arrsgareitine3', 'none');
        __venteSetDisplay('hdepartitine1', 'none');
        __venteSetDisplay('hdepartitine2', 'none');
        __venteSetDisplay('hdepartitine3', 'none');

        [
            '#idchemins', '#idchemins1', '#idchemins2',
            '#idcheminsheur', '#idcheminsheur1', '#idcheminsheur2',
            '#hdepartitine', '#psiegesitines', '#psiegesitines1', '#psiegesitines2', '#psiegesitines3',
            '#quartier1', '#quartier2', '#quartier3'
        ].forEach(function (s) {
            var el = document.querySelector(s);
            if (el && el.options) {
                el.options.length = 1;
                el.value = '';
                el.onchange = null;
            }
        });
        ['#transitedepargare1', '#transitedepargare2', '#transitedepargare3', '#transitedepargare4'].forEach(function (s) {
            var el = document.querySelector(s);
            if (el && el.options) el.options.length = 0;
        });
        [
            '#itinecode', '#itinecodes', '#lignetineraire', '#lignesitineraire', '#nbrtrans',
            '#idcompg', '#idcompg1', '#idcompg2', '#idcompg3',
            '#prix_axetransit', '#prix_axetransit1', '#prix_axetransit2',
            '#hertrans', '#dateprtrans', '#program', '#cate', '#catetransit', '#catetransit1', '#catetransit2'
        ].forEach(function (s) {
            var el = document.querySelector(s);
            if (el) el.value = '';
        });
        if (typeof __venteHideAllTransitProgSelects === 'function') __venteHideAllTransitProgSelects();
        if (typeof __venteClearDownstreamCheminHeures === 'function') __venteClearDownstreamCheminHeures();
    }

    function __venteShowCheminSelector(chemins, onPick) {
        __venteEnsureCheminSelector();
        var box = document.getElementById('selchemin_box');
        var sel = document.getElementById('selchemin_transit');
        var hint = document.getElementById('selchemin_hint');
        if (!box || !sel) {
            var et0 = chemins && chemins[0] ? __venteNormalizeEtapes(chemins[0].etapes) : [];
            if (typeof window.__venteApplyTransitLegs === 'function') window.__venteApplyTransitLegs(et0);
            else if (typeof onPick === 'function') onPick(et0);
            return;
        }
        window.__venteCheminsCache = chemins;
        sel.options.length = 1;
        for (var i = 0; i < chemins.length; i++) {
            var c = chemins[i];
            var opt = document.createElement('option');
            opt.value = String(i);
            opt.textContent = c.label || (('Chemin ' + (i + 1)) + ' · ' + (c.nb_jambes || '') + ' jambes');
            sel.add(opt);
        }
        box.style.display = 'block';
        var applyIdx = function (idx) {
            var ch = chemins[idx];
            if (hint) hint.textContent = __venteFormatAttenteLabel(ch);
            var etapes = __venteNormalizeEtapes(ch && ch.etapes);
            // Toujours passer par ApplyTransitLegs (recharge champs prédéfinis).
            if (typeof window.__venteApplyTransitLegs === 'function') {
                window.__venteApplyTransitLegs(etapes);
            } else if (typeof onPick === 'function') {
                onPick(etapes);
            }
        };
        sel.onchange = function () {
            var idx = parseInt(sel.value, 10);
            if (isNaN(idx) || !chemins[idx]) {
                if (hint) hint.textContent = '';
                if (typeof window.__venteApplyTransitLegs === 'function') window.__venteApplyTransitLegs([]);
                else if (typeof onPick === 'function') onPick([]);
                return;
            }
            applyIdx(idx);
        };
        // Appliquer le 1er (composition déclarée en tête) ; l'agent peut changer.
        sel.selectedIndex = 1;
        applyIdx(0);
    }

    function __venteRequestTransitLegs(seltdep, arr, datedepart, sougid, force, onDone) {
        var sg = (sougid != null && sougid !== '') ? sougid : '0';
        var forceFlag = force ? '1' : '0';
        var done = function (etapes) {
            etapes = __venteNormalizeEtapes(etapes);
            if (typeof onDone === 'function') {
                onDone(etapes);
            } else if (typeof window.__venteApplyTransitLegs === 'function') {
                window.__venteApplyTransitLegs(etapes);
            }
        };
        var httpRequestitine = new XMLHttpRequest();
        httpRequestitine.open(
            'GET',
            window.location.origin + `${APP_ROOT}/programmes/verifchemins/`
                + encodeURIComponent(seltdep + '-' + arr) + '/'
                + encodeURIComponent(datedepart) + '/'
                + encodeURIComponent(sg) + '/'
                + forceFlag,
            true
        );
        httpRequestitine.onload = function () {
            var payload = null;
            try { payload = JSON.parse(httpRequestitine.responseText); } catch (e) { payload = null; }
            // Compat : ancien verifitine = tableau d'étapes
            if (Array.isArray(payload)) {
                __venteHideCheminSelector();
                done(payload);
                return;
            }
            if (!payload || typeof payload !== 'object') {
                __venteHideCheminSelector();
                done([]);
                return;
            }
            if (payload.mode === 'direct' || payload.mode === 'none') {
                __venteHideCheminSelector();
                done([]);
                return;
            }
            var chemins = Array.isArray(payload.chemins) ? payload.chemins : [];
            if (chemins.length > 1) {
                __venteShowCheminSelector(chemins, done);
                return;
            }
            __venteHideCheminSelector();
            if (chemins.length === 1 && chemins[0].etapes) {
                done(chemins[0].etapes);
                return;
            }
            if (payload.etapes && (Array.isArray(payload.etapes) ? payload.etapes.length : Object.keys(payload.etapes).length)) {
                done(payload.etapes);
                return;
            }
            done([]);
        };
        httpRequestitine.setRequestHeader('Content-Type', 'application/json');
        httpRequestitine.send();
    }

    function __venteFillQuartierSelect(rows) {
        var q = document.querySelector('#quartier');
        if (!q) return;
        var keep = window.__venteSavedQuartierValue || q.value || '';
        q.options.length = 1;
        var list = Array.isArray(rows) ? rows
            : (rows && typeof rows === 'object' ? Object.keys(rows).map(function (k) { return rows[k]; }) : []);
        for (var i = 0; i < list.length; i++) {
            var row = list[i];
            if (!row) continue;
            var nom = row.nom_quartier || row.nom || '';
            if (!nom) continue;
            var opt = document.createElement('option');
            opt.value = nom;
            opt.textContent = nom;
            q.add(opt);
        }
        if (keep) {
            q.value = keep;
            if (q.value === keep) {
                window.__venteSavedQuartierValue = keep;
            }
        }
    }
    function __venteLoadQuartiersArrivee() {
        try {
            ['#prix_axe','#tarifattrib','#date_depheure','#program','#idcompg','#idcompg1','#idcompg2','#idcompg3'].forEach(function (s) {
                __venteSafeReset(s, null);
            });
            ['#hdepart','#quartier','#psieges','#selprog','#hdepartitine','#psiegesitines','#idcheminsheur',
             '#idchemins','#idchemins1','#idchemins2','#psiegesitines1','#idcheminsheur1',
             '#psiegesitines2','#idcheminsheur2','#psiegesitines3','#quartier1','#quartier2','#quartier3',
             '#transitedepargare1','#transitedepargare2','#transitedepargare3','#transitedepargare4'
            ].forEach(function (s) { __venteSafeReset(s, 1); });
            __venteHideProgSelect();
            __venteShowMainQuartier();
            var arEl = document.querySelector('#arrsgare');
            if (!arEl || !arEl.value) return;
            var typgare = String(arEl.value).split('/')[0].trim();
            if (!typgare) return;
            var url = window.location.origin + (typeof APP_ROOT !== 'undefined' ? APP_ROOT : '')
                + '/programmes/verifquart/' + encodeURIComponent(typgare);
            var onOk = function (xhr) {
                try {
                    __venteFillQuartierSelect(JSON.parse(xhr.responseText || '[]'));
                    __venteShowMainQuartier();
                } catch (err) {
                    console.error('verifquart', err);
                    __venteSafeReset('#quartier', 1);
                }
            };
            if (window.AppRequestGuard && AppRequestGuard.getJson) {
                AppRequestGuard.getJson(url, 'verifquart-' + typgare, onOk, function () { __venteSafeReset('#quartier', 1); });
            } else {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', url, true);
                xhr.onload = function () { if (xhr.status >= 200 && xhr.status < 300) onOk(xhr); };
                xhr.send();
            }
        } catch (e) { console.error('quartier arrivee', e); }
    }

    var arBoot = document.querySelector('#arrsgare');
    if (arBoot && !arBoot._venteQuartierBound) {
        arBoot.addEventListener('change', __venteLoadQuartiersArrivee);
        arBoot._venteQuartierBound = true;
    }
    
    document.querySelectorAll('.addventeticket').forEach(function (e) 
    {
        __venteSetTaTitle('VENTE DE TICKET');
            
            let da = document.querySelector('#date_depheure');
            if (da !== null){
                da.onchange = () => 
                {
                    
                    document.querySelector('#hdepart').options.length = 1;
                    document.querySelector('#psieges').options.length = 1;
                    document.querySelector('#hdepartitine').options.length = 1;
                    document.querySelector('#psiegesitines').options.length = 1;
                    document.querySelector('#idcheminsheur').options.length = 1;
                    //document.querySelector('#lignesitineraire').value = '';
                    // selects départ transit : pas de placeholder — length=1 créerait une option vide sélectionnée
                    document.querySelector('#transitedepargare1').options.length = 0;
                    document.querySelector('#transitedepargare2').options.length = 0;
                    document.querySelector('#transitedepargare3').options.length = 0;
                    document.querySelector('#transitedepargare4').options.length = 0;
                    document.querySelector('#idchemins').options.length = 1;
                    document.querySelector('#idchemins1').options.length = 1;
                    document.querySelector('#idchemins2').options.length = 1;
                    document.querySelector('#idcompg').value = '';
                    document.querySelector('#idcompg1').value = '';
                    document.querySelector('#idcompg2').value = '';
                    document.querySelector('#idcompg3').value = '';
                        
                    document.querySelector('#psiegesitines1').options.length = 1;
                    document.querySelector('#idcheminsheur1').options.length = 1;
                    document.querySelector('#psiegesitines2').options.length = 1;
                    document.querySelector('#idcheminsheur2').options.length = 1;
                    document.querySelector('#psiegesitines3').options.length = 1;
                    document.querySelector('#quartier1').options.length = 1;
                    document.querySelector('#quartier2').options.length = 1;
                    document.querySelector('#quartier3').options.length = 1;
                    

                    let httpRequetes;
                    
                    if (window.XMLHttpRequest) {
                        httpRequetes = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpRequetes = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                        var depa = document.querySelector('#depargare').value;
                        var arrpa = document.querySelector('#arrsgare').value;
                        var arr1 = arrpa.split('/');
                        var arr = arr1[0];
                        var arr2 = arr1[1];
                        
                        var datedepart = document.querySelector('#date_depheure').value;
                        var dateactu = document.querySelector('#actu').value;
                                         
                        var post_lhdep = depa.split('/');
                        var seltdep = post_lhdep[0];
                        var sougid = post_lhdep[1];
                        if(datedepart >= dateactu)
                        {
                            
                            httpRequetes.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheuresvente/${seltdep}-${arr}/${datedepart}/${sougid || '0'}`, true);
                            httpRequetes.onload = () => {
                                var payloadHv = {};
                                try { payloadHv = JSON.parse(httpRequetes.responseText) || {}; } catch (eHv) { payloadHv = {}; }
                                var heuresList = Array.isArray(payloadHv.heures) ? payloadHv.heures : [];
                                window.__venteHasTransit = !!payloadHv.has_transit;
                                window.__venteLastHeuresVente = heuresList;

                                document.querySelector('#smsdt').style.display = 'none';
                                document.querySelector('#date_depheure').style.color = "black";
                                document.querySelector('#date_depheure').style.border = "1px solid";

                                // Phase 1 : toujours lister le catalogue d'heures (avec ou sans départ).
                                __venteShowDirectHourUi();
                                __venteFillHeuresVente(heuresList, window.__venteHasTransit);

                                // Applique les jambes transit (réutilisé au choix d'une heure sans départ).
                                window.__venteApplyTransitLegs = function (donitines) {
                                                    donitines = (typeof __venteNormalizeEtapes === 'function')
                                                        ? __venteNormalizeEtapes(donitines) : donitines;
                                                    if(donitines === null || donitines === '' || (typeof donitines === 'object' && !Object.keys(donitines).length))
                                                    {
                                                        if (typeof __venteHideCheminSelector === 'function') __venteHideCheminSelector();
                                                        if (typeof __venteResetTransitFieldsBeforeApply === 'function') __venteResetTransitFieldsBeforeApply();
                                                        __venteSetDisplay('depitin1', 'none');
                                                        __venteSetDisplay('depargareitine1', 'none');
                                                        document.querySelector('#iddeptrans1').style.display = 'none';
                                                        document.querySelector('#transitedepargare1').style.display = 'none';
                                                        document.querySelector('#iddeptrans2').style.display = 'none';
                                                        document.querySelector('#transitedepargare2').style.display = 'none';
                                                        document.querySelector('#iddeptrans3').style.display = 'none';
                                                        document.querySelector('#transitedepargare3').style.display = 'none';
                                                        document.querySelector('#iddeptrans4').style.display = 'none';
                                                        document.querySelector('#transitedepargare4').style.display = 'none';
                                                        document.querySelector('#arritin1').style.display = 'none';
                                                        __venteSetDisplay('arrsgareitine1', 'none');
                                                        document.querySelector('#arritin1').style.display = 'none';
                                                        __venteSetDisplay('arrsgareitine1', 'none');
                                                        document.querySelector('#heureitin1').style.display = 'none';
                                                        __venteSetDisplay('hdepartitine1', 'none');
                                                        document.querySelector('#lignesitineraire').style.display = 'none';
                                                        document.querySelector('#ligne1').style.display = 'none';
                                                        document.querySelector('#siegitine1').style.display = 'none';
                                                        document.querySelector('#psiegesitines1').style.display = 'none';
                                                        __venteSetDisplay('depitin2', 'none');
                                                        __venteSetDisplay('depargareitine2', 'none');
                                                        document.querySelector('#arritin2').style.display = 'none';
                                                        __venteSetDisplay('arrsgareitine2', 'none');
                                                        document.querySelector('#heureitin2').style.display = 'none';
                                                        __venteSetDisplay('hdepartitine2', 'none');
                                                        document.querySelector('#siegitine2').style.display = 'none';
                                                        document.querySelector('#psiegesitines2').style.display = 'none';
                                                        __venteSetDisplay('depitin3', 'none');
                                                        __venteSetDisplay('depargareitine3', 'none');
                                                        document.querySelector('#arritin3').style.display = 'none';
                                                        __venteSetDisplay('arrsgareitine3', 'none');
                                                        document.querySelector('#heureitin3').style.display = 'none';
                                                        __venteSetDisplay('hdepartitine3', 'none');
                                                        document.querySelector('#siegitine3').style.display = 'none';
                                                        document.querySelector('#psiegesitines3').style.display = 'none';
                                                        document.querySelector('#quartier1').style.display = 'none';
                                                        document.querySelector('#quartier2').style.display = 'none';
                                                        document.querySelector('#idquart1').style.display = 'none';
                                                        document.querySelector('#idquart2').style.display = 'none';
                                                        document.querySelector('#idquart3').style.display = 'none';

                                                        document.querySelector('#tran').style.display = 'none';
                                                        document.querySelector('#heureitin').style.display = 'none';
                                                        document.querySelector('#hdepartitine').style.display = 'none';
                                                        document.querySelector('#siegitine').style.display = 'none';
                                                        document.querySelector('#psiegesitines').style.display = 'none';
                                                        document.querySelector('#hrid').style.display = 'block';
                                                        document.querySelector('#hdepart').style.display = 'block';
                                                        document.querySelector('#sigid').style.display = 'block';
                                                        document.querySelector('#psieges').style.display = 'block';
                                                        document.querySelector('#iddep').style.display = 'block';
                                                        document.querySelector('#depargare').style.display = 'block';
                                                        document.querySelector('#arrid').style.display = 'block';
                                                        document.querySelector('#arrsgare').style.display = 'block';
                                                        __venteSetMainEscaleVisible(true);

                                                    }
                                                    else
                                                    {
                                                        if (typeof __venteResetTransitFieldsBeforeApply === 'function') {
                                                            __venteResetTransitFieldsBeforeApply();
                                                        }
                                                        if (Object.entries(donitines).length >= 1) 
                                                        {
                                                            var i = Object.entries(donitines).length;
                                                            document.querySelector('#nbrtrans').value = i;
                                                            {
                                                                if(i === 2){
                                                                    document.querySelector('#arritin1').style.display = 'block';
                                                                    document.querySelector('#idchemins').style.display = 'block';
                                                                    document.querySelector('#heureitin1').style.display = 'block';
                                                                    document.querySelector('#idcheminsheur').style.display = 'block';
                                                                    document.querySelector('#siegitine1').style.display = 'block';
                                                                    document.querySelector('#psiegesitines1').style.display = 'block';
                                                                    document.querySelector('#quartier1').style.display = 'block';
                                                                    document.querySelector('#idquart1').style.display = 'block';
                                                                    document.querySelector('#iddeptrans1').style.display = 'block';
                                                                    document.querySelector('#transitedepargare1').style.display = 'block';
                                                                    document.querySelector('#iddeptrans2').style.display = 'block';
                                                                    document.querySelector('#transitedepargare2').style.display = 'block';
                                                                    
                                                                }
                                                                
                                                                if(i === 3){
                                                                    document.querySelector('#iddeptrans1').style.display = 'block';
                                                                    document.querySelector('#transitedepargare1').style.display = 'block';
                                                                    document.querySelector('#iddeptrans2').style.display = 'block';
                                                                    document.querySelector('#transitedepargare2').style.display = 'block';
                                                                    document.querySelector('#iddeptrans3').style.display = 'block';
                                                                    document.querySelector('#transitedepargare3').style.display = 'block';
                                                                    document.querySelector('#arritin1').style.display = 'block';
                                                                    document.querySelector('#idchemins').style.display = 'block';
                                                                    document.querySelector('#heureitin1').style.display = 'block';
                                                                    document.querySelector('#idcheminsheur').style.display = 'block';
                                                                    document.querySelector('#siegitine1').style.display = 'block';
                                                                    document.querySelector('#psiegesitines1').style.display = 'block';
                                                                    document.querySelector('#idquart1').style.display = 'block';
                                                                    document.querySelector('#idquart2').style.display = 'block';

                                                                    document.querySelector('#arritin2').style.display = 'block';
                                                                    document.querySelector('#idchemins1').style.display = 'block';
                                                                    document.querySelector('#heureitin2').style.display = 'block';
                                                                    document.querySelector('#idcheminsheur1').style.display = 'block';
                                                                    document.querySelector('#siegitine2').style.display = 'block';
                                                                    document.querySelector('#psiegesitines2').style.display = 'block';
                                                                    document.querySelector('#quartier1').style.display = 'block';
                                                                    document.querySelector('#quartier2').style.display = 'block';
                                                                }if(i === 4){
                                                                    
                                                                    document.querySelector('#iddeptrans1').style.display = 'block';
                                                                    document.querySelector('#transitedepargare1').style.display = 'block';
                                                                    document.querySelector('#iddeptrans2').style.display = 'block';
                                                                    document.querySelector('#transitedepargare2').style.display = 'block';
                                                                    document.querySelector('#iddeptrans3').style.display = 'block';
                                                                    document.querySelector('#transitedepargare3').style.display = 'block';
                                                                    document.querySelector('#iddeptrans4').style.display = 'block';
                                                                    document.querySelector('#transitedepargare4').style.display = 'block';
                                                                    document.querySelector('#arritin1').style.display = 'block';
                                                                    document.querySelector('#idchemins').style.display = 'block';
                                                                    document.querySelector('#heureitin1').style.display = 'block';
                                                                    document.querySelector('#idcheminsheur').style.display = 'block';
                                                                    document.querySelector('#siegitine1').style.display = 'block';
                                                                    document.querySelector('#psiegesitines1').style.display = 'block';
                                                                    document.querySelector('#arritin2').style.display = 'block';
                                                                    document.querySelector('#idchemins1').style.display = 'block';
                                                                    document.querySelector('#heureitin2').style.display = 'block';
                                                                    document.querySelector('#idcheminsheur1').style.display = 'block';
                                                                    document.querySelector('#siegitine2').style.display = 'block';
                                                                    document.querySelector('#psiegesitines2').style.display = 'block';
                                                                    document.querySelector('#arritin3').style.display = 'block';
                                                                    document.querySelector('#idchemins2').style.display = 'block';
                                                                    document.querySelector('#heureitin3').style.display = 'block';
                                                                    document.querySelector('#idcheminsheur2').style.display = 'block';
                                                                    document.querySelector('#siegitine3').style.display = 'block';
                                                                    document.querySelector('#psiegesitines3').style.display = 'block';
                                                                    document.querySelector('#quartier1').style.display = 'block';
                                                                    document.querySelector('#quartier2').style.display = 'block';
                                                                    document.querySelector('#quartier3').style.display = 'block';
                                                                    document.querySelector('#idquart1').style.display = 'block';
                                                                    document.querySelector('#idquart2').style.display = 'block';
                                                                    document.querySelector('#idquart3').style.display = 'block';
                                                                    // Jambe 4 : quartier final = #quartier (haut de formulaire)
                                                                    __venteShowMainQuartier();

                                                                }
                                                                document.querySelector('#tran').style.display = 'block';
                                                                document.querySelector('#heureitin').style.display = 'block';
                                                                document.querySelector('#hdepartitine').style.display = 'block';
                                                                document.querySelector('#lignesitineraire').style.display = 'block';
                                                                document.querySelector('#ligne1').style.display = 'block';
                                                                document.querySelector('#siegitine').style.display = 'block';
                                                                document.querySelector('#psiegesitines').style.display = 'block';
                                                                document.querySelector('#hrid').style.display = 'none';
                                                                document.querySelector('#hdepart').style.display = 'none';
                                                                document.querySelector('#sigid').style.display = 'none';
                                                                document.querySelector('#psieges').style.display = 'none';
                                                                document.querySelector('#iddep').style.display = 'none';
                                                                document.querySelector('#depargare').style.display = 'none';
                                                                document.querySelector('#arrid').style.display = 'none';
                                                                document.querySelector('#arrsgare').style.display = 'none';
                                                                __venteSetMainEscaleVisible(false);


                                                                document.querySelector('#idcompg').value = `${donitines[0].id_compaga}`;
                                                                __venteFillLigne1Locked(donitines[0], function (codeSel) {
                                                                    if (!codeSel) return;
                                                                    var hd = document.querySelector('#hdepartitine');
                                                                    if (hd) hd.options.length = 1;
                                                                    var datedepart = document.querySelector('#date_depheure') ? document.querySelector('#date_depheure').value : '';
                                                                    var httpH = new XMLHttpRequest();
                                                                    httpH.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheureitine/${encodeURIComponent(codeSel)}/${encodeURIComponent(datedepart)}`, true);
                                                                    httpH.onload = function () {
                                                                        try {
                                                                            var infositin = JSON.parse(httpH.responseText);
                                                                            __venteFillHeureItineSelect(hd, infositin);
                                                                        } catch (eH) {}
                                                                    };
                                                                    httpH.setRequestHeader('Content-Type', 'application/json');
                                                                    httpH.send();
                                                                });
                                                            }
                                                
                                                            if(i === 2)
                                                            {
                                                                __venteSetCheminLigneOption('#idchemins', donitines[1].code_itineraires, donitines[1].nom_itineraires);

                                                                document.querySelector('#itinecodes').value = `${donitines[0].id_lignes}`;
                                                                document.querySelector('#idcompg').value = `${donitines[0].id_compaga}`;
                                                                document.querySelector('#idcompg1').value = `${donitines[1].id_compaga}`;
                                                                var typgare1 = (donitines[0] && donitines[0].code_itineraires) ? String(donitines[0].code_itineraires) : (document.querySelector('#itinecode').value || '');
                                                                var post_typgare1 = typgare1.split('-');
                                                                var seltypgare1 = post_typgare1[0];
                                                                var typgaresel = post_typgare1[1];
                                                                    let httptypequart1;
                                                                    httptypequart1 = new XMLHttpRequest();
                                                                    
                                                                    httptypequart1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgaresel}`, true);
                                                                    httptypequart1.onload = () => 
                                                                    {
                                                                        const donqua1 = JSON.parse(httptypequart1.responseText);
                                                                        if (donqua1 == '') {
                                                                            document.querySelector('#quartier1').options.length = 1;
                                                                        }
                                                                        else{
                                                                            if (Object.entries(donqua1).length >= 1) {
                                                                                            
                                                                                for (let key in Object.entries(donqua1)) {
                                                                                    let optq = document.createElement('option');
                                                                                    optq.value = `${donqua1[key].nom_quartier}`;
                                                                                    optq.innerHTML = `${donqua1[key].nom_quartier}`;
                                                                                    document.querySelector('#quartier1').add(optq);
                                                                                }
                                                                            } else {
                                                                                document.querySelector('#quartier1').options.length = 1;
                                                                            }
                                                                        }
                                                                        

                                                                    };
                                                                    httptypequart1.setRequestHeader('Content-Type', 'application/json');
                                                                    httptypequart1.send();

                                                                        let httptypequartitin;
                                                                        httptypequartitin = new XMLHttpRequest();
                                                                        var itinpro = document.querySelector('#itinecode').value;
                                                                        var datedepart = document.querySelector('#date_depheure').value;
                                                                        httptypequartitin.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheureitine/${itinpro}/${datedepart}`, true);
                                                                    httptypequartitin.onload = () => 
                                                                    {
                                                                        const infositin = JSON.parse(httptypequartitin.responseText);
                                                                        if (infositin == null) 
                                                                        {

                                                                        }
                                                                        __venteFillHeureItineSelect('#hdepartitine', infositin);
                                                                    };
                                                                    httptypequartitin.setRequestHeader('Content-Type', 'application/json');
                                                                    httptypequartitin.send();
                                                                let hrdepartine = document.querySelector('#hdepartitine');
                                                                if (hrdepartine !== null) {
                                                                    hrdepartine.onchange = () => 
                                                                    {   
                                                                        
                                                                        const httpsousgare = new XMLHttpRequest();
                                                                        httpsousgare.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${seltypgare1}`, true);
                                                                        httpsousgare.onload = () => 
                                                                        {
                                                                            const donsousg = JSON.parse(httpsousgare.responseText);
                                                                            console.debug(`${typeof donsousg}-${donsousg.attributes}`, console.memory);
                                                                            var td1 = document.querySelector('#transitedepargare1');
                                                                            if (td1) td1.options.length = 0;
                                                                            if (Object.entries(donsousg).length >= 1) {
                                                                                for (let key in Object.entries(donsousg)) 
                                                                                {
                                                                                    let opt = document.createElement('option');
                                                                                    opt.value = `${donsousg[key].idsousgare}`;
                                                                                    opt.innerHTML = `${donsousg[key].nomsousgare}`;
                                                                                    if (td1) td1.add(opt);
                        
                                                                                }
                                                                                if (td1 && td1.options.length > 0) td1.selectedIndex = 0;
                                                                            }
                                                                        };
                                                                        httpsousgare.setRequestHeader('Content-Type', 'application/json');
                                                                        httpsousgare.send();

                                                                        document.querySelector('#psiegesitines').options.length = 1;
                                                                        const httpRequestit = new XMLHttpRequest();
                                                                        const seleitine = document.querySelector('#hdepartitine')
                                                                            .options[document.querySelector('#hdepartitine').options.selectedIndex].value;

                                                                            var post_lhitine = seleitine.split('/');
                                                                            var selitine = post_lhitine[0];
                                                                            var lhselitine = post_lhitine[1];

                                                                            const dpt_dateitine = document.querySelector('#date_depheure').value;
                                                                            var itinproit = document.querySelector('#itinecode').value;
                                                                        httpRequestit.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifprog/${itinproit}/${dpt_dateitine}/${selitine}/${(typeof sougid !== 'undefined' && sougid) ? sougid : '0'}`, true);
                                                                        httpRequestit.onload = () => 
                                                                        {
                                                                            const donit = JSON.parse(httpRequestit.responseText);
                                                                                console.debug(`${typeof donit} - ${donit.attributes}`, console.memory);

                                                                                if (__venteHandleTransit1ProgList(donit, selitine, dpt_dateitine)) { return; }
                                                                                if (donit == '') 
                                                                                {
                                                                                    
                                                                                        let opt = document.createElement('option');
                                                                                        opt.value = '';                                                             
                                                                                    
                                                                                } 
                                                                                else 
                                                                                {       
                                                                                    if (Object.entries(donit).length >= 1) {
                                                                                        for (let key in Object.entries(donit)) {
                                                                                            document.querySelector('#programtrans').value = `${donit[key].code_progr}`;
                                                                                            document.querySelector('#tarifattrib').value = `${donit[key].typetarif}`;
                                                                                            document.querySelector('#dateprtrans').value = `${donit[key].date_progr}`;
                                                                                            document.querySelector('#deplignetrans').value = `${donit[key].gareidentif}`;
                                                                                            document.querySelector('#intertrans1').value = `${donit[key].intervalle1}`;
                                                                                            document.querySelector('#intertrans2').value = `${donit[key].intervalle2}`;
                                                                                            document.querySelector('#ligntrans').value = `${donit[key].ident_ligne}`;
                                                                                            document.querySelector('#nomitintrans').value = `${donit[key].nom_ligne}`;
                                                                                            document.querySelector('#hertrans').value = `${donit[key].heure}`;
                                                                                            document.querySelector('#catetrans').value = `${donit[key].categori}`;
                                                                                                
                                                                                        }
                                                                                    } 
                                                                                    
                                                                                    const httpPrixit = new XMLHttpRequest();
                                                                                    const seleitine = document.querySelector('#hdepartitine')
                                                                                    .options[document.querySelector('#hdepartitine').options.selectedIndex].value;

                                                                                    var post_lhitine = seleitine.split('/');
                                                                                    var selitine = post_lhitine[0];
                                                                                    var lhselitine = post_lhitine[1];
                                                                                    var tfbs = document.querySelector('#tarifattrib').value;
                                                                                    httpPrixit.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifpriprg/${selitine}/${tfbs}`, true);
                                                                                    httpPrixit.onload = () => 
                                                                                    {

                                                                                        const donprixit = JSON.parse(httpPrixit.responseText);
                                                                                        console.debug(`${typeof donprixit}-${donprixit.attributes}`, console.memory);
                                                                                        if (Object.entries(donprixit).length >= 1) {
                                                                                            for (let key in Object.entries(donprixit)) 
                                                                                            {
                                                                                                document.querySelector('#prix_axetrans').value = `${donprixit[key].prix}`;
                                    
                                                                                            }
                                                                                        }
                                                                                    };
                                                                                    httpPrixit.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpPrixit.send();
                                                                                    
                                                                                    
                                                                                    
                                                                                    const httpRequetteit = new XMLHttpRequest();
                                                                                    const cdprogit = document.querySelector('#programtrans').value;
                                                                                    const dbit = document.querySelector('#intertrans1').value;
                                                                                    const fnit = document.querySelector('#intertrans2').value;
                                                                                    const lgit = document.querySelector('#nomitintrans').value;
                                                                                    const timit = document.querySelector('#hertrans').value;
                                                                                    const dpt_dateitine = document.querySelector('#date_depheure').value;
                                                                                        httpRequetteit.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${cdprogit}/${dpt_dateitine}/${lgit}/${timit}/${dbit}/${fnit}`, true);
                                                                                    httpRequetteit.onload = () => {
                                                                                        const dattait = JSON.parse(httpRequetteit.responseText);
                                                                                        console.debug(`${typeof dattait} - ${dattait.attributes}`, console.memory);
                                                                                        if (Object.entries(dattait).length >= 1) {
                                                                                            for (let key in Object.entries(dattait)) {
                                                                                                
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dattait[key].siege_num}`;
                                                                                                opt.innerHTML = `${dattait[key].siege_num}`;
                                                                                                document.querySelector('#psiegesitines').add(opt);
                                                                                                
                                                                                            }
                                                                                            
                                                                                        } else {
                                                                                            document.querySelector('#psiegesitines').options.length = 1;
                                                                                        }
                                                                                    };
                                                                                    httpRequetteit.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpRequetteit.send();

                                                                                }  
                                                                                
                                                                        };
                                                                        httpRequestit.setRequestHeader('Content-Type', 'application/json');
                                                                        httpRequestit.send();
                                                                         
                                                                    };
                                                                    
                                                            
                                                                }
                                                                progsiegestrans = document.querySelector('#psiegesitines');
                                                                if (progsiegestrans !== null) {
                                                                    progsiegestrans.onchange = () => 
                                                                    {

                                                                        gareidentiftrans = document.querySelector('#deplignetrans').value;
                                                                            // Ne pas vider un départ déjà choisi à chaque clic siège.
                                                                            var td1cur = document.querySelector('#transitedepargare1');
                                                                            if (!td1cur || !td1cur.value) {
                                                                                __venteFillTransitDepart('#transitedepargare1', gareidentiftrans);
                                                                            }
                                                                        let httpSiegestrans;
                                                                        httpSiegestrans = new XMLHttpRequest();
                                                                        const sigstrans = document.querySelector('#psiegesitines')
                                                                        .options[document.querySelector('#psiegesitines').options.selectedIndex].value;
                                                                        const prostrans = document.querySelector('#programtrans').value;

                                                                        httpSiegestrans.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${prostrans}/${sigstrans}`, true);
                                                                        httpSiegestrans.onload = () => 
                                                                        {
                                                                            const donsgetrans = JSON.parse(httpSiegestrans.responseText);
                                                                            console.debug(`${typeof donsgetrans} - ${donsgetrans.attributes}`, console.memory);
                                                                            if(donsgetrans == '')
                                                                            {
                                                                                let httpSiegstrans;
                                                                                httpSiegstrans = new XMLHttpRequest();

                                                                                httpSiegstrans.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${prostrans}/${sigstrans}`, true);
                                                                                httpSiegstrans.onload = () => 
                                                                                {
                                                                                    const dongtrans = JSON.parse(httpSiegstrans.responseText);
                                                                                    document.querySelector('#mess').style.display = 'none';
                                                                                    if (Object.entries(dongtrans).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtrans)) {
                                                                                                document.querySelector('#idtampotrans').value = `${dongtrans[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselecttrans').value = `${dongtrans[key].numsieg}`;
                                                                                            }
                                                                                        }
                                                                                };
                                                                                httpSiegstrans.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSiegstrans.send();
                                                                            }
                                                                            else {
                                                                                document.querySelector('#psiegesitines').value = '';     
                                                                                if (Object.entries(donsgetrans).length >= 1)
                                                                                {
                                                                                    for (let key in Object.entries(donsgetrans)) {
                                                                                        document.querySelector('#idtampotrans').value = `${donsgetrans[key].idtamp}`;                    
                                                                                        document.querySelector('#siegselecttrans').value = `${donsgetrans[key].numsieg}`;
                                                                                    }

                                                                                }
                                                                                document.querySelector('#mess').style.display = 'block';
                                                                                document.querySelector('#erreurMess').innerHTML = `Siege déjà utilisé.`;
                                                                            }
                                                                        };
                                                                        httpSiegestrans.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegestrans.send();

                                                                    
                                                                    };
                                                                }

                                                                let progchemin = document.querySelector('#idchemins');
                                                                if (progchemin !== null) 
                                                                {
                                                                    progchemin.onchange = () => 
                                                                    {
                                                                        document.querySelector('#idcheminsheur').options.length = 1;
                                                                        document.querySelector('#psiegesitines1').options.length = 1;

                                                                        let httpSiegeschemin;
                                                                        httpSiegeschemin = new XMLHttpRequest();
                                                                        
                                                                        const prostranschemin = document.querySelector('#idchemins')
                                                                        .options[document.querySelector('#idchemins').options.selectedIndex].value;

                                                                        var post_typgare2 = prostranschemin.split('-');
                                                                        var seltypgare2 = post_typgare2[0];
                                                                        var typgaresel1 = post_typgare2[1];
                                                                        var tfbs = document.querySelector('#tarifattrib').value;
                                                                        var datedepart = document.querySelector('#date_depheure').value;
                                                                        httpSiegeschemin.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemintr/${prostranschemin}/${datedepart}/${tfbs}`, true);
                                                                        httpSiegeschemin.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschem = JSON.parse(httpSiegeschemin.responseText);
                                                                                    __venteFillCheminHeures('#idcheminsheur', dongtranschem, 'tr2');
                                                                        };
                                                                        httpSiegeschemin.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegeschemin.send();

                                                                    };
                                                                        let prochemintra = document.querySelector('#idcheminsheur');
                                                                    if (prochemintra !== null)
                                                                        __venteWireCheminHeur('idcheminsheur', 'tr2'); if (false) prochemintra.onchange = () => 
                                                                        {  
                                                                            const httpPrixittransite = new XMLHttpRequest();

                                                                            document.querySelector('#psiegesitines1').options.length = 1;
                                                                            document.querySelector('#transitedepargare2').options.length = 1;

                                                                                const transselitine = document.querySelector('#idcheminsheur')
                                                                            .options[document.querySelector('#idcheminsheur').options.selectedIndex].value;
                                                                                var post_trans = transselitine.split('/');
                                                                            var itinetras = post_trans[0];
                                                                            var dbitra = post_trans[1];
                                                                            var fnitra = post_trans[2];
                                                                            var lhertra = post_trans[3];
                                                                            var prixtra = post_trans[4];

                                                                                httpPrixittransite.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetras}`, true);
                                                                                httpPrixittransite.onload = () => 
                                                                                {
                                                                                    const donprixitran = JSON.parse(httpPrixittransite.responseText);
                                                                                    console.debug(`${typeof donprixitran}-${donprixitran.attributes}`, console.memory);
                                                                                    if (Object.entries(donprixitran).length >= 1) {
                                                                                        for (let key in Object.entries(donprixitran)) 
                                                                                        {
                                                                                            document.querySelector('#prix_axetransit').value = `${prixtra}`;
                                                                                            document.querySelector('#catetransit').value = `${donprixitran[key].categori}`;
                                                                                            document.querySelector('#gidtrans').value =  `${donprixitran[key].gareidentif}`;
                                                                                            document.querySelector('#nomitintrans1').value = `${donprixitran[key].nom_ligne}`;
                                                                                            document.querySelector('#ligntrans1').value = `${donprixitran[key].ident_ligne}`;

                                                                                        }
                                                                                        __venteFillTransitDepart('#transitedepargare2', document.querySelector('#gidtrans').value);
                                                                                    }
                                                                                };
                                                                                httpPrixittransite.setRequestHeader('Content-Type', 'application/json');
                                                                                httpPrixittransite.send();
                                                                                
                                                                                      
                                                                                    
                                                                                const httpRequetteitra = new XMLHttpRequest();
                                                                        
                                                                                    httpRequetteitra.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetras}/${dbitra}/${fnitra}`, true);
                                                                                httpRequetteitra.onload = () => {
                                                                                    const dattaitra = JSON.parse(httpRequetteitra.responseText);
                                                                                    console.debug(`${typeof dattaitra} - ${dattaitra.attributes}`, console.memory);
                                                                                    if (Object.entries(dattaitra).length >= 1) {
                                                                                        for (let key in Object.entries(dattaitra)) {
                                                                                            
                                                                                            let opt = document.createElement('option');
                                                                                            opt.value = `${dattaitra[key].siege_num}`;
                                                                                            opt.innerHTML = `${dattaitra[key].siege_num}`;
                                                                                            document.querySelector('#psiegesitines1').add(opt);
                                                                                            
                                                                                        }
                                                                                        
                                                                                    } else {
                                                                                        document.querySelector('#psiegesitines1').options.length = 1;
                                                                                    }
                                                                                };
                                                                                httpRequetteitra.setRequestHeader('Content-Type', 'application/json');
                                                                                httpRequetteitra.send();
                                                                        };

                                                                        progsieges1 = document.querySelector('#psiegesitines1');
                                                                        if (progsieges1 !== null) 
                                                                        {
                                                                            progsieges1.onchange = () => 
                                                                            {
                                                                                

                                                                                const transselitine1 = document.querySelector('#idcheminsheur')
                                                                                .options[document.querySelector('#idcheminsheur').options.selectedIndex].value;
                                                                                var post_trans1 = transselitine1.split('/');
                                                                                var itinetras1 = post_trans1[0];
                                                                                
                                                                                // Départ Corr. 2 déjà rempli au choix de l'heure ; re-sync si besoin.
                                                                                __venteFillTransitDepart('#transitedepargare2', document.querySelector('#gidtrans').value);
                                                                              
                                                                                let httpSieges1;
                                                                                httpSieges1 = new XMLHttpRequest();
                                                                                const sigs1 = document.querySelector('#psiegesitines1')
                                                                                .options[document.querySelector('#psiegesitines1').options.selectedIndex].value;
                                                                                //const pros1 = document.querySelector('#program').value;

                                                                                httpSieges1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetras1}/${sigs1}`, true);
                                                                                httpSieges1.onload = () => 
                                                                                {
                                                                                    const donsge1 = JSON.parse(httpSieges1.responseText);
                                                                                    console.debug(`${typeof donsge1} - ${donsge1.attributes}`, console.memory);
                                                                                    if(donsge1 == '')
                                                                                    {
                                                                                        let httpSiegs1;
                                                                                        httpSiegs1 = new XMLHttpRequest();

                                                                                        httpSiegs1.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetras1}/${sigs1}`, true);
                                                                                        httpSiegs1.onload = () => 
                                                                                        {
                                                                                            const dong1 = JSON.parse(httpSiegs1.responseText);
                                                                                            document.querySelector('#mess').style.display = 'none';
                                                                                            if (Object.entries(dong1).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(dong1)) {
                                                                                                        document.querySelector('#idtampo1').value = `${dong1[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselect1').value = `${dong1[key].numsieg}`;
                                                                                                    }
                                                                                                }
                                                                                        };
                                                                                        httpSiegs1.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegs1.send();
                                                                                    }
                                                                                    else {
                                                                                        document.querySelector('#psiegesitines1').value = '';     
                                                                                        if (Object.entries(donsge1).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(donsge1)) {
                                                                                                document.querySelector('#idtampo1').value = `${donsge1[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselect1').value = `${donsge1[key].numsieg}`;
                                                                                            }

                                                                                        }
                                                                                        document.querySelector('#mess').style.display = 'block';
                                                                                        document.querySelector('#erreurMess').innerHTML = `Siege déjà utilisé.`;
                                                                                    }
                                                                                };
                                                                                httpSieges1.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSieges1.send();

                                                                            };
                                                                        }
                                                                }               
                                                            }
                                                            //second itineraire
                                                            if(i === 3)
                                                            {
                                                                __venteSetCheminLigneOption('#idchemins', donitines[1].code_itineraires, donitines[1].nom_itineraires);

                                                                document.querySelector('#itinecodes').value = `${donitines[0].id_lignes}`;
                                                               document.querySelector('#idcompg').value = `${donitines[0].id_compaga}`;

                                                                __venteSetCheminLigneOption('#idchemins1', donitines[2].code_itineraires, donitines[2].nom_itineraires);

                                                                document.querySelector('#idcompg1').value = `${donitines[1].id_compaga}`;
                                                                document.querySelector('#idcompg2').value = `${donitines[2].id_compaga}`;
                                                                var typgare1 = (donitines[0] && donitines[0].code_itineraires) ? String(donitines[0].code_itineraires) : (document.querySelector('#itinecode').value || '');
                                                                var post_typgare1 = typgare1.split('-');
                                                                var seltypgare1 = post_typgare1[0];
                                                                var typgaresel = post_typgare1[1];
                                                                    let httptypequart1;
                                                                    httptypequart1 = new XMLHttpRequest();
                                                                    
                                                                    httptypequart1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgaresel}`, true);
                                                                    httptypequart1.onload = () => 
                                                                    {
                                                                        const donqua1 = JSON.parse(httptypequart1.responseText);
                                                                        if (donqua1 == '') {
                                                                            document.querySelector('#quartier1').options.length = 1;
                                                                        }
                                                                        else{
                                                                            if (Object.entries(donqua1).length >= 1) {
                                                                                            
                                                                                for (let key in Object.entries(donqua1)) {
                                                                                    let optq = document.createElement('option');
                                                                                    optq.value = `${donqua1[key].nom_quartier}`;
                                                                                    optq.innerHTML = `${donqua1[key].nom_quartier}`;
                                                                                    document.querySelector('#quartier1').add(optq);
                                                                                }
                                                                            } else {
                                                                                document.querySelector('#quartier1').options.length = 1;
                                                                            }
                                                                        }
                                                                        

                                                                    };
                                                                    httptypequart1.setRequestHeader('Content-Type', 'application/json');
                                                                    httptypequart1.send();


                                                                        let httptypequartitin1;
                                                                        httptypequartitin1 = new XMLHttpRequest();
                                                                        var itinpro1 = document.querySelector('#itinecode').value;
                                                                        var datedepart = document.querySelector('#date_depheure').value;
                                                                        httptypequartitin1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheureitine/${itinpro1}/${datedepart}`, true);
                                                                    httptypequartitin1.onload = () => 
                                                                    {
                                                                        const infositin1 = JSON.parse(httptypequartitin1.responseText);
                                                                        if (infositin1 == null) 
                                                                        {


                                                                        }
                                                                        __venteFillHeureItineSelect('#hdepartitine', infositin1);
                                                                    };
                                                                    httptypequartitin1.setRequestHeader('Content-Type', 'application/json');
                                                                    httptypequartitin1.send();
                                                                let hrdepartine1 = document.querySelector('#hdepartitine');
                                                                if (hrdepartine1 !== null) {
                                                                    hrdepartine1.onchange = () => 
                                                                    {
                                                                        var tfbs1 = document.querySelector('#tarifattrib').value;
                                                                        document.querySelector('#psiegesitines').options.length = 1;
                                                                        const httpRequestit1 = new XMLHttpRequest();
                                                                        const seleitine1 = document.querySelector('#hdepartitine')
                                                                            .options[document.querySelector('#hdepartitine').options.selectedIndex].value;

                                                                            var post_lhitine1 = seleitine1.split('/');
                                                                            var selitine1 = post_lhitine1[0];
                                                                            var lhselitine1 = post_lhitine1[1];

                                                                            const dpt_dateitine1 = document.querySelector('#date_depheure').value;
                                                                            var itinproit1 = document.querySelector('#itinecode').value;
                                                                        httpRequestit1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifprog/${itinproit1}/${dpt_dateitine1}/${selitine1}/${(typeof sougid !== 'undefined' && sougid) ? sougid : '0'}`, true);
                                                                        httpRequestit1.onload = () => 
                                                                        {
                                                                            const donit1 = JSON.parse(httpRequestit1.responseText);
                                                                                console.debug(`${typeof donit1} - ${donit1.attributes}`, console.memory);

                                                                                if (__venteHandleTransit1ProgList(donit1, selitine1, dpt_dateitine1)) { return; }
                                                                                if (donit1 == '') 
                                                                                {
                                                                                    
                                                                                        let opt = document.createElement('option');
                                                                                        opt.value = '';                                                             
                                                                                   
                                                                                    
                                                                                    
                                                                                } 
                                                                                else 
                                                                                {       
                                                                                    if (Object.entries(donit1).length >= 1) {
                                                                                        for (let key in Object.entries(donit1)) {
                                                                                            document.querySelector('#programtrans').value = `${donit1[key].code_progr}`;
                                                                                            document.querySelector('#dateprtrans').value = `${donit1[key].date_progr}`;
                                                                                            document.querySelector('#tarifattrib').value = `${donit1[key].typetarif}`;
                                                                                            document.querySelector('#deplignetrans').value = `${donit1[key].gareidentif}`;
                                                                                            document.querySelector('#intertrans1').value = `${donit1[key].intervalle1}`;
                                                                                            document.querySelector('#intertrans2').value = `${donit1[key].intervalle2}`;
                                                                                            document.querySelector('#ligntrans').value = `${donit1[key].ident_ligne}`;
                                                                                            document.querySelector('#nomitintrans').value = `${donit1[key].nom_ligne}`;
                                                                                            document.querySelector('#hertrans').value = `${donit1[key].heure}`;
                                                                                            document.querySelector('#catetrans').value = `${donit1[key].categori}`;

                                                                                        }
                                                                                    } 
                                                                                    
                                                                                    const httpPrixit = new XMLHttpRequest();
                                                                                    const seleitine = document.querySelector('#hdepartitine')
                                                                                    .options[document.querySelector('#hdepartitine').options.selectedIndex].value;

                                                                                    var post_lhitine = seleitine.split('/');
                                                                                    var selitine = post_lhitine[0];
                                                                                    var lhselitine = post_lhitine[1];
                                                                                    var tfbs2 = document.querySelector('#tarifattrib').value;
                                                                                    httpPrixit.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifpriprg/${selitine}/${tfbs2}`, true);
                                                                                    httpPrixit.onload = () => 
                                                                                    {
                                                                                        const donprixit = JSON.parse(httpPrixit.responseText);
                                                                                        console.debug(`${typeof donprixit}-${donprixit.attributes}`, console.memory);
                                                                                        if (Object.entries(donprixit).length >= 1) {
                                                                                            for (let key in Object.entries(donprixit)) 
                                                                                            {
                                                                                                document.querySelector('#prix_axetrans').value = `${donprixit[key].prix}`;
                                    
                                                                                            }
                                                                                        }
                                                                                    };
                                                                                    httpPrixit.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpPrixit.send();

                                                                                    

                                                                                    const httpRequetteit = new XMLHttpRequest();
                                                                                    const cdprogit = document.querySelector('#programtrans').value;
                                                                                    const dbit = document.querySelector('#intertrans1').value;
                                                                                    const fnit = document.querySelector('#intertrans2').value;
                                                                                    const lgit = document.querySelector('#nomitintrans').value;
                                                                                    const timit = document.querySelector('#hertrans').value;
                                                                                    const dpt_dateitine = document.querySelector('#date_depheure').value;
                                                                                        httpRequetteit.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${cdprogit}/${dpt_dateitine}/${lgit}/${timit}/${dbit}/${fnit}`, true);
                                                                                    httpRequetteit.onload = () => {
                                                                                        const dattait = JSON.parse(httpRequetteit.responseText);
                                                                                        console.debug(`${typeof dattait} - ${dattait.attributes}`, console.memory);
                                                                                        if (Object.entries(dattait).length >= 1) {
                                                                                            for (let key in Object.entries(dattait)) {
                                                                                                
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dattait[key].siege_num}`;
                                                                                                opt.innerHTML = `${dattait[key].siege_num}`;
                                                                                                document.querySelector('#psiegesitines').add(opt);
                                                                                                
                                                                                            }
                                                                                            
                                                                                        } else {
                                                                                            document.querySelector('#psiegesitines').options.length = 1;
                                                                                        }
                                                                                    };
                                                                                    httpRequetteit.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpRequetteit.send();

                                                                                }  
                                                                                
                                                                        };
                                                                        httpRequestit1.setRequestHeader('Content-Type', 'application/json');
                                                                        httpRequestit1.send();
                                                                         
                                                                    };
                                                                    
                                                            
                                                                }
                                                                let progsiegestrans = document.querySelector('#psiegesitines');
                                                                if (progsiegestrans !== null) {
                                                                    progsiegestrans.onchange = () => 
                                                                    {

                                                                        const gareidentiftrans1 = document.querySelector('#deplignetrans').value;
                                                                        var td1cur = document.querySelector('#transitedepargare1');
                                                                        if (!td1cur || !td1cur.value) {
                                                                            __venteFillTransitDepart('#transitedepargare1', gareidentiftrans1);
                                                                        }
                                                                        let httpSiegestrans1;
                                                                        httpSiegestrans1 = new XMLHttpRequest();
                                                                        const sigstrans = document.querySelector('#psiegesitines')
                                                                        .options[document.querySelector('#psiegesitines').options.selectedIndex].value;
                                                                        const prostrans = document.querySelector('#programtrans').value;

                                                                        httpSiegestrans1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${prostrans}/${sigstrans}`, true);
                                                                        httpSiegestrans1.onload = () => 
                                                                        {
                                                                            const donsgetrans = JSON.parse(httpSiegestrans1.responseText);
                                                                            console.debug(`${typeof donsgetrans} - ${donsgetrans.attributes}`, console.memory);
                                                                            if(donsgetrans == '')
                                                                            {
                                                                                let httpSiegstrans;
                                                                                httpSiegstrans = new XMLHttpRequest();

                                                                                httpSiegstrans.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${prostrans}/${sigstrans}`, true);
                                                                                httpSiegstrans.onload = () => 
                                                                                {
                                                                                    const dongtrans = JSON.parse(httpSiegstrans.responseText);
                                                                                    document.querySelector('#mess').style.display = 'none';
                                                                                    if (Object.entries(dongtrans).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtrans)) {
                                                                                                document.querySelector('#idtampotrans').value = `${dongtrans[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselecttrans').value = `${dongtrans[key].numsieg}`;
                                                                                            }
                                                                                        }
                                                                                };
                                                                                httpSiegstrans.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSiegstrans.send();
                                                                            }
                                                                            else {
                                                                                document.querySelector('#psiegesitines').value = '';     
                                                                                if (Object.entries(donsgetrans).length >= 1)
                                                                                {
                                                                                    for (let key in Object.entries(donsgetrans)) {
                                                                                        document.querySelector('#idtampotrans').value = `${donsgetrans[key].idtamp}`;                    
                                                                                        document.querySelector('#siegselecttrans').value = `${donsgetrans[key].numsieg}`;
                                                                                    }

                                                                                }
                                                                                document.querySelector('#mess').style.display = 'block';
                                                                                document.querySelector('#erreurMess').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                        };
                                                                        httpSiegestrans1.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegestrans1.send();

                                                                    
                                                                    };
                                                                }
                                                                //premier transite
                                                                let progchemin = document.querySelector('#idchemins');
                                                                if (progchemin !== null) 
                                                                {
                                                                    progchemin.onchange = () => 
                                                                    {
                                                                        document.querySelector('#idcheminsheur').options.length = 1;
                                                                        document.querySelector('#psiegesitines1').options.length = 1;

                                                                        const prostranschemin = document.querySelector('#idchemins')
                                                                        .options[document.querySelector('#idchemins').options.selectedIndex].value;

                                                                        var post_typgare2 = prostranschemin.split('-');
                                                                        var seltypgare2 = post_typgare2[0];
                                                                        var typgaresel1 = post_typgare2[1];
                                                                        let httptypequart2;
                                                                        httptypequart2 = new XMLHttpRequest();
                                                                        
                                                                        httptypequart2.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgaresel1}`, true);
                                                                        httptypequart2.onload = () => 
                                                                        {
                                                                            const donqua2 = JSON.parse(httptypequart2.responseText);
                                                                            if (donqua2 == '') {
                                                                                document.querySelector('#quartier2').options.length = 1;
                                                                            }
                                                                            else{
                                                                                if (Object.entries(donqua2).length >= 1) {
                                                                                                
                                                                                    for (let key in Object.entries(donqua2)) {
                                                                                        let optq1 = document.createElement('option');
                                                                                        optq1.value = `${donqua2[key].nom_quartier}`;
                                                                                        optq1.innerHTML = `${donqua2[key].nom_quartier}`;
                                                                                        document.querySelector('#quartier2').add(optq1);
                                                                                    }
                                                                                } else {
                                                                                    document.querySelector('#quartier2').options.length = 1;
                                                                                }
                                                                            }
                                                                            

                                                                        };
                                                                        httptypequart2.setRequestHeader('Content-Type', 'application/json');
                                                                        httptypequart2.send();

                                                                        let httpSiegeschemin;
                                                                        httpSiegeschemin = new XMLHttpRequest();

                                                                        var tfbs = document.querySelector('#tarifattrib').value;
                                                                        var datedepart = document.querySelector('#date_depheure').value;
                                                                        
                                                                        httpSiegeschemin.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemintr/${prostranschemin}/${datedepart}/${tfbs}`, true);
                                                                        httpSiegeschemin.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschem = JSON.parse(httpSiegeschemin.responseText);
                                                                                    __venteFillCheminHeures('#idcheminsheur', dongtranschem, 'tr2');
                                                                        };
                                                                        httpSiegeschemin.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegeschemin.send();

                                                                    };
                                                                       let prochemintra = document.querySelector('#idcheminsheur');
                                                                    if (prochemintra !== null)
                                                                        __venteWireCheminHeur('idcheminsheur', 'tr2'); if (false) prochemintra.onchange = () => 
                                                                        {  

                                                                           
                                                                            document.querySelector('#psiegesitines1').options.length = 1;
                                                                            document.querySelector('#transitedepargare2').options.length = 1;

                                                                            const httpPrixittransite = new XMLHttpRequest();
                                                                                const transselitine = document.querySelector('#idcheminsheur')
                                                                            .options[document.querySelector('#idcheminsheur').options.selectedIndex].value;
                                                                                var post_trans = transselitine.split('/');
                                                                            var itinetras = post_trans[0];
                                                                            var dbitra = post_trans[1];
                                                                            var fnitra = post_trans[2];
                                                                            var lhertra = post_trans[3];
                                                                            var prixtra = post_trans[4];

                                                                                httpPrixittransite.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetras}`, true);
                                                                                httpPrixittransite.onload = () => 
                                                                                {
                                                                                    const donprixitran = JSON.parse(httpPrixittransite.responseText);
                                                                                    console.debug(`${typeof donprixitran}-${donprixitran.attributes}`, console.memory);
                                                                                    if (Object.entries(donprixitran).length >= 1) {
                                                                                        for (let key in Object.entries(donprixitran)) 
                                                                                        {
                                                                                            document.querySelector('#prix_axetransit').value = `${prixtra}`;
                                                                                            document.querySelector('#catetransit').value = `${donprixitran[key].categori}`;
                                                                                            document.querySelector('#gidtrans').value =  `${donprixitran[key].gareidentif}`;
                                                                                            document.querySelector('#nomitintrans1').value = `${donprixitran[key].nom_ligne}`; 
                                                                                        document.querySelector('#ligntrans1').value = `${donprixitran[key].ident_ligne}`;
                                                                                        }
                                                                                        __venteFillTransitDepart('#transitedepargare2', document.querySelector('#gidtrans').value);
                                                                                    }
                                                                                };
                                                                                httpPrixittransite.setRequestHeader('Content-Type', 'application/json');
                                                                                httpPrixittransite.send();


                                                                                

                                                                                const httpRequetteitra = new XMLHttpRequest();
                                                                        
                                                                                    httpRequetteitra.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetras}/${dbitra}/${fnitra}`, true);
                                                                                httpRequetteitra.onload = () => {
                                                                                    const dattaitra = JSON.parse(httpRequetteitra.responseText);
                                                                                    console.debug(`${typeof dattaitra} - ${dattaitra.attributes}`, console.memory);
                                                                                    if (Object.entries(dattaitra).length >= 1) {
                                                                                        for (let key in Object.entries(dattaitra)) {
                                                                                            
                                                                                            let opt = document.createElement('option');
                                                                                            opt.value = `${dattaitra[key].siege_num}`;
                                                                                            opt.innerHTML = `${dattaitra[key].siege_num}`;
                                                                                            document.querySelector('#psiegesitines1').add(opt);
                                                                                            
                                                                                        }
                                                                                        
                                                                                    } else {
                                                                                        document.querySelector('#psiegesitines1').options.length = 1;
                                                                                    }
                                                                                };
                                                                                httpRequetteitra.setRequestHeader('Content-Type', 'application/json');
                                                                                httpRequetteitra.send();
                                                                        };

                                                                        let progsieges1 = document.querySelector('#psiegesitines1');
                                                                        if (progsieges1 !== null) 
                                                                        {
                                                                            progsieges1.onchange = () => 
                                                                            {

                                                                              __venteFillTransitDepart('#transitedepargare2', document.querySelector('#gidtrans').value);
                                                                                 const transselitine1 = document.querySelector('#idcheminsheur')
                                                                                .options[document.querySelector('#idcheminsheur').options.selectedIndex].value;
                                                                                var post_trans1 = transselitine1.split('/');
                                                                                var itinetras1 = post_trans1[0];
                                                                    
                                                                                

                                                                                let httpSieges1;
                                                                                httpSieges1 = new XMLHttpRequest();
                                                                                const sigs1 = document.querySelector('#psiegesitines1')
                                                                                .options[document.querySelector('#psiegesitines1').options.selectedIndex].value;
                                                                                //const pros1 = document.querySelector('#program').value;

                                                                                httpSieges1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetras1}/${sigs1}`, true);
                                                                                httpSieges1.onload = () => 
                                                                                {
                                                                                    const donsge1 = JSON.parse(httpSieges1.responseText);
                                                                                    console.debug(`${typeof donsge1} - ${donsge1.attributes}`, console.memory);
                                                                                    if(donsge1 == '')
                                                                                    {
                                                                                        let httpSiegs1;
                                                                                        httpSiegs1 = new XMLHttpRequest();

                                                                                        httpSiegs1.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetras1}/${sigs1}`, true);
                                                                                        httpSiegs1.onload = () => 
                                                                                        {
                                                                                            const dong1 = JSON.parse(httpSiegs1.responseText);
                                                                                            document.querySelector('#mess').style.display = 'none';
                                                                                            if (Object.entries(dong1).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(dong1)) {
                                                                                                        document.querySelector('#idtampo1').value = `${dong1[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselect1').value = `${dong1[key].numsieg}`;
                                                                                                    }
                                                                                                }
                                                                                        };
                                                                                        httpSiegs1.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegs1.send();
                                                                                    }
                                                                                    else {
                                                                                        document.querySelector('#psiegesitines1').value = '';     
                                                                                        if (Object.entries(donsge1).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(donsge1)) {
                                                                                                document.querySelector('#idtampo1').value = `${donsge1[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselect1').value = `${donsge1[key].numsieg}`;
                                                                                            }

                                                                                        }
                                                                                        document.querySelector('#mess').style.display = 'block';
                                                                                        document.querySelector('#erreurMess').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                                };
                                                                                httpSieges1.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSieges1.send();

                                                                            };
                                                                        }
                                                                }
                                                                let progchemin1 = document.querySelector('#idchemins1');
                                                                if (progchemin1 !== null) 
                                                                {
                                                                    progchemin1.onchange = () => 
                                                                    {
                                                                       
                                                                        document.querySelector('#idcheminsheur1').options.length = 1;
                                                                        document.querySelector('#psiegesitines2').options.length = 1;

                                                                        const prostranschemin32 = document.querySelector('#idchemins1')
                                                                        .options[document.querySelector('#idchemins1').options.selectedIndex].value;

                                                                        var post_typgare32 = prostranschemin32.split('-');
                                                                        var seltypgare32 = post_typgare32[0];
                                                                        var typgaresel31 = post_typgare32[1];
                                                                      
                                                                        let httpSiegeschemin1;
                                                                        httpSiegeschemin1 = new XMLHttpRequest();

                                                                        var datedepart = document.querySelector('#date_depheure').value;
                                                                        var tfbs = document.querySelector('#tarifattrib').value;
                                                                        const prostranschemin1 = document.querySelector('#idchemins1')
                                                                        .options[document.querySelector('#idchemins1').options.selectedIndex].value;

                                                                        httpSiegeschemin1.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemintr/${prostranschemin1}/${datedepart}/${tfbs}`, true);
                                                                        httpSiegeschemin1.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschem1 = JSON.parse(httpSiegeschemin1.responseText);
                                                                                    __venteFillCheminHeures('#idcheminsheur1', dongtranschem1, 'tr3');
                                                                        };
                                                                        httpSiegeschemin1.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegeschemin1.send();

                                                                    };
                                                                      let prochemintra1 = document.querySelector('#idcheminsheur1');
                                                                    if (prochemintra1 !== null)
                                                                        __venteWireCheminHeur('idcheminsheur1', 'tr3'); if (false) prochemintra1.onchange = () => 
                                                                        {  

                                                                                
                                                                                document.querySelector('#psiegesitines2').options.length = 1;
                                                                                document.querySelector('#transitedepargare3').options.length = 1;

                                                                            const httpPrixittransite1 = new XMLHttpRequest();
                                                                                const transselitine1 = document.querySelector('#idcheminsheur1')
                                                                            .options[document.querySelector('#idcheminsheur1').options.selectedIndex].value;
                                                                                var post_trans1 = transselitine1.split('/');
                                                                            var itinetras1 = post_trans1[0];
                                                                            var dbitra1 = post_trans1[1];
                                                                            var fnitra1 = post_trans1[2];
                                                                            var lhertra1 = post_trans1[3];
                                                                            var prixtra1 = post_trans1[4];

                                                                                httpPrixittransite1.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetras1}`, true);
                                                                                httpPrixittransite1.onload = () => 
                                                                                {
                                                                                    const donprixitran1 = JSON.parse(httpPrixittransite1.responseText);
                                                                                    if (Object.entries(donprixitran1).length >= 1) {
                                                                                        for (let key in Object.entries(donprixitran1)) 
                                                                                        {
                                                                                            document.querySelector('#prix_axetransit1').value = `${prixtra1}`;
                                                                                            document.querySelector('#catetransit1').value = `${donprixitran1[key].categori}`;
                                                                                            document.querySelector('#gidtrans1').value =  `${donprixitran1[key].gareidentif}`;
                                                                                            document.querySelector('#nomitintrans2').value = `${donprixitran1[key].nom_ligne}`;
                                                                                            document.querySelector('#ligntrans2').value = `${donprixitran1[key].ident_ligne}`;
                                                                                        }
                                                                                        __venteFillTransitDepart('#transitedepargare3', document.querySelector('#gidtrans1').value);
                                                                                    }
                                                                                };
                                                                                httpPrixittransite1.setRequestHeader('Content-Type', 'application/json');
                                                                                httpPrixittransite1.send();
                                                                      
                                                                              
                                                                               
                                                                                const httpRequetteitra1 = new XMLHttpRequest();
                                                                        
                                                                                    httpRequetteitra1.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetras1}/${dbitra1}/${fnitra1}`, true);
                                                                                httpRequetteitra1.onload = () => {
                                                                                    const dattaitra1 = JSON.parse(httpRequetteitra1.responseText);
                                                                                    console.debug(`${typeof dattaitra1} - ${dattaitra1.attributes}`, console.memory);
                                                                                    if (Object.entries(dattaitra1).length >= 1) {
                                                                                        for (let key in Object.entries(dattaitra1)) {
                                                                                            
                                                                                            let opt = document.createElement('option');
                                                                                            opt.value = `${dattaitra1[key].siege_num}`;
                                                                                            opt.innerHTML = `${dattaitra1[key].siege_num}`;
                                                                                            document.querySelector('#psiegesitines2').add(opt);
                                                                                            
                                                                                        }
                                                                                        
                                                                                    } else {
                                                                                        document.querySelector('#psiegesitines2').options.length = 1;
                                                                                    }
                                                                                };
                                                                                httpRequetteitra1.setRequestHeader('Content-Type', 'application/json');
                                                                                httpRequetteitra1.send();
                                                                        };

                                                                        let progsieges2 = document.querySelector('#psiegesitines2');
                                                                        if (progsieges2 !== null) 
                                                                        {
                                                                            progsieges2.onchange = () => 
                                                                            {
                                                                                    const transselitine2 = document.querySelector('#idcheminsheur1')
                                                                                .options[document.querySelector('#idcheminsheur1').options.selectedIndex].value;
                                                                                var post_trans2 = transselitine2.split('/');
                                                                                var itinetras2 = post_trans2[0];
                                                                                    
                                                                                    __venteFillTransitDepart('#transitedepargare3', document.querySelector('#gidtrans1').value);

                                                                                let httpSieges2;
                                                                                httpSieges2 = new XMLHttpRequest();
                                                                                const sigs2 = document.querySelector('#psiegesitines2')
                                                                                .options[document.querySelector('#psiegesitines2').options.selectedIndex].value;

                                                                                httpSieges2.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetras2}/${sigs2}`, true);
                                                                                httpSieges2.onload = () => 
                                                                                {
                                                                                    const donsge2 = JSON.parse(httpSieges2.responseText);
                                                                                    if(donsge2 == '')
                                                                                    {
                                                                                        let httpSiegs2;
                                                                                        httpSiegs2 = new XMLHttpRequest();

                                                                                        httpSiegs2.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetras2}/${sigs2}`, true);
                                                                                        httpSiegs2.onload = () => 
                                                                                        {
                                                                                            const dong2 = JSON.parse(httpSiegs2.responseText);
                                                                                            document.querySelector('#mess').style.display = 'none';
                                                                                            if (Object.entries(dong2).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(dong2)) {
                                                                                                        document.querySelector('#idtampo2').value = `${dong2[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselect2').value = `${dong2[key].numsieg}`;
                                                                                                    }
                                                                                                }
                                                                                        };
                                                                                        httpSiegs2.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegs2.send();
                                                                                    }
                                                                                    else {
                                                                                        document.querySelector('#psiegesitines2').value = '';     
                                                                                        if (Object.entries(donsge2).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(donsge1)) {
                                                                                                document.querySelector('#idtampo2').value = `${donsge2[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselect2').value = `${donsge2[key].numsieg}`;
                                                                                            }

                                                                                        }
                                                                                        document.querySelector('#mess').style.display = 'block';
                                                                                        document.querySelector('#erreurMess').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                                };
                                                                                httpSieges2.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSieges2.send();

                                                                            };
                                                                        }
                                                                }               
                                                            }

                                                            //troisieme itineraire
                                                            if(i === 4)
                                                            {
                                                                __venteSetCheminLigneOption('#idchemins', donitines[1].code_itineraires, donitines[1].nom_itineraires);
                                                                __venteSetCheminLigneOption('#idchemins1', donitines[2].code_itineraires, donitines[2].nom_itineraires);
                                                                __venteSetCheminLigneOption('#idchemins2', donitines[3].code_itineraires, donitines[3].nom_itineraires);

                                                               
                                                                document.querySelector('#itinecodes').value = `${donitines[0].id_lignes}`;
                                                                document.querySelector('#idcompg').value = `${donitines[0].id_compaga}`;
                                                                document.querySelector('#idcompg1').value = `${donitines[1].id_compaga}`;
                                                                document.querySelector('#idcompg2').value = `${donitines[2].id_compaga}`;
                                                                document.querySelector('#idcompg3').value = `${donitines[3].id_compaga}`;
                                                                var typgare1 = (donitines[0] && donitines[0].code_itineraires) ? String(donitines[0].code_itineraires) : (document.querySelector('#itinecode').value || '');
                                                                var post_typgare1 = typgare1.split('-');
                                                                var seltypgare1 = post_typgare1[0];
                                                                var typgaresel = post_typgare1[1];
                                                                    let httptypequart1;
                                                                    httptypequart1 = new XMLHttpRequest();
                                                                    
                                                                    httptypequart1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgaresel}`, true);
                                                                    httptypequart1.onload = () => 
                                                                    {
                                                                        const donqua1 = JSON.parse(httptypequart1.responseText);
                                                                        if (donqua1 == '') {
                                                                            document.querySelector('#quartier1').options.length = 1;
                                                                        }
                                                                        else{
                                                                            if (Object.entries(donqua1).length >= 1) {
                                                                                            
                                                                                for (let key in Object.entries(donqua1)) {
                                                                                    let optq = document.createElement('option');
                                                                                    optq.value = `${donqua1[key].nom_quartier}`;
                                                                                    optq.innerHTML = `${donqua1[key].nom_quartier}`;
                                                                                    document.querySelector('#quartier1').add(optq);
                                                                                }
                                                                            } else {
                                                                                document.querySelector('#quartier1').options.length = 1;
                                                                            }
                                                                        }
                                                                        

                                                                    };
                                                                    httptypequart1.setRequestHeader('Content-Type', 'application/json');
                                                                    httptypequart1.send();



                                                                        let httptypequartitin1;
                                                                        httptypequartitin1 = new XMLHttpRequest();
                                                                        var datedepart = document.querySelector('#date_depheure').value;
                                                                        var itinpro1 = document.querySelector('#itinecode').value;
                                                                        httptypequartitin1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheureitine/${itinpro1}/${datedepart}`, true);
                                                                    httptypequartitin1.onload = () => 
                                                                    {
                                                                        const infositin1 = JSON.parse(httptypequartitin1.responseText);
                                                                        if (infositin1 == null) 
                                                                        {


                                                                        }
                                                                        __venteFillHeureItineSelect('#hdepartitine', infositin1);
                                                                    };
                                                                    httptypequartitin1.setRequestHeader('Content-Type', 'application/json');
                                                                    httptypequartitin1.send();
                                                                let hrdepartine1 = document.querySelector('#hdepartitine');
                                                                if (hrdepartine1 !== null) {
                                                                    hrdepartine1.onchange = () => 
                                                                    {
                                                                        document.querySelector('#psiegesitines').options.length = 1;
                                                                        const httpRequestit1 = new XMLHttpRequest();
                                                                        const seleitine1 = document.querySelector('#hdepartitine')
                                                                            .options[document.querySelector('#hdepartitine').options.selectedIndex].value;

                                                                            var post_lhitine1 = seleitine1.split('/');
                                                                            var selitine1 = post_lhitine1[0];
                                                                            var lhselitine1 = post_lhitine1[1];
                                                                            
                                                                            const dpt_dateitine1 = document.querySelector('#date_depheure').value;
                                                                            var itinproit1 = document.querySelector('#itinecode').value;
                                                                        httpRequestit1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifprog/${itinproit1}/${dpt_dateitine1}/${selitine1}/${(typeof sougid !== 'undefined' && sougid) ? sougid : '0'}`, true);
                                                                        httpRequestit1.onload = () => 
                                                                        {
                                                                            const donit1 = JSON.parse(httpRequestit1.responseText);
                                                                                console.debug(`${typeof donit1} - ${donit1.attributes}`, console.memory);

                                                                                if (__venteHandleTransit1ProgList(donit1, selitine1, dpt_dateitine1)) { return; }
                                                                                if (donit1 == '') 
                                                                                {
                                                                                    
                                                                                        let opt = document.createElement('option');
                                                                                        opt.value = '';                                                             
                                                                                   
                                                                                    
                                                                                    
                                                                                } 
                                                                                else 
                                                                                {       
                                                                                    if (Object.entries(donit1).length >= 1) {
                                                                                        for (let key in Object.entries(donit1)) {
                                                                                            document.querySelector('#programtrans').value = `${donit1[key].code_progr}`;
                                                                                            document.querySelector('#dateprtrans').value = `${donit1[key].date_progr}`;
                                                                                            document.querySelector('#tarifattrib').value = `${donit1[key].typetarif}`;
                                                                                            document.querySelector('#deplignetrans').value = `${donit1[key].gareidentif}`;
                                                                                            document.querySelector('#intertrans1').value = `${donit1[key].intervalle1}`;
                                                                                            document.querySelector('#intertrans2').value = `${donit1[key].intervalle2}`;
                                                                                            document.querySelector('#ligntrans').value = `${donit1[key].ident_ligne}`;
                                                                                            document.querySelector('#nomitintrans').value = `${donit1[key].nom_ligne}`;
                                                                                            document.querySelector('#hertrans').value = `${donit1[key].heure}`;
                                                                                            document.querySelector('#catetrans').value = `${donit1[key].categori}`;

                                                                                        }
                                                                                    } 
                                                                                    
                                                                                    const httpPrixit = new XMLHttpRequest();
                                                                                    const seleitine = document.querySelector('#hdepartitine')
                                                                                    .options[document.querySelector('#hdepartitine').options.selectedIndex].value;

                                                                                    var post_lhitine = seleitine.split('/');
                                                                                    var selitine = post_lhitine[0];
                                                                                    var lhselitine = post_lhitine[1];
                                                                                            var tfbs1 = document.querySelector('#tarifattrib').value;
                                                                                    httpPrixit.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifpriprg/${selitine}/${tfbs1}`, true);
                                                                                    httpPrixit.onload = () => 
                                                                                    {
                                                                                        const donprixit = JSON.parse(httpPrixit.responseText);
                                                                                        console.debug(`${typeof donprixit}-${donprixit.attributes}`, console.memory);
                                                                                        if (Object.entries(donprixit).length >= 1) {
                                                                                            for (let key in Object.entries(donprixit)) 
                                                                                            {
                                                                                                document.querySelector('#prix_axetrans').value = `${donprixit[key].prix}`;
                                    
                                                                                            }
                                                                                        }
                                                                                    };
                                                                                    httpPrixit.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpPrixit.send();

                                                                                    

                                                                                    const httpRequetteit = new XMLHttpRequest();
                                                                                    const cdprogit = document.querySelector('#programtrans').value;
                                                                                    const dbit = document.querySelector('#intertrans1').value;
                                                                                    const fnit = document.querySelector('#intertrans2').value;
                                                                                    const lgit = document.querySelector('#nomitintrans').value;
                                                                                    const timit = document.querySelector('#hertrans').value;
                                                                                    const dpt_dateitine = document.querySelector('#date_depheure').value;
                                                                                        httpRequetteit.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${cdprogit}/${dpt_dateitine}/${lgit}/${timit}/${dbit}/${fnit}`, true);
                                                                                    httpRequetteit.onload = () => {
                                                                                        const dattait = JSON.parse(httpRequetteit.responseText);
                                                                                        console.debug(`${typeof dattait} - ${dattait.attributes}`, console.memory);
                                                                                        if (Object.entries(dattait).length >= 1) {
                                                                                            for (let key in Object.entries(dattait)) {
                                                                                                
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dattait[key].siege_num}`;
                                                                                                opt.innerHTML = `${dattait[key].siege_num}`;
                                                                                                document.querySelector('#psiegesitines').add(opt);
                                                                                                
                                                                                            }
                                                                                            
                                                                                        } else {
                                                                                            document.querySelector('#psiegesitines').options.length = 1;
                                                                                        }
                                                                                    };
                                                                                    httpRequetteit.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpRequetteit.send();

                                                                                }  
                                                                                
                                                                        };
                                                                        httpRequestit1.setRequestHeader('Content-Type', 'application/json');
                                                                        httpRequestit1.send();
                                                                         
                                                                    };
                                                                    
                                                            
                                                                }
                                                                let progsiegestrans = document.querySelector('#psiegesitines');
                                                                if (progsiegestrans !== null) {
                                                                    progsiegestrans.onchange = () => 
                                                                    {

                                                                       const gareidentiftrans1 = document.querySelector('#deplignetrans').value;
                                                                        var td1cur = document.querySelector('#transitedepargare1');
                                                                        if (!td1cur || !td1cur.value) {
                                                                            __venteFillTransitDepart('#transitedepargare1', gareidentiftrans1);
                                                                        }
                                                                        let httpSiegestrans1;
                                                                        httpSiegestrans1 = new XMLHttpRequest();
                                                                        const sigstrans = document.querySelector('#psiegesitines')
                                                                        .options[document.querySelector('#psiegesitines').options.selectedIndex].value;
                                                                        const prostrans = document.querySelector('#programtrans').value;

                                                                        httpSiegestrans1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${prostrans}/${sigstrans}`, true);
                                                                        httpSiegestrans1.onload = () => 
                                                                        {
                                                                            const donsgetrans = JSON.parse(httpSiegestrans1.responseText);
                                                                            console.debug(`${typeof donsgetrans} - ${donsgetrans.attributes}`, console.memory);
                                                                            if(donsgetrans == '')
                                                                            {
                                                                                let httpSiegstrans;
                                                                                httpSiegstrans = new XMLHttpRequest();

                                                                                httpSiegstrans.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${prostrans}/${sigstrans}`, true);
                                                                                httpSiegstrans.onload = () => 
                                                                                {
                                                                                    const dongtrans = JSON.parse(httpSiegstrans.responseText);
                                                                                    document.querySelector('#mess').style.display = 'none';
                                                                                    if (Object.entries(dongtrans).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtrans)) {
                                                                                                document.querySelector('#idtampotrans').value = `${dongtrans[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselecttrans').value = `${dongtrans[key].numsieg}`;
                                                                                            }
                                                                                        }
                                                                                };
                                                                                httpSiegstrans.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSiegstrans.send();
                                                                            }
                                                                            else {
                                                                                document.querySelector('#psiegesitines').value = '';     
                                                                                if (Object.entries(donsgetrans).length >= 1)
                                                                                {
                                                                                    for (let key in Object.entries(donsgetrans)) {
                                                                                        document.querySelector('#idtampotrans').value = `${donsgetrans[key].idtamp}`;                    
                                                                                        document.querySelector('#siegselecttrans').value = `${donsgetrans[key].numsieg}`;
                                                                                    }

                                                                                }
                                                                                document.querySelector('#mess').style.display = 'block';
                                                                                document.querySelector('#erreurMess').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                        };
                                                                        httpSiegestrans1.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegestrans1.send();

                                                                    
                                                                    };
                                                                }
                                                                //premier transite
                                                                let progchemin = document.querySelector('#idchemins');
                                                                if (progchemin !== null) 
                                                                {
                                                                    progchemin.onchange = () => 
                                                                    {
                                                                        document.querySelector('#idcheminsheur').options.length = 1;
                                                                        document.querySelector('#psiegesitines1').options.length = 1;

                                                                        var datedepart = document.querySelector('#date_depheure').value;
                                                                        
                                                                        const prostranschemin = document.querySelector('#idchemins')
                                                                        .options[document.querySelector('#idchemins').options.selectedIndex].value;

                                                                        var post_typgare2 = prostranschemin.split('-');
                                                                        var seltypgare2 = post_typgare2[0];
                                                                        var typgaresel1 = post_typgare2[1];
                                                                        let httptypequart2;
                                                                        httptypequart2 = new XMLHttpRequest();
                                                                        
                                                                        httptypequart2.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgaresel1}`, true);
                                                                        httptypequart2.onload = () => 
                                                                        {
                                                                            const donqua2 = JSON.parse(httptypequart2.responseText);
                                                                            if (donqua2 == '') {
                                                                                document.querySelector('#quartier2').options.length = 1;
                                                                            }
                                                                            else{
                                                                                if (Object.entries(donqua2).length >= 1) {
                                                                                                
                                                                                    for (let key in Object.entries(donqua2)) {
                                                                                        let optq1 = document.createElement('option');
                                                                                        optq1.value = `${donqua2[key].nom_quartier}`;
                                                                                        optq1.innerHTML = `${donqua2[key].nom_quartier}`;
                                                                                        document.querySelector('#quartier2').add(optq1);
                                                                                    }
                                                                                } else {
                                                                                    document.querySelector('#quartier2').options.length = 1;
                                                                                }
                                                                            }
                                                                            

                                                                        };
                                                                        httptypequart2.setRequestHeader('Content-Type', 'application/json');
                                                                        httptypequart2.send();
                                                                        
                                                                        let httpSiegeschemin;
                                                                        httpSiegeschemin = new XMLHttpRequest();
                                                                        
                                                                        var tfbs = document.querySelector('#tarifattrib').value;

                                                                        httpSiegeschemin.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemintr/${prostranschemin}/${datedepart}/${tfbs}`, true);
                                                                        httpSiegeschemin.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschem = JSON.parse(httpSiegeschemin.responseText);
                                                                                    __venteFillCheminHeures('#idcheminsheur', dongtranschem, 'tr2');
                                                                        };
                                                                        httpSiegeschemin.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegeschemin.send();

                                                                    };
                                                                        let prochemintra = document.querySelector('#idcheminsheur');
                                                                        if (prochemintra !== null){
                                                                            __venteWireCheminHeur('idcheminsheur', 'tr2'); if (false) prochemintra.onchange = () => 
                                                                            {  

                                                                               
                                                                                document.querySelector('#psiegesitines1').options.length = 1;
                                                                                document.querySelector('#transitedepargare2').options.length = 1;

                                                                                const httpPrixittransite = new XMLHttpRequest();
                                                                                    const transselitine = document.querySelector('#idcheminsheur')
                                                                                .options[document.querySelector('#idcheminsheur').options.selectedIndex].value;
                                                                                    var post_trans = transselitine.split('/');
                                                                                var itinetras = post_trans[0];
                                                                                var dbitra = post_trans[1];
                                                                                var fnitra = post_trans[2];
                                                                                var lhertra = post_trans[3];
                                                                                var prixtra = post_trans[4];

                                                                                    httpPrixittransite.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetras}`, true);
                                                                                    httpPrixittransite.onload = () => 
                                                                                    {
                                                                                        const donprixitran = JSON.parse(httpPrixittransite.responseText);
                                                                                        console.debug(`${typeof donprixitran}-${donprixitran.attributes}`, console.memory);
                                                                                        if (Object.entries(donprixitran).length >= 1) {
                                                                                            for (let key in Object.entries(donprixitran)) 
                                                                                            {
                                                                                                document.querySelector('#prix_axetransit').value = `${prixtra}`;
                                                                                                document.querySelector('#catetransit').value = `${donprixitran[key].categori}`;
                                                                                                document.querySelector('#gidtrans').value =  `${donprixitran[key].gareidentif}`;
                                                                                                document.querySelector('#nomitintrans1').value = `${donprixitran[key].nom_ligne}`;
                                                                                                document.querySelector('#ligntrans1').value = `${donprixitran[key].ident_ligne}`;
                                                                                            }
                                                                                            __venteFillTransitDepart('#transitedepargare2', document.querySelector('#gidtrans').value);
                                                                                        }
                                                                                    };
                                                                                    httpPrixittransite.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpPrixittransite.send();
                                                                          

                                                                                    
                                                                                    const httpRequetteitra = new XMLHttpRequest();
                                                                            
                                                                                        httpRequetteitra.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetras}/${dbitra}/${fnitra}`, true);
                                                                                    httpRequetteitra.onload = () => {
                                                                                        const dattaitra = JSON.parse(httpRequetteitra.responseText);
                                                                                        console.debug(`${typeof dattaitra} - ${dattaitra.attributes}`, console.memory);
                                                                                        if (Object.entries(dattaitra).length >= 1) {
                                                                                            for (let key in Object.entries(dattaitra)) {
                                                                                                
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dattaitra[key].siege_num}`;
                                                                                                opt.innerHTML = `${dattaitra[key].siege_num}`;
                                                                                                document.querySelector('#psiegesitines1').add(opt);
                                                                                                
                                                                                            }
                                                                                            
                                                                                        } else {
                                                                                            document.querySelector('#psiegesitines1').options.length = 1;
                                                                                        }
                                                                                    };
                                                                                    httpRequetteitra.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpRequetteitra.send();
                                                                            };
                                                                        }
                                                                        let progsieges1 = document.querySelector('#psiegesitines1');
                                                                        if (progsieges1 !== null) 
                                                                        {
                                                                            progsieges1.onchange = () => 
                                                                            {

                                                                               __venteFillTransitDepart('#transitedepargare2', document.querySelector('#gidtrans').value);

                                                                                    const transselitine1 = document.querySelector('#idcheminsheur')
                                                                                .options[document.querySelector('#idcheminsheur').options.selectedIndex].value;
                                                                                var post_trans1 = transselitine1.split('/');
                                                                                var itinetras1 = post_trans1[0];
                                                                    
                                                                                let httpSieges1;
                                                                                httpSieges1 = new XMLHttpRequest();
                                                                                const sigs1 = document.querySelector('#psiegesitines1')
                                                                                .options[document.querySelector('#psiegesitines1').options.selectedIndex].value;
                                                                                //const pros1 = document.querySelector('#program').value;

                                                                                httpSieges1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetras1}/${sigs1}`, true);
                                                                                httpSieges1.onload = () => 
                                                                                {
                                                                                    const donsge1 = JSON.parse(httpSieges1.responseText);
                                                                                    console.debug(`${typeof donsge1} - ${donsge1.attributes}`, console.memory);
                                                                                    if(donsge1 == '')
                                                                                    {
                                                                                        let httpSiegs1;
                                                                                        httpSiegs1 = new XMLHttpRequest();

                                                                                        httpSiegs1.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetras1}/${sigs1}`, true);
                                                                                        httpSiegs1.onload = () => 
                                                                                        {
                                                                                            const dong1 = JSON.parse(httpSiegs1.responseText);
                                                                                            document.querySelector('#mess').style.display = 'none';
                                                                                            if (Object.entries(dong1).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(dong1)) {
                                                                                                        document.querySelector('#idtampo1').value = `${dong1[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselect1').value = `${dong1[key].numsieg}`;
                                                                                                    }
                                                                                                }
                                                                                        };
                                                                                        httpSiegs1.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegs1.send();
                                                                                    }
                                                                                    else {
                                                                                        document.querySelector('#psiegesitines1').value = '';     
                                                                                        if (Object.entries(donsge1).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(donsge1)) {
                                                                                                document.querySelector('#idtampo1').value = `${donsge1[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselect1').value = `${donsge1[key].numsieg}`;
                                                                                            }

                                                                                        }
                                                                                        document.querySelector('#mess').style.display = 'block';
                                                                                        document.querySelector('#erreurMess').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                                };
                                                                                httpSieges1.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSieges1.send();

                                                                            };
                                                                        }
                                                                }
                                                                //deuxieme transite
                                                                let progchemin1 = document.querySelector('#idchemins1');
                                                                if (progchemin1 !== null) 
                                                                {
                                                                    progchemin1.onchange = () => 
                                                                    {
                                                                        document.querySelector('#idcheminsheur1').options.length = 1;
                                                                        document.querySelector('#psiegesitines2').options.length = 1;

                                                                        const prostranschemin32 = document.querySelector('#idchemins1')
                                                                        .options[document.querySelector('#idchemins1').options.selectedIndex].value;

                                                                        var post_typgare32 = prostranschemin32.split('-');
                                                                        var seltypgare32 = post_typgare32[0];
                                                                        var typgaresel31 = post_typgare32[1];
                                                                        let httptypequart32;
                                                                        httptypequart32 = new XMLHttpRequest();
                                                                        
                                                                        httptypequart32.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgaresel31}`, true);
                                                                        httptypequart32.onload = () => 
                                                                        {
                                                                            const donqua32 = JSON.parse(httptypequart32.responseText);
                                                                            if (donqua32 == '') {
                                                                                document.querySelector('#quartier3').options.length = 1;
                                                                            }
                                                                            else{
                                                                                if (Object.entries(donqua32).length >= 1) {
                                                                                                
                                                                                    for (let key in Object.entries(donqua32)) {
                                                                                        let optq31 = document.createElement('option');
                                                                                        optq31.value = `${donqua32[key].nom_quartier}`;
                                                                                        optq31.innerHTML = `${donqua32[key].nom_quartier}`;
                                                                                        document.querySelector('#quartier3').add(optq31);
                                                                                    }
                                                                                } else {
                                                                                    document.querySelector('#quartier3').options.length = 1;
                                                                                }
                                                                            }
                                                                            

                                                                        };
                                                                        httptypequart32.setRequestHeader('Content-Type', 'application/json');
                                                                        httptypequart32.send();
                                                                        
                                                                        let httpSiegeschemin1;
                                                                        httpSiegeschemin1 = new XMLHttpRequest();
                                                                        
                                                                        var datedepart = document.querySelector('#date_depheure').value;
                                                                        var tfbs = document.querySelector('#tarifattrib').value;
                                                                        const prostranschemin1 = document.querySelector('#idchemins1')
                                                                        .options[document.querySelector('#idchemins1').options.selectedIndex].value;

                                                                        httpSiegeschemin1.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemintr/${prostranschemin1}/${datedepart}/${tfbs}`, true);
                                                                        httpSiegeschemin1.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschem1 = JSON.parse(httpSiegeschemin1.responseText);
                                                                                    __venteFillCheminHeures('#idcheminsheur1', dongtranschem1, 'tr3');
                                                                        };
                                                                        httpSiegeschemin1.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegeschemin1.send();

                                                                    };
                                                                       let prochemintra1 = document.querySelector('#idcheminsheur1');
                                                                    if (prochemintra1 !== null)
                                                                        __venteWireCheminHeur('idcheminsheur1', 'tr3'); if (false) prochemintra1.onchange = () => 
                                                                        {  
                                                                            
                                                                            document.querySelector('#psiegesitines2').options.length = 1;
                                                                            document.querySelector('#transitedepargare3').options.length = 1;

                                                                            const httpPrixittransite1 = new XMLHttpRequest();
                                                                                const transselitine1 = document.querySelector('#idcheminsheur1')
                                                                            .options[document.querySelector('#idcheminsheur1').options.selectedIndex].value;
                                                                                var post_trans1 = transselitine1.split('/');
                                                                            var itinetras1 = post_trans1[0];
                                                                            var dbitra1 = post_trans1[1];
                                                                            var fnitra1 = post_trans1[2];
                                                                            var lhertra1 = post_trans1[3];
                                                                            var prixtra1 = post_trans1[4];

                                                                                httpPrixittransite1.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetras1}`, true);
                                                                                httpPrixittransite1.onload = () => 
                                                                                {
                                                                                    const donprixitran1 = JSON.parse(httpPrixittransite1.responseText);
                                                                                    if (Object.entries(donprixitran1).length >= 1) {
                                                                                        for (let key in Object.entries(donprixitran1)) 
                                                                                        {
                                                                                            document.querySelector('#prix_axetransit1').value = `${prixtra1}`;
                                                                                            document.querySelector('#catetransit1').value = `${donprixitran1[key].categori}`;
                                                                                            document.querySelector('#gidtrans1').value =  `${donprixitran1[key].gareidentif}`;
                                                                                            document.querySelector('#nomitintrans2').value = `${donprixitran1[key].nom_ligne}`;
                                                                                            document.querySelector('#ligntrans2').value = `${donprixitran1[key].ident_ligne}`;
                                                                                        }
                                                                                        __venteFillTransitDepart('#transitedepargare3', document.querySelector('#gidtrans1').value);
                                                                                    }
                                                                                };
                                                                                httpPrixittransite1.setRequestHeader('Content-Type', 'application/json');
                                                                                httpPrixittransite1.send();
                                                                      
                                                                                

                                                                                const httpRequetteitra1 = new XMLHttpRequest();
                                                                        
                                                                                    httpRequetteitra1.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetras1}/${dbitra1}/${fnitra1}`, true);
                                                                                httpRequetteitra1.onload = () => {
                                                                                    const dattaitra1 = JSON.parse(httpRequetteitra1.responseText);
                                                                                    console.debug(`${typeof dattaitra1} - ${dattaitra1.attributes}`, console.memory);
                                                                                    if (Object.entries(dattaitra1).length >= 1) {
                                                                                        for (let key in Object.entries(dattaitra1)) {
                                                                                            
                                                                                            let opt = document.createElement('option');
                                                                                            opt.value = `${dattaitra1[key].siege_num}`;
                                                                                            opt.innerHTML = `${dattaitra1[key].siege_num}`;
                                                                                            document.querySelector('#psiegesitines2').add(opt);
                                                                                            
                                                                                        }
                                                                                        
                                                                                    } else {
                                                                                        document.querySelector('#psiegesitines2').options.length = 1;
                                                                                    }
                                                                                };
                                                                                httpRequetteitra1.setRequestHeader('Content-Type', 'application/json');
                                                                                httpRequetteitra1.send();
                                                                        };

                                                                       let progsieges2 = document.querySelector('#psiegesitines2');
                                                                        if (progsieges2 !== null) 
                                                                        {
                                                                            progsieges2.onchange = () => 
                                                                            {

                                                                               __venteFillTransitDepart('#transitedepargare3', document.querySelector('#gidtrans1').value);
                                                                                    const transselitine2 = document.querySelector('#idcheminsheur1')
                                                                                .options[document.querySelector('#idcheminsheur1').options.selectedIndex].value;
                                                                                var post_trans2 = transselitine2.split('/');
                                                                                var itinetras2 = post_trans2[0];
                                                                    
                                                                                let httpSieges2;
                                                                                httpSieges2 = new XMLHttpRequest();
                                                                                const sigs2 = document.querySelector('#psiegesitines2')
                                                                                .options[document.querySelector('#psiegesitines2').options.selectedIndex].value;

                                                                                httpSieges2.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetras2}/${sigs2}`, true);
                                                                                httpSieges2.onload = () => 
                                                                                {
                                                                                    const donsge2 = JSON.parse(httpSieges2.responseText);
                                                                                    if(donsge2 == '')
                                                                                    {
                                                                                        let httpSiegs2;
                                                                                        httpSiegs2 = new XMLHttpRequest();

                                                                                        httpSiegs2.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetras2}/${sigs2}`, true);
                                                                                        httpSiegs2.onload = () => 
                                                                                        {
                                                                                            const dong2 = JSON.parse(httpSiegs2.responseText);
                                                                                            document.querySelector('#mess').style.display = 'none';
                                                                                            if (Object.entries(dong2).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(dong2)) {
                                                                                                        document.querySelector('#idtampo2').value = `${dong2[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselect2').value = `${dong2[key].numsieg}`;
                                                                                                    }
                                                                                                }
                                                                                        };
                                                                                        httpSiegs2.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegs2.send();
                                                                                    }
                                                                                    else {
                                                                                        document.querySelector('#psiegesitines2').value = '';     
                                                                                        if (Object.entries(donsge2).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(donsge2)) {
                                                                                                document.querySelector('#idtampo2').value = `${donsge2[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselect2').value = `${donsge2[key].numsieg}`;
                                                                                            }

                                                                                        }
                                                                                        document.querySelector('#mess').style.display = 'block';
                                                                                        document.querySelector('#erreurMess').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                                };
                                                                                httpSieges2.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSieges2.send();

                                                                            };
                                                                        }
                                                                }   

                                                                //troisieme transite
                                                               let progchemin2 = document.querySelector('#idchemins2');
                                                                if (progchemin2 !== null) 
                                                                {
                                                                    progchemin2.onchange = () => 
                                                                    {
                                                                        document.querySelector('#idcheminsheur2').options.length = 1;
                                                                        document.querySelector('#psiegesitines3').options.length = 1;

                                                                        const prostranschemin42 = document.querySelector('#idchemins2')
                                                                        .options[document.querySelector('#idchemins2').options.selectedIndex].value;

                                                                        var post_typgare42 = prostranschemin42.split('-');
                                                                        var seltypgare42 = post_typgare42[0];
                                                                        var typgaresel41 = post_typgare42[1];

                                                                        // Jambe 4 : #quartier = arrivée finale (déjà chargé via arrsgare).
                                                                        // Ne pas reconstruire le select ici — ça vidait la sélection du client.
                                                                        var qMain4 = document.querySelector('#quartier');
                                                                        if (typgaresel41 && qMain4 && qMain4.options.length <= 1) {
                                                                            var httptypequart4 = new XMLHttpRequest();
                                                                            httptypequart4.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgaresel41}`, true);
                                                                            httptypequart4.onload = () => {
                                                                                var donqua4 = [];
                                                                                try { donqua4 = JSON.parse(httptypequart4.responseText) || []; } catch (e4) { donqua4 = []; }
                                                                                __venteFillQuartierSelect(donqua4);
                                                                                __venteShowMainQuartier();
                                                                            };
                                                                            httptypequart4.setRequestHeader('Content-Type', 'application/json');
                                                                            httptypequart4.send();
                                                                        } else if (qMain4) {
                                                                            __venteShowMainQuartier();
                                                                        }
                                                                        
                                                                        let httpSiegeschemin2;
                                                                        httpSiegeschemin2 = new XMLHttpRequest();
                                                                        
                                                                        var datedepart = document.querySelector('#date_depheure').value;
                                                                        var tfbs = document.querySelector('#tarifattrib').value;

                                                                        const prostranschemin2 = document.querySelector('#idchemins2')
                                                                        .options[document.querySelector('#idchemins2').options.selectedIndex].value;

                                                                        httpSiegeschemin2.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemintr/${prostranschemin2}/${datedepart}/${tfbs}`, true);
                                                                        httpSiegeschemin2.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschem2 = JSON.parse(httpSiegeschemin2.responseText);
                                                                                    __venteFillCheminHeures('#idcheminsheur2', dongtranschem2, 'tr4');
                                                                        };
                                                                        httpSiegeschemin2.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegeschemin2.send();

                                                                    };
                                                                      let prochemintra2 = document.querySelector('#idcheminsheur2');
                                                                    if (prochemintra2 !== null)
                                                                        __venteWireCheminHeur('idcheminsheur2', 'tr4'); if (false) prochemintra2.onchange = () => 
                                                                        {  
                                                                            
                                                                            document.querySelector('#psiegesitines3').options.length = 1;
                                                                            document.querySelector('#transitedepargare4').options.length = 1;

                                                                            const httpPrixittransite2 = new XMLHttpRequest();
                                                                                const transselitine2 = document.querySelector('#idcheminsheur2')
                                                                            .options[document.querySelector('#idcheminsheur2').options.selectedIndex].value;
                                                                                var post_trans2 = transselitine2.split('/');
                                                                            var itinetras2 = post_trans2[0];
                                                                            var dbitra2 = post_trans2[1];
                                                                            var fnitra2 = post_trans2[2];
                                                                            var lhertra2 = post_trans2[3];
                                                                            var prixtra2 = post_trans2[4];

                                                                                httpPrixittransite2.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetras2}`, true);
                                                                                httpPrixittransite2.onload = () => 
                                                                                {
                                                                                    const donprixitran2 = JSON.parse(httpPrixittransite2.responseText);
                                                                                    if (Object.entries(donprixitran2).length >= 1) {
                                                                                        for (let key in Object.entries(donprixitran2)) 
                                                                                        {
                                                                                            document.querySelector('#prix_axetransit2').value = `${prixtra2}`;
                                                                                            document.querySelector('#catetransit2').value = `${donprixitran2[key].categori}`;
                                                                                            document.querySelector('#gidtrans2').value =  `${donprixitran2[key].gareidentif}`;
                                                                                            document.querySelector('#nomitintrans3').value = `${donprixitran2[key].nom_ligne}`;
                                                                                            document.querySelector('#ligntrans3').value = `${donprixitran2[key].ident_ligne}`;
                                                                                        }
                                                                                        __venteFillTransitDepart('#transitedepargare4', document.querySelector('#gidtrans2').value);
                                                                                    }
                                                                                };
                                                                                httpPrixittransite2.setRequestHeader('Content-Type', 'application/json');
                                                                                httpPrixittransite2.send();
                                                                      
                                                                                

                                                                                const httpRequetteitra2 = new XMLHttpRequest();
                                                                        
                                                                                    httpRequetteitra2.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetras2}/${dbitra2}/${fnitra2}`, true);
                                                                                httpRequetteitra2.onload = () => {
                                                                                    const dattaitra2 = JSON.parse(httpRequetteitra2.responseText);
                                                                                    console.debug(`${typeof dattaitra2} - ${dattaitra2.attributes}`, console.memory);
                                                                                    if (Object.entries(dattaitra2).length >= 1) {
                                                                                        for (let key in Object.entries(dattaitra2)) {
                                                                                            
                                                                                            let opt = document.createElement('option');
                                                                                            opt.value = `${dattaitra2[key].siege_num}`;
                                                                                            opt.innerHTML = `${dattaitra2[key].siege_num}`;
                                                                                            document.querySelector('#psiegesitines3').add(opt);
                                                                                            
                                                                                        }
                                                                                        
                                                                                    } else {
                                                                                        document.querySelector('#psiegesitines3').options.length = 1;
                                                                                    }
                                                                                };
                                                                                httpRequetteitra2.setRequestHeader('Content-Type', 'application/json');
                                                                                httpRequetteitra2.send();
                                                                        };

                                                                       let progsieges3 = document.querySelector('#psiegesitines3');
                                                                        if (progsieges3 !== null) 
                                                                        {
                                                                            progsieges3.onchange = () => 
                                                                            {


                                                                               __venteFillTransitDepart('#transitedepargare4', document.querySelector('#gidtrans2').value);
                                                                                    const transselitine3 = document.querySelector('#idcheminsheur2')
                                                                                .options[document.querySelector('#idcheminsheur2').options.selectedIndex].value;
                                                                                var post_trans3 = transselitine3.split('/');
                                                                                var itinetras3 = post_trans3[0];
                                                                    
                                                                                let httpSieges3;
                                                                                httpSieges3 = new XMLHttpRequest();
                                                                                const sigs3 = document.querySelector('#psiegesitines3')
                                                                                .options[document.querySelector('#psiegesitines3').options.selectedIndex].value;

                                                                                httpSieges3.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetras3}/${sigs3}`, true);
                                                                                httpSieges3.onload = () => 
                                                                                {
                                                                                    const donsge3 = JSON.parse(httpSieges3.responseText);
                                                                                    if(donsge3 == '')
                                                                                    {
                                                                                        let httpSiegs3;
                                                                                        httpSiegs3 = new XMLHttpRequest();

                                                                                        httpSiegs3.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetras3}/${sigs3}`, true);
                                                                                        httpSiegs3.onload = () => 
                                                                                        {
                                                                                            const dong3 = JSON.parse(httpSiegs3.responseText);
                                                                                            document.querySelector('#mess').style.display = 'none';
                                                                                            if (Object.entries(dong3).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(dong3)) {
                                                                                                        document.querySelector('#idtampo3').value = `${dong3[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselect3').value = `${dong3[key].numsieg}`;
                                                                                                    }
                                                                                                }
                                                                                        };
                                                                                        httpSiegs3.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegs3.send();
                                                                                    }
                                                                                    else {
                                                                                        document.querySelector('#psiegesitines3').value = '';     
                                                                                        if (Object.entries(donsge3).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(donsge3)) {
                                                                                                document.querySelector('#idtampo3').value = `${donsge3[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselect3').value = `${donsge3[key].numsieg}`;
                                                                                            }

                                                                                        }
                                                                                        document.querySelector('#mess').style.display = 'block';
                                                                                        document.querySelector('#erreurMess').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                                };
                                                                                httpSieges3.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSieges3.send();

                                                                            };
                                                                        }
                                                                }            
                                                            }
                                                                
                                                        }
                                                    }
                                        }; // fin __venteApplyTransitLegs

                                // Ne pas ouvrir le transit au clic date (même catalogue vide) :
                                // le champ heure reste visible ; transit uniquement au choix d'une heure sans départ.

                                        let hrdepart = document.querySelector('#hdepart');
                                        if (hrdepart !== null) {
                                            hrdepart.onchange = () => 
                                            {
                                                document.querySelector('#psieges').options.length = 1;
                                                document.querySelector('#typegare').value = '';
                                                __venteHideProgSelect();
                                                const hOpt = document.querySelector('#hdepart').options[document.querySelector('#hdepart').options.selectedIndex];
                                                const sele = hOpt ? hOpt.value : '';
                                                const hasProgHour = hOpt && hOpt.getAttribute('data-has-programme') === '1';

                                                // Phase 1 : heure sans départ → correspondances (pas creedepart).
                                                if (sele && !hasProgHour) {
                                                    var messEl = document.querySelector('#mess');
                                                    var errEl = document.querySelector('#erreurMess');
                                                    if (window.__venteHasTransit) {
                                                        if (messEl) messEl.style.display = 'block';
                                                        if (errEl) errEl.innerHTML = 'Pas de départ à cette heure — correspondances proposées.';
                                                        __venteRequestTransitLegs(seltdep, arr, datedepart, sougid, true);
                                                    } else {
                                                        __venteShowDirectHourUi();
                                                        if (messEl) messEl.style.display = 'block';
                                                        if (errEl) errEl.innerHTML = 'Aucun départ ni correspondance pour cette heure.';
                                                    }
                                                    return;
                                                }

                                                // Heure avec départ : vente directe classique.
                                                __venteShowDirectHourUi();
                                                if (document.querySelector('#mess')) document.querySelector('#mess').style.display = 'none';
                                                const httpRequest = new XMLHttpRequest();

                                                    var post_lh = sele.split('/');
                                                    var sel = post_lh[0];
                                                    var lhsel = post_lh[1];

                                                    const dpt_date = document.querySelector('#date_depheure').value;
                                                    //var typgare = document.querySelector('#arrsgare').value;
                                                    var typgarepa = document.querySelector('#arrsgare').value;
                                                    var artypgarepa1 = typgarepa.split('/');
                                                    var typgare = artypgarepa1[0];
                                                    var typgare2 = artypgarepa1[1];
                                                    
                                                    const httptypegare = new XMLHttpRequest();
                                                    httptypegare.open('GET', window.location.origin + `${APP_ROOT}/programmes/gareprincipale/${typgare}/${lhsel}`, true);
                                                    httptypegare.onload = () => 
                                                    {
                                                        const dongare = JSON.parse(httptypegare.responseText);
                                                        if (Object.entries(dongare).length >= 1)
                                                        for (let key in Object.entries(dongare)) 
                                                        document.querySelector('#typegare').value = `${dongare[key].typestatutgare}`;
                                                    };
                                                    httptypegare.setRequestHeader('Content-Type', 'application/json');
                                                    httptypegare.send();

                                                


                                                httpRequest.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifprog/${seltdep}-${arr}/${dpt_date}/${sel}/${sougid || '0'}`, true);
                                                httpRequest.onload = () => 
                                                {
                                                    var typ_gare = document.querySelector('#typegare').value;    
                                                    const don = JSON.parse(httpRequest.responseText);
                                                        console.debug(`${typeof don} - ${don.attributes}`, console.memory);
                                                        // Multi-départs même heure : N>=1 → sélecteur / auto ; N=0 → creedepart
                                                        if (__venteHandleProgList(don, sel, dpt_date)) {
                                                            return;
                                                        }
                                                        if (don == '' || __venteProgListFromResponse(don).length === 0) 
                                                        {
                                                            if(typ_gare == 'Principale'){
                                                                
                                                                    let opt = document.createElement('option');
                                                                    opt.value = 1;
                                                                    opt.innerHTML = 1;
                                                                    document.querySelector('#psieges').add(opt);
                                                            
                                                                    departpsieges = document.querySelector('#psieges');
                                                                    if (departpsieges !== null) {
                                                                        departpsieges.onchange = () => 
                                                                        {
                                                                            let httpProg;
                                                                            httpProg = new XMLHttpRequest();
                                                                            httpProg.open('GET', window.location.origin + `${APP_ROOT}/programmes/creedepart/${seltdep}/${dpt_date}/${sel}/${lhsel}`, true);
                                                                            httpProg.onload = () => 
                                                                            {
                                                                                const dons = JSON.parse(httpProg.responseText);
                                                                                console.debug(`${typeof dons} - ${dons.attributes}`, console.memory);
                                                                                if (Object.entries(dons).length >= 1) {
                                                                                    for (let key in Object.entries(dons)) {
                                                                                        document.querySelector('#program').value = `${dons[key].code_progr}`;
                                                                                        document.querySelector('#tarifattrib').value = `${dons[key].typetarif}`;
                                                                                        document.querySelector('#cate').value = `${dons[key].categorie}`;
                                                                                        document.querySelector('#depligne').value = `${dons[key].gareidentif}`;
                                                                                        document.querySelector('#lign').value = `${dons[key].ident_ligne}`;
                                                                                        document.querySelector('#nomitin').value = `${dons[key].nom_ligne}`;
                                                                                        document.querySelector('#prix_axe').value = `${dons[key].prix}`;
                                                                                    }
                                                                                        let httpSiege;
                                                                                        httpSiege = new XMLHttpRequest();
                                                                                        const sig = document.querySelector('#psieges')
                                                                                        .options[document.querySelector('#psieges').options.selectedIndex].value;
                                                                                        const pro = document.querySelector('#program').value;
                                                                                        httpSiege.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${pro}/${sig}`, true);
                                                                                        httpSiege.onload = () => 
                                                                                        {
                                                                                            const donsg = JSON.parse(httpSiege.responseText);
                                                                                            console.debug(`${typeof donsg} - ${donsg.attributes}`, console.memory);
                                                                                            if(donsg == '')
                                                                                            {
                                                                                                let httpSieg;
                                                                                                httpSieg = new XMLHttpRequest();
                    
                                                                                                httpSieg.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${pro}/${sig}`, true);
                                                                                                httpSieg.onload = () => 
                                                                                                {
                                                                                                    const donsg2 = JSON.parse(httpSieg.responseText);
                                                                                                    document.querySelector('#mess').style.display = 'none';
                                                                                                    if (Object.entries(donsg2).length >= 1)
                                                                                                        {
                                                                                                            for (let key in Object.entries(donsg2)) {
                                                                                                                document.querySelector('#idtampo').value = `${donsg2[key].idtamp}`;                    
                                                                                                                document.querySelector('#siegselect').value = `${donsg2[key].numsieg}`;
                                                                                                            }
                                                                                                        }
                                                                                                };
                                                                                                httpSieg.setRequestHeader('Content-Type', 'application/json');
                                                                                                httpSieg.send();
                                                                                            }
                                                                                            else 
                                                                                            {
                                                                                                document.querySelector('#psieges').value = ''; 
                                                                                                if (Object.entries(donsg).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(donsg)) 
                                                                                                    {
                                                                                                        document.querySelector('#idtampo').value = `${donsg[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselect').value = `${donsg[key].numsieg}`;
                                                                                                    }
        
                                                                                                }
                                                                                                document.querySelector('#mess').style.display = 'block';
                                                                                                document.querySelector('#erreurMess').innerHTML = `Siege déjà utilisé.`;                   
                                                                                            }
                                                                                        };
                                                                                        httpSiege.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiege.send();
                    
                                                                                   
                                                                                }
                                                                            };
                                                                            httpProg.setRequestHeader('Content-Type', 'application/json');
                                                                            httpProg.send();
        
                                                                            
                                                                        
                                                                        };
        
                                                                        
                                                                    }
                                                            }else{
                                                                let opt = document.createElement('option');
                                                                opt.value = '';                                                             
                                                            }
                                                            
                                                            
                                                        }  
                                                        
                                                    };
                                                    httpRequest.setRequestHeader('Content-Type', 'application/json');
                                                    httpRequest.send();
                                                     
                                                };
                                                
                                        
                                            }
                                };
                                httpRequetes.setRequestHeader('Content-Type', 'application/json');
                                httpRequetes.send();
                        }
                        else
                        {
                            document.querySelector('#date_depheure').style.color = "#FF0000";
                            document.querySelector('#date_depheure').style.border = "2px solid #FF0000";
                            document.querySelector('#smsdt').style.display = 'block';
                            document.querySelector('#erreurSmsdt').innerHTML = `Date non valide.`;
                        }
                    

                };
                
            }
            let progsieges = document.querySelector('#psieges');
            if (progsieges !== null) {
                progsieges.onchange = () => 
                {
                    let httpSieges;
                    httpSieges = new XMLHttpRequest();
                    const sigs = document.querySelector('#psieges')
                    .options[document.querySelector('#psieges').options.selectedIndex].value;
                    const pros = document.querySelector('#program').value;

                    httpSieges.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${pros}/${sigs}`, true);
                    httpSieges.onload = () => 
                    {
                        const donsge = JSON.parse(httpSieges.responseText);
                        console.debug(`${typeof donsge} - ${donsge.attributes}`, console.memory);
                        if(donsge == '')
                        {
                            let httpSiegs;
                            httpSiegs = new XMLHttpRequest();

                            httpSiegs.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${pros}/${sigs}`, true);
                            httpSiegs.onload = () => 
                            {
                                const dong = JSON.parse(httpSiegs.responseText);
                                document.querySelector('#mess').style.display = 'none';
                                if (Object.entries(dong).length >= 1)
                                    {
                                        for (let key in Object.entries(dong)) {
                                            document.querySelector('#idtampo').value = `${dong[key].idtamp}`;                    
                                            document.querySelector('#siegselect').value = `${dong[key].numsieg}`;
                                        }
                                    }
                            };
                            httpSiegs.setRequestHeader('Content-Type', 'application/json');
                            httpSiegs.send();
                        }
                        else {
                            document.querySelector('#psieges').value = '';     
                            if (Object.entries(donsge).length >= 1)
                            {
                                for (let key in Object.entries(donsge)) {
                                    document.querySelector('#idtampo').value = `${donsge[key].idtamp}`;                    
                                    document.querySelector('#siegselect').value = `${donsge[key].numsieg}`;
                                }

                            }
                            document.querySelector('#mess').style.display = 'block';
                            document.querySelector('#erreurMess').innerHTML = `Siege déjà utilisé.`;                                                                   }
                    };
                    httpSieges.setRequestHeader('Content-Type', 'application/json');
                    httpSieges.send();

                
                };
            }
           
            let infdoc = document.querySelector('#cltype');
        if (infdoc !== null)
            infdoc.onchange = () => 
            {
                let httpDocs;
                if (window.XMLHttpRequest) {
                    httpDocs = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpDocs = new ActiveXObject("Microsoft.XMLHTTP");
                }
                var docum = document.querySelector('#cltype').value;
                
                if (docum == 'Adulte') {
                    document.querySelector('#motif').style.display = 'none';
                    document.querySelector('#motifrefus').style.display = 'none';
                    document.querySelector('#doc').style.display = 'none';
                    document.querySelector('#docdelivre').style.display = 'none';
                    document.querySelector('#datedocdel').style.display = 'none';
                    document.querySelector('#num_doc').style.display = 'none';
                    document.querySelector('#rclient').style.display = 'block';
                    document.querySelector('#prnclient').style.display = 'block';
                    document.querySelector('#cnib').style.display = 'block';
                    document.querySelector('#date_cnib').style.display = 'block';
                    document.querySelector('#lieudelivre').style.display = 'block';
                    console.debug(`${docum}`, console.memory);

                } 
                    if (docum == 'Etudiant') {
                        document.querySelector('#doc').style.display = 'block';
                        document.querySelector('#num_doc').style.display = 'block';
                        document.querySelector('#docdelivre').style.display = 'block';
                        document.querySelector('#datedocdel').style.display = 'block';
                        document.querySelector('#rclient').style.display = 'block';
                        document.querySelector('#prnclient').style.display = 'block';
                        document.querySelector('#cnib').style.display = 'none';
                        document.querySelector('#date_cnib').style.display = 'none';
                        document.querySelector('#lieudelivre').style.display = 'none';
                        console.debug(`${docum}`, console.memory);

                    } 
                    if (docum == 'Elève') {
                        document.querySelector('#doc').style.display = 'block';
                        document.querySelector('#num_doc').style.display = 'block';
                        document.querySelector('#docdelivre').style.display = 'block';
                        document.querySelector('#datedocdel').style.display = 'block';
                        document.querySelector('#rclient').style.display = 'block';
                        document.querySelector('#prnclient').style.display = 'block';
                        document.querySelector('#cnib').style.display = 'none';
                        document.querySelector('#date_cnib').style.display = 'none';
                        document.querySelector('#lieudelivre').style.display = 'none';
                        console.debug(`${docum}`, console.memory);

                    } 
                    if (docum == 'Enfant') {
                        document.querySelector('#doc').style.display = 'block';
                        document.querySelector('#num_doc').style.display = 'block';
                        document.querySelector('#docdelivre').style.display = 'block';
                        document.querySelector('#datedocdel').style.display = 'block';
                        document.querySelector('#rclient').style.display = 'block';
                        document.querySelector('#prnclient').style.display = 'block';
                        document.querySelector('#cnib').style.display = 'none';
                        document.querySelector('#date_cnib').style.display = 'none';
                        document.querySelector('#lieudelivre').style.display = 'none';
                        console.debug(`${docum}`, console.memory);

                    } 
                    if (docum == 'Autres') {
                        document.querySelector('#motif').style.display = 'block';
                        document.querySelector('#motifrefus').style.display = 'block';
                        document.querySelector('#rclient').style.display = 'block';
                        document.querySelector('#prnclient').style.display = 'block';
                        document.querySelector('#cnib').style.display = 'none';
                        document.querySelector('#date_cnib').style.display = 'none';
                        document.querySelector('#lieudelivre').style.display = 'none';
                        document.querySelector('#doc').style.display = 'none';
                        document.querySelector('#num_doc').style.display = 'none';
                        document.querySelector('#docdelivre').style.display = 'none';
                        document.querySelector('#datedocdel').style.display = 'none';
                        console.debug(`${docum}`, console.memory);

                    } 
                    
            };

            
        //recherche d'information du client depart principal
        let inf = document.querySelector('#rnclient_contact');
        if (inf !== null && inf.dataset.guarded !== '1') {
            inf.dataset.guarded = '1';
            inf.addEventListener('keyup', () => {
                const rawPhone = inf.value.trim();
                const digits = AppRequestGuard.phoneDigits(rawPhone);
                if (digits.length < 7) {
                    return;
                }
                AppRequestGuard.debounce('verifinfos', () => {
                    AppRequestGuard.getJson(
                        window.location.origin + `${APP_ROOT}/programmes/verifinfos/${encodeURIComponent(rawPhone)}`,
                        'verifinfos',
                        (httpInfos) => {
                            let infos = null;
                            try {
                                infos = JSON.parse(httpInfos.responseText);
                            } catch (err) {
                                return;
                            }
                            if (infos == null || Object.keys(infos).length < 1) {
                                document.querySelector('#pascompagnie').value = '';
                                return;
                            }
                            if (AppRequestGuard.phonesMatch(infos.contact_client, rawPhone)) {
                                document.querySelector('#rclient').value = `${infos.nom_client || ''}`;
                                document.querySelector('#prnclient').value = `${infos.prenom_client || ''}`;
                                document.querySelector('#cnib').value = `${infos.num_CNIB || ''}`;
                                document.querySelector('#date_cnib').value = `${infos.date_delivre || ''}`;
                                document.querySelector('#lieudelivre').value = `${infos.lieu_delivre || ''}`;
                                document.querySelector('#pascompagnie').value = `${infos.id_client || ''}`;
                                document.querySelector('#rclientcp').value = `${infos.nom_client || ''}`;
                                document.querySelector('#prnclientcp').value = `${infos.prenom_client || ''}`;
                                document.querySelector('#cnibcp').value = `${infos.num_CNIB || ''}`;
                                document.querySelector('#date_cnibcp').value = `${infos.date_delivre || ''}`;
                                document.querySelector('#lieudelivrecp').value = `${infos.lieu_delivre || ''}`;
                            } else {
                                document.querySelector('#pascompagnie').value = '';
                            }
                        }
                    );
                }, 400);
            });
        }
            
            let butonclic = document.querySelector('#idreset');
            if (butonclic !== null) {
                butonclic.onclick = () => 
                {
                    let httpSiegeselect;
                    httpSiegeselect = new XMLHttpRequest();
                    const siegselect = document.querySelector('#siegselect').value;
                    //const pros = document.querySelector('#program').value;
                    const idtap = document.querySelector('#idtampo').value;
                    httpSiegeselect.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtap}/${siegselect}`, true);
                    httpSiegeselect.onload = () => 
                    {
                        const donselect= JSON.parse(httpSiegeselect.responseText);
                        console.debug(`${typeof donselect} - ${donselect.attributes}`, console.memory);
                        document.querySelector('#mess').style.display = 'none';
                        
                    };
                    httpSiegeselect.setRequestHeader('Content-Type', 'application/json');
                    httpSiegeselect.send();

                
                };
            }
                
                e.onclick = function () {   
                    __venteSetTaTitle('VENTE DE TICKET');
                    let taForm = document.querySelector('#taForm');
                    
                    taForm.setAttribute('action', `${APP_ROOT}/Programmes/addpassager/${e.dataset.cle_compagnie}`);
                    AppRequestGuard.ensureNonce('#taForm', 'sale_nonce');
                    AppRequestGuard.guardForm('#taForm');
                }

                var taFormEl = document.querySelector('#taForm');
                if (taFormEl && !taFormEl.dataset.salePrepared) {
                    taFormEl.dataset.salePrepared = '1';
                    taFormEl.addEventListener('submit', function () {
                        AppRequestGuard.ensureNonce('#taForm', 'sale_nonce');
                        AppRequestGuard.syncClientMirror([
                            ['#rclient', '#rclientcp'],
                            ['#prnclient', '#prnclientcp'],
                            ['#cnib', '#cnibcp'],
                            ['#date_cnib', '#date_cnibcp'],
                            ['#lieudelivre', '#lieudelivrecp']
                        ]);
                    });
                }

                AppRequestGuard.guardForm('#taForm');
                AppRequestGuard.ensureNonce('#taForm', 'sale_nonce');
    })

});