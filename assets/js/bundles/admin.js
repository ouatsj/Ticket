/* Bundle admin — genere par scripts/build_module_bundles.php */
/* --- addperso.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addperso').forEach(function (e) 
    {
        document.querySelector('h3#persoTitle').innerHTML = `ENREGISTREMENT DES PERSONNELS`;

       
        let persoinf = document.querySelector('#idpersonnel');
        
        if (persoinf !== null) 
            persoinf.onchange = () => 
            {   
                    
                    document.querySelector('#matperso').style.display = 'none';
                    document.querySelector('#idmatperso').style.display = 'none';
                    document.querySelector('#idnom').style.display = 'none';
                    document.querySelector('#idprenom').style.display = 'none';
                    document.querySelector('#idadres').style.display = 'none';
                    document.querySelector('#idadresse').style.display = 'none';
                    document.querySelector('#idcont').style.display = 'none';
                    document.querySelector('#contid').style.display = 'none';
                    document.querySelector('#idsecond').style.display = 'none';
                    document.querySelector('#secondid').style.display = 'none';
                    document.querySelector('#idpermis').style.display = 'none';
                    document.querySelector('#permisid').style.display = 'none';
                    document.querySelector('#idcat').style.display = 'none';
                    document.querySelector('#catid').style.display = 'none';
                    document.querySelector('#iddel').style.display = 'none';
                    document.querySelector('#delid').style.display = 'none';
                    document.querySelector('#idexp').style.display = 'none';
                    document.querySelector('#expid').style.display = 'none';
                    document.querySelector('#idcnib').style.display = 'none';
                    document.querySelector('#cnibid').style.display = 'none';
                    document.querySelector('#idcnidel').style.display = 'none';
                    document.querySelector('#cnibidle').style.display = 'none';
                    document.querySelector('#idexpir').style.display = 'none';
                    document.querySelector('#expirid').style.display = 'none';
                    document.querySelector('#nomclientid').style.display = 'none';
                    document.querySelector('#idnomclient').style.display = 'none';
                    document.querySelector('#idpren').style.display = 'none';
                    document.querySelector('#prenid').style.display = 'none';
                    document.querySelector('#lieucl').style.display = 'none';
                    document.querySelector('#cl_lieu').style.display = 'none';
					document.querySelector('#idtypper').style.display = 'none';
                    document.querySelector('#persid').style.display = 'none';
                    document.querySelector('#tel_num').style.display = 'none';
                    document.querySelector('#num_tel').style.display = 'none';

                    var infoperso = document.querySelector('#idpersonnel')
                    .options[document.querySelector('#idpersonnel').options.selectedIndex].value;

                    if(infoperso === 'perso'){
                        document.querySelector('#matperso').style.display = 'block';
                        document.querySelector('#idmatperso').style.display = 'block';
                        document.querySelector('#idnom').style.display = 'block';
                        document.querySelector('#idprenom').style.display = 'block';
                        document.querySelector('#idadres').style.display = 'block';
                        document.querySelector('#idadresse').style.display = 'block';
                        document.querySelector('#idcont').style.display = 'block';
                        document.querySelector('#contid').style.display = 'block';
                        document.querySelector('#idsecond').style.display = 'block';
                        document.querySelector('#secondid').style.display = 'block';
                        document.querySelector('#idpermis').style.display = 'block';
                        document.querySelector('#permisid').style.display = 'block';
                        document.querySelector('#idcat').style.display = 'block';
                        document.querySelector('#catid').style.display = 'block';
                        document.querySelector('#iddel').style.display = 'block';
                        document.querySelector('#delid').style.display = 'block';
                        document.querySelector('#idexp').style.display = 'block';
                        document.querySelector('#expid').style.display = 'block';
                        document.querySelector('#idcnib').style.display = 'block';
                        document.querySelector('#cnibid').style.display = 'block';
                        document.querySelector('#idcnidel').style.display = 'block';
                        document.querySelector('#cnibidle').style.display = 'block';
                        document.querySelector('#idexpir').style.display = 'block';
                        document.querySelector('#expirid').style.display = 'block';
						document.querySelector('#idtypper').style.display = 'block';
						document.querySelector('#persid').style.display = 'block';
                        document.querySelector('#nomclientid').style.display = 'none';
                        document.querySelector('#idnomclient').style.display = 'none';
                        document.querySelector('#idpren').style.display = 'none';
                        document.querySelector('#prenid').style.display = 'none';
                        document.querySelector('#tel_num').style.display = 'none';
                        document.querySelector('#num_tel').style.display = 'none';
                        document.querySelector('#lieucl').style.display = 'none';
                        document.querySelector('#cl_lieu').style.display = 'none';
                    }
                    //client 
                    if(infoperso === 'client'){
                            document.querySelector('#tel_num').style.display = 'block';
                            document.querySelector('#num_tel').style.display = 'block';
                            document.querySelector('#nomclientid').style.display = 'block';
                            document.querySelector('#idnomclient').style.display = 'block';
                            document.querySelector('#idpren').style.display = 'block';
                            document.querySelector('#prenid').style.display = 'block';
                            document.querySelector('#idnom').style.display = 'none';
                            document.querySelector('#idprenom').style.display = 'none';
                            document.querySelector('#lieucl').style.display = 'block';
                            document.querySelector('#cl_lieu').style.display = 'block';
                            document.querySelector('#idadres').style.display = 'none';
                            document.querySelector('#idadresse').style.display = 'none';
                            document.querySelector('#idcont').style.display = 'none';
                            document.querySelector('#contid').style.display = 'none';
                            document.querySelector('#idsecond').style.display = 'none';
                            document.querySelector('#secondid').style.display = 'none';
                            document.querySelector('#idpermis').style.display = 'none';
                            document.querySelector('#permisid').style.display = 'none';
                            document.querySelector('#idcat').style.display = 'none';
                            document.querySelector('#catid').style.display = 'none';
                            document.querySelector('#iddel').style.display = 'none';
                            document.querySelector('#delid').style.display = 'none';
                            document.querySelector('#idexp').style.display = 'none';
                            document.querySelector('#expid').style.display = 'none';
                            document.querySelector('#idcnib').style.display = 'none';
                            document.querySelector('#cnibid').style.display = 'none';
                            document.querySelector('#idcnidel').style.display = 'none';
                            document.querySelector('#cnibidle').style.display = 'none';
                            document.querySelector('#idexpir').style.display = 'none';
                            document.querySelector('#expirid').style.display = 'none';
                            document.querySelector('#matperso').style.display = 'none';
                            document.querySelector('#idmatperso').style.display = 'none';
							document.querySelector('#idtypper').style.display = 'none';
							document.querySelector('#persid').style.display = 'none';
                            
                    }
                    

                    if(infoperso === 'autrepersonnel'){
                            document.querySelector('#tel_num').style.display = 'block';
                            document.querySelector('#num_tel').style.display = 'block';
                            document.querySelector('#nomclientid').style.display = 'block';
                            document.querySelector('#idnomclient').style.display = 'block';
                            document.querySelector('#idpren').style.display = 'block';
                            document.querySelector('#prenid').style.display = 'block';
                            document.querySelector('#lieucl').style.display = 'block';
                            document.querySelector('#cl_lieu').style.display = 'block';
                            document.querySelector('#idnom').style.display = 'none';
                            document.querySelector('#idprenom').style.display = 'none';
                            document.querySelector('#idadres').style.display = 'none';
                            document.querySelector('#idadresse').style.display = 'none';
                            document.querySelector('#idcont').style.display = 'none';
                            document.querySelector('#contid').style.display = 'none';
                            document.querySelector('#idsecond').style.display = 'none';
                            document.querySelector('#secondid').style.display = 'none';
                            document.querySelector('#idpermis').style.display = 'none';
                            document.querySelector('#permisid').style.display = 'none';
                            document.querySelector('#idcat').style.display = 'none';
                            document.querySelector('#catid').style.display = 'none';
                            document.querySelector('#iddel').style.display = 'none';
                            document.querySelector('#delid').style.display = 'none';
                            document.querySelector('#idexp').style.display = 'none';
                            document.querySelector('#expid').style.display = 'none';
                            document.querySelector('#idcnib').style.display = 'none';
                            document.querySelector('#cnibid').style.display = 'none';
                            document.querySelector('#idcnidel').style.display = 'none';
                            document.querySelector('#cnibidle').style.display = 'none';
                            document.querySelector('#idexpir').style.display = 'none';
                            document.querySelector('#expirid').style.display = 'none';
                            document.querySelector('#matperso').style.display = 'none';
                            document.querySelector('#idmatperso').style.display = 'none';
							document.querySelector('#idtypper').style.display = 'none';
							document.querySelector('#persid').style.display = 'none';
                            
                    }
            };
        e.onclick = function () {
        let listeperso = document.querySelector('#persoForm');
        listeperso.setAttribute('action', `${APP_ROOT}/Personnels/add/${e.dataset.cle_compagnie}`);
        }

    })
});
;
/* --- user-list-filter.js --- */
/**
 * Filtre instantané pour les listes utilisateurs (cartes ou lignes de tableau).
 */
(function ($) {
    'use strict';

    function normalize(text) {
        return (text || '').toString().toLowerCase();
    }

    function initCardFilter() {
        $('[data-user-filter-input]').each(function () {
            var $input = $(this);
            var listId = $input.attr('data-user-filter-input');
            var $list = $('#' + listId);
            if (!$list.length) {
                return;
            }

            var $items = $list.find('[data-user-filter-item]');
            var $count = $('#' + listId + '-count');
            var $empty = $('#' + listId + '-empty');
            var label = $input.attr('data-user-filter-label') || 'utilisateur(s)';

            function applyFilter() {
                var query = normalize($.trim($input.val()));
                var visible = 0;

                $items.each(function () {
                    var $item = $(this);
                    var haystack = normalize($item.attr('data-search') || $item.text());
                    var match = !query || haystack.indexOf(query) !== -1;
                    $item.toggle(match);
                    if (match) {
                        visible++;
                    }
                });

                if ($count.length) {
                    if (query) {
                        $count.text(visible + ' / ' + $items.length + ' ' + label);
                    } else {
                        $count.text($items.length + ' ' + label);
                    }
                }

                if ($empty.length) {
                    $empty.toggle(!!query && visible === 0);
                }
            }

            $input.on('input keyup search', applyFilter);
            applyFilter();
        });
    }

    function initTableFilter() {
        $('[data-user-table-filter]').each(function () {
            var $input = $(this);
            var selector = $input.attr('data-user-table-filter');
            var $table = $(selector);
            if (!$table.length) {
                return;
            }

            var $rows = $table.find('tbody tr[data-user-filter-item]');
            var $empty = $($input.attr('data-user-table-empty') || '');
            var label = $input.attr('data-user-filter-label') || 'ligne(s)';
            var $count = $($input.attr('data-user-table-count') || '');

            function applyFilter() {
                var query = normalize($.trim($input.val()));
                var visible = 0;

                $rows.each(function () {
                    var $row = $(this);
                    var haystack = normalize($row.attr('data-search') || $row.text());
                    var match = !query || haystack.indexOf(query) !== -1;
                    $row.toggle(match);
                    if (match) {
                        visible++;
                    }
                });

                if ($count.length) {
                    if (query) {
                        $count.text(visible + ' / ' + $rows.length + ' ' + label);
                    } else {
                        $count.text($rows.length + ' ' + label);
                    }
                }

                if ($empty.length) {
                    $empty.toggle(!!query && visible === 0);
                }
            }

            $input.on('input keyup search', applyFilter);
            applyFilter();
        });
    }

    $(function () {
        initCardFilter();
        initTableFilter();
    });
}(jQuery));

;
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
/* --- tarif-edit.js --- */
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('tarif-edit-0');
    if (!modal) {
        return;
    }

    var form = modal.querySelector('form');
    var titleEl = modal.querySelector('.modal-title');
    var tarifSel = modal.querySelector('[name="tarifbase"]');
    var clientSel = modal.querySelector('[name="typeclient"]');
    var itinSel = modal.querySelector('[name="itineraire"]');
    var montantEl = modal.querySelector('[name="montanttarif"]');

    document.querySelectorAll('.js-tarif-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!form) {
                return;
            }

            var ekey = btn.getAttribute('data-ekey') || '';
            var id = btn.getAttribute('data-id') || '';
            var typeTarif = btn.getAttribute('data-type_tarif') || '';
            var typetarifId = btn.getAttribute('data-typetarif_id') || '';
            var nomType = btn.getAttribute('data-nom_type') || '';
            var typeclientId = btn.getAttribute('data-typeclient_id') || '';
            var lhId = btn.getAttribute('data-ligne_heure_id') || '';
            var ligneId = btn.getAttribute('data-ligne_id') || '';
            var nomLigne = btn.getAttribute('data-nom_ligne') || '';
            var heure = btn.getAttribute('data-heure') || '';
            var prix = btn.getAttribute('data-prix') || '';

            if (titleEl) {
                titleEl.textContent = 'MODIFICATION SUR ' + typeTarif;
            }
            if (tarifSel) {
                tarifSel.value = typetarifId;
            }
            if (clientSel) {
                clientSel.value = typeclientId;
            }
            if (itinSel) {
                itinSel.innerHTML = '<option value="' + lhId + '.' + ligneId + '">' +
                    nomLigne + '/' + heure + '</option>';
                itinSel.value = lhId + '.' + ligneId;
            }
            if (montantEl) {
                montantEl.value = prix;
            }

            form.setAttribute('action', (window.APP_ROOT || '') + '/Tarifs/edit_/' + ekey + '/' + id);
        });
    });
});

