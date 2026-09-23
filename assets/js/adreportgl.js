document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adreportgl').forEach(function (e) 
    {
        document.querySelector('h3#Titlereps').innerHTML = `ETAT GLOBAL TICKET GUICHETIER`;

        // Opérateurs : chargés par tri-filtre-dynamique.js (utilisateurs/triactifs).
        e.onclick = function () {
        let tickForms = document.querySelector('#tickForms');
            tickForms.setAttribute('action', `${APP_ROOT}/Rapport/reports/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })

    if (!window.__recapGlTkSousgareBound) {
        window.__recapGlTkSousgareBound = true;
        document.querySelectorAll('select[name="sousgaretkt"]').forEach(function (sousSel) {
            var form = sousSel.closest('form');
            if (!form) return;
            var gareSel = form.querySelector('select[name="departgar"]');
            if (!gareSel) return;

            function resetSousGare() {
                sousSel.options.length = 0;
                var allOpt = document.createElement('option');
                allOpt.value = '';
                allOpt.innerHTML = 'Toutes';
                sousSel.add(allOpt);
            }

            gareSel.addEventListener('change', function () {
                var gid = gareSel.value;
                resetSousGare();
                if (!gid) return;
                var http = new XMLHttpRequest();
                http.open(
                    'GET',
                    window.location.origin + `${APP_ROOT}/programmes/verifsousgares/` + encodeURIComponent(gid),
                    true
                );
                http.onload = function () {
                    var rows = null;
                    try { rows = JSON.parse(http.responseText); } catch (err) { rows = null; }
                    resetSousGare();
                    if (!rows) return;
                    Object.keys(rows).forEach(function (key) {
                        var row = rows[key];
                        if (!row || row.idsousgare == null) return;
                        var opt = document.createElement('option');
                        opt.value = row.idsousgare;
                        opt.innerHTML = row.nomsousgare;
                        sousSel.add(opt);
                    });
                };
                http.send();
            });
        });
    }
});