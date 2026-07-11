document.addEventListener('DOMContentLoaded', () => {
   

    document.querySelectorAll('.adsuivis').forEach(function (e) 
    {
        document.querySelector('h3#suiviTitlebg').innerHTML = `ENREGISTREMENT BAGAGES`;

            function loadProgrammesSuiviLegacy() {
                var selectLigne = document.querySelector('#deptscouridlignesuivi');
                var selectDate = document.querySelector('#courdeptchoisirdatesuivi');
                var selectProg = document.querySelector('#courdeptidprogsuivi');
                if (!selectLigne || !selectDate || !selectProg) {
                    return;
                }

                var ligne = parseLigneOption(selectLigne.options[selectLigne.selectedIndex].value);
                var verifidate = selectDate.value;
                selectProg.options.length = 1;

                if (!ligne.ident || !verifidate) {
                    return;
                }

                var httpInfoprog = new XMLHttpRequest();
                httpInfoprog.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifprogramm/${encodeURIComponent(ligne.ident)}/${verifidate}`, true);
                httpInfoprog.onload = function () {
                    var resultp;
                    try {
                        resultp = JSON.parse(httpInfoprog.responseText);
                    } catch (err) {
                        return;
                    }

                    if (!resultp || !resultp.length) {
                        selectProg.options.length = 1;
                        return;
                    }

                    resultp.forEach(function (item) {
                        var opt = document.createElement('option');
                        opt.value = `${item.code_progr}/${item.heure}/${item.id_ligneheure}/${item.depart_code}`;
                        opt.innerHTML = `${item.code_progr}/${item.heure}`;
                        selectProg.add(opt);
                    });
                };
                httpInfoprog.send();
            }

            let infolignes = document.querySelector('#deptscouridlignesuivi');
            if (infolignes !== null) {
                infolignes.onchange = () => {
                    loadProgrammesSuiviLegacy();
                };
            }

            let infoligne = document.querySelector('#courdeptchoisirdatesuivi');
            if (infoligne !== null)
            infoligne.onchange = () => {
                loadProgrammesSuiviLegacy();
                                     
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
                var lidlignes2 = parseLigneOption(lidlignes).ident;
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