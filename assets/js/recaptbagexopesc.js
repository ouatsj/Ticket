document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.recaptbagexopesc').forEach(function (e) 
    {
        document.querySelector('h3#optitleesc').innerHTML = `EXERCICE MENSUEL BAGAGEESCAL OPERATEUR`;
        // Opérateurs : tri-filtre-dynamique.js (type=bagage)
        e.onclick = function () {
        let tickFormsgl = document.querySelector('#tickFormopesc');
            tickFormsgl.setAttribute('action', `${APP_ROOT}/Rapport/exercicesbagopesc/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
