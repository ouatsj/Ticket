document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtrioexo').forEach(function (e) 
    {
        var title = document.querySelector('h3#caisTitleexo');
        if (title) {
            title.innerHTML = `BROUILLARD(EXERCICE) TICKET`;
        }
        // Opérateurs : chargés par tri-filtre-dynamique.js (utilisateurs/triactifs).
        e.onclick = function () {
            let encaisForm = document.querySelector('#encaismentFormexo');
            if (encaisForm) {
                encaisForm.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementsexo/${e.dataset.ekey}/${e.dataset.idsgare}`);
            }
        };
    });
});
