document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addversebank').forEach(function (e) 
    {
        document.querySelector('h3#bankTitle').innerHTML = `ENREGISTREMENT DES VERSEMENTS BANCAIRE`;

        verseverif = function () {
                    
                    var m = parseInt(document.querySelector('#versmontant').value);
                    var n = document.querySelector('#versmontant').value;
                    var solde = parseInt(document.querySelector('#soldecaisse').value);
                        
                            if (solde < m) 
                            {
        
                                document.querySelector('#smsverser').style.display = 'block';
                                document.querySelector('#versementsms').innerHTML = `le montant que vous aviez saisi dépasse le solde de votre caisse`;
                                
                                document.querySelector('#versmontant').value = 'VERIFIER SOLDE';
                            } 
                            else{

                                document.querySelector('#smsverser').style.display = 'none';

                                document.querySelector('#versmontant').value = n ;
                            }
                    };
            
        e.onclick = function () {
        let banq = document.querySelector('#verseFormbank');
        banq.setAttribute('action', `${APP_ROOT}/Caisses/addbank/${e.dataset.cle_compagnie}`);
        }

    })
});