document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addgprogramme').forEach(function (e) {
        
        e.onclick = function () {
            let prForm = document.querySelector('#formprog');
            document.querySelector('h3#Titleprog').innerHTML = `MODIFICATION DU PROGRAMME`;
            $('#idcateg').val(`${e.dataset.categorie}`);
            $('#typetaf').val(`${e.dataset.typtarif}`);
            $('#progh').val(`${e.dataset.eure}`);
            $('#ouotadebut').val(`${e.dataset.inter1}`);
            $('#ouotafin').val(`${e.dataset.inter2}`);
            $('#progdate').val(`${e.dataset.pdate}`);
            $('#ouotafinancien').val(`${e.dataset.categnbplace}`);
        
                let typcat = document.querySelector('#idcateg');
                
                if (typcat !== null) 
                typcat.onchange = () => 
                {
                    let Infoscateg;
                    if (window.XMLHttpRequest) {
                        Infoscateg = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        Infoscateg = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var categchoisi = document.querySelector('#idcateg')
                    .options[document.querySelector('#idcateg').options.selectedIndex].value;
                    Infoscateg.open('GET', window.location.origin + `${APP_ROOT}/categories/getnbrplace/${categchoisi}`, true);
                    Infoscateg.onload = () => {
                        const rescat = JSON.parse(Infoscateg.responseText);
        
                            if (Object.entries(rescat).length >= 1) {
                                  
                                    document.querySelector('#ouotafin').value = `${rescat.nbr_place}`;
                                    document.querySelector('#ouotafinnouveau').value = `${rescat.nbr_place}`;
                                
                            } 
        
                        };
                        
                        Infoscateg.setRequestHeader('Content-Type', 'application/json');
                        Infoscateg.send();
    
                };
            prForm.setAttribute('action', `${APP_ROOT}/Programmes/editgare_/${e.dataset.cle_compagnie}/${e.dataset.code}/${e.dataset.departcd}`);

        }
    })
});