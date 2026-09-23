document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adreportversjs').forEach(function (e) 
    {
        var title = document.querySelector('h3#Titlerepvers');
        if (title) {
            title.innerHTML = `TRI REPORT DES RECETTES`;
        }
        // Opérateurs : chargés par tri-filtre-dynamique.js (utilisateurs/triactifs).
        e.onclick = function () {
            let tickversForm = document.querySelector('#tickversForm');
            if (tickversForm) {
                tickversForm.setAttribute('action', `${APP_ROOT}/Rapport/exoreportsvers/${e.dataset.ekey}/${e.dataset.idgares}`);
            }
        };
    });
});
