document.addEventListener('DOMContentLoaded', () => {

    window.__confHasTransit = false;
    window.__confLastHeuresVente = [];
    window.__confApplyTransitLegs = null;

    function __confFillHeuresVente(heures) {
        var hSel = document.querySelector('#heured');
        if (!hSel) return;
        hSel.options.length = 1;
        var list = Array.isArray(heures) ? heures : [];
        for (var i = 0; i < list.length; i++) {
            var hr = list[i];
            if (!hr || hr.id_ligneheure == null || hr.id_ligneheure === '') continue;
            var opt = document.createElement('option');
            var hasProg = !!(hr.has_programme === true || hr.has_programme === 1 || hr.has_programme === '1');
            // Avec programme : format historique confirm (code_progr/tarif/id_lh) pour siegdispo.
            // Sans programme : id_lh/heure (transit au choix).
            if (hasProg && hr.code_progr) {
                opt.value = String(hr.code_progr) + '/1/' + String(hr.id_ligneheure);
            } else {
                opt.value = String(hr.id_ligneheure) + '/' + String(hr.heure || '');
            }
            opt.setAttribute('data-has-programme', hasProg ? '1' : '0');
            opt.innerHTML = hr.heure || '';
            hSel.add(opt);
        }
    }

    function __confShowDirectHourUi() {
        var hideIds = [
            'iddeptranscf1','transitedepargarecf1','iddeptranscf2','transitedepargarecf2',
            'iddeptranscf3','transitedepargarecf3','iddeptranscf4','transitedepargarecf4',
            'arritincf1','arritincf2','arritincf3','heureitincf','heureitincf1','heureitincf2',
            'hdepartitinecf','hdepartitinecf2','lignesitinerairecf','lignecf1',
            'siegitinecf','siegitinecf1','siegitinecf2','psiegesitinescf','psiegesitinescf1','psiegesitinescf2',
            'quartiercf1','quartiercf2','quartiercf3','idquartcf1','idquartcf2','idquartcf3',
            'idcheminscf','idcheminscf1','idcheminscf2','idcheminsheurcf','idcheminsheurcf1','idcheminsheurcf2'
        ];
        for (var i = 0; i < hideIds.length; i++) {
            var el = document.getElementById(hideIds[i]);
            if (el) el.style.display = 'none';
        }
        var tran = document.querySelector('#trancf');
        if (tran) tran.style.display = 'none';
        if (typeof window.__confSetMainEscaleVisible === 'function') window.__confSetMainEscaleVisible(true);
        ['heured','depsieg'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.style.display = 'block';
        });
        var nbr = document.querySelector('#nbrtranscf');
        if (nbr) nbr.value = '';
    }

    function __confSetMainEscaleVisible(visible) {
        var wrap = document.querySelector('#escale_dest_wrap_cf');
        if (!wrap) return;
        if (!visible) {
            var ck = document.querySelector('#escale_vente_check_cf');
            if (ck) ck.checked = false;
            ['#id_escale_ventecf','#code_gadest_ventecf','#nom_dest_ventecf'].forEach(function (s) {
                var el = document.querySelector(s);
                if (el) el.value = '';
            });
            var fields = document.querySelector('#escale_dest_fields_cf');
            if (fields) fields.style.display = 'none';
            wrap.style.display = 'none';
        } else {
            wrap.style.display = '';
        }
    }
    window.__confSetMainEscaleVisible = __confSetMainEscaleVisible;

    function __confDepSousGare() {
        var dep = document.querySelector('#confirm-0 #depargare') || document.querySelector('#confForm #depargare') || document.querySelector('#depargare');
        if (!dep || !dep.value) return '0';
        var parts = String(dep.value).split('/');
        return parts[1] || '0';
    }

    function __confEnsureCheminSelector() {
        var existing = document.getElementById('selchemin_box_cf');
        if (existing) return existing;
        var box = document.createElement('div');
        box.className = 'form-group col-sm-12';
        box.id = 'selchemin_box_cf';
        box.style.display = 'none';
        box.innerHTML = ''
            + '<label id="selchemin_label_cf">Itinéraire de correspondance</label>'
            + '<select class="form-control form-control-sm" id="selchemin_transit_cf">'
            + '<option value="">Choisissez l\'itinéraire</option>'
            + '</select>'
            + '<small class="form-text text-muted" id="selchemin_hint_cf"></small>';
        var anchor = document.getElementById('heured')
            || document.getElementById('hdepartitinecf')
            || document.getElementById('nbrtranscf');
        if (anchor && anchor.parentNode && anchor.parentNode.parentNode) {
            anchor.parentNode.parentNode.insertBefore(box, anchor.parentNode);
        } else if (anchor && anchor.parentNode) {
            anchor.parentNode.insertBefore(box, anchor);
        } else {
            document.body.appendChild(box);
        }
        return box;
    }

    function __confHideCheminSelector() {
        var box = document.getElementById('selchemin_box_cf');
        var sel = document.getElementById('selchemin_transit_cf');
        var hint = document.getElementById('selchemin_hint_cf');
        if (box) box.style.display = 'none';
        if (sel) { sel.options.length = 1; sel.value = ''; sel.onchange = null; }
        if (hint) hint.textContent = '';
    }

    function __confFormatAttenteLabel(chemin) {
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


    function __confNormalizeEtapes(etapes) {
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
    function __confSetCheminLigneOption(selectSel, code, nom) {
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
        if (typeof sel.onchange === 'function') {
            sel.onchange();
        }
    }

    function __confEnsureLigne1LockedInput() {
        var el = document.getElementById('lignesitinerairecf');
        if (!el) return null;
        if (el.tagName === 'INPUT') {
            el.disabled = true;
            el.setAttribute('disabled', 'disabled');
            el.readOnly = true;
            return el;
        }
        var inp = document.createElement('input');
        inp.type = 'text';
        inp.id = 'lignesitinerairecf';
        inp.name = el.getAttribute('name') || 'lignesitinerairescf';
        inp.className = el.className || 'form-control form-control-sm';
        inp.disabled = true;
        inp.setAttribute('disabled', 'disabled');
        inp.readOnly = true;
        if (el.parentNode) el.parentNode.replaceChild(inp, el);
        return inp;
    }

    function __confFillLigne1Locked(etape0, onPick) {
        if (!etape0) return;
        var code = etape0.code_itineraires || '';
        var nom = etape0.nom_itineraires || code;
        var el = __confEnsureLigne1LockedInput();
        if (el) el.value = nom;
        var itc = document.querySelector('#itinecodecf');
        var ltn = document.querySelector('#lignetinerairecf');
        if (itc) itc.value = code;
        if (ltn) ltn.value = nom;
        if (typeof onPick === 'function') onPick(code, nom);
    }


    function __confResetTransitFieldsBeforeApply() {
        [
            'arritin1cf','idcheminscf','heureitin1cf','idcheminsheurcf','siegitine1cf','psiegesitines1cf',
            'arritin2cf','idcheminscf1','heureitin2cf','idcheminsheurcf1','siegitine2cf','psiegesitines2cf',
            'arritin3cf','idcheminscf2','heureitin3cf','idcheminsheurcf','nbrtranscf',
            'iddeptranscf1','transitedepargarecf1','iddeptranscf2','transitedepargarecf2',
            'iddeptranscf3','transitedepargarecf3','iddeptranscf4','transitedepargarecf4',
            'trancf','heureitincf','hdepartitinecf','siegitinecf','psiegesitinescf'
        ].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });
        [
            '#idcheminscf','#idcheminscf1','#idcheminscf2',
            '#idcheminsheurcf','#idcheminsheurcf1','#hdepartitinecf',
            '#psiegesitinescf','#psiegesitines1cf','#psiegesitines2cf'
        ].forEach(function (s) {
            var el = document.querySelector(s);
            if (el && el.options) { el.options.length = 1; el.value = ''; el.onchange = null; }
        });
        ['#transitedepargarecf1','#transitedepargarecf2','#transitedepargarecf3','#transitedepargarecf4'].forEach(function (s) {
            var el = document.querySelector(s);
            if (el && el.options) el.options.length = 0;
        });
        ['#itinecodecf','#itinecodescf','#lignetinerairecf','#nbrtranscf','#idcompgcf','#idcompgcf1','#idcompgcf2','#idcompgcf3'].forEach(function (s) {
            var el = document.querySelector(s);
            if (el) el.value = '';
        });
    }

    function __confShowCheminSelector(chemins, onPick) {
        __confEnsureCheminSelector();
        var box = document.getElementById('selchemin_box_cf');
        var sel = document.getElementById('selchemin_transit_cf');
        var hint = document.getElementById('selchemin_hint_cf');
        if (!box || !sel) {
            var et0 = chemins && chemins[0] ? __confNormalizeEtapes(chemins[0].etapes) : [];
            if (typeof window.__confApplyTransitLegs === 'function') window.__confApplyTransitLegs(et0);
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
            if (hint) hint.textContent = __confFormatAttenteLabel(ch);
            var etapes = __confNormalizeEtapes(ch && ch.etapes);
            if (typeof window.__confApplyTransitLegs === 'function') window.__confApplyTransitLegs(etapes);
            else if (typeof onPick === 'function') onPick(etapes);
        };
        sel.onchange = function () {
            var idx = parseInt(sel.value, 10);
            if (isNaN(idx) || !chemins[idx]) {
                if (hint) hint.textContent = '';
                if (typeof window.__confApplyTransitLegs === 'function') window.__confApplyTransitLegs([]);
                else if (typeof onPick === 'function') onPick([]);
                return;
            }
            applyIdx(idx);
        };
        sel.selectedIndex = 1;
        applyIdx(0);
    }

    function __confRequestTransitLegs(axe, datedepart, sougid, force, onDone) {
        var sg = (sougid != null && sougid !== '') ? sougid : '0';
        var forceFlag = force ? '1' : '0';
        var done = function (etapes) {
            if (typeof onDone === 'function') onDone(etapes);
            else if (typeof window.__confApplyTransitLegs === 'function') window.__confApplyTransitLegs(etapes);
        };
        var httpRequestitinecf = new XMLHttpRequest();
        httpRequestitinecf.open(
            'GET',
            window.location.origin + `${APP_ROOT}/programmes/verifchemins/`
                + encodeURIComponent(axe) + '/'
                + encodeURIComponent(datedepart) + '/'
                + encodeURIComponent(sg) + '/'
                + forceFlag,
            true
        );
        httpRequestitinecf.onload = function () {
            var payload = null;
            try { payload = JSON.parse(httpRequestitinecf.responseText); } catch (e) { payload = null; }
            if (Array.isArray(payload)) { __confHideCheminSelector(); done(payload); return; }
            if (!payload || typeof payload !== 'object') { __confHideCheminSelector(); done([]); return; }
            if (payload.mode === 'direct' || payload.mode === 'none') { __confHideCheminSelector(); done([]); return; }
            var chemins = Array.isArray(payload.chemins) ? payload.chemins : [];
            if (chemins.length > 1) { __confShowCheminSelector(chemins, done); return; }
            __confHideCheminSelector();
            if (chemins.length === 1 && chemins[0].etapes) { done(chemins[0].etapes); return; }
            if (payload.etapes && (Array.isArray(payload.etapes) ? payload.etapes.length : Object.keys(payload.etapes).length)) {
                done(payload.etapes); return;
            }
            done([]);
        };
        httpRequestitinecf.setRequestHeader('Content-Type', 'application/json');
        httpRequestitinecf.send();
    }

    /** Remplit départ correspondance confirm (sans option vide sélectionnée). */
    function __confFillTransitDepart(selectSel, gareIdentif) {
        var sel = document.querySelector(selectSel);
        if (!sel) return;
        sel.options.length = 0;
        if (gareIdentif == null || gareIdentif === '') return;
        var http = new XMLHttpRequest();
        http.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/` + encodeURIComponent(gareIdentif), true);
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


    var __CONF_TRANSIT_MARGE_MIN = 30;

    function __confHeureToMinutes(h) {
        if (h == null || h === '') return null;
        var parts = String(h).trim().split(/[:hH]/);
        if (!parts || !parts.length) return null;
        var hh = parseInt(parts[0], 10);
        if (isNaN(hh)) return null;
        var mm = (parts[1] != null && parts[1] !== '') ? parseInt(parts[1], 10) : 0;
        if (isNaN(mm)) mm = 0;
        return (hh * 60) + mm;
    }

    function __confRowIsAfterPrev(row, prevDate, prevMinutes, marge) {
        if (prevMinutes == null || !prevDate) return true;
        var rd = row && row.date_progr ? String(row.date_progr).slice(0, 10) : '';
        var rm = __confHeureToMinutes(row && row.heure);
        if (!rd || rm == null) return false;
        if (rd > prevDate) return true;
        if (rd < prevDate) return false;
        return rm >= (prevMinutes + (marge != null ? marge : __CONF_TRANSIT_MARGE_MIN));
    }

    /** Remplit un select heures correspondance confirm, filtré vs jambe précédente. */
    function __confAppendFilteredCheminOptions(selectId, rowsObj, prevDate, prevHeure) {
        var sel = document.querySelector(selectId);
        if (!sel) return;
        sel.options.length = 1;
        var list = [];
        if (Array.isArray(rowsObj)) list = rowsObj;
        else if (rowsObj && typeof rowsObj === 'object') {
            Object.keys(rowsObj).forEach(function (k) { list.push(rowsObj[k]); });
        }
        var pDate = prevDate ? String(prevDate).slice(0, 10) : '';
        var pMin = __confHeureToMinutes(prevHeure);
        list = list.filter(function (row) {
            return row && row.code_progr != null && __confRowIsAfterPrev(row, pDate, pMin, __CONF_TRANSIT_MARGE_MIN);
        });
        list.sort(function (a, b) {
            var da = String(a.date_progr || '').slice(0, 10);
            var db = String(b.date_progr || '').slice(0, 10);
            if (da < db) return -1;
            if (da > db) return 1;
            return (__confHeureToMinutes(a.heure) || 0) - (__confHeureToMinutes(b.heure) || 0);
        });
        for (var i = 0; i < list.length; i++) {
            var row = list[i];
            var opt = document.createElement('option');
            opt.value = `${row.code_progr}/${row.intervalle1}/${row.intervalle2}/${row.id_ligneheure}/${row.prix}`;
            opt.setAttribute('data-heure', row.heure || '');
            opt.setAttribute('data-date-progr', row.date_progr ? String(row.date_progr).slice(0, 10) : '');
            opt.innerHTML = `${row.heure}/${row.date_progr}`;
            sel.add(opt);
        }
    }

    function __confPrevFromLeg1() {
        var d = document.querySelector('#dateprtranscf');
        var h = document.querySelector('#hertranscf');
        var date = (d && d.value) ? String(d.value).slice(0, 10) : (document.querySelector('#actuel') ? document.querySelector('#actuel').value : '');
        var heure = (h && h.value) ? String(h.value) : '';
        if (!heure) {
            var hs = document.querySelector('#heured');
            if (hs && hs.selectedIndex > 0) {
                var parts = String(hs.options[hs.selectedIndex].value || '').split('/');
                // verifheureitine format id/heure OR code/tarif/id
                if (parts.length >= 2 && parts[1].indexOf(':') >= 0) heure = parts[1];
                else if (hs.options[hs.selectedIndex].getAttribute('data-heure')) {
                    heure = hs.options[hs.selectedIndex].getAttribute('data-heure');
                }
            }
        }
        return { date: date, heure: heure };
    }

    function __confPrevFromSelect(selectId) {
        var hs = document.querySelector(selectId);
        if (!hs || hs.selectedIndex < 1) return { date: '', heure: '' };
        var opt = hs.options[hs.selectedIndex];
        return {
            date: opt.getAttribute('data-date-progr') || '',
            heure: opt.getAttribute('data-heure') || ''
        };
    }

    function __confSetVal(id, val) {
        var el = document.querySelector(id);
        if (el) el.value = val == null ? '' : String(val);
    }

    function __confApplyTransit1Fields(p) {
        if (!p) return;
        __confSetVal('#programtranscf', p.code_progr);
        var tf = (p.typetarif != null && String(p.typetarif).trim() !== '') ? p.typetarif : '1';
        __confSetVal('#tarifattribcf', tf);
        __confSetVal('#dateprtranscf', p.date_progr);
        __confSetVal('#deplignetranscf', p.gareidentif);
        __confSetVal('#intertranscf1', p.intervalle1);
        __confSetVal('#intertranscf2', p.intervalle2);
        __confSetVal('#ligntranscf', p.ident_ligne);
        __confSetVal('#nomitintranscf', p.nom_ligne);
        __confSetVal('#hertranscf', p.heure);
        __confSetVal('#catetranscf', p.categori);
        // Align programme confirm (flux direct) pour cohérence POST.
        __confSetVal('#programconf', p.code_progr);
        __confSetVal('#dateconfirme', p.date_progr);
        __confSetVal('#confheure', p.heure);
        __confSetVal('#directid', p.nom_ligne);
        __confSetVal('#caissepvend_', p.intervalle1);
        __confSetVal('#caissedpvend_', p.intervalle2);
        __confSetVal('#gareid_dep', p.gareidentif || p.gaexp_lg || '');
        __confSetVal('#lignehconf', p.id_ligneheure || '');
        if (p.prix != null && String(p.prix).trim() !== '') {
            __confSetVal('#prix_axetranscf', p.prix);
        }
        if (p.gareidentif) {
            __confFillTransitDepart('#transitedepargarecf1', p.gareidentif);
        }
        ['#hdepartitinecf','#idcheminsheurcf','#idcheminsheurcf1'].forEach(function (s) {
            var el = document.querySelector(s); if (el) el.options.length = 1;
        });
    }

    function __confProgListFromResponse(don) {
        if (!don || don === '') return [];
        if (Array.isArray(don)) return don.filter(Boolean);
        if (typeof don === 'object') {
            var keys = Object.keys(don);
            var out = [];
            for (var i = 0; i < keys.length; i++) {
                var k = keys[i];
                if (don[k] && typeof don[k] === 'object' && don[k].code_progr != null) {
                    out.push(don[k]);
                }
            }
            return out;
        }
        return [];
    }

    /** Remplit #depsieg via siegdisponible (sans encode — comme vente guichet). */
    function __confFillDepsiegFromProg() {
        var dSel = document.querySelector('#depsieg');
        if (dSel) dSel.options.length = 1;
        var cd = document.querySelector('#programtranscf') ? document.querySelector('#programtranscf').value : '';
        var dbit = document.querySelector('#intertranscf1') ? document.querySelector('#intertranscf1').value : '';
        var fnit = document.querySelector('#intertranscf2') ? document.querySelector('#intertranscf2').value : '';
        var lgit = document.querySelector('#nomitintranscf') ? document.querySelector('#nomitintranscf').value : '';
        var timit = document.querySelector('#hertranscf') ? document.querySelector('#hertranscf').value : '';
        var dProg = document.querySelector('#dateprtranscf') ? document.querySelector('#dateprtranscf').value : '';
        if (!cd || !dProg || !lgit || !timit || dbit === '' || fnit === '') return;
        var httpSieg = new XMLHttpRequest();
        httpSieg.open(
            'GET',
            window.location.origin + `${APP_ROOT}/programmes/siegdisponible/`
                + cd + '/' + dProg + '/' + lgit + '/' + timit + '/' + dbit + '/' + fnit,
            true
        );
        httpSieg.onload = function () {
            try {
                var rows = JSON.parse(httpSieg.responseText);
                if (dSel) dSel.options.length = 1;
                if (rows && Object.entries(rows).length >= 1) {
                    for (var key2 in Object.entries(rows)) {
                        var opt = document.createElement('option');
                        opt.value = `${rows[key2].siege_num}`;
                        opt.innerHTML = `${rows[key2].siege_num}`;
                        if (dSel) dSel.add(opt);
                    }
                }
            } catch (e2) {
                if (dSel) dSel.options.length = 1;
            }
        };
        httpSieg.setRequestHeader('Content-Type', 'application/json');
        httpSieg.send();
    }

    function __confLoadSiegesTransit1(idLigneheure, dptDate) {
        var tfEl = document.querySelector('#tarifattribcf');
        var tfbs = tfEl && String(tfEl.value || '').trim() !== '' ? String(tfEl.value).trim() : '1';
        if (tfEl && String(tfEl.value || '').trim() === '') tfEl.value = tfbs;
        if (idLigneheure) {
            var httpPrix = new XMLHttpRequest();
            httpPrix.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifpriprg/` + idLigneheure + '/' + tfbs, true);
            httpPrix.onload = function () {
                try {
                    var donprix = JSON.parse(httpPrix.responseText);
                    if (donprix && Object.entries(donprix).length >= 1) {
                        for (var key in Object.entries(donprix)) {
                            __confSetVal('#prix_axetranscf', donprix[key].prix);
                        }
                    }
                } catch (e) {}
            };
            httpPrix.setRequestHeader('Content-Type', 'application/json');
            httpPrix.send();
        }
        __confFillDepsiegFromProg();
    }

    /** Charge meta programme par code_progr (fiable, sans filtre sous-gare). */
    function __confLoadProgByCode(codeProgr, idLh, fallbackGare, done) {
        if (!codeProgr) {
            if (typeof done === 'function') done(false);
            return;
        }
        var http = new XMLHttpRequest();
        http.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/` + codeProgr, true);
        http.onload = function () {
            var don = null;
            try { don = JSON.parse(http.responseText); } catch (e) { don = null; }
            var list = __confProgListFromResponse(don);
            if (list.length < 1) {
                if (typeof done === 'function') done(false);
                return;
            }
            __confApplyTransit1Fields(list[0]);
            if (!list[0].gareidentif && fallbackGare) {
                __confFillTransitDepart('#transitedepargarecf1', fallbackGare);
            }
            __confLoadSiegesTransit1(idLh || list[0].id_ligneheure, document.querySelector('#actuel') ? document.querySelector('#actuel').value : '');
            if (typeof done === 'function') done(true);
        };
        http.setRequestHeader('Content-Type', 'application/json');
        http.send();
    }

    /**
     * onchange heure jambe 1 transit confirm.
     * 1) code_progr sur l'option → siegdispotrans (prioritaire)
     * 2) sinon verifprog (avec sous-gare confirm, puis sans filtre strict via retry code)
     */
    function __confOnHeureTransit1Change(fallbackGare) {
        var hSel = document.querySelector('#heured');
        if (!hSel || hSel.selectedIndex < 1) return;
        var hOpt = hSel.options[hSel.selectedIndex];
        var raw = hOpt ? (hOpt.value || '') : '';
        var parts = String(raw).split('/');
        var idLh = parts[0] || '';
        if (!idLh) return;

        var codeProgr = hOpt.getAttribute('data-code-progr') || '';
        var gareDep1 = hOpt.getAttribute('data-gareidentif') || fallbackGare;
        var depl = document.querySelector('#deplignetranscf');
        if (depl && depl.value) gareDep1 = depl.value;
        __confFillTransitDepart('#transitedepargarecf1', gareDep1);

        var dSel = document.querySelector('#depsieg');
        if (dSel) dSel.options.length = 1;

        if (codeProgr) {
            __confLoadProgByCode(codeProgr, idLh, gareDep1);
            return;
        }

        var itinCode = document.querySelector('#itinecodecf') ? document.querySelector('#itinecodecf').value : '';
        var dptDate = document.querySelector('#actuel') ? document.querySelector('#actuel').value : '';
        // Sous-gare du modal confirm uniquement (évite le #depargare vente).
        var sougid = '0';
        var depCf = document.querySelector('#confirm-0 #depargare') || document.querySelector('#confForm #depargare');
        if (depCf && depCf.value) {
            var sp = String(depCf.value).split('/');
            if (sp[1]) sougid = sp[1];
        }
        if (!itinCode || !dptDate) return;

        var http = new XMLHttpRequest();
        http.open(
            'GET',
            window.location.origin + `${APP_ROOT}/programmes/verifprog/`
                + itinCode + '/' + dptDate + '/' + idLh + '/' + (sougid || '0'),
            true
        );
        http.onload = function () {
            var don = null;
            try { don = JSON.parse(http.responseText); } catch (e) { don = null; }
            var list = __confProgListFromResponse(don);
            if (list.length >= 1) {
                __confApplyTransit1Fields(list[0]);
                __confLoadSiegesTransit1(idLh, dptDate);
                return;
            }
            // Retry sans filtre sous-gare trop strict : reprendre code_progr éventuel du 1er résultat heure.
            // Dernier recours : rien.
        };
        http.setRequestHeader('Content-Type', 'application/json');
        http.send();
    }

    /**
     * Charge heures jambe 1 via verifheureitine (ident_ligne jambe),
     * attache data-code-progr, puis auto-déclenche siège + départ.
     */
    function __confLoadHeuresJambe1AndTrigger(codeItineraire, datedepart, fallbackGare) {
        var hSel = document.querySelector('#heured');
        var dSel = document.querySelector('#depsieg');
        if (hSel) hSel.options.length = 1;
        if (dSel) dSel.options.length = 1;
        var depSel = document.querySelector('#transitedepargarecf1');
        if (depSel) depSel.options.length = 0;
        if (!codeItineraire || !datedepart) return;
        var http = new XMLHttpRequest();
        http.open(
            'GET',
            window.location.origin + `${APP_ROOT}/programmes/verifheureitine/`
                + encodeURIComponent(codeItineraire) + '/'
                + encodeURIComponent(datedepart),
            true
        );
        http.onload = function () {
            var rows = null;
            try { rows = JSON.parse(http.responseText); } catch (e) { rows = null; }
            if (!hSel) return;
            hSel.options.length = 1;
            if (rows && Object.entries(rows).length >= 1) {
                for (var key in Object.entries(rows)) {
                    var row = rows[key];
                    if (!row || row.id_ligneheure == null) continue;
                    var opt = document.createElement('option');
                    opt.value = `${row.id_ligneheure}/${row.heure || ''}`;
                    opt.setAttribute('data-has-programme', '1');
                    opt.setAttribute('data-transit-leg1', '1');
                    if (row.code_progr) opt.setAttribute('data-code-progr', String(row.code_progr));
                    if (row.gareidentif) opt.setAttribute('data-gareidentif', String(row.gareidentif));
                    opt.innerHTML = row.heure || '';
                    hSel.add(opt);
                }
            }
            if (hSel.options.length > 1) {
                hSel.selectedIndex = 1;
                if (typeof hSel.onchange === 'function') {
                    hSel.onchange();
                } else {
                    __confOnHeureTransit1Change(fallbackGare);
                }
            }
        };
        http.setRequestHeader('Content-Type', 'application/json');
        http.send();
    }



    function __confModalRoot() {
        return document.getElementById('confirm-0');
    }

    function __confField(id) {
        var root = __confModalRoot();
        return root ? root.querySelector('#' + id) : document.getElementById(id);
    }

    function __confResetUi() {
        var root = __confModalRoot();
        if (!root) return;
        var msg = root.querySelector('#messagep');
        if (msg) msg.style.display = 'none';
        var code = root.querySelector('#codeconfirm');
        if (code) code.value = '';
        var actuel = root.querySelector('#actuel');
        if (actuel) actuel.value = new Date().toISOString().slice(0, 10);
        ['pasnompconf', 'pasprenompconf', 'pascontactpconf', 'pascnibpconf', 'pasdatepconf', 'delivrelieu', 'heured', 'depsieg'].forEach(function (id) {
            var el = root.querySelector('#' + id);
            if (el) el.style.display = 'none';
        });
        var btnOrd = root.querySelector('#valid');
        var btnEp = root.querySelector('#validep');
        if (btnOrd) {
            btnOrd.style.display = 'none';
            btnOrd.disabled = true;
            btnOrd.setAttribute('disabled', 'disabled');
        }
        if (btnEp) btnEp.style.display = 'none';
    }

    function __confBindDateReload(axeselect) {
        var actuelEl = __confField('actuel');
        if (!actuelEl || actuelEl.dataset.confDateBound) return;
        actuelEl.dataset.confDateBound = '1';
        actuelEl.addEventListener('change', function () {
            if (axeselect && axeselect.value && typeof axeselect.onchange === 'function') {
                axeselect.onchange();
            }
        });
    }


    document.querySelectorAll('.addconfirme').forEach(function (e) {
        document.querySelector('h3#confTitle').innerHTML = `CONFIRMATION`;

        let cod = document.querySelector('#confirmer_infos');
        if (cod !== null)
        cod.onclick = () => {
            
            //verification code de confirmation
            let Request;
            
            if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                Request = new XMLHttpRequest();
            } else if (window.ActiveXObject) { // IE 6 and older
                Request = new ActiveXObject("Microsoft.XMLHTTP");
            }
            
            var confir = (__confField('codeconfirm') || {}).value || document.querySelector("#codeconfirm").value;

            Request.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verificationcode/${confir}`, true);
            Request.onload = () => {
                
            const data = JSON.parse(Request.responseText);
            
            var btnOrdinaire = __confField('valid');
            var btnEpson = __confField('validep');
            var showField = function (id) {
                var el = __confField(id);
                if (el) el.style.display = 'block';
            };
            var hideField = function (id) {
                var el = __confField(id);
                if (el) el.style.display = 'none';
            };

            if (data == null) {
                        
                        showField('pasnompconf');
                        showField('pasprenompconf');
                        showField('pascontactpconf');
                        showField('pascnibpconf');
                        showField('pasdatepconf');
                        showField('delivrelieu');
                        showField('heured');
                        showField('depsieg');
                        if (btnOrdinaire) {
                            btnOrdinaire.style.display = 'block';
                            btnOrdinaire.disabled = false;
                            btnOrdinaire.removeAttribute('disabled');
                        }
                        if (btnEpson) btnEpson.style.display = 'block';
                        var msgOk = __confField('messagep');
                        if (msgOk) msgOk.style.display = 'none';

                } else {
                    if (Object.entries(data).length > 1) {
                        var msgErr = __confField('messagep');
                        if (msgErr) msgErr.style.display = 'block';
                        var errEl = __confField('erreurMessagep') || document.querySelector('#erreurMessagep');
                        if (errEl) errEl.innerHTML = 'Cet ticket ne peut pas être confirmé .';
                        hideField('pasnompconf');
                        hideField('pasprenompconf');
                        hideField('pascontactpconf');
                        hideField('pascnibpconf');
                        hideField('pasdatepconf');
                        hideField('delivrelieu');
                        hideField('heured');
                        hideField('depsieg');
                        if (btnOrdinaire) {
                            btnOrdinaire.style.display = 'none';
                            btnOrdinaire.disabled = true;
                            btnOrdinaire.setAttribute('disabled', 'disabled');
                        }
                        if (btnEpson) btnEpson.style.display = 'none';
                    }
                      
                }
            };
            Request.setRequestHeader('Content-Type', 'application/json');
            Request.send();
        };
        let Requests;
        if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
            Requests = new XMLHttpRequest();
        } else if (window.ActiveXObject) { // IE 6 and older
            Requests = new ActiveXObject("Microsoft.XMLHTTP");
        }
        let axeselect = document.querySelector('#axeconf');
        if (axeselect !== null)
        axeselect.onchange = () => 
        {
            
                var heureaxep = document.querySelector('#axeconf').value;
                var dateactuel = document.querySelector('#actuel').value;
                document.querySelector('#heured').options.length = 1;
                document.querySelector('#depsieg').options.length = 1;
                document.querySelector('#quartconf').options.length = 1;
                document.querySelector('#hdepartitinecf').options.length = 1;
                document.querySelector('#psiegesitinescf').options.length = 1;
                document.querySelector('#idcheminsheurcf').options.length = 1;
                document.querySelector('#transitedepargarecf1').options.length = 0;
                document.querySelector('#transitedepargarecf2').options.length = 0;
                document.querySelector('#transitedepargarecf3').options.length = 0;
                document.querySelector('#iddeptranscf4').style.display = 'none';
                document.querySelector('#transitedepargarecf4').options.length = 0;
                document.querySelector('#idcheminscf').options.length = 1;
                document.querySelector('#idcheminscf1').options.length = 1;
                document.querySelector('#idcheminscf2').options.length = 1;
                document.querySelector('#idcompgcf').value = '';
                document.querySelector('#idcompgcf1').value = '';
                document.querySelector('#idcompgcf2').value = '';
                document.querySelector('#idcompgcf3').value = '';
                document.querySelector('#psiegesitinescf1').options.length = 1;
                document.querySelector('#idcheminsheurcf1').options.length = 1;
                document.querySelector('#psiegesitinescf2').options.length = 1;
                document.querySelector('#idcheminsheurcf1').options.length = 1;
                document.querySelector('#quartiercf1').options.length = 1;
                document.querySelector('#quartiercf2').options.length = 1;
                document.querySelector('#quartiercf3').options.length = 1;
                
                document.querySelector('#iddeptranscf1').style.display = 'none';
                document.querySelector('#transitedepargarecf1').style.display = 'none';
                document.querySelector('#iddeptranscf2').style.display = 'none';
                document.querySelector('#transitedepargarecf2').style.display = 'none';
                document.querySelector('#iddeptranscf3').style.display = 'none';
                document.querySelector('#transitedepargarecf3').style.display = 'none';
                document.querySelector('#arritincf1').style.display = 'none';
                document.querySelector('#heureitincf1').style.display = 'none';
                document.querySelector('#lignesitinerairecf').style.display = 'none';
                document.querySelector('#lignecf1').style.display = 'none';
                document.querySelector('#siegitinecf1').style.display = 'none';
                document.querySelector('#psiegesitinescf1').style.display = 'none';
                document.querySelector('#arritincf2').style.display = 'none';
                document.querySelector('#siegitinecf2').style.display = 'none';
                document.querySelector('#psiegesitinescf2').style.display = 'none';
                document.querySelector('#arritincf3').style.display = 'none';
                document.querySelector('#quartiercf1').style.display = 'none';
                document.querySelector('#quartiercf2').style.display = 'none';
                document.querySelector('#quartiercf3').style.display = 'none';
                document.querySelector('#idquartcf1').style.display = 'none';
                document.querySelector('#idquartcf2').style.display = 'none';
                document.querySelector('#idquartcf3').style.display = 'none';
                document.querySelector('#idquartcf2').style.display = 'none';
                document.querySelector('#idcheminscf2').style.display = 'none';
                document.querySelector('#idcheminsheurcf1').style.display = 'none';
                document.querySelector('#heureitincf2').style.display = 'none';
                document.querySelector('#idcheminsheurcf').style.display = 'none';
                document.querySelector('#idcheminscf1').style.display = 'none';
                
                document.querySelector('#transitedepargarecf4').style.display = 'none';
                document.querySelector('#trancf').style.display = 'none';
                document.querySelector('#heureitincf').style.display = 'none';
                document.querySelector('#hdepartitinecf').style.display = 'none';
                document.querySelector('#siegitinecf').style.display = 'none';
                document.querySelector('#psiegesitinescf').style.display = 'none';
                document.querySelector('#heured').style.display = 'block';
                document.querySelector('#depsieg').style.display = 'block';
                document.querySelector('#programcf').value = '';

                let httpRequetesquart = new XMLHttpRequest();
                httpRequetesquart.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfquart/${heureaxep}`, true);
                httpRequetesquart.onload = () => {
                const dataq = JSON.parse(httpRequetesquart.responseText);
                    if(dataq == ''){
                        document.querySelector('#quartconf').options.length = 1;
                    }else
                    {
                        if (Object.entries(dataq).length >= 1) {
                                    
                            for (let key in Object.entries(dataq)) {
                                let opt = document.createElement('option');
                                opt.value = `${dataq[key].nom_quartier}`;
                                opt.innerHTML = `${dataq[key].nom_quartier}`;
                                document.querySelector('#quartconf').add(opt);
                            }
                        } else {
                            document.querySelector('#quartconf').options.length = 1;
                        }
                    }  
                        
                };
                httpRequetesquart.setRequestHeader('Content-Type', 'application/json');
                httpRequetesquart.send();

                // Catalogue heures moderne (même modèle que vente guichet).
                var sougidcf = __confDepSousGare();
                Requests.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheuresvente/${encodeURIComponent(heureaxep)}/${encodeURIComponent(dateactuel)}/${encodeURIComponent(sougidcf || '0')}`, true);
                Requests.onload = () => {
                    var payloadHvCf = {};
                    try { payloadHvCf = JSON.parse(Requests.responseText) || {}; } catch (eHvCf) { payloadHvCf = {}; }
                    var heuresHvCf = Array.isArray(payloadHvCf.heures) ? payloadHvCf.heures : [];
                    window.__confHasTransit = !!payloadHvCf.has_transit;
                    window.__confLastHeuresVente = heuresHvCf;

                    __confShowDirectHourUi();
                    __confFillHeuresVente(heuresHvCf);

                    window.__confApplyTransitLegs = function (donitinescf) {
                                                    donitinescf = (typeof __confNormalizeEtapes === 'function')
                                                        ? __confNormalizeEtapes(donitinescf) : donitinescf;
                            if(donitinescf === null || donitinescf === '' || (typeof donitinescf === 'object' && !Object.keys(donitinescf).length))
                            {
                                document.querySelector('#iddeptranscf1').style.display = 'none';
                                document.querySelector('#transitedepargarecf1').style.display = 'none';
                                document.querySelector('#iddeptranscf2').style.display = 'none';
                                document.querySelector('#transitedepargarecf2').style.display = 'none';
                                document.querySelector('#iddeptranscf3').style.display = 'none';
                                document.querySelector('#transitedepargarecf3').style.display = 'none';
                                document.querySelector('#iddeptranscf4').style.display = 'none';
                                document.querySelector('#transitedepargarecf4').style.display = 'none';
                                document.querySelector('#arritincf1').style.display = 'none';
                                document.querySelector('#heureitincf1').style.display = 'none';
                                document.querySelector('#lignesitinerairecf').style.display = 'none';
                                document.querySelector('#lignecf1').style.display = 'none';
                                document.querySelector('#siegitinecf1').style.display = 'none';
                                document.querySelector('#psiegesitinescf1').style.display = 'none';
                                document.querySelector('#arritincf2').style.display = 'none';
                                document.querySelector('#heureitincf2').style.display = 'none';
                                document.querySelector('#hdepartitinecf2').style.display = 'none';
                                document.querySelector('#siegitinecf2').style.display = 'none';
                                document.querySelector('#psiegesitinescf2').style.display = 'none';
                                document.querySelector('#arritincf3').style.display = 'none';
                                document.querySelector('#quartiercf1').style.display = 'none';
                                document.querySelector('#quartiercf2').style.display = 'none';
                                document.querySelector('#quartiercf3').style.display = 'none';
                                document.querySelector('#idquartcf1').style.display = 'none';
                                document.querySelector('#idquartcf2').style.display = 'none';
                                document.querySelector('#idquartcf3').style.display = 'none';

                                document.querySelector('#trancf').style.display = 'none'; if (typeof window.__confSetMainEscaleVisible === 'function') window.__confSetMainEscaleVisible(true);
                                document.querySelector('#heureitincf').style.display = 'none';
                                document.querySelector('#hdepartitinecf').style.display = 'none';
                                document.querySelector('#siegitinecf').style.display = 'none';
                                document.querySelector('#psiegesitinescf').style.display = 'none';
                                document.querySelector('#heured').style.display = 'block';
                            }
                            else
                                                    {
                                                        if (typeof __confResetTransitFieldsBeforeApply === 'function') __confResetTransitFieldsBeforeApply();
                                                        if (Object.entries(donitinescf).length >= 1) 
                                {
                                    var i = Object.entries(donitinescf).length;
                                    
                                    for (let key in Object.entries(donitinescf)) 
                                    {
                                        
                                        document.querySelector('#nbrtranscf').value = Object.entries(donitinescf).length;
                                        if(i === 2){
                                            document.querySelector('#idcheminscf').style.display = 'block';
                                            document.querySelector('#heured').style.display = 'block';
                                            document.querySelector('#heureitincf').style.display = 'block';
                                            document.querySelector('#hdepartitinecf').style.display = 'block';
                                            document.querySelector('#siegitinecf').style.display = 'block';
                                            document.querySelector('#psiegesitinescf').style.display = 'block';
                                            document.querySelector('#depsieg').style.display = 'block';
                                            document.querySelector('#quartiercf1').style.display = 'block';
                                            document.querySelector('#idquartcf1').style.display = 'block';
                                            document.querySelector('#lignecf1').style.display = 'block';
                                            document.querySelector('#lignesitinerairecf').style.display = 'block';
                                            document.querySelector('#iddeptranscf1').style.display = 'block';
                                            document.querySelector('#arritincf1').style.display = 'block';
                                            document.querySelector('#transitedepargarecf1').style.display = 'block';
                                            document.querySelector('#iddeptranscf2').style.display = 'block';
                                            document.querySelector('#transitedepargarecf2').style.display = 'block';
                                        }
                                        
                                        if(i === 3){
                                            document.querySelector('#idcheminscf').style.display = 'block';
                                            document.querySelector('#heureitincf').style.display = 'block';
                                            document.querySelector('#hdepartitinecf').style.display = 'block';
                                            document.querySelector('#siegitinecf').style.display = 'block';
                                            document.querySelector('#psiegesitinescf').style.display = 'block';
                                            document.querySelector('#depsieg').style.display = 'block';
                                            document.querySelector('#quartiercf1').style.display = 'block';
                                            document.querySelector('#idquartcf1').style.display = 'block';
                                            document.querySelector('#lignecf1').style.display = 'block';
                                            document.querySelector('#lignesitinerairecf').style.display = 'block';
                                            document.querySelector('#heured').style.display = 'block';
                                            document.querySelector('#iddeptranscf1').style.display = 'block';
                                            document.querySelector('#arritincf1').style.display = 'block';
                                            document.querySelector('#transitedepargarecf1').style.display = 'block';
                                            
                                            document.querySelector('#arritincf2').style.display = 'block';
                                            document.querySelector('#idcheminscf1').style.display = 'block';
                                            document.querySelector('#idquartcf2').style.display = 'block';
                                            document.querySelector('#quartiercf2').style.display = 'block';
                                            document.querySelector('#heureitincf1').style.display = 'block';
                                            document.querySelector('#idcheminsheurcf').style.display = 'block';
                                            document.querySelector('#siegitinecf1').style.display = 'block';
                                            document.querySelector('#psiegesitinescf1').style.display = 'block';
                                            document.querySelector('#iddeptranscf2').style.display = 'block';
                                            document.querySelector('#transitedepargarecf2').style.display = 'block';
                                            document.querySelector('#iddeptranscf3').style.display = 'block';
                                            document.querySelector('#transitedepargarecf3').style.display = 'block';
                                        }if(i === 4){

                                            document.querySelector('#idcheminscf').style.display = 'block';
                                            document.querySelector('#heureitincf').style.display = 'block';
                                            document.querySelector('#hdepartitinecf').style.display = 'block';
                                            document.querySelector('#siegitinecf').style.display = 'block';
                                            document.querySelector('#psiegesitinescf').style.display = 'block';
                                            document.querySelector('#depsieg').style.display = 'block';
                                            document.querySelector('#quartiercf1').style.display = 'block';
                                            document.querySelector('#idquartcf1').style.display = 'block';
                                            document.querySelector('#lignecf1').style.display = 'block';
                                            document.querySelector('#lignesitinerairecf').style.display = 'block';
                                            document.querySelector('#heured').style.display = 'block';
                                            document.querySelector('#iddeptranscf1').style.display = 'block';
                                            document.querySelector('#arritincf1').style.display = 'block';
                                            document.querySelector('#transitedepargarecf1').style.display = 'block';


                                            document.querySelector('#arritincf2').style.display = 'block';
                                            document.querySelector('#idcheminscf1').style.display = 'block';
                                            
                                            document.querySelector('#idcheminscf2').style.display = 'block';
                                            document.querySelector('#idquartcf2').style.display = 'block';
                                            document.querySelector('#quartiercf2').style.display = 'block';
                                            document.querySelector('#heureitincf1').style.display = 'block';
                                            document.querySelector('#idcheminsheurcf').style.display = 'block';
                                            document.querySelector('#siegitinecf1').style.display = 'block';
                                            document.querySelector('#psiegesitinescf1').style.display = 'block';
                                            document.querySelector('#iddeptranscf2').style.display = 'block';
                                            document.querySelector('#transitedepargarecf2').style.display = 'block';


                                            document.querySelector('#arritincf3').style.display = 'block';
                                            document.querySelector('#idquartcf3').style.display = 'block';
                                            document.querySelector('#quartiercf3').style.display = 'block';
                                            document.querySelector('#heureitincf2').style.display = 'block';
                                            document.querySelector('#idcheminsheurcf1').style.display = 'block';
                                            document.querySelector('#siegitinecf2').style.display = 'block';
                                            document.querySelector('#psiegesitinescf2').style.display = 'block';
                                            document.querySelector('#iddeptranscf3').style.display = 'block';
                                            document.querySelector('#transitedepargarecf3').style.display = 'block';
                                            document.querySelector('#iddeptranscf4').style.display = 'block';
                                            document.querySelector('#transitedepargarecf4').style.display = 'block';
                                        }
                                        document.querySelector('#trancf').style.display = 'block'; if (typeof window.__confSetMainEscaleVisible === 'function') window.__confSetMainEscaleVisible(false);
                                        if (document.querySelector('#depsieg')) document.querySelector('#depsieg').options.length = 1;
                                        if (document.querySelector('#transitedepargarecf1')) document.querySelector('#transitedepargarecf1').options.length = 0;
                                        if (document.querySelector('#heured')) document.querySelector('#heured').options.length = 1;
                                        document.querySelector('#heureitincf').style.display = 'block';
                                        document.querySelector('#hdepartitinecf').style.display = 'block';
                                        document.querySelector('#lignesitinerairecf').style.display = 'block';
                                        document.querySelector('#lignecf1').style.display = 'block';
                                        document.querySelector('#siegitinecf').style.display = 'block';
                                        document.querySelector('#psiegesitinescf').style.display = 'block';
                                        document.querySelector('#heured').style.display = 'block';
                    
                                        document.querySelector('#idcompgcf').value = `${donitinescf[0].id_compaga}`;
                                        __confFillLigne1Locked(donitinescf[0]);
                                    }
                        
                                    if(i === 2)
                                    {
                                        __confSetCheminLigneOption('#idcheminscf', donitinescf[1].code_itineraires, donitinescf[1].nom_itineraires);

                                        __confFillLigne1Locked(donitinescf[0]);
                                        document.querySelector('#itinecodescf').value = `${donitinescf[0].id_lignes}`;
                                        document.querySelector('#idcompgcf').value = `${donitinescf[0].id_compaga}`;
                                        document.querySelector('#idcompgcf1').value = `${donitinescf[1].id_compaga}`;
                                        var typgarecf1 = document.querySelector('#itinecodecf').value;
                                        var post_typgarecf1 = (typgarecf1 || '').split('-');
                                        var seltypgarecf1 = (donitinescf[0] && (donitinescf[0].gaexp_lg || donitinescf[0].code_gaexp)) ? (donitinescf[0].gaexp_lg || donitinescf[0].code_gaexp) : post_typgarecf1[0];
                                        var typgareselcf = (donitinescf[0] && donitinescf[0].gadest_lg) ? donitinescf[0].gadest_lg : post_typgarecf1[1];
                                        var identLigneJambe1cf = (donitinescf[0] && donitinescf[0].id_lignes) ? donitinescf[0].id_lignes : (document.querySelector('#itinecodescf') && document.querySelector('#itinecodescf').value);
                                        if (document.querySelector('#itinecodescf') && identLigneJambe1cf) document.querySelector('#itinecodescf').value = identLigneJambe1cf;
                                            let httptypequartcf1;
                                            httptypequartcf1 = new XMLHttpRequest();
                                            
                                            httptypequartcf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgareselcf}`, true);
                                            httptypequartcf1.onload = () => 
                                            {
                                                const donquacf1 = JSON.parse(httptypequartcf1.responseText);
                                                if (donquacf1 == '') {
                                                    document.querySelector('#quartiercf1').options.length = 1;
                                                }
                                                else{
                                                    if (Object.entries(donquacf1).length >= 1) {
                                                                    
                                                        for (let key in Object.entries(donquacf1)) {
                                                            let optq = document.createElement('option');
                                                            optq.value = `${donquacf1[key].nom_quartier}`;
                                                            optq.innerHTML = `${donquacf1[key].nom_quartier}`;
                                                            document.querySelector('#quartiercf1').add(optq);
                                                        }
                                                    } else {
                                                        document.querySelector('#quartiercf1').options.length = 1;
                                                    }
                                                }
                                                

                                            };
                                            httptypequartcf1.setRequestHeader('Content-Type', 'application/json');
                                            httptypequartcf1.send();

                                        let hrdepartinecf = document.querySelector('#heured');
                                        if (hrdepartinecf !== null) {
                                            hrdepartinecf.onchange = () => {
                                                __confOnHeureTransit1Change(seltypgarecf1);
                                            };
                                            __confLoadHeuresJambe1AndTrigger((document.querySelector('#itinecodecf') && document.querySelector('#itinecodecf').value) || '', document.querySelector('#actuel').value, seltypgarecf1);
                                    
                                        }
                                        progsiegestranscf = document.querySelector('#depsieg');
                                        if (progsiegestranscf !== null) {
                                            progsiegestranscf.onchange = () => 
                                            {

                                                gareidentiftranscf = document.querySelector('#deplignetranscf').value;
                                                    const httpsousgarecf = new XMLHttpRequest();
                                                    __confFillTransitDepart('#transitedepargarecf1', gareidentiftranscf);
                                                let httpSiegestranscf;
                                                httpSiegestranscf = new XMLHttpRequest();
                                                const sigstranscf = document.querySelector('#depsieg')
                                                .options[document.querySelector('#depsieg').options.selectedIndex].value;
                                                const prostranscf = document.querySelector('#programtranscf').value;

                                                httpSiegestranscf.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${prostranscf}/${sigstranscf}`, true);
                                                httpSiegestranscf.onload = () => 
                                                {
                                                    const donsgetranscf = JSON.parse(httpSiegestranscf.responseText);
                                                    console.debug(`${typeof donsgetranscf} - ${donsgetranscf.attributes}`, console.memory);
                                                    if(donsgetranscf == '')
                                                    {
                                                        let httpSiegstranscf;
                                                        httpSiegstranscf = new XMLHttpRequest();

                                                        httpSiegstranscf.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${prostranscf}/${sigstranscf}`, true);
                                                        httpSiegstranscf.onload = () => 
                                                        {
                                                            const dongtranscf = JSON.parse(httpSiegstranscf.responseText);
                                                            document.querySelector('#messconf').style.display = 'none';
                                                            if (Object.entries(dongtranscf).length >= 1)
                                                                {
                                                                    for (let key in Object.entries(dongtranscf)) {
                                                                        document.querySelector('#idtampotranscf').value = `${dongtranscf[key].idtamp}`;                    
                                                                        document.querySelector('#siegselecttranscf').value = `${dongtranscf[key].numsieg}`;
                                                                    }
                                                                }
                                                        };
                                                        httpSiegstranscf.setRequestHeader('Content-Type', 'application/json');
                                                        httpSiegstranscf.send();
                                                    }
                                                    else {
                                                        document.querySelector('#depsieg').value = '';     
                                                        if (Object.entries(donsgetranscf).length >= 1)
                                                        {
                                                            for (let key in Object.entries(donsgetranscf)) {
                                                                document.querySelector('#idtampotranscf').value = `${donsgetranscf[key].idtamp}`;                    
                                                                document.querySelector('#siegselecttranscf').value = `${donsgetranscf[key].numsieg}`;
                                                            }

                                                        }
                                                        document.querySelector('#messconf').style.display = 'block';
                                                        document.querySelector('#erreurMessconf').innerHTML = `Siege déjà utilisé.`;
                                                    }
                                                };
                                                httpSiegestranscf.setRequestHeader('Content-Type', 'application/json');
                                                httpSiegestranscf.send();

                                            
                                            };
                                        }

                                        let progchemincf = document.querySelector('#idcheminscf');
                                        if (progchemincf !== null) 
                                        {
                                            progchemincf.onchange = () => 
                                            {
                                                let httpSiegeschemincf;
                                                httpSiegeschemincf = new XMLHttpRequest();
                                                
                                                const prostranschemincf = document.querySelector('#idcheminscf')
                                                .options[document.querySelector('#idcheminscf').options.selectedIndex].value;

                                                var post_typgarecf2 = prostranschemincf.split('-');
                                                var seltypgarecf2 = post_typgarecf2[0];
                                                var typgareselcf1 = post_typgarecf2[1];
                                                
                                                var datedepartcf = document.querySelector('#actuel').value;
                                                httpSiegeschemincf.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranschemincf}/${datedepartcf}`, true);
                                                httpSiegeschemincf.onload = () => 
                                                {
                                        
                                                    const dongtranschemcf = JSON.parse(httpSiegeschemincf.responseText);
                                                    var __prevCfX = __confPrevFromLeg1();
                                                        __confAppendFilteredCheminOptions('#hdepartitinecf', dongtranschemcf, __prevCfX.date, __prevCfX.heure);
                                                };
                                                httpSiegeschemincf.setRequestHeader('Content-Type', 'application/json');
                                                httpSiegeschemincf.send();

                                            };
                                                let prochemintracf = document.querySelector('#hdepartitinecf');
                                            if (prochemintracf !== null)
                                                prochemintracf.onchange = () => 
                                                {  
                                                    const httpPrixittransitecf = new XMLHttpRequest();
                                                    const transselitinecf = document.querySelector('#hdepartitinecf')
                                                    .options[document.querySelector('#hdepartitinecf').options.selectedIndex].value;
                                                    var post_transcf = transselitinecf.split('/');
                                                    var itinetrascf = post_transcf[0];
                                                    var dbitracf = post_transcf[1];
                                                    var fnitracf = post_transcf[2];
                                                    var lhertracf = post_transcf[3];
                                                    var prixtracf = post_transcf[4];

                                                        httpPrixittransitecf.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetrascf}`, true);
                                                        httpPrixittransitecf.onload = () => 
                                                        {
                                                            const donprixitrancf = JSON.parse(httpPrixittransitecf.responseText);
                                                            console.debug(`${typeof donprixitrancf}-${donprixitrancf.attributes}`, console.memory);
                                                            if (Object.entries(donprixitrancf).length >= 1) {
                                                                for (let key in Object.entries(donprixitrancf)) 
                                                                {
                                                                    document.querySelector('#prix_axetransitcf').value = `${prixtracf}`;
                                                                    document.querySelector('#catetransitcf').value = `${donprixitrancf[key].categori}`;
                                                                    document.querySelector('#gidtranscf').value =  `${donprixitrancf[key].gareidentif}`;
                                                                    document.querySelector('#nomitintranscf1').value = `${donprixitrancf[key].nom_ligne}`;
                                                                    document.querySelector('#ligntranscf1').value = `${donprixitrancf[key].ident_ligne}`;

                                                                }
                                                            }
                                                        };
                                                        httpPrixittransitecf.setRequestHeader('Content-Type', 'application/json');
                                                        httpPrixittransitecf.send();
                                                        
                                                              
                                                            
                                                        const httpRequetteitracf = new XMLHttpRequest();
                                                
                                                        httpRequetteitracf.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetrascf}/${dbitracf}/${fnitracf}`, true);
                                                        httpRequetteitracf.onload = () => {
                                                            const dattaitracf = JSON.parse(httpRequetteitracf.responseText);
                                                            console.debug(`${typeof dattaitracf} - ${dattaitracf.attributes}`, console.memory);
                                                            if (Object.entries(dattaitracf).length >= 1) {
                                                                for (let key in Object.entries(dattaitracf)) {
                                                                    
                                                                    let opt = document.createElement('option');
                                                                    opt.value = `${dattaitracf[key].siege_num}`;
                                                                    opt.innerHTML = `${dattaitracf[key].siege_num}`;
                                                                    document.querySelector('#psiegesitinescf').add(opt);
                                                                    
                                                                }
                                                                
                                                            } else {
                                                                document.querySelector('#psiegesitinescf').options.length = 1;
                                                            }
                                                        };
                                                        httpRequetteitracf.setRequestHeader('Content-Type', 'application/json');
                                                        httpRequetteitracf.send();
                                                };

                                                progsiegescf1 = document.querySelector('#psiegesitinescf');
                                                if (progsiegescf1 !== null) 
                                                {
                                                    progsiegescf1.onchange = () => 
                                                    {
                                                        
                                                        const transselitinecf1 = document.querySelector('#hdepartitinecf')
                                                        .options[document.querySelector('#hdepartitinecf').options.selectedIndex].value;
                                                        var post_transcf1 = transselitinecf1.split('/');
                                                        var itinetrascf1 = post_transcf1[0];
                                                        
                                                        gareidentiftranscf2 = document.querySelector('#gidtranscf').value;
                                                        const httpsousgarecf1 = new XMLHttpRequest();
                                                        httpsousgarecf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftranscf2}`, true);
                                                        httpsousgarecf1.onload = () => 
                                                        {
                                                            const donsousgcf1 = JSON.parse(httpsousgarecf1.responseText);
                                                            console.debug(`${typeof donsousgcf1}-${donsousgcf1.attributes}`, console.memory);
                                                            if (Object.entries(donsousgcf1).length >= 1) {
                                                                for (let key in Object.entries(donsousgcf1)) 
                                                                {
                                                                    let opt = document.createElement('option');
                                                                    opt.value = `${donsousgcf1[key].idsousgare}`;
                                                                    opt.innerHTML = `${donsousgcf1[key].nomsousgare}`;
                                                                    document.querySelector('#transitedepargarecf2').add(opt);
        
                                                                }
                                                            }
                                                        };
                                                        httpsousgarecf1.setRequestHeader('Content-Type', 'application/json');
                                                        httpsousgarecf1.send();
                                                      
                                                        let httpSiegescf1;
                                                        httpSiegescf1 = new XMLHttpRequest();
                                                        const sigscf1 = document.querySelector('#psiegesitinescf')
                                                        .options[document.querySelector('#psiegesitinescf').options.selectedIndex].value;

                                                        httpSiegescf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetrascf1}/${sigscf1}`, true);
                                                        httpSiegescf1.onload = () => 
                                                        {
                                                            const donsgecf1 = JSON.parse(httpSiegescf1.responseText);
                                                            console.debug(`${typeof donsgecf1} - ${donsgecf1.attributes}`, console.memory);
                                                            if(donsgecf1 == '')
                                                            {
                                                                let httpSiegscf1;
                                                                httpSiegscf1 = new XMLHttpRequest();

                                                                httpSiegscf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetrascf1}/${sigscf1}`, true);
                                                                httpSiegscf1.onload = () => 
                                                                {
                                                                    const dongcf1 = JSON.parse(httpSiegscf1.responseText);
                                                                    document.querySelector('#messconf').style.display = 'none';
                                                                    if (Object.entries(dongcf1).length >= 1)
                                                                    {
                                                                        for (let key in Object.entries(dongcf1)) {
                                                                            document.querySelector('#idtampocf1').value = `${dongcf1[key].idtamp}`;                    
                                                                            document.querySelector('#siegselectcf1').value = `${dongcf1[key].numsieg}`;
                                                                        }
                                                                    }
                                                                };
                                                                httpSiegscf1.setRequestHeader('Content-Type', 'application/json');
                                                                httpSiegscf1.send();
                                                            }
                                                            else {
                                                                document.querySelector('#psiegesitinescf').value = '';     
                                                                if (Object.entries(donsgecf1).length >= 1)
                                                                {
                                                                    for (let key in Object.entries(donsgecf1)) {
                                                                        document.querySelector('#idtampocf1').value = `${donsgecf1[key].idtamp}`;                    
                                                                        document.querySelector('#siegselectcf1').value = `${donsgecf1[key].numsieg}`;
                                                                    }

                                                                }
                                                                document.querySelector('#messconf').style.display = 'block';
                                                                document.querySelector('#erreurMessconf').innerHTML = `Siege déjà utilisé.`;
                                                            }
                                                        };
                                                        httpSiegescf1.setRequestHeader('Content-Type', 'application/json');
                                                        httpSiegescf1.send();

                                                    };
                                                }
                                        }               
                                    }
                                    //second itineraire
                                    if(i === 3)
                                    {
                                        __confSetCheminLigneOption('#idcheminscf', donitinescf[1].code_itineraires, donitinescf[1].nom_itineraires);

                                        __confFillLigne1Locked(donitinescf[0]);
                                        document.querySelector('#itinecodescf').value = `${donitinescf[0].id_lignes}`;
                                        document.querySelector('#idcompgcf').value = `${donitinescf[0].id_compaga}`;

                                        __confSetCheminLigneOption('#idcheminscf1', donitinescf[2].code_itineraires, donitinescf[2].nom_itineraires);

                                        document.querySelector('#idcompgcf1').value = `${donitinescf[1].id_compaga}`;
                                        document.querySelector('#idcompgcf2').value = `${donitinescf[2].id_compaga}`;
                                        var typgarecf1 = document.querySelector('#itinecodecf').value;
                                        var post_typgarecf1 = typgarecf1.split('-');
                                        var seltypgarecf1 = (donitinescf[0] && (donitinescf[0].gaexp_lg || donitinescf[0].code_gaexp)) ? (donitinescf[0].gaexp_lg || donitinescf[0].code_gaexp) : post_typgarecf1[0];
                                        var typgareselcf = post_typgarecf1[1];
                                            let httptypequartcf1;
                                            httptypequartcf1 = new XMLHttpRequest();
                                            
                                            httptypequartcf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgareselcf}`, true);
                                            httptypequartcf1.onload = () => 
                                            {
                                                const donquacf1 = JSON.parse(httptypequartcf1.responseText);
                                                if (donquacf1 == '') {
                                                    document.querySelector('#quartiercf1').options.length = 1;
                                                }
                                                else{
                                                    if (Object.entries(donquacf1).length >= 1) {
                                                                    
                                                        for (let key in Object.entries(donquacf1)) {
                                                            let optq = document.createElement('option');
                                                            optq.value = `${donquacf1[key].nom_quartier}`;
                                                            optq.innerHTML = `${donquacf1[key].nom_quartier}`;
                                                            document.querySelector('#quartiercf1').add(optq);
                                                        }
                                                    } else {
                                                        document.querySelector('#quartiercf1').options.length = 1;
                                                    }
                                                }
                                                

                                            };
                                            httptypequartcf1.setRequestHeader('Content-Type', 'application/json');
                                            httptypequartcf1.send();


                                        let hrdepartinecf1 = document.querySelector('#heured');
                                        if (hrdepartinecf1 !== null) {
                                            hrdepartinecf1.onchange = () => {
                                                __confOnHeureTransit1Change(seltypgarecf1);
                                            };
                                            __confLoadHeuresJambe1AndTrigger((document.querySelector('#itinecodecf') && document.querySelector('#itinecodecf').value) || '', document.querySelector('#actuel').value, seltypgarecf1);
                                    
                                        }
                                        let progsiegestranscf = document.querySelector('#depsieg');
                                        if (progsiegestranscf !== null) {
                                            progsiegestranscf.onchange = () => 
                                            {

                                                const gareidentiftranscf1 = document.querySelector('#deplignetranscf').value;
                                                const httpsousgarecf = new XMLHttpRequest();
                                                __confFillTransitDepart('#transitedepargarecf1', gareidentiftranscf1);
                                                let httpSiegestranscf1;
                                                httpSiegestranscf1 = new XMLHttpRequest();
                                                const sigstranscf = document.querySelector('#depsieg')
                                                .options[document.querySelector('#depsieg').options.selectedIndex].value;
                                                const prostranscf = document.querySelector('#programtranscf').value;

                                                httpSiegestranscf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${prostranscf}/${sigstranscf}`, true);
                                                httpSiegestranscf1.onload = () => 
                                                {
                                                    const donsgetranscf = JSON.parse(httpSiegestranscf1.responseText);
                                                    console.debug(`${typeof donsgetranscf} - ${donsgetranscf.attributes}`, console.memory);
                                                    if(donsgetranscf == '')
                                                    {
                                                        let httpSiegstranscf;
                                                        httpSiegstranscf = new XMLHttpRequest();

                                                        httpSiegstranscf.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${prostranscf}/${sigstranscf}`, true);
                                                        httpSiegstranscf.onload = () => 
                                                        {
                                                            const dongtranscf = JSON.parse(httpSiegstranscf.responseText);
                                                            document.querySelector('#messconf').style.display = 'none';
                                                            if (Object.entries(dongtranscf).length >= 1)
                                                                {
                                                                    for (let key in Object.entries(dongtranscf)) {
                                                                        document.querySelector('#idtampotranscf').value = `${dongtranscf[key].idtamp}`;                    
                                                                        document.querySelector('#siegselecttranscf').value = `${dongtranscf[key].numsieg}`;
                                                                    }
                                                                }
                                                        };
                                                        httpSiegstranscf.setRequestHeader('Content-Type', 'application/json');
                                                        httpSiegstranscf.send();
                                                    }
                                                    else {
                                                        document.querySelector('#depsieg').value = '';     
                                                        if (Object.entries(donsgetranscf).length >= 1)
                                                        {
                                                            for (let key in Object.entries(donsgetranscf)) {
                                                                document.querySelector('#idtampotranscf').value = `${donsgetranscf[key].idtamp}`;                    
                                                                document.querySelector('#siegselecttranscf').value = `${donsgetranscf[key].numsieg}`;
                                                            }

                                                        }
                                                        document.querySelector('#messconf').style.display = 'block';
                                                        document.querySelector('#erreurMessconf').innerHTML = `Siege déjà utilisé.`;                                                                   
                                                    }
                                                };
                                                httpSiegestranscf1.setRequestHeader('Content-Type', 'application/json');
                                                httpSiegestranscf1.send();
                                            };                                                                
                                            
                                        }
                                        //premier transite
                                        let progchemincf = document.querySelector('#idcheminscf');
                                        if (progchemincf !== null) 
                                        {
                                            progchemincf.onchange = () => 
                                            {
                                                
                                                const prostranschemincf = document.querySelector('#idcheminscf')
                                                .options[document.querySelector('#idcheminscf').options.selectedIndex].value;

                                                var post_typgarecf2 = prostranschemincf.split('-');
                                                var seltypgarecf2 = post_typgarecf2[0];
                                                var typgareselcf1 = post_typgarecf2[1];
                                                let httptypequartcf2;
                                                httptypequartcf2 = new XMLHttpRequest();
                                                
                                                httptypequartcf2.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgareselcf1}`, true);
                                                httptypequartcf2.onload = () => 
                                                {
                                                    const donquacf2 = JSON.parse(httptypequartcf2.responseText);
                                                    if (donquacf2 == '') {
                                                        document.querySelector('#quartiercf2').options.length = 1;
                                                    }
                                                    else{
                                                        if (Object.entries(donquacf2).length >= 1) {
                                                                        
                                                            for (let key in Object.entries(donquacf2)) {
                                                                let optq1 = document.createElement('option');
                                                                optq1.value = `${donquacf2[key].nom_quartier}`;
                                                                optq1.innerHTML = `${donquacf2[key].nom_quartier}`;
                                                                document.querySelector('#quartiercf2').add(optq1);
                                                            }
                                                        } else {
                                                            document.querySelector('#quartiercf2').options.length = 1;
                                                        }
                                                    }
                                                    

                                                };
                                                httptypequartcf2.setRequestHeader('Content-Type', 'application/json');
                                                httptypequartcf2.send();

                                                let httpSiegeschemincf;
                                                httpSiegeschemincf = new XMLHttpRequest();

                                                var datedepartcf = document.querySelector('#actuel').value;
                                                
                                                httpSiegeschemincf.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranschemincf}/${datedepartcf}`, true);
                                                httpSiegeschemincf.onload = () => 
                                                {
                                        
                                                    const dongtranschemcf = JSON.parse(httpSiegeschemincf.responseText);
                                                        var __prevCfX = __confPrevFromLeg1();
                                                        __confAppendFilteredCheminOptions('#hdepartitinecf', dongtranschemcf, __prevCfX.date, __prevCfX.heure);
                                                };
                                                httpSiegeschemincf.setRequestHeader('Content-Type', 'application/json');
                                                httpSiegeschemincf.send();

                                            };
                                               let prochemintracf = document.querySelector('#hdepartitinecf');
                                            if (prochemintracf !== null)
                                                prochemintracf.onchange = () => 
                                                {  
                                                    const httpPrixittransitecf = new XMLHttpRequest();
                                                    const transselitinecf = document.querySelector('#hdepartitinecf')
                                                    .options[document.querySelector('#hdepartitinecf').options.selectedIndex].value;
                                                    var post_transcf = transselitinecf.split('/');
                                                    var itinetrascf = post_transcf[0];
                                                    var dbitracf = post_transcf[1];
                                                    var fnitracf = post_transcf[2];
                                                    var lhertracf = post_transcf[3];
                                                    var prixtracf = post_transcf[4];

                                                    httpPrixittransitecf.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetrascf}`, true);
                                                    httpPrixittransitecf.onload = () => 
                                                    {
                                                        const donprixitrancf = JSON.parse(httpPrixittransitecf.responseText);
                                                        console.debug(`${typeof donprixitrancf}-${donprixitrancf.attributes}`, console.memory);
                                                        if (Object.entries(donprixitrancf).length >= 1) {
                                                            for (let key in Object.entries(donprixitrancf)) 
                                                            {
                                                                document.querySelector('#prix_axetransitcf').value = `${prixtracf}`;
                                                                document.querySelector('#catetransitcf').value = `${donprixitrancf[key].categori}`;
                                                                document.querySelector('#gidtranscf').value =  `${donprixitrancf[key].gareidentif}`;
                                                                document.querySelector('#nomitintranscf1').value = `${donprixitrancf[key].nom_ligne}`; 
                                                                document.querySelector('#ligntranscf1').value = `${donprixitrancf[key].ident_ligne}`;
                                                            }
                                                        }
                                                    };
                                                    httpPrixittransitecf.setRequestHeader('Content-Type', 'application/json');
                                                    httpPrixittransitecf.send();

                                                    const httpRequetteitracf = new XMLHttpRequest();
                                            
                                                    httpRequetteitracf.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetrascf}/${dbitracf}/${fnitracf}`, true);
                                                    httpRequetteitracf.onload = () => {
                                                        const dattaitracf = JSON.parse(httpRequetteitracf.responseText);
                                                        console.debug(`${typeof dattaitracf} - ${dattaitracf.attributes}`, console.memory);
                                                        if (Object.entries(dattaitracf).length >= 1) {
                                                            for (let key in Object.entries(dattaitracf)) {
                                                                
                                                                let opt = document.createElement('option');
                                                                opt.value = `${dattaitracf[key].siege_num}`;
                                                                opt.innerHTML = `${dattaitracf[key].siege_num}`;
                                                                document.querySelector('#psiegesitinescf').add(opt);
                                                                
                                                            }
                                                            
                                                        } else {
                                                            document.querySelector('#psiegesitinescf').options.length = 1;
                                                        }
                                                    };
                                                    httpRequetteitracf.setRequestHeader('Content-Type', 'application/json');
                                                    httpRequetteitracf.send();
                                                };

                                                let progsiegescf1 = document.querySelector('#psiegesitinescf');
                                                if (progsiegescf1 !== null) 
                                                {
                                                    progsiegescf1.onchange = () => 
                                                    {

                                                        const  gareidentiftranscf2 = document.querySelector('#gidtranscf').value;
                                                            const httpsousgarecf1 = new XMLHttpRequest();
                                                            httpsousgarecf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftranscf2}`, true);
                                                            httpsousgarecf1.onload = () => 
                                                            {
                                                                const donsousgcf1 = JSON.parse(httpsousgarecf1.responseText);
                                                                console.debug(`${typeof donsousgcf1}-${donsousgcf1.attributes}`, console.memory);
                                                                if (Object.entries(donsousgcf1).length >= 1) {
                                                                    for (let key in Object.entries(donsousgcf1)) 
                                                                    {
                                                                        let opt = document.createElement('option');
                                                                        opt.value = `${donsousgcf1[key].idsousgare}`;
                                                                        opt.innerHTML = `${donsousgcf1[key].nomsousgare}`;
                                                                        document.querySelector('#transitedepargarecf2').add(opt);
            
                                                                    }
                                                                }
                                                            };
                                                            httpsousgarecf1.setRequestHeader('Content-Type', 'application/json');
                                                            httpsousgarecf1.send();
                                                         const transselitinecf1 = document.querySelector('#hdepartitinecf')
                                                        .options[document.querySelector('#hdepartitinecf').options.selectedIndex].value;
                                                        var post_transcf1 = transselitinecf1.split('/');
                                                        var itinetrascf1 = post_transcf1[0];
                                            
                                                        

                                                        let httpSiegescf1;
                                                        httpSiegescf1 = new XMLHttpRequest();
                                                        const sigscf1 = document.querySelector('#psiegesitinescf')
                                                        .options[document.querySelector('#psiegesitinescf').options.selectedIndex].value;

                                                        httpSiegescf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetrascf1}/${sigscf1}`, true);
                                                        httpSiegescf1.onload = () => 
                                                        {
                                                            const donsgecf1 = JSON.parse(httpSiegescf1.responseText);
                                                            console.debug(`${typeof donsgecf1} - ${donsgecf1.attributes}`, console.memory);
                                                            if(donsgecf1 == '')
                                                            {
                                                                let httpSiegscf1;
                                                                httpSiegscf1 = new XMLHttpRequest();

                                                                httpSiegscf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetrascf1}/${sigscf1}`, true);
                                                                httpSiegscf1.onload = () => 
                                                                {
                                                                    const dongcf1 = JSON.parse(httpSiegscf1.responseText);
                                                                    document.querySelector('#messconf').style.display = 'none';
                                                                    if (Object.entries(dongcf1).length >= 1)
                                                                        {
                                                                            for (let key in Object.entries(dongcf1)) {
                                                                                document.querySelector('#idtampocf1').value = `${dongcf1[key].idtamp}`;                    
                                                                                document.querySelector('#siegselectcf1').value = `${dongcf1[key].numsieg}`;
                                                                            }
                                                                        }
                                                                };
                                                                httpSiegscf1.setRequestHeader('Content-Type', 'application/json');
                                                                httpSiegscf1.send();
                                                            }
                                                            else {
                                                                document.querySelector('#psiegesitinescf').value = '';     
                                                                if (Object.entries(donsgecf1).length >= 1)
                                                                {
                                                                    for (let key in Object.entries(donsgecf1)) {
                                                                        document.querySelector('#idtampocf1').value = `${donsgecf1[key].idtamp}`;                    
                                                                        document.querySelector('#siegselectcf1').value = `${donsgecf1[key].numsieg}`;
                                                                    }

                                                                }
                                                                document.querySelector('#messconf').style.display = 'block';
                                                                document.querySelector('#erreurMessconf').innerHTML = `Siege déjà utilisé.`;
                                                            }
                                                        };
                                                        httpSiegescf1.setRequestHeader('Content-Type', 'application/json');
                                                        httpSiegescf1.send();

                                                    };
                                                }
                                        }
                                        let progchemincf1 = document.querySelector('#idcheminscf1');
                                        if (progchemincf1 !== null) 
                                        {
                                            progchemincf1.onchange = () => 
                                            {
                                               
                                                const prostranschemincf32 = document.querySelector('#idcheminscf1')
                                                .options[document.querySelector('#idcheminscf1').options.selectedIndex].value;

                                                var post_typgarecf32 = prostranschemincf32.split('-');
                                                var seltypgarecf32 = post_typgarecf32[0];
                                                var typgareselcf31 = post_typgarecf32[1];
                                                
                                                
                                                let httpSiegeschemincf1;
                                                httpSiegeschemincf1 = new XMLHttpRequest();

                                                var datedepartcf = document.querySelector('#actuel').value;
                                                const prostranschemincf1 = document.querySelector('#idcheminscf1')
                                                .options[document.querySelector('#idcheminscf1').options.selectedIndex].value;

                                                httpSiegeschemincf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranschemincf1}/${datedepartcf}`, true);
                                                httpSiegeschemincf1.onload = () => 
                                                {
                                        
                                                    const dongtranschemcf1 = JSON.parse(httpSiegeschemincf1.responseText);
                                                    var __prevCfX = __confPrevFromSelect('#hdepartitinecf');
                                                        __confAppendFilteredCheminOptions('#idcheminsheurcf', dongtranschemcf1, __prevCfX.date, __prevCfX.heure);
                                                };
                                                httpSiegeschemincf1.setRequestHeader('Content-Type', 'application/json');
                                                httpSiegeschemincf1.send();

                                            };
                                              let prochemintracf1 = document.querySelector('#idcheminsheurcf');
                                            if (prochemintracf1 !== null)
                                                prochemintracf1.onchange = () => 
                                                {  
                                                    const httpPrixittransitecf1 = new XMLHttpRequest();
                                                    const transselitinecf1 = document.querySelector('#idcheminsheurcf')
                                                    .options[document.querySelector('#idcheminsheurcf').options.selectedIndex].value;
                                                    var post_transcf1 = transselitinecf1.split('/');
                                                    var itinetrascf1 = post_transcf1[0];
                                                    var dbitracf1 = post_transcf1[1];
                                                    var fnitracf1 = post_transcf1[2];
                                                    var lhertracf1 = post_transcf1[3];
                                                    var prixtracf1 = post_transcf1[4];

                                                        httpPrixittransitecf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetrascf1}`, true);
                                                        httpPrixittransitecf1.onload = () => 
                                                        {
                                                            const donprixitrancf1 = JSON.parse(httpPrixittransitecf1.responseText);
                                                            if (Object.entries(donprixitrancf1).length >= 1) {
                                                                for (let key in Object.entries(donprixitrancf1)) 
                                                                {
                                                                    document.querySelector('#prix_axetransitcf1').value = `${prixtracf1}`;
                                                                    document.querySelector('#catetransitcf1').value = `${donprixitrancf1[key].categori}`;
                                                                    document.querySelector('#gidtranscf1').value =  `${donprixitrancf1[key].gareidentif}`;
                                                                    document.querySelector('#nomitintranscf2').value = `${donprixitrancf1[key].nom_ligne}`;
                                                                    document.querySelector('#ligntranscf2').value = `${donprixitrancf1[key].ident_ligne}`;
                                                                }
                                                            }
                                                        };
                                                        httpPrixittransitecf1.setRequestHeader('Content-Type', 'application/json');
                                                        httpPrixittransitecf1.send();
                                              
                                                      
                                                       
                                                        const httpRequetteitracf1 = new XMLHttpRequest();
                                                
                                                        httpRequetteitracf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetrascf1}/${dbitracf1}/${fnitracf1}`, true);
                                                        httpRequetteitracf1.onload = () => {
                                                            const dattaitracf1 = JSON.parse(httpRequetteitracf1.responseText);
                                                            console.debug(`${typeof dattaitracf1} - ${dattaitracf1.attributes}`, console.memory);
                                                            if (Object.entries(dattaitracf1).length >= 1) {
                                                                for (let key in Object.entries(dattaitracf1)) {
                                                                    
                                                                    let opte = document.createElement('option');
                                                                    opte.value = `${dattaitracf1[key].siege_num}`;
                                                                    opte.innerHTML = `${dattaitracf1[key].siege_num}`;
                                                                    document.querySelector('#psiegesitinescf1').add(opte);
                                                                    
                                                                }
                                                                
                                                            } else {
                                                                document.querySelector('#psiegesitinescf1').options.length = 1;
                                                            }
                                                        };
                                                        httpRequetteitracf1.setRequestHeader('Content-Type', 'application/json');
                                                        httpRequetteitracf1.send();
                                                };

                                                let progsiegescf2 = document.querySelector('#psiegesitinescf1');
                                                if (progsiegescf2 !== null) 
                                                {
                                                    progsiegescf2.onchange = () => 
                                                    {
                                                            const transselitinecf2 = document.querySelector('#idcheminsheurcf')
                                                        .options[document.querySelector('#idcheminsheurcf').options.selectedIndex].value;
                                                        var post_transcf2 = transselitinecf2.split('/');
                                                        var itinetrascf2 = post_transcf2[0];
                                                            
                                                            const gareidentiftranscf4 = document.querySelector('#gidtranscf1').value;
                                                            const httpsousgarecf4 = new XMLHttpRequest();
                                                            httpsousgarecf4.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftranscf4}`, true);
                                                            httpsousgarecf4.onload = () => 
                                                            {
                                                                const donsousgcf4 = JSON.parse(httpsousgarecf4.responseText);
                                                                console.debug(`${typeof donsousgcf4}-${donsousgcf4.attributes}`, console.memory);
                                                                if (Object.entries(donsousgcf4).length >= 1) {
                                                                    for (let key in Object.entries(donsousgcf4)) 
                                                                    {
                                                                        let opt = document.createElement('option');
                                                                        opt.value = `${donsousgcf4[key].idsousgare}`;
                                                                        opt.innerHTML = `${donsousgcf4[key].nomsousgare}`;
                                                                        document.querySelector('#transitedepargarecf3').add(opt);
            
                                                                    }
                                                                }
                                                            };

                                                            httpsousgarecf4.setRequestHeader('Content-Type', 'application/json');
                                                            httpsousgarecf4.send();

                                                        let httpSiegescf2;
                                                        httpSiegescf2 = new XMLHttpRequest();
                                                        const sigscf2 = document.querySelector('#psiegesitinescf1')
                                                        .options[document.querySelector('#psiegesitinescf1').options.selectedIndex].value;

                                                        httpSiegescf2.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetrascf2}/${sigscf2}`, true);
                                                        httpSiegescf2.onload = () => 
                                                        {
                                                            const donsgecf2 = JSON.parse(httpSiegescf2.responseText);
                                                            if(donsgecf2 == '')
                                                            {
                                                                let httpSiegscf2;
                                                                httpSiegscf2 = new XMLHttpRequest();

                                                                httpSiegscf2.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetrascf2}/${sigscf2}`, true);
                                                                httpSiegscf2.onload = () => 
                                                                {
                                                                    const dongcf2 = JSON.parse(httpSiegscf2.responseText);
                                                                    document.querySelector('#messconf').style.display = 'none';
                                                                    if (Object.entries(dongcf2).length >= 1)
                                                                        {
                                                                            for (let key in Object.entries(dongcf2)) {
                                                                                document.querySelector('#idtampocf2').value = `${dongcf2[key].idtamp}`;                    
                                                                                document.querySelector('#siegselectcf2').value = `${dongcf2[key].numsieg}`;
                                                                            }
                                                                        }
                                                                };
                                                                httpSiegscf2.setRequestHeader('Content-Type', 'application/json');
                                                                httpSiegscf2.send();
                                                            }
                                                            else {
                                                                document.querySelector('#psiegesitinescf1').value = '';     
                                                                if (Object.entries(donsgecf2).length >= 1)
                                                                {
                                                                    for (let key in Object.entries(donsgecf2)) {
                                                                        document.querySelector('#idtampocf2').value = `${donsgecf2[key].idtamp}`;                    
                                                                        document.querySelector('#siegselectcf2').value = `${donsgecf2[key].numsieg}`;
                                                                    }

                                                                }
                                                                document.querySelector('#messconf').style.display = 'block';
                                                                document.querySelector('#erreurMessconf').innerHTML = `Siege déjà utilisé.`;                                                                   
                                                            }
                                                        };
                                                        httpSiegescf2.setRequestHeader('Content-Type', 'application/json');
                                                        httpSiegescf2.send();

                                                    };
                                                }
                                        }               
                                    }

                                    //troisieme itineraire
                                    if(i === 4)
                                    {
                                        __confSetCheminLigneOption('#idcheminscf', donitinescf[1].code_itineraires, donitinescf[1].nom_itineraires);


                                        __confSetCheminLigneOption('#idcheminscf1', donitinescf[2].code_itineraires, donitinescf[2].nom_itineraires);

                                        __confSetCheminLigneOption('#idcheminscf2', donitinescf[3].code_itineraires, donitinescf[3].nom_itineraires);

                                        __confFillLigne1Locked(donitinescf[0]);
                                       
                                        document.querySelector('#itinecodescf').value = `${donitinescf[0].id_lignes}`;
                                        document.querySelector('#idcompgcf').value = `${donitinescf[0].id_compaga}`;
                                        document.querySelector('#idcompgcf1').value = `${donitinescf[1].id_compaga}`;
                                        document.querySelector('#idcompgcf2').value = `${donitinescf[2].id_compaga}`;
                                        document.querySelector('#idcompgcf3').value = `${donitinescf[3].id_compaga}`;
                                        var typgarecf1 = document.querySelector('#itinecodecf').value;
                                        var post_typgarecf1 = typgarecf1.split('-');
                                        var seltypgarecf1 = (donitinescf[0] && (donitinescf[0].gaexp_lg || donitinescf[0].code_gaexp)) ? (donitinescf[0].gaexp_lg || donitinescf[0].code_gaexp) : post_typgarecf1[0];
                                        var typgareselcf = post_typgarecf1[1];
                                            let httptypequartcf1;
                                            httptypequartcf1 = new XMLHttpRequest();
                                            
                                            httptypequartcf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgareselcf}`, true);
                                            httptypequartcf1.onload = () => 
                                            {
                                                const donquacf1 = JSON.parse(httptypequartcf1.responseText);
                                                if (donquacf1 == '') {
                                                    document.querySelector('#quartiercf1').options.length = 1;
                                                }
                                                else{
                                                    if (Object.entries(donquacf1).length >= 1) {
                                                                    
                                                        for (let key in Object.entries(donquacf1)) {
                                                            let optq = document.createElement('option');
                                                            optq.value = `${donquacf1[key].nom_quartier}`;
                                                            optq.innerHTML = `${donquacf1[key].nom_quartier}`;
                                                            document.querySelector('#quartiercf1').add(optq);
                                                        }
                                                    } else {
                                                        document.querySelector('#quartiercf1').options.length = 1;
                                                    }
                                                }
                                                

                                            };
                                            httptypequartcf1.setRequestHeader('Content-Type', 'application/json');
                                            httptypequartcf1.send();


                                        let hrdepartinecf1 = document.querySelector('#heured');
                                        if (hrdepartinecf1 !== null) {
                                            hrdepartinecf1.onchange = () => {
                                                __confOnHeureTransit1Change(seltypgarecf1);
                                            };
                                            __confLoadHeuresJambe1AndTrigger((document.querySelector('#itinecodecf') && document.querySelector('#itinecodecf').value) || '', document.querySelector('#actuel').value, seltypgarecf1);
                                    
                                        }
                                        let progsiegestranscf = document.querySelector('#depsieg');
                                        if (progsiegestranscf !== null) {
                                            progsiegestranscf.onchange = () => 
                                            {

                                                const gareidentiftranscf1 = document.querySelector('#deplignetranscf').value;
                                                    const httpsousgarecf = new XMLHttpRequest();
                                                    __confFillTransitDepart('#transitedepargarecf1', gareidentiftranscf1);
                                                let httpSiegestranscf1;
                                                httpSiegestranscf1 = new XMLHttpRequest();
                                                const sigstranscf = document.querySelector('#depsieg')
                                                .options[document.querySelector('#depsieg').options.selectedIndex].value;
                                                const prostranscf = document.querySelector('#programtranscf').value;

                                                httpSiegestranscf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${prostranscf}/${sigstranscf}`, true);
                                                httpSiegestranscf1.onload = () => 
                                                {
                                                    const donsgetranscf = JSON.parse(httpSiegestranscf1.responseText);
                                                    console.debug(`${typeof donsgetranscf} - ${donsgetranscf.attributes}`, console.memory);
                                                    if(donsgetranscf == '')
                                                    {
                                                        let httpSiegstranscf;
                                                        httpSiegstranscf = new XMLHttpRequest();

                                                        httpSiegstranscf.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${prostranscf}/${sigstranscf}`, true);
                                                        httpSiegstranscf.onload = () => 
                                                        {
                                                            const dongtranscf = JSON.parse(httpSiegstranscf.responseText);
                                                            document.querySelector('#messconf').style.display = 'none';
                                                            if (Object.entries(dongtranscf).length >= 1)
                                                                {
                                                                    for (let key in Object.entries(dongtranscf)) {
                                                                        document.querySelector('#idtampotranscf').value = `${dongtranscf[key].idtamp}`;                    
                                                                        document.querySelector('#siegselecttranscf').value = `${dongtranscf[key].numsieg}`;
                                                                    }
                                                                }
                                                        };
                                                        httpSiegstranscf.setRequestHeader('Content-Type', 'application/json');
                                                        httpSiegstranscf.send();
                                                    }
                                                    else 
                                                    {
                                                        document.querySelector('#depsieg').value = '';     
                                                        if (Object.entries(donsgetranscf).length >= 1)
                                                        {
                                                            for (let key in Object.entries(donsgetranscf)) {
                                                                document.querySelector('#idtampotranscf').value = `${donsgetranscf[key].idtamp}`;                    
                                                                document.querySelector('#siegselecttranscf').value = `${donsgetranscf[key].numsieg}`;
                                                            }

                                                        }
                                                        document.querySelector('#messconf').style.display = 'block';
                                                        document.querySelector('#erreurMessconf').innerHTML = `Siege déjà utilisé.`; 
                                                    }
                                                };
                                                httpSiegestranscf1.setRequestHeader('Content-Type', 'application/json');
                                                httpSiegestranscf1.send();
                                            };
                                        }
                                        //premier transite
                                        let progchemincf = document.querySelector('#idcheminscf');
                                        if (progchemincf !== null) 
                                        {
                                            progchemincf.onchange = () => 
                                            {
                                                var datedepartcf = document.querySelector('#actuel').value;
                                                
                                                const prostranschemincf = document.querySelector('#idcheminscf')
                                                .options[document.querySelector('#idcheminscf').options.selectedIndex].value;

                                                var post_typgarecf2 = prostranschemincf.split('-');
                                                var seltypgarecf2 = post_typgarecf2[0];
                                                var typgareselcf1 = post_typgarecf2[1];
                                                let httptypequartcf2;
                                                httptypequartcf2 = new XMLHttpRequest();
                                                
                                                httptypequartcf2.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgareselcf1}`, true);
                                                httptypequartcf2.onload = () => 
                                                {
                                                    const donquacf2 = JSON.parse(httptypequartcf2.responseText);
                                                    if (donquacf2 == '') {
                                                        document.querySelector('#quartiercf2').options.length = 1;
                                                    }
                                                    else{
                                                        if (Object.entries(donquacf2).length >= 1) {
                                                                        
                                                            for (let key in Object.entries(donquacf2)) {
                                                                let optq1 = document.createElement('option');
                                                                optq1.value = `${donquacf2[key].nom_quartier}`;
                                                                optq1.innerHTML = `${donquacf2[key].nom_quartier}`;
                                                                document.querySelector('#quartiercf2').add(optq1);
                                                            }
                                                        } else {
                                                            document.querySelector('#quartiercf2').options.length = 1;
                                                        }
                                                    }
                                                    

                                                };
                                                httptypequartcf2.setRequestHeader('Content-Type', 'application/json');
                                                httptypequartcf2.send();
                                                
                                                let httpSiegeschemincf;
                                                httpSiegeschemincf = new XMLHttpRequest();
                                                
                                                httpSiegeschemincf.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranschemincf}/${datedepartcf}`, true);
                                                httpSiegeschemincf.onload = () => 
                                                {
                                        
                                                    const dongtranschemcf = JSON.parse(httpSiegeschemincf.responseText);
                                                    var __prevCfX = __confPrevFromLeg1();
                                                        __confAppendFilteredCheminOptions('#hdepartitinecf', dongtranschemcf, __prevCfX.date, __prevCfX.heure);
                                                };
                                                httpSiegeschemincf.setRequestHeader('Content-Type', 'application/json');
                                                httpSiegeschemincf.send();

                                            };
                                            let prochemintracf = document.querySelector('#hdepartitinecf');
                                            if (prochemintracf !== null){
                                                prochemintracf.onchange = () => 
                                                {  
                                                    const httpPrixittransitecf = new XMLHttpRequest();
                                                    const transselitinecf = document.querySelector('#hdepartitinecf')
                                                    .options[document.querySelector('#hdepartitinecf').options.selectedIndex].value;
                                                    var post_transcf = transselitinecf.split('/');
                                                    var itinetrascf = post_transcf[0];
                                                    var dbitracf = post_transcf[1];
                                                    var fnitracf = post_transcf[2];
                                                    var lhertracf = post_transcf[3];
                                                    var prixtracf = post_transcf[4];

                                                        httpPrixittransitecf.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetrascf}`, true);
                                                        httpPrixittransitecf.onload = () => 
                                                        {
                                                            const donprixitrancf = JSON.parse(httpPrixittransitecf.responseText);
                                                            console.debug(`${typeof donprixitrancf}-${donprixitrancf.attributes}`, console.memory);
                                                            if (Object.entries(donprixitrancf).length >= 1) {
                                                                for (let key in Object.entries(donprixitrancf)) 
                                                                {
                                                                    document.querySelector('#prix_axetransitcf').value = `${prixtracf}`;
                                                                    document.querySelector('#catetransitcf').value = `${donprixitrancf[key].categori}`;
                                                                    document.querySelector('#gidtranscf').value =  `${donprixitrancf[key].gareidentif}`;
                                                                    document.querySelector('#nomitintranscf1').value = `${donprixitrancf[key].nom_ligne}`;
                                                                    document.querySelector('#ligntranscf1').value = `${donprixitrancf[key].ident_ligne}`;
                                                                }
                                                            }
                                                        };
                                                        httpPrixittransitecf.setRequestHeader('Content-Type', 'application/json');
                                                        httpPrixittransitecf.send();
                                              

                                                        
                                                        const httpRequetteitracf = new XMLHttpRequest();
                                                
                                                            httpRequetteitracf.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetrascf}/${dbitracf}/${fnitracf}`, true);
                                                        httpRequetteitracf.onload = () => {
                                                            const dattaitracf = JSON.parse(httpRequetteitracf.responseText);
                                                            console.debug(`${typeof dattaitracf} - ${dattaitracf.attributes}`, console.memory);
                                                            if (Object.entries(dattaitracf).length >= 1) {
                                                                for (let key in Object.entries(dattaitracf)) {
                                                                    
                                                                    let opt = document.createElement('option');
                                                                    opt.value = `${dattaitracf[key].siege_num}`;
                                                                    opt.innerHTML = `${dattaitracf[key].siege_num}`;
                                                                    document.querySelector('#psiegesitinescf').add(opt);
                                                                    
                                                                }
                                                                
                                                            } else {
                                                                document.querySelector('#psiegesitinescf').options.length = 1;
                                                            }
                                                        };
                                                        httpRequetteitracf.setRequestHeader('Content-Type', 'application/json');
                                                        httpRequetteitracf.send();
                                                };
                                            }
                                            let progsiegescf1 = document.querySelector('#psiegesitinescf');
                                            if (progsiegescf1 !== null) 
                                            {
                                                progsiegescf1.onchange = () => 
                                                {

                                                   const gareidentiftranscf2 = document.querySelector('#gidtranscf').value;
                                                        const httpsousgarecf1 = new XMLHttpRequest();
                                                        httpsousgarecf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftranscf2}`, true);
                                                        httpsousgarecf1.onload = () => 
                                                        {
                                                            const donsousgcf1 = JSON.parse(httpsousgarecf1.responseText);
                                                            console.debug(`${typeof donsousgcf1}-${donsousgcf1.attributes}`, console.memory);
                                                            if (Object.entries(donsousgcf1).length >= 1) {
                                                                for (let key in Object.entries(donsousgcf1)) 
                                                                {
                                                                    let opt = document.createElement('option');
                                                                    opt.value = `${donsousgcf1[key].idsousgare}`;
                                                                    opt.innerHTML = `${donsousgcf1[key].nomsousgare}`;
                                                                    document.querySelector('#transitedepargarecf2').add(opt);
        
                                                                }
                                                            }
                                                        };
                                                        httpsousgarecf1.setRequestHeader('Content-Type', 'application/json');
                                                        httpsousgarecf1.send();
                                                    

                                                    const transselitinecf1 = document.querySelector('#hdepartitinecf')
                                                    .options[document.querySelector('#hdepartitinecf').options.selectedIndex].value;
                                                    var post_transcf1 = transselitinecf1.split('/');
                                                    var itinetrascf1 = post_transcf1[0];
                                        
                                                    let httpSiegescf1;
                                                    httpSiegescf1 = new XMLHttpRequest();
                                                    const sigscf1 = document.querySelector('#psiegesitinescf')
                                                    .options[document.querySelector('#psiegesitinescf').options.selectedIndex].value;
                                                    //const pros1 = document.querySelector('#program').value;

                                                    httpSiegescf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetrascf1}/${sigscf1}`, true);
                                                    httpSiegescf1.onload = () => 
                                                    {
                                                        const donsgecf1 = JSON.parse(httpSiegescf1.responseText);
                                                        console.debug(`${typeof donsgecf1} - ${donsgecf1.attributes}`, console.memory);
                                                        if(donsgecf1 == '')
                                                        {
                                                            let httpSiegscf1;
                                                            httpSiegscf1 = new XMLHttpRequest();

                                                            httpSiegscf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetrascf1}/${sigscf1}`, true);
                                                            httpSiegscf1.onload = () => 
                                                            {
                                                                const dongcf1 = JSON.parse(httpSiegscf1.responseText);
                                                                document.querySelector('#messconf').style.display = 'none';
                                                                if (Object.entries(dongcf1).length >= 1)
                                                                    {
                                                                        for (let key in Object.entries(dongcf1)) {
                                                                            document.querySelector('#idtampocf1').value = `${dongcf1[key].idtamp}`;                    
                                                                            document.querySelector('#siegselectcf1').value = `${dongcf1[key].numsieg}`;
                                                                        }
                                                                    }
                                                            };
                                                            httpSiegscf1.setRequestHeader('Content-Type', 'application/json');
                                                            httpSiegscf1.send();
                                                        }
                                                        else {
                                                            document.querySelector('#psiegesitinescf').value = '';     
                                                            if (Object.entries(donsgecf1).length >= 1)
                                                            {
                                                                for (let key in Object.entries(donsgecf1)) {
                                                                    document.querySelector('#idtampocf1').value = `${donsgecf1[key].idtamp}`;                    
                                                                    document.querySelector('#siegselectcf1').value = `${donsgecf1[key].numsieg}`;
                                                                }

                                                            }
                                                            document.querySelector('#messconf').style.display = 'block';
                                                            document.querySelector('#erreurMessconf').innerHTML = `Siege déjà utilisé.`;                                                                   
                                                        }
                                                    };
                                                    httpSiegescf1.setRequestHeader('Content-Type', 'application/json');
                                                    httpSiegescf1.send();

                                                };
                                            }
                                        }
                                        //deuxieme transite
                                        let progchemincf1 = document.querySelector('#idcheminscf1');
                                        if (progchemincf1 !== null) 
                                        {
                                            progchemincf1.onchange = () => 
                                            {

                                                const prostranschemincf32 = document.querySelector('#idcheminscf1')
                                                .options[document.querySelector('#idcheminscf1').options.selectedIndex].value;

                                                var post_typgarecf32 = prostranschemincf32.split('-');
                                                var seltypgarecf32 = post_typgarecf32[0];
                                                var typgareselcf31 = post_typgarecf32[1];
                                                let httptypequartcf32;
                                                httptypequartcf32 = new XMLHttpRequest();
                                                
                                                httptypequartcf32.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgareselcf31}`, true);
                                                httptypequartcf32.onload = () => 
                                                {
                                                    const donquacf32 = JSON.parse(httptypequartcf32.responseText);
                                                    if (donquacf32 == '') {
                                                        document.querySelector('#quartiercf3').options.length = 1;
                                                    }
                                                    else{
                                                        if (Object.entries(donquacf32).length >= 1) {
                                                                        
                                                            for (let key in Object.entries(donquacf32)) {
                                                                let optq31 = document.createElement('option');
                                                                optq31.value = `${donquacf32[key].nom_quartier}`;
                                                                optq31.innerHTML = `${donquacf32[key].nom_quartier}`;
                                                                document.querySelector('#quartiercf3').add(optq31);
                                                            }
                                                        } else {
                                                            document.querySelector('#quartiercf3').options.length = 1;
                                                        }
                                                    }
                                                    

                                                };
                                                httptypequartcf32.setRequestHeader('Content-Type', 'application/json');
                                                httptypequartcf32.send();
                                                
                                                let httpSiegeschemincf1;
                                                httpSiegeschemincf1 = new XMLHttpRequest();
                                                
                                                var datedepartcf = document.querySelector('#actuel').value;
                                                const prostranschemincf1 = document.querySelector('#idcheminscf1')
                                                .options[document.querySelector('#idcheminscf1').options.selectedIndex].value;

                                                httpSiegeschemincf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranschemincf1}/${datedepartcf}`, true);
                                                httpSiegeschemincf1.onload = () => 
                                                {
                                        
                                                    const dongtranschemcf1 = JSON.parse(httpSiegeschemincf1.responseText);
                                                    var __prevCfX = __confPrevFromSelect('#hdepartitinecf');
                                                        __confAppendFilteredCheminOptions('#idcheminsheurcf', dongtranschemcf1, __prevCfX.date, __prevCfX.heure);
                                                };
                                                httpSiegeschemincf1.setRequestHeader('Content-Type', 'application/json');
                                                httpSiegeschemincf1.send();

                                            };
                                               let prochemintracf1 = document.querySelector('#idcheminsheurcf');
                                            if (prochemintracf1 !== null)
                                                prochemintracf1.onchange = () => 
                                                {  
                                                    const httpPrixittransitecf1 = new XMLHttpRequest();
                                                    const transselitinecf1 = document.querySelector('#idcheminsheurcf')
                                                    .options[document.querySelector('#idcheminsheurcf').options.selectedIndex].value;
                                                        var post_transcf1 = transselitinecf1.split('/');
                                                    var itinetrascf1 = post_transcf1[0];
                                                    var dbitracf1 = post_transcf1[1];
                                                    var fnitracf1 = post_transcf1[2];
                                                    var lhertracf1 = post_transcf1[3];
                                                    var prixtracf1 = post_transcf1[4];

                                                        httpPrixittransitecf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetrascf1}`, true);
                                                        httpPrixittransitecf1.onload = () => 
                                                        {
                                                            const donprixitrancf1 = JSON.parse(httpPrixittransitecf1.responseText);
                                                            if (Object.entries(donprixitrancf1).length >= 1) {
                                                                for (let key in Object.entries(donprixitrancf1)) 
                                                                {
                                                                    document.querySelector('#prix_axetransitcf1').value = `${prixtracf1}`;
                                                                    document.querySelector('#catetransitcf1').value = `${donprixitrancf1[key].categori}`;
                                                                    document.querySelector('#gidtranscf1').value =  `${donprixitrancf1[key].gareidentif}`;
                                                                    document.querySelector('#nomitintranscf2').value = `${donprixitrancf1[key].nom_ligne}`;
                                                                    document.querySelector('#ligntranscf2').value = `${donprixitrancf1[key].ident_ligne}`;
                                                                }
                                                            }
                                                        };
                                                        httpPrixittransitecf1.setRequestHeader('Content-Type', 'application/json');
                                                        httpPrixittransitecf1.send();
                                              
                                                        

                                                        const httpRequetteitracf1 = new XMLHttpRequest();
                                                
                                                        httpRequetteitracf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetrascf1}/${dbitracf1}/${fnitracf1}`, true);
                                                        httpRequetteitracf1.onload = () => {
                                                            const dattaitracf1 = JSON.parse(httpRequetteitracf1.responseText);
                                                            console.debug(`${typeof dattaitracf1} - ${dattaitracf1.attributes}`, console.memory);
                                                            if (Object.entries(dattaitracf1).length >= 1) {
                                                                for (let key in Object.entries(dattaitracf1)) {
                                                                    
                                                                    let opt = document.createElement('option');
                                                                    opt.value = `${dattaitracf1[key].siege_num}`;
                                                                    opt.innerHTML = `${dattaitracf1[key].siege_num}`;
                                                                    document.querySelector('#psiegesitinescf1').add(opt);
                                                                    
                                                                }
                                                                
                                                            } else {
                                                                document.querySelector('#psiegesitinescf1').options.length = 1;
                                                            }
                                                        };
                                                        httpRequetteitracf1.setRequestHeader('Content-Type', 'application/json');
                                                        httpRequetteitracf1.send();
                                                };

                                               let progsiegescf2 = document.querySelector('#psiegesitinescf1');
                                                if (progsiegescf2 !== null) 
                                                {
                                                    progsiegescf2.onchange = () => 
                                                    {

                                                       const gareidentiftranscf4 = document.querySelector('#gidtranscf1').value;
                                                        const httpsousgarecf4 = new XMLHttpRequest();
                                                        httpsousgarecf4.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftranscf4}`, true);
                                                        httpsousgarecf4.onload = () => 
                                                        {
                                                            const donsousgcf4 = JSON.parse(httpsousgarecf4.responseText);
                                                            console.debug(`${typeof donsousgcf4}-${donsousgcf4.attributes}`, console.memory);
                                                            if (Object.entries(donsousgcf4).length >= 1) {
                                                                for (let key in Object.entries(donsousgcf4)) 
                                                                {
                                                                    let opt23 = document.createElement('option');
                                                                    opt23.value = `${donsousgcf4[key].idsousgare}`;
                                                                    opt23.innerHTML = `${donsousgcf4[key].nomsousgare}`;
                                                                    document.querySelector('#transitedepargarecf3').add(opt23);
                                                                }
                                                            }
                                                        };
                                                        httpsousgarecf4.setRequestHeader('Content-Type', 'application/json');
                                                        httpsousgarecf4.send();

                                                        const transselitinecf2 = document.querySelector('#idcheminsheurcf')
                                                        .options[document.querySelector('#idcheminsheurcf').options.selectedIndex].value;
                                                        var post_transcf2 = transselitinecf2.split('/');
                                                        var itinetrascf2 = post_transcf2[0];
                                            
                                                        let httpSiegescf2;
                                                        httpSiegescf2 = new XMLHttpRequest();
                                                        const sigscf2 = document.querySelector('#psiegesitinescf1')
                                                        .options[document.querySelector('#psiegesitinescf1').options.selectedIndex].value;

                                                        httpSiegescf2.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetrascf2}/${sigscf2}`, true);
                                                        httpSiegescf2.onload = () => 
                                                        {
                                                            const donsgecf2 = JSON.parse(httpSiegescf2.responseText);
                                                            if(donsgecf2 == '')
                                                            {
                                                                let httpSiegscf2;
                                                                httpSiegscf2 = new XMLHttpRequest();

                                                                httpSiegscf2.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetrascf2}/${sigscf2}`, true);
                                                                httpSiegscf2.onload = () => 
                                                                {
                                                                    const dongcf2 = JSON.parse(httpSiegscf2.responseText);
                                                                    document.querySelector('#messconf').style.display = 'none';
                                                                    if (Object.entries(dongcf2).length >= 1)
                                                                    {
                                                                        for (let key in Object.entries(dongcf2)) {
                                                                            document.querySelector('#idtampocf2').value = `${dongcf2[key].idtamp}`;                    
                                                                            document.querySelector('#siegselectcf2').value = `${dongcf2[key].numsieg}`;
                                                                        }
                                                                    }
                                                                };
                                                                httpSiegscf2.setRequestHeader('Content-Type', 'application/json');
                                                                httpSiegscf2.send();
                                                            }
                                                            else 
                                                            {
                                                                document.querySelector('#psiegesitinescf1').value = '';     
                                                                if (Object.entries(donsgecf2).length >= 1)
                                                                {
                                                                    for (let key in Object.entries(donsgecf2)) {
                                                                        document.querySelector('#idtampocf2').value = `${donsgecf2[key].idtamp}`;                    
                                                                        document.querySelector('#siegselectcf2').value = `${donsgecf2[key].numsieg}`;
                                                                    }

                                                                }
                                                                document.querySelector('#messconf').style.display = 'block';
                                                                document.querySelector('#erreurMessconf').innerHTML = `Siege déjà utilisé.`;                                                                   
                                                            }
                                                        };
                                                        httpSiegescf2.setRequestHeader('Content-Type', 'application/json');
                                                        httpSiegescf2.send();

                                                    };
                                                }
                                        }   

                                        //troisieme transite
                                       let progchemincf2 = document.querySelector('#idcheminscf2');
                                        if (progchemincf2 !== null) 
                                        {
                                            progchemincf2.onchange = () => 
                                            {
                                                const prostranschemincf42 = document.querySelector('#idcheminscf2')
                                                .options[document.querySelector('#idcheminscf2').options.selectedIndex].value;

                                                var post_typgarecf42 = prostranschemincf42.split('-');
                                                var seltypgarecf42 = post_typgarecf42[0];
                                                var typgareselcf41 = post_typgarecf42[1];
                                                
                                                let httpSiegeschemincf2;
                                                httpSiegeschemincf2 = new XMLHttpRequest();
                                                
                                                var datedepartcf = document.querySelector('#actuel').value;
                                                const prostranschemincf2 = document.querySelector('#idcheminscf2')
                                                .options[document.querySelector('#idcheminscf2').options.selectedIndex].value;

                                                httpSiegeschemincf2.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranschemincf2}/${datedepartcf}`, true);
                                                httpSiegeschemincf2.onload = () => 
                                                {
                                        
                                                            const dongtranschemcf2 = JSON.parse(httpSiegeschemincf2.responseText);
                                                            var __prevCfX = __confPrevFromSelect('#idcheminsheurcf');
                                                        __confAppendFilteredCheminOptions('#idcheminsheurcf1', dongtranschemcf2, __prevCfX.date, __prevCfX.heure);
                                                };
                                                httpSiegeschemincf2.setRequestHeader('Content-Type', 'application/json');
                                                httpSiegeschemincf2.send();

                                            };
                                              let prochemintracf2 = document.querySelector('#idcheminsheurcf1');
                                            if (prochemintracf2 !== null)
                                                prochemintracf2.onchange = () => 
                                                {  
                                                    const httpPrixittransitecf2 = new XMLHttpRequest();
                                                        const transselitinecf2 = document.querySelector('#idcheminsheurcf1')
                                                    .options[document.querySelector('#idcheminsheurcf1').options.selectedIndex].value;
                                                        var post_transcf2 = transselitinecf2.split('/');
                                                    var itinetrascf2 = post_transcf2[0];
                                                    var dbitracf2 = post_transcf2[1];
                                                    var fnitracf2 = post_transcf2[2];
                                                    var lhertracf2 = post_transcf2[3];
                                                    var prixtracf2 = post_transcf2[4];

                                                        httpPrixittransitecf2.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetrascf2}`, true);
                                                        httpPrixittransitecf2.onload = () => 
                                                        {
                                                            const donprixitrancf2 = JSON.parse(httpPrixittransitecf2.responseText);
                                                            if (Object.entries(donprixitrancf2).length >= 1) {
                                                                for (let key in Object.entries(donprixitrancf2)) 
                                                                {
                                                                    document.querySelector('#prix_axetransitcf2').value = `${prixtracf2}`;
                                                                    document.querySelector('#catetransitcf2').value = `${donprixitrancf2[key].categori}`;
                                                                    document.querySelector('#gidtranscf2').value =  `${donprixitrancf2[key].gareidentif}`;
                                                                    document.querySelector('#nomitintranscf3').value = `${donprixitrancf2[key].nom_ligne}`;
                                                                    document.querySelector('#ligntranscf3').value = `${donprixitrancf2[key].ident_ligne}`;
                                                                }
                                                            }
                                                        };
                                                        httpPrixittransitecf2.setRequestHeader('Content-Type', 'application/json');
                                                        httpPrixittransitecf2.send();

                                                        const httpRequetteitracf2 = new XMLHttpRequest();
                                                
                                                        httpRequetteitracf2.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetrascf2}/${dbitracf2}/${fnitracf2}`, true);
                                                        httpRequetteitracf2.onload = () => {
                                                            const dattaitracf2 = JSON.parse(httpRequetteitracf2.responseText);
                                                            console.debug(`${typeof dattaitracf2} - ${dattaitracf2.attributes}`, console.memory);
                                                            if (Object.entries(dattaitracf2).length >= 1) {
                                                                for (let key in Object.entries(dattaitracf2)) {
                                                                    
                                                                    let opt = document.createElement('option');
                                                                    opt.value = `${dattaitracf2[key].siege_num}`;
                                                                    opt.innerHTML = `${dattaitracf2[key].siege_num}`;
                                                                    document.querySelector('#psiegesitinescf2').add(opt);
                                                                    
                                                                }
                                                                
                                                            } else {
                                                                document.querySelector('#psiegesitinescf2').options.length = 1;
                                                            }
                                                        };
                                                        httpRequetteitracf2.setRequestHeader('Content-Type', 'application/json');
                                                        httpRequetteitracf2.send();
                                                };

                                               let progsiegescf3 = document.querySelector('#psiegesitinescf2');
                                                if (progsiegescf3 !== null) 
                                                {
                                                    progsiegescf3.onchange = () => 
                                                    {

                                                       const gareidentiftranscf5 = document.querySelector('#gidtranscf2').value;
                                                        const httpsousgarecf5 = new XMLHttpRequest();
                                                        httpsousgarecf5.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftranscf5}`, true);
                                                        httpsousgarecf5.onload = () => 
                                                        {
                                                            const donsousgcf5 = JSON.parse(httpsousgarecf5.responseText);
                                                            console.debug(`${typeof donsousgcf5}-${donsousgcf5.attributes}`, console.memory);
                                                            if (Object.entries(donsousgcf5).length >= 1) {
                                                                for (let key in Object.entries(donsousgcf5)) 
                                                                {
                                                                    let opt = document.createElement('option');
                                                                    opt.value = `${donsousgcf5[key].idsousgare}`;
                                                                    opt.innerHTML = `${donsousgcf5[key].nomsousgare}`;
                                                                    document.querySelector('#transitedepargarecf4').add(opt);
        
                                                                }
                                                            }
                                                        };
                                                        httpsousgarecf5.setRequestHeader('Content-Type', 'application/json');
                                                        httpsousgarecf5.send();
                                                        const transselitinecf3 = document.querySelector('#idcheminsheurcf1')
                                                        .options[document.querySelector('#idcheminsheurcf1').options.selectedIndex].value;
                                                        var post_transcf3 = transselitinecf3.split('/');
                                                        var itinetrascf3 = post_transcf3[0];
                                            
                                                        let httpSiegescf3;
                                                        httpSiegescf3 = new XMLHttpRequest();
                                                        const sigscf3 = document.querySelector('#psiegesitinescf2')
                                                        .options[document.querySelector('#psiegesitinescf2').options.selectedIndex].value;

                                                        httpSiegescf3.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetrascf3}/${sigscf3}`, true);
                                                        httpSiegescf3.onload = () => 
                                                        {
                                                            const donsgecf3 = JSON.parse(httpSiegescf3.responseText);
                                                            if(donsgecf3 == '')
                                                            {
                                                                let httpSiegscf3;
                                                                httpSiegscf3 = new XMLHttpRequest();

                                                                httpSiegscf3.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetrascf3}/${sigscf3}`, true);
                                                                httpSiegscf3.onload = () => 
                                                                {
                                                                    const dongcf3 = JSON.parse(httpSiegscf3.responseText);
                                                                    document.querySelector('#messconf').style.display = 'none';
                                                                    if (Object.entries(dongcf3).length >= 1)
                                                                    {
                                                                        for (let key in Object.entries(dongcf3)) {
                                                                            document.querySelector('#idtampocf3').value = `${dongcf3[key].idtamp}`;                    
                                                                            document.querySelector('#siegselectcf3').value = `${dongcf3[key].numsieg}`;
                                                                        }
                                                                    }
                                                                };
                                                                httpSiegscf3.setRequestHeader('Content-Type', 'application/json');
                                                                httpSiegscf3.send();
                                                            }
                                                            else {
                                                                document.querySelector('#psiegesitinescf2').value = '';     
                                                                if (Object.entries(donsgecf3).length >= 1)
                                                                {
                                                                    for (let key in Object.entries(donsgecf3)) {
                                                                        document.querySelector('#idtampocf3').value = `${donsgecf3[key].idtamp}`;                    
                                                                        document.querySelector('#siegselectcf3').value = `${donsgecf3[key].numsieg}`;
                                                                    }

                                                                }
                                                                document.querySelector('#messconf').style.display = 'block';
                                                                document.querySelector('#erreurMessconf').innerHTML = `Siege déjà utilisé.`;                                                                   
                                                            }
                                                        };
                                                        httpSiegescf3.setRequestHeader('Content-Type', 'application/json');
                                                        httpSiegescf3.send();
                                                        
                                                    };
                                                }

                                        }            
                                    }
                                        
                                }
                            }

                        }; // fin __confApplyTransitLegs

                    // Ne pas ouvrir le transit au changement d'axe — seulement au choix d'une heure sans départ.

                        let heurdeprt = document.querySelector('#heured');
                        if (heurdeprt !== null)
                            heurdeprt.onchange = () => {
                                
                                document.querySelector('#depsieg').options.length = 1;
                                const hOptCf = document.querySelector('#heured').options[document.querySelector('#heured').options.selectedIndex];
                                const selectorp = hOptCf ? hOptCf.value : '';
                                const hasProgHourCf = hOptCf && hOptCf.getAttribute('data-has-programme') === '1';

                                // Heure sans départ → correspondances (modèle vente guichet).
                                if (selectorp && !hasProgHourCf) {
                                    var messElCf = document.querySelector('#messconf');
                                    var errElCf = document.querySelector('#erreurMessconf');
                                    if (window.__confHasTransit) {
                                        if (messElCf) messElCf.style.display = 'block';
                                        if (errElCf) errElCf.innerHTML = 'Pas de départ à cette heure — correspondances proposées.';
                                        __confRequestTransitLegs(heureaxep, dateactuel, __confDepSousGare(), true);
                                    } else {
                                        __confShowDirectHourUi();
                                        if (messElCf) messElCf.style.display = 'block';
                                        if (errElCf) errElCf.innerHTML = 'Aucun départ ni correspondance pour cette heure.';
                                    }
                                    return;
                                }

                                // Heure avec départ : flux confirm direct (siegdispo) inchangé.
                                __confShowDirectHourUi();
                                if (document.querySelector('#messconf')) document.querySelector('#messconf').style.display = 'none';
                                const Requeste = new XMLHttpRequest();
                                var selectorp1 = selectorp.split('/');
                                var selectorp2 = selectorp1[0];
                                var selectorp3 = selectorp1[1];
                                Requeste.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/siegdispo/${selectorp2}`, true);
                                Requeste.onload = () => {
                                    const datasgc = JSON.parse(Requeste.responseText);
                                    if (Object.entries(datasgc).length >= 1) {
                                        for (let key in Object.entries(datasgc)) {
                                            
                                            document.querySelector('#caissepvend_').value = `${datasgc[key].intervalle1}`;
                                            document.querySelector('#caissedpvend_').value = `${datasgc[key].intervalle2}`;
                                            document.querySelector('#directid').value = `${datasgc[key].nom_ligne}`;
                                            document.querySelector('#confheure').value = `${datasgc[key].heure}`;
                                            document.querySelector('#gareid_dep').value = `${datasgc[key].gaexp_lg}`;
                                            document.querySelector('#dateconfirme').value = `${datasgc[key].date_progr}`;
                                            document.querySelector('#catconfirme').value = `${datasgc[key].categori}`;
                                            document.querySelector('#lignehconf').value = `${datasgc[key].id_ligneheure}`;
                                            document.querySelector('#programconf').value = `${datasgc[key].code_progr}`;
                                        }
                                    } 
                                    const Requestbis = new XMLHttpRequest();
                                            const pldebut = document.querySelector('#caissepvend_').value;
                                            const plfin = document.querySelector('#caissedpvend_').value;
                                            const cfdir = document.querySelector('#directid').value;
                                            const hconfir = document.querySelector('#confheure').value;
                                            const dconfirme = document.querySelector('#dateconfirme').value;
                                    Requestbis.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${selectorp2}/${dconfirme}/${cfdir}/${hconfir}/${pldebut}/${plfin}`, true);
                                    Requestbis.onload = () => {
                                        const datasgcbis = JSON.parse(Requestbis.responseText);
                                        if (Object.entries(datasgcbis).length >= 1) {
                                            for (let key in Object.entries(datasgcbis)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${datasgcbis[key].siege_num}`;
                                                opt.innerHTML = `${datasgcbis[key].siege_num}`;
                                                document.querySelector('#depsieg').add(opt);
                                            }
                                        } else {
                                            document.querySelector('#depsieg').options.length = 1;
                                        }
                                    };
                                    Requestbis.setRequestHeader('Content-Type', 'application/json');
                                    Requestbis.send();
                                };
                                Requeste.setRequestHeader('Content-Type', 'application/json');
                                Requeste.send();
                            };
                };
                Requests.setRequestHeader('Content-Type', 'application/json');
                Requests.send();
        };
        __confBindDateReload(axeselect);
        
        let depsiegconf = document.querySelector('#depsieg');
        if (depsiegconf !== null)
        depsiegconf.onchange = () => {
                
                let Requestsiegevenduconf;
                
                if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                    Requestsiegevenduconf = new XMLHttpRequest();
                } else if (window.ActiveXObject) { // IE 6 and older
                    Requestsiegevenduconf = new ActiveXObject("Microsoft.XMLHTTP");
                }
                
                const dp_progconf = document.querySelector('#programconf').value;
                const dp_siegeconf = document.querySelector('#depsieg').options[document.querySelector('#depsieg').options.selectedIndex].value;
                Requestsiegevenduconf.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${dp_progconf}/${dp_siegeconf}`, true);
                Requestsiegevenduconf.onload = () => 
                {
                    
                        const confdonsieg = JSON.parse(Requestsiegevenduconf.responseText);
                        if (confdonsieg == '')
                                {
                                    let httpSiegsconf;
                                    httpSiegsconf = new XMLHttpRequest();

                                    httpSiegsconf.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${dp_progconf}/${dp_siegeconf}`, true);
                                    httpSiegsconf.onload = () => 
                                    {
                                        const dongconf= JSON.parse(httpSiegsconf.responseText);
                                        document.querySelector('#messconf').style.display = 'none';
                                        if (Object.entries(dongconf).length >= 1)
                                        {
                                            for (let key in Object.entries(dongconf)) {
                                                document.querySelector('#idtampoconf').value = `${dongconf[key].idtamp}`;                    
                                                document.querySelector('#siegselectconf').value = `${dongconf[key].numsieg}`;
                                            }
                                        }
                                    };
                                    httpSiegsconf.setRequestHeader('Content-Type', 'application/json');
                                    httpSiegsconf.send();
                                }
                                else {
                                    document.querySelector('#depsieg').value = '';     
                                    if (Object.entries(confdonsieg).length >= 1)
                                    {
                                        for (let key in Object.entries(confdonsieg)) {
                                            document.querySelector('#idtampoconf').value = `${confdonsieg[key].idtamp}`;                    
                                            document.querySelector('#siegselectconf').value = `${confdonsieg[key].numsieg}`;
                                        }

                                    }
                                    document.querySelector('#messconf').style.display = 'block';
                                    document.querySelector('#erreurMessconf').innerHTML = `Siege déjà utilisé.`; 
                                }
                };
                Requestsiegevenduconf.setRequestHeader('content-Type', 'text/json');
                Requestsiegevenduconf.send();
            };
        //bouton annuler
        butoncliconf = document.querySelector('#confreset');
        if (butoncliconf !== null) {
            butoncliconf.onclick = () => 
            {
                let httpSiegeselectconf;
                httpSiegeselectconf = new XMLHttpRequest();
                const siegselectconf = document.querySelector('#siegselectconf').value;
                const idtapconf = document.querySelector('#idtampoconf').value;
                httpSiegeselectconf.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtapconf}/${siegselectconf}`, true);
                httpSiegeselectconf.onload = () => 
                {
                    const donselectconf = JSON.parse(httpSiegeselectconf.responseText);
                    console.debug(`${typeof donselectconf} - ${donselectconf.attributes}`, console.memory);
                    document.querySelector('#messconf').style.display = 'none';
                };
                httpSiegeselectconf.setRequestHeader('Content-Type', 'application/json');
                httpSiegeselectconf.send();
            };
        }
        //recherche d'information du client depart principal
        let infcontact = document.querySelector('#pascontactpconf');
        if (infcontact !== null)
        infcontact.onkeyup = () => {
                let httpInfosrequest;
                if (window.XMLHttpRequest) {
                    httpInfosrequest = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfosrequest = new ActiveXObject("Microsoft.XMLHTTP");
                }
                var verifict = document.querySelector('#pascontactpconf').value;
                httpInfosrequest.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifinfos/${verifict}`, true);
                httpInfosrequest.onload = () => {
                    const infosreq = JSON.parse(httpInfosrequest.responseText);
                    if (infosreq == null) {
                        document.querySelector('#pasnompconf').value = "";
                        document.querySelector('#pasprenompconf').value = "";
                        document.querySelector('#pascnibpconf').value = "";
                        document.querySelector('#pasdatepconf').value = "";
                        document.querySelector('#delivrelieu').value = "";
                        document.querySelector('#clientconfirmeid').value = "";
                    } else {
                        if (Object.entries(infosreq).length > 1) {
                            
                            if (infosreq.contact_client == verifict) {
                                document.querySelector('#pasnompconf').value = `${infosreq.nom_client}`;
                                document.querySelector('#pasprenompconf').value = `${infosreq.prenom_client}`;
                                document.querySelector('#pascnibpconf').value = `${infosreq.num_CNIB}`;
                                document.querySelector('#pasdatepconf').value = `${infosreq.date_delivre}`;
                                document.querySelector('#delivrelieu').value = `${infosreq.lieu_delivre}`;
                                document.querySelector('#clientconfirmeid').value = `${infosreq.id_client}`;

                                document.querySelector('#pasnompconfcp').value = `${infosreq.nom_client}`;
                                document.querySelector('#pasprenompconfcp').value = `${infosreq.prenom_client}`;
                                document.querySelector('#pascnibpconfcp').value = `${infosreq.num_CNIB}`;
                                document.querySelector('#pasdatepconfcp').value = `${infosreq.date_delivre}`;
                                document.querySelector('#lieucnibconf').value = `${infosreq.lieu_delivre}`;
                            } else {
                                document.querySelector('#pasnompconf').value = "";
                                document.querySelector('#pasprenompconf').value = "";
                                document.querySelector('#pascnibpconf').value = "";
                                document.querySelector('#pasdatepconf').value = "";
                                document.querySelector('#delivrelieu').value = "";
                                document.querySelector('#clientconfirmeid').value = "";
                            }
                        }
                    }
                };
                httpInfosrequest.setRequestHeader('Content-Type', 'application/json');
                httpInfosrequest.send();
        };
        e.addEventListener('click', function () {
            __confResetUi();
            let confForm = document.querySelector('#confForm');
            if (confForm) confForm.setAttribute('action', `${APP_ROOT}/Confirmation/confirme/${e.dataset.cle_compagnie}`);
        });
    })
});