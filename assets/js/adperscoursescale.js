document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adperscoursescale').forEach(function (e) 
    {
       
            let arcourpers = document.querySelector('#arrscourpersoesc');
            if (arcourpers !== null)
            arcourpers.onchange = () => {
                document.querySelector('#date_depheurecourexpersoesc').value = '';
                document.querySelector('#hdepcourpersoesc').options.length = 1;
                document.querySelector('#quartiercourpersoesc').options.length = 1;
                const garedepartcourpers = document.querySelector('#deparcourpersoesc').value;
                const garearrivpers = document.querySelector('#arrscourpersoesc').value;
                var post_arpers = garearrivpers.split('/');
                var seltarpers = post_arpers[0];
                var sougidarrpers = post_arpers[1];
                let httptypequartpers;
                httptypequartpers = new XMLHttpRequest();
                
                httptypequartpers.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${seltarpers}`, true);
                httptypequartpers.onload = () => 
                {
                    const courquapers = JSON.parse(httptypequartpers.responseText);
                    if (courquapers == '') {
                        document.querySelector('#quartiercourpersoesc').options.length = 1;
                    }
                    else{
                        if (Object.entries(courquapers).length >= 1) {
                                        
                            for (let key in Object.entries(courquapers)) {
                                let opt = document.createElement('option');
                                opt.value = `${courquapers[key].code_quart}/${courquapers[key].nom_quartier}`;
                                opt.innerHTML = `${courquapers[key].nom_quartier}/${courquapers[key].code_quart}`;
                                document.querySelector('#quartiercourpersoesc').add(opt);
                            }
                        } else {
                            document.querySelector('#quartiercourpersoesc').options.length = 1;
                        }
                    }
                    

                };
                httptypequartpers.setRequestHeader('Content-Type', 'application/json');
                httptypequartpers.send();
            };
            let dpcourexpers = document.querySelector('#date_depheurecourexpersoesc');
            if (dpcourexpers !== null)
               dpcourexpers.onchange = () => {

                    const dateactuexpers = document.querySelector('#dateactpersoesc').value;
                    const garearriveexpers = document.querySelector('#arrscourpersoesc').value;
                    const progdepartexpers = document.querySelector('#date_depheurecourexpersoesc').value;
                    const garedepartcourexpers = document.querySelector('#deparcourpersoesc').value;
                    document.querySelector('#hdepcourpersoesc').options.length = 1;
                    var post_lhdepexpers = garedepartcourexpers.split('/');
                    var seltdepexpers = post_lhdepexpers[0];
                    var sougidexpers = post_lhdepexpers[1];
                    var post_arrexpers = garearriveexpers.split('/');
                    var seltarrexpers = post_arrexpers[0];
                    var sougidarexpers = post_arrexpers[1];
                    if(progdepartexpers >= dateactuexpers)
                    {
                                                 
                        
                        let Requestitinespers;
                        Requestitinespers = new XMLHttpRequest();
                        Requestitinespers.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheure1/${seltdepexpers}-${seltarrexpers}/${progdepartexpers}`, true);
                            Requestitinespers.onload = () => 
                            {
                                const infoscourrspers = JSON.parse(Requestitinespers.responseText);
                                    document.querySelector('#smsdtcrpersoesc').style.display = 'none';
                                    
                                if(infoscourrspers == ''){

                                }
                                else
                                {
                                    if (Object.entries(infoscourrspers).length >= 1) {
                                   
                                        for (let key in Object.entries(infoscourrspers)) {
                                            let opt = document.createElement('option');
                                            opt.value = `${infoscourrspers[key].id_ligneheure}`;
                                            opt.innerHTML = `${infoscourrspers[key].heure}`;
                                            document.querySelector('#hdepcourpersoesc').add(opt);
                                        }
                                    } else {
                                        document.querySelector('#hdepcourpersoesc').options.length = 1;
                                    }
                                }
                                        
                            };
                            Requestitinespers.setRequestHeader('Content-Type', 'application/json');
                            Requestitinespers.send();
                
                    }
                    else
                    {
                        document.querySelector('#date_depheurecourexpersoesc').style.color = "#FF0000";
                        document.querySelector('#date_depheurecourexpersoesc').style.border = "2px solid #FF0000";
                        document.querySelector('#smsdtcrpersoesc').style.display = 'block';
                        document.querySelector('#erreurSmsdtcrpersoesc').innerHTML = `Date non valide.`;
                    }
                };
               
               let typerspers = document.querySelector('#type_personpersoesc');
                if (typerspers !== null)
                typerspers.onchange = () => {

                    var typersoperspers = document.querySelector('#type_personpersoesc').
                        options[document.querySelector('#type_personpersoesc').options.selectedIndex].value;
                        var typerso1perspers = typersoperspers.split('/');
                        var typerso2perspers = typerso1perspers[0];
                        var typerso3perspers = typerso1perspers[1];

                        document.querySelector('#types_courrierspersoesc').options.length = 1;
                        document.querySelector('#personidpersoesc').options.length = 1;
                        document.querySelector('#exp_nompersoesc').value = "";
                        document.querySelector('#exp_prenompersoesc').value = "";
                        document.querySelector('#cnib_exppersoesc').value = "";
                        document.querySelector('#iddate_cnibpersoesc').value = "";
                        document.querySelector('#lieudelexppersoesc').value = "";
                        document.querySelector('#rclientcpexppersoesc').value = "";
                        document.querySelector('#prnclientcpexppersoesc').value = "";
                        document.querySelector('#cnibcpexppersoesc').value = "";
                        document.querySelector('#date_cnibcpexppersoesc').value = "";
                        document.querySelector('#lieudelivrecpexppersoesc').value = "";
                        document.querySelector('#idclientypeexppersoesc').value = "";
                    
                    
                        let httppersospers;
                            if (window.XMLHttpRequest) {
                                httppersospers = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httppersospers = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                            httppersospers.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectperso/${typerso3perspers}`, true);
                            httppersospers.onload = () => 
                            {

                                const infosperspers = JSON.parse(httppersospers.responseText);

                                if (Object.entries(infosperspers).length >= 1) 
                                {


                                    for (let key in Object.entries(infosperspers))
                                    {

                                        let opt = document.createElement('option');
                                        opt.value = `${infosperspers[key].matricule}`;
                                        opt.innerHTML = `${infosperspers[key].nomprenom_perso}`;
                                        document.querySelector('#personidpersoesc').add(opt);
                                    }
                                       
                                       let httpRequesttesperso2pers;
                                        httpRequesttesperso2pers = new XMLHttpRequest();

                                            
                                        httpRequesttesperso2pers.open('GET', window.location.origin + `${APP_ROOT}/confirmation/fetch_typecourriers`, true);
                                        httpRequesttesperso2pers.onload = () => {
                                            const datapersos2pers = JSON.parse(httpRequesttesperso2pers.responseText);
                                                 if (Object.entries(datapersos2pers).length >= 1) {
                                               
                                                    for (let key in Object.entries(datapersos2pers)) {
                                                        let opt = document.createElement('option');
                                                        opt.value = `${datapersos2pers[key].id_cat}/${datapersos2pers[key].categ}/${datapersos2pers[key].indicatif}`;
                                                        opt.innerHTML = `${datapersos2pers[key].categ}`;
                                                        document.querySelector('#types_courrierspersoesc').add(opt);
                                                    }
                                                } else {
                                                    document.querySelector('#types_courrierspersoesc').options.length = 1;
                                                }
                                        };
                        
                                        httpRequesttesperso2pers.setRequestHeader('Content-Type', 'application/json');
                                        httpRequesttesperso2pers.send(); 
                                }
                                else 
                                {
                                    document.querySelector('#personidpersoesc').options.length = 1;
                                }

                            };
                            httppersospers.setRequestHeader('Content-Type', 'application/json');
                            httppersospers.send();
                    
                };
                
                let tscdpers = document.querySelector('#types_courrierspersoesc');
                if (tscdpers !== null)
                tscdpers.onchange = () => {
                        let httpRequesttespers;
    
                        if (window.XMLHttpRequest) {
                            httpRequesttespers = new XMLHttpRequest();
                        } else if (window.ActiveXObject) {
                            httpRequesttespers = new ActiveXObject("Microsoft.XMLHTTP");
                        }

                        var typersopers = document.querySelector('#type_personpersoesc').
                        options[document.querySelector('#type_personpersoesc').options.selectedIndex].value;
                        var typerso1pers = typersopers.split('/');
                        var typerso2pers = typerso1pers[0];
                        var typerso3pers = typerso1pers[1];

                        
                            document.querySelector('#exp_nompersoesc').value = "";
                            document.querySelector('#exp_prenompersoesc').value = "";
                            document.querySelector('#cnib_exppersoesc').value = "";
                            document.querySelector('#iddate_cnibpersoesc').value = "";
                            document.querySelector('#lieudelexppersoesc').value = "";
                            document.querySelector('#rclientcpexppersoesc').value = "";
                            document.querySelector('#prnclientcpexppersoesc').value = "";
                            document.querySelector('#cnibcpexppersoesc').value = "";
                            document.querySelector('#date_cnibcpexppersoesc').value = "";
                            document.querySelector('#lieudelivrecpexppersoesc').value = "";
                            document.querySelector('#idclientypeexppersoesc').value = "";
                            let httpInfosperspers;
                            if (window.XMLHttpRequest) {
                                httpInfosperspers = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httpInfosperspers = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            var idverifipers = document.querySelector('#personidpersoesc').options[document.querySelector('#personidpersoesc').options.selectedIndex].value;
                
                            httpInfosperspers.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoperso/${idverifipers}`, true);
                            httpInfosperspers.onload = () => {
                                const infosperpers = JSON.parse(httpInfosperspers.responseText);
                                
                                    if (Object.entries(infosperpers).length >= 1) {
                                        
                               
                                        var typepersospers = `${infosperpers.nomprenom_perso}`;
                                        var typer1persospers = typepersospers.split(' ');
                                        var typer2persospers = typer1persospers[0];
                                        var typer3persospers = typer1persospers[1];
                                        var typer4persospers = typer1persospers[2];
                                        if(typer4persospers === undefined){
                                            var typer5persospers = `${typer3persospers}`;
                                        }
                                        else{
                                            var typer5persospers = `${typer3persospers} ${typer4persospers}`;
                                        }
                                        document.querySelector('#exp_nompersoesc').value = `${typer2persospers}`;
                                        document.querySelector('#exp_prenompersoesc').value = `${typer5persospers}`;
                                        document.querySelector('#cnib_exppersoesc').value = `${infosperpers.pieces2}`;
                                        document.querySelector('#iddate_cnibpersoesc').value = `${infosperpers.date_delivre2}`;
                                        document.querySelector('#persocompagniepersoesc').value = `${infosperpers.matricule}`;
                                        document.querySelector('#rclientcpexppersoesc').value = `${typer2persospers}`;
                                        document.querySelector('#prnclientcpexppersoesc').value = `${typer5persospers}`;
                                        document.querySelector('#cnibcpexppersoesc').value = `${infosperpers.pieces2}`;
                                        document.querySelector('#date_cnibcpexppersoesc').value = `${infosperpers.date_delivre2}`;
                                        document.querySelector('#idclientypeexppersoesc').value = 'personnel';
                                        
                                    } 
                                                           
                            };
                            httpInfosperspers.setRequestHeader('Content-Type', 'application/json');
                            httpInfosperspers.send();
                            
                };

                   
                let infopersospers = document.querySelector('#idtypepersoesc');
        
                if (infopersospers !== null) 
                infopersospers.onchange = () => 
                {
                
                    document.querySelector('#contactidpersoesc').style.display = 'none';
                    document.querySelector('#idcontpersoesc').style.display = 'none';
                    document.querySelector('#sonnelpersoesc').style.display = 'none';
                    document.querySelector('#idsonnelspersoesc').style.display = 'none';
                    document.querySelector('#idpartespersoesc').options.length = 1;
                    document.querySelector('#contactidpersoesc').value = '';
                    document.querySelector('#partcontpersoesc').style.display = 'none';
                            
                    var personns = document.querySelector('#idtypepersoesc')
                        .options[document.querySelector('#idtypepersoesc').options.selectedIndex].value;
                        if(personns === 'personnel')
                        {
                    
                            document.querySelector('#sonnelpersoesc').style.display = 'block';
                            document.querySelector('#idsonnelspersoesc').style.display = 'block';
                            document.querySelector('#contactidpersoesc').style.display = 'none';
                            document.querySelector('#idcontpersoesc').style.display = 'none';
                            document.querySelector('#partcontpersoesc').style.display = 'none';
                            document.querySelector('#idpartespersoesc').style.display = 'none';
                            document.querySelector('#sonnelpersomemesc').style.display = 'none';
                            document.querySelector('#idsonnelspersomemesc').style.display = 'none';
                            document.querySelector('#idsonnelspersomemesc').options.length = 1;
                            document.querySelector('#idpartespersoesc').options.length = 1;


                                
                            let httppersosdestpers;
                            if (window.XMLHttpRequest) {
                                httppersosdestpers = new XMLHttpRequest();
                            } else if (window.ActiveXObject) {
                                httppersosdestpers = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            
                            httppersosdestpers.open('GET', window.location.origin + `${APP_ROOT}/confirmation/selectperso/${personns}`, true);
                            httppersosdestpers.onload = () => 
                            {

                                const infospersdestpers = JSON.parse(httppersosdestpers.responseText);

                                if (Object.entries(infospersdestpers).length >= 1) 
                                {


                                    for (let key in Object.entries(infospersdestpers))
                                    {

                                        let opt = document.createElement('option');
                                        opt.value = `${infospersdestpers[key].matricule}`;
                                        opt.innerHTML = `${infospersdestpers[key].nomprenom_perso}`;
                                        document.querySelector('#idsonnelspersoesc').add(opt);
                                    }
                                 
                                }
                                else 
                                {
                                    document.querySelector('#idsonnelspersoesc').options.length = 1;
                                }

                            };
                            httppersosdestpers.setRequestHeader('Content-Type', 'application/json');
                            httppersosdestpers.send();

                            let infopersosdestpers = document.querySelector('#idsonnelspersoesc');
        
                            if (infopersosdestpers !== null) 
                            infopersosdestpers.onchange = () => 
                            {

                            
                                let httpInfospersdest;
                                if (window.XMLHttpRequest) {
                                    httpInfospersdest = new XMLHttpRequest();
                                } else if (window.ActiveXObject) {
                                    httpInfospersdest = new ActiveXObject("Microsoft.XMLHTTP");
                                }

                                document.querySelector('#contactidpersoesc').style.display = 'none';
                                document.querySelector('#idcontpersoesc').style.display = 'none';
                                document.querySelector('#compagniepassdestpersoesc').value = '';
                                var idverifidest = document.querySelector('#idsonnelspersoesc').options[document.querySelector('#idsonnelspersoesc').options.selectedIndex].value;
                    
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
                                        document.querySelector('#nomdestidpersoesc').value = `${typer2persosdest}`;
                                        document.querySelector('#prenomdestidpersoesc').value = `${typer5persosdest}`;
                                        document.querySelector('#persodestcompagniepersoesc').value = `${infosperdest.matricule}`;
                                        document.querySelector('#rclientcpexppersoesc').value = `${typer2persosdest}`;
                                        document.querySelector('#prnclientcpexppersoesc').value = `${typer5persosdest}`;
                                        document.querySelector('#idclientypedestpersoesc').value = 'personnel';
                                        document.querySelector('#idclientcontpersoesc').value = "";
                                    } 
                                    else
                                    {
                                        document.querySelector('#nomdestidpersoesc').value = "";
                                        document.querySelector('#prenomdestidpersoesc').value = "";
                                        document.querySelector('#persodestcompagniepersoesc').value = "";
                                        document.querySelector('#rclientcpdestpersoesc').value = "";
                                        document.querySelector('#prnclientcpdestpersoesc').value = "";
                                        document.querySelector('#idclientypedestpersoesc').value = "";
                                        document.querySelector('#idclientcontpersoesc').value = "";
                                    }
                                                                
                                };
                                httpInfospersdest.setRequestHeader('Content-Type', 'application/json');
                                httpInfospersdest.send();
                            };
                        }
                        else
                        {
                            document.querySelector('#sonnelpersomemesc').style.display = 'none';
                            document.querySelector('#idsonnelspersomemesc').style.display = 'none';
                            document.querySelector('#idsonnelspersomemesc').options.length = 1;
                            document.querySelector('#sonnelpersoesc').style.display = 'none';
                            document.querySelector('#idsonnelspersoesc').style.display = 'none';
                            document.querySelector('#partcontpersoesc').style.display = 'none';
                            document.querySelector('#idpartespersoesc').style.display = 'none';
                            document.querySelector('#idsonnelspersoesc').options.length = 1;
                            document.querySelector('#idpartespersoesc').options.length = 1;
                            document.querySelector('#idcontpersoesc').style.display = 'block';
                            document.querySelector('#contactidpersoesc').style.display = 'block';
                            document.querySelector('#idmatripersoesc').style.display = 'none';
                            document.querySelector('#matri_destpersoesc').style.display = 'none';
                            document.querySelector('#nomdestidpersoesc').value = "";
                            document.querySelector('#prenomdestidpersoesc').value = "";
                            document.querySelector('#compagniepassdestpersoesc').value = "";
                            document.querySelector('#rclientcpdestpersoesc').value = "";
                            document.querySelector('#prnclientcpdestpersoesc').value = "";
                            document.querySelector('#idclientypedestpersoesc').value = "";
                            document.querySelector('#idclientcontpersoesc').value = "";
                            let infdest = document.querySelector('#contactidpersoesc');
                            if (infdest !== null)
                                infdest.onkeyup = () => {
                                    let httpInfosdest;
                                    if (window.XMLHttpRequest) {
                                        httpInfosdest = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) {
                                        httpInfosdest = new ActiveXObject("Microsoft.XMLHTTP");
                                    }

                                    document.querySelector('#nomdestidpersoesc').value = "";
                                    document.querySelector('#prenomdestidpersoesc').value = "";
                                    document.querySelector('#compagniepassdestpersoesc').value = "";
                                    document.querySelector('#rclientcpdestpersoesc').value = "";
                                    document.querySelector('#prnclientcpdestpersoesc').value = "";
                                    document.querySelector('#idclientypedestpersoesc').value = "";
                                    document.querySelector('#idclientcontpersoesc').value = "";
                                    var verificatdest = document.querySelector('#contactidpersoesc').value;
                                    document.querySelector('#persodestcompagniepersoesc').value = "";

                                    httpInfosdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfos/${verificatdest}`, true);
                                    httpInfosdest.onload = () => {
                                        const infosdest = JSON.parse(httpInfosdest.responseText);
                                        if (infosdest == null) {
                                            document.querySelector('#nomdestidpersoesc').value = "";
                                            document.querySelector('#prenomdestidpersoesc').value = "";
                                            document.querySelector('#compagniepasspersoesc').value = "";
                                            document.querySelector('#rclientcpdestpersoesc').value = "";
                                            document.querySelector('#prnclientcpdestpersoesc').value = "";
                                            document.querySelector('#idclientypedestpersoesc').value = "";
                                            document.querySelector('#idclientcontpersoesc').value = "";
                                        } else 
                                        {
                                            if (Object.entries(infosdest).length > 1) {
                                                
                                                if (infosdest.contact_client == verificatdest) {
                                                    document.querySelector('#nomdestidpersoesc').value = `${infosdest.nom_client}`;
                                                    document.querySelector('#prenomdestidpersoesc').value = `${infosdest.prenom_client}`;
                                                    document.querySelector('#compagniepassdestpersoesc').value = `${infosdest.id_client}`;
                                                    document.querySelector('#idclientypedestpersoesc').value = `${infosdest.type_client}`;
                                                    document.querySelector('#rclientcpdestpersoesc').value = `${infosdest.nom_client}`;
                                                    document.querySelector('#prnclientcpdestpersoesc').value = `${infosdest.prenom_client}`;
                                                    document.querySelector('#idclientcontpersoesc').value = `${infosdest.contact_client}`;
                                                    document.querySelector('#date_cnibdestidpersoesc').value = `${infosdest.date_delivre}`;
                                                } else {
                                                    document.querySelector('#idclientcontpersoesc').value = "";
                                                    document.querySelector('#nomdestidpersoesc').value = "";
                                                    document.querySelector('#prenomdestidpersoesc').value = "";
                                                    document.querySelector('#compagniepassdestpersoesc').value = "";
                                                    document.querySelector('#rclientcpdestpersoesc').value = "";
                                                    document.querySelector('#prnclientcpdestpersoesc').value = "";
                                                    document.querySelector('#idclientypedestpersoesc').value = "";
                                                    document.querySelector('#date_cnibdestidpersoesc').value = "";
                                            
                                                }
                                            }
                                        }
                                    };
                                    httpInfosdest.setRequestHeader('Content-Type', 'application/json');
                                    httpInfosdest.send();
                                };
                        }

                        if(personns === 'membre'){

                                document.querySelector('#sonnelpersomemesc').style.display = 'block';
                                document.querySelector('#idsonnelspersomemesc').style.display = 'block';
                                document.querySelector('#idcontpersoesc').style.display = 'none';
                                document.querySelector('#contactidpersoesc').style.display = 'none';
                                document.querySelector('#sonnelpersoesc').style.display = 'none';
                                document.querySelector('#idsonnelspersoesc').style.display = 'none';
                                document.querySelector('#idsonnelspersoesc').options.length = 1;
                                
                        
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
                                                document.querySelector('#idsonnelspersomemesc').add(opt);
                                            }
                                                
                                        }
                                        else 
                                        {
                                            document.querySelector('#idsonnelspersomemesc').options.length = 1;
                                        }

                                    };
                                    httppaternesdestm.setRequestHeader('Content-Type', 'application/json');
                                    httppaternesdestm.send();

                                let paternstscdestin2m = document.querySelector('#idsonnelspersomemesc');
                                if (paternstscdestin2m !== null)
                                paternstscdestin2m.onchange = () => {
                                    let httpInfospersdestin2m;
                                        httpInfospersdestin2m = new XMLHttpRequest();
                                    document.querySelector('#persodestcompagniepersoesc').value = '';
                                    document.querySelector('#contactidpersoesc').style.display = 'none';
                                    document.querySelector('#idcontpersoesc').style.display = 'none';
                                    document.querySelector('#contactidpersoesc').value = '';
                                        var ternsdest2m = document.querySelector('#idsonnelspersomemesc').
                                            options[document.querySelector('#idsonnelspersomemesc').options.selectedIndex].value;
                                        httpInfospersdestin2m.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest2m}`, true);
                                    httpInfospersdestin2m.onload = () => {
                                        const infosperdestin2m = JSON.parse(httpInfospersdestin2m.responseText);
                                        
                                        if (Object.entries(infosperdestin2m).length >= 1) {
                                            
                                            document.querySelector('#nomdestidpersoesc').value = `${infosperdestin2m.nom_client}`;
                                            document.querySelector('#prenomdestidpersoesc').value = `${infosperdestin2m.prenom_client}`;
                                            document.querySelector('#compagniepassdestpersoesc').value = `${infosperdestin2m.id_client}`;
                                            document.querySelector('#idclientypedestpersoesc').value = `${infosperdestin2m.type_client}`;
                                            document.querySelector('#rclientcpdestpersoesc').value = `${infosperdestin2m.nom_client}`;
                                            document.querySelector('#prnclientcpdestpersoesc').value = `${infosperdestin2m.prenom_client}`;
                                            document.querySelector('#date_cnibdestidpersoesc').value = `${infosperdestin2m.date_delivre}`;
                                            
                                        } 
                                        else
                                        {
                                            document.querySelector('#nomdestidpersoesc').value = "";
                                            document.querySelector('#prenomdestidpersoesc').value = "";
                                            document.querySelector('#compagniepassdestpersoesc').value = "";
                                            document.querySelector('#rclientcpdestpersoesc').value = "";
                                            document.querySelector('#prnclientcpdestpersoesc').value = "";
                                            document.querySelector('#idclientypedestpersoesc').value = "";
                                            document.querySelector('#date_cnibdestidpersoesc').value = "";
                                        }
                                                                    
                                    };
                                    httpInfospersdestin2m.setRequestHeader('Content-Type', 'application/json');
                                    httpInfospersdestin2m.send();
                                };
                   
                            
                            }
                        if(personns === 'partenaire_client' || personns === 'partenaire_simple'){
                            document.querySelector('#sonnelpersomemesc').style.display = 'none';
                            document.querySelector('#idsonnelspersomemesc').style.display = 'none';
                            document.querySelector('#idsonnelspersomemesc').options.length = 1;
                            document.querySelector('#partcontpersoesc').style.display = 'block';
                            document.querySelector('#idpartespersoesc').style.display = 'block';
                            document.querySelector('#sonnelpersoesc').style.display = 'none';
                            document.querySelector('#idsonnelspersoesc').style.display = 'none';
                            document.querySelector('#idsonnelspersoesc').options.length = 1;
                            document.querySelector('#contactidpersoesc').style.display = 'none';
                            document.querySelector('#idcontpersoesc').style.display = 'none';
                            document.querySelector('#nomdestidpersoesc').value = '';
                            document.querySelector('#prenomdestidpersoesc').value = '';
                            document.querySelector('#compagniepassdestpersoesc').value = '';
                            document.querySelector('#idclientypedestpersoesc').value = '';
                            document.querySelector('#rclientcpdestpersoesc').value = '';
                            document.querySelector('#prnclientcpdestpersoesc').value = '';
                            document.querySelector('#contactidpersoesc').value = '';
                            document.querySelector('#idclientcontpersoesc').value = '';
                            
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
                                            document.querySelector('#idpartespersoesc').add(opt);
                                        }
                                            
                                    }
                                    else 
                                    {
                                        document.querySelector('#idpartespersoesc').options.length = 1;
                                    }

                                };
                                httppaternsdest.setRequestHeader('Content-Type', 'application/json');
                                httppaternsdest.send();

                                let paternstscdestin = document.querySelector('#idpartespersoesc');
                            if (paternstscdestin !== null)
                            paternstscdestin.onchange = () => {
                                let httpInfospersdestin;
                                    httpInfospersdestin = new XMLHttpRequest();
                                document.querySelector('#persodestcompagniepersoesc').value = '';
                                document.querySelector('#contactidpersoesc').style.display = 'none';
                                document.querySelector('#idcontpersoesc').style.display = 'none';
                                document.querySelector('#contactidpersoesc').value = '';
                                var ternsdest= document.querySelector('#idpartespersoesc').
                                    options[document.querySelector('#idpartespersoesc').options.selectedIndex].value;
                                httpInfospersdestin.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest}`, true);
                                httpInfospersdestin.onload = () => {
                                    const infosperdestin = JSON.parse(httpInfospersdestin.responseText);
                                    
                                    if (Object.entries(infosperdestin).length >= 1) {
                                        
                                        document.querySelector('#nomdestidpersoesc').value = `${infosperdestin.nom_client}`;
                                        document.querySelector('#prenomdestidpersoesc').value = `${infosperdestin.prenom_client}`;
                                        document.querySelector('#compagniepassdestpersoesc').value = `${infosperdestin.id_client}`;
                                        document.querySelector('#idclientypedestpersoesc').value = `${infosperdestin.type_client}`;
                                        document.querySelector('#rclientcpdestpersoesc').value = `${infosperdestin.nom_client}`;
                                        document.querySelector('#prnclientcpdestpersoesc').value = `${infosperdestin.prenom_client}`;
                                        document.querySelector('#date_cnibdestidpersoesc').value = `${infosperdestin.date_delivre}`;
                                    } 
                                    else
                                    {
                                        document.querySelector('#nomdestidpersoesc').value = "";
                                        document.querySelector('#prenomdestidpersoesc').value = "";
                                        document.querySelector('#compagniepassdestpersoesc').value = "";
                                        document.querySelector('#rclientcpdestpersoesc').value = "";
                                        document.querySelector('#prnclientcpdestpersoesc').value = "";
                                        document.querySelector('#idclientypedestpersoesc').value = "";
                                        document.querySelector('#date_cnibdestidpersoesc').value = "";
                                    }
                                                                
                                };
                                httpInfospersdestin.setRequestHeader('Content-Type', 'application/json');
                                httpInfospersdestin.send();
                            };
 
                        }
                        else
                        {


                            if(personns === 'partenaire_specifique'){

                                document.querySelector('#partcontpersoesc').style.display = 'block';
                                document.querySelector('#idpartespersoesc').style.display = 'block';
                                document.querySelector('#sonnelpersoesc').style.display = 'none';
                                document.querySelector('#idsonnelspersoesc').style.display = 'none';
                                document.querySelector('#idcontpersoesc').style.display = 'none';
                                document.querySelector('#contactidpersoesc').style.display = 'none';
                                document.querySelector('#sonnelpersomemesc').style.display = 'none';
                                document.querySelector('#idsonnelspersomemesc').style.display = 'none';
                                document.querySelector('#idsonnelspersomemesc').options.length = 1;
                                document.querySelector('#idsonnelspersoesc').options.length = 1;
                                
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
                                                document.querySelector('#idpartespersoesc').add(opt);
                                            }
                                                
                                        }
                                        else 
                                        {
                                            document.querySelector('#idpartespersoesc').options.length = 1;
                                        }

                                    };
                                    httppaternesdest.setRequestHeader('Content-Type', 'application/json');
                                    httppaternesdest.send();

                                let paternstscdestin2 = document.querySelector('#idpartespersoesc');
                                if (paternstscdestin2 !== null)
                                paternstscdestin2.onchange = () => {
                                    let httpInfospersdestin2;
                                        httpInfospersdestin2 = new XMLHttpRequest();
                                    document.querySelector('#persodestcompagniepersoesc').value = '';
                                    document.querySelector('#contactidpersoesc').style.display = 'none';
                                    document.querySelector('#idcontpersoesc').style.display = 'none';
                                    document.querySelector('#contactidpersoesc').value = '';
                                        var ternsdest2 = document.querySelector('#idpartespersoesc').
                                            options[document.querySelector('#idpartespersoesc').options.selectedIndex].value;
                                        httpInfospersdestin2.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest2}`, true);
                                    httpInfospersdestin2.onload = () => {
                                        const infosperdestin2 = JSON.parse(httpInfospersdestin2.responseText);
                                        
                                        if (Object.entries(infosperdestin2).length >= 1) {
                                            
                                            document.querySelector('#nomdestidpersoesc').value = `${infosperdestin2.nom_client}`;
                                            document.querySelector('#prenomdestidpersoesc').value = `${infosperdestin2.prenom_client}`;
                                            document.querySelector('#compagniepassdestpersoesc').value = `${infosperdestin2.id_client}`;
                                            document.querySelector('#idclientypedestpersoesc').value = `${infosperdestin2.type_client}`;
                                            document.querySelector('#rclientcpdestpersoesc').value = `${infosperdestin2.nom_client}`;
                                            document.querySelector('#prnclientcpdestpersoesc').value = `${infosperdestin2.prenom_client}`;
                                            document.querySelector('#date_cnibdestidpersoesc').value = `${infosperdestin2.date_delivre}`;
                                            
                                        } 
                                        else
                                        {
                                            document.querySelector('#nomdestidpersoesc').value = "";
                                            document.querySelector('#prenomdestidpersoesc').value = "";
                                            document.querySelector('#compagniepassdestpersoesc').value = "";
                                            document.querySelector('#rclientcpdestpersoesc').value = "";
                                            document.querySelector('#prnclientcpdestpersoesc').value = "";
                                            document.querySelector('#idclientypedestpersoesc').value = "";
                                            document.querySelector('#date_cnibdestidpersoesc').value = "";
                                        }
                                                                    
                                    };
                                    httpInfospersdestin2.setRequestHeader('Content-Type', 'application/json');
                                    httpInfospersdestin2.send();
                                };
                   
                            
                            }
                            
                        }
                        
                        
                    }
        e.onclick = function () {
            let copersoForm = document.querySelector('#copersoFormesc');
            copersoForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/addpersoesc/${e.dataset.cle_compagnie}`);
        }

        var clique = true;

            $('#bottonpersoesc').click(function(event) 
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