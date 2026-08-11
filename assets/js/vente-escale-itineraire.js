/**
 * Vente guichet sur escales (itineraire_escales).
 * Case « Vente escale » : destination partielle ; pas de quartier (escales sans quartier).
 */
(function () {
    'use strict';

    var lastKey = null;
    var lastCataloguePrix = '';
    var cache = {};

    function formatPrix(val) {
        var n = Number(val);
        if (!val && val !== 0 && val !== '0') return '';
        if (isNaN(n)) return String(val);
        return n.toLocaleString('fr-FR');
    }

    function syncPrixAffiche() {
        var src = $('#prix_axe');
        var dst = $('#prix_axe_affiche');
        if (!dst) return;
        var v = src ? String(src.value || '').trim() : '';
        dst.value = v === '' ? '' : formatPrix(v);
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

    function isEscaleMode() {
        var ck = $('#escale_vente_check');
        return !!(ck && ck.checked);
    }

    function mainQuartierWrap() {
        var q = $('#quartier');
        return q ? q.closest('.form-group') : null;
    }

    function setQuartierVisible(visible) {
        // Délègue aux helpers vente : mémorise / restaure, ne vide pas.
        if (visible) {
            if (typeof window.__venteShowMainQuartier === 'function') {
                window.__venteShowMainQuartier();
                return;
            }
        } else {
            if (typeof window.__venteHideMainQuartier === 'function') {
                window.__venteHideMainQuartier();
                return;
            }
        }
        var wrap = mainQuartierWrap();
        var label = $('#idquart');
        var sel = $('#quartier');
        if (!visible && sel && sel.style.display !== 'none') {
            window.__venteSavedQuartierValue = sel.value;
        }
        if (wrap) wrap.style.display = visible ? '' : 'none';
        if (label) label.style.display = visible ? 'block' : 'none';
        if (sel) {
            sel.style.display = visible ? 'block' : 'none';
            if (visible && window.__venteSavedQuartierValue != null && window.__venteSavedQuartierValue !== '') {
                sel.value = window.__venteSavedQuartierValue;
            }
        }
    }

    function clearEscaleFields() {
        var idEl = $('#id_escale_vente');
        var codeEl = $('#code_gadest_vente');
        var nomEl = $('#nom_dest_vente');
        if (idEl) idEl.value = '';
        if (codeEl) codeEl.value = '';
        if (nomEl) nomEl.value = '';
    }

    function applyCataloguePrix() {
        var prixEl = $('#prix_axe');
        if (prixEl && lastCataloguePrix !== '') {
            prixEl.value = lastCataloguePrix;
        }
    }

    function setHelp(text, isWarn) {
        var help = $('#escale_dest_help');
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
        var idEl = $('#id_escale_vente');
        return !!(idEl && String(idEl.value || '').trim() !== '');
    }

    function syncEscaleVisibility() {
        var fields = $('#escale_dest_fields');
        var sel = $('#escale_dest_select');
        if (!fields) return;

        if (isEscaleMode()) {
            // Afficher le sélecteur d'escale — ne pas toucher au quartier tant qu'aucune escale n'est choisie.
            fields.style.display = 'block';
            refresh(true);
            if (hasEscaleSelected()) {
                setQuartierVisible(false);
            } else {
                setQuartierVisible(true);
            }
        } else {
            fields.style.display = 'none';
            if (sel) sel.value = '';
            clearEscaleFields();
            applyCataloguePrix();
            setQuartierVisible(true);
        }
    }

    function onEscaleChange() {
        if (!isEscaleMode()) {
            clearEscaleFields();
            applyCataloguePrix();
            setQuartierVisible(true);
            return;
        }
        var sel = $('#escale_dest_select');
        if (!sel) return;
        var opt = sel.options[sel.selectedIndex];
        if (!opt || !opt.value) {
            clearEscaleFields();
            applyCataloguePrix();
            // Case cochée mais pas d'escale : quartier reste visible avec sa valeur.
            setQuartierVisible(true);
            setHelp('Choisissez l\'escale demandée par le client.', false);
            return;
        }
        $('#id_escale_vente').value = opt.value;
        $('#code_gadest_vente').value = opt.getAttribute('data-code') || '';
        $('#nom_dest_vente').value = opt.getAttribute('data-nom') || '';
        var prix = opt.getAttribute('data-prix');
        if (prix !== null && $('#prix_axe')) {
            $('#prix_axe').value = prix;
        }
        // Activation réelle : escale choisie → masquer le quartier (valeur mémorisée).
        setQuartierVisible(false);
        setHelp('Escale sélectionnée — prix ' + Number(prix).toLocaleString('fr-FR') + ' F (sans quartier).', false);
        syncPrixAffiche();
    }

    function rememberCataloguePrix() {
        var prixEl = $('#prix_axe');
        var idEsc = $('#id_escale_vente');
        if (!prixEl) return;
        if (idEsc && idEsc.value) return;
        if (prixEl.value !== '') {
            lastCataloguePrix = prixEl.value;
        }
    }

    function fillSelect(escales, ligneNom) {
        var sel = $('#escale_dest_select');
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
            fillSelect(cache[key], ($('#nomitin') && $('#nomitin').value) || ligne);
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
            fillSelect(list, ($('#nomitin') && $('#nomitin').value) || ligne);
        };
        xhr.send();
    }

    function refresh(force) {
        rememberCataloguePrix();
        syncPrixAffiche();

        if (!isEscaleMode()) {
            if ($('#id_escale_vente') && $('#id_escale_vente').value) {
                clearEscaleFields();
            }
            return;
        }

        // Ne pas masquer le quartier ici : uniquement quand une escale est choisie (onEscaleChange).

        var gaexp = codeFromSelect('#depargare');
        var gadest = codeFromSelect('#arrsgare');
        var lignEl = $('#lign');
        var ligne = lignEl ? String(lignEl.value || '').trim() : '';
        var key = gaexp + '|' + gadest + '|' + ligne;

        if (!force && key === lastKey) {
            if ($('#id_escale_vente') && $('#id_escale_vente').value) {
                var sel = $('#escale_dest_select');
                if (sel && sel.value) {
                    var opt = sel.options[sel.selectedIndex];
                    if (opt && opt.getAttribute('data-prix') && $('#prix_axe')) {
                        $('#prix_axe').value = opt.getAttribute('data-prix');
                    }
                }
            }
            return;
        }
        lastKey = key;
        lastCataloguePrix = lastCataloguePrix || (($('#prix_axe') && $('#prix_axe').value) || '');

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
        var ck = $('#escale_vente_check');
        if (ck && !ck._escaleBound) {
            ck.addEventListener('change', syncEscaleVisibility);
            ck._escaleBound = true;
        }

        var sel = $('#escale_dest_select');
        if (sel && !sel._escaleBound) {
            sel.addEventListener('change', onEscaleChange);
            sel._escaleBound = true;
        }

        ['#arrsgare', '#depargare', '#date_depheure', '#hdepart'].forEach(function (s) {
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

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();


/**
 * Escales sur les jambes de transit / correspondances.
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

    function isTranVisible() {
        return isShown($('#tran'));
    }

    var cache = {};
    var legs = [
        {
            n: 1,
            ligne: '#ligntrans',
            lineUi: ['#ligne1', '#lignesitineraire'],
            prix: '#prix_axetrans',
            quartier: '#quartier1',
            quartierLabel: '#idquart1',
            wrap: '#escale_leg_wrap_tr1',
            check: '#escale_vente_check_tr1',
            fields: '#escale_dest_fields_tr1',
            select: '#escale_dest_select_tr1',
            idEsc: '#id_escale_vente_tr1',
            codeEsc: '#code_gadest_vente_tr1',
            nomEsc: '#nom_dest_vente_tr1'
        },
        {
            n: 2,
            ligne: '#ligntrans1',
            lineUi: ['#arritin1', '#idchemins'],
            prix: '#prix_axetransit',
            quartier: '#quartier2',
            quartierLabel: '#idquart2',
            wrap: '#escale_leg_wrap_tr2',
            check: '#escale_vente_check_tr2',
            fields: '#escale_dest_fields_tr2',
            select: '#escale_dest_select_tr2',
            idEsc: '#id_escale_vente_tr2',
            codeEsc: '#code_gadest_vente_tr2',
            nomEsc: '#nom_dest_vente_tr2'
        },
        {
            n: 3,
            ligne: '#ligntrans2',
            lineUi: ['#arritin2', '#idchemins1'],
            prix: '#prix_axetransit1',
            quartier: '#quartier3',
            quartierLabel: '#idquart3',
            wrap: '#escale_leg_wrap_tr3',
            check: '#escale_vente_check_tr3',
            fields: '#escale_dest_fields_tr3',
            select: '#escale_dest_select_tr3',
            idEsc: '#id_escale_vente_tr3',
            codeEsc: '#code_gadest_vente_tr3',
            nomEsc: '#nom_dest_vente_tr3'
        },
        {
            n: 4,
            ligne: '#ligntrans3',
            lineUi: ['#arritin3', '#idchemins2'],
            prix: '#prix_axetransit2',
            quartier: null,
            quartierLabel: null,
            wrap: '#escale_leg_wrap_tr4',
            check: '#escale_vente_check_tr4',
            fields: '#escale_dest_fields_tr4',
            select: '#escale_dest_select_tr4',
            idEsc: '#id_escale_vente_tr4',
            codeEsc: '#code_gadest_vente_tr4',
            nomEsc: '#nom_dest_vente_tr4'
        }
    ];

    var lastLigne = {};
    var cataloguePrix = {};
    var hasEscales = {};
    var loading = {};

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

    function lineUiVisible(leg) {
        if (!leg.lineUi || !leg.lineUi.length) return isTranVisible();
        for (var i = 0; i < leg.lineUi.length; i++) {
            if (isShown($(leg.lineUi[i]))) return true;
        }
        return false;
    }

    function nbrTrans() {
        var el = $('#nbrtrans');
        var n = el ? parseInt(el.value, 10) : 0;
        return isNaN(n) ? 0 : n;
    }

    /** Vente escale transit : uniquement sur la dernière correspondance. */
    function isLastTransitLeg(leg) {
        var nbr = nbrTrans();
        if (nbr < 1) return false;
        return leg.n === nbr;
    }

    function canShowEscaleLeg(leg) {
        return isTranVisible() && lineUiVisible(leg) && isLastTransitLeg(leg);
    }

    /** Quartiers liés à une jambe (dernière jambe = #quartier / quartconfirme). */
    function quartierTargets(leg) {
        var out = [];
        var nbr = nbrTrans();
        // Dernière jambe (2, 3 ou 4) : champ Quartier du haut de formulaire.
        if (nbr > 0 && leg.n === nbr) {
            out.push({ sel: '#quartier', label: '#idquart' });
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

    var savedLegQuartiers = {};

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
            }
            if (q) q.style.display = 'none';
            if (lab) lab.style.display = 'none';
            if (wrap) wrap.style.display = 'none';
        }
        // Quartier principal (dernière jambe) via helper — sans vider.
        if (typeof window.__venteHideMainQuartier === 'function') {
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
        if (typeof window.__venteShowMainQuartier === 'function') {
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
        if (prixEl && cataloguePrix[leg.n] !== undefined && cataloguePrix[leg.n] !== '') {
            prixEl.value = cataloguePrix[leg.n];
        }
    }

    function rememberPrix(leg) {
        var prixEl = $(leg.prix);
        var idEsc = $(leg.idEsc);
        if (!prixEl) return;
        if (idEsc && idEsc.value) return;
        if (prixEl.value !== '') {
            cataloguePrix[leg.n] = prixEl.value;
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

        hasEscales[leg.n] = !!(escales && escales.length);

        if (!hasEscales[leg.n]) {
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
            // Afficher le sélecteur — quartier inchangé tant qu'aucune escale n'est choisie.
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
            // Case cochée sans escale : restaurer / garder le quartier.
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

        // Pas la dernière correspondance : masquer et vider.
        if (!isLastTransitLeg(leg)) {
            showWrap(leg, false);
            return;
        }

        if (!visibleContext) {
            if (wrap) wrap.style.display = 'none';
            return;
        }

        if (ligne !== (lastLigne[leg.n] || '')) {
            lastLigne[leg.n] = ligne;
            cataloguePrix[leg.n] = '';
            hasEscales[leg.n] = false;
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

        if (ligne && hasEscales[leg.n]) {
            showWrap(leg, true);
            if ($(leg.check) && $(leg.check).checked) {
                // Masquer le quartier seulement si une escale est déjà sélectionnée.
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

;
