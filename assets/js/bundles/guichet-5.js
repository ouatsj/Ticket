/* Bundle guichet role=5 — genere par scripts/build_guichet_bundles.php */
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

    function __venteFiFillCheminHeures(selectId, rows, legKey) {
        var sel = document.getElementById(selectId);
        if (!sel) return;
        sel.options.length = 1;
        var list = Array.isArray(rows) ? rows
            : (rows && typeof rows === 'object' ? Object.keys(rows).map(function (k) { return rows[k]; }) : []);
        var groups = {};
        var order = [];
        for (var i = 0; i < list.length; i++) {
            var row = list[i];
            if (!row || row.code_progr == null || row.code_progr === '') continue;
            var lh = String(row.id_ligneheure != null ? row.id_ligneheure : '');
            if (!lh) continue;
            if (!groups[lh]) {
                groups[lh] = { heure: row.heure || '', rows: [] };
                order.push(lh);
            }
            var exists = false;
            for (var j = 0; j < groups[lh].rows.length; j++) {
                if (String(groups[lh].rows[j].code_progr) === String(row.code_progr)) { exists = true; break; }
            }
            if (!exists) groups[lh].rows.push(row);
        }
        if (!window.__venteFiCheminGroups) window.__venteFiCheminGroups = {};
        window.__venteFiCheminGroups[selectId] = groups;
        for (var k = 0; k < order.length; k++) {
            var idLh = order[k];
            var g = groups[idLh];
            var opt = document.createElement('option');
            opt.value = idLh;
            opt.innerHTML = g.rows.length > 1 ? ((g.heure || idLh) + ' (' + g.rows.length + ' départs)') : (g.heure || idLh);
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

    function __venteFiRequestTransitLegs(seltdep, arr, datedepart, sougid, force, onDone) {
        var sg = (sougid != null && sougid !== '') ? sougid : '0';
        var forceFlag = force ? '1' : '0';
        var httpRequestitinefi = new XMLHttpRequest();
        httpRequestitinefi.open(
            'GET',
            window.location.origin + `${APP_ROOT}/programmes/verifitine/`
                + encodeURIComponent(seltdep + '-' + arr) + '/'
                + encodeURIComponent(datedepart) + '/'
                + encodeURIComponent(sg) + '/'
                + forceFlag,
            true
        );
        httpRequestitinefi.onload = function () {
            var donitinesfi = null;
            try { donitinesfi = JSON.parse(httpRequestitinefi.responseText); } catch (e) { donitinesfi = null; }
            if (typeof onDone === 'function') {
                onDone(donitinesfi);
            } else if (typeof window.__venteFiApplyTransitLegs === 'function') {
                window.__venteFiApplyTransitLegs(donitinesfi);
            }
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
                                                                document.querySelector('#itinecodefid').value = `${donitinesfi[0].code_itineraires}`;

                                                                
                                                                document.querySelector('#lignetinerairefid').value = `${donitinesfi[0].nom_itineraires}`;
                                                            }
                                                            
                                                
                                                            if(i === 2)
                                                            {
                                                                let opt = document.createElement('option');
                                                                opt.value = `${donitinesfi[1].code_itineraires}`;
                                                                opt.innerHTML = `${donitinesfi[1].nom_itineraires}`;
                                                                document.querySelector('#idcheminsfid').add(opt);

                                                                document.querySelector('#lignesitinerairefid').value = `${donitinesfi[0].nom_itineraires}`;
                                                                document.querySelector('#itinecodesfid').value = `${donitinesfi[0].id_lignes}`;
                                                                    

                                                                var typgare1fi = document.querySelector('#itinecodefid').value;
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
                                                                        if (Object.entries(infositinfi).length >= 1) 
                                                                        {
                                                                                
                                                                            
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

                                                                
                                                                let opt = document.createElement('option');
                                                                opt.value = `${donitinesfi[1].code_itineraires}`;
                                                                opt.innerHTML = `${donitinesfi[1].nom_itineraires}`;
                                                                
                                                                document.querySelector('#idcheminsfid').add(opt);

                                                                document.querySelector('#lignesitinerairefid').value = `${donitinesfi[0].nom_itineraires}`;
                                                                document.querySelector('#itinecodesfid').value = `${donitinesfi[0].id_lignes}`;
                                                               

                                                                let opt1 = document.createElement('option');
                                                                opt1.value = `${donitinesfi[2].code_itineraires}`;
                                                                opt1.innerHTML = `${donitinesfi[2].nom_itineraires}`;
                                                                document.querySelector('#idchemins1fid').add(opt1);


                                                                var typgare1fi = document.querySelector('#itinecodefid').value;
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
                                                                        if (Object.entries(infositin1fi).length >= 1) 
                                                                        {
                                                                                
                                                                            
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
                                                                let opt = document.createElement('option');
                                                                opt.value = `${donitinesfi[1].code_itineraires}`;
                                                                opt.innerHTML = `${donitinesfi[1].nom_itineraires}`;
                                                                document.querySelector('#idcheminsfid').add(opt);


                                                                let opt1 = document.createElement('option');
                                                                opt1.value = `${donitinesfi[2].code_itineraires}`;
                                                                opt1.innerHTML = `${donitinesfi[2].nom_itineraires}`;
                                                                document.querySelector('#idchemins1fid').add(opt1);

                                                                let opt2 = document.createElement('option');
                                                                opt2.value = `${donitinesfi[3].code_itineraires}`;
                                                                opt2.innerHTML = `${donitinesfi[3].nom_itineraires}`;
                                                                document.querySelector('#idchemins2fid').add(opt2);

                                                                document.querySelector('#lignesitinerairefid').value = `${donitinesfi[0].nom_itineraires}`;
                                                               
                                                                document.querySelector('#itinecodesfid').value = `${donitinesfi[0].id_lignes}`;

                                                                    var typgare1fi = document.querySelector('#itinecodefid').value;
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
                                                                        if (Object.entries(infositin1fi).length >= 1) 
                                                                        {
                                                                                
                                                                            
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
                        AppRequestGuard.syncClientMirror([
                            ['#rclientfi', '#rclientcpfi'],
                            ['#prnclientfi', '#prnclientcpfi'],
                            ['#cnibfi', '#cnibcpfi'],
                            ['#date_cnibfi', '#date_cnibcpfi'],
                            ['#lieudelivrefi', '#lieudelivrecpfi']
                        ]);
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
                n: 2,
                sfx: f,
                nbr: conf ? '#nbrtranscf' : (fid ? '#nbrtransfid' : '#nbrtrans'),
                tran: conf ? '#trancf' : (fid ? '#tranfid' : '#tran'),
                ligne: conf ? '#ligntranscf1' : (fid ? '#ligntrans1fid' : '#ligntrans1'),
                lineUi: conf ? ['#arritincf1', '#idcheminscf'] : (fid ? ['#arritin1fid', '#idcheminsfid'] : ['#arritin1', '#idchemins']),
                prix: conf ? '#prix_axetransitcf' : (fid ? '#prix_axetransitfid' : '#prix_axetransit'),
                quartier: conf ? '#quartiercf2' : (fid ? '#quartier2fid' : '#quartier2'),
                quartierLabel: conf ? '#idquartcf2' : (fid ? '#idquart2fid' : '#idquart2'),
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
                n: 3,
                sfx: f,
                nbr: conf ? '#nbrtranscf' : (fid ? '#nbrtransfid' : '#nbrtrans'),
                tran: conf ? '#trancf' : (fid ? '#tranfid' : '#tran'),
                ligne: conf ? '#ligntranscf2' : (fid ? '#ligntrans2fid' : '#ligntrans2'),
                lineUi: conf ? ['#arritincf2', '#idcheminscf1'] : (fid ? ['#arritin2fid', '#idchemins1fid'] : ['#arritin2', '#idchemins1']),
                prix: conf ? '#prix_axetransitcf1' : (fid ? '#prix_axetransit1fid' : '#prix_axetransit1'),
                quartier: conf ? '#quartiercf3' : (fid ? '#quartier3fid' : '#quartier3'),
                quartierLabel: conf ? '#idquartcf3' : (fid ? '#idquart3fid' : '#idquart3'),
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
                n: 4,
                sfx: f,
                nbr: conf ? '#nbrtranscf' : (fid ? '#nbrtransfid' : '#nbrtrans'),
                tran: conf ? '#trancf' : (fid ? '#tranfid' : '#tran'),
                ligne: conf ? '#ligntranscf3' : (fid ? '#ligntrans3fid' : '#ligntrans3'),
                lineUi: conf ? ['#arritincf3', '#idcheminscf2'] : (fid ? ['#arritin3fid', '#idchemins2fid'] : ['#arritin3', '#idchemins2']),
                prix: conf ? '#prix_axetransitcf2' : (fid ? '#prix_axetransit2fid' : '#prix_axetransit2'),
                quartier: null,
                quartierLabel: null,
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

    function __confRequestTransitLegs(axe, datedepart, sougid, force, onDone) {
        var sg = (sougid != null && sougid !== '') ? sougid : '0';
        var forceFlag = force ? '1' : '0';
        var httpRequestitinecf = new XMLHttpRequest();
        httpRequestitinecf.open(
            'GET',
            window.location.origin + `${APP_ROOT}/programmes/verifitine/`
                + encodeURIComponent(axe) + '/'
                + encodeURIComponent(datedepart) + '/'
                + encodeURIComponent(sg) + '/'
                + forceFlag,
            true
        );
        httpRequestitinecf.onload = function () {
            var donitinescf = null;
            try { donitinescf = JSON.parse(httpRequestitinecf.responseText); } catch (e) { donitinescf = null; }
            if (typeof onDone === 'function') onDone(donitinescf);
            else if (typeof window.__confApplyTransitLegs === 'function') window.__confApplyTransitLegs(donitinescf);
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
            
            var confir = document.querySelector("#codeconfirm").value;

            Request.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verificationcode/${confir}`, true);
            Request.onload = () => {
                
            const data = JSON.parse(Request.responseText);
            
            if (data == null) {
                        
                        document.querySelector('#pasnompconf').style.display = 'block';
                        document.querySelector('#pasprenompconf').style.display = 'block';
                        document.querySelector('#pascontactpconf').style.display = 'block';
                        document.querySelector('#pascnibpconf').style.display = 'block';
                        document.querySelector('#pasdatepconf').style.display = 'block';
                        document.querySelector('#delivrelieu').style.display = 'block';
                        document.querySelector('#heured').style.display = 'block';
                        document.querySelector('#depsieg').style.display = 'block';
                        document.querySelector('#valid').style.display = 'block';
                        document.querySelector('#validep').style.display = 'block';
                        document.querySelector('#messagep').style.display = 'none';

                } else {
                    if (Object.entries(data).length > 1) {
                        
                        document.querySelector('#messagep').style.display = 'block';
                        document.querySelector('#erreurMessagep').innerHTML = `Cet ticket ne peut pas être confirmé .`;
                        document.querySelector('#pasnompconf').style.display = 'none';
                        document.querySelector('#pasprenompconf').style.display = 'none';
                        document.querySelector('#pascontactpconf').style.display = 'none';
                        document.querySelector('#pascnibpconf').style.display = 'none';
                        document.querySelector('#pasdatepconf').style.display = 'none';
                        document.querySelector('#delivrelieu').style.display = 'none';
                        document.querySelector('#heured').style.display = 'none';
                        document.querySelector('#depsieg').style.display = 'none';
                        document.querySelector('#valid').style.display = 'none';
                        document.querySelector('#validep').style.display = 'none';
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
                    
                                        document.querySelector('#itinecodecf').value = `${donitinescf[0].code_itineraires}`;

                                        document.querySelector('#idcompgcf').value = `${donitinescf[0].id_compaga}`;
                                        document.querySelector('#lignetinerairecf').value = `${donitinescf[0].nom_itineraires}`;
                                    }
                        
                                    if(i === 2)
                                    {
                                        let opt = document.createElement('option');
                                        opt.value = `${donitinescf[1].code_itineraires}`;
                                        opt.innerHTML = `${donitinescf[1].nom_itineraires}`;
                                        document.querySelector('#idcheminscf').add(opt);

                                        document.querySelector('#lignesitinerairecf').value = `${donitinescf[0].nom_itineraires}`;
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
                                                    if (Object.entries(dongtranschemcf).length >= 1)
                                                    {
                                                        for (let key in Object.entries(dongtranschemcf)) {
                                                            let opt = document.createElement('option');
                                                            opt.value = `${dongtranschemcf[key].code_progr}/${dongtranschemcf[key].intervalle1}/${dongtranschemcf[key].intervalle2}/${dongtranschemcf[key].id_ligneheure}/${dongtranschemcf[key].prix}`;
                                                            opt.innerHTML = `${dongtranschemcf[key].heure}/${dongtranschemcf[key].date_progr}`;
                                                            document.querySelector('#hdepartitinecf').add(opt);
                                                        }
                                                    }
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
                                        let opt = document.createElement('option');
                                        opt.value = `${donitinescf[1].code_itineraires}`;
                                        opt.innerHTML = `${donitinescf[1].nom_itineraires}`;
                                        
                                        document.querySelector('#idcheminscf').add(opt);

                                        document.querySelector('#lignesitinerairecf').value = `${donitinescf[0].nom_itineraires}`;
                                        document.querySelector('#itinecodescf').value = `${donitinescf[0].id_lignes}`;
                                        document.querySelector('#idcompgcf').value = `${donitinescf[0].id_compaga}`;

                                        let opt1 = document.createElement('option');
                                        opt1.value = `${donitinescf[2].code_itineraires}`;
                                        opt1.innerHTML = `${donitinescf[2].nom_itineraires}`;
                                        document.querySelector('#idcheminscf1').add(opt1);

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
                                                        if (Object.entries(dongtranschemcf).length >= 1)
                                                        {
                                                            for (let key in Object.entries(dongtranschemcf)) {
                                                                let opt = document.createElement('option');
                                                                opt.value = `${dongtranschemcf[key].code_progr}/${dongtranschemcf[key].intervalle1}/${dongtranschemcf[key].intervalle2}/${dongtranschemcf[key].id_ligneheure}/${dongtranschemcf[key].prix}`;
                                                                opt.innerHTML = `${dongtranschemcf[key].heure}/${dongtranschemcf[key].date_progr}`;
                                                                document.querySelector('#hdepartitinecf').add(opt);
                                                            }
                                                        }
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
                                                    if (Object.entries(dongtranschemcf1).length >= 1)
                                                    {
                                                        for (let key in Object.entries(dongtranschemcf1)) {
                                                            let opt = document.createElement('option');
                                                            opt.value = `${dongtranschemcf1[key].code_progr}/${dongtranschemcf1[key].intervalle1}/${dongtranschemcf1[key].intervalle2}/${dongtranschemcf1[key].id_ligneheure}/${dongtranschemcf1[key].prix}`;
                                                            opt.innerHTML = `${dongtranschemcf1[key].heure}/${dongtranschemcf1[key].date_progr}`;
                                                            document.querySelector('#idcheminsheurcf').add(opt);
                                                        }
                                                    }
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
                                        let opt = document.createElement('option');
                                        opt.value = `${donitinescf[1].code_itineraires}`;
                                        opt.innerHTML = `${donitinescf[1].nom_itineraires}`;
                                        document.querySelector('#idcheminscf').add(opt);


                                        let opt1 = document.createElement('option');
                                        opt1.value = `${donitinescf[2].code_itineraires}`;
                                        opt1.innerHTML = `${donitinescf[2].nom_itineraires}`;
                                        document.querySelector('#idcheminscf1').add(opt1);

                                        let opt2 = document.createElement('option');
                                        opt2.value = `${donitinescf[3].code_itineraires}`;
                                        opt2.innerHTML = `${donitinescf[3].nom_itineraires}`;
                                        document.querySelector('#idcheminscf2').add(opt2);

                                        document.querySelector('#lignesitinerairecf').value = `${donitinescf[0].nom_itineraires}`;
                                       
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
                                                    if (Object.entries(dongtranschemcf).length >= 1)
                                                    {
                                                        for (let key in Object.entries(dongtranschemcf)) {
                                                            let opt = document.createElement('option');
                                                            opt.value = `${dongtranschemcf[key].code_progr}/${dongtranschemcf[key].intervalle1}/${dongtranschemcf[key].intervalle2}/${dongtranschemcf[key].id_ligneheure}/${dongtranschemcf[key].prix}`;
                                                            opt.innerHTML = `${dongtranschemcf[key].heure}/${dongtranschemcf[key].date_progr}`;
                                                            document.querySelector('#hdepartitinecf').add(opt);
                                                        }
                                                    }
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
                                                    if (Object.entries(dongtranschemcf1).length >= 1)
                                                        {
                                                            for (let key in Object.entries(dongtranschemcf1)) {
                                                                let opt = document.createElement('option');
                                                                opt.value = `${dongtranschemcf1[key].code_progr}/${dongtranschemcf1[key].intervalle1}/${dongtranschemcf1[key].intervalle2}/${dongtranschemcf1[key].id_ligneheure}/${dongtranschemcf1[key].prix}`;
                                                                opt.innerHTML = `${dongtranschemcf1[key].heure}/${dongtranschemcf1[key].date_progr}`;
                                                                document.querySelector('#idcheminsheurcf').add(opt);
                                                            }
                                                        }
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
                                                            if (Object.entries(dongtranschemcf2).length >= 1)
                                                                {
                                                                    for (let key in Object.entries(dongtranschemcf2)) {
                                                                        let opt = document.createElement('option');
                                                                        opt.value = `${dongtranschemcf2[key].code_progr}/${dongtranschemcf2[key].intervalle1}/${dongtranschemcf2[key].intervalle2}/${dongtranschemcf2[key].id_ligneheure}/${dongtranschemcf2[key].prix}`;
                                                                        opt.innerHTML = `${dongtranschemcf2[key].heure}/${dongtranschemcf2[key].date_progr}`;
                                                                        document.querySelector('#idcheminsheurcf1').add(opt);
                                                                    }
                                                                }
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
        e.onclick = function () {
            let confForm = document.querySelector('#confForm');
            confForm.setAttribute('action', `${APP_ROOT}/Confirmation/confirme/${e.dataset.cle_compagnie}`);
        }
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
/* --- addretour.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addretour').forEach(function (e) {
        document.querySelector('h3#retourTitle').innerHTML = `RETOUR`;

        let infosret = document.querySelector('#retreprogrammer_infos');
        if (infosret !== null)
            infosret.onclick = () => {
                //verification code de reprogrammation
                let httpRequestRep;
                
                if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                    httpRequestRep = new XMLHttpRequest();
                } else if (window.ActiveXObject) { // IE 6 and older
                    httpRequestRep = new ActiveXObject("Microsoft.XMLHTTP");
                }
                
                document.querySelector('#retnompre').options.length = 1;
                var retcocl = document.querySelector("#retcodeclientp").value;
                var retgd = document.querySelector("#retgareconnect").value;
                var retu = document.querySelector("#retuserconnected").value;

                httpRequestRep.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/verifretcodecl/${retgd}/${retcocl}/${retu}`, true);
                httpRequestRep.onload = () => {
                    const donneesrt = JSON.parse(httpRequestRep.responseText);
                    if (donneesrt == null) {
                        document.querySelector('#retnompre').options.length = 1;
                    } else 
                    {
                               
                            if (Object.entries(donneesrt).length >= 1) {
                                for (let key in Object.entries(donneesrt)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${donneesrt[key].tamponcod}/${donneesrt[key].tamponcod}/${donneesrt[key].tamponcod}/${donneesrt[key].tamponcod}`;
                                    opt.innerHTML = `${donneesrt[key].nom_client} ${donneesrt[key].prenom_client}`;
                                    document.querySelector('#retnompre').add(opt);
                                    
                                    
                                }
                            }else{
                                document.querySelector('#retnompre').options.length = 1;
                            }
                           
                    }
                };
                httpRequestRep.setRequestHeader('Content-Type', 'application/json');
                httpRequestRep.send();
            };

        let infosretr = document.querySelector('#retnompre');
        if (infosretr !== null)
            infosretr.onclick = () => {
                //verification code de reprogrammation
                let httpRequestRt;
                
                if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                    httpRequestRt = new XMLHttpRequest();
                } else if (window.ActiveXObject) { // IE 6 and older
                    httpRequestRt = new ActiveXObject("Microsoft.XMLHTTP");
                }
                 const selectcd = document.querySelector('#retnompre').
                    options[document.querySelector('#retnompre').options.selectedIndex].value;
                    
                httpRequestRt.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/verifcoderetour/${selectcd}`, true);
                httpRequestRt.onload = () => {
                    const donnees = JSON.parse(httpRequestRt.responseText);
                    if (donnees == null) {
                        document.querySelector('#retpasserp').value = '';
                        document.querySelector('#retligneid').value = '';
                        document.querySelector('#retnomligne').value = '';
                        document.querySelector('#usret').value = '';
                        document.querySelector('#retcle').value = '';
                        document.querySelector('#retsgd').value = '';
                        document.querySelector('#retprixvent').value = '';
                        document.querySelector('#retcodeticket').value = '';
                        document.querySelector('#retdepgid').value = '';
                        document.querySelector('#dateventeret').value = '';
                        document.querySelector('#retcompcd').value = '';


                        
                    } else 
                    {
                               
                        if (Object.entries(donnees).length >= 1){
                            document.querySelector('#retpasserp').value = `${donnees.code_passager}`;
                            document.querySelector('#retligneid').value = `${donnees.ligne_id}`;
                            document.querySelector('#retnomligne').value = `${donnees.nom_ligne}`;
                            document.querySelector('#usret').value = `${donnees.idcptuser}`;
                            document.querySelector('#retcle').value = `${donnees.id_client_pass}`;
                            document.querySelector('#retsgd').value = `${donnees.departclient_idgare}`;
                            document.querySelector('#retprixvent').value = `${donnees.prixvente}`;
                            document.querySelector('#retcodeticket').value = `${donnees.tamponcod}`;
                            document.querySelector('#retdepgid').value = `${donnees.gaexp_lg}`;
                            document.querySelector('#retcompcd').value = `${donnees.id_compaga}`;
                            document.querySelector('#dateventeret').value = `${donnees.datep_create}`;
                        } else {
                            document.querySelector('#retnompre').options.length = 1;
                        }
                           
                    }
                };
                httpRequestRt.setRequestHeader('Content-Type', 'application/json');
                httpRequestRt.send();
            };
        
        e.onclick = function () {
            let retForm = document.querySelector('#retForm');
            retForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/retour/${e.dataset.cle_compagnie}`);
        }

        var clique = true;

            $('#idretepson').click(function(event) 
            {
                if(clique)
                {
                    clique = false;
                    return true;
                }
                else return false;
            })
    })
});
;
/* --- addreprogramme.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addreprogramme').forEach(function (e) {
        document.querySelector('h3#rTitle').innerHTML = `REPROGRAMMATION`;

        let infos = document.querySelector('#reprogrammer_infos');
        if (infos !== null)
            infos.onclick = () => {
                //verification code de reprogrammation
                let httpRequestRep;
                
                if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                    httpRequestRep = new XMLHttpRequest();
                } else if (window.ActiveXObject) { // IE 6 and older
                    httpRequestRep = new ActiveXObject("Microsoft.XMLHTTP");
                }
                
                
                var cocl = document.querySelector("#codeclientp").value;
                httpRequestRep.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/verifcodecl/${cocl}`, true);
                httpRequestRep.onload = () => {
                    const donnees = JSON.parse(httpRequestRep.responseText);
                    if (donnees == null) {
                        
                        document.querySelector('#smsp').style.display = 'block';
                        document.querySelector('#erreurSmsp').innerHTML = `Cet ticket ne peut pas être reprogrammé ici.`;
                        document.querySelector('#nomclp').innerHTML = ``;
                        document.querySelector('#prenomclp').innerHTML = ``;
                        document.querySelector('#contactclp').innerHTML = ``;
                        document.querySelector('#refclp').innerHTML = ``;
                        document.querySelector('#directionclp').innerHTML = ``;
                        document.querySelector('#codeclp').innerHTML = ``;
                        document.querySelector('#heureclp').innerHTML = ``;
                        document.querySelector('#heuredepartp').style.display = 'none';
                        document.querySelector('#numsiegep').style.display = 'none';
                        document.querySelector('#heuredepartp').options.length = 1;

                    } else 
                    {
                               
                            if (Object.entries(donnees).length >= 1){
                                    document.querySelector('#smsp').style.display = 'none';
                                    document.querySelector('#heuredepartp').style.display = 'block';
                                    document.querySelector('#numsiegep').style.display = 'block';       
                                    document.querySelector('#nomclp').innerHTML = `NOM: ${donnees.nom_client}`;
                                    document.querySelector('#prenomclp').innerHTML = `PRENOM: ${donnees.prenom_client}`;
                                    document.querySelector('#contactclp').innerHTML = `CONTACT: ${donnees.contact_client}`;
                                    document.querySelector('#refclp').innerHTML = `REFERENCE CNIB: ${donnees.num_CNIB}`;
                                    document.querySelector('#directionclp').innerHTML = `AXE: ${donnees.nom_ligne}`;
                                    document.querySelector('#codeclp').innerHTML = `CODE TICKET: ${donnees.code_passager}`;
                                    document.querySelector('#heureclp').innerHTML = `HEURE: ${donnees.heure} SIEGE :${donnees.num_siege_categorie}`;
                                    document.querySelector('#passerp').value = `${donnees.code_passager}`;
                                    document.querySelector('#idclpasserid').value = `${donnees.ligne_id}`;
                                    document.querySelector('#client_idp').value = `${donnees.id_client_pass}`;
                                    document.querySelector('#pasnomp').value = `${donnees.nom_client}`;
                                    document.querySelector('#pasprenomp').value = `${donnees.prenom_client}`;
                                    document.querySelector('#pascontactp').value = `${donnees.contact_client}`;
                                    document.querySelector('#pascnibp').value = `${donnees.num_CNIB}`;
                                    document.querySelector('#pasdatep').value = `${donnees.date_delivre}`;
                                    document.querySelector('#nsiegep').value = `${donnees.num_siege_categorie}`;
                                    document.querySelector('#delivrelie').value = `${donnees.lieu_delivre}`;
                                    document.querySelector('#depold').value = `${donnees.code_pro}`;
                                    document.querySelector('#codeid').value = `${donnees.code_passager}`;
                                    document.querySelector('#codetickets').value = `${donnees.tamponcod}`;
                                    document.querySelector('#codenonp').value = `${donnees.code_non_pass}`;
                                    document.querySelector('#statconf').value = `${donnees.statut_confirme}`;
                                    document.querySelector('#statrep').value = `${donnees.statut_reprog}`;
                                    document.querySelector('#programrep').value = `${donnees.code_progr}`;
                                    document.querySelector('#depgid').value = `${donnees.gaexp_lg}`;
                                    document.querySelector('#dateventerep').value = `${donnees.datep_create}`;


                            } else {
                                document.querySelector('#heuredepartp').style.display = 'none';
                                document.querySelector('#numsiegep').style.display = 'none';
                            }
                            var datdepartrep = document.querySelector('#dateventerep').value;
                            var daterepactu = document.querySelector('#actueldaterep').value;
                            var daterep1  = new Date(datdepartrep);
                            var daterep2 = new Date(daterepactu);
                            // différence des heures
                            var time_diff = daterep2.getTime() - daterep1.getTime();
                                // différence de jours
                            const days_Diff = time_diff / (1000 * 3600 * 24);

                            if(days_Diff < 28 || days_Diff < 29 || days_Diff < 30 || days_Diff < 31)    
                            {
                                const hdpaxep = `${donnees.ligne_id}`;
                                const hcl = `${donnees.code_progr}`;
                                const ligneheure =`${donnees.heure_identif}`;
                                let httpRequestews;
                                if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                                    httpRequestews = new XMLHttpRequest();
                                } else if (window.ActiveXObject) { // IE 6 and older
                                    httpRequestews = new ActiveXObject("Microsoft.XMLHTTP");
                                }
                                httpRequestews.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/hdepartprepro/${hdpaxep}/${hcl}/${ligneheure}`, true);
                                
                                httpRequestews.onload = () => {
                                    const data2 = JSON.parse(httpRequestews.responseText);
                                    console.debug(`${typeof data2} - ${data2.attributes}`, console.memory);
                                    if (Object.entries(data2).length >= 1) {
                                        for (let key in Object.entries(data2)) {
                                            let opt = document.createElement('option');
                                            opt.value = `${data2[key].code_progr}/${data2[key].id_ligneheure}/${data2[key].typetarif}`;
                                            opt.innerHTML = `${data2[key].heure}/${data2[key].date_progr}`;
                                            document.querySelector('#heuredepartp').add(opt);
                                            
                                        }
                                    } else {
                                        document.querySelector('#heuredepartp').options.length = 1;
                                    }
                                };
                                httpRequestews.setRequestHeader('Content-Type', 'application/json');
                                httpRequestews.send();
                            }
                            else
                            {
                                document.querySelector('#nomclp').innerHTML = ``;
                                document.querySelector('#prenomclp').innerHTML = ``;
                                document.querySelector('#contactclp').innerHTML = ``;
                                document.querySelector('#refclp').innerHTML = ``;
                                document.querySelector('#directionclp').innerHTML = ``;
                                document.querySelector('#codeclp').innerHTML = ``;
                                document.querySelector('#heureclp').innerHTML = ``;
                                document.querySelector('#heuredepartp').style.display = 'none';
                                document.querySelector('#numsiegep').style.display = 'none';
                                document.querySelector('#billetrep').style.display = 'block';
                                document.querySelector('#billetSmsrep').innerHTML = `Billet non valable, la durée de validité est dépassée.`;
                            }
        
                    }
                };
                httpRequestRep.setRequestHeader('Content-Type', 'application/json');
                httpRequestRep.send();
            };
        
            let heurdep = document.querySelector('#heuredepartp');
            if (heurdep !== null) {
                heurdep.onchange = () => {
                    document.querySelector('#numsiegep').options.length = 1;
                    
                const httpRequerst = new XMLHttpRequest();
                const selectorts = document.querySelector('#heuredepartp').
                    options[document.querySelector('#heuredepartp').options.selectedIndex].value;
					
					var post_lh = selectorts.split('/');
					var selh = post_lh[0];
					var lignehsel = post_lh[1];
					
                    var post_lh1 = lignehsel.split('/');
                    var selh1 = post_lh1[0];
                    var lignehsel1 = post_lh1[1];
                    var vr = selh1;
                httpRequerst.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/siegdispo/${selh}`, true);
                httpRequerst.onload = () => {
                        const data = JSON.parse(httpRequerst.responseText);
                        console.debug(`${typeof data} - ${data.attributes}`, console.memory);
                        if (Object.entries(data).length > 0) {
                            for (let key in Object.entries(data)) {
    
                                document.querySelector('#placevendu').value = `${data[key].intervalle1}`;
                                document.querySelector('#dplacevendu').value = `${data[key].intervalle2}`;
                                document.querySelector('#replign').value = `${data[key].nom_ligne}`;
                                document.querySelector('#repher').value = `${data[key].heure}`;
                                document.querySelector('#datereprogramme').value = `${data[key].date_progr}`;
                                document.querySelector('#catreprogramme').value = `${data[key].categori}`;
                                }
                            } 
                            
                            
                            const httpRequetterep = new XMLHttpRequest();
                                const pld = document.querySelector('#placevendu').value;
                                const plf = document.querySelector('#dplacevendu').value;
                                const lgr = document.querySelector('#replign').value;
                                const reph = document.querySelector('#repher').value;
                                const dtrep = document.querySelector('#datereprogramme').value;
                            httpRequetterep.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${selh}/${dtrep}/${lgr}/${reph}/${pld}/${plf}`, true);
                            httpRequetterep.onload = () => {
                            const dattas = JSON.parse(httpRequetterep.responseText);
                            console.debug(`${typeof dattas} - ${dattas.attributes}`, console.memory);
                            if (Object.entries(dattas).length >= 1) {
                               
                                for (let key in Object.entries(dattas)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${dattas[key].siege_num}`;
                                    opt.innerHTML = `${dattas[key].siege_num}`;
                                    document.querySelector('#numsiegep').add(opt);
                                    console.debug(`${dattas[key].siege_num}`, console.memory)
                                }
                            } else {
                                document.querySelector('#numsiegep').options.length = 1;
                                
                            }
                    };
                    httpRequetterep.setRequestHeader('Content-Type', 'application/json');
                    httpRequetterep.send();
                    };
                    httpRequerst.setRequestHeader('Content-Type', 'application/json');
                    httpRequerst.send();
                };
           
            }

            let numsiege = document.querySelector('#numsiegep');
            if (numsiege !== null)
            numsiege.onchange = () => {
                    
                    let Requestsiegevendu;
                    
                    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                        Requestsiegevendu = new XMLHttpRequest();
                    } else if (window.ActiveXObject) { // IE 6 and older
                        Requestsiegevendu = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                    const dp_progrep = document.querySelector('#programrep').value;
                    const dp_siegerep = document.querySelector('#numsiegep').options[document.querySelector('#numsiegep').options.selectedIndex].value;
                    Requestsiegevendu.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${dp_progrep}/${dp_siegerep}`, true);
                    Requestsiegevendu.onload = () => 
                    {
                        
                            const donsieg = JSON.parse(Requestsiegevendu.responseText);
                            if (donsieg == '')
                                    {
                                        let httpSiegsrep;
                                        httpSiegsrep = new XMLHttpRequest();

                                        httpSiegsrep.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${dp_progrep}/${dp_siegerep}`, true);
                                        httpSiegsrep.onload = () => 
                                        {
                                            const dongrep= JSON.parse(httpSiegsrep.responseText);
                                            document.querySelector('#erreursieg').style.display = 'none';
                                            if (Object.entries(dongrep).length >= 1)
                                            {
                                                for (let key in Object.entries(dongrep)) {
                                                    document.querySelector('#idtamporep').value = `${dongrep[key].idtamp}`;                    
                                                    document.querySelector('#siegselectrep').value = `${dongrep[key].numsieg}`;
                                                }

                                            }
                                        };
                                        httpSiegsrep.setRequestHeader('Content-Type', 'application/json');
                                        httpSiegsrep.send();
                                    }
                                    else {
                                        document.querySelector('#numsiegep').value = '';     
                                        if (Object.entries(donsieg).length >= 1)
                                        {
                                            for (let key in Object.entries(donsieg)) {
                                                document.querySelector('#idtamporep').value = `${donsieg[key].idtamp}`;                    
                                                document.querySelector('#siegselectrep').value = `${donsieg[key].numsieg}`;
                                            }

                                        }
                                        document.querySelector('#erreursieg').style.display = 'block';
                                        document.querySelector('#erreurSiege').innerHTML = `Siege déjà utilisé.`; 
                                    }
                    };
                    Requestsiegevendu.setRequestHeader('content-Type', 'text/json');
                    Requestsiegevendu.send();
                };

                butonclicrep = document.querySelector('#rese');
            if (butonclicrep !== null) {
                butonclicrep.onclick = () => 
                {
                    let httpSiegeselectrep;
                    httpSiegeselectrep = new XMLHttpRequest();
                    const siegselectrep= document.querySelector('#siegselectrep').value;
                    const idtaprep = document.querySelector('#idtamporep').value;
                    httpSiegeselectrep.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtaprep}/${siegselectrep}`, true);
                    httpSiegeselectrep.onload = () => 
                    {
                        const donselectrep= JSON.parse(httpSiegeselectrep.responseText);
                        console.debug(`${typeof donselectrep} - ${donselectrep.attributes}`, console.memory);
                        document.querySelector('#erreursieg').style.display = 'none';
                        
                    };
                    httpSiegeselectrep.setRequestHeader('Content-Type', 'application/json');
                    httpSiegeselectrep.send();

                
                };
            }

            
        e.onclick = function () {
            let rForm = document.querySelector('#rForm');
            rForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/update/${e.dataset.cle_compagnie}`);
        }
    })
});
;
/* --- addreprogadmin.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addreprogadmin').forEach(function (e) {
        document.querySelector('h3#adminrTitle').innerHTML = `REPROGRAMMATION`;

        let admininfos = document.querySelector('#adminreprogrammer_infos');
        if (admininfos !== null)
            admininfos.onclick = () => {
                //verification code de reprogrammation
                let httpRequestRep;
                
                if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                    httpRequestRep = new XMLHttpRequest();
                } else if (window.ActiveXObject) { // IE 6 and older
                    httpRequestRep = new ActiveXObject("Microsoft.XMLHTTP");
                }
               
                
                    var admincocl = document.querySelector("#admincodeclientp").value;
                httpRequestRep.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/adminverifcodecl/${admincocl}`, true);
                httpRequestRep.onload = () => {
                    const donnees = JSON.parse(httpRequestRep.responseText);
                    if (donnees == null) {
                        
                        document.querySelector('#adminsmsp').style.display = 'block';
                        document.querySelector('#adminerreurSmsp').innerHTML = `Cet ticket ne peut pas être reprogrammé ici.`;
                        document.querySelector('#adminnomclp').innerHTML = ``;
                        document.querySelector('#adminprenomclp').innerHTML = ``;
                        document.querySelector('#admincontactclp').innerHTML = ``;
                        document.querySelector('#adminrefclp').innerHTML = ``;
                        document.querySelector('#admindirectionclp').innerHTML = ``;
                        document.querySelector('#admincodeclp').innerHTML = ``;
                        document.querySelector('#adminheureclp').innerHTML = ``;
                        document.querySelector('#adminheuredepartp').style.display = 'none';
                        document.querySelector('#adminnumsiegep').style.display = 'none';
                        document.querySelector('#adminheuredepartp').options.length = 1;
                        document.querySelector('#admincdpassager').value = ``;

                        
                    } else
                    {

                    
                        if (Object.entries(donnees).length >= 1){

                        
                            document.querySelector('#adminsmsp').style.display = 'none';
                            document.querySelector('#adminheuredepartp').style.display = 'block';
                            document.querySelector('#adminnumsiegep').style.display = 'block';       
                            document.querySelector('#adminnomclp').innerHTML = `NOM: ${donnees.nom_client}`;
                            document.querySelector('#adminprenomclp').innerHTML = `PRENOM: ${donnees.prenom_client}`;
                            document.querySelector('#admincontactclp').innerHTML = `CONTACT: ${donnees.contact_client}`;
                            document.querySelector('#adminrefclp').innerHTML = `REFERENCE CNIB: ${donnees.num_CNIB}`;
                            document.querySelector('#admindirectionclp').innerHTML = `AXE: ${donnees.nom_ligne}`;
                            document.querySelector('#admincodeclp').innerHTML = `CODE TICKET: ${donnees.code_passager}`;
                            document.querySelector('#adminheureclp').innerHTML = `HEURE: ${donnees.heure} SIEGE: ${donnees.num_siege_categorie}`;
                            document.querySelector('#adminpasserp').value = `${donnees.code_passager}`;
                            document.querySelector('#adminidclpasserid').value = `${donnees.ligne_id}`;
                            document.querySelector('#adminclient_idp').value = `${donnees.id_client_pass}`;
                            document.querySelector('#adminpasnomp').value = `${donnees.nom_client}`;
                            document.querySelector('#adminpasprenomp').value = `${donnees.prenom_client}`;
                            document.querySelector('#adminpascontactp').value = `${donnees.contact_client}`;
                            document.querySelector('#adminpascnibp').value = `${donnees.num_CNIB}`;
                            document.querySelector('#adminpasdatep').value = `${donnees.date_delivre}`;
                            document.querySelector('#adminnsiegep').value = `${donnees.num_siege_categorie}`;
                            document.querySelector('#admindelivrelie').value = `${donnees.lieu_delivre}`;
                            document.querySelector('#admindepold').value = `${donnees.code_pro}`;
                            document.querySelector('#admincodeid').value = `${donnees.code_passager}`;
                            document.querySelector('#admincodetickets').value = `${donnees.tamponcod}`;
                            document.querySelector('#admincodenonp').value = `${donnees.code_non_pass}`;
                            document.querySelector('#adminstatconf').value = `${donnees.statut_confirme}`;
                            document.querySelector('#adminstatrep').value = `${donnees.statut_reprog}`;
                            document.querySelector('#adminprogramrep').value = `${donnees.code_progr}`;
                            document.querySelector('#admindepgid').value = `${donnees.gaexp_lg}`;
                            document.querySelector('#dateventerep').value = `${donnees.datep_create}`;
                            document.querySelector('#admincdpassager').value = `${donnees.code_ticket}`;

                        } else {
                            document.querySelector('#adminheuredepartp').style.display = 'none';
                            document.querySelector('#adminnumsiegep').style.display = 'none';
                        }       
                            var addatdepartrep = document.querySelector('#dateventerep').value;
                            var addaterepactu = document.querySelector('#actueldaterep').value;
                            var addaterep1  = new Date(addatdepartrep);
                            var addaterep2 = new Date(addaterepactu);
                            // différence des heures
                            var time_diff = addaterep2.getTime() - addaterep1.getTime();
                                // différence de jours
                            const days_Diff = time_diff / (1000 * 3600 * 24);

                            if(days_Diff < 28 || days_Diff < 29 || days_Diff < 30 || days_Diff < 31)    
                            {
                                const hdpaxep = `${donnees.ligne_id}`;
                                const hcl = `${donnees.code_progr}`;
                                const ligneheure =`${donnees.heure_identif}`;
                                let httpRequestews;
                                if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                                    httpRequestews = new XMLHttpRequest();
                                } else if (window.ActiveXObject) { // IE 6 and older
                                    httpRequestews = new ActiveXObject("Microsoft.XMLHTTP");
                                }
                                httpRequestews.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/hdepartprepro/${hdpaxep}/${hcl}/${ligneheure}`, true);
                                
                                httpRequestews.onload = () => {
                                    const data2 = JSON.parse(httpRequestews.responseText);
                                    console.debug(`${typeof data2} - ${data2.attributes}`, console.memory);
                                    if (Object.entries(data2).length >= 1) {
                                        for (let key in Object.entries(data2)) {
                                            let opt = document.createElement('option');
                                            opt.value = `${data2[key].code_progr}/${data2[key].id_ligneheure}/${data2[key].typetarif}`;
                                            opt.innerHTML = `${data2[key].heure}/${data2[key].date_progr}`;
                                            document.querySelector('#adminheuredepartp').add(opt);
                                            
                                        }
                                    } else {
                                        document.querySelector('#adminheuredepartp').options.length = 1;
                                    }
                                };
                                httpRequestews.setRequestHeader('Content-Type', 'application/json');
                                httpRequestews.send();
                            }
                            else{

                                document.querySelector('#adminnomclp').innerHTML = ``;
                                document.querySelector('#adminprenomclp').innerHTML = ``;
                                document.querySelector('#admincontactclp').innerHTML = ``;
                                document.querySelector('#adminrefclp').innerHTML = ``;
                                document.querySelector('#admindirectionclp').innerHTML = ``;
                                document.querySelector('#admincodeclp').innerHTML = ``;
                                document.querySelector('#adminheureclp').innerHTML = ``;
                                document.querySelector('#adminheuredepartp').style.display = 'none';
                                document.querySelector('#adminnumsiegep').style.display = 'none';
                                document.querySelector('#adbilletrep').style.display = 'block';
                                document.querySelector('#adbilletSmsrep').innerHTML = `Billet non valable, la durée de validité est dépassée.`;
                            }
                    }
                };
                httpRequestRep.setRequestHeader('Content-Type', 'application/json');
                httpRequestRep.send();
            };
                
        
            let heurdep = document.querySelector('#adminheuredepartp');
            if (heurdep !== null) {
                heurdep.onchange = () => {
                    document.querySelector('#adminnumsiegep').options.length = 1;
                    
                const httpRequerst = new XMLHttpRequest();
                const selectorts = document.querySelector('#adminheuredepartp').
                    options[document.querySelector('#adminheuredepartp').options.selectedIndex].value;
					
					var post_lh = selectorts.split('/');
					var selh = post_lh[0];
					var lignehsel = post_lh[1];
                    var post_lh1 = lignehsel.split('/');
                    var selh1 = post_lh1[0];
                    var lignehsel1 = post_lh1[1];
                    var vr = selh1;
                httpRequerst.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/siegdispo/${selh}`, true);
                httpRequerst.onload = () => {
                        const data = JSON.parse(httpRequerst.responseText);
                        console.debug(`${typeof data} - ${data.attributes}`, console.memory);
                        if (Object.entries(data).length > 0)
                            for (let key in Object.entries(data)) {
    
                                document.querySelector('#adminplacevendu').value = `${data[key].intervalle1}`;
                                document.querySelector('#admindplacevendu').value = `${data[key].intervalle2}`;
                                document.querySelector('#adminreplign').value = `${data[key].nom_ligne}`;
                                document.querySelector('#adminrepher').value = `${data[key].heure}`;
                                document.querySelector('#admindatereprogramme').value = `${data[key].date_progr}`;
                                document.querySelector('#admincatreprogramme').value = `${data[key].categori}`;
                                }
                            
                            const httpRequetterep = new XMLHttpRequest();
                                const pld = document.querySelector('#adminplacevendu').value;
                                const plf = document.querySelector('#admindplacevendu').value;
                                const lgr = document.querySelector('#adminreplign').value;
                                const reph = document.querySelector('#adminrepher').value;
                                const dtrep = document.querySelector('#admindatereprogramme').value;

                            httpRequetterep.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${selh}/${dtrep}/${lgr}/${reph}/${pld}/${plf}`, true);
                            httpRequetterep.onload = () => {
                            const dattas = JSON.parse(httpRequetterep.responseText);
                            console.debug(`${typeof dattas} - ${dattas.attributes}`, console.memory);
                            if (Object.entries(dattas).length >= 1)
                            {
                               
                                for (let key in Object.entries(dattas)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${dattas[key].siege_num}`;
                                    opt.innerHTML = `${dattas[key].siege_num}`;
                                    document.querySelector('#adminnumsiegep').add(opt);
                                    console.debug(`${dattas[key].siege_num}`, console.memory)
                                }
                            } else {
                                document.querySelector('#adminnumsiegep').options.length = 1;
                                
                            }
                    };
                    httpRequetterep.setRequestHeader('Content-Type', 'application/json');
                    httpRequetterep.send();
                    };
                    httpRequerst.setRequestHeader('Content-Type', 'application/json');
                    httpRequerst.send();
                };
           
            }

            let numsiege = document.querySelector('#adminnumsiegep');
            if (numsiege !== null)
            numsiege.onchange = () => {
                    
                    let Requestsiegevendu;
                    
                    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                        Requestsiegevendu = new XMLHttpRequest();
                    } else if (window.ActiveXObject) { // IE 6 and older
                        Requestsiegevendu = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                    const dp_progrep = document.querySelector('#adminprogramrep').value;
                    const dp_siegerep = document.querySelector('#adminnumsiegep').options[document.querySelector('#numsiegep').options.selectedIndex].value;
                    Requestsiegevendu.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${dp_progrep}/${dp_siegerep}`, true);
                    Requestsiegevendu.onload = () => 
                    {
                        
                            const donsieg = JSON.parse(Requestsiegevendu.responseText);
                            if (donsieg == '')
                                    {
                                        let httpSiegsrep;
                                        httpSiegsrep = new XMLHttpRequest();

                                        httpSiegsrep.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${dp_progrep}/${dp_siegerep}`, true);
                                        httpSiegsrep.onload = () => 
                                        {
                                            const dongrep= JSON.parse(httpSiegsrep.responseText);
                                            document.querySelector('#adminerreursieg').style.display = 'none';
                                            if (Object.entries(dongrep).length >= 1)
                                            {
                                                for (let key in Object.entries(dongrep)) {
                                                    document.querySelector('#adminidtamporep').value = `${dongrep[key].idtamp}`;                    
                                                    document.querySelector('#adminsiegselectrep').value = `${dongrep[key].numsieg}`;
                                                }

                                            }
                                        };
                                        httpSiegsrep.setRequestHeader('Content-Type', 'application/json');
                                        httpSiegsrep.send();
                                    }
                                    else {
                                        document.querySelector('#adminnumsiegep').value = '';     
                                        if (Object.entries(donsieg).length >= 1)
                                        {
                                            for (let key in Object.entries(donsieg)) {
                                                document.querySelector('#adminidtamporep').value = `${donsieg[key].idtamp}`;                    
                                                document.querySelector('#adminsiegselectrep').value = `${donsieg[key].numsieg}`;
                                            }

                                        }
                                        document.querySelector('#adminerreursieg').style.display = 'block';
                                        document.querySelector('#adminerreurSiege').innerHTML = `Siege déjà utilisé.`; 
                                    }
                    };
                    Requestsiegevendu.setRequestHeader('content-Type', 'text/json');
                    Requestsiegevendu.send();
                };

                butonclicrep = document.querySelector('#adminrese');
            if (butonclicrep !== null) {
                butonclicrep.onclick = () => 
                {
                    let httpSiegeselectrep;
                    httpSiegeselectrep = new XMLHttpRequest();
                    const siegselectrep= document.querySelector('#adminsiegselectrep').value;
                    const idtaprep = document.querySelector('#adminidtamporep').value;
                    httpSiegeselectrep.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtaprep}/${siegselectrep}`, true);
                    httpSiegeselectrep.onload = () => 
                    {
                        const donselectrep= JSON.parse(httpSiegeselectrep.responseText);
                        console.debug(`${typeof donselectrep} - ${donselectrep.attributes}`, console.memory);
                        document.querySelector('#adminerreursieg').style.display = 'none';
                        
                    };
                    httpSiegeselectrep.setRequestHeader('Content-Type', 'application/json');
                    httpSiegeselectrep.send();

                
                };
            }

            
        e.onclick = function () {
            let rForm = document.querySelector('#adminrForm');
            rForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/adupdate/${e.dataset.cle_compagnie}`);
        }
    })
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
