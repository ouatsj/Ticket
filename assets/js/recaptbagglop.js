document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.recaptbagglop').forEach(function (e) 
    {
        document.querySelector('h3#optitlegl').innerHTML = `ETAT GLOBAL BAGAGE OPERATEUR`;
        // Opérateurs : tri-filtre-dynamique.js (type=bagage)
        e.onclick = function () {
        let tickFormsgl = document.querySelector('#tickFormopgl');
            tickFormsgl.setAttribute('action', `${APP_ROOT}/Rapport/reportbaggl/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
