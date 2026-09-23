document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.recaptbagglopesc').forEach(function (e) 
    {
        document.querySelector('h3#optitleglesc').innerHTML = `ETAT GLOBAL BAGAGEESCAL OPERATEUR`;
        // Opérateurs : tri-filtre-dynamique.js (type=bagage)
        e.onclick = function () {
        let tickFormsgl = document.querySelector('#tickFormopglesc');
            tickFormsgl.setAttribute('action', `${APP_ROOT}/Rapport/reportbagglesc/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
