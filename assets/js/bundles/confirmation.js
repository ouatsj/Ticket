/* Bundle confirmation — genere par scripts/build_module_bundles.php */
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

    /** Libellé heure : ajoute JJ/MM si date_progr ≠ date voyage (évite doublons J / J+1). */
    function __confHeureOptionLabel(heure, dateProgr, voyageDate) {
        var label = String(heure || '');
        var dprog = dateProgr ? String(dateProgr).slice(0, 10) : '';
        var vDate = voyageDate ? String(voyageDate).slice(0, 10) : '';
        if (dprog && (!vDate || dprog !== vDate)) {
            var short = __confFormatDateShort(dprog);
            if (short) label = label + ' — ' + short;
        }
        return label;
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
        for (var j = 0; j < order.length; j++) {
            var r = bySlot[order[j]];
            var opt = document.createElement('option');
            opt.value = `${r.code_progr}/${r.intervalle1}/${r.intervalle2}/${r.id_ligneheure}/${r.prix}`;
            opt.setAttribute('data-heure', r.heure || '');
            opt.setAttribute('data-date-progr', r.date_progr ? String(r.date_progr).slice(0, 10) : '');
            var voyageDateAf = (document.querySelector('#actuel') && document.querySelector('#actuel').value)
                ? String(document.querySelector('#actuel').value).slice(0, 10)
                : pDate;
            opt.innerHTML = __confHeureOptionLabel(r.heure, r.date_progr, voyageDateAf);
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
                opt.innerHTML = __confHeureOptionLabel(r.heure, r.date_progr, datedepart);
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
/* --- adventemobile.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adventemobile').forEach(function (e) 
    {
            let dpgar= document.querySelector('#depargaremob');
            if (dpgar !== null)
            dpgar.onchange = () => {

                document.querySelector('#tarifattribmob').value = '';
                document.querySelector('#date_depheuremob').value = '';
                document.querySelector('#arrsgaremob').value = '';
                document.querySelector('#hdepartmob').options.length = 1;
                document.querySelector('#quartiermob').options.length = 1;
                document.querySelector('#psiegesmob').options.length = 1;
                document.querySelector('#prix_axemob').value = '';
                document.querySelector('#programmob').value = '';
                document.querySelector('#nomitinmob').value = '';
                document.querySelector('#typesmob').value = '';
                
                  
            };
            let armob= document.querySelector('#arrsgaremob');
            if (armob !== null)
            armob.onchange = () => {

                document.querySelector('#prix_axemob').value = '';
                document.querySelector('#date_depheuremob').value = '';
                document.querySelector('#hdepartmob').options.length = 1;
                document.querySelector('#quartiermob').options.length = 1;
                document.querySelector('#psiegesmob').options.length = 1;
                document.querySelector('#programmob').value = '';
                document.querySelector('#tarifattribmob').value = '';
                document.querySelector('#nomitinmob').value = '';
                document.querySelector('#typesmob').value = '';
                  
                    //const typgaremob = document.querySelector('#arrsgaremob')
                    //.options[document.querySelector('#arrsgaremob').options.selectedIndex].value;
                    const prosmob = document.querySelector('#programmob').value;
                    
                    var typgaremobi = document.querySelector('#arrsgaremob')
                    .options[document.querySelector('#arrsgaremob').options.selectedIndex].value;
                    var artypgarepa2 = typgaremobi.split('/');
                    const typgaremob = artypgarepa2[0];
                    var typgaremob2 = artypgarepa2[1];
                    //const prosmob = document.querySelector('#programmob').value;
                    let httptypequartmob;
                    httptypequartmob = new XMLHttpRequest();
                    
                    httptypequartmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${typgaremob}`, true);
                    httptypequartmob.onload = () => 
                    {
                        const donquamob = JSON.parse(httptypequartmob.responseText);
                        if (donquamob == '') {
                            document.querySelector('#quartiermob').options.length = 1;
                        }
                        else{
                            if (Object.entries(donquamob).length >= 1) {
                                            
                                for (let key in Object.entries(donquamob)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${donquamob[key].nom_quartier}`;
                                    opt.innerHTML = `${donquamob[key].nom_quartier}`;
                                    document.querySelector('#quartiermob').add(opt);
                                }
                            } else {
                                document.querySelector('#quartiermob').options.length = 1;
                            }
                        }
                        

                    };
                    httptypequartmob.setRequestHeader('Content-Type', 'application/json');
                    httptypequartmob.send();
            };
            
            let damob = document.querySelector('#date_depheuremob');
            if (damob !== null){
                damob.onchange = () => 
                {
                    
                    document.querySelector('#hdepartmob').options.length = 1;
                    document.querySelector('#psiegesmob').options.length = 1;
                    
                    let httpRequetesmob;
                    
                    if (window.XMLHttpRequest) {
                        httpRequetesmob = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpRequetesmob = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                        var depamob = document.querySelector('#depargaremob').value;
                        
                        //var arrmob = document.querySelector('#arrsgaremob').value;
                        var typgaremob = document.querySelector('#arrsgaremob')
                    .options[document.querySelector('#arrsgaremob').options.selectedIndex].value;
                        var artypgarepa2 = typgaremob.split('/');
                        var arrmob = artypgarepa2[0];
                        var typgaremob2 = artypgarepa2[1];
                        var datedepartmob = document.querySelector('#date_depheuremob').value;
                        var dateactumob = document.querySelector('#actumob').value;
                                         
                        var post_lhdepmob = depamob.split('/');
                        var seltdepmob = post_lhdepmob[0];
                        var sougidmob = post_lhdepmob[1];
                        if(datedepartmob >= dateactumob)
                        {
                            
                            httpRequetesmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheure1/${seltdepmob}-${arrmob}/${datedepartmob}`, true);
                            httpRequetesmob.onload = () => {
                                const dataAxemob = JSON.parse(httpRequetesmob.responseText);
                                
                                    if (dataAxemob == '') {
                                        
                                        document.querySelector('#smsdtmob').style.display = 'none';
                                        document.querySelector('#date_depheuremob').style.color = "black";
                                        document.querySelector('#date_depheuremob').style.border = "1px solid";
                                        
                                    } 
                                    else 
                                    {       
                                        
                                        document.querySelector('#smsdtmob').style.display = 'none';
                                        document.querySelector('#date_depheuremob').style.color = "black";
                                        document.querySelector('#date_depheuremob').style.border = "1px solid";
                                        if (Object.entries(dataAxemob).length >= 1) 
                                        {
                                                
                                            
    
                                            for (let key in Object.entries(dataAxemob)) {
                                                    let opt = document.createElement('option');
                                                    opt.value = `${dataAxemob[key].id_ligneheure}/${dataAxemob[key].heure}`;
                                                    opt.innerHTML = `${dataAxemob[key].heure}`;
                                                    document.querySelector('#hdepartmob').add(opt);
                                                }
                                        } else {
                                            document.querySelector('#hdepartmob').options.length = 1;
                                        }
                                    }

                                        let hrdepartmob = document.querySelector('#hdepartmob');
                                        if (hrdepartmob !== null) {
                                            hrdepartmob.onchange = () => 
                                            {
                                                document.querySelector('#psiegesmob').options.length = 1;
                                                const httpRequestmob = new XMLHttpRequest();
                                                const selemob = document.querySelector('#hdepartmob')
                                                    .options[document.querySelector('#hdepartmob').options.selectedIndex].value;

                                                    var post_lhmob = selemob.split('/');
                                                    var selmob = post_lhmob[0];
                                                    var lhselmob = post_lhmob[1];

                                                    const dpt_datemob = document.querySelector('#date_depheuremob').value;
                                                    
                                                httpRequestmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifprog/${seltdepmob}-${arrmob}/${dpt_datemob}/${selmob}`, true);
                                                httpRequestmob.onload = () => 
                                                {
                                                    const donmob = JSON.parse(httpRequestmob.responseText);
                                                        console.debug(`${typeof donmob} - ${donmob.attributes}`, console.memory);
                                                        if (donmob == '') 
                                                        {
                            
                                                                    /*let opt = document.createElement('option');
                                                                    opt.value = 1;
                                                                    opt.innerHTML = 1;
                                                                    document.querySelector('#psiegesmob').add(opt);
                                                            
                                                                    departpsiegesmob = document.querySelector('#psiegesmob');
                                                                    if (departpsiegesmob !== null) {
                                                                        departpsiegesmob.onchange = () => 
                                                                        {
                                                                            let httpProgmob;
                                                                            httpProgmob = new XMLHttpRequest();
                                                                            httpProgmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/creedepartmob/${seltdepmob}/${dpt_datemob}/${selmob}/${lhselmob}`, true);
                                                                            httpProgmob.onload = () => 
                                                                            {
                                                                                const donsmob = JSON.parse(httpProgmob.responseText);
                                                                                console.debug(`${typeof donsmob} - ${donsmob.attributes}`, console.memory);
                                                                                if (Object.entries(donsmob).length >= 1) {
                                                                                    for (let key in Object.entries(donsmob)) {
                                                                                        document.querySelector('#programmob').value = `${donsmob[key].code_progr}`;
                                                                                        document.querySelector('#tarifattribmob').value = `${donsmob[key].typetarif}`;
                                                                                        document.querySelector('#catemob').value = `${donsmob[key].categorie}`;
                                                                                        document.querySelector('#deplignemob').value = `${donsmob[key].gareidentif}`;
                                                                                        document.querySelector('#lignmob').value = `${donsmob[key].ident_ligne}`;
                                                                                        document.querySelector('#nomitinmob').value = `${donsmob[key].nom_ligne}`;
                                                                                        document.querySelector('#prix_axemob').value = `${donsmob[key].prix}`;
                                                                                    }
                                                                                        let httpSiegemob;
                                                                                        httpSiegemob = new XMLHttpRequest();
                                                                                        const sigmob = document.querySelector('#psiegesmob')
                                                                                        .options[document.querySelector('#psiegesmob').options.selectedIndex].value;
                                                                                        const promob = document.querySelector('#programmob').value;
                                                                                        httpSiegemob.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${promob}/${sigmob}`, true);
                                                                                        httpSiegemob.onload = () => 
                                                                                        {
                                                                                            const donsgmob = JSON.parse(httpSiegemob.responseText);
                                                                                            console.debug(`${typeof donsgmob} - ${donsgmob.attributes}`, console.memory);
                                                                                            if(donsgmob == '')
                                                                                            {
                                                                                                let httpSiegmob;
                                                                                                httpSiegmob = new XMLHttpRequest();
                    
                                                                                                httpSiegmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${promob}/${sigmob}`, true);
                                                                                                httpSiegmob.onload = () => 
                                                                                                {
                                                                                                    const donsg2mob = JSON.parse(httpSiegmob.responseText);
                                                                                                    document.querySelector('#messmob').style.display = 'none';
                                                                                                    if (Object.entries(donsg2mob).length >= 1)
                                                                                                        {
                                                                                                            for (let key in Object.entries(donsg2mob)) {
                                                                                                                document.querySelector('#idtampomob').value = `${donsg2mob[key].idtamp}`;                    
                                                                                                                document.querySelector('#siegselectmob').value = `${donsg2mob[key].numsieg}`;
                                                                                                            }
                                                                                                        }
                                                                                                };
                                                                                                httpSiegmob.setRequestHeader('Content-Type', 'application/json');
                                                                                                httpSiegmob.send();
                                                                                            }
                                                                                            else 
                                                                                            {
                                                                                                document.querySelector('#psiegesmob').value = ''; 
                                                                                                if (Object.entries(donsgmob).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(donsgmob)) 
                                                                                                    {
                                                                                                        document.querySelector('#idtampomob').value = `${donsgmob[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselectmob').value = `${donsgmob[key].numsieg}`;
                                                                                                    }
        
                                                                                                }
                                                                                                document.querySelector('#messmob').style.display = 'block';
                                                                                                document.querySelector('#erreurMessmob').innerHTML = `Siege déjà utilisé.`;                   
                                                                                            }
                                                                                        };
                                                                                        httpSiegemob.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegemob.send();
                    
                                                                                   
                                                                                }
                                                                            };
                                                                            httpProgmob.setRequestHeader('Content-Type', 'application/json');
                                                                            httpProgmob.send();
        
                                                                            
                                                                        
                                                                        };
        
                                                                        
                                                                    }*/
                                                            
                                                        } 
                                                        else 
                                                        {       
                                                            if (Object.entries(donmob).length >= 1) {
                                                                for (let key in Object.entries(donmob)) {
                                                                    document.querySelector('#programmob').value = `${donmob[key].code_progr}`;
                                                                    document.querySelector('#tarifattribmob').value = `${donmob[key].typetarif}`;
                                                                    document.querySelector('#dateprmob').value = `${donmob[key].date_progr}`;
                                                                    document.querySelector('#deplignemob').value = `${donmob[key].gareidentif}`;
                                                                    document.querySelector('#inter1mob').value = `${donmob[key].intervalle1}`;
                                                                    document.querySelector('#inter2mob').value = `${donmob[key].intervalle2}`;
                                                                    document.querySelector('#lignmob').value = `${donmob[key].ident_ligne}`;
                                                                    document.querySelector('#hermob').value = `${donmob[key].heure}`;
                                                                    document.querySelector('#catemob').value = `${donmob[key].categori}`;
                                                                    document.querySelector('#nomitinmob').value = `${donmob[key].nom_ligne}`;

                                                                }
                                                            } 
                                                            
                                                            var tfbsmob = document.querySelector('#tarifattribmob').value;
                                                            const httpPrixmob = new XMLHttpRequest();
                                                            httpPrixmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifpriprg/${selmob}/${tfbsmob}`, true);
                                                            httpPrixmob.onload = () => 
                                                            {

                                                                const donprixmob = JSON.parse(httpPrixmob.responseText);
                                                                console.debug(`${typeof donprixmob}-${donprixmob.attributes}`, console.memory);
                                                                if (Object.entries(donprixmob).length >= 1) {
                                                                    for (let key in Object.entries(donprixmob)) 
                                                                    {
                                                                        document.querySelector('#prix_axemob').value = `${donprixmob[key].prix}`;
            
                                                                    }
                                                                }
                                                            };
                                                            httpPrixmob.setRequestHeader('Content-Type', 'application/json');
                                                            httpPrixmob.send();
                                                            
                                                            const httpRequettemob = new XMLHttpRequest();
                                                            const cdprogmob = document.querySelector('#programmob').value;
                                                            const dbmob = document.querySelector('#inter1mob').value;
                                                            const fnmob = document.querySelector('#inter2mob').value;
                                                            
                                                            var lgmob = document.querySelector('#nomitinmob').value;
                                                            const timmob = document.querySelector('#hermob').value;
                                                                httpRequettemob.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${cdprogmob}/${dpt_datemob}/${lgmob}/${timmob}/${dbmob}/${fnmob}`, true);
                                                            httpRequettemob.onload = () => {
                                                                const dattamob = JSON.parse(httpRequettemob.responseText);
                                                                if (Object.entries(dattamob).length >= 1) {
                                                                    for (let key in Object.entries(dattamob)) {
                                                                        
                                                                        let opt = document.createElement('option');
                                                                        opt.value = `${dattamob[key].siege_num}`;
                                                                        opt.innerHTML = `${dattamob[key].siege_num}`;
                                                                        document.querySelector('#psiegesmob').add(opt);
                                                                        
                                                                    }
                                                                    
                                                                } else {
                                                                    document.querySelector('#psiegesmob').options.length = 1;
                                                                }
                                                            };
                                                            httpRequettemob.setRequestHeader('Content-Type', 'application/json');
                                                            httpRequettemob.send();
                                                        }  
                                                        
                                                    };
                                                    httpRequestmob.setRequestHeader('Content-Type', 'application/json');
                                                    httpRequestmob.send();
                                                     
                                                };
                                                
                                        
                                            }
                                };
                                httpRequetesmob.setRequestHeader('Content-Type', 'application/json');
                                httpRequetesmob.send();
                        }
                        else
                        {
                            document.querySelector('#date_depheuremob').style.color = "#FF0000";
                            document.querySelector('#date_depheuremob').style.border = "2px solid #FF0000";
                            document.querySelector('#smsdtmob').style.display = 'block';
                            document.querySelector('#erreurSmsdtmob').innerHTML = `Date non valide.`;
                        }
                    

                };
                
            }
            let progsiegesmob = document.querySelector('#psiegesmob');
            if (progsiegesmob !== null) {
                progsiegesmob.onchange = () => 
                {
                    let httpSiegesmob;
                    httpSiegesmob = new XMLHttpRequest();
                    const sigsmob = document.querySelector('#psiegesmob')
                    .options[document.querySelector('#psiegesmob').options.selectedIndex].value;
                    const prosmob = document.querySelector('#programmob').value;

                    httpSiegesmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${prosmob}/${sigsmob}`, true);
                    httpSiegesmob.onload = () => 
                    {
                        const donsgemob = JSON.parse(httpSiegesmob.responseText);
                        console.debug(`${typeof donsgemob} - ${donsgemob.attributes}`, console.memory);
                        if(donsgemob == '')
                        {
                            let httpSiegsmob;
                            httpSiegsmob = new XMLHttpRequest();

                            httpSiegsmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${prosmob}/${sigsmob}`, true);
                            httpSiegsmob.onload = () => 
                            {
                                const dongmob = JSON.parse(httpSiegsmob.responseText);
                                document.querySelector('#messmob').style.display = 'none';
                                if (Object.entries(dongmob).length >= 1)
                                    {
                                        for (let key in Object.entries(dongmob)) {
                                            document.querySelector('#idtampomob').value = `${dongmob[key].idtamp}`;                    
                                            document.querySelector('#siegselectmob').value = `${dongmob[key].numsieg}`;
                                        }
                                    }
                            };
                            httpSiegsmob.setRequestHeader('Content-Type', 'application/json');
                            httpSiegsmob.send();
                        }
                        else {
                            document.querySelector('#psiegesmob').value = '';     
                            if (Object.entries(donsgemob).length >= 1)
                            {
                                for (let key in Object.entries(donsgemob)) {
                                    document.querySelector('#idtampomob').value = `${donsgemob[key].idtamp}`;                    
                                    document.querySelector('#siegselectmob').value = `${donsgemob[key].numsieg}`;
                                }

                            }
                            document.querySelector('#messmob').style.display = 'block';
                            document.querySelector('#erreurMessmob').innerHTML = `Siege déjà utilisé.`;                                                                   }
                    };
                    httpSiegesmob.setRequestHeader('Content-Type', 'application/json');
                    httpSiegesmob.send();

                
                };
            }
           
            
        //recherche d'information du client depart principal
        let infmob = document.querySelector('#rnclient_contactmob');
        if (infmob !== null)
            infmob.onkeyup = () => {
                let httpInfosmob;
                if (window.XMLHttpRequest) {
                    httpInfosmob = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfosmob = new ActiveXObject("Microsoft.XMLHTTP");
                }
                var verificatmob = document.querySelector('#rnclient_contactmob').value;
                
                httpInfosmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifinfos/${verificatmob}`, true);
                httpInfosmob.onload = () => {
                    const infosmob = JSON.parse(httpInfosmob.responseText);
                    if (infosmob == null) {
                        document.querySelector('#rclientmob').value = "";
                        document.querySelector('#prnclientmob').value = "";
                        document.querySelector('#pascompagniemob').value = "";
                        document.querySelector('#typesmob').value = "";
                        document.querySelector('#cnibclmob').value = "";
                        document.querySelector('#dateclmob').value = "";
                        document.querySelector('#lieuclmob').value = "";
                    } else {
                        if (Object.entries(infosmob).length > 1) {
                            
                            if (infosmob.contact_client == verificatmob) {
                                document.querySelector('#rclientmob').value = `${infosmob.nom_client}`;
                                document.querySelector('#prnclientmob').value = `${infosmob.prenom_client}`;
                                document.querySelector('#pascompagniemob').value = `${infosmob.id_client}`;
                                document.querySelector('#cnibclmob').value = `${infosmob.num_CNIB}`;
                                document.querySelector('#dateclmob').value = `${infosmob.date_delivre}`;
                                document.querySelector('#lieuclmob').value = `${infosmob.lieu_delivre}`;
                                document.querySelector('#rclientcpmob').value = `${infosmob.nom_client}`;
                                document.querySelector('#prnclientcpmob').value = `${infosmob.prenom_client}`;
                                document.querySelector('#typesmob').value = `${infosmob.type_client}`;
                                document.querySelector('#cnibclientcpmob').value = `${infosmob.num_CNIB}`;
                                document.querySelector('#cnibdateclientcpmob').value = `${infosmob.date_delivre}`;
                                document.querySelector('#cniblieudelivmob').value = `${infosmob.lieu_delivre}`;
                       
                            } else {
                                document.querySelector('#rclientmob').value = "";
                                document.querySelector('#prnclientmob').value = "";
                                document.querySelector('#pascompagniemob').value = "";
                                document.querySelector('#typesmob').value = "";
                                document.querySelector('#cnibclmob').value = "";
                                document.querySelector('#dateclmob').value = "";
                                document.querySelector('#lieuclmob').value = "";
                            }
                        }
                    }
                };
                httpInfosmob.setRequestHeader('Content-Type', 'application/json');
                httpInfosmob.send();
            };
            let butonclicmob = document.querySelector('#idresetmob');
            if (butonclicmob !== null) {
                butonclicmob.onclick = () => 
                {
                    let httpSiegeselectmob;
                    httpSiegeselectmob = new XMLHttpRequest();
                    const siegselectmob = document.querySelector('#siegselectmob').value;
                    const idtapmob = document.querySelector('#idtampomob').value;
                    httpSiegeselectmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtapmob}/${siegselectmob}`, true);
                    httpSiegeselectmob.onload = () => 
                    {
                        const donselectmob = JSON.parse(httpSiegeselectmob.responseText);
                        document.querySelector('#messmob').style.display = 'none';
                        
                    };
                    httpSiegeselectmob.setRequestHeader('Content-Type', 'application/json');
                    httpSiegeselectmob.send();

                
                };
            }
                
                e.onclick = function () {   
                    let mobForm = document.querySelector('#mobForm');
                    
                    mobForm.setAttribute('action', `${APP_ROOT}/Programmes/passagermobil/${e.dataset.cle_compagnie}`);   
                }
                
                var clique = true;

                $('#bottonmob').click(function(event) 
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
/* --- addventemobile.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addventemobile').forEach(function (e) 
    {
        document.querySelector('h3#mobTitle').innerHTML = `VENTE DE TICKET`;

            let dpgar= document.querySelector('#depargaremob');
            if (dpgar !== null)
            dpgar.onchange = () => {
                document.querySelector('#prix_axemob').value = '';
                document.querySelector('#date_depheuremob').value = '';
                document.querySelector('#arrsgaremob').value = '';
                document.querySelector('#hdepartmob').options.length = 1;
                document.querySelector('#quartiermob').options.length = 1;
                document.querySelector('#psiegesmob').options.length = 1;
                document.querySelector('#programmob').value = '';
                document.querySelector('#nomitinmob').value = '';
                document.querySelector('#typesmob').value = '';
                document.querySelector('#tarifattribmob').value = '';
                  
            };
            let armob= document.querySelector('#arrsgaremob');
            if (armob !== null)
            armob.onchange = () => {
                document.querySelector('#prix_axemob').value = '';
                document.querySelector('#date_depheuremob').value = '';
                document.querySelector('#hdepartmob').options.length = 1;
                document.querySelector('#quartiermob').options.length = 1;
                document.querySelector('#psiegesmob').options.length = 1;
                  document.querySelector('#programmob').value = '';
                  document.querySelector('#tarifattribmob').value = '';
                  document.querySelector('#nomitinmob').value = '';
                document.querySelector('#typesmob').value = '';
                  
                    const typgaremob = document.querySelector('#arrsgaremob')
                    .options[document.querySelector('#arrsgaremob').options.selectedIndex].value;
                    const prosmob = document.querySelector('#programmob').value;
                    let httptypequartmob;
                    httptypequartmob = new XMLHttpRequest();
                    
                    httptypequartmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${typgaremob}`, true);
                    httptypequartmob.onload = () => 
                    {
                        const donquamob = JSON.parse(httptypequartmob.responseText);
                        if (donquamob == '') {
                            document.querySelector('#quartiermob').options.length = 1;
                        }
                        else{
                            if (Object.entries(donquamob).length >= 1) {
                                            
                                for (let key in Object.entries(donquamob)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${donquamob[key].nom_quartier}`;
                                    opt.innerHTML = `${donquamob[key].nom_quartier}`;
                                    document.querySelector('#quartiermob').add(opt);
                                }
                            } else {
                                document.querySelector('#quartiermob').options.length = 1;
                            }
                        }
                        

                    };
                    httptypequartmob.setRequestHeader('Content-Type', 'application/json');
                    httptypequartmob.send();
            };
            
            let damob = document.querySelector('#date_depheuremob');
            if (damob !== null){
                damob.onchange = () => 
                {
                    
                    document.querySelector('#hdepartmob').options.length = 1;
                    document.querySelector('#psiegesmob').options.length = 1;
                    
                    let httpRequetesmob;
                    
                    if (window.XMLHttpRequest) {
                        httpRequetesmob = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpRequetesmob = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                        var depamob = document.querySelector('#depargaremob').value;
                        var arrmob = document.querySelector('#arrsgaremob').value;
                        var datedepartmob = document.querySelector('#date_depheuremob').value;
                        var dateactumob = document.querySelector('#actumob').value;
                                         
                        var post_lhdepmob = depamob.split('/');
                        var seltdepmob = post_lhdepmob[0];
                        var sougidmob = post_lhdepmob[1];
                        if(datedepartmob >= dateactumob)
                        {
                            
                            httpRequetesmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheure1/${seltdepmob}-${arrmob}/${datedepartmob}`, true);
                            httpRequetesmob.onload = () => {
                                const dataAxemob = JSON.parse(httpRequetesmob.responseText);
                                
                                    if (dataAxemob == '') {
                                        
                                        document.querySelector('#smsdtmob').style.display = 'none';
                                        document.querySelector('#date_depheuremob').style.color = "black";
                                        document.querySelector('#date_depheuremob').style.border = "1px solid";
                                        
                                    } 
                                    else 
                                    {       
                                        
                                        document.querySelector('#smsdtmob').style.display = 'none';
                                        document.querySelector('#date_depheuremob').style.color = "black";
                                        document.querySelector('#date_depheuremob').style.border = "1px solid";
                                        if (Object.entries(dataAxemob).length >= 1) 
                                        {
                                                
                                            
    
                                            for (let key in Object.entries(dataAxemob)) {
                                                    let opt = document.createElement('option');
                                                    opt.value = `${dataAxemob[key].id_ligneheure}/${dataAxemob[key].heure}`;
                                                    opt.innerHTML = `${dataAxemob[key].heure}`;
                                                    document.querySelector('#hdepartmob').add(opt);
                                                }
                                        } else {
                                            document.querySelector('#hdepartmob').options.length = 1;
                                        }
                                    }

                                        let hrdepartmob = document.querySelector('#hdepartmob');
                                        if (hrdepartmob !== null) {
                                            hrdepartmob.onchange = () => 
                                            {
                                                document.querySelector('#psiegesmob').options.length = 1;
                                                const httpRequestmob = new XMLHttpRequest();
                                                const selemob = document.querySelector('#hdepartmob')
                                                    .options[document.querySelector('#hdepartmob').options.selectedIndex].value;

                                                    var post_lhmob = selemob.split('/');
                                                    var selmob = post_lhmob[0];
                                                    var lhselmob = post_lhmob[1];

                                                    const dpt_datemob = document.querySelector('#date_depheuremob').value;
                                                    
                                                httpRequestmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifprog/${seltdepmob}-${arrmob}/${dpt_datemob}/${selmob}`, true);
                                                httpRequestmob.onload = () => 
                                                {
                                                    const donmob = JSON.parse(httpRequestmob.responseText);
                                                        console.debug(`${typeof donmob} - ${donmob.attributes}`, console.memory);
                                                        if (donmob == '') 
                                                        {
                            
                                                                    /*let opt = document.createElement('option');
                                                                    opt.value = 1;
                                                                    opt.innerHTML = 1;
                                                                    document.querySelector('#psiegesmob').add(opt);
                                                            
                                                                    departpsiegesmob = document.querySelector('#psiegesmob');
                                                                    if (departpsiegesmob !== null) {
                                                                        departpsiegesmob.onchange = () => 
                                                                        {
                                                                            let httpProgmob;
                                                                            httpProgmob = new XMLHttpRequest();
                                                                            httpProgmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/creedepartmob/${seltdepmob}/${dpt_datemob}/${selmob}/${lhselmob}`, true);
                                                                            httpProgmob.onload = () => 
                                                                            {
                                                                                const donsmob = JSON.parse(httpProgmob.responseText);
                                                                                console.debug(`${typeof donsmob} - ${donsmob.attributes}`, console.memory);
                                                                                if (Object.entries(donsmob).length >= 1) {
                                                                                    for (let key in Object.entries(donsmob)) {
                                                                                        document.querySelector('#programmob').value = `${donsmob[key].code_progr}`;
                                                                                        document.querySelector('#tarifattribmob').value = `${donsmob[key].typetarif}`;
                                                                                        document.querySelector('#catemob').value = `${donsmob[key].categorie}`;
                                                                                        document.querySelector('#deplignemob').value = `${donsmob[key].gareidentif}`;
                                                                                        document.querySelector('#lignmob').value = `${donsmob[key].ident_ligne}`;
                                                                                        document.querySelector('#nomitinmob').value = `${donsmob[key].nom_ligne}`;
                                                                                        document.querySelector('#prix_axemob').value = `${donsmob[key].prix}`;
                                                                                    }
                                                                                        let httpSiegemob;
                                                                                        httpSiegemob = new XMLHttpRequest();
                                                                                        const sigmob = document.querySelector('#psiegesmob')
                                                                                        .options[document.querySelector('#psiegesmob').options.selectedIndex].value;
                                                                                        const promob = document.querySelector('#programmob').value;
                                                                                        httpSiegemob.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${promob}/${sigmob}`, true);
                                                                                        httpSiegemob.onload = () => 
                                                                                        {
                                                                                            const donsgmob = JSON.parse(httpSiegemob.responseText);
                                                                                            console.debug(`${typeof donsgmob} - ${donsgmob.attributes}`, console.memory);
                                                                                            if(donsgmob == '')
                                                                                            {
                                                                                                let httpSiegmob;
                                                                                                httpSiegmob = new XMLHttpRequest();
                    
                                                                                                httpSiegmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${promob}/${sigmob}`, true);
                                                                                                httpSiegmob.onload = () => 
                                                                                                {
                                                                                                    const donsg2mob = JSON.parse(httpSiegmob.responseText);
                                                                                                    document.querySelector('#messmob').style.display = 'none';
                                                                                                    if (Object.entries(donsg2mob).length >= 1)
                                                                                                        {
                                                                                                            for (let key in Object.entries(donsg2mob)) {
                                                                                                                document.querySelector('#idtampomob').value = `${donsg2mob[key].idtamp}`;                    
                                                                                                                document.querySelector('#siegselectmob').value = `${donsg2mob[key].numsieg}`;
                                                                                                            }
                                                                                                        }
                                                                                                };
                                                                                                httpSiegmob.setRequestHeader('Content-Type', 'application/json');
                                                                                                httpSiegmob.send();
                                                                                            }
                                                                                            else 
                                                                                            {
                                                                                                document.querySelector('#psiegesmob').value = ''; 
                                                                                                if (Object.entries(donsgmob).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(donsgmob)) 
                                                                                                    {
                                                                                                        document.querySelector('#idtampomob').value = `${donsgmob[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselectmob').value = `${donsgmob[key].numsieg}`;
                                                                                                    }
        
                                                                                                }
                                                                                                document.querySelector('#messmob').style.display = 'block';
                                                                                                document.querySelector('#erreurMessmob').innerHTML = `Siege déjà utilisé.`;                   
                                                                                            }
                                                                                        };
                                                                                        httpSiegemob.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegemob.send();
                    
                                                                                   
                                                                                }
                                                                            };
                                                                            httpProgmob.setRequestHeader('Content-Type', 'application/json');
                                                                            httpProgmob.send();
        
                                                                            
                                                                        
                                                                        };
        
                                                                        
                                                                    }*/
                                                            
                                                        } 
                                                        else 
                                                        {       
                                                            if (Object.entries(donmob).length >= 1) {
                                                                for (let key in Object.entries(donmob)) {
                                                                    document.querySelector('#programmob').value = `${donmob[key].code_progr}`;
                                                                    document.querySelector('#tarifattribmob').value = `${donmob[key].typetarif}`;
                                                                    document.querySelector('#dateprmob').value = `${donmob[key].date_progr}`;
                                                                    document.querySelector('#deplignemob').value = `${donmob[key].gareidentif}`;
                                                                    document.querySelector('#inter1mob').value = `${donmob[key].intervalle1}`;
                                                                    document.querySelector('#inter2mob').value = `${donmob[key].intervalle2}`;
                                                                    document.querySelector('#lignmob').value = `${donmob[key].ident_ligne}`;
                                                                    document.querySelector('#hermob').value = `${donmob[key].heure}`;
                                                                    document.querySelector('#catemob').value = `${donmob[key].categori}`;
                                                                    document.querySelector('#nomitinmob').value = `${donmob[key].nom_ligne}`;

                                                                }
                                                            } 
                                                            
                                                            var tfbsmob = document.querySelector('#tarifattribmob').value;
                                                            const httpPrixmob = new XMLHttpRequest();
                                                            httpPrixmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifpriprg/${selmob}/${tfbsmob}`, true);
                                                            httpPrixmob.onload = () => 
                                                            {

                                                                const donprixmob = JSON.parse(httpPrixmob.responseText);
                                                                console.debug(`${typeof donprixmob}-${donprixmob.attributes}`, console.memory);
                                                                if (Object.entries(donprixmob).length >= 1) {
                                                                    for (let key in Object.entries(donprixmob)) 
                                                                    {
                                                                        document.querySelector('#prix_axemob').value = `${donprixmob[key].prix}`;
            
                                                                    }
                                                                }
                                                            };
                                                            httpPrixmob.setRequestHeader('Content-Type', 'application/json');
                                                            httpPrixmob.send();
                                                            
                                                            const httpRequettemob = new XMLHttpRequest();
                                                            const cdprogmob = document.querySelector('#programmob').value;
                                                            const dbmob = document.querySelector('#inter1mob').value;
                                                            const fnmob = document.querySelector('#inter2mob').value;
                                                            
                                                            var lgmob = document.querySelector('#nomitinmob').value;
                                                            const timmob = document.querySelector('#hermob').value;
                                                                httpRequettemob.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${cdprogmob}/${dpt_datemob}/${lgmob}/${timmob}/${dbmob}/${fnmob}`, true);
                                                            httpRequettemob.onload = () => {
                                                                const dattamob = JSON.parse(httpRequettemob.responseText);
                                                                if (Object.entries(dattamob).length >= 1) {
                                                                    for (let key in Object.entries(dattamob)) {
                                                                        
                                                                        let opt = document.createElement('option');
                                                                        opt.value = `${dattamob[key].siege_num}`;
                                                                        opt.innerHTML = `${dattamob[key].siege_num}`;
                                                                        document.querySelector('#psiegesmob').add(opt);
                                                                        
                                                                    }
                                                                    
                                                                } else {
                                                                    document.querySelector('#psiegesmob').options.length = 1;
                                                                }
                                                            };
                                                            httpRequettemob.setRequestHeader('Content-Type', 'application/json');
                                                            httpRequettemob.send();
                                                        }  
                                                        
                                                    };
                                                    httpRequestmob.setRequestHeader('Content-Type', 'application/json');
                                                    httpRequestmob.send();
                                                     
                                                };
                                                
                                        
                                            }
                                };
                                httpRequetesmob.setRequestHeader('Content-Type', 'application/json');
                                httpRequetesmob.send();
                        }
                        else
                        {
                            document.querySelector('#date_depheuremob').style.color = "#FF0000";
                            document.querySelector('#date_depheuremob').style.border = "2px solid #FF0000";
                            document.querySelector('#smsdtmob').style.display = 'block';
                            document.querySelector('#erreurSmsdtmob').innerHTML = `Date non valide.`;
                        }
                    

                };
                
            }
            let progsiegesmob = document.querySelector('#psiegesmob');
            if (progsiegesmob !== null) {
                progsiegesmob.onchange = () => 
                {
                    let httpSiegesmob;
                    httpSiegesmob = new XMLHttpRequest();
                    const sigsmob = document.querySelector('#psiegesmob')
                    .options[document.querySelector('#psiegesmob').options.selectedIndex].value;
                    const prosmob = document.querySelector('#programmob').value;

                    httpSiegesmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${prosmob}/${sigsmob}`, true);
                    httpSiegesmob.onload = () => 
                    {
                        const donsgemob = JSON.parse(httpSiegesmob.responseText);
                        console.debug(`${typeof donsgemob} - ${donsgemob.attributes}`, console.memory);
                        if(donsgemob == '')
                        {
                            let httpSiegsmob;
                            httpSiegsmob = new XMLHttpRequest();

                            httpSiegsmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${prosmob}/${sigsmob}`, true);
                            httpSiegsmob.onload = () => 
                            {
                                const dongmob = JSON.parse(httpSiegsmob.responseText);
                                document.querySelector('#messmob').style.display = 'none';
                                if (Object.entries(dongmob).length >= 1)
                                    {
                                        for (let key in Object.entries(dongmob)) {
                                            document.querySelector('#idtampomob').value = `${dongmob[key].idtamp}`;                    
                                            document.querySelector('#siegselectmob').value = `${dongmob[key].numsieg}`;
                                        }
                                    }
                            };
                            httpSiegsmob.setRequestHeader('Content-Type', 'application/json');
                            httpSiegsmob.send();
                        }
                        else {
                            document.querySelector('#psiegesmob').value = '';     
                            if (Object.entries(donsgemob).length >= 1)
                            {
                                for (let key in Object.entries(donsgemob)) {
                                    document.querySelector('#idtampomob').value = `${donsgemob[key].idtamp}`;                    
                                    document.querySelector('#siegselectmob').value = `${donsgemob[key].numsieg}`;
                                }

                            }
                            document.querySelector('#messmob').style.display = 'block';
                            document.querySelector('#erreurMessmob').innerHTML = `Siege déjà utilisé.`;                                                                   }
                    };
                    httpSiegesmob.setRequestHeader('Content-Type', 'application/json');
                    httpSiegesmob.send();

                
                };
            }
           
            
        //recherche d'information du client depart principal
        let infmob = document.querySelector('#rnclient_contactmob');
        if (infmob !== null)
            infmob.onkeyup = () => {
                let httpInfosmob;
                if (window.XMLHttpRequest) {
                    httpInfosmob = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfosmob = new ActiveXObject("Microsoft.XMLHTTP");
                }
                var verificatmob = document.querySelector('#rnclient_contactmob').value;
                
                httpInfosmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifinfos/${verificatmob}`, true);
                httpInfosmob.onload = () => {
                    const infosmob = JSON.parse(httpInfosmob.responseText);
                    if (infosmob == null) {
                        document.querySelector('#rclientmob').value = "";
                        document.querySelector('#prnclientmob').value = "";
                        document.querySelector('#pascompagniemob').value = "";
                        document.querySelector('#typesmob').value = "";
                    } else {
                        if (Object.entries(infosmob).length > 1) {
                            
                            if (infosmob.contact_client == verificatmob) {
                                document.querySelector('#rclientmob').value = `${infosmob.nom_client}`;
                                document.querySelector('#prnclientmob').value = `${infosmob.prenom_client}`;
                                document.querySelector('#pascompagniemob').value = `${infosmob.id_client}`;
                                document.querySelector('#rclientcpmob').value = `${infosmob.nom_client}`;
                                document.querySelector('#prnclientcpmob').value = `${infosmob.prenom_client}`;
                                document.querySelector('#typesmob').value = `${infosmob.type_client}`;
                            } else {
                                document.querySelector('#rclientmob').value = "";
                                document.querySelector('#prnclientmob').value = "";
                                document.querySelector('#pascompagniemob').value = "";
                                document.querySelector('#typesmob').value = "";
                            }
                        }
                    }
                };
                httpInfosmob.setRequestHeader('Content-Type', 'application/json');
                httpInfosmob.send();
            };
            
            let butonclicmob = document.querySelector('#idresetmob');
            if (butonclicmob !== null) {
                butonclicmob.onclick = () => 
                {
                    let httpSiegeselectmob;
                    httpSiegeselectmob = new XMLHttpRequest();
                    const siegselectmob = document.querySelector('#siegselectmob').value;
                    const idtapmob = document.querySelector('#idtampomob').value;
                    httpSiegeselectmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtapmob}/${siegselectmob}`, true);
                    httpSiegeselectmob.onload = () => 
                    {
                        const donselectmob = JSON.parse(httpSiegeselectmob.responseText);
                        document.querySelector('#messmob').style.display = 'none';
                        
                    };
                    httpSiegeselectmob.setRequestHeader('Content-Type', 'application/json');
                    httpSiegeselectmob.send();

                
                };
            }
                
                e.onclick = function () {   
                    let mobForm = document.querySelector('#mobForm');
                    
                    mobForm.setAttribute('action', `${APP_ROOT}/Programmes/passagermobil/${e.dataset.cle_compagnie}`);   
                }
                
    })

});
;
/* --- addbagage.js --- */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.addbagage').forEach(function (e) 
    {
        
        let baginfos = document.querySelector('#confirme_infocodeticket');
        if (baginfos !== null)
        baginfos.onclick = () => {

            let httpRequestBag;
            
            if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                httpRequestBag = new XMLHttpRequest();
            } else if (window.ActiveXObject) { // IE 6 and older
                httpRequestBag = new ActiveXObject("Microsoft.XMLHTTP");
            }
           
            var bagcocl = document.querySelector("#codeticketbag").value;
            var baggid = document.querySelector("#codebaggid").value;
            var bagsgid = document.querySelector("#codebagsousgid").value;
            httpRequestBag.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/codeclientverif/${bagcocl}/${baggid}/${bagsgid}`, true);
            httpRequestBag.onload = () => {

                const donneesbag = JSON.parse(httpRequestBag.responseText);
                
                if (donneesbag == null) {
                    document.querySelector('#pascontactbagsans').value = '';
                    document.querySelector('#rclientcpbagsans').value = '';
                    document.querySelector('#nclbagasans').value = '';
                    document.querySelector('#prnclientcpbagsans').value = '';
                    document.querySelector('#programbagsans').value = '';
                    document.querySelector('#siegebagsans').value = '';
                    document.querySelector('#codebusbagsans').value = '';
                    document.querySelector('#codtickbagsans').value = '';
                    document.querySelector('#lgcodtickbagsans').value = '';
                    document.querySelector('#siegebagasans').value = '';
                    document.querySelector('#idcompaga').value = '';
                    document.querySelector('#lignespasse').value = '';
                    document.querySelector('#quartpasse').value = '';
                    document.querySelector('#lgecodtickbagsanstr').value = '';
                    document.querySelector('#lgecodtickbagsanstrenr').value = '';
                } else
                {
                    if (Object.entries(donneesbag).length >= 1){

                        document.querySelector('#pascontactbagsans').value = `${String(donneesbag.contact_client)}`;
                        document.querySelector('#rclientcpbagsans').value = `${donneesbag.id_client_pass}`;
                        document.querySelector('#nclbagasans').value = `${donneesbag.nom_client}`;
                        document.querySelector('#prnclientcpbagsans').value = `${donneesbag.prenom_client}`;
                        document.querySelector('#programbagsans').value = `${donneesbag.code_pro}`;
                        document.querySelector('#siegebagsans').value = `${donneesbag.num_siege_categorie}`;
                        document.querySelector('#codebusbagsans').value = `${donneesbag.depart_code}`;
                        document.querySelector('#codtickbagsans').value = `${String(donneesbag.code_ticket)}`;
                        document.querySelector('#lgcodtickbagsans').value = `${donneesbag.code_passager}`;
                        document.querySelector('#siegebagasans').value = `SIEGE : ${donneesbag.num_siege_categorie}  ${donneesbag.nom_gadest}  ${donneesbag.quart} ${donneesbag.heure} Bus : ${donneesbag.depart_code}`;
                        document.querySelector('#idcompaga').value = `${donneesbag.id_compaga}`;
                        document.querySelector('#lgecodtickbagsanstr').value = `${donneesbag.tamponcodtr}`;
                        document.querySelector('#lgecodtickbagsanstrenr').value = `${donneesbag.tamponcodtr}`;
                    } 
                }
            };
            httpRequestBag.setRequestHeader('Content-Type', 'application/json');
            httpRequestBag.send();
        };

        let baginfostr = document.querySelector('#confirme_infocdticket');
        if (baginfostr !== null)
        baginfostr.onclick = () => {

            let httpRequestBagtr;
            
            if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                httpRequestBagtr = new XMLHttpRequest();
            } else if (window.ActiveXObject) { // IE 6 and older
                httpRequestBagtr = new ActiveXObject("Microsoft.XMLHTTP");
            }
           
            var bagcocltr = document.querySelector("#lgecodtickbagsanstr").value;
            var baggid = document.querySelector("#codebaggid").value;
            var bagsgid = document.querySelector("#codebagsousgid").value;

            httpRequestBagtr.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/codeclientveriftr/${bagcocltr}`, true);
            httpRequestBagtr.onload = () => {

                const donneesbagtr = JSON.parse(httpRequestBagtr.responseText);
                
                if (Object.entries(donneesbagtr).length == 1) {
                    
                    for (let item of donneesbagtr) {
                        document.querySelector('#lignespasse').value = `${item.ident_ligne}/${item.idgaresdest}`;
                        document.querySelector('#quartpasse').value = item.quart;
                    }
                    const tbody = document.getElementById("table-body");

                    donneesbagtr.forEach(item => {
                        const tr = document.createElement("tr");

                        tr.innerHTML = `
                            <td>${item.ident_ligne}</td>
                            <td>${item.quart}</td>
                        `;

                        tbody.appendChild(tr);
                    });
                } 
                else
                {
                    if (Object.entries(donneesbagtr).length > 1) {

                        let httpRequestBagtr2;
                        httpRequestBagtr2 = new XMLHttpRequest();
                        httpRequestBagtr2.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/codeclientveriftr2/${bagcocltr}`, true);
                        httpRequestBagtr2.onload = () => {

                            const donneesbagtr2 = JSON.parse(httpRequestBagtr2.responseText);
                
                            if (Object.entries(donneesbagtr2).length == 0) {

                                for (let [key, value] of Object.entries(donneesbagtr)) {
                                    document.querySelector('#lignespasse').value = `${value.ident_ligne}/${value.idgaresdest}`;
                                    document.querySelector('#quartpasse').value = value.quart;
                                }

                                const tbody = document.getElementById("table-body");

                                donneesbagtr.forEach(item => {
                                    const tr = document.createElement("tr");

                                    tr.innerHTML = `
                                        <td>${item.ident_ligne}</td>
                                        <td>${item.quart}</td>
                                    `;

                                    tbody.appendChild(tr);
                                });
                            }
                            else{
                        
                                for (let item of donneesbagtr2) {
                                    document.querySelector('#lignespasse').value = `${item.ident_ligne}/${item.idgaresdest}`;
                                    document.querySelector('#quartpasse').value = item.quart;
                                }
                                const tbody = document.getElementById("table-body");

                                donneesbagtr2.forEach(item => {
                                    const tr = document.createElement("tr");

                                    tr.innerHTML = `
                                        <td>${item.ident_ligne}</td>
                                        <td>${item.quart}</td>
                                    `;

                                    tbody.appendChild(tr);
                                });
                            }
                        };

                        httpRequestBagtr2.setRequestHeader('Content-Type', 'application/json');
                        httpRequestBagtr2.send();
                    }
                }
            };

            httpRequestBagtr.setRequestHeader('Content-Type', 'application/json');
            httpRequestBagtr.send();
        };
        updateContenu = function () 
        {
            // Récupérer le champ "Contenu"
            var contenuField = document.querySelector('textarea[name="naturebagagesans"]');
            
            // Récupérer toutes les cases à cocher (checkbox)
            var checkboxes = document.querySelectorAll('input[name="types_bagsans[]"]:checked');
            
            // Créer un tableau pour stocker les valeurs des cases cochées
            var selectedValues = [];
            
            // Parcourir les cases cochées et récupérer leur valeur
            checkboxes.forEach(function(checkbox) {
                selectedValues.push(checkbox.value);
            });
            
            // Mettre à jour le contenu du champ avec les cases sélectionnées
            contenuField.value = selectedValues.join(', '); // Séparer par des virgules
        }
        e.onclick = function () {
            let bagsansForm = document.querySelector('#bagsansForm');
            
            bagsansForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/savebag/${e.dataset.cle_compagnie}`);   
        }
        
        var clique = true;

        $('#bottonbag').click(function(event) 
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
/* --- addbagagesuivi.js --- */
document.addEventListener('DOMContentLoaded', () => { 
    document.querySelectorAll('.addbagagesuivi').forEach(function (e) 
    {
        let axeselectsbag = document.querySelector('#bagligne');
        if (axeselectsbag !== null)
        axeselectsbag.onchange = () => {
            
            document.querySelector('#quartierbag').options.length = 1;
           
            const lignessbag = document.querySelector('#bagligne').options[document.querySelector('#bagligne').options.selectedIndex].value;
                var post_arlgsbag = lignessbag.split('/');
                var seltarsbag = post_arlgsbag[0];
                var sougidarr1sbag = post_arlgsbag[1];

                var post_arlg1sbag = sougidarr1sbag.split('/');
                var seltar1sbag = post_arlg1sbag[0];
                var sougidarr2sbag = post_arlg1sbag[1];
    
            let httpRequetesquartsbag = new XMLHttpRequest();
            httpRequetesquartsbag.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/verifconfquart/${seltarsbag}`, true);
            httpRequetesquartsbag.onload = () => {
                const dataqsbag = JSON.parse(httpRequetesquartsbag.responseText);
                if(dataqsbag == ''){
                    document.querySelector('#quartierbag').options.length = 1;
                }else{
                    if (Object.entries(dataqsbag).length >= 1) {
                        for (let key in Object.entries(dataqsbag)) {
                            let opt = document.createElement('option');
                            opt.value = `${dataqsbag[key].nom_quartier}`;
                            opt.innerHTML = `${dataqsbag[key].nom_quartier}`;
                            document.querySelector('#quartierbag').add(opt);
                        }
                    } else {
                        document.querySelector('#quartierbag').options.length = 1;
                    }
                }          
            };
            httpRequetesquartsbag.setRequestHeader('Content-Type', 'application/json');
            httpRequetesquartsbag.send();         
        };

        updateContenu = function () 
        {
            // Récupérer le champ "Contenu"
            var contenuField = document.querySelector('textarea[name="naturebagage"]');
            
            // Récupérer toutes les cases à cocher (checkbox)
            var checkboxes = document.querySelectorAll('input[name="types_bagage[]"]:checked');
            
            // Créer un tableau pour stocker les valeurs des cases cochées
            var selectedValues = [];
            
            // Parcourir les cases cochées et récupérer leur valeur
            checkboxes.forEach(function(checkbox) {
                selectedValues.push(checkbox.value);
            });
            
            // Mettre à jour le contenu du champ avec les cases sélectionnées
            contenuField.value = selectedValues.join(', '); // Séparer par des virgules
        }

        let infmobbag = document.querySelector('#rnclient_contactbag');
        if (infmobbag !== null)
            infmobbag.onkeyup = () => {
                let httpInfosmobbag;
                if (window.XMLHttpRequest) {
                    httpInfosmobbag = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfosmobbag = new ActiveXObject("Microsoft.XMLHTTP");
                }
                var verificatmobbag = document.querySelector('#rnclient_contactbag').value;
                
                httpInfosmobbag.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/verifinfos/${verificatmobbag}`, true);
                httpInfosmobbag.onload = () => {
                    const infosmobbag = JSON.parse(httpInfosmobbag.responseText);
                    if (infosmobbag == null) {
                        document.querySelector('#rclientbag').value = "";
                        document.querySelector('#prnclientbag').value = "";
                        document.querySelector('#pascompagniebag').value = "";
                        document.querySelector('#typesmobbag').value = "";
                    } else {
                        if (Object.entries(infosmobbag).length > 1) {
                            
                            if (infosmobbag.contact_client == verificatmobbag) {
                                document.querySelector('#rclientbag').value = `${infosmobbag.nom_client}`;
                                document.querySelector('#prnclientbag').value = `${infosmobbag.prenom_client}`;
                                document.querySelector('#pascompagniebag').value = `${infosmobbag.id_client}`;
                                document.querySelector('#rclientcpbag').value = `${infosmobbag.nom_client}`;
                                document.querySelector('#prnclientcpbag').value = `${infosmobbag.prenom_client}`;
                                document.querySelector('#typesmobbag').value = `${infosmobbag.type_client}`;
                            } else {
                                document.querySelector('#rclientbag').value = "";
                                document.querySelector('#prnclientbag').value = "";
                                document.querySelector('#pascompagniebag').value = "";
                                document.querySelector('#typesmobbag').value = "";
                            }
                        }
                    }
                };
                httpInfosmobbag.setRequestHeader('Content-Type', 'application/json');
                httpInfosmobbag.send();
            };
         
        e.onclick = function (){

            let bagFormsuivi = document.querySelector('#bagFormsuivi');
            
            bagFormsuivi.setAttribute('action', `${APP_ROOT}/Reprogrammes/savebagsuivi/${e.dataset.cle_compagnie}`);   
        }

        var clique = true;

            $('#bottonsuiv').click(function(event) 
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
/* --- addbagagenfact.js --- */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.addbagagenfact').forEach(function (e) 
    {
        let baginfosn = document.querySelector('#confirme_infocodeticketn');
        if (baginfosn !== null)
            baginfosn.onclick = () => {

            let httpRequestBagn;
            
            if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                httpRequestBagn = new XMLHttpRequest();
            } else if (window.ActiveXObject) { // IE 6 and older
                httpRequestBagn = new ActiveXObject("Microsoft.XMLHTTP");
            }
           
            
            var bagcocln = document.querySelector("#codeticketbagn").value;
            var baggidn = document.querySelector("#codebaggidn").value;
            var bagsgidn = document.querySelector("#codebagsousgidn").value;
            httpRequestBagn.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/codeclientverif/${bagcocln}/${baggidn}/${bagsgidn}`, true);
            httpRequestBagn.onload = () => {

                const donneesbagn = JSON.parse(httpRequestBagn.responseText);
                
                if (donneesbagn == null) {
                    
                        document.querySelector('#pascontactbagsansn').value = '';
                        document.querySelector('#rclientcpbagsansn').value = '';
                        document.querySelector('#nclbagasansn').value = '';
                        document.querySelector('#prnclientcpbagsansn').value = '';
                        document.querySelector('#programbagsansn').value = '';
                        document.querySelector('#siegebagsansn').value = '';
                        document.querySelector('#codebusbagsansn').value = '';
                        document.querySelector('#codtickbagsansn').value = '';
                        document.querySelector('#lgcodtickbagsansn').value = '';
                        document.querySelector('#lgcodtickbagsansntr').value = '';
                        document.querySelector('#siegebagasansn').value = '';
                        document.querySelector('#lignespassen').value = '';
                        document.querySelector('#quartpassen').value = '';
                        document.querySelector('#lgcodtickbagsansntrenr').value = '';
                } else
                {

                
                    if (Object.entries(donneesbagn).length >= 1){

                    
                        document.querySelector('#pascontactbagsansn').value = `${donneesbagn.contact_client}`;
                        document.querySelector('#rclientcpbagsansn').value = `${donneesbagn.id_client_pass}`;
                        document.querySelector('#nclbagasansn').value = `${donneesbagn.nom_client}`;
                        document.querySelector('#prnclientcpbagsansn').value = `${donneesbagn.prenom_client}`;
                        document.querySelector('#programbagsansn').value = `${donneesbagn.code_pro}`;
                        document.querySelector('#siegebagsansn').value = `${donneesbagn.num_siege_categorie}`;
                        document.querySelector('#codebusbagsansn').value = `${donneesbagn.depart_code}`;
                        document.querySelector('#codtickbagsansn').value = `${donneesbagn.code_ticket}`;
                        document.querySelector('#lgcodtickbagsansn').value = `${donneesbagn.code_passager}`;
                        document.querySelector('#siegebagasansn').value = `SIEGE : ${donneesbagn.num_siege_categorie}  ${donneesbagn.nom_gadest}  ${donneesbagn.quart} ${donneesbagn.heure} Bus : ${donneesbagn.depart_code}`;
                        document.querySelector('#lgcodtickbagsansntr').value = `${donneesbagn.tamponcodtr}`;
                        document.querySelector('#lgcodtickbagsansntrenr').value = `${donneesbagn.tamponcodtr}`;
                    } 
                }
            };
            httpRequestBagn.setRequestHeader('Content-Type', 'application/json');
            httpRequestBagn.send();
        };

        let baginfostr = document.querySelector('#confirme_infocdticketsn');
        if (baginfostr !== null)
        baginfostr.onclick = () => {

            let httpRequestBagtr;
            
            if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                httpRequestBagtr = new XMLHttpRequest();
            } else if (window.ActiveXObject) { // IE 6 and older
                httpRequestBagtr = new ActiveXObject("Microsoft.XMLHTTP");
            }
            
            var baggidtr = document.querySelector("#codebaggidn").value;

            var bagcoclsntr = document.querySelector("#lgcodtickbagsansntr").value;

            httpRequestBagtr.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/codeclientveriftr/${bagcoclsntr}`, true);
            httpRequestBagtr.onload = () => {

                const donneesbagtr = JSON.parse(httpRequestBagtr.responseText);
                
                if (Object.entries(donneesbagtr).length == 1) {
                    
                    for (let item of donneesbagtr) {
                        document.querySelector('#lignespassen').value = `${item.ident_ligne}/${item.idgaresdest}`;
                        document.querySelector('#quartpassen').value = item.quart;
                    }

                    const tbody = document.getElementById("table-body");

                    donneesbagtr.forEach(item => {
                        const tr = document.createElement("tr");

                        tr.innerHTML = `
                            <td>${item.ident_ligne}</td>
                            <td>${item.quart}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                } 
                else
                {
                    if (Object.entries(donneesbagtr).length > 1) {
                        let httpRequestBagtr2;
                        httpRequestBagtr2 = new XMLHttpRequest();
                        httpRequestBagtr2.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/codeclientveriftr2/${bagcocltr}`, true);
                        httpRequestBagtr2.onload = () => {

                            const donneesbagtr2 = JSON.parse(httpRequestBagtr2.responseText);
                
                            if (Object.entries(donneesbagtr2).length == 0) {

                                for (let [key, value] of Object.entries(donneesbagtr)) {
                                    document.querySelector('#lignespassen').value = `${value.ident_ligne}/${value.idgaresdest}`;
                                    document.querySelector('#quartpassen').value = value.quart;
                                }

                                const tbody = document.getElementById("table-body");

                                donneesbagtr.forEach(item => {
                                    const tr = document.createElement("tr");

                                    tr.innerHTML = `
                                        <td>${item.ident_ligne}</td>
                                        <td>${item.quart}</td>
                                    `;

                                    tbody.appendChild(tr);
                                });
                            }
                            else{
                        
                                for (let item of donneesbagtr2) {
                                    document.querySelector('#lignespassen').value = `${item.ident_ligne}/${item.idgaresdest}`;
                                    document.querySelector('#quartpassen').value = item.quart;
                                }
                                const tbody = document.getElementById("table-body");

                                donneesbagtr2.forEach(item => {
                                    const tr = document.createElement("tr");

                                    tr.innerHTML = `
                                        <td>${item.ident_ligne}</td>
                                        <td>${item.quart}</td>
                                    `;

                                    tbody.appendChild(tr);
                                });
                            }
                        };

                        httpRequestBagtr2.setRequestHeader('Content-Type', 'application/json');
                        httpRequestBagtr2.send();
                    } 
                }
            };

            httpRequestBagtr.setRequestHeader('Content-Type', 'application/json');
            httpRequestBagtr.send();
        };
        
        updateContenu = function () 
        {
            // Récupérer le champ "Contenu"
            var contenuField = document.querySelector('textarea[name="naturebagagesansn"]');
            
            // Récupérer toutes les cases à cocher (checkbox)
            var checkboxes = document.querySelectorAll('input[name="types_bagsansn[]"]:checked');
            
            // Créer un tableau pour stocker les valeurs des cases cochées
            var selectedValues = [];
            
            // Parcourir les cases cochées et récupérer leur valeur
            checkboxes.forEach(function(checkbox) {
                selectedValues.push(checkbox.value);
            });
            
            // Mettre à jour le contenu du champ avec les cases sélectionnées
            contenuField.value = selectedValues.join(', '); // Séparer par des virgules
        }
        
        e.onclick = function () {   
            let bagsansnForm = document.querySelector('#bagsansnForm');
            
            bagsansnForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/savebagnfact/${e.dataset.cle_compagnie}`);   
        }
        
        var clique = true;

            $('#bottonbagnf').click(function(event) 
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
/* --- adautrfactbag.js --- */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.adautrfactbag').forEach(function (e) {
        let Requests;
        if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
            Requests = new XMLHttpRequest();
        } else if (window.ActiveXObject) { // IE 6 and older
            Requests = new ActiveXObject("Microsoft.XMLHTTP");
        }
        let axeselect = document.querySelector('#auaxeconf');
        if (axeselect !== null)
        axeselect.onchange = () => {
            
            document.querySelector('#auquartierbag').options.length = 1;
           
            const heureaxep = document.querySelector('#auaxeconf').options[document.querySelector('#auaxeconf').options.selectedIndex].value;
            
            var tpost_arlgsbag = heureaxep.split('/');
                var tseltarsbag = tpost_arlgsbag[0];
                var tsougidarr1sbag = tpost_arlgsbag[1];

                var tpost_arlg1sbag = tsougidarr1sbag.split('/');
                var tseltar1sbag = tpost_arlg1sbag[0];
                var tsougidarr2sbag = tpost_arlg1sbag[1];

            let httpRequetesquart = new XMLHttpRequest();

                httpRequetesquart.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfquart/${tseltarsbag}`, true);
                        httpRequetesquart.onload = () => {
                        const dataq = JSON.parse(httpRequetesquart.responseText);
                        if(dataq == ''){
                            document.querySelector('#auquartierbag').options.length = 1;
                        }else
                        {
                            if (Object.entries(dataq).length >= 1) {
                                        
                                for (let key in Object.entries(dataq)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${dataq[key].nom_quartier}`;
                                    opt.innerHTML = `${dataq[key].nom_quartier}`;
                                    document.querySelector('#auquartierbag').add(opt);
                                }
                            } else {
                                document.querySelector('#auquartierbag').options.length = 1;
                            }
                        }
                            
                            
                };
                httpRequetesquart.setRequestHeader('Content-Type', 'application/json');
                httpRequetesquart.send();

        };
        
        updateContenu = function () 
        {
            // Récupérer le champ "Contenu"
            var contenuField = document.querySelector('textarea[name="aunaturebagagesans"]');
            
            // Récupérer toutes les cases à cocher (checkbox)
            var checkboxes = document.querySelectorAll('input[name="autypes_bagsans[]"]:checked');
            
            // Créer un tableau pour stocker les valeurs des cases cochées
            var selectedValues = [];
            
            // Parcourir les cases cochées et récupérer leur valeur
            checkboxes.forEach(function(checkbox) {
                selectedValues.push(checkbox.value);
            });
            
            // Mettre à jour le contenu du champ avec les cases sélectionnées
            contenuField.value = selectedValues.join(', '); // Séparer par des virgules
        }
        
            //recherche d'information du client depart principal
        let infcontact = document.querySelector('#aupascontactpconf');
        if (infcontact !== null)
        infcontact.onkeyup = () => {
                let httpInfosrequest;
                if (window.XMLHttpRequest) {
                    httpInfosrequest = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfosrequest = new ActiveXObject("Microsoft.XMLHTTP");
                }
                var verifict = document.querySelector('#aupascontactpconf').value;
                httpInfosrequest.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/verifinfos/${verifict}`, true);
                httpInfosrequest.onload = () => {
                    const infosreq = JSON.parse(httpInfosrequest.responseText);
                    if (infosreq == null) {
                        document.querySelector('#aupasnompconf').value = "";
                        document.querySelector('#aupasprenompconf').value = "";
                        document.querySelector('#pascnibpconf').value = "";
                        document.querySelector('#aupasdatepconf').value = "";
                        document.querySelector('#audelivrelieu').value = "";
                        document.querySelector('#auclientconfirmeid').value = "";
                    } else {
                        if (Object.entries(infosreq).length > 1) {
                            
                            if (infosreq.contact_client == verifict) {
                                document.querySelector('#aupasnompconf').value = `${infosreq.nom_client}`;
                                document.querySelector('#aupasprenompconf').value = `${infosreq.prenom_client}`;
                                document.querySelector('#pascnibpconf').value = `${infosreq.num_CNIB}`;
                                document.querySelector('#aupasdatepconf').value = `${infosreq.date_delivre}`;
                                document.querySelector('#audelivrelieu').value = `${infosreq.lieu_delivre}`;
                                document.querySelector('#auclientconfirmeid').value = `${infosreq.id_client}`;

                                document.querySelector('#aupasnompconfcp').value = `${infosreq.nom_client}`;
                                document.querySelector('#aupasprenompconfcp').value = `${infosreq.prenom_client}`;
                                document.querySelector('#aupascnibpconfcp').value = `${infosreq.num_CNIB}`;
                                document.querySelector('#aupasdatepconfcp').value = `${infosreq.date_delivre}`;
                                document.querySelector('#aulieucnibconf').value = `${infosreq.lieu_delivre}`;
                            } else {
                                document.querySelector('#aupasnompconf').value = "";
                                document.querySelector('#aupasprenompconf').value = "";
                                document.querySelector('#pascnibpconf').value = "";
                                document.querySelector('#aupasdatepconf').value = "";
                                document.querySelector('#audelivrelieu').value = "";
                                document.querySelector('#auclientconfirmeid').value = "";
                            }
                        }
                    }
                };
                httpInfosrequest.setRequestHeader('Content-Type', 'application/json');
                httpInfosrequest.send();
            };
        e.onclick = function () {
            let autrForm = document.querySelector('#autrForm');
            autrForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/autresave/${e.dataset.cle_compagnie}`);
        }

        var clique = true;

            $('#auvalidep').click(function(event) 
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
/* --- adventeescale.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adventeescale').forEach(function (e) 
    {
            let escalgar= document.querySelector('#depargareescal');
            if (escalgar !== null)
            escalgar.onchange = () => {
                document.querySelector('#prix_axeescal').value = '';
                document.querySelector('#date_depheureescal').value = '';
                document.querySelector('#arrsgareescal').value = '';
                document.querySelector('#hdepartescal').options.length = 1;
                document.querySelector('#quartierescal').options.length = 1;
                document.querySelector('#typesescal').value = '';
                  
            };
            let arescal = document.querySelector('#arrsgareescal');
            if (arescal !== null)
            arescal.onchange = () => {
                document.querySelector('#prix_axeescal').value = '';
                document.querySelector('#date_depheureescal').value = '';
                document.querySelector('#hdepartescal').options.length = 1;
                document.querySelector('#quartierescal').options.length = 1;
                document.querySelector('#typesescal').value = '';
                  
                    const typgareescal = document.querySelector('#arrsgareescal')
                    .options[document.querySelector('#arrsgareescal').options.selectedIndex].value;
                    let httptypequartescal;
                    httptypequartescal = new XMLHttpRequest();
                    
                    httptypequartescal.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${typgareescal}`, true);
                    httptypequartescal.onload = () => 
                    {
                        const donquaescal = JSON.parse(httptypequartescal.responseText);
                        if (donquaescal == '') {
                            document.querySelector('#quartierescal').options.length = 1;
                        }
                        else{
                            if (Object.entries(donquaescal).length >= 1) {
                                            
                                for (let key in Object.entries(donquaescal)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${donquaescal[key].nom_quartier}`;
                                    opt.innerHTML = `${donquaescal[key].nom_quartier}`;
                                    document.querySelector('#quartierescal').add(opt);
                                }
                            } else {
                                document.querySelector('#quartierescal').options.length = 1;
                            }
                        }
                        
                    };
                    httptypequartescal.setRequestHeader('Content-Type', 'application/json');
                    httptypequartescal.send();
            };
            
            let daescal = document.querySelector('#date_depheureescal');
            if (daescal !== null){
                daescal.onchange = () => 
                {
                    
                    document.querySelector('#hdepartescal').options.length = 1;
                    
                    let httpRequetesescal;
                    
                    if (window.XMLHttpRequest) {
                        httpRequetesescal = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpRequetesescal = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                        var depaescal = document.querySelector('#depargareescal').value;
                        var arrescal = document.querySelector('#arrsgareescal').value;
                        var datedepartescal = document.querySelector('#date_depheureescal').value;
                        var dateactuescal = document.querySelector('#actuescal').value;
                                         
                        var post_lhdepescal = depaescal.split('/');
                        var seltdepescal = post_lhdepescal[0];
                        var sougidescal = post_lhdepescal[1];
                        var dest_lhdepescal = arrescal.split('/');
                        var seltdestescal = dest_lhdepescal[0];
                        var sougesescal = dest_lhdepescal[1];
                        if(datedepartescal >= dateactuescal)
                        {
                            
                            httpRequetesescal.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheure1/${seltdepescal}-${seltdestescal}/${datedepartescal}`, true);
                            httpRequetesescal.onload = () => {
                                const dataAxeescal = JSON.parse(httpRequetesescal.responseText);
                                
                                    if (dataAxeescal == '') {
                                        
                                        document.querySelector('#smsdtescal').style.display = 'none';
                                        document.querySelector('#date_depheureescal').style.color = "black";
                                        document.querySelector('#date_depheureescal').style.border = "1px solid";
                                        
                                    } 
                                    else 
                                    {       
                                        
                                        document.querySelector('#smsdtescal').style.display = 'none';
                                        document.querySelector('#date_depheureescal').style.color = "black";
                                        document.querySelector('#date_depheureescal').style.border = "1px solid";
                                        if (Object.entries(dataAxeescal).length >= 1) 
                                        {
                                                
                                            
                                            for (let key in Object.entries(dataAxeescal)) {
                                                    let opt = document.createElement('option');
                                                    opt.value = `${dataAxeescal[key].id_ligneheure}/${dataAxeescal[key].heure}`;
                                                    opt.innerHTML = `${dataAxeescal[key].heure}`;
                                                    document.querySelector('#hdepartescal').add(opt);
                                                }
                                        } else {
                                            document.querySelector('#hdepartescal').options.length = 1;
                                        }
                                    }
                                                                               
                            };
                            httpRequetesescal.setRequestHeader('Content-Type', 'application/json');
                            httpRequetesescal.send();

                            let hrdepartescal = document.querySelector('#hdepartescal');
                            if (hrdepartescal !== null) {
                                hrdepartescal.onchange = () => 
                                {       
                                    const seleescal = document.querySelector('#hdepartescal')
                                        .options[document.querySelector('#hdepartescal').options.selectedIndex].value;

                                        var post_lhescal = seleescal.split('/');
                                        var selescal = post_lhescal[0];
                                        var lhselescal = post_lhescal[1];

                                    var tfbsescal = document.querySelector('#tarifattribescal').value;
                                    const httpPrixescal = new XMLHttpRequest();
                                    httpPrixescal.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifpriesc/${selescal}/${tfbsescal}/${seltdepescal}`, true);
                                    httpPrixescal.onload = () => 
                                    {

                                        const donprixescal = JSON.parse(httpPrixescal.responseText);
                                        console.debug(`${typeof donprixescal}-${donprixescal.attributes}`, console.memory);
                                        if (Object.entries(donprixescal).length >= 1) {
                                            for (let key in Object.entries(donprixescal)) 
                                            {
                                                document.querySelector('#prix_axeescal').value = `${donprixescal[key].prix}`;

                                            }
                                        }
                                    };
                                    httpPrixescal.setRequestHeader('Content-Type', 'application/json');
                                    httpPrixescal.send();
                                };
                            }

                        }
                        else
                        {
                            document.querySelector('#date_depheureescal').style.color = "#FF0000";
                            document.querySelector('#date_depheureescal').style.border = "2px solid #FF0000";
                            document.querySelector('#smsdtescal').style.display = 'block';
                            document.querySelector('#erreurSmsdtescal').innerHTML = `Date non valide.`;
                        }
                    
                }; 
            }
            
        //recherche d'information du client depart principal
        let infescal = document.querySelector('#rnclient_contactescal');
        if (infescal !== null)
            infescal.onkeyup = () => {
                let httpInfosescal;
                if (window.XMLHttpRequest) {
                    httpInfosescal = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfosescal = new ActiveXObject("Microsoft.XMLHTTP");
                }
                var verificatescal = document.querySelector('#rnclient_contactescal').value;
                
                httpInfosescal.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifinfos/${verificatescal}`, true);
                httpInfosescal.onload = () => {
                    const infosescal = JSON.parse(httpInfosescal.responseText);
                    if (infosescal == null) {
                        document.querySelector('#rclientescal').value = "";
                        document.querySelector('#prnclientescal').value = "";
                        document.querySelector('#pascompagnieescal').value = "";
                        document.querySelector('#typesescal').value = "";
                        document.querySelector('#cnibclescal').value = "";
                        document.querySelector('#dateclescal').value = "";
                        document.querySelector('#lieuclescal').value = "";
                    
                    } else {
                        if (Object.entries(infosescal).length > 1) {
                            
                            if (infosescal.contact_client == verificatescal) {
                                document.querySelector('#rclientescal').value = `${infosescal.nom_client}`;
                                document.querySelector('#prnclientescal').value = `${infosescal.prenom_client}`;
                                document.querySelector('#pascompagnieescal').value = `${infosescal.id_client}`;
                                document.querySelector('#rclientcpescal').value = `${infosescal.nom_client}`;
                                document.querySelector('#prnclientcpescal').value = `${infosescal.prenom_client}`;
                                document.querySelector('#typesescal').value = `${infosescal.type_client}`;
                                document.querySelector('#cnibclescal').value = `${infosescal.num_CNIB}`;
                                document.querySelector('#dateclescal').value = `${infosescal.date_delivre}`;
                                document.querySelector('#lieuclescal').value = `${infosescal.lieu_delivre}`;
                    
                            } else {
                                document.querySelector('#rclientescal').value = "";
                                document.querySelector('#prnclientescal').value = "";
                                document.querySelector('#pascompagnieescal').value = "";
                                document.querySelector('#typesescal').value = "";
                                document.querySelector('#cnibclescal').value = "";
                                document.querySelector('#dateclescal').value = "";
                                document.querySelector('#lieuclescal').value = "";
                    
                            }
                        }
                    }
                };
                httpInfosescal.setRequestHeader('Content-Type', 'application/json');
                httpInfosescal.send();
            };
            
                e.onclick = function () {   
                    let escalForm = document.querySelector('#escalForm');
                    
                    escalForm.setAttribute('action', `${APP_ROOT}/Ventescales/passagerescal/${e.dataset.cle_compagnie}`);   
                }

                var clique = true;

                $('#bottonescal').click(function(event) 
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
/* --- adbagescale.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adbagescale').forEach(function (e) 
    {
        
        let baginfos = document.querySelector('#infocodeticketesc');
        if (baginfos !== null)
            baginfos.onclick = () => {


            let httpRequestBag;
            
            if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                httpRequestBag = new XMLHttpRequest();
            } else if (window.ActiveXObject) { // IE 6 and older
                httpRequestBag = new ActiveXObject("Microsoft.XMLHTTP");
            }
           
            
            var bagcocl = document.querySelector("#codeticketbagesc").value;
            var baggid = document.querySelector("#codebaggidesc").value;
            var bagsgid = document.querySelector("#codebagsousgidesc").value;
            httpRequestBag.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/codeclientverifesc/${bagcocl}/${baggid}/${bagsgid}`, true);
            httpRequestBag.onload = () => {

                const donneesbag = JSON.parse(httpRequestBag.responseText);
                
                if (donneesbag == null) {
                    
                        document.querySelector('#pascontactbagsansescbg').value = '';
                        document.querySelector('#rclientcpescalbag').value = '';
                        document.querySelector('#nclientcpescalbag').value = '';
                        document.querySelector('#prnclientcpescalbag').value = '';
                        document.querySelector('#id_lgeheurescalbag').value = '';
                        document.querySelector('#codtickbagsansesc').value = '';
                        document.querySelector('#idcompagaescbag').value = '';
                        document.querySelector('#lignescalbag').value = '';
                        document.querySelector('#quartpasseesc').value = '';
                        document.querySelector('#infobagasansesc').value = '';
                } else
                {

                
                    if (Object.entries(donneesbag).length >= 1){

                    rclientcpescalbag
                        document.querySelector('#pascontactbagsansescbg').value = `${donneesbag.contact_client}`;
                        document.querySelector('#rclientcpescalbag').value = `${donneesbag.clientescal}`;
                        document.querySelector('#nclientcpescalbag').value = `${donneesbag.nom_client}`;
                        document.querySelector('#prnclientcpescalbag').value = `${donneesbag.prenom_client}`;
                        document.querySelector('#id_lgeheurescalbag').value = `${donneesbag.id_ligneheure}`;
                        document.querySelector('#codtickbagsansesc').value = `${donneesbag.idclescal}`;
                        document.querySelector('#idcompagaescbag').value = `${donneesbag.id_compaga}`;
                        document.querySelector('#lignescalbag').value = `${donneesbag.ident_ligne}`;
                        document.querySelector('#quartpasseesc').value = `${donneesbag.quartier_escal}`;
                        document.querySelector('#infobagasansesc').value = `${donneesbag.nom_client} ${donneesbag.prenom_client}  ${donneesbag.nom_gadest}  ${donneesbag.quartier_escal} ${donneesbag.heure}`;
                    } 
                }
            };
            httpRequestBag.setRequestHeader('Content-Type', 'application/json');
            httpRequestBag.send();
        };
        
        updateContenu = function () 
        {
            // Récupérer le champ "Contenu"
            var contenuField = document.querySelector('textarea[name="naturebagagesansesc"]');
            
            // Récupérer toutes les cases à cocher (checkbox)
            var checkboxes = document.querySelectorAll('input[name="types_bagsansesc[]"]:checked');
            
            // Créer un tableau pour stocker les valeurs des cases cochées
            var selectedValues = [];
            
            // Parcourir les cases cochées et récupérer leur valeur
            checkboxes.forEach(function(checkbox) {
                selectedValues.push(checkbox.value);
            });
            
            // Mettre à jour le contenu du champ avec les cases sélectionnées
            contenuField.value = selectedValues.join(', '); // Séparer par des virgules
        }
        e.onclick = function () {   
            let bagsansForm = document.querySelector('#escalFormbag');
            
            bagsansForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/savebagesc/${e.dataset.cle_compagnie}`);   
        }
        
        var clique = true;

            $('#bottonbagesc').click(function(event) 
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
/* --- adcourescale.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adcourescale').forEach(function (e) 
    {       

            let arcour = document.querySelector('#arrscouresc');
            if (arcour !== null)
            arcour.onchange = () => {
                document.querySelector('#date_depheurecourexesc').value = '';
                document.querySelector('#hdepcouresc').options.length = 1;
                document.querySelector('#quartiercouresc').options.length = 1;
                document.querySelector('#statenvoiesc').value = 'nonpatterns';
                const garedepartcour = document.querySelector('#deparcouresc').value;
                const garearriv = document.querySelector('#arrscouresc').value;
                var post_ar = garearriv.split('/');
                var seltar = post_ar[0];
                var sougidarr = post_ar[1];
                let httptypequart;
                httptypequart = new XMLHttpRequest();
                
                httptypequart.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${seltar}`, true);
                httptypequart.onload = () => 
                {
                    const courqua = JSON.parse(httptypequart.responseText);
                    if (courqua == '') {
                        document.querySelector('#quartiercouresc').options.length = 1;
                    }
                    else{
                        if (Object.entries(courqua).length >= 1) {
                                        
                            for (let key in Object.entries(courqua)) {
                                let opt = document.createElement('option');
                                opt.value = `${courqua[key].code_quart}/${courqua[key].nom_quartier}`;
                                opt.innerHTML = `${courqua[key].nom_quartier}/${courqua[key].code_quart}`;
                                document.querySelector('#quartiercouresc').add(opt);
                            }
                        } else {
                            document.querySelector('#quartiercouresc').options.length = 1;
                        }
                    }
                    

                };
                httptypequart.setRequestHeader('Content-Type', 'application/json');
                httptypequart.send();
            };
            let dpcourex = document.querySelector('#date_depheurecourexesc');
            if (dpcourex !== null)
               dpcourex.onchange = () => {

                    const dateactuex = document.querySelector('#dateactesc').value;
                    const progdepartex = document.querySelector('#date_depheurecourexesc').value;
                    document.querySelector('#hdepcouresc').options.length = 1;
                    if(progdepartex >= dateactuex)
                    {
                        
                            const garearrive1 = document.querySelector('#arrscouresc').value;
                            const garedepartcour1 = document.querySelector('#deparcouresc').value;
                            const progdepart1 = document.querySelector('#date_depheurecourexesc').value;
                            document.querySelector('#hdepcouresc').options.length = 1;
                            var post_lhdep1 = garedepartcour1.split('/');
                            var seltdep1 = post_lhdep1[0];
                            var sougid1 = post_lhdep1[1];
                            var post_arr1 = garearrive1.split('/');
                            var seltarr1 = post_arr1[0];
                            var sougidar1 = post_arr1[1];
                            
                            let httpRequetesescal;
                            httpRequetesescal = new XMLHttpRequest();
                
                            httpRequetesescal.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheure1/${seltdep1}-${seltarr1}/${progdepart1}`, true);
                            httpRequetesescal.onload = () => {
                                const dataAxeescal = JSON.parse(httpRequetesescal.responseText);
                                
                                    if (dataAxeescal == '') {
                                        
                                        document.querySelector('#smsdtcresc').style.display = 'none';
                                        document.querySelector('#date_depheurecourexesc').style.color = "black";
                                        document.querySelector('#date_depheurecourexesc').style.border = "1px solid";
                                        
                                    } 
                                    else 
                                    {
                                        document.querySelector('#smsdtcresc').style.display = 'none';
                                        document.querySelector('#date_depheurecourexesc').style.color = "black";
                                        document.querySelector('#date_depheurecourexesc').style.border = "1px solid";
                                        if (Object.entries(dataAxeescal).length >= 1) 
                                        {
                                            for (let key in Object.entries(dataAxeescal)) {
                                                    let opt = document.createElement('option');
                                                    opt.value = `${dataAxeescal[key].id_ligneheure}`;
                                                    opt.innerHTML = `${dataAxeescal[key].heure}`;
                                                    document.querySelector('#hdepcouresc').add(opt);
                                                }
                                        } else {
                                            document.querySelector('#hdepcouresc').options.length = 1;
                                        }
                                    }
                                                                               
                            };
                            httpRequetesescal.setRequestHeader('Content-Type', 'application/json');
                            httpRequetesescal.send();
                    }
                    else
                    {
                        document.querySelector('#date_depheurecourexesc').style.color = "#FF0000";
                        document.querySelector('#date_depheurecourexesc').style.border = "2px solid #FF0000";
                        document.querySelector('#smsdtcresc').style.display = 'block';
                        document.querySelector('#erreurSmsdtcresc').innerHTML = `Date non valide.`;
                    }
                };

                
               
               let typers = document.querySelector('#type_personesc');
                if (typers !== null)
                typers.onchange = () => {

                    var typersopers = document.querySelector('#type_personesc').
                        options[document.querySelector('#type_personesc').options.selectedIndex].value;
                        var typerso1pers = typersopers.split('/');
                        var typerso2pers = typerso1pers[0];
                        var typerso3pers = typerso1pers[1];

                        document.querySelector('#types_courriersesc').options.length = 1;
                        document.querySelector('#exp_nomesc').value = "";
                        document.querySelector('#exp_prenomesc').value = "";
                        document.querySelector('#cnib_expesc').value = "";
                        document.querySelector('#iddate_cnibesc').value = "";
                        document.querySelector('#lieudelexpesc').value = "";
                        document.querySelector('#passcompagnieesc').value = "";
                        document.querySelector('#rclientcpexpesc').value = "";
                        document.querySelector('#prnclientcpexpesc').value = "";
                        document.querySelector('#cnibcpexpesc').value = "";
                        document.querySelector('#date_cnibcpexpesc').value = "";
                        document.querySelector('#lieudelivrecpexpesc').value = "";
                        document.querySelector('#idclientypeexpesc').value = "";
                        

                            let httpRequesttespersos1;
                            httpRequesttespersos1 = new XMLHttpRequest();

                                
                            httpRequesttespersos1.open('GET', window.location.origin + `${APP_ROOT}/confirmation/fetch_typecourriers`, true);
                            httpRequesttespersos1.onload = () => {
                                const datapersos1 = JSON.parse(httpRequesttespersos1.responseText);
                                     if (Object.entries(datapersos1).length >= 1) {
                                   
                                        for (let key in Object.entries(datapersos1)) {
                                            let opt = document.createElement('option');
                                            opt.value = `${datapersos1[key].id_cat}/${datapersos1[key].categ}/${datapersos1[key].indicatif}`;
                                            opt.innerHTML = `${datapersos1[key].categ}`;
                                            document.querySelector('#types_courriersesc').add(opt);
                                        }
                                    } else {
                                        document.querySelector('#types_courriersesc').options.length = 1;
                                    }
                            };
                    
                            httpRequesttespersos1.setRequestHeader('Content-Type', 'application/json');
                            httpRequesttespersos1.send();
                };

                
                let inf = document.querySelector('#exp_contactesc');
                if (inf !== null)
                inf.onkeyup = () => {
                    let httpInfos;
                    if (window.XMLHttpRequest) {
                        httpInfos = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfos = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var verificat = document.querySelector('#exp_contactesc').value;
                    
                    httpInfos.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfos/${verificat}`, true);
                    httpInfos.onload = () => {
                        const infos = JSON.parse(httpInfos.responseText);
                        if (infos == null) {
                            document.querySelector('#exp_nomesc').value = "";
                            document.querySelector('#exp_prenomesc').value = "";
                            document.querySelector('#cnib_expesc').value = "";
                            document.querySelector('#iddate_cnibesc').value = "";
                            document.querySelector('#lieudelexpesc').value = "";
                            document.querySelector('#passcompagnieesc').value = "";
                            document.querySelector('#rclientcpexpesc').value = "";
                            document.querySelector('#prnclientcpexpesc').value = "";
                            document.querySelector('#cnibcpexpesc').value = "";
                            document.querySelector('#date_cnibcpexpesc').value = "";
                            document.querySelector('#lieudelivrecpexpesc').value = "";
                            document.querySelector('#idclientypeexpesc').value = "";
                          
                        } else 
                        {
                            if (Object.entries(infos).length > 1) {
                                
                                if (infos.contact_client == verificat) {
                                    document.querySelector('#exp_nomesc').value = `${infos.nom_client}`;
                                    document.querySelector('#exp_prenomesc').value = `${infos.prenom_client}`;
                                    document.querySelector('#cnib_expesc').value = `${infos.num_CNIB}`;
                                    document.querySelector('#iddate_cnibesc').value = `${infos.date_delivre}`;
                                    document.querySelector('#lieudelexpesc').value = `${infos.lieu_delivre}`;
                                    document.querySelector('#passcompagnieesc').value = `${infos.id_client}`;
                                    document.querySelector('#rclientcpexpesc').value = `${infos.nom_client}`;
                                    document.querySelector('#prnclientcpexpesc').value = `${infos.prenom_client}`;
                                    document.querySelector('#cnibcpexpesc').value = `${infos.num_CNIB}`;
                                    document.querySelector('#date_cnibcpexpesc').value = `${infos.date_delivre}`;
                                    document.querySelector('#lieudelivrecpexpesc').value = `${infos.lieu_delivre}`;
                                    document.querySelector('#idclientypeexpesc').value = `${infos.type_client}`;
                          
                                } else {
                                    document.querySelector('#exp_nomesc').value = "";
                                    document.querySelector('#exp_prenomesc').value = "";
                                    document.querySelector('#cnib_expesc').value = "";
                                    document.querySelector('#iddate_cnibesc').value = "";
                                    document.querySelector('#lieudelexpesc').value = "";
                                    document.querySelector('#passcompagnieesc').value = "";
                                    document.querySelector('#rclientcpexpesc').value = "";
                                    document.querySelector('#prnclientcpexpesc').value = "";
                                    document.querySelector('#cnibcpexpesc').value = "";
                                    document.querySelector('#date_cnibcpexpesc').value = "";
                                    document.querySelector('#lieudelivrecpexpesc').value = "";
                                    document.querySelector('#idclientypeexpesc').value = "";
                          
                                }
                            }
                        }
                    };
                    httpInfos.setRequestHeader('Content-Type', 'application/json');
                    httpInfos.send();
                };
                

                let infopersos = document.querySelector('#idtypeesc');
                if (infopersos !== null) 
                infopersos.onchange = () => 
                {
                    document.querySelector('#contactidesc').style.display = 'none';
                    document.querySelector('#idcontesc').style.display = 'none';
                    document.querySelector('#sonnelesc').style.display = 'none';
                    document.querySelector('#idsonnelsesc').style.display = 'none';
                    document.querySelector('#idpartesesc').options.length = 1;
                    document.querySelector('#membrepartoidesc').options.length = 1;
                    document.querySelector('#contactidesc').value = '';
                    document.querySelector('#membrepartoesc').style.display = 'none';
                    document.querySelector('#membrepartoidesc').style.display = 'none';
                           
                    var personns = document.querySelector('#idtypeesc')
                        .options[document.querySelector('#idtypeesc').options.selectedIndex].value;
                        if(personns === 'personnel')
                        {
                    
                            document.querySelector('#sonnelesc').style.display = 'block';
                            document.querySelector('#idsonnelsesc').style.display = 'block';
                            document.querySelector('#contactidesc').style.display = 'none';
                            document.querySelector('#idcontesc').style.display = 'none';
                            document.querySelector('#partcontesc').style.display = 'none';
                            document.querySelector('#idpartesesc').style.display = 'none';
                            document.querySelector('#membrepartoesc').style.display = 'none';
                            document.querySelector('#membrepartoidesc').style.display = 'none';
                                
                                    let httppersosdest;
                            if (window.XMLHttpRequest) {
                                httppersosdest = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httppersosdest = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                            httppersosdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectperso/${personns}`, true);
                            httppersosdest.onload = () => 
                            {

                                const infospersdest = JSON.parse(httppersosdest.responseText);

                                if (Object.entries(infospersdest).length >= 1) 
                                {


                                    for (let key in Object.entries(infospersdest))
                                    {

                                        let opt = document.createElement('option');
                                        opt.value = `${infospersdest[key].matricule}`;
                                        opt.innerHTML = `${infospersdest[key].nomprenom_perso}`;
                                        document.querySelector('#idsonnelsesc').add(opt);
                                    }
                                 
                                }
                                else 
                                {
                                    document.querySelector('#idsonnelsesc').options.length = 1;
                                }

                            };
                            httppersosdest.setRequestHeader('Content-Type', 'application/json');
                            httppersosdest.send();

                            let infopersosdest = document.querySelector('#idsonnels');
        
                            if (infopersosdest !== null) 
                            infopersosdest.onchange = () => 
                            {

                            
                                let httpInfospersdest;
                                if (window.XMLHttpRequest) {
                                    httpInfospersdest = new XMLHttpRequest();
                                } else if (window.ActiveXObject) {
                                    httpInfospersdest = new ActiveXObject("Microsoft.XMLHTTP");
                                }

                                document.querySelector('#contactidesc').style.display = 'none';
                                document.querySelector('#idcontesc').style.display = 'none';
                                document.querySelector('#compagniepassdestesc').value = '';
                                var idverifidest = document.querySelector('#idsonnelsesc').options[document.querySelector('#idsonnelsesc').options.selectedIndex].value;
                    
                                httpInfospersdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoperso/${idverifidest}`, true);
                                httpInfospersdest.onload = () => {
                                    const infosperdest = JSON.parse(httpInfospersdest.responseText);
                                    
                                    if (Object.entries(infosperdest).length >= 1) {
                                        
                               
                                        var typepersosdest = `${infosperdest.nomprenom_perso}`;
                                        var typer1persosdest = typepersosdest.split(' ');
                                        var typer2persosdest = typer1persosdest[0];
                                        var typer3persosdest = typer1persosdest[1];
                                        var typer4persosdest = typer1persosdest[2];
                                        if(typer4persosdest === undefined){
                                            var typer5persosdest = `${typer3persosdest}`;
                                        }
                                        else
                                        {
                                            var typer5persosdest = `${typer3persosdest} ${typer4persosdest}`;
                                        }
                                        document.querySelector('#nomdestidesc').value = `${typer2persosdest}`;
                                        document.querySelector('#prenomdestidesc').value = `${typer5persosdest}`;
                                        document.querySelector('#persodestcompagnieesc').value = `${infosperdest.matricule}`;
                                        document.querySelector('#rclientcpexpesc').value = `${typer2persosdest}`;
                                        document.querySelector('#prnclientcpexpesc').value = `${typer5persosdest}`;
                                        document.querySelector('#idclientypedestesc').value = 'personnel';
                                        
                                    } 
                                    else
                                    {
                                        document.querySelector('#nomdestidesc').value = "";
                                        document.querySelector('#prenomdestidesc').value = "";
                                        document.querySelector('#persodestcompagnieesc').value = "";
                                        document.querySelector('#rclientcpdestesc').value = "";
                                        document.querySelector('#prnclientcpdestesc').value = "";
                                        document.querySelector('#idclientypedestesc').value = "";
                                    }
                                                                
                                };
                                httpInfospersdest.setRequestHeader('Content-Type', 'application/json');
                                httpInfospersdest.send();
                            };
                        }
                        else
                        {
                            document.querySelector('#membrepartoesc').style.display = 'none';
                            document.querySelector('#membrepartoidesc').style.display = 'none';
                            document.querySelector('#sonnelesc').style.display = 'none';
                            document.querySelector('#idsonnelsesc').style.display = 'none';
                            document.querySelector('#partcontesc').style.display = 'none';
                            document.querySelector('#idpartesesc').style.display = 'none';
                            document.querySelector('#idcontesc').style.display = 'block';
                            document.querySelector('#contactidesc').style.display = 'block';
                            document.querySelector('#nomdestidesc').value = "";
                            document.querySelector('#prenomdestidesc').value = "";
                            document.querySelector('#compagniepassdestesc').value = "";
                            document.querySelector('#rclientcpdestesc').value = "";
                            document.querySelector('#prnclientcpdestesc').value = "";
                            document.querySelector('#idclientypedestesc').value = "";
                            let infdest = document.querySelector('#contactidesc');
                            if (infdest !== null)
                                infdest.onkeyup = () => {
                                    let httpInfosdest;
                                    if (window.XMLHttpRequest) {
                                        httpInfosdest = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) {
                                        httpInfosdest = new ActiveXObject("Microsoft.XMLHTTP");
                                    }

                                    document.querySelector('#nomdestidesc').value = "";
                                    document.querySelector('#prenomdestidesc').value = "";
                                    document.querySelector('#compagniepassdestesc').value = "";
                                    document.querySelector('#rclientcpdestesc').value = "";
                                    document.querySelector('#prnclientcpdestesc').value = "";
                                    document.querySelector('#idclientypedestesc').value = "";
                                    var verificatdest = document.querySelector('#contactidesc').value;
                                    document.querySelector('#persodestcompagnieesc').value = "";

                                    httpInfosdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfos/${verificatdest}`, true);
                                    httpInfosdest.onload = () => {
                                        const infosdest = JSON.parse(httpInfosdest.responseText);
                                        if (infosdest == null) {
                                            document.querySelector('#nomdestidesc').value = "";
                                            document.querySelector('#prenomdestidesc').value = "";
                                            document.querySelector('#compagniepassdestesc').value = "";
                                            document.querySelector('#rclientcpdestesc').value = "";
                                            document.querySelector('#prnclientcpdestesc').value = "";
                                            document.querySelector('#idclientypedestesc').value = "";
                                            document.querySelector('#date_cnibdestidesc').value = "";
                                            
                                        } else 
                                        {
                                            if (Object.entries(infosdest).length > 1) {
                                                
                                                if (infosdest.contact_client == verificatdest) {
                                                    document.querySelector('#nomdestidesc').value = `${infosdest.nom_client}`;
                                                    document.querySelector('#prenomdestidesc').value = `${infosdest.prenom_client}`;
                                                    document.querySelector('#compagniepassdestesc').value = `${infosdest.id_client}`;
                                                    document.querySelector('#idclientypedestesc').value = `${infosdest.type_client}`;
                                                    document.querySelector('#rclientcpdestesc').value = `${infosdest.nom_client}`;
                                                    document.querySelector('#prnclientcpdestesc').value = `${infosdest.prenom_client}`;
                                                    document.querySelector('#date_cnibdestidesc').value = `${infosdest.date_delivre}`;
                                                    
                                                } else {
                                                    document.querySelector('#nomdestidesc').value = "";
                                                    document.querySelector('#prenomdestidesc').value = "";
                                                    document.querySelector('#compagniepassdestesc').value = "";
                                                    document.querySelector('#rclientcpdestesc').value = "";
                                                    document.querySelector('#prnclientcpdestesc').value = "";
                                                    document.querySelector('#idclientypedestesc').value = "";
                                                    document.querySelector('#date_cnibdestidesc').value = "";
                                                    
                                                }
                                            }
                                        }
                                    };
                                    httpInfosdest.setRequestHeader('Content-Type', 'application/json');
                                    httpInfosdest.send();
                                };
                        }
                        if(personns === 'membre'){

                                document.querySelector('#membrepartoesc').style.display = 'block';
                                document.querySelector('#membrepartoidesc').style.display = 'block';
                                document.querySelector('#sonnelesc').style.display = 'none';
                                document.querySelector('#idsonnelsesc').style.display = 'none';
                                document.querySelector('#idcontesc').style.display = 'none';
                                document.querySelector('#contactidesc').style.display = 'none';
                                document.querySelector('#partcontesc').style.display = 'none';
                                document.querySelector('#idpartesesc').style.display = 'none';
                                
                                
                        
                                let httppaternesdestm;
                                    if (window.XMLHttpRequest) {
                                        httppaternesdestm = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) {
                                        httppaternesdestm = new ActiveXObject("Microsoft.XMLHTTP");
                                    }
                                    
                                    httppaternesdestm.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectpartenaire/${personns}`, true);
                                    httppaternesdestm.onload = () => {
                                        const infospartenedestm = JSON.parse(httppaternesdestm.responseText);

                                        if (Object.entries(infospartenedestm).length >= 1) 
                                        {

                                            for (let key in Object.entries(infospartenedestm))
                                            {

                                                let opt = document.createElement('option');
                                                opt.value = `${infospartenedestm[key].id_client}`;
                                                opt.innerHTML = `${infospartenedestm[key].nom_client} ${infospartenedestm[key].prenom_client}`;
                                                document.querySelector('#membrepartoidesc').add(opt);
                                            }
                                                
                                        }
                                        else 
                                        {
                                            document.querySelector('#membrepartoidesc').options.length = 1;
                                        }

                                    };
                                    httppaternesdestm.setRequestHeader('Content-Type', 'application/json');
                                    httppaternesdestm.send();

                                let paternstscdestin2m = document.querySelector('#membrepartoidesc');
                                if (paternstscdestin2m !== null)
                                paternstscdestin2m.onchange = () => {
                                    let httpInfospersdestin2m;
                                        httpInfospersdestin2m = new XMLHttpRequest();
                                    document.querySelector('#persodestcompagnieesc').value = '';
                                    document.querySelector('#contactidesc').style.display = 'none';
                                    document.querySelector('#idcontesc').style.display = 'none';
                                    document.querySelector('#contactidesc').value = '';
                                        var ternsdest2m = document.querySelector('#membrepartoidesc').
                                            options[document.querySelector('#membrepartoidesc').options.selectedIndex].value;
                                        httpInfospersdestin2m.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest2m}`, true);
                                    httpInfospersdestin2m.onload = () => {
                                        const infosperdestin2m = JSON.parse(httpInfospersdestin2m.responseText);
                                        
                                        if (Object.entries(infosperdestin2m).length >= 1) {
                                            
                                   
                                            
                                            document.querySelector('#nomdestidesc').value = `${infosperdestin2m.nom_client}`;
                                            document.querySelector('#prenomdestidesc').value = `${infosperdestin2m.prenom_client}`;
                                            document.querySelector('#compagniepassdestesc').value = `${infosperdestin2m.id_client}`;
                                            document.querySelector('#idclientypedestesc').value = `${infosperdestin2m.type_client}`;
                                            document.querySelector('#rclientcpdestesc').value = `${infosperdestin2m.nom_client}`;
                                            document.querySelector('#prnclientcpdestesc').value = `${infosperdestin2m.prenom_client}`;
                                            document.querySelector('#date_cnibdestidesc').value = `${infosperdestin2m.date_delivre}`;
                                        } 
                                        else
                                        {
                                            document.querySelector('#nomdestidesc').value = "";
                                            document.querySelector('#prenomdestidesc').value = "";
                                            document.querySelector('#compagniepassdestesc').value = "";
                                            document.querySelector('#rclientcpdestesc').value = "";
                                            document.querySelector('#prnclientcpdestesc').value = "";
                                            document.querySelector('#idclientypedestesc').value = "";
                                            document.querySelector('#date_cnibdestidesc').value = "";
                                        }
                                                                    
                                    };
                                    httpInfospersdestin2m.setRequestHeader('Content-Type', 'application/json');
                                    httpInfospersdestin2m.send();
                                };
                   
                            
                        }

                        if(personns === 'partenaire_client' || personns === 'partenaire_simple'){
                            document.querySelector('#partcontesc').style.display = 'block';
                            document.querySelector('#idpartesesc').style.display = 'block';
                            document.querySelector('#sonnelesc').style.display = 'none';
                            document.querySelector('#idsonnelsesc').style.display = 'none';
                            document.querySelector('#contactidesc').style.display = 'none';
                            document.querySelector('#idcontesc').style.display = 'none';
                            document.querySelector('#nomdestidesc').value = '';
                            document.querySelector('#prenomdestidesc').value = '';
                            document.querySelector('#compagniepassdestesc').value = '';
                            document.querySelector('#idclientypedestesc').value = '';
                            document.querySelector('#rclientcpdestesc').value = '';
                            document.querySelector('#prnclientcpdestesc').value = '';
                            document.querySelector('#contactidesc').value = '';
                            document.querySelector('#membrepartoesc').style.display = 'none';
                            document.querySelector('#membrepartoidesc').style.display = 'none';
                            let httppaternsdest;
                                if (window.XMLHttpRequest) {
                                    httppaternsdest = new XMLHttpRequest();
                                } else if (window.ActiveXObject) {
                                    httppaternsdest = new ActiveXObject("Microsoft.XMLHTTP");
                                }
                                
                                httppaternsdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectpartenaire/${personns}`, true);
                                httppaternsdest.onload = () => {
                                    const infospartendest = JSON.parse(httppaternsdest.responseText);

                                    if (Object.entries(infospartendest).length >= 1) 
                                    {

                                        for (let key in Object.entries(infospartendest))
                                        {

                                            let opt = document.createElement('option');
                                            opt.value = `${infospartendest[key].id_client}`;
                                            opt.innerHTML = `${infospartendest[key].nom_client} ${infospartendest[key].prenom_client}`;
                                            document.querySelector('#idpartesesc').add(opt);
                                        }
                                            
                                    }
                                    else 
                                    {
                                        document.querySelector('#idpartesesc').options.length = 1;
                                    }

                                };
                                httppaternsdest.setRequestHeader('Content-Type', 'application/json');
                                httppaternsdest.send();

                                let paternstscdestin = document.querySelector('#idpartesesc');
                            if (paternstscdestin !== null)
                            paternstscdestin.onchange = () => {
                                let httpInfospersdestin;
                                    httpInfospersdestin = new XMLHttpRequest();
                                document.querySelector('#persodestcompagnieesc').value = '';
                                document.querySelector('#contactidesc').style.display = 'none';
                                document.querySelector('#idcontesc').style.display = 'none';
                                document.querySelector('#contactidesc').value = '';
                                var ternsdest= document.querySelector('#idpartesesc').
                                    options[document.querySelector('#idpartesesc').options.selectedIndex].value;
                                httpInfospersdestin.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest}`, true);
                                httpInfospersdestin.onload = () => {
                                    const infosperdestin = JSON.parse(httpInfospersdestin.responseText);
                                    
                                    if (Object.entries(infosperdestin).length >= 1) {
                                        
                               
                                        
                                        document.querySelector('#nomdestidesc').value = `${infosperdestin.nom_client}`;
                                        document.querySelector('#prenomdestidesc').value = `${infosperdestin.prenom_client}`;
                                        document.querySelector('#compagniepassdestesc').value = `${infosperdestin.id_client}`;
                                        document.querySelector('#idclientypedestesc').value = `${infosperdestin.type_client}`;
                                        document.querySelector('#rclientcpdestesc').value = `${infosperdestin.nom_client}`;
                                        document.querySelector('#prnclientcpdestesc').value = `${infosperdestin.prenom_client}`;
                                        document.querySelector('#date_cnibdestidesc').value = `${infosperdestin.date_delivre}`;
                                        
                                    } 
                                    else
                                    {
                                        document.querySelector('#nomdestidesc').value = "";
                                        document.querySelector('#prenomdestidesc').value = "";
                                        document.querySelector('#compagniepassdestesc').value = "";
                                        document.querySelector('#rclientcpdestesc').value = "";
                                        document.querySelector('#prnclientcpdestesc').value = "";
                                        document.querySelector('#idclientypedestesc').value = "";
                                        document.querySelector('#date_cnibdestidesc').value = "";
                                    }
                                                                
                                };
                                httpInfospersdestin.setRequestHeader('Content-Type', 'application/json');
                                httpInfospersdestin.send();
                            };
 
                        }
                        else
                        {


                            if(personns === 'partenaire_specifique'){

                                document.querySelector('#partcontesc').style.display = 'block';
                                document.querySelector('#idpartesesc').style.display = 'block';
                                document.querySelector('#sonnelesc').style.display = 'none';
                                document.querySelector('#idsonnelsesc').style.display = 'none';
                                document.querySelector('#idcontesc').style.display = 'none';
                                document.querySelector('#contactidesc').style.display = 'none';
                                document.querySelector('#membrepartoesc').style.display = 'none';
                                document.querySelector('#membrepartoidesc').style.display = 'none';
                                
                        
                                let httppaternesdest;
                                    if (window.XMLHttpRequest) {
                                        httppaternesdest = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) {
                                        httppaternesdest = new ActiveXObject("Microsoft.XMLHTTP");
                                    }
                                    
                                    httppaternesdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectpartenaire/${personns}`, true);
                                    httppaternesdest.onload = () => {
                                        const infospartenedest = JSON.parse(httppaternesdest.responseText);

                                        if (Object.entries(infospartenedest).length >= 1) 
                                        {

                                            for (let key in Object.entries(infospartenedest))
                                            {

                                                let opt = document.createElement('option');
                                                opt.value = `${infospartenedest[key].id_client}`;
                                                opt.innerHTML = `${infospartenedest[key].nom_client} ${infospartenedest[key].prenom_client}`;
                                                document.querySelector('#idpartesesc').add(opt);
                                            }
                                                
                                        }
                                        else 
                                        {
                                            document.querySelector('#idpartesesc').options.length = 1;
                                        }

                                    };
                                    httppaternesdest.setRequestHeader('Content-Type', 'application/json');
                                    httppaternesdest.send();

                                let paternstscdestin2 = document.querySelector('#idpartesesc');
                                if (paternstscdestin2 !== null)
                                paternstscdestin2.onchange = () => {
                                    let httpInfospersdestin2;
                                        httpInfospersdestin2 = new XMLHttpRequest();
                                    document.querySelector('#persodestcompagnieesc').value = '';
                                    document.querySelector('#contactidesc').style.display = 'none';
                                    document.querySelector('#idcontesc').style.display = 'none';
                                    document.querySelector('#contactidesc').value = '';
                                        var ternsdest2 = document.querySelector('#idpartesesc').
                                            options[document.querySelector('#idpartesesc').options.selectedIndex].value;
                                        httpInfospersdestin2.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest2}`, true);
                                    httpInfospersdestin2.onload = () => {
                                        const infosperdestin2 = JSON.parse(httpInfospersdestin2.responseText);
                                        
                                        if (Object.entries(infosperdestin2).length >= 1) {
                                            
                                   
                                            
                                            document.querySelector('#nomdestidesc').value = `${infosperdestin2.nom_client}`;
                                            document.querySelector('#prenomdestidesc').value = `${infosperdestin2.prenom_client}`;
                                            document.querySelector('#compagniepassdestesc').value = `${infosperdestin2.id_client}`;
                                            document.querySelector('#idclientypedestesc').value = `${infosperdestin2.type_client}`;
                                            document.querySelector('#rclientcpdestesc').value = `${infosperdestin2.nom_client}`;
                                            document.querySelector('#prnclientcpdestesc').value = `${infosperdestin2.prenom_client}`;
                                            document.querySelector('#date_cnibdestidesc').value = `${httpInfospersdestin2.date_delivre}`;
                                        } 
                                        else
                                        {
                                            document.querySelector('#nomdestidesc').value = "";
                                            document.querySelector('#prenomdestidesc').value = "";
                                            document.querySelector('#compagniepassdestesc').value = "";
                                            document.querySelector('#rclientcpdestesc').value = "";
                                            document.querySelector('#prnclientcpdestesc').value = "";
                                            document.querySelector('#idclientypedestesc').value = "";
                                            document.querySelector('#date_cnibdestidesc').value = "";
                                        }
                                                                    
                                    };
                                    httpInfospersdestin2.setRequestHeader('Content-Type', 'application/json');
                                    httpInfospersdestin2.send();
                                };
                   
                            
                            }
                            
                        }
                        
                }
        e.onclick = function () {
            let coordForm = document.querySelector('#coordFormesc');
            coordForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/addordesc/${e.dataset.cle_compagnie}`);
        }

            var clique = true;

            $('#bottonesc').click(function(event) 
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
/* --- adpartcoursescale.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adpartcoursescale').forEach(function (e) 
    {       

            let arcour = document.querySelector('#arrscourpartoesc');
            if (arcour !== null)
            arcour.onchange = () => {
                document.querySelector('#date_depheurecourexpartoesc').value = '';
                document.querySelector('#hdepcourpartoesc').options.length = 1;
                document.querySelector('#quartiercourpartoesc').options.length = 1;
                const garedepartcour = document.querySelector('#deparcourpartoesc').value;
                const garearriv = document.querySelector('#arrscourpartoesc').value;
                var post_ar = garearriv.split('/');
                var seltar = post_ar[0];
                var sougidarr = post_ar[1];
                let httptypequart;
                httptypequart = new XMLHttpRequest();
                
                httptypequart.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${seltar}`, true);
                httptypequart.onload = () => 
                {
                    const courqua = JSON.parse(httptypequart.responseText);
                    if (courqua == '') {
                        document.querySelector('#quartiercourpartoesc').options.length = 1;
                    }
                    else{
                        if (Object.entries(courqua).length >= 1) {
                                        
                            for (let key in Object.entries(courqua)) {
                                let opt = document.createElement('option');
                                opt.value = `${courqua[key].code_quart}/${courqua[key].nom_quartier}`;
                                opt.innerHTML = `${courqua[key].nom_quartier}/${courqua[key].code_quart}`;
                                document.querySelector('#quartiercourpartoesc').add(opt);
                            }
                        } else {
                            document.querySelector('#quartiercourpartoesc').options.length = 1;
                        }
                    }
                    

                };
                httptypequart.setRequestHeader('Content-Type', 'application/json');
                httptypequart.send();
            };
            let dpcourex = document.querySelector('#date_depheurecourexpartoesc');
            if (dpcourex !== null)
               dpcourex.onchange = () => {

                    const dateactuex = document.querySelector('#dateactpartoesc').value;
                    const garearriveex = document.querySelector('#arrscourpartoesc').value;
                    const progdepartex = document.querySelector('#date_depheurecourexpartoesc').value;
                    const garedepartcourex = document.querySelector('#deparcourpartoesc').value;
                    document.querySelector('#hdepcourpartoesc').options.length = 1;
                    var post_lhdepex = garedepartcourex.split('/');
                    var seltdepex = post_lhdepex[0];
                    var sougidex = post_lhdepex[1];
                    var post_arrex = garearriveex.split('/');
                    var seltarrex = post_arrex[0];
                    var sougidarex = post_arrex[1];
                    if(progdepartex >= dateactuex)
                    {

                            let httpRequtcourex;

                                if (window.XMLHttpRequest) {
                                    httpRequtcourex = new XMLHttpRequest();
                                } else if (window.ActiveXObject) {
                                    httpRequtcourex = new ActiveXObject("Microsoft.XMLHTTP");
                                }

                            const reponsecourex = httpRequtcourex.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheure1/${seltdepex}-${seltarrex}/${progdepartex}`, true);
                            httpRequtcourex.onload = () => 
                            {
                                const infoscourex = JSON.parse(httpRequtcourex.responseText);
                                    document.querySelector('#smsdtcrpartoesc').style.display = 'none';
                                    document.querySelector('#date_depheurecourexpartoesc').style.color = "black";
                                    document.querySelector('#date_depheurecourexpartoesc').style.border = "1px solid"; 

                                if(infoscourex == '')
                                {
                                

                                }
                                else
                                {
                                    if (Object.entries(infoscourex).length >= 1) {
                                   
                                        for (let key in Object.entries(infoscourex)) {
                                            let opt = document.createElement('option');
                                            opt.value = `${infoscourex[key].id_ligneheure}`;
                                            opt.innerHTML = `${infoscourex[key].heure}`;
                                            document.querySelector('#hdepcourpartoesc').add(opt);
                                        }
                                    } else {
                                        document.querySelector('#hdepcourpartoesc').options.length = 1;
                                    }
                                } 
                                
                            };
                            httpRequtcourex.setRequestHeader('Content-Type', 'application/json');
                            httpRequtcourex.send();
                
                    }
                    else
                    {
                        document.querySelector('#date_depheurecourexpartoesc').style.color = "#FF0000";
                        document.querySelector('#date_depheurecourexpartoesc').style.border = "2px solid #FF0000";
                        document.querySelector('#smsdtcrpartoesc').style.display = 'block';
                        document.querySelector('#erreurSmsdtcrpartoesc').innerHTML = `Date non valide.`;
                    }
                };

               let typers = document.querySelector('#type_personpartoesc');
                if (typers !== null)
                typers.onchange = () => {

                    var typersopers = document.querySelector('#type_personpartoesc').
                        options[document.querySelector('#type_personpartoesc').options.selectedIndex].value;
                        var typerso1pers = typersopers.split('/');
                        var typerso2pers = typerso1pers[0];
                        var typerso3pers = typerso1pers[1];

                        document.querySelector('#types_courrierspartoesc').options.length = 1;
                        document.querySelector('#partenairespartoesc').options.length = 1;      
                        document.querySelector('#idvalepartoesc').style.display = 'block';
                        document.querySelector('#valeur1partoesc').style.display = 'block';
                        document.querySelector('#idfraispartoesc').style.display = 'block';
                        document.querySelector('#fraisexpartoesc').style.display = 'block';
                        document.querySelector('#exp_nompartoesc').value = "";
                        document.querySelector('#exp_prenompartoesc').value = "";
                        document.querySelector('#cnib_exppartoesc').value = "";
                        document.querySelector('#iddate_cnibpartoesc').value = "";
                        document.querySelector('#lieudelexppartoesc').value = "";
                        document.querySelector('#passcompagniepartoesc').value = "";
                        document.querySelector('#rclientcpexppartoesc').value = "";
                        document.querySelector('#prnclientcpexppartoesc').value = "";
                        document.querySelector('#cnibcpexppartoesc').value = "";
                        document.querySelector('#date_cnibcpexppartoesc').value = "";
                        document.querySelector('#lieudelivrecpexppartoesc').value = "";
                        document.querySelector('#idclientypeexppartoesc').value = "";
                    
                    if((typerso3pers === 'partenaire_specifique') || (typerso3pers === 'partenaire_client') || (typerso3pers === 'partenaire_simple'))
                    {


                        document.querySelector('#partidpartoesc').style.display = 'block';
                        document.querySelector('#partenairespartoesc').style.display = 'block';
                        document.querySelector('#idvalepartoesc').style.display = 'block';
                        document.querySelector('#valeur1partoesc').style.display = 'block';
                        document.querySelector('#idfraispartoesc').style.display = 'block';
                        document.querySelector('#fraisexpartoesc').style.display = 'block';
                        document.querySelector('#fraisexpartoesc').value = '';
                        
                        let httppaterns;
                            if (window.XMLHttpRequest) {
                                httppaterns = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httppaterns = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                            httppaterns.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectpartenaire/${typerso3pers}`, true);
                            httppaterns.onload = () => {
                                const infosparten = JSON.parse(httppaterns.responseText);

                                if (Object.entries(infosparten).length >= 1) 
                                {

                                    for (let key in Object.entries(infosparten))
                                    {

                                        let opt = document.createElement('option');
                                        opt.value = `${infosparten[key].id_client}`;
                                        opt.innerHTML = `${infosparten[key].nom_client} ${infosparten[key].prenom_client}`;
                                        document.querySelector('#partenairespartoesc').add(opt);
                                    }
                                        
                                }
                                else 
                                {
                                    document.querySelector('#partenairespartoesc').options.length = 1;
                                }

                            };
                            httppaterns.setRequestHeader('Content-Type', 'application/json');
                            httppaterns.send();
                        
                    }
                    
                    
                };
                let paternstscd = document.querySelector('#partenairespartoesc');
                if (paternstscd !== null)
                paternstscd.onchange = () => {
                    let httpRequesttespers;
                        httpRequesttespers = new XMLHttpRequest();
                        document.querySelector('#types_courrierspartoesc').options.length = 1;
                        var terns= document.querySelector('#partenairespartoesc').
                        options[document.querySelector('#partenairespartoesc').options.selectedIndex].value;
                        httpRequesttespers.open('GET', window.location.origin + `${APP_ROOT}/confirmation/fetch_typecourrier/${terns}`, true);
                        httpRequesttespers.onload = () => {
                            const dataperso = JSON.parse(httpRequesttespers.responseText);
                            if(dataperso == ''){
                                
                                let httpRequesttesperso;
                                httpRequesttesperso = new XMLHttpRequest();

                                    
                                httpRequesttesperso.open('GET', window.location.origin + `${APP_ROOT}/confirmation/fetch_typecourriers`, true);
                                httpRequesttesperso.onload = () => {
                                    const datapersos = JSON.parse(httpRequesttesperso.responseText);
                                         if (Object.entries(datapersos).length >= 1) {
                                       
                                            for (let key in Object.entries(datapersos)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${datapersos[key].id_cat}/${datapersos[key].categ}/${datapersos[key].indicatif}`;
                                                opt.innerHTML = `${datapersos[key].categ}`;
                                                document.querySelector('#types_courrierspartoesc').add(opt);
                                            }
                                        } else {
                                            document.querySelector('#types_courrierspartoesc').options.length = 1;
                                        }
                                };
                        
                                httpRequesttesperso.setRequestHeader('Content-Type', 'application/json');
                                httpRequesttesperso.send();
                                        
                            }else
                            {
                                if (Object.entries(dataperso).length >= 1) {
                               
                                    for (let key in Object.entries(dataperso)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${dataperso[key].id_cat}/${dataperso[key].categ}/${dataperso[key].indicatif}`;
                                        opt.innerHTML = `${dataperso[key].categ}`;
                                        document.querySelector('#types_courrierspartoesc').add(opt);
                                    }
                                } else {
                                    document.querySelector('#types_courrierspartoesc').options.length = 1;
                                }
                            }
                        };
                
                        httpRequesttespers.setRequestHeader('Content-Type', 'application/json');
                        httpRequesttespers.send();
                };

                
                let tscd = document.querySelector('#types_courrierspartoesc');
                if (tscd !== null)
                tscd.onchange = () => {
                        let httpRequesttes;
    
                        if (window.XMLHttpRequest) {
                            httpRequesttes = new XMLHttpRequest();
                        } else if (window.ActiveXObject) {
                            httpRequesttes = new ActiveXObject("Microsoft.XMLHTTP");
                        }

                        var typerso = document.querySelector('#type_personpartoesc').
                        options[document.querySelector('#type_personpartoesc').options.selectedIndex].value;
                        var typerso1 = typerso.split('/');
                        var typerso2 = typerso1[0];
                        var typerso3 = typerso1[1];

                        var selectorscd = document.querySelector('#types_courrierspartoesc').
                        options[document.querySelector('#types_courrierspartoesc').options.selectedIndex].value;
                        
                        var nat = selectorscd.split('/');
                        var natid = nat[0];
                        var natu = nat[1];

                        var natid1 = natu.split('/');
                        var natu1 = natid1[0];
                        var natu2 = natid1[0];

                        const departdirectioncour = document.querySelector('#deparcourpartoesc').value;
                        var post_lhcour = departdirectioncour.split('/');
                        var seltdepcour = post_lhcour[0];
                        var sougidcour = post_lhcour[1];
                        const directioncour = document.querySelector('#arrscourpartoesc').value;
                        var directar = directioncour.split('/');
                        var directarg = directar[0];
                        var directarcd = directar[1];
                        httpRequesttes.open('GET', window.location.origin + `${APP_ROOT}/confirmation/fetch_mont/${natid}/${seltdepcour}-${directarg}/${typerso2}`, true);
                        httpRequesttes.onload = () => {
                            const data = JSON.parse(httpRequesttes.responseText);
                            if(data == null){
                                
        
                            }else
                            {
                                if (Object.entries(data).length >= 1) {
                                
                                    document.querySelector('#val1partoesc').value = `${data.val1}`;
                                    document.querySelector('#val2partoesc').value = `${data.val2}`;
                                    document.querySelector('#montantpartoesc').value = `${data.montant}`;
                                    document.querySelector('#intervpartoesc').value = `${data.id_inter}`

                                } 
                            }
                        };
                
                        httpRequesttes.setRequestHeader('Content-Type', 'application/json');
                        httpRequesttes.send();
     

                        if(typerso3 === 'partenaire_client' || typerso3 === 'partenaire_simple'){
                            document.querySelector('#idvalepartoesc').style.display = 'block';
                            document.querySelector('#valeur1partoesc').style.display = 'block';
                            document.querySelector('#idfraispartoesc').style.display = 'block';
                            document.querySelector('#fraisexpartoesc').style.display = 'block';
                            document.querySelector('#exp_prenompartoesc').value = "";
                            document.querySelector('#cnib_exppartoesc').value = "";
                            document.querySelector('#iddate_cnibpartoesc').value = "";
                            document.querySelector('#lieudelexppartoesc').value = "";
                            document.querySelector('#passcompagniepartoesc').value = "";
                            document.querySelector('#rclientcpexppartoesc').value = "";
                            document.querySelector('#prnclientcpexppartoesc').value = "";
                            document.querySelector('#cnibcpexppartoesc').value = "";
                            document.querySelector('#date_cnibcpexppartoesc').value = "";
                            document.querySelector('#lieudelivrecpexppartoesc').value = "";
                            document.querySelector('#idclientypeexppartoesc').value = "";
                            let httpInfoscl;
                            if (window.XMLHttpRequest) {
                                httpInfoscl = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfoscl = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            var idclit = document.querySelector('#partenairespartoesc').options[document.querySelector('#partenairespartoesc').options.selectedIndex].value;
                
                            httpInfoscl.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${idclit}`, true);
                            httpInfoscl.onload = () => {
                                const infosc = JSON.parse(httpInfoscl.responseText);
                                
                                    if (Object.entries(infosc).length >= 1) {
                                        
                                        document.querySelector('#exp_nompartoesc').value = `${infosc.nom_client}`;
                                        document.querySelector('#exp_prenompartoesc').value = `${infosc.prenom_client}`;
                                        document.querySelector('#cnib_exppartoesc').value = `${infosc.num_CNIB}`;
                                        document.querySelector('#iddate_cnibpartoesc').value = `${infosc.date_delivre}`;
                                        document.querySelector('#lieudelexppartoesc').value = `${infosc.lieu_delivre}`;
                                        document.querySelector('#passcompagniepartoesc').value = `${infosc.id_client}`;
                                        document.querySelector('#rclientcpexppartoesc').value = `${infosc.nom_client}`;
                                        document.querySelector('#prnclientcpexppartoesc').value = `${infosc.prenom_client}`;
                                        document.querySelector('#cnibcpexppartoesc').value = `${infosc.num_CNIB}`;
                                        document.querySelector('#date_cnibcpexppartoesc').value = `${infosc.date_delivre}`;
                                        document.querySelector('#lieudelivrecpexppartoesc').value = `${infosc.lieu_delivre}`;
                                        document.querySelector('#idclientypeexppartoesc').value = `${infosc.type_client}`;
                                      
                                        
                                    } 
                                    else
                                    {
                                        document.querySelector('#exp_nompartoesc').value = "";
                                        document.querySelector('#exp_prenompartoesc').value = "";
                                        document.querySelector('#cnib_exppartoesc').value = "";
                                        document.querySelector('#iddate_cnibpartoesc').value = "";
                                        document.querySelector('#lieudelexppartoesc').value = "";
                                        document.querySelector('#passcompagniepartoesc').value = "";
                                        document.querySelector('#rclientcpexppartoesc').value = "";
                                        document.querySelector('#prnclientcpexppartoesc').value = "";
                                        document.querySelector('#cnibcpexppartoesc').value = "";
                                        document.querySelector('#date_cnibcpexppartoesc').value = "";
                                        document.querySelector('#lieudelivrecpexppartoesc').value = "";
                                        document.querySelector('#idclientypeexppartoesc').value = "";
                                      
                                        
                                    }
                                                            
                            };
                            httpInfoscl.setRequestHeader('Content-Type', 'application/json');
                            httpInfoscl.send();
                            
                        }

                        if(typerso3 === 'partenaire_specifique'){
                            document.querySelector('#idvalepartoesc').style.display = 'none';
                            document.querySelector('#valeur1partoesc').style.display = 'none';
                            document.querySelector('#idfraispartoesc').style.display = 'none';
                            document.querySelector('#fraisexpartoesc').style.display = 'none';
                            document.querySelector('#fraisexpartoesc').value = 0;
                            document.querySelector('#exp_prenompartoesc').value = "";
                            document.querySelector('#cnib_exppartoesc').value = "";
                            document.querySelector('#iddate_cnibpartoesc').value = "";
                            document.querySelector('#lieudelexppartoesc').value = "";
                            document.querySelector('#passcompagniepartoesc').value = "";
                            document.querySelector('#rclientcpexppartoesc').value = "";
                            document.querySelector('#prnclientcpexppartoesc').value = "";
                            document.querySelector('#cnibcpexppartoesc').value = "";
                            document.querySelector('#date_cnibcpexppartoesc').value = "";
                            document.querySelector('#lieudelivrecpexppartoesc').value = "";
                            document.querySelector('#idclientypeexppartoesc').value = "";
                            let httpInfoscl2;
                            if (window.XMLHttpRequest) {
                                httpInfoscl2 = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfoscl2 = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            var idclit2 = document.querySelector('#partenairespartoesc').options[document.querySelector('#partenairespartoesc').options.selectedIndex].value;
                
                            httpInfoscl2.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${idclit2}`, true);
                            httpInfoscl2.onload = () => {
                                const infosc2 = JSON.parse(httpInfoscl2.responseText);
                                
                                    if (Object.entries(infosc2).length >= 1) {
                                        
                                        document.querySelector('#exp_nompartoesc').value = `${infosc2.nom_client}`;
                                        document.querySelector('#exp_prenompartoesc').value = `${infosc2.prenom_client}`;
                                        document.querySelector('#cnib_exppartoesc').value = `${infosc2.num_CNIB}`;
                                        document.querySelector('#iddate_cnibpartoesc').value = `${infosc2.date_delivre}`;
                                        document.querySelector('#lieudelexppartoesc').value = `${infosc2.lieu_delivre}`;
                                        document.querySelector('#passcompagniepartoesc').value = `${infosc2.id_client}`;
                                        document.querySelector('#rclientcpexppartoesc').value = `${infosc2.nom_client}`;
                                        document.querySelector('#prnclientcpexppartoesc').value = `${infosc2.prenom_client}`;
                                        document.querySelector('#cnibcpexppartoesc').value = `${infosc2.num_CNIB}`;
                                        document.querySelector('#date_cnibcpexppartoesc').value = `${infosc2.date_delivre}`;
                                        document.querySelector('#lieudelivrecpexppartoesc').value = `${infosc2.lieu_delivre}`;
                                        document.querySelector('#idclientypeexppartoesc').value = `${infosc2.type_client}`;
                                      
                                        
                                    } 
                                    else
                                    {
                                        document.querySelector('#exp_nompartoesc').value = "";
                                        document.querySelector('#exp_prenompartoesc').value = "";
                                        document.querySelector('#cnib_exppartoesc').value = "";
                                        document.querySelector('#iddate_cnibpartoesc').value = "";
                                        document.querySelector('#lieudelexppartoesc').value = "";
                                        document.querySelector('#passcompagniepartoesc').value = "";
                                        document.querySelector('#rclientcpexppartoesc').value = "";
                                        document.querySelector('#prnclientcpexppartoesc').value = "";
                                        document.querySelector('#cnibcpexppartoesc').value = "";
                                        document.querySelector('#date_cnibcpexppartoesc').value = "";
                                        document.querySelector('#lieudelivrecpexppartoesc').value = "";
                                        document.querySelector('#idclientypeexppartoesc').value = "";
                                      
                                        
                                    }
                                                            
                            };
                            httpInfoscl2.setRequestHeader('Content-Type', 'application/json');
                            httpInfoscl2.send();
                            
                        }
                        
                };

                   
                let infopersos = document.querySelector('#idtypepartoesc');
        
                if (infopersos !== null) 
                infopersos.onchange = () => 
                {
                
                    document.querySelector('#contactidpartoesc').style.display = 'none';
                    document.querySelector('#idcontpartoesc').style.display = 'none';
                    document.querySelector('#sonnelpartoesc').style.display = 'none';
                    document.querySelector('#idsonnelspartoesc').style.display = 'none';
                    document.querySelector('#idpartespartoesc').options.length = 1;
                    document.querySelector('#contactidpartoesc').value = '';
                    document.querySelector('#partcontpartoesc').style.display = 'none';
                            
                    var personns = document.querySelector('#idtypepartoesc')
                        .options[document.querySelector('#idtypepartoesc').options.selectedIndex].value;
                        if(personns === 'personnel')
                        {
                            document.querySelector('#membrepartcontesc').style.display = 'none';
                            document.querySelector('#idmembrenameesc').style.display = 'none';
                            document.querySelector('#sonnelpartoesc').style.display = 'block';
                            document.querySelector('#idsonnelspartoesc').style.display = 'block';
                            document.querySelector('#contactidpartoesc').style.display = 'none';
                            document.querySelector('#idcontpartoesc').style.display = 'none';
                            document.querySelector('#partcontpartoesc').style.display = 'none';
                            document.querySelector('#idpartespartoesc').style.display = 'none';
                            
                                    let httppersosdest;
                            if (window.XMLHttpRequest) {
                                httppersosdest = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httppersosdest = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                            httppersosdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectperso/${personns}`, true);
                            httppersosdest.onload = () => 
                            {

                                const infospersdest = JSON.parse(httppersosdest.responseText);

                                if (Object.entries(infospersdest).length >= 1) 
                                {


                                    for (let key in Object.entries(infospersdest))
                                    {

                                        let opt = document.createElement('option');
                                        opt.value = `${infospersdest[key].matricule}`;
                                        opt.innerHTML = `${infospersdest[key].nomprenom_perso}`;
                                        document.querySelector('#idsonnelspartoesc').add(opt);
                                    }
                                 
                                }
                                else 
                                {
                                    document.querySelector('#idsonnelspartoesc').options.length = 1;
                                }

                            };
                            httppersosdest.setRequestHeader('Content-Type', 'application/json');
                            httppersosdest.send();

                            let infopersosdest = document.querySelector('#idsonnelspartoesc');
        
                            if (infopersosdest !== null) 
                            infopersosdest.onchange = () => 
                            {

                            
                                let httpInfospersdest;
                                if (window.XMLHttpRequest) {
                                    httpInfospersdest = new XMLHttpRequest();
                                } else if (window.ActiveXObject) {
                                    httpInfospersdest = new ActiveXObject("Microsoft.XMLHTTP");
                                }

                                document.querySelector('#contactidpartoesc').style.display = 'none';
                                document.querySelector('#idcontpartoesc').style.display = 'none';
                                document.querySelector('#compagniepassdestpartoesc').value = '';
                                var idverifidest = document.querySelector('#idsonnelspartoesc').options[document.querySelector('#idsonnelspartoesc').options.selectedIndex].value;
                    
                                httpInfospersdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoperso/${idverifidest}`, true);
                                httpInfospersdest.onload = () => {
                                    const infosperdest = JSON.parse(httpInfospersdest.responseText);
                                    
                                    if (Object.entries(infosperdest).length >= 1) {
                                        
                               
                                        var typepersosdest = `${infosperdest.nomprenom_perso}`;
                                        var typer1persosdest = typepersosdest.split(' ');
                                        var typer2persosdest = typer1persosdest[0];
                                        var typer3persosdest = typer1persosdest[1];
                                        var typer4persosdest = typer1persosdest[2];
                                        if(typer4persosdest === undefined){
                                            var typer5persosdest = `${typer3persosdest}`;
                                        }
                                        else{
                                            var typer5persosdest = `${typer3persosdest} ${typer4persosdest}`;
                                        }
                                        document.querySelector('#nomdestidpartoesc').value = `${typer2persosdest}`;
                                        document.querySelector('#prenomdestidpartoesc').value = `${typer5persosdest}`;
                                        document.querySelector('#persodestcompagniepartoesc').value = `${infosperdest.matricule}`;
                                        document.querySelector('#rclientcpexppartoesc').value = `${typer2persosdest}`;
                                        document.querySelector('#prnclientcpexppartoesc').value = `${typer5persosdest}`;
                                        document.querySelector('#idclientypedestpartoesc').value = 'personnel';
                                        
                                    } 
                                    else
                                    {
                                        document.querySelector('#nomdestidpartoesc').value = "";
                                        document.querySelector('#prenomdestidpartoesc').value = "";
                                        document.querySelector('#persodestcompagniepartoesc').value = "";
                                        document.querySelector('#rclientcpdestpartoesc').value = "";
                                        document.querySelector('#prnclientcpdestpartoesc').value = "";
                                        document.querySelector('#idclientypedestpartoesc').value = "";
                                    }
                                                                
                                };
                                httpInfospersdest.setRequestHeader('Content-Type', 'application/json');
                                httpInfospersdest.send();
                            };
                        }
                        else
                        {
                            document.querySelector('#sonnelpartoesc').style.display = 'none';
                            document.querySelector('#idsonnelspartoesc').style.display = 'none';
                            document.querySelector('#partcontpartoesc').style.display = 'none';
                            document.querySelector('#idpartespartoesc').style.display = 'none';
                            document.querySelector('#idcontpartoesc').style.display = 'block';
                            document.querySelector('#contactidpartoesc').style.display = 'block';
                            document.querySelector('#idmatripartoesc').style.display = 'none';
                            document.querySelector('#matri_destpartoesc').style.display = 'none';
                            document.querySelector('#nomdestidpartoesc').value = "";
                            document.querySelector('#prenomdestidpartoesc').value = "";
                            document.querySelector('#compagniepassdestpartoesc').value = "";
                            document.querySelector('#rclientcpdestpartoesc').value = "";
                            document.querySelector('#prnclientcpdestpartoesc').value = "";
                            document.querySelector('#idclientypedestpartoesc').value = "";
                            document.querySelector('#idclientcontdestpartoesc').value = "";
                            document.querySelector('#membrepartcontesc').style.display = 'none';
                            document.querySelector('#idmembrenameesc').style.display = 'none';
                            
                            let infdest = document.querySelector('#contactidpartoesc');
                            if (infdest !== null)
                                infdest.onkeyup = () => {
                                    let httpInfosdest;
                                    if (window.XMLHttpRequest) {
                                        httpInfosdest = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) {
                                        httpInfosdest = new ActiveXObject("Microsoft.XMLHTTP");
                                    }

                                    document.querySelector('#nomdestidpartoesc').value = "";
                                    document.querySelector('#prenomdestidpartoesc').value = "";
                                    document.querySelector('#compagniepassdestpartoesc').value = "";
                                    document.querySelector('#rclientcpdestpartoesc').value = "";
                                    document.querySelector('#prnclientcpdestpartoesc').value = "";
                                    document.querySelector('#idclientypedestpartoesc').value = "";
                                    document.querySelector('#idclientcontdestpartoesc').value = "";
                                    document.querySelector('#date_cnibdestidpartoesc').value = "";
                                    var verificatdest = document.querySelector('#contactidpartoesc').value;
                                    document.querySelector('#persodestcompagniepartoesc').value = "";

                                    httpInfosdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfos/${verificatdest}`, true);
                                    httpInfosdest.onload = () => {
                                        const infosdest = JSON.parse(httpInfosdest.responseText);
                                        if (infosdest == null) {
                                            document.querySelector('#nomdestidpartoesc').value = "";
                                            document.querySelector('#prenomdestidpartoesc').value = "";
                                            document.querySelector('#compagniepassdestpartoesc').value = "";
                                            document.querySelector('#rclientcpdestpartoesc').value = "";
                                            document.querySelector('#prnclientcpdestpartoesc').value = "";
                                            document.querySelector('#idclientypedestpartoesc').value = "";
                                            document.querySelector('#idclientcontdestpartoesc').value = "";
                                            document.querySelector('#date_cnibdestidpartoesc').value = "";
                                            
                                        } else 
                                        {
                                            if (Object.entries(infosdest).length > 1) {
                                                
                                                if (infosdest.contact_client == verificatdest) {
                                                    document.querySelector('#nomdestidpartoesc').value = `${infosdest.nom_client}`;
                                                    document.querySelector('#prenomdestidpartoesc').value = `${infosdest.prenom_client}`;
                                                    document.querySelector('#compagniepassdestpartoesc').value = `${infosdest.id_client}`;
                                                    document.querySelector('#idclientypedestpartoesc').value = `${infosdest.type_client}`;
                                                    document.querySelector('#rclientcpdestpartoesc').value = `${infosdest.nom_client}`;
                                                    document.querySelector('#prnclientcpdestpartoesc').value = `${infosdest.prenom_client}`;
                                                    document.querySelector('#idclientcontdestpartoesc').value = `${infosdest.contact_client}`;
                                                    document.querySelector('#date_cnibdestidpartoesc').value = `${infosdest.date_delivre}`;
                                                } else {
                                                    document.querySelector('#nomdestidpartoesc').value = "";
                                                    document.querySelector('#prenomdestidpartoesc').value = "";
                                                    document.querySelector('#compagniepassdestpartoesc').value = "";
                                                    document.querySelector('#rclientcpdestpartoesc').value = "";
                                                    document.querySelector('#prnclientcpdestpartoesc').value = "";
                                                    document.querySelector('#idclientypedestpartoesc').value = "";
                                                    document.querySelector('#idclientcontdestpartoesc').value = "";
                                                    document.querySelector('#date_cnibdestidpartoesc').value = "";
                                                }
                                            }
                                        }
                                    };
                                    httpInfosdest.setRequestHeader('Content-Type', 'application/json');
                                    httpInfosdest.send();
                                };
                        }

                        if(personns === 'membre'){

                                document.querySelector('#membrepartcontesc').style.display = 'block';
                                document.querySelector('#idmembrenameesc').style.display = 'block';
                                document.querySelector('#sonnelpartoesc').style.display = 'none';
                                document.querySelector('#idsonnelspartoesc').style.display = 'none';
                                document.querySelector('#idcontpartoesc').style.display = 'none';
                                document.querySelector('#contactidpartoesc').style.display = 'none';
                                document.querySelector('#partcontpartoesc').style.display = 'none';
                                document.querySelector('#idpartespartoesc').style.display = 'none';
                                
                                
                        
                                let httppaternesdestm;
                                    if (window.XMLHttpRequest) {
                                        httppaternesdestm = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) {
                                        httppaternesdestm = new ActiveXObject("Microsoft.XMLHTTP");
                                    }
                                    
                                    httppaternesdestm.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectpartenaire/${personns}`, true);
                                    httppaternesdestm.onload = () => {
                                        const infospartenedestm = JSON.parse(httppaternesdestm.responseText);

                                        if (Object.entries(infospartenedestm).length >= 1) 
                                        {

                                            for (let key in Object.entries(infospartenedestm))
                                            {

                                                let opt = document.createElement('option');
                                                opt.value = `${infospartenedestm[key].id_client}`;
                                                opt.innerHTML = `${infospartenedestm[key].nom_client} ${infospartenedestm[key].prenom_client}`;
                                                document.querySelector('#idmembrenameesc').add(opt);
                                            }
                                                
                                        }
                                        else 
                                        {
                                            document.querySelector('#idmembrenameesc').options.length = 1;
                                        }

                                    };
                                    httppaternesdestm.setRequestHeader('Content-Type', 'application/json');
                                    httppaternesdestm.send();

                                let paternstscdestin2m = document.querySelector('#idmembrenameesc');
                                if (paternstscdestin2m !== null)
                                paternstscdestin2m.onchange = () => {
                                    let httpInfospersdestin2m;
                                        httpInfospersdestin2m = new XMLHttpRequest();
                                    document.querySelector('#persodestcompagniepartoesc').value = '';
                                    document.querySelector('#contactidpartoesc').style.display = 'none';
                                    document.querySelector('#idcontpartoesc').style.display = 'none';
                                    document.querySelector('#contactidpartoesc').value = '';
                                        var ternsdest2m = document.querySelector('#idmembrenameesc').
                                            options[document.querySelector('#idmembrenameesc').options.selectedIndex].value;
                                        httpInfospersdestin2m.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest2m}`, true);
                                    httpInfospersdestin2m.onload = () => {
                                        const infosperdestin2m = JSON.parse(httpInfospersdestin2m.responseText);
                                        
                                        if (Object.entries(infosperdestin2m).length >= 1) {
                                            
                                
                                            document.querySelector('#nomdestidpartoesc').value = `${infosperdestin2m.nom_client}`;
                                            document.querySelector('#prenomdestidpartoesc').value = `${infosperdestin2m.prenom_client}`;
                                            document.querySelector('#compagniepassdestpartoesc').value = `${infosperdestin2m.id_client}`;
                                            document.querySelector('#idclientypedestpartoesc').value = `${infosperdestin2m.type_client}`;
                                            document.querySelector('#rclientcpdestpartoesc').value = `${infosperdestin2m.nom_client}`;
                                            document.querySelector('#prnclientcpdestpartoesc').value = `${infosperdestin2m.prenom_client}`;
                                            document.querySelector('#date_cnibdestidpartoesc').value = `${infosperdestin2m.date_delivre}`;
                                        } 
                                        else
                                        {
                                            document.querySelector('#nomdestidpartoesc').value = "";
                                            document.querySelector('#prenomdestidpartoesc').value = "";
                                            document.querySelector('#compagniepassdestpartoesc').value = "";
                                            document.querySelector('#rclientcpdestpartoesc').value = "";
                                            document.querySelector('#prnclientcpdestpartoesc').value = "";
                                            document.querySelector('#idclientypedestpartoesc').value = "";
                                            document.querySelector('#date_cnibdestidpartoesc').value = "";
                                        }
                                                                    
                                    };
                                    httpInfospersdestin2m.setRequestHeader('Content-Type', 'application/json');
                                    httpInfospersdestin2m.send();
                                };
                     
                        }

                        if(personns === 'partenaire_client' || personns === 'partenaire_simple'){
                            document.querySelector('#membrepartcontesc').style.display = 'none';
                            document.querySelector('#idmembrenameesc').style.display = 'none';
                            document.querySelector('#partcontpartoesc').style.display = 'block';
                            document.querySelector('#idpartespartoesc').style.display = 'block';
                            document.querySelector('#sonnelpartoesc').style.display = 'none';
                            document.querySelector('#idsonnelspartoesc').style.display = 'none';
                            document.querySelector('#contactidpartoesc').style.display = 'none';
                            document.querySelector('#idcontpartoesc').style.display = 'none';
                            document.querySelector('#nomdestidpartoesc').value = '';
                            document.querySelector('#prenomdestidpartoesc').value = '';
                            document.querySelector('#compagniepassdestpartoesc').value = '';
                            document.querySelector('#idclientypedestpartoesc').value = '';
                            document.querySelector('#rclientcpdestpartoesc').value = '';
                            document.querySelector('#prnclientcpdestpartoesc').value = '';
                            document.querySelector('#contactidpartoesc').value = '';
                            document.querySelector('#idclientcontdestpartoesc').value = '';
                            document.querySelector('#date_cnibdestidpartoesc').value = "";
                            let httppaternsdest;
                                if (window.XMLHttpRequest) {
                                    httppaternsdest = new XMLHttpRequest();
                                } else if (window.ActiveXObject) {
                                    httppaternsdest = new ActiveXObject("Microsoft.XMLHTTP");
                                }
                                
                                httppaternsdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectpartenaire/${personns}`, true);
                                httppaternsdest.onload = () => {
                                    const infospartendest = JSON.parse(httppaternsdest.responseText);

                                    if (Object.entries(infospartendest).length >= 1) 
                                    {

                                        for (let key in Object.entries(infospartendest))
                                        {

                                            let opt = document.createElement('option');
                                            opt.value = `${infospartendest[key].id_client}`;
                                            opt.innerHTML = `${infospartendest[key].nom_client} ${infospartendest[key].prenom_client}`;
                                            document.querySelector('#idpartespartoesc').add(opt);
                                        }
                                            
                                    }
                                    else 
                                    {
                                        document.querySelector('#idpartespartoesc').options.length = 1;
                                    }

                                };
                                httppaternsdest.setRequestHeader('Content-Type', 'application/json');
                                httppaternsdest.send();

                                let paternstscdestin = document.querySelector('#idpartespartoesc');
                            if (paternstscdestin !== null)
                            paternstscdestin.onchange = () => {
                                let httpInfospersdestin;
                                    httpInfospersdestin = new XMLHttpRequest();
                                document.querySelector('#persodestcompagniepartoesc').value = '';
                                document.querySelector('#contactidpartoesc').style.display = 'none';
                                document.querySelector('#idcontpartoesc').style.display = 'none';
                                document.querySelector('#contactidpartoesc').value = '';
                                var ternsdest= document.querySelector('#idpartespartoesc').
                                    options[document.querySelector('#idpartespartoesc').options.selectedIndex].value;
                                httpInfospersdestin.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest}`, true);
                                httpInfospersdestin.onload = () => {
                                    const infosperdestin = JSON.parse(httpInfospersdestin.responseText);
                                    
                                    if (Object.entries(infosperdestin).length >= 1) {
                                        
                               
                                        
                                        document.querySelector('#nomdestidpartoesc').value = `${infosperdestin.nom_client}`;
                                        document.querySelector('#prenomdestidpartoesc').value = `${infosperdestin.prenom_client}`;
                                        document.querySelector('#compagniepassdestpartoesc').value = `${infosperdestin.id_client}`;
                                        document.querySelector('#idclientypedestpartoesc').value = `${infosperdestin.type_client}`;
                                        document.querySelector('#rclientcpdestpartoesc').value = `${infosperdestin.nom_client}`;
                                        document.querySelector('#prnclientcpdestpartoesc').value = `${infosperdestin.prenom_client}`;
                                        document.querySelector('#date_cnibdestidpartoesc').value = `${infosperdestin.date_delivre}`;
                                    } 
                                    else
                                    {
                                        document.querySelector('#nomdestidpartoesc').value = "";
                                        document.querySelector('#prenomdestidpartoesc').value = "";
                                        document.querySelector('#compagniepassdestpartoesc').value = "";
                                        document.querySelector('#rclientcpdestpartoesc').value = "";
                                        document.querySelector('#prnclientcpdestpartoesc').value = "";
                                        document.querySelector('#idclientypedestpartoesc').value = "";
                                        document.querySelector('#date_cnibdestidpartoesc').value = "";
                                    }
                                                                
                                };
                                httpInfospersdestin.setRequestHeader('Content-Type', 'application/json');
                                httpInfospersdestin.send();
                            };
 
                        }
                        else
                        {
                            if(personns === 'partenaire_specifique'){

                                document.querySelector('#partcontpartoesc').style.display = 'block';
                                document.querySelector('#idpartespartoesc').style.display = 'block';
                                document.querySelector('#sonnelpartoesc').style.display = 'none';
                                document.querySelector('#idsonnelspartoesc').style.display = 'none';
                                document.querySelector('#idcontpartoesc').style.display = 'none';
                                document.querySelector('#contactidpartoesc').style.display = 'none';
                                document.querySelector('#membrepartcontesc').style.display = 'none';
                                document.querySelector('#idmembrenameesc').style.display = 'none';
                            
                                let httppaternesdest;
                                    if (window.XMLHttpRequest) {
                                        httppaternesdest = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) {
                                        httppaternesdest = new ActiveXObject("Microsoft.XMLHTTP");
                                    }
                                    
                                    httppaternesdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectpartenaire/${personns}`, true);
                                    httppaternesdest.onload = () => {
                                        const infospartenedest = JSON.parse(httppaternesdest.responseText);

                                        if (Object.entries(infospartenedest).length >= 1) 
                                        {

                                            for (let key in Object.entries(infospartenedest))
                                            {

                                                let opt = document.createElement('option');
                                                opt.value = `${infospartenedest[key].id_client}`;
                                                opt.innerHTML = `${infospartenedest[key].nom_client} ${infospartenedest[key].prenom_client}`;
                                                document.querySelector('#idpartespartoesc').add(opt);
                                            }
                                                
                                        }
                                        else 
                                        {
                                            document.querySelector('#idpartespartoesc').options.length = 1;
                                        }

                                    };
                                    httppaternesdest.setRequestHeader('Content-Type', 'application/json');
                                    httppaternesdest.send();

                                let paternstscdestin2 = document.querySelector('#idpartespartoesc');
                                if (paternstscdestin2 !== null)
                                paternstscdestin2.onchange = () => {
                                    let httpInfospersdestin2;
                                        httpInfospersdestin2 = new XMLHttpRequest();
                                    document.querySelector('#persodestcompagniepartoesc').value = '';
                                    document.querySelector('#contactidpartoesc').style.display = 'none';
                                    document.querySelector('#idcontpartoesc').style.display = 'none';
                                    document.querySelector('#contactidpartoesc').value = '';
                                        var ternsdest2 = document.querySelector('#idpartespartoesc').
                                            options[document.querySelector('#idpartespartoesc').options.selectedIndex].value;
                                        httpInfospersdestin2.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest2}`, true);
                                    httpInfospersdestin2.onload = () => {
                                        const infosperdestin2 = JSON.parse(httpInfospersdestin2.responseText);
                                        
                                        if (Object.entries(infosperdestin2).length >= 1) {
                                
                                            document.querySelector('#nomdestidpartoesc').value = `${infosperdestin2.nom_client}`;
                                            document.querySelector('#prenomdestidpartoesc').value = `${infosperdestin2.prenom_client}`;
                                            document.querySelector('#compagniepassdestpartoesc').value = `${infosperdestin2.id_client}`;
                                            document.querySelector('#idclientypedestpartoesc').value = `${infosperdestin2.type_client}`;
                                            document.querySelector('#rclientcpdestpartoesc').value = `${infosperdestin2.nom_client}`;
                                            document.querySelector('#prnclientcpdestpartoesc').value = `${infosperdestin2.prenom_client}`;
                                            document.querySelector('#date_cnibdestidpartoesc').value = `${infosperdestin2.date_delivre}`;
                                        } 
                                        else
                                        {
                                            document.querySelector('#nomdestidpartoesc').value = "";
                                            document.querySelector('#prenomdestidpartoesc').value = "";
                                            document.querySelector('#compagniepassdestpartoesc').value = "";
                                            document.querySelector('#rclientcpdestpartoesc').value = "";
                                            document.querySelector('#prnclientcpdestpartoesc').value = "";
                                            document.querySelector('#idclientypedestpartoesc').value = "";
                                            document.querySelector('#date_cnibdestidpartoesc').value = "";
                                        }
                                                                    
                                    };
                                    httpInfospersdestin2.setRequestHeader('Content-Type', 'application/json');
                                    httpInfospersdestin2.send();
                                };
                   
                            }   
                        }
                    }
        e.onclick = function () {
            let copartoForm = document.querySelector('#copartoFormesc');
            copartoForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/addpartoesc/${e.dataset.cle_compagnie}`);
        }

        var clique = true;

            $('#bottonpartoesc').click(function(event) 
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
/* --- adperscoursescale.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adperscoursescale').forEach(function (e) 
    {
       
            let arcourpers = document.querySelector('#arrscourpersoesc');
            if (arcourpers !== null)
            arcourpers.onchange = () => {
                document.querySelector('#date_depheurecourexpersoesc').value = '';
                document.querySelector('#hdepcourpersoesc').options.length = 1;
                document.querySelector('#quartiercourpersoesc').options.length = 1;
                const garedepartcourpers = document.querySelector('#deparcourpersoesc').value;
                const garearrivpers = document.querySelector('#arrscourpersoesc').value;
                var post_arpers = garearrivpers.split('/');
                var seltarpers = post_arpers[0];
                var sougidarrpers = post_arpers[1];
                let httptypequartpers;
                httptypequartpers = new XMLHttpRequest();
                
                httptypequartpers.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${seltarpers}`, true);
                httptypequartpers.onload = () => 
                {
                    const courquapers = JSON.parse(httptypequartpers.responseText);
                    if (courquapers == '') {
                        document.querySelector('#quartiercourpersoesc').options.length = 1;
                    }
                    else{
                        if (Object.entries(courquapers).length >= 1) {
                                        
                            for (let key in Object.entries(courquapers)) {
                                let opt = document.createElement('option');
                                opt.value = `${courquapers[key].code_quart}/${courquapers[key].nom_quartier}`;
                                opt.innerHTML = `${courquapers[key].nom_quartier}/${courquapers[key].code_quart}`;
                                document.querySelector('#quartiercourpersoesc').add(opt);
                            }
                        } else {
                            document.querySelector('#quartiercourpersoesc').options.length = 1;
                        }
                    }
                    

                };
                httptypequartpers.setRequestHeader('Content-Type', 'application/json');
                httptypequartpers.send();
            };
            let dpcourexpers = document.querySelector('#date_depheurecourexpersoesc');
            if (dpcourexpers !== null)
               dpcourexpers.onchange = () => {

                    const dateactuexpers = document.querySelector('#dateactpersoesc').value;
                    const garearriveexpers = document.querySelector('#arrscourpersoesc').value;
                    const progdepartexpers = document.querySelector('#date_depheurecourexpersoesc').value;
                    const garedepartcourexpers = document.querySelector('#deparcourpersoesc').value;
                    document.querySelector('#hdepcourpersoesc').options.length = 1;
                    var post_lhdepexpers = garedepartcourexpers.split('/');
                    var seltdepexpers = post_lhdepexpers[0];
                    var sougidexpers = post_lhdepexpers[1];
                    var post_arrexpers = garearriveexpers.split('/');
                    var seltarrexpers = post_arrexpers[0];
                    var sougidarexpers = post_arrexpers[1];
                    if(progdepartexpers >= dateactuexpers)
                    {
                                                 
                        
                        let Requestitinespers;
                        Requestitinespers = new XMLHttpRequest();
                        Requestitinespers.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheure1/${seltdepexpers}-${seltarrexpers}/${progdepartexpers}`, true);
                            Requestitinespers.onload = () => 
                            {
                                const infoscourrspers = JSON.parse(Requestitinespers.responseText);
                                    document.querySelector('#smsdtcrpersoesc').style.display = 'none';
                                    
                                if(infoscourrspers == ''){

                                }
                                else
                                {
                                    if (Object.entries(infoscourrspers).length >= 1) {
                                   
                                        for (let key in Object.entries(infoscourrspers)) {
                                            let opt = document.createElement('option');
                                            opt.value = `${infoscourrspers[key].id_ligneheure}`;
                                            opt.innerHTML = `${infoscourrspers[key].heure}`;
                                            document.querySelector('#hdepcourpersoesc').add(opt);
                                        }
                                    } else {
                                        document.querySelector('#hdepcourpersoesc').options.length = 1;
                                    }
                                }
                                        
                            };
                            Requestitinespers.setRequestHeader('Content-Type', 'application/json');
                            Requestitinespers.send();
                
                    }
                    else
                    {
                        document.querySelector('#date_depheurecourexpersoesc').style.color = "#FF0000";
                        document.querySelector('#date_depheurecourexpersoesc').style.border = "2px solid #FF0000";
                        document.querySelector('#smsdtcrpersoesc').style.display = 'block';
                        document.querySelector('#erreurSmsdtcrpersoesc').innerHTML = `Date non valide.`;
                    }
                };
               
               let typerspers = document.querySelector('#type_personpersoesc');
                if (typerspers !== null)
                typerspers.onchange = () => {

                    var typersoperspers = document.querySelector('#type_personpersoesc').
                        options[document.querySelector('#type_personpersoesc').options.selectedIndex].value;
                        var typerso1perspers = typersoperspers.split('/');
                        var typerso2perspers = typerso1perspers[0];
                        var typerso3perspers = typerso1perspers[1];

                        document.querySelector('#types_courrierspersoesc').options.length = 1;
                        document.querySelector('#personidpersoesc').options.length = 1;
                        document.querySelector('#exp_nompersoesc').value = "";
                        document.querySelector('#exp_prenompersoesc').value = "";
                        document.querySelector('#cnib_exppersoesc').value = "";
                        document.querySelector('#iddate_cnibpersoesc').value = "";
                        document.querySelector('#lieudelexppersoesc').value = "";
                        document.querySelector('#rclientcpexppersoesc').value = "";
                        document.querySelector('#prnclientcpexppersoesc').value = "";
                        document.querySelector('#cnibcpexppersoesc').value = "";
                        document.querySelector('#date_cnibcpexppersoesc').value = "";
                        document.querySelector('#lieudelivrecpexppersoesc').value = "";
                        document.querySelector('#idclientypeexppersoesc').value = "";
                    
                    
                        let httppersospers;
                            if (window.XMLHttpRequest) {
                                httppersospers = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httppersospers = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                            httppersospers.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectperso/${typerso3perspers}`, true);
                            httppersospers.onload = () => 
                            {

                                const infosperspers = JSON.parse(httppersospers.responseText);

                                if (Object.entries(infosperspers).length >= 1) 
                                {


                                    for (let key in Object.entries(infosperspers))
                                    {

                                        let opt = document.createElement('option');
                                        opt.value = `${infosperspers[key].matricule}`;
                                        opt.innerHTML = `${infosperspers[key].nomprenom_perso}`;
                                        document.querySelector('#personidpersoesc').add(opt);
                                    }
                                       
                                       let httpRequesttesperso2pers;
                                        httpRequesttesperso2pers = new XMLHttpRequest();

                                            
                                        httpRequesttesperso2pers.open('GET', window.location.origin + `${APP_ROOT}/confirmation/fetch_typecourriers`, true);
                                        httpRequesttesperso2pers.onload = () => {
                                            const datapersos2pers = JSON.parse(httpRequesttesperso2pers.responseText);
                                                 if (Object.entries(datapersos2pers).length >= 1) {
                                               
                                                    for (let key in Object.entries(datapersos2pers)) {
                                                        let opt = document.createElement('option');
                                                        opt.value = `${datapersos2pers[key].id_cat}/${datapersos2pers[key].categ}/${datapersos2pers[key].indicatif}`;
                                                        opt.innerHTML = `${datapersos2pers[key].categ}`;
                                                        document.querySelector('#types_courrierspersoesc').add(opt);
                                                    }
                                                } else {
                                                    document.querySelector('#types_courrierspersoesc').options.length = 1;
                                                }
                                        };
                        
                                        httpRequesttesperso2pers.setRequestHeader('Content-Type', 'application/json');
                                        httpRequesttesperso2pers.send(); 
                                }
                                else 
                                {
                                    document.querySelector('#personidpersoesc').options.length = 1;
                                }

                            };
                            httppersospers.setRequestHeader('Content-Type', 'application/json');
                            httppersospers.send();
                    
                };
                
                let tscdpers = document.querySelector('#types_courrierspersoesc');
                if (tscdpers !== null)
                tscdpers.onchange = () => {
                        let httpRequesttespers;
    
                        if (window.XMLHttpRequest) {
                            httpRequesttespers = new XMLHttpRequest();
                        } else if (window.ActiveXObject) {
                            httpRequesttespers = new ActiveXObject("Microsoft.XMLHTTP");
                        }

                        var typersopers = document.querySelector('#type_personpersoesc').
                        options[document.querySelector('#type_personpersoesc').options.selectedIndex].value;
                        var typerso1pers = typersopers.split('/');
                        var typerso2pers = typerso1pers[0];
                        var typerso3pers = typerso1pers[1];

                        
                            document.querySelector('#exp_nompersoesc').value = "";
                            document.querySelector('#exp_prenompersoesc').value = "";
                            document.querySelector('#cnib_exppersoesc').value = "";
                            document.querySelector('#iddate_cnibpersoesc').value = "";
                            document.querySelector('#lieudelexppersoesc').value = "";
                            document.querySelector('#rclientcpexppersoesc').value = "";
                            document.querySelector('#prnclientcpexppersoesc').value = "";
                            document.querySelector('#cnibcpexppersoesc').value = "";
                            document.querySelector('#date_cnibcpexppersoesc').value = "";
                            document.querySelector('#lieudelivrecpexppersoesc').value = "";
                            document.querySelector('#idclientypeexppersoesc').value = "";
                            let httpInfosperspers;
                            if (window.XMLHttpRequest) {
                                httpInfosperspers = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosperspers = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            var idverifipers = document.querySelector('#personidpersoesc').options[document.querySelector('#personidpersoesc').options.selectedIndex].value;
                
                            httpInfosperspers.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoperso/${idverifipers}`, true);
                            httpInfosperspers.onload = () => {
                                const infosperpers = JSON.parse(httpInfosperspers.responseText);
                                
                                    if (Object.entries(infosperpers).length >= 1) {
                                        
                               
                                        var typepersospers = `${infosperpers.nomprenom_perso}`;
                                        var typer1persospers = typepersospers.split(' ');
                                        var typer2persospers = typer1persospers[0];
                                        var typer3persospers = typer1persospers[1];
                                        var typer4persospers = typer1persospers[2];
                                        if(typer4persospers === undefined){
                                            var typer5persospers = `${typer3persospers}`;
                                        }
                                        else{
                                            var typer5persospers = `${typer3persospers} ${typer4persospers}`;
                                        }
                                        document.querySelector('#exp_nompersoesc').value = `${typer2persospers}`;
                                        document.querySelector('#exp_prenompersoesc').value = `${typer5persospers}`;
                                        document.querySelector('#cnib_exppersoesc').value = `${infosperpers.pieces2}`;
                                        document.querySelector('#iddate_cnibpersoesc').value = `${infosperpers.date_delivre2}`;
                                        document.querySelector('#persocompagniepersoesc').value = `${infosperpers.matricule}`;
                                        document.querySelector('#rclientcpexppersoesc').value = `${typer2persospers}`;
                                        document.querySelector('#prnclientcpexppersoesc').value = `${typer5persospers}`;
                                        document.querySelector('#cnibcpexppersoesc').value = `${infosperpers.pieces2}`;
                                        document.querySelector('#date_cnibcpexppersoesc').value = `${infosperpers.date_delivre2}`;
                                        document.querySelector('#idclientypeexppersoesc').value = 'personnel';
                                        
                                    } 
                                                           
                            };
                            httpInfosperspers.setRequestHeader('Content-Type', 'application/json');
                            httpInfosperspers.send();
                            
                };

                   
                let infopersospers = document.querySelector('#idtypepersoesc');
        
                if (infopersospers !== null) 
                infopersospers.onchange = () => 
                {
                
                    document.querySelector('#contactidpersoesc').style.display = 'none';
                    document.querySelector('#idcontpersoesc').style.display = 'none';
                    document.querySelector('#sonnelpersoesc').style.display = 'none';
                    document.querySelector('#idsonnelspersoesc').style.display = 'none';
                    document.querySelector('#idpartespersoesc').options.length = 1;
                    document.querySelector('#contactidpersoesc').value = '';
                    document.querySelector('#partcontpersoesc').style.display = 'none';
                            
                    var personns = document.querySelector('#idtypepersoesc')
                        .options[document.querySelector('#idtypepersoesc').options.selectedIndex].value;
                        if(personns === 'personnel')
                        {
                    
                            document.querySelector('#sonnelpersoesc').style.display = 'block';
                            document.querySelector('#idsonnelspersoesc').style.display = 'block';
                            document.querySelector('#contactidpersoesc').style.display = 'none';
                            document.querySelector('#idcontpersoesc').style.display = 'none';
                            document.querySelector('#partcontpersoesc').style.display = 'none';
                            document.querySelector('#idpartespersoesc').style.display = 'none';
                            document.querySelector('#sonnelpersomemesc').style.display = 'none';
                            document.querySelector('#idsonnelspersomemesc').style.display = 'none';
                            document.querySelector('#idsonnelspersomemesc').options.length = 1;
                            document.querySelector('#idpartespersoesc').options.length = 1;


                                
                            let httppersosdestpers;
                            if (window.XMLHttpRequest) {
                                httppersosdestpers = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httppersosdestpers = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                            httppersosdestpers.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectperso/${personns}`, true);
                            httppersosdestpers.onload = () => 
                            {

                                const infospersdestpers = JSON.parse(httppersosdestpers.responseText);

                                if (Object.entries(infospersdestpers).length >= 1) 
                                {


                                    for (let key in Object.entries(infospersdestpers))
                                    {

                                        let opt = document.createElement('option');
                                        opt.value = `${infospersdestpers[key].matricule}`;
                                        opt.innerHTML = `${infospersdestpers[key].nomprenom_perso}`;
                                        document.querySelector('#idsonnelspersoesc').add(opt);
                                    }
                                 
                                }
                                else 
                                {
                                    document.querySelector('#idsonnelspersoesc').options.length = 1;
                                }

                            };
                            httppersosdestpers.setRequestHeader('Content-Type', 'application/json');
                            httppersosdestpers.send();

                            let infopersosdestpers = document.querySelector('#idsonnelspersoesc');
        
                            if (infopersosdestpers !== null) 
                            infopersosdestpers.onchange = () => 
                            {

                            
                                let httpInfospersdest;
                                if (window.XMLHttpRequest) {
                                    httpInfospersdest = new XMLHttpRequest();
                                } else if (window.ActiveXObject) {
                                    httpInfospersdest = new ActiveXObject("Microsoft.XMLHTTP");
                                }

                                document.querySelector('#contactidpersoesc').style.display = 'none';
                                document.querySelector('#idcontpersoesc').style.display = 'none';
                                document.querySelector('#compagniepassdestpersoesc').value = '';
                                var idverifidest = document.querySelector('#idsonnelspersoesc').options[document.querySelector('#idsonnelspersoesc').options.selectedIndex].value;
                    
                                httpInfospersdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoperso/${idverifidest}`, true);
                                httpInfospersdest.onload = () => {
                                    const infosperdest = JSON.parse(httpInfospersdest.responseText);
                                    
                                    if (Object.entries(infosperdest).length >= 1) {
                                        
                               
                                        var typepersosdest = `${infosperdest.nomprenom_perso}`;
                                        var typer1persosdest = typepersosdest.split(' ');
                                        var typer2persosdest = typer1persosdest[0];
                                        var typer3persosdest = typer1persosdest[1];
                                        var typer4persosdest = typer1persosdest[2];
                                        if(typer4persosdest === undefined){
                                            var typer5persosdest = `${typer3persosdest}`;
                                        }
                                        else{
                                            var typer5persosdest = `${typer3persosdest} ${typer4persosdest}`;
                                        }
                                        document.querySelector('#nomdestidpersoesc').value = `${typer2persosdest}`;
                                        document.querySelector('#prenomdestidpersoesc').value = `${typer5persosdest}`;
                                        document.querySelector('#persodestcompagniepersoesc').value = `${infosperdest.matricule}`;
                                        document.querySelector('#rclientcpexppersoesc').value = `${typer2persosdest}`;
                                        document.querySelector('#prnclientcpexppersoesc').value = `${typer5persosdest}`;
                                        document.querySelector('#idclientypedestpersoesc').value = 'personnel';
                                        document.querySelector('#idclientcontpersoesc').value = "";
                                    } 
                                    else
                                    {
                                        document.querySelector('#nomdestidpersoesc').value = "";
                                        document.querySelector('#prenomdestidpersoesc').value = "";
                                        document.querySelector('#persodestcompagniepersoesc').value = "";
                                        document.querySelector('#rclientcpdestpersoesc').value = "";
                                        document.querySelector('#prnclientcpdestpersoesc').value = "";
                                        document.querySelector('#idclientypedestpersoesc').value = "";
                                        document.querySelector('#idclientcontpersoesc').value = "";
                                    }
                                                                
                                };
                                httpInfospersdest.setRequestHeader('Content-Type', 'application/json');
                                httpInfospersdest.send();
                            };
                        }
                        else
                        {
                            document.querySelector('#sonnelpersomemesc').style.display = 'none';
                            document.querySelector('#idsonnelspersomemesc').style.display = 'none';
                            document.querySelector('#idsonnelspersomemesc').options.length = 1;
                            document.querySelector('#sonnelpersoesc').style.display = 'none';
                            document.querySelector('#idsonnelspersoesc').style.display = 'none';
                            document.querySelector('#partcontpersoesc').style.display = 'none';
                            document.querySelector('#idpartespersoesc').style.display = 'none';
                            document.querySelector('#idsonnelspersoesc').options.length = 1;
                            document.querySelector('#idpartespersoesc').options.length = 1;
                            document.querySelector('#idcontpersoesc').style.display = 'block';
                            document.querySelector('#contactidpersoesc').style.display = 'block';
                            document.querySelector('#idmatripersoesc').style.display = 'none';
                            document.querySelector('#matri_destpersoesc').style.display = 'none';
                            document.querySelector('#nomdestidpersoesc').value = "";
                            document.querySelector('#prenomdestidpersoesc').value = "";
                            document.querySelector('#compagniepassdestpersoesc').value = "";
                            document.querySelector('#rclientcpdestpersoesc').value = "";
                            document.querySelector('#prnclientcpdestpersoesc').value = "";
                            document.querySelector('#idclientypedestpersoesc').value = "";
                            document.querySelector('#idclientcontpersoesc').value = "";
                            let infdest = document.querySelector('#contactidpersoesc');
                            if (infdest !== null)
                                infdest.onkeyup = () => {
                                    let httpInfosdest;
                                    if (window.XMLHttpRequest) {
                                        httpInfosdest = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) {
                                        httpInfosdest = new ActiveXObject("Microsoft.XMLHTTP");
                                    }

                                    document.querySelector('#nomdestidpersoesc').value = "";
                                    document.querySelector('#prenomdestidpersoesc').value = "";
                                    document.querySelector('#compagniepassdestpersoesc').value = "";
                                    document.querySelector('#rclientcpdestpersoesc').value = "";
                                    document.querySelector('#prnclientcpdestpersoesc').value = "";
                                    document.querySelector('#idclientypedestpersoesc').value = "";
                                    document.querySelector('#idclientcontpersoesc').value = "";
                                    var verificatdest = document.querySelector('#contactidpersoesc').value;
                                    document.querySelector('#persodestcompagniepersoesc').value = "";

                                    httpInfosdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfos/${verificatdest}`, true);
                                    httpInfosdest.onload = () => {
                                        const infosdest = JSON.parse(httpInfosdest.responseText);
                                        if (infosdest == null) {
                                            document.querySelector('#nomdestidpersoesc').value = "";
                                            document.querySelector('#prenomdestidpersoesc').value = "";
                                            document.querySelector('#compagniepasspersoesc').value = "";
                                            document.querySelector('#rclientcpdestpersoesc').value = "";
                                            document.querySelector('#prnclientcpdestpersoesc').value = "";
                                            document.querySelector('#idclientypedestpersoesc').value = "";
                                            document.querySelector('#idclientcontpersoesc').value = "";
                                        } else 
                                        {
                                            if (Object.entries(infosdest).length > 1) {
                                                
                                                if (infosdest.contact_client == verificatdest) {
                                                    document.querySelector('#nomdestidpersoesc').value = `${infosdest.nom_client}`;
                                                    document.querySelector('#prenomdestidpersoesc').value = `${infosdest.prenom_client}`;
                                                    document.querySelector('#compagniepassdestpersoesc').value = `${infosdest.id_client}`;
                                                    document.querySelector('#idclientypedestpersoesc').value = `${infosdest.type_client}`;
                                                    document.querySelector('#rclientcpdestpersoesc').value = `${infosdest.nom_client}`;
                                                    document.querySelector('#prnclientcpdestpersoesc').value = `${infosdest.prenom_client}`;
                                                    document.querySelector('#idclientcontpersoesc').value = `${infosdest.contact_client}`;
                                                    document.querySelector('#date_cnibdestidpersoesc').value = `${infosdest.date_delivre}`;
                                                } else {
                                                    document.querySelector('#idclientcontpersoesc').value = "";
                                                    document.querySelector('#nomdestidpersoesc').value = "";
                                                    document.querySelector('#prenomdestidpersoesc').value = "";
                                                    document.querySelector('#compagniepassdestpersoesc').value = "";
                                                    document.querySelector('#rclientcpdestpersoesc').value = "";
                                                    document.querySelector('#prnclientcpdestpersoesc').value = "";
                                                    document.querySelector('#idclientypedestpersoesc').value = "";
                                                    document.querySelector('#date_cnibdestidpersoesc').value = "";
                                            
                                                }
                                            }
                                        }
                                    };
                                    httpInfosdest.setRequestHeader('Content-Type', 'application/json');
                                    httpInfosdest.send();
                                };
                        }

                        if(personns === 'membre'){

                                document.querySelector('#sonnelpersomemesc').style.display = 'block';
                                document.querySelector('#idsonnelspersomemesc').style.display = 'block';
                                document.querySelector('#idcontpersoesc').style.display = 'none';
                                document.querySelector('#contactidpersoesc').style.display = 'none';
                                document.querySelector('#sonnelpersoesc').style.display = 'none';
                                document.querySelector('#idsonnelspersoesc').style.display = 'none';
                                document.querySelector('#idsonnelspersoesc').options.length = 1;
                                
                        
                                let httppaternesdestm;
                                    if (window.XMLHttpRequest) {
                                        httppaternesdestm = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) {
                                        httppaternesdestm = new ActiveXObject("Microsoft.XMLHTTP");
                                    }
                                    
                                    httppaternesdestm.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectpartenaire/${personns}`, true);
                                    httppaternesdestm.onload = () => {
                                        const infospartenedestm = JSON.parse(httppaternesdestm.responseText);

                                        if (Object.entries(infospartenedestm).length >= 1) 
                                        {

                                            for (let key in Object.entries(infospartenedestm))
                                            {

                                                let opt = document.createElement('option');
                                                opt.value = `${infospartenedestm[key].id_client}`;
                                                opt.innerHTML = `${infospartenedestm[key].nom_client} ${infospartenedestm[key].prenom_client}`;
                                                document.querySelector('#idsonnelspersomemesc').add(opt);
                                            }
                                                
                                        }
                                        else 
                                        {
                                            document.querySelector('#idsonnelspersomemesc').options.length = 1;
                                        }

                                    };
                                    httppaternesdestm.setRequestHeader('Content-Type', 'application/json');
                                    httppaternesdestm.send();

                                let paternstscdestin2m = document.querySelector('#idsonnelspersomemesc');
                                if (paternstscdestin2m !== null)
                                paternstscdestin2m.onchange = () => {
                                    let httpInfospersdestin2m;
                                        httpInfospersdestin2m = new XMLHttpRequest();
                                    document.querySelector('#persodestcompagniepersoesc').value = '';
                                    document.querySelector('#contactidpersoesc').style.display = 'none';
                                    document.querySelector('#idcontpersoesc').style.display = 'none';
                                    document.querySelector('#contactidpersoesc').value = '';
                                        var ternsdest2m = document.querySelector('#idsonnelspersomemesc').
                                            options[document.querySelector('#idsonnelspersomemesc').options.selectedIndex].value;
                                        httpInfospersdestin2m.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest2m}`, true);
                                    httpInfospersdestin2m.onload = () => {
                                        const infosperdestin2m = JSON.parse(httpInfospersdestin2m.responseText);
                                        
                                        if (Object.entries(infosperdestin2m).length >= 1) {
                                            
                                            document.querySelector('#nomdestidpersoesc').value = `${infosperdestin2m.nom_client}`;
                                            document.querySelector('#prenomdestidpersoesc').value = `${infosperdestin2m.prenom_client}`;
                                            document.querySelector('#compagniepassdestpersoesc').value = `${infosperdestin2m.id_client}`;
                                            document.querySelector('#idclientypedestpersoesc').value = `${infosperdestin2m.type_client}`;
                                            document.querySelector('#rclientcpdestpersoesc').value = `${infosperdestin2m.nom_client}`;
                                            document.querySelector('#prnclientcpdestpersoesc').value = `${infosperdestin2m.prenom_client}`;
                                            document.querySelector('#date_cnibdestidpersoesc').value = `${infosperdestin2m.date_delivre}`;
                                            
                                        } 
                                        else
                                        {
                                            document.querySelector('#nomdestidpersoesc').value = "";
                                            document.querySelector('#prenomdestidpersoesc').value = "";
                                            document.querySelector('#compagniepassdestpersoesc').value = "";
                                            document.querySelector('#rclientcpdestpersoesc').value = "";
                                            document.querySelector('#prnclientcpdestpersoesc').value = "";
                                            document.querySelector('#idclientypedestpersoesc').value = "";
                                            document.querySelector('#date_cnibdestidpersoesc').value = "";
                                        }
                                                                    
                                    };
                                    httpInfospersdestin2m.setRequestHeader('Content-Type', 'application/json');
                                    httpInfospersdestin2m.send();
                                };
                   
                            
                            }
                        if(personns === 'partenaire_client' || personns === 'partenaire_simple'){
                            document.querySelector('#sonnelpersomemesc').style.display = 'none';
                            document.querySelector('#idsonnelspersomemesc').style.display = 'none';
                            document.querySelector('#idsonnelspersomemesc').options.length = 1;
                            document.querySelector('#partcontpersoesc').style.display = 'block';
                            document.querySelector('#idpartespersoesc').style.display = 'block';
                            document.querySelector('#sonnelpersoesc').style.display = 'none';
                            document.querySelector('#idsonnelspersoesc').style.display = 'none';
                            document.querySelector('#idsonnelspersoesc').options.length = 1;
                            document.querySelector('#contactidpersoesc').style.display = 'none';
                            document.querySelector('#idcontpersoesc').style.display = 'none';
                            document.querySelector('#nomdestidpersoesc').value = '';
                            document.querySelector('#prenomdestidpersoesc').value = '';
                            document.querySelector('#compagniepassdestpersoesc').value = '';
                            document.querySelector('#idclientypedestpersoesc').value = '';
                            document.querySelector('#rclientcpdestpersoesc').value = '';
                            document.querySelector('#prnclientcpdestpersoesc').value = '';
                            document.querySelector('#contactidpersoesc').value = '';
                            document.querySelector('#idclientcontpersoesc').value = '';
                            
                            let httppaternsdest;
                                if (window.XMLHttpRequest) {
                                    httppaternsdest = new XMLHttpRequest();
                                } else if (window.ActiveXObject) {
                                    httppaternsdest = new ActiveXObject("Microsoft.XMLHTTP");
                                }
                                
                                httppaternsdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectpartenaire/${personns}`, true);
                                httppaternsdest.onload = () => {
                                    const infospartendest = JSON.parse(httppaternsdest.responseText);

                                    if (Object.entries(infospartendest).length >= 1) 
                                    {

                                        for (let key in Object.entries(infospartendest))
                                        {

                                            let opt = document.createElement('option');
                                            opt.value = `${infospartendest[key].id_client}`;
                                            opt.innerHTML = `${infospartendest[key].nom_client} ${infospartendest[key].prenom_client}`;
                                            document.querySelector('#idpartespersoesc').add(opt);
                                        }
                                            
                                    }
                                    else 
                                    {
                                        document.querySelector('#idpartespersoesc').options.length = 1;
                                    }

                                };
                                httppaternsdest.setRequestHeader('Content-Type', 'application/json');
                                httppaternsdest.send();

                                let paternstscdestin = document.querySelector('#idpartespersoesc');
                            if (paternstscdestin !== null)
                            paternstscdestin.onchange = () => {
                                let httpInfospersdestin;
                                    httpInfospersdestin = new XMLHttpRequest();
                                document.querySelector('#persodestcompagniepersoesc').value = '';
                                document.querySelector('#contactidpersoesc').style.display = 'none';
                                document.querySelector('#idcontpersoesc').style.display = 'none';
                                document.querySelector('#contactidpersoesc').value = '';
                                var ternsdest= document.querySelector('#idpartespersoesc').
                                    options[document.querySelector('#idpartespersoesc').options.selectedIndex].value;
                                httpInfospersdestin.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest}`, true);
                                httpInfospersdestin.onload = () => {
                                    const infosperdestin = JSON.parse(httpInfospersdestin.responseText);
                                    
                                    if (Object.entries(infosperdestin).length >= 1) {
                                        
                                        document.querySelector('#nomdestidpersoesc').value = `${infosperdestin.nom_client}`;
                                        document.querySelector('#prenomdestidpersoesc').value = `${infosperdestin.prenom_client}`;
                                        document.querySelector('#compagniepassdestpersoesc').value = `${infosperdestin.id_client}`;
                                        document.querySelector('#idclientypedestpersoesc').value = `${infosperdestin.type_client}`;
                                        document.querySelector('#rclientcpdestpersoesc').value = `${infosperdestin.nom_client}`;
                                        document.querySelector('#prnclientcpdestpersoesc').value = `${infosperdestin.prenom_client}`;
                                        document.querySelector('#date_cnibdestidpersoesc').value = `${infosperdestin.date_delivre}`;
                                    } 
                                    else
                                    {
                                        document.querySelector('#nomdestidpersoesc').value = "";
                                        document.querySelector('#prenomdestidpersoesc').value = "";
                                        document.querySelector('#compagniepassdestpersoesc').value = "";
                                        document.querySelector('#rclientcpdestpersoesc').value = "";
                                        document.querySelector('#prnclientcpdestpersoesc').value = "";
                                        document.querySelector('#idclientypedestpersoesc').value = "";
                                        document.querySelector('#date_cnibdestidpersoesc').value = "";
                                    }
                                                                
                                };
                                httpInfospersdestin.setRequestHeader('Content-Type', 'application/json');
                                httpInfospersdestin.send();
                            };
 
                        }
                        else
                        {


                            if(personns === 'partenaire_specifique'){

                                document.querySelector('#partcontpersoesc').style.display = 'block';
                                document.querySelector('#idpartespersoesc').style.display = 'block';
                                document.querySelector('#sonnelpersoesc').style.display = 'none';
                                document.querySelector('#idsonnelspersoesc').style.display = 'none';
                                document.querySelector('#idcontpersoesc').style.display = 'none';
                                document.querySelector('#contactidpersoesc').style.display = 'none';
                                document.querySelector('#sonnelpersomemesc').style.display = 'none';
                                document.querySelector('#idsonnelspersomemesc').style.display = 'none';
                                document.querySelector('#idsonnelspersomemesc').options.length = 1;
                                document.querySelector('#idsonnelspersoesc').options.length = 1;
                                
                                let httppaternesdest;
                                    if (window.XMLHttpRequest) {
                                        httppaternesdest = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) {
                                        httppaternesdest = new ActiveXObject("Microsoft.XMLHTTP");
                                    }
                                    
                                    httppaternesdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectpartenaire/${personns}`, true);
                                    httppaternesdest.onload = () => {
                                        const infospartenedest = JSON.parse(httppaternesdest.responseText);

                                        if (Object.entries(infospartenedest).length >= 1) 
                                        {

                                            for (let key in Object.entries(infospartenedest))
                                            {

                                                let opt = document.createElement('option');
                                                opt.value = `${infospartenedest[key].id_client}`;
                                                opt.innerHTML = `${infospartenedest[key].nom_client} ${infospartenedest[key].prenom_client}`;
                                                document.querySelector('#idpartespersoesc').add(opt);
                                            }
                                                
                                        }
                                        else 
                                        {
                                            document.querySelector('#idpartespersoesc').options.length = 1;
                                        }

                                    };
                                    httppaternesdest.setRequestHeader('Content-Type', 'application/json');
                                    httppaternesdest.send();

                                let paternstscdestin2 = document.querySelector('#idpartespersoesc');
                                if (paternstscdestin2 !== null)
                                paternstscdestin2.onchange = () => {
                                    let httpInfospersdestin2;
                                        httpInfospersdestin2 = new XMLHttpRequest();
                                    document.querySelector('#persodestcompagniepersoesc').value = '';
                                    document.querySelector('#contactidpersoesc').style.display = 'none';
                                    document.querySelector('#idcontpersoesc').style.display = 'none';
                                    document.querySelector('#contactidpersoesc').value = '';
                                        var ternsdest2 = document.querySelector('#idpartespersoesc').
                                            options[document.querySelector('#idpartespersoesc').options.selectedIndex].value;
                                        httpInfospersdestin2.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest2}`, true);
                                    httpInfospersdestin2.onload = () => {
                                        const infosperdestin2 = JSON.parse(httpInfospersdestin2.responseText);
                                        
                                        if (Object.entries(infosperdestin2).length >= 1) {
                                            
                                            document.querySelector('#nomdestidpersoesc').value = `${infosperdestin2.nom_client}`;
                                            document.querySelector('#prenomdestidpersoesc').value = `${infosperdestin2.prenom_client}`;
                                            document.querySelector('#compagniepassdestpersoesc').value = `${infosperdestin2.id_client}`;
                                            document.querySelector('#idclientypedestpersoesc').value = `${infosperdestin2.type_client}`;
                                            document.querySelector('#rclientcpdestpersoesc').value = `${infosperdestin2.nom_client}`;
                                            document.querySelector('#prnclientcpdestpersoesc').value = `${infosperdestin2.prenom_client}`;
                                            document.querySelector('#date_cnibdestidpersoesc').value = `${infosperdestin2.date_delivre}`;
                                            
                                        } 
                                        else
                                        {
                                            document.querySelector('#nomdestidpersoesc').value = "";
                                            document.querySelector('#prenomdestidpersoesc').value = "";
                                            document.querySelector('#compagniepassdestpersoesc').value = "";
                                            document.querySelector('#rclientcpdestpersoesc').value = "";
                                            document.querySelector('#prnclientcpdestpersoesc').value = "";
                                            document.querySelector('#idclientypedestpersoesc').value = "";
                                            document.querySelector('#date_cnibdestidpersoesc').value = "";
                                        }
                                                                    
                                    };
                                    httpInfospersdestin2.setRequestHeader('Content-Type', 'application/json');
                                    httpInfospersdestin2.send();
                                };
                   
                            
                            }
                            
                        }
                        
                        
                    }
        e.onclick = function () {
            let copersoForm = document.querySelector('#copersoFormesc');
            copersoForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/addpersoesc/${e.dataset.cle_compagnie}`);
        }

        var clique = true;

            $('#bottonpersoesc').click(function(event) 
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
/* --- addsbordesc.js --- */
document.addEventListener('DOMContentLoaded', () => {
   

    document.querySelectorAll('.addsbordesc').forEach(function (e) 
    {
        document.querySelector('h3#bordsTitleesc').innerHTML = `TIRAGE BORDEREAU PAR LIGNE`;

        let arcourr = document.querySelector('#deptscouridligneesc');
            if (arcourr !== null)
            arcourr.onchange = () => {
                
                document.querySelector('#courdeptidprogesc').options.length = 1;
                document.querySelector('#courdeptquartieridesc').options.length = 1;
                const lidlignecr = document.querySelector('#deptscouridligneesc')
                .options[document.querySelector('#deptscouridligneesc').options.selectedIndex].value;
                var ligne = parseLigneOption(lidlignecr);
                if (!ligne.gareDest) {
                    return;
                }
                let httptypequartr;
                httptypequartr = new XMLHttpRequest();
                
                httptypequartr.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifquart/${encodeURIComponent(ligne.gareDest)}`, true);
                httptypequartr.onload = () => 
                {
                    const courquar = JSON.parse(httptypequartr.responseText);
                    if (courquar == '') {
                        document.querySelector('#courdeptquartieridesc').options.length = 1;
                    }
                    else{
                        if (Object.entries(courquar).length >= 1) {
                                        
                            for (let key in Object.entries(courquar)) {
                                let opt = document.createElement('option');
                                opt.value = `${courquar[key].nom_quartier}`;
                                opt.innerHTML = `${courquar[key].nom_quartier}/${courquar[key].code_quart}`;
                                document.querySelector('#courdeptquartieridesc').add(opt);
                            }
                        } else {
                            document.querySelector('#courdeptquartieridesc').options.length = 1;
                        }
                    }
                    

                };
                httptypequartr.setRequestHeader('Content-Type', 'application/json');
                httptypequartr.send();
            };
            let infoligne = document.querySelector('#courdeptchoisirdateesc');
            if (infoligne !== null)
            infoligne.onchange = () => {
                let httpInfoprog;
                if (window.XMLHttpRequest) {
                    httpInfoprog = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfoprog = new ActiveXObject("Microsoft.XMLHTTP");
                }

                
                const lidligne = document.querySelector('#deptscouridligneesc')
                .options[document.querySelector('#deptscouridligneesc').options.selectedIndex].value;
                var ligne = parseLigneOption(lidligne);
                var verifidate = document.querySelector('#courdeptchoisirdateesc').value;
                document.querySelector('#courdeptidprogesc').options.length = 1;
                if (!ligne.ident || !verifidate) {
                    return;
                }
                httpInfoprog.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheure/${encodeURIComponent(ligne.ident)}/${verifidate}`, true);
                httpInfoprog.onload = () => {
                    const resultp = JSON.parse(httpInfoprog.responseText);
                    if(resultp == null){
                    
                    } else {
                        if (Object.entries(resultp).length >= 1) 
                        {
                           
                            for (let key in Object.entries(resultp)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resultp[key].id_ligneheure}`;
                                    opt.innerHTML = `${resultp[key].heure}`;
                                    document.querySelector('#courdeptidprogesc').add(opt);
                                }
                        } else {
                            document.querySelector('#courdeptidprogesc').options.length = 1;
                        }
                        
                    }
                };
                httpInfoprog.setRequestHeader('Content-Type', 'application/json');
                httpInfoprog.send();
                                     
            };
                    let infchaufbords = document.querySelector('#courstyppersoidesc');
                    if (infchaufbords !== null)
                    infchaufbords.onchange = () => 
                    {
                        document.querySelector('#coursidchaufesc').options.length = 1;
                        const chauffesbords = document.querySelector('#courstyppersoidesc')
                            .options[document.querySelector('#courstyppersoidesc').options.selectedIndex].value;

                        if(chauffesbords === 'chauffeur')
                        {
                            let httpInfosinfochaufbords;
                            if (window.XMLHttpRequest) {
                                httpInfosinfochaufbords = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfochaufbords = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfochaufbords.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifpersonne/${chauffesbords}`, true);
                            httpInfosinfochaufbords.onload = () => {
                                const resultchauffsbords = JSON.parse(httpInfosinfochaufbords.responseText);
                                
                                    if (Object.entries(resultchauffsbords).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultchauffsbords)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultchauffsbords[key].nomprenom_perso}`;
                                                opt.innerHTML = `${resultchauffsbords[key].nomprenom_perso}`;
                                                document.querySelector('#coursidchaufesc').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#coursidchaufesc').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfochaufbords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfochaufbords.send();   
                        }
                        if(chauffesbords === 'autrepersonnel')
                        {
                            let httpInfosinfopersobords;
                            if (window.XMLHttpRequest) {
                                httpInfosinfopersobords = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfopersobords = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfopersobords.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifclient/${chauffesbords}`, true);
                            httpInfosinfopersobords.onload = () => {
                                const resultpersobords = JSON.parse(httpInfosinfopersobords.responseText);
                                
                                    if (Object.entries(resultpersobords).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultpersobords)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultpersobords[key].nom_client} ${resultpersobords[key].prenom_client}`;
                                                opt.innerHTML = `${resultpersobords[key].nom_client} ${resultpersobords[key].prenom_client}`;
                                                document.querySelector('#coursidchaufesc').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#coursidchaufesc').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfopersobords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfopersobords.send();   
                        }
                        
                    };

                    let infconvoibords = document.querySelector('#courstyppersoid1esc');
                    if (infconvoibords !== null)
                    infconvoibords.onchange = () => 
                    {
                        document.querySelector('#couridconvoiesc').options.length = 1;
                        const convoisbords = document.querySelector('#courstyppersoid1esc')
                            .options[document.querySelector('#courstyppersoid1esc').options.selectedIndex].value;

                        if(convoisbords === 'convoyeur')
                        {
                            let httpInfosinfoconvbords;
                            if (window.XMLHttpRequest) {
                                httpInfosinfoconvbords = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfoconvbords = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfoconvbords.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifconvoi/${convoisbords}`, true);
                            httpInfosinfoconvbords.onload = () => {
                                const resultconvbords = JSON.parse(httpInfosinfoconvbords.responseText);
                                
                                    if (Object.entries(resultconvbords).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultconvbords)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultconvbords[key].nomprenom_perso}`;
                                                opt.innerHTML = `${resultconvbords[key].nomprenom_perso}`;
                                                document.querySelector('#couridconvoiesc').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#couridconvoiesc').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfoconvbords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfoconvbords.send();   
                        }
                        if(convoisbords === 'autrepersonnel')
                        {
                            let httpInfosinfopersosbords;
                            if (window.XMLHttpRequest) {
                                httpInfosinfopersosbords = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfopersosbords = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfopersosbords.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifclient/${convoisbords}`, true);
                            httpInfosinfopersosbords.onload = () => {
                                const resultpersosbords = JSON.parse(httpInfosinfopersosbords.responseText);
                                
                                    if (Object.entries(resultpersosbords).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultpersosbords)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultpersosbords[key].nom_client} ${resultpersosbords[key].prenom_client}`;
                                                opt.innerHTML = `${resultpersosbords[key].nom_client} ${resultpersosbords[key].prenom_client}`;
                                                document.querySelector('#couridconvoiesc').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#couridconvoiesc').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfopersosbords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfopersosbords.send();   
                        }
                    };
        e.onclick = function (){
            let bordesForm = document.querySelector('#bordesFormesc');
            bordesForm.setAttribute('action', `${APP_ROOT}/Rapport/listescourriersesc/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- addreception.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addreception').forEach(function (e) {
        document.querySelector('h3#reTitle').innerHTML = `RECEPTION`;

        let infosrecept = document.querySelector('#confirmer_infocode');
        if (infosrecept !== null)
            infosrecept.onclick = () => {
                let httpRequestRecep;
                
                if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                    httpRequestRecep = new XMLHttpRequest();
                } else if (window.ActiveXObject) { // IE 6 and older
                    httpRequestRecep = new ActiveXObject("Microsoft.XMLHTTP");
                }
                
                var cdcour = document.querySelector("#codecourrier").value;
                var gdar = document.querySelector("#gdidar").value;
                var sgdar = document.querySelector("#sgdiar").value;
                httpRequestRecep.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifcodecourrier/${cdcour}/${gdar}/${sgdar}`, true);
                httpRequestRecep.onload = () => {
                    const donnees = JSON.parse(httpRequestRecep.responseText);
                    if (donnees == null) {
                        
                        document.querySelector('#smscr').style.display = 'block';
                        document.querySelector('#erreurSmscour').innerHTML = `Veuillez vérifier le code saisi, ou ce courrier n'est pas encore arrivé.`;
                        document.querySelector('#nomexpt').innerHTML = ``;
                        document.querySelector('#prenomexpt').innerHTML = ``;
                        document.querySelector('#contactexpt').innerHTML = ``;
                        document.querySelector('#nomrecept').innerHTML = ``;
                        document.querySelector('#prenomrecept').innerHTML = ``;
                        document.querySelector('#contactrecept').innerHTML = ``;
                        document.querySelector('#refcourr').innerHTML = ``;
                        document.querySelector('#directioncour').innerHTML = ``;
                        document.querySelector('#codecou').innerHTML = ``;
                        document.querySelector('#heurecour').innerHTML = ``;
                        document.querySelector('#receptidentifedclidct').value = ``;
                        document.querySelector('#receptidentifedclidcttype').value = ``;

                    } else 
                    {
                               
                        if (Object.entries(donnees).length >= 1){
                                document.querySelector('#smscr').style.display = 'none';
                                document.querySelector('#refcourr').innerHTML = `LIBELLE : ${donnees.nombrecolis} ${donnees.naturecoli} ${donnees.naturecourrier}`;
                                document.querySelector('#heurecour').innerHTML = `LIGNE: ${donnees.nom_ligne} DATE : ${donnees.date_progr} HEURE: ${donnees.heure}`;
                                document.querySelector('#iddatevalid').innerHTML = `DATE DE VALIDATION: ${donnees.datevalider}`;
                                document.querySelector('#codecou').innerHTML = `REFERENCE: ${donnees.courrierexpid}`;
                                document.querySelector('#destident').value = `${donnees.receptid}`;
                                document.querySelector('#destclient').value = `${donnees.client_recept}`;
                                document.querySelector('#perdestclient').value = `${donnees.persorecep}`;
                                document.querySelector('#destnom').value = `${donnees.nom_client}`;
                                document.querySelector('#destprenom').value = `${donnees.prenom_client}`;
                                document.querySelector('#contdest').value = `${donnees.contact_client}`;
                                document.querySelector('#cnibdest').value = `${donnees.num_CNIB}`;
                                document.querySelector('#delivredest').value = `${donnees.date_delivre}`;
                                document.querySelector('#lieudest').value = `${donnees.lieu_delivre}`;
                                document.querySelector('#receptidentifedclidct').value = `${donnees.contact_client}`;
                                document.querySelector('#receptidentifedclidcttype').value = `${donnees.type_client}`;

                        } else {
                            
                        }
                    }       
                };
                httpRequestRecep.setRequestHeader('Content-Type', 'application/json');
                httpRequestRecep.send();
            };
            
           let inforp1 = document.querySelector('#contdest');
        if (inforp1 !== null)
            inforp1.onkeyup = () => {
                let httpInfosmd3;
                if (window.XMLHttpRequest) {
                    httpInfosmd3 = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfosmd3 = new ActiveXObject("Microsoft.XMLHTTP");
                }
                var verificatp3 = document.querySelector('#contdest').value;
                
                httpInfosmd3.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifinfos/${verificatp3}`, true);
                httpInfosmd3.onload = () => {
                    const infosp3 = JSON.parse(httpInfosmd3.responseText);
                    if (infosp3 == null) {

                                document.querySelector('#destnom').value = "";
                                document.querySelector('#destprenom').value = "";
                                document.querySelector('#cnibdest').value = "";
                                document.querySelector('#delivredest').value = "";
                                document.querySelector('#lieudest').value = "";
                                document.querySelector('#receptidentifedclid').value = "";
                    } else {
                        if (Object.entries(infosp3).length > 1) {
                            
                            if (infosp3.contact_client == verificatp3) {
                                document.querySelector('#destnom').value = `${infosp3.nom_client}`;
                                document.querySelector('#destprenom').value = `${infosp3.prenom_client}`;
                                document.querySelector('#cnibdest').value = `${infosp3.num_CNIB}`;
                                document.querySelector('#delivredest').value = `${infosp3.date_delivre}`;
                                document.querySelector('#lieudest').value = `${infosp3.lieu_delivre}`;
                                document.querySelector('#receptidentifedclid').value = `${infosp3.id_client}`;
                                
                            } else {
                                document.querySelector('#destnom').value = "";
                                document.querySelector('#destprenom').value = "";
                                document.querySelector('#cnibdest').value = "";
                                document.querySelector('#delivredest').value = "";
                                document.querySelector('#lieudest').value = "";
                                document.querySelector('#receptidentifedclid').value = "";
                            }
                        }
                    }
                };
                httpInfosmd3.setRequestHeader('Content-Type', 'application/json');
                httpInfosmd3.send();
            };
        e.onclick = function () {
            let recepForm = document.querySelector('#receptForm');
            recepForm.setAttribute('action', `${APP_ROOT}/Confirmation/updatedrecept/${e.dataset.cle_compagnie}`);
        }
    })
});
;
/* --- adreceptperso.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adreceptperso').forEach(function (e) {
        document.querySelector('h3#reTitleperso').innerHTML = `RECEPTION PERSONNEL`;

        let infosreceptperso = document.querySelector('#confirmer_infocodeperso');
        if (infosreceptperso !== null)
            infosreceptperso.onclick = () => {
                let httpRequestRecepperso;
                
                if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                    httpRequestRecepperso = new XMLHttpRequest();
                } else if (window.ActiveXObject) { // IE 6 and older
                    httpRequestRecepperso = new ActiveXObject("Microsoft.XMLHTTP");
                }
                
                var cdcourperso = document.querySelector("#codecourrierperso").value;
                var gdarperso = document.querySelector("#gdidarperso").value;
                var sgdarperso = document.querySelector("#sgdiarperso").value;
                httpRequestRecepperso.open('GET', window.location.origin + `${APP_ROOT}/courrier/verifcodecourrierperso/${cdcourperso}/${gdarperso}/${sgdarperso}`, true);
                httpRequestRecepperso.onload = () => {
                    const donneesperso = JSON.parse(httpRequestRecepperso.responseText);
                    if (donneesperso == null) {
                        
                        document.querySelector('#smscrperso').style.display = 'block';
                        document.querySelector('#erreurSmscourperso').innerHTML = `Veuillez vérifier le code saisi, ou ce courrier n'est pas encore arrivé.`;
                        document.querySelector('#nomexptperso').innerHTML = ``;
                        document.querySelector('#contactexptperso').innerHTML = ``;
                        document.querySelector('#nomreceptperso').innerHTML = ``;
                        document.querySelector('#contactreceptperso').innerHTML = ``;
                        document.querySelector('#refcourrperso').innerHTML = ``;
                        document.querySelector('#directioncourperso').innerHTML = ``;
                        document.querySelector('#codecouperso').innerHTML = ``;
                        document.querySelector('#heurecourperso').innerHTML = ``;


                    } else 
                    {
                               
                        if (Object.entries(donneesperso).length >= 1){
                                document.querySelector('#smscrperso').style.display = 'none';
                                document.querySelector('#refcourrperso').innerHTML = `LIBELLE : ${donneesperso.nombrecolis} ${donneesperso.naturecoli} ${donneesperso.naturecourrier}`;
                                document.querySelector('#heurecourperso').innerHTML = `LIGNE: ${donneesperso.nom_ligne} DATE : ${donneesperso.date_progr} HEURE: ${donneesperso.heure}`;
                                document.querySelector('#iddatevalidperso').innerHTML = `DATE DE VALIDATION: ${donneesperso.datevalider}`;
                                document.querySelector('#codecouperso').innerHTML = `REFERENCE: ${donneesperso.courrierexpid}`;
                                document.querySelector('#destidentperso').value = `${donneesperso.receptid}`;
                                document.querySelector('#destclientperso').value = `${donneesperso.persorecep}`;
                                document.querySelector('#perdestclientperso').value = `${donneesperso.persorecep}`;
                                document.querySelector('#destnomperso').value = `${donneesperso.nomprenom_perso}`;
                                document.querySelector('#contdestperso').value = `${donneesperso.contact_perso}`;
                        } else {
                            
                        }
                    }       
                };
                httpRequestRecepperso.setRequestHeader('Content-Type', 'application/json');
                httpRequestRecepperso.send();
            };
                    

        e.onclick = function () {
            let recepFormperso = document.querySelector('#receptFormperso');
            recepFormperso.setAttribute('action', `${APP_ROOT}/Confirmation/updatedreceptperso/${e.dataset.cle_compagnie}`);
        }
    })
});
;
/* --- adsbords.js --- */
document.addEventListener('DOMContentLoaded', () => {
   

    document.querySelectorAll('.adsbords').forEach(function (e) 
    {
        document.querySelector('h3#bordsTitlebg').innerHTML = `TIRAGE DE SUIVI`;

        function loadProgrammesBord() {
            var selectLigne = document.querySelector('#deptscouridlignebg');
            var selectDate = document.querySelector('#courdeptchoisirdatebg');
            var selectProg = document.querySelector('#courdeptidprogbg');
            if (!selectLigne || !selectDate || !selectProg) {
                return;
            }

            var ligne = parseLigneOption(selectLigne.options[selectLigne.selectedIndex].value);
            var verifidate = selectDate.value;
            selectProg.options.length = 1;

            if (!ligne.ident || !verifidate) {
                return;
            }

            var httpInfoprog = new XMLHttpRequest();
            httpInfoprog.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifprogramm/${encodeURIComponent(ligne.ident)}/${verifidate}`, true);
            httpInfoprog.onload = function () {
                var resultp;
                try {
                    resultp = JSON.parse(httpInfoprog.responseText);
                } catch (err) {
                    return;
                }

                if (!resultp || !resultp.length) {
                    selectProg.options.length = 1;
                    return;
                }

                resultp.forEach(function (item) {
                    var opt = document.createElement('option');
                    opt.value = `${item.code_progr}/${item.heure}/${item.id_ligneheure}/${item.depart_code}`;
                    opt.innerHTML = `${item.code_progr}/${item.heure}`;
                    selectProg.add(opt);
                });
            };
            httpInfoprog.send();
        }

        let arcourr = document.querySelector('#deptscouridlignebg');
            if (arcourr !== null)
            arcourr.onchange = () => {
                
                document.querySelector('#courdeptidprogbg').options.length = 1;
                document.querySelector('#courdeptquartieridbg').options.length = 1;
                const lidlignecr = document.querySelector('#deptscouridlignebg')
                .options[document.querySelector('#deptscouridlignebg').options.selectedIndex].value;
                var ligne = parseLigneOption(lidlignecr);
                if (!ligne.gareDest) {
                    return;
                }
                let httptypequartr;
                httptypequartr = new XMLHttpRequest();
                
                httptypequartr.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifquart/${encodeURIComponent(ligne.gareDest)}`, true);
                httptypequartr.onload = () => 
                {
                    const courquar = JSON.parse(httptypequartr.responseText);
                    if (courquar == '') {
                        document.querySelector('#courdeptquartieridbg').options.length = 1;
                    }
                    else{
                        if (Object.entries(courquar).length >= 1) {
                                        
                            for (let key in Object.entries(courquar)) {
                                let opt = document.createElement('option');
                                opt.value = `${courquar[key].nom_quartier}`;
                                opt.innerHTML = `${courquar[key].nom_quartier}/${courquar[key].code_quart}`;
                                document.querySelector('#courdeptquartieridbg').add(opt);
                            }
                        } else {
                            document.querySelector('#courdeptquartieridbg').options.length = 1;
                        }
                    }
                    

                };
                httptypequartr.setRequestHeader('Content-Type', 'application/json');
                httptypequartr.send();
                loadProgrammesBord();
            };
            let infoligne = document.querySelector('#courdeptchoisirdatebg');
            if (infoligne !== null)
            infoligne.onchange = () => {
                loadProgrammesBord();
                                     
            };
                    let infchaufbords = document.querySelector('#courstyppersoidbg');
                    if (infchaufbords !== null)
                    infchaufbords.onchange = () => 
                    {
                        document.querySelector('#coursidchaufbg').options.length = 1;
                        const chauffesbords = document.querySelector('#courstyppersoidbg')
                            .options[document.querySelector('#courstyppersoidbg').options.selectedIndex].value;

                        if(chauffesbords === 'chauffeur')
                        {
                            let httpInfosinfochaufbords;
                            if (window.XMLHttpRequest) {
                                httpInfosinfochaufbords = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfochaufbords = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfochaufbords.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifpersonne/${chauffesbords}`, true);
                            httpInfosinfochaufbords.onload = () => {
                                const resultchauffsbords = JSON.parse(httpInfosinfochaufbords.responseText);
                                
                                    if (Object.entries(resultchauffsbords).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultchauffsbords)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultchauffsbords[key].nomprenom_perso}`;
                                                opt.innerHTML = `${resultchauffsbords[key].nomprenom_perso}`;
                                                document.querySelector('#coursidchaufbg').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#coursidchaufbg').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfochaufbords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfochaufbords.send();   
                        }
                        if(chauffesbords === 'autrepersonnel')
                        {
                            let httpInfosinfopersobords;
                            if (window.XMLHttpRequest) {
                                httpInfosinfopersobords = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfopersobords = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfopersobords.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifclient/${chauffesbords}`, true);
                            httpInfosinfopersobords.onload = () => {
                                const resultpersobords = JSON.parse(httpInfosinfopersobords.responseText);
                                
                                    if (Object.entries(resultpersobords).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultpersobords)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultpersobords[key].nom_client} ${resultpersobords[key].prenom_client}`;
                                                opt.innerHTML = `${resultpersobords[key].nom_client} ${resultpersobords[key].prenom_client}`;
                                                document.querySelector('#coursidchaufbg').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#coursidchaufbg').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfopersobords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfopersobords.send();   
                        }
                        
                    };

                    let infconvoibords = document.querySelector('#courstyppersoid1bg');
                    if (infconvoibords !== null)
                    infconvoibords.onchange = () => 
                    {
                        document.querySelector('#couridconvoibg').options.length = 1;
                        const convoisbords = document.querySelector('#courstyppersoid1bg')
                            .options[document.querySelector('#courstyppersoid1bg').options.selectedIndex].value;

                        if(convoisbords === 'convoyeur')
                        {
                            let httpInfosinfoconvbords;
                            if (window.XMLHttpRequest) {
                                httpInfosinfoconvbords = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfoconvbords = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfoconvbords.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifconvoi/${convoisbords}`, true);
                            httpInfosinfoconvbords.onload = () => {
                                const resultconvbords = JSON.parse(httpInfosinfoconvbords.responseText);
                                
                                    if (Object.entries(resultconvbords).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultconvbords)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultconvbords[key].nomprenom_perso}`;
                                                opt.innerHTML = `${resultconvbords[key].nomprenom_perso}`;
                                                document.querySelector('#couridconvoibg').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#couridconvoibg').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfoconvbords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfoconvbords.send();   
                        }
                        if(convoisbords === 'autrepersonnel')
                        {
                            let httpInfosinfopersosbords;
                            if (window.XMLHttpRequest) {
                                httpInfosinfopersosbords = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfopersosbords = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfopersosbords.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifclient/${convoisbords}`, true);
                            httpInfosinfopersosbords.onload = () => {
                                const resultpersosbords = JSON.parse(httpInfosinfopersosbords.responseText);
                                
                                    if (Object.entries(resultpersosbords).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultpersosbords)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultpersosbords[key].nom_client} ${resultpersosbords[key].prenom_client}`;
                                                opt.innerHTML = `${resultpersosbords[key].nom_client} ${resultpersosbords[key].prenom_client}`;
                                                document.querySelector('#couridconvoibg').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#couridconvoibg').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfopersosbords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfopersosbords.send();   
                        }
                    };
        e.onclick = function (){
            let bordesForm = document.querySelector('#bordesFormbg');
            bordesForm.setAttribute('action', `${APP_ROOT}/Rapport/listesbagages/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- adsbordst.js --- */
document.addEventListener('DOMContentLoaded', () => {
   

    document.querySelectorAll('.adsbordst').forEach(function (e) 
    {
        document.querySelector('h3#bordsTitlebgt').innerHTML = `TIRAGE DE SUIVI TPE`;

        function loadProgrammesBordT() {
            var selectLigne = document.querySelector('#deptscouridlignebgt');
            var selectDate = document.querySelector('#courdeptchoisirdatebgt');
            var selectProg = document.querySelector('#courdeptidprogbgt');
            if (!selectLigne || !selectDate || !selectProg) {
                return;
            }

            var ligne = parseLigneOption(selectLigne.options[selectLigne.selectedIndex].value);
            var verifidate = selectDate.value;
            selectProg.options.length = 1;

            if (!ligne.ident || !verifidate) {
                return;
            }

            var httpInfoprog = new XMLHttpRequest();
            httpInfoprog.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifprogramm/${encodeURIComponent(ligne.ident)}/${verifidate}`, true);
            httpInfoprog.onload = function () {
                var resultp;
                try {
                    resultp = JSON.parse(httpInfoprog.responseText);
                } catch (err) {
                    return;
                }

                if (!resultp || !resultp.length) {
                    selectProg.options.length = 1;
                    return;
                }

                resultp.forEach(function (item) {
                    var opt = document.createElement('option');
                    opt.value = `${item.code_progr}/${item.heure}/${item.id_ligneheure}/${item.depart_code}`;
                    opt.innerHTML = `${item.code_progr}/${item.heure}`;
                    selectProg.add(opt);
                });
            };
            httpInfoprog.send();
        }

        let arcourr = document.querySelector('#deptscouridlignebgt');
            if (arcourr !== null)
            arcourr.onchange = () => {
                
                document.querySelector('#courdeptidprogbgt').options.length = 1;
                document.querySelector('#courdeptquartieridbgt').options.length = 1;
                const lidlignecr = document.querySelector('#deptscouridlignebgt')
                .options[document.querySelector('#deptscouridlignebgt').options.selectedIndex].value;
                var ligne = parseLigneOption(lidlignecr);
                if (!ligne.gareDest) {
                    return;
                }
                let httptypequartr;
                httptypequartr = new XMLHttpRequest();
                
                httptypequartr.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifquart/${encodeURIComponent(ligne.gareDest)}`, true);
                httptypequartr.onload = () => 
                {
                    const courquar = JSON.parse(httptypequartr.responseText);
                    if (courquar == '') {
                        document.querySelector('#courdeptquartieridbgt').options.length = 1;
                    }
                    else{
                        if (Object.entries(courquar).length >= 1) {
                                        
                            for (let key in Object.entries(courquar)) {
                                let opt = document.createElement('option');
                                opt.value = `${courquar[key].nom_quartier}`;
                                opt.innerHTML = `${courquar[key].nom_quartier}/${courquar[key].code_quart}`;
                                document.querySelector('#courdeptquartieridbgt').add(opt);
                            }
                        } else {
                            document.querySelector('#courdeptquartieridbgt').options.length = 1;
                        }
                    }
                    

                };
                httptypequartr.setRequestHeader('Content-Type', 'application/json');
                httptypequartr.send();
                loadProgrammesBordT();
            };
            let infoligne = document.querySelector('#courdeptchoisirdatebgt');
            if (infoligne !== null)
            infoligne.onchange = () => {
                loadProgrammesBordT();
                                     
            };
                    let infchaufbords = document.querySelector('#courstyppersoidbgt');
                    if (infchaufbords !== null)
                    infchaufbords.onchange = () => 
                    {
                        document.querySelector('#coursidchaufbgt').options.length = 1;
                        const chauffesbords = document.querySelector('#courstyppersoidbgt')
                            .options[document.querySelector('#courstyppersoidbgt').options.selectedIndex].value;

                        if(chauffesbords === 'chauffeur')
                        {
                            let httpInfosinfochaufbords;
                            if (window.XMLHttpRequest) {
                                httpInfosinfochaufbords = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfochaufbords = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfochaufbords.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifpersonne/${chauffesbords}`, true);
                            httpInfosinfochaufbords.onload = () => {
                                const resultchauffsbords = JSON.parse(httpInfosinfochaufbords.responseText);
                                
                                    if (Object.entries(resultchauffsbords).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultchauffsbords)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultchauffsbords[key].nomprenom_perso}`;
                                                opt.innerHTML = `${resultchauffsbords[key].nomprenom_perso}`;
                                                document.querySelector('#coursidchaufbgt').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#coursidchaufbgt').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfochaufbords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfochaufbords.send();   
                        }
                        if(chauffesbords === 'autrepersonnel')
                        {
                            let httpInfosinfopersobords;
                            if (window.XMLHttpRequest) {
                                httpInfosinfopersobords = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfopersobords = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfopersobords.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifclient/${chauffesbords}`, true);
                            httpInfosinfopersobords.onload = () => {
                                const resultpersobords = JSON.parse(httpInfosinfopersobords.responseText);
                                
                                    if (Object.entries(resultpersobords).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultpersobords)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultpersobords[key].nom_client} ${resultpersobords[key].prenom_client}`;
                                                opt.innerHTML = `${resultpersobords[key].nom_client} ${resultpersobords[key].prenom_client}`;
                                                document.querySelector('#coursidchaufbgt').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#coursidchaufbgt').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfopersobords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfopersobords.send();   
                        }
                        
                    };

                    let infconvoibords = document.querySelector('#courstyppersoid1bgt');
                    if (infconvoibords !== null)
                    infconvoibords.onchange = () => 
                    {
                        document.querySelector('#couridconvoibgt').options.length = 1;
                        const convoisbords = document.querySelector('#courstyppersoid1bgt')
                            .options[document.querySelector('#courstyppersoid1bgt').options.selectedIndex].value;

                        if(convoisbords === 'convoyeur')
                        {
                            let httpInfosinfoconvbords;
                            if (window.XMLHttpRequest) {
                                httpInfosinfoconvbords = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfoconvbords = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfoconvbords.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifconvoi/${convoisbords}`, true);
                            httpInfosinfoconvbords.onload = () => {
                                const resultconvbords = JSON.parse(httpInfosinfoconvbords.responseText);
                                
                                    if (Object.entries(resultconvbords).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultconvbords)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultconvbords[key].nomprenom_perso}`;
                                                opt.innerHTML = `${resultconvbords[key].nomprenom_perso}`;
                                                document.querySelector('#couridconvoibgt').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#couridconvoibgt').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfoconvbords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfoconvbords.send();   
                        }
                        if(convoisbords === 'autrepersonnel')
                        {
                            let httpInfosinfopersosbords;
                            if (window.XMLHttpRequest) {
                                httpInfosinfopersosbords = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosinfopersosbords = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                        
                            httpInfosinfopersosbords.open('GET', window.location.origin + `${APP_ROOT}/personnels/verifclient/${convoisbords}`, true);
                            httpInfosinfopersosbords.onload = () => {
                                const resultpersosbords = JSON.parse(httpInfosinfopersosbords.responseText);
                                
                                    if (Object.entries(resultpersosbords).length >= 1) 
                                    {
                                        for (let key in Object.entries(resultpersosbords)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${resultpersosbords[key].nom_client} ${resultpersosbords[key].prenom_client}`;
                                                opt.innerHTML = `${resultpersosbords[key].nom_client} ${resultpersosbords[key].prenom_client}`;
                                                document.querySelector('#couridconvoibgt').add(opt);
                                            }
                                    } else {
                                        document.querySelector('#couridconvoibgt').options.length = 1;
                                    }
                                    
                                
                            };
                            httpInfosinfopersosbords.setRequestHeader('Content-Type', 'application/json');
                            httpInfosinfopersosbords.send();   
                        }
                    };
        e.onclick = function (){
            let bordesForm = document.querySelector('#bordesFormbgt');
            
            bordesForm.setAttribute('action', `${APP_ROOT}/Historique_Passagers/listesbagagestpe/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- addbordt.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addbordt').forEach(function (e) 
    {
        document.querySelector('h3#bordTitlet').innerHTML = `TIRAGE BORDEREAU`;


                    
            let arcourr = document.querySelector('#couridlignedeptt');
            if (arcourr !== null)
            arcourr.onchange = () => {
                
                document.querySelector('#choisirheurecourdeptt').options.length = 1;
                document.querySelector('#quartieridbgt').options.length = 1;
                const lidlignecr = document.querySelector('#couridlignedeptt')
                .options[document.querySelector('#couridlignedeptt').options.selectedIndex].value;
                var ligne = parseLigneOption(lidlignecr);
                if (!ligne.gareDest) {
                    return;
                }
                let httptypequartr;
                httptypequartr = new XMLHttpRequest();
                
                httptypequartr.open('GET', window.location.origin + `${APP_ROOT}/Confirmation/verifquart/${ligne.gareDest}`, true);
                httptypequartr.onload = () => 
                {
                    const courquar = JSON.parse(httptypequartr.responseText);
                    if (courquar == '') {
                        document.querySelector('#quartieridbgt').options.length = 1;
                    }
                    else{
                        if (Object.entries(courquar).length >= 1) {
                                        
                            for (let key in Object.entries(courquar)) {
                                let opt = document.createElement('option');
                                opt.value = `${courquar[key].nom_quartier}`;
                                opt.innerHTML = `${courquar[key].nom_quartier}/${courquar[key].code_quart}`;
                                document.querySelector('#quartieridbgt').add(opt);
                            }
                        } else {
                            document.querySelector('#quartieridbgt').options.length = 1;
                        }
                    }
                    

                };
                httptypequartr.setRequestHeader('Content-Type', 'application/json');
                httptypequartr.send();
            };
                    let infdatecour = document.querySelector('#choisirdatecourdeptt');
                    
                    if (infdatecour !== null) 
                    infdatecour.onchange = () => 
                    {
                    
                        let httpInfoscodebordereau;
                        if (window.XMLHttpRequest) {
                            httpInfoscodebordereau = new XMLHttpRequest();
                        } else if (window.ActiveXObject) {
                            httpInfoscodebordereau = new ActiveXObject("Microsoft.XMLHTTP");
                        }
                            document.querySelector('#choisirheurecourdeptt').options.length = 1;
                            document.querySelector('#idprogcourdeptt').options.length = 1;

                        var verifdatebord = document.querySelector('#choisirdatecourdeptt').value;
                        const veriflignebord = document.querySelector('#couridlignedeptt')
                                .options[document.querySelector('#couridlignedeptt').options.selectedIndex].value;

                        
                        httpInfoscodebordereau.open('GET', window.location.origin + `${APP_ROOT}/Confirmation/verifitiragedepart/${veriflignebord}/${verifdatebord}`, true);
                        httpInfoscodebordereau.onload = () => 
                        {
                            const heurebord = JSON.parse(httpInfoscodebordereau.responseText);
                            if(heurebord == ''){
                                document.querySelector('#infosmsheuret').style.display = 'block';
                                document.querySelector('#erreurinfoheuret').innerHTML = `Il n'y a pas de programme pour le moment`;
                            } else
                            {
                                if (Object.entries(heurebord).length >= 1) 
                                {
                                        document.querySelector('#infosmsheuret').style.display = 'none';

                                        for (let key in Object.entries(heurebord)) {
                                            document.querySelector('#chaufdeptt').value = `${heurebord[key].chauff}`;
                                            document.querySelector('#convoideptt').value = `${heurebord[key].convoy}`;
                                            document.querySelector('#ligndeptt').value = `${heurebord[key].nom_ligne}`;
                                            document.querySelector('#datedeptt').value = `${heurebord[key].datedepart_bus}`;
                                            document.querySelector('#progdeptt').value = `${heurebord[key].depart_code}`;

                                                let opt = document.createElement('option');
                                                opt.value = `${heurebord[key].id_ligneheure}/${heurebord[key].heure}`;
                                                opt.innerHTML = `${heurebord[key].heure}`;
                                                document.querySelector('#choisirheurecourdeptt').add(opt);

                                                
                                            }
                                } else {

                                    document.querySelector('#choisirheurecourdeptt').options.length = 1;

                                }
                            }   
                        };
                        httpInfoscodebordereau.setRequestHeader('Content-Type', 'application/json');
                        httpInfoscodebordereau.send();
                    };
                   
                
                let hrcour = document.querySelector('#choisirheurecourdeptt');
                    
                    if (hrcour !== null) 
                    hrcour.onchange = () => 
                    {
                    
                        let httpprog;
                        if (window.XMLHttpRequest) {
                            httpprog = new XMLHttpRequest();
                        } else if (window.ActiveXObject) {
                            httpprog = new ActiveXObject("Microsoft.XMLHTTP");
                        }
                            document.querySelector('#idprogcourdeptt').options.length = 1;

                        var verifhrcour = document.querySelector('#choisirdatecourdeptt').value;
                        const veriflignehrcour = document.querySelector('#couridlignedeptt')
                                .options[document.querySelector('#couridlignedeptt').options.selectedIndex].value;

                            const veriflignehr1 = document.querySelector('#choisirheurecourdeptt')
                                .options[document.querySelector('#choisirheurecourdeptt').options.selectedIndex].value;

                                var veriflignehr2 = veriflignehr1.split('/');
                            var hrex1 = veriflignehr2[0];
                            var hrex2 = veriflignehr2[1];

                        httpprog.open('GET', window.location.origin + `${APP_ROOT}/Confirmation/verifitiragedeparth/${veriflignehrcour}/${verifhrcour}/${hrex1}`, true);
                        httpprog.onload = () => 
                        {
                            const hprog = JSON.parse(httpprog.responseText);
                            if(hprog == ''){
                                

                            } else 
                            {
                                if (Object.entries(hprog).length >= 1) 
                                {

                                        for (let key in Object.entries(hprog)) {
                                            
                                                let opt = document.createElement('option');
                                                opt.value = `${hprog[key].code_progr}/${hprog[key].depart_code}/${hprog[key].chauff}/${hprog[key].convoy}`;
                                                opt.innerHTML = `${hprog[key].code_progr}/${hprog[key].depart_code}`;
                                                document.querySelector('#idprogcourdeptt').add(opt);

                                            }
                                } else {
                                    
                                    document.querySelector('#idprogcourdeptt').options.length = 1;

                                }
                            }   
                        };
                        httpprog.setRequestHeader('Content-Type', 'application/json');
                        httpprog.send();
                    };
        e.onclick = function (){
            let bordeForm = document.querySelector('#bordeFormt');
            bordeForm.setAttribute('action', `${APP_ROOT}/Rapport/listebisep/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- adsuivis.js --- */
document.addEventListener('DOMContentLoaded', () => {
   

    document.querySelectorAll('.adsuivis').forEach(function (e) 
    {
        document.querySelector('h3#suiviTitlebg').innerHTML = `ENREGISTREMENT BAGAGES`;

            function loadProgrammesSuiviLegacy() {
                var selectLigne = document.querySelector('#deptscouridlignesuivi');
                var selectDate = document.querySelector('#courdeptchoisirdatesuivi');
                var selectProg = document.querySelector('#courdeptidprogsuivi');
                if (!selectLigne || !selectDate || !selectProg) {
                    return;
                }

                var ligne = parseLigneOption(selectLigne.options[selectLigne.selectedIndex].value);
                var verifidate = selectDate.value;
                selectProg.options.length = 1;

                if (!ligne.ident || !verifidate) {
                    return;
                }

                var httpInfoprog = new XMLHttpRequest();
                httpInfoprog.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifprogramm/${encodeURIComponent(ligne.ident)}/${verifidate}`, true);
                httpInfoprog.onload = function () {
                    var resultp;
                    try {
                        resultp = JSON.parse(httpInfoprog.responseText);
                    } catch (err) {
                        return;
                    }

                    if (!resultp || !resultp.length) {
                        selectProg.options.length = 1;
                        return;
                    }

                    resultp.forEach(function (item) {
                        var opt = document.createElement('option');
                        opt.value = `${item.code_progr}/${item.heure}/${item.id_ligneheure}/${item.depart_code}`;
                        opt.innerHTML = `${item.code_progr}/${item.heure}`;
                        selectProg.add(opt);
                    });
                };
                httpInfoprog.send();
            }

            let infolignes = document.querySelector('#deptscouridlignesuivi');
            if (infolignes !== null) {
                infolignes.onchange = () => {
                    loadProgrammesSuiviLegacy();
                };
            }

            let infoligne = document.querySelector('#courdeptchoisirdatesuivi');
            if (infoligne !== null)
            infoligne.onchange = () => {
                loadProgrammesSuiviLegacy();
                                     
            };
            let infrecubag = document.querySelector('#numcoderecu');
        if (infrecubag !== null)
            infrecubag.onkeyup = () => {
                let httpInfosbag;
                if (window.XMLHttpRequest) {
                    httpInfosbag = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfosbag = new ActiveXObject("Microsoft.XMLHTTP");
                }

                var verificatbag = document.querySelector('#numcoderecu').value;
                
                const lidlignes = document.querySelector('#deptscouridlignesuivi')
                .options[document.querySelector('#deptscouridlignesuivi').options.selectedIndex].value;
                var lidlignes2 = parseLigneOption(lidlignes).ident;
                httpInfosbag.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinforecus/${verificatbag}/${lidlignes2}`, true);
                httpInfosbag.onload = () => {
                    
                    const infosbag = JSON.parse(httpInfosbag.responseText);
                    if (infosbag == null) {

                        document.querySelector('#smsmbg').style.display = 'block';
                        document.querySelector('#smsmvfbg').innerHTML = `Verifier le code de bagage saisi!`;
                        document.querySelector('#idbagenv').value = "";
                        document.querySelector('#gddeptsuiviid').value = "";
                        document.querySelector('#sousgddeptsuiviid').value = "";
                        document.querySelector('#typbagid').value = "";
                        document.querySelector('#nombrebgsuiviid').value = "";
                        document.querySelector('#contenubgsuiviid').value = "";
                        document.querySelector('#idgarbag').value = "";
                    } else {
                        if (Object.entries(infosbag).length > 1) {
                            
                            if (infosbag.id_bagage == verificatbag && infosbag.ident_ligne == lidlignes2){

                                console.debug(`${infosbag.id_bagage}-${verificatbag}-${infosbag.ident_ligne}-${lidlignes2}`, console.memory);
                                document.querySelector('#idbagenv').value = `${infosbag.id_bagage}`;
                                document.querySelector('#gddeptsuiviid').value = `${infosbag.idgarebag}`;
                                document.querySelector('#sousgddeptsuiviid').value = `${infosbag.idsgarebag}`;
                                document.querySelector('#typbagid').value = `${infosbag.typebagages}`;
                                document.querySelector('#nombrebgsuiviid').value = `${infosbag.nombrebagage}`;
                                document.querySelector('#contenubgsuiviid').value = `${infosbag.contenubagage}`;
                                document.querySelector('#idgarbag').value = `${infosbag.gidarrbag}`;
                                document.querySelector('#smsmbg').style.display = 'none';
                            } else {
                                document.querySelector('#idbagenv').value = "";
                                document.querySelector('#gddeptsuiviid').value = "";
                                document.querySelector('#sousgddeptsuiviid').value = "";
                                document.querySelector('#typbagid').value = "";
                                document.querySelector('#nombrebgsuiviid').value = "";
                                document.querySelector('#contenubgsuiviid').value = "";
                                document.querySelector('#idgarbag').value = "";
                                document.querySelector('#smsmbg').style.display = 'block';
                                document.querySelector('#smsmvfbg').innerHTML = `Verifier le code de bagage saisi!`;
                    
                            }
                        }
                    }
                };
                httpInfosbag.setRequestHeader('Content-Type', 'application/json');
                httpInfosbag.send();
            };

            verifnb = function () 
            {
                var entree = parseInt(document.querySelector('#nombreenvid').value);
                    var n = document.querySelector('#nombreenvid').value;
                    var exist = parseInt(document.querySelector('#nombrebgsuiviid').value);
                        
                if(entree > exist) 
                {
                    document.querySelector('#smsmtbg').style.display = 'block';
                    document.querySelector('#smsmontantbg').innerHTML = `le mombre que vous aviez saisi dépasse le nombre de bagage`;
                    
                    document.querySelector('#nombreenvid').value = 'VERIFIER NOMBRE';  
                } 
                else
                {

                    document.querySelector('#smsmtbg').style.display = 'none';

                    document.querySelector('#nombreenvid').value = n ;
                    
                }
            };

        e.onclick = function (){
            let bordesForm = document.querySelector('#bordesFormsuivi');
            bordesForm.setAttribute('action', `${APP_ROOT}/Confirmation/enregbagages/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- sadsuivis.js --- */
document.addEventListener('DOMContentLoaded', () => {
   
    document.querySelectorAll('.sadsuivis').forEach(function (e) 
    {
        document.querySelector('h3#ssuiviTitlebg').innerHTML = `ENREGISTREMENT BAGAGES`;

            function loadProgrammesSuivi() {
                var selectLigne = document.querySelector('#sdeptscouridlignesuivi');
                var selectDate = document.querySelector('#scourdeptchoisirdatesuivi');
                var selectProg = document.querySelector('#scourdeptidprogsuivi');
                if (!selectLigne || !selectDate || !selectProg) {
                    return;
                }

                var ligne = parseLigneOption(selectLigne.options[selectLigne.selectedIndex].value);
                var verifidate = selectDate.value;
                selectProg.options.length = 1;

                if (!ligne.ident || !verifidate) {
                    return;
                }

                var httpInfoprog = new XMLHttpRequest();
                httpInfoprog.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifprogramm/${encodeURIComponent(ligne.ident)}/${verifidate}`, true);
                httpInfoprog.onload = function () {
                    var resultp;
                    try {
                        resultp = JSON.parse(httpInfoprog.responseText);
                    } catch (err) {
                        return;
                    }

                    if (!resultp || !resultp.length) {
                        selectProg.options.length = 1;
                        return;
                    }

                    resultp.forEach(function (item) {
                        var opt = document.createElement('option');
                        opt.value = `${item.code_progr}/${item.ident_ligne}/${item.heure}/${item.id_ligneheure}/${item.depart_code}`;
                        opt.innerHTML = `${item.code_progr}/${item.heure}`;
                        selectProg.add(opt);
                    });
                };
                httpInfoprog.send();
            }

            let infoligner = document.querySelector('#sdeptscouridlignesuivi');
            if (infoligner !== null)
            infoligner.onchange = () => {
                document.querySelector('#scourdeptidprogsuivi').options.length = 1;
                document.querySelector('#quartieridbgsuivi').options.length = 1;
                document.querySelector('#snumcoderecu').value = '';
                document.querySelector('#snombreenvid').value = '';
                let httptypequartrbg;

                    const lidlignes = document.querySelector('#sdeptscouridlignesuivi')
                    .options[document.querySelector('#sdeptscouridlignesuivi').options.selectedIndex].value;
                    var ligne = parseLigneOption(lidlignes);
                    if (!ligne.gareDest) {
                        return;
                    }
                    httptypequartrbg = new XMLHttpRequest();
                    
                    httptypequartrbg.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifquart/${encodeURIComponent(ligne.gareDest)}`, true);
                    httptypequartrbg.onload = () => 
                    {
                        const courquarbg = JSON.parse(httptypequartrbg.responseText);
                        if (courquarbg == '') {
                            document.querySelector('#quartieridbgsuivi').options.length = 1;
                        }
                        else{
                            if (Object.entries(courquarbg).length >= 1) {
                                            
                                for (let key in Object.entries(courquarbg)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${courquarbg[key].nom_quartier}`;
                                    opt.innerHTML = `${courquarbg[key].nom_quartier}`;
                                    document.querySelector('#quartieridbgsuivi').add(opt);
                                }
                            } else {
                                document.querySelector('#quartieridbgsuivi').options.length = 1;
                            }
                        }
                    };
                    httptypequartrbg.setRequestHeader('Content-Type', 'application/json');
                    httptypequartrbg.send();

                    loadProgrammesSuivi();
            }
            let infoligne = document.querySelector('#scourdeptchoisirdatesuivi');
            if (infoligne !== null)
            infoligne.onchange = () => {
                document.querySelector('#snumcoderecu').value = '';
                document.querySelector('#snombreenvid').value = '';
                loadProgrammesSuivi();
            };

            /*let infrecubag = document.querySelector('#snumcoderecu');
            if (infrecubag !== null)
            infrecubag.onkeyup = () => {
                let httpInfosbag;
                if (window.XMLHttpRequest) {
                    httpInfosbag = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfosbag = new ActiveXObject("Microsoft.XMLHTTP");
                }

                var anencrbag = document.querySelector('#idanencourenv').value;

                var verificatbag = document.querySelector('#snumcoderecu').value;

                 const verife = `"${verificatbag}${anencrbag}"`;

                const lidlignes = document.querySelector('#sdeptscouridlignesuivi')
                .options[document.querySelector('#sdeptscouridlignesuivi').options.selectedIndex].value;
                var lidlignes1 = lidlignes.split('/');
                var lidlignes2 = lidlignes1[0];
                httpInfosbag.open('GET', window.location.origin + `${APP_ROOT}/confirmation/sverifinforecus/${verificatbag}${anencrbag}`, true);
                httpInfosbag.onload = () => {
                    
                    const infosbag = JSON.parse(httpInfosbag.responseText);
                    if (infosbag == null) {
                        document.querySelector('#ssmsmbg').style.display = 'block';
                        document.querySelector('#ssmsmvfbg').innerHTML = `Verifier le code de bagage saisi!`;
                        document.querySelector('#sidbagenv').value = "";
                        document.querySelector('#sgddeptsuiviid').value = "";
                        document.querySelector('#ssousgddeptsuiviid').value = "";
                        document.querySelector('#stypbagid').value = "";
                        document.querySelector('#snombrebgsuiviid').value = "";
                        document.querySelector('#scontenubgsuiviid').value = "";
                        document.querySelector('#sidgarbag').value = "";
                        document.querySelector('#lgidbagages').value = "";
                    } else {
                        if (Object.entries(infosbag).length > 1) {
                            
                            if (String(infosbag.id_bagage) == String(verife)) {
                                console.debug(`${infosbag.id_bagage}-${verife}-${infosbag.ident_ligne}-${lidlignes2}`, console.memory);
                                document.querySelector('#sidbagenv').value = `${infosbag.id_bagage}`;
                                document.querySelector('#sgddeptsuiviid').value = `${infosbag.idgarebag}`;
                                document.querySelector('#ssousgddeptsuiviid').value = `${infosbag.idsgarebag}`;
                                document.querySelector('#stypbagid').value = `${infosbag.typebagages}`;
                                document.querySelector('#snombrebgsuiviid').value = `${infosbag.nombrebagage}`;
                                document.querySelector('#scontenubgsuiviid').value = `${infosbag.contenubagage}`;
                                document.querySelector('#sidgarbag').value = `${infosbag.gidarrbag}`;
                                document.querySelector('#lgidbagages').value = `${infosbag.lgidbagage}`;
                                document.querySelector('#ssmsmbg').style.display = 'none';
                            } else {
                                console.debug(`${verife}`, console.memory);
                                
                                document.querySelector('#sidbagenv').value = "";
                                document.querySelector('#sgddeptsuiviid').value = "";
                                document.querySelector('#ssousgddeptsuiviid').value = "";
                                document.querySelector('#stypbagid').value = "";
                                document.querySelector('#snombrebgsuiviid').value = "";
                                document.querySelector('#scontenubgsuiviid').value = "";
                                document.querySelector('#sidgarbag').value = "";
                                document.querySelector('#lgidbagages').value = "";
                                document.querySelector('#ssmsmbg').style.display = 'block';
                                document.querySelector('#ssmsmvfbg').innerHTML = `Verifier le code bagage saisi!`;
                            }
                        }
                    }
                };
                httpInfosbag.setRequestHeader('Content-Type', 'application/json');
                httpInfosbag.send();
            };*/
            let infrecubag = document.querySelector('#snumcoderecu');
                let timerBag = null;

                if (infrecubag !== null) {
                    infrecubag.onkeyup = () => {

                        // ⛔ annule l'exécution précédente
                        clearTimeout(timerBag);

                        // ⏱ attend que l'utilisateur ait fini de taper
                        timerBag = setTimeout(() => {

                            let httpInfosbag;
                            if (window.XMLHttpRequest) {
                                httpInfosbag = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosbag = new ActiveXObject("Microsoft.XMLHTTP");
                            }

                            var anencrbag = document.querySelector('#idanencourenv').value;
                            var verificatbag = document.querySelector('#snumcoderecu').value;

                            // 🔒 sécurité minimale : pas de requête si vide
                            if (!verificatbag || !anencrbag) return;

                            const verife = `${verificatbag}${anencrbag}`;

                            const lidlignes = document.querySelector('#sdeptscouridlignesuivi')
                                .options[document.querySelector('#sdeptscouridlignesuivi').options.selectedIndex].value;
                            var lidlignes1 = lidlignes.split('/');
                            var lidlignes2 = lidlignes1[0];

                            httpInfosbag.open(
                                'GET',
                                window.location.origin + `${APP_ROOT}/confirmation/sverifinforecus/${verificatbag}${anencrbag}`,
                                true
                            );

                            httpInfosbag.onload = () => {

                                const infosbag = JSON.parse(httpInfosbag.responseText);

                                if (infosbag == null) {
                                    document.querySelector('#ssmsmbg').style.display = 'block';
                                    document.querySelector('#ssmsmvfbg').innerHTML = `Verifier le code de bagage saisi!`;

                                    document.querySelector('#sidbagenv').value = "";
                                    document.querySelector('#sgddeptsuiviid').value = "";
                                    document.querySelector('#ssousgddeptsuiviid').value = "";
                                    document.querySelector('#stypbagid').value = "";
                                    document.querySelector('#snombrebgsuiviid').value = "";
                                    document.querySelector('#scontenubgsuiviid').value = "";
                                    document.querySelector('#sidgarbag').value = "";
                                    document.querySelector('#lgidbagages').value = "";
                                } else {

                                    if (Object.entries(infosbag).length > 1) {

                                        if (infosbag.id_bagage === verife) {

                                            document.querySelector('#sidbagenv').value = infosbag.id_bagage;
                                            document.querySelector('#sgddeptsuiviid').value = infosbag.idgarebag;
                                            document.querySelector('#ssousgddeptsuiviid').value = infosbag.idsgarebag;
                                            document.querySelector('#stypbagid').value = infosbag.typebagages;
                                            document.querySelector('#snombrebgsuiviid').value = infosbag.nombrebagage;
                                            document.querySelector('#scontenubgsuiviid').value = infosbag.contenubagage;
                                            document.querySelector('#sidgarbag').value = infosbag.gidarrbag;
                                            document.querySelector('#lgidbagages').value = infosbag.lgidbagage;
                                            document.querySelector('#ssmsmbg').style.display = 'none';
                                        } else {
                                            document.querySelector('#sidbagenv').value = "";
                                            document.querySelector('#sgddeptsuiviid').value = "";
                                            document.querySelector('#ssousgddeptsuiviid').value = "";
                                            document.querySelector('#stypbagid').value = "";
                                            document.querySelector('#snombrebgsuiviid').value = "";
                                            document.querySelector('#scontenubgsuiviid').value = "";
                                            document.querySelector('#sidgarbag').value = "";
                                            document.querySelector('#lgidbagages').value = "";
                                            //document.querySelector('#slgidbagages').value = `${verificatbag}${anencrbag} ${infosbag.id_bagage}`;
                                            document.querySelector('#ssmsmbg').style.display = 'block';
                                            document.querySelector('#ssmsmvfbg').innerHTML = `Verifier le code bagage saisi!`;
                                        }
                                    }
                                }
                            };

                            httpInfosbag.send();

                        }, 600); // ⏱ 600ms = fin de saisie
                    };
                }


            let infolignep = document.querySelector('#scourdeptidprogsuivi');
            if (infolignep !== null)
            infolignep.onchange = () => {
                var verifintbag = document.querySelector('#lgidbagages').value;

                   const slidlignes = document.querySelector('#sdeptscouridlignesuivi')
                    .options[document.querySelector('#sdeptscouridlignesuivi').options.selectedIndex].value;
                    var slidlignes2 = parseLigneOption(slidlignes).ident;

                let httpRequestitines;
                httpRequestitines = new XMLHttpRequest();
                httpRequestitines.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifitine/${verifintbag}/${slidlignes2}`, true);
                httpRequestitines.onload = () => {
                const donitiness = JSON.parse(httpRequestitines.responseText);
                    if((donitiness.length > 0 ) || (verifintbag === slidlignes2))
                    {   
                        document.getElementById("snombreenvid").disabled = false;
                    }
                    else
                    {   
                        document.querySelector('#ssmlg').style.display = 'block';
                        document.querySelector('#ssmsmlg').innerHTML = `Verifiez la ligne choisi et comparer avec celui du recu`;
                        document.getElementById("snombreenvid").disabled = true;
                    }
                };
                httpRequestitines.setRequestHeader('Content-Type', 'application/json');
                httpRequestitines.send();
            };

            sverifnb = function () 
                        {
                var entree = parseInt(document.querySelector('#snombreenvid').value);
                    var n = document.querySelector('#snombreenvid').value;
                    var exist = parseInt(document.querySelector('#snombrebgsuiviid').value);
                        
                if(entree > exist) 
                {
                    document.querySelector('#ssmsmtbg').style.display = 'block';
                    document.querySelector('#ssmsmontantbg').innerHTML = `le mombre que vous aviez saisi dépasse le nombre de bagage`;
                    
                    document.querySelector('#snombreenvid').value = 'VERIFIER NOMBRE';  
                } 
                else
                {

                    document.querySelector('#ssmsmtbg').style.display = 'none';

                    document.querySelector('#snombreenvid').value = n ;
                    
                }
            };

        e.onclick = function (){
            let bordesForm = document.querySelector('#sbordesFormsuivi');
            bordesForm.setAttribute('action', `${APP_ROOT}/Confirmation/senregbagages/${e.dataset.cle_compagnie}`);
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
