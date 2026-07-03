document.addEventListener('DOMContentLoaded', () => {
   

    document.querySelectorAll('.adsuivis').forEach(function (e) 
    {
        document.querySelector('h3#suiviTitlebg').innerHTML = `ENREGISTREMENT BAGAGES`;

            let infoligne = document.querySelector('#courdeptchoisirdatesuivi');
            if (infoligne !== null)
            infoligne.onchange = () => {
                let httpInfoprog;
                if (window.XMLHttpRequest) {
                    httpInfoprog = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfoprog = new ActiveXObject("Microsoft.XMLHTTP");
                }
                const lidligne = document.querySelector('#deptscouridlignesuivi')
                .options[document.querySelector('#deptscouridlignesuivi').options.selectedIndex].value;
                var lidligne1 = lidligne.split('/');
                var lidligne2 = lidligne1[0];
                var lidligne3 = lidligne1[1];
                var verifidate = document.querySelector('#courdeptchoisirdatesuivi').value;
                httpInfoprog.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifprogramm/${lidligne2}/${verifidate}`, true);
                httpInfoprog.onload = () => {
                    const resultp = JSON.parse(httpInfoprog.responseText);
                    if(resultp == null){

                        
                    
                    } else {
                        if (Object.entries(resultp).length >= 1) 
                        {
                           
                            for (let key in Object.entries(resultp)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${resultp[key].code_progr}/${resultp[key].heure}/${resultp[key].id_ligneheure}/${resultp[key].depart_code}`;
                                    opt.innerHTML = `${resultp[key].code_progr}/${resultp[key].heure}`;
                                    document.querySelector('#courdeptidprogsuivi').add(opt);
                                }
                        } else {
                            document.querySelector('#courdeptidprogsuivi').options.length = 1;
                        }
                        
                    }
                };
                httpInfoprog.setRequestHeader('Content-Type', 'application/json');
                httpInfoprog.send();
                                     
            };
            let infrecubag = document.querySelector('#numcoderecu');
        if (infrecubag !== null)
            infrecubag.onkeyup = () => {
                let httpInfosbag;
                if (window.XMLHttpRequest) {
                    httpInfosbag = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfosbag = new ActiveXObject("Microsoft.XMLHTTP");
                }

                var verificatbag = document.querySelector('#numcoderecu').value;
                
                const lidlignes = document.querySelector('#deptscouridlignesuivi')
                .options[document.querySelector('#deptscouridlignesuivi').options.selectedIndex].value;
                var lidlignes1 = lidlignes.split('/');
                var lidlignes2 = lidlignes1[0];
                httpInfosbag.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinforecus/${verificatbag}/${lidlignes2}`, true);
                httpInfosbag.onload = () => {
                    
                    const infosbag = JSON.parse(httpInfosbag.responseText);
                    if (infosbag == null) {

                        document.querySelector('#smsmbg').style.display = 'block';
                        document.querySelector('#smsmvfbg').innerHTML = `Verifier le code de bagage saisi!`;
                        document.querySelector('#idbagenv').value = "";
                        document.querySelector('#gddeptsuiviid').value = "";
                        document.querySelector('#sousgddeptsuiviid').value = "";
                        document.querySelector('#typbagid').value = "";
                        document.querySelector('#nombrebgsuiviid').value = "";
                        document.querySelector('#contenubgsuiviid').value = "";
                        document.querySelector('#idgarbag').value = "";
                    } else {
                        if (Object.entries(infosbag).length > 1) {
                            
                            if (infosbag.id_bagage == verificatbag && infosbag.ident_ligne == lidlignes2){

                                console.debug(`${infosbag.id_bagage}-${verificatbag}-${infosbag.ident_ligne}-${lidlignes2}`, console.memory);
                                document.querySelector('#idbagenv').value = `${infosbag.id_bagage}`;
                                document.querySelector('#gddeptsuiviid').value = `${infosbag.idgarebag}`;
                                document.querySelector('#sousgddeptsuiviid').value = `${infosbag.idsgarebag}`;
                                document.querySelector('#typbagid').value = `${infosbag.typebagages}`;
                                document.querySelector('#nombrebgsuiviid').value = `${infosbag.nombrebagage}`;
                                document.querySelector('#contenubgsuiviid').value = `${infosbag.contenubagage}`;
                                document.querySelector('#idgarbag').value = `${infosbag.gidarrbag}`;
                                document.querySelector('#smsmbg').style.display = 'none';
                            } else {
                                document.querySelector('#idbagenv').value = "";
                                document.querySelector('#gddeptsuiviid').value = "";
                                document.querySelector('#sousgddeptsuiviid').value = "";
                                document.querySelector('#typbagid').value = "";
                                document.querySelector('#nombrebgsuiviid').value = "";
                                document.querySelector('#contenubgsuiviid').value = "";
                                document.querySelector('#idgarbag').value = "";
                                document.querySelector('#smsmbg').style.display = 'block';
                                document.querySelector('#smsmvfbg').innerHTML = `Verifier le code de bagage saisi!`;
                    
                            }
                        }
                    }
                };
                httpInfosbag.setRequestHeader('Content-Type', 'application/json');
                httpInfosbag.send();
            };

            verifnb = function () 
            {
                var entree = parseInt(document.querySelector('#nombreenvid').value);
                    var n = document.querySelector('#nombreenvid').value;
                    var exist = parseInt(document.querySelector('#nombrebgsuiviid').value);
                        
                if(entree > exist) 
                {
                    document.querySelector('#smsmtbg').style.display = 'block';
                    document.querySelector('#smsmontantbg').innerHTML = `le mombre que vous aviez saisi dépasse le nombre de bagage`;
                    
                    document.querySelector('#nombreenvid').value = 'VERIFIER NOMBRE';  
                } 
                else
                {

                    document.querySelector('#smsmtbg').style.display = 'none';

                    document.querySelector('#nombreenvid').value = n ;
                    
                }
            };

        e.onclick = function (){
            let bordesForm = document.querySelector('#bordesFormsuivi');
            bordesForm.setAttribute('action', `${APP_ROOT}/Confirmation/enregbagages/${e.dataset.cle_compagnie}`);
        }

    })
});