/* Bundle guichet role=18 — genere par scripts/build_guichet_bundles.php */
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
        if (n === 'CBT') return true;
        return /(^|[^A-Z0-9])CBT([^A-Z0-9]|$)/.test(n);
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
        var targetId = box.getAttribute('data-target-arrivee');
        var arriveeSelect = targetId
            ? document.getElementById(targetId)
            : box._arriveeSelect;
        if (!arriveeSelect) return;

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

        // Une seule compagnie active : si plusieurs CBT match, garder la première cochée
        var checked = box.querySelectorAll('.js-filtre-compagnie-check:checked');
        if (checked.length > 1) {
            for (var i = 1; i < checked.length; i++) {
                checked[i].checked = false;
            }
        }

        placeCompanyBox(box, arriveeSelect);

        box.addEventListener('change', function (e) {
            var t = e.target;
            if (!t || !t.classList.contains('js-filtre-compagnie-check')) return;

            // Exclusif : cocher une compagnie décoche les autres
            if (t.checked) {
                box.querySelectorAll('.js-filtre-compagnie-check').forEach(function (c) {
                    if (c !== t) c.checked = false;
                });
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
/* --- addventeticketfi.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
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

    function __venteFiHandleProgList(don, dptDate) {
        var list = __venteFiProgListFromResponse(don);
        __venteFiHideProgSelect();
        var ps = document.querySelector('#psiegesfid');
        if (ps) ps.options.length = 1;
        if (list.length === 0) return false;
        if (list.length === 1) {
            __venteFiApplyProgFields(list[0]);
            __venteFiLoadSieges(dptDate);
            return true;
        }
        var box = document.getElementById('selprog_box_fid');
        var sel = document.getElementById('selprogfid');
        if (!sel) {
            __venteFiApplyProgFields(list[0]);
            __venteFiLoadSieges(dptDate);
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
            if (isNaN(idx) || !list[idx]) {
                __venteFiApplyProgFields({});
                return;
            }
            __venteFiApplyProgFields(list[idx]);
            __venteFiLoadSieges(dptDate);
        };
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
            var gkey = dprog + '|' + lh;
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
                label = (g.heure || '') + ' — ' + __venteFiFormatDateShort(g.date_progr);
            }
            if (g.rows.length > 1) label = label + ' (' + g.rows.length + ' départs)';
            opt.innerHTML = label;
            sel.add(opt);
        }
        if (legKey) __venteFiWireCheminHeur(selectId, legKey);
    }


    function __venteFiLoadSiegesChemin(cfg, row) {
        var ps = document.getElementById(cfg.sieges);
        if (ps) ps.options.length = 1;
        if (!row || !row.code_progr) return;
        if (cfg.prix && row.prix != null) {
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

    window.__venteFiHasTransit = false;
    window.__venteFiLastHeuresVente = [];
    window.__venteFiApplyTransitLegs = null;

    function __venteFiFillHeuresVente(heures) {
        var hSel = document.querySelector('#hdepartfid');
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
        if (existing) return existing;
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
        var anchor = document.getElementById('hdepartitinefid')
            || document.getElementById('idcheminsfid')
            || document.getElementById('nbrtransfid');
        if (anchor && anchor.parentNode && anchor.parentNode.parentNode) {
            anchor.parentNode.parentNode.insertBefore(box, anchor.parentNode);
        } else if (anchor && anchor.parentNode) {
            anchor.parentNode.insertBefore(box, anchor);
        } else {
            document.body.appendChild(box);
        }
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
    function __venteFiSetCheminLigneOption(selectSel, code, nom) {
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
        sel.selectedIndex = 1;
        applyIdx(0);
    }

    function __venteFiRequestTransitLegs(seltdep, arr, datedepart, sougid, force, onDone) {
        var sg = (sougid != null && sougid !== '') ? sougid : '0';
        var forceFlag = force ? '1' : '0';
        var done = function (etapes) {
            if (typeof onDone === 'function') onDone(etapes);
            else if (typeof window.__venteFiApplyTransitLegs === 'function') window.__venteFiApplyTransitLegs(etapes);
        };
        var httpRequestitinefi = new XMLHttpRequest();
        httpRequestitinefi.open(
            'GET',
            window.location.origin + `${APP_ROOT}/programmes/verifchemins/`
                + encodeURIComponent(seltdep + '-' + arr) + '/'
                + encodeURIComponent(datedepart) + '/'
                + encodeURIComponent(sg) + '/'
                + forceFlag,
            true
        );
        httpRequestitinefi.onload = function () {
            var payload = null;
            try { payload = JSON.parse(httpRequestitinefi.responseText); } catch (e) { payload = null; }
            if (Array.isArray(payload)) { __venteFiHideCheminSelector(); done(payload); return; }
            if (!payload || typeof payload !== 'object') { __venteFiHideCheminSelector(); done([]); return; }
            if (payload.mode === 'direct' || payload.mode === 'none') { __venteFiHideCheminSelector(); done([]); return; }
            var chemins = Array.isArray(payload.chemins) ? payload.chemins : [];
            if (chemins.length > 1) { __venteFiShowCheminSelector(chemins, done); return; }
            __venteFiHideCheminSelector();
            if (chemins.length === 1 && chemins[0].etapes) { done(chemins[0].etapes); return; }
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
        if (p.prix != null && String(p.prix).trim() !== '') {
            set('#prix_axetransfid', p.prix);
        }
        __venteFiClearDownstreamCheminHeures();
    }

    function __venteFiLoadSiegesTransit1(idLh, dptDate) {
        var ps = document.querySelector('#psiegesitinesfid');
        if (ps) ps.options.length = 1;
        var tfEl = document.querySelector('#tarifattribfid');
        var tfbs = tfEl && String(tfEl.value || '').trim() !== '' ? String(tfEl.value).trim() : '1';
        if (tfEl && String(tfEl.value || '').trim() === '') tfEl.value = tfbs;
        if (idLh) {
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
                                                                __venteFiFillLigne1Locked(donitinesfi[0], function (codeSel) {
                                                                    if (!codeSel) return;
                                                                    var hd = document.querySelector('#hdepartitinefid');
                                                                    if (hd) hd.options.length = 1;
                                                                    var datedepart = document.querySelector('#date_depheurefid')
                                                                        ? document.querySelector('#date_depheurefid').value
                                                                        : (document.querySelector('#date_depheure') ? document.querySelector('#date_depheure').value : '');
                                                                    var httpH = new XMLHttpRequest();
                                                                    httpH.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheureitine/${encodeURIComponent(codeSel)}/${encodeURIComponent(datedepart)}`, true);
                                                                    httpH.onload = function () {
                                                                        try {
                                                                            var infositin = JSON.parse(httpH.responseText);
                                                                            if (hd) hd.options.length = 1;
                                                                            if (infositin && Object.entries(infositin).length >= 1) {
                                                                                for (var key in Object.entries(infositin)) {
                                                                                    var opt = document.createElement('option');
                                                                                    opt.value = `${infositin[key].id_ligneheure}/${infositin[key].heure}`;
                                                                                    opt.innerHTML = `${infositin[key].heure}`;
                                                                                    if (hd) hd.add(opt);
                                                                                }
                                                                            }
                                                                        } catch (eH) {}
                                                                    };
                                                                    httpH.setRequestHeader('Content-Type', 'application/json');
                                                                    httpH.send();
                                                                });
                                                            }
                                                            
                                                
                                                            if(i === 2)
                                                            {
                                                                __venteFiSetCheminLigneOption('#idcheminsfid', donitinesfi[1].code_itineraires, donitinesfi[1].nom_itineraires);

                                                                                                                                document.querySelector('#itinecodesfid').value = `${donitinesfi[0].id_lignes}`;
                                                                    

                                                                var typgare1fi = (donitinesfi[0] && donitinesfi[0].code_itineraires) ? String(donitinesfi[0].code_itineraires) : (document.querySelector('#itinecodefid').value || '');
                                                                var post_typgare1fi = typgare1fi.split('-');
                                                                var seltypgare1fi = post_typgare1fi[0];
                                                                var typgareselfi = post_typgare1fi[1];
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

                                                                        let httptypequartitinfi;
                                                                        httptypequartitinfi = new XMLHttpRequest();
                                                                        var itinprofi = document.querySelector('#itinecodefid').value;
                                                                        var datedepartfi = document.querySelector('#date_depheurefid').value;
                                                                        httptypequartitinfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheureitine/${itinprofi}/${datedepartfi}`, true);
                                                                    httptypequartitinfi.onload = () => 
                                                                    {
                                                                        const infositinfi = JSON.parse(httptypequartitinfi.responseText);
                                                                        if (infositinfi == null) 
                                                                        {


                                                                        }
                                                                        if (typeof window.__venteFillHeureItineSelect === 'function') {
                                                                            window.__venteFillHeureItineSelect('#hdepartitinefid', infositinfi);
                                                                        } else if (Object.entries(infositinfi).length >= 1) {
                                                                            for (let key in Object.entries(infositinfi)) {
                                                                                    let opt = document.createElement('option');
                                                                                    opt.value = `${infositinfi[key].id_ligneheure}/${infositinfi[key].heure}`;
                                                                                    opt.innerHTML = `${infositinfi[key].heure}`;
                                                                                    document.querySelector('#hdepartitinefid').add(opt);
                                                                                }
                                                                        } else {
                                                                            document.querySelector('#hdepartitinefid').options.length = 1;
                                                                        }
                                                                    };
                                                                    httptypequartitinfi.setRequestHeader('Content-Type', 'application/json');
                                                                    httptypequartitinfi.send();
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

                                                                        var post_typgare2fi = prostranscheminfi.split('-');
                                                                        var seltypgare2fi = post_typgare2fi[0];
                                                                        var typgaresel1fi = post_typgare2fi[1];
 
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

                                                                
                                                                __venteFiSetCheminLigneOption('#idcheminsfid', donitinesfi[1].code_itineraires, donitinesfi[1].nom_itineraires);

                                                                                                                                document.querySelector('#itinecodesfid').value = `${donitinesfi[0].id_lignes}`;
                                                               

                                                                __venteFiSetCheminLigneOption('#idchemins1fid', donitinesfi[2].code_itineraires, donitinesfi[2].nom_itineraires);


                                                                var typgare1fi = (donitinesfi[0] && donitinesfi[0].code_itineraires) ? String(donitinesfi[0].code_itineraires) : (document.querySelector('#itinecodefid').value || '');
                                                                var post_typgare1fi = typgare1fi.split('-');
                                                                var seltypgare1fi = post_typgare1fi[0];
                                                                var typgareselfi = post_typgare1fi[1];
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


                                                                        let httptypequartitin1fi;
                                                                        httptypequartitin1fi = new XMLHttpRequest();
                                                                        var itinpro1fi = document.querySelector('#itinecodefid').value;
                                                                        var datedepartfi = document.querySelector('#date_depheurefid').value;
                                                                        httptypequartitin1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheureitine/${itinpro1fi}/${datedepartfi}`, true);
                                                                    httptypequartitin1fi.onload = () => 
                                                                    {
                                                                        const infositin1fi = JSON.parse(httptypequartitin1fi.responseText);
                                                                        if (infositin1fi == null) 
                                                                        {


                                                                        }
                                                                        if (typeof window.__venteFillHeureItineSelect === 'function') {
                                                                            window.__venteFillHeureItineSelect('#hdepartitinefid', infositin1fi);
                                                                        } else if (Object.entries(infositin1fi).length >= 1) {
                                                                            for (let key in Object.entries(infositin1fi)) {
                                                                                    let opt = document.createElement('option');
                                                                                    opt.value = `${infositin1fi[key].id_ligneheure}/${infositin1fi[key].heure}`;
                                                                                    opt.innerHTML = `${infositin1fi[key].heure}`;
                                                                                    document.querySelector('#hdepartitinefid').add(opt);
                                                                                }
                                                                        } else {
                                                                            document.querySelector('#hdepartitinefid').options.length = 1;
                                                                        }
                                                                    };
                                                                    httptypequartitin1fi.setRequestHeader('Content-Type', 'application/json');
                                                                    httptypequartitin1fi.send();
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

                                                                        var post_typgare2fi = prostranscheminfi.split('-');
                                                                        var seltypgare2fi = post_typgare2fi[0];
                                                                        var typgaresel1fi = post_typgare2fi[1];
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

                                                                        var post_typgare32fi = prostranschemin32fi.split('-');
                                                                        var seltypgare32fi = post_typgare32fi[0];
                                                                        var typgaresel31fi = post_typgare32fi[1];
                                                                        
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
                                                                __venteFiSetCheminLigneOption('#idcheminsfid', donitinesfi[1].code_itineraires, donitinesfi[1].nom_itineraires);


                                                                __venteFiSetCheminLigneOption('#idchemins1fid', donitinesfi[2].code_itineraires, donitinesfi[2].nom_itineraires);

                                                                __venteFiSetCheminLigneOption('#idchemins2fid', donitinesfi[3].code_itineraires, donitinesfi[3].nom_itineraires);

                                                                                                                               
                                                                document.querySelector('#itinecodesfid').value = `${donitinesfi[0].id_lignes}`;

                                                                    var typgare1fi = (donitinesfi[0] && donitinesfi[0].code_itineraires) ? String(donitinesfi[0].code_itineraires) : (document.querySelector('#itinecodefid').value || '');
                                                                var post_typgare1fi = typgare1fi.split('-');
                                                                var seltypgare1fi = post_typgare1fi[0];
                                                                var typgareselfi = post_typgare1fi[1];
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



                                                                        let httptypequartitin1fi;
                                                                        httptypequartitin1fi = new XMLHttpRequest();
                                                                        var datedepartfi = document.querySelector('#date_depheurefid').value;
                                                                        var itinpro1fi = document.querySelector('#itinecodefid').value;
                                                                        httptypequartitin1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheureitine/${itinpro1fi}/${datedepartfi}`, true);
                                                                    httptypequartitin1fi.onload = () => 
                                                                    {
                                                                        const infositin1fi = JSON.parse(httptypequartitin1fi.responseText);
                                                                        if (infositin1fi == null) 
                                                                        {


                                                                        }
                                                                        if (typeof window.__venteFillHeureItineSelect === 'function') {
                                                                            window.__venteFillHeureItineSelect('#hdepartitinefid', infositin1fi);
                                                                        } else if (Object.entries(infositin1fi).length >= 1) {
                                                                            for (let key in Object.entries(infositin1fi)) {
                                                                                    let opt = document.createElement('option');
                                                                                    opt.value = `${infositin1fi[key].id_ligneheure}/${infositin1fi[key].heure}`;
                                                                                    opt.innerHTML = `${infositin1fi[key].heure}`;
                                                                                    document.querySelector('#hdepartitinefid').add(opt);
                                                                                }
                                                                        } else {
                                                                            document.querySelector('#hdepartitinefid').options.length = 1;
                                                                        }
                                                                    };
                                                                    httptypequartitin1fi.setRequestHeader('Content-Type', 'application/json');
                                                                    httptypequartitin1fi.send();
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

                                                                        var post_typgare2fi = prostranscheminfi.split('-');
                                                                        var seltypgare2fi = post_typgare2fi[0];
                                                                        var typgaresel1fi = post_typgare2fi[1];
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

                                                                        var post_typgare32fi = prostranschemin32fi.split('-');
                                                                        var seltypgare32fi = post_typgare32fi[0];
                                                                        var typgaresel31fi = post_typgare32fi[1];
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

                                                                        var post_typgare42fi = prostranschemin42fi.split('-');
                                                                        var seltypgare42fi = post_typgare42fi[0];
                                                                        var typgaresel41fi = post_typgare42fi[1];

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
                                                        if (__venteFiHandleProgList(donfi, dpt_datefi)) {
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
            
            let butonclicfi = document.querySelector('#idresetfid');
            if (butonclicfi !== null) {
                butonclicfi.onclick = () => 
                {
                    let httpSiegeselectfi;
                    httpSiegeselectfi = new XMLHttpRequest();
                    const siegselectfi = document.querySelector('#siegselectfid').value;
                    const idtapfi = document.querySelector('#idtampofid').value;
                    httpSiegeselectfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtapfi}/${siegselectfi}`, true);
                    httpSiegeselectfi.onload = () => 
                    {
                        const donselectfi= JSON.parse(httpSiegeselectfi.responseText);
                        console.debug(`${typeof donselectfi} - ${donselectfi.attributes}`, console.memory);
                        document.querySelector('#messfid').style.display = 'none';
                        
                    };
                    httpSiegeselectfi.setRequestHeader('Content-Type', 'application/json');
                    httpSiegeselectfi.send();

                
                };
            }
                
                e.onclick = function () {   
                    let taFormfi = document.querySelector('#tafiForm');
                    
                    taFormfi.setAttribute('action', `${APP_ROOT}/Programmes/addpassagerfi/${e.dataset.cle_compagnie}`);
                    AppRequestGuard.ensureNonce('#tafiForm', 'sale_nonce');
                    AppRequestGuard.guardForm('#tafiForm');
                }

                var tafiFormEl = document.querySelector('#tafiForm');
                if (tafiFormEl && !tafiFormEl.dataset.salePrepared) {
                    tafiFormEl.dataset.salePrepared = '1';
                    tafiFormEl.addEventListener('submit', function () {
                        AppRequestGuard.ensureNonce('#tafiForm', 'sale_nonce');
                        // Ne pas synchroniser les miroirs client : ils servent à détecter
                        // un changement d'identité (même téléphone, autre passager).
                    });
                }

                AppRequestGuard.guardForm('#tafiForm');
                AppRequestGuard.ensureNonce('#tafiForm', 'sale_nonce');
                
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

