document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adreportjsesc').forEach(function (e) 
    {
        var title = document.querySelector('h3#Titlerepesc');
        if (title) {
            title.innerHTML = `EXERCICE MENSUEL TICKET GUICHETIER ESCAL`;
        }
        // Opérateurs : chargés par tri-filtre-dynamique.js (utilisateurs/triactifs).
        e.onclick = function () {
            let tickForm = document.querySelector('#tickFormesc');
            if (tickForm) {
                tickForm.setAttribute('action', `${APP_ROOT}/Rapport/exoreportsesc/${e.dataset.ekey}/${e.dataset.idgares}`);
            }
        };
    });
});
