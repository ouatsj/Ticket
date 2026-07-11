document.addEventListener('DOMContentLoaded', () => {
   
    document.querySelectorAll('.sadsuivis').forEach(function (e) 
    {
        document.querySelector('h3#ssuiviTitlebg').innerHTML = `ENREGISTREMENT BAGAGES`;

            function loadProgrammesSuivi() {
                var selectLigne = document.querySelector('#sdeptscouridlignesuivi');
                var selectDate = document.querySelector('#scourdeptchoisirdatesuivi');
                var selectProg = document.querySelector('#scourdeptidprogsuivi');
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
                        opt.value = `${item.code_progr}/${item.ident_ligne}/${item.heure}/${item.id_ligneheure}/${item.depart_code}`;
                        opt.innerHTML = `${item.code_progr}/${item.heure}`;
                        selectProg.add(opt);
                    });
                };
                httpInfoprog.send();
            }

            let infoligner = document.querySelector('#sdeptscouridlignesuivi');
            if (infoligner !== null)
            infoligner.onchange = () => {
                document.querySelector('#scourdeptidprogsuivi').options.length = 1;
                document.querySelector('#quartieridbgsuivi').options.length = 1;
                document.querySelector('#snumcoderecu').value = '';
                document.querySelector('#snombreenvid').value = '';
                let httptypequartrbg;

                    const lidlignes = document.querySelector('#sdeptscouridlignesuivi')
                    .options[document.querySelector('#sdeptscouridlignesuivi').options.selectedIndex].value;
                    var ligne = parseLigneOption(lidlignes);
                    if (!ligne.gareDest) {
                        return;
                    }
                    httptypequartrbg = new XMLHttpRequest();
                    
                    httptypequartrbg.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifquart/${encodeURIComponent(ligne.gareDest)}`, true);
                    httptypequartrbg.onload = () => 
                    {
                        const courquarbg = JSON.parse(httptypequartrbg.responseText);
                        if (courquarbg == '') {
                            document.querySelector('#quartieridbgsuivi').options.length = 1;
                        }
                        else{
                            if (Object.entries(courquarbg).length >= 1) {
                                            
                                for (let key in Object.entries(courquarbg)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${courquarbg[key].nom_quartier}`;
                                    opt.innerHTML = `${courquarbg[key].nom_quartier}`;
                                    document.querySelector('#quartieridbgsuivi').add(opt);
                                }
                            } else {
                                document.querySelector('#quartieridbgsuivi').options.length = 1;
                            }
                        }
                    };
                    httptypequartrbg.setRequestHeader('Content-Type', 'application/json');
                    httptypequartrbg.send();

                    loadProgrammesSuivi();
            }
            let infoligne = document.querySelector('#scourdeptchoisirdatesuivi');
            if (infoligne !== null)
            infoligne.onchange = () => {
                document.querySelector('#snumcoderecu').value = '';
                document.querySelector('#snombreenvid').value = '';
                loadProgrammesSuivi();
            };

            /*let infrecubag = document.querySelector('#snumcoderecu');
            if (infrecubag !== null)
            infrecubag.onkeyup = () => {
                let httpInfosbag;
                if (window.XMLHttpRequest) {
                    httpInfosbag = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfosbag = new ActiveXObject("Microsoft.XMLHTTP");
                }

                var anencrbag = document.querySelector('#idanencourenv').value;

                var verificatbag = document.querySelector('#snumcoderecu').value;

                 const verife = `"${verificatbag}${anencrbag}"`;

                const lidlignes = document.querySelector('#sdeptscouridlignesuivi')
                .options[document.querySelector('#sdeptscouridlignesuivi').options.selectedIndex].value;
                var lidlignes1 = lidlignes.split('/');
                var lidlignes2 = lidlignes1[0];
                httpInfosbag.open('GET', window.location.origin + `${APP_ROOT}/confirmation/sverifinforecus/${verificatbag}${anencrbag}`, true);
                httpInfosbag.onload = () => {
                    
                    const infosbag = JSON.parse(httpInfosbag.responseText);
                    if (infosbag == null) {
                        document.querySelector('#ssmsmbg').style.display = 'block';
                        document.querySelector('#ssmsmvfbg').innerHTML = `Verifier le code de bagage saisi!`;
                        document.querySelector('#sidbagenv').value = "";
                        document.querySelector('#sgddeptsuiviid').value = "";
                        document.querySelector('#ssousgddeptsuiviid').value = "";
                        document.querySelector('#stypbagid').value = "";
                        document.querySelector('#snombrebgsuiviid').value = "";
                        document.querySelector('#scontenubgsuiviid').value = "";
                        document.querySelector('#sidgarbag').value = "";
                        document.querySelector('#lgidbagages').value = "";
                    } else {
                        if (Object.entries(infosbag).length > 1) {
                            
                            if (String(infosbag.id_bagage) == String(verife)) {
                                console.debug(`${infosbag.id_bagage}-${verife}-${infosbag.ident_ligne}-${lidlignes2}`, console.memory);
                                document.querySelector('#sidbagenv').value = `${infosbag.id_bagage}`;
                                document.querySelector('#sgddeptsuiviid').value = `${infosbag.idgarebag}`;
                                document.querySelector('#ssousgddeptsuiviid').value = `${infosbag.idsgarebag}`;
                                document.querySelector('#stypbagid').value = `${infosbag.typebagages}`;
                                document.querySelector('#snombrebgsuiviid').value = `${infosbag.nombrebagage}`;
                                document.querySelector('#scontenubgsuiviid').value = `${infosbag.contenubagage}`;
                                document.querySelector('#sidgarbag').value = `${infosbag.gidarrbag}`;
                                document.querySelector('#lgidbagages').value = `${infosbag.lgidbagage}`;
                                document.querySelector('#ssmsmbg').style.display = 'none';
                            } else {
                                console.debug(`${verife}`, console.memory);
                                
                                document.querySelector('#sidbagenv').value = "";
                                document.querySelector('#sgddeptsuiviid').value = "";
                                document.querySelector('#ssousgddeptsuiviid').value = "";
                                document.querySelector('#stypbagid').value = "";
                                document.querySelector('#snombrebgsuiviid').value = "";
                                document.querySelector('#scontenubgsuiviid').value = "";
                                document.querySelector('#sidgarbag').value = "";
                                document.querySelector('#lgidbagages').value = "";
                                document.querySelector('#ssmsmbg').style.display = 'block';
                                document.querySelector('#ssmsmvfbg').innerHTML = `Verifier le code bagage saisi!`;
                            }
                        }
                    }
                };
                httpInfosbag.setRequestHeader('Content-Type', 'application/json');
                httpInfosbag.send();
            };*/
            let infrecubag = document.querySelector('#snumcoderecu');
                let timerBag = null;

                if (infrecubag !== null) {
                    infrecubag.onkeyup = () => {

                        // ⛔ annule l'exécution précédente
                        clearTimeout(timerBag);

                        // ⏱ attend que l'utilisateur ait fini de taper
                        timerBag = setTimeout(() => {

                            let httpInfosbag;
                            if (window.XMLHttpRequest) {
                                httpInfosbag = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosbag = new ActiveXObject("Microsoft.XMLHTTP");
                            }

                            var anencrbag = document.querySelector('#idanencourenv').value;
                            var verificatbag = document.querySelector('#snumcoderecu').value;

                            // 🔒 sécurité minimale : pas de requête si vide
                            if (!verificatbag || !anencrbag) return;

                            const verife = `${verificatbag}${anencrbag}`;

                            const lidlignes = document.querySelector('#sdeptscouridlignesuivi')
                                .options[document.querySelector('#sdeptscouridlignesuivi').options.selectedIndex].value;
                            var lidlignes1 = lidlignes.split('/');
                            var lidlignes2 = lidlignes1[0];

                            httpInfosbag.open(
                                'GET',
                                window.location.origin + `${APP_ROOT}/confirmation/sverifinforecus/${verificatbag}${anencrbag}`,
                                true
                            );

                            httpInfosbag.onload = () => {

                                const infosbag = JSON.parse(httpInfosbag.responseText);

                                if (infosbag == null) {
                                    document.querySelector('#ssmsmbg').style.display = 'block';
                                    document.querySelector('#ssmsmvfbg').innerHTML = `Verifier le code de bagage saisi!`;

                                    document.querySelector('#sidbagenv').value = "";
                                    document.querySelector('#sgddeptsuiviid').value = "";
                                    document.querySelector('#ssousgddeptsuiviid').value = "";
                                    document.querySelector('#stypbagid').value = "";
                                    document.querySelector('#snombrebgsuiviid').value = "";
                                    document.querySelector('#scontenubgsuiviid').value = "";
                                    document.querySelector('#sidgarbag').value = "";
                                    document.querySelector('#lgidbagages').value = "";
                                } else {

                                    if (Object.entries(infosbag).length > 1) {

                                        if (infosbag.id_bagage === verife) {

                                            document.querySelector('#sidbagenv').value = infosbag.id_bagage;
                                            document.querySelector('#sgddeptsuiviid').value = infosbag.idgarebag;
                                            document.querySelector('#ssousgddeptsuiviid').value = infosbag.idsgarebag;
                                            document.querySelector('#stypbagid').value = infosbag.typebagages;
                                            document.querySelector('#snombrebgsuiviid').value = infosbag.nombrebagage;
                                            document.querySelector('#scontenubgsuiviid').value = infosbag.contenubagage;
                                            document.querySelector('#sidgarbag').value = infosbag.gidarrbag;
                                            document.querySelector('#lgidbagages').value = infosbag.lgidbagage;
                                            document.querySelector('#ssmsmbg').style.display = 'none';
                                        } else {
                                            document.querySelector('#sidbagenv').value = "";
                                            document.querySelector('#sgddeptsuiviid').value = "";
                                            document.querySelector('#ssousgddeptsuiviid').value = "";
                                            document.querySelector('#stypbagid').value = "";
                                            document.querySelector('#snombrebgsuiviid').value = "";
                                            document.querySelector('#scontenubgsuiviid').value = "";
                                            document.querySelector('#sidgarbag').value = "";
                                            document.querySelector('#lgidbagages').value = "";
                                            //document.querySelector('#slgidbagages').value = `${verificatbag}${anencrbag} ${infosbag.id_bagage}`;
                                            document.querySelector('#ssmsmbg').style.display = 'block';
                                            document.querySelector('#ssmsmvfbg').innerHTML = `Verifier le code bagage saisi!`;
                                        }
                                    }
                                }
                            };

                            httpInfosbag.send();

                        }, 600); // ⏱ 600ms = fin de saisie
                    };
                }


            let infolignep = document.querySelector('#scourdeptidprogsuivi');
            if (infolignep !== null)
            infolignep.onchange = () => {
                var verifintbag = document.querySelector('#lgidbagages').value;

                   const slidlignes = document.querySelector('#sdeptscouridlignesuivi')
                    .options[document.querySelector('#sdeptscouridlignesuivi').options.selectedIndex].value;
                    var slidlignes2 = parseLigneOption(slidlignes).ident;

                let httpRequestitines;
                httpRequestitines = new XMLHttpRequest();
                httpRequestitines.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifitine/${verifintbag}/${slidlignes2}`, true);
                httpRequestitines.onload = () => {
                const donitiness = JSON.parse(httpRequestitines.responseText);
                    if((donitiness.length > 0 ) || (verifintbag === slidlignes2))
                    {   
                        document.getElementById("snombreenvid").disabled = false;
                    }
                    else
                    {   
                        document.querySelector('#ssmlg').style.display = 'block';
                        document.querySelector('#ssmsmlg').innerHTML = `Verifiez la ligne choisi et comparer avec celui du recu`;
                        document.getElementById("snombreenvid").disabled = true;
                    }
                };
                httpRequestitines.setRequestHeader('Content-Type', 'application/json');
                httpRequestitines.send();
            };

            sverifnb = function () 
                        {
                var entree = parseInt(document.querySelector('#snombreenvid').value);
                    var n = document.querySelector('#snombreenvid').value;
                    var exist = parseInt(document.querySelector('#snombrebgsuiviid').value);
                        
                if(entree > exist) 
                {
                    document.querySelector('#ssmsmtbg').style.display = 'block';
                    document.querySelector('#ssmsmontantbg').innerHTML = `le mombre que vous aviez saisi dépasse le nombre de bagage`;
                    
                    document.querySelector('#snombreenvid').value = 'VERIFIER NOMBRE';  
                } 
                else
                {

                    document.querySelector('#ssmsmtbg').style.display = 'none';

                    document.querySelector('#snombreenvid').value = n ;
                    
                }
            };

        e.onclick = function (){
            let bordesForm = document.querySelector('#sbordesFormsuivi');
            bordesForm.setAttribute('action', `${APP_ROOT}/Confirmation/senregbagages/${e.dataset.cle_compagnie}`);
        }

    })
});