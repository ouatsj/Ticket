document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.ventechefguichet').forEach(function (e) 
    {
        document.querySelector('h3#Titlerecet').innerHTML = `ARRET COMPTE VENTE`;

        let typinf = document.querySelector('#idgenrechef');
        
        if (typinf !== null) 
        typinf.onchange = () => 
        {
                document.querySelector('#idnomprenomchef').options.length = 1;
                    let httpInfostypinf;
                    if (window.XMLHttpRequest) {
                        httpInfostypinf = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfostypinf = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var verificationtypinf = document.querySelector('#idgenrechef')
                    .options[document.querySelector('#idgenrechef').options.selectedIndex].value;
                    httpInfostypinf.open('GET', window.location.origin + `${APP_ROOT}/recettes/nom_genre/${verificationtypinf}`, true);
                    httpInfostypinf.onload = () => {
                        const resulte = JSON.parse(httpInfostypinf.responseText);
        
                            if(resulte == null){
                                document.querySelector('#idnomprenomchef').value = "";
        
                            } 
                            else
                            {

                                
                                if (Object.entries(resulte).length >= 1) {
                            
                                    for (let key in Object.entries(resulte)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${resulte[key].nomprenom_perso}`;
                                        opt.innerHTML = `${resulte[key].nomprenom_perso}`;
                                        document.querySelector('#idnomprenomchef').add(opt);
                                        
                                    }
                                } else {
                                    document.querySelector('#idnomprenomchef').options.length = 1;
                                }
                            }
                        };
                        
                        httpInfostypinf.setRequestHeader('Content-Type', 'application/json');
                        httpInfostypinf.send();
    
                };
            
        e.onclick = function () {
        let listerecets = document.querySelector('#Formrecette');
        listerecets.setAttribute('action', `${APP_ROOT}/Caisses/validerec/${e.dataset.cle_compagnie}/${e.dataset.id_cpuser}/${e.dataset.dateconect}/${e.dataset.gareuser}`);
            $('#upmontant').val(`${e.dataset.montant}`);
        }

    })
});