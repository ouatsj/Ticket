document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtrioexobagesc').forEach(function (e)
    {
        document.querySelector('h3#Titlexpglobversbgesc').innerHTML = `BROUILLARD(EXERCICE) BAGAGESESCAL`;
        // Opérateurs : tri-filtre-dynamique.js (type=bagage)
        e.onclick = function () {
        let encaisFormvbe = document.querySelector('#expglobFormsversbgesc');
            encaisFormvbe.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementsexobagesc/${e.dataset.ekey}/${e.dataset.idgare}`);
        }

    })
});
