document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addbordt').forEach(function (e) 
    {
        document.querySelector('h3#bordTitlet').innerHTML = `TIRAGE BORDEREAU`;


                    
            let arcourr = document.querySelector('#couridlignedeptt');
            if (arcourr !== null)
            arcourr.onchange = () => {
                
                document.querySelector('#choisirheurecourdeptt').options.length = 1;
                document.querySelector('#quartieridbgt').options.length = 1;
                const lidlignecr = document.querySelector('#couridlignedeptt')
                .options[document.querySelector('#couridlignedeptt').options.selectedIndex].value;
                var lidlignecr1 = lidlignecr.split('/');
                var lidlignecr2 = lidlignecr1[0];
                var qart = lidlignecr2.split('-');
                var lidlignecr3 = qart[0];
                var lidlignecr4 = qart[1];
                let httptypequartr;
                httptypequartr = new XMLHttpRequest();
                
                httptypequartr.open('GET', window.location.origin + `${APP_ROOT}/Confirmation/verifquart/${lidlignecr4}`, true);
                httptypequartr.onload = () => 
                {
                    const courquar = JSON.parse(httptypequartr.responseText);
                    if (courquar == '') {
                        document.querySelector('#quartieridbgt').options.length = 1;
                    }
                    else{
                        if (Object.entries(courquar).length >= 1) {
                                        
                            for (let key in Object.entries(courquar)) {
                                let opt = document.createElement('option');
                                opt.value = `${courquar[key].nom_quartier}`;
                                opt.innerHTML = `${courquar[key].nom_quartier}/${courquar[key].code_quart}`;
                                document.querySelector('#quartieridbgt').add(opt);
                            }
                        } else {
                            document.querySelector('#quartieridbgt').options.length = 1;
                        }
                    }
                    

                };
                httptypequartr.setRequestHeader('Content-Type', 'application/json');
                httptypequartr.send();
            };
                    let infdatecour = document.querySelector('#choisirdatecourdeptt');
                    
                    if (infdatecour !== null) 
                    infdatecour.onchange = () => 
                    {
                    
                        let httpInfoscodebordereau;
                        if (window.XMLHttpRequest) {
                            httpInfoscodebordereau = new XMLHttpRequest();
                        } else if (window.ActiveXObject) {
                            httpInfoscodebordereau = new ActiveXObject("Microsoft.XMLHTTP");
                        }
                            document.querySelector('#choisirheurecourdeptt').options.length = 1;
                            document.querySelector('#idprogcourdeptt').options.length = 1;

                        var verifdatebord = document.querySelector('#choisirdatecourdeptt').value;
                        const veriflignebord = document.querySelector('#couridlignedeptt')
                                .options[document.querySelector('#couridlignedeptt').options.selectedIndex].value;

                        
                        httpInfoscodebordereau.open('GET', window.location.origin + `${APP_ROOT}/Confirmation/verifitiragedepart/${veriflignebord}/${verifdatebord}`, true);
                        httpInfoscodebordereau.onload = () => 
                        {
                            const heurebord = JSON.parse(httpInfoscodebordereau.responseText);
                            if(heurebord == ''){
                                document.querySelector('#infosmsheuret').style.display = 'block';
                                document.querySelector('#erreurinfoheuret').innerHTML = `Il n'y a pas de programme pour le moment`;
                            } else
                            {
                                if (Object.entries(heurebord).length >= 1) 
                                {
                                        document.querySelector('#infosmsheuret').style.display = 'none';

                                        for (let key in Object.entries(heurebord)) {
                                            document.querySelector('#chaufdeptt').value = `${heurebord[key].chauff}`;
                                            document.querySelector('#convoideptt').value = `${heurebord[key].convoy}`;
                                            document.querySelector('#ligndeptt').value = `${heurebord[key].nom_ligne}`;
                                            document.querySelector('#datedeptt').value = `${heurebord[key].datedepart_bus}`;
                                            document.querySelector('#progdeptt').value = `${heurebord[key].depart_code}`;

                                                let opt = document.createElement('option');
                                                opt.value = `${heurebord[key].id_ligneheure}/${heurebord[key].heure}`;
                                                opt.innerHTML = `${heurebord[key].heure}`;
                                                document.querySelector('#choisirheurecourdeptt').add(opt);

                                                
                                            }
                                } else {

                                    document.querySelector('#choisirheurecourdeptt').options.length = 1;

                                }
                            }   
                        };
                        httpInfoscodebordereau.setRequestHeader('Content-Type', 'application/json');
                        httpInfoscodebordereau.send();
                    };
                   
                
                let hrcour = document.querySelector('#choisirheurecourdeptt');
                    
                    if (hrcour !== null) 
                    hrcour.onchange = () => 
                    {
                    
                        let httpprog;
                        if (window.XMLHttpRequest) {
                            httpprog = new XMLHttpRequest();
                        } else if (window.ActiveXObject) {
                            httpprog = new ActiveXObject("Microsoft.XMLHTTP");
                        }
                            document.querySelector('#idprogcourdeptt').options.length = 1;

                        var verifhrcour = document.querySelector('#choisirdatecourdeptt').value;
                        const veriflignehrcour = document.querySelector('#couridlignedeptt')
                                .options[document.querySelector('#couridlignedeptt').options.selectedIndex].value;

                            const veriflignehr1 = document.querySelector('#choisirheurecourdeptt')
                                .options[document.querySelector('#choisirheurecourdeptt').options.selectedIndex].value;

                                var veriflignehr2 = veriflignehr1.split('/');
                            var hrex1 = veriflignehr2[0];
                            var hrex2 = veriflignehr2[1];

                        httpprog.open('GET', window.location.origin + `${APP_ROOT}/Confirmation/verifitiragedeparth/${veriflignehrcour}/${verifhrcour}/${hrex1}`, true);
                        httpprog.onload = () => 
                        {
                            const hprog = JSON.parse(httpprog.responseText);
                            if(hprog == ''){
                                

                            } else 
                            {
                                if (Object.entries(hprog).length >= 1) 
                                {

                                        for (let key in Object.entries(hprog)) {
                                            
                                                let opt = document.createElement('option');
                                                opt.value = `${hprog[key].code_progr}/${hprog[key].depart_code}/${hprog[key].chauff}/${hprog[key].convoy}`;
                                                opt.innerHTML = `${hprog[key].code_progr}/${hprog[key].depart_code}`;
                                                document.querySelector('#idprogcourdeptt').add(opt);

                                            }
                                } else {
                                    
                                    document.querySelector('#idprogcourdeptt').options.length = 1;

                                }
                            }   
                        };
                        httpprog.setRequestHeader('Content-Type', 'application/json');
                        httpprog.send();
                    };
        e.onclick = function (){
            let bordeForm = document.querySelector('#bordeFormt');
            bordeForm.setAttribute('action', `${APP_ROOT}/Rapport/listebisep/${e.dataset.cle_compagnie}`);
        }

    })
});