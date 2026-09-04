(function (global) {
    'use strict';

    function siteBase() {
        if (typeof global.__SITE_BASE === 'string' && global.__SITE_BASE) {
            return global.__SITE_BASE.replace(/\/$/, '');
        }
        var root = (typeof global.APP_ROOT !== 'undefined') ? String(global.APP_ROOT) : '';
        if (root && root.charAt(0) !== '/') {
            root = '/' + root;
        }
        return (global.location.origin + root).replace(/\/$/, '');
    }

    function sortedNums(checkboxes) {
        return checkboxes
            .filter(function (c) { return c.checked && !c.disabled; })
            .map(function (c) { return parseInt(c.value, 10); })
            .filter(function (n) { return !isNaN(n); })
            .sort(function (a, b) { return a - b; });
    }

    function isContiguous(nums) {
        if (!nums.length) {
            return false;
        }
        for (var i = 1; i < nums.length; i++) {
            if (nums[i] - nums[i - 1] > 1) {
                return false;
            }
        }
        return true;
    }

    function fetchJson(url) {
        return fetch(url, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(function (r) {
            return r.text().then(function (t) {
                try {
                    return JSON.parse(t);
                } catch (e) {
                    throw new Error('Réponse non JSON');
                }
            });
        });
    }

    function findCategSelect(block) {
        if (!block) {
            return null;
        }
        if (block.dataset.categSelect) {
            var byId = document.getElementById(block.dataset.categSelect);
            if (byId) {
                return byId;
            }
        }
        var form = block.closest('form');
        return form ? form.querySelector('select[name="categorie"]') : null;
    }

    function bindBlock(block) {
        if (!block) {
            return null;
        }
        if (block.getAttribute('data-quota-bound') !== '1') {
            block.setAttribute('data-quota-bound', '1');
        }

        var form = block.closest('form');
        var debutEl = block.querySelector('.js-quota-debut-field');
        var finEl = block.querySelector('.js-quota-fin-field');
        var grid = block.querySelector('.js-quota-sieges-grid');
        var summary = block.querySelector('.js-quota-summary');
        var libererFields = block.querySelector('.js-quota-liberer-fields');
        var bloqueFields = block.querySelector('.js-quota-bloque-fields');
        var hintEl = block.querySelector('.js-quota-hint');
        var bloqueAlert = block.querySelector('.js-quota-bloque-alert');
        var isEditBlock = block.getAttribute('data-quota-mode') === 'edit';

        var state = block._quotaState || {
            nbrPlace: 0,
            sold: {},
            blocked: {},
            tampon: {},
            reco: {},
            recoMode: false,
            rangeDebut: 0,
            rangeFin: 0,
            editMode: isEditBlock,
            reverting: false
        };
        if (!state.tampon) {
            state.tampon = {};
        }
        block._quotaState = state;
        if (isEditBlock) {
            state.editMode = true;
        }

        function setSummary(text) {
            if (summary) {
                summary.textContent = text;
            }
        }

        function setHint(text) {
            if (hintEl && text) {
                hintEl.textContent = text;
            }
        }

        function recoCount() {
            return Object.keys(state.reco).length;
        }

        function isRecoSeat(n) {
            return !!state.reco[String(n)];
        }

        /**
         * Libération : uniquement les sièges déjà vendus décochés
         * (en reconduction : seulement parmi les alloués).
         * Les trous libres → sieges_bloques[], pas sieges_liberer[].
         */
        function getToLiberate(checked) {
            if (!grid) {
                return [];
            }
            var out = [];
            grid.querySelectorAll('.js-quota-siege[data-sold="1"]').forEach(function (cb) {
                if (cb.disabled || cb.checked) {
                    return;
                }
                var n = parseInt(cb.value, 10);
                if (isNaN(n) || n <= 0) {
                    return;
                }
                if (state.recoMode && !isRecoSeat(n)) {
                    return;
                }
                out.push(n);
            });
            return out.sort(function (a, b) { return a - b; });
        }

        function quotaRange() {
            var d = debutEl ? parseInt(debutEl.value, 10) : 0;
            var f = finEl ? parseInt(finEl.value, 10) : 0;
            if (!(d > 0 && f >= d) && state.rangeDebut > 0 && state.rangeFin >= state.rangeDebut) {
                d = state.rangeDebut;
                f = state.rangeFin;
            }
            return { d: d, f: f };
        }

        function syncBloquesHidden() {
            if (!bloqueFields || !grid) {
                return;
            }
            bloqueFields.innerHTML = '';
            var range = quotaRange();
            var d = range.d;
            var f = range.f;
            // Ne poster que les trous dans [debut, fin] — hors intervalle = hors quota, pas en base.
            if (!(d > 0 && f >= d)) {
                return;
            }
            grid.querySelectorAll('.js-quota-siege').forEach(function (cb) {
                if (cb.checked || cb.disabled || cb.getAttribute('data-sold') === '1'
                    || cb.getAttribute('data-tampon') === '1') {
                    return;
                }
                var n = parseInt(cb.value, 10);
                if (isNaN(n) || n < d || n > f) {
                    return;
                }
                var inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'sieges_bloques[]';
                inp.value = String(n);
                bloqueFields.appendChild(inp);
            });
        }

        function setBloqueAlert(blockedN, tamponN) {
            if (!bloqueAlert) {
                return;
            }
            var parts = [];
            if (blockedN > 0) {
                parts.push(blockedN + ' siège(s) bloqué(s) hors vente (décochés volontairement).');
            }
            if (tamponN > 0) {
                parts.push(tamponN + ' siège(s) en tampon (vente guichet en cours, TTL 45 min).');
            }
            if (!parts.length) {
                bloqueAlert.classList.add('d-none');
                bloqueAlert.textContent = '';
                return;
            }
            bloqueAlert.classList.remove('d-none');
            bloqueAlert.textContent = parts.join(' ');
        }

        function syncLibererHidden(lib) {
            if (!libererFields) {
                return;
            }
            libererFields.innerHTML = '';
            (lib || []).forEach(function (n) {
                var inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'sieges_liberer[]';
                inp.value = String(n);
                libererFields.appendChild(inp);
            });
        }

        function syncHidden() {
            var boxes = grid ? Array.from(grid.querySelectorAll('.js-quota-siege')) : [];
            var nums = sortedNums(boxes);
            if (!nums.length) {
                syncLibererHidden([]);
                syncBloquesHidden();
                if (debutEl) debutEl.value = '';
                if (finEl) finEl.value = '';
                setSummary('Quota : —');
                if (typeof setBloqueAlert === 'function') {
                    setBloqueAlert(0, Object.keys(state.tampon || {}).length);
                }
                return false;
            }
            var lib = getToLiberate(nums);
            var d;
            var f;
            if (state.recoMode && state.rangeDebut > 0 && state.rangeFin >= state.rangeDebut) {
                // Reconduction : conserver la plage programme (vente filtrée par liste)
                d = state.rangeDebut;
                f = state.rangeFin;
            } else {
                d = nums[0];
                f = nums[nums.length - 1];
                lib.forEach(function (n) {
                    if (n < d) d = n;
                    if (n > f) f = n;
                });
            }
            if (debutEl) debutEl.value = String(d);
            if (finEl) finEl.value = String(f);
            syncLibererHidden(lib);
            syncBloquesHidden();
            var soldN = Object.keys(state.sold).length;
            var blockedN = 0;
            if (grid) {
                grid.querySelectorAll('.js-quota-siege').forEach(function (cb) {
                    var n = parseInt(cb.value, 10);
                    if (!cb.checked && !cb.disabled && cb.getAttribute('data-sold') !== '1'
                        && cb.getAttribute('data-tampon') !== '1'
                        && !isNaN(n) && n >= d && n <= f) {
                        blockedN++;
                    }
                });
            }
            var tamponN = Object.keys(state.tampon || {}).length;
            var parts = [];
            if (state.recoMode) {
                parts.push('Reconduit : ' + recoCount() + ' siège(s)');
                parts.push('cochés ' + nums.length);
            } else {
                parts.push('Quota : ' + d + ' → ' + f + ' (' + nums.length + ' siège(s))');
            }
            if (soldN > 0) {
                parts.push(soldN + ' vendu(s)');
            }
            if (tamponN > 0) {
                parts.push(tamponN + ' tampon(s)');
            }
            if (lib.length) {
                parts.push(lib.length + ' libéré(s) → revendable(s)');
            }
            if (blockedN > 0) {
                parts.push(blockedN + ' bloqué(s) → hors vente');
            }
            setSummary(parts.join(' · '));
            if (typeof setBloqueAlert === 'function') {
                setBloqueAlert(blockedN, tamponN);
            }
            return true;
        }

        function renderGrid(from, to, checkedFrom, checkedTo, sold, recoList, blockedList, tamponList) {
            if (!grid) {
                return;
            }
            state.sold = {};
            (sold || []).forEach(function (n) {
                var num = parseInt(n, 10);
                if (!isNaN(num) && num > 0) {
                    state.sold[String(num)] = true;
                }
            });
            state.blocked = {};
            (blockedList || []).forEach(function (n) {
                var num = parseInt(n, 10);
                if (!isNaN(num) && num > 0) {
                    state.blocked[String(num)] = true;
                }
            });
            state.tampon = {};
            (tamponList || []).forEach(function (n) {
                var num = parseInt(n, 10);
                if (!isNaN(num) && num > 0 && !state.sold[String(num)]) {
                    state.tampon[String(num)] = true;
                }
            });
            state.reco = {};
            state.recoMode = false;
            if (Array.isArray(recoList) && recoList.length) {
                state.recoMode = true;
                recoList.forEach(function (n) {
                    var num = parseInt(n, 10);
                    if (!isNaN(num) && num > 0) {
                        state.reco[String(num)] = true;
                    }
                });
            }

            var html = '';
            for (var n = from; n <= to; n++) {
                var isReco = state.recoMode ? isRecoSeat(n) : true;
                var isSold = !!state.sold[String(n)] && isReco;
                var isTampon = !isSold && !!state.tampon[String(n)];
                var checked;
                if (state.recoMode) {
                    // Reconduction : sièges reconduits cochés, sauf bloqués admin.
                    checked = isReco;
                } else {
                    checked = (n >= checkedFrom && n <= checkedTo) || isSold || isTampon;
                }
                // Toujours respecter les bloqués (édition + reconduction).
                if (state.blocked[String(n)] && !isSold && !isTampon) {
                    checked = false;
                }
                var disabled = state.recoMode && !isReco;
                var isBlocked = !checked && !isSold && !isTampon && !disabled
                    && !!state.blocked[String(n)];
                var wrapStyle;
                var labelExtra = '';
                if (isSold) {
                    wrapStyle = 'background:#fff3cd;border:1px solid #ffc107;border-radius:4px;padding:4px 6px;display:block;';
                    labelExtra = ' <span style="color:#856404;font-size:11px;font-weight:600;">VENDU</span>';
                } else if (isTampon) {
                    wrapStyle = 'background:#ffe5d0;border:1px solid #fd7e14;border-radius:4px;padding:4px 6px;display:block;';
                    labelExtra = ' <span style="color:#9a3412;font-size:11px;font-weight:600;">TAMPON</span>';
                } else if (isBlocked) {
                    wrapStyle = 'background:#e2e3e5;border:1px solid #6c757d;border-radius:4px;padding:4px 6px;display:block;opacity:0.75;';
                    labelExtra = ' <span style="color:#495057;font-size:11px;font-weight:600;">BLOQUÉ</span>';
                } else if (state.recoMode && isReco) {
                    wrapStyle = 'background:#d1ecf1;border:1px solid #17a2b8;border-radius:4px;padding:4px 6px;display:block;';
                    labelExtra = ' <span style="color:#0c5460;font-size:11px;font-weight:600;">RECONDUIT</span>';
                } else if (disabled) {
                    wrapStyle = 'background:#f8f9fa;border:1px solid #dee2e6;border-radius:4px;padding:4px 6px;display:block;opacity:0.55;';
                    labelExtra = ' <span style="color:#6c757d;font-size:10px;">hors</span>';
                } else {
                    wrapStyle = 'padding:4px 6px;display:block;';
                }
                html += '<div class="col-3 col-md-2 mb-1"><label style="font-weight:400;'
                    + (disabled ? 'cursor:not-allowed;' : 'cursor:pointer;')
                    + wrapStyle + '">'
                    + '<input type="checkbox" class="js-quota-siege'
                    + (isSold ? ' js-quota-siege-sold' : '')
                    + (isTampon ? ' js-quota-siege-tampon' : '')
                    + (isReco && state.recoMode ? ' js-quota-siege-reco' : '')
                    + '" value="' + n + '"'
                    + (checked ? ' checked' : '')
                    + (disabled ? ' disabled' : '')
                    + (isSold ? ' data-sold="1"' : '')
                    + (isTampon ? ' data-tampon="1"' : '')
                    + (state.recoMode && isReco ? ' data-reco="1"' : '')
                    + '> <strong>' + n + '</strong>'
                    + labelExtra
                    + '</label></div>';
            }
            grid.innerHTML = html;
            grid.querySelectorAll('.js-quota-siege:not([disabled])').forEach(function (cb) {
                cb.addEventListener('change', onToggle);
            });
            if (state.recoMode) {
                setHint('Fond bleu = siège reconduit (' + recoCount() + '). Jaune = déjà vendu. Orange = tampon. Gris = hors reconduction.');
            }
            syncHidden();
        }

        function renderAll(nbrPlace, rangeDebut, rangeFin, sold, recoList, blockedList, tamponList) {
            state.nbrPlace = nbrPlace;
            var d = rangeDebut > 0 ? rangeDebut : 1;
            var f = rangeFin > 0 ? rangeFin : nbrPlace;
            if (f > nbrPlace) {
                f = nbrPlace;
            }
            if (d < 1) {
                d = 1;
            }
            state.rangeDebut = d;
            state.rangeFin = f;
            renderGrid(1, nbrPlace, d, f, sold, recoList || null, blockedList || null, tamponList || null);
        }

        function onToggle(ev) {
            if (state.reverting) {
                return;
            }
            var cb = ev.target;
            if (cb.disabled) {
                return;
            }
            var boxes = Array.from(grid.querySelectorAll('.js-quota-siege'));
            var nums = sortedNums(boxes);

            if (!nums.length) {
                state.reverting = true;
                cb.checked = true;
                state.reverting = false;
                setSummary('Au moins un siège requis.');
                syncLibererHidden([]);
                syncBloquesHidden();
                return;
            }

            // Édition / reconduction : trous autorisés (= libération ou blocage)
            if (state.editMode || state.recoMode) {
                if (cb.getAttribute('data-sold') === '1' && !cb.checked) {
                    var lab = cb.closest('label');
                    if (lab) {
                        lab.style.background = '#f8d7da';
                        lab.style.borderColor = '#dc3545';
                        var tag = lab.querySelector('span');
                        if (tag) {
                            tag.textContent = 'LIBÉRÉ';
                            tag.style.color = '#721c24';
                        }
                    }
                } else if (cb.getAttribute('data-sold') === '1' && cb.checked) {
                    var lab2 = cb.closest('label');
                    if (lab2) {
                        lab2.style.background = '#fff3cd';
                        lab2.style.borderColor = '#ffc107';
                        var tag2 = lab2.querySelector('span');
                        if (tag2) {
                            tag2.textContent = 'VENDU';
                            tag2.style.color = '#856404';
                        }
                    }
                } else if (!cb.checked && cb.getAttribute('data-sold') !== '1'
                    && cb.getAttribute('data-tampon') !== '1') {
                    state.blocked[String(cb.value)] = true;
                    var labB = cb.closest('label');
                    if (labB) {
                        labB.style.background = '#e2e3e5';
                        labB.style.borderColor = '#6c757d';
                        labB.style.opacity = '0.75';
                        var tagB = labB.querySelector('span');
                        if (tagB) {
                            tagB.textContent = 'BLOQUÉ';
                            tagB.style.color = '#495057';
                        }
                    }
                } else if (cb.checked && cb.getAttribute('data-sold') !== '1'
                    && cb.getAttribute('data-tampon') !== '1') {
                    delete state.blocked[String(cb.value)];
                    var labOk = cb.closest('label');
                    if (labOk && cb.getAttribute('data-reco') !== '1') {
                        labOk.style.background = '';
                        labOk.style.borderColor = '';
                        labOk.style.opacity = '';
                        var tagOk = labOk.querySelector('span');
                        if (tagOk && tagOk.textContent === 'BLOQUÉ') {
                            tagOk.textContent = '';
                        }
                    }
                } else if (cb.getAttribute('data-reco') === '1' && cb.checked) {
                    var lab3 = cb.closest('label');
                    if (lab3 && cb.getAttribute('data-sold') !== '1') {
                        lab3.style.background = '#d1ecf1';
                        lab3.style.borderColor = '#17a2b8';
                        var tag3 = lab3.querySelector('span');
                        if (tag3) {
                            tag3.textContent = 'RECONDUIT';
                            tag3.style.color = '#0c5460';
                        }
                    }
                }
                syncHidden();
                return;
            }

            if (!isContiguous(nums)) {
                state.reverting = true;
                cb.checked = !cb.checked;
                state.reverting = false;
                setSummary('Plage contiguë requise.');
                return;
            }
            syncHidden();
        }

        function loadFromCategory(categ, inter1, inter2, sold) {
            if (!grid) {
                return Promise.resolve();
            }
            state.recoMode = false;
            state.reco = {};
            if (!categ) {
                grid.innerHTML = '<div class="col-12"><small class="text-muted">Choisissez une catégorie de bus.</small></div>';
                state.sold = {};
                syncHidden();
                return Promise.resolve();
            }
            grid.innerHTML = '<div class="col-12"><small class="text-muted">Chargement du plan…</small></div>';
            setSummary('Chargement…');
            var url = siteBase() + '/categories/getnbrplace/' + encodeURIComponent(categ);
            return fetchJson(url)
                .then(function (res) {
                    var n = res && res.nbr_place ? parseInt(res.nbr_place, 10) : 0;
                    if (n <= 0) {
                        grid.innerHTML = '<div class="col-12"><small class="text-muted">Catégorie sans plan de sièges.</small></div>';
                        state.sold = {};
                        setSummary('Quota : —');
                        return;
                    }
                    var d = parseInt(inter1, 10) || 1;
                    var f = parseInt(inter2, 10) || n;
                    renderAll(n, d, f, sold || [], null);
                })
                .catch(function () {
                    grid.innerHTML = '<div class="col-12"><small class="text-danger">Impossible de charger le plan de sièges.</small></div>';
                    setSummary('Erreur de chargement');
                });
        }

        function loadForEdit(codeProgr, ekey, inter1, inter2, categ, soldFallback) {
            state.editMode = true;
            var fallback = Array.isArray(soldFallback) ? soldFallback : [];
            if (!codeProgr || !ekey) {
                grid.innerHTML = '<div class="col-12"><small class="text-danger">Impossible de charger le plan : code programme manquant.</small></div>';
                setSummary('Erreur de chargement');
                return Promise.resolve();
            }
            grid.innerHTML = '<div class="col-12"><small class="text-muted">Chargement du plan…</small></div>';
            setSummary('Chargement…');
            // Minuscule : aligné routes.php (programmes/apercu_quota/…)
            var url = siteBase() + '/programmes/apercu_quota/' + encodeURIComponent(ekey) + '/' + encodeURIComponent(codeProgr);
            return fetchJson(url)
                .then(function (data) {
                    state.editMode = true;
                    var sold = fallback.slice();
                    if (data && data.ok && Array.isArray(data.sieges_occupes)) {
                        data.sieges_occupes.forEach(function (n) {
                            var num = parseInt(n, 10);
                            if (!isNaN(num) && num > 0 && sold.indexOf(num) === -1) {
                                sold.push(num);
                            }
                        });
                    }
                    // Édition : ne jamais basculer sur un plan « tout coché » sans bloqués.
                    if (!data || !data.ok) {
                        grid.innerHTML = '<div class="col-12"><small class="text-danger">Impossible de charger les sièges bloqués — fermez et rouvrez l’édition.</small></div>';
                        setSummary('Erreur de chargement');
                        return;
                    }
                    var recoList = null;
                    // Toujours passer la liste (même vide) pour distinguer « aucun bloqué » d’un échec.
                    var blockedList = Array.isArray(data.sieges_bloques) ? data.sieges_bloques : [];
                    var tamponList = null;
                    if (data.is_reconduction_cible && Array.isArray(data.sieges_reconduits) && data.sieges_reconduits.length) {
                        recoList = data.sieges_reconduits;
                    }
                    if (Array.isArray(data.sieges_tampon) && data.sieges_tampon.length) {
                        tamponList = data.sieges_tampon;
                    }
                    renderAll(
                        parseInt(data.nbr_place, 10) || 0,
                        parseInt(data.intervalle1, 10) || parseInt(inter1, 10) || 1,
                        parseInt(data.intervalle2, 10) || parseInt(inter2, 10) || 0,
                        sold,
                        recoList,
                        blockedList,
                        tamponList
                    );
                    var categSel = findCategSelect(block);
                    if (categSel && data.categori && !categSel.value) {
                        categSel.value = data.categori;
                    }
                })
                .catch(function () {
                    state.editMode = true;
                    grid.innerHTML = '<div class="col-12"><small class="text-danger">Impossible de charger le plan de sièges (réseau). Réessayez.</small></div>';
                    setSummary('Erreur de chargement');
                });
        }

        if (!block.getAttribute('data-quota-ui-bound')) {
            block.setAttribute('data-quota-ui-bound', '1');
            var btnAll = block.querySelector('.js-quota-check-all');
            if (btnAll) {
                btnAll.addEventListener('click', function () {
                    if (state.nbrPlace <= 0) {
                        return;
                    }
                    grid.querySelectorAll('.js-quota-siege').forEach(function (cb) {
                        if (cb.disabled) {
                            return;
                        }
                        if (state.recoMode && cb.getAttribute('data-reco') !== '1') {
                            return;
                        }
                        cb.checked = true;
                    });
                    syncHidden();
                });
            }
            var btnNone = block.querySelector('.js-quota-uncheck-all');
            if (btnNone) {
                btnNone.addEventListener('click', function () {
                    grid.querySelectorAll('.js-quota-siege').forEach(function (cb) {
                        if (!cb.disabled) {
                            cb.checked = false;
                        }
                    });
                    syncHidden();
                    setSummary('Au moins un siège requis.');
                });
            }
            if (form) {
                form.addEventListener('submit', function (ev) {
                    if (!grid.querySelector('.js-quota-siege')) {
                        return;
                    }
                    var nums = sortedNums(Array.from(grid.querySelectorAll('.js-quota-siege')));
                    if (!nums.length) {
                        ev.preventDefault();
                        setSummary('Sélectionnez au moins un siège.');
                        return false;
                    }
                    if (!state.editMode && !state.recoMode && !isContiguous(nums)) {
                        ev.preventDefault();
                        setSummary('Sélectionnez une plage de sièges contiguë.');
                        return false;
                    }
                    syncHidden();
                });
            }
        }

        block._quotaLoadEdit = loadForEdit;
        block._quotaLoadCategory = loadFromCategory;
        return block;
    }

    function loadCategoryForSelect(sel) {
        if (!sel || sel.name !== 'categorie') {
            return Promise.resolve();
        }
        var form = sel.closest('form');
        if (!form) {
            return Promise.resolve();
        }
        var block = form.querySelector('.js-quota-sieges-block');
        if (!block) {
            return Promise.resolve();
        }
        bindBlock(block);
        var debutEl = block.querySelector('.js-quota-debut-field');
        var finEl = block.querySelector('.js-quota-fin-field');
        var d = debutEl && debutEl.value ? parseInt(debutEl.value, 10) : 0;
        var f = finEl && finEl.value ? parseInt(finEl.value, 10) : 0;
        var sold = block._quotaState && block._quotaState.sold
            ? Object.keys(block._quotaState.sold).map(function (k) { return parseInt(k, 10); })
            : [];
        if (typeof block._quotaLoadCategory === 'function') {
            return block._quotaLoadCategory(sel.value, d, f, sold);
        }
        return Promise.resolve();
    }

    function init() {
        document.querySelectorAll('.js-quota-sieges-block').forEach(bindBlock);
    }

    function loadEditForForm(formId, codeProgr, ekey, inter1, inter2, categ, soldFallback) {
        var form = document.getElementById(formId);
        if (!form) {
            return Promise.resolve();
        }
        var block = form.querySelector('.js-quota-sieges-block');
        if (!block) {
            return Promise.resolve();
        }
        bindBlock(block);
        if (block._quotaState) {
            block._quotaState.editMode = true;
        }
        if (typeof block._quotaLoadEdit === 'function') {
            return block._quotaLoadEdit(codeProgr, ekey, inter1, inter2, categ, soldFallback || []);
        }
        return Promise.resolve();
    }

    document.addEventListener('change', function (ev) {
        var t = ev.target;
        if (t && t.tagName === 'SELECT' && t.name === 'categorie') {
            loadCategoryForSelect(t);
        }
    });

    global.ProgQuotaSieges = {
        init: init,
        loadEditForForm: loadEditForForm,
        loadCategoryForSelect: loadCategoryForSelect
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}(window));
