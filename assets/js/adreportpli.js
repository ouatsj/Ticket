document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adreportpli').forEach(function (e) 
    {
        document.querySelector('h3#Titlexpglob').innerHTML = `EXERCICE MENSUEL COURRIER GUICHETIER`;
        // Guichetiers : tri-filtre-dynamique.js (type=courrier)
        e.onclick = function () {
            let expglobForms = document.querySelector('#expglobForms');
            expglobForms.setAttribute('action', `${APP_ROOT}/Rapport/etatsplis1/${e.dataset.ekey}/${e.dataset.idgares}`);
        }

    })
});
