document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtriocour').forEach(function (e)
    {
        const title = document.querySelector('h3#caisTitlecour');
        if (title) {
            title.innerHTML = `VERSEMENT COURRIER GUICHETIER`;
        }
        e.onclick = function () {
            const encaisForm = document.querySelector('#encaismentFormcour');
            if (encaisForm) {
                encaisForm.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementscour/${e.dataset.ekey}/${e.dataset.idsgare}`);
            }
        };
    });
});
