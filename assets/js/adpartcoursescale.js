document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adpartcoursescale').forEach(function (e) 
    {       

            let arcour = document.querySelector('#arrscourpartoesc');
            if (arcour !== null)
            arcour.onchange = () => {
                document.querySelector('#date_depheurecourexpartoesc').value = '';
                document.querySelector('#hdepcourpartoesc').options.length = 1;
                document.querySelector('#quartiercourpartoesc').options.length = 1;
                const garedepartcour = document.querySelector('#deparcourpartoesc').value;
                const garearriv = document.querySelector('#arrscourpartoesc').value;
                var post_ar = garearriv.split('/');
                var seltar = post_ar[0];
                var sougidarr = post_ar[1];
                let httptypequart;
                httptypequart = new XMLHttpRequest();
                
                httptypequart.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${seltar}`, true);
                httptypequart.onload = () => 
                {
                    const courqua = JSON.parse(httptypequart.responseText);
                    if (courqua == '') {
                        document.querySelector('#quartiercourpartoesc').options.length = 1;
                    }
                    else{
                        if (Object.entries(courqua).length >= 1) {
                                        
                            for (let key in Object.entries(courqua)) {
                                let opt = document.createElement('option');
                                opt.value = `${courqua[key].code_quart}/${courqua[key].nom_quartier}`;
                                opt.innerHTML = `${courqua[key].nom_quartier}/${courqua[key].code_quart}`;
                                document.querySelector('#quartiercourpartoesc').add(opt);
                            }
                        } else {
                            document.querySelector('#quartiercourpartoesc').options.length = 1;
                        }
                    }
                    

                };
                httptypequart.setRequestHeader('Content-Type', 'application/json');
                httptypequart.send();
            };
            let dpcourex = document.querySelector('#date_depheurecourexpartoesc');
            if (dpcourex !== null)
               dpcourex.onchange = () => {

                    const dateactuex = document.querySelector('#dateactpartoesc').value;
                    const garearriveex = document.querySelector('#arrscourpartoesc').value;
                    const progdepartex = document.querySelector('#date_depheurecourexpartoesc').value;
                    const garedepartcourex = document.querySelector('#deparcourpartoesc').value;
                    document.querySelector('#hdepcourpartoesc').options.length = 1;
                    var post_lhdepex = garedepartcourex.split('/');
                    var seltdepex = post_lhdepex[0];
                    var sougidex = post_lhdepex[1];
                    var post_arrex = garearriveex.split('/');
                    var seltarrex = post_arrex[0];
                    var sougidarex = post_arrex[1];
                    if(progdepartex >= dateactuex)
                    {

                            let httpRequtcourex;

                                if (window.XMLHttpRequest) {
                                    httpRequtcourex = new XMLHttpRequest();
                                } else if (window.ActiveXObject) {
                                    httpRequtcourex = new ActiveXObject("Microsoft.XMLHTTP");
                                }

                            const reponsecourex = httpRequtcourex.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheure1/${seltdepex}-${seltarrex}/${progdepartex}`, true);
                            httpRequtcourex.onload = () => 
                            {
                                const infoscourex = JSON.parse(httpRequtcourex.responseText);
                                    document.querySelector('#smsdtcrpartoesc').style.display = 'none';
                                    document.querySelector('#date_depheurecourexpartoesc').style.color = "black";
                                    document.querySelector('#date_depheurecourexpartoesc').style.border = "1px solid"; 

                                if(infoscourex == '')
                                {
                                

                                }
                                else
                                {
                                    if (Object.entries(infoscourex).length >= 1) {
                                   
                                        for (let key in Object.entries(infoscourex)) {
                                            let opt = document.createElement('option');
                                            opt.value = `${infoscourex[key].id_ligneheure}`;
                                            opt.innerHTML = `${infoscourex[key].heure}`;
                                            document.querySelector('#hdepcourpartoesc').add(opt);
                                        }
                                    } else {
                                        document.querySelector('#hdepcourpartoesc').options.length = 1;
                                    }
                                } 
                                
                            };
                            httpRequtcourex.setRequestHeader('Content-Type', 'application/json');
                            httpRequtcourex.send();
                
                    }
                    else
                    {
                        document.querySelector('#date_depheurecourexpartoesc').style.color = "#FF0000";
                        document.querySelector('#date_depheurecourexpartoesc').style.border = "2px solid #FF0000";
                        document.querySelector('#smsdtcrpartoesc').style.display = 'block';
                        document.querySelector('#erreurSmsdtcrpartoesc').innerHTML = `Date non valide.`;
                    }
                };

               let typers = document.querySelector('#type_personpartoesc');
                if (typers !== null)
                typers.onchange = () => {

                    var typersopers = document.querySelector('#type_personpartoesc').
                        options[document.querySelector('#type_personpartoesc').options.selectedIndex].value;
                        var typerso1pers = typersopers.split('/');
                        var typerso2pers = typerso1pers[0];
                        var typerso3pers = typerso1pers[1];

                        document.querySelector('#types_courrierspartoesc').options.length = 1;
                        document.querySelector('#partenairespartoesc').options.length = 1;      
                        document.querySelector('#idvalepartoesc').style.display = 'block';
                        document.querySelector('#valeur1partoesc').style.display = 'block';
                        document.querySelector('#idfraispartoesc').style.display = 'block';
                        document.querySelector('#fraisexpartoesc').style.display = 'block';
                        document.querySelector('#exp_nompartoesc').value = "";
                        document.querySelector('#exp_prenompartoesc').value = "";
                        document.querySelector('#cnib_exppartoesc').value = "";
                        document.querySelector('#iddate_cnibpartoesc').value = "";
                        document.querySelector('#lieudelexppartoesc').value = "";
                        document.querySelector('#passcompagniepartoesc').value = "";
                        document.querySelector('#rclientcpexppartoesc').value = "";
                        document.querySelector('#prnclientcpexppartoesc').value = "";
                        document.querySelector('#cnibcpexppartoesc').value = "";
                        document.querySelector('#date_cnibcpexppartoesc').value = "";
                        document.querySelector('#lieudelivrecpexppartoesc').value = "";
                        document.querySelector('#idclientypeexppartoesc').value = "";
                    
                    if((typerso3pers === 'partenaire_specifique') || (typerso3pers === 'partenaire_client') || (typerso3pers === 'partenaire_simple'))
                    {


                        document.querySelector('#partidpartoesc').style.display = 'block';
                        document.querySelector('#partenairespartoesc').style.display = 'block';
                        document.querySelector('#idvalepartoesc').style.display = 'block';
                        document.querySelector('#valeur1partoesc').style.display = 'block';
                        document.querySelector('#idfraispartoesc').style.display = 'block';
                        document.querySelector('#fraisexpartoesc').style.display = 'block';
                        document.querySelector('#fraisexpartoesc').value = '';
                        
                        let httppaterns;
                            if (window.XMLHttpRequest) {
                                httppaterns = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httppaterns = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                            httppaterns.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectpartenaire/${typerso3pers}`, true);
                            httppaterns.onload = () => {
                                const infosparten = JSON.parse(httppaterns.responseText);

                                if (Object.entries(infosparten).length >= 1) 
                                {

                                    for (let key in Object.entries(infosparten))
                                    {

                                        let opt = document.createElement('option');
                                        opt.value = `${infosparten[key].id_client}`;
                                        opt.innerHTML = `${infosparten[key].nom_client} ${infosparten[key].prenom_client}`;
                                        document.querySelector('#partenairespartoesc').add(opt);
                                    }
                                        
                                }
                                else 
                                {
                                    document.querySelector('#partenairespartoesc').options.length = 1;
                                }

                            };
                            httppaterns.setRequestHeader('Content-Type', 'application/json');
                            httppaterns.send();
                        
                    }
                    
                    
                };
                let paternstscd = document.querySelector('#partenairespartoesc');
                if (paternstscd !== null)
                paternstscd.onchange = () => {
                    let httpRequesttespers;
                        httpRequesttespers = new XMLHttpRequest();
                        document.querySelector('#types_courrierspartoesc').options.length = 1;
                        var terns= document.querySelector('#partenairespartoesc').
                        options[document.querySelector('#partenairespartoesc').options.selectedIndex].value;
                        httpRequesttespers.open('GET', window.location.origin + `${APP_ROOT}/confirmation/fetch_typecourrier/${terns}`, true);
                        httpRequesttespers.onload = () => {
                            const dataperso = JSON.parse(httpRequesttespers.responseText);
                            if(dataperso == ''){
                                
                                let httpRequesttesperso;
                                httpRequesttesperso = new XMLHttpRequest();

                                    
                                httpRequesttesperso.open('GET', window.location.origin + `${APP_ROOT}/confirmation/fetch_typecourriers`, true);
                                httpRequesttesperso.onload = () => {
                                    const datapersos = JSON.parse(httpRequesttesperso.responseText);
                                         if (Object.entries(datapersos).length >= 1) {
                                       
                                            for (let key in Object.entries(datapersos)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${datapersos[key].id_cat}/${datapersos[key].categ}/${datapersos[key].indicatif}`;
                                                opt.innerHTML = `${datapersos[key].categ}`;
                                                document.querySelector('#types_courrierspartoesc').add(opt);
                                            }
                                        } else {
                                            document.querySelector('#types_courrierspartoesc').options.length = 1;
                                        }
                                };
                        
                                httpRequesttesperso.setRequestHeader('Content-Type', 'application/json');
                                httpRequesttesperso.send();
                                        
                            }else
                            {
                                if (Object.entries(dataperso).length >= 1) {
                               
                                    for (let key in Object.entries(dataperso)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${dataperso[key].id_cat}/${dataperso[key].categ}/${dataperso[key].indicatif}`;
                                        opt.innerHTML = `${dataperso[key].categ}`;
                                        document.querySelector('#types_courrierspartoesc').add(opt);
                                    }
                                } else {
                                    document.querySelector('#types_courrierspartoesc').options.length = 1;
                                }
                            }
                        };
                
                        httpRequesttespers.setRequestHeader('Content-Type', 'application/json');
                        httpRequesttespers.send();
                };

                
                let tscd = document.querySelector('#types_courrierspartoesc');
                if (tscd !== null)
                tscd.onchange = () => {
                        let httpRequesttes;
    
                        if (window.XMLHttpRequest) {
                            httpRequesttes = new XMLHttpRequest();
                        } else if (window.ActiveXObject) {
                            httpRequesttes = new ActiveXObject("Microsoft.XMLHTTP");
                        }

                        var typerso = document.querySelector('#type_personpartoesc').
                        options[document.querySelector('#type_personpartoesc').options.selectedIndex].value;
                        var typerso1 = typerso.split('/');
                        var typerso2 = typerso1[0];
                        var typerso3 = typerso1[1];

                        var selectorscd = document.querySelector('#types_courrierspartoesc').
                        options[document.querySelector('#types_courrierspartoesc').options.selectedIndex].value;
                        
                        var nat = selectorscd.split('/');
                        var natid = nat[0];
                        var natu = nat[1];

                        var natid1 = natu.split('/');
                        var natu1 = natid1[0];
                        var natu2 = natid1[0];

                        const departdirectioncour = document.querySelector('#deparcourpartoesc').value;
                        var post_lhcour = departdirectioncour.split('/');
                        var seltdepcour = post_lhcour[0];
                        var sougidcour = post_lhcour[1];
                        const directioncour = document.querySelector('#arrscourpartoesc').value;
                        var directar = directioncour.split('/');
                        var directarg = directar[0];
                        var directarcd = directar[1];
                        httpRequesttes.open('GET', window.location.origin + `${APP_ROOT}/confirmation/fetch_mont/${natid}/${seltdepcour}-${directarg}/${typerso2}`, true);
                        httpRequesttes.onload = () => {
                            const data = JSON.parse(httpRequesttes.responseText);
                            if(data == null){
                                
        
                            }else
                            {
                                if (Object.entries(data).length >= 1) {
                                
                                    document.querySelector('#val1partoesc').value = `${data.val1}`;
                                    document.querySelector('#val2partoesc').value = `${data.val2}`;
                                    document.querySelector('#montantpartoesc').value = `${data.montant}`;
                                    document.querySelector('#intervpartoesc').value = `${data.id_inter}`

                                } 
                            }
                        };
                
                        httpRequesttes.setRequestHeader('Content-Type', 'application/json');
                        httpRequesttes.send();
     

                        if(typerso3 === 'partenaire_client' || typerso3 === 'partenaire_simple'){
                            document.querySelector('#idvalepartoesc').style.display = 'block';
                            document.querySelector('#valeur1partoesc').style.display = 'block';
                            document.querySelector('#idfraispartoesc').style.display = 'block';
                            document.querySelector('#fraisexpartoesc').style.display = 'block';
                            document.querySelector('#exp_prenompartoesc').value = "";
                            document.querySelector('#cnib_exppartoesc').value = "";
                            document.querySelector('#iddate_cnibpartoesc').value = "";
                            document.querySelector('#lieudelexppartoesc').value = "";
                            document.querySelector('#passcompagniepartoesc').value = "";
                            document.querySelector('#rclientcpexppartoesc').value = "";
                            document.querySelector('#prnclientcpexppartoesc').value = "";
                            document.querySelector('#cnibcpexppartoesc').value = "";
                            document.querySelector('#date_cnibcpexppartoesc').value = "";
                            document.querySelector('#lieudelivrecpexppartoesc').value = "";
                            document.querySelector('#idclientypeexppartoesc').value = "";
                            let httpInfoscl;
                            if (window.XMLHttpRequest) {
                                httpInfoscl = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfoscl = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            var idclit = document.querySelector('#partenairespartoesc').options[document.querySelector('#partenairespartoesc').options.selectedIndex].value;
                
                            httpInfoscl.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${idclit}`, true);
                            httpInfoscl.onload = () => {
                                const infosc = JSON.parse(httpInfoscl.responseText);
                                
                                    if (Object.entries(infosc).length >= 1) {
                                        
                                        document.querySelector('#exp_nompartoesc').value = `${infosc.nom_client}`;
                                        document.querySelector('#exp_prenompartoesc').value = `${infosc.prenom_client}`;
                                        document.querySelector('#cnib_exppartoesc').value = `${infosc.num_CNIB}`;
                                        document.querySelector('#iddate_cnibpartoesc').value = `${infosc.date_delivre}`;
                                        document.querySelector('#lieudelexppartoesc').value = `${infosc.lieu_delivre}`;
                                        document.querySelector('#passcompagniepartoesc').value = `${infosc.id_client}`;
                                        document.querySelector('#rclientcpexppartoesc').value = `${infosc.nom_client}`;
                                        document.querySelector('#prnclientcpexppartoesc').value = `${infosc.prenom_client}`;
                                        document.querySelector('#cnibcpexppartoesc').value = `${infosc.num_CNIB}`;
                                        document.querySelector('#date_cnibcpexppartoesc').value = `${infosc.date_delivre}`;
                                        document.querySelector('#lieudelivrecpexppartoesc').value = `${infosc.lieu_delivre}`;
                                        document.querySelector('#idclientypeexppartoesc').value = `${infosc.type_client}`;
                                      
                                        
                                    } 
                                    else
                                    {
                                        document.querySelector('#exp_nompartoesc').value = "";
                                        document.querySelector('#exp_prenompartoesc').value = "";
                                        document.querySelector('#cnib_exppartoesc').value = "";
                                        document.querySelector('#iddate_cnibpartoesc').value = "";
                                        document.querySelector('#lieudelexppartoesc').value = "";
                                        document.querySelector('#passcompagniepartoesc').value = "";
                                        document.querySelector('#rclientcpexppartoesc').value = "";
                                        document.querySelector('#prnclientcpexppartoesc').value = "";
                                        document.querySelector('#cnibcpexppartoesc').value = "";
                                        document.querySelector('#date_cnibcpexppartoesc').value = "";
                                        document.querySelector('#lieudelivrecpexppartoesc').value = "";
                                        document.querySelector('#idclientypeexppartoesc').value = "";
                                      
                                        
                                    }
                                                            
                            };
                            httpInfoscl.setRequestHeader('Content-Type', 'application/json');
                            httpInfoscl.send();
                            
                        }

                        if(typerso3 === 'partenaire_specifique'){
                            document.querySelector('#idvalepartoesc').style.display = 'none';
                            document.querySelector('#valeur1partoesc').style.display = 'none';
                            document.querySelector('#idfraispartoesc').style.display = 'none';
                            document.querySelector('#fraisexpartoesc').style.display = 'none';
                            document.querySelector('#fraisexpartoesc').value = 0;
                            document.querySelector('#exp_prenompartoesc').value = "";
                            document.querySelector('#cnib_exppartoesc').value = "";
                            document.querySelector('#iddate_cnibpartoesc').value = "";
                            document.querySelector('#lieudelexppartoesc').value = "";
                            document.querySelector('#passcompagniepartoesc').value = "";
                            document.querySelector('#rclientcpexppartoesc').value = "";
                            document.querySelector('#prnclientcpexppartoesc').value = "";
                            document.querySelector('#cnibcpexppartoesc').value = "";
                            document.querySelector('#date_cnibcpexppartoesc').value = "";
                            document.querySelector('#lieudelivrecpexppartoesc').value = "";
                            document.querySelector('#idclientypeexppartoesc').value = "";
                            let httpInfoscl2;
                            if (window.XMLHttpRequest) {
                                httpInfoscl2 = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfoscl2 = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            var idclit2 = document.querySelector('#partenairespartoesc').options[document.querySelector('#partenairespartoesc').options.selectedIndex].value;
                
                            httpInfoscl2.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${idclit2}`, true);
                            httpInfoscl2.onload = () => {
                                const infosc2 = JSON.parse(httpInfoscl2.responseText);
                                
                                    if (Object.entries(infosc2).length >= 1) {
                                        
                                        document.querySelector('#exp_nompartoesc').value = `${infosc2.nom_client}`;
                                        document.querySelector('#exp_prenompartoesc').value = `${infosc2.prenom_client}`;
                                        document.querySelector('#cnib_exppartoesc').value = `${infosc2.num_CNIB}`;
                                        document.querySelector('#iddate_cnibpartoesc').value = `${infosc2.date_delivre}`;
                                        document.querySelector('#lieudelexppartoesc').value = `${infosc2.lieu_delivre}`;
                                        document.querySelector('#passcompagniepartoesc').value = `${infosc2.id_client}`;
                                        document.querySelector('#rclientcpexppartoesc').value = `${infosc2.nom_client}`;
                                        document.querySelector('#prnclientcpexppartoesc').value = `${infosc2.prenom_client}`;
                                        document.querySelector('#cnibcpexppartoesc').value = `${infosc2.num_CNIB}`;
                                        document.querySelector('#date_cnibcpexppartoesc').value = `${infosc2.date_delivre}`;
                                        document.querySelector('#lieudelivrecpexppartoesc').value = `${infosc2.lieu_delivre}`;
                                        document.querySelector('#idclientypeexppartoesc').value = `${infosc2.type_client}`;
                                      
                                        
                                    } 
                                    else
                                    {
                                        document.querySelector('#exp_nompartoesc').value = "";
                                        document.querySelector('#exp_prenompartoesc').value = "";
                                        document.querySelector('#cnib_exppartoesc').value = "";
                                        document.querySelector('#iddate_cnibpartoesc').value = "";
                                        document.querySelector('#lieudelexppartoesc').value = "";
                                        document.querySelector('#passcompagniepartoesc').value = "";
                                        document.querySelector('#rclientcpexppartoesc').value = "";
                                        document.querySelector('#prnclientcpexppartoesc').value = "";
                                        document.querySelector('#cnibcpexppartoesc').value = "";
                                        document.querySelector('#date_cnibcpexppartoesc').value = "";
                                        document.querySelector('#lieudelivrecpexppartoesc').value = "";
                                        document.querySelector('#idclientypeexppartoesc').value = "";
                                      
                                        
                                    }
                                                            
                            };
                            httpInfoscl2.setRequestHeader('Content-Type', 'application/json');
                            httpInfoscl2.send();
                            
                        }
                        
                };

                   
                let infopersos = document.querySelector('#idtypepartoesc');
        
                if (infopersos !== null) 
                infopersos.onchange = () => 
                {
                
                    document.querySelector('#contactidpartoesc').style.display = 'none';
                    document.querySelector('#idcontpartoesc').style.display = 'none';
                    document.querySelector('#sonnelpartoesc').style.display = 'none';
                    document.querySelector('#idsonnelspartoesc').style.display = 'none';
                    document.querySelector('#idpartespartoesc').options.length = 1;
                    document.querySelector('#contactidpartoesc').value = '';
                    document.querySelector('#partcontpartoesc').style.display = 'none';
                            
                    var personns = document.querySelector('#idtypepartoesc')
                        .options[document.querySelector('#idtypepartoesc').options.selectedIndex].value;
                        if(personns === 'personnel')
                        {
                            document.querySelector('#membrepartcontesc').style.display = 'none';
                            document.querySelector('#idmembrenameesc').style.display = 'none';
                            document.querySelector('#sonnelpartoesc').style.display = 'block';
                            document.querySelector('#idsonnelspartoesc').style.display = 'block';
                            document.querySelector('#contactidpartoesc').style.display = 'none';
                            document.querySelector('#idcontpartoesc').style.display = 'none';
                            document.querySelector('#partcontpartoesc').style.display = 'none';
                            document.querySelector('#idpartespartoesc').style.display = 'none';
                            
                                    let httppersosdest;
                            if (window.XMLHttpRequest) {
                                httppersosdest = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httppersosdest = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                            httppersosdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectperso/${personns}`, true);
                            httppersosdest.onload = () => 
                            {

                                const infospersdest = JSON.parse(httppersosdest.responseText);

                                if (Object.entries(infospersdest).length >= 1) 
                                {


                                    for (let key in Object.entries(infospersdest))
                                    {

                                        let opt = document.createElement('option');
                                        opt.value = `${infospersdest[key].matricule}`;
                                        opt.innerHTML = `${infospersdest[key].nomprenom_perso}`;
                                        document.querySelector('#idsonnelspartoesc').add(opt);
                                    }
                                 
                                }
                                else 
                                {
                                    document.querySelector('#idsonnelspartoesc').options.length = 1;
                                }

                            };
                            httppersosdest.setRequestHeader('Content-Type', 'application/json');
                            httppersosdest.send();

                            let infopersosdest = document.querySelector('#idsonnelspartoesc');
        
                            if (infopersosdest !== null) 
                            infopersosdest.onchange = () => 
                            {

                            
                                let httpInfospersdest;
                                if (window.XMLHttpRequest) {
                                    httpInfospersdest = new XMLHttpRequest();
                                } else if (window.ActiveXObject) {
                                    httpInfospersdest = new ActiveXObject("Microsoft.XMLHTTP");
                                }

                                document.querySelector('#contactidpartoesc').style.display = 'none';
                                document.querySelector('#idcontpartoesc').style.display = 'none';
                                document.querySelector('#compagniepassdestpartoesc').value = '';
                                var idverifidest = document.querySelector('#idsonnelspartoesc').options[document.querySelector('#idsonnelspartoesc').options.selectedIndex].value;
                    
                                httpInfospersdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoperso/${idverifidest}`, true);
                                httpInfospersdest.onload = () => {
                                    const infosperdest = JSON.parse(httpInfospersdest.responseText);
                                    
                                    if (Object.entries(infosperdest).length >= 1) {
                                        
                               
                                        var typepersosdest = `${infosperdest.nomprenom_perso}`;
                                        var typer1persosdest = typepersosdest.split(' ');
                                        var typer2persosdest = typer1persosdest[0];
                                        var typer3persosdest = typer1persosdest[1];
                                        var typer4persosdest = typer1persosdest[2];
                                        if(typer4persosdest === undefined){
                                            var typer5persosdest = `${typer3persosdest}`;
                                        }
                                        else{
                                            var typer5persosdest = `${typer3persosdest} ${typer4persosdest}`;
                                        }
                                        document.querySelector('#nomdestidpartoesc').value = `${typer2persosdest}`;
                                        document.querySelector('#prenomdestidpartoesc').value = `${typer5persosdest}`;
                                        document.querySelector('#persodestcompagniepartoesc').value = `${infosperdest.matricule}`;
                                        document.querySelector('#rclientcpexppartoesc').value = `${typer2persosdest}`;
                                        document.querySelector('#prnclientcpexppartoesc').value = `${typer5persosdest}`;
                                        document.querySelector('#idclientypedestpartoesc').value = 'personnel';
                                        
                                    } 
                                    else
                                    {
                                        document.querySelector('#nomdestidpartoesc').value = "";
                                        document.querySelector('#prenomdestidpartoesc').value = "";
                                        document.querySelector('#persodestcompagniepartoesc').value = "";
                                        document.querySelector('#rclientcpdestpartoesc').value = "";
                                        document.querySelector('#prnclientcpdestpartoesc').value = "";
                                        document.querySelector('#idclientypedestpartoesc').value = "";
                                    }
                                                                
                                };
                                httpInfospersdest.setRequestHeader('Content-Type', 'application/json');
                                httpInfospersdest.send();
                            };
                        }
                        else
                        {
                            document.querySelector('#sonnelpartoesc').style.display = 'none';
                            document.querySelector('#idsonnelspartoesc').style.display = 'none';
                            document.querySelector('#partcontpartoesc').style.display = 'none';
                            document.querySelector('#idpartespartoesc').style.display = 'none';
                            document.querySelector('#idcontpartoesc').style.display = 'block';
                            document.querySelector('#contactidpartoesc').style.display = 'block';
                            document.querySelector('#idmatripartoesc').style.display = 'none';
                            document.querySelector('#matri_destpartoesc').style.display = 'none';
                            document.querySelector('#nomdestidpartoesc').value = "";
                            document.querySelector('#prenomdestidpartoesc').value = "";
                            document.querySelector('#compagniepassdestpartoesc').value = "";
                            document.querySelector('#rclientcpdestpartoesc').value = "";
                            document.querySelector('#prnclientcpdestpartoesc').value = "";
                            document.querySelector('#idclientypedestpartoesc').value = "";
                            document.querySelector('#idclientcontdestpartoesc').value = "";
                            document.querySelector('#membrepartcontesc').style.display = 'none';
                            document.querySelector('#idmembrenameesc').style.display = 'none';
                            
                            let infdest = document.querySelector('#contactidpartoesc');
                            if (infdest !== null)
                                infdest.onkeyup = () => {
                                    let httpInfosdest;
                                    if (window.XMLHttpRequest) {
                                        httpInfosdest = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) {
                                        httpInfosdest = new ActiveXObject("Microsoft.XMLHTTP");
                                    }

                                    document.querySelector('#nomdestidpartoesc').value = "";
                                    document.querySelector('#prenomdestidpartoesc').value = "";
                                    document.querySelector('#compagniepassdestpartoesc').value = "";
                                    document.querySelector('#rclientcpdestpartoesc').value = "";
                                    document.querySelector('#prnclientcpdestpartoesc').value = "";
                                    document.querySelector('#idclientypedestpartoesc').value = "";
                                    document.querySelector('#idclientcontdestpartoesc').value = "";
                                    document.querySelector('#date_cnibdestidpartoesc').value = "";
                                    var verificatdest = document.querySelector('#contactidpartoesc').value;
                                    document.querySelector('#persodestcompagniepartoesc').value = "";

                                    httpInfosdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfos/${verificatdest}`, true);
                                    httpInfosdest.onload = () => {
                                        const infosdest = JSON.parse(httpInfosdest.responseText);
                                        if (infosdest == null) {
                                            document.querySelector('#nomdestidpartoesc').value = "";
                                            document.querySelector('#prenomdestidpartoesc').value = "";
                                            document.querySelector('#compagniepassdestpartoesc').value = "";
                                            document.querySelector('#rclientcpdestpartoesc').value = "";
                                            document.querySelector('#prnclientcpdestpartoesc').value = "";
                                            document.querySelector('#idclientypedestpartoesc').value = "";
                                            document.querySelector('#idclientcontdestpartoesc').value = "";
                                            document.querySelector('#date_cnibdestidpartoesc').value = "";
                                            
                                        } else 
                                        {
                                            if (Object.entries(infosdest).length > 1) {
                                                
                                                if (infosdest.contact_client == verificatdest) {
                                                    document.querySelector('#nomdestidpartoesc').value = `${infosdest.nom_client}`;
                                                    document.querySelector('#prenomdestidpartoesc').value = `${infosdest.prenom_client}`;
                                                    document.querySelector('#compagniepassdestpartoesc').value = `${infosdest.id_client}`;
                                                    document.querySelector('#idclientypedestpartoesc').value = `${infosdest.type_client}`;
                                                    document.querySelector('#rclientcpdestpartoesc').value = `${infosdest.nom_client}`;
                                                    document.querySelector('#prnclientcpdestpartoesc').value = `${infosdest.prenom_client}`;
                                                    document.querySelector('#idclientcontdestpartoesc').value = `${infosdest.contact_client}`;
                                                    document.querySelector('#date_cnibdestidpartoesc').value = `${infosdest.date_delivre}`;
                                                } else {
                                                    document.querySelector('#nomdestidpartoesc').value = "";
                                                    document.querySelector('#prenomdestidpartoesc').value = "";
                                                    document.querySelector('#compagniepassdestpartoesc').value = "";
                                                    document.querySelector('#rclientcpdestpartoesc').value = "";
                                                    document.querySelector('#prnclientcpdestpartoesc').value = "";
                                                    document.querySelector('#idclientypedestpartoesc').value = "";
                                                    document.querySelector('#idclientcontdestpartoesc').value = "";
                                                    document.querySelector('#date_cnibdestidpartoesc').value = "";
                                                }
                                            }
                                        }
                                    };
                                    httpInfosdest.setRequestHeader('Content-Type', 'application/json');
                                    httpInfosdest.send();
                                };
                        }

                        if(personns === 'membre'){

                                document.querySelector('#membrepartcontesc').style.display = 'block';
                                document.querySelector('#idmembrenameesc').style.display = 'block';
                                document.querySelector('#sonnelpartoesc').style.display = 'none';
                                document.querySelector('#idsonnelspartoesc').style.display = 'none';
                                document.querySelector('#idcontpartoesc').style.display = 'none';
                                document.querySelector('#contactidpartoesc').style.display = 'none';
                                document.querySelector('#partcontpartoesc').style.display = 'none';
                                document.querySelector('#idpartespartoesc').style.display = 'none';
                                
                                
                        
                                let httppaternesdestm;
                                    if (window.XMLHttpRequest) {
                                        httppaternesdestm = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) {
                                        httppaternesdestm = new ActiveXObject("Microsoft.XMLHTTP");
                                    }
                                    
                                    httppaternesdestm.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectpartenaire/${personns}`, true);
                                    httppaternesdestm.onload = () => {
                                        const infospartenedestm = JSON.parse(httppaternesdestm.responseText);

                                        if (Object.entries(infospartenedestm).length >= 1) 
                                        {

                                            for (let key in Object.entries(infospartenedestm))
                                            {

                                                let opt = document.createElement('option');
                                                opt.value = `${infospartenedestm[key].id_client}`;
                                                opt.innerHTML = `${infospartenedestm[key].nom_client} ${infospartenedestm[key].prenom_client}`;
                                                document.querySelector('#idmembrenameesc').add(opt);
                                            }
                                                
                                        }
                                        else 
                                        {
                                            document.querySelector('#idmembrenameesc').options.length = 1;
                                        }

                                    };
                                    httppaternesdestm.setRequestHeader('Content-Type', 'application/json');
                                    httppaternesdestm.send();

                                let paternstscdestin2m = document.querySelector('#idmembrenameesc');
                                if (paternstscdestin2m !== null)
                                paternstscdestin2m.onchange = () => {
                                    let httpInfospersdestin2m;
                                        httpInfospersdestin2m = new XMLHttpRequest();
                                    document.querySelector('#persodestcompagniepartoesc').value = '';
                                    document.querySelector('#contactidpartoesc').style.display = 'none';
                                    document.querySelector('#idcontpartoesc').style.display = 'none';
                                    document.querySelector('#contactidpartoesc').value = '';
                                        var ternsdest2m = document.querySelector('#idmembrenameesc').
                                            options[document.querySelector('#idmembrenameesc').options.selectedIndex].value;
                                        httpInfospersdestin2m.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest2m}`, true);
                                    httpInfospersdestin2m.onload = () => {
                                        const infosperdestin2m = JSON.parse(httpInfospersdestin2m.responseText);
                                        
                                        if (Object.entries(infosperdestin2m).length >= 1) {
                                            
                                
                                            document.querySelector('#nomdestidpartoesc').value = `${infosperdestin2m.nom_client}`;
                                            document.querySelector('#prenomdestidpartoesc').value = `${infosperdestin2m.prenom_client}`;
                                            document.querySelector('#compagniepassdestpartoesc').value = `${infosperdestin2m.id_client}`;
                                            document.querySelector('#idclientypedestpartoesc').value = `${infosperdestin2m.type_client}`;
                                            document.querySelector('#rclientcpdestpartoesc').value = `${infosperdestin2m.nom_client}`;
                                            document.querySelector('#prnclientcpdestpartoesc').value = `${infosperdestin2m.prenom_client}`;
                                            document.querySelector('#date_cnibdestidpartoesc').value = `${infosperdestin2m.date_delivre}`;
                                        } 
                                        else
                                        {
                                            document.querySelector('#nomdestidpartoesc').value = "";
                                            document.querySelector('#prenomdestidpartoesc').value = "";
                                            document.querySelector('#compagniepassdestpartoesc').value = "";
                                            document.querySelector('#rclientcpdestpartoesc').value = "";
                                            document.querySelector('#prnclientcpdestpartoesc').value = "";
                                            document.querySelector('#idclientypedestpartoesc').value = "";
                                            document.querySelector('#date_cnibdestidpartoesc').value = "";
                                        }
                                                                    
                                    };
                                    httpInfospersdestin2m.setRequestHeader('Content-Type', 'application/json');
                                    httpInfospersdestin2m.send();
                                };
                     
                        }

                        if(personns === 'partenaire_client' || personns === 'partenaire_simple'){
                            document.querySelector('#membrepartcontesc').style.display = 'none';
                            document.querySelector('#idmembrenameesc').style.display = 'none';
                            document.querySelector('#partcontpartoesc').style.display = 'block';
                            document.querySelector('#idpartespartoesc').style.display = 'block';
                            document.querySelector('#sonnelpartoesc').style.display = 'none';
                            document.querySelector('#idsonnelspartoesc').style.display = 'none';
                            document.querySelector('#contactidpartoesc').style.display = 'none';
                            document.querySelector('#idcontpartoesc').style.display = 'none';
                            document.querySelector('#nomdestidpartoesc').value = '';
                            document.querySelector('#prenomdestidpartoesc').value = '';
                            document.querySelector('#compagniepassdestpartoesc').value = '';
                            document.querySelector('#idclientypedestpartoesc').value = '';
                            document.querySelector('#rclientcpdestpartoesc').value = '';
                            document.querySelector('#prnclientcpdestpartoesc').value = '';
                            document.querySelector('#contactidpartoesc').value = '';
                            document.querySelector('#idclientcontdestpartoesc').value = '';
                            document.querySelector('#date_cnibdestidpartoesc').value = "";
                            let httppaternsdest;
                                if (window.XMLHttpRequest) {
                                    httppaternsdest = new XMLHttpRequest();
                                } else if (window.ActiveXObject) {
                                    httppaternsdest = new ActiveXObject("Microsoft.XMLHTTP");
                                }
                                
                                httppaternsdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectpartenaire/${personns}`, true);
                                httppaternsdest.onload = () => {
                                    const infospartendest = JSON.parse(httppaternsdest.responseText);

                                    if (Object.entries(infospartendest).length >= 1) 
                                    {

                                        for (let key in Object.entries(infospartendest))
                                        {

                                            let opt = document.createElement('option');
                                            opt.value = `${infospartendest[key].id_client}`;
                                            opt.innerHTML = `${infospartendest[key].nom_client} ${infospartendest[key].prenom_client}`;
                                            document.querySelector('#idpartespartoesc').add(opt);
                                        }
                                            
                                    }
                                    else 
                                    {
                                        document.querySelector('#idpartespartoesc').options.length = 1;
                                    }

                                };
                                httppaternsdest.setRequestHeader('Content-Type', 'application/json');
                                httppaternsdest.send();

                                let paternstscdestin = document.querySelector('#idpartespartoesc');
                            if (paternstscdestin !== null)
                            paternstscdestin.onchange = () => {
                                let httpInfospersdestin;
                                    httpInfospersdestin = new XMLHttpRequest();
                                document.querySelector('#persodestcompagniepartoesc').value = '';
                                document.querySelector('#contactidpartoesc').style.display = 'none';
                                document.querySelector('#idcontpartoesc').style.display = 'none';
                                document.querySelector('#contactidpartoesc').value = '';
                                var ternsdest= document.querySelector('#idpartespartoesc').
                                    options[document.querySelector('#idpartespartoesc').options.selectedIndex].value;
                                httpInfospersdestin.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest}`, true);
                                httpInfospersdestin.onload = () => {
                                    const infosperdestin = JSON.parse(httpInfospersdestin.responseText);
                                    
                                    if (Object.entries(infosperdestin).length >= 1) {
                                        
                               
                                        
                                        document.querySelector('#nomdestidpartoesc').value = `${infosperdestin.nom_client}`;
                                        document.querySelector('#prenomdestidpartoesc').value = `${infosperdestin.prenom_client}`;
                                        document.querySelector('#compagniepassdestpartoesc').value = `${infosperdestin.id_client}`;
                                        document.querySelector('#idclientypedestpartoesc').value = `${infosperdestin.type_client}`;
                                        document.querySelector('#rclientcpdestpartoesc').value = `${infosperdestin.nom_client}`;
                                        document.querySelector('#prnclientcpdestpartoesc').value = `${infosperdestin.prenom_client}`;
                                        document.querySelector('#date_cnibdestidpartoesc').value = `${infosperdestin.date_delivre}`;
                                    } 
                                    else
                                    {
                                        document.querySelector('#nomdestidpartoesc').value = "";
                                        document.querySelector('#prenomdestidpartoesc').value = "";
                                        document.querySelector('#compagniepassdestpartoesc').value = "";
                                        document.querySelector('#rclientcpdestpartoesc').value = "";
                                        document.querySelector('#prnclientcpdestpartoesc').value = "";
                                        document.querySelector('#idclientypedestpartoesc').value = "";
                                        document.querySelector('#date_cnibdestidpartoesc').value = "";
                                    }
                                                                
                                };
                                httpInfospersdestin.setRequestHeader('Content-Type', 'application/json');
                                httpInfospersdestin.send();
                            };
 
                        }
                        else
                        {
                            if(personns === 'partenaire_specifique'){

                                document.querySelector('#partcontpartoesc').style.display = 'block';
                                document.querySelector('#idpartespartoesc').style.display = 'block';
                                document.querySelector('#sonnelpartoesc').style.display = 'none';
                                document.querySelector('#idsonnelspartoesc').style.display = 'none';
                                document.querySelector('#idcontpartoesc').style.display = 'none';
                                document.querySelector('#contactidpartoesc').style.display = 'none';
                                document.querySelector('#membrepartcontesc').style.display = 'none';
                                document.querySelector('#idmembrenameesc').style.display = 'none';
                            
                                let httppaternesdest;
                                    if (window.XMLHttpRequest) {
                                        httppaternesdest = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) {
                                        httppaternesdest = new ActiveXObject("Microsoft.XMLHTTP");
                                    }
                                    
                                    httppaternesdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectpartenaire/${personns}`, true);
                                    httppaternesdest.onload = () => {
                                        const infospartenedest = JSON.parse(httppaternesdest.responseText);

                                        if (Object.entries(infospartenedest).length >= 1) 
                                        {

                                            for (let key in Object.entries(infospartenedest))
                                            {

                                                let opt = document.createElement('option');
                                                opt.value = `${infospartenedest[key].id_client}`;
                                                opt.innerHTML = `${infospartenedest[key].nom_client} ${infospartenedest[key].prenom_client}`;
                                                document.querySelector('#idpartespartoesc').add(opt);
                                            }
                                                
                                        }
                                        else 
                                        {
                                            document.querySelector('#idpartespartoesc').options.length = 1;
                                        }

                                    };
                                    httppaternesdest.setRequestHeader('Content-Type', 'application/json');
                                    httppaternesdest.send();

                                let paternstscdestin2 = document.querySelector('#idpartespartoesc');
                                if (paternstscdestin2 !== null)
                                paternstscdestin2.onchange = () => {
                                    let httpInfospersdestin2;
                                        httpInfospersdestin2 = new XMLHttpRequest();
                                    document.querySelector('#persodestcompagniepartoesc').value = '';
                                    document.querySelector('#contactidpartoesc').style.display = 'none';
                                    document.querySelector('#idcontpartoesc').style.display = 'none';
                                    document.querySelector('#contactidpartoesc').value = '';
                                        var ternsdest2 = document.querySelector('#idpartespartoesc').
                                            options[document.querySelector('#idpartespartoesc').options.selectedIndex].value;
                                        httpInfospersdestin2.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest2}`, true);
                                    httpInfospersdestin2.onload = () => {
                                        const infosperdestin2 = JSON.parse(httpInfospersdestin2.responseText);
                                        
                                        if (Object.entries(infosperdestin2).length >= 1) {
                                
                                            document.querySelector('#nomdestidpartoesc').value = `${infosperdestin2.nom_client}`;
                                            document.querySelector('#prenomdestidpartoesc').value = `${infosperdestin2.prenom_client}`;
                                            document.querySelector('#compagniepassdestpartoesc').value = `${infosperdestin2.id_client}`;
                                            document.querySelector('#idclientypedestpartoesc').value = `${infosperdestin2.type_client}`;
                                            document.querySelector('#rclientcpdestpartoesc').value = `${infosperdestin2.nom_client}`;
                                            document.querySelector('#prnclientcpdestpartoesc').value = `${infosperdestin2.prenom_client}`;
                                            document.querySelector('#date_cnibdestidpartoesc').value = `${infosperdestin2.date_delivre}`;
                                        } 
                                        else
                                        {
                                            document.querySelector('#nomdestidpartoesc').value = "";
                                            document.querySelector('#prenomdestidpartoesc').value = "";
                                            document.querySelector('#compagniepassdestpartoesc').value = "";
                                            document.querySelector('#rclientcpdestpartoesc').value = "";
                                            document.querySelector('#prnclientcpdestpartoesc').value = "";
                                            document.querySelector('#idclientypedestpartoesc').value = "";
                                            document.querySelector('#date_cnibdestidpartoesc').value = "";
                                        }
                                                                    
                                    };
                                    httpInfospersdestin2.setRequestHeader('Content-Type', 'application/json');
                                    httpInfospersdestin2.send();
                                };
                   
                            }   
                        }
                    }
        e.onclick = function () {
            let copartoForm = document.querySelector('#copartoFormesc');
            copartoForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/addpartoesc/${e.dataset.cle_compagnie}`);
        }

        var clique = true;

            $('#bottonpartoesc').click(function(event) 
            {
                if(clique) 
                {
                    clique = false;
                    return true;
                }
                else return false;
            })
    })
});