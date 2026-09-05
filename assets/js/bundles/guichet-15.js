/* Bundle guichet role=15 — genere par scripts/build_guichet_bundles.php */
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
/* --- addventeticketfi.js --- */
document.addEventListener('DOMContentLoaded', () => {

    /** Autre vente FI : prix saisis à la main (0 = ticket gratuit), jamais écrasés par le tarif programme. */
    window.__venteFiPrixManuel = true;

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

    function __venteFiFillHeuresVente(heures) {
        var hSel = document.querySelector('#hdepartfid');
        if (!hSel) return;
        hSel.options.length = 1;
        var list = Array.isArray(heures) ? heures : [];
        var hasTransit = !!window.__venteFiHasTransit;
        for (var i = 0; i < list.length; i++) {
            var hr = list[i];
            if (!hr || hr.id_ligneheure == null || hr.id_ligneheure === '') continue;
            var opt = document.createElement('option');
            opt.value = hr.id_ligneheure + '/' + hr.heure;
            var hasProg = !!(hr.has_programme === true || hr.has_programme === 1 || hr.has_programme === '1');
            opt.setAttribute('data-has-programme', hasProg ? '1' : '0');
            opt.innerHTML = hasProg
                ? hr.heure
                : (String(hr.heure) + (hasTransit ? ' (correspondance)' : ''));
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
        for (var d = 0; d < chemins.length; d++) {
            if (chemins[d].source !== 'direct') { defaultIdx = d; break; }
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
                                                                            if (typeof window.__venteFillHeureItineSelect === 'function') {
                                                                                window.__venteFillHeureItineSelect(hd, infositin, window.__venteSelectedHour);
                                                                            } else if (hd && infositin && Object.entries(infositin).length >= 1) {
                                                                                hd.options.length = 1;
                                                                                for (var key in Object.entries(infositin)) {
                                                                                    var opt = document.createElement('option');
                                                                                    opt.value = `${infositin[key].id_ligneheure}/${infositin[key].heure}`;
                                                                                    opt.innerHTML = `${infositin[key].heure}`;
                                                                                    hd.add(opt);
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
                                                        var postLhFi = selefi.split('/');
                                                        window.__venteSelectedHour = {
                                                            value: selefi,
                                                            idLh: postLhFi[0] || '',
                                                            heure: postLhFi[1] || '',
                                                            hasProg: false
                                                        };
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

;
/* --- addconfirme.js --- */
document.addEventListener('DOMContentLoaded', () => {

    window.__confHasTransit = false;
    window.__confLastHeuresVente = [];
    window.__confApplyTransitLegs = null;

    function __confFillHeuresVente(heures) {
        var hSel = document.querySelector('#heured');
        if (!hSel) return;
        hSel.options.length = 1;
        var list = Array.isArray(heures) ? heures : [];
        var bySlot = {};
        var order = [];
        for (var i = 0; i < list.length; i++) {
            var hr = list[i];
            if (!hr || hr.id_ligneheure == null || hr.id_ligneheure === '') continue;
            var hh = String(hr.heure || '').trim();
            var slot = hh + '|' + String(hr.id_ligneheure);
            // Sans date côté vente directe : dédupliquer par heure (libellé affiché).
            var slotVis = hh || slot;
            if (bySlot[slotVis]) continue;
            bySlot[slotVis] = hr;
            order.push(slotVis);
        }
        for (var j = 0; j < order.length; j++) {
            var hr2 = bySlot[order[j]];
            var opt = document.createElement('option');
            var hasProg = !!(hr2.has_programme === true || hr2.has_programme === 1 || hr2.has_programme === '1');
            if (hasProg && hr2.code_progr) {
                var tfHv = (hr2.typetarif != null && String(hr2.typetarif).trim() !== '') ? String(hr2.typetarif) : '1';
                opt.value = String(hr2.code_progr) + '/' + tfHv + '/' + String(hr2.id_ligneheure);
            } else {
                opt.value = String(hr2.id_ligneheure) + '/' + String(hr2.heure || '');
            }
            opt.setAttribute('data-has-programme', hasProg ? '1' : '0');
            if (hr2.heure) opt.setAttribute('data-heure', String(hr2.heure));
            opt.innerHTML = hr2.heure || '';
            hSel.add(opt);
        }
    }

    function __confSetDisp(id, on) {
        var el = document.getElementById(id);
        if (el) el.style.display = on ? 'block' : 'none';
    }

    function __confShowDirectHourUi() {
        var hideIds = [
            'iddeptranscf1','transitedepargarecf1','iddeptranscf2','transitedepargarecf2',
            'iddeptranscf3','transitedepargarecf3','iddeptranscf4','transitedepargarecf4',
            'arritincf1','arritincf2','arritincf3','heureitincf','heureitincf1','heureitincf2','heureitincf3',
            'hdepartitinecf','hdepartitinecf2','lignesitinerairecf','lignecf1',
            'siegitinecf','siegitinecf1','siegitinecf2','siegitinecf3',
            'psiegesitinescf','psiegesitinescf1','psiegesitinescf2','psiegesitinescf3',
            'quartiercf1','quartiercf2','quartiercf3','idquartcf1','idquartcf2','idquartcf3',
            'idcheminscf','idcheminscf1','idcheminscf2','idcheminsheurcf','idcheminsheurcf1','idcheminsheurcf2',
            'heureleg1cf','siegleg1cf'
        ];
        for (var i = 0; i < hideIds.length; i++) {
            __confSetDisp(hideIds[i], false);
        }
        var tran = document.querySelector('#trancf');
        if (tran) tran.style.display = 'none';
        if (typeof window.__confSetMainEscaleVisible === 'function') window.__confSetMainEscaleVisible(true);
        ['heured','depsieg'].forEach(function (id) { __confSetDisp(id, true); });
        var nbr = document.querySelector('#nbrtranscf');
        if (nbr) nbr.value = '';
    }

    /** Même schéma que la vente guichet : masquer heure/siège directs, afficher chaque jambe. */
    function __confShowTransitLegsUi(n) {
        n = parseInt(n, 10) || 0;
        var savedNbr = document.querySelector('#nbrtranscf') ? document.querySelector('#nbrtranscf').value : '';
        __confShowDirectHourUi();
        var nbrEl = document.querySelector('#nbrtranscf');
        if (nbrEl) nbrEl.value = savedNbr;
        if (n < 2) return;
        // Plus de siegdispo d'axe : #heured/#depsieg deviennent heure/siège de la jambe 1.
        ['heured','depsieg'].forEach(function (id) { __confSetDisp(id, true); });
        var h1 = document.getElementById('heured');
        if (h1 && h1.parentNode && !document.getElementById('heureleg1cf')) {
            var lab1 = document.createElement('label');
            lab1.id = 'heureleg1cf';
            lab1.textContent = 'Heure transite1';
            h1.parentNode.insertBefore(lab1, h1);
        }
        var s1 = document.getElementById('depsieg');
        if (s1 && s1.parentNode && !document.getElementById('siegleg1cf')) {
            var labS = document.createElement('label');
            labS.id = 'siegleg1cf';
            labS.textContent = 'Siège transite1';
            s1.parentNode.insertBefore(labS, s1);
        }
        var labels = {
            heureitincf: 'Heure transite2', siegitinecf: 'Siège transite2',
            heureitincf1: 'Heure transite3', siegitinecf1: 'Siège transite3',
            heureitincf2: 'Heure transite4', siegitinecf2: 'Siège transite4'
        };
        Object.keys(labels).forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.textContent = labels[id];
        });
        var show = [
            'trancf','lignecf1','lignesitinerairecf','idquartcf1','quartiercf1',
            'heured','depsieg',
            'iddeptranscf1','transitedepargarecf1',
            'arritincf1','idcheminscf',
            'heureitincf','hdepartitinecf','siegitinecf','psiegesitinescf',
            'iddeptranscf2','transitedepargarecf2'
        ];
        if (n >= 3) {
            show = show.concat([
                'arritincf2','idcheminscf1','idquartcf2','quartiercf2',
                'heureitincf1','idcheminsheurcf','siegitinecf1','psiegesitinescf1',
                'iddeptranscf3','transitedepargarecf3'
            ]);
        }
        if (n >= 4) {
            show = show.concat([
                'arritincf3','idcheminscf2','idquartcf3','quartiercf3',
                'heureitincf2','idcheminsheurcf1','siegitinecf2','psiegesitinescf2',
                'iddeptranscf4','transitedepargarecf4',
                'heureitincf3','idcheminsheurcf2','siegitinecf3','psiegesitinescf3'
            ]);
        }
        for (var i = 0; i < show.length; i++) __confSetDisp(show[i], true);
        if (typeof window.__confSetMainEscaleVisible === 'function') window.__confSetMainEscaleVisible(false);
    }

    function __confKickCheminSelect(selId) {
        var sel = document.querySelector(selId);
        if (sel && typeof sel.onchange === 'function' && sel.selectedIndex > 0) {
            sel.onchange();
        }
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
            '#idcheminsheurcf','#idcheminsheurcf1','#idcheminsheurcf2','#hdepartitinecf',
            '#psiegesitinescf','#psiegesitinescf1','#psiegesitinescf2','#psiegesitinescf3'
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

    function __confFormatDateShort(ymd) {
        if (!ymd || String(ymd).length < 10) return '';
        var p = String(ymd).slice(0, 10).split('-');
        return (p.length === 3) ? (p[2] + '/' + p[1]) : String(ymd).slice(0, 10);
    }

    /**
     * Libellé heure : ajoute JJ/MM si date ≠ voyage, ou si la liste couvre plusieurs jours
     * (évite « 07:00 » ×2 pour J et J+1).
     */
    function __confHeureOptionLabel(heure, dateProgr, voyageDate, forceDate) {
        var label = String(heure || '');
        var dprog = dateProgr ? String(dateProgr).slice(0, 10) : '';
        var vDate = voyageDate ? String(voyageDate).slice(0, 10) : '';
        var showDate = !!forceDate || (dprog && (!vDate || dprog !== vDate));
        if (showDate && dprog) {
            var short = __confFormatDateShort(dprog);
            if (short) label = label + ' — ' + short;
        }
        return label;
    }

    /** True si les lignes couvrent plus d'une date_progr. */
    function __confListSpansMultipleDays(rows) {
        var seen = {};
        var n = 0;
        for (var i = 0; i < rows.length; i++) {
            var d = rows[i] && rows[i].date_progr ? String(rows[i].date_progr).slice(0, 10) : '';
            if (!d || seen[d]) continue;
            seen[d] = 1;
            n++;
            if (n > 1) return true;
        }
        return false;
    }

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

    /** Typetarif pour chemintr (comme vente #tarifattrib). */
    function __confTarifattrib() {
        var el = document.querySelector('#tarifattribcf');
        var tf = el && String(el.value || '').trim() !== '' ? String(el.value).trim() : '1';
        if (el && String(el.value || '').trim() === '') {
            el.value = tf;
        }
        return tf;
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
        // Une option par créneau (date + heure) : plusieurs code_progr = même libellé sinon.
        var bySlot = {};
        var order = [];
        for (var i = 0; i < list.length; i++) {
            var row = list[i];
            var dprog = row.date_progr ? String(row.date_progr).slice(0, 10) : '';
            var hh = String(row.heure || '').trim();
            var slot = dprog + '|' + hh;
            if (!bySlot[slot]) {
                bySlot[slot] = row;
                order.push(slot);
            }
        }
        order.sort(function (a, b) {
            var ra = bySlot[a], rb = bySlot[b];
            var da = String(ra.date_progr || '').slice(0, 10);
            var db = String(rb.date_progr || '').slice(0, 10);
            if (da < db) return -1;
            if (da > db) return 1;
            return (__confHeureToMinutes(ra.heure) || 0) - (__confHeureToMinutes(rb.heure) || 0);
        });
        var slotRows = order.map(function (k) { return bySlot[k]; });
        var forceDateAf = __confListSpansMultipleDays(slotRows);
        var voyageDateAf = (document.querySelector('#actuel') && document.querySelector('#actuel').value)
            ? String(document.querySelector('#actuel').value).slice(0, 10)
            : pDate;
        for (var j = 0; j < order.length; j++) {
            var r = bySlot[order[j]];
            var opt = document.createElement('option');
            opt.value = `${r.code_progr}/${r.intervalle1}/${r.intervalle2}/${r.id_ligneheure}/${r.prix}`;
            opt.setAttribute('data-heure', r.heure || '');
            opt.setAttribute('data-date-progr', r.date_progr ? String(r.date_progr).slice(0, 10) : '');
            opt.innerHTML = __confHeureOptionLabel(r.heure, r.date_progr, voyageDateAf, forceDateAf);
            sel.add(opt);
        }
        if (sel.options.length > 1) {
            sel.selectedIndex = 1;
            if (typeof sel.onchange === 'function') sel.onchange();
        }
        if (selectId === '#hdepartitinecf') __confKickCheminSelect('#idcheminscf1');
        if (selectId === '#idcheminsheurcf') __confKickCheminSelect('#idcheminscf2');
    }

    function __confPrevFromLeg1() {
        var d = document.querySelector('#dateprtranscf');
        var h = document.querySelector('#hertranscf');
        var date = (d && d.value) ? String(d.value).slice(0, 10) : (document.querySelector('#actuel') ? document.querySelector('#actuel').value : '');
        var heure = (h && h.value) ? String(h.value) : '';
        if (!heure) {
            var hs = document.querySelector('#heured');
            if (hs && hs.selectedIndex > 0) {
                var opt = hs.options[hs.selectedIndex];
                if (opt.getAttribute('data-heure')) heure = opt.getAttribute('data-heure');
                else {
                    var parts = String(opt.value || '').split('/');
                    if (parts.length >= 2 && parts[1].indexOf(':') >= 0) heure = parts[1];
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
        ['#hdepartitinecf','#idcheminsheurcf','#idcheminsheurcf1','#idcheminsheurcf2'].forEach(function (s) {
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

    /** Remplit #depsieg via siegdisponible (jambe 1 transite). */
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
            __confKickCheminSelect('#idcheminscf');
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
            var list = [];
            if (Array.isArray(rows)) list = rows;
            else if (rows && typeof rows === 'object') {
                Object.keys(rows).forEach(function (k) { list.push(rows[k]); });
            }
            // Une option par créneau (date + heure) — verifheureitine peut renvoyer N programmes.
            var bySlot = {};
            var order = [];
            for (var i = 0; i < list.length; i++) {
                var row = list[i];
                if (!row || row.id_ligneheure == null) continue;
                var dprog = row.date_progr ? String(row.date_progr).slice(0, 10) : '';
                var hh = String(row.heure || '').trim();
                var slot = dprog + '|' + hh;
                if (!bySlot[slot]) {
                    bySlot[slot] = row;
                    order.push(slot);
                }
            }
            order.sort(function (a, b) {
                var ra = bySlot[a], rb = bySlot[b];
                var da = String(ra.date_progr || '').slice(0, 10);
                var db = String(rb.date_progr || '').slice(0, 10);
                if (da < db) return -1;
                if (da > db) return 1;
                return (__confHeureToMinutes(ra.heure) || 0) - (__confHeureToMinutes(rb.heure) || 0);
            });
            var slotRowsL1 = order.map(function (k) { return bySlot[k]; });
            var forceDateL1 = __confListSpansMultipleDays(slotRowsL1);
            for (var j = 0; j < order.length; j++) {
                var r = bySlot[order[j]];
                var opt = document.createElement('option');
                if (r.code_progr) {
                    var tfL1 = (r.typetarif != null && String(r.typetarif).trim() !== '') ? String(r.typetarif) : '1';
                    opt.value = String(r.code_progr) + '/' + tfL1 + '/' + String(r.id_ligneheure);
                } else {
                    opt.value = `${r.id_ligneheure}/${r.heure || ''}`;
                }
                opt.setAttribute('data-has-programme', '1');
                opt.setAttribute('data-transit-leg1', '1');
                opt.setAttribute('data-heure', r.heure || '');
                if (r.date_progr) opt.setAttribute('data-date-progr', String(r.date_progr).slice(0, 10));
                if (r.code_progr) opt.setAttribute('data-code-progr', String(r.code_progr));
                if (r.gareidentif) opt.setAttribute('data-gareidentif', String(r.gareidentif));
                opt.innerHTML = __confHeureOptionLabel(r.heure, r.date_progr, datedepart, forceDateL1);
                hSel.add(opt);
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
                                    document.querySelector('#nbrtranscf').value = i;
                                    __confShowTransitLegsUi(i);
                                    if (document.querySelector('#psiegesitinescf')) document.querySelector('#psiegesitinescf').options.length = 1;
                                    if (document.querySelector('#transitedepargarecf1')) document.querySelector('#transitedepargarecf1').options.length = 0;
                                    if (document.querySelector('#hdepartitinecf')) document.querySelector('#hdepartitinecf').options.length = 1;
                                    document.querySelector('#idcompgcf').value = `${donitinescf[0].id_compaga}`;
                                    __confFillLigne1Locked(donitinescf[0]);
                        
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
                                                var __tfCheminCf = __confTarifattrib();
                                                httpSiegeschemincf.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemintr/${prostranschemincf}/${datedepartcf}/${__tfCheminCf}`, true);
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
                                                
                                                var __tfCheminCf = __confTarifattrib();
                                                httpSiegeschemincf.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemintr/${prostranschemincf}/${datedepartcf}/${__tfCheminCf}`, true);
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

                                                var __tfCheminCf = __confTarifattrib();
                                                httpSiegeschemincf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemintr/${prostranschemincf1}/${datedepartcf}/${__tfCheminCf}`, true);
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
                                                
                                                var __tfCheminCf = __confTarifattrib();
                                                httpSiegeschemincf.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemintr/${prostranschemincf}/${datedepartcf}/${__tfCheminCf}`, true);
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

                                                var __tfCheminCf = __confTarifattrib();
                                                httpSiegeschemincf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemintr/${prostranschemincf1}/${datedepartcf}/${__tfCheminCf}`, true);
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

                                                var __tfCheminCf = __confTarifattrib();
                                                httpSiegeschemincf2.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemintr/${prostranschemincf2}/${datedepartcf}/${__tfCheminCf}`, true);
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
/* --- addreprog_unifie.js --- */
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
        __reprogSetAncreVisible(!window.__reprogState.isTransitTicket);
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
            + encodeURIComponent(st.sgid || '0') + '/1';
        var hh = __reprogHhmm(hhmm);
        if (hh) {
            url += '?heure=' + encodeURIComponent(hh);
        }
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

    function __reprogDirectsAsChemins(dateYmd) {
        return __reprogFilterByDate(dateYmd).map(function (row, idx) {
            var hh = __reprogHhmm(row.heure);
            var cie = __reprogCieName(row) || 'Compagnie';
            return {
                source: 'direct',
                id: 'direct-' + idx + '-' + (row.code_progr || ''),
                label: 'Direct — ' + cie + ' — ' + hh,
                etapes: [{
                    code_itineraires: row.ident_ligne || row.ligne_id || '',
                    nom_ligne: row.nom_ligne || '',
                    nom_itineraires: row.nom_ligne || '',
                    id_compaga: row.id_compaga || '',
                    heure: row.heure || hh,
                    code_gadest: row.code_gadest || row.gadest_lg || '',
                    typetarif: row.typetarif,
                    categori: row.categori || '',
                    _code_progr: row.code_progr || '',
                    _id_ligneheure: row.id_ligneheure || ''
                }]
            };
        });
    }

    function __reprogMergeItineraires(directs, chemins) {
        var out = __reprogRowsArray(directs).slice();
        __reprogRowsArray(chemins).forEach(function (ch) {
            if (!ch || ch.source === 'direct') return;
            var et = __reprogNormalizeEtapes(ch.etapes || ch.legs);
            if (et.length >= 2) {
                out.push(ch);
            }
        });
        return out;
    }

    function __reprogSetAncreVisible(show) {
        var h = __reprogQ('reprog_ancre_heure_wrap');
        var c = __reprogQ('reprog_cie_ancre_wrap');
        var disp = show ? '' : 'none';
        if (h) h.style.display = disp;
        if (c) c.style.display = disp;
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
            if (ch.source !== 'direct' && etapes.length) {
                label = etapes.map(function (e) {
                    return e.nom_itineraires || e.nom_ligne || e.code_itineraires || '';
                }).filter(Boolean).join(' → ') || label;
                label += ' (' + etapes.length + ' segment' + (etapes.length > 1 ? 's' : '') + ')';
            } else if (ch.source !== 'direct' && etapes.length === 1) {
                label += ' (1 segment)';
            } else if (etapes.length > 1 && ch.source === 'direct') {
                label += ' (' + etapes.length + ' segments)';
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
            var dateDef = (__reprogQ('datereprog_unifie') || {}).value || '';
            var wrap = document.createElement('div');
            wrap.className = 'reprog-seg';
            wrap.id = 'reprog_seg_' + idx;
            // Compagnie + date + heure + siège par segment
            wrap.innerHTML =
                '<h6>Segment ' + (idx + 1) + ' — ' + ligneNom + '</h6>'
                + '<div class="form-row">'
                + '<div class="form-group col-md-6 col-lg-3 mb-2">'
                + '<label class="small mb-0">Ligne</label>'
                + '<input class="form-control form-control-sm" type="text" id="reprog_seg_ligne_' + idx + '" value="'
                + String(ligneNom).replace(/"/g, '&quot;') + '" readonly>'
                + '</div>'
                + '<div class="form-group col-md-6 col-lg-3 mb-2">'
                + '<label class="small mb-0">Compagnie</label>'
                + '<select class="form-control form-control-sm" id="reprog_ui_cie_' + idx + '">'
                + '<option value="">Choisissez</option></select>'
                + '</div>'
                + '<div class="form-group col-md-6 col-lg-2 mb-2">'
                + '<label class="small mb-0">Date</label>'
                + '<input class="form-control form-control-sm" type="date" id="reprog_ui_date_' + idx + '" value="'
                + String(dateDef).replace(/"/g, '&quot;') + '">'
                + '</div>'
                + '<div class="form-group col-md-6 col-lg-2 mb-2">'
                + '<label class="small mb-0">Heure</label>'
                + '<select class="form-control form-control-sm" id="reprog_ui_heure_' + idx + '">'
                + '<option value="">Choisissez l\'heure</option></select>'
                + '</div>'
                + '<div class="form-group col-md-6 col-lg-2 mb-2">'
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

            var dateSeg = __reprogQ('reprog_ui_date_' + idx);
            if (dateSeg) {
                dateSeg.onchange = function () {
                    __reprogLoadSegmentCompanies(idx);
                };
            }
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
        var dateYmd = (__reprogQ('reprog_ui_date_' + idx) || {}).value
            || (__reprogQ('datereprog_unifie') || {}).value || '';
        if (!dateYmd) {
            __reprogSegErr(idx, 'Choisissez d’abord une date pour ce segment.');
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
        __reprogResetSelect(__reprogQ('heuredepartpunifie'), "Choisissez l'heure");
        __reprogResetSelect(__reprogQ('compagniepunifie'), 'Choisissez la compagnie');
        __reprogResetSelect(__reprogQ('numsiegepunifie'), 'Choisissez le siège');
        __reprogHideDirect();
        __reprogHideCorr();
        __reprogSetPost('', '', '');
        __reprogClearSegPosts();
        __reprogSetDirectInfo('');
        if (__reprogQ('smspunifie')) __reprogQ('smspunifie').style.display = 'none';
        if (!dateYmd) return;

        var st = window.__reprogState;

        // Ticket transit 2 codes : date → itinéraires possibles (axe des 2 tickets).
        if (st.isTransitTicket) {
            __reprogSetAncreVisible(false);
            if (!st.axe) {
                var boxA = __reprogQ('smspunifie');
                var errA = __reprogQ('erreurSmspunifie');
                if (boxA) boxA.style.display = 'block';
                if (errA) errA.textContent = 'Axe transit incomplet (départ / arrivée des 2 codes).';
                return;
            }
            __reprogFetchChemins(dateYmd, '', function (chemins) {
                var all = __reprogMergeItineraires(__reprogDirectsAsChemins(dateYmd), chemins);
                st.chemins = all;
                if (!all.length) {
                    var box = __reprogQ('smspunifie');
                    var err = __reprogQ('erreurSmspunifie');
                    if (box) box.style.display = 'block';
                    if (err) {
                        err.textContent = 'Aucun départ ni correspondance pour relier cet itinéraire à cette date.';
                    }
                    return;
                }
                __reprogShowCorrExclusive(
                    all,
                    'Itinéraires trouvés pour ' + st.axe + ' le ' + dateYmd + ' — choisissez-en un.'
                );
            });
            return;
        }

        // Ticket simple : date → heure → compagnie → siège.
        __reprogSetAncreVisible(true);
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
        __reprogHideDirect();
        __reprogClearSegPosts();
        if (etapes.length >= 2) {
            window.__reprogState.mode = 'transit';
            __reprogQ('reprog_mode_unifie').value = 'transit';
            __reprogQ('reprog_nbr_seg_unifie').value = String(etapes.length);
        } else {
            // Un seul segment (direct) : même UI segment, commit en mode direct.
            window.__reprogState.mode = 'direct';
            __reprogQ('reprog_mode_unifie').value = 'direct';
            __reprogQ('reprog_nbr_seg_unifie').value = '0';
        }
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
            var etapes = window.__reprogState.etapes || [];

            // Multi-segments (correspondances)
            if (window.__reprogState.mode === 'transit' && etapes.length >= 2) {
                var prep = __reprogPrepareTransitSubmit();
                if (!prep.ok) {
                    ev.preventDefault();
                    alert(prep.msg || 'Itinéraire incomplet.');
                    return false;
                }
                return true;
            }

            // Itinéraire à 1 segment (direct via liste) : posts classiques.
            if (etapes.length === 1 && window.__reprogState.segData
                && window.__reprogState.segData[0]) {
                var synced = __reprogSyncSegPost(0);
                if (!synced || !synced.prog || !synced.siege) {
                    ev.preventDefault();
                    alert('Complétez compagnie, heure et siège du segment.');
                    return false;
                }
                __reprogClearSegPosts();
                __reprogQ('reprog_mode_unifie').value = 'direct';
                __reprogQ('reprog_nbr_seg_unifie').value = '0';
                __reprogSetPost(synced.prog, synced.compaga, synced.siege);
                if (synced.row) {
                    __reprogQ('catreprogrammeunifie').value = synced.row.categori || '';
                }
                return true;
            }

            __reprogClearSegPosts();
            __reprogQ('reprog_mode_unifie').value = 'direct';
            var cie = __reprogQ('compagniepunifie');
            var sie = __reprogQ('numsiegepunifie');
            if (!cie || !cie.value || cie.value.indexOf('corr:') === 0 || !sie || !sie.value) {
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

;
/* --- addconfirmadmin.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addconfirmadmin').forEach(function (e) {
        document.querySelector('h3#admincTitle').innerHTML = `CONFIRMATION`;

        let c = document.querySelector('#adminconfirme_info');
        if (c !== null)
        c.onclick = () => {
            
            //verification code de confirmation
            let Request;
            
            if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                Request = new XMLHttpRequest();
            } else if (window.ActiveXObject) { // IE 6 and older
                Request = new ActiveXObject("Microsoft.XMLHTTP");
            }
            
            var codes = document.querySelector("#admincodeconfirm").value;

            Request.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifcodeconf/${codes}`, true);
            Request.onload = () => {
                const dons = JSON.parse(Request.responseText);
                    if (dons == null) {
                        document.querySelector('#adminmessagep').style.display = 'block';
                        document.querySelector('#adminerreurMessagep').innerHTML = `Cet ticket ne peut pas être confirmé ici.`;
                        document.querySelector('#adminheured').style.display = 'none';
                        document.querySelector('#admindepsieg').style.display = 'none';
                        document.querySelector('#adminquartconf').style.display = 'none';
                        document.querySelector('#adminnomp').innerText = ``;
                        document.querySelector('#adminprenomp').innerText = ``;
                        document.querySelector('#admincontactp').innerHTML = ``;
                        document.querySelector('#adminrefp').innerHTML = ``;
                        document.querySelector('#admindirectionp').innerHTML = ``;
                        document.querySelector('#admincodecp').innerHTML = ``;
                        document.querySelector('#axeconfirm').style.display = 'none';
                        document.querySelector('#ligneconflg').value = '';
                    }
                    else 
                    {
                        
                        if (Object.entries(dons).length >= 1){
                            document.querySelector('#adminerreurMessagep').innerHTML = '';
                            document.querySelector('#adminheured').style.display = 'block';
                            document.querySelector('#admindepsieg').style.display = 'block';
                            document.querySelector('#adminquartconf').style.display = 'block';
                            document.querySelector('#axeconfirm').style.display = 'block';
                            document.querySelector('#adminnomp').innerText = `NOM: ${dons.nom_client}`;
                            document.querySelector('#adminprenomp').innerText = `PRENOM: ${dons.prenom_client}`;
                            document.querySelector('#admincontactp').innerHTML = `CONTACT: ${dons.contact_client}`;
                            document.querySelector('#adminrefp').innerHTML = `REFERENCE CNIB: ${dons.num_CNIB}`;
                            document.querySelector('#admindirectionp').innerHTML = `AXE: ${dons.nom_ligne}`;
                            document.querySelector('#admincodecp').innerHTML = `CODE VENTE: ${dons.code_non_pass}`;
                            document.querySelector('#adminpassep').value = `${dons.code_non_pass}`;
                            document.querySelector('#adminpascodetick').value = `${dons.codeticket}`;
                            document.querySelector('#adminclientidp').value = `${dons.id_client_npass}`;
                            document.querySelector('#adminpasnomp').value = `${dons.nom_client}`;
                            document.querySelector('#adminpasprenomp').value = `${dons.prenom_client}`;
                            document.querySelector('#adminpascontactp').value = `${dons.contact_client}`;
                            document.querySelector('#adminpascnibp').value = `${dons.num_CNIB}`;
                            document.querySelector('#adminpasdatep').value = `${dons.date_delivre}`;
                            document.querySelector('#adcommentclient').value = `${dons.comment_client}`;
                            document.querySelector('#adminlieu').value = `${dons.lieu_delivre}`;
                            document.querySelector('#admimtype').value = `${dons.type_client}`;
                            document.querySelector('#dateventeconf').value = `${dons.datevente}`;
                            document.querySelector('#axeligneconf').value = `${dons.id_ligne_pass}`;
                            document.querySelector('#ligneconflg').value = `${dons.nom_ligne}`;
                            document.querySelector('#admincodecpas').value = `${dons.code_non_pass}`;
                            document.querySelector('#adlignehconf').value = `${dons.id_ligneheure}`;
                            document.querySelector('#admincodeconfi').value = `${dons.tamponcod}`;


                        } 
                        else 
                        {
                            document.querySelector('#adminheured').style.display = 'none';
                            document.querySelector('#admindepsieg').style.display = 'none';
                            document.querySelector('#adminquartconf').style.display = 'none';
                            document.querySelector('#axeconfirm').style.display = 'none';
                        }
                        
                                let Requestslg = new XMLHttpRequest();
                                    const confirheurelg = document.querySelector('#ligneconflg').value;
                                    var postmob = confirheurelg.split('-');
                                    var avmob = postmob[0];
                                    var apmob = postmob[1];
                                    Requestslg.open('GET', window.location.origin + `${APP_ROOT}/confirmation/veriflignelg/${apmob}-${avmob}`, true);
                                    Requestslg.onload = () => {
                                        const datas2lg = JSON.parse(Requestslg.responseText);
                                        if (Object.entries(datas2lg).length >= 1) {
                                    for (let key in Object.entries(datas2lg)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${datas2lg.ident_ligne}`;
                                        opt.innerHTML = `${datas2lg.nom_ligne}`;
                                        document.querySelector('#axeconfirm').add(opt);
                                        
                                        
                                    }
                                }else{
                                    document.querySelector('#axeconfirm').options.length = 1;
                                }
                            };
                            Requestslg.setRequestHeader('Content-Type', 'application/json');
                            Requestslg.send();
                       
                            
                                            
                            let axeselectconf = document.querySelector('#axeconfirm');
                            if (axeselectconf !== null)
                                axeselectconf.onchange = () => 
                                {
                                       
                                            var datdepart = document.querySelector('#dateventeconf').value;
                                            var datdepartactu = document.querySelector('#datactu').value;
                                            var date1  = new Date(datdepart);
                                            var date2 = new Date(datdepartactu);
                                            // différence des heures
                                            var time_diff = date2.getTime() - date1.getTime();
                                                // différence de jours
                                            const days_Diff = time_diff / (1000 * 3600 * 24);
                                            if(days_Diff < 28 || days_Diff < 29 || days_Diff < 30 || days_Diff < 31)    
                                            {
                                                const heureaxeconf = document.querySelector('#axeconfirm').options[document.querySelector('#axeconfirm').options.selectedIndex].value;
                                    
                                                let Requests = new XMLHttpRequest();
                                                const confirheure = document.querySelector('#axeconfirm').
                                                options[document.querySelector('#axeconfirm').options.selectedIndex].value;
                                                
                                                var dateactuel = document.querySelector('#datactu').value;
                                                Requests.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfprog/${confirheure}/${dateactuel}`, true);
                                                Requests.onload = () => {
                                                    const datas2 = JSON.parse(Requests.responseText);
                                                    if (Object.entries(datas2).length >= 1) {
                                                        for (let key in Object.entries(datas2)) {
                                                            let opt = document.createElement('option');
                                                            opt.value = `${datas2[key].code_progr}/${datas2[key].typetarif}`;
                                                            opt.innerHTML = `${datas2[key].heure}/${datas2[key].date_progr}`;
                                                            document.querySelector('#adminheured').add(opt);
                                                            
                                                            
                                                        }
                                                    }else{
                                                        document.querySelector('#adminheured').options.length = 1;
                                                    }
                                                };
                                                Requests.setRequestHeader('Content-Type', 'application/json');
                                                Requests.send();
                                            
                                                var dateactuel = document.querySelector('#datactu').value;
                                                
                                                let httpRequetesquart = new XMLHttpRequest();
                                                    httpRequetesquart.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfquart/${heureaxeconf}`, true);
                                                httpRequetesquart.onload = () => {
                                                    const dataq = JSON.parse(httpRequetesquart.responseText);
                                                    if(dataq == ''){
                                                        document.querySelector('#adminquartconf').options.length = 1;
                                                    }else{
                                                        if (Object.entries(dataq).length >= 1) {
                                                                    
                                                            for (let key in Object.entries(dataq)) {
                                                                let opt = document.createElement('option');
                                                                opt.value = `${dataq[key].nom_quartier}`;
                                                                opt.innerHTML = `${dataq[key].nom_quartier}`;
                                                                document.querySelector('#adminquartconf').add(opt);
                                                            }
                                                        } else {
                                                            document.querySelector('#adminquartconf').options.length = 1;
                                                        }
                                                    }
                                                        
                                                            
                                                };
                                                httpRequetesquart.setRequestHeader('Content-Type', 'application/json');
                                                httpRequetesquart.send();
                                            }
                                            else
                                            {
                                                document.querySelector('#adminheured').style.display = 'none';
                                                document.querySelector('#admindepsieg').style.display = 'none';
                                                document.querySelector('#adminquartconf').style.display = 'none';
                                                document.querySelector('#adminnomp').innerText = ``;
                                                document.querySelector('#adminprenomp').innerText = ``;
                                                document.querySelector('#admincontactp').innerHTML = ``;
                                                document.querySelector('#adminrefp').innerHTML = ``;
                                                document.querySelector('#admindirectionp').innerHTML = ``;
                                                document.querySelector('#admincodecp').innerHTML = ``;
                                                document.querySelector('#billet').style.display = 'block';
                                                document.querySelector('#billetSms').innerHTML = `Billet non valable, la durée de validité est dépassée.`;
            
                                            }
                                };
                                            
                                            
                    }
               
            };
            Request.setRequestHeader('Content-Type', 'application/json');
            Request.send(); 
        };

        let heurdeprt = document.querySelector('#adminheured');
        if (heurdeprt !== null)
            heurdeprt.onchange = () => {
                
                document.querySelector('#admindepsieg').options.length = 1;
                const Requeste = new XMLHttpRequest();
                const selectorp = document.querySelector('#adminheured').options[document.querySelector('#adminheured').
                options.selectedIndex].value;
                var selectorp1 = selectorp.split('/');
                var selectorp2 = selectorp1[0];
                var selectorp3 = selectorp1[1];
                Requeste.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/siegdispo/${selectorp2}`, true);
                Requeste.onload = () => {
                    const datasgc = JSON.parse(Requeste.responseText);
                    if (Object.entries(datasgc).length >= 1) {
                        for (let key in Object.entries(datasgc)) {
                            
                            document.querySelector('#adcaissepvend_').value = `${datasgc[key].intervalle1}`;
                            document.querySelector('#adcaissedpvend_').value = `${datasgc[key].intervalle2}`;
                            document.querySelector('#addirectid').value = `${datasgc[key].nom_ligne}`;
                            document.querySelector('#adconfheure').value = `${datasgc[key].heure}`;
                            document.querySelector('#addateconfirme').value = `${datasgc[key].date_progr}`;
                            document.querySelector('#adcatego').value = `${datasgc[key].categori}`;
                            document.querySelector('#adlignehconf').value = `${datasgc[key].id_ligneheure}`;
                            document.querySelector('#adprogramconf').value = `${datasgc[key].code_progr}`;
                        }
                    } 
                    const Requestbis = new XMLHttpRequest();
                            const pldebut = document.querySelector('#adcaissepvend_').value;
                            const plfin = document.querySelector('#adcaissedpvend_').value;
                            const cfdir = document.querySelector('#addirectid').value;
                            const hconfir = document.querySelector('#adconfheure').value;
                            const dconfirme = document.querySelector('#addateconfirme').value;
                    Requestbis.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${selectorp2}/${dconfirme}/${cfdir}/${hconfir}/${pldebut}/${plfin}`, true);
                    Requestbis.onload = () => {
                        const datasgcbis = JSON.parse(Requestbis.responseText);
                        if (Object.entries(datasgcbis).length >= 1) {
                            for (let key in Object.entries(datasgcbis)) {
                                let opt = document.createElement('option');
                                opt.value = `${datasgcbis[key].siege_num}`;
                                opt.innerHTML = `${datasgcbis[key].siege_num}`;
                                document.querySelector('#admindepsieg').add(opt);
                            }
                        } else {
                            document.querySelector('#admindepsieg').options.length = 1;
                        }
                    };
                    Requestbis.setRequestHeader('Content-Type', 'application/json');
                    Requestbis.send();
                };
                Requeste.setRequestHeader('Content-Type', 'application/json');
                Requeste.send();
            };

            let depsiegconf = document.querySelector('#admindepsieg');
            if (depsiegconf !== null)
            depsiegconf.onchange = () => {
                    
                    let Requestsiegevenduconf;
                    
                    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                        Requestsiegevenduconf = new XMLHttpRequest();
                    } else if (window.ActiveXObject) { // IE 6 and older
                        Requestsiegevenduconf = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                    const dp_progconf = document.querySelector('#adprogramconf').value;
                    const dp_siegeconf = document.querySelector('#admindepsieg').options[document.querySelector('#admindepsieg').options.selectedIndex].value;
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
                                            document.querySelector('#adminmessconf').style.display = 'none';
                                            if (Object.entries(dongconf).length >= 1)
                                        {
                                            for (let key in Object.entries(dongconf)) {
                                                document.querySelector('#adminidtampoconf').value = `${dongconf[key].idtamp}`;                    
                                                document.querySelector('#adminsiegselectconf').value = `${dongconf[key].numsieg}`;
                                            }

                                        }
                                        
                                        };
                                        httpSiegsconf.setRequestHeader('Content-Type', 'application/json');
                                        httpSiegsconf.send();
                                    }
                                    else {
                                        document.querySelector('#admindepsieg').value = '';     
                                        if (Object.entries(confdonsieg).length >= 1)
                                        {
                                            for (let key in Object.entries(confdonsieg)) {
                                                document.querySelector('#adminidtampoconf').value = `${confdonsieg[key].idtamp}`;                    
                                                document.querySelector('#adminsiegselectconf').value = `${confdonsieg[key].numsieg}`;
                                            }

                                        }
                                        document.querySelector('#adminmessconf').style.display = 'block';
                                        document.querySelector('#adminerreurMessconf').innerHTML = `Siege déjà utilisé.`; 
                                    }
                    };
                    Requestsiegevenduconf.setRequestHeader('content-Type', 'text/json');
                    Requestsiegevenduconf.send();
                };
            //bouton annuler
                butoncliconf = document.querySelector('#adminconfreset');
                if (butoncliconf !== null) {
                    butoncliconf.onclick = () => 
                    {
                        let httpSiegeselectconf;
                        httpSiegeselectconf = new XMLHttpRequest();
                        const siegselectconf = document.querySelector('#adminsiegselectconf').value;
                        const idtapconf = document.querySelector('#adminidtampoconf').value;
                        httpSiegeselectconf.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtapconf}/${siegselectconf}`, true);
                        httpSiegeselectconf.onload = () => 
                        {
                            const donselectconf = JSON.parse(httpSiegeselectconf.responseText);
                            console.debug(`${typeof donselectconf} - ${donselectconf.attributes}`, console.memory);
                            document.querySelector('#adminmessconf').style.display = 'none';
                            
                        };
                        httpSiegeselectconf.setRequestHeader('Content-Type', 'application/json');
                        httpSiegeselectconf.send();
    
                    
                    };
                }       
                       
        e.onclick = function () {
            let adcForm = document.querySelector('#admincForm');
            adcForm.setAttribute('action', `${APP_ROOT}/Confirmation/adminconfirme/${e.dataset.ckey}`);
        }
    })
});
;
/* --- addconfirmbon.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addconfirmbon').forEach(function (e) {
        document.querySelector('h3#bonconfTitle').innerHTML = `CONFIRMATION BON`;

        let cb = document.querySelector('#bonconfirme_info');
        if (cb !== null)
        cb.onclick = () => {
            
            //verification code de confirmation
            let Requestbon;
            
            if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                Requestbon = new XMLHttpRequest();
            } else if (window.ActiveXObject) { // IE 6 and older
                Requestbon = new ActiveXObject("Microsoft.XMLHTTP");
            }
            
            var codeb = document.querySelector("#boncodeconfirm").value;

            Requestbon.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifcodebon/${codeb}`, true);
            Requestbon.onload = () => {
                const donsbon = JSON.parse(Requestbon.responseText);
                    if (donsbon == null) {
                        document.querySelector('#bonmessagep').style.display = 'block';
                        document.querySelector('#bonerreurMessagep').innerHTML = `Cet bon ne peut pas être confirmé ici.`;
                        document.querySelector('#bonheured').style.display = 'none';
                        document.querySelector('#bondepsieg').style.display = 'none';
                        document.querySelector('#bonquartconf').style.display = 'none';
                        document.querySelector('#bonnomp').innerText = ``;
                        document.querySelector('#bonprenomp').innerText = ``;
                        document.querySelector('#boncontactp').innerHTML = ``;
                        document.querySelector('#bonrefp').innerHTML = ``;
                        document.querySelector('#bondirectionp').innerHTML = ``;
                        document.querySelector('#boncodecp').innerHTML = ``;
                        document.querySelector('#bonaxeconfirm').style.display = 'none';
                        document.querySelector('#bonligneconflg').value = '';
                        document.querySelector('#bonlignehconf').value = '';
                    }
                    else 
                    {
                        
                        if (Object.entries(donsbon).length >= 1){
                            document.querySelector('#bonerreurMessagep').innerHTML = '';
                            document.querySelector('#bonheured').style.display = 'block';
                            document.querySelector('#bondepsieg').style.display = 'block';
                            document.querySelector('#bonquartconf').style.display = 'block';
                            document.querySelector('#bonaxeconfirm').style.display = 'block';
                            document.querySelector('#bonnomp').innerText = `NOM: ${donsbon.nom_client}`;
                            document.querySelector('#bonprenomp').innerText = `PRENOM: ${donsbon.prenom_client}`;
                            document.querySelector('#boncontactp').innerHTML = `CONTACT: ${donsbon.contact_client}`;
                            document.querySelector('#bonrefp').innerHTML = `REFERENCE CNIB: ${donsbon.num_CNIB}`;
                            document.querySelector('#bondirectionp').innerHTML = `AXE: ${donsbon.nom_gaep}-${donsbon.nom_gadest}`;
                            document.querySelector('#boncodecp').innerHTML = `CODE BON: ${donsbon.bonsecondid}-${donsbon.code_bon}`;
                            document.querySelector('#bonpassep').value = `${donsbon.idbon}`;
                            document.querySelector('#bonpascodetick').value = `${donsbon.bonsecondid}`;
                            document.querySelector('#bonclientidp').value = `${donsbon.id_client_bon}`;
                            document.querySelector('#bonpasnomp').value = `${donsbon.nom_client}`;
                            document.querySelector('#bonpasprenomp').value = `${donsbon.prenom_client}`;
                            document.querySelector('#bonpascontactp').value = `${donsbon.contact_client}`;
                            document.querySelector('#bonpascnibp').value = `${donsbon.num_CNIB}`;
                            document.querySelector('#bonpasdatep').value = `${donsbon.date_delivre}`;
                            document.querySelector('#boncommentclient').value = `${donsbon.comment_client}`;
                            document.querySelector('#bonlieu').value = `${donsbon.lieu_delivre}`;
                            document.querySelector('#bontype').value = `${donsbon.type_client}`;
                            document.querySelector('#boncode').value = `${donsbon.bonsecondid}`;
                            document.querySelector('#bonaxeligneconf').value = `${donsbon.ligne_depart}-${donsbon.ligne_dest}`;
                            document.querySelector('#bonligneconflg').value = `${donsbon.nom_gaep}-${donsbon.nom_gadest}`;
                            document.querySelector('#boncodecpas').value = `${donsbon.bonsecondid}`;
                            document.querySelector('#bonlignehconf').value = `${donsbon.id_ligneheure}`;
                            document.querySelector('#boncodeconfi').value = `${donsbon.idbon}`;


                        } 
                        else 
                        {
                            document.querySelector('#bonheured').style.display = 'none';
                            document.querySelector('#bondepsieg').style.display = 'none';
                            document.querySelector('#bonquartconf').style.display = 'none';
                            document.querySelector('#bonaxeconfirm').style.display = 'none';
                        }
                        
                                let Requestslgbon = new XMLHttpRequest();
                                    const confirheurelgbon = document.querySelector('#bonligneconflg').value;
                                    //var postmobbon = confirheurelgbon.split('-');
                                    //var avmobbon = postmobbon[0];
                                    //var apmobbon = postmobbon[1];
                                    Requestslgbon.open('GET', window.location.origin + `${APP_ROOT}/confirmation/veriflignelg/${confirheurelgbon}`, true);
                                    Requestslgbon.onload = () => {
                                        const datas2lgbon = JSON.parse(Requestslgbon.responseText);
                                        if (Object.entries(datas2lgbon).length >= 1) {
                                    for (let key in Object.entries(datas2lgbon)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${datas2lgbon.ident_ligne}`;
                                        opt.innerHTML = `${datas2lgbon.nom_ligne}`;
                                        document.querySelector('#bonaxeconfirm').add(opt);
                                        
                                        
                                    }
                                }else
                                {
                                    document.querySelector('#bonaxeconfirm').options.length = 1;
                                }
                            };
                            Requestslgbon.setRequestHeader('Content-Type', 'application/json');
                            Requestslgbon.send();
                       
                            
                                            
                            let axeselectconf = document.querySelector('#bonaxeconfirm');
                            if (axeselectconf !== null)
                                axeselectconf.onchange = () => 
                                {
                                       
                                                const heureaxeconfbon = document.querySelector('#bonaxeconfirm').options[document.querySelector('#bonaxeconfirm').options.selectedIndex].value;
                                    
                                                let Requestsbon = new XMLHttpRequest();
                                                const confirheurebon = document.querySelector('#bonaxeconfirm').
                                                options[document.querySelector('#bonaxeconfirm').options.selectedIndex].value;
                                                
                                                var dateactuelbon = document.querySelector('#bondatactu').value;
                                                Requestsbon.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfprog/${confirheurebon}/${dateactuelbon}`, true);
                                                Requestsbon.onload = () => {
                                                    const datas2 = JSON.parse(Requestsbon.responseText);
                                                    if (Object.entries(datas2).length >= 1) {
                                                        for (let key in Object.entries(datas2)) {
                                                            let opt = document.createElement('option');
                                                            opt.value = `${datas2[key].code_progr}/${datas2[key].typetarif}`;
                                                            opt.innerHTML = `${datas2[key].heure}/${datas2[key].date_progr}`;
                                                            document.querySelector('#bonheured').add(opt);
                                                            
                                                            
                                                        }
                                                    }else{
                                                        document.querySelector('#bonheured').options.length = 1;
                                                    }
                                                };
                                                Requestsbon.setRequestHeader('Content-Type', 'application/json');
                                                Requestsbon.send();
                                            
                                                var dateactuelbon = document.querySelector('#bondatactu').value;
                                                
                                                let httpRequetesquartbon = new XMLHttpRequest();
                                                    httpRequetesquartbon.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfquart/${heureaxeconfbon}`, true);
                                                httpRequetesquartbon.onload = () => {
                                                    const dataqbon = JSON.parse(httpRequetesquartbon.responseText);
                                                    if(dataqbon == ''){
                                                        document.querySelector('#bonquartconf').options.length = 1;
                                                    }else{
                                                        if (Object.entries(dataqbon).length >= 1) {
                                                                    
                                                            for (let key in Object.entries(dataqbon)) {
                                                                let opt = document.createElement('option');
                                                                opt.value = `${dataqbon[key].nom_quartier}`;
                                                                opt.innerHTML = `${dataqbon[key].nom_quartier}`;
                                                                document.querySelector('#bonquartconf').add(opt);
                                                            }
                                                        } else {
                                                            document.querySelector('#bonquartconf').options.length = 1;
                                                        }
                                                    }
                                                        
                                                            
                                                };
                                                httpRequetesquartbon.setRequestHeader('Content-Type', 'application/json');
                                                httpRequetesquartbon.send();
                                            
                                            
                                };
                                            
                                            
                    }
               
            };
            Requestbon.setRequestHeader('Content-Type', 'application/json');
            Requestbon.send(); 
        };

        let heurdeprtbon = document.querySelector('#bonheured');
        if (heurdeprtbon !== null)
            heurdeprtbon.onchange = () => {
                
                document.querySelector('#bondepsieg').options.length = 1;
                const Requestebon = new XMLHttpRequest();
                const selectorpbon = document.querySelector('#bonheured').options[document.querySelector('#bonheured').
                options.selectedIndex].value;
                var selectorp1bon = selectorpbon.split('/');
                var selectorp2bon = selectorp1bon[0];
                var selectorp3bon = selectorp1bon[1];
                Requestebon.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/siegdispo/${selectorp2bon}`, true);
                Requestebon.onload = () => {
                    const datasgcbon = JSON.parse(Requestebon.responseText);
                    if (Object.entries(datasgcbon).length >= 1) {
                        for (let key in Object.entries(datasgcbon)) {
                            
                            document.querySelector('#boncaissepvend_').value = `${datasgcbon[key].intervalle1}`;
                            document.querySelector('#boncaissedpvend_').value = `${datasgcbon[key].intervalle2}`;
                            document.querySelector('#bondirectid').value = `${datasgcbon[key].nom_ligne}`;
                            document.querySelector('#bonconfheure').value = `${datasgcbon[key].heure}`;
                            document.querySelector('#bondateconfirme').value = `${datasgcbon[key].date_progr}`;
                            document.querySelector('#boncatego').value = `${datasgcbon[key].categori}`;
                            document.querySelector('#bonlignehconf').value = `${datasgcbon[key].id_ligneheure}`;
                            document.querySelector('#bonprogramconf').value = `${datasgcbon[key].code_progr}`;
                        }
                    } 
                    const Requestbisbon = new XMLHttpRequest();
                            const pldebutbon = document.querySelector('#boncaissepvend_').value;
                            const plfinbon = document.querySelector('#boncaissedpvend_').value;
                            const cfdirbon = document.querySelector('#bondirectid').value;
                            const hconfirbon = document.querySelector('#bonconfheure').value;
                            const dconfirmebon = document.querySelector('#bondateconfirme').value;
                    Requestbisbon.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${selectorp2bon}/${dconfirmebon}/${cfdirbon}/${hconfirbon}/${pldebutbon}/${plfinbon}`, true);
                    Requestbisbon.onload = () => {
                        const datasgcbisbon = JSON.parse(Requestbisbon.responseText);
                        if (Object.entries(datasgcbisbon).length >= 1) {
                            for (let key in Object.entries(datasgcbisbon)) {
                                let opt = document.createElement('option');
                                opt.value = `${datasgcbisbon[key].siege_num}`;
                                opt.innerHTML = `${datasgcbisbon[key].siege_num}`;
                                document.querySelector('#bondepsieg').add(opt);
                            }
                        } else {
                            document.querySelector('#bondepsieg').options.length = 1;
                        }
                    };
                    Requestbisbon.setRequestHeader('Content-Type', 'application/json');
                    Requestbisbon.send();
                };
                Requestebon.setRequestHeader('Content-Type', 'application/json');
                Requestebon.send();
            };

            let depsiegconfbon = document.querySelector('#bondepsieg');
            if (depsiegconfbon !== null)
            depsiegconfbon.onchange = () => {
                    
                    let Requestsiegevenduconfbon;
                    
                    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                        Requestsiegevenduconfbon = new XMLHttpRequest();
                    } else if (window.ActiveXObject) { // IE 6 and older
                        Requestsiegevenduconfbon = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                    const dp_progconfbon = document.querySelector('#bonprogramconf').value;
                    const dp_siegeconfbon = document.querySelector('#bondepsieg').options[document.querySelector('#bondepsieg').options.selectedIndex].value;
                    Requestsiegevenduconfbon.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${dp_progconfbon}/${dp_siegeconfbon}`, true);
                    Requestsiegevenduconfbon.onload = () => 
                    {
                        
                            const confdonsiegbon = JSON.parse(Requestsiegevenduconfbon.responseText);
                            if (confdonsiegbon == '')
                                    {
                                        let httpSiegsconfbon;
                                        httpSiegsconfbon = new XMLHttpRequest();

                                        httpSiegsconfbon.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${dp_progconfbon}/${dp_siegeconfbon}`, true);
                                        httpSiegsconfbon.onload = () => 
                                        {
                                            const dongconfbon = JSON.parse(httpSiegsconfbon.responseText);
                                            document.querySelector('#bonmessconf').style.display = 'none';
                                            if (Object.entries(dongconfbon).length >= 1)
                                        {
                                            for (let key in Object.entries(dongconfbon)) {
                                                document.querySelector('#bonidtampoconf').value = `${dongconfbon[key].idtamp}`;                    
                                                document.querySelector('#bonsiegselectconf').value = `${dongconfbon[key].numsieg}`;
                                            }

                                        }
                                        
                                        };
                                        httpSiegsconfbon.setRequestHeader('Content-Type', 'application/json');
                                        httpSiegsconfbon.send();
                                    }
                                    else {
                                        document.querySelector('#bondepsieg').value = '';     
                                        if (Object.entries(confdonsiegbon).length >= 1)
                                        {
                                            for (let key in Object.entries(confdonsiegbon)) {
                                                document.querySelector('#bonidtampoconf').value = `${confdonsiegbon[key].idtamp}`;                    
                                                document.querySelector('#bonsiegselectconf').value = `${confdonsiegbon[key].numsieg}`;
                                            }

                                        }
                                        document.querySelector('#bonmessconf').style.display = 'block';
                                        document.querySelector('#bonerreurMessconf').innerHTML = `Siege déjà utilisé.`; 
                                    }
                    };
                    Requestsiegevenduconfbon.setRequestHeader('content-Type', 'text/json');
                    Requestsiegevenduconfbon.send();
                };
            //bouton annuler
                butoncliconfbon = document.querySelector('#bonconfreset');
                if (butoncliconfbon !== null) {
                    butoncliconfbon.onclick = () => 
                    {
                        let httpSiegeselectconfbon;
                        httpSiegeselectconfbon = new XMLHttpRequest();
                        const siegselectconfbon = document.querySelector('#bonsiegselectconf').value;
                        const idtapconfbon = document.querySelector('#bonidtampoconf').value;
                        httpSiegeselectconfbon.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtapconfbon}/${siegselectconfbon}`, true);
                        httpSiegeselectconfbon.onload = () => 
                        {
                            const donselectconfbon = JSON.parse(httpSiegeselectconfbon.responseText);
                            console.debug(`${typeof donselectconfbon} - ${donselectconfbon.attributes}`, console.memory);
                            document.querySelector('#bonmessconf').style.display = 'none';
                            
                        };
                        httpSiegeselectconfbon.setRequestHeader('Content-Type', 'application/json');
                        httpSiegeselectconfbon.send();
    
                    
                    };
                }       
                       
        e.onclick = function () {
            let adcbForm = document.querySelector('#boncForm');
            adcbForm.setAttribute('action', `${APP_ROOT}/Confirmation/bonconfirme/${e.dataset.cle_compagnie}`);
        }
    })
});
;
/* --- addconfirmcarte.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addconfirmcarte').forEach(function (e) {
        document.querySelector('h3#carteconfTitle').innerHTML = `CONFIRMATION CARTE`;

        let ctbinf = document.querySelector('#carteconfirme_info');
        if (ctbinf !== null)
        ctbinf.onclick = () => {
            
            //verification code de confirmation
            let httpRequestcarte;
            
            if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                httpRequestcarte = new XMLHttpRequest();
            } else if (window.ActiveXObject) { // IE 6 and older
                httpRequestcarte = new ActiveXObject("Microsoft.XMLHTTP");
            }
            
            var codecart = document.querySelector("#cartecodeconfirm").value;

            httpRequestcarte.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifcodecarte/${codecart}`, true);
            httpRequestcarte.onload = () => {
                const donscart = JSON.parse(httpRequestcarte.responseText);
                    if (donscart == null) {
                        document.querySelector('#cartemessagep').style.display = 'block';
                        document.querySelector('#carteerreurMessagep').innerHTML = `Cette carte ne peut pas être confirmée ici.`;
                        document.querySelector('#carteheured').style.display = 'none';
                        document.querySelector('#cartedepsieg').style.display = 'none';
                        document.querySelector('#cartequartconf').style.display = 'none';
                        document.querySelector('#cartenomp').innerText = ``;
                        document.querySelector('#carteprenomp').innerText = ``;
                        document.querySelector('#cartecontactp').innerHTML = ``;
                        document.querySelector('#carterefp').innerHTML = ``;
                        document.querySelector('#cartecodecp').innerHTML = ``;
                        document.querySelector('#carteaxeconfirm').style.display = 'none';
                        document.querySelector('#carteligneconflg').value = '';
                        document.querySelector('#cartelignehconf').value = '';
                        document.querySelector('#creditcarteid').value = '';
                    }
                    else 
                    {
                        
                        if (Object.entries(donscart).length >= 1){
                            document.querySelector('#carteerreurMessagep').innerHTML = '';
                            document.querySelector('#carteheured').style.display = 'block';
                            document.querySelector('#cartedepsieg').style.display = 'block';
                            document.querySelector('#cartequartconf').style.display = 'block';
                            document.querySelector('#carteaxeconfirm').style.display = 'block';
                            document.querySelector('#cartenomp').innerText = `NOM: ${donscart.nom_client}`;
                            document.querySelector('#carteprenomp').innerText = `PRENOM: ${donscart.prenom_client}`;
                            document.querySelector('#cartecontactp').innerHTML = `CONTACT: ${donscart.contact_client}`;
                            document.querySelector('#carterefp').innerHTML = `REFERENCE CNIB: ${donscart.num_CNIB}`;
                            document.querySelector('#cartecodecp').innerHTML = `CODE CARTE: ${donscart.id_carte}-${donscart.num_carte}`;
                            document.querySelector('#cartepassep').value = `${donscart.id_carte}`;
                            document.querySelector('#cartepascodetick').value = `${donscart.num_carte }`;
                            document.querySelector('#carteclientidp').value = `${donscart.idcarte_client}`;
                            document.querySelector('#cartetype').value = `${donscart.type_client}`;
                            document.querySelector('#cartecode').value = `${donscart.num_carte}`;
                            document.querySelector('#cartecodecpas').value = `${donscart.num_carte}`;
                            document.querySelector('#cartelignehconf').value = `${donscart.id_ligneheure}`;
                            document.querySelector('#cartecodeconfi').value = `${donscart.id_carte}`;
                            document.querySelector('#cartecomptid').value = `${donscart.comptidcl}`;
                            document.querySelector('#creditcarteid').value = `${donscart.debitecompte}`;
                        } 
                        else 
                        {
                            document.querySelector('#carteheured').style.display = 'none';
                            document.querySelector('#cartedepsieg').style.display = 'none';
                            document.querySelector('#cartequartconf').style.display = 'none';
                            document.querySelector('#carteaxeconfirm').style.display = 'none';
                        }
                        
                    }
               
            };
            httpRequestcarte.setRequestHeader('Content-Type', 'application/json');
            httpRequestcarte.send(); 
        };                    
        let axeselectconf = document.querySelector('#carteaxeconfirm');
        if (axeselectconf !== null)
            axeselectconf.onchange = () => 
            {
                   document.querySelector('#cartequartconf').options.length = 1;
                const heureaxeconfcart = document.querySelector('#carteaxeconfirm').options[document.querySelector('#carteaxeconfirm').options.selectedIndex].value;
    
                let httpRequestscart = new XMLHttpRequest();
                const confirheurecart = document.querySelector('#carteaxeconfirm').
                options[document.querySelector('#carteaxeconfirm').options.selectedIndex].value;
                
                var dateactuelcart = document.querySelector('#cartedatactu').value;
                httpRequestscart.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfprog/${confirheurecart}/${dateactuelcart}`, true);
                httpRequestscart.onload = () => {
                    const data = JSON.parse(httpRequestscart.responseText);
                    if (Object.entries(data).length >= 1) {
                        for (let key in Object.entries(data)) {
                            let opt = document.createElement('option');
                            opt.value = `${data[key].code_progr}/${data[key].typetarif}`;
                            opt.innerHTML = `${data[key].heure}/${data[key].date_progr}`;
                            document.querySelector('#carteheured').add(opt);
                            
                            
                        }
                    }else{
                        document.querySelector('#carteheured').options.length = 1;
                    }
                };
                httpRequestscart.setRequestHeader('Content-Type', 'application/json');
                httpRequestscart.send();
            
                var dateactuelcart = document.querySelector('#cartedatactu').value;
                
                let httpRequetesquartcart = new XMLHttpRequest();
                    httpRequetesquartcart.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfquart/${heureaxeconfcart}`, true);
                httpRequetesquartcart.onload = () => {
                    const dataqcart = JSON.parse(httpRequetesquartcart.responseText);
                    if(dataqcart == ''){
                        document.querySelector('#cartequartconf').options.length = 1;
                    }else{
                        if (Object.entries(dataqcart).length >= 1) {
                                    
                            for (let key in Object.entries(dataqcart)) {
                                let opt = document.createElement('option');
                                opt.value = `${dataqcart[key].nom_quartier}`;
                                opt.innerHTML = `${dataqcart[key].nom_quartier}`;
                                document.querySelector('#cartequartconf').add(opt);
                            }
                        } else {
                            document.querySelector('#cartequartconf').options.length = 1;
                        }
                    }
                        
                            
                };
                httpRequetesquartcart.setRequestHeader('Content-Type', 'application/json');
                httpRequetesquartcart.send();
                        
                        
            };
                        
                                        
        let heurdeprtcart = document.querySelector('#carteheured');
        if (heurdeprtcart !== null)
            heurdeprtcart.onchange = () => {
                
                document.querySelector('#cartedepsieg').options.length = 1;
                const httpRequestecart = new XMLHttpRequest();
                const selectorpcart = document.querySelector('#carteheured').options[document.querySelector('#carteheured').
                options.selectedIndex].value;
                var selectorp1cart = selectorpcart.split('/');
                var selectorp2cart = selectorp1cart[0];
                var selectorp3cart = selectorp1cart[1];
                httpRequestecart.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/siegdispo/${selectorp2cart}`, true);
                httpRequestecart.onload = () => {
                    const datasgccart = JSON.parse(httpRequestecart.responseText);
                    if (Object.entries(datasgccart).length >= 1) {
                        for (let key in Object.entries(datasgccart)) {
                            
                            document.querySelector('#cartecaissepvend_').value = `${datasgccart[key].intervalle1}`;
                            document.querySelector('#cartecaissedpvend_').value = `${datasgccart[key].intervalle2}`;
                            document.querySelector('#cartedirectid').value = `${datasgccart[key].nom_ligne}`;
                            document.querySelector('#carteconfheure').value = `${datasgccart[key].heure}`;
                            document.querySelector('#cartedateconfirme').value = `${datasgccart[key].date_progr}`;
                            document.querySelector('#cartecatego').value = `${datasgccart[key].categori}`;
                            document.querySelector('#cartelignehconf').value = `${datasgccart[key].id_ligneheure}`;
                            document.querySelector('#carteprogramconf').value = `${datasgccart[key].code_progr}`;
                        }
                    } 
                    const httpRequestbiscart = new XMLHttpRequest();
                            const pldebutcart = document.querySelector('#cartecaissepvend_').value;
                            const plfincart = document.querySelector('#cartecaissedpvend_').value;
                            const cfdircart = document.querySelector('#cartedirectid').value;
                            const hconfircart = document.querySelector('#carteconfheure').value;
                            const dconfirmecart = document.querySelector('#cartedateconfirme').value;
                    httpRequestbiscart.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${selectorp2cart}/${dconfirmecart}/${cfdircart}/${hconfircart}/${pldebutcart}/${plfincart}`, true);
                    httpRequestbiscart.onload = () => {
                        const datasgcbiscart = JSON.parse(httpRequestbiscart.responseText);
                        if (Object.entries(datasgcbiscart).length >= 1) {
                            for (let key in Object.entries(datasgcbiscart)) {
                                let opt = document.createElement('option');
                                opt.value = `${datasgcbiscart[key].siege_num}`;
                                opt.innerHTML = `${datasgcbiscart[key].siege_num}`;
                                document.querySelector('#cartedepsieg').add(opt);
                            }
                        } else {
                            document.querySelector('#cartedepsieg').options.length = 1;
                        }
                    };
                    httpRequestbiscart.setRequestHeader('Content-Type', 'application/json');
                    httpRequestbiscart.send();
                };
                httpRequestecart.setRequestHeader('Content-Type', 'application/json');
                httpRequestecart.send();
            };

            let depsiegconfcart = document.querySelector('#cartedepsieg');
            if (depsiegconfcart !== null)
            depsiegconfcart.onchange = () => {
                    
                    let httpRequestsiegevenduconfcart;
                    
                    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                        httpRequestsiegevenduconfcart = new XMLHttpRequest();
                    } else if (window.ActiveXObject) { // IE 6 and older
                        httpRequestsiegevenduconfcart = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                    const dp_progconfcart = document.querySelector('#carteprogramconf').value;
                    const dp_siegeconfcart = document.querySelector('#cartedepsieg').options[document.querySelector('#cartedepsieg').options.selectedIndex].value;
                    httpRequestsiegevenduconfcart.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${dp_progconfcart}/${dp_siegeconfcart}`, true);
                    httpRequestsiegevenduconfcart.onload = () => 
                    {
                        
                            const confdonsiegcart = JSON.parse(httpRequestsiegevenduconfcart.responseText);
                            if (confdonsiegcart == '')
                                    {
                                        let httpSiegsconfcart;
                                        httpSiegsconfcart = new XMLHttpRequest();

                                        httpSiegsconfcart.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${dp_progconfcart}/${dp_siegeconfcart}`, true);
                                        httpSiegsconfcart.onload = () => 
                                        {
                                            const dongconfcart = JSON.parse(httpSiegsconfcart.responseText);
                                            document.querySelector('#cartemessconf').style.display = 'none';
                                            if (Object.entries(dongconfcart).length >= 1)
                                        {
                                            for (let key in Object.entries(dongconfcart)) {
                                                document.querySelector('#carteidtampoconf').value = `${dongconfcart[key].idtamp}`;                    
                                                document.querySelector('#cartesiegselectconf').value = `${dongconfcart[key].numsieg}`;
                                            }

                                        }
                                        
                                        };
                                        httpSiegsconfcart.setRequestHeader('Content-Type', 'application/json');
                                        httpSiegsconfcart.send();
                                    }
                                    else {
                                        document.querySelector('#cartedepsieg').value = '';     
                                        if (Object.entries(confdonsiegcart).length >= 1)
                                        {
                                            for (let key in Object.entries(confdonsiegcart)) {
                                                document.querySelector('#carteidtampoconf').value = `${confdonsiegcart[key].idtamp}`;                    
                                                document.querySelector('#cartesiegselectconf').value = `${confdonsiegcart[key].numsieg}`;
                                            }

                                        }
                                        document.querySelector('#cartemessconf').style.display = 'block';
                                        document.querySelector('#carteerreurMessconf').innerHTML = `Siege déjà utilisé.`; 
                                    }
                    };
                    httpRequestsiegevenduconfcart.setRequestHeader('content-Type', 'text/json');
                    httpRequestsiegevenduconfcart.send();
                };
            //bouton annuler
                butoncliconfcart = document.querySelector('#carteconfreset');
                if (butoncliconfcart !== null) {
                    butoncliconfcart.onclick = () => 
                    {
                        let httpSiegeselectconfcart;
                        httpSiegeselectconfcart = new XMLHttpRequest();
                        const siegselectconfcart = document.querySelector('#cartesiegselectconf').value;
                        const idtapconfcart = document.querySelector('#carteidtampoconf').value;
                        httpSiegeselectconfcart.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtapconfcart}/${siegselectconfcart}`, true);
                        httpSiegeselectconfcart.onload = () => 
                        {
                            const donselectconfcart = JSON.parse(httpSiegeselectconfcart.responseText);
                            console.debug(`${typeof donselectconfcart} - ${donselectconfcart.attributes}`, console.memory);
                            document.querySelector('#cartemessconf').style.display = 'none';
                            
                        };
                        httpSiegeselectconfcart.setRequestHeader('Content-Type', 'application/json');
                        httpSiegeselectconfcart.send();
    
                    
                    };
                }       
                       
        e.onclick = function () {
            let cartcbForm = document.querySelector('#cartecForm');
            cartcbForm.setAttribute('action', `${APP_ROOT}/Confirmation/carteconfirme/${e.dataset.cle_compagnie}`);
        }
    })
});
;
/* --- addrecu.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addrecu').forEach(function (e) {
        document.querySelector('h3#recuTitle').innerHTML = `FAIRE RECU`;

        let infosrecu = document.querySelector('#recu_infos');
        if (infosrecu !== null)
            infosrecu.onclick = () => {
                //verification code du ticket
                let httpRequestRecu;
                
                if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                    httpRequestRecu = new XMLHttpRequest();
                } else if (window.ActiveXObject) { // IE 6 and older
                    httpRequestRecu = new ActiveXObject("Microsoft.XMLHTTP");
                }
                
                
                var codrecu = document.querySelector("#codeclientprecu").value;
                var gdrecu = document.querySelector("#gareconnectrecu").value;
                httpRequestRecu.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/verifcoderecu/${gdrecu}/${codrecu}`, true);
                httpRequestRecu.onload = () => {
                    const donneesrecu = JSON.parse(httpRequestRecu.responseText);
                    if (donneesrecu == null) {
                        
                        document.querySelector('#validrecu').style.display = 'none';
                        document.querySelector('#billetrecu').style.display = 'block';
                        document.querySelector('#billetSmsrecu').innerHTML = `Ce client a déjà pris son reçu merci!`;
                        document.querySelector('#nomclprecu').innerHTML = ``;
                        document.querySelector('#prenomclprecu').innerHTML = ``;
                        document.querySelector('#contactclprecu').innerHTML = ``;
                        document.querySelector('#refclprecu').innerHTML = ``;
                        document.querySelector('#directionclprecu').innerHTML = ``;
                        document.querySelector('#codeclprecu').innerHTML = ``;
                        document.querySelector('#heureclprecu').innerHTML = ``;
                        document.querySelector('#passerprecu').value = '';
                        document.querySelector('#idclpasseridrecu').value = '';
                        document.querySelector('#client_idprecu').value = '';
                        document.querySelector('#pasnomprecu').value = '';
                        document.querySelector('#pasprenomprecu').value = '';
                        document.querySelector('#pascontactprecu').value = '';
                        document.querySelector('#pascnibprecu').value = '';
                        document.querySelector('#pasdateprecu').value = '';
                        document.querySelector('#delivrelierecu').value = '';
                        document.querySelector('#codetamponrecus').value = '';
                        document.querySelector('#passaxeprecu').value = '';
                        document.querySelector('#prixrecu').value = '';
                        document.querySelector('#codenonprecu').value = '';
                                    

                    } else 
                    {
                               
                            if (Object.entries(donneesrecu).length >= 1){
                                    document.querySelector('#billetrecu').style.display = 'none';
                                    document.querySelector('#billetSmsrecu').style.display = 'block';
                                    document.querySelector('#validrecu').style.display = 'block';
                                    document.querySelector('#nomclprecu').innerHTML = `NOM: ${donneesrecu.nom_client}`;
                                    document.querySelector('#prenomclprecu').innerHTML = `PRENOM: ${donneesrecu.prenom_client}`;
                                    document.querySelector('#contactclprecu').innerHTML = `CONTACT: ${donneesrecu.contact_client}`;
                                    document.querySelector('#refclprecu').innerHTML = `REFERENCE CNIB: ${donneesrecu.num_CNIB}`;
                                    document.querySelector('#directionclprecu').innerHTML = `AXE: ${donneesrecu.nom_ligne}`;
                                    document.querySelector('#codeclprecu').innerHTML = `CODE TICKET: ${donneesrecu.code_passager}`;
                                    document.querySelector('#heureclprecu').innerHTML = `HEURE: ${donneesrecu.heure} SIEGE :${donneesrecu.num_siege_categorie}`;
                                    document.querySelector('#passerprecu').value = `${donneesrecu.code_ticket}`;
                                    document.querySelector('#codenonprecu').value = `${donneesrecu.codeticket}`;
                                    document.querySelector('#idclpasseridrecu').value = `${donneesrecu.ligne_id}`;
                                    document.querySelector('#client_idprecu').value = `${donneesrecu.id_client_pass}`;
                                    document.querySelector('#pasnomprecu').value = `${donneesrecu.nom_client}`;
                                    document.querySelector('#pasprenomprecu').value = `${donneesrecu.prenom_client}`;
                                    document.querySelector('#pascontactprecu').value = `${donneesrecu.contact_client}`;
                                    document.querySelector('#pascnibprecu').value = `${donneesrecu.num_CNIB}`;
                                    document.querySelector('#pasdateprecu').value = `${donneesrecu.date_delivre}`;
                                    document.querySelector('#delivrelierecu').value = `${donneesrecu.lieu_delivre}`;
                                    document.querySelector('#codetamponrecus').value = `${donneesrecu.tamponcod}`;
                                    document.querySelector('#passaxeprecu').value = `${donneesrecu.nom_ligne}`;
                                    document.querySelector('#prixrecu').value = `${donneesrecu.prixvente}`;
                                    

                            } 
                            
                    }
                };
                httpRequestRecu.setRequestHeader('Content-Type', 'application/json');
                httpRequestRecu.send();
            };
        
            
        e.onclick = function () {
            let recuForm = document.querySelector('#recuForm');
            recuForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/recuclient/${e.dataset.cle_compagnie}`);
        }
    })
});
;
/* --- addbon.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addbon').forEach(function (e) {
        document.querySelector('h3#bonTitle').innerHTML = `ENREGISTREMENT BON MILITAIRE`;

            //recherche d'information du client
        let idcontact = document.querySelector('#idcontactbon');
        if (idcontact !== null)
        idcontact.onkeyup = () => {
                let httpInfosrequestbon;
                if (window.XMLHttpRequest) {
                    httpInfosrequestbon = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfosrequestbon = new ActiveXObject("Microsoft.XMLHTTP");
                }
                var verifictbon = document.querySelector('#idcontactbon').value;
                httpInfosrequestbon.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifinfos/${verifictbon}`, true);
                httpInfosrequestbon.onload = () => {
                    const infosreqbon = JSON.parse(httpInfosrequestbon.responseText);
                    if (infosreqbon == null) {
                        document.querySelector('#idnombon').value = "";
                                document.querySelector('#idprenombon').value = "";
                                document.querySelector('#bon').value = "";
                                document.querySelector('#date_carte').value = "";
                                document.querySelector('#lieudelivre_cart').value = "";
                                document.querySelector('#clientbonid').value = "";
                    } else {
                        if (Object.entries(infosreqbon).length > 1) {
                            
                            if (infosreqbon.contact_client == verifictbon) {
                                document.querySelector('#idnombon').value = `${infosreqbon.nom_client}`;
                                document.querySelector('#idprenombon').value = `${infosreqbon.prenom_client}`;
                                document.querySelector('#bon').value = `${infosreqbon.num_CNIB}`;
                                document.querySelector('#date_carte').value = `${infosreqbon.date_delivre}`;
                                document.querySelector('#lieudelivre_cart').value = `${infosreqbon.lieu_delivre}`;
                                document.querySelector('#clientbonid').value = `${infosreqbon.id_client}`;

                                document.querySelector('#pasnompbon').value = `${infosreqbon.nom_client}`;
                                document.querySelector('#pasprenompbon').value = `${infosreqbon.prenom_client}`;
                                document.querySelector('#pascnibpbon').value = `${infosreqbon.num_CNIB}`;
                                document.querySelector('#pasdatepbon').value = `${infosreqbon.date_delivre}`;
                                document.querySelector('#lieubon').value = `${infosreqbon.lieu_delivre}`;
                            } else {
                                document.querySelector('#idnombon').value = "";
                                document.querySelector('#idprenombon').value = "";
                                document.querySelector('#bon').value = "";
                                document.querySelector('#date_carte').value = "";
                                document.querySelector('#lieudelivre_cart').value = "";
                                document.querySelector('#clientbonid').value = "";
                            }
                        }
                    }
                };
                httpInfosrequestbon.setRequestHeader('Content-Type', 'application/json');
                httpInfosrequestbon.send();
            };
        e.onclick = function () {
            let bonForm = document.querySelector('#bonForm');
            bonForm.setAttribute('action', `${APP_ROOT}/Bon_Millitaire/addbon/${e.dataset.cle_compagnie}`);
        }
    })
});
;
/* --- addcarte.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addcarte').forEach(function (e)
    {

        document.querySelector('h3#carteTitle').innerHTML = `ENREGISTRER CARTE DE VOYAGE`;

            //recherche d'information du client depart principal
        let idcontact = document.querySelector('#idcontactcarte');
        if (idcontact !== null)
        idcontact.onkeyup = () => {
                let httpInfoscarte;
                if (window.XMLHttpRequest) {
                    httpInfoscarte = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfoscarte = new ActiveXObject("Microsoft.XMLHTTP");
                }
                var verifictcarte = document.querySelector('#idcontactcarte').value;
                httpInfoscarte.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifinfos/${verifictcarte}`, true);
                httpInfoscarte.onload = () => {
                    const infosreqcarte = JSON.parse(httpInfoscarte.responseText);
                    if (infosreqcarte == null) {
                                document.querySelector('#idnomcarte').value = "";
                                document.querySelector('#idprenomcarte').value = "";
                                document.querySelector('#carte').value = "";
                                document.querySelector('#datecartev').value = "";
                                document.querySelector('#lieudelivrecarte').value = "";
                                document.querySelector('#clientcarteid').value = "";
                    } else {
                        if (Object.entries(infosreqcarte).length > 1) {
                            
                            if (infosreqcarte.contact_client == verifictcarte) {
                                document.querySelector('#idnomcarte').value = `${infosreqcarte.nom_client}`;
                                document.querySelector('#idprenomcarte').value = `${infosreqcarte.prenom_client}`;
                                document.querySelector('#carte').value = `${infosreqcarte.num_CNIB}`;
                                document.querySelector('#datecartev').value = `${infosreqcarte.date_delivre}`;
                                document.querySelector('#lieudelivrecarte').value = `${infosreqcarte.lieu_delivre}`;
                                document.querySelector('#clientcarteid').value = `${infosreqcarte.id_client}`;
                                document.querySelector('#nomcarte_voyageid').value = `${infosreqcarte.nom_client}`;
                                document.querySelector('#prenomcartevoyageid').value = `${infosreqcarte.prenom_client}`;
                                document.querySelector('#cnibcartevoyageid').value = `${infosreqcarte.num_CNIB}`;
                                document.querySelector('#datecartevoyageid').value = `${infosreqcarte.date_delivre}`;
                                document.querySelector('#lieucartevoyageid').value = `${infosreqcarte.lieu_delivre}`;
                            }
                        }
                    }
                };
                httpInfoscarte.setRequestHeader('Content-Type', 'application/json');
                httpInfoscarte.send();
            };
            
        e.onclick = function () {
            let cartForm = document.querySelector('#carteForm');
            cartForm.setAttribute('action', `${APP_ROOT}/Cartes_Voyage/addcarte/${e.dataset.cle_compagnie}`);
        }
    })
});
