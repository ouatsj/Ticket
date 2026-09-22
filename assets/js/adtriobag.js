document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtriobag').forEach(function (e)
    {
        const title = document.querySelector('h3#caisTitlebag');
        if (title) {
            title.innerHTML = `VERSEMENT BAGAGES`;
        }
        e.onclick = function () {
            const encaisForm = document.querySelector('#encaismentFormbag');
            if (encaisForm) {
                encaisForm.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementsbag/${e.dataset.ekey}/${e.dataset.idsgare}`);
            }
        };
    });
});
