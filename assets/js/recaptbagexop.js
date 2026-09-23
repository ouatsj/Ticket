document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.recaptbagexop').forEach(function (e) 
    {
        document.querySelector('h3#optitle').innerHTML = `EXERCICE MENSUEL BAGAGE OPERATEUR`;
        // Opérateurs : tri-filtre-dynamique.js (type=bagage)
        e.onclick = function () {
        let tickFormsgl = document.querySelector('#tickFormop');
            tickFormsgl.setAttribute('action', `${APP_ROOT}/Rapport/exercicesbagop/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
