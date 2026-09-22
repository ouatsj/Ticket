document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.advers').forEach(function (e)
    {
        const title = document.querySelector('h3#caiTitle');
        if (title) {
            title.innerHTML = `RECETTE PAR OPERATEUR TICKET`;
        }
        e.onclick = function () {
            const encaisForms = document.querySelector('#encaisForms');
            if (encaisForms) {
                encaisForms.setAttribute('action', `${APP_ROOT}/Rapport/triencaissement/${e.dataset.ekey}/${e.dataset.idsgare}`);
            }
        };
    });
});
