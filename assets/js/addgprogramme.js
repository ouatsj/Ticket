document.addEventListener('DOMContentLoaded', () => {

    function parseVentesSgs(raw) {
        var map = {};
        String(raw || '').split(',').forEach(function (part) {
            part = String(part || '').trim();
            if (!part) return;
            var bits = part.split(':');
            var id = String(bits[0] || '').trim();
            var nb = parseInt(bits[1], 10) || 0;
            if (id && nb > 0) map[id] = nb;
        });
        return map;
    }

    function applyVentesLock(box, ventes) {
        box.querySelectorAll('.js-sg-check').forEach(function (c) {
            var nb = ventes[String(c.value)] || 0;
            var badge = c.parentNode ? c.parentNode.querySelector('.js-sg-ventes') : null;
            var wrap = c.closest('.form-group') || c.parentNode;
            if (nb > 0) {
                c.setAttribute('data-locked', '1');
                c.checked = true;
                if (wrap) wrap.style.opacity = '0.65';
                if (badge) {
                    badge.style.display = '';
                    badge.textContent = ' — ' + nb + ' vente' + (nb > 1 ? 's' : '');
                }
            } else {
                c.setAttribute('data-locked', '0');
                if (wrap) wrap.style.opacity = '';
                if (badge) {
                    badge.style.display = 'none';
                    badge.textContent = '';
                }
            }
        });
    }

    document.querySelectorAll('.addgprogramme').forEach(function (e) {
        
        e.onclick = function () {
            let prForm = document.querySelector('#formprog');
            document.querySelector('h3#Titleprog').innerHTML = `MODIFICATION DU PROGRAMME`;
            $('#idcateg').val(`${e.dataset.categorie}`);
            $('#typetaf').val(`${e.dataset.typtarif}`);
            if (typeof window.__syncDepartCompagnie === 'function') {
                window.__syncDepartCompagnie('progh', e.dataset.eure);
            } else {
                $('#progh').val(`${e.dataset.eure}`);
            }
            $('#progdate').val(`${e.dataset.pdate}`);
            var ouotAncien = document.querySelector('#ouotafinancien');
            var ouotNouveau = document.querySelector('#ouotafinnouveau');
            if (ouotAncien) ouotAncien.value = `${e.dataset.categnbplace}`;
            if (ouotNouveau) ouotNouveau.value = `${e.dataset.categnbplace}`;
            var portee = (e.dataset.porteeSgs || '').trim();
            var ids = portee ? portee.split(',').map(function (x) { return String(x).trim(); }).filter(Boolean) : [];
            var ventes = parseVentesSgs(e.dataset.ventesSgs);
            var box = document.querySelector('#portee_sousgares_box_edit');
            if (box) {
                var radioGare = box.querySelector('.js-scope-mode[value="gare"]');
                var radioSg = box.querySelector('.js-scope-mode[value="sousgare"]');
                var checks = box.querySelectorAll('.js-sg-check');
                applyVentesLock(box, ventes);
                if (ids.length === 0) {
                    if (radioGare) radioGare.checked = true;
                    checks.forEach(function (c) { c.checked = true; });
                } else {
                    if (radioSg) radioSg.checked = true;
                    checks.forEach(function (c) {
                        var locked = c.getAttribute('data-locked') === '1';
                        c.checked = locked || ids.indexOf(String(c.value)) !== -1;
                    });
                }
                if (typeof window.__applyPorteeScopeMode === 'function') {
                    window.__applyPorteeScopeMode(box);
                }
                if (ids.length > 0) {
                    checks.forEach(function (c) {
                        var locked = c.getAttribute('data-locked') === '1';
                        c.checked = locked || ids.indexOf(String(c.value)) !== -1;
                        c.disabled = locked ? true : !(radioSg && radioSg.checked);
                    });
                } else {
                    checks.forEach(function (c) {
                        c.checked = true;
                        c.disabled = true;
                    });
                }
            }

            if (window.ProgQuotaSieges && typeof window.ProgQuotaSieges.loadEditForForm === 'function') {
                var soldAttr = (e.dataset.siegesOccupes || '').split(',').map(function (x) {
                    return parseInt(String(x).trim(), 10);
                }).filter(function (n) { return !isNaN(n) && n > 0; });
                window.ProgQuotaSieges.loadEditForForm(
                    'formprog',
                    e.dataset.code,
                    e.dataset.cle_compagnie,
                    e.dataset.inter1,
                    e.dataset.inter2,
                    e.dataset.categorie,
                    soldAttr
                );
            }
        
            let typcat = document.querySelector('#idcateg');
            if (typcat !== null) {
                typcat.onchange = () => {
                    let Infoscateg;
                    if (window.XMLHttpRequest) {
                        Infoscateg = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        Infoscateg = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var categchoisi = document.querySelector('#idcateg')
                        .options[document.querySelector('#idcateg').options.selectedIndex].value;
                    Infoscateg.open('GET', window.location.origin + `${APP_ROOT}/categories/getnbrplace/${categchoisi}`, true);
                    Infoscateg.onload = () => {
                        const rescat = JSON.parse(Infoscateg.responseText);
                        if (Object.entries(rescat).length >= 1) {
                            var ouotFinNouveau = document.querySelector('#ouotafinnouveau');
                            if (ouotFinNouveau) {
                                ouotFinNouveau.value = `${rescat.nbr_place}`;
                            }
                        }
                    };
                    Infoscateg.setRequestHeader('Content-Type', 'application/json');
                    Infoscateg.send();
                };
            }
            prForm.setAttribute('action', `${APP_ROOT}/Programmes/editgare_/${e.dataset.cle_compagnie}/${e.dataset.code}/${e.dataset.departcd}`);

        }
    })
});
