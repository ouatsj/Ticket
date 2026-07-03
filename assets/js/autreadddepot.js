document.addEventListener('DOMContentLoaded', () => {
        
    document.querySelectorAll('.autreadddepot').forEach(function (e) 
    {
        document.querySelector('h3#addautreTitle').innerHTML = `ENREGISTREMENT DES DEPOTS CLIENT`;

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
                depverif = function () {
                    
                    var depos = parseInt(document.querySelector('#depotautremontant').value);
                    var depo = document.querySelector('#depotautremontant').value;
                    var solddp = parseInt(document.querySelector('#soldeautrecaisse').value);
                        
                            if (solddp < depos) 
                            {
        
                                document.querySelector('#smsautredepot').style.display = 'block';
                                document.querySelector('#depotautresms').innerHTML = `le montant que vous aviez saisi dépasse le solde de votre caisse`;
                                
                                document.querySelector('#depotautremontant').value = 'VERIFIER SOLDE';
                            } 
                            else{

                                document.querySelector('#smsautredepot').style.display = 'none';

                                document.querySelector('#depotautremontant').value = depo ;
                            }
                    };
            
        e.onclick = function () {
        let autrelistedepot = document.querySelector('#depautredepot');
        autrelistedepot.setAttribute('action', `${APP_ROOT}/Depots/addautre/${e.dataset.cle_compagnie}`);
        }

    })
});