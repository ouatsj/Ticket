document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtrioexoplis').forEach(function (e)
    {
        document.querySelector('h3#Titlexpglobvers').innerHTML = `BROUILLARD(EXERCICE) COURRIER`;
        // Guichetiers : tri-filtre-dynamique.js (type=courrier)
        e.onclick = function () {
        let encaisFormv = document.querySelector('#expglobFormsvers');
            encaisFormv.setAttribute('action', `${APP_ROOT}/Rapport/etatsverseplis/${e.dataset.ekey}/${e.dataset.idgare}`);
        }

    })
});
