document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.listetirage').forEach(function (e) 
    {
        document.querySelector('h3#listeTitle').innerHTML = `LISTE DES PASSAGERS`;

            let infoligne = document.querySelector('#choisirdateliste');
            if (infoligne !== null)
                infoligne.onchange = () => {
                    let httpInfosheure;
                    if (window.XMLHttpRequest) {
                        httpInfosheure = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfosheure = new ActiveXObject("Microsoft.XMLHTTP");
                    }

                    document.querySelector('#choisirheureliste').options.length = 1;
                    var dt = document.querySelector('#choisirdateliste').value;
                    const idlignes = document.querySelector('#idligneliste')
                    .options[document.querySelector('#idligneliste').options.selectedIndex].value;
                    
                    httpInfosheure.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheure/${idlignes}/${dt}`, true);
                    httpInfosheure.onload = () => {
                        const resultheur = JSON.parse(httpInfosheure.responseText);
                        if (!resultheur || resultheur === '') {
                            return;
                        }
                        if (Array.isArray(resultheur) && resultheur.length >= 1) {
                            resultheur.forEach(function (row) {
                                let opt = document.createElement('option');
                                opt.value = `${row.heure_identif}/${row.heure}`;
                                opt.innerHTML = `${row.heure}`;
                                document.querySelector('#choisirheureliste').add(opt);
                            });
                        } else {
                            document.querySelector('#choisirheureliste').options.length = 1;
                        }
                    };
                    httpInfosheure.setRequestHeader('Content-Type', 'application/json');
                    httpInfosheure.send();
                                         
            };
        
        
        e.onclick = function () {
        let Formliste = document.querySelector('#Formliste');
        Formliste.setAttribute('action', `${APP_ROOT}/Ticket/listechefguichet/${e.dataset.cle_compagnie}`);
        }

    })
});