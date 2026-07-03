document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addventemobile').forEach(function (e) 
    {
        document.querySelector('h3#mobTitle').innerHTML = `VENTE DE TICKET`;

            let dpgar= document.querySelector('#depargaremob');
            if (dpgar !== null)
            dpgar.onchange = () => {
                document.querySelector('#prix_axemob').value = '';
                document.querySelector('#date_depheuremob').value = '';
                document.querySelector('#arrsgaremob').value = '';
                document.querySelector('#hdepartmob').options.length = 1;
                document.querySelector('#quartiermob').options.length = 1;
                document.querySelector('#psiegesmob').options.length = 1;
                document.querySelector('#programmob').value = '';
                document.querySelector('#nomitinmob').value = '';
                document.querySelector('#typesmob').value = '';
                document.querySelector('#tarifattribmob').value = '';
                  
            };
            let armob= document.querySelector('#arrsgaremob');
            if (armob !== null)
            armob.onchange = () => {
                document.querySelector('#prix_axemob').value = '';
                document.querySelector('#date_depheuremob').value = '';
                document.querySelector('#hdepartmob').options.length = 1;
                document.querySelector('#quartiermob').options.length = 1;
                document.querySelector('#psiegesmob').options.length = 1;
                  document.querySelector('#programmob').value = '';
                  document.querySelector('#tarifattribmob').value = '';
                  document.querySelector('#nomitinmob').value = '';
                document.querySelector('#typesmob').value = '';
                  
                    const typgaremob = document.querySelector('#arrsgaremob')
                    .options[document.querySelector('#arrsgaremob').options.selectedIndex].value;
                    const prosmob = document.querySelector('#programmob').value;
                    let httptypequartmob;
                    httptypequartmob = new XMLHttpRequest();
                    
                    httptypequartmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${typgaremob}`, true);
                    httptypequartmob.onload = () => 
                    {
                        const donquamob = JSON.parse(httptypequartmob.responseText);
                        if (donquamob == '') {
                            document.querySelector('#quartiermob').options.length = 1;
                        }
                        else{
                            if (Object.entries(donquamob).length >= 1) {
                                            
                                for (let key in Object.entries(donquamob)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${donquamob[key].nom_quartier}`;
                                    opt.innerHTML = `${donquamob[key].nom_quartier}`;
                                    document.querySelector('#quartiermob').add(opt);
                                }
                            } else {
                                document.querySelector('#quartiermob').options.length = 1;
                            }
                        }
                        

                    };
                    httptypequartmob.setRequestHeader('Content-Type', 'application/json');
                    httptypequartmob.send();
            };
            
            let damob = document.querySelector('#date_depheuremob');
            if (damob !== null){
                damob.onchange = () => 
                {
                    
                    document.querySelector('#hdepartmob').options.length = 1;
                    document.querySelector('#psiegesmob').options.length = 1;
                    
                    let httpRequetesmob;
                    
                    if (window.XMLHttpRequest) {
                        httpRequetesmob = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpRequetesmob = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                        var depamob = document.querySelector('#depargaremob').value;
                        var arrmob = document.querySelector('#arrsgaremob').value;
                        var datedepartmob = document.querySelector('#date_depheuremob').value;
                        var dateactumob = document.querySelector('#actumob').value;
                                         
                        var post_lhdepmob = depamob.split('/');
                        var seltdepmob = post_lhdepmob[0];
                        var sougidmob = post_lhdepmob[1];
                        if(datedepartmob >= dateactumob)
                        {
                            
                            httpRequetesmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheure1/${seltdepmob}-${arrmob}/${datedepartmob}`, true);
                            httpRequetesmob.onload = () => {
                                const dataAxemob = JSON.parse(httpRequetesmob.responseText);
                                
                                    if (dataAxemob == '') {
                                        
                                        document.querySelector('#smsdtmob').style.display = 'none';
                                        document.querySelector('#date_depheuremob').style.color = "black";
                                        document.querySelector('#date_depheuremob').style.border = "1px solid";
                                        
                                    } 
                                    else 
                                    {       
                                        
                                        document.querySelector('#smsdtmob').style.display = 'none';
                                        document.querySelector('#date_depheuremob').style.color = "black";
                                        document.querySelector('#date_depheuremob').style.border = "1px solid";
                                        if (Object.entries(dataAxemob).length >= 1) 
                                        {
                                                
                                            
    
                                            for (let key in Object.entries(dataAxemob)) {
                                                    let opt = document.createElement('option');
                                                    opt.value = `${dataAxemob[key].id_ligneheure}/${dataAxemob[key].heure}`;
                                                    opt.innerHTML = `${dataAxemob[key].heure}`;
                                                    document.querySelector('#hdepartmob').add(opt);
                                                }
                                        } else {
                                            document.querySelector('#hdepartmob').options.length = 1;
                                        }
                                    }

                                        let hrdepartmob = document.querySelector('#hdepartmob');
                                        if (hrdepartmob !== null) {
                                            hrdepartmob.onchange = () => 
                                            {
                                                document.querySelector('#psiegesmob').options.length = 1;
                                                const httpRequestmob = new XMLHttpRequest();
                                                const selemob = document.querySelector('#hdepartmob')
                                                    .options[document.querySelector('#hdepartmob').options.selectedIndex].value;

                                                    var post_lhmob = selemob.split('/');
                                                    var selmob = post_lhmob[0];
                                                    var lhselmob = post_lhmob[1];

                                                    const dpt_datemob = document.querySelector('#date_depheuremob').value;
                                                    
                                                httpRequestmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifprog/${seltdepmob}-${arrmob}/${dpt_datemob}/${selmob}`, true);
                                                httpRequestmob.onload = () => 
                                                {
                                                    const donmob = JSON.parse(httpRequestmob.responseText);
                                                        console.debug(`${typeof donmob} - ${donmob.attributes}`, console.memory);
                                                        if (donmob == '') 
                                                        {
                            
                                                                    /*let opt = document.createElement('option');
                                                                    opt.value = 1;
                                                                    opt.innerHTML = 1;
                                                                    document.querySelector('#psiegesmob').add(opt);
                                                            
                                                                    departpsiegesmob = document.querySelector('#psiegesmob');
                                                                    if (departpsiegesmob !== null) {
                                                                        departpsiegesmob.onchange = () => 
                                                                        {
                                                                            let httpProgmob;
                                                                            httpProgmob = new XMLHttpRequest();
                                                                            httpProgmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/creedepartmob/${seltdepmob}/${dpt_datemob}/${selmob}/${lhselmob}`, true);
                                                                            httpProgmob.onload = () => 
                                                                            {
                                                                                const donsmob = JSON.parse(httpProgmob.responseText);
                                                                                console.debug(`${typeof donsmob} - ${donsmob.attributes}`, console.memory);
                                                                                if (Object.entries(donsmob).length >= 1) {
                                                                                    for (let key in Object.entries(donsmob)) {
                                                                                        document.querySelector('#programmob').value = `${donsmob[key].code_progr}`;
                                                                                        document.querySelector('#tarifattribmob').value = `${donsmob[key].typetarif}`;
                                                                                        document.querySelector('#catemob').value = `${donsmob[key].categorie}`;
                                                                                        document.querySelector('#deplignemob').value = `${donsmob[key].gareidentif}`;
                                                                                        document.querySelector('#lignmob').value = `${donsmob[key].ident_ligne}`;
                                                                                        document.querySelector('#nomitinmob').value = `${donsmob[key].nom_ligne}`;
                                                                                        document.querySelector('#prix_axemob').value = `${donsmob[key].prix}`;
                                                                                    }
                                                                                        let httpSiegemob;
                                                                                        httpSiegemob = new XMLHttpRequest();
                                                                                        const sigmob = document.querySelector('#psiegesmob')
                                                                                        .options[document.querySelector('#psiegesmob').options.selectedIndex].value;
                                                                                        const promob = document.querySelector('#programmob').value;
                                                                                        httpSiegemob.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${promob}/${sigmob}`, true);
                                                                                        httpSiegemob.onload = () => 
                                                                                        {
                                                                                            const donsgmob = JSON.parse(httpSiegemob.responseText);
                                                                                            console.debug(`${typeof donsgmob} - ${donsgmob.attributes}`, console.memory);
                                                                                            if(donsgmob == '')
                                                                                            {
                                                                                                let httpSiegmob;
                                                                                                httpSiegmob = new XMLHttpRequest();
                    
                                                                                                httpSiegmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${promob}/${sigmob}`, true);
                                                                                                httpSiegmob.onload = () => 
                                                                                                {
                                                                                                    const donsg2mob = JSON.parse(httpSiegmob.responseText);
                                                                                                    document.querySelector('#messmob').style.display = 'none';
                                                                                                    if (Object.entries(donsg2mob).length >= 1)
                                                                                                        {
                                                                                                            for (let key in Object.entries(donsg2mob)) {
                                                                                                                document.querySelector('#idtampomob').value = `${donsg2mob[key].idtamp}`;                    
                                                                                                                document.querySelector('#siegselectmob').value = `${donsg2mob[key].numsieg}`;
                                                                                                            }
                                                                                                        }
                                                                                                };
                                                                                                httpSiegmob.setRequestHeader('Content-Type', 'application/json');
                                                                                                httpSiegmob.send();
                                                                                            }
                                                                                            else 
                                                                                            {
                                                                                                document.querySelector('#psiegesmob').value = ''; 
                                                                                                if (Object.entries(donsgmob).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(donsgmob)) 
                                                                                                    {
                                                                                                        document.querySelector('#idtampomob').value = `${donsgmob[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselectmob').value = `${donsgmob[key].numsieg}`;
                                                                                                    }
        
                                                                                                }
                                                                                                document.querySelector('#messmob').style.display = 'block';
                                                                                                document.querySelector('#erreurMessmob').innerHTML = `Siege déjà utilisé.`;                   
                                                                                            }
                                                                                        };
                                                                                        httpSiegemob.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegemob.send();
                    
                                                                                   
                                                                                }
                                                                            };
                                                                            httpProgmob.setRequestHeader('Content-Type', 'application/json');
                                                                            httpProgmob.send();
        
                                                                            
                                                                        
                                                                        };
        
                                                                        
                                                                    }*/
                                                            
                                                        } 
                                                        else 
                                                        {       
                                                            if (Object.entries(donmob).length >= 1) {
                                                                for (let key in Object.entries(donmob)) {
                                                                    document.querySelector('#programmob').value = `${donmob[key].code_progr}`;
                                                                    document.querySelector('#tarifattribmob').value = `${donmob[key].typetarif}`;
                                                                    document.querySelector('#dateprmob').value = `${donmob[key].date_progr}`;
                                                                    document.querySelector('#deplignemob').value = `${donmob[key].gareidentif}`;
                                                                    document.querySelector('#inter1mob').value = `${donmob[key].intervalle1}`;
                                                                    document.querySelector('#inter2mob').value = `${donmob[key].intervalle2}`;
                                                                    document.querySelector('#lignmob').value = `${donmob[key].ident_ligne}`;
                                                                    document.querySelector('#hermob').value = `${donmob[key].heure}`;
                                                                    document.querySelector('#catemob').value = `${donmob[key].categori}`;
                                                                    document.querySelector('#nomitinmob').value = `${donmob[key].nom_ligne}`;

                                                                }
                                                            } 
                                                            
                                                            var tfbsmob = document.querySelector('#tarifattribmob').value;
                                                            const httpPrixmob = new XMLHttpRequest();
                                                            httpPrixmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifpriprg/${selmob}/${tfbsmob}`, true);
                                                            httpPrixmob.onload = () => 
                                                            {

                                                                const donprixmob = JSON.parse(httpPrixmob.responseText);
                                                                console.debug(`${typeof donprixmob}-${donprixmob.attributes}`, console.memory);
                                                                if (Object.entries(donprixmob).length >= 1) {
                                                                    for (let key in Object.entries(donprixmob)) 
                                                                    {
                                                                        document.querySelector('#prix_axemob').value = `${donprixmob[key].prix}`;
            
                                                                    }
                                                                }
                                                            };
                                                            httpPrixmob.setRequestHeader('Content-Type', 'application/json');
                                                            httpPrixmob.send();
                                                            
                                                            const httpRequettemob = new XMLHttpRequest();
                                                            const cdprogmob = document.querySelector('#programmob').value;
                                                            const dbmob = document.querySelector('#inter1mob').value;
                                                            const fnmob = document.querySelector('#inter2mob').value;
                                                            
                                                            var lgmob = document.querySelector('#nomitinmob').value;
                                                            const timmob = document.querySelector('#hermob').value;
                                                                httpRequettemob.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${cdprogmob}/${dpt_datemob}/${lgmob}/${timmob}/${dbmob}/${fnmob}`, true);
                                                            httpRequettemob.onload = () => {
                                                                const dattamob = JSON.parse(httpRequettemob.responseText);
                                                                if (Object.entries(dattamob).length >= 1) {
                                                                    for (let key in Object.entries(dattamob)) {
                                                                        
                                                                        let opt = document.createElement('option');
                                                                        opt.value = `${dattamob[key].siege_num}`;
                                                                        opt.innerHTML = `${dattamob[key].siege_num}`;
                                                                        document.querySelector('#psiegesmob').add(opt);
                                                                        
                                                                    }
                                                                    
                                                                } else {
                                                                    document.querySelector('#psiegesmob').options.length = 1;
                                                                }
                                                            };
                                                            httpRequettemob.setRequestHeader('Content-Type', 'application/json');
                                                            httpRequettemob.send();
                                                        }  
                                                        
                                                    };
                                                    httpRequestmob.setRequestHeader('Content-Type', 'application/json');
                                                    httpRequestmob.send();
                                                     
                                                };
                                                
                                        
                                            }
                                };
                                httpRequetesmob.setRequestHeader('Content-Type', 'application/json');
                                httpRequetesmob.send();
                        }
                        else
                        {
                            document.querySelector('#date_depheuremob').style.color = "#FF0000";
                            document.querySelector('#date_depheuremob').style.border = "2px solid #FF0000";
                            document.querySelector('#smsdtmob').style.display = 'block';
                            document.querySelector('#erreurSmsdtmob').innerHTML = `Date non valide.`;
                        }
                    

                };
                
            }
            let progsiegesmob = document.querySelector('#psiegesmob');
            if (progsiegesmob !== null) {
                progsiegesmob.onchange = () => 
                {
                    let httpSiegesmob;
                    httpSiegesmob = new XMLHttpRequest();
                    const sigsmob = document.querySelector('#psiegesmob')
                    .options[document.querySelector('#psiegesmob').options.selectedIndex].value;
                    const prosmob = document.querySelector('#programmob').value;

                    httpSiegesmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${prosmob}/${sigsmob}`, true);
                    httpSiegesmob.onload = () => 
                    {
                        const donsgemob = JSON.parse(httpSiegesmob.responseText);
                        console.debug(`${typeof donsgemob} - ${donsgemob.attributes}`, console.memory);
                        if(donsgemob == '')
                        {
                            let httpSiegsmob;
                            httpSiegsmob = new XMLHttpRequest();

                            httpSiegsmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${prosmob}/${sigsmob}`, true);
                            httpSiegsmob.onload = () => 
                            {
                                const dongmob = JSON.parse(httpSiegsmob.responseText);
                                document.querySelector('#messmob').style.display = 'none';
                                if (Object.entries(dongmob).length >= 1)
                                    {
                                        for (let key in Object.entries(dongmob)) {
                                            document.querySelector('#idtampomob').value = `${dongmob[key].idtamp}`;                    
                                            document.querySelector('#siegselectmob').value = `${dongmob[key].numsieg}`;
                                        }
                                    }
                            };
                            httpSiegsmob.setRequestHeader('Content-Type', 'application/json');
                            httpSiegsmob.send();
                        }
                        else {
                            document.querySelector('#psiegesmob').value = '';     
                            if (Object.entries(donsgemob).length >= 1)
                            {
                                for (let key in Object.entries(donsgemob)) {
                                    document.querySelector('#idtampomob').value = `${donsgemob[key].idtamp}`;                    
                                    document.querySelector('#siegselectmob').value = `${donsgemob[key].numsieg}`;
                                }

                            }
                            document.querySelector('#messmob').style.display = 'block';
                            document.querySelector('#erreurMessmob').innerHTML = `Siege déjà utilisé.`;                                                                   }
                    };
                    httpSiegesmob.setRequestHeader('Content-Type', 'application/json');
                    httpSiegesmob.send();

                
                };
            }
           
            
        //recherche d'information du client depart principal
        let infmob = document.querySelector('#rnclient_contactmob');
        if (infmob !== null)
            infmob.onkeyup = () => {
                let httpInfosmob;
                if (window.XMLHttpRequest) {
                    httpInfosmob = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfosmob = new ActiveXObject("Microsoft.XMLHTTP");
                }
                var verificatmob = document.querySelector('#rnclient_contactmob').value;
                
                httpInfosmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifinfos/${verificatmob}`, true);
                httpInfosmob.onload = () => {
                    const infosmob = JSON.parse(httpInfosmob.responseText);
                    if (infosmob == null) {
                        document.querySelector('#rclientmob').value = "";
                        document.querySelector('#prnclientmob').value = "";
                        document.querySelector('#pascompagniemob').value = "";
                        document.querySelector('#typesmob').value = "";
                    } else {
                        if (Object.entries(infosmob).length > 1) {
                            
                            if (infosmob.contact_client == verificatmob) {
                                document.querySelector('#rclientmob').value = `${infosmob.nom_client}`;
                                document.querySelector('#prnclientmob').value = `${infosmob.prenom_client}`;
                                document.querySelector('#pascompagniemob').value = `${infosmob.id_client}`;
                                document.querySelector('#rclientcpmob').value = `${infosmob.nom_client}`;
                                document.querySelector('#prnclientcpmob').value = `${infosmob.prenom_client}`;
                                document.querySelector('#typesmob').value = `${infosmob.type_client}`;
                            } else {
                                document.querySelector('#rclientmob').value = "";
                                document.querySelector('#prnclientmob').value = "";
                                document.querySelector('#pascompagniemob').value = "";
                                document.querySelector('#typesmob').value = "";
                            }
                        }
                    }
                };
                httpInfosmob.setRequestHeader('Content-Type', 'application/json');
                httpInfosmob.send();
            };
            
            let butonclicmob = document.querySelector('#idresetmob');
            if (butonclicmob !== null) {
                butonclicmob.onclick = () => 
                {
                    let httpSiegeselectmob;
                    httpSiegeselectmob = new XMLHttpRequest();
                    const siegselectmob = document.querySelector('#siegselectmob').value;
                    const idtapmob = document.querySelector('#idtampomob').value;
                    httpSiegeselectmob.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtapmob}/${siegselectmob}`, true);
                    httpSiegeselectmob.onload = () => 
                    {
                        const donselectmob = JSON.parse(httpSiegeselectmob.responseText);
                        document.querySelector('#messmob').style.display = 'none';
                        
                    };
                    httpSiegeselectmob.setRequestHeader('Content-Type', 'application/json');
                    httpSiegeselectmob.send();

                
                };
            }
                
                e.onclick = function () {   
                    let mobForm = document.querySelector('#mobForm');
                    
                    mobForm.setAttribute('action', `${APP_ROOT}/Programmes/passagermobil/${e.dataset.cle_compagnie}`);   
                }
                
    })

});