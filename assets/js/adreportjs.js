document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adreportjs').forEach(function (e) 
    {
        var title = document.querySelector('h3#Titlerep');
        if (title) {
            title.innerHTML = `EXERCICE MENSUEL TICKET GUICHETIER`;
        }
        // Opérateurs : chargés par tri-filtre-dynamique.js (utilisateurs/triactifs).
        e.onclick = function () {
            let tickForm = document.querySelector('#tickForm');
            if (tickForm) {
                tickForm.setAttribute('action', `${APP_ROOT}/Rapport/exoreports/${e.dataset.ekey}/${e.dataset.idgares}`);
            }
        };
    });
});
