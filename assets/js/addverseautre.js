document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addverseautre').forEach(function (e) 
    {
        document.querySelector('h3#autrevserseTitle').innerHTML = `ENREGISTREMENT DES VERSEMENTS CLIENT`;

    let typcl = document.querySelector('#client_ident');
        
        if (typcl !== null) 
        typcl.onchange = () => 
        {
            let Infostypinfcl;
            if (window.XMLHttpRequest) {
                Infostypinfcl = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                Infostypinfcl = new ActiveXObject("Microsoft.XMLHTTP");
            }
            document.querySelector('#prenomnomident').options.length = 1;
            var typerecetchoisicl = document.querySelector('#client_ident')
            .options[document.querySelector('#client_ident').options.selectedIndex].value;

            Infostypinfcl.open('GET', window.location.origin + `${APP_ROOT}/depenses/listesnom/${typerecetchoisicl}`, true);
            Infostypinfcl.onload = () => {
                const resulcl = JSON.parse(Infostypinfcl.responseText);

                    if(resulcl == null){
                        document.querySelector('#prenomnomident').value = "";

                    } 
                    if (Object.entries(resulcl).length >= 1) {

                        for (let key in Object.entries(resulcl)) {
                            let opt = document.createElement('option');
                            opt.value = `${resulcl[key].nom_client} ${resulcl[key].prenom_client}`;
                            opt.innerHTML = `${resulcl[key].nom_client} ${resulcl[key].prenom_client}`;
                            document.querySelector('#prenomnomident').add(opt);
                            
                        }
                    } else {
                        document.querySelector('#prenomnomident').options.length = 1;
                    }

                };
                
                Infostypinfcl.setRequestHeader('Content-Type', 'application/json');
                Infostypinfcl.send();

        };
        verseverif = function () {
                    
                    var m = parseInt(document.querySelector('#autreversmontant').value);
                    var n = document.querySelector('#autreversmontant').value;
                    var solde = parseInt(document.querySelector('#soldecaisse').value);
                        
                            if (solde < m) 
                            {
        
                                document.querySelector('#autresmsverser').style.display = 'block';
                                document.querySelector('#autreversementsms').innerHTML = `le montant que vous aviez saisi dépasse le solde de votre caisse`;
                                
                                document.querySelector('#autreversmontant').value = 'VERIFIER SOLDE';
                            } 
                            else{

                                document.querySelector('#autresmsverser').style.display = 'none';

                                document.querySelector('#autreversmontant').value = n ;
                            }
                    };
            
        e.onclick = function () {
        let autre = document.querySelector('#verseFormautre');
        autre.setAttribute('action', `${APP_ROOT}/Caisses/addverseautre/${e.dataset.cle_compagnie}`);
        }

    })
});