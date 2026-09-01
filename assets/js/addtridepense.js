document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addtridepense').forEach(function (e) {
        var title = document.querySelector('h3#depTitle');
        if (title) {
            title.innerHTML = `TRI DEPENSES`;
        }

        var gpinf = document.querySelector('#dtype');
        var typo = document.querySelector('#gtype');
        var gnom = document.querySelector('#gnom');
        var root = (typeof APP_ROOT !== 'undefined') ? APP_ROOT : '';

        if (gpinf !== null) {
            gpinf.onchange = () => {
                if (typo) {
                    typo.options.length = 1;
                    typo.selectedIndex = 0;
                }
                if (gnom) {
                    gnom.options.length = 1;
                    gnom.selectedIndex = 0;
                }

                var verificationtypinfo = gpinf.options[gpinf.selectedIndex].value;
                if (!verificationtypinfo) {
                    return;
                }

                var httpInfostypinfo = window.XMLHttpRequest
                    ? new XMLHttpRequest()
                    : new ActiveXObject('Microsoft.XMLHTTP');

                httpInfostypinfo.open(
                    'GET',
                    window.location.origin + root + '/depenses/listegenre/' + encodeURIComponent(verificationtypinfo),
                    true
                );
                httpInfostypinfo.onload = () => {
                    var resp;
                    try {
                        resp = JSON.parse(httpInfostypinfo.responseText);
                    } catch (err) {
                        return;
                    }

                    if (!typo) {
                        return;
                    }
                    typo.options.length = 1;

                    if (!resp) {
                        return;
                    }

                    var list = Array.isArray(resp)
                        ? resp
                        : Object.keys(resp).map(function (k) { return resp[k]; });

                    for (var i = 0; i < list.length; i++) {
                        if (!list[i] || list[i].genre_depens == null || list[i].genre_depens === '') {
                            continue;
                        }
                        var opt = document.createElement('option');
                        opt.value = list[i].genre_depens;
                        opt.textContent = list[i].genre_depens;
                        typo.appendChild(opt);
                    }
                };
                httpInfostypinfo.send();
            };
        }

        if (typo !== null) {
            typo.onchange = () => {
                if (gnom) {
                    gnom.options.length = 1;
                    gnom.selectedIndex = 0;
                }

                var idcaissEl = document.querySelector('#idcaiss');
                var idcaid = idcaissEl ? idcaissEl.value : '';
                var typedepchoisi = gpinf ? gpinf.options[gpinf.selectedIndex].value : '';
                var ficationtypinfo = typo.options[typo.selectedIndex].value;

                if (!idcaid || !typedepchoisi || !ficationtypinfo) {
                    return;
                }

                var Infostypinfo = window.XMLHttpRequest
                    ? new XMLHttpRequest()
                    : new ActiveXObject('Microsoft.XMLHTTP');

                Infostypinfo.open(
                    'GET',
                    window.location.origin + root + '/depenses/listenom/'
                        + encodeURIComponent(idcaid) + '/'
                        + encodeURIComponent(typedepchoisi) + '/'
                        + encodeURIComponent(ficationtypinfo),
                    true
                );
                Infostypinfo.onload = () => {
                    var respe;
                    try {
                        respe = JSON.parse(Infostypinfo.responseText);
                    } catch (err) {
                        return;
                    }

                    if (!gnom) {
                        return;
                    }
                    gnom.options.length = 1;

                    if (!respe) {
                        return;
                    }

                    var list = Array.isArray(respe)
                        ? respe
                        : Object.keys(respe).map(function (k) { return respe[k]; });

                    for (var i = 0; i < list.length; i++) {
                        if (!list[i] || list[i].nom_perso == null || list[i].nom_perso === '') {
                            continue;
                        }
                        var opt = document.createElement('option');
                        opt.value = list[i].nom_perso;
                        opt.textContent = list[i].nom_perso;
                        gnom.appendChild(opt);
                    }
                };
                Infostypinfo.send();
            };
        }

        e.onclick = function () {
            var listedepense = document.querySelector('#dpForm');
            if (listedepense) {
                listedepense.setAttribute(
                    'action',
                    root + '/Rapport/depense/' + e.dataset.cle_compagnie
                );
            }
        };
    });
});
