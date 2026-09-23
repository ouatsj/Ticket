document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtrioexoplisesc').forEach(function (e)
    {
        document.querySelector('h3#Titlexpglobversesc').innerHTML = `BROUILLARD(EXERCICE) COURRIERESCAL`;
        // Guichetiers : tri-filtre-dynamique.js (type=courrier)
        e.onclick = function () {
        let encaisFormv = document.querySelector('#expglobFormsversesc');
            encaisFormv.setAttribute('action', `${APP_ROOT}/Rapport/etatsverseplisesc/${e.dataset.ekey}/${e.dataset.idgare}`);
        }

    })
});
