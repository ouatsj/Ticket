document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtrioexoesc').forEach(function (e) 
    {
        var title = document.querySelector('h3#caisTitleexoesc');
        if (title) {
            title.innerHTML = `BROUILLARD(EXERCICE)TICKET ESCAL`;
        }
        // Opérateurs : chargés par tri-filtre-dynamique.js (utilisateurs/triactifs).
        e.onclick = function () {
            let encaisForm = document.querySelector('#encaismentFormexoesc');
            if (encaisForm) {
                encaisForm.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementsexoesc/${e.dataset.ekey}/${e.dataset.idsgare}`);
            }
        };
    });
});
