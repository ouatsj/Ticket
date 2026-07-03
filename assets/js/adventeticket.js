document.addEventListener('DOMContentLoaded', () => {
        
    document.querySelectorAll('.adventeticket').forEach(function (e) 
    {
        document.querySelector('h3#fideliteTitle').innerHTML = `VENTE DE TICKET`;

            let ar= document.querySelector('#arrsgarefi');
            if (ar !== null)
            ar.onchange = () => {
                document.querySelector('#date_depheurefi').value = '';
                document.querySelector('#hdepartfi').options.length = 1;
                document.querySelector('#quartierfi').options.length = 1;
                document.querySelector('#psiegesfi').options.length = 1;
                
                    const typgare = document.querySelector('#arrsgarefi').value;
                    let httptypequart;
                    httptypequart = new XMLHttpRequest();
                    
                    httptypequart.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${typgare}`, true);
                    httptypequart.onload = () => 
                    {
                        const donqua = JSON.parse(httptypequart.responseText);
                        if (donqua == '') {
                            document.querySelector('#quartierfi').options.length = 1;
                        }
                        else{
                            if (Object.entries(donqua).length >= 1) {
                                            
                                for (let key in Object.entries(donqua)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${donqua[key].nom_quartier}`;
                                    opt.innerHTML = `${donqua[key].nom_quartier}`;
                                    document.querySelector('#quartierfi').add(opt);
                                }
                            } else {
                                document.querySelector('#quartierfi').options.length = 1;
                            }
                        }
                        

                    };
                    httptypequart.setRequestHeader('Content-Type', 'application/json');
                    httptypequart.send();
            };
            
            let da = document.querySelector('#date_depheurefi');
            if (da !== null){
                da.onchange = () => 
                {
                    
                    document.querySelector('#hdepartfi').options.length = 1;
                    document.querySelector('#psiegesfi').options.length = 1;

                    let httpRequetes;
                    
                    if (window.XMLHttpRequest) {
                        httpRequetes = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpRequetes = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                        var depa = document.querySelector('#depargarefi').value;
                        var arr = document.querySelector('#arrsgarefi').value;
                        var datedepart = document.querySelector('#date_depheurefi').value;
                        var dateactu = document.querySelector('#actufi').value;
                                         
                        var post_lhdep = depa.split('/');
                        var seltdep = post_lhdep[0];
                        var sougid = post_lhdep[1];
                        var arr = document.querySelector('#arrsgarefi').value;
                        var datedepart = document.querySelector('#date_depheurefi').value;
                        var dateactu = document.querySelector('#actufi').value;                   
                        const heuredef = document.querySelector('#heureidprogfi')
                        .options[document.querySelector('#heureidprogfi').options.selectedIndex].value;
                        if(datedepart >= dateactu)
                        {
                            
                            httpRequetes.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheure1/${seltdep}-${arr}/${datedepart}`, true);
                            httpRequetes.onload = () => {
                                const dataAxe = JSON.parse(httpRequetes.responseText);
                                
                                    if (dataAxe == '') {
                                        
                                        document.querySelector('#smsdtfi').style.display = 'none';
                                        document.querySelector('#date_depheurefi').style.color = "black";
                                        document.querySelector('#date_depheurefi').style.border = "1px solid";
                                    } 
                                    else 
                                    {       
                                        
                                        document.querySelector('#smsdtfi').style.display = 'none';
                                        document.querySelector('#date_depheurefi').style.color = "black";
                                        document.querySelector('#date_depheurefi').style.border = "1px solid";
                                        if (Object.entries(dataAxe).length >= 1) 
                                        {
                                                
                                            
    
                                            for (let key in Object.entries(dataAxe)) {
                                                    let opt = document.createElement('option');
                                                    opt.value = `${dataAxe[key].id_ligneheure}/${dataAxe[key].heure}`;
                                                    opt.innerHTML = `${dataAxe[key].heure}`;
                                                    document.querySelector('#hdepartfi').add(opt);
                                                }
                                        } else {
                                            document.querySelector('#hdepartfi').options.length = 1;
                                        }
                                    }

                                        let hrdepart = document.querySelector('#hdepartfi');
                                        if (hrdepart !== null) {
                                            hrdepart.onchange = () => 
                                            {
                                                document.querySelector('#psiegesfi').options.length = 1;
                                                document.querySelector('#typegarefi').value = '';
                                                const httpRequest = new XMLHttpRequest();
                                                //var typ_gare = document.querySelector('#typegare').value; 
                                                const sele = document.querySelector('#hdepartfi')
                                                    .options[document.querySelector('#hdepartfi').options.selectedIndex].value;

                                                    var post_lh = sele.split('/');
                                                    var sel = post_lh[0];
                                                    var lhsel = post_lh[1];

                                                    const dpt_date = document.querySelector('#date_depheurefi').value;
                                                    var typgare = document.querySelector('#arrsgarefi').value;
                                                    const httptypegare = new XMLHttpRequest();
                                                    httptypegare.open('GET', window.location.origin + `${APP_ROOT}/programmes/gareprincipale/${typgare}/${lhsel}`, true);
                                                    httptypegare.onload = () => 
                                                    {
                                                        const dongare = JSON.parse(httptypegare.responseText);
                                                        if (Object.entries(dongare).length >= 1)
                                                        for (let key in Object.entries(dongare)) 
                                                        document.querySelector('#typegarefi').value = `${dongare[key].typestatutgare}`;
                                                    };
                                                    httptypegare.setRequestHeader('Content-Type', 'application/json');
                                                    httptypegare.send();

                                                


                                                httpRequest.open('GET', window.location.origin + `${APP_ROOT}/programmes/progactifnonactif/${seltdep}-${arr}/${dpt_date}/${sel}`, true);
                                                httpRequest.onload = () => 
                                                {
                                                    var typ_gare = document.querySelector('#typegarefi').value;    
                                                    const don = JSON.parse(httpRequest.responseText);
                                                        console.debug(`${typeof don} - ${don.attributes}`, console.memory);

                                                        if (don == '') 
                                                        {
                                                            if(typ_gare == 'principale'){
                                                                    let opt = document.createElement('option');
                                                                    opt.value = 1;
                                                                    opt.innerHTML = 1;
                                                                    document.querySelector('#psiegesfi').add(opt);
                                                            
                                                                    departpsieges = document.querySelector('#psiegesfi');
                                                                    if (departpsieges !== null) {
                                                                        departpsieges.onchange = () => 
                                                                        {
                                                                        let httpProg;
                                                                            httpProg = new XMLHttpRequest();
                                                                            httpProg.open('GET', window.location.origin + `${APP_ROOT}/programmes/creedepart/${seltdep}/${dpt_date}/${sel}/${lhsel}`, true);
                                                                            httpProg.onload = () => 
                                                                            {
                                                                             const dons = JSON.parse(httpProg.responseText);
                                                                                console.debug(`${typeof dons} - ${dons.attributes}`, console.memory);
                                                                                if (Object.entries(dons).length >= 1) {
                                                                                    for (let key in Object.entries(dons)) {
                                                                                        document.querySelector('#programfi').value = `${dons[key].code_progr}`;
                                                                                        document.querySelector('#catefi').value = `${dons[key].categorie}`;
                                                                                        document.querySelector('#deplignefi').value = `${dons[key].gareidentif}`;
                                                                                        document.querySelector('#lignfi').value = `${dons[key].ident_ligne}`;
                                                                                        document.querySelector('#nomitinfi').value = `${dons[key].nom_ligne}`;
                                                                                    }
                                                                                        let httpSiege;
                                                                                        httpSiege = new XMLHttpRequest();
                                                                                        const sig = document.querySelector('#psiegesfi')
                                                                                        .options[document.querySelector('#psiegesfi').options.selectedIndex].value;
                                                                                        const pro = document.querySelector('#programfi').value;
                                                                                        httpSiege.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${pro}/${sig}`, true);
                                                                                        httpSiege.onload = () => 
                                                                                        {
                                                                                            const donsg = JSON.parse(httpSiege.responseText);
                                                                                            console.debug(`${typeof donsg} - ${donsg.attributes}`, console.memory);
                                                                                            if(donsg == '')
                                                                                            {
                                                                                                let httpSieg;
                                                                                                httpSieg = new XMLHttpRequest();
                    
                                                                                                httpSieg.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${pro}/${sig}`, true);
                                                                                                httpSieg.onload = () => 
                                                                                                {
                                                                                                    const donsg2 = JSON.parse(httpSieg.responseText);
                                                                                                    document.querySelector('#messfi').style.display = 'none';
                                                                                                    if (Object.entries(donsg2).length >= 1)
                                                                                                        {
                                                                                                            for (let key in Object.entries(donsg2)) {
                                                                                                                document.querySelector('#idtampofi').value = `${donsg2[key].idtamp}`;                    
                                                                                                                document.querySelector('#siegselectfi').value = `${donsg2[key].numsieg}`;
                                                                                                            }
                                                                                                        }
                                                                                                };
                                                                                                httpSieg.setRequestHeader('Content-Type', 'application/json');
                                                                                                httpSieg.send();
                                                                                            }
                                                                                            else 
                                                                                            {
                                                                                                document.querySelector('#psiegesfi').value = ''; 
                                                                                                if (Object.entries(donsg).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(donsg)) 
                                                                                                    {
                                                                                                        document.querySelector('#idtampofi').value = `${donsg[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselectfi').value = `${donsg[key].numsieg}`;
                                                                                                    }
        
                                                                                                }
                                                                                                document.querySelector('#messfi').style.display = 'block';
                                                                                                document.querySelector('#erreurMessfi').innerHTML = `Siege déjà utilisé.`;                   
                                                                                            }
                                                                                        };
                                                                                        httpSiege.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiege.send();
                    
                                                                                   
                                                                                }
                                                                            };
                                                                            httpProg.setRequestHeader('Content-Type', 'application/json');
                                                                            httpProg.send();
        
                                                                            
                                                                        
                                                                        };
        
                                                                        
                                                                    }
                                                            }else{
                                                                let opt = document.createElement('option');
                                                                opt.value = '';                                                             
                                                            }
                                                            
                                                            
                                                        } 
                                                        else 
                                                        {       
                                                            if (Object.entries(don).length >= 1) {
                                                                for (let key in Object.entries(don)) {
                                                                    document.querySelector('#programfi').value = `${don[key].code_progr}`;
                                                                    document.querySelector('#dateprfi').value = `${don[key].date_progr}`;
                                                                    document.querySelector('#deplignefi').value = `${don[key].gareidentif}`;
                                                                    document.querySelector('#inter1fi').value = `${don[key].intervalle1}`;
                                                                    document.querySelector('#inter2fi').value = `${don[key].intervalle2}`;
                                                                    document.querySelector('#lignfi').value = `${don[key].ident_ligne}`;
                                                                    document.querySelector('#nomitinfi').value = `${don[key].nom_ligne}`;
                                                                    document.querySelector('#herfi').value = `${don[key].heure}`;
                                                                    document.querySelector('#catefi').value = `${don[key].categori}`;

                                                                }
                                                            } 
                                                            
                                                            const httpPrix = new XMLHttpRequest();
                                                            httpPrix.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifpriprg/${sel}`, true);
                                                            httpPrix.onload = () => 
                                                            {
                                                                const donprix = JSON.parse(httpPrix.responseText);
                                                                console.debug(`${typeof donprix}-${donprix.attributes}`, console.memory);
                                                                if (Object.entries(donprix).length >= 1) {
                                                                    for (let key in Object.entries(donprix)) {
                                                                        document.querySelector('#prix_axe').value = `${donprix[key].prix}`;
            
                                                                    }
                                                                }
                                                            };
                                                            httpPrix.setRequestHeader('Content-Type', 'application/json');
                                                            httpPrix.send();
                                                            
                                                            const httpRequette = new XMLHttpRequest();
                                                            const cdprog = document.querySelector('#programfi').value;
                                                            const db = document.querySelector('#inter1fi').value;
                                                            const fn = document.querySelector('#inter2fi').value;
                                                            const lg = document.querySelector('#nomitinfi').value;
                                                            const tim = document.querySelector('#herfi').value;
                                                                httpRequette.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${cdprog}/${dpt_date}/${lg}/${tim}/${db}/${fn}`, true);
                                                            httpRequette.onload = () => {
                                                                const datta = JSON.parse(httpRequette.responseText);
                                                                console.debug(`${typeof datta} - ${datta.attributes}`, console.memory);
                                                                if (Object.entries(datta).length >= 1) {
                                                                    for (let key in Object.entries(datta)) {
                                                                        
                                                                        let opt = document.createElement('option');
                                                                        opt.value = `${datta[key].siege_num}`;
                                                                        opt.innerHTML = `${datta[key].siege_num}`;
                                                                        document.querySelector('#psiegesfi').add(opt);
                                                                        
                                                                    }
                                                                    
                                                                } else {
                                                                    document.querySelector('#psiegesfi').options.length = 1;
                                                                }
                                                            };
                                                            httpRequette.setRequestHeader('Content-Type', 'application/json');
                                                            httpRequette.send();
                                                        }  
                                                        
                                                    };
                                                    httpRequest.setRequestHeader('Content-Type', 'application/json');
                                                    httpRequest.send();
                                                     
                                                };
                                                
                                        
                                            }
                                };
                                httpRequetes.setRequestHeader('Content-Type', 'application/json');
                                httpRequetes.send();
                        }
                        else
                        {
                            document.querySelector('#date_depheurefi').style.color = "#FF0000";
                            document.querySelector('#date_depheurefi').style.border = "2px solid #FF0000";
                            document.querySelector('#smsdtfi').style.display = 'block';
                            document.querySelector('#erreurSmsdtfi').innerHTML = `Date non valide.`;
                        }
                    

                };
                
            }
            progsieges = document.querySelector('#psiegesfi');
            if (progsieges !== null) {
                progsieges.onchange = () => 
                {
                    let httpSieges;
                    httpSieges = new XMLHttpRequest();
                    const sigs = document.querySelector('#psiegesfi')
                    .options[document.querySelector('#psiegesfi').options.selectedIndex].value;
                    const pros = document.querySelector('#programfi').value;

                    httpSieges.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${pros}/${sigs}`, true);
                    httpSieges.onload = () => 
                    {
                        const donsge = JSON.parse(httpSieges.responseText);
                        console.debug(`${typeof donsge} - ${donsge.attributes}`, console.memory);
                        if(donsge == '')
                        {
                            let httpSiegs;
                            httpSiegs = new XMLHttpRequest();

                            httpSiegs.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${pros}/${sigs}`, true);
                            httpSiegs.onload = () => 
                            {
                                const dong = JSON.parse(httpSiegs.responseText);
                                document.querySelector('#messfi').style.display = 'none';
                                if (Object.entries(dong).length >= 1)
                                    {
                                        for (let key in Object.entries(dong)) {
                                            document.querySelector('#idtampofi').value = `${dong[key].idtamp}`;                    
                                            document.querySelector('#siegselectfi').value = `${dong[key].numsieg}`;
                                        }
                                    }
                            };
                            httpSiegs.setRequestHeader('Content-Type', 'application/json');
                            httpSiegs.send();
                        }
                        else {
                            document.querySelector('#psiegesfi').value = '';     
                            if (Object.entries(donsge).length >= 1)
                            {
                                for (let key in Object.entries(donsge)) {
                                    document.querySelector('#idtampofi').value = `${donsge[key].idtamp}`;                    
                                    document.querySelector('#siegselectfi').value = `${donsge[key].numsieg}`;
                                }

                            }
                            document.querySelector('#messfi').style.display = 'block';
                            document.querySelector('#erreurMessfi').innerHTML = `Siege déjà utilisé.`;                                                                   }
                    };
                    httpSieges.setRequestHeader('Content-Type', 'application/json');
                    httpSieges.send();

                
                };
            }
           
            let infdoc = document.querySelector('#cltypefi');
        if (infdoc !== null)
            infdoc.onchange = () => 
            {
                let httpDocs;
                if (window.XMLHttpRequest) {
                    httpDocs = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpDocs = new ActiveXObject("Microsoft.XMLHTTP");
                }
                var docum = document.querySelector('#cltypefi').value;
                
                if (docum == 'Adulte') {
                    document.querySelector('#motiffi').style.display = 'none';
                    document.querySelector('#motifrefusfi').style.display = 'none';
                    document.querySelector('#docfi').style.display = 'none';
                    document.querySelector('#docdelivrefi').style.display = 'none';
                    document.querySelector('#datedocdelfi').style.display = 'none';
                    document.querySelector('#num_docfi').style.display = 'none';
                    document.querySelector('#rclientfi').style.display = 'block';
                    document.querySelector('#prnclientfi').style.display = 'block';
                    document.querySelector('#cnibfi').style.display = 'block';
                    document.querySelector('#date_cnibfi').style.display = 'block';
                    document.querySelector('#lieudelivrefi').style.display = 'block';
                    console.debug(`${docum}`, console.memory);

                } 
                    if (docum == 'Etudiant') {
                        document.querySelector('#docfi').style.display = 'block';
                        document.querySelector('#num_docfi').style.display = 'block';
                        document.querySelector('#docdelivrefi').style.display = 'block';
                        document.querySelector('#datedocdelfi').style.display = 'block';
                        document.querySelector('#rclientfi').style.display = 'block';
                        document.querySelector('#prnclientfi').style.display = 'block';
                        document.querySelector('#cnibfi').style.display = 'none';
                        document.querySelector('#date_cnibfi').style.display = 'none';
                        document.querySelector('#lieudelivrefi').style.display = 'none';
                        console.debug(`${docum}`, console.memory);

                    } 
                    if (docum == 'Elève') {
                        document.querySelector('#docfi').style.display = 'block';
                        document.querySelector('#num_docfi').style.display = 'block';
                        document.querySelector('#docdelivrefi').style.display = 'block';
                        document.querySelector('#datedocdelfi').style.display = 'block';
                        document.querySelector('#rclientfi').style.display = 'block';
                        document.querySelector('#prnclientfi').style.display = 'block';
                        document.querySelector('#cnibfi').style.display = 'none';
                        document.querySelector('#date_cnibfi').style.display = 'none';
                        document.querySelector('#lieudelivrefi').style.display = 'none';
                        console.debug(`${docum}`, console.memory);

                    } 
                    if (docum == 'Enfant') {
                        document.querySelector('#docfi').style.display = 'block';
                        document.querySelector('#num_docfi').style.display = 'block';
                        document.querySelector('#docdelivrefi').style.display = 'block';
                        document.querySelector('#datedocdelfi').style.display = 'block';
                        document.querySelector('#rclientfi').style.display = 'block';
                        document.querySelector('#prnclientfi').style.display = 'block';
                        document.querySelector('#cnibfi').style.display = 'none';
                        document.querySelector('#date_cnibfi').style.display = 'none';
                        document.querySelector('#lieudelivrefi').style.display = 'none';
                        console.debug(`${docum}`, console.memory);

                    } 
                    if (docum == 'Autres') {
                        document.querySelector('#motiffi').style.display = 'block';
                        document.querySelector('#motifrefusfi').style.display = 'block';
                        document.querySelector('#rclientfi').style.display = 'block';
                        document.querySelector('#prnclientfi').style.display = 'block';
                        document.querySelector('#cnibfi').style.display = 'none';
                        document.querySelector('#date_cnibfi').style.display = 'none';
                        document.querySelector('#lieudelivrefi').style.display = 'none';
                        document.querySelector('#docfi').style.display = 'none';
                        document.querySelector('#num_docfi').style.display = 'none';
                        document.querySelector('#docdelivrefi').style.display = 'none';
                        document.querySelector('#datedocdelfi').style.display = 'none';
                        console.debug(`${docum}`, console.memory);

                    } 
                    
            };

            
        //recherche d'information du client depart principal
        let inf = document.querySelector('#rnclient_contactfi');
        if (inf !== null)
            inf.onkeyup = () => {
                let httpInfos;
                if (window.XMLHttpRequest) {
                    httpInfos = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfos = new ActiveXObject("Microsoft.XMLHTTP");
                }
                var verificat = document.querySelector('#rnclient_contactfi').value;
                
                httpInfos.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifinfos/${verificat}`, true);
                httpInfos.onload = () => {
                    const infos = JSON.parse(httpInfos.responseText);
                    if (infos == null) {
                        document.querySelector('#rclientfi').value = "";
                        document.querySelector('#prnclientfi').value = "";
                        document.querySelector('#cnibfi').value = "";
                        document.querySelector('#date_cnibfi').value = "";
                        document.querySelector('#lieudelivrefi').value = "";
                        document.querySelector('#pascompagniefi').value = "";
                    } else {
                        if (Object.entries(infos).length > 1) {
                            
                            if (infos.contact_client == verificat) {
                                document.querySelector('#rclientfi').value = `${infos.nom_client}`;
                                document.querySelector('#prnclientfi').value = `${infos.prenom_client}`;
                                document.querySelector('#cnibfi').value = `${infos.num_CNIB}`;
                                document.querySelector('#date_cnibfi').value = `${infos.date_delivre}`;
                                document.querySelector('#lieudelivrefi').value = `${infos.lieu_delivre}`;
                                document.querySelector('#pascompagniefi').value = `${infos.id_client}`;
                                document.querySelector('#rclientcpfi').value = `${infos.nom_client}`;
                                document.querySelector('#prnclientcpfi').value = `${infos.prenom_client}`;
                                document.querySelector('#cnibcpfi').value = `${infos.num_CNIB}`;
                                document.querySelector('#date_cnibcpfi').value = `${infos.date_delivre}`;
                                document.querySelector('#lieudelivrecpfi').value = `${infos.lieu_delivre}`;
                            } else {
                                document.querySelector('#rclientfi').value = "";
                                document.querySelector('#prnclientfi').value = "";
                                document.querySelector('#cnibfi').value = "";
                                document.querySelector('#date_cnibfi').value = "";
                                document.querySelector('#lieudelivrefi').value = "";
                                document.querySelector('#pascompagniefi').value = "";
                            }
                        }
                    }
                };
                httpInfos.setRequestHeader('Content-Type', 'application/json');
                httpInfos.send();
            };
            
            butonclic = document.querySelector('#idresetfi');
            if (butonclic !== null) {
                butonclic.onclick = () => 
                {
                    let httpSiegeselect;
                    httpSiegeselect = new XMLHttpRequest();
                    const siegselect = document.querySelector('#siegselectfi').value;
                    //const pros = document.querySelector('#program').value;
                    const idtap = document.querySelector('#idtampofi').value;
                    httpSiegeselect.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtap}/${siegselect}`, true);
                    httpSiegeselect.onload = () => 
                    {
                        const donselect= JSON.parse(httpSiegeselect.responseText);
                        console.debug(`${typeof donselect} - ${donselect.attributes}`, console.memory);
                        document.querySelector('#messfi').style.display = 'none';
                        
                    };
                    httpSiegeselect.setRequestHeader('Content-Type', 'application/json');
                    httpSiegeselect.send();

                
                };
            }
                
                e.onclick = function () {   
                    let taForm = document.querySelector('#fiForm');
                    
                    taForm.setAttribute('action', `${APP_ROOT}/Programmes/addtickfidel/${e.dataset.cle_compagnie}`);   
                }
                
                var clique = true;

                $('#bottontickfi').click(function(event) 
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