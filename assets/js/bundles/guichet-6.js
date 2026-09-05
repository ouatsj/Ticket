/* Bundle guichet role=6 — genere par scripts/build_guichet_bundles.php */
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
/* --- addventeticket.js --- */
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

    function __venteListSpansMultipleDays(rows) {
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

    function __venteHeureOptionLabel(heure, dateProgr, voyageDate, forceDate) {
        var label = String(heure || '');
        var dprog = dateProgr ? String(dateProgr).slice(0, 10) : '';
        var vDate = voyageDate ? String(voyageDate).slice(0, 10) : '';
        var showDate = !!forceDate || (dprog && vDate && dprog !== vDate);
        if (showDate && dprog) {
            var short = __venteFormatDateShort(dprog);
            if (short) label = label + ' — ' + short;
        }
        return label;
    }

    /** Remplit #hdepartitine avec J et J+1 (1 option / créneau, date si multi-jours). */
    function __venteFillHeureItineSelect(selectEl, rows, preselectHour) {
        var sel = typeof selectEl === 'string' ? document.querySelector(selectEl) : selectEl;
        if (!sel) return;
        sel.options.length = 1;
        if (!rows) return;
        var list = Array.isArray(rows) ? rows
            : (typeof rows === 'object' ? Object.keys(rows).map(function (k) { return rows[k]; }) : []);
        var dateEl = document.querySelector('#date_depheure') || document.querySelector('#date_depheurefid');
        var voyageDate = dateEl ? String(dateEl.value || '').slice(0, 10) : '';
        var bySlot = {};
        var order = [];
        for (var i = 0; i < list.length; i++) {
            var row = list[i];
            if (!row || row.id_ligneheure == null || row.heure == null) continue;
            var dprog = row.date_progr ? String(row.date_progr).slice(0, 10) : '';
            var hh = String(row.heure).trim();
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
            return (__venteHeureToMinutes(ra.heure) || 0) - (__venteHeureToMinutes(rb.heure) || 0);
        });
        var slotRows = order.map(function (k) { return bySlot[k]; });
        var forceDate = __venteListSpansMultipleDays(slotRows);
        for (var j = 0; j < order.length; j++) {
            var r = bySlot[order[j]];
            var opt = document.createElement('option');
            var dprogOpt = r.date_progr ? String(r.date_progr).slice(0, 10) : '';
            opt.value = String(r.id_ligneheure) + '/' + String(r.heure);
            if (dprogOpt) opt.setAttribute('data-date-progr', dprogOpt);
            opt.setAttribute('data-heure', String(r.heure || ''));
            opt.innerHTML = __venteHeureOptionLabel(r.heure, r.date_progr, voyageDate, forceDate);
            sel.add(opt);
        }
        if (preselectHour && preselectHour.value) {
            __venteSelectHourInSelect(sel, preselectHour);
            if (sel.selectedIndex > 0 && typeof sel.onchange === 'function') {
                sel.onchange();
            }
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
            var hh = String(row.heure || '').trim();
            // Clé date|heure : 1 option visible par créneau (plusieurs id_lh / code_progr → sélecteur prog).
            var gkey = dprog + '|' + hh;
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
            var label = __venteHeureOptionLabel(g.heure || key, g.date_progr, voyageDate, forceDate);
            if (g.rows.length > 1) {
                label = label + ' (' + g.rows.length + ' départs)';
            }
            opt.innerHTML = label;
            sel.add(opt);
        }
        if (legKey) {
            __venteWireCheminHeur(sel.id, legKey);
        }
        if (window.__venteCheminCascadeStarted && legKey) {
            __ventePreselectCheminHeurFromEtape(sel, __venteCheminEtapeForLeg(legKey), legKey);
            __venteAdvanceCheminCascade(legKey);
        }
    }

    function __venteCheminEtapeForLeg(legKey) {
        var etapes = window.__venteCheminEtapes;
        if (!etapes || !etapes.length) return null;
        if (legKey === 'tr2') return etapes[1] || null;
        if (legKey === 'tr3') return etapes[2] || null;
        if (legKey === 'tr4') return etapes[3] || null;
        return null;
    }

    function __ventePreselectCheminHeurFromEtape(heurSel, etape, legKey) {
        var sel = typeof heurSel === 'string' ? document.querySelector(heurSel) : heurSel;
        if (!sel || !legKey) return false;
        var cfg = __venteCheminLegCfg[legKey];
        if (!cfg) return false;
        var targetCode = (etape && etape._graphe_code_progr != null) ? String(etape._graphe_code_progr) : '';
        var targetLh = (etape && etape._graphe_id_ligneheure != null) ? String(etape._graphe_id_ligneheure) : '';
        var targetHeure = (etape && etape._graphe_heure != null) ? String(etape._graphe_heure) : '';
        var targetDate = (etape && etape._graphe_date_progr) ? String(etape._graphe_date_progr).slice(0, 10) : '';
        var groups = (window.__venteCheminGroups && window.__venteCheminGroups[sel.id]) || {};
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
                    if (!targetHeure || String(row.heure) === targetHeure) {
                        pickRow = row;
                    }
                }
            }
            if (pickRow && targetDate && String(pickRow.date_progr || '').slice(0, 10) !== targetDate) {
                pickRow = null;
            }
            if (!pickRow) continue;
            sel.selectedIndex = idx;
            if (g.rows.length === 1) {
                __venteApplyCheminRow(cfg, pickRow);
                __venteLoadSiegesChemin(cfg, pickRow);
            } else {
                __venteOnCheminHeurChange(legKey);
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
            __venteOnCheminHeurChange(legKey);
            return true;
        }
        return false;
    }

    function __venteAdvanceCheminCascade(completedLegKey) {
        if (!window.__venteCheminCascadeStarted) return;
        var etapes = window.__venteCheminEtapes;
        if (!etapes || etapes.length < 2) return;
        if (completedLegKey === 'tr2' && etapes.length >= 3 && etapes[2]) {
            __venteSetCheminLigneOption('#idchemins1', etapes[2].code_itineraires, etapes[2].nom_itineraires);
        } else if (completedLegKey === 'tr3' && etapes.length >= 4 && etapes[3]) {
            __venteSetCheminLigneOption('#idchemins2', etapes[3].code_itineraires, etapes[3].nom_itineraires);
        }
    }

    function __venteStartDownstreamCheminLegs(donitines) {
        donitines = (typeof __venteNormalizeEtapes === 'function')
            ? __venteNormalizeEtapes(donitines) : donitines;
        if (!donitines || donitines.length < 2 || !donitines[1]) return;
        window.__venteCheminEtapes = donitines;
        window.__venteCheminCascadeStarted = true;
        __venteSetCheminLigneOption('#idchemins', donitines[1].code_itineraires, donitines[1].nom_itineraires);
    }

    function __venteMaybeStartCheminCascade() {
        if (window.__venteCheminEtapes && window.__venteCheminEtapes.length >= 2 && !window.__venteCheminCascadeStarted) {
            __venteStartDownstreamCheminLegs(window.__venteCheminEtapes);
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

    function __venteReleaseTamponSiege(idtampoId, siegselectId) {
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

    var __venteTamponSiegePairs = [
        ['idtampo', 'siegselect'],
        ['idtampotrans', 'siegselecttrans'],
        ['idtampo1', 'siegselect1'],
        ['idtampo2', 'siegselect2'],
        ['idtampo3', 'siegselect3']
    ];

    function __venteReleaseAllTamponSieges() {
        var chain = Promise.resolve();
        __venteTamponSiegePairs.forEach(function (p) {
            chain = chain.then(function () {
                return __venteReleaseTamponSiege(p[0], p[1]);
            });
        });
        return chain;
    }

    /** Libération synchrone (fermeture onglet / crash navigation). */
    function __venteFlushTamponsSync() {
        __venteTamponSiegePairs.forEach(function (p) {
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

    function __venteWireTamponLifecycle() {
        if (window.__venteTamponLifecycleWired) return;
        window.__venteTamponLifecycleWired = true;

        window.addEventListener('pagehide', function () {
            __venteFlushTamponsSync();
        });
        window.addEventListener('beforeunload', function () {
            __venteFlushTamponsSync();
        });

        // Heartbeat : prolonge le TTL des tamppons connus (direct + 1re jambe).
        setInterval(function () {
            var touches = [
                ['idtampo', 'siegselect', '#program'],
                ['idtampotrans', 'siegselecttrans', '#programtrans']
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
    __venteWireTamponLifecycle();

    function __venteResetSaleUiAfterCancel() {
        window.__venteHasTransit = false;
        window.__venteLastHeuresVente = [];
        window.__venteSelectedHour = null;
        window.__venteOdCtx = null;
        window.__venteSavedHourValue = '';
        window.__venteSavedQuartierValue = null;
        window.__venteCheminsCache = null;
        window.__venteCheminGroups = {};
        window.__venteCheminEtapes = null;
        window.__venteCheminCascadeStarted = false;
        window.__venteReloadFromOdChange = false;

        if (typeof __venteHideCheminSelector === 'function') __venteHideCheminSelector();
        if (typeof __venteResetTransitFieldsBeforeApply === 'function') __venteResetTransitFieldsBeforeApply();
        if (typeof __venteShowDirectHourUi === 'function') __venteShowDirectHourUi();
        if (typeof __venteResetMainEscaleUi === 'function') __venteResetMainEscaleUi();
        if (typeof __venteHideProgSelect === 'function') __venteHideProgSelect();

        ['#hdepart', '#psieges', '#quartier'].forEach(function (s) {
            var el = document.querySelector(s);
            if (el && el.options) {
                el.options.length = 1;
                el.selectedIndex = 0;
                el.value = '';
                el.onchange = null;
            }
        });

        var mess = document.querySelector('#mess');
        if (mess) mess.style.display = 'none';
        var err = document.querySelector('#erreurMess');
        if (err) err.innerHTML = '';

        var form = document.getElementById('taForm');
        if (form) form.reset();
    }

    function __venteCancelSale(ev) {
        if (ev && ev.preventDefault) ev.preventDefault();
        __venteReleaseAllTamponSieges().then(function () {
            __venteResetSaleUiAfterCancel();
        });
    }

    function __venteWireCancelButton(btnId) {
        var btn = document.getElementById(btnId);
        if (!btn || btn.dataset.venteCancelWired === '1') return;
        btn.dataset.venteCancelWired = '1';
        btn.type = 'button';
        btn.addEventListener('click', __venteCancelSale);
    }

    function __venteNotifyPrixAffiche() {
        if (typeof window.__venteSyncPrixAffiche === 'function') {
            try { window.__venteSyncPrixAffiche(); } catch (e) {}
        }
    }

    function __venteHideTransitPanel() {
        var tran = document.querySelector('#tran');
        if (tran) tran.style.display = 'none';
        __venteNotifyPrixAffiche();
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
        __venteMaybeStartCheminCascade();
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
            // Heures sans départ OD : créneaux transit (itinéraires à choisir après).
            opt.innerHTML = hasProg
                ? hr.heure
                : (String(hr.heure) + (hasTransit ? ' (correspondance)' : ''));
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
        // Hors du panneau #tran (souvent display:none) — visible dès le choix d’heure.
        var anchor = document.getElementById('hdepart')
            || document.getElementById('hrid')
            || document.getElementById('date_depheure');
        var tran = document.getElementById('tran');
        if (anchor) {
            var fg = anchor.closest ? anchor.closest('.form-group') : null;
            if (fg && fg.parentNode) {
                if (tran && tran.parentNode === fg.parentNode) {
                    fg.parentNode.insertBefore(box, tran);
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
        if (tran && tran.parentNode) {
            tran.parentNode.insertBefore(box, tran);
            return box;
        }
        document.body.appendChild(box);
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
     * Correspondance 2/3/4 — ligne du chemin : sélection + chargement heures (chemintr).
     */
    function __venteSetCheminLigneOption(selectSel, code, nom, fireChange) {
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
            '#prix_axetrans', '#prix_axetransit', '#prix_axetransit1', '#prix_axetransit2',
            '#hertrans', '#dateprtrans', '#program', '#cate', '#catetransit', '#catetransit1', '#catetransit2'
        ].forEach(function (s) {
            var el = document.querySelector(s);
            if (el) el.value = '';
        });
        __venteNotifyPrixAffiche();
        if (typeof __venteHideAllTransitProgSelects === 'function') __venteHideAllTransitProgSelects();
        if (typeof __venteClearDownstreamCheminHeures === 'function') __venteClearDownstreamCheminHeures();
        window.__venteCheminEtapes = null;
        window.__venteCheminCascadeStarted = false;
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
        if (box.scrollIntoView) {
            try { box.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (eSc) {}
        }
        var applyIdx = function (idx) {
            var ch = chemins[idx];
            if (hint) hint.textContent = __venteFormatAttenteLabel(ch);
            __venteApplyCheminChoice(ch);
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
        var defaultIdx = __venteDefaultCheminIndex(chemins, window.__venteSelectedHour);
        sel.selectedIndex = defaultIdx + 1;
        applyIdx(defaultIdx);
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
            if (chemins.length >= 1) {
                __venteShowCheminSelector(chemins, done);
                return;
            }
            __venteHideCheminSelector();
            if (payload.etapes && (Array.isArray(payload.etapes) ? payload.etapes.length : Object.keys(payload.etapes).length)) {
                done(payload.etapes);
                return;
            }
            done([]);
        };
        httpRequestitine.setRequestHeader('Content-Type', 'application/json');
        httpRequestitine.send();
    }

    function __venteParseHourOption(hOpt) {
        if (!hOpt || !hOpt.value) return null;
        var parts = String(hOpt.value).split('/');
        return {
            value: hOpt.value,
            idLh: parts[0] || '',
            heure: parts[1] || '',
            hasProg: hOpt.getAttribute('data-has-programme') === '1'
        };
    }

    function __venteSelectHourInSelect(selectEl, hour) {
        if (!hour || !hour.value) return;
        var sel = typeof selectEl === 'string' ? document.querySelector(selectEl) : selectEl;
        if (!sel || !sel.options) return;
        for (var i = 0; i < sel.options.length; i++) {
            if (sel.options[i].value === hour.value) {
                sel.selectedIndex = i;
                if (typeof sel.onchange === 'function') {
                    sel.onchange();
                }
                return;
            }
        }
    }

    function __venteCheminsFromPayload(payload) {
        if (!payload || typeof payload !== 'object') return [];
        var chemins = Array.isArray(payload.chemins) ? payload.chemins.slice() : [];
        if (chemins.length === 0 && payload.etapes) {
            var etapes = __venteNormalizeEtapes(payload.etapes);
            if (etapes.length >= 2) {
                chemins.push({
                    id: 0,
                    label: etapes.length + ' jambes · composition',
                    nb_jambes: etapes.length,
                    etapes: etapes,
                    source: payload.mode || 'declaratif'
                });
            }
        }
        return chemins;
    }

    /** Retire l'option « direct » fictive si l'heure choisie n'a pas de programme OD. */
    function __venteFilterCheminsGuichet(chemins, hour) {
        if (!Array.isArray(chemins)) return [];
        if (hour && hour.hasProg) return chemins.slice();
        return chemins.filter(function (c) {
            return c && c.source !== 'direct';
        });
    }

    function __venteDefaultCheminIndex(chemins, hour) {
        if (!Array.isArray(chemins) || !chemins.length) return 0;
        for (var i = 0; i < chemins.length; i++) {
            if (hour && !hour.hasProg && chemins[i].source === 'direct') continue;
            return i;
        }
        return 0;
    }

    function __venteFetchChemins(ctx, hour, callback) {
        if (!ctx || !ctx.seltdep || !ctx.arr || !ctx.datedepart) {
            if (typeof callback === 'function') callback([], null);
            return;
        }
        var sg = (ctx.sougid != null && ctx.sougid !== '') ? ctx.sougid : '0';
        var url = window.location.origin + `${APP_ROOT}/programmes/verifchemins/`
            + encodeURIComponent(ctx.seltdep + '-' + ctx.arr) + '/'
            + encodeURIComponent(ctx.datedepart) + '/'
            + encodeURIComponent(sg) + '/1';
        if (hour && hour.heure) {
            url += '?heure=' + encodeURIComponent(hour.heure);
        }
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.onload = function () {
            var payload = null;
            try { payload = JSON.parse(xhr.responseText); } catch (e) { payload = null; }
            if (Array.isArray(payload)) {
                var legacy = payload.length >= 2 ? [{
                    id: 0,
                    label: payload.length + ' jambes',
                    nb_jambes: payload.length,
                    etapes: payload,
                    source: 'legacy'
                }] : [];
                if (typeof callback === 'function') callback(legacy, { mode: 'legacy', chemins: legacy });
                return;
            }
            var chemins = __venteCheminsFromPayload(payload);
            if (typeof callback === 'function') callback(chemins, payload);
        };
        xhr.onerror = function () {
            if (typeof callback === 'function') callback([], null);
        };
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.send();
    }

    function __venteApplyCheminChoice(ch) {
        if (!ch) return;
        if (ch.source === 'direct') {
            var hourDirect = window.__venteSelectedHour;
            if (!hourDirect || !hourDirect.hasProg) return;
            __venteApplyDirectProgrammeForHour(hourDirect, window.__venteOdCtx);
            return;
        }
        var etapes = __venteNormalizeEtapes(ch.etapes);
        if (typeof window.__venteApplyTransitLegs === 'function') {
            window.__venteApplyTransitLegs(etapes);
        }
    }

    function __venteApplyDirectProgrammeForHour(hour, ctx) {
        if (!hour || !ctx) return;
        __venteHideCheminSelector();
        __venteShowDirectHourUi();
        var messEl = document.querySelector('#mess');
        if (messEl) messEl.style.display = 'none';

        var post_lh = String(hour.value).split('/');
        var sel = post_lh[0];
        var lhsel = post_lh[1];
        var dpt_date = ctx.datedepart;
        var typgarepa = document.querySelector('#arrsgare') ? document.querySelector('#arrsgare').value : '';
        var artypgarepa1 = typgarepa.split('/');
        var typgare = artypgarepa1[0];

        var httptypegare = new XMLHttpRequest();
        httptypegare.open('GET', window.location.origin + `${APP_ROOT}/programmes/gareprincipale/${typgare}/${lhsel}`, true);
        httptypegare.onload = function () {
            try {
                var dongare = JSON.parse(httptypegare.responseText);
                if (Object.entries(dongare).length >= 1) {
                    for (var key in Object.entries(dongare)) {
                        var tg = document.querySelector('#typegare');
                        if (tg) tg.value = `${dongare[key].typestatutgare}`;
                    }
                }
            } catch (eG) {}
        };
        httptypegare.setRequestHeader('Content-Type', 'application/json');
        httptypegare.send();

        var httpRequest = new XMLHttpRequest();
        httpRequest.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifprog/${ctx.seltdep}-${ctx.arr}/${dpt_date}/${sel}/${ctx.sougid || '0'}`, true);
        httpRequest.onload = function () {
            var typ_gare = document.querySelector('#typegare') ? document.querySelector('#typegare').value : '';
            var don = null;
            try { don = JSON.parse(httpRequest.responseText); } catch (eP) { don = null; }
            if (__venteHandleProgList(don, sel, dpt_date)) {
                return;
            }
            if (don == '' || __venteProgListFromResponse(don).length === 0) {
                if (typ_gare === 'Principale') {
                    var opt = document.createElement('option');
                    opt.value = 1;
                    opt.innerHTML = 1;
                    var ps = document.querySelector('#psieges');
                    if (ps) ps.add(opt);
                }
            }
        };
        httpRequest.setRequestHeader('Content-Type', 'application/json');
        httpRequest.send();
    }

    function __venteProposeItinerairesAfterHour() {
        var hour = window.__venteSelectedHour;
        var ctx = window.__venteOdCtx;
        var messEl = document.querySelector('#mess');
        var errEl = document.querySelector('#erreurMess');

        if (!hour || !hour.value || !ctx) {
            return;
        }

        __venteHideCheminSelector();
        if (messEl) messEl.style.display = 'block';
        if (errEl) errEl.innerHTML = 'Recherche des itinéraires…';

        __venteFetchChemins(ctx, hour, function (chemins, payload) {
            if (payload && (payload.mode === 'direct' || payload.mode === 'none')) {
                chemins = [];
            }
            chemins = __venteFilterCheminsGuichet(chemins, hour);
            if (!chemins.length) {
                __venteShowDirectHourUi();
                if (errEl) errEl.innerHTML = hour && !hour.hasProg && window.__venteHasTransit
                    ? 'Pas de départ à cette heure — aucune correspondance faisable.'
                    : 'Aucun itinéraire disponible pour cette heure.';
                return;
            }
            if (errEl) errEl.innerHTML = chemins.length > 1
                ? 'Choisissez un itinéraire (direct ou correspondances).'
                : 'Itinéraire proposé — vérifiez les correspondances.';
            // Toujours afficher le select dès qu’il y a ≥1 chemin (même un seul).
            __venteShowCheminSelector(chemins);
        });
    }

    function __venteOnHeureDepartChange() {
        var ps = document.querySelector('#psieges');
        if (ps) ps.options.length = 1;
        var tg = document.querySelector('#typegare');
        if (tg) tg.value = '';
        __venteHideProgSelect();

        var hOpt = document.querySelector('#hdepart').options[document.querySelector('#hdepart').options.selectedIndex];
        var hour = __venteParseHourOption(hOpt);
        window.__venteSelectedHour = hour;
        if (!hour || !hour.value) {
            __venteHideCheminSelector();
            return;
        }

        var messEl = document.querySelector('#mess');
        var errEl = document.querySelector('#erreurMess');

        // Aligné « Autres ventes » : départ réel → vente directe ; sinon transit si disponible.
        if (hour.hasProg) {
            __venteHideCheminSelector();
            if (messEl) messEl.style.display = 'none';
            __venteApplyDirectProgrammeForHour(hour, window.__venteOdCtx);
            return;
        }

        if (!window.__venteHasTransit) {
            __venteHideCheminSelector();
            __venteShowDirectHourUi();
            if (messEl) messEl.style.display = 'block';
            if (errEl) errEl.innerHTML = 'Aucun départ ni correspondance pour cette heure.';
            return;
        }

        if (messEl) messEl.style.display = 'block';
        if (errEl) errEl.innerHTML = 'Pas de départ à cette heure — correspondances proposées.';
        __venteProposeItinerairesAfterHour();
    }

    function __venteSaveHourSelection() {
        var h = document.querySelector('#hdepart');
        window.__venteSavedHourValue = (h && h.selectedIndex > 0 && h.value) ? h.value : '';
    }

    function __venteRestoreHourSelectionAndPropose() {
        var saved = window.__venteSavedHourValue || '';
        if (!saved) return;
        var h = document.querySelector('#hdepart');
        if (!h) return;
        for (var i = 0; i < h.options.length; i++) {
            if (h.options[i].value === saved) {
                h.selectedIndex = i;
                window.__venteSelectedHour = __venteParseHourOption(h.options[i]);
                __venteOnHeureDepartChange();
                return;
            }
        }
        window.__venteSavedHourValue = '';
    }

    function __venteTriggerHeuresReloadIfReady() {
        var depa = document.querySelector('#depargare');
        var arrpa = document.querySelector('#arrsgare');
        var da = document.querySelector('#date_depheure');
        var actu = document.querySelector('#actu');
        if (!depa || !String(depa.value || '').trim()) return;
        if (!arrpa || !String(arrpa.value || '').trim()) return;
        if (!da || !String(da.value || '').trim()) return;
        if (actu && da.value < actu.value) return;
        __venteHideCheminSelector();
        window.__venteReloadFromOdChange = true;
        if (typeof da.onchange === 'function') {
            da.onchange();
        }
        window.__venteReloadFromOdChange = false;
    }

    function __venteOnArrsgareChange() {
        __venteSaveHourSelection();
        __venteLoadQuartiersArrivee();
        __venteTriggerHeuresReloadIfReady();
    }

    function __venteOnDepargareChange() {
        __venteSaveHourSelection();
        __venteTriggerHeuresReloadIfReady();
    }

    function __venteFillQuartierSelect(rows) {
        var q = document.querySelector('#quartier');
        if (!q) return;
        var keep = window.__venteSavedQuartierValue || q.value || '';
        q.options.length = 1;
        q.selectedIndex = 0;
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
            } else {
                // Ancienne valeur absente de la nouvelle liste (ex. Marche/Banfora après correction NIA4).
                q.selectedIndex = 0;
            }
        }
    }
    function __venteLoadQuartiersArrivee() {
        try {
            var qBefore = document.querySelector('#quartier');
            if (qBefore && qBefore.value) {
                window.__venteSavedQuartierValue = qBefore.value;
            }
            ['#prix_axe','#tarifattrib','#date_depheure','#program','#idcompg','#idcompg1','#idcompg2','#idcompg3'].forEach(function (s) {
                __venteSafeReset(s, null);
            });
            ['#hdepart','#psieges','#selprog','#hdepartitine','#psiegesitines','#idcheminsheur',
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
        arBoot.addEventListener('change', __venteOnArrsgareChange);
        arBoot._venteQuartierBound = true;
    }
    document.querySelectorAll('#depargare').forEach(function (depEl) {
        if (depEl && !depEl._venteOdReloadBound) {
            depEl.addEventListener('change', __venteOnDepargareChange);
            depEl._venteOdReloadBound = true;
        }
    });
    
    document.querySelectorAll('.addventeticket').forEach(function (e) 
    {
        __venteSetTaTitle('VENTE DE TICKET');
            
            let da = document.querySelector('#date_depheure');
            if (da !== null){
                da.onchange = () => 
                {
                    if (!window.__venteReloadFromOdChange) {
                        window.__venteSavedHourValue = '';
                    }
                    __venteHideCheminSelector();
                    __venteShowDirectHourUi();
                    
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
                        window.__venteOdCtx = {
                            seltdep: seltdep,
                            arr: arr,
                            arr2: arr2,
                            sougid: sougid,
                            datedepart: datedepart
                        };
                        if(datedepart >= dateactu)
                        {
                            
                            httpRequetes.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheuresvente/${seltdep}-${arr}/${datedepart}/${sougid || '0'}`, true);
                            httpRequetes.onload = () => {
                                var payloadHv = {};
                                try { payloadHv = JSON.parse(httpRequetes.responseText) || {}; } catch (eHv) { payloadHv = {}; }
                                var heuresList = Array.isArray(payloadHv.heures) ? payloadHv.heures : [];
                                window.__venteHasTransit = !!payloadHv.has_transit;
                                window.__venteTransitSources = Array.isArray(payloadHv.transit_sources)
                                    ? payloadHv.transit_sources : [];
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
                                                        __venteNotifyPrixAffiche();
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
                                                        window.__venteCheminEtapes = donitines;
                                                        window.__venteCheminCascadeStarted = false;
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
                                                                __venteNotifyPrixAffiche();


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
                                                                            __venteFillHeureItineSelect(hd, infositin, window.__venteSelectedHour);
                                                                        } catch (eH) {}
                                                                    };
                                                                    httpH.setRequestHeader('Content-Type', 'application/json');
                                                                    httpH.send();
                                                                });
                                                            }
                                                
                                                            if(i === 2)
                                                            {
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
                                                                                        __venteMaybeStartCheminCascade();
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
                                                                document.querySelector('#itinecodes').value = `${donitines[0].id_lignes}`;
                                                               document.querySelector('#idcompg').value = `${donitines[0].id_compaga}`;

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
                                                                                        __venteMaybeStartCheminCascade();
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
                                                                                        __venteMaybeStartCheminCascade();
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

                                // Après départ + arrivée + date + heure : proposer direct et multi-jambes.

                                        let hrdepart = document.querySelector('#hdepart');
                                        if (hrdepart !== null) {
                                            hrdepart.onchange = __venteOnHeureDepartChange;
                                        }
                                        __venteRestoreHourSelectionAndPropose();
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
            
            __venteWireCancelButton('idreset');
                
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
                        // Ne pas synchroniser les miroirs client : ils servent à détecter
                        // un changement d'identité (même téléphone, autre passager).
                    });
                }

                AppRequestGuard.guardForm('#taForm');
                AppRequestGuard.ensureNonce('#taForm', 'sale_nonce');
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
        prixLegs: {},
        id_escale: '',
        exclude: '',
        tarif: '1',
        isTransitTicket: false,
        isRetourConfirme: false,
        nbrJambes: 1,
        jambesExpected: [],
        legsVerified: {},
        lookup1Done: false,
        lookup2Done: false,
        tamponcodtr: ''
    };

    function __reprogQ(id) { return document.getElementById(id); }

    function __reprogSetVal(id, val) {
        var el = __reprogQ(id);
        if (el) el.value = val == null ? '' : val;
        return el;
    }

    function __reprogAllowPrixDiff() {
        // Report gratuit : écart de prix correspondances autorisé pour tous les rôles.
        return true;
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
        if (st.isTransitTicket) {
            Object.keys(st.prixLegs || {}).forEach(function (k) {
                if (String(k) === '1') return;
                total += __reprogNumPrix(st.prixLegs[k]);
            });
            if (!Object.keys(st.prixLegs || {}).length && st.lookup2Done) {
                total += __reprogNumPrix(st.prix2);
            }
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
            box.className = 'small mb-0 mt-2 text-muted';
            box.textContent = 'Report gratuit — somme segments ' + sum
                + ' (ticket d’origine ' + ref + ') : non facturé à l’agent.';
            return true;
        }
        box.className = 'small mb-0 mt-2 ' + (ok ? 'text-success' : 'text-muted');
        box.textContent = ok
            ? ('Somme segments ' + sum + ' = ticket ' + ref + ' (report gratuit)')
            : ('Somme segments ' + sum + ' / ticket ' + ref + ' (report gratuit, OK)');
        return true;
    }

    function __reprogPrepareTransitSubmit() {
        var etapes = window.__reprogState.etapes || [];
        var n = etapes.length;
        if (n < 2) return { ok: false, msg: 'Itinéraire invalide.' };
        if (window.__reprogState.isTransitTicket && !__reprogAllLegsVerified()) {
            return { ok: false, msg: 'Vérifiez tous les codes du ticket transit avant de reporter.' };
        }
        __reprogQ('reprog_mode_unifie').value = 'transit';
        __reprogQ('reprog_nbr_seg_unifie').value = String(n);
        var first = null;
        for (var i = 0; i < n; i++) {
            var synced = __reprogSyncSegPost(i);
            if (!synced || !synced.prog || !synced.siege) {
                return {
                    ok: false,
                    msg: 'Complétez compagnie, heure et siège pour la correspondance ' + (i + 1) + '.'
                };
            }
            if (i === 0) first = synced;
        }
        var ref = __reprogPrixRef();
        var refEl = __reprogQ('prixventeunifie_ref');
        if (refEl) refEl.value = String(ref);
        var pxEl = __reprogQ('prixventeunifie');
        if (pxEl) pxEl.value = String(ref);
        if (first) {
            __reprogSetPost(first.prog, first.compaga, first.siege);
            if (first.row) {
                __reprogQ('catreprogrammeunifie').value = first.row.categori || '';
            }
        }
        return { ok: true };
    }

    function __reprogAllLegsVerified() {
        var st = window.__reprogState;
        if (!st.isTransitTicket) return true;
        var n = st.nbrJambes || 1;
        if (n < 2) return true;
        for (var i = 1; i <= n; i++) {
            if (!st.legsVerified[i]) return false;
        }
        return true;
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
            'directionclpunifie', 'escaleclpunifie', 'codeclpunifie', 'heureclpunifie', 'compagnieclpunifie',
            'prixclpunifie', 'code2clpunifie'
        ].forEach(function (id) {
            var el = __reprogQ(id);
            if (el) {
                el.innerHTML = '';
                if (id === 'code2clpunifie' || id === 'escaleclpunifie') el.style.display = 'none';
            }
        });
        var infos = __reprogQ('reprog_infos_wrap');
        if (infos) infos.style.display = 'none';
        var wrap = __reprogQ('reprog_choix_wrap');
        if (wrap) wrap.style.display = 'none';
        var extra = __reprogQ('reprog_codes_extra_wrap');
        if (extra) {
            extra.innerHTML = '';
            extra.style.display = 'none';
        }
        var det = __reprogQ('reprog_transit_detect_msg');
        if (det) {
            det.style.display = 'none';
            det.textContent = '';
        }
        var resume = __reprogQ('reprog_od_resume');
        if (resume) {
            resume.style.display = 'none';
            resume.textContent = '';
        }
        [2, 3, 4].forEach(function (li) {
            ['passerpunifie', 'codeticketsunifie', 'codeclient_ticket_unifie', 'prixventeunifie'].forEach(function (pref) {
                var el = __reprogQ(pref + li);
                if (el) el.value = '';
            });
        });
        ['prixventeunifie_ref', 'id_escale_vente_reprog', 'code_gadest_vente_reprog', 'nom_dest_vente_reprog'].forEach(function (id) {
            var el = __reprogQ(id);
            if (el) el.value = '';
        });
        var isTr = __reprogQ('reprog_is_transit_ticket');
        if (isTr) isTr.value = '0';
        var nOrig = __reprogQ('reprog_nbr_jambes_origine');
        if (nOrig) nOrig.value = '1';
        __reprogResetChoix();
        window.__reprogState.rows = [];
        window.__reprogState.hasTransit = false;
        window.__reprogState.transitHours = [];
        window.__reprogState.prix = '';
        window.__reprogState.prix2 = '';
        window.__reprogState.prixLegs = {};
        window.__reprogState.id_escale = '';
        window.__reprogState.lookup1Done = false;
        window.__reprogState.lookup2Done = false;
        window.__reprogState.tamponcodtr = '';
        window.__reprogState.isTransitTicket = false;
        window.__reprogState.isRetourConfirme = false;
        window.__reprogState.nbrJambes = 1;
        window.__reprogState.jambesExpected = [];
        window.__reprogState.legsVerified = {};
    }

    function __reprogCanOpenChoix() {
        var st = window.__reprogState;
        if (!st.lookup1Done) return false;
        if (st.isTransitTicket && !__reprogAllLegsVerified()) return false;
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
            var nJ = window.__reprogState.nbrJambes || 1;
            prixCl.textContent = window.__reprogState.isTransitTicket
                ? ('PRIX TOTAL (' + nJ + ' jambes): ' + ref)
                : ('PRIX: ' + ref);
        }

        // Résumé OD avant choix date / itinéraires.
        var st = window.__reprogState;
        var resume = __reprogQ('reprog_od_resume');
        if (resume) {
            var escHint = '';
            var escNom = (__reprogQ('nom_dest_vente_reprog') || {}).value || '';
            if (escNom || st.id_escale) {
                escHint = ' — destination ticket : ' + (escNom || 'escale') + ' (conservée)';
            }
            resume.style.display = 'block';
            resume.textContent = (st.isRetourConfirme ? 'Retour confirmé à reporter : ' : 'Parcours à reporter : ')
                + (st.gaexp || '—') + ' → ' + (st.gadest || '—')
                + (st.isTransitTicket ? (' (transit ' + (st.nbrJambes || '') + ' jambes)') : ' (direct)')
                + escHint
                + '. Choisissez une date puis un itinéraire (direct ou correspondance).';
        }

        __reprogQ('reprog_choix_wrap').style.display = 'block';
        __reprogSetAncreVisible(false);
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
                + '?prix=' + encodeURIComponent(String(ref))
                + (window.__reprogState.id_escale
                    ? ('&id_escale=' + encodeURIComponent(String(window.__reprogState.id_escale)))
                    : ''),
            function (data2) {
                window.__reprogState.rows = __reprogRowsArray(data2);
                __reprogOnDateChange();
            }
        );
    }

    function __reprogLookupMode() {
        var modal = __reprogQ('repro-unifie-0');
        var allowTampon = modal && modal.getAttribute('data-allow-tampon') === '1';
        var tamponCb = __reprogQ('mode_tampon_unifie');
        return (allowTampon && tamponCb && tamponCb.checked) ? 'tampon' : 'ticket';
    }

    function __reprogBuildExtraCodeFields(nbrJambes, jambes, verifiedCode) {
        var wrap = __reprogQ('reprog_codes_extra_wrap');
        if (!wrap) return;
        wrap.innerHTML = '';
        if (!nbrJambes || nbrJambes < 2) {
            wrap.style.display = 'none';
            return;
        }
        wrap.style.display = 'block';
        var used = {};
        used[String(verifiedCode || '').trim().toUpperCase()] = 1;
        var hints = [];
        (jambes || []).forEach(function (j) {
            if (!j) return;
            var ct = String(j.code_ticket || '').trim();
            if (ct && !used[ct.toUpperCase()]) {
                hints.push(ct);
                used[ct.toUpperCase()] = 1;
            }
        });
        var hintIdx = 0;
        for (var leg = 2; leg <= nbrJambes; leg++) {
            var row = document.createElement('div');
            row.className = 'form-row align-items-end reprog-leg-row';
            row.id = 'reprog_leg_row_' + leg;
            var hint = hints[hintIdx] || '';
            if (hint) hintIdx++;
            row.innerHTML =
                '<div class="form-group col-md-6 mb-2">'
                + '<label class="small mb-0">' + leg + '<sup>e</sup> code (jambe transit)</label>'
                + '<input class="form-control form-control-sm" type="text" id="code_lookup_leg_' + leg
                + '" autocomplete="off" placeholder="Code de la ' + leg + 'ᵉ jambe"'
                + (hint ? (' value="' + String(hint).replace(/"/g, '&quot;') + '"') : '') + '>'
                + '</div>'
                + '<div class="form-group col-md-6 mb-2">'
                + '<span class="btn btn-outline-success btn-sm btn-block" type="button" id="reprogrammer_infos_leg_'
                + leg + '">Vérifier le ' + leg + '<sup>e</sup> code</span>'
                + '<p class="small text-success mb-0 mt-1" id="reprog_leg_ok_' + leg + '" style="display:none">Vérifié</p>'
                + '</div>';
            wrap.appendChild(row);
            (function (legNum) {
                var btn = __reprogQ('reprogrammer_infos_leg_' + legNum);
                if (btn) {
                    btn.onclick = function () {
                        __reprogVerifyExtraLeg(legNum);
                    };
                }
            })(leg);
        }
    }

    function __reprogApplyOdFromLegs() {
        var st = window.__reprogState;
        var jambes = st.jambesExpected || [];
        if (!jambes.length) return;
        var first = jambes[0] || {};
        var last = jambes[jambes.length - 1] || {};
        var ga = st.gaexp || first.gaexp_lg || '';
        var gd = last.gadest_lg || st.gadest || '';
        if (last.nom_dest_vente) {
            // escale éventuelle sur dernière jambe
            if (__reprogQ('id_escale_vente_reprog') && last.id_escale_vente) {
                __reprogQ('id_escale_vente_reprog').value = String(last.id_escale_vente);
                st.id_escale = String(last.id_escale_vente);
            }
            if (__reprogQ('code_gadest_vente_reprog')) {
                __reprogQ('code_gadest_vente_reprog').value = last.code_gadest_vente || '';
            }
            if (__reprogQ('nom_dest_vente_reprog')) {
                __reprogQ('nom_dest_vente_reprog').value = last.nom_dest_vente || last.dest_affiche || '';
            }
        }
        if (ga && gd) {
            st.gaexp = ga;
            st.gadest = gd;
            st.axe = ga + '-' + gd;
            if (__reprogQ('gaexp_unifie')) __reprogQ('gaexp_unifie').value = ga;
            if (__reprogQ('gadest_unifie')) __reprogQ('gadest_unifie').value = gd;
            if (__reprogQ('axe_unifie')) __reprogQ('axe_unifie').value = st.axe;
            var dirEl = __reprogQ('directionclpunifie');
            if (dirEl) {
                dirEl.textContent = 'DIRECTION: ' + ga + ' → ' + (last.dest_affiche || gd)
                    + ' (transit ' + (st.nbrJambes || jambes.length) + ' jambes)';
            }
        }
    }

    function __reprogVerifyExtraLeg(legNum) {
        var dateEl = __reprogQ('datereprog_unifie');
        var input = __reprogQ('code_lookup_leg_' + legNum);
        var cocl = String((input && input.value) || '').trim();
        if (__reprogQ('smspunifie')) __reprogQ('smspunifie').style.display = 'none';
        if (!window.__reprogState.lookup1Done) {
            alert('Vérifiez d’abord le 1er code.');
            return;
        }
        if (!cocl) {
            alert('Saisissez le ' + legNum + 'ᵉ code.');
            return;
        }
        var code1 = String((__reprogQ('code_lookup_unifie') || {}).value || '').trim();
        if (cocl === code1
            || cocl === String((__reprogQ('codeclient_ticket_unifie') || {}).value || '').trim()
            || cocl === String((__reprogQ('codeticketsunifie') || {}).value || '').trim()) {
            alert('Ce code doit être différent du 1er.');
            return;
        }
        for (var prev = 2; prev < legNum; prev++) {
            if (!window.__reprogState.legsVerified[prev]) {
                alert('Vérifiez d’abord le ' + prev + 'ᵉ code.');
                return;
            }
            var prevVal = String((__reprogQ('code_lookup_leg_' + prev) || {}).value || '').trim();
            if (cocl === prevVal) {
                alert('Code déjà utilisé pour une autre jambe.');
                return;
            }
        }

        __reprogXhrGet(
            window.location.origin + APP_ROOT
                + '/reprogrammes/lookup_unifie?mode=' + encodeURIComponent(__reprogLookupMode())
                + '&code=' + encodeURIComponent(cocl),
            function (d2) {
                if (__reprogLookupRefused(d2, legNum + 'ᵉ code invalide ou non reprogrammable.')) {
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
                            'Ce code n’appartient pas au même ticket transit (lien / client).';
                    }
                    return;
                }
                if (String(d2.tamponcod || '') === String((__reprogQ('codeticketsunifie') || {}).value || '')
                    || String(d2.code_passager || '') === String((__reprogQ('passerpunifie') || {}).value || '')) {
                    alert('Ce code correspond au même billet que le 1er.');
                    return;
                }

                var elPass = __reprogQ('passerpunifie' + legNum);
                var elTamp = __reprogQ('codeticketsunifie' + legNum);
                var elTick = __reprogQ('codeclient_ticket_unifie' + legNum);
                var elPrix = __reprogQ('prixventeunifie' + legNum);
                if (elPass) elPass.value = d2.code_passager || '';
                if (elTamp) elTamp.value = d2.tamponcod || '';
                if (elTick) elTick.value = d2.code_ticket || '';
                if (elPrix) elPrix.value = d2.prixvente != null ? d2.prixvente : '';
                window.__reprogState.prixLegs[legNum] = d2.prixvente != null ? String(d2.prixvente) : '0';
                if (legNum === 2) {
                    window.__reprogState.prix2 = window.__reprogState.prixLegs[2];
                }
                window.__reprogState.legsVerified[legNum] = true;
                window.__reprogState.lookup2Done = __reprogAllLegsVerified();

                var okEl = __reprogQ('reprog_leg_ok_' + legNum);
                if (okEl) {
                    okEl.style.display = 'block';
                    okEl.textContent = 'OK — ' + (d2.code_ticket || '') + ' / '
                        + (d2.nom_ligne || '') + ' ' + (d2.heure || '');
                }
                var infoExtra = __reprogQ('code2clpunifie');
                if (infoExtra) {
                    infoExtra.style.display = 'block';
                    var parts = [];
                    for (var i = 2; i <= (window.__reprogState.nbrJambes || 2); i++) {
                        if (!window.__reprogState.legsVerified[i]) continue;
                        parts.push(i + 'ᵉ: ' + ((__reprogQ('codeclient_ticket_unifie' + i) || {}).value || ''));
                    }
                    infoExtra.textContent = parts.join(' | ');
                }

                __reprogApplyOdFromLegs();
                if (d2.gadest_lg) {
                    // Affiner OD avec la dernière jambe vérifiée
                    var ga1 = window.__reprogState.gaexp || '';
                    if (ga1) {
                        window.__reprogState.gadest = d2.gadest_lg;
                        window.__reprogState.axe = ga1 + '-' + d2.gadest_lg;
                        if (__reprogQ('gadest_unifie')) __reprogQ('gadest_unifie').value = d2.gadest_lg;
                        if (__reprogQ('axe_unifie')) __reprogQ('axe_unifie').value = window.__reprogState.axe;
                    }
                }

                if (__reprogAllLegsVerified()) {
                    if (__reprogQ('smspunifie')) __reprogQ('smspunifie').style.display = 'none';
                    __reprogOpenChoixIfReady(dateEl);
                } else {
                    if (__reprogQ('smspunifie')) __reprogQ('smspunifie').style.display = 'block';
                    if (__reprogQ('erreurSmspunifie')) {
                        __reprogQ('erreurSmspunifie').textContent =
                            'Code ' + legNum + ' OK. Vérifiez les codes restants.';
                    }
                }
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

    function __reprogEtapePreferCode(etape) {
        if (!etape) return '';
        return String(
            etape._graphe_code_progr || etape._code_progr || etape.code_progr || ''
        );
    }

    function __reprogLoadSiegesDirect(progValue) {
        var siegeSel = __reprogQ('numsiegepunifie');
        __reprogResetSelect(siegeSel, 'Choisissez le siège');
        var errBox = __reprogQ('erreursiegunifie');
        var errTxt = __reprogQ('erreurSiegeunifie');
        function showErr(msg) {
            if (errBox) errBox.style.display = msg ? 'block' : 'none';
            if (errTxt) errTxt.textContent = msg || '';
        }
        showErr('');
        if (!progValue) return;
        var parts = String(progValue).split('/');
        var selh = parts[0];
        if (!selh) return;
        __reprogQ('programrepunifie').value = selh;

        var root = (typeof APP_ROOT !== 'undefined' && APP_ROOT) ? APP_ROOT : '';
        window.__reprogState._directSiegeLoadId = (window.__reprogState._directSiegeLoadId || 0) + 1;
        var loadId = window.__reprogState._directSiegeLoadId;

        function fillDirectSieges(dattas, err) {
            if (window.__reprogState._directSiegeLoadId !== loadId) return;
            var live = __reprogQ('numsiegepunifie');
            if (!live) return;
            __reprogResetSelect(live, 'Choisissez le siège');
            if (err) {
                showErr('Erreur chargement sièges (' + err + ').');
                return;
            }
            var n = 0;
            __reprogRowsArray(dattas).forEach(function (s) {
                if (!s || s.siege_num == null || s.siege_num === '') return;
                var optS = document.createElement('option');
                optS.value = s.siege_num;
                optS.textContent = s.siege_num;
                live.add(optS);
                n++;
            });
            if (n === 0) {
                showErr('Aucun siège disponible pour ce départ.');
            }
        }

        function loadWithIntervals(i1, i2) {
            if (i1 === '' || i1 == null || i2 === '' || i2 == null) {
                showErr('Intervalles de sièges manquants pour ce programme.');
                return;
            }
            __reprogXhrGet(
                window.location.origin + root + '/programmes/siegdisponibletrans/'
                    + encodeURIComponent(selh) + '/'
                    + encodeURIComponent(i1) + '/'
                    + encodeURIComponent(i2),
                fillDirectSieges
            );
        }

        // Intervalles déjà connus dans heures_unifie → sièges tout de suite.
        var fromRows = __reprogRowsArray(window.__reprogState.rows).filter(function (r) {
            return r && String(r.code_progr) === String(selh);
        })[0];
        if (fromRows && fromRows.intervalle1 != null && fromRows.intervalle2 != null
            && fromRows.intervalle1 !== '' && fromRows.intervalle2 !== '') {
            __reprogSetVal('placevenduunifie', fromRows.intervalle1);
            __reprogSetVal('dplacevenduunifie', fromRows.intervalle2);
            if (fromRows.nom_ligne) __reprogSetVal('replignunifie', fromRows.nom_ligne);
            if (fromRows.heure) __reprogSetVal('repherunifie', fromRows.heure);
            if (fromRows.date_progr) __reprogSetVal('datereprogrammeunifie', fromRows.date_progr);
            if (fromRows.categori) __reprogSetVal('catreprogrammeunifie', fromRows.categori);
            if (fromRows.ident_ligne || fromRows.ligne_id) {
                __reprogSetVal('idreplignunifie', fromRows.ident_ligne || fromRows.ligne_id);
            }
            if (fromRows.id_compaga) __reprogSetVal('compgcfunifie', fromRows.id_compaga);
            loadWithIntervals(fromRows.intervalle1, fromRows.intervalle2);
            return;
        }

        __reprogXhrGet(
            window.location.origin + root + '/reprogrammes/siegdispo/' + encodeURIComponent(selh),
            function (data, errMeta) {
                if (window.__reprogState._directSiegeLoadId !== loadId) return;
                var rows = __reprogRowsArray(data);
                if (!rows.length) {
                    showErr(errMeta
                        ? ('Programme introuvable (' + errMeta + ').')
                        : 'Programme introuvable pour charger les sièges.');
                    return;
                }
                var meta = rows[0];
                var i1 = meta.intervalle1;
                var i2 = meta.intervalle2;
                __reprogSetVal('placevenduunifie', i1 != null ? i1 : '');
                __reprogSetVal('dplacevenduunifie', i2 != null ? i2 : '');
                __reprogSetVal('replignunifie', meta.nom_ligne || '');
                __reprogSetVal('repherunifie', meta.heure || '');
                __reprogSetVal('datereprogrammeunifie', meta.date_progr || '');
                __reprogSetVal('catreprogrammeunifie', meta.categori || '');
                __reprogSetVal('idreplignunifie', meta.ident_ligne || meta.ligne_id || '');
                if (meta.id_compaga) __reprogSetVal('compgcfunifie', meta.id_compaga);
                loadWithIntervals(i1, i2);
            }
        );
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
        // sg=0 : propositions sur l’OD du ticket (pas le filtre sous-gare session).
        var url = window.location.origin + APP_ROOT
            + '/programmes/verifchemins/'
            + encodeURIComponent(st.axe) + '/'
            + encodeURIComponent(dateYmd) + '/0/1';
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
                    code_progr: row.code_progr || '',
                    _code_progr: row.code_progr || '',
                    _id_ligneheure: row.id_ligneheure || '',
                    id_ligneheure: row.id_ligneheure || '',
                    intervalle1: row.intervalle1,
                    intervalle2: row.intervalle2,
                    date_progr: row.date_progr || dateYmd,
                    prix: row.prix
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

    function __reprogEtapeOdLabel(etape, idx, total) {
        if (!etape) return '';
        var dep = etape.depart_itine || etape.nom_gaep || etape.code_gaexp
            || etape.gaexp_lg || etape.gaexp || '';
        var arr = etape.arrive_itine || etape.nom_gadest || etape.code_gadest
            || etape.gadest_lg || etape.gadest || '';
        // Dernier segment : destination ticket = escale déjà vendue (si présente).
        if (total > 0 && idx === total - 1) {
            var escNom = (__reprogQ('nom_dest_vente_reprog') || {}).value || '';
            var escId = (__reprogQ('id_escale_vente_reprog') || {}).value || '';
            if (escId || escNom) {
                arr = (escNom || arr) + ' (escale)';
            }
        }
        if (dep && arr) return dep + ' → ' + arr;
        if (arr) return '→ ' + arr;
        if (dep) return dep + ' → …';
        return '';
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
            var odLabel = __reprogEtapeOdLabel(etape, idx, etapes.length);
            var dateDef = (__reprogQ('datereprog_unifie') || {}).value || '';
            // Correspondance graphe : chaque jambe peut être J+1 (day_offset / date programme).
            if (etape && etape._graphe_date_progr) {
                dateDef = String(etape._graphe_date_progr).slice(0, 10);
            } else if (etape && etape._graphe_day_offset && dateDef) {
                var off = parseInt(etape._graphe_day_offset, 10) || 0;
                if (off > 0) {
                    var dParts = String(dateDef).split('-');
                    if (dParts.length === 3) {
                        var dObj = new Date(
                            parseInt(dParts[0], 10),
                            parseInt(dParts[1], 10) - 1,
                            parseInt(dParts[2], 10) + off
                        );
                        var mm = String(dObj.getMonth() + 1);
                        var dd = String(dObj.getDate());
                        if (mm.length < 2) mm = '0' + mm;
                        if (dd.length < 2) dd = '0' + dd;
                        dateDef = dObj.getFullYear() + '-' + mm + '-' + dd;
                    }
                }
            }
            var wrap = document.createElement('div');
            wrap.className = 'reprog-seg';
            wrap.id = 'reprog_seg_' + idx;
            // Compagnie · OD (±escale) · date · heure · siège
            wrap.innerHTML =
                '<h6>Segment ' + (idx + 1) + ' — ' + ligneNom
                + (odLabel ? (' <span class="text-muted font-weight-normal">(' + odLabel + ')</span>') : '')
                + '</h6>'
                + (odLabel
                    ? ('<p class="small text-muted mb-2" id="reprog_seg_od_' + idx + '">OD : ' + odLabel + '</p>')
                    : '')
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
            // Jambe unique (direct) : injecter le programme exact si seg_progs l’a filtré.
            var preferCode = __reprogEtapePreferCode(seg.etape);
            if (preferCode) {
                var hasPref = false;
                for (var pi = 0; pi < rows.length; pi++) {
                    if (rows[pi] && String(rows[pi].code_progr) === preferCode) {
                        hasPref = true;
                        break;
                    }
                }
                if (!hasPref) {
                    var fromState = __reprogRowsArray(window.__reprogState.rows).filter(function (r) {
                        return r && String(r.code_progr) === preferCode;
                    });
                    if (fromState.length) {
                        rows = rows.concat(fromState);
                    } else if (seg.etape && (seg.etape.intervalle1 != null || seg.etape._code_progr)) {
                        // Reconstruire une ligne minimale depuis l’étape directe.
                        rows = rows.concat([{
                            code_progr: preferCode,
                            id_ligneheure: seg.etape.id_ligneheure || seg.etape._id_ligneheure || '',
                            typetarif: seg.etape.typetarif || tarif,
                            heure: seg.etape.heure || '',
                            id_compaga: seg.etape.id_compaga || '',
                            nom_ligne: seg.etape.nom_ligne || seg.etape.nom_itineraires || '',
                            ident_ligne: seg.ligneId,
                            intervalle1: seg.etape.intervalle1,
                            intervalle2: seg.etape.intervalle2,
                            categori: seg.etape.categori || '',
                            date_progr: seg.etape.date_progr || dateYmd,
                            prix: seg.etape.prix
                        }]);
                    }
                }
            }

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

            // Prefer compagnie du programme exact (1 jambe / graphe).
            var prefCie = __reprogEtapeCieKey(seg.etape);
            if (preferCode && seg.rows) {
                for (var rj = 0; rj < seg.rows.length; rj++) {
                    if (seg.rows[rj] && String(seg.rows[rj].code_progr) === preferCode) {
                        var ck = __reprogRowCieKey(seg.rows[rj]);
                        if (ck && seg.byCie[ck]) {
                            prefCie = ck;
                        }
                        break;
                    }
                }
            }
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
                // Même sans résultat seg_progs : 1 jambe avec code connu → injecter via fillCompanies.
                if (!rows.length && __reprogEtapePreferCode(seg.etape)) {
                    fillCompanies([], false);
                    if (Object.keys((window.__reprogState.segData[idx] || {}).byCie || {}).length) {
                        return;
                    }
                }
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
        var preferCode = __reprogEtapePreferCode(seg.etape);
        // 1 jambe : heure du programme exact si connu.
        if (preferCode) {
            for (var hi = 0; hi < hours.length; hi++) {
                var cand = hoursMap[hours[hi]] || [];
                for (var cj = 0; cj < cand.length; cj++) {
                    if (cand[cj] && String(cand[cj].code_progr) === preferCode) {
                        pref = hours[hi];
                        hi = hours.length;
                        break;
                    }
                }
            }
        }
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
        // Préférer le programme exact (direct _code_progr ou graphe _graphe_code_progr).
        var preferCode = __reprogEtapePreferCode(seg.etape);
        var row = list[0];
        if (preferCode) {
            for (var ri = 0; ri < list.length; ri++) {
                if (list[ri] && String(list[ri].code_progr) === preferCode) {
                    row = list[ri];
                    break;
                }
            }
        }
        seg.selectedRow = row;
        seg.siegeLoadId = (seg.siegeLoadId || 0) + 1;
        var loadId = seg.siegeLoadId;
        __reprogSegErr(idx, 'Chargement des sièges…');

        var i1 = row.intervalle1 != null && row.intervalle1 !== '' ? row.intervalle1 : '';
        var i2 = row.intervalle2 != null && row.intervalle2 !== '' ? row.intervalle2 : '';
        var compaga = row.id_compaga || row.cle_compagnie_arrivee || __reprogRowCieKey(row);
        var root = (typeof APP_ROOT !== 'undefined' && APP_ROOT) ? APP_ROOT : '';

        if (idx === 0) {
            var progVal = row.code_progr + '/' + (row.id_ligneheure || '') + '/'
                + (row.typetarif || __reprogSegTarif());
            __reprogSetPost(progVal, compaga, '');
            __reprogSetVal('replignunifie', row.nom_ligne || '');
            __reprogSetVal('repherunifie', row.heure || '');
            __reprogSetVal('datereprogrammeunifie', row.date_progr || '');
            __reprogSetVal('catreprogrammeunifie', row.categori || '');
            __reprogSetVal('idreplignunifie', row.ident_ligne || row.ligne_id || '');
            __reprogSetVal('placevenduunifie', i1);
            __reprogSetVal('dplacevenduunifie', i2);
        }
        __reprogSyncSegPost(idx);
        __reprogUpdatePrixSum();

        function fillSieges(dattas, err) {
            if (seg.siegeLoadId !== loadId) return;
            if (err) {
                __reprogSegErr(idx, 'Erreur chargement sièges (' + err + ').');
                return;
            }
            var liveSel = __reprogUiSiege(idx);
            if (!liveSel) return;
            // Re-reset au cas où une réponse concurrente a touché le select.
            if (liveSel !== siegeSel) {
                __reprogResetSelect(liveSel, 'Choisissez le siège');
                siegeSel = liveSel;
            }
            var n = 0;
            __reprogRowsArray(dattas).forEach(function (s) {
                if (!s || s.siege_num == null || s.siege_num === '') return;
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
            if (seg.siegeLoadId !== loadId) return;
            if (db === '' || fn === '' || db == null || fn == null) {
                __reprogSegErr(idx, 'Intervalles de sièges manquants pour ce programme.');
                return;
            }
            __reprogXhrGet(
                window.location.origin + root
                    + '/programmes/siegdisponibletrans/'
                    + encodeURIComponent(row.code_progr) + '/'
                    + encodeURIComponent(db) + '/'
                    + encodeURIComponent(fn),
                fillSieges
            );
        }

        // Comme la vente : charger les sièges dès que les intervalles sont connus
        // (ne pas attendre siegdispotrans — évite file d’attente session / timeout).
        if (i1 !== '' && i1 != null && i2 !== '' && i2 != null) {
            loadSiegesWithIntervals(i1, i2);
        }

        __reprogXhrGet(
            window.location.origin + root
                + '/programmes/siegdispotrans/'
                + encodeURIComponent(row.code_progr),
            function (meta, errMeta) {
                if (seg.siegeLoadId !== loadId) return;
                var metas = __reprogRowsArray(meta);
                if (metas.length) {
                    var m = metas[0];
                    var hadIntervals = (i1 !== '' && i1 != null && i2 !== '' && i2 != null);
                    if ((i1 === '' || i1 == null) && m.intervalle1 != null) i1 = m.intervalle1;
                    if ((i2 === '' || i2 == null) && m.intervalle2 != null) i2 = m.intervalle2;
                    if (m.prix != null && (row.prix == null || row.prix === '')) {
                        row.prix = m.prix;
                    }
                    if (m.categori) row.categori = m.categori;
                    if (idx === 0) {
                        if (m.categori) __reprogSetVal('catreprogrammeunifie', m.categori);
                        __reprogSetVal('placevenduunifie', i1);
                        __reprogSetVal('dplacevenduunifie', i2);
                    }
                    __reprogSyncSegPost(idx);
                    __reprogUpdatePrixSum();
                    // Intervalles seulement via meta → charger maintenant.
                    if (!hadIntervals) {
                        loadSiegesWithIntervals(i1, i2);
                    }
                } else if (i1 === '' || i1 == null || i2 === '' || i2 == null) {
                    __reprogSegErr(idx, errMeta
                        ? ('Intervalles sièges introuvables (' + errMeta + ').')
                        : 'Intervalles de sièges manquants pour ce programme.');
                }
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
        __reprogSetAncreVisible(false);
        if (__reprogQ('smspunifie')) __reprogQ('smspunifie').style.display = 'none';
        if (!dateYmd) return;

        var st = window.__reprogState;
        // Tous tickets : date → itinéraires (axe du ticket / 2 jambes) → segments.
        if (!st.axe) {
            var boxA = __reprogQ('smspunifie');
            var errA = __reprogQ('erreurSmspunifie');
            if (boxA) boxA.style.display = 'block';
            if (errA) errA.textContent = 'Axe ticket incomplet (gare départ / arrivée).';
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
                    err.textContent = 'Aucun départ ni correspondance pour relier l’axe '
                        + st.axe + ' à cette date (gare départ ticket : '
                        + (st.gaexp || '—') + ').';
                }
                return;
            }
            __reprogShowCorrExclusive(
                all,
                'Itinéraires possibles pour ' + st.axe + ' le ' + dateYmd
                + ' — choisissez-en un (direct = 1 segment, correspondance = plusieurs).'
            );
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

    function __reprogLookupRefused(donnees, fallbackMsg) {
        if (!donnees || typeof donnees !== 'object') {
            if (__reprogQ('smspunifie')) __reprogQ('smspunifie').style.display = 'block';
            if (__reprogQ('erreurSmspunifie')) {
                __reprogQ('erreurSmspunifie').textContent =
                    fallbackMsg || 'Cet ticket ne peut pas être reprogrammé ici.';
            }
            return true;
        }
        if (donnees.ok === false || donnees.error === 'gare_refuse') {
            if (__reprogQ('smspunifie')) __reprogQ('smspunifie').style.display = 'block';
            if (__reprogQ('erreurSmspunifie')) {
                __reprogQ('erreurSmspunifie').textContent = donnees.reason
                    || 'Reprogrammation refusée pour ce ticket.';
            }
            return true;
        }
        if (donnees.error === 'retour_non_confirme') {
            if (__reprogQ('smspunifie')) __reprogQ('smspunifie').style.display = 'block';
            if (__reprogQ('erreurSmspunifie')) {
                __reprogQ('erreurSmspunifie').textContent = donnees.reason
                    || 'Ce retour n’est pas encore confirmé. Confirmez-le avant de le reprogrammer.';
            }
            return true;
        }
        if (!donnees.code_passager && !donnees.code_ticket) {
            if (__reprogQ('smspunifie')) __reprogQ('smspunifie').style.display = 'block';
            if (__reprogQ('erreurSmspunifie')) {
                __reprogQ('erreurSmspunifie').textContent =
                    fallbackMsg || 'Cet ticket ne peut pas être reprogrammé ici.';
            }
            return true;
        }
        return false;
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

        var infos = __reprogQ('reprogrammer_infos_unifie');
        if (infos) {
            infos.onclick = function () {
                var cocl = String((__reprogQ('code_lookup_unifie') || {}).value || '').trim();
                var mode = __reprogLookupMode();

                __reprogResetUi();
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
                        if (__reprogLookupRefused(donnees, 'Cet ticket ne peut pas être reprogrammé ici.')) {
                            return;
                        }

                        var infosWrap = __reprogQ('reprog_infos_wrap');
                        if (infosWrap) infosWrap.style.display = 'grid';
                        __reprogQ('nomclpunifie').textContent = 'NOM: ' + (donnees.nom_client || '');
                        __reprogQ('prenomclpunifie').textContent = 'PRENOM: ' + (donnees.prenom_client || '');
                        __reprogQ('contactclpunifie').textContent = 'CONTACT: ' + (donnees.contact_client || '');
                        __reprogQ('refclpunifie').textContent = 'CNIB: ' + (donnees.num_CNIB || '');
                        var destEsc = donnees.dest_affiche || donnees.nom_dest_vente || '';
                        var isEsc = parseInt(donnees.est_escale_vente, 10) === 1
                            || (donnees.id_escale_vente && String(donnees.id_escale_vente) !== '0')
                            || !!donnees.nom_dest_vente;
                        __reprogQ('directionclpunifie').textContent = isEsc
                            ? ('AXE: ' + (donnees.gaexp_lg || '') + ' → ' + destEsc
                                + ' (escale — ligne ' + (donnees.nom_ligne || (donnees.gaexp_lg + '-' + donnees.gadest_lg)) + ')')
                            : ('AXE: ' + (donnees.gaexp_lg || '') + ' → ' + (donnees.gadest_lg || '')
                                + (donnees.nom_ligne ? (' — ' + donnees.nom_ligne) : ''));
                        if (__reprogQ('escaleclpunifie')) {
                            __reprogQ('escaleclpunifie').textContent = isEsc
                                ? ('DESTINATION TICKET: ' + destEsc + ' (conservée — pas le terminus)')
                                : '';
                            __reprogQ('escaleclpunifie').style.display = isEsc ? '' : 'none';
                        }
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
                            'PRIX: ' + (donnees.prixvente != null ? donnees.prixvente : '')
                            + (parseInt(donnees.est_retour, 10) === 1
                                ? (donnees.prix_source === 'prixretour' ? ' (retour)' : ' — RETOUR CONFIRMÉ')
                                : '');

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
                        if (__reprogQ('id_escale_vente_reprog')) {
                            __reprogQ('id_escale_vente_reprog').value = isEsc && donnees.id_escale_vente
                                ? String(donnees.id_escale_vente) : '';
                        }
                        if (__reprogQ('code_gadest_vente_reprog')) {
                            __reprogQ('code_gadest_vente_reprog').value = isEsc
                                ? (donnees.code_gadest_vente || '') : '';
                        }
                        if (__reprogQ('nom_dest_vente_reprog')) {
                            __reprogQ('nom_dest_vente_reprog').value = isEsc
                                ? (donnees.nom_dest_vente || destEsc || '') : '';
                        }

                        window.__reprogState.gaexp = donnees.gaexp_lg || '';
                        window.__reprogState.gadest = donnees.gadest_lg || '';
                        window.__reprogState.axe = (donnees.gaexp_lg || '') + '-' + (donnees.gadest_lg || '');
                        window.__reprogState.exclude = donnees.code_progr || '';
                        window.__reprogState.prix = donnees.prixvente != null ? String(donnees.prixvente) : '';
                        window.__reprogState.prixLegs = { 1: window.__reprogState.prix };
                        window.__reprogState.id_escale = isEsc && donnees.id_escale_vente
                            ? String(donnees.id_escale_vente) : '';
                        window.__reprogState.tarif = (donnees.typetarif != null && String(donnees.typetarif).trim() !== '')
                            ? String(donnees.typetarif).trim()
                            : '1';
                        window.__reprogState.tamponcodtr = donnees.tamponcodtr || '';
                        window.__reprogState.lookup1Done = true;
                        window.__reprogState.legsVerified = { 1: true };
                        window.__reprogState.isRetourConfirme = parseInt(donnees.est_retour, 10) === 1;
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

                        var nbrJ = parseInt(donnees.nbr_jambes, 10) || 1;
                        var estTr = parseInt(donnees.est_transit, 10) === 1 || nbrJ >= 2;
                        if (nbrJ > 4) nbrJ = 4;
                        window.__reprogState.isTransitTicket = estTr;
                        window.__reprogState.nbrJambes = estTr ? nbrJ : 1;
                        window.__reprogState.jambesExpected = Array.isArray(donnees.jambes) ? donnees.jambes : [];
                        var hidTr = __reprogQ('reprog_is_transit_ticket');
                        if (hidTr) hidTr.value = estTr ? '1' : '0';
                        var nOrig = __reprogQ('reprog_nbr_jambes_origine');
                        if (nOrig) nOrig.value = String(window.__reprogState.nbrJambes);
                        window.__reprogState.lookup2Done = !estTr;

                        var kindLabel = window.__reprogState.isRetourConfirme
                            ? 'Retour confirmé'
                            : 'Ticket';
                        var det = __reprogQ('reprog_transit_detect_msg');
                        if (det) {
                            if (estTr) {
                                det.style.display = 'block';
                                det.className = 'small text-info mb-1';
                                det.textContent = kindLabel + ' transit détecté : '
                                    + window.__reprogState.nbrJambes
                                    + ' codes à vérifier — axe '
                                    + (window.__reprogState.axe || '') + '.';
                            } else if (window.__reprogState.isRetourConfirme) {
                                det.style.display = 'block';
                                det.className = 'small text-info mb-1';
                                det.textContent = 'Retour confirmé détecté — axe retour '
                                    + (window.__reprogState.axe || '')
                                    + (donnees.ligne_retour ? (' (' + donnees.ligne_retour + ')') : '')
                                    + '. Choisissez une date puis un itinéraire.';
                            } else {
                                det.style.display = 'block';
                                det.className = 'small text-muted mb-1';
                                det.textContent = 'Ticket direct détecté.';
                            }
                        }

                        if (estTr) {
                            __reprogApplyOdFromLegs();
                            __reprogBuildExtraCodeFields(
                                window.__reprogState.nbrJambes,
                                window.__reprogState.jambesExpected,
                                donnees.code_ticket || cocl
                            );
                            if (__reprogQ('smspunifie')) __reprogQ('smspunifie').style.display = 'block';
                            if (__reprogQ('erreurSmspunifie')) {
                                __reprogQ('erreurSmspunifie').textContent =
                                    '1er code OK. Vérifiez les '
                                    + (window.__reprogState.nbrJambes - 1)
                                    + ' autre(s) code(s) du transit.';
                            }
                            return;
                        }

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
            if (window.__reprogState.isTransitTicket && !__reprogAllLegsVerified()) {
                ev.preventDefault();
                alert('Ticket transit : vérifiez tous les codes avant de reporter.');
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
/* --- addconfirmadmintran.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addconfirmadmintran').forEach(function (e) {
        
        document.querySelector('h3#admincTitletran').innerHTML = `CONFIRMATION`;

        let c = document.querySelector('#adminconfirme_infotran');
        if (c !== null)
        c.onclick = () => {
            
            //verification code de confirmation
            let Request;
            
            if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                Request = new XMLHttpRequest();
            } else if (window.ActiveXObject) { // IE 6 and older
                Request = new ActiveXObject("Microsoft.XMLHTTP");
            }
            
            var codes = document.querySelector("#admincodeconfirmtran").value;
            document.querySelector('#axeconfirmtran').options.length = 1;
            document.querySelector('#depargarestran').options.length = 1;
            Request.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifcodeconftran/${codes}`, true);
            Request.onload = () => {
                const dons = JSON.parse(Request.responseText);
                    if (dons == null) {
                        document.querySelector('#adminmessageptran').style.display = 'block';
                        document.querySelector('#adminerreurMessageptran').innerHTML = `Cet ticket ne peut pas être confirmé ici.`;
                        document.querySelector('#adminheuredtran').style.display = 'none';
                        document.querySelector('#admindepsiegtran').style.display = 'none';
                        document.querySelector('#adminquartconftran').style.display = 'none';
                        document.querySelector('#adminnomptran').innerText = ``;
                        document.querySelector('#adminprenomptran').innerText = ``;
                        document.querySelector('#admincontactptran').innerHTML = ``;
                        document.querySelector('#adminrefptran').innerHTML = ``;
                        document.querySelector('#admindirectionptran').innerHTML = ``;
                        document.querySelector('#admincodecptran').innerHTML = ``;
                        document.querySelector('#axeconfirmtran').style.display = 'none';
                        document.querySelector('#ligneconflgtran').value = '';
                    }
                    else 
                    {
                        
                        if (Object.entries(dons).length >= 1){
                            document.querySelector('#adminerreurMessageptran').innerHTML = '';
                            document.querySelector('#adminheuredtran').style.display = 'block';
                            document.querySelector('#admindepsiegtran').style.display = 'block';
                            document.querySelector('#adminquartconftran').style.display = 'block';
                            document.querySelector('#axeconfirmtran').style.display = 'block';
                            document.querySelector('#adminnomptran').innerText = `NOM: ${dons.nom_client}`;
                            document.querySelector('#adminprenomptran').innerText = `PRENOM: ${dons.prenom_client}`;
                            document.querySelector('#admincontactptran').innerHTML = `CONTACT: ${dons.contact_client}`;
                            document.querySelector('#adminrefptran').innerHTML = `REFERENCE CNIB: ${dons.num_CNIB}`;
                            document.querySelector('#admindirectionptran').innerHTML = `AXE: ${dons.nom_ligne}`;
                            document.querySelector('#admincodecptran').innerHTML = `CODE VENTE: ${dons.code_non_pass}`;
                            document.querySelector('#adminpasseptran').value = `${dons.code_non_pass}`;
                            document.querySelector('#adminpascodeticktran').value = `${dons.codeticket}`;
                            document.querySelector('#adminclientidptran').value = `${dons.id_client_npass}`;
                            document.querySelector('#adminpasnomptran').value = `${dons.nom_client}`;
                            document.querySelector('#adminpasprenomptran').value = `${dons.prenom_client}`;
                            document.querySelector('#adminpascontactptran').value = `${dons.contact_client}`;
                            document.querySelector('#adminpascnibptran').value = `${dons.num_CNIB}`;
                            document.querySelector('#adminpasdateptran').value = `${dons.date_delivre}`;
                            document.querySelector('#adcommentclienttran').value = `${dons.comment_client}`;
                            document.querySelector('#adminlieutran').value = `${dons.lieu_delivre}`;
                            document.querySelector('#admimtypetran').value = `${dons.type_client}`;
                            document.querySelector('#dateventeconftran').value = `${dons.datevente}`;
                            document.querySelector('#axeligneconftran').value = `${dons.id_ligne_pass}`;
                            document.querySelector('#ligneconflgtran').value = `${dons.nom_ligne}`;
                            document.querySelector('#admincodecpastran').value = `${dons.code_non_pass}`;
                            document.querySelector('#adlignehconftran').value = `${dons.id_ligneheure}`;
                            document.querySelector('#admincodeconfitran').value = `${dons.tamponcod}`;


                        } 
                        else 
                        {
                            document.querySelector('#adminheuredtran').style.display = 'none';
                            document.querySelector('#admindepsiegtran').style.display = 'none';
                            document.querySelector('#adminquartconftran').style.display = 'none';
                            document.querySelector('#axeconfirmtran').style.display = 'none';
                        }
                        
                            let Requestslg = new XMLHttpRequest();
                            const confirheurelg = document.querySelector('#ligneconflgtran').value;
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
                                        document.querySelector('#axeconfirmtran').add(opt);   
                                    }
                                }else{
                                    document.querySelector('#axeconfirmtran').options.length = 1;
                                }
                            };
                            Requestslg.setRequestHeader('Content-Type', 'application/json');
                            Requestslg.send();
                       
                            
                                            
                            let axeselectconf = document.querySelector('#axeconfirmtran');
                            if (axeselectconf !== null)
                                axeselectconf.onchange = () => 
                                {
                               
                                    var datdepart = document.querySelector('#dateventeconftran').value;
                                    var datdepartactu = document.querySelector('#datactutran').value;
                                    var date1  = new Date(datdepart);
                                    var date2 = new Date(datdepartactu);
                                    // différence des heures
                                    var time_diff = date2.getTime() - date1.getTime();
                                        // différence de jours
                                    const days_Diff = time_diff / (1000 * 3600 * 24);
                                    if(days_Diff < 28 || days_Diff < 29 || days_Diff < 30 || days_Diff < 31)    
                                    {
                                        const heureaxeconf = document.querySelector('#axeconfirmtran').options[document.querySelector('#axeconfirmtran').options.selectedIndex].value;
                            
                                        let Requests = new XMLHttpRequest();
                                        let Requests1 = new XMLHttpRequest();
                                        const confirheure = document.querySelector('#axeconfirmtran').
                                        options[document.querySelector('#axeconfirmtran').options.selectedIndex].value;
                                        
                                        var postmobt = confirheure.split('-');
                                        var confirh = postmobt[0];
                                        var apmobt = postmobt[1];
                                        var dateactuel = document.querySelector('#datactutran').value;
                                    
                                        Requests.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfprog/${confirheure}/${dateactuel}`, true);
                                        Requests.onload = () => {
                                            const datas2 = JSON.parse(Requests.responseText);
                                            if (Object.entries(datas2).length >= 1) {
                                                for (let key in Object.entries(datas2)) {
                                                    let opt = document.createElement('option');
                                                    opt.value = `${datas2[key].code_progr}/${datas2[key].typetarif}`;
                                                    opt.innerHTML = `${datas2[key].heure}/${datas2[key].date_progr}`;
                                                    document.querySelector('#adminheuredtran').add(opt);  
                                                }
                                            }else{
                                                document.querySelector('#adminheuredtran').options.length = 1;
                                            }
                                        };
                                        Requests.setRequestHeader('Content-Type', 'application/json');
                                        Requests.send();
                                        
                                        Requests1.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifsoug/${confirh}`, true);
                                        Requests1.onload = () => {

                                        const datasg2 = JSON.parse(Requests1.responseText);
                                            if (Object.entries(datasg2).length >= 1) {
        
                                                for (let key in Object.entries(datasg2)) {
                                                    let opt1 = document.createElement('option');
                                                    opt1.value = `${datasg2[key].code_gaexp}/${datasg2[key].idsousgare}`;
                                                    opt1.innerHTML = `${datasg2[key].nom_gaep}/${datasg2[key].nomsousgare}`;
                                                    document.querySelector('#depargarestran').add(opt1); 
                                                }
                                            }else{
                                                
                                                document.querySelector('#depargarestran').options.length = 1;
                                            }
                                        };
                                        Requests1.setRequestHeader('Content-Type', 'application/json');
                                        Requests1.send();
                                        
                                        let httpRequetesquart = new XMLHttpRequest();
                                            httpRequetesquart.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfquart/${heureaxeconf}`, true);
                                        httpRequetesquart.onload = () => {
                                            const dataq = JSON.parse(httpRequetesquart.responseText);
                                            if(dataq == ''){
                                                document.querySelector('#adminquartconftran').options.length = 1;
                                            }else{
                                                if (Object.entries(dataq).length >= 1) {
                                                            
                                                    for (let key in Object.entries(dataq)) {
                                                        let opt = document.createElement('option');
                                                        opt.value = `${dataq[key].nom_quartier}`;
                                                        opt.innerHTML = `${dataq[key].nom_quartier}`;
                                                        document.querySelector('#adminquartconftran').add(opt);
                                                    }
                                                } else {
                                                    document.querySelector('#adminquartconftran').options.length = 1;
                                                }
                                            }
                                                
                                                    
                                        };
                                        httpRequetesquart.setRequestHeader('Content-Type', 'application/json');
                                        httpRequetesquart.send();
                                    }
                                    else
                                    {
                                        document.querySelector('#adminheuredtran').style.display = 'none';
                                        document.querySelector('#admindepsiegtran').style.display = 'none';
                                        document.querySelector('#adminquartconftran').style.display = 'none';
                                        document.querySelector('#adminnomptran').innerText = ``;
                                        document.querySelector('#adminprenomptran').innerText = ``;
                                        document.querySelector('#admincontactptran').innerHTML = ``;
                                        document.querySelector('#adminrefptran').innerHTML = ``;
                                        document.querySelector('#admindirectionptran').innerHTML = ``;
                                        document.querySelector('#admincodecptran').innerHTML = ``;
                                        document.querySelector('#billettran').style.display = 'block';
                                        document.querySelector('#billetSmstran').innerHTML = `Billet non valable, la durée de validité est dépassée.`;
    
                                    }
                                };
                                        
                                            
                    }
               
            };
            Request.setRequestHeader('Content-Type', 'application/json');
            Request.send(); 
        };

        let heurdeprt = document.querySelector('#adminheuredtran');
        if (heurdeprt !== null)
            heurdeprt.onchange = () => {
                
                document.querySelector('#admindepsiegtran').options.length = 1;
                const Requeste = new XMLHttpRequest();
                const selectorp = document.querySelector('#adminheuredtran').options[document.querySelector('#adminheuredtran').
                options.selectedIndex].value;
                var selectorp1 = selectorp.split('/');
                var selectorp2 = selectorp1[0];
                var selectorp3 = selectorp1[1];
                Requeste.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/siegdispo/${selectorp2}`, true);
                Requeste.onload = () => {
                    const datasgc = JSON.parse(Requeste.responseText);
                    if (Object.entries(datasgc).length >= 1) {
                        for (let key in Object.entries(datasgc)) {
                            
                            document.querySelector('#adcaissepvend_tran').value = `${datasgc[key].intervalle1}`;
                            document.querySelector('#adcaissedpvend_tran').value = `${datasgc[key].intervalle2}`;
                            document.querySelector('#addirectidtran').value = `${datasgc[key].nom_ligne}`;
                            document.querySelector('#adconfheuretran').value = `${datasgc[key].heure}`;
                            document.querySelector('#addateconfirmetran').value = `${datasgc[key].date_progr}`;
                            document.querySelector('#adcategotran').value = `${datasgc[key].categori}`;
                            document.querySelector('#adlignehconftran').value = `${datasgc[key].id_ligneheure}`;
                            document.querySelector('#adprogramconftran').value = `${datasgc[key].code_progr}`;
                        }
                    } 
                    const Requestbis = new XMLHttpRequest();
                    const pldebut = document.querySelector('#adcaissepvend_tran').value;
                    const plfin = document.querySelector('#adcaissedpvend_tran').value;
                    const cfdir = document.querySelector('#addirectidtran').value;
                    const hconfir = document.querySelector('#adconfheuretran').value;
                    const dconfirme = document.querySelector('#addateconfirmetran').value;
                    Requestbis.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${selectorp2}/${dconfirme}/${cfdir}/${hconfir}/${pldebut}/${plfin}`, true);
                    Requestbis.onload = () => {
                        const datasgcbis = JSON.parse(Requestbis.responseText);
                        if (Object.entries(datasgcbis).length >= 1) {
                            for (let key in Object.entries(datasgcbis)) {
                                let opt = document.createElement('option');
                                opt.value = `${datasgcbis[key].siege_num}`;
                                opt.innerHTML = `${datasgcbis[key].siege_num}`;
                                document.querySelector('#admindepsiegtran').add(opt);
                            }
                        } else {
                            document.querySelector('#admindepsiegtran').options.length = 1;
                        }
                    };
                    Requestbis.setRequestHeader('Content-Type', 'application/json');
                    Requestbis.send();
                };
                Requeste.setRequestHeader('Content-Type', 'application/json');
                Requeste.send();
            };

            let depsiegconf = document.querySelector('#admindepsiegtran');
            if (depsiegconf !== null)
            depsiegconf.onchange = () => {
                    
                    let Requestsiegevenduconf;
                    
                    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                        Requestsiegevenduconf = new XMLHttpRequest();
                    } else if (window.ActiveXObject) { // IE 6 and older
                        Requestsiegevenduconf = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                    const dp_progconf = document.querySelector('#adprogramconftran').value;
                    const dp_siegeconf = document.querySelector('#admindepsiegtran').options[document.querySelector('#admindepsiegtran').options.selectedIndex].value;
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
                                            document.querySelector('#adminmessconftran').style.display = 'none';
                                            if (Object.entries(dongconf).length >= 1)
                                        {
                                            for (let key in Object.entries(dongconf)) {
                                                document.querySelector('#adminidtampoconftran').value = `${dongconf[key].idtamp}`;                    
                                                document.querySelector('#adminsiegselectconftran').value = `${dongconf[key].numsieg}`;
                                            }

                                        }
                                        
                                        };
                                        httpSiegsconf.setRequestHeader('Content-Type', 'application/json');
                                        httpSiegsconf.send();
                                    }
                                    else {
                                        document.querySelector('#admindepsiegtran').value = '';     
                                        if (Object.entries(confdonsieg).length >= 1)
                                        {
                                            for (let key in Object.entries(confdonsieg)) {
                                                document.querySelector('#adminidtampoconftran').value = `${confdonsieg[key].idtamp}`;                    
                                                document.querySelector('#adminsiegselectconftran').value = `${confdonsieg[key].numsieg}`;
                                            }

                                        }
                                        document.querySelector('#adminmessconftran').style.display = 'block';
                                        document.querySelector('#adminerreurMessconftran').innerHTML = `Siege déjà utilisé.`; 
                                    }
                    };
                    Requestsiegevenduconf.setRequestHeader('content-Type', 'text/json');
                    Requestsiegevenduconf.send();
                };
            //bouton annuler
                butoncliconf = document.querySelector('#adminconfresettran');
                if (butoncliconf !== null) {
                    butoncliconf.onclick = () => 
                    {
                        let httpSiegeselectconf;
                        httpSiegeselectconf = new XMLHttpRequest();
                        const siegselectconf = document.querySelector('#adminsiegselectconftran').value;
                        const idtapconf = document.querySelector('#adminidtampoconftran').value;
                        httpSiegeselectconf.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtapconf}/${siegselectconf}`, true);
                        httpSiegeselectconf.onload = () => 
                        {
                            const donselectconf = JSON.parse(httpSiegeselectconf.responseText);
                            console.debug(`${typeof donselectconf} - ${donselectconf.attributes}`, console.memory);
                            document.querySelector('#adminmessconftran').style.display = 'none';
                            
                        };
                        httpSiegeselectconf.setRequestHeader('Content-Type', 'application/json');
                        httpSiegeselectconf.send();
    
                    
                    };
                }       
                       
        e.onclick = function () {
            let adcForm = document.querySelector('#admincFormtran');
            adcForm.setAttribute('action', `${APP_ROOT}/Confirmation/adminconfirmetran/${e.dataset.ckey}`);
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
