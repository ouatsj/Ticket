document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adreportglesc').forEach(function (e) 
    {
        var title = document.querySelector('h3#Titlerepsesc');
        if (title) {
            title.innerHTML = `ETAT GLOBAL TICKET GUICHETIER ESCAL`;
        }
        // Opérateurs : chargés par tri-filtre-dynamique.js (utilisateurs/triactifs).
        e.onclick = function () {
            let tickForms = document.querySelector('#tickFormsesc');
            if (tickForms) {
                tickForms.setAttribute('action', `${APP_ROOT}/Rapport/reportsesc/${e.dataset.ekey}/${e.dataset.idsgare}`);
            }
        };
    });
});
