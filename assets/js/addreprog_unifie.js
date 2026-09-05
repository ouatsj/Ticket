document.addEventListener('DOMContentLoaded', () => {
    window.__reprogState = {
        rows: [],
        hasTransit: false,
        transitHours: [],
        chemins: [],
        etapes: [],
        segData: {},
        mode: 'direct', // direct | transit
        gaexp: '',
        gadest: '',
        axe: '',
        sgid: '0',
        prix: '',
        prix2: '',
        exclude: '',
        tarif: '1',
        isTransitTicket: false,
        lookup1Done: false,
        lookup2Done: false,
        tamponcodtr: ''
    };

    function __reprogQ(id) { return document.getElementById(id); }

    function __reprogAllowPrixDiff() {
        var modal = __reprogQ('repro-unifie-0');
        return !!(modal && String(modal.getAttribute('data-allow-prix-diff') || '') === '1');
    }

    function __reprogHhmm(h) {
        if (h == null || h === '') return '';
        var s = String(h).trim();
        var m = s.match(/(\d{1,2}):(\d{2})/);
        if (!m) return s.slice(0, 5);
        return (m[1].length === 1 ? '0' + m[1] : m[1]) + ':' + m[2];
    }

    function __reprogRowsArray(rows) {
        if (!rows) return [];
        if (Array.isArray(rows)) return rows;
        var out = [];
        Object.keys(rows).forEach(function (k) {
            if (rows[k] && typeof rows[k] === 'object') out.push(rows[k]);
        });
        return out;
    }

    function __reprogNormalizeEtapes(etapes) {
        if (!etapes) return [];
        if (Array.isArray(etapes)) return etapes.filter(Boolean);
        return Object.keys(etapes).map(function (k) { return etapes[k]; }).filter(Boolean);
    }

    function __reprogResetSelect(sel, placeholder) {
        if (!sel || !sel.tagName || String(sel.tagName).toUpperCase() !== 'SELECT' || !sel.options) {
            return;
        }
        while (sel.options.length) {
            sel.remove(0);
        }
        var opt = document.createElement('option');
        opt.value = '';
        opt.textContent = placeholder || '—';
        sel.add(opt);
    }

    function __reprogUiCie(idx) { return __reprogQ('reprog_ui_cie_' + idx); }
    function __reprogUiHeure(idx) { return __reprogQ('reprog_ui_heure_' + idx); }
    function __reprogUiSiege(idx) { return __reprogQ('reprog_ui_siege_' + idx); }

    function __reprogXhrGet(url, cb) {
        // Préférer jQuery (timeout fiable) ; sinon XHR + abort manuel.
        if (window.jQuery && typeof jQuery.ajax === 'function') {
            jQuery.ajax({
                url: url,
                method: 'GET',
                dataType: 'json',
                timeout: 12000,
                cache: false
            }).done(function (data) {
                try { cb(data, null); } catch (e1) {}
            }).fail(function (xhr, status) {
                var err = status || 'erreur';
                if (xhr && xhr.status) err = 'HTTP ' + xhr.status + ' / ' + err;
                try { cb(null, err); } catch (e2) {}
            });
            return;
        }
        var http = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
        var done = false;
        var tid = null;
        function finish(data, err) {
            if (done) return;
            done = true;
            if (tid) clearTimeout(tid);
            try { cb(data, err || null); } catch (e2) {}
        }
        try {
            http.open('GET', url, true);
            if ('timeout' in http) http.timeout = 12000;
            tid = setTimeout(function () {
                try { http.abort(); } catch (eA) {}
                finish(null, 'Délai dépassé');
            }, 12000);
            http.onreadystatechange = function () {
                if (http.readyState !== 4) return;
                var data = null;
                var err = null;
                if (http.status && http.status >= 400) {
                    err = 'HTTP ' + http.status;
                } else {
                    try { data = JSON.parse(http.responseText); } catch (e) {
                        err = 'JSON invalide';
                        data = null;
                    }
                }
                finish(data, err);
            };
            http.onerror = function () { finish(null, 'Réseau'); };
            http.send();
        } catch (e3) {
            finish(null, String(e3 && e3.message ? e3.message : e3));
        }
    }

    function __reprogSetPost(progValue, compaga, siege) {
        var postH = __reprogQ('heuredepart_post_unifie');
        var postC = __reprogQ('compgcfunifie');
        var postS = __reprogQ('numsiege_post_unifie');
        if (postH) postH.value = progValue || '';
        if (postC) postC.value = compaga || '';
        if (postS && siege != null) postS.value = siege;
        if (progValue) {
            var parts = String(progValue).split('/');
            if (parts[0] && __reprogQ('programrepunifie')) {
                __reprogQ('programrepunifie').value = parts[0];
            }
        }
    }

    function __reprogClearSegPosts() {
        var modeEl = __reprogQ('reprog_mode_unifie');
        var nbrEl = __reprogQ('reprog_nbr_seg_unifie');
        if (modeEl) modeEl.value = 'direct';
        if (nbrEl) nbrEl.value = '0';
        for (var i = 0; i < 4; i++) {
            ['prog', 'siege', 'compaga', 'cat', 'prix'].forEach(function (k) {
                var el = __reprogQ('reprog_seg_' + k + '_' + i);
                if (el) el.value = '';
            });
        }
    }

    function __reprogSyncSegPost(idx) {
        var heureSel = __reprogUiHeure(idx);
        var cieSel = __reprogUiCie(idx);
        var siegeSel = __reprogUiSiege(idx);
        var seg = window.__reprogState.segData[idx];
        if (!cieSel || cieSel.value === '' || !seg || !heureSel || !heureSel.value) {
            ['prog', 'siege', 'compaga', 'cat', 'prix'].forEach(function (k) {
                var el = __reprogQ('reprog_seg_' + k + '_' + idx);
                if (el) el.value = '';
            });
            seg && (seg.selectedRow = null);
            return null;
        }
        var row = seg.selectedRow;
        if (!row || !row.code_progr) {
            var list = (seg.byCieHour[cieSel.value] && seg.byCieHour[cieSel.value][heureSel.value]) || [];
            row = list[0] || null;
            seg.selectedRow = row;
        }
        if (!row || !row.code_progr) return null;
        var progVal = row.code_progr + '/' + (row.id_ligneheure || '') + '/'
            + (row.typetarif || __reprogSegTarif());
        var elP = __reprogQ('reprog_seg_prog_' + idx);
        var elS = __reprogQ('reprog_seg_siege_' + idx);
        var elC = __reprogQ('reprog_seg_compaga_' + idx);
        var elCat = __reprogQ('reprog_seg_cat_' + idx);
        var elPx = __reprogQ('reprog_seg_prix_' + idx);
        if (elP) elP.value = progVal;
        if (elS) elS.value = siegeSel ? (siegeSel.value || '') : '';
        if (elC) {
            elC.value = (row.id_compaga || row.cle_compagnie_arrivee
                || __reprogRowCieKey(row) || cieSel.value || '');
        }
        if (elCat) elCat.value = row.categori || '';
        if (elPx) elPx.value = (row.prix != null ? row.prix : '');
        return {
            prog: progVal,
            siege: elS ? elS.value : '',
            compaga: elC ? elC.value : '',
            prix: __reprogNumPrix(row.prix),
            row: row
        };
    }

    function __reprogNumPrix(v) {
        if (v == null || v === '') return 0;
        var n = parseFloat(String(v).replace(/\s/g, '').replace(',', '.'));
        return isNaN(n) ? 0 : n;
    }

    function __reprogPrixRef() {
        var st = window.__reprogState;
        var total = __reprogNumPrix(st.prix);
        if (st.isTransitTicket && st.lookup2Done) {
            total += __reprogNumPrix(st.prix2);
        }
        return Math.round(total * 100) / 100;
    }

    function __reprogUpdatePrixSum() {
        var box = __reprogQ('corr_prix_sum_unifie');
        if (!box) return;
        var etapes = window.__reprogState.etapes || [];
        if (etapes.length < 2) {
            box.style.display = 'none';
            return;
        }
        var sum = 0;
        var complete = true;
        for (var i = 0; i < etapes.length; i++) {
            var synced = __reprogSyncSegPost(i);
            if (!synced || !synced.row) {
                complete = false;
                continue;
            }
            sum += synced.prix;
        }
        sum = Math.round(sum * 100) / 100;
        var ref = __reprogPrixRef();
        box.style.display = 'block';
        if (!complete) {
            box.className = 'small mb-0 mt-2 text-muted';
            box.textContent = 'Prix segments (en cours) : ' + sum + ' — ticket vérifié : ' + ref;
            return;
        }
        var ok = Math.abs(sum - ref) < 0.05;
        var allowDiff = __reprogAllowPrixDiff();
        if (allowDiff && !ok) {
            box.className = 'small mb-0 mt-2 text-warning';
            box.textContent = 'Prix différent autorisé (admin/chef) : somme '
                + sum + ' ≠ ticket ' + ref;
            return true;
        }
        box.className = 'small mb-0 mt-2 ' + (ok ? 'text-success' : 'text-danger');
        box.textContent = ok
            ? ('Prix OK : somme correspondances ' + sum + ' = ticket ' + ref)
            : ('Prix incorrect : somme ' + sum + ' ≠ ticket vérifié ' + ref);
        return ok;
    }

    function __reprogPrepareTransitSubmit() {
        var etapes = window.__reprogState.etapes || [];
        var n = etapes.length;
        if (n < 2) return { ok: false, msg: 'Itinéraire invalide.' };
        if (window.__reprogState.isTransitTicket && !window.__reprogState.lookup2Done) {
            return { ok: false, msg: 'Vérifiez le 2ᵉ code du ticket transit avant de reporter.' };
        }
        __reprogQ('reprog_mode_unifie').value = 'transit';
        __reprogQ('reprog_nbr_seg_unifie').value = String(n);
        var first = null;
        var sum = 0;
        for (var i = 0; i < n; i++) {
            var synced = __reprogSyncSegPost(i);
            if (!synced || !synced.prog || !synced.siege) {
                return {
                    ok: false,
                    msg: 'Complétez compagnie, heure et siège pour la correspondance ' + (i + 1) + '.'
                };
            }
            sum += synced.prix;
            if (i === 0) first = synced;
        }
        sum = Math.round(sum * 100) / 100;
        var ref = __reprogPrixRef();
        if (!__reprogAllowPrixDiff() && Math.abs(sum - ref) >= 0.05) {
            return {
                ok: false,
                msg: 'La somme des prix des correspondances (' + sum
                    + ') doit être égale au prix du ticket vérifié (' + ref + ').'
            };
        }
        var refEl = __reprogQ('prixventeunifie_ref');
        if (refEl) refEl.value = String(ref);
        var pxEl = __reprogQ('prixventeunifie');
        // Admin/chef avec prix différent : conserver le prix ticket d’origine en ref,
        // chaque jambe garde son prix programme (posté dans reprog_seg_prix_*).
        if (pxEl) pxEl.value = String(ref);
        if (first) {
            __reprogSetPost(first.prog, first.compaga, first.siege);
            if (first.row) {
                __reprogQ('catreprogrammeunifie').value = first.row.categori || '';
            }
        }
        return { ok: true };
    }

    function __reprogResetChoix() {
        __reprogResetSelect(__reprogQ('heuredepartpunifie'), "Choisissez l'heure");
        __reprogResetSelect(__reprogQ('compagniepunifie'), 'Choisissez la compagnie');
        __reprogResetSelect(__reprogQ('numsiegepunifie'), 'Choisissez le siège');
        __reprogHideDirect();
        __reprogHideCorr();
        __reprogSetPost('', '', '');
        __reprogClearSegPosts();
        window.__reprogState.chemins = [];
        window.__reprogState.etapes = [];
        window.__reprogState.segData = {};
        window.__reprogState.mode = 'direct';
    }

    function __reprogHideDirect() {
        var w = __reprogQ('reprog_direct_wrap');
        if (w) w.style.display = 'none';
    }

    function __reprogShowDirect() {
        var w = __reprogQ('reprog_direct_wrap');
        if (w) w.style.display = 'block';
        var c = __reprogQ('corr_unifie_wrap');
        if (c) c.style.display = 'none';
        window.__reprogState.mode = 'direct';
    }

    function __reprogHideCorr() {
        var w = __reprogQ('corr_unifie_wrap');
        if (w) w.style.display = 'none';
        __reprogResetSelect(__reprogQ('corr_unifie_select'), 'Choisissez un itinéraire');
        var segs = __reprogQ('corr_segments_unifie');
        if (segs) segs.innerHTML = '';
        var msg = __reprogQ('corr_unifie_msg');
        if (msg) msg.textContent = '';
    }

    function __reprogShowCorrPanel() {
        var w = __reprogQ('corr_unifie_wrap');
        if (w) w.style.display = 'block';
        window.__reprogState.mode = 'transit';
    }

    function __reprogResetUi() {
        ['nomclpunifie', 'prenomclpunifie', 'contactclpunifie', 'refclpunifie',
            'directionclpunifie', 'codeclpunifie', 'heureclpunifie', 'compagnieclpunifie',
            'prixclpunifie', 'code2clpunifie'
        ].forEach(function (id) {
            var el = __reprogQ(id);
            if (el) {
                el.innerHTML = '';
                if (id === 'code2clpunifie') el.style.display = 'none';
            }
        });
        var infos = __reprogQ('reprog_infos_wrap');
        if (infos) infos.style.display = 'none';
        var wrap = __reprogQ('reprog_choix_wrap');
        if (wrap) wrap.style.display = 'none';
        var c2 = __reprogQ('reprog_code2_wrap');
        if (c2) c2.style.display = 'none';
        ['passerpunifie2', 'codeticketsunifie2', 'codeclient_ticket_unifie2',
            'prixventeunifie2', 'prixventeunifie_ref', 'code_lookup2_unifie'
        ].forEach(function (id) {
            var el = __reprogQ(id);
            if (el) el.value = '';
        });
        var isTr = __reprogQ('reprog_is_transit_ticket');
        if (isTr) isTr.value = '0';
        __reprogResetChoix();
        window.__reprogState.rows = [];
        window.__reprogState.hasTransit = false;
        window.__reprogState.transitHours = [];
        window.__reprogState.prix = '';
        window.__reprogState.prix2 = '';
        window.__reprogState.lookup1Done = false;
        window.__reprogState.lookup2Done = false;
        window.__reprogState.tamponcodtr = '';
        window.__reprogState.isTransitTicket = !!(__reprogQ('mode_transit_ticket_unifie')
            && __reprogQ('mode_transit_ticket_unifie').checked);
    }

    function __reprogCanOpenChoix() {
        var st = window.__reprogState;
        if (!st.lookup1Done) return false;
        if (st.isTransitTicket && !st.lookup2Done) return false;
        return true;
    }

    function __reprogOpenChoixIfReady(dateEl) {
        if (!__reprogCanOpenChoix()) return;
        var ref = __reprogPrixRef();
        var refEl = __reprogQ('prixventeunifie_ref');
        if (refEl) refEl.value = String(ref);
        var px = __reprogQ('prixventeunifie');
        if (px) px.value = String(ref);
        var prixCl = __reprogQ('prixclpunifie');
        if (prixCl) {
            prixCl.textContent = window.__reprogState.isTransitTicket
                ? ('PRIX TOTAL (2 jambes): ' + ref)
                : ('PRIX: ' + ref);
        }

        __reprogQ('reprog_choix_wrap').style.display = 'block';
        var today = __reprogQ('actueldaterepunifie').value || '';
        if (dateEl) {
            dateEl.min = today;
            dateEl.value = today;
        }
        __reprogXhrGet(
            window.location.origin + APP_ROOT
                + '/reprogrammes/heures_unifie/'
                + encodeURIComponent(window.__reprogState.gaexp) + '/'
                + encodeURIComponent(window.__reprogState.gadest) + '/'
                + encodeURIComponent(window.__reprogState.exclude)
                + '?prix=' + encodeURIComponent(String(ref)),
            function (data2) {
                window.__reprogState.rows = __reprogRowsArray(data2);
                __reprogOnDateChange();
            }
        );
    }

    function __reprogFilterByDate(dateYmd) {
        var d = String(dateYmd || '').slice(0, 10);
        return __reprogRowsArray(window.__reprogState.rows).filter(function (r) {
            return r && String(r.date_progr || '').slice(0, 10) === d;
        });
    }

    function __reprogFillHeuresForDate(dateYmd) {
        var sel = __reprogQ('heuredepartpunifie');
        __reprogResetSelect(sel, "Choisissez l'heure");
        __reprogHideDirect();
        __reprogHideCorr();

        var seen = {};
        __reprogFilterByDate(dateYmd).forEach(function (row) {
            var hh = __reprogHhmm(row.heure);
            if (!hh || seen[hh]) return;
            seen[hh] = 1;
            var opt = document.createElement('option');
            opt.value = hh;
            opt.textContent = hh;
            opt.setAttribute('data-has-prog', '1');
            sel.add(opt);
        });

        if (window.__reprogState.hasTransit) {
            __reprogRowsArray(window.__reprogState.transitHours).forEach(function (hr) {
                var hh = __reprogHhmm(hr.heure || hr);
                if (!hh || seen[hh]) return;
                if (hr.has_programme === false || hr.has_programme === 0 || hr.has_programme === '0') {
                    seen[hh] = 1;
                    var optT = document.createElement('option');
                    optT.value = hh;
                    optT.textContent = hh + ' (correspondance)';
                    optT.setAttribute('data-has-prog', '0');
                    sel.add(optT);
                }
            });
        }
    }

    function __reprogFillCompagnies(dateYmd, hhmm, chemins) {
        var sel = __reprogQ('compagniepunifie');
        __reprogResetSelect(sel, 'Choisissez la compagnie');
        __reprogResetSelect(__reprogQ('numsiegepunifie'), 'Choisissez le siège');
        var hh = __reprogHhmm(hhmm);
        var matches = __reprogFilterByDate(dateYmd).filter(function (r) {
            return __reprogHhmm(r.heure) === hh;
        });
        var seen = {};
        matches.forEach(function (row) {
            var cieKey = __reprogRowCieKey(row);
            var key = cieKey + '|' + String(row.code_progr || '');
            if (seen[key]) return;
            seen[key] = 1;
            var opt = document.createElement('option');
            opt.value = row.code_progr + '/' + row.id_ligneheure + '/' + row.typetarif;
            opt.setAttribute('data-compaga', row.id_compaga || '');
            opt.setAttribute('data-cie-key', cieKey);
            opt.setAttribute('data-kind', 'direct');
            opt.setAttribute('data-ligne', row.nom_ligne || '');
            opt.setAttribute('data-heure', __reprogHhmm(row.heure));
            opt.setAttribute('data-date', String(row.date_progr || dateYmd).slice(0, 10));
            opt.textContent = (__reprogCieName(row) || 'Compagnie')
                + ' — direct ' + __reprogHhmm(row.heure);
            sel.add(opt);
        });

        // Compagnies issues des 1ers segments de correspondance (heure sans départ OD).
        var cieSeen = {};
        matches.forEach(function (row) {
            cieSeen[__reprogRowCieKey(row)] = 1;
        });
        __reprogRowsArray(chemins).forEach(function (ch) {
            var etapes = __reprogNormalizeEtapes(ch.etapes || ch.legs);
            if (etapes.length < 2) return;
            var e0 = etapes[0] || {};
            var cieKey = __reprogEtapeCieKey(e0);
            var name = e0.nom_compagnie_arrivee || e0.nom_compagnie || cieKey || 'Correspondance';
            var dedupe = cieKey || name;
            if (!dedupe || cieSeen[dedupe]) return;
            cieSeen[dedupe] = 1;
            var optT = document.createElement('option');
            optT.value = 'corr:' + dedupe;
            optT.setAttribute('data-compaga', cieKey);
            optT.setAttribute('data-cie-key', cieKey);
            optT.setAttribute('data-kind', 'corr');
            optT.textContent = name + ' — correspondance';
            sel.add(optT);
        });

        return matches.length;
    }

    function __reprogCheminsMulti(chemins) {
        return __reprogRowsArray(chemins).filter(function (ch) {
            return __reprogNormalizeEtapes(ch.etapes || ch.legs).length >= 2;
        });
    }

    function __reprogDirectMatchesForCie(dateYmd, hhmm, cieKey, progValue) {
        var hh = __reprogHhmm(hhmm);
        return __reprogFilterByDate(dateYmd).filter(function (r) {
            if (__reprogHhmm(r.heure) !== hh) return false;
            if (progValue && String(r.code_progr + '/' + r.id_ligneheure + '/' + r.typetarif) === String(progValue)) {
                return true;
            }
            if (cieKey && __reprogRowCieKey(r) === String(cieKey)) return true;
            return false;
        });
    }

    function __reprogSetDirectInfo(text) {
        var el = __reprogQ('reprog_direct_info');
        if (el) el.textContent = text || '';
    }

    function __reprogShowDirectExclusive() {
        __reprogHideCorr();
        __reprogShowDirect();
        __reprogQ('reprog_mode_unifie').value = 'direct';
        __reprogQ('reprog_nbr_seg_unifie').value = '0';
        __reprogClearSegPosts();
    }

    function __reprogShowCorrExclusive(chemins, message) {
        __reprogHideDirect();
        __reprogSetDirectInfo('');
        __reprogSetPost('', '', '');
        __reprogFillItineraireSelect(chemins, message);
    }

    function __reprogLoadSiegesDirect(progValue) {
        var siegeSel = __reprogQ('numsiegepunifie');
        __reprogResetSelect(siegeSel, 'Choisissez le siège');
        if (!progValue) return;
        var parts = String(progValue).split('/');
        var selh = parts[0];
        if (!selh) return;
        __reprogQ('programrepunifie').value = selh;

        __reprogXhrGet(window.location.origin + APP_ROOT + '/reprogrammes/siegdispo/' + encodeURIComponent(selh), function (data) {
            var rows = __reprogRowsArray(data);
            if (!rows.length) return;
            var meta = rows[0];
            var i1 = meta.intervalle1;
            var i2 = meta.intervalle2;
            __reprogQ('placevenduunifie').value = i1 != null ? i1 : '';
            __reprogQ('dplacevenduunifie').value = i2 != null ? i2 : '';
            __reprogQ('replignunifie').value = meta.nom_ligne || '';
            __reprogQ('repherunifie').value = meta.heure || '';
            __reprogQ('datereprogrammeunifie').value = meta.date_progr || '';
            __reprogQ('catreprogrammeunifie').value = meta.categori || '';
            __reprogQ('idreplignunifie').value = meta.ligne_id || '';
            if (meta.id_compaga) __reprogQ('compgcfunifie').value = meta.id_compaga;

            // siegdisponibletrans : code + intervalles (évite heure URL-encodée cassée par CI3).
            if (i1 === '' || i1 == null || i2 === '' || i2 == null) {
                return;
            }
            __reprogXhrGet(
                window.location.origin + APP_ROOT + '/programmes/siegdisponibletrans/'
                    + encodeURIComponent(selh) + '/'
                    + encodeURIComponent(i1) + '/'
                    + encodeURIComponent(i2),
                function (dattas) {
                    __reprogRowsArray(dattas).forEach(function (s) {
                        if (!s || s.siege_num == null) return;
                        var optS = document.createElement('option');
                        optS.value = s.siege_num;
                        optS.textContent = s.siege_num;
                        siegeSel.add(optS);
                    });
                }
            );
        });
    }

    function __reprogLoadTransitHours(dateYmd, cb) {
        var st = window.__reprogState;
        if (!st.axe) {
            st.hasTransit = false;
            st.transitHours = [];
            if (cb) cb();
            return;
        }
        var url = window.location.origin + APP_ROOT
            + '/programmes/verifheuresvente/'
            + encodeURIComponent(st.axe) + '/'
            + encodeURIComponent(dateYmd) + '/'
            + encodeURIComponent(st.sgid || '0');
        __reprogXhrGet(url, function (payload) {
            st.hasTransit = !!(payload && payload.has_transit);
            st.transitHours = (payload && payload.heures) ? payload.heures : [];
            if (cb) cb(payload);
        });
    }

    function __reprogFetchChemins(dateYmd, hhmm, after) {
        var st = window.__reprogState;
        var url = window.location.origin + APP_ROOT
            + '/programmes/verifchemins/'
            + encodeURIComponent(st.axe) + '/'
            + encodeURIComponent(dateYmd) + '/'
            + encodeURIComponent(st.sgid || '0') + '/1'
            + '?heure=' + encodeURIComponent(hhmm);
        __reprogXhrGet(url, function (payload) {
            var chemins = [];
            if (payload) {
                if (Array.isArray(payload.chemins)) chemins = payload.chemins;
                else if (Array.isArray(payload)) chemins = payload;
                else if (payload.chemins) chemins = __reprogRowsArray(payload.chemins);
            }
            chemins = chemins.filter(function (c) {
                if (!c) return false;
                if (c.source === 'direct' && !__reprogNormalizeEtapes(c.etapes || c.legs).length) return false;
                return true;
            });
            st.chemins = chemins;
            if (after) after(chemins);
        });
    }

    function __reprogFillItineraireSelect(chemins, message) {
        var sel = __reprogQ('corr_unifie_select');
        var msg = __reprogQ('corr_unifie_msg');
        var segs = __reprogQ('corr_segments_unifie');
        if (segs) segs.innerHTML = '';
        __reprogResetSelect(sel, 'Choisissez un itinéraire');
        if (msg) msg.textContent = message || '';
        chemins.forEach(function (ch, idx) {
            var etapes = __reprogNormalizeEtapes(ch.etapes || ch.legs);
            var label = ch.label || ch.nom || ch.resume || ('Itinéraire ' + (idx + 1));
            if (etapes.length) {
                label = etapes.map(function (e) {
                    return e.nom_itineraires || e.nom_ligne || e.code_itineraires || '';
                }).filter(Boolean).join(' → ') || label;
                label += ' (' + etapes.length + ' segment' + (etapes.length > 1 ? 's' : '') + ')';
            }
            var opt = document.createElement('option');
            opt.value = String(idx);
            opt.textContent = label;
            sel.add(opt);
        });
        __reprogShowCorrPanel();
        if (sel.options.length === 2) {
            sel.selectedIndex = 1;
            sel.dispatchEvent(new Event('change'));
        }
    }

    function __reprogCieName(row) {
        // Compagnie = gare d’arrivée (comme vente / tickets).
        return row.nom_compagnie_arrivee || row.nom_compagnie || row.nom_compagnie_depart || '';
    }

    function __reprogRowCieKey(row) {
        if (!row) return '';
        return String(
            row.id_compaga || row.cle_compagnie_arrivee || row.cle_compagnie
            || row.id_compagd || row.cle_compagnie_depart
            || row.nom_compagnie_arrivee || row.nom_compagnie || ''
        );
    }

    function __reprogCieLabel(row) {
        var n = __reprogCieName(row);
        if (n) return n + (row.categori ? (' / ' + row.categori) : '');
        var k = __reprogRowCieKey(row);
        return (k || 'Compagnie') + (row.categori ? (' / ' + row.categori) : '');
    }

    function __reprogLigneId(etape) {
        return etape.code_itineraires || etape.ligne_id
            || etape.code_ligne || etape.ligne || etape.ident_ligne || '';
    }

    function __reprogEtapeHeure(etape) {
        if (!etape) return '';
        return __reprogHhmm(
            etape.heure || etape._graphe_heure || etape.heure_depart || ''
        );
    }

    function __reprogEtapeCieKey(etape) {
        if (!etape) return '';
        // Compagnie d’arrivée du segment (id_compaga).
        return String(etape.id_compaga || etape.cle_compagnie || etape.id_compagd || '');
    }

    function __reprogEtapeGadest(etape) {
        if (!etape) return '';
        return String(etape.code_gadest || etape.gadest_lg || etape.gadest || '');
    }

    function __reprogFireChange(el) {
        if (!el) return;
        if (typeof el.onchange === 'function') {
            el.onchange();
            return;
        }
        try {
            el.dispatchEvent(new Event('change', { bubbles: true }));
        } catch (e) {
            var ev;
            if (document.createEvent) {
                ev = document.createEvent('HTMLEvents');
                ev.initEvent('change', true, false);
                el.dispatchEvent(ev);
            }
        }
    }

    function __reprogBuildSegments(etapes) {
        var box = __reprogQ('corr_segments_unifie');
        if (!box) return;
        box.innerHTML = '';
        window.__reprogState.etapes = etapes;
        window.__reprogState.segData = {};
        var prixBox = __reprogQ('corr_prix_sum_unifie');
        if (prixBox) {
            prixBox.style.display = 'none';
            prixBox.textContent = '';
        }

        etapes.forEach(function (etape, idx) {
            var ligneId = __reprogLigneId(etape);
            var ligneNom = etape.nom_itineraires || etape.nom_ligne || ligneId || ('Segment ' + (idx + 1));
            var wrap = document.createElement('div');
            wrap.className = 'reprog-seg';
            wrap.id = 'reprog_seg_' + idx;
            // Ligne → Compagnie (auto) → Heure (selon cie) → Siège
            wrap.innerHTML =
                '<h6>Correspondance ' + (idx + 1) + ' — ' + ligneNom + '</h6>'
                + '<div class="form-row">'
                + '<div class="form-group col-md-3 mb-2">'
                + '<label class="small mb-0">Ligne</label>'
                + '<input class="form-control form-control-sm" type="text" id="reprog_seg_ligne_' + idx + '" value="'
                + String(ligneNom).replace(/"/g, '&quot;') + '" readonly>'
                + '</div>'
                + '<div class="form-group col-md-3 mb-2">'
                + '<label class="small mb-0">Compagnie</label>'
                + '<select class="form-control form-control-sm" id="reprog_ui_cie_' + idx + '">'
                + '<option value="">Choisissez</option></select>'
                + '</div>'
                + '<div class="form-group col-md-3 mb-2">'
                + '<label class="small mb-0">Heure disponible</label>'
                + '<select class="form-control form-control-sm" id="reprog_ui_heure_' + idx + '">'
                + '<option value="">Choisissez l\'heure</option></select>'
                + '</div>'
                + '<div class="form-group col-md-3 mb-2">'
                + '<label class="small mb-0">Siège</label>'
                + '<select class="form-control form-control-sm" id="reprog_ui_siege_' + idx + '">'
                + '<option value="">Choisissez le siège</option></select>'
                + '</div>'
                + '</div>'
                + '<p class="text-danger small mb-0" id="reprog_seg_err_' + idx + '" style="display:none"></p>';
            box.appendChild(wrap);

            window.__reprogState.segData[idx] = {
                etape: etape,
                ligneId: ligneId,
                rows: [],
                byCie: {},
                byCieHour: {},
                selectedRow: null
            };

            __reprogLoadSegmentCompanies(idx);
        });
    }

    function __reprogSegErr(idx, msg) {
        var el = __reprogQ('reprog_seg_err_' + idx);
        if (!el) return;
        if (!msg) {
            el.style.display = 'none';
            el.textContent = '';
            return;
        }
        el.style.display = 'block';
        el.textContent = msg;
    }

    function __reprogSegTarif() {
        var st = window.__reprogState;
        var tf = st.tarif != null ? String(st.tarif).trim() : '';
        if (tf === '' || tf === '0') return '1';
        return tf;
    }

    function __reprogIndexSegRows(seg, rows, tarif, fromChemin) {
        rows = __reprogRowsArray(rows);
        if (fromChemin && tarif) {
            var filtered = rows.filter(function (r) {
                if (r.typetarif == null || r.typetarif === '') return true;
                return String(r.typetarif) === String(tarif);
            });
            if (filtered.length) rows = filtered;
        }
        seg.rows = rows;
        seg.byCie = {};
        seg.byCieHour = {};
        rows.forEach(function (r) {
            if (!r || !r.code_progr) return;
            var cie = __reprogRowCieKey(r);
            if (!cie) cie = '_';
            var hh = __reprogHhmm(r.heure);
            if (!hh) return;
            if (!seg.byCie[cie]) seg.byCie[cie] = { key: cie, label: __reprogCieLabel(r), rows: [] };
            seg.byCie[cie].rows.push(r);
            if (!seg.byCieHour[cie]) seg.byCieHour[cie] = {};
            if (!seg.byCieHour[cie][hh]) seg.byCieHour[cie][hh] = [];
            seg.byCieHour[cie][hh].push(r);
        });
        return Object.keys(seg.byCie);
    }

    function __reprogLoadSegmentCompanies(idx) {
        var st = window.__reprogState;
        var seg = st.segData[idx];
        if (!seg || !seg.ligneId) {
            __reprogSegErr(idx, 'Ligne du segment introuvable.');
            return;
        }
        var dateYmd = (__reprogQ('datereprog_unifie') || {}).value || '';
        if (!dateYmd) {
            __reprogSegErr(idx, 'Choisissez d’abord une date de départ.');
            return;
        }
        var tarif = __reprogSegTarif();
        var root = (typeof APP_ROOT !== 'undefined' && APP_ROOT != null) ? APP_ROOT : '';
        var lignePath = encodeURIComponent(String(seg.ligneId));
        var datePath = encodeURIComponent(String(dateYmd));
        var prefCie = __reprogEtapeCieKey(seg.etape);
        var prefGadest = __reprogEtapeGadest(seg.etape);
        var urlSeg = window.location.origin + root
            + '/reprogrammes/seg_progs/' + lignePath + '/' + datePath
            + '/' + encodeURIComponent(tarif);
        var qs = [];
        if (prefCie) qs.push('compaga=' + encodeURIComponent(prefCie));
        if (prefGadest) qs.push('gadest=' + encodeURIComponent(prefGadest));
        if (qs.length) urlSeg += '?' + qs.join('&');

        function fillCompanies(rows, fromChemin) {
            var cieKeys = __reprogIndexSegRows(seg, rows, tarif, fromChemin);
            var cieSel = __reprogUiCie(idx);
            var heureSel = __reprogUiHeure(idx);
            var siegeSel = __reprogUiSiege(idx);
            if (!cieSel) {
                __reprogSegErr(idx, 'Champ compagnie introuvable.');
                return;
            }
            __reprogResetSelect(cieSel, 'Choisissez');
            __reprogResetSelect(heureSel, "Choisissez l'heure");
            __reprogResetSelect(siegeSel, 'Choisissez le siège');
            seg.selectedRow = null;

            if (!cieKeys.length) {
                __reprogSegErr(idx, 'Aucune compagnie / programme pour « '
                    + seg.ligneId + ' » le ' + dateYmd + ' (tarif ' + tarif + ').');
                return;
            }
            __reprogSegErr(idx, '');
            cieKeys.sort().forEach(function (k) {
                var info = seg.byCie[k];
                var opt = document.createElement('option');
                opt.value = k;
                opt.textContent = (info && info.label) ? info.label : k;
                cieSel.add(opt);
            });

            cieSel.onchange = function () { __reprogOnSegCie(idx); };
            if (heureSel) heureSel.onchange = function () { __reprogOnSegHeure(idx); };
            if (siegeSel) siegeSel.onchange = function () { __reprogOnSegSiege(idx); };

            if (prefCie && seg.byCie[prefCie]) {
                cieSel.value = prefCie;
            } else if (cieKeys.length === 1) {
                cieSel.value = cieKeys[0];
            }
            if (cieSel.value) {
                __reprogFireChange(cieSel);
            }
        }

        __reprogSegErr(idx, 'Chargement des compagnies…');
        // Endpoint dédié léger (évite programmes/chemin qui peut bloquer).
        // Ne plus appeler /programmes/chemin (JOIN tarification trop lourd).
        __reprogXhrGet(urlSeg, function (dataSeg, errSeg) {
            try {
                var rows = __reprogRowsArray(dataSeg);
                if (rows.length) {
                    fillCompanies(rows, false);
                    return;
                }
                __reprogSegErr(idx, 'Aucune compagnie pour « ' + seg.ligneId
                    + ' » le ' + dateYmd
                    + (errSeg ? (' (' + errSeg + ')') : '')
                    + '. Vérifiez programmes actifs sur cette ligne.');
            } catch (eFill) {
                __reprogSegErr(idx, 'Erreur affichage compagnies: '
                    + (eFill && eFill.message ? eFill.message : eFill));
            }
        });
    }

    function __reprogOnSegCie(idx) {
        var seg = window.__reprogState.segData[idx];
        var cieSel = __reprogUiCie(idx);
        var heureSel = __reprogUiHeure(idx);
        var siegeSel = __reprogUiSiege(idx);
        __reprogResetSelect(heureSel, "Choisissez l'heure");
        __reprogResetSelect(siegeSel, 'Choisissez le siège');
        if (!seg) return;
        seg.selectedRow = null;
        __reprogSyncSegPost(idx);
        __reprogUpdatePrixSum();
        if (!cieSel || !cieSel.value) return;

        var hoursMap = (seg.byCieHour && seg.byCieHour[cieSel.value]) || {};
        var hours = Object.keys(hoursMap).sort();
        if (!hours.length) {
            __reprogSegErr(idx, 'Aucune heure pour cette compagnie sur la ligne.');
            return;
        }
        __reprogSegErr(idx, '');
        hours.forEach(function (hh) {
            var opt = document.createElement('option');
            opt.value = hh;
            opt.textContent = hh;
            heureSel.add(opt);
        });

        var pref = __reprogEtapeHeure(seg.etape);
        if (!pref || !hoursMap[pref]) {
            var anchor = __reprogQ('heuredepartpunifie');
            pref = __reprogHhmm(anchor ? anchor.value : '');
        }
        if (pref && hoursMap[pref]) {
            heureSel.value = pref;
        } else if (hours.length === 1) {
            heureSel.value = hours[0];
        }
        if (heureSel.value) {
            __reprogFireChange(heureSel);
        }
    }

    function __reprogOnSegHeure(idx) {
        var seg = window.__reprogState.segData[idx];
        var cieSel = __reprogUiCie(idx);
        var heureSel = __reprogUiHeure(idx);
        var siegeSel = __reprogUiSiege(idx);
        __reprogResetSelect(siegeSel, 'Choisissez le siège');
        if (!seg) return;
        seg.selectedRow = null;
        if (!cieSel || !cieSel.value || !heureSel || !heureSel.value) {
            __reprogSyncSegPost(idx);
            __reprogUpdatePrixSum();
            return;
        }

        var list = (seg.byCieHour[cieSel.value] && seg.byCieHour[cieSel.value][heureSel.value]) || [];
        if (!list.length) {
            __reprogSegErr(idx, 'Aucun départ pour cette compagnie / heure.');
            __reprogSyncSegPost(idx);
            __reprogUpdatePrixSum();
            return;
        }
        // Un départ = 1ère ligne (plusieurs programmes rares : on prend le 1er).
        var row = list[0];
        seg.selectedRow = row;
        __reprogSegErr(idx, 'Chargement des sièges…');

        var i1 = row.intervalle1 != null ? row.intervalle1 : '';
        var i2 = row.intervalle2 != null ? row.intervalle2 : '';
        var compaga = row.id_compaga || row.cle_compagnie_arrivee || __reprogRowCieKey(row);

        if (idx === 0) {
            var progVal = row.code_progr + '/' + (row.id_ligneheure || '') + '/'
                + (row.typetarif || __reprogSegTarif());
            __reprogSetPost(progVal, compaga, '');
            __reprogQ('replignunifie').value = row.nom_ligne || '';
            __reprogQ('repherunifie').value = row.heure || '';
            __reprogQ('datereprogrammeunifie').value = row.date_progr || '';
            __reprogQ('catreprogrammeunifie').value = row.categori || '';
            __reprogQ('idreplignunifie').value = row.ident_ligne || row.ligne_id || '';
            __reprogQ('placevenduunifie').value = i1;
            __reprogQ('dplacevenduunifie').value = i2;
        }
        __reprogSyncSegPost(idx);
        __reprogUpdatePrixSum();

        function fillSieges(dattas) {
            var n = 0;
            __reprogRowsArray(dattas).forEach(function (s) {
                if (!s || s.siege_num == null) return;
                var o = document.createElement('option');
                o.value = s.siege_num;
                o.textContent = s.siege_num;
                siegeSel.add(o);
                n++;
            });
            if (n === 0) {
                __reprogSegErr(idx, 'Aucun siège disponible pour ce départ.');
            } else {
                __reprogSegErr(idx, '');
            }
        }

        function loadSiegesWithIntervals(db, fn) {
            if (db === '' || fn === '' || db == null || fn == null) {
                __reprogSegErr(idx, 'Intervalles de sièges manquants pour ce programme.');
                return;
            }
            __reprogXhrGet(
                window.location.origin + ((typeof APP_ROOT !== 'undefined' && APP_ROOT) ? APP_ROOT : '')
                    + '/programmes/siegdisponibletrans/'
                    + encodeURIComponent(row.code_progr) + '/'
                    + encodeURIComponent(db) + '/'
                    + encodeURIComponent(fn),
                fillSieges
            );
        }

        __reprogXhrGet(
            window.location.origin + ((typeof APP_ROOT !== 'undefined' && APP_ROOT) ? APP_ROOT : '')
                + '/programmes/siegdispotrans/'
                + encodeURIComponent(row.code_progr),
            function (meta) {
                var metas = __reprogRowsArray(meta);
                if (metas.length) {
                    var m = metas[0];
                    if ((i1 === '' || i1 == null) && m.intervalle1 != null) i1 = m.intervalle1;
                    if ((i2 === '' || i2 == null) && m.intervalle2 != null) i2 = m.intervalle2;
                    if (m.prix != null && (row.prix == null || row.prix === '')) {
                        row.prix = m.prix;
                    }
                    if (m.categori) row.categori = m.categori;
                    if (idx === 0) {
                        if (m.categori) __reprogQ('catreprogrammeunifie').value = m.categori;
                        __reprogQ('placevenduunifie').value = i1;
                        __reprogQ('dplacevenduunifie').value = i2;
                    }
                    __reprogSyncSegPost(idx);
                    __reprogUpdatePrixSum();
                }
                loadSiegesWithIntervals(i1, i2);
            }
        );
    }

    function __reprogOnSegSiege(idx) {
        __reprogSyncSegPost(idx);
        __reprogUpdatePrixSum();
        if (idx !== 0) return;
        var siegeSel = __reprogUiSiege(idx);
        if (!siegeSel) return;
        var postS = __reprogQ('numsiege_post_unifie');
        if (postS) postS.value = siegeSel.value || '';

        var prog = (__reprogQ('programrepunifie') || {}).value || '';
        var siege = siegeSel.value;
        if (!prog || !siege) return;
        var root = (typeof APP_ROOT !== 'undefined' && APP_ROOT) ? APP_ROOT : '';
        __reprogXhrGet(
            window.location.origin + root + '/programmes/verifisieges/'
                + encodeURIComponent(prog) + '/' + encodeURIComponent(siege),
            function (donsieg) {
                if (donsieg == '' || donsieg === null) {
                    __reprogXhrGet(
                        window.location.origin + root + '/programmes/creersiege/'
                            + encodeURIComponent(prog) + '/' + encodeURIComponent(siege),
                        function (dongrep) {
                            var rows = __reprogRowsArray(dongrep);
                            if (rows.length) {
                                __reprogQ('idtamporepunifie').value = rows[0].idtamp || '';
                                __reprogQ('siegselectrepunifie').value = rows[0].numsieg || '';
                            }
                        }
                    );
                } else {
                    siegeSel.value = '';
                    if (postS) postS.value = '';
                    __reprogSyncSegPost(idx);
                    __reprogUpdatePrixSum();
                    alert('Siège déjà réservé sur ce segment.');
                }
            }
        );
    }

    function __reprogOnDateChange() {
        var dateEl = __reprogQ('datereprog_unifie');
        var dateYmd = dateEl ? dateEl.value : '';
        if (!dateYmd) {
            __reprogResetChoix();
            return;
        }
        __reprogLoadTransitHours(dateYmd, function () {
            __reprogFillHeuresForDate(dateYmd);
        });
    }

    function __reprogOnHeureChange() {
        var dateEl = __reprogQ('datereprog_unifie');
        var heureSel = __reprogQ('heuredepartpunifie');
        var dateYmd = dateEl ? dateEl.value : '';
        __reprogSetPost('', '', '');
        __reprogHideDirect();
        __reprogHideCorr();
        __reprogSetDirectInfo('');
        __reprogResetSelect(__reprogQ('compagniepunifie'), 'Choisissez la compagnie');
        __reprogResetSelect(__reprogQ('numsiegepunifie'), 'Choisissez le siège');
        if (__reprogQ('smspunifie')) __reprogQ('smspunifie').style.display = 'none';

        if (!heureSel || !dateYmd || !heureSel.value) return;
        var hh = __reprogHhmm(heureSel.value);
        var st = window.__reprogState;

        function afterCieFill(nDirect) {
            var cieSel = __reprogQ('compagniepunifie');
            if (!cieSel || cieSel.options.length <= 1) {
                var box = __reprogQ('smspunifie');
                var err = __reprogQ('erreurSmspunifie');
                if (box) box.style.display = 'block';
                if (err) {
                    err.textContent = nDirect > 0
                        ? 'Aucune compagnie pour cette heure.'
                        : 'Aucun départ ni correspondance pour cette heure.';
                }
                return;
            }
            if (cieSel.options.length === 2) {
                cieSel.selectedIndex = 1;
                __reprogFireChange(cieSel);
            }
        }

        // Toujours chercher les chemins : date → heure → compagnie → itinéraire (direct ou segments).
        __reprogFetchChemins(dateYmd, hh, function (chemins) {
            var nDirect = __reprogFillCompagnies(dateYmd, hh, chemins);
            afterCieFill(nDirect);
        });
    }

    function __reprogOnCompagnieChange() {
        var cieSel = __reprogQ('compagniepunifie');
        var dateEl = __reprogQ('datereprog_unifie');
        var heureSel = __reprogQ('heuredepartpunifie');
        var dateYmd = dateEl ? dateEl.value : '';
        var hh = heureSel ? __reprogHhmm(heureSel.value) : '';

        __reprogResetSelect(__reprogQ('numsiegepunifie'), 'Choisissez le siège');
        __reprogSetDirectInfo('');
        if (__reprogQ('smspunifie')) __reprogQ('smspunifie').style.display = 'none';

        if (!cieSel || !cieSel.value || !dateYmd || !hh) {
            __reprogSetPost('', '', '');
            __reprogHideDirect();
            __reprogHideCorr();
            return;
        }

        var opt = cieSel.options[cieSel.selectedIndex];
        var kind = opt ? (opt.getAttribute('data-kind') || 'direct') : 'direct';
        var cieKey = opt ? (opt.getAttribute('data-cie-key') || opt.getAttribute('data-compaga') || '') : '';
        var compaga = opt ? (opt.getAttribute('data-compaga') || '') : '';
        var multi = __reprogCheminsMulti(window.__reprogState.chemins);

        // Direct pour cette compagnie → ligne + date + heure + siège (exclusif).
        if (kind === 'direct' && cieSel.value.indexOf('corr:') !== 0) {
            var directs = __reprogDirectMatchesForCie(dateYmd, hh, cieKey, cieSel.value);
            if (directs.length) {
                __reprogShowDirectExclusive();
                __reprogSetPost(cieSel.value, compaga, '');
                window.__reprogState.mode = 'direct';
                var ligne = opt.getAttribute('data-ligne') || directs[0].nom_ligne || '';
                var dAff = opt.getAttribute('data-date') || dateYmd;
                var hAff = opt.getAttribute('data-heure') || hh;
                __reprogSetDirectInfo(
                    'Ligne : ' + ligne + ' — Date : ' + dAff + ' — Heure : ' + hAff
                );
                __reprogLoadSiegesDirect(cieSel.value);
                return;
            }
        }

        // Sinon correspondances multi-segments.
        if (multi.length) {
            var filtered = multi;
            if (cieKey) {
                var pref = multi.filter(function (ch) {
                    var e0 = __reprogNormalizeEtapes(ch.etapes || ch.legs)[0] || {};
                    return __reprogEtapeCieKey(e0) === String(cieKey);
                });
                if (pref.length) filtered = pref;
            }
            __reprogShowCorrExclusive(
                filtered,
                'Choisissez un itinéraire, puis compagnie / heure / siège pour chaque segment.'
            );
            return;
        }

        __reprogHideDirect();
        __reprogHideCorr();
        __reprogSetPost('', '', '');
        var box = __reprogQ('smspunifie');
        var err = __reprogQ('erreurSmspunifie');
        if (box) box.style.display = 'block';
        if (err) err.textContent = 'Aucun itinéraire disponible pour cette compagnie.';
    }

    function __reprogOnSiegeDirectChange() {
        var siegeSel = __reprogQ('numsiegepunifie');
        var postS = __reprogQ('numsiege_post_unifie');
        if (postS) postS.value = siegeSel ? (siegeSel.value || '') : '';
        if (!siegeSel || !siegeSel.value) return;
        var prog = (__reprogQ('programrepunifie') || {}).value || '';
        __reprogXhrGet(
            window.location.origin + APP_ROOT + '/programmes/verifisieges/'
                + encodeURIComponent(prog) + '/' + encodeURIComponent(siegeSel.value),
            function (donsieg) {
                var errBox = __reprogQ('erreursiegunifie');
                if (donsieg == '' || donsieg === null) {
                    if (errBox) errBox.style.display = 'none';
                    __reprogXhrGet(
                        window.location.origin + APP_ROOT + '/programmes/creersiege/'
                            + encodeURIComponent(prog) + '/' + encodeURIComponent(siegeSel.value),
                        function (dongrep) {
                            var rows = __reprogRowsArray(dongrep);
                            if (rows.length) {
                                __reprogQ('idtamporepunifie').value = rows[0].idtamp || '';
                                __reprogQ('siegselectrepunifie').value = rows[0].numsieg || '';
                            }
                        }
                    );
                } else {
                    siegeSel.value = '';
                    if (postS) postS.value = '';
                    if (errBox) errBox.style.display = 'block';
                    var err = __reprogQ('erreurSiegeunifie');
                    if (err) err.textContent = 'Siège déjà réservé.';
                }
            }
        );
    }

    function __reprogOnCorrChange() {
        var sel = __reprogQ('corr_unifie_select');
        var segs = __reprogQ('corr_segments_unifie');
        if (!sel || sel.value === '') {
            if (segs) segs.innerHTML = '';
            return;
        }
        var ch = window.__reprogState.chemins[parseInt(sel.value, 10)];
        if (!ch) return;
        var etapes = __reprogNormalizeEtapes(ch.etapes || ch.legs);
        if (!etapes.length) {
            if (segs) segs.innerHTML = '<p class="text-danger small">Itinéraire sans segments.</p>';
            return;
        }
        // Prefer transit mode for commit
        window.__reprogState.mode = 'transit';
        __reprogHideDirect();
        __reprogClearSegPosts();
        __reprogQ('reprog_mode_unifie').value = 'transit';
        __reprogQ('reprog_nbr_seg_unifie').value = String(etapes.length);
        __reprogBuildSegments(etapes);
    }

    document.querySelectorAll('.addreprog_unifie').forEach(function (btn) {
        var title = __reprogQ('rTitleUnifie');
        if (title) title.textContent = 'REPROGRAMMATION';

        var dateEl = __reprogQ('datereprog_unifie');
        if (dateEl) dateEl.onchange = __reprogOnDateChange;
        var heurdep = __reprogQ('heuredepartpunifie');
        if (heurdep) heurdep.onchange = __reprogOnHeureChange;
        var cieSel = __reprogQ('compagniepunifie');
        if (cieSel) cieSel.onchange = __reprogOnCompagnieChange;
        var siegeDirect = __reprogQ('numsiegepunifie');
        if (siegeDirect) siegeDirect.onchange = __reprogOnSiegeDirectChange;
        var corrSel = __reprogQ('corr_unifie_select');
        if (corrSel) corrSel.onchange = __reprogOnCorrChange;

        var transitCb = __reprogQ('mode_transit_ticket_unifie');
        if (transitCb) {
            transitCb.onchange = function () {
                var st = window.__reprogState;
                st.isTransitTicket = !!transitCb.checked;
                var hid = __reprogQ('reprog_is_transit_ticket');
                if (hid) hid.value = st.isTransitTicket ? '1' : '0';
                var c2 = __reprogQ('reprog_code2_wrap');
                if (!st.isTransitTicket) {
                    if (c2) c2.style.display = 'none';
                    st.lookup2Done = false;
                    st.prix2 = '';
                    if (st.lookup1Done) __reprogOpenChoixIfReady(dateEl);
                } else if (st.lookup1Done && !st.lookup2Done) {
                    if (c2) c2.style.display = '';
                    var choix = __reprogQ('reprog_choix_wrap');
                    if (choix) choix.style.display = 'none';
                }
            };
        }

        var infos = __reprogQ('reprogrammer_infos_unifie');
        if (infos) {
            infos.onclick = function () {
                var cocl = String((__reprogQ('code_lookup_unifie') || {}).value || '').trim();
                var modal = __reprogQ('repro-unifie-0');
                var allowTampon = modal && modal.getAttribute('data-allow-tampon') === '1';
                var tamponCb = __reprogQ('mode_tampon_unifie');
                var mode = (allowTampon && tamponCb && tamponCb.checked) ? 'tampon' : 'ticket';
                var isTransit = !!(__reprogQ('mode_transit_ticket_unifie')
                    && __reprogQ('mode_transit_ticket_unifie').checked);

                __reprogResetUi();
                window.__reprogState.isTransitTicket = isTransit;
                var hidTr = __reprogQ('reprog_is_transit_ticket');
                if (hidTr) hidTr.value = isTransit ? '1' : '0';
                if (__reprogQ('smspunifie')) __reprogQ('smspunifie').style.display = 'none';
                if (__reprogQ('billetrepunifie')) __reprogQ('billetrepunifie').style.display = 'none';

                if (!cocl) {
                    if (__reprogQ('smspunifie')) __reprogQ('smspunifie').style.display = 'block';
                    if (__reprogQ('erreurSmspunifie')) {
                        __reprogQ('erreurSmspunifie').textContent = 'Saisissez un code ticket.';
                    }
                    return;
                }

                __reprogXhrGet(
                    window.location.origin + APP_ROOT
                        + '/reprogrammes/lookup_unifie?mode=' + encodeURIComponent(mode)
                        + '&code=' + encodeURIComponent(cocl),
                    function (donnees) {
                        if (!donnees || typeof donnees !== 'object') {
                            if (__reprogQ('smspunifie')) __reprogQ('smspunifie').style.display = 'block';
                            if (__reprogQ('erreurSmspunifie')) {
                                __reprogQ('erreurSmspunifie').textContent =
                                    'Cet ticket ne peut pas être reprogrammé ici.';
                            }
                            return;
                        }

                        var infosWrap = __reprogQ('reprog_infos_wrap');
                        if (infosWrap) infosWrap.style.display = 'grid';
                        __reprogQ('nomclpunifie').textContent = 'NOM: ' + (donnees.nom_client || '');
                        __reprogQ('prenomclpunifie').textContent = 'PRENOM: ' + (donnees.prenom_client || '');
                        __reprogQ('contactclpunifie').textContent = 'CONTACT: ' + (donnees.contact_client || '');
                        __reprogQ('refclpunifie').textContent = 'CNIB: ' + (donnees.num_CNIB || '');
                        __reprogQ('directionclpunifie').textContent = 'AXE: ' + (donnees.nom_ligne || '');
                        __reprogQ('codeclpunifie').textContent =
                            'TICKET: ' + (donnees.code_ticket || '') + ' / PASS: ' + (donnees.code_passager || '');
                        __reprogQ('heureclpunifie').textContent =
                            'HEURE: ' + (donnees.heure || '') + ' — SIÈGE: ' + (donnees.num_siege_categorie || '')
                            + ' — DATE: ' + (donnees.date_progr || '');
                        var cieArr = donnees.nom_compagnie || '';
                        var cieDep = donnees.nom_compagnie_depart || '';
                        __reprogQ('compagnieclpunifie').textContent = cieArr
                            ? ('COMPAGNIE: ' + cieArr + (cieDep && cieDep !== cieArr ? ' (dép. ' + cieDep + ')' : ''))
                            : '';
                        __reprogQ('prixclpunifie').textContent =
                            'PRIX: ' + (donnees.prixvente != null ? donnees.prixvente : '');

                        __reprogQ('passerpunifie').value = donnees.code_passager || '';
                        __reprogQ('idclpasseridunifie').value = donnees.ligne_id || '';
                        __reprogQ('client_idpunifie').value = donnees.id_client_pass || '';
                        __reprogQ('pasnompunifie').value = donnees.nom_client || '';
                        __reprogQ('pasprenompunifie').value = donnees.prenom_client || '';
                        __reprogQ('pascontactpunifie').value = donnees.contact_client || '';
                        __reprogQ('pascnibpunifie').value = donnees.num_CNIB || '';
                        __reprogQ('pasdatepunifie').value = donnees.date_delivre || '';
                        __reprogQ('nsiegepunifie').value = donnees.num_siege_categorie || '';
                        __reprogQ('delivrelieunifie').value = donnees.lieu_delivre || '';
                        __reprogQ('depoldunifie').value = donnees.code_pro || '';
                        __reprogQ('id_compaga_unifie').value = donnees.id_compaga || '';
                        __reprogQ('codeidunifie').value = donnees.code_passager || '';
                        __reprogQ('codeticketsunifie').value = donnees.tamponcod || '';
                        __reprogQ('lgcodeticketsunifie').value = donnees.tamponcodtr || '';
                        __reprogQ('codenonpunifie').value = donnees.code_non_pass || '';
                        __reprogQ('statconfunifie').value = donnees.statut_confirme || '';
                        __reprogQ('statrepunifie').value = donnees.statut_reprog || '';
                        __reprogQ('programrepunifie').value = donnees.code_progr || '';
                        __reprogQ('depgidunifie').value = donnees.gaexp_lg || '';
                        __reprogQ('dateventerepunifie').value = donnees.datep_create || '';
                        __reprogQ('gareidentifunifie').value = donnees.gareidentif || '';
                        __reprogQ('departclientidgareunifie').value = donnees.departclient_idgare || '';
                        __reprogQ('codeclient_ticket_unifie').value = donnees.code_ticket || '';
                        __reprogQ('prixventeunifie').value = donnees.prixvente != null ? donnees.prixvente : '';
                        __reprogQ('gaexp_unifie').value = donnees.gaexp_lg || '';
                        __reprogQ('gadest_unifie').value = donnees.gadest_lg || '';
                        __reprogQ('axe_unifie').value = (donnees.gaexp_lg || '') + '-' + (donnees.gadest_lg || '');

                        window.__reprogState.gaexp = donnees.gaexp_lg || '';
                        window.__reprogState.gadest = donnees.gadest_lg || '';
                        window.__reprogState.axe = (donnees.gaexp_lg || '') + '-' + (donnees.gadest_lg || '');
                        window.__reprogState.exclude = donnees.code_progr || '';
                        window.__reprogState.prix = donnees.prixvente != null ? String(donnees.prixvente) : '';
                        window.__reprogState.tarif = (donnees.typetarif != null && String(donnees.typetarif).trim() !== '')
                            ? String(donnees.typetarif).trim()
                            : '1';
                        window.__reprogState.tamponcodtr = donnees.tamponcodtr || '';
                        window.__reprogState.lookup1Done = true;
                        window.__reprogState.lookup2Done = false;
                        window.__reprogState.prix2 = '';
                        var sgEl = document.querySelector('input[name="sousgareconnect"]');
                        window.__reprogState.sgid = (sgEl && sgEl.value) ? sgEl.value : '0';

                        var days = (new Date(__reprogQ('actueldaterepunifie').value).getTime()
                            - new Date(__reprogQ('dateventerepunifie').value).getTime()) / (1000 * 3600 * 24);
                        if (days >= 31) {
                            __reprogQ('billetrepunifie').style.display = 'block';
                            __reprogQ('billetSmsrepunifie').textContent =
                                'Billet non valable, la durée de validité est dépassée.';
                            return;
                        }

                        if (isTransit) {
                            var c2w = __reprogQ('reprog_code2_wrap');
                            if (c2w) c2w.style.display = '';
                            if (__reprogQ('smspunifie')) __reprogQ('smspunifie').style.display = 'block';
                            if (__reprogQ('erreurSmspunifie')) {
                                __reprogQ('erreurSmspunifie').textContent =
                                    '1er code OK. Vérifiez maintenant le 2ᵉ code de la correspondance.';
                            }
                            return;
                        }

                        __reprogOpenChoixIfReady(dateEl);
                    }
                );
            };
        }

        var infos2 = __reprogQ('reprogrammer_infos2_unifie');
        if (infos2) {
            infos2.onclick = function () {
                var cocl2 = String((__reprogQ('code_lookup2_unifie') || {}).value || '').trim();
                var modal = __reprogQ('repro-unifie-0');
                var allowTampon = modal && modal.getAttribute('data-allow-tampon') === '1';
                var tamponCb = __reprogQ('mode_tampon_unifie');
                var mode = (allowTampon && tamponCb && tamponCb.checked) ? 'tampon' : 'ticket';
                if (__reprogQ('smspunifie')) __reprogQ('smspunifie').style.display = 'none';

                if (!window.__reprogState.lookup1Done) {
                    alert('Vérifiez d’abord le 1er code.');
                    return;
                }
                if (!cocl2) {
                    alert('Saisissez le 2ᵉ code.');
                    return;
                }
                if (cocl2 === String((__reprogQ('code_lookup_unifie') || {}).value || '').trim()
                    || cocl2 === String((__reprogQ('codeclient_ticket_unifie') || {}).value || '').trim()
                    || cocl2 === String((__reprogQ('codeticketsunifie') || {}).value || '').trim()) {
                    alert('Le 2ᵉ code doit être différent du 1er.');
                    return;
                }

                __reprogXhrGet(
                    window.location.origin + APP_ROOT
                        + '/reprogrammes/lookup_unifie?mode=' + encodeURIComponent(mode)
                        + '&code=' + encodeURIComponent(cocl2),
                    function (d2) {
                        if (!d2 || typeof d2 !== 'object') {
                            if (__reprogQ('smspunifie')) __reprogQ('smspunifie').style.display = 'block';
                            if (__reprogQ('erreurSmspunifie')) {
                                __reprogQ('erreurSmspunifie').textContent =
                                    '2ᵉ code invalide ou non reprogrammable.';
                            }
                            return;
                        }
                        var tr1 = String(window.__reprogState.tamponcodtr || '');
                        var tr2 = String(d2.tamponcodtr || '');
                        var cl1 = String((__reprogQ('client_idpunifie') || {}).value || '');
                        var cl2 = String(d2.id_client_pass || '');
                        var sameTr = tr1 && tr2 && tr1 === tr2;
                        var sameCl = cl1 && cl2 && cl1 === cl2;
                        if (!sameTr && !sameCl) {
                            if (__reprogQ('smspunifie')) __reprogQ('smspunifie').style.display = 'block';
                            if (__reprogQ('erreurSmspunifie')) {
                                __reprogQ('erreurSmspunifie').textContent =
                                    'Le 2ᵉ code n’appartient pas au même ticket transit (lien / client).';
                            }
                            return;
                        }
                        if (String(d2.tamponcod || '') === String((__reprogQ('codeticketsunifie') || {}).value || '')
                            || String(d2.code_passager || '') === String((__reprogQ('passerpunifie') || {}).value || '')) {
                            alert('Le 2ᵉ code correspond au même billet que le 1er.');
                            return;
                        }

                        __reprogQ('passerpunifie2').value = d2.code_passager || '';
                        __reprogQ('codeticketsunifie2').value = d2.tamponcod || '';
                        __reprogQ('codeclient_ticket_unifie2').value = d2.code_ticket || '';
                        __reprogQ('prixventeunifie2').value = d2.prixvente != null ? d2.prixvente : '';
                        window.__reprogState.prix2 = d2.prixvente != null ? String(d2.prixvente) : '0';
                        window.__reprogState.lookup2Done = true;

                        // OD complet du ticket transit : départ jambe1 → arrivée jambe2.
                        var ga1 = window.__reprogState.gaexp || (__reprogQ('gaexp_unifie') || {}).value || '';
                        var gd2 = d2.gadest_lg || '';
                        if (ga1 && gd2) {
                            window.__reprogState.gadest = gd2;
                            window.__reprogState.axe = ga1 + '-' + gd2;
                            if (__reprogQ('gadest_unifie')) __reprogQ('gadest_unifie').value = gd2;
                            if (__reprogQ('axe_unifie')) __reprogQ('axe_unifie').value = ga1 + '-' + gd2;
                            var dirEl = __reprogQ('directionclpunifie');
                            if (dirEl) {
                                dirEl.textContent = 'DIRECTION: ' + ga1 + ' → ' + gd2
                                    + ' (transit 2 jambes)';
                            }
                        }

                        var c2info = __reprogQ('code2clpunifie');
                        if (c2info) {
                            c2info.style.display = 'block';
                            c2info.textContent = '2ᵉ TICKET: ' + (d2.code_ticket || '')
                                + ' / PASS: ' + (d2.code_passager || '')
                                + ' — PRIX: ' + (d2.prixvente != null ? d2.prixvente : '')
                                + ' — ' + (d2.nom_ligne || '') + ' ' + (d2.heure || '');
                        }
                        if (__reprogQ('smspunifie')) __reprogQ('smspunifie').style.display = 'none';
                        __reprogOpenChoixIfReady(dateEl);
                    }
                );
            };
        }

        var resetBtn = __reprogQ('reseunifie');
        if (resetBtn) {
            resetBtn.onclick = function () {
                var idtap = (__reprogQ('idtamporepunifie') || {}).value || '';
                var sieg = (__reprogQ('siegselectrepunifie') || {}).value || '';
                if (!idtap) return;
                __reprogXhrGet(
                    window.location.origin + APP_ROOT + '/programmes/deltamponsieg/'
                        + encodeURIComponent(idtap) + '/' + encodeURIComponent(sieg),
                    function () {}
                );
            };
        }

        // Ouverture modal : fixer l’action du formulaire (ne pas préparer le POST ici).
        btn.addEventListener('click', function () {
            var form = __reprogQ('rFormUnifie');
            if (form && btn.dataset.cle_compagnie) {
                form.setAttribute(
                    'action',
                    APP_ROOT + '/Reprogrammes/updatetransit/' + btn.dataset.cle_compagnie
                );
            }
        });
    });

    // Submit : un seul listener (évite les doublons si plusieurs boutons toolbar).
    (function () {
        var formEl = __reprogQ('rFormUnifie');
        if (!formEl || formEl.getAttribute('data-reprog-submit-bound') === '1') {
            return;
        }
        formEl.setAttribute('data-reprog-submit-bound', '1');
        formEl.addEventListener('submit', function (ev) {
            if (window.__reprogState.isTransitTicket && !window.__reprogState.lookup2Done) {
                ev.preventDefault();
                alert('Ticket transit : vérifiez le 2ᵉ code avant de reporter.');
                return false;
            }
            if (window.__reprogState.mode === 'transit'
                && (window.__reprogState.etapes || []).length >= 2) {
                var prep = __reprogPrepareTransitSubmit();
                if (!prep.ok) {
                    ev.preventDefault();
                    alert(prep.msg || 'Itinéraire incomplet.');
                    return false;
                }
                return true;
            }

            __reprogClearSegPosts();
            __reprogQ('reprog_mode_unifie').value = 'direct';
            var cie = __reprogQ('compagniepunifie');
            var sie = __reprogQ('numsiegepunifie');
            if (!cie || !cie.value || !sie || !sie.value) {
                ev.preventDefault();
                alert('Choisissez la compagnie et le siège pour le départ direct.');
                return false;
            }
            var opt = cie.options[cie.selectedIndex];
            __reprogSetPost(cie.value, opt ? opt.getAttribute('data-compaga') : '', sie.value);
            return true;
        });
    })();
});
