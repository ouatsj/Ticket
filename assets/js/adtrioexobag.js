document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtrioexobag').forEach(function (e)
    {
        document.querySelector('h3#Titlexpglobversbg').innerHTML = `BROUILLARD(EXERCICE) BAGAGES`;
        // Opérateurs : tri-filtre-dynamique.js (type=bagage)
        e.onclick = function () {
        let encaisFormvb = document.querySelector('#expglobFormsversbg');
            encaisFormvb.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementsexobag/${e.dataset.ekey}/${e.dataset.idgare}`);
        }

    })
});
