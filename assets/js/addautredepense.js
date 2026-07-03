document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addautredepense').forEach(function (e) 
    {
        document.querySelector('h3#depTitle').innerHTML = `ENREGISTREMENT DES AUTRES DEPENSES`;
        

                verifautre = function () {
                    
                    var m = parseInt(document.querySelector('#autredepmontant').value);
                    var n = document.querySelector('#autredepmontant').value;
                    var solde = parseInt(document.querySelector('#autremontcaisse').value);
                        
                            if (solde < m) 
                            {
        
                                document.querySelector('#autresms').style.display = 'block';
                                document.querySelector('#smsmontant').innerHTML = `le montant que vous aviez saisi dépasse le solde de votre caisse`;
                                
                                document.querySelector('#autredepmontant').value = 'VERIFIER SOLDE';
                            } 
                            else{

                                document.querySelector('#autresms').style.display = 'none';

                                document.querySelector('#autredepmontant').value = n ;
                            }
                };
            
        e.onclick = function () {
        let listedepens = document.querySelector('#depensForm');
        listedepens.setAttribute('action', `${APP_ROOT}/Depenses/addautre/${e.dataset.cle_compagnie}`);
        }

    })
});