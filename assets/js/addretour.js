document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addretour').forEach(function (e) {
        document.querySelector('h3#retourTitle').innerHTML = `RETOUR`;

        let infosret = document.querySelector('#retreprogrammer_infos');
        if (infosret !== null)
            infosret.onclick = () => {
                //verification code de reprogrammation
                let httpRequestRep;
                
                if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                    httpRequestRep = new XMLHttpRequest();
                } else if (window.ActiveXObject) { // IE 6 and older
                    httpRequestRep = new ActiveXObject("Microsoft.XMLHTTP");
                }
                
                document.querySelector('#retnompre').options.length = 1;
                var retcocl = document.querySelector("#retcodeclientp").value;
                var retgd = document.querySelector("#retgareconnect").value;
                var retu = document.querySelector("#retuserconnected").value;

                httpRequestRep.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/verifretcodecl/${retgd}/${retcocl}/${retu}`, true);
                httpRequestRep.onload = () => {
                    const donneesrt = JSON.parse(httpRequestRep.responseText);
                    if (donneesrt == null) {
                        document.querySelector('#retnompre').options.length = 1;
                    } else 
                    {
                               
                            if (Object.entries(donneesrt).length >= 1) {
                                for (let key in Object.entries(donneesrt)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${donneesrt[key].tamponcod}/${donneesrt[key].tamponcod}/${donneesrt[key].tamponcod}/${donneesrt[key].tamponcod}`;
                                    opt.innerHTML = `${donneesrt[key].nom_client} ${donneesrt[key].prenom_client}`;
                                    document.querySelector('#retnompre').add(opt);
                                    
                                    
                                }
                            }else{
                                document.querySelector('#retnompre').options.length = 1;
                            }
                           
                    }
                };
                httpRequestRep.setRequestHeader('Content-Type', 'application/json');
                httpRequestRep.send();
            };

        let infosretr = document.querySelector('#retnompre');
        if (infosretr !== null)
            infosretr.onclick = () => {
                //verification code de reprogrammation
                let httpRequestRt;
                
                if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                    httpRequestRt = new XMLHttpRequest();
                } else if (window.ActiveXObject) { // IE 6 and older
                    httpRequestRt = new ActiveXObject("Microsoft.XMLHTTP");
                }
                 const selectcd = document.querySelector('#retnompre').
                    options[document.querySelector('#retnompre').options.selectedIndex].value;
                    
                httpRequestRt.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/verifcoderetour/${selectcd}`, true);
                httpRequestRt.onload = () => {
                    const donnees = JSON.parse(httpRequestRt.responseText);
                    if (donnees == null) {
                        document.querySelector('#retpasserp').value = '';
                        document.querySelector('#retligneid').value = '';
                        document.querySelector('#retnomligne').value = '';
                        document.querySelector('#usret').value = '';
                        document.querySelector('#retcle').value = '';
                        document.querySelector('#retsgd').value = '';
                        document.querySelector('#retprixvent').value = '';
                        document.querySelector('#retcodeticket').value = '';
                        document.querySelector('#retdepgid').value = '';
                        document.querySelector('#dateventeret').value = '';
                        document.querySelector('#retcompcd').value = '';


                        
                    } else 
                    {
                               
                        if (Object.entries(donnees).length >= 1){
                            document.querySelector('#retpasserp').value = `${donnees.code_passager}`;
                            document.querySelector('#retligneid').value = `${donnees.ligne_id}`;
                            document.querySelector('#retnomligne').value = `${donnees.nom_ligne}`;
                            document.querySelector('#usret').value = `${donnees.idcptuser}`;
                            document.querySelector('#retcle').value = `${donnees.id_client_pass}`;
                            document.querySelector('#retsgd').value = `${donnees.departclient_idgare}`;
                            document.querySelector('#retprixvent').value = `${donnees.prixvente}`;
                            document.querySelector('#retcodeticket').value = `${donnees.tamponcod}`;
                            document.querySelector('#retdepgid').value = `${donnees.gaexp_lg}`;
                            document.querySelector('#retcompcd').value = `${donnees.id_compaga}`;
                            document.querySelector('#dateventeret').value = `${donnees.datep_create}`;
                        } else {
                            document.querySelector('#retnompre').options.length = 1;
                        }
                           
                    }
                };
                httpRequestRt.setRequestHeader('Content-Type', 'application/json');
                httpRequestRt.send();
            };
        
        e.onclick = function () {
            let retForm = document.querySelector('#retForm');
            retForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/retour/${e.dataset.cle_compagnie}`);
        }

        var clique = true;

            $('#idretepson').click(function(event) 
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