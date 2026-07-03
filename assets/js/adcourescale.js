document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adcourescale').forEach(function (e) 
    {       

            let arcour = document.querySelector('#arrscouresc');
            if (arcour !== null)
            arcour.onchange = () => {
                document.querySelector('#date_depheurecourexesc').value = '';
                document.querySelector('#hdepcouresc').options.length = 1;
                document.querySelector('#quartiercouresc').options.length = 1;
                document.querySelector('#statenvoiesc').value = 'nonpatterns';
                const garedepartcour = document.querySelector('#deparcouresc').value;
                const garearriv = document.querySelector('#arrscouresc').value;
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
                        document.querySelector('#quartiercouresc').options.length = 1;
                    }
                    else{
                        if (Object.entries(courqua).length >= 1) {
                                        
                            for (let key in Object.entries(courqua)) {
                                let opt = document.createElement('option');
                                opt.value = `${courqua[key].code_quart}/${courqua[key].nom_quartier}`;
                                opt.innerHTML = `${courqua[key].nom_quartier}/${courqua[key].code_quart}`;
                                document.querySelector('#quartiercouresc').add(opt);
                            }
                        } else {
                            document.querySelector('#quartiercouresc').options.length = 1;
                        }
                    }
                    

                };
                httptypequart.setRequestHeader('Content-Type', 'application/json');
                httptypequart.send();
            };
            let dpcourex = document.querySelector('#date_depheurecourexesc');
            if (dpcourex !== null)
               dpcourex.onchange = () => {

                    const dateactuex = document.querySelector('#dateactesc').value;
                    const progdepartex = document.querySelector('#date_depheurecourexesc').value;
                    document.querySelector('#hdepcouresc').options.length = 1;
                    if(progdepartex >= dateactuex)
                    {
                        
                            const garearrive1 = document.querySelector('#arrscouresc').value;
                            const garedepartcour1 = document.querySelector('#deparcouresc').value;
                            const progdepart1 = document.querySelector('#date_depheurecourexesc').value;
                            document.querySelector('#hdepcouresc').options.length = 1;
                            var post_lhdep1 = garedepartcour1.split('/');
                            var seltdep1 = post_lhdep1[0];
                            var sougid1 = post_lhdep1[1];
                            var post_arr1 = garearrive1.split('/');
                            var seltarr1 = post_arr1[0];
                            var sougidar1 = post_arr1[1];
                            
                            let httpRequetesescal;
                            httpRequetesescal = new XMLHttpRequest();
                
                            httpRequetesescal.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheure1/${seltdep1}-${seltarr1}/${progdepart1}`, true);
                            httpRequetesescal.onload = () => {
                                const dataAxeescal = JSON.parse(httpRequetesescal.responseText);
                                
                                    if (dataAxeescal == '') {
                                        
                                        document.querySelector('#smsdtcresc').style.display = 'none';
                                        document.querySelector('#date_depheurecourexesc').style.color = "black";
                                        document.querySelector('#date_depheurecourexesc').style.border = "1px solid";
                                        
                                    } 
                                    else 
                                    {
                                        document.querySelector('#smsdtcresc').style.display = 'none';
                                        document.querySelector('#date_depheurecourexesc').style.color = "black";
                                        document.querySelector('#date_depheurecourexesc').style.border = "1px solid";
                                        if (Object.entries(dataAxeescal).length >= 1) 
                                        {
                                            for (let key in Object.entries(dataAxeescal)) {
                                                    let opt = document.createElement('option');
                                                    opt.value = `${dataAxeescal[key].id_ligneheure}`;
                                                    opt.innerHTML = `${dataAxeescal[key].heure}`;
                                                    document.querySelector('#hdepcouresc').add(opt);
                                                }
                                        } else {
                                            document.querySelector('#hdepcouresc').options.length = 1;
                                        }
                                    }
                                                                               
                            };
                            httpRequetesescal.setRequestHeader('Content-Type', 'application/json');
                            httpRequetesescal.send();
                    }
                    else
                    {
                        document.querySelector('#date_depheurecourexesc').style.color = "#FF0000";
                        document.querySelector('#date_depheurecourexesc').style.border = "2px solid #FF0000";
                        document.querySelector('#smsdtcresc').style.display = 'block';
                        document.querySelector('#erreurSmsdtcresc').innerHTML = `Date non valide.`;
                    }
                };

                
               
               let typers = document.querySelector('#type_personesc');
                if (typers !== null)
                typers.onchange = () => {

                    var typersopers = document.querySelector('#type_personesc').
                        options[document.querySelector('#type_personesc').options.selectedIndex].value;
                        var typerso1pers = typersopers.split('/');
                        var typerso2pers = typerso1pers[0];
                        var typerso3pers = typerso1pers[1];

                        document.querySelector('#types_courriersesc').options.length = 1;
                        document.querySelector('#exp_nomesc').value = "";
                        document.querySelector('#exp_prenomesc').value = "";
                        document.querySelector('#cnib_expesc').value = "";
                        document.querySelector('#iddate_cnibesc').value = "";
                        document.querySelector('#lieudelexpesc').value = "";
                        document.querySelector('#passcompagnieesc').value = "";
                        document.querySelector('#rclientcpexpesc').value = "";
                        document.querySelector('#prnclientcpexpesc').value = "";
                        document.querySelector('#cnibcpexpesc').value = "";
                        document.querySelector('#date_cnibcpexpesc').value = "";
                        document.querySelector('#lieudelivrecpexpesc').value = "";
                        document.querySelector('#idclientypeexpesc').value = "";
                        

                            let httpRequesttespersos1;
                            httpRequesttespersos1 = new XMLHttpRequest();

                                
                            httpRequesttespersos1.open('GET', window.location.origin + `${APP_ROOT}/confirmation/fetch_typecourriers`, true);
                            httpRequesttespersos1.onload = () => {
                                const datapersos1 = JSON.parse(httpRequesttespersos1.responseText);
                                     if (Object.entries(datapersos1).length >= 1) {
                                   
                                        for (let key in Object.entries(datapersos1)) {
                                            let opt = document.createElement('option');
                                            opt.value = `${datapersos1[key].id_cat}/${datapersos1[key].categ}/${datapersos1[key].indicatif}`;
                                            opt.innerHTML = `${datapersos1[key].categ}`;
                                            document.querySelector('#types_courriersesc').add(opt);
                                        }
                                    } else {
                                        document.querySelector('#types_courriersesc').options.length = 1;
                                    }
                            };
                    
                            httpRequesttespersos1.setRequestHeader('Content-Type', 'application/json');
                            httpRequesttespersos1.send();
                };

                
                let inf = document.querySelector('#exp_contactesc');
                if (inf !== null)
                inf.onkeyup = () => {
                    let httpInfos;
                    if (window.XMLHttpRequest) {
                        httpInfos = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfos = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var verificat = document.querySelector('#exp_contactesc').value;
                    
                    httpInfos.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfos/${verificat}`, true);
                    httpInfos.onload = () => {
                        const infos = JSON.parse(httpInfos.responseText);
                        if (infos == null) {
                            document.querySelector('#exp_nomesc').value = "";
                            document.querySelector('#exp_prenomesc').value = "";
                            document.querySelector('#cnib_expesc').value = "";
                            document.querySelector('#iddate_cnibesc').value = "";
                            document.querySelector('#lieudelexpesc').value = "";
                            document.querySelector('#passcompagnieesc').value = "";
                            document.querySelector('#rclientcpexpesc').value = "";
                            document.querySelector('#prnclientcpexpesc').value = "";
                            document.querySelector('#cnibcpexpesc').value = "";
                            document.querySelector('#date_cnibcpexpesc').value = "";
                            document.querySelector('#lieudelivrecpexpesc').value = "";
                            document.querySelector('#idclientypeexpesc').value = "";
                          
                        } else 
                        {
                            if (Object.entries(infos).length > 1) {
                                
                                if (infos.contact_client == verificat) {
                                    document.querySelector('#exp_nomesc').value = `${infos.nom_client}`;
                                    document.querySelector('#exp_prenomesc').value = `${infos.prenom_client}`;
                                    document.querySelector('#cnib_expesc').value = `${infos.num_CNIB}`;
                                    document.querySelector('#iddate_cnibesc').value = `${infos.date_delivre}`;
                                    document.querySelector('#lieudelexpesc').value = `${infos.lieu_delivre}`;
                                    document.querySelector('#passcompagnieesc').value = `${infos.id_client}`;
                                    document.querySelector('#rclientcpexpesc').value = `${infos.nom_client}`;
                                    document.querySelector('#prnclientcpexpesc').value = `${infos.prenom_client}`;
                                    document.querySelector('#cnibcpexpesc').value = `${infos.num_CNIB}`;
                                    document.querySelector('#date_cnibcpexpesc').value = `${infos.date_delivre}`;
                                    document.querySelector('#lieudelivrecpexpesc').value = `${infos.lieu_delivre}`;
                                    document.querySelector('#idclientypeexpesc').value = `${infos.type_client}`;
                          
                                } else {
                                    document.querySelector('#exp_nomesc').value = "";
                                    document.querySelector('#exp_prenomesc').value = "";
                                    document.querySelector('#cnib_expesc').value = "";
                                    document.querySelector('#iddate_cnibesc').value = "";
                                    document.querySelector('#lieudelexpesc').value = "";
                                    document.querySelector('#passcompagnieesc').value = "";
                                    document.querySelector('#rclientcpexpesc').value = "";
                                    document.querySelector('#prnclientcpexpesc').value = "";
                                    document.querySelector('#cnibcpexpesc').value = "";
                                    document.querySelector('#date_cnibcpexpesc').value = "";
                                    document.querySelector('#lieudelivrecpexpesc').value = "";
                                    document.querySelector('#idclientypeexpesc').value = "";
                          
                                }
                            }
                        }
                    };
                    httpInfos.setRequestHeader('Content-Type', 'application/json');
                    httpInfos.send();
                };
                

                let infopersos = document.querySelector('#idtypeesc');
                if (infopersos !== null) 
                infopersos.onchange = () => 
                {
                    document.querySelector('#contactidesc').style.display = 'none';
                    document.querySelector('#idcontesc').style.display = 'none';
                    document.querySelector('#sonnelesc').style.display = 'none';
                    document.querySelector('#idsonnelsesc').style.display = 'none';
                    document.querySelector('#idpartesesc').options.length = 1;
                    document.querySelector('#membrepartoidesc').options.length = 1;
                    document.querySelector('#contactidesc').value = '';
                    document.querySelector('#membrepartoesc').style.display = 'none';
                    document.querySelector('#membrepartoidesc').style.display = 'none';
                           
                    var personns = document.querySelector('#idtypeesc')
                        .options[document.querySelector('#idtypeesc').options.selectedIndex].value;
                        if(personns === 'personnel')
                        {
                    
                            document.querySelector('#sonnelesc').style.display = 'block';
                            document.querySelector('#idsonnelsesc').style.display = 'block';
                            document.querySelector('#contactidesc').style.display = 'none';
                            document.querySelector('#idcontesc').style.display = 'none';
                            document.querySelector('#partcontesc').style.display = 'none';
                            document.querySelector('#idpartesesc').style.display = 'none';
                            document.querySelector('#membrepartoesc').style.display = 'none';
                            document.querySelector('#membrepartoidesc').style.display = 'none';
                                
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
                                        document.querySelector('#idsonnelsesc').add(opt);
                                    }
                                 
                                }
                                else 
                                {
                                    document.querySelector('#idsonnelsesc').options.length = 1;
                                }

                            };
                            httppersosdest.setRequestHeader('Content-Type', 'application/json');
                            httppersosdest.send();

                            let infopersosdest = document.querySelector('#idsonnels');
        
                            if (infopersosdest !== null) 
                            infopersosdest.onchange = () => 
                            {

                            
                                let httpInfospersdest;
                                if (window.XMLHttpRequest) {
                                    httpInfospersdest = new XMLHttpRequest();
                                } else if (window.ActiveXObject) {
                                    httpInfospersdest = new ActiveXObject("Microsoft.XMLHTTP");
                                }

                                document.querySelector('#contactidesc').style.display = 'none';
                                document.querySelector('#idcontesc').style.display = 'none';
                                document.querySelector('#compagniepassdestesc').value = '';
                                var idverifidest = document.querySelector('#idsonnelsesc').options[document.querySelector('#idsonnelsesc').options.selectedIndex].value;
                    
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
                                        else
                                        {
                                            var typer5persosdest = `${typer3persosdest} ${typer4persosdest}`;
                                        }
                                        document.querySelector('#nomdestidesc').value = `${typer2persosdest}`;
                                        document.querySelector('#prenomdestidesc').value = `${typer5persosdest}`;
                                        document.querySelector('#persodestcompagnieesc').value = `${infosperdest.matricule}`;
                                        document.querySelector('#rclientcpexpesc').value = `${typer2persosdest}`;
                                        document.querySelector('#prnclientcpexpesc').value = `${typer5persosdest}`;
                                        document.querySelector('#idclientypedestesc').value = 'personnel';
                                        
                                    } 
                                    else
                                    {
                                        document.querySelector('#nomdestidesc').value = "";
                                        document.querySelector('#prenomdestidesc').value = "";
                                        document.querySelector('#persodestcompagnieesc').value = "";
                                        document.querySelector('#rclientcpdestesc').value = "";
                                        document.querySelector('#prnclientcpdestesc').value = "";
                                        document.querySelector('#idclientypedestesc').value = "";
                                    }
                                                                
                                };
                                httpInfospersdest.setRequestHeader('Content-Type', 'application/json');
                                httpInfospersdest.send();
                            };
                        }
                        else
                        {
                            document.querySelector('#membrepartoesc').style.display = 'none';
                            document.querySelector('#membrepartoidesc').style.display = 'none';
                            document.querySelector('#sonnelesc').style.display = 'none';
                            document.querySelector('#idsonnelsesc').style.display = 'none';
                            document.querySelector('#partcontesc').style.display = 'none';
                            document.querySelector('#idpartesesc').style.display = 'none';
                            document.querySelector('#idcontesc').style.display = 'block';
                            document.querySelector('#contactidesc').style.display = 'block';
                            document.querySelector('#nomdestidesc').value = "";
                            document.querySelector('#prenomdestidesc').value = "";
                            document.querySelector('#compagniepassdestesc').value = "";
                            document.querySelector('#rclientcpdestesc').value = "";
                            document.querySelector('#prnclientcpdestesc').value = "";
                            document.querySelector('#idclientypedestesc').value = "";
                            let infdest = document.querySelector('#contactidesc');
                            if (infdest !== null)
                                infdest.onkeyup = () => {
                                    let httpInfosdest;
                                    if (window.XMLHttpRequest) {
                                        httpInfosdest = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) {
                                        httpInfosdest = new ActiveXObject("Microsoft.XMLHTTP");
                                    }

                                    document.querySelector('#nomdestidesc').value = "";
                                    document.querySelector('#prenomdestidesc').value = "";
                                    document.querySelector('#compagniepassdestesc').value = "";
                                    document.querySelector('#rclientcpdestesc').value = "";
                                    document.querySelector('#prnclientcpdestesc').value = "";
                                    document.querySelector('#idclientypedestesc').value = "";
                                    var verificatdest = document.querySelector('#contactidesc').value;
                                    document.querySelector('#persodestcompagnieesc').value = "";

                                    httpInfosdest.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfos/${verificatdest}`, true);
                                    httpInfosdest.onload = () => {
                                        const infosdest = JSON.parse(httpInfosdest.responseText);
                                        if (infosdest == null) {
                                            document.querySelector('#nomdestidesc').value = "";
                                            document.querySelector('#prenomdestidesc').value = "";
                                            document.querySelector('#compagniepassdestesc').value = "";
                                            document.querySelector('#rclientcpdestesc').value = "";
                                            document.querySelector('#prnclientcpdestesc').value = "";
                                            document.querySelector('#idclientypedestesc').value = "";
                                            document.querySelector('#date_cnibdestidesc').value = "";
                                            
                                        } else 
                                        {
                                            if (Object.entries(infosdest).length > 1) {
                                                
                                                if (infosdest.contact_client == verificatdest) {
                                                    document.querySelector('#nomdestidesc').value = `${infosdest.nom_client}`;
                                                    document.querySelector('#prenomdestidesc').value = `${infosdest.prenom_client}`;
                                                    document.querySelector('#compagniepassdestesc').value = `${infosdest.id_client}`;
                                                    document.querySelector('#idclientypedestesc').value = `${infosdest.type_client}`;
                                                    document.querySelector('#rclientcpdestesc').value = `${infosdest.nom_client}`;
                                                    document.querySelector('#prnclientcpdestesc').value = `${infosdest.prenom_client}`;
                                                    document.querySelector('#date_cnibdestidesc').value = `${infosdest.date_delivre}`;
                                                    
                                                } else {
                                                    document.querySelector('#nomdestidesc').value = "";
                                                    document.querySelector('#prenomdestidesc').value = "";
                                                    document.querySelector('#compagniepassdestesc').value = "";
                                                    document.querySelector('#rclientcpdestesc').value = "";
                                                    document.querySelector('#prnclientcpdestesc').value = "";
                                                    document.querySelector('#idclientypedestesc').value = "";
                                                    document.querySelector('#date_cnibdestidesc').value = "";
                                                    
                                                }
                                            }
                                        }
                                    };
                                    httpInfosdest.setRequestHeader('Content-Type', 'application/json');
                                    httpInfosdest.send();
                                };
                        }
                        if(personns === 'membre'){

                                document.querySelector('#membrepartoesc').style.display = 'block';
                                document.querySelector('#membrepartoidesc').style.display = 'block';
                                document.querySelector('#sonnelesc').style.display = 'none';
                                document.querySelector('#idsonnelsesc').style.display = 'none';
                                document.querySelector('#idcontesc').style.display = 'none';
                                document.querySelector('#contactidesc').style.display = 'none';
                                document.querySelector('#partcontesc').style.display = 'none';
                                document.querySelector('#idpartesesc').style.display = 'none';
                                
                                
                        
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
                                                document.querySelector('#membrepartoidesc').add(opt);
                                            }
                                                
                                        }
                                        else 
                                        {
                                            document.querySelector('#membrepartoidesc').options.length = 1;
                                        }

                                    };
                                    httppaternesdestm.setRequestHeader('Content-Type', 'application/json');
                                    httppaternesdestm.send();

                                let paternstscdestin2m = document.querySelector('#membrepartoidesc');
                                if (paternstscdestin2m !== null)
                                paternstscdestin2m.onchange = () => {
                                    let httpInfospersdestin2m;
                                        httpInfospersdestin2m = new XMLHttpRequest();
                                    document.querySelector('#persodestcompagnieesc').value = '';
                                    document.querySelector('#contactidesc').style.display = 'none';
                                    document.querySelector('#idcontesc').style.display = 'none';
                                    document.querySelector('#contactidesc').value = '';
                                        var ternsdest2m = document.querySelector('#membrepartoidesc').
                                            options[document.querySelector('#membrepartoidesc').options.selectedIndex].value;
                                        httpInfospersdestin2m.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest2m}`, true);
                                    httpInfospersdestin2m.onload = () => {
                                        const infosperdestin2m = JSON.parse(httpInfospersdestin2m.responseText);
                                        
                                        if (Object.entries(infosperdestin2m).length >= 1) {
                                            
                                   
                                            
                                            document.querySelector('#nomdestidesc').value = `${infosperdestin2m.nom_client}`;
                                            document.querySelector('#prenomdestidesc').value = `${infosperdestin2m.prenom_client}`;
                                            document.querySelector('#compagniepassdestesc').value = `${infosperdestin2m.id_client}`;
                                            document.querySelector('#idclientypedestesc').value = `${infosperdestin2m.type_client}`;
                                            document.querySelector('#rclientcpdestesc').value = `${infosperdestin2m.nom_client}`;
                                            document.querySelector('#prnclientcpdestesc').value = `${infosperdestin2m.prenom_client}`;
                                            document.querySelector('#date_cnibdestidesc').value = `${infosperdestin2m.date_delivre}`;
                                        } 
                                        else
                                        {
                                            document.querySelector('#nomdestidesc').value = "";
                                            document.querySelector('#prenomdestidesc').value = "";
                                            document.querySelector('#compagniepassdestesc').value = "";
                                            document.querySelector('#rclientcpdestesc').value = "";
                                            document.querySelector('#prnclientcpdestesc').value = "";
                                            document.querySelector('#idclientypedestesc').value = "";
                                            document.querySelector('#date_cnibdestidesc').value = "";
                                        }
                                                                    
                                    };
                                    httpInfospersdestin2m.setRequestHeader('Content-Type', 'application/json');
                                    httpInfospersdestin2m.send();
                                };
                   
                            
                        }

                        if(personns === 'partenaire_client' || personns === 'partenaire_simple'){
                            document.querySelector('#partcontesc').style.display = 'block';
                            document.querySelector('#idpartesesc').style.display = 'block';
                            document.querySelector('#sonnelesc').style.display = 'none';
                            document.querySelector('#idsonnelsesc').style.display = 'none';
                            document.querySelector('#contactidesc').style.display = 'none';
                            document.querySelector('#idcontesc').style.display = 'none';
                            document.querySelector('#nomdestidesc').value = '';
                            document.querySelector('#prenomdestidesc').value = '';
                            document.querySelector('#compagniepassdestesc').value = '';
                            document.querySelector('#idclientypedestesc').value = '';
                            document.querySelector('#rclientcpdestesc').value = '';
                            document.querySelector('#prnclientcpdestesc').value = '';
                            document.querySelector('#contactidesc').value = '';
                            document.querySelector('#membrepartoesc').style.display = 'none';
                            document.querySelector('#membrepartoidesc').style.display = 'none';
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
                                            document.querySelector('#idpartesesc').add(opt);
                                        }
                                            
                                    }
                                    else 
                                    {
                                        document.querySelector('#idpartesesc').options.length = 1;
                                    }

                                };
                                httppaternsdest.setRequestHeader('Content-Type', 'application/json');
                                httppaternsdest.send();

                                let paternstscdestin = document.querySelector('#idpartesesc');
                            if (paternstscdestin !== null)
                            paternstscdestin.onchange = () => {
                                let httpInfospersdestin;
                                    httpInfospersdestin = new XMLHttpRequest();
                                document.querySelector('#persodestcompagnieesc').value = '';
                                document.querySelector('#contactidesc').style.display = 'none';
                                document.querySelector('#idcontesc').style.display = 'none';
                                document.querySelector('#contactidesc').value = '';
                                var ternsdest= document.querySelector('#idpartesesc').
                                    options[document.querySelector('#idpartesesc').options.selectedIndex].value;
                                httpInfospersdestin.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest}`, true);
                                httpInfospersdestin.onload = () => {
                                    const infosperdestin = JSON.parse(httpInfospersdestin.responseText);
                                    
                                    if (Object.entries(infosperdestin).length >= 1) {
                                        
                               
                                        
                                        document.querySelector('#nomdestidesc').value = `${infosperdestin.nom_client}`;
                                        document.querySelector('#prenomdestidesc').value = `${infosperdestin.prenom_client}`;
                                        document.querySelector('#compagniepassdestesc').value = `${infosperdestin.id_client}`;
                                        document.querySelector('#idclientypedestesc').value = `${infosperdestin.type_client}`;
                                        document.querySelector('#rclientcpdestesc').value = `${infosperdestin.nom_client}`;
                                        document.querySelector('#prnclientcpdestesc').value = `${infosperdestin.prenom_client}`;
                                        document.querySelector('#date_cnibdestidesc').value = `${infosperdestin.date_delivre}`;
                                        
                                    } 
                                    else
                                    {
                                        document.querySelector('#nomdestidesc').value = "";
                                        document.querySelector('#prenomdestidesc').value = "";
                                        document.querySelector('#compagniepassdestesc').value = "";
                                        document.querySelector('#rclientcpdestesc').value = "";
                                        document.querySelector('#prnclientcpdestesc').value = "";
                                        document.querySelector('#idclientypedestesc').value = "";
                                        document.querySelector('#date_cnibdestidesc').value = "";
                                    }
                                                                
                                };
                                httpInfospersdestin.setRequestHeader('Content-Type', 'application/json');
                                httpInfospersdestin.send();
                            };
 
                        }
                        else
                        {


                            if(personns === 'partenaire_specifique'){

                                document.querySelector('#partcontesc').style.display = 'block';
                                document.querySelector('#idpartesesc').style.display = 'block';
                                document.querySelector('#sonnelesc').style.display = 'none';
                                document.querySelector('#idsonnelsesc').style.display = 'none';
                                document.querySelector('#idcontesc').style.display = 'none';
                                document.querySelector('#contactidesc').style.display = 'none';
                                document.querySelector('#membrepartoesc').style.display = 'none';
                                document.querySelector('#membrepartoidesc').style.display = 'none';
                                
                        
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
                                                document.querySelector('#idpartesesc').add(opt);
                                            }
                                                
                                        }
                                        else 
                                        {
                                            document.querySelector('#idpartesesc').options.length = 1;
                                        }

                                    };
                                    httppaternesdest.setRequestHeader('Content-Type', 'application/json');
                                    httppaternesdest.send();

                                let paternstscdestin2 = document.querySelector('#idpartesesc');
                                if (paternstscdestin2 !== null)
                                paternstscdestin2.onchange = () => {
                                    let httpInfospersdestin2;
                                        httpInfospersdestin2 = new XMLHttpRequest();
                                    document.querySelector('#persodestcompagnieesc').value = '';
                                    document.querySelector('#contactidesc').style.display = 'none';
                                    document.querySelector('#idcontesc').style.display = 'none';
                                    document.querySelector('#contactidesc').value = '';
                                        var ternsdest2 = document.querySelector('#idpartesesc').
                                            options[document.querySelector('#idpartesesc').options.selectedIndex].value;
                                        httpInfospersdestin2.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifinfoclients/${ternsdest2}`, true);
                                    httpInfospersdestin2.onload = () => {
                                        const infosperdestin2 = JSON.parse(httpInfospersdestin2.responseText);
                                        
                                        if (Object.entries(infosperdestin2).length >= 1) {
                                            
                                   
                                            
                                            document.querySelector('#nomdestidesc').value = `${infosperdestin2.nom_client}`;
                                            document.querySelector('#prenomdestidesc').value = `${infosperdestin2.prenom_client}`;
                                            document.querySelector('#compagniepassdestesc').value = `${infosperdestin2.id_client}`;
                                            document.querySelector('#idclientypedestesc').value = `${infosperdestin2.type_client}`;
                                            document.querySelector('#rclientcpdestesc').value = `${infosperdestin2.nom_client}`;
                                            document.querySelector('#prnclientcpdestesc').value = `${infosperdestin2.prenom_client}`;
                                            document.querySelector('#date_cnibdestidesc').value = `${httpInfospersdestin2.date_delivre}`;
                                        } 
                                        else
                                        {
                                            document.querySelector('#nomdestidesc').value = "";
                                            document.querySelector('#prenomdestidesc').value = "";
                                            document.querySelector('#compagniepassdestesc').value = "";
                                            document.querySelector('#rclientcpdestesc').value = "";
                                            document.querySelector('#prnclientcpdestesc').value = "";
                                            document.querySelector('#idclientypedestesc').value = "";
                                            document.querySelector('#date_cnibdestidesc').value = "";
                                        }
                                                                    
                                    };
                                    httpInfospersdestin2.setRequestHeader('Content-Type', 'application/json');
                                    httpInfospersdestin2.send();
                                };
                   
                            
                            }
                            
                        }
                        
                }
        e.onclick = function () {
            let coordForm = document.querySelector('#coordFormesc');
            coordForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/addordesc/${e.dataset.cle_compagnie}`);
        }

            var clique = true;

            $('#bottonesc').click(function(event) 
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