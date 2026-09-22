document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adreportversgljs').forEach(function (e)
    {
        const title = document.querySelector('h3#Titlerepversgl');
        if (title) {
            title.innerHTML = `TRI REPORT GLOBAL DES RECETTES`;
        }
        e.onclick = function () {
            const tickversForm = document.querySelector('#tickversglForm');
            if (tickversForm) {
                tickversForm.setAttribute('action', `${APP_ROOT}/Rapport/exoreportsversgl/${e.dataset.ekey}/${e.dataset.idgares}`);
            }
        };
    });
});
