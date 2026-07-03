document.addEventListener('DOMContentLoaded', () => {
        
    document.querySelectorAll('.confirmecarte').forEach(function (e) {
        document.querySelector('h3#carteconfTitle').innerHTML = `CONFIRMATION CARTE DE VOYAGE`;

        let codcart = document.querySelector('#confirmer_infoscart');
        if (codcart !== null)
        codcart.onclick = () => {
            
            //verification code de confirmation
            let Requestcart;
            
            if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                Requestcart = new XMLHttpRequest();
            } else if (window.ActiveXObject) { // IE 6 and older
                Requestcart = new ActiveXObject("Microsoft.XMLHTTP");
            }
            
            var confircart = document.querySelector("#codeconfirmcart").value;

            Requestcart.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verificationcarte/${confircart}`, true);
            Requestcart.onload = () => {
                const cartdata = JSON.parse(Requestcart.responseText);
                if (cartdata == null) {
                        
                        
                    document.querySelector('#messagepcart').style.display = 'block';
                    document.querySelector('#erreurMessagepcart').innerHTML = `Cette carte ne peut pas être confirmée .`;
                    document.querySelector('#pasnompconfcart').style.display = 'none';
                    document.querySelector('#pasprenompconfcart').style.display = 'none';
                    document.querySelector('#pascontactpconfcart').style.display = 'none';
                    document.querySelector('#pascnibpconfcart').style.display = 'none';
                    document.querySelector('#pasdatepconfcart').style.display = 'none';
                    document.querySelector('#delivrelieucart').style.display = 'none';
                    document.querySelector('#heuredcart').style.display = 'none';
                    document.querySelector('#depsiegcart').style.display = 'none';
                    document.querySelector('#validcart').style.display = 'none';
                        

                } else {
                    if (Object.entries(cartdata).length > 1) {

                        document.querySelector('#pasnompconfcart').style.display = 'block';
                        document.querySelector('#pasprenompconfcart').style.display = 'block';
                        document.querySelector('#pascontactpconfcart').style.display = 'block';
                        document.querySelector('#pascnibpconfcart').style.display = 'block';
                        document.querySelector('#pasdatepconfcart').style.display = 'block';
                        document.querySelector('#delivrelieucart').style.display = 'block';
                        document.querySelector('#heuredcart').style.display = 'block';
                        document.querySelector('#depsiegcart').style.display = 'block';
                        document.querySelector('#validcart').style.display = 'block';
                        document.querySelector('#messagepcart').style.display = 'none';

                        
                            document.querySelector('#pasnompconfcart').value = `${cartdata.nom_client}`;
                            document.querySelector('#pasprenompconfcart').value = `${cartdata.prenom_client}`;
                            document.querySelector('#pascnibpconfcart').value = `${cartdata.num_CNIB}`;
                            document.querySelector('#pasdatepconfcart').value = `${cartdata.date_delivre}`;
                            document.querySelector('#delivrelieucart').value = `${cartdata.lieu_delivre}`;
                            document.querySelector('#clientconfirmeidcart').value = `${cartdata.id_client}`;
                            document.querySelector('#pascontactpconfcart').value = `${cartdata.contact_client}`;
                    }
                      
                }
            };
            Requestcart.setRequestHeader('Content-Type', 'application/json');
            Requestcart.send();
        };
        let Requestscart;
        if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
            Requestscart = new XMLHttpRequest();
        } else if (window.ActiveXObject) { // IE 6 and older
            Requestscart = new ActiveXObject("Microsoft.XMLHTTP");
        }
        let axeselectcart = document.querySelector('#axeconfcart');
        if (axeselectcart !== null)
        axeselectcart.onchange = () => {
                document.querySelector('#heuredcart').options.length = 1;
                
            var heureaxepcart = document.querySelector('#axeconfcart').value;
            var dateactuelcart = document.querySelector('#actuelcart').value;
            
            let httpRequetesquartcart = new XMLHttpRequest();
                    httpRequetesquartcart.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfquart/${heureaxepcart}`, true);
                            httpRequetesquartcart.onload = () => {
                            const dataqcart = JSON.parse(httpRequetesquartcart.responseText);
                            if(dataqcart == ''){
                                document.querySelector('#quartconf').options.length = 1;
                            }else{
                                if (Object.entries(dataqcart).length >= 1) {
                                            
                                    for (let key in Object.entries(dataqcart)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${dataqcart[key].nom_quartier}`;
                                        opt.innerHTML = `${dataqcart[key].nom_quartier}`;
                                        document.querySelector('#quartconfcart').add(opt);
                                    }
                                } else {
                                    document.querySelector('#quartconfcart').options.length = 1;
                                }
                            }
                                
                                    
                            };
                            httpRequetesquartcart.setRequestHeader('Content-Type', 'application/json');
                            httpRequetesquartcart.send();
            Requestscart.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfprog/${heureaxepcart}/${dateactuelcart}`, true);
            Requestscart.onload = () => {
                const data2cart = JSON.parse(Requestscart.responseText);
                if (Object.entries(data2cart).length >= 1) {
                    for (let key in Object.entries(data2cart)) {
                        let opt = document.createElement('option');
                        opt.value = `${data2cart[key].code_progr}`;
                        opt.innerHTML = `${data2cart[key].heure}/${data2cart[key].date_progr}`;
                        document.querySelector('#heuredcart').add(opt);
                        
                        
                    }
                }else{
                    document.querySelector('#heuredcart').options.length = 1;
                }
            };
            Requestscart.setRequestHeader('Content-Type', 'application/json');
            Requestscart.send();
        };
        
        let cartheurdeprtcart = document.querySelector('#heuredcart');
        if (cartheurdeprtcart !== null)
        cartheurdeprtcart.onchange = () => {
                
                document.querySelector('#depsiegcart').options.length = 1;
                const Requestecart = new XMLHttpRequest();
                const selectorpcart = document.querySelector('#heuredcart').options[document.querySelector('#heuredcart').
                options.selectedIndex].value;
                Requestecart.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/siegdispo/${selectorpcart}`, true);
                Requestecart.onload = () => {
                    const datasgccart = JSON.parse(Requestecart.responseText);
                    if (Object.entries(datasgccart).length >= 1) {
                        for (let key in Object.entries(datasgccart)) {
                            
                            document.querySelector('#caissepvend_cart').value = `${datasgccart[key].intervalle1}`;
                            document.querySelector('#caissedpvend_cart').value = `${datasgccart[key].intervalle2}`;
                            document.querySelector('#directidcart').value = `${datasgccart[key].nom_ligne}`;
                            document.querySelector('#confheurecart').value = `${datasgccart[key].heure}`;
                            document.querySelector('#gareid_depcart').value = `${datasgccart[key].gaexp_lg}`;
                            document.querySelector('#dateconfirmecart').value = `${datasgccart[key].date_progr}`;
                            document.querySelector('#catconfirmecart').value = `${datasgccart[key].categori}`;
                            document.querySelector('#lignehconfcart').value = `${datasgccart[key].id_ligneheure}`;
                            document.querySelector('#programconfcart').value = `${datasgccart[key].code_progr}`;
                        }
                    } 
                    const Requestbiscart = new XMLHttpRequest();
                            const pldebutcart = document.querySelector('#caissepvend_cart').value;
                            const plfincart = document.querySelector('#caissedpvend_cart').value;
                            const cfdircart = document.querySelector('#directidcart').value;
                            const hconfircart = document.querySelector('#confheurecart').value;
                            const dconfirmecart = document.querySelector('#dateconfirmecart').value;
                    Requestbiscart.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${selectorpcart}/${dconfirmecart}/${cfdircart}/${hconfircart}/${pldebutcart}/${plfincart}`, true);
                    Requestbiscart.onload = () => {
                        const datasgcbiscart = JSON.parse(Requestbiscart.responseText);
                        if (Object.entries(datasgcbiscart).length >= 1) {
                            for (let key in Object.entries(datasgcbiscart)) {
                                let opt = document.createElement('option');
                                opt.value = `${datasgcbiscart[key].siege_num}`;
                                opt.innerHTML = `${datasgcbiscart[key].siege_num}`;
                                document.querySelector('#depsieg').add(opt);
                            }
                        } else {
                            document.querySelector('#depsiegcart').options.length = 1;
                        }
                    };
                    Requestbiscartcart.setRequestHeader('Content-Type', 'application/json');
                    Requestbiscartcart.send();
                };
                Requestecartcart.setRequestHeader('Content-Type', 'application/json');
                Requestecartcart.send();
            };

            let depsiegconfcart = document.querySelector('#depsiegcart');
            if (depsiegconfcart !== null)
            depsiegconfcart.onchange = () => {
                    
                    let Requestsiegevenduconfcart;
                    
                    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                        Requestsiegevenduconfcart = new XMLHttpRequest();
                    } else if (window.ActiveXObject) { // IE 6 and older
                        Requestsiegevenduconfcart = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                    const dp_progconfcart = document.querySelector('#programconfcart').value;
                    const dp_siegeconfcart = document.querySelector('#depsiegcart').options[document.querySelector('#depsiegcart').
                    options.selectedIndex].value;
                    Requestsiegevenduconfcart.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${dp_progconfcart}/${dp_siegeconfcart}`, true);
                    Requestsiegevenduconfcart.onload = () => 
                    {
                        
                            const confdonsiegcart = JSON.parse(Requestsiegevenduconfcart.responseText);
                            if (confdonsiegcart == '')
                                    {
                                        let httpSiegsconfcart;
                                        httpSiegsconfcart = new XMLHttpRequest();

                                        httpSiegsconfcart.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${dp_progconfcart}/${dp_siegeconfcart}`, true);
                                        httpSiegsconfcart.onload = () => 
                                        {
                                            const dongconfcart = JSON.parse(httpSiegsconfcart.responseText);
                                            document.querySelector('#messconfcart').style.display = 'none';
                                            if (Object.entries(dongconfcart).length >= 1)
                                            {
                                                for (let key in Object.entries(dongconfcart)) {
                                                    document.querySelector('#idtampoconfcart').value = `${dongconfcart[key].idtamp}`;                    
                                                    document.querySelector('#siegselectconfcart').value = `${dongconfcart[key].numsieg}`;
                                                }

                                            }
                                        };
                                        httpSiegsconfcart.setRequestHeader('Content-Type', 'application/json');
                                        httpSiegsconfcart.send();
                                    }
                                    else {
                                        document.querySelector('#depsiegcart').value = '';     
                                        if (Object.entries(confdonsiegcart).length >= 1)
                                        {
                                            for (let key in Object.entries(confdonsiegcart)) {
                                                document.querySelector('#idtampoconfcart').value = `${confdonsiegcart[key].idtamp}`;                    
                                                document.querySelector('#siegselectconfcart').value = `${confdonsiegcart[key].numsieg}`;
                                            }

                                        }
                                        document.querySelector('#messconfcart').style.display = 'block';
                                        document.querySelector('#erreurMessconfcart').innerHTML = `Siege déjà utilisé.`; 
                                    }
                    };
                    Requestsiegevenduconfcart.setRequestHeader('content-Type', 'text/json');
                    Requestsiegevenduconfcart.send();
                };
//bouton annuler
                butoncliconfcart = document.querySelector('#cartreset');
                if (butoncliconfcart !== null) {
                    butoncliconfcart.onclick = () => 
                    {
                        let httpSiegeselectconfcart;
                        httpSiegeselectconfcart = new XMLHttpRequest();
                        const siegselectconfcart = document.querySelector('#siegselectconfcart').value;
                        const idtapconfcart = document.querySelector('#idtampoconfcart').value;
                        httpSiegeselectconfcart.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtapconfcart}/${siegselectconfcart}`, true);
                        httpSiegeselectconfcart.onload = () => 
                        {
                            const donselectconfcart = JSON.parse(httpSiegeselectconfcart.responseText);
                            console.debug(`${typeof donselectconfcart} - ${donselectconfcart.attributes}`, console.memory);
                            document.querySelector('#messconfcart').style.display = 'none';
                            
                        };
                        httpSiegeselectconfcart.setRequestHeader('Content-Type', 'application/json');
                        httpSiegeselectconfcart.send();
    
                    
                    };
                }
    //close
        
        e.onclick = function () {
            let confFormcart = document.querySelector('#cartForm');
            confFormcart.setAttribute('action', `${APP_ROOT}/Confirmation/confirmecarte/${e.dataset.cle_compagnie}`);
        }
    })
});