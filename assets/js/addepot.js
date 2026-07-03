document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addepot').forEach(function (e) 
    {
        document.querySelector('h3#depotTitle').innerHTML = `ENREGISTREMENT DES DEPOTS BANCAIRE`;

                depotverif = function () {
                    
                    var depot = parseInt(document.querySelector('#depotmontant').value);
                    var depo = document.querySelector('#depotmontant').value;
                    var soldedp = parseInt(document.querySelector('#soldecaisse').value);
                        
                            if (soldedp < depot) 
                            {
        
                                document.querySelector('#smsdepot').style.display = 'block';
                                document.querySelector('#depotsms').innerHTML = `le montant que vous aviez saisi dépasse le solde de votre caisse`;
                                
                                document.querySelector('#depotmontant').value = 'VERIFIER SOLDE';
                            } 
                            else{

                                document.querySelector('#smsdepot').style.display = 'none';

                                document.querySelector('#depotmontant').value = depo ;
                            }
                    };
            
        e.onclick = function () {
        let listedepot = document.querySelector('#depotForm');
        listedepot.setAttribute('action', `${APP_ROOT}/Depots/adddepot/${e.dataset.cle_compagnie}`);
        }

    })
});