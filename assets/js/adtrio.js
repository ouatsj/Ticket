document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtrio').forEach(function (e)
    {
        const title = document.querySelector('h3#caisTitle');
        if (title) {
            title.innerHTML = `VERSEMENT TICKET GUICHETIER`;
        }
        e.onclick = function () {
            const encaisForm = document.querySelector('#encaismentForm');
            if (encaisForm) {
                encaisForm.setAttribute('action', `${APP_ROOT}/Rapport/triencaissements/${e.dataset.ekey}/${e.dataset.idsgare}`);
            }
        };
    });
});
