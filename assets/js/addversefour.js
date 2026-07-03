document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addversefour').forEach(function (e) 
    {
        document.querySelector('h3#autrevserseTitle').innerHTML = `ENREGISTREMENT DES VERSEMENTS FOURNISSEUR`;

        let typf = document.querySelector('#fourni_id');
        
        if (typf !== null) 
        typf.onchange = () => 
        {
            let Infostypinff;
            if (window.XMLHttpRequest) {
                Infostypinff = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                Infostypinff = new ActiveXObject("Microsoft.XMLHTTP");
            }
            document.querySelector('#nomprenomf').options.length = 1;
            var typerecetchoisif = document.querySelector('#fourni_id')
            .options[document.querySelector('#fourni_id').options.selectedIndex].value;

            Infostypinff.open('GET', window.location.origin + `${APP_ROOT}/depenses/listesnom/${typerecetchoisif}`, true);
            Infostypinff.onload = () => {
                const resulf = JSON.parse(Infostypinff.responseText);

                    if(resulf == null){
                        document.querySelector('#nomprenomf').value = "";

                    } 
                    if (Object.entries(resulf).length >= 1) {

                        for (let key in Object.entries(resulf)) {
                            let opt = document.createElement('option');
                            opt.value = `${resulf[key].nom_client} ${resulf[key].prenom_client}`;
                            opt.innerHTML = `${resulf[key].nom_client} ${resulf[key].prenom_client}`;
                            document.querySelector('#nomprenomf').add(opt);
                            
                        }
                    } else {
                        document.querySelector('#nomprenomf').options.length = 1;
                    }

                };
                
                Infostypinff.setRequestHeader('Content-Type', 'application/json');
                Infostypinff.send();

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
        autre.setAttribute('action', `${APP_ROOT}/Caisses/addverseautrefour/${e.dataset.cle_compagnie}`);
        }

    })
});