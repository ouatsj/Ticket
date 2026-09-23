document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adreportpliesc').forEach(function (e) 
    {
        document.querySelector('h3#Titlexpglobesc').innerHTML = `EXERCICE MENSUEL COURRIERESCAL GUICHETIER`;
        // Guichetiers : tri-filtre-dynamique.js (type=courrier)
        e.onclick = function () {
            let expglobForms = document.querySelector('#expglobFormsesc');
            expglobForms.setAttribute('action', `${APP_ROOT}/Rapport/etatsplis1esc/${e.dataset.ekey}/${e.dataset.idgares}`);
        }

    })
});
