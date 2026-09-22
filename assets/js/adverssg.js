document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adverssg').forEach(function (e)
    {
        const title = document.querySelector('h3#caiTitlesg');
        if (title) {
            title.innerHTML = `RECETTE GLOBALE TICKET PAR GARE`;
        }
        e.onclick = function () {
            const encaisForms = document.querySelector('#encaisFormssg');
            if (encaisForms) {
                encaisForms.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementsg/${e.dataset.ekey}/${e.dataset.idsgare}/${e.dataset.idsggare}`);
            }
        };
    });
});
