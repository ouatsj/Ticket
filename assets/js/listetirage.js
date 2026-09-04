document.addEventListener('DOMContentLoaded', () => {

    function __listeResetHeures() {
        var heure = document.querySelector('#choisirheureliste');
        if (heure) heure.options.length = 1;
    }

    function __listeLoadHeures() {
        var heureSel = document.querySelector('#choisirheureliste');
        if (heureSel) heureSel.options.length = 1;
        var dtEl = document.querySelector('#choisirdateliste');
        var lgEl = document.querySelector('#idligneliste');
        if (!dtEl || !lgEl) return;
        var dt = dtEl.value;
        var idlignes = lgEl.value;
        if (!dt || !idlignes) return;

        var httpInfosheure;
        if (window.XMLHttpRequest) {
            httpInfosheure = new XMLHttpRequest();
        } else if (window.ActiveXObject) {
            httpInfosheure = new ActiveXObject('Microsoft.XMLHTTP');
        }
        httpInfosheure.open(
            'GET',
            window.location.origin + `${APP_ROOT}/programmes/verifheure/${encodeURIComponent(idlignes)}/${encodeURIComponent(dt)}`,
            true
        );
        httpInfosheure.onload = () => {
            try {
                const resultheur = JSON.parse(httpInfosheure.responseText);
                if (!resultheur || resultheur === '') return;
                if (Array.isArray(resultheur) && resultheur.length >= 1) {
                    resultheur.forEach(function (row) {
                        let opt = document.createElement('option');
                        opt.value = `${row.heure_identif}/${row.heure}`;
                        opt.innerHTML = `${row.heure}`;
                        if (heureSel) heureSel.add(opt);
                    });
                }
            } catch (err) {}
        };
        httpInfosheure.setRequestHeader('Content-Type', 'application/json');
        httpInfosheure.send();
    }
    
    document.querySelectorAll('.listetirage').forEach(function (e) 
    {
        document.querySelector('h3#listeTitle').innerHTML = `LISTE DES PASSAGERS`;

            let compagnieListe = document.querySelector('#compagnie_liste');
            if (compagnieListe !== null && !compagnieListe.dataset.listeBound) {
                compagnieListe.dataset.listeBound = '1';
                compagnieListe.addEventListener('change', function () {
                    var ligne = document.querySelector('#idligneliste');
                    if (ligne) ligne.value = '';
                    __listeResetHeures();
                });
            }

            let ligneListe = document.querySelector('#idligneliste');
            if (ligneListe !== null && !ligneListe.dataset.listeBound) {
                ligneListe.dataset.listeBound = '1';
                ligneListe.addEventListener('change', function () {
                    __listeLoadHeures();
                });
            }

            let infoligne = document.querySelector('#choisirdateliste');
            if (infoligne !== null && !infoligne.dataset.listeBound) {
                infoligne.dataset.listeBound = '1';
                infoligne.onchange = function () {
                    __listeLoadHeures();
                };
            }
        
        
        e.onclick = function () {
        let Formliste = document.querySelector('#Formliste');
        Formliste.setAttribute('action', `${APP_ROOT}/Ticket/listechefguichet/${e.dataset.cle_compagnie}`);
        }

    })
});
