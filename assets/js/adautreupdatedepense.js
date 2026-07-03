document.addEventListener('DOMContentLoaded', () => {
    
    verifautredepense = function () {
                    
                    var mt = parseInt(document.querySelector('#autremontantidentif').value);
                    var nt = document.querySelector('#autremontantidentif').value;
                    var soldet = parseInt(document.querySelector('#autresoldecaisse').value);
                        
                            if (soldet < mt) 
                            {
        
                                document.querySelector('#autresmsmt').style.display = 'block';
                                document.querySelector('#smsmontantdep').innerHTML = `le montant que vous aviez saisi dépasse le solde de votre caisse`;
                                
                                document.querySelector('#autremontantidentif').value = 'VERIFIER SOLDE';
                            } 
                            else{

                                document.querySelector('#autresmsmt').style.display = 'none';

                                document.querySelector('#autremontantidentif').value = nt ;
                            }
                };
    
});