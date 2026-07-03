document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adventeescale').forEach(function (e) 
    {
            let escalgar= document.querySelector('#depargareescal');
            if (escalgar !== null)
            escalgar.onchange = () => {
                document.querySelector('#prix_axeescal').value = '';
                document.querySelector('#date_depheureescal').value = '';
                document.querySelector('#arrsgareescal').value = '';
                document.querySelector('#hdepartescal').options.length = 1;
                document.querySelector('#quartierescal').options.length = 1;
                document.querySelector('#typesescal').value = '';
                  
            };
            let arescal = document.querySelector('#arrsgareescal');
            if (arescal !== null)
            arescal.onchange = () => {
                document.querySelector('#prix_axeescal').value = '';
                document.querySelector('#date_depheureescal').value = '';
                document.querySelector('#hdepartescal').options.length = 1;
                document.querySelector('#quartierescal').options.length = 1;
                document.querySelector('#typesescal').value = '';
                  
                    const typgareescal = document.querySelector('#arrsgareescal')
                    .options[document.querySelector('#arrsgareescal').options.selectedIndex].value;
                    let httptypequartescal;
                    httptypequartescal = new XMLHttpRequest();
                    
                    httptypequartescal.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${typgareescal}`, true);
                    httptypequartescal.onload = () => 
                    {
                        const donquaescal = JSON.parse(httptypequartescal.responseText);
                        if (donquaescal == '') {
                            document.querySelector('#quartierescal').options.length = 1;
                        }
                        else{
                            if (Object.entries(donquaescal).length >= 1) {
                                            
                                for (let key in Object.entries(donquaescal)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${donquaescal[key].nom_quartier}`;
                                    opt.innerHTML = `${donquaescal[key].nom_quartier}`;
                                    document.querySelector('#quartierescal').add(opt);
                                }
                            } else {
                                document.querySelector('#quartierescal').options.length = 1;
                            }
                        }
                        
                    };
                    httptypequartescal.setRequestHeader('Content-Type', 'application/json');
                    httptypequartescal.send();
            };
            
            let daescal = document.querySelector('#date_depheureescal');
            if (daescal !== null){
                daescal.onchange = () => 
                {
                    
                    document.querySelector('#hdepartescal').options.length = 1;
                    
                    let httpRequetesescal;
                    
                    if (window.XMLHttpRequest) {
                        httpRequetesescal = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpRequetesescal = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                        var depaescal = document.querySelector('#depargareescal').value;
                        var arrescal = document.querySelector('#arrsgareescal').value;
                        var datedepartescal = document.querySelector('#date_depheureescal').value;
                        var dateactuescal = document.querySelector('#actuescal').value;
                                         
                        var post_lhdepescal = depaescal.split('/');
                        var seltdepescal = post_lhdepescal[0];
                        var sougidescal = post_lhdepescal[1];
                        var dest_lhdepescal = arrescal.split('/');
                        var seltdestescal = dest_lhdepescal[0];
                        var sougesescal = dest_lhdepescal[1];
                        if(datedepartescal >= dateactuescal)
                        {
                            
                            httpRequetesescal.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheure1/${seltdepescal}-${seltdestescal}/${datedepartescal}`, true);
                            httpRequetesescal.onload = () => {
                                const dataAxeescal = JSON.parse(httpRequetesescal.responseText);
                                
                                    if (dataAxeescal == '') {
                                        
                                        document.querySelector('#smsdtescal').style.display = 'none';
                                        document.querySelector('#date_depheureescal').style.color = "black";
                                        document.querySelector('#date_depheureescal').style.border = "1px solid";
                                        
                                    } 
                                    else 
                                    {       
                                        
                                        document.querySelector('#smsdtescal').style.display = 'none';
                                        document.querySelector('#date_depheureescal').style.color = "black";
                                        document.querySelector('#date_depheureescal').style.border = "1px solid";
                                        if (Object.entries(dataAxeescal).length >= 1) 
                                        {
                                                
                                            
                                            for (let key in Object.entries(dataAxeescal)) {
                                                    let opt = document.createElement('option');
                                                    opt.value = `${dataAxeescal[key].id_ligneheure}/${dataAxeescal[key].heure}`;
                                                    opt.innerHTML = `${dataAxeescal[key].heure}`;
                                                    document.querySelector('#hdepartescal').add(opt);
                                                }
                                        } else {
                                            document.querySelector('#hdepartescal').options.length = 1;
                                        }
                                    }
                                                                               
                            };
                            httpRequetesescal.setRequestHeader('Content-Type', 'application/json');
                            httpRequetesescal.send();

                            let hrdepartescal = document.querySelector('#hdepartescal');
                            if (hrdepartescal !== null) {
                                hrdepartescal.onchange = () => 
                                {       
                                    const seleescal = document.querySelector('#hdepartescal')
                                        .options[document.querySelector('#hdepartescal').options.selectedIndex].value;

                                        var post_lhescal = seleescal.split('/');
                                        var selescal = post_lhescal[0];
                                        var lhselescal = post_lhescal[1];

                                    var tfbsescal = document.querySelector('#tarifattribescal').value;
                                    const httpPrixescal = new XMLHttpRequest();
                                    httpPrixescal.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifpriesc/${selescal}/${tfbsescal}/${seltdepescal}`, true);
                                    httpPrixescal.onload = () => 
                                    {

                                        const donprixescal = JSON.parse(httpPrixescal.responseText);
                                        console.debug(`${typeof donprixescal}-${donprixescal.attributes}`, console.memory);
                                        if (Object.entries(donprixescal).length >= 1) {
                                            for (let key in Object.entries(donprixescal)) 
                                            {
                                                document.querySelector('#prix_axeescal').value = `${donprixescal[key].prix}`;

                                            }
                                        }
                                    };
                                    httpPrixescal.setRequestHeader('Content-Type', 'application/json');
                                    httpPrixescal.send();
                                };
                            }

                        }
                        else
                        {
                            document.querySelector('#date_depheureescal').style.color = "#FF0000";
                            document.querySelector('#date_depheureescal').style.border = "2px solid #FF0000";
                            document.querySelector('#smsdtescal').style.display = 'block';
                            document.querySelector('#erreurSmsdtescal').innerHTML = `Date non valide.`;
                        }
                    
                }; 
            }
            
        //recherche d'information du client depart principal
        let infescal = document.querySelector('#rnclient_contactescal');
        if (infescal !== null)
            infescal.onkeyup = () => {
                let httpInfosescal;
                if (window.XMLHttpRequest) {
                    httpInfosescal = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfosescal = new ActiveXObject("Microsoft.XMLHTTP");
                }
                var verificatescal = document.querySelector('#rnclient_contactescal').value;
                
                httpInfosescal.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifinfos/${verificatescal}`, true);
                httpInfosescal.onload = () => {
                    const infosescal = JSON.parse(httpInfosescal.responseText);
                    if (infosescal == null) {
                        document.querySelector('#rclientescal').value = "";
                        document.querySelector('#prnclientescal').value = "";
                        document.querySelector('#pascompagnieescal').value = "";
                        document.querySelector('#typesescal').value = "";
                        document.querySelector('#cnibclescal').value = "";
                        document.querySelector('#dateclescal').value = "";
                        document.querySelector('#lieuclescal').value = "";
                    
                    } else {
                        if (Object.entries(infosescal).length > 1) {
                            
                            if (infosescal.contact_client == verificatescal) {
                                document.querySelector('#rclientescal').value = `${infosescal.nom_client}`;
                                document.querySelector('#prnclientescal').value = `${infosescal.prenom_client}`;
                                document.querySelector('#pascompagnieescal').value = `${infosescal.id_client}`;
                                document.querySelector('#rclientcpescal').value = `${infosescal.nom_client}`;
                                document.querySelector('#prnclientcpescal').value = `${infosescal.prenom_client}`;
                                document.querySelector('#typesescal').value = `${infosescal.type_client}`;
                                document.querySelector('#cnibclescal').value = `${infosescal.num_CNIB}`;
                                document.querySelector('#dateclescal').value = `${infosescal.date_delivre}`;
                                document.querySelector('#lieuclescal').value = `${infosescal.lieu_delivre}`;
                    
                            } else {
                                document.querySelector('#rclientescal').value = "";
                                document.querySelector('#prnclientescal').value = "";
                                document.querySelector('#pascompagnieescal').value = "";
                                document.querySelector('#typesescal').value = "";
                                document.querySelector('#cnibclescal').value = "";
                                document.querySelector('#dateclescal').value = "";
                                document.querySelector('#lieuclescal').value = "";
                    
                            }
                        }
                    }
                };
                httpInfosescal.setRequestHeader('Content-Type', 'application/json');
                httpInfosescal.send();
            };
            
                e.onclick = function () {   
                    let escalForm = document.querySelector('#escalForm');
                    
                    escalForm.setAttribute('action', `${APP_ROOT}/Ventescales/passagerescal/${e.dataset.cle_compagnie}`);   
                }

                var clique = true;

                $('#bottonescal').click(function(event) 
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