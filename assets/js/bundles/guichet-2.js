/* Bundle guichet role=2 — genere par scripts/build_guichet_bundles.php */
/* --- addventeticket.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addventeticket').forEach(function (e) 
    {
        document.querySelector('h3#taTitle').innerHTML = `VENTE DE TICKET`;

            let ar = document.querySelector('#arrsgare');
            if (ar !== null)
            ar.onchange = () =>{
                document.querySelector('#prix_axe').value = '';
                  document.querySelector('#tarifattrib').value = '';
                document.querySelector('#date_depheure').value = '';
                document.querySelector('#hdepart').options.length = 1;
                document.querySelector('#quartier').options.length = 1;
                document.querySelector('#psieges').options.length = 1;
                document.querySelector('#hdepartitine').options.length = 1;
                document.querySelector('#psiegesitines').options.length = 1;
                document.querySelector('#idcheminsheur').options.length = 1;
                document.querySelector('#transitedepargare1').options.length = 1;
                document.querySelector('#transitedepargare2').options.length = 1;
                document.querySelector('#transitedepargare3').options.length = 1;
                document.querySelector('#transitedepargare4').options.length = 1;
                document.querySelector('#idchemins').options.length = 1;
                document.querySelector('#idchemins1').options.length = 1;
                document.querySelector('#idchemins2').options.length = 1;
                document.querySelector('#idcompg').value = '';
                document.querySelector('#idcompg1').value = '';
                document.querySelector('#idcompg2').value = '';
                document.querySelector('#idcompg3').value = '';
                document.querySelector('#psiegesitines1').options.length = 1;
                document.querySelector('#idcheminsheur1').options.length = 1;
                document.querySelector('#psiegesitines2').options.length = 1;
                document.querySelector('#idcheminsheur2').options.length = 1;
                document.querySelector('#psiegesitines3').options.length = 1;
                document.querySelector('#quartier1').options.length = 1;
                document.querySelector('#quartier2').options.length = 1;
                document.querySelector('#quartier3').options.length = 1;
                  document.querySelector('#program').value = '';

                    //const typgare = document.querySelector('#arrsgare').value;
                    var typgarepa1 = document.querySelector('#arrsgare').value;
                    var artypgarepa2 = typgarepa1.split('/');
                    const typgare = artypgarepa2[0];
                    var typgare2 = artypgarepa2[1];
                                                    
                    let httptypequart;
                    httptypequart = new XMLHttpRequest();
                    
                    AppRequestGuard.getJson(
                        window.location.origin + `${APP_ROOT}/programmes/verifquart/${encodeURIComponent(typgare)}`,
                        'verifquart-' + typgare,
                        (httptypequart) => 
                    {
                        const donqua = JSON.parse(httptypequart.responseText);
                        if (donqua == '') {
                            document.querySelector('#quartier').options.length = 1;
                        }
                        else{
                            if (Object.entries(donqua).length >= 1) {
                                            
                                for (let key in Object.entries(donqua)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${donqua[key].nom_quartier}`;
                                    opt.innerHTML = `${donqua[key].nom_quartier}`;
                                    document.querySelector('#quartier').add(opt);
                                }
                            } else {
                                document.querySelector('#quartier').options.length = 1;
                            }
                        }
                        

                    });
            };
            
            let da = document.querySelector('#date_depheure');
            if (da !== null){
                da.onchange = () => 
                {
                    
                    document.querySelector('#hdepart').options.length = 1;
                    document.querySelector('#psieges').options.length = 1;
                    document.querySelector('#hdepartitine').options.length = 1;
                    document.querySelector('#psiegesitines').options.length = 1;
                    document.querySelector('#idcheminsheur').options.length = 1;
                    //document.querySelector('#lignesitineraire').value = '';
                    document.querySelector('#transitedepargare1').options.length = 1;
                    document.querySelector('#transitedepargare2').options.length = 1;
                    document.querySelector('#transitedepargare3').options.length = 1;
                    document.querySelector('#transitedepargare4').options.length = 1;
                    document.querySelector('#idchemins').options.length = 1;
                    document.querySelector('#idchemins1').options.length = 1;
                    document.querySelector('#idchemins2').options.length = 1;
                    document.querySelector('#idcompg').value = '';
                    document.querySelector('#idcompg1').value = '';
                    document.querySelector('#idcompg2').value = '';
                    document.querySelector('#idcompg3').value = '';
                        
                    document.querySelector('#psiegesitines1').options.length = 1;
                    document.querySelector('#idcheminsheur1').options.length = 1;
                    document.querySelector('#psiegesitines2').options.length = 1;
                    document.querySelector('#idcheminsheur2').options.length = 1;
                    document.querySelector('#psiegesitines3').options.length = 1;
                    document.querySelector('#quartier1').options.length = 1;
                    document.querySelector('#quartier2').options.length = 1;
                    document.querySelector('#quartier3').options.length = 1;
                    

                    let httpRequetes;
                    
                    if (window.XMLHttpRequest) {
                        httpRequetes = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpRequetes = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                        var depa = document.querySelector('#depargare').value;
                        var arrpa = document.querySelector('#arrsgare').value;
                        var arr1 = arrpa.split('/');
                        var arr = arr1[0];
                        var arr2 = arr1[1];
                        
                        var datedepart = document.querySelector('#date_depheure').value;
                        var dateactu = document.querySelector('#actu').value;
                                         
                        var post_lhdep = depa.split('/');
                        var seltdep = post_lhdep[0];
                        var sougid = post_lhdep[1];
                        if(datedepart >= dateactu)
                        {
                            
                            httpRequetes.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheure/${seltdep}-${arr}/${datedepart}`, true);
                            httpRequetes.onload = () => {
                                const dataAxe = JSON.parse(httpRequetes.responseText);
                                
                                    if (dataAxe == '') {
                                        
                                        document.querySelector('#smsdt').style.display = 'none';
                                        document.querySelector('#date_depheure').style.color = "black";
                                        document.querySelector('#date_depheure').style.border = "1px solid";
                                        //on verifit pour voir si elle n'a pas d'itineraire
                                        let httpRequestitine;
                                        httpRequestitine = new XMLHttpRequest();
                                        httpRequestitine.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifitine/${seltdep}-${arr}`, true);
                                        httpRequestitine.onload = () => {
                                                const donitines = JSON.parse(httpRequestitine.responseText);
                                                    if(donitines === null)
                                                    {
                                                        document.querySelector('#depitin1').style.display = 'none';
                                                        document.querySelector('#depargareitine1').style.display = 'none';
                                                        document.querySelector('#iddeptrans1').style.display = 'none';
                                                        document.querySelector('#transitedepargare1').style.display = 'none';
                                                        document.querySelector('#iddeptrans2').style.display = 'none';
                                                        document.querySelector('#transitedepargare2').style.display = 'none';
                                                        document.querySelector('#iddeptrans3').style.display = 'none';
                                                        document.querySelector('#transitedepargare3').style.display = 'none';
                                                        document.querySelector('#iddeptrans4').style.display = 'none';
                                                        document.querySelector('#transitedepargare4').style.display = 'none';
                                                        document.querySelector('#arritin1').style.display = 'none';
                                                        document.querySelector('#arrsgareitine1').style.display = 'none';
                                                        document.querySelector('#arritin1').style.display = 'none';
                                                        document.querySelector('#arrsgareitine1').style.display = 'none';
                                                        document.querySelector('#heureitin1').style.display = 'none';
                                                        document.querySelector('#hdepartitine1').style.display = 'none';
                                                        document.querySelector('#lignesitineraire').style.display = 'none';
                                                        document.querySelector('#ligne1').style.display = 'none';
                                                        document.querySelector('#siegitine1').style.display = 'none';
                                                        document.querySelector('#psiegesitines1').style.display = 'none';
                                                        document.querySelector('#depitin2').style.display = 'none';
                                                        document.querySelector('#depargareitine2').style.display = 'none';
                                                        document.querySelector('#arritin2').style.display = 'none';
                                                        document.querySelector('#arrsgareitine2').style.display = 'none';
                                                        document.querySelector('#heureitin2').style.display = 'none';
                                                        document.querySelector('#hdepartitine2').style.display = 'none';
                                                        document.querySelector('#siegitine2').style.display = 'none';
                                                        document.querySelector('#psiegesitines2').style.display = 'none';
                                                        document.querySelector('#depitin3').style.display = 'none';
                                                        document.querySelector('#depargareitine3').style.display = 'none';
                                                        document.querySelector('#arritin3').style.display = 'none';
                                                        document.querySelector('#arrsgareitine3').style.display = 'none';
                                                        document.querySelector('#heureitin3').style.display = 'none';
                                                        document.querySelector('#hdepartitine3').style.display = 'none';
                                                        document.querySelector('#siegitine3').style.display = 'none';
                                                        document.querySelector('#psiegesitines3').style.display = 'none';
                                                        document.querySelector('#quartier1').style.display = 'none';
                                                        document.querySelector('#quartier2').style.display = 'none';
                                                        document.querySelector('#idquart1').style.display = 'none';
                                                        document.querySelector('#idquart2').style.display = 'none';
                                                        document.querySelector('#idquart3').style.display = 'none';

                                                        document.querySelector('#tran').style.display = 'none';
                                                        document.querySelector('#heureitin').style.display = 'none';
                                                        document.querySelector('#hdepartitine').style.display = 'none';
                                                        document.querySelector('#siegitine').style.display = 'none';
                                                        document.querySelector('#psiegesitines').style.display = 'none';
                                                        document.querySelector('#hrid').style.display = 'block';
                                                        document.querySelector('#hdepart').style.display = 'block';
                                                        document.querySelector('#sigid').style.display = 'block';
                                                        document.querySelector('#psieges').style.display = 'block';
                                                        document.querySelector('#iddep').style.display = 'block';
                                                        document.querySelector('#depargare').style.display = 'block';
                                                        document.querySelector('#arrid').style.display = 'block';
                                                        document.querySelector('#arrsgare').style.display = 'block';

                                                    }
                                                    else
                                                    {
                                                        if (Object.entries(donitines).length >= 1) 
                                                        {
                                                            var i = Object.entries(donitines).length;
                                                            
                                                            for (let key in Object.entries(donitines)) 
                                                            {
                                                                
                                                                document.querySelector('#nbrtrans').value = Object.entries(donitines).length;;
                                                                if(i === 2){
                                                                    document.querySelector('#arritin1').style.display = 'block';
                                                                    document.querySelector('#idchemins').style.display = 'block';
                                                                    document.querySelector('#heureitin1').style.display = 'block';
                                                                    document.querySelector('#idcheminsheur').style.display = 'block';
                                                                    document.querySelector('#siegitine1').style.display = 'block';
                                                                    document.querySelector('#psiegesitines1').style.display = 'block';
                                                                    document.querySelector('#quartier1').style.display = 'block';
                                                                    document.querySelector('#idquart1').style.display = 'block';
                                                                    document.querySelector('#iddeptrans1').style.display = 'block';
                                                                    document.querySelector('#transitedepargare1').style.display = 'block';
                                                                    document.querySelector('#iddeptrans2').style.display = 'block';
                                                                    document.querySelector('#transitedepargare2').style.display = 'block';
                                                                    
                                                                }
                                                                
                                                                if(i === 3){
                                                                    document.querySelector('#iddeptrans1').style.display = 'block';
                                                                    document.querySelector('#transitedepargare1').style.display = 'block';
                                                                    document.querySelector('#iddeptrans2').style.display = 'block';
                                                                    document.querySelector('#transitedepargare2').style.display = 'block';
                                                                    document.querySelector('#iddeptrans3').style.display = 'block';
                                                                    document.querySelector('#transitedepargare3').style.display = 'block';
                                                                    document.querySelector('#arritin1').style.display = 'block';
                                                                    document.querySelector('#idchemins').style.display = 'block';
                                                                    document.querySelector('#heureitin1').style.display = 'block';
                                                                    document.querySelector('#idcheminsheur').style.display = 'block';
                                                                    document.querySelector('#siegitine1').style.display = 'block';
                                                                    document.querySelector('#psiegesitines1').style.display = 'block';
                                                                    document.querySelector('#idquart1').style.display = 'block';
                                                                    document.querySelector('#idquart2').style.display = 'block';

                                                                    document.querySelector('#arritin2').style.display = 'block';
                                                                    document.querySelector('#idchemins1').style.display = 'block';
                                                                    document.querySelector('#heureitin2').style.display = 'block';
                                                                    document.querySelector('#idcheminsheur1').style.display = 'block';
                                                                    document.querySelector('#siegitine2').style.display = 'block';
                                                                    document.querySelector('#psiegesitines2').style.display = 'block';
                                                                    document.querySelector('#quartier1').style.display = 'block';
                                                                    document.querySelector('#quartier2').style.display = 'block';
                                                                }if(i === 4){
                                                                    
                                                                    document.querySelector('#iddeptrans1').style.display = 'block';
                                                                    document.querySelector('#transitedepargare1').style.display = 'block';
                                                                    document.querySelector('#iddeptrans2').style.display = 'block';
                                                                    document.querySelector('#transitedepargare2').style.display = 'block';
                                                                    document.querySelector('#iddeptrans3').style.display = 'block';
                                                                    document.querySelector('#transitedepargare3').style.display = 'block';
                                                                    document.querySelector('#iddeptrans4').style.display = 'block';
                                                                    document.querySelector('#transitedepargare4').style.display = 'block';
                                                                    document.querySelector('#arritin1').style.display = 'block';
                                                                    document.querySelector('#idchemins').style.display = 'block';
                                                                    document.querySelector('#heureitin1').style.display = 'block';
                                                                    document.querySelector('#idcheminsheur').style.display = 'block';
                                                                    document.querySelector('#siegitine1').style.display = 'block';
                                                                    document.querySelector('#psiegesitines1').style.display = 'block';
                                                                    document.querySelector('#arritin2').style.display = 'block';
                                                                    document.querySelector('#idchemins1').style.display = 'block';
                                                                    document.querySelector('#heureitin2').style.display = 'block';
                                                                    document.querySelector('#idcheminsheur1').style.display = 'block';
                                                                    document.querySelector('#siegitine2').style.display = 'block';
                                                                    document.querySelector('#psiegesitines2').style.display = 'block';
                                                                    document.querySelector('#arritin3').style.display = 'block';
                                                                    document.querySelector('#idchemins2').style.display = 'block';
                                                                    document.querySelector('#heureitin3').style.display = 'block';
                                                                    document.querySelector('#idcheminsheur2').style.display = 'block';
                                                                    document.querySelector('#siegitine3').style.display = 'block';
                                                                    document.querySelector('#psiegesitines3').style.display = 'block';
                                                                    document.querySelector('#quartier1').style.display = 'block';
                                                                    document.querySelector('#quartier2').style.display = 'block';
                                                                    document.querySelector('#quartier3').style.display = 'block';    
                                                                    document.querySelector('#idquart1').style.display = 'block';
                                                                    document.querySelector('#idquart2').style.display = 'block';
                                                                    document.querySelector('#idquart3').style.display = 'block';

                                                                }
                                                                document.querySelector('#tran').style.display = 'block';
                                                                document.querySelector('#heureitin').style.display = 'block';
                                                                document.querySelector('#hdepartitine').style.display = 'block';
                                                                document.querySelector('#lignesitineraire').style.display = 'block';
                                                                document.querySelector('#ligne1').style.display = 'block';
                                                                document.querySelector('#siegitine').style.display = 'block';
                                                                document.querySelector('#psiegesitines').style.display = 'block';
                                                                document.querySelector('#hrid').style.display = 'none';
                                                                document.querySelector('#hdepart').style.display = 'none';
                                                                document.querySelector('#sigid').style.display = 'none';
                                                                document.querySelector('#psieges').style.display = 'none';
                                                                document.querySelector('#iddep').style.display = 'none';
                                                                document.querySelector('#depargare').style.display = 'none';
                                                                document.querySelector('#arrid').style.display = 'none';
                                                                document.querySelector('#arrsgare').style.display = 'none';


                                                                document.querySelector('#itinecode').value = `${donitines[0].code_itineraires}`;

                                                                document.querySelector('#idcompg').value = `${donitines[0].id_compaga}`;
                                                                document.querySelector('#lignetineraire').value = `${donitines[0].nom_itineraires}`;
                                                            }
                                                
                                                            if(i === 2)
                                                            {
                                                                let opt = document.createElement('option');
                                                                opt.value = `${donitines[1].code_itineraires}`;
                                                                opt.innerHTML = `${donitines[1].nom_itineraires}`;
                                                                document.querySelector('#idchemins').add(opt);

                                                                document.querySelector('#lignesitineraire').value = `${donitines[0].nom_itineraires}`;
                                                                document.querySelector('#itinecodes').value = `${donitines[0].id_lignes}`;
                                                                document.querySelector('#idcompg').value = `${donitines[0].id_compaga}`;
                                                                document.querySelector('#idcompg1').value = `${donitines[1].id_compaga}`;
                                                                var typgare1 = document.querySelector('#itinecode').value;
                                                                var post_typgare1 = typgare1.split('-');
                                                                var seltypgare1 = post_typgare1[0];
                                                                var typgaresel = post_typgare1[1];
                                                                    let httptypequart1;
                                                                    httptypequart1 = new XMLHttpRequest();
                                                                    
                                                                    httptypequart1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgaresel}`, true);
                                                                    httptypequart1.onload = () => 
                                                                    {
                                                                        const donqua1 = JSON.parse(httptypequart1.responseText);
                                                                        if (donqua1 == '') {
                                                                            document.querySelector('#quartier1').options.length = 1;
                                                                        }
                                                                        else{
                                                                            if (Object.entries(donqua1).length >= 1) {
                                                                                            
                                                                                for (let key in Object.entries(donqua1)) {
                                                                                    let optq = document.createElement('option');
                                                                                    optq.value = `${donqua1[key].nom_quartier}`;
                                                                                    optq.innerHTML = `${donqua1[key].nom_quartier}`;
                                                                                    document.querySelector('#quartier1').add(optq);
                                                                                }
                                                                            } else {
                                                                                document.querySelector('#quartier1').options.length = 1;
                                                                            }
                                                                        }
                                                                        

                                                                    };
                                                                    httptypequart1.setRequestHeader('Content-Type', 'application/json');
                                                                    httptypequart1.send();

                                                                        let httptypequartitin;
                                                                        httptypequartitin = new XMLHttpRequest();
                                                                        var itinpro = document.querySelector('#itinecode').value;
                                                                        var datedepart = document.querySelector('#date_depheure').value;
                                                                        httptypequartitin.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheureitine/${itinpro}/${datedepart}`, true);
                                                                    httptypequartitin.onload = () => 
                                                                    {
                                                                        const infositin = JSON.parse(httptypequartitin.responseText);
                                                                        if (infositin == null) 
                                                                        {

                                                                        }
                                                                        if (Object.entries(infositin).length >= 1) 
                                                                        {
                                                                                
                                                                            
                                                                            for (let key in Object.entries(infositin)) {
                                                                                    let opt = document.createElement('option');
                                                                                    opt.value = `${infositin[key].id_ligneheure}/${infositin[key].heure}`;
                                                                                    opt.innerHTML = `${infositin[key].heure}`;
                                                                                    document.querySelector('#hdepartitine').add(opt);
                                                                                }
                                                                        } else {
                                                                            document.querySelector('#hdepartitine').options.length = 1;
                                                                        }
                                                                    };
                                                                    httptypequartitin.setRequestHeader('Content-Type', 'application/json');
                                                                    httptypequartitin.send();
                                                                let hrdepartine = document.querySelector('#hdepartitine');
                                                                if (hrdepartine !== null) {
                                                                    hrdepartine.onchange = () => 
                                                                    {   
                                                                        
                                                                        const httpsousgare = new XMLHttpRequest();
                                                                        httpsousgare.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${seltypgare1}`, true);
                                                                        httpsousgare.onload = () => 
                                                                        {
                                                                            const donsousg = JSON.parse(httpsousgare.responseText);
                                                                            console.debug(`${typeof donsousg}-${donsousg.attributes}`, console.memory);
                                                                            if (Object.entries(donsousg).length >= 1) {
                                                                                for (let key in Object.entries(donsousg)) 
                                                                                {
                                                                                    let opt = document.createElement('option');
                                                                                    opt.value = `${donsousg[key].idsousgare}`;
                                                                                    opt.innerHTML = `${donsousg[key].nomsousgare}`;
                                                                                    document.querySelector('#transitedepargare1').add(opt);
                        
                                                                                }
                                                                            }
                                                                        };
                                                                        httpsousgare.setRequestHeader('Content-Type', 'application/json');
                                                                        httpsousgare.send();

                                                                        document.querySelector('#psiegesitines').options.length = 1;
                                                                        const httpRequestit = new XMLHttpRequest();
                                                                        const seleitine = document.querySelector('#hdepartitine')
                                                                            .options[document.querySelector('#hdepartitine').options.selectedIndex].value;

                                                                            var post_lhitine = seleitine.split('/');
                                                                            var selitine = post_lhitine[0];
                                                                            var lhselitine = post_lhitine[1];

                                                                            const dpt_dateitine = document.querySelector('#date_depheure').value;
                                                                            var itinproit = document.querySelector('#itinecode').value;
                                                                        httpRequestit.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifprog/${itinproit}/${dpt_dateitine}/${selitine}`, true);
                                                                        httpRequestit.onload = () => 
                                                                        {
                                                                            const donit = JSON.parse(httpRequestit.responseText);
                                                                                console.debug(`${typeof donit} - ${donit.attributes}`, console.memory);

                                                                                if (donit == '') 
                                                                                {
                                                                                    
                                                                                        let opt = document.createElement('option');
                                                                                        opt.value = '';                                                             
                                                                                    
                                                                                } 
                                                                                else 
                                                                                {       
                                                                                    if (Object.entries(donit).length >= 1) {
                                                                                        for (let key in Object.entries(donit)) {
                                                                                            document.querySelector('#programtrans').value = `${donit[key].code_progr}`;
                                                                                            document.querySelector('#tarifattrib').value = `${donit[key].typetarif}`;
                                                                                            document.querySelector('#dateprtrans').value = `${donit[key].date_progr}`;
                                                                                            document.querySelector('#deplignetrans').value = `${donit[key].gareidentif}`;
                                                                                            document.querySelector('#intertrans1').value = `${donit[key].intervalle1}`;
                                                                                            document.querySelector('#intertrans2').value = `${donit[key].intervalle2}`;
                                                                                            document.querySelector('#ligntrans').value = `${donit[key].ident_ligne}`;
                                                                                            document.querySelector('#nomitintrans').value = `${donit[key].nom_ligne}`;
                                                                                            document.querySelector('#hertrans').value = `${donit[key].heure}`;
                                                                                            document.querySelector('#catetrans').value = `${donit[key].categori}`;
                                                                                                
                                                                                        }
                                                                                    } 
                                                                                    
                                                                                    const httpPrixit = new XMLHttpRequest();
                                                                                    const seleitine = document.querySelector('#hdepartitine')
                                                                                    .options[document.querySelector('#hdepartitine').options.selectedIndex].value;

                                                                                    var post_lhitine = seleitine.split('/');
                                                                                    var selitine = post_lhitine[0];
                                                                                    var lhselitine = post_lhitine[1];
                                                                                    var tfbs = document.querySelector('#tarifattrib').value;
                                                                                    httpPrixit.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifpriprg/${selitine}/${tfbs}`, true);
                                                                                    httpPrixit.onload = () => 
                                                                                    {

                                                                                        const donprixit = JSON.parse(httpPrixit.responseText);
                                                                                        console.debug(`${typeof donprixit}-${donprixit.attributes}`, console.memory);
                                                                                        if (Object.entries(donprixit).length >= 1) {
                                                                                            for (let key in Object.entries(donprixit)) 
                                                                                            {
                                                                                                document.querySelector('#prix_axetrans').value = `${donprixit[key].prix}`;
                                    
                                                                                            }
                                                                                        }
                                                                                    };
                                                                                    httpPrixit.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpPrixit.send();
                                                                                    
                                                                                    
                                                                                    
                                                                                    const httpRequetteit = new XMLHttpRequest();
                                                                                    const cdprogit = document.querySelector('#programtrans').value;
                                                                                    const dbit = document.querySelector('#intertrans1').value;
                                                                                    const fnit = document.querySelector('#intertrans2').value;
                                                                                    const lgit = document.querySelector('#nomitintrans').value;
                                                                                    const timit = document.querySelector('#hertrans').value;
                                                                                    const dpt_dateitine = document.querySelector('#date_depheure').value;
                                                                                        httpRequetteit.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${cdprogit}/${dpt_dateitine}/${lgit}/${timit}/${dbit}/${fnit}`, true);
                                                                                    httpRequetteit.onload = () => {
                                                                                        const dattait = JSON.parse(httpRequetteit.responseText);
                                                                                        console.debug(`${typeof dattait} - ${dattait.attributes}`, console.memory);
                                                                                        if (Object.entries(dattait).length >= 1) {
                                                                                            for (let key in Object.entries(dattait)) {
                                                                                                
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dattait[key].siege_num}`;
                                                                                                opt.innerHTML = `${dattait[key].siege_num}`;
                                                                                                document.querySelector('#psiegesitines').add(opt);
                                                                                                
                                                                                            }
                                                                                            
                                                                                        } else {
                                                                                            document.querySelector('#psiegesitines').options.length = 1;
                                                                                        }
                                                                                    };
                                                                                    httpRequetteit.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpRequetteit.send();

                                                                                }  
                                                                                
                                                                        };
                                                                        httpRequestit.setRequestHeader('Content-Type', 'application/json');
                                                                        httpRequestit.send();
                                                                         
                                                                    };
                                                                    
                                                            
                                                                }
                                                                progsiegestrans = document.querySelector('#psiegesitines');
                                                                if (progsiegestrans !== null) {
                                                                    progsiegestrans.onchange = () => 
                                                                    {

                                                                        gareidentiftrans = document.querySelector('#deplignetrans').value;
                                                                            const httpsousgare = new XMLHttpRequest();
                                                                            httpsousgare.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftrans}`, true);
                                                                            httpsousgare.onload = () => 
                                                                            {
                                                                                const donsousg = JSON.parse(httpsousgare.responseText);
                                                                                console.debug(`${typeof donsousg}-${donsousg.attributes}`, console.memory);
                                                                                if (Object.entries(donsousg).length >= 1) {
                                                                                    for (let key in Object.entries(donsousg)) 
                                                                                    {
                                                                                        let opt = document.createElement('option');
                                                                                        opt.value = `${donsousg[key].idsousgare}`;
                                                                                        opt.innerHTML = `${donsousg[key].nomsousgare}`;
                                                                                        document.querySelector('#transitedepargare1').add(opt);
                            
                                                                                    }
                                                                                }
                                                                            };
                                                                            httpsousgare.setRequestHeader('Content-Type', 'application/json');
                                                                            httpsousgare.send();
                                                                        let httpSiegestrans;
                                                                        httpSiegestrans = new XMLHttpRequest();
                                                                        const sigstrans = document.querySelector('#psiegesitines')
                                                                        .options[document.querySelector('#psiegesitines').options.selectedIndex].value;
                                                                        const prostrans = document.querySelector('#programtrans').value;

                                                                        httpSiegestrans.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${prostrans}/${sigstrans}`, true);
                                                                        httpSiegestrans.onload = () => 
                                                                        {
                                                                            const donsgetrans = JSON.parse(httpSiegestrans.responseText);
                                                                            console.debug(`${typeof donsgetrans} - ${donsgetrans.attributes}`, console.memory);
                                                                            if(donsgetrans == '')
                                                                            {
                                                                                let httpSiegstrans;
                                                                                httpSiegstrans = new XMLHttpRequest();

                                                                                httpSiegstrans.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${prostrans}/${sigstrans}`, true);
                                                                                httpSiegstrans.onload = () => 
                                                                                {
                                                                                    const dongtrans = JSON.parse(httpSiegstrans.responseText);
                                                                                    document.querySelector('#mess').style.display = 'none';
                                                                                    if (Object.entries(dongtrans).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtrans)) {
                                                                                                document.querySelector('#idtampotrans').value = `${dongtrans[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselecttrans').value = `${dongtrans[key].numsieg}`;
                                                                                            }
                                                                                        }
                                                                                };
                                                                                httpSiegstrans.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSiegstrans.send();
                                                                            }
                                                                            else {
                                                                                document.querySelector('#psiegesitines').value = '';     
                                                                                if (Object.entries(donsgetrans).length >= 1)
                                                                                {
                                                                                    for (let key in Object.entries(donsgetrans)) {
                                                                                        document.querySelector('#idtampotrans').value = `${donsgetrans[key].idtamp}`;                    
                                                                                        document.querySelector('#siegselecttrans').value = `${donsgetrans[key].numsieg}`;
                                                                                    }

                                                                                }
                                                                                document.querySelector('#mess').style.display = 'block';
                                                                                document.querySelector('#erreurMess').innerHTML = `Siege déjà utilisé.`;
                                                                            }
                                                                        };
                                                                        httpSiegestrans.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegestrans.send();

                                                                    
                                                                    };
                                                                }

                                                                let progchemin = document.querySelector('#idchemins');
                                                                if (progchemin !== null) 
                                                                {
                                                                    progchemin.onchange = () => 
                                                                    {
                                                                        document.querySelector('#idcheminsheur').options.length = 1;
                                                                        document.querySelector('#psiegesitines1').options.length = 1;

                                                                        let httpSiegeschemin;
                                                                        httpSiegeschemin = new XMLHttpRequest();
                                                                        
                                                                        const prostranschemin = document.querySelector('#idchemins')
                                                                        .options[document.querySelector('#idchemins').options.selectedIndex].value;

                                                                        var post_typgare2 = prostranschemin.split('-');
                                                                        var seltypgare2 = post_typgare2[0];
                                                                        var typgaresel1 = post_typgare2[1];
                                                                        var tfbs = document.querySelector('#tarifattrib').value;
                                                                        var datedepart = document.querySelector('#date_depheure').value;
                                                                        httpSiegeschemin.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemintr/${prostranschemin}/${datedepart}/${tfbs}`, true);
                                                                        httpSiegeschemin.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschem = JSON.parse(httpSiegeschemin.responseText);
                                                                                    if (Object.entries(dongtranschem).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtranschem)) {
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dongtranschem[key].code_progr}/${dongtranschem[key].intervalle1}/${dongtranschem[key].intervalle2}/${dongtranschem[key].id_ligneheure}/${dongtranschem[key].prix}`;
                                                                                                opt.innerHTML = `${dongtranschem[key].heure}/${dongtranschem[key].date_progr}`;
                                                                                                document.querySelector('#idcheminsheur').add(opt);
                                                                                            }
                                                                                        }
                                                                        };
                                                                        httpSiegeschemin.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegeschemin.send();

                                                                    };
                                                                        let prochemintra = document.querySelector('#idcheminsheur');
                                                                    if (prochemintra !== null)
                                                                        prochemintra.onchange = () => 
                                                                        {  
                                                                            const httpPrixittransite = new XMLHttpRequest();

                                                                            document.querySelector('#psiegesitines1').options.length = 1;

                                                                                const transselitine = document.querySelector('#idcheminsheur')
                                                                            .options[document.querySelector('#idcheminsheur').options.selectedIndex].value;
                                                                                var post_trans = transselitine.split('/');
                                                                            var itinetras = post_trans[0];
                                                                            var dbitra = post_trans[1];
                                                                            var fnitra = post_trans[2];
                                                                            var lhertra = post_trans[3];
                                                                            var prixtra = post_trans[4];

                                                                                httpPrixittransite.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetras}`, true);
                                                                                httpPrixittransite.onload = () => 
                                                                                {
                                                                                    const donprixitran = JSON.parse(httpPrixittransite.responseText);
                                                                                    console.debug(`${typeof donprixitran}-${donprixitran.attributes}`, console.memory);
                                                                                    if (Object.entries(donprixitran).length >= 1) {
                                                                                        for (let key in Object.entries(donprixitran)) 
                                                                                        {
                                                                                            document.querySelector('#prix_axetransit').value = `${prixtra}`;
                                                                                            document.querySelector('#catetransit').value = `${donprixitran[key].categori}`;
                                                                                            document.querySelector('#gidtrans').value =  `${donprixitran[key].gareidentif}`;
                                                                                            document.querySelector('#nomitintrans1').value = `${donprixitran[key].nom_ligne}`;
                                                                                            document.querySelector('#ligntrans1').value = `${donprixitran[key].ident_ligne}`;

                                                                                        }
                                                                                    }
                                                                                };
                                                                                httpPrixittransite.setRequestHeader('Content-Type', 'application/json');
                                                                                httpPrixittransite.send();
                                                                                
                                                                                      
                                                                                    
                                                                                const httpRequetteitra = new XMLHttpRequest();
                                                                        
                                                                                    httpRequetteitra.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetras}/${dbitra}/${fnitra}`, true);
                                                                                httpRequetteitra.onload = () => {
                                                                                    const dattaitra = JSON.parse(httpRequetteitra.responseText);
                                                                                    console.debug(`${typeof dattaitra} - ${dattaitra.attributes}`, console.memory);
                                                                                    if (Object.entries(dattaitra).length >= 1) {
                                                                                        for (let key in Object.entries(dattaitra)) {
                                                                                            
                                                                                            let opt = document.createElement('option');
                                                                                            opt.value = `${dattaitra[key].siege_num}`;
                                                                                            opt.innerHTML = `${dattaitra[key].siege_num}`;
                                                                                            document.querySelector('#psiegesitines1').add(opt);
                                                                                            
                                                                                        }
                                                                                        
                                                                                    } else {
                                                                                        document.querySelector('#psiegesitines1').options.length = 1;
                                                                                    }
                                                                                };
                                                                                httpRequetteitra.setRequestHeader('Content-Type', 'application/json');
                                                                                httpRequetteitra.send();
                                                                        };

                                                                        progsieges1 = document.querySelector('#psiegesitines1');
                                                                        if (progsieges1 !== null) 
                                                                        {
                                                                            progsieges1.onchange = () => 
                                                                            {
                                                                                

                                                                                const transselitine1 = document.querySelector('#idcheminsheur')
                                                                                .options[document.querySelector('#idcheminsheur').options.selectedIndex].value;
                                                                                var post_trans1 = transselitine1.split('/');
                                                                                var itinetras1 = post_trans1[0];
                                                                                
                                                                                gareidentiftrans2 = document.querySelector('#gidtrans').value;
                                                                                const httpsousgare1 = new XMLHttpRequest();
                                                                                httpsousgare1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftrans2}`, true);
                                                                                httpsousgare1.onload = () => 
                                                                                {
                                                                                    const donsousg1 = JSON.parse(httpsousgare1.responseText);
                                                                                    console.debug(`${typeof donsousg1}-${donsousg1.attributes}`, console.memory);
                                                                                    if (Object.entries(donsousg1).length >= 1) {
                                                                                        for (let key in Object.entries(donsousg1)) 
                                                                                        {
                                                                                            let opt = document.createElement('option');
                                                                                            opt.value = `${donsousg1[key].idsousgare}`;
                                                                                            opt.innerHTML = `${donsousg1[key].nomsousgare}`;
                                                                                            document.querySelector('#transitedepargare2').add(opt);
                                
                                                                                        }
                                                                                    }
                                                                                };
                                                                                httpsousgare1.setRequestHeader('Content-Type', 'application/json');
                                                                                httpsousgare1.send();
                                                                              
                                                                                let httpSieges1;
                                                                                httpSieges1 = new XMLHttpRequest();
                                                                                const sigs1 = document.querySelector('#psiegesitines1')
                                                                                .options[document.querySelector('#psiegesitines1').options.selectedIndex].value;
                                                                                //const pros1 = document.querySelector('#program').value;

                                                                                httpSieges1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetras1}/${sigs1}`, true);
                                                                                httpSieges1.onload = () => 
                                                                                {
                                                                                    const donsge1 = JSON.parse(httpSieges1.responseText);
                                                                                    console.debug(`${typeof donsge1} - ${donsge1.attributes}`, console.memory);
                                                                                    if(donsge1 == '')
                                                                                    {
                                                                                        let httpSiegs1;
                                                                                        httpSiegs1 = new XMLHttpRequest();

                                                                                        httpSiegs1.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetras1}/${sigs1}`, true);
                                                                                        httpSiegs1.onload = () => 
                                                                                        {
                                                                                            const dong1 = JSON.parse(httpSiegs1.responseText);
                                                                                            document.querySelector('#mess').style.display = 'none';
                                                                                            if (Object.entries(dong1).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(dong1)) {
                                                                                                        document.querySelector('#idtampo1').value = `${dong1[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselect1').value = `${dong1[key].numsieg}`;
                                                                                                    }
                                                                                                }
                                                                                        };
                                                                                        httpSiegs1.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegs1.send();
                                                                                    }
                                                                                    else {
                                                                                        document.querySelector('#psiegesitines1').value = '';     
                                                                                        if (Object.entries(donsge1).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(donsge1)) {
                                                                                                document.querySelector('#idtampo1').value = `${donsge1[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselect1').value = `${donsge1[key].numsieg}`;
                                                                                            }

                                                                                        }
                                                                                        document.querySelector('#mess').style.display = 'block';
                                                                                        document.querySelector('#erreurMess').innerHTML = `Siege déjà utilisé.`;
                                                                                    }
                                                                                };
                                                                                httpSieges1.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSieges1.send();

                                                                            };
                                                                        }
                                                                }               
                                                            }
                                                            //second itineraire
                                                            if(i === 3)
                                                            {

                                                                
                                                                let opt = document.createElement('option');
                                                                opt.value = `${donitines[1].code_itineraires}`;
                                                                opt.innerHTML = `${donitines[1].nom_itineraires}`;
                                                                
                                                                document.querySelector('#idchemins').add(opt);

                                                                document.querySelector('#lignesitineraire').value = `${donitines[0].nom_itineraires}`;
                                                                document.querySelector('#itinecodes').value = `${donitines[0].id_lignes}`;
                                                               document.querySelector('#idcompg').value = `${donitines[0].id_compaga}`;

                                                                let opt1 = document.createElement('option');
                                                                opt1.value = `${donitines[2].code_itineraires}`;
                                                                opt1.innerHTML = `${donitines[2].nom_itineraires}`;
                                                                document.querySelector('#idchemins1').add(opt1);

                                                                document.querySelector('#idcompg1').value = `${donitines[1].id_compaga}`;
                                                                document.querySelector('#idcompg2').value = `${donitines[2].id_compaga}`;
                                                                var typgare1 = document.querySelector('#itinecode').value;
                                                                var post_typgare1 = typgare1.split('-');
                                                                var seltypgare1 = post_typgare1[0];
                                                                var typgaresel = post_typgare1[1];
                                                                    let httptypequart1;
                                                                    httptypequart1 = new XMLHttpRequest();
                                                                    
                                                                    httptypequart1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgaresel}`, true);
                                                                    httptypequart1.onload = () => 
                                                                    {
                                                                        const donqua1 = JSON.parse(httptypequart1.responseText);
                                                                        if (donqua1 == '') {
                                                                            document.querySelector('#quartier1').options.length = 1;
                                                                        }
                                                                        else{
                                                                            if (Object.entries(donqua1).length >= 1) {
                                                                                            
                                                                                for (let key in Object.entries(donqua1)) {
                                                                                    let optq = document.createElement('option');
                                                                                    optq.value = `${donqua1[key].nom_quartier}`;
                                                                                    optq.innerHTML = `${donqua1[key].nom_quartier}`;
                                                                                    document.querySelector('#quartier1').add(optq);
                                                                                }
                                                                            } else {
                                                                                document.querySelector('#quartier1').options.length = 1;
                                                                            }
                                                                        }
                                                                        

                                                                    };
                                                                    httptypequart1.setRequestHeader('Content-Type', 'application/json');
                                                                    httptypequart1.send();


                                                                        let httptypequartitin1;
                                                                        httptypequartitin1 = new XMLHttpRequest();
                                                                        var itinpro1 = document.querySelector('#itinecode').value;
                                                                        var datedepart = document.querySelector('#date_depheure').value;
                                                                        httptypequartitin1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheureitine/${itinpro1}/${datedepart}`, true);
                                                                    httptypequartitin1.onload = () => 
                                                                    {
                                                                        const infositin1 = JSON.parse(httptypequartitin1.responseText);
                                                                        if (infositin1 == null) 
                                                                        {


                                                                        }
                                                                        if (Object.entries(infositin1).length >= 1) 
                                                                        {
                                                                                
                                                                            
                                                                            for (let key in Object.entries(infositin1)) {
                                                                                    let opt = document.createElement('option');
                                                                                    opt.value = `${infositin1[key].id_ligneheure}/${infositin1[key].heure}`;
                                                                                    opt.innerHTML = `${infositin1[key].heure}`;
                                                                                    document.querySelector('#hdepartitine').add(opt);
                                                                                }
                                                                        } else {
                                                                            document.querySelector('#hdepartitine').options.length = 1;
                                                                        }
                                                                    };
                                                                    httptypequartitin1.setRequestHeader('Content-Type', 'application/json');
                                                                    httptypequartitin1.send();
                                                                let hrdepartine1 = document.querySelector('#hdepartitine');
                                                                if (hrdepartine1 !== null) {
                                                                    hrdepartine1.onchange = () => 
                                                                    {
                                                                        var tfbs1 = document.querySelector('#tarifattrib').value;
                                                                        document.querySelector('#psiegesitines').options.length = 1;
                                                                        const httpRequestit1 = new XMLHttpRequest();
                                                                        const seleitine1 = document.querySelector('#hdepartitine')
                                                                            .options[document.querySelector('#hdepartitine').options.selectedIndex].value;

                                                                            var post_lhitine1 = seleitine1.split('/');
                                                                            var selitine1 = post_lhitine1[0];
                                                                            var lhselitine1 = post_lhitine1[1];

                                                                            const dpt_dateitine1 = document.querySelector('#date_depheure').value;
                                                                            var itinproit1 = document.querySelector('#itinecode').value;
                                                                        httpRequestit1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifprog/${itinproit1}/${dpt_dateitine1}/${selitine1}`, true);
                                                                        httpRequestit1.onload = () => 
                                                                        {
                                                                            const donit1 = JSON.parse(httpRequestit1.responseText);
                                                                                console.debug(`${typeof donit1} - ${donit1.attributes}`, console.memory);

                                                                                if (donit1 == '') 
                                                                                {
                                                                                    
                                                                                        let opt = document.createElement('option');
                                                                                        opt.value = '';                                                             
                                                                                   
                                                                                    
                                                                                    
                                                                                } 
                                                                                else 
                                                                                {       
                                                                                    if (Object.entries(donit1).length >= 1) {
                                                                                        for (let key in Object.entries(donit1)) {
                                                                                            document.querySelector('#programtrans').value = `${donit1[key].code_progr}`;
                                                                                            document.querySelector('#dateprtrans').value = `${donit1[key].date_progr}`;
                                                                                            document.querySelector('#tarifattrib').value = `${donit1[key].typetarif}`;
                                                                                            document.querySelector('#deplignetrans').value = `${donit1[key].gareidentif}`;
                                                                                            document.querySelector('#intertrans1').value = `${donit1[key].intervalle1}`;
                                                                                            document.querySelector('#intertrans2').value = `${donit1[key].intervalle2}`;
                                                                                            document.querySelector('#ligntrans').value = `${donit1[key].ident_ligne}`;
                                                                                            document.querySelector('#nomitintrans').value = `${donit1[key].nom_ligne}`;
                                                                                            document.querySelector('#hertrans').value = `${donit1[key].heure}`;
                                                                                            document.querySelector('#catetrans').value = `${donit1[key].categori}`;

                                                                                        }
                                                                                    } 
                                                                                    
                                                                                    const httpPrixit = new XMLHttpRequest();
                                                                                    const seleitine = document.querySelector('#hdepartitine')
                                                                                    .options[document.querySelector('#hdepartitine').options.selectedIndex].value;

                                                                                    var post_lhitine = seleitine.split('/');
                                                                                    var selitine = post_lhitine[0];
                                                                                    var lhselitine = post_lhitine[1];
                                                                                    var tfbs2 = document.querySelector('#tarifattrib').value;
                                                                                    httpPrixit.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifpriprg/${selitine}/${tfbs2}`, true);
                                                                                    httpPrixit.onload = () => 
                                                                                    {
                                                                                        const donprixit = JSON.parse(httpPrixit.responseText);
                                                                                        console.debug(`${typeof donprixit}-${donprixit.attributes}`, console.memory);
                                                                                        if (Object.entries(donprixit).length >= 1) {
                                                                                            for (let key in Object.entries(donprixit)) 
                                                                                            {
                                                                                                document.querySelector('#prix_axetrans').value = `${donprixit[key].prix}`;
                                    
                                                                                            }
                                                                                        }
                                                                                    };
                                                                                    httpPrixit.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpPrixit.send();

                                                                                    

                                                                                    const httpRequetteit = new XMLHttpRequest();
                                                                                    const cdprogit = document.querySelector('#programtrans').value;
                                                                                    const dbit = document.querySelector('#intertrans1').value;
                                                                                    const fnit = document.querySelector('#intertrans2').value;
                                                                                    const lgit = document.querySelector('#nomitintrans').value;
                                                                                    const timit = document.querySelector('#hertrans').value;
                                                                                    const dpt_dateitine = document.querySelector('#date_depheure').value;
                                                                                        httpRequetteit.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${cdprogit}/${dpt_dateitine}/${lgit}/${timit}/${dbit}/${fnit}`, true);
                                                                                    httpRequetteit.onload = () => {
                                                                                        const dattait = JSON.parse(httpRequetteit.responseText);
                                                                                        console.debug(`${typeof dattait} - ${dattait.attributes}`, console.memory);
                                                                                        if (Object.entries(dattait).length >= 1) {
                                                                                            for (let key in Object.entries(dattait)) {
                                                                                                
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dattait[key].siege_num}`;
                                                                                                opt.innerHTML = `${dattait[key].siege_num}`;
                                                                                                document.querySelector('#psiegesitines').add(opt);
                                                                                                
                                                                                            }
                                                                                            
                                                                                        } else {
                                                                                            document.querySelector('#psiegesitines').options.length = 1;
                                                                                        }
                                                                                    };
                                                                                    httpRequetteit.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpRequetteit.send();

                                                                                }  
                                                                                
                                                                        };
                                                                        httpRequestit1.setRequestHeader('Content-Type', 'application/json');
                                                                        httpRequestit1.send();
                                                                         
                                                                    };
                                                                    
                                                            
                                                                }
                                                                let progsiegestrans = document.querySelector('#psiegesitines');
                                                                if (progsiegestrans !== null) {
                                                                    progsiegestrans.onchange = () => 
                                                                    {

                                                                        const gareidentiftrans1 = document.querySelector('#deplignetrans').value;
                                                                        const httpsousgare = new XMLHttpRequest();
                                                                        httpsousgare.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftrans1}`, true);
                                                                        httpsousgare.onload = () => 
                                                                        {
                                                                            const donsousg = JSON.parse(httpsousgare.responseText);
                                                                            console.debug(`${typeof donsousg}-${donsousg.attributes}`, console.memory);
                                                                            if (Object.entries(donsousg).length >= 1) {
                                                                                for (let key in Object.entries(donsousg)) 
                                                                                {
                                                                                    let opt = document.createElement('option');
                                                                                    opt.value = `${donsousg[key].idsousgare}`;
                                                                                    opt.innerHTML = `${donsousg[key].nomsousgare}`;
                                                                                    document.querySelector('#transitedepargare1').add(opt);
                        
                                                                                }
                                                                            }
                                                                        };
                                                                        httpsousgare.setRequestHeader('Content-Type', 'application/json');
                                                                        httpsousgare.send();
                                                                        let httpSiegestrans1;
                                                                        httpSiegestrans1 = new XMLHttpRequest();
                                                                        const sigstrans = document.querySelector('#psiegesitines')
                                                                        .options[document.querySelector('#psiegesitines').options.selectedIndex].value;
                                                                        const prostrans = document.querySelector('#programtrans').value;

                                                                        httpSiegestrans1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${prostrans}/${sigstrans}`, true);
                                                                        httpSiegestrans1.onload = () => 
                                                                        {
                                                                            const donsgetrans = JSON.parse(httpSiegestrans1.responseText);
                                                                            console.debug(`${typeof donsgetrans} - ${donsgetrans.attributes}`, console.memory);
                                                                            if(donsgetrans == '')
                                                                            {
                                                                                let httpSiegstrans;
                                                                                httpSiegstrans = new XMLHttpRequest();

                                                                                httpSiegstrans.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${prostrans}/${sigstrans}`, true);
                                                                                httpSiegstrans.onload = () => 
                                                                                {
                                                                                    const dongtrans = JSON.parse(httpSiegstrans.responseText);
                                                                                    document.querySelector('#mess').style.display = 'none';
                                                                                    if (Object.entries(dongtrans).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtrans)) {
                                                                                                document.querySelector('#idtampotrans').value = `${dongtrans[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselecttrans').value = `${dongtrans[key].numsieg}`;
                                                                                            }
                                                                                        }
                                                                                };
                                                                                httpSiegstrans.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSiegstrans.send();
                                                                            }
                                                                            else {
                                                                                document.querySelector('#psiegesitines').value = '';     
                                                                                if (Object.entries(donsgetrans).length >= 1)
                                                                                {
                                                                                    for (let key in Object.entries(donsgetrans)) {
                                                                                        document.querySelector('#idtampotrans').value = `${donsgetrans[key].idtamp}`;                    
                                                                                        document.querySelector('#siegselecttrans').value = `${donsgetrans[key].numsieg}`;
                                                                                    }

                                                                                }
                                                                                document.querySelector('#mess').style.display = 'block';
                                                                                document.querySelector('#erreurMess').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                        };
                                                                        httpSiegestrans1.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegestrans1.send();

                                                                    
                                                                    };
                                                                }
                                                                //premier transite
                                                                let progchemin = document.querySelector('#idchemins');
                                                                if (progchemin !== null) 
                                                                {
                                                                    progchemin.onchange = () => 
                                                                    {
                                                                        document.querySelector('#idcheminsheur').options.length = 1;
                                                                        document.querySelector('#psiegesitines1').options.length = 1;

                                                                        const prostranschemin = document.querySelector('#idchemins')
                                                                        .options[document.querySelector('#idchemins').options.selectedIndex].value;

                                                                        var post_typgare2 = prostranschemin.split('-');
                                                                        var seltypgare2 = post_typgare2[0];
                                                                        var typgaresel1 = post_typgare2[1];
                                                                        let httptypequart2;
                                                                        httptypequart2 = new XMLHttpRequest();
                                                                        
                                                                        httptypequart2.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgaresel1}`, true);
                                                                        httptypequart2.onload = () => 
                                                                        {
                                                                            const donqua2 = JSON.parse(httptypequart2.responseText);
                                                                            if (donqua2 == '') {
                                                                                document.querySelector('#quartier2').options.length = 1;
                                                                            }
                                                                            else{
                                                                                if (Object.entries(donqua2).length >= 1) {
                                                                                                
                                                                                    for (let key in Object.entries(donqua2)) {
                                                                                        let optq1 = document.createElement('option');
                                                                                        optq1.value = `${donqua2[key].nom_quartier}`;
                                                                                        optq1.innerHTML = `${donqua2[key].nom_quartier}`;
                                                                                        document.querySelector('#quartier2').add(optq1);
                                                                                    }
                                                                                } else {
                                                                                    document.querySelector('#quartier2').options.length = 1;
                                                                                }
                                                                            }
                                                                            

                                                                        };
                                                                        httptypequart2.setRequestHeader('Content-Type', 'application/json');
                                                                        httptypequart2.send();

                                                                        let httpSiegeschemin;
                                                                        httpSiegeschemin = new XMLHttpRequest();

                                                                        var tfbs = document.querySelector('#tarifattrib').value;
                                                                        var datedepart = document.querySelector('#date_depheure').value;
                                                                        
                                                                        httpSiegeschemin.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemintr/${prostranschemin}/${datedepart}/${tfbs}`, true);
                                                                        httpSiegeschemin.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschem = JSON.parse(httpSiegeschemin.responseText);
                                                                                    if (Object.entries(dongtranschem).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtranschem)) {
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dongtranschem[key].code_progr}/${dongtranschem[key].intervalle1}/${dongtranschem[key].intervalle2}/${dongtranschem[key].id_ligneheure}/${dongtranschem[key].prix}`;
                                                                                                opt.innerHTML = `${dongtranschem[key].heure}/${dongtranschem[key].date_progr}`;
                                                                                                document.querySelector('#idcheminsheur').add(opt);
                                                                                            }
                                                                                        }
                                                                        };
                                                                        httpSiegeschemin.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegeschemin.send();

                                                                    };
                                                                       let prochemintra = document.querySelector('#idcheminsheur');
                                                                    if (prochemintra !== null)
                                                                        prochemintra.onchange = () => 
                                                                        {  

                                                                           
                                                                            document.querySelector('#psiegesitines1').options.length = 1;

                                                                            const httpPrixittransite = new XMLHttpRequest();
                                                                                const transselitine = document.querySelector('#idcheminsheur')
                                                                            .options[document.querySelector('#idcheminsheur').options.selectedIndex].value;
                                                                                var post_trans = transselitine.split('/');
                                                                            var itinetras = post_trans[0];
                                                                            var dbitra = post_trans[1];
                                                                            var fnitra = post_trans[2];
                                                                            var lhertra = post_trans[3];
                                                                            var prixtra = post_trans[4];

                                                                                httpPrixittransite.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetras}`, true);
                                                                                httpPrixittransite.onload = () => 
                                                                                {
                                                                                    const donprixitran = JSON.parse(httpPrixittransite.responseText);
                                                                                    console.debug(`${typeof donprixitran}-${donprixitran.attributes}`, console.memory);
                                                                                    if (Object.entries(donprixitran).length >= 1) {
                                                                                        for (let key in Object.entries(donprixitran)) 
                                                                                        {
                                                                                            document.querySelector('#prix_axetransit').value = `${prixtra}`;
                                                                                            document.querySelector('#catetransit').value = `${donprixitran[key].categori}`;
                                                                                            document.querySelector('#gidtrans').value =  `${donprixitran[key].gareidentif}`;
                                                                                            document.querySelector('#nomitintrans1').value = `${donprixitran[key].nom_ligne}`; 
                                                                                        document.querySelector('#ligntrans1').value = `${donprixitran[key].ident_ligne}`;
                                                                                        }
                                                                                    }
                                                                                };
                                                                                httpPrixittransite.setRequestHeader('Content-Type', 'application/json');
                                                                                httpPrixittransite.send();


                                                                                

                                                                                const httpRequetteitra = new XMLHttpRequest();
                                                                        
                                                                                    httpRequetteitra.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetras}/${dbitra}/${fnitra}`, true);
                                                                                httpRequetteitra.onload = () => {
                                                                                    const dattaitra = JSON.parse(httpRequetteitra.responseText);
                                                                                    console.debug(`${typeof dattaitra} - ${dattaitra.attributes}`, console.memory);
                                                                                    if (Object.entries(dattaitra).length >= 1) {
                                                                                        for (let key in Object.entries(dattaitra)) {
                                                                                            
                                                                                            let opt = document.createElement('option');
                                                                                            opt.value = `${dattaitra[key].siege_num}`;
                                                                                            opt.innerHTML = `${dattaitra[key].siege_num}`;
                                                                                            document.querySelector('#psiegesitines1').add(opt);
                                                                                            
                                                                                        }
                                                                                        
                                                                                    } else {
                                                                                        document.querySelector('#psiegesitines1').options.length = 1;
                                                                                    }
                                                                                };
                                                                                httpRequetteitra.setRequestHeader('Content-Type', 'application/json');
                                                                                httpRequetteitra.send();
                                                                        };

                                                                        let progsieges1 = document.querySelector('#psiegesitines1');
                                                                        if (progsieges1 !== null) 
                                                                        {
                                                                            progsieges1.onchange = () => 
                                                                            {

                                                                              const  gareidentiftrans2 = document.querySelector('#gidtrans').value;
                                                                                    const httpsousgare1 = new XMLHttpRequest();
                                                                                    httpsousgare1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftrans2}`, true);
                                                                                    httpsousgare1.onload = () => 
                                                                                    {
                                                                                        const donsousg1 = JSON.parse(httpsousgare1.responseText);
                                                                                        console.debug(`${typeof donsousg1}-${donsousg1.attributes}`, console.memory);
                                                                                        if (Object.entries(donsousg1).length >= 1) {
                                                                                            for (let key in Object.entries(donsousg1)) 
                                                                                            {
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${donsousg1[key].idsousgare}`;
                                                                                                opt.innerHTML = `${donsousg1[key].nomsousgare}`;
                                                                                                document.querySelector('#transitedepargare2').add(opt);
                                    
                                                                                            }
                                                                                        }
                                                                                    };
                                                                                    httpsousgare1.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpsousgare1.send();
                                                                                 const transselitine1 = document.querySelector('#idcheminsheur')
                                                                                .options[document.querySelector('#idcheminsheur').options.selectedIndex].value;
                                                                                var post_trans1 = transselitine1.split('/');
                                                                                var itinetras1 = post_trans1[0];
                                                                    
                                                                                

                                                                                let httpSieges1;
                                                                                httpSieges1 = new XMLHttpRequest();
                                                                                const sigs1 = document.querySelector('#psiegesitines1')
                                                                                .options[document.querySelector('#psiegesitines1').options.selectedIndex].value;
                                                                                //const pros1 = document.querySelector('#program').value;

                                                                                httpSieges1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetras1}/${sigs1}`, true);
                                                                                httpSieges1.onload = () => 
                                                                                {
                                                                                    const donsge1 = JSON.parse(httpSieges1.responseText);
                                                                                    console.debug(`${typeof donsge1} - ${donsge1.attributes}`, console.memory);
                                                                                    if(donsge1 == '')
                                                                                    {
                                                                                        let httpSiegs1;
                                                                                        httpSiegs1 = new XMLHttpRequest();

                                                                                        httpSiegs1.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetras1}/${sigs1}`, true);
                                                                                        httpSiegs1.onload = () => 
                                                                                        {
                                                                                            const dong1 = JSON.parse(httpSiegs1.responseText);
                                                                                            document.querySelector('#mess').style.display = 'none';
                                                                                            if (Object.entries(dong1).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(dong1)) {
                                                                                                        document.querySelector('#idtampo1').value = `${dong1[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselect1').value = `${dong1[key].numsieg}`;
                                                                                                    }
                                                                                                }
                                                                                        };
                                                                                        httpSiegs1.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegs1.send();
                                                                                    }
                                                                                    else {
                                                                                        document.querySelector('#psiegesitines1').value = '';     
                                                                                        if (Object.entries(donsge1).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(donsge1)) {
                                                                                                document.querySelector('#idtampo1').value = `${donsge1[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselect1').value = `${donsge1[key].numsieg}`;
                                                                                            }

                                                                                        }
                                                                                        document.querySelector('#mess').style.display = 'block';
                                                                                        document.querySelector('#erreurMess').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                                };
                                                                                httpSieges1.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSieges1.send();

                                                                            };
                                                                        }
                                                                }
                                                                let progchemin1 = document.querySelector('#idchemins1');
                                                                if (progchemin1 !== null) 
                                                                {
                                                                    progchemin1.onchange = () => 
                                                                    {
                                                                       
                                                                        document.querySelector('#idcheminsheur1').options.length = 1;
                                                                        document.querySelector('#psiegesitines2').options.length = 1;

                                                                        const prostranschemin32 = document.querySelector('#idchemins1')
                                                                        .options[document.querySelector('#idchemins1').options.selectedIndex].value;

                                                                        var post_typgare32 = prostranschemin32.split('-');
                                                                        var seltypgare32 = post_typgare32[0];
                                                                        var typgaresel31 = post_typgare32[1];
                                                                      
                                                                        let httpSiegeschemin1;
                                                                        httpSiegeschemin1 = new XMLHttpRequest();

                                                                        var datedepart = document.querySelector('#date_depheure').value;
                                                                        var tfbs = document.querySelector('#tarifattrib').value;
                                                                        const prostranschemin1 = document.querySelector('#idchemins1')
                                                                        .options[document.querySelector('#idchemins1').options.selectedIndex].value;

                                                                        httpSiegeschemin1.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemintr/${prostranschemin1}/${datedepart}/${tfbs}`, true);
                                                                        httpSiegeschemin1.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschem1 = JSON.parse(httpSiegeschemin1.responseText);
                                                                                    if (Object.entries(dongtranschem1).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtranschem1)) {
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dongtranschem1[key].code_progr}/${dongtranschem1[key].intervalle1}/${dongtranschem1[key].intervalle2}/${dongtranschem1[key].id_ligneheure}/${dongtranschem1[key].prix}`;
                                                                                                opt.innerHTML = `${dongtranschem1[key].heure}/${dongtranschem1[key].date_progr}`;
                                                                                                document.querySelector('#idcheminsheur1').add(opt);
                                                                                            }
                                                                                        }
                                                                        };
                                                                        httpSiegeschemin1.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegeschemin1.send();

                                                                    };
                                                                      let prochemintra1 = document.querySelector('#idcheminsheur1');
                                                                    if (prochemintra1 !== null)
                                                                        prochemintra1.onchange = () => 
                                                                        {  

                                                                                
                                                                                document.querySelector('#psiegesitines2').options.length = 1;

                                                                            const httpPrixittransite1 = new XMLHttpRequest();
                                                                                const transselitine1 = document.querySelector('#idcheminsheur1')
                                                                            .options[document.querySelector('#idcheminsheur1').options.selectedIndex].value;
                                                                                var post_trans1 = transselitine1.split('/');
                                                                            var itinetras1 = post_trans1[0];
                                                                            var dbitra1 = post_trans1[1];
                                                                            var fnitra1 = post_trans1[2];
                                                                            var lhertra1 = post_trans1[3];
                                                                            var prixtra1 = post_trans1[4];

                                                                                httpPrixittransite1.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetras1}`, true);
                                                                                httpPrixittransite1.onload = () => 
                                                                                {
                                                                                    const donprixitran1 = JSON.parse(httpPrixittransite1.responseText);
                                                                                    if (Object.entries(donprixitran1).length >= 1) {
                                                                                        for (let key in Object.entries(donprixitran1)) 
                                                                                        {
                                                                                            document.querySelector('#prix_axetransit1').value = `${prixtra1}`;
                                                                                            document.querySelector('#catetransit1').value = `${donprixitran1[key].categori}`;
                                                                                            document.querySelector('#gidtrans1').value =  `${donprixitran1[key].gareidentif}`;
                                                                                            document.querySelector('#nomitintrans2').value = `${donprixitran1[key].nom_ligne}`;
                                                                                            document.querySelector('#ligntrans2').value = `${donprixitran1[key].ident_ligne}`;
                                                                                        }
                                                                                    }
                                                                                };
                                                                                httpPrixittransite1.setRequestHeader('Content-Type', 'application/json');
                                                                                httpPrixittransite1.send();
                                                                      
                                                                              
                                                                               
                                                                                const httpRequetteitra1 = new XMLHttpRequest();
                                                                        
                                                                                    httpRequetteitra1.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetras1}/${dbitra1}/${fnitra1}`, true);
                                                                                httpRequetteitra1.onload = () => {
                                                                                    const dattaitra1 = JSON.parse(httpRequetteitra1.responseText);
                                                                                    console.debug(`${typeof dattaitra1} - ${dattaitra1.attributes}`, console.memory);
                                                                                    if (Object.entries(dattaitra1).length >= 1) {
                                                                                        for (let key in Object.entries(dattaitra1)) {
                                                                                            
                                                                                            let opt = document.createElement('option');
                                                                                            opt.value = `${dattaitra1[key].siege_num}`;
                                                                                            opt.innerHTML = `${dattaitra1[key].siege_num}`;
                                                                                            document.querySelector('#psiegesitines2').add(opt);
                                                                                            
                                                                                        }
                                                                                        
                                                                                    } else {
                                                                                        document.querySelector('#psiegesitines2').options.length = 1;
                                                                                    }
                                                                                };
                                                                                httpRequetteitra1.setRequestHeader('Content-Type', 'application/json');
                                                                                httpRequetteitra1.send();
                                                                        };

                                                                        let progsieges2 = document.querySelector('#psiegesitines2');
                                                                        if (progsieges2 !== null) 
                                                                        {
                                                                            progsieges2.onchange = () => 
                                                                            {
                                                                                    const transselitine2 = document.querySelector('#idcheminsheur1')
                                                                                .options[document.querySelector('#idcheminsheur1').options.selectedIndex].value;
                                                                                var post_trans2 = transselitine2.split('/');
                                                                                var itinetras2 = post_trans2[0];
                                                                                    
                                                                                    const gareidentiftrans4 = document.querySelector('#gidtrans1').value;
                                                                                    const httpsousgare4 = new XMLHttpRequest();
                                                                                    httpsousgare4.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftrans4}`, true);
                                                                                    httpsousgare4.onload = () => 
                                                                                    {
                                                                                        const donsousg4 = JSON.parse(httpsousgare4.responseText);
                                                                                        console.debug(`${typeof donsousg4}-${donsousg4.attributes}`, console.memory);
                                                                                        if (Object.entries(donsousg4).length >= 1) {
                                                                                            for (let key in Object.entries(donsousg4)) 
                                                                                            {
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${donsousg4[key].idsousgare}`;
                                                                                                opt.innerHTML = `${donsousg4[key].nomsousgare}`;
                                                                                                document.querySelector('#transitedepargare3').add(opt);
                                    
                                                                                            }
                                                                                        }
                                                                                    };
                                                                                    httpsousgare4.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpsousgare4.send();

                                                                                let httpSieges2;
                                                                                httpSieges2 = new XMLHttpRequest();
                                                                                const sigs2 = document.querySelector('#psiegesitines2')
                                                                                .options[document.querySelector('#psiegesitines2').options.selectedIndex].value;

                                                                                httpSieges2.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetras2}/${sigs2}`, true);
                                                                                httpSieges2.onload = () => 
                                                                                {
                                                                                    const donsge2 = JSON.parse(httpSieges2.responseText);
                                                                                    if(donsge2 == '')
                                                                                    {
                                                                                        let httpSiegs2;
                                                                                        httpSiegs2 = new XMLHttpRequest();

                                                                                        httpSiegs2.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetras2}/${sigs2}`, true);
                                                                                        httpSiegs2.onload = () => 
                                                                                        {
                                                                                            const dong2 = JSON.parse(httpSiegs2.responseText);
                                                                                            document.querySelector('#mess').style.display = 'none';
                                                                                            if (Object.entries(dong2).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(dong2)) {
                                                                                                        document.querySelector('#idtampo2').value = `${dong2[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselect2').value = `${dong2[key].numsieg}`;
                                                                                                    }
                                                                                                }
                                                                                        };
                                                                                        httpSiegs2.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegs2.send();
                                                                                    }
                                                                                    else {
                                                                                        document.querySelector('#psiegesitines2').value = '';     
                                                                                        if (Object.entries(donsge2).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(donsge1)) {
                                                                                                document.querySelector('#idtampo2').value = `${donsge2[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselect2').value = `${donsge2[key].numsieg}`;
                                                                                            }

                                                                                        }
                                                                                        document.querySelector('#mess').style.display = 'block';
                                                                                        document.querySelector('#erreurMess').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                                };
                                                                                httpSieges2.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSieges2.send();

                                                                            };
                                                                        }
                                                                }               
                                                            }

                                                            //troisieme itineraire
                                                            if(i === 4)
                                                            {
                                                                let opt = document.createElement('option');
                                                                opt.value = `${donitines[1].code_itineraires}`;
                                                                opt.innerHTML = `${donitines[1].nom_itineraires}`;
                                                                document.querySelector('#idchemins').add(opt);


                                                                let opt1 = document.createElement('option');
                                                                opt1.value = `${donitines[2].code_itineraires}`;
                                                                opt1.innerHTML = `${donitines[2].nom_itineraires}`;
                                                                document.querySelector('#idchemins1').add(opt1);

                                                                let opt2 = document.createElement('option');
                                                                opt2.value = `${donitines[3].code_itineraires}`;
                                                                opt2.innerHTML = `${donitines[3].nom_itineraires}`;
                                                                document.querySelector('#idchemins2').add(opt2);

                                                                document.querySelector('#lignesitineraire').value = `${donitines[0].nom_itineraires}`;
                                                               
                                                                document.querySelector('#itinecodes').value = `${donitines[0].id_lignes}`;
                                                                document.querySelector('#idcompg').value = `${donitines[0].id_compaga}`;
                                                                document.querySelector('#idcompg1').value = `${donitines[1].id_compaga}`;
                                                                document.querySelector('#idcompg2').value = `${donitines[2].id_compaga}`;
                                                                document.querySelector('#idcompg3').value = `${donitines[3].id_compaga}`;
                                                                var typgare1 = document.querySelector('#itinecode').value;
                                                                var post_typgare1 = typgare1.split('-');
                                                                var seltypgare1 = post_typgare1[0];
                                                                var typgaresel = post_typgare1[1];
                                                                    let httptypequart1;
                                                                    httptypequart1 = new XMLHttpRequest();
                                                                    
                                                                    httptypequart1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgaresel}`, true);
                                                                    httptypequart1.onload = () => 
                                                                    {
                                                                        const donqua1 = JSON.parse(httptypequart1.responseText);
                                                                        if (donqua1 == '') {
                                                                            document.querySelector('#quartier1').options.length = 1;
                                                                        }
                                                                        else{
                                                                            if (Object.entries(donqua1).length >= 1) {
                                                                                            
                                                                                for (let key in Object.entries(donqua1)) {
                                                                                    let optq = document.createElement('option');
                                                                                    optq.value = `${donqua1[key].nom_quartier}`;
                                                                                    optq.innerHTML = `${donqua1[key].nom_quartier}`;
                                                                                    document.querySelector('#quartier1').add(optq);
                                                                                }
                                                                            } else {
                                                                                document.querySelector('#quartier1').options.length = 1;
                                                                            }
                                                                        }
                                                                        

                                                                    };
                                                                    httptypequart1.setRequestHeader('Content-Type', 'application/json');
                                                                    httptypequart1.send();



                                                                        let httptypequartitin1;
                                                                        httptypequartitin1 = new XMLHttpRequest();
                                                                        var datedepart = document.querySelector('#date_depheure').value;
                                                                        var itinpro1 = document.querySelector('#itinecode').value;
                                                                        httptypequartitin1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheureitine/${itinpro1}/${datedepart}`, true);
                                                                    httptypequartitin1.onload = () => 
                                                                    {
                                                                        const infositin1 = JSON.parse(httptypequartitin1.responseText);
                                                                        if (infositin1 == null) 
                                                                        {


                                                                        }
                                                                        if (Object.entries(infositin1).length >= 1) 
                                                                        {
                                                                                
                                                                            
                                                                            for (let key in Object.entries(infositin1)) {
                                                                                    let opt = document.createElement('option');
                                                                                    opt.value = `${infositin1[key].id_ligneheure}/${infositin1[key].heure}`;
                                                                                    opt.innerHTML = `${infositin1[key].heure}`;
                                                                                    document.querySelector('#hdepartitine').add(opt);
                                                                                }
                                                                        } else {
                                                                            document.querySelector('#hdepartitine').options.length = 1;
                                                                        }
                                                                    };
                                                                    httptypequartitin1.setRequestHeader('Content-Type', 'application/json');
                                                                    httptypequartitin1.send();
                                                                let hrdepartine1 = document.querySelector('#hdepartitine');
                                                                if (hrdepartine1 !== null) {
                                                                    hrdepartine1.onchange = () => 
                                                                    {
                                                                        document.querySelector('#psiegesitines').options.length = 1;
                                                                        const httpRequestit1 = new XMLHttpRequest();
                                                                        const seleitine1 = document.querySelector('#hdepartitine')
                                                                            .options[document.querySelector('#hdepartitine').options.selectedIndex].value;

                                                                            var post_lhitine1 = seleitine1.split('/');
                                                                            var selitine1 = post_lhitine1[0];
                                                                            var lhselitine1 = post_lhitine1[1];
                                                                            
                                                                            const dpt_dateitine1 = document.querySelector('#date_depheure').value;
                                                                            var itinproit1 = document.querySelector('#itinecode').value;
                                                                        httpRequestit1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifprog/${itinproit1}/${dpt_dateitine1}/${selitine1}`, true);
                                                                        httpRequestit1.onload = () => 
                                                                        {
                                                                            const donit1 = JSON.parse(httpRequestit1.responseText);
                                                                                console.debug(`${typeof donit1} - ${donit1.attributes}`, console.memory);

                                                                                if (donit1 == '') 
                                                                                {
                                                                                    
                                                                                        let opt = document.createElement('option');
                                                                                        opt.value = '';                                                             
                                                                                   
                                                                                    
                                                                                    
                                                                                } 
                                                                                else 
                                                                                {       
                                                                                    if (Object.entries(donit1).length >= 1) {
                                                                                        for (let key in Object.entries(donit1)) {
                                                                                            document.querySelector('#programtrans').value = `${donit1[key].code_progr}`;
                                                                                            document.querySelector('#dateprtrans').value = `${donit1[key].date_progr}`;
                                                                                            document.querySelector('#tarifattrib').value = `${donit1[key].typetarif}`;
                                                                                            document.querySelector('#deplignetrans').value = `${donit1[key].gareidentif}`;
                                                                                            document.querySelector('#intertrans1').value = `${donit1[key].intervalle1}`;
                                                                                            document.querySelector('#intertrans2').value = `${donit1[key].intervalle2}`;
                                                                                            document.querySelector('#ligntrans').value = `${donit1[key].ident_ligne}`;
                                                                                            document.querySelector('#nomitintrans').value = `${donit1[key].nom_ligne}`;
                                                                                            document.querySelector('#hertrans').value = `${donit1[key].heure}`;
                                                                                            document.querySelector('#catetrans').value = `${donit1[key].categori}`;

                                                                                        }
                                                                                    } 
                                                                                    
                                                                                    const httpPrixit = new XMLHttpRequest();
                                                                                    const seleitine = document.querySelector('#hdepartitine')
                                                                                    .options[document.querySelector('#hdepartitine').options.selectedIndex].value;

                                                                                    var post_lhitine = seleitine.split('/');
                                                                                    var selitine = post_lhitine[0];
                                                                                    var lhselitine = post_lhitine[1];
                                                                                            var tfbs1 = document.querySelector('#tarifattrib').value;
                                                                                    httpPrixit.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifpriprg/${selitine}/${tfbs1}`, true);
                                                                                    httpPrixit.onload = () => 
                                                                                    {
                                                                                        const donprixit = JSON.parse(httpPrixit.responseText);
                                                                                        console.debug(`${typeof donprixit}-${donprixit.attributes}`, console.memory);
                                                                                        if (Object.entries(donprixit).length >= 1) {
                                                                                            for (let key in Object.entries(donprixit)) 
                                                                                            {
                                                                                                document.querySelector('#prix_axetrans').value = `${donprixit[key].prix}`;
                                    
                                                                                            }
                                                                                        }
                                                                                    };
                                                                                    httpPrixit.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpPrixit.send();

                                                                                    

                                                                                    const httpRequetteit = new XMLHttpRequest();
                                                                                    const cdprogit = document.querySelector('#programtrans').value;
                                                                                    const dbit = document.querySelector('#intertrans1').value;
                                                                                    const fnit = document.querySelector('#intertrans2').value;
                                                                                    const lgit = document.querySelector('#nomitintrans').value;
                                                                                    const timit = document.querySelector('#hertrans').value;
                                                                                    const dpt_dateitine = document.querySelector('#date_depheure').value;
                                                                                        httpRequetteit.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${cdprogit}/${dpt_dateitine}/${lgit}/${timit}/${dbit}/${fnit}`, true);
                                                                                    httpRequetteit.onload = () => {
                                                                                        const dattait = JSON.parse(httpRequetteit.responseText);
                                                                                        console.debug(`${typeof dattait} - ${dattait.attributes}`, console.memory);
                                                                                        if (Object.entries(dattait).length >= 1) {
                                                                                            for (let key in Object.entries(dattait)) {
                                                                                                
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dattait[key].siege_num}`;
                                                                                                opt.innerHTML = `${dattait[key].siege_num}`;
                                                                                                document.querySelector('#psiegesitines').add(opt);
                                                                                                
                                                                                            }
                                                                                            
                                                                                        } else {
                                                                                            document.querySelector('#psiegesitines').options.length = 1;
                                                                                        }
                                                                                    };
                                                                                    httpRequetteit.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpRequetteit.send();

                                                                                }  
                                                                                
                                                                        };
                                                                        httpRequestit1.setRequestHeader('Content-Type', 'application/json');
                                                                        httpRequestit1.send();
                                                                         
                                                                    };
                                                                    
                                                            
                                                                }
                                                                let progsiegestrans = document.querySelector('#psiegesitines');
                                                                if (progsiegestrans !== null) {
                                                                    progsiegestrans.onchange = () => 
                                                                    {

                                                                       const gareidentiftrans1 = document.querySelector('#deplignetrans').value;
                                                                                    const httpsousgare = new XMLHttpRequest();
                                                                                    httpsousgare.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftrans1}`, true);
                                                                                    httpsousgare.onload = () => 
                                                                                    {
                                                                                        const donsousg = JSON.parse(httpsousgare.responseText);
                                                                                        console.debug(`${typeof donsousg}-${donsousg.attributes}`, console.memory);
                                                                                        if (Object.entries(donsousg).length >= 1) {
                                                                                            for (let key in Object.entries(donsousg)) 
                                                                                            {
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${donsousg[key].idsousgare}`;
                                                                                                opt.innerHTML = `${donsousg[key].nomsousgare}`;
                                                                                                document.querySelector('#transitedepargare1').add(opt);
                                    
                                                                                            }
                                                                                        }
                                                                                    };
                                                                                    httpsousgare.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpsousgare.send();
                                                                        let httpSiegestrans1;
                                                                        httpSiegestrans1 = new XMLHttpRequest();
                                                                        const sigstrans = document.querySelector('#psiegesitines')
                                                                        .options[document.querySelector('#psiegesitines').options.selectedIndex].value;
                                                                        const prostrans = document.querySelector('#programtrans').value;

                                                                        httpSiegestrans1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${prostrans}/${sigstrans}`, true);
                                                                        httpSiegestrans1.onload = () => 
                                                                        {
                                                                            const donsgetrans = JSON.parse(httpSiegestrans1.responseText);
                                                                            console.debug(`${typeof donsgetrans} - ${donsgetrans.attributes}`, console.memory);
                                                                            if(donsgetrans == '')
                                                                            {
                                                                                let httpSiegstrans;
                                                                                httpSiegstrans = new XMLHttpRequest();

                                                                                httpSiegstrans.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${prostrans}/${sigstrans}`, true);
                                                                                httpSiegstrans.onload = () => 
                                                                                {
                                                                                    const dongtrans = JSON.parse(httpSiegstrans.responseText);
                                                                                    document.querySelector('#mess').style.display = 'none';
                                                                                    if (Object.entries(dongtrans).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtrans)) {
                                                                                                document.querySelector('#idtampotrans').value = `${dongtrans[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselecttrans').value = `${dongtrans[key].numsieg}`;
                                                                                            }
                                                                                        }
                                                                                };
                                                                                httpSiegstrans.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSiegstrans.send();
                                                                            }
                                                                            else {
                                                                                document.querySelector('#psiegesitines').value = '';     
                                                                                if (Object.entries(donsgetrans).length >= 1)
                                                                                {
                                                                                    for (let key in Object.entries(donsgetrans)) {
                                                                                        document.querySelector('#idtampotrans').value = `${donsgetrans[key].idtamp}`;                    
                                                                                        document.querySelector('#siegselecttrans').value = `${donsgetrans[key].numsieg}`;
                                                                                    }

                                                                                }
                                                                                document.querySelector('#mess').style.display = 'block';
                                                                                document.querySelector('#erreurMess').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                        };
                                                                        httpSiegestrans1.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegestrans1.send();

                                                                    
                                                                    };
                                                                }
                                                                //premier transite
                                                                let progchemin = document.querySelector('#idchemins');
                                                                if (progchemin !== null) 
                                                                {
                                                                    progchemin.onchange = () => 
                                                                    {
                                                                        document.querySelector('#idcheminsheur').options.length = 1;
                                                                        document.querySelector('#psiegesitines1').options.length = 1;

                                                                        var datedepart = document.querySelector('#date_depheure').value;
                                                                        
                                                                        const prostranschemin = document.querySelector('#idchemins')
                                                                        .options[document.querySelector('#idchemins').options.selectedIndex].value;

                                                                        var post_typgare2 = prostranschemin.split('-');
                                                                        var seltypgare2 = post_typgare2[0];
                                                                        var typgaresel1 = post_typgare2[1];
                                                                        let httptypequart2;
                                                                        httptypequart2 = new XMLHttpRequest();
                                                                        
                                                                        httptypequart2.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgaresel1}`, true);
                                                                        httptypequart2.onload = () => 
                                                                        {
                                                                            const donqua2 = JSON.parse(httptypequart2.responseText);
                                                                            if (donqua2 == '') {
                                                                                document.querySelector('#quartier2').options.length = 1;
                                                                            }
                                                                            else{
                                                                                if (Object.entries(donqua2).length >= 1) {
                                                                                                
                                                                                    for (let key in Object.entries(donqua2)) {
                                                                                        let optq1 = document.createElement('option');
                                                                                        optq1.value = `${donqua2[key].nom_quartier}`;
                                                                                        optq1.innerHTML = `${donqua2[key].nom_quartier}`;
                                                                                        document.querySelector('#quartier2').add(optq1);
                                                                                    }
                                                                                } else {
                                                                                    document.querySelector('#quartier2').options.length = 1;
                                                                                }
                                                                            }
                                                                            

                                                                        };
                                                                        httptypequart2.setRequestHeader('Content-Type', 'application/json');
                                                                        httptypequart2.send();
                                                                        
                                                                        let httpSiegeschemin;
                                                                        httpSiegeschemin = new XMLHttpRequest();
                                                                        
                                                                        var tfbs = document.querySelector('#tarifattrib').value;

                                                                        httpSiegeschemin.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemintr/${prostranschemin}/${datedepart}/${tfbs}`, true);
                                                                        httpSiegeschemin.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschem = JSON.parse(httpSiegeschemin.responseText);
                                                                                    if (Object.entries(dongtranschem).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtranschem)) {
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dongtranschem[key].code_progr}/${dongtranschem[key].intervalle1}/${dongtranschem[key].intervalle2}/${dongtranschem[key].id_ligneheure}/${dongtranschem[key].prix}`;
                                                                                                opt.innerHTML = `${dongtranschem[key].heure}/${dongtranschem[key].date_progr}`;
                                                                                                document.querySelector('#idcheminsheur').add(opt);
                                                                                            }
                                                                                        }
                                                                        };
                                                                        httpSiegeschemin.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegeschemin.send();

                                                                    };
                                                                        let prochemintra = document.querySelector('#idcheminsheur');
                                                                        if (prochemintra !== null){
                                                                            prochemintra.onchange = () => 
                                                                            {  

                                                                               
                                                                                document.querySelector('#psiegesitines1').options.length = 1; 

                                                                                const httpPrixittransite = new XMLHttpRequest();
                                                                                    const transselitine = document.querySelector('#idcheminsheur')
                                                                                .options[document.querySelector('#idcheminsheur').options.selectedIndex].value;
                                                                                    var post_trans = transselitine.split('/');
                                                                                var itinetras = post_trans[0];
                                                                                var dbitra = post_trans[1];
                                                                                var fnitra = post_trans[2];
                                                                                var lhertra = post_trans[3];
                                                                                var prixtra = post_trans[4];

                                                                                    httpPrixittransite.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetras}`, true);
                                                                                    httpPrixittransite.onload = () => 
                                                                                    {
                                                                                        const donprixitran = JSON.parse(httpPrixittransite.responseText);
                                                                                        console.debug(`${typeof donprixitran}-${donprixitran.attributes}`, console.memory);
                                                                                        if (Object.entries(donprixitran).length >= 1) {
                                                                                            for (let key in Object.entries(donprixitran)) 
                                                                                            {
                                                                                                document.querySelector('#prix_axetransit').value = `${prixtra}`;
                                                                                                document.querySelector('#catetransit').value = `${donprixitran[key].categori}`;
                                                                                                document.querySelector('#gidtrans').value =  `${donprixitran[key].gareidentif}`;
                                                                                                document.querySelector('#nomitintrans1').value = `${donprixitran[key].nom_ligne}`;
                                                                                                document.querySelector('#ligntrans1').value = `${donprixitran[key].ident_ligne}`;
                                                                                            }
                                                                                        }
                                                                                    };
                                                                                    httpPrixittransite.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpPrixittransite.send();
                                                                          

                                                                                    
                                                                                    const httpRequetteitra = new XMLHttpRequest();
                                                                            
                                                                                        httpRequetteitra.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetras}/${dbitra}/${fnitra}`, true);
                                                                                    httpRequetteitra.onload = () => {
                                                                                        const dattaitra = JSON.parse(httpRequetteitra.responseText);
                                                                                        console.debug(`${typeof dattaitra} - ${dattaitra.attributes}`, console.memory);
                                                                                        if (Object.entries(dattaitra).length >= 1) {
                                                                                            for (let key in Object.entries(dattaitra)) {
                                                                                                
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dattaitra[key].siege_num}`;
                                                                                                opt.innerHTML = `${dattaitra[key].siege_num}`;
                                                                                                document.querySelector('#psiegesitines1').add(opt);
                                                                                                
                                                                                            }
                                                                                            
                                                                                        } else {
                                                                                            document.querySelector('#psiegesitines1').options.length = 1;
                                                                                        }
                                                                                    };
                                                                                    httpRequetteitra.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpRequetteitra.send();
                                                                            };
                                                                        }
                                                                        let progsieges1 = document.querySelector('#psiegesitines1');
                                                                        if (progsieges1 !== null) 
                                                                        {
                                                                            progsieges1.onchange = () => 
                                                                            {

                                                                               const gareidentiftrans2 = document.querySelector('#gidtrans').value;
                                                                                    const httpsousgare1 = new XMLHttpRequest();
                                                                                    httpsousgare1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftrans2}`, true);
                                                                                    httpsousgare1.onload = () => 
                                                                                    {
                                                                                        const donsousg1 = JSON.parse(httpsousgare1.responseText);
                                                                                        console.debug(`${typeof donsousg1}-${donsousg1.attributes}`, console.memory);
                                                                                        if (Object.entries(donsousg1).length >= 1) {
                                                                                            for (let key in Object.entries(donsousg1)) 
                                                                                            {
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${donsousg1[key].idsousgare}`;
                                                                                                opt.innerHTML = `${donsousg1[key].nomsousgare}`;
                                                                                                document.querySelector('#transitedepargare2').add(opt);
                                    
                                                                                            }
                                                                                        }
                                                                                    };
                                                                                    httpsousgare1.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpsousgare1.send();
                                                                                

                                                                                    const transselitine1 = document.querySelector('#idcheminsheur')
                                                                                .options[document.querySelector('#idcheminsheur').options.selectedIndex].value;
                                                                                var post_trans1 = transselitine1.split('/');
                                                                                var itinetras1 = post_trans1[0];
                                                                    
                                                                                let httpSieges1;
                                                                                httpSieges1 = new XMLHttpRequest();
                                                                                const sigs1 = document.querySelector('#psiegesitines1')
                                                                                .options[document.querySelector('#psiegesitines1').options.selectedIndex].value;
                                                                                //const pros1 = document.querySelector('#program').value;

                                                                                httpSieges1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetras1}/${sigs1}`, true);
                                                                                httpSieges1.onload = () => 
                                                                                {
                                                                                    const donsge1 = JSON.parse(httpSieges1.responseText);
                                                                                    console.debug(`${typeof donsge1} - ${donsge1.attributes}`, console.memory);
                                                                                    if(donsge1 == '')
                                                                                    {
                                                                                        let httpSiegs1;
                                                                                        httpSiegs1 = new XMLHttpRequest();

                                                                                        httpSiegs1.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetras1}/${sigs1}`, true);
                                                                                        httpSiegs1.onload = () => 
                                                                                        {
                                                                                            const dong1 = JSON.parse(httpSiegs1.responseText);
                                                                                            document.querySelector('#mess').style.display = 'none';
                                                                                            if (Object.entries(dong1).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(dong1)) {
                                                                                                        document.querySelector('#idtampo1').value = `${dong1[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselect1').value = `${dong1[key].numsieg}`;
                                                                                                    }
                                                                                                }
                                                                                        };
                                                                                        httpSiegs1.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegs1.send();
                                                                                    }
                                                                                    else {
                                                                                        document.querySelector('#psiegesitines1').value = '';     
                                                                                        if (Object.entries(donsge1).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(donsge1)) {
                                                                                                document.querySelector('#idtampo1').value = `${donsge1[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselect1').value = `${donsge1[key].numsieg}`;
                                                                                            }

                                                                                        }
                                                                                        document.querySelector('#mess').style.display = 'block';
                                                                                        document.querySelector('#erreurMess').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                                };
                                                                                httpSieges1.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSieges1.send();

                                                                            };
                                                                        }
                                                                }
                                                                //deuxieme transite
                                                                let progchemin1 = document.querySelector('#idchemins1');
                                                                if (progchemin1 !== null) 
                                                                {
                                                                    progchemin1.onchange = () => 
                                                                    {
                                                                        document.querySelector('#idcheminsheur1').options.length = 1;
                                                                        document.querySelector('#psiegesitines2').options.length = 1;

                                                                        const prostranschemin32 = document.querySelector('#idchemins1')
                                                                        .options[document.querySelector('#idchemins1').options.selectedIndex].value;

                                                                        var post_typgare32 = prostranschemin32.split('-');
                                                                        var seltypgare32 = post_typgare32[0];
                                                                        var typgaresel31 = post_typgare32[1];
                                                                        let httptypequart32;
                                                                        httptypequart32 = new XMLHttpRequest();
                                                                        
                                                                        httptypequart32.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgaresel31}`, true);
                                                                        httptypequart32.onload = () => 
                                                                        {
                                                                            const donqua32 = JSON.parse(httptypequart32.responseText);
                                                                            if (donqua32 == '') {
                                                                                document.querySelector('#quartier3').options.length = 1;
                                                                            }
                                                                            else{
                                                                                if (Object.entries(donqua32).length >= 1) {
                                                                                                
                                                                                    for (let key in Object.entries(donqua32)) {
                                                                                        let optq31 = document.createElement('option');
                                                                                        optq31.value = `${donqua32[key].nom_quartier}`;
                                                                                        optq31.innerHTML = `${donqua32[key].nom_quartier}`;
                                                                                        document.querySelector('#quartier3').add(optq31);
                                                                                    }
                                                                                } else {
                                                                                    document.querySelector('#quartier3').options.length = 1;
                                                                                }
                                                                            }
                                                                            

                                                                        };
                                                                        httptypequart32.setRequestHeader('Content-Type', 'application/json');
                                                                        httptypequart32.send();
                                                                        
                                                                        let httpSiegeschemin1;
                                                                        httpSiegeschemin1 = new XMLHttpRequest();
                                                                        
                                                                        var datedepart = document.querySelector('#date_depheure').value;
                                                                        var tfbs = document.querySelector('#tarifattrib').value;
                                                                        const prostranschemin1 = document.querySelector('#idchemins1')
                                                                        .options[document.querySelector('#idchemins1').options.selectedIndex].value;

                                                                        httpSiegeschemin1.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemintr/${prostranschemin1}/${datedepart}/${tfbs}`, true);
                                                                        httpSiegeschemin1.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschem1 = JSON.parse(httpSiegeschemin1.responseText);
                                                                                    if (Object.entries(dongtranschem1).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtranschem1)) {
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dongtranschem1[key].code_progr}/${dongtranschem1[key].intervalle1}/${dongtranschem1[key].intervalle2}/${dongtranschem1[key].id_ligneheure}/${dongtranschem1[key].prix}`;
                                                                                                opt.innerHTML = `${dongtranschem1[key].heure}/${dongtranschem1[key].date_progr}`;
                                                                                                document.querySelector('#idcheminsheur1').add(opt);
                                                                                            }
                                                                                        }
                                                                        };
                                                                        httpSiegeschemin1.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegeschemin1.send();

                                                                    };
                                                                       let prochemintra1 = document.querySelector('#idcheminsheur1');
                                                                    if (prochemintra1 !== null)
                                                                        prochemintra1.onchange = () => 
                                                                        {  
                                                                            
                                                                            document.querySelector('#psiegesitines2').options.length = 1;

                                                                            const httpPrixittransite1 = new XMLHttpRequest();
                                                                                const transselitine1 = document.querySelector('#idcheminsheur1')
                                                                            .options[document.querySelector('#idcheminsheur1').options.selectedIndex].value;
                                                                                var post_trans1 = transselitine1.split('/');
                                                                            var itinetras1 = post_trans1[0];
                                                                            var dbitra1 = post_trans1[1];
                                                                            var fnitra1 = post_trans1[2];
                                                                            var lhertra1 = post_trans1[3];
                                                                            var prixtra1 = post_trans1[4];

                                                                                httpPrixittransite1.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetras1}`, true);
                                                                                httpPrixittransite1.onload = () => 
                                                                                {
                                                                                    const donprixitran1 = JSON.parse(httpPrixittransite1.responseText);
                                                                                    if (Object.entries(donprixitran1).length >= 1) {
                                                                                        for (let key in Object.entries(donprixitran1)) 
                                                                                        {
                                                                                            document.querySelector('#prix_axetransit1').value = `${prixtra1}`;
                                                                                            document.querySelector('#catetransit1').value = `${donprixitran1[key].categori}`;
                                                                                            document.querySelector('#gidtrans1').value =  `${donprixitran1[key].gareidentif}`;
                                                                                            document.querySelector('#nomitintrans2').value = `${donprixitran1[key].nom_ligne}`;
                                                                                            document.querySelector('#ligntrans2').value = `${donprixitran1[key].ident_ligne}`;
                                                                                        }
                                                                                    }
                                                                                };
                                                                                httpPrixittransite1.setRequestHeader('Content-Type', 'application/json');
                                                                                httpPrixittransite1.send();
                                                                      
                                                                                

                                                                                const httpRequetteitra1 = new XMLHttpRequest();
                                                                        
                                                                                    httpRequetteitra1.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetras1}/${dbitra1}/${fnitra1}`, true);
                                                                                httpRequetteitra1.onload = () => {
                                                                                    const dattaitra1 = JSON.parse(httpRequetteitra1.responseText);
                                                                                    console.debug(`${typeof dattaitra1} - ${dattaitra1.attributes}`, console.memory);
                                                                                    if (Object.entries(dattaitra1).length >= 1) {
                                                                                        for (let key in Object.entries(dattaitra1)) {
                                                                                            
                                                                                            let opt = document.createElement('option');
                                                                                            opt.value = `${dattaitra1[key].siege_num}`;
                                                                                            opt.innerHTML = `${dattaitra1[key].siege_num}`;
                                                                                            document.querySelector('#psiegesitines2').add(opt);
                                                                                            
                                                                                        }
                                                                                        
                                                                                    } else {
                                                                                        document.querySelector('#psiegesitines2').options.length = 1;
                                                                                    }
                                                                                };
                                                                                httpRequetteitra1.setRequestHeader('Content-Type', 'application/json');
                                                                                httpRequetteitra1.send();
                                                                        };

                                                                       let progsieges2 = document.querySelector('#psiegesitines2');
                                                                        if (progsieges2 !== null) 
                                                                        {
                                                                            progsieges2.onchange = () => 
                                                                            {

                                                                               const gareidentiftrans4 = document.querySelector('#gidtrans1').value;
                                                                                const httpsousgare4 = new XMLHttpRequest();
                                                                                httpsousgare4.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftrans4}`, true);
                                                                                httpsousgare4.onload = () => 
                                                                                {
                                                                                    const donsousg4 = JSON.parse(httpsousgare4.responseText);
                                                                                    console.debug(`${typeof donsousg4}-${donsousg4.attributes}`, console.memory);
                                                                                    if (Object.entries(donsousg4).length >= 1) {
                                                                                        for (let key in Object.entries(donsousg4)) 
                                                                                        {
                                                                                            let opt = document.createElement('option');
                                                                                            opt.value = `${donsousg4[key].idsousgare}`;
                                                                                            opt.innerHTML = `${donsousg4[key].nomsousgare}`;
                                                                                            document.querySelector('#transitedepargare3').add(opt);
                                
                                                                                        }
                                                                                    }
                                                                                };
                                                                                httpsousgare4.setRequestHeader('Content-Type', 'application/json');
                                                                                httpsousgare4.send();
                                                                                    const transselitine2 = document.querySelector('#idcheminsheur1')
                                                                                .options[document.querySelector('#idcheminsheur1').options.selectedIndex].value;
                                                                                var post_trans2 = transselitine2.split('/');
                                                                                var itinetras2 = post_trans2[0];
                                                                    
                                                                                let httpSieges2;
                                                                                httpSieges2 = new XMLHttpRequest();
                                                                                const sigs2 = document.querySelector('#psiegesitines2')
                                                                                .options[document.querySelector('#psiegesitines2').options.selectedIndex].value;

                                                                                httpSieges2.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetras2}/${sigs2}`, true);
                                                                                httpSieges2.onload = () => 
                                                                                {
                                                                                    const donsge2 = JSON.parse(httpSieges2.responseText);
                                                                                    if(donsge2 == '')
                                                                                    {
                                                                                        let httpSiegs2;
                                                                                        httpSiegs2 = new XMLHttpRequest();

                                                                                        httpSiegs2.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetras2}/${sigs2}`, true);
                                                                                        httpSiegs2.onload = () => 
                                                                                        {
                                                                                            const dong2 = JSON.parse(httpSiegs2.responseText);
                                                                                            document.querySelector('#mess').style.display = 'none';
                                                                                            if (Object.entries(dong2).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(dong2)) {
                                                                                                        document.querySelector('#idtampo2').value = `${dong2[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselect2').value = `${dong2[key].numsieg}`;
                                                                                                    }
                                                                                                }
                                                                                        };
                                                                                        httpSiegs2.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegs2.send();
                                                                                    }
                                                                                    else {
                                                                                        document.querySelector('#psiegesitines2').value = '';     
                                                                                        if (Object.entries(donsge2).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(donsge2)) {
                                                                                                document.querySelector('#idtampo2').value = `${donsge2[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselect2').value = `${donsge2[key].numsieg}`;
                                                                                            }

                                                                                        }
                                                                                        document.querySelector('#mess').style.display = 'block';
                                                                                        document.querySelector('#erreurMess').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                                };
                                                                                httpSieges2.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSieges2.send();

                                                                            };
                                                                        }
                                                                }   

                                                                //troisieme transite
                                                               let progchemin2 = document.querySelector('#idchemins2');
                                                                if (progchemin2 !== null) 
                                                                {
                                                                    progchemin2.onchange = () => 
                                                                    {
                                                                        document.querySelector('#idcheminsheur2').options.length = 1;
                                                                        document.querySelector('#psiegesitines3').options.length = 1;

                                                                        const prostranschemin42 = document.querySelector('#idchemins2')
                                                                        .options[document.querySelector('#idchemins2').options.selectedIndex].value;

                                                                        var post_typgare42 = prostranschemin42.split('-');
                                                                        var seltypgare42 = post_typgare42[0];
                                                                        var typgaresel41 = post_typgare42[1];
                                                                        
                                                                        let httpSiegeschemin2;
                                                                        httpSiegeschemin2 = new XMLHttpRequest();
                                                                        
                                                                        var datedepart = document.querySelector('#date_depheure').value;
                                                                        var tfbs = document.querySelector('#tarifattrib').value;

                                                                        const prostranschemin2 = document.querySelector('#idchemins2')
                                                                        .options[document.querySelector('#idchemins2').options.selectedIndex].value;

                                                                        httpSiegeschemin2.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemintr/${prostranschemin2}/${datedepart}/${tfbs}`, true);
                                                                        httpSiegeschemin2.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschem2 = JSON.parse(httpSiegeschemin2.responseText);
                                                                                    if (Object.entries(dongtranschem2).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtranschem2)) {
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dongtranschem2[key].code_progr}/${dongtranschem2[key].intervalle1}/${dongtranschem2[key].intervalle2}/${dongtranschem2[key].id_ligneheure}/${dongtranschem2[key].prix}`;
                                                                                                opt.innerHTML = `${dongtranschem2[key].heure}/${dongtranschem2[key].date_progr}`;
                                                                                                document.querySelector('#idcheminsheur2').add(opt);
                                                                                            }
                                                                                        }
                                                                        };
                                                                        httpSiegeschemin2.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegeschemin2.send();

                                                                    };
                                                                      let prochemintra2 = document.querySelector('#idcheminsheur2');
                                                                    if (prochemintra2 !== null)
                                                                        prochemintra2.onchange = () => 
                                                                        {  
                                                                            
                                                                            document.querySelector('#psiegesitines3').options.length = 1;

                                                                            const httpPrixittransite2 = new XMLHttpRequest();
                                                                                const transselitine2 = document.querySelector('#idcheminsheur2')
                                                                            .options[document.querySelector('#idcheminsheur2').options.selectedIndex].value;
                                                                                var post_trans2 = transselitine2.split('/');
                                                                            var itinetras2 = post_trans2[0];
                                                                            var dbitra2 = post_trans2[1];
                                                                            var fnitra2 = post_trans2[2];
                                                                            var lhertra2 = post_trans2[3];
                                                                            var prixtra2 = post_trans2[4];

                                                                                httpPrixittransite2.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetras2}`, true);
                                                                                httpPrixittransite2.onload = () => 
                                                                                {
                                                                                    const donprixitran2 = JSON.parse(httpPrixittransite2.responseText);
                                                                                    if (Object.entries(donprixitran2).length >= 1) {
                                                                                        for (let key in Object.entries(donprixitran2)) 
                                                                                        {
                                                                                            document.querySelector('#prix_axetransit2').value = `${prixtra2}`;
                                                                                            document.querySelector('#catetransit2').value = `${donprixitran2[key].categori}`;
                                                                                            document.querySelector('#gidtrans2').value =  `${donprixitran2[key].gareidentif}`;
                                                                                            document.querySelector('#nomitintrans3').value = `${donprixitran2[key].nom_ligne}`;
                                                                                            document.querySelector('#ligntrans3').value = `${donprixitran2[key].ident_ligne}`;
                                                                                        }
                                                                                    }
                                                                                };
                                                                                httpPrixittransite2.setRequestHeader('Content-Type', 'application/json');
                                                                                httpPrixittransite2.send();
                                                                      
                                                                                

                                                                                const httpRequetteitra2 = new XMLHttpRequest();
                                                                        
                                                                                    httpRequetteitra2.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetras2}/${dbitra2}/${fnitra2}`, true);
                                                                                httpRequetteitra2.onload = () => {
                                                                                    const dattaitra2 = JSON.parse(httpRequetteitra2.responseText);
                                                                                    console.debug(`${typeof dattaitra2} - ${dattaitra2.attributes}`, console.memory);
                                                                                    if (Object.entries(dattaitra2).length >= 1) {
                                                                                        for (let key in Object.entries(dattaitra2)) {
                                                                                            
                                                                                            let opt = document.createElement('option');
                                                                                            opt.value = `${dattaitra2[key].siege_num}`;
                                                                                            opt.innerHTML = `${dattaitra2[key].siege_num}`;
                                                                                            document.querySelector('#psiegesitines3').add(opt);
                                                                                            
                                                                                        }
                                                                                        
                                                                                    } else {
                                                                                        document.querySelector('#psiegesitines3').options.length = 1;
                                                                                    }
                                                                                };
                                                                                httpRequetteitra2.setRequestHeader('Content-Type', 'application/json');
                                                                                httpRequetteitra2.send();
                                                                        };

                                                                       let progsieges3 = document.querySelector('#psiegesitines3');
                                                                        if (progsieges3 !== null) 
                                                                        {
                                                                            progsieges3.onchange = () => 
                                                                            {


                                                                               const gareidentiftrans5 = document.querySelector('#gidtrans2').value;
                                                                                const httpsousgare5 = new XMLHttpRequest();
                                                                                httpsousgare5.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftrans5}`, true);
                                                                                httpsousgare5.onload = () => 
                                                                                {
                                                                                    const donsousg5 = JSON.parse(httpsousgare5.responseText);
                                                                                    console.debug(`${typeof donsousg5}-${donsousg5.attributes}`, console.memory);
                                                                                    if (Object.entries(donsousg5).length >= 1) {
                                                                                        for (let key in Object.entries(donsousg5)) 
                                                                                        {
                                                                                            let opt = document.createElement('option');
                                                                                            opt.value = `${donsousg5[key].idsousgare}`;
                                                                                            opt.innerHTML = `${donsousg5[key].nomsousgare}`;
                                                                                            document.querySelector('#transitedepargare4').add(opt);
                                
                                                                                        }
                                                                                    }
                                                                                };
                                                                                httpsousgare5.setRequestHeader('Content-Type', 'application/json');
                                                                                httpsousgare5.send();
                                                                                    const transselitine3 = document.querySelector('#idcheminsheur2')
                                                                                .options[document.querySelector('#idcheminsheur2').options.selectedIndex].value;
                                                                                var post_trans3 = transselitine3.split('/');
                                                                                var itinetras3 = post_trans3[0];
                                                                    
                                                                                let httpSieges3;
                                                                                httpSieges3 = new XMLHttpRequest();
                                                                                const sigs3 = document.querySelector('#psiegesitines3')
                                                                                .options[document.querySelector('#psiegesitines3').options.selectedIndex].value;

                                                                                httpSieges3.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetras3}/${sigs3}`, true);
                                                                                httpSieges3.onload = () => 
                                                                                {
                                                                                    const donsge3 = JSON.parse(httpSieges3.responseText);
                                                                                    if(donsge3 == '')
                                                                                    {
                                                                                        let httpSiegs3;
                                                                                        httpSiegs3 = new XMLHttpRequest();

                                                                                        httpSiegs3.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetras3}/${sigs3}`, true);
                                                                                        httpSiegs3.onload = () => 
                                                                                        {
                                                                                            const dong3 = JSON.parse(httpSiegs3.responseText);
                                                                                            document.querySelector('#mess').style.display = 'none';
                                                                                            if (Object.entries(dong3).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(dong3)) {
                                                                                                        document.querySelector('#idtampo3').value = `${dong3[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselect3').value = `${dong3[key].numsieg}`;
                                                                                                    }
                                                                                                }
                                                                                        };
                                                                                        httpSiegs3.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegs3.send();
                                                                                    }
                                                                                    else {
                                                                                        document.querySelector('#psiegesitines3').value = '';     
                                                                                        if (Object.entries(donsge3).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(donsge3)) {
                                                                                                document.querySelector('#idtampo3').value = `${donsge3[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselect3').value = `${donsge3[key].numsieg}`;
                                                                                            }

                                                                                        }
                                                                                        document.querySelector('#mess').style.display = 'block';
                                                                                        document.querySelector('#erreurMess').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                                };
                                                                                httpSieges3.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSieges3.send();

                                                                            };
                                                                        }
                                                                }            
                                                            }
                                                                
                                                        }
                                                    }
                                        };
                                        httpRequestitine.setRequestHeader('Content-Type', 'application/json');
                                        httpRequestitine.send();
                                    } 
                                    else 
                                    {       
                                        
                                        document.querySelector('#smsdt').style.display = 'none';
                                        document.querySelector('#date_depheure').style.color = "black";
                                        document.querySelector('#date_depheure').style.border = "1px solid";
                                        if (Object.entries(dataAxe).length >= 1) 
                                        {
                                                
                                            
    
                                            for (let key in Object.entries(dataAxe)) {
                                                    let opt = document.createElement('option');
                                                    opt.value = `${dataAxe[key].id_ligneheure}/${dataAxe[key].heure}`;
                                                    opt.innerHTML = `${dataAxe[key].heure}`;
                                                    document.querySelector('#hdepart').add(opt);
                                                }
                                        } else {
                                            document.querySelector('#hdepart').options.length = 1;
                                        }
                                    }

                                        let hrdepart = document.querySelector('#hdepart');
                                        if (hrdepart !== null) {
                                            hrdepart.onchange = () => 
                                            {
                                                document.querySelector('#psieges').options.length = 1;
                                                document.querySelector('#typegare').value = '';
                                                const httpRequest = new XMLHttpRequest();
                                                const sele = document.querySelector('#hdepart')
                                                    .options[document.querySelector('#hdepart').options.selectedIndex].value;

                                                    var post_lh = sele.split('/');
                                                    var sel = post_lh[0];
                                                    var lhsel = post_lh[1];

                                                    const dpt_date = document.querySelector('#date_depheure').value;
                                                    //var typgare = document.querySelector('#arrsgare').value;
                                                    var typgarepa = document.querySelector('#arrsgare').value;
                                                    var artypgarepa1 = typgarepa.split('/');
                                                    var typgare = artypgarepa1[0];
                                                    var typgare2 = artypgarepa1[1];
                                                    
                                                    const httptypegare = new XMLHttpRequest();
                                                    httptypegare.open('GET', window.location.origin + `${APP_ROOT}/programmes/gareprincipale/${typgare}/${lhsel}`, true);
                                                    httptypegare.onload = () => 
                                                    {
                                                        const dongare = JSON.parse(httptypegare.responseText);
                                                        if (Object.entries(dongare).length >= 1)
                                                        for (let key in Object.entries(dongare)) 
                                                        document.querySelector('#typegare').value = `${dongare[key].typestatutgare}`;
                                                    };
                                                    httptypegare.setRequestHeader('Content-Type', 'application/json');
                                                    httptypegare.send();

                                                


                                                httpRequest.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifprog/${seltdep}-${arr}/${dpt_date}/${sel}`, true);
                                                httpRequest.onload = () => 
                                                {
                                                    var typ_gare = document.querySelector('#typegare').value;    
                                                    const don = JSON.parse(httpRequest.responseText);
                                                        console.debug(`${typeof don} - ${don.attributes}`, console.memory);
                                                        //const tabe = [];
                                                        if (don == '') 
                                                        {
                                                            if(typ_gare == 'Principale'){
                                                                
                                                                    let opt = document.createElement('option');
                                                                    opt.value = 1;
                                                                    opt.innerHTML = 1;
                                                                    document.querySelector('#psieges').add(opt);
                                                            
                                                                    departpsieges = document.querySelector('#psieges');
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
                                                                                        document.querySelector('#program').value = `${dons[key].code_progr}`;
                                                                                        document.querySelector('#tarifattrib').value = `${dons[key].typetarif}`;
                                                                                        document.querySelector('#cate').value = `${dons[key].categorie}`;
                                                                                        document.querySelector('#depligne').value = `${dons[key].gareidentif}`;
                                                                                        document.querySelector('#lign').value = `${dons[key].ident_ligne}`;
                                                                                        document.querySelector('#nomitin').value = `${dons[key].nom_ligne}`;
                                                                                        document.querySelector('#prix_axe').value = `${dons[key].prix}`;
                                                                                    }
                                                                                        let httpSiege;
                                                                                        httpSiege = new XMLHttpRequest();
                                                                                        const sig = document.querySelector('#psieges')
                                                                                        .options[document.querySelector('#psieges').options.selectedIndex].value;
                                                                                        const pro = document.querySelector('#program').value;
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
                                                                                                    document.querySelector('#mess').style.display = 'none';
                                                                                                    if (Object.entries(donsg2).length >= 1)
                                                                                                        {
                                                                                                            for (let key in Object.entries(donsg2)) {
                                                                                                                document.querySelector('#idtampo').value = `${donsg2[key].idtamp}`;                    
                                                                                                                document.querySelector('#siegselect').value = `${donsg2[key].numsieg}`;
                                                                                                            }
                                                                                                        }
                                                                                                };
                                                                                                httpSieg.setRequestHeader('Content-Type', 'application/json');
                                                                                                httpSieg.send();
                                                                                            }
                                                                                            else 
                                                                                            {
                                                                                                document.querySelector('#psieges').value = ''; 
                                                                                                if (Object.entries(donsg).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(donsg)) 
                                                                                                    {
                                                                                                        document.querySelector('#idtampo').value = `${donsg[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselect').value = `${donsg[key].numsieg}`;
                                                                                                    }
        
                                                                                                }
                                                                                                document.querySelector('#mess').style.display = 'block';
                                                                                                document.querySelector('#erreurMess').innerHTML = `Siege déjà utilisé.`;                   
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
                                                                    document.querySelector('#program').value = `${don[key].code_progr}`;
                                                                    document.querySelector('#tarifattrib').value = `${don[key].typetarif}`;
                                                                    document.querySelector('#datepr').value = `${don[key].date_progr}`;
                                                                    document.querySelector('#depligne').value = `${don[key].gareidentif}`;
                                                                    document.querySelector('#inter1').value = `${don[key].intervalle1}`;
                                                                    document.querySelector('#inter2').value = `${don[key].intervalle2}`;
                                                                    document.querySelector('#lign').value = `${don[key].ident_ligne}`;
                                                                    document.querySelector('#nomitin').value = `${don[key].nom_ligne}`;
                                                                    document.querySelector('#her').value = `${don[key].heure}`;
                                                                    document.querySelector('#cate').value = `${don[key].categori}`;

                                                                }
                                                            } 
                                                            
                                                            var tfbs = document.querySelector('#tarifattrib').value;
                                                            const httpPrix = new XMLHttpRequest();
                                                            httpPrix.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifpriprg/${sel}/${tfbs}`, true);
                                                            httpPrix.onload = () => 
                                                            {

                                                                const donprix = JSON.parse(httpPrix.responseText);
                                                                console.debug(`${typeof donprix}-${donprix.attributes}`, console.memory);
                                                                if (Object.entries(donprix).length >= 1) {
                                                                    for (let key in Object.entries(donprix)) 
                                                                    {
                                                                        document.querySelector('#prix_axe').value = `${donprix[key].prix}`;
            
                                                                    }
                                                                }
                                                            };
                                                            httpPrix.setRequestHeader('Content-Type', 'application/json');
                                                            httpPrix.send();
                                                            
                                                            const httpRequette = new XMLHttpRequest();
                                                            const cdprog = document.querySelector('#program').value;
                                                            const db = document.querySelector('#inter1').value;
                                                            const fn = document.querySelector('#inter2').value;
                                                            const lg = document.querySelector('#nomitin').value;
                                                            const tim = document.querySelector('#her').value;
                                                                httpRequette.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${cdprog}/${dpt_date}/${lg}/${tim}/${db}/${fn}`, true);
                                                            httpRequette.onload = () => {
                                                                const datta = JSON.parse(httpRequette.responseText);
                                                                console.debug(`${typeof datta} - ${datta.attributes}`, console.memory);
                                                                if (Object.entries(datta).length >= 1) {
                                                                    for (let key in Object.entries(datta)) {
                                                                        
                                                                        let opt = document.createElement('option');
                                                                        opt.value = `${datta[key].siege_num}`;
                                                                        opt.innerHTML = `${datta[key].siege_num}`;
                                                                        document.querySelector('#psieges').add(opt);
                                                                        
                                                                    }
                                                                    
                                                                } else {
                                                                    document.querySelector('#psieges').options.length = 1;
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
                            document.querySelector('#date_depheure').style.color = "#FF0000";
                            document.querySelector('#date_depheure').style.border = "2px solid #FF0000";
                            document.querySelector('#smsdt').style.display = 'block';
                            document.querySelector('#erreurSmsdt').innerHTML = `Date non valide.`;
                        }
                    

                };
                
            }
            let progsieges = document.querySelector('#psieges');
            if (progsieges !== null) {
                progsieges.onchange = () => 
                {
                    let httpSieges;
                    httpSieges = new XMLHttpRequest();
                    const sigs = document.querySelector('#psieges')
                    .options[document.querySelector('#psieges').options.selectedIndex].value;
                    const pros = document.querySelector('#program').value;

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
                                document.querySelector('#mess').style.display = 'none';
                                if (Object.entries(dong).length >= 1)
                                    {
                                        for (let key in Object.entries(dong)) {
                                            document.querySelector('#idtampo').value = `${dong[key].idtamp}`;                    
                                            document.querySelector('#siegselect').value = `${dong[key].numsieg}`;
                                        }
                                    }
                            };
                            httpSiegs.setRequestHeader('Content-Type', 'application/json');
                            httpSiegs.send();
                        }
                        else {
                            document.querySelector('#psieges').value = '';     
                            if (Object.entries(donsge).length >= 1)
                            {
                                for (let key in Object.entries(donsge)) {
                                    document.querySelector('#idtampo').value = `${donsge[key].idtamp}`;                    
                                    document.querySelector('#siegselect').value = `${donsge[key].numsieg}`;
                                }

                            }
                            document.querySelector('#mess').style.display = 'block';
                            document.querySelector('#erreurMess').innerHTML = `Siege déjà utilisé.`;                                                                   }
                    };
                    httpSieges.setRequestHeader('Content-Type', 'application/json');
                    httpSieges.send();

                
                };
            }
           
            let infdoc = document.querySelector('#cltype');
        if (infdoc !== null)
            infdoc.onchange = () => 
            {
                let httpDocs;
                if (window.XMLHttpRequest) {
                    httpDocs = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpDocs = new ActiveXObject("Microsoft.XMLHTTP");
                }
                var docum = document.querySelector('#cltype').value;
                
                if (docum == 'Adulte') {
                    document.querySelector('#motif').style.display = 'none';
                    document.querySelector('#motifrefus').style.display = 'none';
                    document.querySelector('#doc').style.display = 'none';
                    document.querySelector('#docdelivre').style.display = 'none';
                    document.querySelector('#datedocdel').style.display = 'none';
                    document.querySelector('#num_doc').style.display = 'none';
                    document.querySelector('#rclient').style.display = 'block';
                    document.querySelector('#prnclient').style.display = 'block';
                    document.querySelector('#cnib').style.display = 'block';
                    document.querySelector('#date_cnib').style.display = 'block';
                    document.querySelector('#lieudelivre').style.display = 'block';
                    console.debug(`${docum}`, console.memory);

                } 
                    if (docum == 'Etudiant') {
                        document.querySelector('#doc').style.display = 'block';
                        document.querySelector('#num_doc').style.display = 'block';
                        document.querySelector('#docdelivre').style.display = 'block';
                        document.querySelector('#datedocdel').style.display = 'block';
                        document.querySelector('#rclient').style.display = 'block';
                        document.querySelector('#prnclient').style.display = 'block';
                        document.querySelector('#cnib').style.display = 'none';
                        document.querySelector('#date_cnib').style.display = 'none';
                        document.querySelector('#lieudelivre').style.display = 'none';
                        console.debug(`${docum}`, console.memory);

                    } 
                    if (docum == 'Elève') {
                        document.querySelector('#doc').style.display = 'block';
                        document.querySelector('#num_doc').style.display = 'block';
                        document.querySelector('#docdelivre').style.display = 'block';
                        document.querySelector('#datedocdel').style.display = 'block';
                        document.querySelector('#rclient').style.display = 'block';
                        document.querySelector('#prnclient').style.display = 'block';
                        document.querySelector('#cnib').style.display = 'none';
                        document.querySelector('#date_cnib').style.display = 'none';
                        document.querySelector('#lieudelivre').style.display = 'none';
                        console.debug(`${docum}`, console.memory);

                    } 
                    if (docum == 'Enfant') {
                        document.querySelector('#doc').style.display = 'block';
                        document.querySelector('#num_doc').style.display = 'block';
                        document.querySelector('#docdelivre').style.display = 'block';
                        document.querySelector('#datedocdel').style.display = 'block';
                        document.querySelector('#rclient').style.display = 'block';
                        document.querySelector('#prnclient').style.display = 'block';
                        document.querySelector('#cnib').style.display = 'none';
                        document.querySelector('#date_cnib').style.display = 'none';
                        document.querySelector('#lieudelivre').style.display = 'none';
                        console.debug(`${docum}`, console.memory);

                    } 
                    if (docum == 'Autres') {
                        document.querySelector('#motif').style.display = 'block';
                        document.querySelector('#motifrefus').style.display = 'block';
                        document.querySelector('#rclient').style.display = 'block';
                        document.querySelector('#prnclient').style.display = 'block';
                        document.querySelector('#cnib').style.display = 'none';
                        document.querySelector('#date_cnib').style.display = 'none';
                        document.querySelector('#lieudelivre').style.display = 'none';
                        document.querySelector('#doc').style.display = 'none';
                        document.querySelector('#num_doc').style.display = 'none';
                        document.querySelector('#docdelivre').style.display = 'none';
                        document.querySelector('#datedocdel').style.display = 'none';
                        console.debug(`${docum}`, console.memory);

                    } 
                    
            };

            
        //recherche d'information du client depart principal
        let inf = document.querySelector('#rnclient_contact');
        if (inf !== null && inf.dataset.guarded !== '1') {
            inf.dataset.guarded = '1';
            inf.addEventListener('keyup', () => {
                const rawPhone = inf.value.trim();
                const digits = AppRequestGuard.phoneDigits(rawPhone);
                if (digits.length < 7) {
                    return;
                }
                AppRequestGuard.debounce('verifinfos', () => {
                    AppRequestGuard.getJson(
                        window.location.origin + `${APP_ROOT}/programmes/verifinfos/${encodeURIComponent(rawPhone)}`,
                        'verifinfos',
                        (httpInfos) => {
                            let infos = null;
                            try {
                                infos = JSON.parse(httpInfos.responseText);
                            } catch (err) {
                                return;
                            }
                            if (infos == null || Object.keys(infos).length < 1) {
                                document.querySelector('#pascompagnie').value = '';
                                return;
                            }
                            if (AppRequestGuard.phonesMatch(infos.contact_client, rawPhone)) {
                                document.querySelector('#rclient').value = `${infos.nom_client || ''}`;
                                document.querySelector('#prnclient').value = `${infos.prenom_client || ''}`;
                                document.querySelector('#cnib').value = `${infos.num_CNIB || ''}`;
                                document.querySelector('#date_cnib').value = `${infos.date_delivre || ''}`;
                                document.querySelector('#lieudelivre').value = `${infos.lieu_delivre || ''}`;
                                document.querySelector('#pascompagnie').value = `${infos.id_client || ''}`;
                                document.querySelector('#rclientcp').value = `${infos.nom_client || ''}`;
                                document.querySelector('#prnclientcp').value = `${infos.prenom_client || ''}`;
                                document.querySelector('#cnibcp').value = `${infos.num_CNIB || ''}`;
                                document.querySelector('#date_cnibcp').value = `${infos.date_delivre || ''}`;
                                document.querySelector('#lieudelivrecp').value = `${infos.lieu_delivre || ''}`;
                            } else {
                                document.querySelector('#pascompagnie').value = '';
                            }
                        }
                    );
                }, 400);
            });
        }
            
            let butonclic = document.querySelector('#idreset');
            if (butonclic !== null) {
                butonclic.onclick = () => 
                {
                    let httpSiegeselect;
                    httpSiegeselect = new XMLHttpRequest();
                    const siegselect = document.querySelector('#siegselect').value;
                    //const pros = document.querySelector('#program').value;
                    const idtap = document.querySelector('#idtampo').value;
                    httpSiegeselect.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtap}/${siegselect}`, true);
                    httpSiegeselect.onload = () => 
                    {
                        const donselect= JSON.parse(httpSiegeselect.responseText);
                        console.debug(`${typeof donselect} - ${donselect.attributes}`, console.memory);
                        document.querySelector('#mess').style.display = 'none';
                        
                    };
                    httpSiegeselect.setRequestHeader('Content-Type', 'application/json');
                    httpSiegeselect.send();

                
                };
            }
                
                e.onclick = function () {   
                    let taForm = document.querySelector('#taForm');
                    
                    taForm.setAttribute('action', `${APP_ROOT}/Programmes/addpassager/${e.dataset.cle_compagnie}`);
                    AppRequestGuard.ensureNonce('#taForm', 'sale_nonce');
                    AppRequestGuard.guardForm('#taForm');
                }

                var taFormEl = document.querySelector('#taForm');
                if (taFormEl && !taFormEl.dataset.salePrepared) {
                    taFormEl.dataset.salePrepared = '1';
                    taFormEl.addEventListener('submit', function () {
                        AppRequestGuard.ensureNonce('#taForm', 'sale_nonce');
                        AppRequestGuard.syncClientMirror([
                            ['#rclient', '#rclientcp'],
                            ['#prnclient', '#prnclientcp'],
                            ['#cnib', '#cnibcp'],
                            ['#date_cnib', '#date_cnibcp'],
                            ['#lieudelivre', '#lieudelivrecp']
                        ]);
                    });
                }

                AppRequestGuard.guardForm('#taForm');
                AppRequestGuard.ensureNonce('#taForm', 'sale_nonce');
    })

});
;
/* --- addventeticketfi.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addventeticketfi').forEach(function (e) 
    {
        document.querySelector('h3#tafiTitle').innerHTML = `VENTE DE FIDELITE`;

            let arfi= document.querySelector('#arrsgarefid');
            if (arfi !== null)
            arfi.onchange = () => {
                document.querySelector('#prix_axefid').value = '';
                document.querySelector('#prix_axefid').value = '';
                document.querySelector('#date_depheurefid').value = '';
                document.querySelector('#hdepartfid').options.length = 1;
                document.querySelector('#quartierfid').options.length = 1;
                document.querySelector('#psiegesfid').options.length = 1;
                document.querySelector('#hdepartitinefid').options.length = 1;
                document.querySelector('#psiegesitinesfid').options.length = 1;
                document.querySelector('#idcheminsheurfid').options.length = 1;
                document.querySelector('#transitedepargare1fid').options.length = 1;
                document.querySelector('#transitedepargare2fid').options.length = 1;
                document.querySelector('#transitedepargare3fid').options.length = 1;
                document.querySelector('#transitedepargare4fid').options.length = 1;
                document.querySelector('#idcheminsfid').options.length = 1;
                document.querySelector('#idchemins1fid').options.length = 1;
                document.querySelector('#idchemins2fid').options.length = 1;
                document.querySelector('#psiegesitines1fid').options.length = 1;
                document.querySelector('#idcheminsheur1fid').options.length = 1;
                document.querySelector('#psiegesitines2fid').options.length = 1;
                document.querySelector('#idcheminsheur2fid').options.length = 1;
                document.querySelector('#psiegesitines3fid').options.length = 1;
                document.querySelector('#quartier1fid').options.length = 1;
                document.querySelector('#quartier2fid').options.length = 1;
                document.querySelector('#quartier3fid').options.length = 1;
                    const typgarefi = document.querySelector('#arrsgarefid').value;
                    let httptypequartfi;
                    httptypequartfi = new XMLHttpRequest();
                    
                    httptypequartfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${typgarefi}`, true);
                    httptypequartfi.onload = () => 
                    {
                        const donquafi = JSON.parse(httptypequartfi.responseText);
                        if (donquafi == '') {
                            document.querySelector('#quartierfid').options.length = 1;
                        }
                        else{
                            if (Object.entries(donquafi).length >= 1) {
                                            
                                for (let key in Object.entries(donquafi)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${donquafi[key].nom_quartier}`;
                                    opt.innerHTML = `${donquafi[key].nom_quartier}`;
                                    document.querySelector('#quartierfid').add(opt);
                                }
                            } else {
                                document.querySelector('#quartierfid').options.length = 1;
                            }
                        }
                        

                    };
                    httptypequartfi.setRequestHeader('Content-Type', 'application/json');
                    httptypequartfi.send();
            };
            
            let dafi = document.querySelector('#date_depheurefid');
            if (dafi !== null){
                dafi.onchange = () => 
                {
                    
                    document.querySelector('#hdepartfid').options.length = 1;
                    document.querySelector('#psiegesfid').options.length = 1;
                    document.querySelector('#hdepartitinefid').options.length = 1;
                    document.querySelector('#psiegesitinesfid').options.length = 1;
                    document.querySelector('#idcheminsheurfid').options.length = 1;
                    //document.querySelector('#lignesitinerairefid').value = '';
                    document.querySelector('#transitedepargare1fid').options.length = 1;
                    document.querySelector('#transitedepargare2fid').options.length = 1;
                    document.querySelector('#transitedepargare3fid').options.length = 1;
                    document.querySelector('#transitedepargare4fid').options.length = 1;
                    document.querySelector('#idcheminsfid').options.length = 1;
                    document.querySelector('#idchemins1fid').options.length = 1;
                    document.querySelector('#idchemins2fid').options.length = 1;
                    document.querySelector('#psiegesitines1fid').options.length = 1;
                    document.querySelector('#idcheminsheur1fid').options.length = 1;
                    document.querySelector('#psiegesitines2fid').options.length = 1;
                    document.querySelector('#idcheminsheur2fid').options.length = 1;
                    document.querySelector('#psiegesitines3fid').options.length = 1;
                    document.querySelector('#quartier1fid').options.length = 1;
                    document.querySelector('#quartier2fid').options.length = 1;
                    document.querySelector('#quartier3fid').options.length = 1;


                    let httpRequetesfid;
                    
                    if (window.XMLHttpRequest) {
                        httpRequetesfid = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpRequetesfid = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                        var depafi = document.querySelector('#depargarefid').value;
                        var arrfi = document.querySelector('#arrsgarefid').value;
                        var datedepartfi = document.querySelector('#date_depheurefid').value;
                        var dateactufi = document.querySelector('#actufid').value;
                                         
                        var post_lhdepfi = depafi.split('/');
                        var seltdepfi = post_lhdepfi[0];
                        var sougidfi = post_lhdepfi[1];
                        if(datedepartfi >= dateactufi)
                        {
                            let httpRequetesfi;
                            httpRequetesfi = new XMLHttpRequest();
                            httpRequetesfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheure/${seltdepfi}-${arrfi}/${datedepartfi}`, true);
                            httpRequetesfi.onload = () => {
                                const dataAxefi = JSON.parse(httpRequetesfi.responseText);
                                
                                    if (dataAxefi == '') {
                                        
                                        document.querySelector('#smsdtfid').style.display = 'none';
                                        document.querySelector('#date_depheurefid').style.color = "black";
                                        document.querySelector('#date_depheurefid').style.border = "1px solid";
                                        //on verifit pour voir si elle n'a pas d'itineraire
                                        let httpRequestitinefi;
                                        httpRequestitinefi = new XMLHttpRequest();
                                        httpRequestitinefi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifitine/${seltdepfi}-${arrfi}`, true);
                                        httpRequestitinefi.onload = () => {
                                                const donitinesfi = JSON.parse(httpRequestitinefi.responseText);
                                                    if(donitinesfi === null)
                                                    {
                                                        document.querySelector('#depitin1fid').style.display = 'none';
                                                        document.querySelector('#depargareitine1fid').style.display = 'none';
                                                        document.querySelector('#iddeptrans1fid').style.display = 'none';
                                                        document.querySelector('#transitedepargare1fid').style.display = 'none';
                                                        document.querySelector('#iddeptrans2fid').style.display = 'none';
                                                        document.querySelector('#transitedepargare2fid').style.display = 'none';
                                                        document.querySelector('#iddeptrans3fid').style.display = 'none';
                                                        document.querySelector('#transitedepargare3fid').style.display = 'none';
                                                        document.querySelector('#iddeptrans4fid').style.display = 'none';
                                                        document.querySelector('#transitedepargare4fid').style.display = 'none';
                                                        document.querySelector('#arritin1fid').style.display = 'none';
                                                        document.querySelector('#arrsgareitine1fid').style.display = 'none';
                                                        document.querySelector('#arritin1fid').style.display = 'none';
                                                        document.querySelector('#arrsgareitine1fid').style.display = 'none';
                                                        document.querySelector('#heureitin1fid').style.display = 'none';
                                                        document.querySelector('#hdepartitine1fid').style.display = 'none';
                                                        document.querySelector('#lignesitinerairefid').style.display = 'none';
                                                        document.querySelector('#ligne1fid').style.display = 'none';
                                                        document.querySelector('#siegitine1fid').style.display = 'none';
                                                        document.querySelector('#psiegesitines1fid').style.display = 'none';
                                                        document.querySelector('#depitin2fid').style.display = 'none';
                                                        document.querySelector('#depargareitine2fid').style.display = 'none';
                                                        document.querySelector('#arritin2fid').style.display = 'none';
                                                        document.querySelector('#arrsgareitine2fid').style.display = 'none';
                                                        document.querySelector('#heureitin2fid').style.display = 'none';
                                                        document.querySelector('#hdepartitine2fid').style.display = 'none';
                                                        document.querySelector('#siegitine2fid').style.display = 'none';
                                                        document.querySelector('#psiegesitines2fid').style.display = 'none';
                                                        document.querySelector('#depitin3fid').style.display = 'none';
                                                        document.querySelector('#depargareitine3fid').style.display = 'none';
                                                        document.querySelector('#arritin3fid').style.display = 'none';
                                                        document.querySelector('#arrsgareitine3fid').style.display = 'none';
                                                        document.querySelector('#heureitin3fid').style.display = 'none';
                                                        document.querySelector('#hdepartitine3fid').style.display = 'none';
                                                        document.querySelector('#siegitine3fid').style.display = 'none';
                                                        document.querySelector('#psiegesitines3fid').style.display = 'none';
                                                        document.querySelector('#quartier1fid').style.display = 'none';
                                                        document.querySelector('#quartier2fid').style.display = 'none';
                                                        document.querySelector('#quartier3fid').style.display = 'none';
                                                        document.querySelector('#idquart1fid').style.display = 'none';
                                                        document.querySelector('#idquart2fid').style.display = 'none';
                                                        document.querySelector('#idquart3fid').style.display = 'none';

                                                        document.querySelector('#prix_axetransfid').style.display = 'none';
                                                        document.querySelector('#prix_axetransfid1').style.display = 'none';
                                                        document.querySelector('#prix_axetransitfid1').style.display = 'none';
                                                        document.querySelector('#prix_axetransitfid').style.display = 'none';
                                                        document.querySelector('#prix_axetransit1fid1').style.display = 'none';
                                                        document.querySelector('#prix_axetransit1fid').style.display = 'none';
                                                        document.querySelector('#prix_axetransit2fid1').style.display = 'none';
                                                        document.querySelector('#prix_axetransit2fid').style.display = 'none';
                                                        document.querySelector('#tranfid').style.display = 'none';
                                                        document.querySelector('#heureitinfid').style.display = 'none';
                                                        document.querySelector('#hdepartitinefid').style.display = 'none';
                                                        document.querySelector('#siegitinefid').style.display = 'none';
                                                        document.querySelector('#psiegesitinesfid').style.display = 'none';
                                                        document.querySelector('#hridfid').style.display = 'block';
                                                        document.querySelector('#hdepartfid').style.display = 'block';
                                                        document.querySelector('#sigidfid').style.display = 'block';
                                                        document.querySelector('#psiegesfid').style.display = 'block';
                                                        document.querySelector('#iddepfid').style.display = 'block';
                                                        document.querySelector('#depargarefid').style.display = 'block';
                                                        document.querySelector('#arridfid').style.display = 'block';
                                                        document.querySelector('#arrsgarefid').style.display = 'block';
                                                        document.querySelector('#prix_axefid1').style.display = 'block';
                                                        document.querySelector('#prix_axefid').style.display = 'block';
                                                    }
                                                    else
                                                    {
                                                        if (Object.entries(donitinesfi).length >= 1) 
                                                        {
                                                            var i = Object.entries(donitinesfi).length;
                                                            
                                                            for (let key in Object.entries(donitinesfi)) 
                                                            {
                                                                
                                                                document.querySelector('#nbrtransfid').value = Object.entries(donitinesfi).length;;
                                                                if(i === 2){
                                                                    document.querySelector('#arritin1fid').style.display = 'block';
                                                                    document.querySelector('#idcheminsfid').style.display = 'block';
                                                                    document.querySelector('#heureitin1fid').style.display = 'block';
                                                                    document.querySelector('#idcheminsheurfid').style.display = 'block';
                                                                    document.querySelector('#siegitine1fid').style.display = 'block';
                                                                    document.querySelector('#psiegesitines1fid').style.display = 'block';
                                                                    document.querySelector('#quartier1fid').style.display = 'block';
                                                                    document.querySelector('#idquart1fid').style.display = 'block';
                                                                    document.querySelector('#iddeptrans1fid').style.display = 'block';
                                                                    document.querySelector('#transitedepargare1fid').style.display = 'block';
                                                                    document.querySelector('#iddeptrans2fid').style.display = 'block';
                                                                    document.querySelector('#transitedepargare2fid').style.display = 'block';
                                                                    document.querySelector('#prix_axetransfid').style.display = 'block';
                                                                    document.querySelector('#prix_axetransfid1').style.display = 'block';
                                                                    document.querySelector('#prix_axetransitfid1').style.display = 'block';
                                                                    document.querySelector('#prix_axetransitfid').style.display = 'block';
                                                                    
                                                                }
                                                                
                                                                if(i === 3){
                                                                    document.querySelector('#iddeptrans1fid').style.display = 'block';
                                                                    document.querySelector('#transitedepargare1fid').style.display = 'block';
                                                                    document.querySelector('#iddeptrans2fid').style.display = 'block';
                                                                    document.querySelector('#transitedepargare2fid').style.display = 'block';
                                                                    document.querySelector('#iddeptrans3fid').style.display = 'block';
                                                                    document.querySelector('#transitedepargare3fid').style.display = 'block';
                                                                    document.querySelector('#arritin1fid').style.display = 'block';
                                                                    document.querySelector('#idcheminsfid').style.display = 'block';
                                                                    document.querySelector('#heureitin1fid').style.display = 'block';
                                                                    document.querySelector('#idcheminsheurfid').style.display = 'block';
                                                                    document.querySelector('#siegitine1fid').style.display = 'block';
                                                                    document.querySelector('#psiegesitines1fid').style.display = 'block';
                                                                    document.querySelector('#idquart1fid').style.display = 'block';
                                                                    document.querySelector('#idquart2fid').style.display = 'block';
                                                                                                                 document.querySelector('#arritin2fid').style.display = 'block';
                                                                    document.querySelector('#idchemins1fid').style.display = 'block';
                                                                    document.querySelector('#heureitin2fid').style.display = 'block';
                                                                    document.querySelector('#idcheminsheur1fid').style.display = 'block';
                                                                    document.querySelector('#siegitine2fid').style.display = 'block';
                                                                    document.querySelector('#psiegesitines2fid').style.display = 'block';
                                                                    document.querySelector('#quartier1fid').style.display = 'block';
                                                                    document.querySelector('#quartier2fid').style.display = 'block';
                                                                    
                                                                    document.querySelector('#prix_axetransfid').style.display = 'block';
                                                                    document.querySelector('#prix_axetransfid1').style.display = 'block';
                                                                    document.querySelector('#prix_axetransitfid1').style.display = 'block';
                                                                    document.querySelector('#prix_axetransitfid').style.display = 'block';
                                                                    document.querySelector('#prix_axetransit1fid1').style.display = 'block';
                                                                    document.querySelector('#prix_axetransit1fid').style.display = 'block';
                                                                    }if(i === 4){
                                                                    
                                                                    document.querySelector('#iddeptrans1fid').style.display = 'block';
                                                                    document.querySelector('#transitedepargare1fid').style.display = 'block';
                                                                    document.querySelector('#iddeptrans2fid').style.display = 'block';
                                                                    document.querySelector('#transitedepargare2fid').style.display = 'block';
                                                                    document.querySelector('#iddeptrans3fid').style.display = 'block';
                                                                    document.querySelector('#transitedepargare3fid').style.display = 'block';
                                                                    document.querySelector('#iddeptrans4fid').style.display = 'block';
                                                                    document.querySelector('#transitedepargare4fid').style.display = 'block';
                                                                    document.querySelector('#arritin1fid').style.display = 'block';
                                                                    document.querySelector('#idcheminsfid').style.display = 'block';
                                                                    document.querySelector('#heureitin1fid').style.display = 'block';
                                                                    document.querySelector('#idcheminsheurfid').style.display = 'block';
                                                                    document.querySelector('#siegitine1fid').style.display = 'block';
                                                                    document.querySelector('#psiegesitines1fid').style.display = 'block';
                                                                    document.querySelector('#arritin2fid').style.display = 'block';
                                                                    document.querySelector('#idchemins1fid').style.display = 'block';
                                                                    document.querySelector('#heureitin2fid').style.display = 'block';
                                                                    document.querySelector('#idcheminsheur1fid').style.display = 'block';
                                                                    document.querySelector('#siegitine2fid').style.display = 'block';
                                                                    document.querySelector('#psiegesitines2fid').style.display = 'block';
                                                                    document.querySelector('#arritin3fid').style.display = 'block';
                                                                    document.querySelector('#idchemins2fid').style.display = 'block';
                                                                    document.querySelector('#heureitin3fid').style.display = 'block';
                                                                    document.querySelector('#idcheminsheur2fid').style.display = 'block';
                                                                    document.querySelector('#siegitine3fid').style.display = 'block';
                                                                    document.querySelector('#psiegesitines3fid').style.display = 'block';
                                                                    document.querySelector('#quartier1fid').style.display = 'block';
                                                                    document.querySelector('#quartier2fid').style.display = 'block';
                                                                    document.querySelector('#quartier3fid').style.display = 'block';    
                                                                    document.querySelector('#idquart1fid').style.display = 'block';
                                                                    document.querySelector('#idquart2fid').style.display = 'block';
                                                                    document.querySelector('#idquart3fid').style.display = 'block';
                                                                    document.querySelector('#prix_axetransfid').style.display = 'block';
                                                                    document.querySelector('#prix_axetransfid1').style.display = 'block';
                                                                    document.querySelector('#prix_axetransitfid1').style.display = 'block';
                                                                    document.querySelector('#prix_axetransitfid').style.display = 'block';
                                                                    document.querySelector('#prix_axetransit1fid1').style.display = 'block';
                                                                    document.querySelector('#prix_axetransit1fid').style.display = 'block';
                                                                    document.querySelector('#prix_axetransit2fid1').style.display = 'block';
                                                                    document.querySelector('#prix_axetransit2fid').style.display = 'block';
                                                                

                                                                }
                                                                document.querySelector('#tranfid').style.display = 'block';
                                                                document.querySelector('#heureitinfid').style.display = 'block';
                                                                document.querySelector('#hdepartitinefid').style.display = 'block';
                                                                document.querySelector('#lignesitinerairefid').style.display = 'block';
                                                                document.querySelector('#ligne1fid').style.display = 'block';
                                                                document.querySelector('#siegitinefid').style.display = 'block';
                                                                document.querySelector('#psiegesitinesfid').style.display = 'block';
                                                                document.querySelector('#hridfid').style.display = 'none';
                                                                document.querySelector('#hdepartfid').style.display = 'none';
                                                                document.querySelector('#sigidfid').style.display = 'none';
                                                                document.querySelector('#psiegesfid').style.display = 'none';
                                                                document.querySelector('#iddepfid').style.display = 'none';
                                                                document.querySelector('#depargarefid').style.display = 'none';
                                                                document.querySelector('#arridfid').style.display = 'none';
                                                                document.querySelector('#arrsgarefid').style.display = 'none';

                                                                document.querySelector('#prix_axefid1').style.display = 'none';
                                                                document.querySelector('#prix_axefid').style.display = 'none';
                                                                document.querySelector('#itinecodefid').value = `${donitinesfi[0].code_itineraires}`;

                                                                
                                                                document.querySelector('#lignetinerairefid').value = `${donitinesfi[0].nom_itineraires}`;
                                                            }
                                                            
                                                
                                                            if(i === 2)
                                                            {
                                                                let opt = document.createElement('option');
                                                                opt.value = `${donitinesfi[1].code_itineraires}`;
                                                                opt.innerHTML = `${donitinesfi[1].nom_itineraires}`;
                                                                document.querySelector('#idcheminsfid').add(opt);

                                                                document.querySelector('#lignesitinerairefid').value = `${donitinesfi[0].nom_itineraires}`;
                                                                document.querySelector('#itinecodesfid').value = `${donitinesfi[0].id_lignes}`;
                                                                    

                                                                var typgare1fi = document.querySelector('#itinecodefid').value;
                                                                var post_typgare1fi = typgare1fi.split('-');
                                                                var seltypgare1fi = post_typgare1fi[0];
                                                                var typgareselfi = post_typgare1fi[1];
                                                                    let httptypequart1fi;
                                                                    httptypequart1fi = new XMLHttpRequest();
                                                                    
                                                                    httptypequart1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgareselfi}`, true);
                                                                    httptypequart1fi.onload = () => 
                                                                    {
                                                                        const donqua1fi = JSON.parse(httptypequart1fi.responseText);
                                                                        if (donqua1fi == '') {
                                                                            document.querySelector('#quartier1fid').options.length = 1;
                                                                        }
                                                                        else{
                                                                            if (Object.entries(donqua1fi).length >= 1) {
                                                                                            
                                                                                for (let key in Object.entries(donqua1fi)) {
                                                                                    let optq = document.createElement('option');
                                                                                    optq.value = `${donqua1fi[key].nom_quartier}`;
                                                                                    optq.innerHTML = `${donqua1fi[key].nom_quartier}`;
                                                                                    document.querySelector('#quartier1fid').add(optq);
                                                                                }
                                                                            } else {
                                                                                document.querySelector('#quartier1fid').options.length = 1;
                                                                            }
                                                                        }
                                                                        

                                                                    };
                                                                    httptypequart1fi.setRequestHeader('Content-Type', 'application/json');
                                                                    httptypequart1fi.send();

                                                                        let httptypequartitinfi;
                                                                        httptypequartitinfi = new XMLHttpRequest();
                                                                        var itinprofi = document.querySelector('#itinecodefid').value;
                                                                        var datedepartfi = document.querySelector('#date_depheurefid').value;
                                                                        httptypequartitinfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheureitine/${itinprofi}/${datedepartfi}`, true);
                                                                    httptypequartitinfi.onload = () => 
                                                                    {
                                                                        const infositinfi = JSON.parse(httptypequartitinfi.responseText);
                                                                        if (infositinfi == null) 
                                                                        {


                                                                        }
                                                                        if (Object.entries(infositinfi).length >= 1) 
                                                                        {
                                                                                
                                                                            
                                                                            for (let key in Object.entries(infositinfi)) {
                                                                                    let opt = document.createElement('option');
                                                                                    opt.value = `${infositinfi[key].id_ligneheure}/${infositinfi[key].heure}`;
                                                                                    opt.innerHTML = `${infositinfi[key].heure}`;
                                                                                    document.querySelector('#hdepartitinefid').add(opt);
                                                                                }
                                                                        } else {
                                                                            document.querySelector('#hdepartitinefid').options.length = 1;
                                                                        }
                                                                    };
                                                                    httptypequartitinfi.setRequestHeader('Content-Type', 'application/json');
                                                                    httptypequartitinfi.send();
                                                                let hrdepartinefi = document.querySelector('#hdepartitinefid');
                                                                if (hrdepartinefi !== null) {
                                                                    hrdepartinefi.onchange = () => 
                                                                    {
                                                                        const httpsousgarefi = new XMLHttpRequest();
                                                                        httpsousgarefi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${seltypgare1fi}`, true);
                                                                        httpsousgarefi.onload = () => 
                                                                        {
                                                                            const donsousgfi = JSON.parse(httpsousgarefi.responseText);
                                                                            console.debug(`${typeof donsousgfi}-${donsousgfi.attributes}`, console.memory);
                                                                            if (Object.entries(donsousgfi).length >= 1) {
                                                                                for (let key in Object.entries(donsousgfi)) 
                                                                                {
                                                                                    let opt = document.createElement('option');
                                                                                    opt.value = `${donsousgfi[key].idsousgare}`;
                                                                                    opt.innerHTML = `${donsousgfi[key].nomsousgare}`;
                                                                                    document.querySelector('#transitedepargare1fid').add(opt);
                        
                                                                                }
                                                                            }
                                                                        };
                                                                        httpsousgarefi.setRequestHeader('Content-Type', 'application/json');
                                                                        httpsousgarefi.send();

                                                                        document.querySelector('#psiegesitinesfid').options.length = 1;
                                                                        const httpRequestitfi = new XMLHttpRequest();
                                                                        const seleitinefi = document.querySelector('#hdepartitinefid')
                                                                            .options[document.querySelector('#hdepartitinefid').options.selectedIndex].value;

                                                                            var post_lhitinefi = seleitinefi.split('/');
                                                                            var selitinefi = post_lhitinefi[0];
                                                                            var lhselitinefi = post_lhitinefi[1];

                                                                            const dpt_dateitinefi = document.querySelector('#date_depheurefid').value;
                                                                            var itinproitfi = document.querySelector('#itinecodefid').value;
                                                                        httpRequestitfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifprog/${itinproitfi}/${dpt_dateitinefi}/${selitinefi}`, true);
                                                                        httpRequestitfi.onload = () => 
                                                                        {
                                                                            const donitfi = JSON.parse(httpRequestitfi.responseText);
                                                                                console.debug(`${typeof donitfi} - ${donitfi.attributes}`, console.memory);

                                                                                if (donitfi == '') 
                                                                                {
                                                                                    
                                                                                        let opt = document.createElement('option');
                                                                                        opt.value = '';                                                             
                                                                                    
                                                                                } 
                                                                                else 
                                                                                {       
                                                                                    if (Object.entries(donitfi).length >= 1) {
                                                                                        for (let key in Object.entries(donitfi)) {
                                                                                            document.querySelector('#programtransfid').value = `${donitfi[key].code_progr}`;
                                                                                            document.querySelector('#dateprtransfid').value = `${donitfi[key].date_progr}`;
                                                                                            document.querySelector('#deplignetransfid').value = `${donitfi[key].gareidentif}`;
                                                                                            document.querySelector('#intertrans1fid').value = `${donitfi[key].intervalle1}`;
                                                                                            document.querySelector('#intertrans2fid').value = `${donitfi[key].intervalle2}`;
                                                                                            document.querySelector('#ligntransfid').value = `${donitfi[key].ident_ligne}`;
                                                                                            document.querySelector('#nomitintransfid').value = `${donitfi[key].nom_ligne}`;
                                                                                            document.querySelector('#hertransfid').value = `${donitfi[key].heure}`;
                                                                                            document.querySelector('#catetransfid').value = `${donitfi[key].categori}`;

                                                                                        }
                                                                                    } 
                                                                                    
                                                                                    
                                                                                    const seleitinefi = document.querySelector('#hdepartitinefid')
                                                                                    .options[document.querySelector('#hdepartitinefid').options.selectedIndex].value;

                                                                                    var post_lhitinefi = seleitinefi.split('/');
                                                                                    var selitinefi = post_lhitinefi[0];
                                                                                    var lhselitinefi = post_lhitinefi[1];
                                                                                    /*const httpPrixitfi = new XMLHttpRequest();
                                                                                    httpPrixitfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifpriprg/${selitinefi}`, true);
                                                                                    httpPrixitfi.onload = () => 
                                                                                    {

                                                                                        const donprixitfi = JSON.parse(httpPrixitfi.responseText);
                                                                                        console.debug(`${typeof donprixitfi}-${donprixitfi.attributes}`, console.memory);
                                                                                        if (Object.entries(donprixitfi).length >= 1) {
                                                                                            for (let key in Object.entries(donprixitfi)) 
                                                                                            {
                                                                                                document.querySelector('#prix_axetransfid').value = `${donprixitfi[key].prix}`;
                                    
                                                                                            }
                                                                                        }
                                                                                    };
                                                                                    httpPrixitfi.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpPrixitfi.send();*/
                                                                                    
                                                                                    
                                                                                    
                                                                                    const httpRequetteitfi = new XMLHttpRequest();
                                                                                    const cdprogitfi = document.querySelector('#programtransfid').value;
                                                                                    const dbitfi = document.querySelector('#intertrans1fid').value;
                                                                                    const fnitfi = document.querySelector('#intertrans2fid').value;
                                                                                    const lgitfi = document.querySelector('#nomitintransfid').value;
                                                                                    const timitfi = document.querySelector('#hertransfid').value;
                                                                                    const dpt_dateitinefi = document.querySelector('#date_depheurefid').value;
                                                                                        httpRequetteitfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${cdprogitfi}/${dpt_dateitinefi}/${lgitfi}/${timitfi}/${dbitfi}/${fnitfi}`, true);
                                                                                    httpRequetteitfi.onload = () => {
                                                                                        const dattaitfi = JSON.parse(httpRequetteitfi.responseText);
                                                                                        console.debug(`${typeof dattaitfi} - ${dattaitfi.attributes}`, console.memory);
                                                                                        if (Object.entries(dattaitfi).length >= 1) {
                                                                                            for (let key in Object.entries(dattaitfi)) {
                                                                                                
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dattaitfi[key].siege_num}`;
                                                                                                opt.innerHTML = `${dattaitfi[key].siege_num}`;
                                                                                                document.querySelector('#psiegesitinesfid').add(opt);
                                                                                                
                                                                                            }
                                                                                            
                                                                                        } else {
                                                                                            document.querySelector('#psiegesitinesfid').options.length = 1;
                                                                                        }
                                                                                    };
                                                                                    httpRequetteitfi.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpRequetteitfi.send();

                                                                                }  
                                                                                
                                                                        };
                                                                        httpRequestitfi.setRequestHeader('Content-Type', 'application/json');
                                                                        httpRequestitfi.send();
                                                                         
                                                                    };
                                                                    
                                                            
                                                                }
                                                                progsiegestransfi = document.querySelector('#psiegesitinesfid');
                                                                if (progsiegestransfi !== null) {
                                                                    progsiegestransfi.onchange = () => 
                                                                    {

                                                                        gareidentiftransfi = document.querySelector('#deplignetransfid').value;
                                                                            const httpsousgarefi = new XMLHttpRequest();
                                                                            httpsousgarefi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftransfi}`, true);
                                                                            httpsousgarefi.onload = () => 
                                                                            {
                                                                                const donsousgfi = JSON.parse(httpsousgarefi.responseText);
                                                                                console.debug(`${typeof donsousgfi}-${donsousgfi.attributes}`, console.memory);
                                                                                if (Object.entries(donsousgfi).length >= 1) {
                                                                                    for (let key in Object.entries(donsousgfi)) 
                                                                                    {
                                                                                        let opt = document.createElement('option');
                                                                                        opt.value = `${donsousgfi[key].idsousgare}`;
                                                                                        opt.innerHTML = `${donsousgfi[key].nomsousgare}`;
                                                                                        document.querySelector('#transitedepargare1fid').add(opt);
                            
                                                                                    }
                                                                                }
                                                                            };
                                                                            httpsousgarefi.setRequestHeader('Content-Type', 'application/json');
                                                                            httpsousgarefi.send();
                                                                        let httpSiegestransfi;
                                                                        httpSiegestransfi = new XMLHttpRequest();
                                                                        const sigstransfi = document.querySelector('#psiegesitinesfid')
                                                                        .options[document.querySelector('#psiegesitinesfid').options.selectedIndex].value;
                                                                        const prostransfi = document.querySelector('#programtransfid').value;

                                                                        httpSiegestransfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${prostransfi}/${sigstransfi}`, true);
                                                                        httpSiegestransfi.onload = () => 
                                                                        {
                                                                            const donsgetransfi = JSON.parse(httpSiegestransfi.responseText);
                                                                            console.debug(`${typeof donsgetransfi} - ${donsgetransfi.attributes}`, console.memory);
                                                                            if(donsgetransfi == '')
                                                                            {
                                                                                let httpSiegstransfi;
                                                                                httpSiegstransfi = new XMLHttpRequest();

                                                                                httpSiegstransfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${prostransfi}/${sigstransfi}`, true);
                                                                                httpSiegstransfi.onload = () => 
                                                                                {
                                                                                    const dongtransfi = JSON.parse(httpSiegstransfi.responseText);
                                                                                    document.querySelector('#messfid').style.display = 'none';
                                                                                    if (Object.entries(dongtransfi).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtransfi)) {
                                                                                                document.querySelector('#idtampotransfid').value = `${dongtransfi[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselecttransfid').value = `${dongtransfi[key].numsieg}`;
                                                                                            }
                                                                                        }
                                                                                };
                                                                                httpSiegstransfi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSiegstransfi.send();
                                                                            }
                                                                            else {
                                                                                document.querySelector('#psiegesitinesfid').value = '';     
                                                                                if (Object.entries(donsgetransfi).length >= 1)
                                                                                {
                                                                                    for (let key in Object.entries(donsgetransfi)) {
                                                                                        document.querySelector('#idtampotransfid').value = `${donsgetransfi[key].idtamp}`;                    
                                                                                        document.querySelector('#siegselecttransfid').value = `${donsgetransfi[key].numsieg}`;
                                                                                    }

                                                                                }
                                                                                document.querySelector('#messfid').style.display = 'block';
                                                                                document.querySelector('#erreurMessfid').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                        };
                                                                        httpSiegestransfi.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegestransfi.send();

                                                                    
                                                                    };
                                                                }

                                                                let progcheminfi = document.querySelector('#idcheminsfid');
                                                                if (progcheminfi !== null) 
                                                                {
                                                                    progcheminfi.onchange = () => 
                                                                    {
                                                                        document.querySelector('#idcheminsheurfid').options.length = 1;
                                                                        document.querySelector('#psiegesitines1fid').options.length = 1;
                                                                        
                                                                        let httpSiegescheminfi;
                                                                        httpSiegescheminfi = new XMLHttpRequest();
                                                                        
                                                                        const prostranscheminfi = document.querySelector('#idcheminsfid')
                                                                        .options[document.querySelector('#idcheminsfid').options.selectedIndex].value;

                                                                        var post_typgare2fi = prostranscheminfi.split('-');
                                                                        var seltypgare2fi = post_typgare2fi[0];
                                                                        var typgaresel1fi = post_typgare2fi[1];
 
                                                                        var datedepartfi = document.querySelector('#date_depheurefid').value;
                                                                        httpSiegescheminfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranscheminfi}/${datedepartfi}`, true);
                                                                        httpSiegescheminfi.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschemfi = JSON.parse(httpSiegescheminfi.responseText);
                                                                                    if (Object.entries(dongtranschemfi).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtranschemfi)) {
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dongtranschemfi[key].code_progr}/${dongtranschemfi[key].intervalle1}/${dongtranschemfi[key].intervalle2}/${dongtranschemfi[key].id_ligneheure}/${dongtranschemfi[key].prix}`;
                                                                                                opt.innerHTML = `${dongtranschemfi[key].heure}/${dongtranschemfi[key].date_progr}`;
                                                                                                document.querySelector('#idcheminsheurfid').add(opt);
                                                                                            }
                                                                                        }
                                                                        };
                                                                        httpSiegescheminfi.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegescheminfi.send();

                                                                    };
                                                                        let prochemintrafi = document.querySelector('#idcheminsheurfid');
                                                                    if (prochemintrafi !== null)
                                                                        prochemintrafi.onchange = () => 
                                                                        {  
                                                                            
                                                                            document.querySelector('#psiegesitines1fid').options.length = 1;

                                                                            const httpPrixittransitefi = new XMLHttpRequest();
                                                                                const transselitinefi = document.querySelector('#idcheminsheurfid')
                                                                            .options[document.querySelector('#idcheminsheurfid').options.selectedIndex].value;
                                                                                var post_transfi = transselitinefi.split('/');
                                                                            var itinetrasfi = post_transfi[0];
                                                                            var dbitrafi = post_transfi[1];
                                                                            var fnitrafi = post_transfi[2];
                                                                            var lhertrafi = post_transfi[3];
                                                                            var prixtrafi = post_transfi[4];

                                                                                httpPrixittransitefi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetrasfi}`, true);
                                                                                httpPrixittransitefi.onload = () => 
                                                                                {
                                                                                    const donprixitranfi = JSON.parse(httpPrixittransitefi.responseText);
                                                                                    console.debug(`${typeof donprixitranfi}-${donprixitranfi.attributes}`, console.memory);
                                                                                    if (Object.entries(donprixitranfi).length >= 1) {
                                                                                        for (let key in Object.entries(donprixitranfi)) 
                                                                                        {
                                                                                            document.querySelector('#catetransitfid').value = `${donprixitranfi[key].categori}`;
                                                                                            document.querySelector('#gidtransfid').value =  `${donprixitranfi[key].gareidentif}`;
                                                                                            document.querySelector('#nomitintrans1fid').value = `${donprixitranfi[key].nom_ligne}`;
                                                                                            document.querySelector('#ligntrans1fid').value = `${donprixitranfi[key].ident_ligne}`;

                                                                                        }
                                                                                    }
                                                                                };
                                                                                httpPrixittransitefi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpPrixittransitefi.send();
                                                                                
                                                                                      
                                                                                    
                                                                                const httpRequetteitrafi = new XMLHttpRequest();
                                                                        
                                                                                    httpRequetteitrafi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetrasfi}/${dbitrafi}/${fnitrafi}`, true);
                                                                                httpRequetteitrafi.onload = () => {
                                                                                    const dattaitrafi = JSON.parse(httpRequetteitrafi.responseText);
                                                                                    console.debug(`${typeof dattaitrafi} - ${dattaitrafi.attributes}`, console.memory);
                                                                                    if (Object.entries(dattaitrafi).length >= 1) {
                                                                                        for (let key in Object.entries(dattaitrafi)) {
                                                                                            
                                                                                            let opt = document.createElement('option');
                                                                                            opt.value = `${dattaitrafi[key].siege_num}`;
                                                                                            opt.innerHTML = `${dattaitrafi[key].siege_num}`;
                                                                                            document.querySelector('#psiegesitines1fid').add(opt);
                                                                                            
                                                                                        }
                                                                                        
                                                                                    } else {
                                                                                        document.querySelector('#psiegesitines1fid').options.length = 1;
                                                                                    }
                                                                                };
                                                                                httpRequetteitrafi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpRequetteitrafi.send();
                                                                        };

                                                                        progsieges1fi = document.querySelector('#psiegesitines1fid');
                                                                        if (progsieges1fi !== null) 
                                                                        {
                                                                            progsieges1fi.onchange = () => 
                                                                            {
                                                                                

                                                                                const transselitine1fi = document.querySelector('#idcheminsheurfid')
                                                                                .options[document.querySelector('#idcheminsheurfid').options.selectedIndex].value;
                                                                                var post_trans1fi = transselitine1fi.split('/');
                                                                                var itinetras1fi = post_trans1fi[0];
                                                                                
                                                                                gareidentiftrans2fi = document.querySelector('#gidtransfid').value;
                                                                                const httpsousgare1fi = new XMLHttpRequest();
                                                                                httpsousgare1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftrans2fi}`, true);
                                                                                httpsousgare1fi.onload = () => 
                                                                                {
                                                                                    const donsousg1fi = JSON.parse(httpsousgare1fi.responseText);
                                                                                    console.debug(`${typeof donsousg1fi}-${donsousg1fi.attributes}`, console.memory);
                                                                                    if (Object.entries(donsousg1fi).length >= 1) {
                                                                                        for (let key in Object.entries(donsousg1fi)) 
                                                                                        {
                                                                                            let opt = document.createElement('option');
                                                                                            opt.value = `${donsousg1fi[key].idsousgare}`;
                                                                                            opt.innerHTML = `${donsousg1fi[key].nomsousgare}`;
                                                                                            document.querySelector('#transitedepargare2fid').add(opt);
                                
                                                                                        }
                                                                                    }
                                                                                };
                                                                                httpsousgare1fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpsousgare1fi.send();
                                                                              
                                                                                let httpSieges1fi;
                                                                                httpSieges1fi = new XMLHttpRequest();
                                                                                const sigs1fi = document.querySelector('#psiegesitines1fid')
                                                                                .options[document.querySelector('#psiegesitines1fid').options.selectedIndex].value;
                                                                                //const pros1 = document.querySelector('#program').value;

                                                                                httpSieges1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetras1fi}/${sigs1fi}`, true);
                                                                                httpSieges1fi.onload = () => 
                                                                                {
                                                                                    const donsge1fi = JSON.parse(httpSieges1fi.responseText);
                                                                                    console.debug(`${typeof donsge1fi} - ${donsge1fi.attributes}`, console.memory);
                                                                                    if(donsge1fi == '')
                                                                                    {
                                                                                        let httpSiegs1fi;
                                                                                        httpSiegs1fi = new XMLHttpRequest();

                                                                                        httpSiegs1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetras1fi}/${sigs1fi}`, true);
                                                                                        httpSiegs1fi.onload = () => 
                                                                                        {
                                                                                            const dong1fi = JSON.parse(httpSiegs1fi.responseText);
                                                                                            document.querySelector('#messfid').style.display = 'none';
                                                                                            if (Object.entries(dong1fi).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(dong1fi)) {
                                                                                                        document.querySelector('#idtampo1fid').value = `${dong1fi[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselect1fid').value = `${dong1fi[key].numsieg}`;
                                                                                                    }
                                                                                                }
                                                                                        };
                                                                                        httpSiegs1fi.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegs1fi.send();
                                                                                    }
                                                                                    else {
                                                                                        document.querySelector('#psiegesitines1fid').value = '';     
                                                                                        if (Object.entries(donsge1fi).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(donsge1fi)) {
                                                                                                document.querySelector('#idtampo1fid').value = `${donsge1fi[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselect1fid').value = `${donsge1fi[key].numsieg}`;
                                                                                            }

                                                                                        }
                                                                                        document.querySelector('#messfid').style.display = 'block';
                                                                                        document.querySelector('#erreurMessfid').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                                };
                                                                                httpSieges1fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSieges1fi.send();

                                                                            };
                                                                        }
                                                                }               
                                                            }
                                                            //second itineraire
                                                            if(i === 3)
                                                            {

                                                                
                                                                let opt = document.createElement('option');
                                                                opt.value = `${donitinesfi[1].code_itineraires}`;
                                                                opt.innerHTML = `${donitinesfi[1].nom_itineraires}`;
                                                                
                                                                document.querySelector('#idcheminsfid').add(opt);

                                                                document.querySelector('#lignesitinerairefid').value = `${donitinesfi[0].nom_itineraires}`;
                                                                document.querySelector('#itinecodesfid').value = `${donitinesfi[0].id_lignes}`;
                                                               

                                                                let opt1 = document.createElement('option');
                                                                opt1.value = `${donitinesfi[2].code_itineraires}`;
                                                                opt1.innerHTML = `${donitinesfi[2].nom_itineraires}`;
                                                                document.querySelector('#idchemins1fid').add(opt1);


                                                                var typgare1fi = document.querySelector('#itinecodefid').value;
                                                                var post_typgare1fi = typgare1fi.split('-');
                                                                var seltypgare1fi = post_typgare1fi[0];
                                                                var typgareselfi = post_typgare1fi[1];
                                                                    let httptypequart1fi;
                                                                    httptypequart1fi = new XMLHttpRequest();
                                                                    
                                                                    httptypequart1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgareselfi}`, true);
                                                                    httptypequart1fi.onload = () => 
                                                                    {
                                                                        const donqua1fi = JSON.parse(httptypequart1fi.responseText);
                                                                        if (donqua1fi == '') {
                                                                            document.querySelector('#quartier1fid').options.length = 1;
                                                                        }
                                                                        else{
                                                                            if (Object.entries(donqua1fi).length >= 1) {
                                                                                            
                                                                                for (let key in Object.entries(donqua1fi)) {
                                                                                    let optq = document.createElement('option');
                                                                                    optq.value = `${donqua1fi[key].nom_quartier}`;
                                                                                    optq.innerHTML = `${donqua1fi[key].nom_quartier}`;
                                                                                    document.querySelector('#quartier1fid').add(optq);
                                                                                }
                                                                            } else {
                                                                                document.querySelector('#quartier1fid').options.length = 1;
                                                                            }
                                                                        }
                                                                        

                                                                    };
                                                                    httptypequart1fi.setRequestHeader('Content-Type', 'application/json');
                                                                    httptypequart1fi.send();


                                                                        let httptypequartitin1fi;
                                                                        httptypequartitin1fi = new XMLHttpRequest();
                                                                        var itinpro1fi = document.querySelector('#itinecodefid').value;
                                                                        var datedepartfi = document.querySelector('#date_depheurefid').value;
                                                                        httptypequartitin1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheureitine/${itinpro1fi}/${datedepartfi}`, true);
                                                                    httptypequartitin1fi.onload = () => 
                                                                    {
                                                                        const infositin1fi = JSON.parse(httptypequartitin1fi.responseText);
                                                                        if (infositin1fi == null) 
                                                                        {


                                                                        }
                                                                        if (Object.entries(infositin1fi).length >= 1) 
                                                                        {
                                                                                
                                                                            
                                                                            for (let key in Object.entries(infositin1fi)) {
                                                                                    let opt = document.createElement('option');
                                                                                    opt.value = `${infositin1fi[key].id_ligneheure}/${infositin1fi[key].heure}`;
                                                                                    opt.innerHTML = `${infositin1fi[key].heure}`;
                                                                                    document.querySelector('#hdepartitinefid').add(opt);
                                                                                }
                                                                        } else {
                                                                            document.querySelector('#hdepartitinefid').options.length = 1;
                                                                        }
                                                                    };
                                                                    httptypequartitin1fi.setRequestHeader('Content-Type', 'application/json');
                                                                    httptypequartitin1fi.send();
                                                                let hrdepartine1fi = document.querySelector('#hdepartitinefid');
                                                                if (hrdepartine1fi !== null) {
                                                                    hrdepartine1fi.onchange = () => 
                                                                    {
                                                                        document.querySelector('#psiegesitinesfid').options.length = 1;
                                                                        const httpRequestit1fi = new XMLHttpRequest();
                                                                        const seleitine1fi = document.querySelector('#hdepartitinefid')
                                                                            .options[document.querySelector('#hdepartitinefid').options.selectedIndex].value;

                                                                            var post_lhitine1fi = seleitine1fi.split('/');
                                                                            var selitine1fi = post_lhitine1fi[0];
                                                                            var lhselitine1fi = post_lhitine1fi[1];

                                                                            const dpt_dateitine1fi = document.querySelector('#date_depheurefid').value;
                                                                            var itinproit1fi = document.querySelector('#itinecodefid').value;
                                                                        httpRequestit1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifprog/${itinproit1fi}/${dpt_dateitine1fi}/${selitine1fi}`, true);
                                                                        httpRequestit1fi.onload = () => 
                                                                        {
                                                                            const donit1fi = JSON.parse(httpRequestit1fi.responseText);
                                                                                console.debug(`${typeof donit1fi} - ${donit1fi.attributes}`, console.memory);

                                                                                if (donit1fi == '') 
                                                                                {
                                                                                    
                                                                                        let opt = document.createElement('option');
                                                                                        opt.value = '';                                                             
                                                                                   
                                                                                    
                                                                                    
                                                                                } 
                                                                                else 
                                                                                {       
                                                                                    if (Object.entries(donit1fi).length >= 1) {
                                                                                        for (let key in Object.entries(donit1fi)) {
                                                                                            document.querySelector('#programtransfid').value = `${donit1fi[key].code_progr}`;
                                                                                            document.querySelector('#dateprtransfid').value = `${donit1fi[key].date_progr}`;
                                                                                            document.querySelector('#deplignetransfid').value = `${donit1fi[key].gareidentif}`;
                                                                                            document.querySelector('#intertrans1fid').value = `${donit1fi[key].intervalle1}`;
                                                                                            document.querySelector('#intertrans2fid').value = `${donit1fi[key].intervalle2}`;
                                                                                            document.querySelector('#ligntransfid').value = `${donit1fi[key].ident_ligne}`;
                                                                                            document.querySelector('#nomitintransfid').value = `${donit1fi[key].nom_ligne}`;
                                                                                            document.querySelector('#hertransfid').value = `${donit1fi[key].heure}`;
                                                                                            document.querySelector('#catetransfid').value = `${donit1fi[key].categori}`;

                                                                                        }
                                                                                    } 
                                                                                    
                                                                                    
                                                                                    const seleitinefi = document.querySelector('#hdepartitinefid')
                                                                                    .options[document.querySelector('#hdepartitinefid').options.selectedIndex].value;

                                                                                    var post_lhitinefi = seleitinefi.split('/');
                                                                                    var selitinefi = post_lhitinefi[0];
                                                                                    var lhselitinefi = post_lhitinefi[1];
                                                                                    
                                                                                    const httpRequetteitfi = new XMLHttpRequest();
                                                                                    const cdprogitfi = document.querySelector('#programtransfid').value;
                                                                                    const dbitfi = document.querySelector('#intertrans1fid').value;
                                                                                    const fnitfi = document.querySelector('#intertrans2fid').value;
                                                                                    const lgitfi = document.querySelector('#nomitintransfid').value;
                                                                                    const timitfi = document.querySelector('#hertransfid').value;
                                                                                    const dpt_dateitinefi = document.querySelector('#date_depheurefid').value;
                                                                                        httpRequetteitfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${cdprogitfi}/${dpt_dateitinefi}/${lgitfi}/${timitfi}/${dbitfi}/${fnitfi}`, true);
                                                                                    httpRequetteitfi.onload = () => {
                                                                                        const dattaitfi = JSON.parse(httpRequetteitfi.responseText);
                                                                                        console.debug(`${typeof dattaitfi} - ${dattaitfi.attributes}`, console.memory);
                                                                                        if (Object.entries(dattaitfi).length >= 1) {
                                                                                            for (let key in Object.entries(dattaitfi)) {
                                                                                                
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dattaitfi[key].siege_num}`;
                                                                                                opt.innerHTML = `${dattaitfi[key].siege_num}`;
                                                                                                document.querySelector('#psiegesitinesfid').add(opt);
                                                                                                
                                                                                            }
                                                                                            
                                                                                        } else {
                                                                                            document.querySelector('#psiegesitinesfid').options.length = 1;
                                                                                        }
                                                                                    };
                                                                                    httpRequetteitfi.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpRequetteitfi.send();

                                                                                }  
                                                                                
                                                                        };
                                                                        httpRequestit1fi.setRequestHeader('Content-Type', 'application/json');
                                                                        httpRequestit1fi.send();
                                                                         
                                                                    };
                                                                    
                                                            
                                                                }
                                                                let progsiegestransfi = document.querySelector('#psiegesitinesfid');
                                                                if (progsiegestransfi !== null) {
                                                                    progsiegestransfi.onchange = () => 
                                                                    {

                                                                        const gareidentiftrans1fi = document.querySelector('#deplignetransfid').value;
                                                                        const httpsousgarefi = new XMLHttpRequest();
                                                                        httpsousgarefi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftrans1fi}`, true);
                                                                        httpsousgarefi.onload = () => 
                                                                        {
                                                                            const donsousgfi = JSON.parse(httpsousgarefi.responseText);
                                                                            console.debug(`${typeof donsousgfi}-${donsousgfi.attributes}`, console.memory);
                                                                            if (Object.entries(donsousgfi).length >= 1) {
                                                                                for (let key in Object.entries(donsousgfi)) 
                                                                                {
                                                                                    let opt = document.createElement('option');
                                                                                    opt.value = `${donsousgfi[key].idsousgare}`;
                                                                                    opt.innerHTML = `${donsousgfi[key].nomsousgare}`;
                                                                                    document.querySelector('#transitedepargare1fid').add(opt);
                        
                                                                                }
                                                                            }
                                                                        };
                                                                        httpsousgarefi.setRequestHeader('Content-Type', 'application/json');
                                                                        httpsousgarefi.send();
                                                                        let httpSiegestrans1fi;
                                                                        httpSiegestrans1fi = new XMLHttpRequest();
                                                                        const sigstransfi = document.querySelector('#psiegesitinesfid')
                                                                        .options[document.querySelector('#psiegesitinesfid').options.selectedIndex].value;
                                                                        const prostransfi = document.querySelector('#programtransfid').value;

                                                                        httpSiegestrans1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${prostransfi}/${sigstransfi}`, true);
                                                                        httpSiegestrans1fi.onload = () => 
                                                                        {
                                                                            const donsgetransfi = JSON.parse(httpSiegestrans1fi.responseText);
                                                                            console.debug(`${typeof donsgetransfi} - ${donsgetransfi.attributes}`, console.memory);
                                                                            if(donsgetransfi == '')
                                                                            {
                                                                                let httpSiegstransfi;
                                                                                httpSiegstransfi = new XMLHttpRequest();

                                                                                httpSiegstransfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${prostransfi}/${sigstransfi}`, true);
                                                                                httpSiegstransfi.onload = () => 
                                                                                {
                                                                                    const dongtransfi = JSON.parse(httpSiegstransfi.responseText);
                                                                                    document.querySelector('#messfid').style.display = 'none';
                                                                                    if (Object.entries(dongtransfi).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtransfi)) {
                                                                                                document.querySelector('#idtampotransfid').value = `${dongtransfi[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselecttransfid').value = `${dongtransfi[key].numsieg}`;
                                                                                            }
                                                                                        }
                                                                                };
                                                                                httpSiegstransfi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSiegstransfi.send();
                                                                            }
                                                                            else {
                                                                                document.querySelector('#psiegesitinesfid').value = '';     
                                                                                if (Object.entries(donsgetransfi).length >= 1)
                                                                                {
                                                                                    for (let key in Object.entries(donsgetransfi)) {
                                                                                        document.querySelector('#idtampotransfid').value = `${donsgetransfi[key].idtamp}`;                    
                                                                                        document.querySelector('#siegselecttransfid').value = `${donsgetransfi[key].numsieg}`;
                                                                                    }

                                                                                }
                                                                                document.querySelector('#messfid').style.display = 'block';
                                                                                document.querySelector('#erreurMessfid').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                        };
                                                                        httpSiegestrans1fi.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegestrans1fi.send();

                                                                    
                                                                    };
                                                                }
                                                                //premier transite
                                                                let progcheminfi = document.querySelector('#idcheminsfid');
                                                                if (progcheminfi !== null) 
                                                                {
                                                                    progcheminfi.onchange = () => 
                                                                    {
                                                                        document.querySelector('#idcheminsheurfid').options.length = 1;
                                                                        document.querySelector('#psiegesitines1fid').options.length = 1;

                                                                        const prostranscheminfi = document.querySelector('#idcheminsfid')
                                                                        .options[document.querySelector('#idcheminsfid').options.selectedIndex].value;

                                                                        var post_typgare2fi = prostranscheminfi.split('-');
                                                                        var seltypgare2fi = post_typgare2fi[0];
                                                                        var typgaresel1fi = post_typgare2fi[1];
                                                                        let httptypequart2fi;
                                                                        httptypequart2fi = new XMLHttpRequest();
                                                                        
                                                                        httptypequart2fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgaresel1fi}`, true);
                                                                        httptypequart2fi.onload = () => 
                                                                        {
                                                                            const donqua2fi = JSON.parse(httptypequart2fi.responseText);
                                                                            if (donqua2fi == '') {
                                                                                document.querySelector('#quartier2fid').options.length = 1;
                                                                            }
                                                                            else{
                                                                                if (Object.entries(donqua2fi).length >= 1) {
                                                                                                
                                                                                    for (let key in Object.entries(donqua2fi)) {
                                                                                        let optq1 = document.createElement('option');
                                                                                        optq1.value = `${donqua2fi[key].nom_quartier}`;
                                                                                        optq1.innerHTML = `${donqua2fi[key].nom_quartier}`;
                                                                                        document.querySelector('#quartier2fid').add(optq1);
                                                                                    }
                                                                                } else {
                                                                                    document.querySelector('#quartier2fid').options.length = 1;
                                                                                }
                                                                            }
                                                                            

                                                                        };
                                                                        httptypequart2fi.setRequestHeader('Content-Type', 'application/json');
                                                                        httptypequart2fi.send();

                                                                        let httpSiegescheminfi;
                                                                        httpSiegescheminfi = new XMLHttpRequest();

                                                                        var datedepartfi = document.querySelector('#date_depheurefid').value;
                                                                        
                                                                        httpSiegescheminfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranscheminfi}/${datedepartfi}`, true);
                                                                        httpSiegescheminfi.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschemfi = JSON.parse(httpSiegescheminfi.responseText);
                                                                                    if (Object.entries(dongtranschemfi).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtranschemfi)) {
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dongtranschemfi[key].code_progr}/${dongtranschemfi[key].intervalle1}/${dongtranschemfi[key].intervalle2}/${dongtranschemfi[key].id_ligneheure}/${dongtranschemfi[key].prix}`;
                                                                                                opt.innerHTML = `${dongtranschemfi[key].heure}/${dongtranschemfi[key].date_progr}`;
                                                                                                document.querySelector('#idcheminsheurfid').add(opt);
                                                                                            }
                                                                                        }
                                                                        };
                                                                        httpSiegescheminfi.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegescheminfi.send();

                                                                    };
                                                                       let prochemintrafi = document.querySelector('#idcheminsheurfid');
                                                                    if (prochemintrafi !== null)
                                                                        prochemintrafi.onchange = () => 
                                                                        {  
                                                                           
                                                                            document.querySelector('#psiegesitines1fid').options.length = 1;

                                                                            const httpPrixittransitefi = new XMLHttpRequest();
                                                                                const transselitinefi = document.querySelector('#idcheminsheurfid')
                                                                            .options[document.querySelector('#idcheminsheurfid').options.selectedIndex].value;
                                                                                var post_transfi = transselitinefi.split('/');
                                                                            var itinetrasfi = post_transfi[0];
                                                                            var dbitrafi = post_transfi[1];
                                                                            var fnitrafi = post_transfi[2];
                                                                            var lhertrafi = post_transfi[3];
                                                                            var prixtrafi = post_transfi[4];

                                                                                httpPrixittransitefi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetrasfi}`, true);
                                                                                httpPrixittransitefi.onload = () => 
                                                                                {
                                                                                    const donprixitranfi = JSON.parse(httpPrixittransitefi.responseText);
                                                                                    console.debug(`${typeof donprixitranfi}-${donprixitranfi.attributes}`, console.memory);
                                                                                    if (Object.entries(donprixitranfi).length >= 1) {
                                                                                        for (let key in Object.entries(donprixitranfi)) 
                                                                                        {
                                                                                            document.querySelector('#catetransitfid').value = `${donprixitranfi[key].categori}`;
                                                                                            document.querySelector('#gidtransfid').value =  `${donprixitranfi[key].gareidentif}`;
                                                                                            document.querySelector('#nomitintrans1fid').value = `${donprixitranfi[key].nom_ligne}`; 
                                                                                        document.querySelector('#ligntrans1fid').value = `${donprixitranfi[key].ident_ligne}`;
                                                                                        }
                                                                                    }
                                                                                };
                                                                                httpPrixittransitefi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpPrixittransitefi.send();


                                                                                

                                                                                const httpRequetteitrafi = new XMLHttpRequest();
                                                                        
                                                                                    httpRequetteitrafi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetrasfi}/${dbitrafi}/${fnitrafi}`, true);
                                                                                httpRequetteitrafi.onload = () => {
                                                                                    const dattaitrafi = JSON.parse(httpRequetteitrafi.responseText);
                                                                                    console.debug(`${typeof dattaitrafi} - ${dattaitrafi.attributes}`, console.memory);
                                                                                    if (Object.entries(dattaitrafi).length >= 1) {
                                                                                        for (let key in Object.entries(dattaitrafi)) {
                                                                                            
                                                                                            let opt = document.createElement('option');
                                                                                            opt.value = `${dattaitrafi[key].siege_num}`;
                                                                                            opt.innerHTML = `${dattaitrafi[key].siege_num}`;
                                                                                            document.querySelector('#psiegesitines1fid').add(opt);
                                                                                            
                                                                                        }
                                                                                        
                                                                                    } else {
                                                                                        document.querySelector('#psiegesitines1fid').options.length = 1;
                                                                                    }
                                                                                };
                                                                                httpRequetteitrafi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpRequetteitrafi.send();
                                                                        };

                                                                        let progsieges1fi = document.querySelector('#psiegesitines1fid');
                                                                        if (progsieges1fi !== null) 
                                                                        {
                                                                            progsieges1fi.onchange = () => 
                                                                            {

                                                                              const  gareidentiftrans2fi = document.querySelector('#gidtransfid').value;
                                                                                    const httpsousgare1fi = new XMLHttpRequest();
                                                                                    httpsousgare1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftrans2fi}`, true);
                                                                                    httpsousgare1fi.onload = () => 
                                                                                    {
                                                                                        const donsousg1fi = JSON.parse(httpsousgare1fi.responseText);
                                                                                        console.debug(`${typeof donsousg1fi}-${donsousg1fi.attributes}`, console.memory);
                                                                                        if (Object.entries(donsousg1fi).length >= 1) {
                                                                                            for (let key in Object.entries(donsousg1fi)) 
                                                                                            {
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${donsousg1fi[key].idsousgare}`;
                                                                                                opt.innerHTML = `${donsousg1fi[key].nomsousgare}`;
                                                                                                document.querySelector('#transitedepargare2fid').add(opt);
                                    
                                                                                            }
                                                                                        }
                                                                                    };
                                                                                    httpsousgare1fi.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpsousgare1fi.send();
                                                                                 const transselitine1fi = document.querySelector('#idcheminsheurfid')
                                                                                .options[document.querySelector('#idcheminsheurfid').options.selectedIndex].value;
                                                                                var post_trans1fi = transselitine1fi.split('/');
                                                                                var itinetras1fi = post_trans1fi[0];
                                                                    
                                                                                

                                                                                let httpSieges1fi;
                                                                                httpSieges1fi = new XMLHttpRequest();
                                                                                const sigs1fi = document.querySelector('#psiegesitines1fid')
                                                                                .options[document.querySelector('#psiegesitines1fid').options.selectedIndex].value;

                                                                                httpSieges1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetras1fi}/${sigs1fi}`, true);
                                                                                httpSieges1fi.onload = () => 
                                                                                {
                                                                                    const donsge1fi = JSON.parse(httpSieges1fi.responseText);
                                                                                    console.debug(`${typeof donsge1fi} - ${donsge1fi.attributes}`, console.memory);
                                                                                    if(donsge1fi == '')
                                                                                    {
                                                                                        let httpSiegs1fi;
                                                                                        httpSiegs1fi = new XMLHttpRequest();

                                                                                        httpSiegs1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetras1fi}/${sigs1fi}`, true);
                                                                                        httpSiegs1fi.onload = () => 
                                                                                        {
                                                                                            const dong1fi = JSON.parse(httpSiegs1fi.responseText);
                                                                                            document.querySelector('#messfid').style.display = 'none';
                                                                                            if (Object.entries(dong1fi).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(dong1fi)) {
                                                                                                        document.querySelector('#idtampo1fid').value = `${dong1fi[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselect1fid').value = `${dong1fi[key].numsieg}`;
                                                                                                    }
                                                                                                }
                                                                                        };
                                                                                        httpSiegs1fi.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegs1fi.send();
                                                                                    }
                                                                                    else {
                                                                                        document.querySelector('#psiegesitines1fid').value = '';     
                                                                                        if (Object.entries(donsge1fi).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(donsge1fi)) {
                                                                                                document.querySelector('#idtampo1fid').value = `${donsge1fi[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselect1fid').value = `${donsge1fi[key].numsieg}`;
                                                                                            }

                                                                                        }
                                                                                        document.querySelector('#messfid').style.display = 'block';
                                                                                        document.querySelector('#erreurMessfid').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                                };
                                                                                httpSieges1fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSieges1fi.send();

                                                                            };
                                                                        }
                                                                }
                                                                let progchemin1fi = document.querySelector('#idchemins1fid');
                                                                if (progchemin1fi !== null) 
                                                                {
                                                                    progchemin1fi.onchange = () => 
                                                                    {
                                                                        document.querySelector('#idcheminsheur1fid').options.length = 1;
                                                                        document.querySelector('#psiegesitines2fid').options.length = 1;
                                                                       
                                                                        const prostranschemin32fi = document.querySelector('#idchemins1fid')
                                                                        .options[document.querySelector('#idchemins1fid').options.selectedIndex].value;

                                                                        var post_typgare32fi = prostranschemin32fi.split('-');
                                                                        var seltypgare32fi = post_typgare32fi[0];
                                                                        var typgaresel31fi = post_typgare32fi[1];
                                                                        
                                                                        let httpSiegeschemin1fi;
                                                                        httpSiegeschemin1fi = new XMLHttpRequest();

                                                                        var datedepartfi = document.querySelector('#date_depheurefid').value;
                                                                        const prostranschemin1fi = document.querySelector('#idchemins1fid')
                                                                        .options[document.querySelector('#idchemins1fid').options.selectedIndex].value;

                                                                        httpSiegeschemin1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranschemin1fi}/${datedepartfi}`, true);
                                                                        httpSiegeschemin1fi.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschem1fi = JSON.parse(httpSiegeschemin1fi.responseText);
                                                                                    if (Object.entries(dongtranschem1fi).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtranschem1fi)) {
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dongtranschem1fi[key].code_progr}/${dongtranschem1fi[key].intervalle1}/${dongtranschem1fi[key].intervalle2}/${dongtranschem1fi[key].id_ligneheure}/${dongtranschem1fi[key].prix}`;
                                                                                                opt.innerHTML = `${dongtranschem1fi[key].heure}/${dongtranschem1fi[key].date_progr}`;
                                                                                                document.querySelector('#idcheminsheur1fid').add(opt);
                                                                                            }
                                                                                        }
                                                                        };
                                                                        httpSiegeschemin1fi.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegeschemin1fi.send();

                                                                    };
                                                                      let prochemintra1fi = document.querySelector('#idcheminsheur1fid');
                                                                    if (prochemintra1fi !== null)
                                                                        prochemintra1fi.onchange = () => 
                                                                        {  
                                                                           
                                                                            document.querySelector('#psiegesitines2fid').options.length = 1;
                                                                       

                                                                            const httpPrixittransite1fi = new XMLHttpRequest();
                                                                                const transselitine1fi = document.querySelector('#idcheminsheur1fid')
                                                                            .options[document.querySelector('#idcheminsheur1fid').options.selectedIndex].value;
                                                                                var post_trans1fi = transselitine1fi.split('/');
                                                                            var itinetras1fi = post_trans1fi[0];
                                                                            var dbitra1fi = post_trans1fi[1];
                                                                            var fnitra1fi = post_trans1fi[2];
                                                                            var lhertra1fi = post_trans1fi[3];
                                                                            var prixtra1fi = post_trans1fi[4];

                                                                                httpPrixittransite1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetras1fi}`, true);
                                                                                httpPrixittransite1fi.onload = () => 
                                                                                {
                                                                                    const donprixitran1fi = JSON.parse(httpPrixittransite1fi.responseText);
                                                                                    if (Object.entries(donprixitran1fi).length >= 1) {
                                                                                        for (let key in Object.entries(donprixitran1fi)) 
                                                                                        {
                                                                                            document.querySelector('#catetransit1fid').value = `${donprixitran1fi[key].categori}`;
                                                                                            document.querySelector('#gidtrans1fid').value =  `${donprixitran1fi[key].gareidentif}`;
                                                                                            document.querySelector('#nomitintrans2fid').value = `${donprixitran1fi[key].nom_ligne}`;
                                                                                            document.querySelector('#ligntrans2fid').value = `${donprixitran1fi[key].ident_ligne}`;
                                                                                        }
                                                                                    }
                                                                                };
                                                                                httpPrixittransite1fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpPrixittransite1fi.send();
                                                                      
                                                                              
                                                                               
                                                                                const httpRequetteitra1fi = new XMLHttpRequest();
                                                                        
                                                                                    httpRequetteitra1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetras1fi}/${dbitra1fi}/${fnitra1fi}`, true);
                                                                                httpRequetteitra1fi.onload = () => {
                                                                                    const dattaitra1fi = JSON.parse(httpRequetteitra1fi.responseText);
                                                                                    console.debug(`${typeof dattaitra1fi} - ${dattaitra1fi.attributes}`, console.memory);
                                                                                    if (Object.entries(dattaitra1fi).length >= 1) {
                                                                                        for (let key in Object.entries(dattaitra1fi)) {
                                                                                            
                                                                                            let opt = document.createElement('option');
                                                                                            opt.value = `${dattaitra1fi[key].siege_num}`;
                                                                                            opt.innerHTML = `${dattaitra1fi[key].siege_num}`;
                                                                                            document.querySelector('#psiegesitines2fid').add(opt);
                                                                                            
                                                                                        }
                                                                                        
                                                                                    } else {
                                                                                        document.querySelector('#psiegesitines2fid').options.length = 1;
                                                                                    }
                                                                                };
                                                                                httpRequetteitra1fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpRequetteitra1fi.send();
                                                                        };

                                                                        let progsieges2fi = document.querySelector('#psiegesitines2fid');
                                                                        if (progsieges2fi !== null) 
                                                                        {
                                                                            progsieges2fi.onchange = () => 
                                                                            {
                                                                                    const transselitine2fi = document.querySelector('#idcheminsheur1fid')
                                                                                .options[document.querySelector('#idcheminsheur1fid').options.selectedIndex].value;
                                                                                var post_trans2fi = transselitine2fi.split('/');
                                                                                var itinetras2fi = post_trans2fi[0];
                                                                                    
                                                                                    const gareidentiftrans4fi = document.querySelector('#gidtrans1fid').value;
                                                                                    const httpsousgare4fi = new XMLHttpRequest();
                                                                                    httpsousgare4fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftrans4fi}`, true);
                                                                                    httpsousgare4fi.onload = () => 
                                                                                    {
                                                                                        const donsousg4fi = JSON.parse(httpsousgare4fi.responseText);
                                                                                        if (Object.entries(donsousg4fi).length >= 1) {
                                                                                            for (let key in Object.entries(donsousg4fi)) 
                                                                                            {
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${donsousg4fi[key].idsousgare}`;
                                                                                                opt.innerHTML = `${donsousg4fi[key].nomsousgare}`;
                                                                                                document.querySelector('#transitedepargare3fid').add(opt);
                                    
                                                                                            }
                                                                                        }
                                                                                    };
                                                                                    httpsousgare4fi.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpsousgare4fi.send();

                                                                                let httpSieges2fi;
                                                                                httpSieges2fi = new XMLHttpRequest();
                                                                                const sigs2fi = document.querySelector('#psiegesitines2fid')
                                                                                .options[document.querySelector('#psiegesitines2fid').options.selectedIndex].value;

                                                                                httpSieges2fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetras2fi}/${sigs2fi}`, true);
                                                                                httpSieges2fi.onload = () => 
                                                                                {
                                                                                    const donsge2fi = JSON.parse(httpSieges2fi.responseText);
                                                                                    if(donsge2fi == '')
                                                                                    {
                                                                                        let httpSiegs2fi;
                                                                                        httpSiegs2fi = new XMLHttpRequest();

                                                                                        httpSiegs2fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetras2fi}/${sigs2fi}`, true);
                                                                                        httpSiegs2fi.onload = () => 
                                                                                        {
                                                                                            const dong2fi = JSON.parse(httpSiegs2fi.responseText);
                                                                                            document.querySelector('#messfid').style.display = 'none';
                                                                                            if (Object.entries(dong2fi).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(dong2fi)) {
                                                                                                        document.querySelector('#idtampo2fid').value = `${dong2fi[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselect2fid').value = `${dong2fi[key].numsieg}`;
                                                                                                    }
                                                                                                }
                                                                                        };
                                                                                        httpSiegs2fi.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegs2fi.send();
                                                                                    }
                                                                                    else {
                                                                                        document.querySelector('#psiegesitines2fid').value = '';     
                                                                                        if (Object.entries(donsge2fi).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(donsge2fi)) {
                                                                                                document.querySelector('#idtampo2fid').value = `${donsge2fi[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselect2fid').value = `${donsge2fi[key].numsieg}`;
                                                                                            }

                                                                                        }
                                                                                        document.querySelector('#messfid').style.display = 'block';
                                                                                        document.querySelector('#erreurMessfid').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                                };
                                                                                httpSieges2fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSieges2fi.send();

                                                                            };
                                                                        }
                                                                }               
                                                            }

                                                            //troisieme itineraire
                                                            if(i === 4)
                                                            {
                                                                let opt = document.createElement('option');
                                                                opt.value = `${donitinesfi[1].code_itineraires}`;
                                                                opt.innerHTML = `${donitinesfi[1].nom_itineraires}`;
                                                                document.querySelector('#idcheminsfid').add(opt);


                                                                let opt1 = document.createElement('option');
                                                                opt1.value = `${donitinesfi[2].code_itineraires}`;
                                                                opt1.innerHTML = `${donitinesfi[2].nom_itineraires}`;
                                                                document.querySelector('#idchemins1fid').add(opt1);

                                                                let opt2 = document.createElement('option');
                                                                opt2.value = `${donitinesfi[3].code_itineraires}`;
                                                                opt2.innerHTML = `${donitinesfi[3].nom_itineraires}`;
                                                                document.querySelector('#idchemins2fid').add(opt2);

                                                                document.querySelector('#lignesitinerairefid').value = `${donitinesfi[0].nom_itineraires}`;
                                                               
                                                                document.querySelector('#itinecodesfid').value = `${donitinesfi[0].id_lignes}`;

                                                                    var typgare1fi = document.querySelector('#itinecodefid').value;
                                                                var post_typgare1fi = typgare1fi.split('-');
                                                                var seltypgare1fi = post_typgare1fi[0];
                                                                var typgareselfi = post_typgare1fi[1];
                                                                    let httptypequart1fi;
                                                                    httptypequart1fi = new XMLHttpRequest();
                                                                    
                                                                    httptypequart1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgareselfi}`, true);
                                                                    httptypequart1fi.onload = () => 
                                                                    {
                                                                        const donqua1fi = JSON.parse(httptypequart1fi.responseText);
                                                                        if (donqua1fi == '') {
                                                                            document.querySelector('#quartier1fid').options.length = 1;
                                                                        }
                                                                        else{
                                                                            if (Object.entries(donqua1fi).length >= 1) {
                                                                                            
                                                                                for (let key in Object.entries(donqua1fi)) {
                                                                                    let optq = document.createElement('option');
                                                                                    optq.value = `${donqua1fi[key].nom_quartier}`;
                                                                                    optq.innerHTML = `${donqua1fi[key].nom_quartier}`;
                                                                                    document.querySelector('#quartier1fid').add(optq);
                                                                                }
                                                                            } else {
                                                                                document.querySelector('#quartier1fid').options.length = 1;
                                                                            }
                                                                        }
                                                                        

                                                                    };
                                                                    httptypequart1fi.setRequestHeader('Content-Type', 'application/json');
                                                                    httptypequart1fi.send();



                                                                        let httptypequartitin1fi;
                                                                        httptypequartitin1fi = new XMLHttpRequest();
                                                                        var datedepartfi = document.querySelector('#date_depheurefid').value;
                                                                        var itinpro1fi = document.querySelector('#itinecodefid').value;
                                                                        httptypequartitin1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifheureitine/${itinpro1fi}/${datedepartfi}`, true);
                                                                    httptypequartitin1fi.onload = () => 
                                                                    {
                                                                        const infositin1fi = JSON.parse(httptypequartitin1fi.responseText);
                                                                        if (infositin1fi == null) 
                                                                        {


                                                                        }
                                                                        if (Object.entries(infositin1fi).length >= 1) 
                                                                        {
                                                                                
                                                                            
                                                                            for (let key in Object.entries(infositin1fi)) {
                                                                                    let opt = document.createElement('option');
                                                                                    opt.value = `${infositin1fi[key].id_ligneheure}/${infositin1fi[key].heure}`;
                                                                                    opt.innerHTML = `${infositin1fi[key].heure}`;
                                                                                    document.querySelector('#hdepartitinefid').add(opt);
                                                                                }
                                                                        } else {
                                                                            document.querySelector('#hdepartitinefid').options.length = 1;
                                                                        }
                                                                    };
                                                                    httptypequartitin1fi.setRequestHeader('Content-Type', 'application/json');
                                                                    httptypequartitin1fi.send();
                                                                let hrdepartine1fi = document.querySelector('#hdepartitinefid');
                                                                if (hrdepartine1fi !== null) {
                                                                    hrdepartine1fi.onchange = () => 
                                                                    {
                                                                        document.querySelector('#psiegesitinesfid').options.length = 1;
                                                                        const httpRequestit1fi = new XMLHttpRequest();
                                                                        const seleitine1fi = document.querySelector('#hdepartitinefid')
                                                                            .options[document.querySelector('#hdepartitinefid').options.selectedIndex].value;

                                                                            var post_lhitine1fi = seleitine1fi.split('/');
                                                                            var selitine1fi = post_lhitine1fi[0];
                                                                            var lhselitine1fi = post_lhitine1fi[1];

                                                                            const dpt_dateitine1fi = document.querySelector('#date_depheurefid').value;
                                                                            var itinproit1fi = document.querySelector('#itinecodefid').value;
                                                                        httpRequestit1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifprog/${itinproit1fi}/${dpt_dateitine1fi}/${selitine1fi}`, true);
                                                                        httpRequestit1fi.onload = () => 
                                                                        {
                                                                            const donit1fi = JSON.parse(httpRequestit1fi.responseText);
                                                                                console.debug(`${typeof donit1fi} - ${donit1fi.attributes}`, console.memory);

                                                                                if (donit1fi == '') 
                                                                                {
                                                                                    
                                                                                        let opt = document.createElement('option');
                                                                                        opt.value = '';                                                             
                                                                                   
                                                                                    
                                                                                    
                                                                                } 
                                                                                else 
                                                                                {       
                                                                                    if (Object.entries(donit1fi).length >= 1) {
                                                                                        for (let key in Object.entries(donit1fi)) {
                                                                                            document.querySelector('#programtransfid').value = `${donit1fi[key].code_progr}`;
                                                                                            document.querySelector('#dateprtransfid').value = `${donit1fi[key].date_progr}`;
                                                                                            document.querySelector('#deplignetransfid').value = `${donit1fi[key].gareidentif}`;
                                                                                            document.querySelector('#intertrans1fid').value = `${donit1fi[key].intervalle1}`;
                                                                                            document.querySelector('#intertrans2fid').value = `${donit1fi[key].intervalle2}`;
                                                                                            document.querySelector('#ligntransfid').value = `${donit1fi[key].ident_ligne}`;
                                                                                            document.querySelector('#nomitintransfid').value = `${donit1fi[key].nom_ligne}`;
                                                                                            document.querySelector('#hertransfid').value = `${donit1fi[key].heure}`;
                                                                                            document.querySelector('#catetransfid').value = `${donit1fi[key].categori}`;

                                                                                        }
                                                                                    } 
                                                                                    
                                                                                    
                                                                                    const seleitinefi = document.querySelector('#hdepartitinefid')
                                                                                    .options[document.querySelector('#hdepartitinefid').options.selectedIndex].value;

                                                                                    var post_lhitinefi = seleitinefi.split('/');
                                                                                    var selitinefi = post_lhitinefi[0];
                                                                                    var lhselitinefi = post_lhitinefi[1];

                                                                                    

                                                                                    

                                                                                    const httpRequetteitfi = new XMLHttpRequest();
                                                                                    const cdprogitfi = document.querySelector('#programtransfid').value;
                                                                                    const dbitfi = document.querySelector('#intertrans1fid').value;
                                                                                    const fnitfi = document.querySelector('#intertrans2fid').value;
                                                                                    const lgitfi = document.querySelector('#nomitintransfid').value;
                                                                                    const timitfi = document.querySelector('#hertransfid').value;
                                                                                    const dpt_dateitinefi = document.querySelector('#date_depheurefid').value;
                                                                                        httpRequetteitfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${cdprogitfi}/${dpt_dateitinefi}/${lgitfi}/${timitfi}/${dbitfi}/${fnitfi}`, true);
                                                                                    httpRequetteitfi.onload = () => {
                                                                                        const dattaitfi = JSON.parse(httpRequetteitfi.responseText);
                                                                                        console.debug(`${typeof dattaitfi} - ${dattaitfi.attributes}`, console.memory);
                                                                                        if (Object.entries(dattaitfi).length >= 1) {
                                                                                            for (let key in Object.entries(dattaitfi)) {
                                                                                                
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dattaitfi[key].siege_num}`;
                                                                                                opt.innerHTML = `${dattaitfi[key].siege_num}`;
                                                                                                document.querySelector('#psiegesitinesfid').add(opt);
                                                                                                
                                                                                            }
                                                                                            
                                                                                        } else {
                                                                                            document.querySelector('#psiegesitinesfid').options.length = 1;
                                                                                        }
                                                                                    };
                                                                                    httpRequetteitfi.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpRequetteitfi.send();

                                                                                }  
                                                                                
                                                                        };
                                                                        httpRequestit1fi.setRequestHeader('Content-Type', 'application/json');
                                                                        httpRequestit1fi.send();
                                                                         
                                                                    };
                                                                    
                                                            
                                                                }
                                                                let progsiegestransfi = document.querySelector('#psiegesitinesfid');
                                                                if (progsiegestransfi !== null) {
                                                                    progsiegestransfi.onchange = () => 
                                                                    {

                                                                       const gareidentiftrans1fi = document.querySelector('#deplignetransfid').value;
                                                                                    const httpsousgarefi = new XMLHttpRequest();
                                                                                    httpsousgarefi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftrans1fi}`, true);
                                                                                    httpsousgarefi.onload = () => 
                                                                                    {
                                                                                        const donsousgfi = JSON.parse(httpsousgarefi.responseText);
                                                                                        console.debug(`${typeof donsousgfi}-${donsousgfi.attributes}`, console.memory);
                                                                                        if (Object.entries(donsousgfi).length >= 1) {
                                                                                            for (let key in Object.entries(donsousgfi)) 
                                                                                            {
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${donsousgfi[key].idsousgare}`;
                                                                                                opt.innerHTML = `${donsousgfi[key].nomsousgare}`;
                                                                                                document.querySelector('#transitedepargare1fid').add(opt);
                                    
                                                                                            }
                                                                                        }
                                                                                    };
                                                                                    httpsousgarefi.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpsousgarefi.send();
                                                                        let httpSiegestrans1fi;
                                                                        httpSiegestrans1fi = new XMLHttpRequest();
                                                                        const sigstransfi = document.querySelector('#psiegesitinesfid')
                                                                        .options[document.querySelector('#psiegesitinesfid').options.selectedIndex].value;
                                                                        const prostransfi = document.querySelector('#programtransfid').value;

                                                                        httpSiegestrans1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${prostransfi}/${sigstransfi}`, true);
                                                                        httpSiegestrans1fi.onload = () => 
                                                                        {
                                                                            const donsgetransfi = JSON.parse(httpSiegestrans1fi.responseText);
                                                                            console.debug(`${typeof donsgetransfi} - ${donsgetransfi.attributes}`, console.memory);
                                                                            if(donsgetransfi == '')
                                                                            {
                                                                                let httpSiegstransfi;
                                                                                httpSiegstransfi = new XMLHttpRequest();

                                                                                httpSiegstransfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${prostransfi}/${sigstransfi}`, true);
                                                                                httpSiegstransfi.onload = () => 
                                                                                {
                                                                                    const dongtransfi = JSON.parse(httpSiegstransfi.responseText);
                                                                                    document.querySelector('#messfid').style.display = 'none';
                                                                                    if (Object.entries(dongtransfi).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtransfi)) {
                                                                                                document.querySelector('#idtampotransfid').value = `${dongtransfi[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselecttransfid').value = `${dongtransfi[key].numsieg}`;
                                                                                            }
                                                                                        }
                                                                                };
                                                                                httpSiegstransfi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSiegstransfi.send();
                                                                            }
                                                                            else {
                                                                                document.querySelector('#psiegesitinesfid').value = '';     
                                                                                if (Object.entries(donsgetransfi).length >= 1)
                                                                                {
                                                                                    for (let key in Object.entries(donsgetransfi)) {
                                                                                        document.querySelector('#idtampotransfid').value = `${donsgetransfi[key].idtamp}`;                    
                                                                                        document.querySelector('#siegselecttransfid').value = `${donsgetransfi[key].numsieg}`;
                                                                                    }

                                                                                }
                                                                                document.querySelector('#messfid').style.display = 'block';
                                                                                document.querySelector('#erreurMessfid').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                        };
                                                                        httpSiegestrans1fi.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegestrans1fi.send();

                                                                    
                                                                    };
                                                                }
                                                                //premier transite
                                                                let progcheminfi = document.querySelector('#idcheminsfid');
                                                                if (progcheminfi !== null) 
                                                                {
                                                                    progcheminfi.onchange = () => 
                                                                    {

                                                                        document.querySelector('#idcheminsheurfid').options.length = 1;
                                                                        document.querySelector('#psiegesitines1fid').options.length = 1;
                                                                       

                                                                        var datedepartfi = document.querySelector('#date_depheurefid').value;
                                                                        
                                                                        const prostranscheminfi = document.querySelector('#idcheminsfid')
                                                                        .options[document.querySelector('#idcheminsfid').options.selectedIndex].value;

                                                                        var post_typgare2fi = prostranscheminfi.split('-');
                                                                        var seltypgare2fi = post_typgare2fi[0];
                                                                        var typgaresel1fi = post_typgare2fi[1];
                                                                        let httptypequart2fi;
                                                                        httptypequart2fi = new XMLHttpRequest();
                                                                        
                                                                        httptypequart2fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgaresel1fi}`, true);
                                                                        httptypequart2fi.onload = () => 
                                                                        {
                                                                            const donqua2fi = JSON.parse(httptypequart2fi.responseText);
                                                                            if (donqua2fi == '') {
                                                                                document.querySelector('#quartier2fid').options.length = 1;
                                                                            }
                                                                            else{
                                                                                if (Object.entries(donqua2fi).length >= 1) {
                                                                                                
                                                                                    for (let key in Object.entries(donqua2fi)) {
                                                                                        let optq1 = document.createElement('option');
                                                                                        optq1.value = `${donqua2fi[key].nom_quartier}`;
                                                                                        optq1.innerHTML = `${donqua2fi[key].nom_quartier}`;
                                                                                        document.querySelector('#quartier2fid').add(optq1);
                                                                                    }
                                                                                } else {
                                                                                    document.querySelector('#quartier2fid').options.length = 1;
                                                                                }
                                                                            }
                                                                            

                                                                        };
                                                                        httptypequart2fi.setRequestHeader('Content-Type', 'application/json');
                                                                        httptypequart2fi.send();
                                                                        
                                                                        let httpSiegescheminfi;
                                                                        httpSiegescheminfi = new XMLHttpRequest();
                                                                        
                                                                        httpSiegescheminfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranscheminfi}/${datedepartfi}`, true);
                                                                        httpSiegescheminfi.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschemfi = JSON.parse(httpSiegescheminfi.responseText);
                                                                                    if (Object.entries(dongtranschemfi).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtranschemfi)) {
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dongtranschemfi[key].code_progr}/${dongtranschemfi[key].intervalle1}/${dongtranschemfi[key].intervalle2}/${dongtranschemfi[key].id_ligneheure}/${dongtranschemfi[key].prix}`;
                                                                                                opt.innerHTML = `${dongtranschemfi[key].heure}/${dongtranschemfi[key].date_progr}`;
                                                                                                document.querySelector('#idcheminsheurfid').add(opt);
                                                                                            }
                                                                                        }
                                                                        };
                                                                        httpSiegescheminfi.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegescheminfi.send();

                                                                    };
                                                                        let prochemintrafi = document.querySelector('#idcheminsheurfid');
                                                                        if (prochemintrafi !== null){
                                                                            prochemintrafi.onchange = () => 
                                                                            {  
                                                                                
                                                                                document.querySelector('#psiegesitines1fid').options.length = 1;
                                                                                const httpPrixittransitefi = new XMLHttpRequest();
                                                                                    const transselitinefi = document.querySelector('#idcheminsheurfid')
                                                                                .options[document.querySelector('#idcheminsheurfid').options.selectedIndex].value;
                                                                                    var post_transfi = transselitinefi.split('/');
                                                                                var itinetrasfi = post_transfi[0];
                                                                                var dbitrafi = post_transfi[1];
                                                                                var fnitrafi = post_transfi[2];
                                                                                var lhertrafi = post_transfi[3];
                                                                                var prixtrafi = post_transfi[4];

                                                                                    httpPrixittransitefi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetrasfi}`, true);
                                                                                    httpPrixittransitefi.onload = () => 
                                                                                    {
                                                                                        const donprixitranfi = JSON.parse(httpPrixittransitefi.responseText);
                                                                                        console.debug(`${typeof donprixitranfi}-${donprixitranfi.attributes}`, console.memory);
                                                                                        if (Object.entries(donprixitranfi).length >= 1) {
                                                                                            for (let key in Object.entries(donprixitranfi)) 
                                                                                            {
                                                                                                document.querySelector('#catetransitfid').value = `${donprixitranfi[key].categori}`;
                                                                                                document.querySelector('#gidtransfid').value =  `${donprixitranfi[key].gareidentif}`;
                                                                                                document.querySelector('#nomitintrans1fid').value = `${donprixitranfi[key].nom_ligne}`;
                                                                                                document.querySelector('#ligntrans1fid').value = `${donprixitranfi[key].ident_ligne}`;
                                                                                            }
                                                                                        }
                                                                                    };
                                                                                    httpPrixittransitefi.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpPrixittransitefi.send();
                                                                          

                                                                                    
                                                                                    const httpRequetteitrafi = new XMLHttpRequest();
                                                                            
                                                                                        httpRequetteitrafi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetrasfi}/${dbitrafi}/${fnitrafi}`, true);
                                                                                    httpRequetteitrafi.onload = () => {
                                                                                        const dattaitrafi = JSON.parse(httpRequetteitrafi.responseText);
                                                                                        console.debug(`${typeof dattaitrafi} - ${dattaitrafi.attributes}`, console.memory);
                                                                                        if (Object.entries(dattaitrafi).length >= 1) {
                                                                                            for (let key in Object.entries(dattaitrafi)) {
                                                                                                
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dattaitrafi[key].siege_num}`;
                                                                                                opt.innerHTML = `${dattaitrafi[key].siege_num}`;
                                                                                                document.querySelector('#psiegesitines1fid').add(opt);
                                                                                                
                                                                                            }
                                                                                            
                                                                                        } else {
                                                                                            document.querySelector('#psiegesitines1fid').options.length = 1;
                                                                                        }
                                                                                    };
                                                                                    httpRequetteitrafi.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpRequetteitrafi.send();
                                                                            };
                                                                        }
                                                                        let progsieges1fi = document.querySelector('#psiegesitines1fid');
                                                                        if (progsieges1fi !== null) 
                                                                        {
                                                                            progsieges1fi.onchange = () => 
                                                                            {

                                                                               const gareidentiftrans2fi = document.querySelector('#gidtransfid').value;
                                                                                    const httpsousgare1fi = new XMLHttpRequest();
                                                                                    httpsousgare1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftrans2fi}`, true);
                                                                                    httpsousgare1fi.onload = () => 
                                                                                    {
                                                                                        const donsousg1fi = JSON.parse(httpsousgare1fi.responseText);
                                                                                        console.debug(`${typeof donsousg1fi}-${donsousg1fi.attributes}`, console.memory);
                                                                                        if (Object.entries(donsousg1fi).length >= 1) {
                                                                                            for (let key in Object.entries(donsousg1fi)) 
                                                                                            {
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${donsousg1fi[key].idsousgare}`;
                                                                                                opt.innerHTML = `${donsousg1fi[key].nomsousgare}`;
                                                                                                document.querySelector('#transitedepargare2fid').add(opt);
                                    
                                                                                            }
                                                                                        }
                                                                                    };
                                                                                    httpsousgare1fi.setRequestHeader('Content-Type', 'application/json');
                                                                                    httpsousgare1fi.send();
                                                                                

                                                                                    const transselitine1fi = document.querySelector('#idcheminsheurfid')
                                                                                .options[document.querySelector('#idcheminsheurfid').options.selectedIndex].value;
                                                                                var post_trans1fi = transselitine1fi.split('/');
                                                                                var itinetras1fi = post_trans1fi[0];
                                                                    
                                                                                let httpSieges1fi;
                                                                                httpSieges1fi = new XMLHttpRequest();
                                                                                const sigs1fi = document.querySelector('#psiegesitines1fid')
                                                                                .options[document.querySelector('#psiegesitines1fid').options.selectedIndex].value;

                                                                                httpSieges1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetras1fi}/${sigs1fi}`, true);
                                                                                httpSieges1fi.onload = () => 
                                                                                {
                                                                                    const donsge1fi = JSON.parse(httpSieges1fi.responseText);
                                                                                    console.debug(`${typeof donsge1fi} - ${donsge1fi.attributes}`, console.memory);
                                                                                    if(donsge1fi == '')
                                                                                    {
                                                                                        let httpSiegs1fi;
                                                                                        httpSiegs1fi = new XMLHttpRequest();

                                                                                        httpSiegs1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetras1fi}/${sigs1fi}`, true);
                                                                                        httpSiegs1fi.onload = () => 
                                                                                        {
                                                                                            const dong1fi = JSON.parse(httpSiegs1fi.responseText);
                                                                                            document.querySelector('#messfid').style.display = 'none';
                                                                                            if (Object.entries(dong1fi).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(dong1fi)) {
                                                                                                        document.querySelector('#idtampo1fid').value = `${dong1fi[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselect1fid').value = `${dong1fi[key].numsieg}`;
                                                                                                    }
                                                                                                }
                                                                                        };
                                                                                        httpSiegs1fi.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegs1fi.send();
                                                                                    }
                                                                                    else {
                                                                                        document.querySelector('#psiegesitines1fid').value = '';     
                                                                                        if (Object.entries(donsge1fi).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(donsge1fi)) {
                                                                                                document.querySelector('#idtampo1fid').value = `${donsge1fi[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselect1fid').value = `${donsge1fi[key].numsieg}`;
                                                                                            }

                                                                                        }
                                                                                        document.querySelector('#messfid').style.display = 'block';
                                                                                        document.querySelector('#erreurMessfid').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                                };
                                                                                httpSieges1fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSieges1fi.send();

                                                                            };
                                                                        }
                                                                }
                                                                //deuxieme transite
                                                                let progchemin1fi = document.querySelector('#idchemins1fid');
                                                                if (progchemin1fi !== null) 
                                                                {
                                                                    progchemin1fi.onchange = () => 
                                                                    {
                                                                        document.querySelector('#idcheminsheur1fid').options.length = 1;
                                                                        document.querySelector('#psiegesitines2fid').options.length = 1;

                                                                        const prostranschemin32fi = document.querySelector('#idchemins1fid')
                                                                        .options[document.querySelector('#idchemins1fid').options.selectedIndex].value;

                                                                        var post_typgare32fi = prostranschemin32fi.split('-');
                                                                        var seltypgare32fi = post_typgare32fi[0];
                                                                        var typgaresel31fi = post_typgare32fi[1];
                                                                        let httptypequart32fi;
                                                                        httptypequart32fi = new XMLHttpRequest();
                                                                        
                                                                        httptypequart32fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgaresel31fi}`, true);
                                                                        httptypequart32fi.onload = () => 
                                                                        {
                                                                            const donqua32fi = JSON.parse(httptypequart32fi.responseText);
                                                                            if (donqua32fi == '') {
                                                                                document.querySelector('#quartier3fid').options.length = 1;
                                                                            }
                                                                            else{
                                                                                if (Object.entries(donqua32fi).length >= 1) {
                                                                                                
                                                                                    for (let key in Object.entries(donqua32fi)) {
                                                                                        let optq31 = document.createElement('option');
                                                                                        optq31.value = `${donqua32fi[key].nom_quartier}`;
                                                                                        optq31.innerHTML = `${donqua32fi[key].nom_quartier}`;
                                                                                        document.querySelector('#quartier3fid').add(optq31);
                                                                                    }
                                                                                } else {
                                                                                    document.querySelector('#quartier3fid').options.length = 1;
                                                                                }
                                                                            }
                                                                            

                                                                        };
                                                                        httptypequart32fi.setRequestHeader('Content-Type', 'application/json');
                                                                        httptypequart32fi.send();
                                                                        
                                                                        let httpSiegeschemin1fi;
                                                                        httpSiegeschemin1fi = new XMLHttpRequest();
                                                                        
                                                                        var datedepartfi = document.querySelector('#date_depheurefid').value;
                                                                        const prostranschemin1fi = document.querySelector('#idchemins1fid')
                                                                        .options[document.querySelector('#idchemins1fid').options.selectedIndex].value;

                                                                        httpSiegeschemin1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranschemin1fi}/${datedepartfi}`, true);
                                                                        httpSiegeschemin1fi.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschem1fi = JSON.parse(httpSiegeschemin1fi.responseText);
                                                                                    if (Object.entries(dongtranschem1fi).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtranschem1fi)) {
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dongtranschem1fi[key].code_progr}/${dongtranschem1fi[key].intervalle1}/${dongtranschem1fi[key].intervalle2}/${dongtranschem1fi[key].id_ligneheure}/${dongtranschem1fi[key].prix}`;
                                                                                                opt.innerHTML = `${dongtranschem1fi[key].heure}/${dongtranschem1fi[key].date_progr}`;
                                                                                                document.querySelector('#idcheminsheur1fid').add(opt);
                                                                                            }
                                                                                        }
                                                                        };
                                                                        httpSiegeschemin1fi.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegeschemin1fi.send();

                                                                    };
                                                                       let prochemintra1fi = document.querySelector('#idcheminsheur1fid');
                                                                    if (prochemintra1fi !== null)
                                                                        prochemintra1fi.onchange = () => 
                                                                        {  
                                                                            
                                                                            document.querySelector('#psiegesitines2fid').options.length = 1;

                                                                            const httpPrixittransite1fi = new XMLHttpRequest();
                                                                                const transselitine1fi = document.querySelector('#idcheminsheur1fid')
                                                                            .options[document.querySelector('#idcheminsheur1fid').options.selectedIndex].value;
                                                                                var post_trans1fi = transselitine1fi.split('/');
                                                                            var itinetras1fi = post_trans1fi[0];
                                                                            var dbitra1fi = post_trans1fi[1];
                                                                            var fnitra1fi = post_trans1fi[2];
                                                                            var lhertra1fi = post_trans1fi[3];
                                                                            var prixtra1fi = post_trans1fi[4];

                                                                                httpPrixittransite1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetras1fi}`, true);
                                                                                httpPrixittransite1fi.onload = () => 
                                                                                {
                                                                                    const donprixitran1fi = JSON.parse(httpPrixittransite1fi.responseText);
                                                                                    if (Object.entries(donprixitran1fi).length >= 1) {
                                                                                        for (let key in Object.entries(donprixitran1fi)) 
                                                                                        {
                                                                                            document.querySelector('#catetransit1fid').value = `${donprixitran1fi[key].categori}`;
                                                                                            document.querySelector('#gidtrans1fid').value =  `${donprixitran1fi[key].gareidentif}`;
                                                                                            document.querySelector('#nomitintrans2fid').value = `${donprixitran1fi[key].nom_ligne}`;
                                                                                            document.querySelector('#ligntrans2fid').value = `${donprixitran1fi[key].ident_ligne}`;
                                                                                        }
                                                                                    }
                                                                                };
                                                                                httpPrixittransite1fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpPrixittransite1fi.send();
                                                                      
                                                                                

                                                                                const httpRequetteitra1fi = new XMLHttpRequest();
                                                                        
                                                                                    httpRequetteitra1fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetras1fi}/${dbitra1fi}/${fnitra1fi}`, true);
                                                                                httpRequetteitra1fi.onload = () => {
                                                                                    const dattaitra1fi = JSON.parse(httpRequetteitra1fi.responseText);
                                                                                    if (Object.entries(dattaitra1fi).length >= 1) {
                                                                                        for (let key in Object.entries(dattaitra1fi)) {
                                                                                            
                                                                                            let opt = document.createElement('option');
                                                                                            opt.value = `${dattaitra1fi[key].siege_num}`;
                                                                                            opt.innerHTML = `${dattaitra1fi[key].siege_num}`;
                                                                                            document.querySelector('#psiegesitines2fid').add(opt);
                                                                                            
                                                                                        }
                                                                                        
                                                                                    } else {
                                                                                        document.querySelector('#psiegesitines2fid').options.length = 1;
                                                                                    }
                                                                                };
                                                                                httpRequetteitra1fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpRequetteitra1fi.send();
                                                                        };

                                                                       let progsieges2fi = document.querySelector('#psiegesitines2fid');
                                                                        if (progsieges2fi !== null) 
                                                                        {
                                                                            progsieges2fi.onchange = () => 
                                                                            {

                                                                               const gareidentiftrans4fi = document.querySelector('#gidtrans1fid').value;
                                                                                const httpsousgare4fi = new XMLHttpRequest();
                                                                                httpsousgare4fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftrans4fi}`, true);
                                                                                httpsousgare4fi.onload = () => 
                                                                                {
                                                                                    const donsousg4fi = JSON.parse(httpsousgare4fi.responseText);
                                                                                    console.debug(`${typeof donsousg4fi}-${donsousg4fi.attributes}`, console.memory);
                                                                                    if (Object.entries(donsousg4fi).length >= 1) {
                                                                                        for (let key in Object.entries(donsousg4fi)) 
                                                                                        {
                                                                                            let opt = document.createElement('option');
                                                                                            opt.value = `${donsousg4fi[key].idsousgare}`;
                                                                                            opt.innerHTML = `${donsousg4fi[key].nomsousgare}`;
                                                                                            document.querySelector('#transitedepargare3fid').add(opt);
                                
                                                                                        }
                                                                                    }
                                                                                };
                                                                                httpsousgare4fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpsousgare4fi.send();
                                                                                    const transselitine2fi = document.querySelector('#idcheminsheur1fid')
                                                                                .options[document.querySelector('#idcheminsheur1fid').options.selectedIndex].value;
                                                                                var post_trans2fi = transselitine2fi.split('/');
                                                                                var itinetras2fi = post_trans2fi[0];
                                                                    
                                                                                let httpSieges2fi;
                                                                                httpSieges2fi = new XMLHttpRequest();
                                                                                const sigs2fi = document.querySelector('#psiegesitines2fid')
                                                                                .options[document.querySelector('#psiegesitines2fid').options.selectedIndex].value;

                                                                                httpSieges2fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetras2fi}/${sigs2fi}`, true);
                                                                                httpSieges2fi.onload = () => 
                                                                                {
                                                                                    const donsge2fi = JSON.parse(httpSieges2fi.responseText);
                                                                                    if(donsge2fi == '')
                                                                                    {
                                                                                        let httpSiegs2fi;
                                                                                        httpSiegs2fi = new XMLHttpRequest();

                                                                                        httpSiegs2fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetras2fi}/${sigs2fi}`, true);
                                                                                        httpSiegs2fi.onload = () => 
                                                                                        {
                                                                                            const dong2fi = JSON.parse(httpSiegs2fi.responseText);
                                                                                            document.querySelector('#messfid').style.display = 'none';
                                                                                            if (Object.entries(dong2fi).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(dong2fi)) {
                                                                                                        document.querySelector('#idtampo2fid').value = `${dong2fi[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselect2fid').value = `${dong2fi[key].numsieg}`;
                                                                                                    }
                                                                                                }
                                                                                        };
                                                                                        httpSiegs2fi.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegs2fi.send();
                                                                                    }
                                                                                    else {
                                                                                        document.querySelector('#psiegesitines2fid').value = '';     
                                                                                        if (Object.entries(donsge2fi).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(donsge2fi)) {
                                                                                                document.querySelector('#idtampo2fid').value = `${donsge2fi[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselect2fid').value = `${donsge2fi[key].numsieg}`;
                                                                                            }

                                                                                        }
                                                                                        document.querySelector('#messfid').style.display = 'block';
                                                                                        document.querySelector('#erreurMessfid').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                                };
                                                                                httpSieges2fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSieges2fi.send();

                                                                            };
                                                                        }
                                                                }   

                                                                //troisieme transite
                                                               let progchemin2fi = document.querySelector('#idchemins2fid');
                                                                if (progchemin2fi !== null) 
                                                                {
                                                                    progchemin2fi.onchange = () => 
                                                                    {
                                                                        document.querySelector('#idcheminsheur2fid').options.length = 1;
                                                                        document.querySelector('#psiegesitines3fid').options.length = 1;

                                                                        const prostranschemin42fi = document.querySelector('#idchemins2fid')
                                                                        .options[document.querySelector('#idchemins2fid').options.selectedIndex].value;

                                                                        var post_typgare42fi = prostranschemin42fi.split('-');
                                                                        var seltypgare42fi = post_typgare42fi[0];
                                                                        var typgaresel41fi = post_typgare42fi[1];
                                                                        

                                                                        let httpSiegeschemin2fi;
                                                                        httpSiegeschemin2fi = new XMLHttpRequest();
                                                                        const prostranschemin2fi = document.querySelector('#idchemins2fid')
                                                                        .options[document.querySelector('#idchemins2fid').options.selectedIndex].value;

                                                                        var datedepartfi = document.querySelector('#date_depheurefid').value;
                                                                        
                                                                        httpSiegeschemin2fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranschemin2fi}/${datedepartfi}`, true);
                                                                        httpSiegeschemin2fi.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschem2fi = JSON.parse(httpSiegeschemin2fi.responseText);
                                                                                    if (Object.entries(dongtranschem2fi).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtranschem2fi)) {
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dongtranschem2fi[key].code_progr}/${dongtranschem2fi[key].intervalle1}/${dongtranschem2fi[key].intervalle2}/${dongtranschem2fi[key].id_ligneheure}/${dongtranschem2fi[key].prix}`;
                                                                                                opt.innerHTML = `${dongtranschem2fi[key].heure}/${dongtranschem2fi[key].date_progr}`;
                                                                                                document.querySelector('#idcheminsheur2fid').add(opt);
                                                                                            }
                                                                                        }
                                                                        };
                                                                        httpSiegeschemin2fi.setRequestHeader('Content-Type', 'application/json');
                                                                        httpSiegeschemin2fi.send();

                                                                    };
                                                                      let prochemintra2fi = document.querySelector('#idcheminsheur2fid');
                                                                    if (prochemintra2fi !== null)
                                                                        prochemintra2fi.onchange = () => 
                                                                        {  
                                                                            
                                                                            document.querySelector('#psiegesitines3fid').options.length = 1;

                                                                            const httpPrixittransite2fi = new XMLHttpRequest();
                                                                                const transselitine2fi = document.querySelector('#idcheminsheur2fid')
                                                                            .options[document.querySelector('#idcheminsheur2fid').options.selectedIndex].value;
                                                                                var post_trans2fi = transselitine2fi.split('/');
                                                                            var itinetras2fi = post_trans2fi[0];
                                                                            var dbitra2fi = post_trans2fi[1];
                                                                            var fnitra2fi = post_trans2fi[2];
                                                                            var lhertra2fi = post_trans2fi[3];
                                                                            var prixtra2fi = post_trans2fi[4];

                                                                                httpPrixittransite2fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetras2fi}`, true);
                                                                                httpPrixittransite2fi.onload = () => 
                                                                                {
                                                                                    const donprixitran2fi = JSON.parse(httpPrixittransite2fi.responseText);
                                                                                    if (Object.entries(donprixitran2fi).length >= 1) {
                                                                                        for (let key in Object.entries(donprixitran2fi)) 
                                                                                        {
                                                                                            document.querySelector('#catetransit2fid').value = `${donprixitran2fi[key].categori}`;
                                                                                            document.querySelector('#gidtrans2fid').value =  `${donprixitran2fi[key].gareidentif}`;
                                                                                            document.querySelector('#nomitintrans3fid').value = `${donprixitran2fi[key].nom_ligne}`;
                                                                                            document.querySelector('#ligntrans3fid').value = `${donprixitran2fi[key].ident_ligne}`;
                                                                                        }
                                                                                    }
                                                                                };
                                                                                httpPrixittransite2fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpPrixittransite2fi.send();
                                                                      
                                                                                

                                                                                const httpRequetteitra2fi = new XMLHttpRequest();
                                                                        
                                                                                    httpRequetteitra2fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetras2fi}/${dbitra2fi}/${fnitra2fi}`, true);
                                                                                httpRequetteitra2fi.onload = () => {
                                                                                    const dattaitra2fi = JSON.parse(httpRequetteitra2fi.responseText);
                                                                                    console.debug(`${typeof dattaitra2fi} - ${dattaitra2fi.attributes}`, console.memory);
                                                                                    if (Object.entries(dattaitra2fi).length >= 1) {
                                                                                        for (let key in Object.entries(dattaitra2fi)) {
                                                                                            
                                                                                            let opt = document.createElement('option');
                                                                                            opt.value = `${dattaitra2fi[key].siege_num}`;
                                                                                            opt.innerHTML = `${dattaitra2fi[key].siege_num}`;
                                                                                            document.querySelector('#psiegesitines3fid').add(opt);
                                                                                            
                                                                                        }
                                                                                        
                                                                                    } else {
                                                                                        document.querySelector('#psiegesitines3fid').options.length = 1;
                                                                                    }
                                                                                };
                                                                                httpRequetteitra2fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpRequetteitra2fi.send();
                                                                        };

                                                                       let progsieges3fi = document.querySelector('#psiegesitines3fid');
                                                                        if (progsieges3fi !== null) 
                                                                        {
                                                                            progsieges3fi.onchange = () => 
                                                                            {

                                                                               const gareidentiftrans5fi = document.querySelector('#gidtrans2fid').value;
                                                                                const httpsousgare5fi = new XMLHttpRequest();
                                                                                httpsousgare5fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftrans5fi}`, true);
                                                                                httpsousgare5fi.onload = () => 
                                                                                {
                                                                                    const donsousg5fi = JSON.parse(httpsousgare5fi.responseText);
                                                                                    if (Object.entries(donsousg5fi).length >= 1) {
                                                                                        for (let key in Object.entries(donsousg5fi)) 
                                                                                        {
                                                                                            let opt = document.createElement('option');
                                                                                            opt.value = `${donsousg5fi[key].idsousgare}`;
                                                                                            opt.innerHTML = `${donsousg5fi[key].nomsousgare}`;
                                                                                            document.querySelector('#transitedepargare4fid').add(opt);
                                
                                                                                        }
                                                                                    }
                                                                                };
                                                                                httpsousgare5fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpsousgare5fi.send();
                                                                                    const transselitine3fi = document.querySelector('#idcheminsheur2fid')
                                                                                .options[document.querySelector('#idcheminsheur2fid').options.selectedIndex].value;
                                                                                var post_trans3fi = transselitine3fi.split('/');
                                                                                var itinetras3fi = post_trans3fi[0];
                                                                    
                                                                                let httpSieges3fi;
                                                                                httpSieges3fi = new XMLHttpRequest();
                                                                                const sigs3fi = document.querySelector('#psiegesitines3fid')
                                                                                .options[document.querySelector('#psiegesitines3fid').options.selectedIndex].value;

                                                                                httpSieges3fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetras3fi}/${sigs3fi}`, true);
                                                                                httpSieges3fi.onload = () => 
                                                                                {
                                                                                    const donsge3fi = JSON.parse(httpSieges3fi.responseText);
                                                                                    if(donsge3fi == '')
                                                                                    {
                                                                                        let httpSiegs3fi;
                                                                                        httpSiegs3fi = new XMLHttpRequest();

                                                                                        httpSiegs3fi.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetras3fi}/${sigs3fi}`, true);
                                                                                        httpSiegs3fi.onload = () => 
                                                                                        {
                                                                                            const dong3fi = JSON.parse(httpSiegs3fi.responseText);
                                                                                            document.querySelector('#messfid').style.display = 'none';
                                                                                            if (Object.entries(dong3fi).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(dong3fi)) {
                                                                                                        document.querySelector('#idtampo3fid').value = `${dong3fi[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselect3fid').value = `${dong3fi[key].numsieg}`;
                                                                                                    }
                                                                                                }
                                                                                        };
                                                                                        httpSiegs3fi.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegs3fi.send();
                                                                                    }
                                                                                    else {
                                                                                        document.querySelector('#psiegesitines3fid').value = '';     
                                                                                        if (Object.entries(donsge3fi).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(donsge3fi)) {
                                                                                                document.querySelector('#idtampo3fid').value = `${donsge3fi[key].idtamp}`;                    
                                                                                                document.querySelector('#siegselect3fid').value = `${donsge3fi[key].numsieg}`;
                                                                                            }

                                                                                        }
                                                                                        document.querySelector('#messfid').style.display = 'block';
                                                                                        document.querySelector('#erreurMessfid').innerHTML = `Siege déjà utilisé.`;                                                                   }
                                                                                };
                                                                                httpSieges3fi.setRequestHeader('Content-Type', 'application/json');
                                                                                httpSieges3fi.send();

                                                                            };
                                                                        }
                                                                }            
                                                            }
                                                                
                                                        }
                                                    }
                                        };
                                        httpRequestitinefi.setRequestHeader('Content-Type', 'application/json');
                                        httpRequestitinefi.send();
                                    } 
                                    else 
                                    {       
                                        
                                        document.querySelector('#smsdtfid').style.display = 'none';
                                        document.querySelector('#date_depheurefid').style.color = "black";
                                        document.querySelector('#date_depheurefid').style.border = "1px solid";
                                        if (Object.entries(dataAxefi).length >= 1) 
                                        {
                                                
                                            
    
                                            for (let key in Object.entries(dataAxefi)) {
                                                    let opt = document.createElement('option');
                                                    opt.value = `${dataAxefi[key].id_ligneheure}/${dataAxefi[key].heure}`;
                                                    opt.innerHTML = `${dataAxefi[key].heure}`;
                                                    document.querySelector('#hdepartfid').add(opt);
                                                }
                                        } else {
                                            document.querySelector('#hdepartfid').options.length = 1;
                                        }
                                    }

                                        let hrdepartfi = document.querySelector('#hdepartfid');
                                        if (hrdepartfi !== null) {
                                            hrdepartfi.onchange = () => 
                                            {
                                                document.querySelector('#psiegesfid').options.length = 1;
                                                document.querySelector('#typegarefid').value = '';
                                                const httpRequestfi = new XMLHttpRequest();
                                                const selefi = document.querySelector('#hdepartfid')
                                                    .options[document.querySelector('#hdepartfid').options.selectedIndex].value;

                                                    var post_lhfi = selefi.split('/');
                                                    var selfi = post_lhfi[0];
                                                    var lhselfi = post_lhfi[1];

                                                    const dpt_datefi = document.querySelector('#date_depheurefid').value;
                                                    var typgarefi = document.querySelector('#arrsgarefid').value;
                                                    const httptypegarefi = new XMLHttpRequest();
                                                    httptypegarefi.open('GET', window.location.origin + `${APP_ROOT}/programmes/gareprincipale/${typgarefi}/${lhselfi}`, true);
                                                    httptypegarefi.onload = () => 
                                                    {
                                                        const dongarefi = JSON.parse(httptypegarefi.responseText);
                                                        if (Object.entries(dongarefi).length >= 1)
                                                        for (let key in Object.entries(dongarefi)) 
                                                        document.querySelector('#typegarefid').value = `${dongarefi[key].typestatutgare}`;
                                                    };
                                                    httptypegarefi.setRequestHeader('Content-Type', 'application/json');
                                                    httptypegarefi.send();

                                                


                                                httpRequestfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifprog/${seltdepfi}-${arrfi}/${dpt_datefi}/${selfi}`, true);
                                                httpRequestfi.onload = () => 
                                                {
                                                    var typ_garefi = document.querySelector('#typegarefid').value;    
                                                    const donfi = JSON.parse(httpRequestfi.responseText);
                                                        //const tabe = [];
                                                        if (donfi == '') 
                                                        {
                                                            if(typ_garefi == 'Principale'){
                                                                
                                                                    let opt = document.createElement('option');
                                                                    opt.value = 1;
                                                                    opt.innerHTML = 1;
                                                                    document.querySelector('#psiegesfid').add(opt);
                                                            
                                                                    departpsiegesfi = document.querySelector('#psiegesfid');
                                                                    if (departpsiegesfi !== null) {
                                                                        departpsiegesfi.onchange = () => 
                                                                        {
                                                                            let httpProgfi;
                                                                            httpProgfi = new XMLHttpRequest();
                                                                            httpProgfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/creedepart/${seltdepfi}/${dpt_datefi}/${selfi}/${lhselfi}`, true);
                                                                            httpProgfi.onload = () => 
                                                                            {
                                                                                const donsfi = JSON.parse(httpProgfi.responseText);
                                                                                if (Object.entries(donsfi).length >= 1) {
                                                                                    for (let key in Object.entries(donsfi)) {
                                                                                        document.querySelector('#programfid').value = `${donsfi[key].code_progr}`;
                                                                                        document.querySelector('#catefid').value = `${donsfi[key].categorie}`;
                                                                                        document.querySelector('#deplignefid').value = `${donsfi[key].gareidentif}`;
                                                                                        document.querySelector('#lignfid').value = `${donsfi[key].ident_ligne}`;
                                                                                        document.querySelector('#nomitinfid').value = `${donsfi[key].nom_ligne}`;
                                                                                    }
                                                                                        let httpSiegefi;
                                                                                        httpSiegefi = new XMLHttpRequest();
                                                                                        const sigfi = document.querySelector('#psiegesfid')
                                                                                        .options[document.querySelector('#psiegesfid').options.selectedIndex].value;
                                                                                        const profi = document.querySelector('#programfid').value;
                                                                                        httpSiegefi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${profi}/${sigfi}`, true);
                                                                                        httpSiegefi.onload = () => 
                                                                                        {
                                                                                            const donsgfi = JSON.parse(httpSiegefi.responseText);
                                                                                            console.debug(`${typeof donsgfi} - ${donsgfi.attributes}`, console.memory);
                                                                                            if(donsgfi == '')
                                                                                            {
                                                                                                let httpSiegfi;
                                                                                                httpSiegfi = new XMLHttpRequest();
                    
                                                                                                httpSiegfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${profi}/${sigfi}`, true);
                                                                                                httpSiegfi.onload = () => 
                                                                                                {
                                                                                                    const donsg2fi = JSON.parse(httpSiegfi.responseText);
                                                                                                    document.querySelector('#messfid').style.display = 'none';
                                                                                                    if (Object.entries(donsg2fi).length >= 1)
                                                                                                        {
                                                                                                            for (let key in Object.entries(donsg2fi)) {
                                                                                                                document.querySelector('#idtampofid').value = `${donsg2fi[key].idtamp}`;                    
                                                                                                                document.querySelector('#siegselectfid').value = `${donsg2fi[key].numsieg}`;
                                                                                                            }
                                                                                                        }
                                                                                                };
                                                                                                httpSiegfi.setRequestHeader('Content-Type', 'application/json');
                                                                                                httpSiegfi.send();
                                                                                            }
                                                                                            else 
                                                                                            {
                                                                                                document.querySelector('#psiegesfid').value = ''; 
                                                                                                if (Object.entries(donsgfi).length >= 1)
                                                                                                {
                                                                                                    for (let key in Object.entries(donsgfi)) 
                                                                                                    {
                                                                                                        document.querySelector('#idtampofid').value = `${donsgfi[key].idtamp}`;                    
                                                                                                        document.querySelector('#siegselectfid').value = `${donsgfi[key].numsieg}`;
                                                                                                    }
        
                                                                                                }
                                                                                                document.querySelector('#messfid').style.display = 'block';
                                                                                                document.querySelector('#erreurMessfid').innerHTML = `Siege déjà utilisé.`;                   
                                                                                            }
                                                                                        };
                                                                                        httpSiegefi.setRequestHeader('Content-Type', 'application/json');
                                                                                        httpSiegefi.send();
                    
                                                                                   
                                                                                }
                                                                            };
                                                                            httpProgfi.setRequestHeader('Content-Type', 'application/json');
                                                                            httpProgfi.send();
        
                                                                            
                                                                        
                                                                        };
        
                                                                        
                                                                    }
                                                            }else{
                                                                let opt = document.createElement('option');
                                                                opt.value = '';                                                             
                                                            }
                                                            
                                                            
                                                        } 
                                                        else 
                                                        {       
                                                            if (Object.entries(donfi).length >= 1) {
                                                                for (let key in Object.entries(donfi)) {
                                                                    document.querySelector('#programfid').value = `${donfi[key].code_progr}`;
                                                                    document.querySelector('#dateprfid').value = `${donfi[key].date_progr}`;
                                                                    document.querySelector('#deplignefid').value = `${donfi[key].gareidentif}`;
                                                                    document.querySelector('#inter1fid').value = `${donfi[key].intervalle1}`;
                                                                    document.querySelector('#inter2fid').value = `${donfi[key].intervalle2}`;
                                                                    document.querySelector('#lignfid').value = `${donfi[key].ident_ligne}`;
                                                                    document.querySelector('#nomitinfid').value = `${donfi[key].nom_ligne}`;
                                                                    document.querySelector('#herfid').value = `${donfi[key].heure}`;
                                                                    document.querySelector('#catefid').value = `${donfi[key].categori}`;

                                                                }
                                                            } 
                                                            
                                                            /*const httpPrixfi = new XMLHttpRequest();
                                                            httpPrixfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifpriprg/${selfi}`, true);
                                                            httpPrixfi.onload = () => 
                                                            {
                                                                const donprixfi = JSON.parse(httpPrixfi.responseText);
                                                                console.debug(`${typeof donprixfi}-${donprixfi.attributes}`, console.memory);
                                                                if (Object.entries(donprixfi).length >= 1) {
                                                                    for (let key in Object.entries(donprix)) 
                                                                    {
                                                                        document.querySelector('#prix_axefid').value = `${donprixfi[key].prix}`;
            
                                                                    }
                                                                }
                                                            };
                                                            httpPrixfi.setRequestHeader('Content-Type', 'application/json');
                                                            httpPrixfi.send();*/
                                                            
                                                            const httpRequettefi = new XMLHttpRequest();
                                                            const cdprogfi = document.querySelector('#programfid').value;
                                                            const dbfi = document.querySelector('#inter1fid').value;
                                                            const fnfi = document.querySelector('#inter2fid').value;
                                                            const lgfi = document.querySelector('#nomitinfid').value;
                                                            const timfi = document.querySelector('#herfid').value;
                                                                
                                                                httpRequettefi.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${cdprogfi}/${dpt_datefi}/${lgfi}/${timfi}/${dbfi}/${fnfi}`, true);
                                                            httpRequettefi.onload = () => {
                                                                const dattafi = JSON.parse(httpRequettefi.responseText);
                                                                console.debug(`${typeof dattafi} - ${dattafi.attributes}`, console.memory);
                                                                if (Object.entries(dattafi).length >= 1) {
                                                                    for (let key in Object.entries(dattafi)) {
                                                                        
                                                                        let opt = document.createElement('option');
                                                                        opt.value = `${dattafi[key].siege_num}`;
                                                                        opt.innerHTML = `${dattafi[key].siege_num}`;
                                                                        document.querySelector('#psiegesfid').add(opt);
                                                                        
                                                                    }
                                                                    
                                                                } else {
                                                                    document.querySelector('#psiegesfid').options.length = 1;
                                                                }
                                                            };
                                                            httpRequettefi.setRequestHeader('Content-Type', 'application/json');
                                                            httpRequettefi.send();
                                                        }  
                                                        
                                                    };
                                                    httpRequestfi.setRequestHeader('Content-Type', 'application/json');
                                                    httpRequestfi.send();
                                                     
                                                };
                                                
                                        
                                            }
                                };
                                httpRequetesfi.setRequestHeader('Content-Type', 'application/json');
                                httpRequetesfi.send();
                        }
                        else
                        {
                            document.querySelector('#date_depheurefid').style.color = "#FF0000";
                            document.querySelector('#date_depheurefid').style.border = "2px solid #FF0000";
                            document.querySelector('#smsdtfid').style.display = 'block';
                            document.querySelector('#erreurSmsdtfid').innerHTML = `Date non valide.`;
                        }
                    

                };
                
            }
            let progsiegesfi = document.querySelector('#psiegesfid');
            if (progsiegesfi !== null) {
                progsiegesfi.onchange = () => 
                {
                    let httpSiegesfi;
                    httpSiegesfi = new XMLHttpRequest();
                    const sigsfi = document.querySelector('#psiegesfid')
                    .options[document.querySelector('#psiegesfid').options.selectedIndex].value;
                    const prosfi = document.querySelector('#programfid').value;

                    httpSiegesfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${prosfi}/${sigsfi}`, true);
                    httpSiegesfi.onload = () => 
                    {
                        const donsgefi = JSON.parse(httpSiegesfi.responseText);
                        console.debug(`${typeof donsgefi} - ${donsgefi.attributes}`, console.memory);
                        if(donsgefi == '')
                        {
                            let httpSiegsfi;
                            httpSiegsfi = new XMLHttpRequest();

                            httpSiegsfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${prosfi}/${sigsfi}`, true);
                            httpSiegsfi.onload = () => 
                            {
                                const dongfi = JSON.parse(httpSiegsfi.responseText);
                                document.querySelector('#messfid').style.display = 'none';
                                if (Object.entries(dongfi).length >= 1)
                                    {
                                        for (let key in Object.entries(dongfi)) {
                                            document.querySelector('#idtampofid').value = `${dongfi[key].idtamp}`;                    
                                            document.querySelector('#siegselectfid').value = `${dongfi[key].numsieg}`;
                                        }
                                    }
                            };
                            httpSiegsfi.setRequestHeader('Content-Type', 'application/json');
                            httpSiegsfi.send();
                        }
                        else {
                            document.querySelector('#psiegesfid').value = '';     
                            if (Object.entries(donsgefi).length >= 1)
                            {
                                for (let key in Object.entries(donsgefi)) {
                                    document.querySelector('#idtampofid').value = `${donsgefi[key].idtamp}`;                    
                                    document.querySelector('#siegselectfid').value = `${donsgefi[key].numsieg}`;
                                }

                            }
                            document.querySelector('#messfid').style.display = 'block';
                            document.querySelector('#erreurMessfid').innerHTML = `Siege déjà utilisé.`;                                                                   }
                    };
                    httpSiegesfi.setRequestHeader('Content-Type', 'application/json');
                    httpSiegesfi.send();

                
                };
            }
           
            let infdocfi = document.querySelector('#cltypefid');
        if (infdocfi !== null)
            infdocfi.onchange = () => 
            {
                let httpDocsfi;
                if (window.XMLHttpRequest) {
                    httpDocsfi = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpDocsfi = new ActiveXObject("Microsoft.XMLHTTP");
                }
                var documfi = document.querySelector('#cltypefid').value;
                
                if (documfi == 'Adulte') {
                    document.querySelector('#motiffid').style.display = 'none';
                    document.querySelector('#motifrefusfid').style.display = 'none';
                    document.querySelector('#docfid').style.display = 'none';
                    document.querySelector('#docdelivrefid').style.display = 'none';
                    document.querySelector('#datedocdelfid').style.display = 'none';
                    document.querySelector('#num_docfid').style.display = 'none';
                    document.querySelector('#rclientfid').style.display = 'block';
                    document.querySelector('#prnclientfid').style.display = 'block';
                    document.querySelector('#cnibfid').style.display = 'block';
                    document.querySelector('#date_cnibfid').style.display = 'block';
                    document.querySelector('#lieudelivrefid').style.display = 'block';
                    console.debug(`${documfid}`, console.memory);

                } 
                    if (documfi == 'Etudiant') {
                        document.querySelector('#docfid').style.display = 'block';
                        document.querySelector('#num_docfid').style.display = 'block';
                        document.querySelector('#docdelivrefid').style.display = 'block';
                        document.querySelector('#datedocdelfid').style.display = 'block';
                        document.querySelector('#rclientfid').style.display = 'block';
                        document.querySelector('#prnclientfid').style.display = 'block';
                        document.querySelector('#cnibfid').style.display = 'none';
                        document.querySelector('#date_cnibfid').style.display = 'none';
                        document.querySelector('#lieudelivrefid').style.display = 'none';
                        console.debug(`${documfi}`, console.memory);

                    } 
                    if (documfi == 'Elève') {
                        document.querySelector('#docfid').style.display = 'block';
                        document.querySelector('#num_docfid').style.display = 'block';
                        document.querySelector('#docdelivrefid').style.display = 'block';
                        document.querySelector('#datedocdelfid').style.display = 'block';
                        document.querySelector('#rclientfid').style.display = 'block';
                        document.querySelector('#prnclientfid').style.display = 'block';
                        document.querySelector('#cnibfid').style.display = 'none';
                        document.querySelector('#date_cnibfid').style.display = 'none';
                        document.querySelector('#lieudelivrefid').style.display = 'none';
                        console.debug(`${documfi}`, console.memory);

                    } 
                    if (documfi == 'Enfant') {
                        document.querySelector('#docfidfid').style.display = 'block';
                        document.querySelector('#num_docfid').style.display = 'block';
                        document.querySelector('#docdelivrefid').style.display = 'block';
                        document.querySelector('#datedocdelfid').style.display = 'block';
                        document.querySelector('#rclientfid').style.display = 'block';
                        document.querySelector('#prnclientfid').style.display = 'block';
                        document.querySelector('#cnibfid').style.display = 'none';
                        document.querySelector('#date_cnibfid').style.display = 'none';
                        document.querySelector('#lieudelivrefid').style.display = 'none';
                        console.debug(`${documfi}`, console.memory);

                    } 
                    if (documfi == 'Autres') {
                        document.querySelector('#motiffid').style.display = 'block';
                        document.querySelector('#motifrefusfid').style.display = 'block';
                        document.querySelector('#rclientfid').style.display = 'block';
                        document.querySelector('#prnclientfid').style.display = 'block';
                        document.querySelector('#cnibfid').style.display = 'none';
                        document.querySelector('#date_cnibfid').style.display = 'none';
                        document.querySelector('#lieudelivrefid').style.display = 'none';
                        document.querySelector('#docfid').style.display = 'none';
                        document.querySelector('#num_docfid').style.display = 'none';
                        document.querySelector('#docdelivrefid').style.display = 'none';
                        document.querySelector('#datedocdelfid').style.display = 'none';
                        console.debug(`${documfi}`, console.memory);

                    } 
                    
            };

            
        //recherche d'information du client depart principal
        let inffi = document.querySelector('#rnclient_contactfid');
        if (inffi !== null && inffi.dataset.guarded !== '1') {
            inffi.dataset.guarded = '1';
            inffi.addEventListener('keyup', () => {
                const rawPhone = inffi.value.trim();
                const digits = AppRequestGuard.phoneDigits(rawPhone);
                if (digits.length < 7) {
                    return;
                }
                AppRequestGuard.debounce('verifinfosfi', () => {
                    AppRequestGuard.getJson(
                        window.location.origin + `${APP_ROOT}/programmes/verifinfos/${encodeURIComponent(rawPhone)}`,
                        'verifinfosfi',
                        (httpInfosfi) => {
                            let infosfi = null;
                            try {
                                infosfi = JSON.parse(httpInfosfi.responseText);
                            } catch (err) {
                                return;
                            }
                            if (infosfi == null || Object.keys(infosfi).length < 1) {
                                document.querySelector('#pascompagniefid').value = '';
                                return;
                            }
                            if (AppRequestGuard.phonesMatch(infosfi.contact_client, rawPhone)) {
                                document.querySelector('#rclientfid').value = `${infosfi.nom_client || ''}`;
                                document.querySelector('#prnclientfid').value = `${infosfi.prenom_client || ''}`;
                                document.querySelector('#cnibfid').value = `${infosfi.num_CNIB || ''}`;
                                document.querySelector('#date_cnibfid').value = `${infosfi.date_delivre || ''}`;
                                document.querySelector('#lieudelivrefid').value = `${infosfi.lieu_delivre || ''}`;
                                document.querySelector('#pascompagniefid').value = `${infosfi.id_client || ''}`;
                                document.querySelector('#rclientcpfid').value = `${infosfi.nom_client || ''}`;
                                document.querySelector('#prnclientcpfid').value = `${infosfi.prenom_client || ''}`;
                                document.querySelector('#cnibcpfid').value = `${infosfi.num_CNIB || ''}`;
                                document.querySelector('#date_cnibcpfid').value = `${infosfi.date_delivre || ''}`;
                                document.querySelector('#lieudelivrecpfid').value = `${infosfi.lieu_delivre || ''}`;
                            } else {
                                document.querySelector('#pascompagniefid').value = '';
                            }
                        }
                    );
                }, 400);
            });
        }
            
            let butonclicfi = document.querySelector('#idresetfid');
            if (butonclicfi !== null) {
                butonclicfi.onclick = () => 
                {
                    let httpSiegeselectfi;
                    httpSiegeselectfi = new XMLHttpRequest();
                    const siegselectfi = document.querySelector('#siegselectfid').value;
                    const idtapfi = document.querySelector('#idtampofid').value;
                    httpSiegeselectfi.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtapfi}/${siegselectfi}`, true);
                    httpSiegeselectfi.onload = () => 
                    {
                        const donselectfi= JSON.parse(httpSiegeselectfi.responseText);
                        console.debug(`${typeof donselectfi} - ${donselectfi.attributes}`, console.memory);
                        document.querySelector('#messfid').style.display = 'none';
                        
                    };
                    httpSiegeselectfi.setRequestHeader('Content-Type', 'application/json');
                    httpSiegeselectfi.send();

                
                };
            }
                
                e.onclick = function () {   
                    let taFormfi = document.querySelector('#tafiForm');
                    
                    taFormfi.setAttribute('action', `${APP_ROOT}/Programmes/addpassagerfi/${e.dataset.cle_compagnie}`);
                    AppRequestGuard.ensureNonce('#tafiForm', 'sale_nonce');
                    AppRequestGuard.guardForm('#tafiForm');
                }

                var tafiFormEl = document.querySelector('#tafiForm');
                if (tafiFormEl && !tafiFormEl.dataset.salePrepared) {
                    tafiFormEl.dataset.salePrepared = '1';
                    tafiFormEl.addEventListener('submit', function () {
                        AppRequestGuard.ensureNonce('#tafiForm', 'sale_nonce');
                        AppRequestGuard.syncClientMirror([
                            ['#rclientfi', '#rclientcpfi'],
                            ['#prnclientfi', '#prnclientcpfi'],
                            ['#cnibfi', '#cnibcpfi'],
                            ['#date_cnibfi', '#date_cnibcpfi'],
                            ['#lieudelivrefi', '#lieudelivrecpfi']
                        ]);
                    });
                }

                AppRequestGuard.guardForm('#tafiForm');
                AppRequestGuard.ensureNonce('#tafiForm', 'sale_nonce');
                
    })

});
;
/* --- addreprogramme.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addreprogramme').forEach(function (e) {
        document.querySelector('h3#rTitle').innerHTML = `REPROGRAMMATION`;

        let infos = document.querySelector('#reprogrammer_infos');
        if (infos !== null)
            infos.onclick = () => {
                //verification code de reprogrammation
                let httpRequestRep;
                
                if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                    httpRequestRep = new XMLHttpRequest();
                } else if (window.ActiveXObject) { // IE 6 and older
                    httpRequestRep = new ActiveXObject("Microsoft.XMLHTTP");
                }
                
                
                var cocl = document.querySelector("#codeclientp").value;
                httpRequestRep.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/verifcodecl/${cocl}`, true);
                httpRequestRep.onload = () => {
                    const donnees = JSON.parse(httpRequestRep.responseText);
                    if (donnees == null) {
                        
                        document.querySelector('#smsp').style.display = 'block';
                        document.querySelector('#erreurSmsp').innerHTML = `Cet ticket ne peut pas être reprogrammé ici.`;
                        document.querySelector('#nomclp').innerHTML = ``;
                        document.querySelector('#prenomclp').innerHTML = ``;
                        document.querySelector('#contactclp').innerHTML = ``;
                        document.querySelector('#refclp').innerHTML = ``;
                        document.querySelector('#directionclp').innerHTML = ``;
                        document.querySelector('#codeclp').innerHTML = ``;
                        document.querySelector('#heureclp').innerHTML = ``;
                        document.querySelector('#heuredepartp').style.display = 'none';
                        document.querySelector('#numsiegep').style.display = 'none';
                        document.querySelector('#heuredepartp').options.length = 1;

                    } else 
                    {
                               
                            if (Object.entries(donnees).length >= 1){
                                    document.querySelector('#smsp').style.display = 'none';
                                    document.querySelector('#heuredepartp').style.display = 'block';
                                    document.querySelector('#numsiegep').style.display = 'block';       
                                    document.querySelector('#nomclp').innerHTML = `NOM: ${donnees.nom_client}`;
                                    document.querySelector('#prenomclp').innerHTML = `PRENOM: ${donnees.prenom_client}`;
                                    document.querySelector('#contactclp').innerHTML = `CONTACT: ${donnees.contact_client}`;
                                    document.querySelector('#refclp').innerHTML = `REFERENCE CNIB: ${donnees.num_CNIB}`;
                                    document.querySelector('#directionclp').innerHTML = `AXE: ${donnees.nom_ligne}`;
                                    document.querySelector('#codeclp').innerHTML = `CODE TICKET: ${donnees.code_passager}`;
                                    document.querySelector('#heureclp').innerHTML = `HEURE: ${donnees.heure} SIEGE :${donnees.num_siege_categorie}`;
                                    document.querySelector('#passerp').value = `${donnees.code_passager}`;
                                    document.querySelector('#idclpasserid').value = `${donnees.ligne_id}`;
                                    document.querySelector('#client_idp').value = `${donnees.id_client_pass}`;
                                    document.querySelector('#pasnomp').value = `${donnees.nom_client}`;
                                    document.querySelector('#pasprenomp').value = `${donnees.prenom_client}`;
                                    document.querySelector('#pascontactp').value = `${donnees.contact_client}`;
                                    document.querySelector('#pascnibp').value = `${donnees.num_CNIB}`;
                                    document.querySelector('#pasdatep').value = `${donnees.date_delivre}`;
                                    document.querySelector('#nsiegep').value = `${donnees.num_siege_categorie}`;
                                    document.querySelector('#delivrelie').value = `${donnees.lieu_delivre}`;
                                    document.querySelector('#depold').value = `${donnees.code_pro}`;
                                    document.querySelector('#codeid').value = `${donnees.code_passager}`;
                                    document.querySelector('#codetickets').value = `${donnees.tamponcod}`;
                                    document.querySelector('#codenonp').value = `${donnees.code_non_pass}`;
                                    document.querySelector('#statconf').value = `${donnees.statut_confirme}`;
                                    document.querySelector('#statrep').value = `${donnees.statut_reprog}`;
                                    document.querySelector('#programrep').value = `${donnees.code_progr}`;
                                    document.querySelector('#depgid').value = `${donnees.gaexp_lg}`;
                                    document.querySelector('#dateventerep').value = `${donnees.datep_create}`;


                            } else {
                                document.querySelector('#heuredepartp').style.display = 'none';
                                document.querySelector('#numsiegep').style.display = 'none';
                            }
                            var datdepartrep = document.querySelector('#dateventerep').value;
                            var daterepactu = document.querySelector('#actueldaterep').value;
                            var daterep1  = new Date(datdepartrep);
                            var daterep2 = new Date(daterepactu);
                            // différence des heures
                            var time_diff = daterep2.getTime() - daterep1.getTime();
                                // différence de jours
                            const days_Diff = time_diff / (1000 * 3600 * 24);

                            if(days_Diff < 28 || days_Diff < 29 || days_Diff < 30 || days_Diff < 31)    
                            {
                                const hdpaxep = `${donnees.ligne_id}`;
                                const hcl = `${donnees.code_progr}`;
                                const ligneheure =`${donnees.heure_identif}`;
                                let httpRequestews;
                                if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                                    httpRequestews = new XMLHttpRequest();
                                } else if (window.ActiveXObject) { // IE 6 and older
                                    httpRequestews = new ActiveXObject("Microsoft.XMLHTTP");
                                }
                                httpRequestews.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/hdepartprepro/${hdpaxep}/${hcl}/${ligneheure}`, true);
                                
                                httpRequestews.onload = () => {
                                    const data2 = JSON.parse(httpRequestews.responseText);
                                    console.debug(`${typeof data2} - ${data2.attributes}`, console.memory);
                                    if (Object.entries(data2).length >= 1) {
                                        for (let key in Object.entries(data2)) {
                                            let opt = document.createElement('option');
                                            opt.value = `${data2[key].code_progr}/${data2[key].id_ligneheure}/${data2[key].typetarif}`;
                                            opt.innerHTML = `${data2[key].heure}/${data2[key].date_progr}`;
                                            document.querySelector('#heuredepartp').add(opt);
                                            
                                        }
                                    } else {
                                        document.querySelector('#heuredepartp').options.length = 1;
                                    }
                                };
                                httpRequestews.setRequestHeader('Content-Type', 'application/json');
                                httpRequestews.send();
                            }
                            else
                            {
                                document.querySelector('#nomclp').innerHTML = ``;
                                document.querySelector('#prenomclp').innerHTML = ``;
                                document.querySelector('#contactclp').innerHTML = ``;
                                document.querySelector('#refclp').innerHTML = ``;
                                document.querySelector('#directionclp').innerHTML = ``;
                                document.querySelector('#codeclp').innerHTML = ``;
                                document.querySelector('#heureclp').innerHTML = ``;
                                document.querySelector('#heuredepartp').style.display = 'none';
                                document.querySelector('#numsiegep').style.display = 'none';
                                document.querySelector('#billetrep').style.display = 'block';
                                document.querySelector('#billetSmsrep').innerHTML = `Billet non valable, la durée de validité est dépassée.`;
                            }
        
                    }
                };
                httpRequestRep.setRequestHeader('Content-Type', 'application/json');
                httpRequestRep.send();
            };
        
            let heurdep = document.querySelector('#heuredepartp');
            if (heurdep !== null) {
                heurdep.onchange = () => {
                    document.querySelector('#numsiegep').options.length = 1;
                    
                const httpRequerst = new XMLHttpRequest();
                const selectorts = document.querySelector('#heuredepartp').
                    options[document.querySelector('#heuredepartp').options.selectedIndex].value;
					
					var post_lh = selectorts.split('/');
					var selh = post_lh[0];
					var lignehsel = post_lh[1];
					
                    var post_lh1 = lignehsel.split('/');
                    var selh1 = post_lh1[0];
                    var lignehsel1 = post_lh1[1];
                    var vr = selh1;
                httpRequerst.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/siegdispo/${selh}`, true);
                httpRequerst.onload = () => {
                        const data = JSON.parse(httpRequerst.responseText);
                        console.debug(`${typeof data} - ${data.attributes}`, console.memory);
                        if (Object.entries(data).length > 0) {
                            for (let key in Object.entries(data)) {
    
                                document.querySelector('#placevendu').value = `${data[key].intervalle1}`;
                                document.querySelector('#dplacevendu').value = `${data[key].intervalle2}`;
                                document.querySelector('#replign').value = `${data[key].nom_ligne}`;
                                document.querySelector('#repher').value = `${data[key].heure}`;
                                document.querySelector('#datereprogramme').value = `${data[key].date_progr}`;
                                document.querySelector('#catreprogramme').value = `${data[key].categori}`;
                                }
                            } 
                            
                            
                            const httpRequetterep = new XMLHttpRequest();
                                const pld = document.querySelector('#placevendu').value;
                                const plf = document.querySelector('#dplacevendu').value;
                                const lgr = document.querySelector('#replign').value;
                                const reph = document.querySelector('#repher').value;
                                const dtrep = document.querySelector('#datereprogramme').value;
                            httpRequetterep.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${selh}/${dtrep}/${lgr}/${reph}/${pld}/${plf}`, true);
                            httpRequetterep.onload = () => {
                            const dattas = JSON.parse(httpRequetterep.responseText);
                            console.debug(`${typeof dattas} - ${dattas.attributes}`, console.memory);
                            if (Object.entries(dattas).length >= 1) {
                               
                                for (let key in Object.entries(dattas)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${dattas[key].siege_num}`;
                                    opt.innerHTML = `${dattas[key].siege_num}`;
                                    document.querySelector('#numsiegep').add(opt);
                                    console.debug(`${dattas[key].siege_num}`, console.memory)
                                }
                            } else {
                                document.querySelector('#numsiegep').options.length = 1;
                                
                            }
                    };
                    httpRequetterep.setRequestHeader('Content-Type', 'application/json');
                    httpRequetterep.send();
                    };
                    httpRequerst.setRequestHeader('Content-Type', 'application/json');
                    httpRequerst.send();
                };
           
            }

            let numsiege = document.querySelector('#numsiegep');
            if (numsiege !== null)
            numsiege.onchange = () => {
                    
                    let Requestsiegevendu;
                    
                    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                        Requestsiegevendu = new XMLHttpRequest();
                    } else if (window.ActiveXObject) { // IE 6 and older
                        Requestsiegevendu = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                    const dp_progrep = document.querySelector('#programrep').value;
                    const dp_siegerep = document.querySelector('#numsiegep').options[document.querySelector('#numsiegep').options.selectedIndex].value;
                    Requestsiegevendu.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${dp_progrep}/${dp_siegerep}`, true);
                    Requestsiegevendu.onload = () => 
                    {
                        
                            const donsieg = JSON.parse(Requestsiegevendu.responseText);
                            if (donsieg == '')
                                    {
                                        let httpSiegsrep;
                                        httpSiegsrep = new XMLHttpRequest();

                                        httpSiegsrep.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${dp_progrep}/${dp_siegerep}`, true);
                                        httpSiegsrep.onload = () => 
                                        {
                                            const dongrep= JSON.parse(httpSiegsrep.responseText);
                                            document.querySelector('#erreursieg').style.display = 'none';
                                            if (Object.entries(dongrep).length >= 1)
                                            {
                                                for (let key in Object.entries(dongrep)) {
                                                    document.querySelector('#idtamporep').value = `${dongrep[key].idtamp}`;                    
                                                    document.querySelector('#siegselectrep').value = `${dongrep[key].numsieg}`;
                                                }

                                            }
                                        };
                                        httpSiegsrep.setRequestHeader('Content-Type', 'application/json');
                                        httpSiegsrep.send();
                                    }
                                    else {
                                        document.querySelector('#numsiegep').value = '';     
                                        if (Object.entries(donsieg).length >= 1)
                                        {
                                            for (let key in Object.entries(donsieg)) {
                                                document.querySelector('#idtamporep').value = `${donsieg[key].idtamp}`;                    
                                                document.querySelector('#siegselectrep').value = `${donsieg[key].numsieg}`;
                                            }

                                        }
                                        document.querySelector('#erreursieg').style.display = 'block';
                                        document.querySelector('#erreurSiege').innerHTML = `Siege déjà utilisé.`; 
                                    }
                    };
                    Requestsiegevendu.setRequestHeader('content-Type', 'text/json');
                    Requestsiegevendu.send();
                };

                butonclicrep = document.querySelector('#rese');
            if (butonclicrep !== null) {
                butonclicrep.onclick = () => 
                {
                    let httpSiegeselectrep;
                    httpSiegeselectrep = new XMLHttpRequest();
                    const siegselectrep= document.querySelector('#siegselectrep').value;
                    const idtaprep = document.querySelector('#idtamporep').value;
                    httpSiegeselectrep.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtaprep}/${siegselectrep}`, true);
                    httpSiegeselectrep.onload = () => 
                    {
                        const donselectrep= JSON.parse(httpSiegeselectrep.responseText);
                        console.debug(`${typeof donselectrep} - ${donselectrep.attributes}`, console.memory);
                        document.querySelector('#erreursieg').style.display = 'none';
                        
                    };
                    httpSiegeselectrep.setRequestHeader('Content-Type', 'application/json');
                    httpSiegeselectrep.send();

                
                };
            }

            
        e.onclick = function () {
            let rForm = document.querySelector('#rForm');
            rForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/update/${e.dataset.cle_compagnie}`);
        }
    })
});
;
/* --- addreprogrammetransit.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addreprogrammetransit').forEach(function (e) {
        document.querySelector('h3#rTitletransit').innerHTML = `REPROGRAMMATION`;

    
        let infos = document.querySelector('#reprogrammer_infostransit');
        if (infos !== null)
            infos.onclick = () => {
                //verification code de reprogrammation
                let httpRequestRep;
                
                if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                    httpRequestRep = new XMLHttpRequest();
                } else if (window.ActiveXObject) { // IE 6 and older
                    httpRequestRep = new ActiveXObject("Microsoft.XMLHTTP");
                }
                
                var cocl = document.querySelector("#codeclientptransit").value;
                
                httpRequestRep.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/verifcodetransit/${cocl}`, true);
                httpRequestRep.onload = () => {
                    const donnees = JSON.parse(httpRequestRep.responseText);
                    if (donnees == null) {
                        
                        document.querySelector('#smsptransit').style.display = 'block';
                        document.querySelector('#erreurSmsptransit').innerHTML = `Cet ticket ne peut pas être reprogrammé ici.`;
                        document.querySelector('#nomclptransit').innerHTML = ``;
                        document.querySelector('#prenomclptransit').innerHTML = ``;
                        document.querySelector('#contactclptransit').innerHTML = ``;
                        document.querySelector('#refclptransit').innerHTML = ``;
                        document.querySelector('#directionclptransit').innerHTML = ``;
                        document.querySelector('#codeclptransit').innerHTML = ``;
                        document.querySelector('#heureclptransit').innerHTML = ``;
                        document.querySelector('#heuredepartptransit').style.display = 'none';
                        document.querySelector('#numsiegeptransit').style.display = 'none';
                        document.querySelector('#heuredepartptransit').options.length = 1;
                    } else 
                    {
                           
                        if (Object.entries(donnees).length >= 1){
                                document.querySelector('#smsptransit').style.display = 'none';
                                document.querySelector('#heuredepartptransit').style.display = 'block';
                                document.querySelector('#numsiegeptransit').style.display = 'block';       
                                document.querySelector('#nomclptransit').innerHTML = `NOM: ${donnees.nom_client}`;
                                document.querySelector('#prenomclptransit').innerHTML = `PRENOM: ${donnees.prenom_client}`;
                                document.querySelector('#contactclptransit').innerHTML = `CONTACT: ${donnees.contact_client}`;
                                document.querySelector('#refclptransit').innerHTML = `REFERENCE CNIB: ${donnees.num_CNIB}`;
                                document.querySelector('#directionclptransit').innerHTML = `AXE: ${donnees.nom_ligne}`;
                                document.querySelector('#codeclptransit').innerHTML = `CODE TICKET: ${donnees.code_passager} DATE : ${donnees.date_progr}`;
                                document.querySelector('#heureclptransit').innerHTML = `HEURE: ${donnees.heure} SIEGE :${donnees.num_siege_categorie}`;
                                document.querySelector('#passerptransit').value = `${donnees.code_passager}`;
                                document.querySelector('#idclpasseridtransit').value = `${donnees.ligne_id}`;
                                document.querySelector('#client_idptransit').value = `${donnees.id_client_pass}`;
                                document.querySelector('#pasnomptransit').value = `${donnees.nom_client}`;
                                document.querySelector('#pasprenomptransit').value = `${donnees.prenom_client}`;
                                document.querySelector('#pascontactptransit').value = `${donnees.contact_client}`;
                                document.querySelector('#pascnibptransit').value = `${donnees.num_CNIB}`;
                                document.querySelector('#pasdateptransit').value = `${donnees.date_delivre}`;
                                document.querySelector('#nsiegeptransit').value = `${donnees.num_siege_categorie}`;
                                document.querySelector('#delivrelietransit').value = `${donnees.lieu_delivre}`;
                                document.querySelector('#depoldtransit').value = `${donnees.code_pro}`;
                                document.querySelector('#id_compagatr').value = `${donnees.id_compaga}`;
                                document.querySelector('#codeidtransit').value = `${donnees.code_passager}`;
                                document.querySelector('#codeticketstransit').value = `${donnees.tamponcod}`;
                                document.querySelector('#lgcodeticketstransit').value = `${donnees.tamponcodtr}`;
                                document.querySelector('#codenonptransit').value = `${donnees.code_non_pass}`;
                                document.querySelector('#statconftransit').value = `${donnees.statut_confirme}`;
                                document.querySelector('#statreptransit').value = `${donnees.statut_reprog}`;
                                document.querySelector('#programreptransit').value = `${donnees.code_progr}`;
                                document.querySelector('#depgidtransit').value = `${donnees.gaexp_lg}`;
                                document.querySelector('#dateventereptransit').value = `${donnees.datep_create}`;
                                document.querySelector('#gareidentiftransit').value = `${donnees.gareidentif}`;
                                document.querySelector('#departclientidgare').value = `${donnees.departclient_idgare}`;

                        } else {
                            document.querySelector('#heuredepartptransit').style.display = 'none';
                            document.querySelector('#numsiegeptransit').style.display = 'none';
                        }
                        var datdepartrep = document.querySelector('#dateventereptransit').value;
                        var daterepactu = document.querySelector('#actueldatereptransit').value;
                        var daterep1 = new Date(datdepartrep);
                        var daterep2 = new Date(daterepactu);
                        // différence des heures
                        var time_diff = daterep2.getTime() - daterep1.getTime();
                            // différence de jours
                        const days_Diff = time_diff / (1000 * 3600 * 24);

                        if(days_Diff < 28 || days_Diff < 29 || days_Diff < 30 || days_Diff < 31)    
                        {

                            //const dates = document.querySelector('#datereptransit').value;
                            const hdpaxep = `${donnees.ligne_id}`;
                            const hcl = `${donnees.code_progr}`;
                            const compag = `${donnees.id_compaga}`;

                            const prx = `${donnees.prixvente}`;
                            const ligneheure =`${donnees.heure_identif}`;
                            
                            const compagn = document.querySelector('#id_compagatr').value;

                                if(compagn === '5001' || compagn === '5002'){
                                    let httpRequestews;
                                    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                                        httpRequestews = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) { // IE 6 and older
                                        httpRequestews = new ActiveXObject("Microsoft.XMLHTTP");
                                    }

                                    httpRequestews.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/hdepartpreprotrt/${hdpaxep}/${compag}/${prx}`, true);
       
                                    httpRequestews.onload = () => {
                                        const data2 = JSON.parse(httpRequestews.responseText);
                                
                                        if (Object.entries(data2).length >= 1) {
                                            for (let key in Object.entries(data2)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${data2[key].code_progr}/${data2[key].id_ligneheure}/${data2[key].typetarif}`;
                                                opt.innerHTML = `${data2[key].heure}/${data2[key].date_progr}`;
                                                document.querySelector('#heuredepartptransit').add(opt);
                                            }
                                        } else {
                                            document.querySelector('#heuredepartptransit').options.length = 1;
                                        }
                                    };
                                    httpRequestews.setRequestHeader('Content-Type', 'application/json');
                                    httpRequestews.send();
                                }else
                                {
                                    
                                    console.debug(`${ compag}-${compag.attributes}`, console.memory);
                                    let httpRequestewst;
                                    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                                        httpRequestewst = new XMLHttpRequest();
                                    } else if (window.ActiveXObject) { // IE 6 and older
                                        httpRequestewst = new ActiveXObject("Microsoft.XMLHTTP");
                                    }
                                    httpRequestewst.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/hdepartpreprotr/${hdpaxep}/${hcl}`, true);
                                    
                                    httpRequestewst.onload = () => {
                                        const datat2 = JSON.parse(httpRequestewst.responseText);
                                    
                                        if (Object.entries(datat2).length >= 1) {
                                            for (let key in Object.entries(datat2)) {
                                                let opt1 = document.createElement('option');
                                                opt1.value = `${datat2[key].code_progr}/${datat2[key].id_ligneheure}/${datat2[key].typetarif}`;
                                                opt1.innerHTML = `${datat2[key].heure}/${datat2[key].date_progr}`;
                                                document.querySelector('#heuredepartptransit').add(opt1);
                                            }
                                        } else {
                                            document.querySelector('#heuredepartptransit').options.length = 1;
                                        }
                                    };
                                    httpRequestewst.setRequestHeader('Content-Type', 'application/json');
                                    httpRequestewst.send();
                                }
                        }
                        else
                        {
                            document.querySelector('#nomclptransit').innerHTML = ``;
                            document.querySelector('#prenomclptransit').innerHTML = ``;
                            document.querySelector('#contactclptransit').innerHTML = ``;
                            document.querySelector('#refclptransit').innerHTML = ``;
                            document.querySelector('#directionclptransit').innerHTML = ``;
                            document.querySelector('#codeclptransit').innerHTML = ``;
                            document.querySelector('#heureclptransit').innerHTML = ``;
                            document.querySelector('#heuredepartptransit').style.display = 'none';
                            document.querySelector('#numsiegeptransit').style.display = 'none';
                            document.querySelector('#billetreptransit').style.display = 'block';
                            document.querySelector('#billetSmsreptransit').innerHTML = `Billet non valable, la durée de validité est dépassée.`;
                        }
                
                    }

                };
                httpRequestRep.setRequestHeader('Content-Type', 'application/json');
                httpRequestRep.send();
            };
        
            let heurdep = document.querySelector('#heuredepartptransit');
            if (heurdep !== null) {
                heurdep.onchange = () => {
                    document.querySelector('#numsiegeptransit').options.length = 1;
                    
                const httpRequerst = new XMLHttpRequest();
                const selectorts = document.querySelector('#heuredepartptransit').
                    options[document.querySelector('#heuredepartptransit').options.selectedIndex].value;
					
					var post_lh = selectorts.split('/');
					var selh = post_lh[0];
					var lignehsel = post_lh[1];
					
                    var post_lh1 = lignehsel.split('/');
                    var selh1 = post_lh1[0];
                    var lignehsel1 = post_lh1[1];
                    var vr = selh1;
                httpRequerst.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/siegdispo/${selh}`, true);
                httpRequerst.onload = () => {
                        const data = JSON.parse(httpRequerst.responseText);
                        console.debug(`${typeof data} - ${data.attributes}`, console.memory);
                        if (Object.entries(data).length > 0) {
                            for (let key in Object.entries(data)) {
                                document.querySelector('#placevendutransit').value = `${data[key].intervalle1}`;
                                document.querySelector('#dplacevendutransit').value = `${data[key].intervalle2}`;
                                document.querySelector('#repligntransit').value = `${data[key].nom_ligne}`;
                                document.querySelector('#rephertransit').value = `${data[key].heure}`;
                                document.querySelector('#datereprogrammetransit').value = `${data[key].date_progr}`;
                                document.querySelector('#catreprogrammetransit').value = `${data[key].categori}`;
                                document.querySelector('#idrepligntransit').value = `${data[key].ligne_id}`;
                                document.querySelector('#compgcftransit').value = `${data[key].id_compaga}`;
                            }
                        } 
                            
                            
                            const httpRequetterep = new XMLHttpRequest();
                                const pld = document.querySelector('#placevendutransit').value;
                                const plf = document.querySelector('#dplacevendutransit').value;
                                const lgr = document.querySelector('#repligntransit').value;
                                const reph = document.querySelector('#rephertransit').value;
                                const dtrep = document.querySelector('#datereprogrammetransit').value;
                            httpRequetterep.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${selh}/${dtrep}/${lgr}/${reph}/${pld}/${plf}`, true);
                            httpRequetterep.onload = () => {
                            const dattas = JSON.parse(httpRequetterep.responseText);
                            console.debug(`${typeof dattas} - ${dattas.attributes}`, console.memory);
                            if (Object.entries(dattas).length >= 1) {
                               
                                for (let key in Object.entries(dattas)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${dattas[key].siege_num}`;
                                    opt.innerHTML = `${dattas[key].siege_num}`;
                                    document.querySelector('#numsiegeptransit').add(opt);
                                    console.debug(`${dattas[key].siege_num}`, console.memory)
                                }
                            } else {
                                document.querySelector('#numsiegeptransit').options.length = 1;
                                
                            }
                    };
                    httpRequetterep.setRequestHeader('Content-Type', 'application/json');
                    httpRequetterep.send();
                    };
                    httpRequerst.setRequestHeader('Content-Type', 'application/json');
                    httpRequerst.send();
                };
           
            }

            let numsiege = document.querySelector('#numsiegeptransit');
            if (numsiege !== null)
            numsiege.onchange = () => {
                    
                    let Requestsiegevendu;
                    
                    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                        Requestsiegevendu = new XMLHttpRequest();
                    } else if (window.ActiveXObject) { // IE 6 and older
                        Requestsiegevendu = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                    const dp_progrep = document.querySelector('#programreptransit').value;
                    const dp_siegerep = document.querySelector('#numsiegeptransit').options[document.querySelector('#numsiegeptransit').options.selectedIndex].value;
                    Requestsiegevendu.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${dp_progrep}/${dp_siegerep}`, true);
                    Requestsiegevendu.onload = () => 
                    {
                        
                            const donsieg = JSON.parse(Requestsiegevendu.responseText);
                            if (donsieg == '')
                                    {
                                        let httpSiegsrep;
                                        httpSiegsrep = new XMLHttpRequest();

                                        httpSiegsrep.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${dp_progrep}/${dp_siegerep}`, true);
                                        httpSiegsrep.onload = () => 
                                        {
                                            const dongrep= JSON.parse(httpSiegsrep.responseText);
                                            document.querySelector('#erreursiegtransit').style.display = 'none';
                                            if (Object.entries(dongrep).length >= 1)
                                            {
                                                for (let key in Object.entries(dongrep)) {
                                                    document.querySelector('#idtamporeptransit').value = `${dongrep[key].idtamp}`;                    
                                                    document.querySelector('#siegselectreptransit').value = `${dongrep[key].numsieg}`;
                                                }

                                            }
                                        };
                                        httpSiegsrep.setRequestHeader('Content-Type', 'application/json');
                                        httpSiegsrep.send();
                                    }
                                    else {
                                        document.querySelector('#numsiegeptransit').value = '';     
                                        if (Object.entries(donsieg).length >= 1)
                                        {
                                            for (let key in Object.entries(donsieg)) {
                                                document.querySelector('#idtamporeptransit').value = `${donsieg[key].idtamp}`;                    
                                                document.querySelector('#siegselectreptransit').value = `${donsieg[key].numsieg}`;
                                            }

                                        }
                                        document.querySelector('#erreursiegtransit').style.display = 'block';
                                        document.querySelector('#erreurSiegetransit').innerHTML = `Siege déjà utilisé.`; 
                                    }
                    };
                    Requestsiegevendu.setRequestHeader('content-Type', 'text/json');
                    Requestsiegevendu.send();
                };

                butonclicrep = document.querySelector('#resetransit');
            if (butonclicrep !== null) {
                butonclicrep.onclick = () => 
                {
                    let httpSiegeselectrep;
                    httpSiegeselectrep = new XMLHttpRequest();
                    const siegselectrep= document.querySelector('#siegselectreptransit').value;
                    const idtaprep = document.querySelector('#idtamporeptransit').value;
                    httpSiegeselectrep.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtaprep}/${siegselectrep}`, true);
                    httpSiegeselectrep.onload = () => 
                    {
                        const donselectrep= JSON.parse(httpSiegeselectrep.responseText);
                        console.debug(`${typeof donselectrep} - ${donselectrep.attributes}`, console.memory);
                        document.querySelector('#erreursiegtransit').style.display = 'none';
                        
                    };
                    httpSiegeselectrep.setRequestHeader('Content-Type', 'application/json');
                    httpSiegeselectrep.send();

                
                };
            }

            
        e.onclick = function () {
            let rForm = document.querySelector('#rFormtransit');
            rForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/updatetransit/${e.dataset.cle_compagnie}`);
        }
    })
});
;
/* --- addreprogadmin.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addreprogadmin').forEach(function (e) {
        document.querySelector('h3#adminrTitle').innerHTML = `REPROGRAMMATION`;

        let admininfos = document.querySelector('#adminreprogrammer_infos');
        if (admininfos !== null)
            admininfos.onclick = () => {
                //verification code de reprogrammation
                let httpRequestRep;
                
                if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                    httpRequestRep = new XMLHttpRequest();
                } else if (window.ActiveXObject) { // IE 6 and older
                    httpRequestRep = new ActiveXObject("Microsoft.XMLHTTP");
                }
               
                
                    var admincocl = document.querySelector("#admincodeclientp").value;
                httpRequestRep.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/adminverifcodecl/${admincocl}`, true);
                httpRequestRep.onload = () => {
                    const donnees = JSON.parse(httpRequestRep.responseText);
                    if (donnees == null) {
                        
                        document.querySelector('#adminsmsp').style.display = 'block';
                        document.querySelector('#adminerreurSmsp').innerHTML = `Cet ticket ne peut pas être reprogrammé ici.`;
                        document.querySelector('#adminnomclp').innerHTML = ``;
                        document.querySelector('#adminprenomclp').innerHTML = ``;
                        document.querySelector('#admincontactclp').innerHTML = ``;
                        document.querySelector('#adminrefclp').innerHTML = ``;
                        document.querySelector('#admindirectionclp').innerHTML = ``;
                        document.querySelector('#admincodeclp').innerHTML = ``;
                        document.querySelector('#adminheureclp').innerHTML = ``;
                        document.querySelector('#adminheuredepartp').style.display = 'none';
                        document.querySelector('#adminnumsiegep').style.display = 'none';
                        document.querySelector('#adminheuredepartp').options.length = 1;
                        document.querySelector('#admincdpassager').value = ``;

                        
                    } else
                    {

                    
                        if (Object.entries(donnees).length >= 1){

                        
                            document.querySelector('#adminsmsp').style.display = 'none';
                            document.querySelector('#adminheuredepartp').style.display = 'block';
                            document.querySelector('#adminnumsiegep').style.display = 'block';       
                            document.querySelector('#adminnomclp').innerHTML = `NOM: ${donnees.nom_client}`;
                            document.querySelector('#adminprenomclp').innerHTML = `PRENOM: ${donnees.prenom_client}`;
                            document.querySelector('#admincontactclp').innerHTML = `CONTACT: ${donnees.contact_client}`;
                            document.querySelector('#adminrefclp').innerHTML = `REFERENCE CNIB: ${donnees.num_CNIB}`;
                            document.querySelector('#admindirectionclp').innerHTML = `AXE: ${donnees.nom_ligne}`;
                            document.querySelector('#admincodeclp').innerHTML = `CODE TICKET: ${donnees.code_passager}`;
                            document.querySelector('#adminheureclp').innerHTML = `HEURE: ${donnees.heure} SIEGE: ${donnees.num_siege_categorie}`;
                            document.querySelector('#adminpasserp').value = `${donnees.code_passager}`;
                            document.querySelector('#adminidclpasserid').value = `${donnees.ligne_id}`;
                            document.querySelector('#adminclient_idp').value = `${donnees.id_client_pass}`;
                            document.querySelector('#adminpasnomp').value = `${donnees.nom_client}`;
                            document.querySelector('#adminpasprenomp').value = `${donnees.prenom_client}`;
                            document.querySelector('#adminpascontactp').value = `${donnees.contact_client}`;
                            document.querySelector('#adminpascnibp').value = `${donnees.num_CNIB}`;
                            document.querySelector('#adminpasdatep').value = `${donnees.date_delivre}`;
                            document.querySelector('#adminnsiegep').value = `${donnees.num_siege_categorie}`;
                            document.querySelector('#admindelivrelie').value = `${donnees.lieu_delivre}`;
                            document.querySelector('#admindepold').value = `${donnees.code_pro}`;
                            document.querySelector('#admincodeid').value = `${donnees.code_passager}`;
                            document.querySelector('#admincodetickets').value = `${donnees.tamponcod}`;
                            document.querySelector('#admincodenonp').value = `${donnees.code_non_pass}`;
                            document.querySelector('#adminstatconf').value = `${donnees.statut_confirme}`;
                            document.querySelector('#adminstatrep').value = `${donnees.statut_reprog}`;
                            document.querySelector('#adminprogramrep').value = `${donnees.code_progr}`;
                            document.querySelector('#admindepgid').value = `${donnees.gaexp_lg}`;
                            document.querySelector('#dateventerep').value = `${donnees.datep_create}`;
                            document.querySelector('#admincdpassager').value = `${donnees.code_ticket}`;

                        } else {
                            document.querySelector('#adminheuredepartp').style.display = 'none';
                            document.querySelector('#adminnumsiegep').style.display = 'none';
                        }       
                            var addatdepartrep = document.querySelector('#dateventerep').value;
                            var addaterepactu = document.querySelector('#actueldaterep').value;
                            var addaterep1  = new Date(addatdepartrep);
                            var addaterep2 = new Date(addaterepactu);
                            // différence des heures
                            var time_diff = addaterep2.getTime() - addaterep1.getTime();
                                // différence de jours
                            const days_Diff = time_diff / (1000 * 3600 * 24);

                            if(days_Diff < 28 || days_Diff < 29 || days_Diff < 30 || days_Diff < 31)    
                            {
                                const hdpaxep = `${donnees.ligne_id}`;
                                const hcl = `${donnees.code_progr}`;
                                const ligneheure =`${donnees.heure_identif}`;
                                let httpRequestews;
                                if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                                    httpRequestews = new XMLHttpRequest();
                                } else if (window.ActiveXObject) { // IE 6 and older
                                    httpRequestews = new ActiveXObject("Microsoft.XMLHTTP");
                                }
                                httpRequestews.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/hdepartprepro/${hdpaxep}/${hcl}/${ligneheure}`, true);
                                
                                httpRequestews.onload = () => {
                                    const data2 = JSON.parse(httpRequestews.responseText);
                                    console.debug(`${typeof data2} - ${data2.attributes}`, console.memory);
                                    if (Object.entries(data2).length >= 1) {
                                        for (let key in Object.entries(data2)) {
                                            let opt = document.createElement('option');
                                            opt.value = `${data2[key].code_progr}/${data2[key].id_ligneheure}/${data2[key].typetarif}`;
                                            opt.innerHTML = `${data2[key].heure}/${data2[key].date_progr}`;
                                            document.querySelector('#adminheuredepartp').add(opt);
                                            
                                        }
                                    } else {
                                        document.querySelector('#adminheuredepartp').options.length = 1;
                                    }
                                };
                                httpRequestews.setRequestHeader('Content-Type', 'application/json');
                                httpRequestews.send();
                            }
                            else{

                                document.querySelector('#adminnomclp').innerHTML = ``;
                                document.querySelector('#adminprenomclp').innerHTML = ``;
                                document.querySelector('#admincontactclp').innerHTML = ``;
                                document.querySelector('#adminrefclp').innerHTML = ``;
                                document.querySelector('#admindirectionclp').innerHTML = ``;
                                document.querySelector('#admincodeclp').innerHTML = ``;
                                document.querySelector('#adminheureclp').innerHTML = ``;
                                document.querySelector('#adminheuredepartp').style.display = 'none';
                                document.querySelector('#adminnumsiegep').style.display = 'none';
                                document.querySelector('#adbilletrep').style.display = 'block';
                                document.querySelector('#adbilletSmsrep').innerHTML = `Billet non valable, la durée de validité est dépassée.`;
                            }
                    }
                };
                httpRequestRep.setRequestHeader('Content-Type', 'application/json');
                httpRequestRep.send();
            };
                
        
            let heurdep = document.querySelector('#adminheuredepartp');
            if (heurdep !== null) {
                heurdep.onchange = () => {
                    document.querySelector('#adminnumsiegep').options.length = 1;
                    
                const httpRequerst = new XMLHttpRequest();
                const selectorts = document.querySelector('#adminheuredepartp').
                    options[document.querySelector('#adminheuredepartp').options.selectedIndex].value;
					
					var post_lh = selectorts.split('/');
					var selh = post_lh[0];
					var lignehsel = post_lh[1];
                    var post_lh1 = lignehsel.split('/');
                    var selh1 = post_lh1[0];
                    var lignehsel1 = post_lh1[1];
                    var vr = selh1;
                httpRequerst.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/siegdispo/${selh}`, true);
                httpRequerst.onload = () => {
                        const data = JSON.parse(httpRequerst.responseText);
                        console.debug(`${typeof data} - ${data.attributes}`, console.memory);
                        if (Object.entries(data).length > 0)
                            for (let key in Object.entries(data)) {
    
                                document.querySelector('#adminplacevendu').value = `${data[key].intervalle1}`;
                                document.querySelector('#admindplacevendu').value = `${data[key].intervalle2}`;
                                document.querySelector('#adminreplign').value = `${data[key].nom_ligne}`;
                                document.querySelector('#adminrepher').value = `${data[key].heure}`;
                                document.querySelector('#admindatereprogramme').value = `${data[key].date_progr}`;
                                document.querySelector('#admincatreprogramme').value = `${data[key].categori}`;
                                }
                            
                            const httpRequetterep = new XMLHttpRequest();
                                const pld = document.querySelector('#adminplacevendu').value;
                                const plf = document.querySelector('#admindplacevendu').value;
                                const lgr = document.querySelector('#adminreplign').value;
                                const reph = document.querySelector('#adminrepher').value;
                                const dtrep = document.querySelector('#admindatereprogramme').value;

                            httpRequetterep.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${selh}/${dtrep}/${lgr}/${reph}/${pld}/${plf}`, true);
                            httpRequetterep.onload = () => {
                            const dattas = JSON.parse(httpRequetterep.responseText);
                            console.debug(`${typeof dattas} - ${dattas.attributes}`, console.memory);
                            if (Object.entries(dattas).length >= 1)
                            {
                               
                                for (let key in Object.entries(dattas)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${dattas[key].siege_num}`;
                                    opt.innerHTML = `${dattas[key].siege_num}`;
                                    document.querySelector('#adminnumsiegep').add(opt);
                                    console.debug(`${dattas[key].siege_num}`, console.memory)
                                }
                            } else {
                                document.querySelector('#adminnumsiegep').options.length = 1;
                                
                            }
                    };
                    httpRequetterep.setRequestHeader('Content-Type', 'application/json');
                    httpRequetterep.send();
                    };
                    httpRequerst.setRequestHeader('Content-Type', 'application/json');
                    httpRequerst.send();
                };
           
            }

            let numsiege = document.querySelector('#adminnumsiegep');
            if (numsiege !== null)
            numsiege.onchange = () => {
                    
                    let Requestsiegevendu;
                    
                    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                        Requestsiegevendu = new XMLHttpRequest();
                    } else if (window.ActiveXObject) { // IE 6 and older
                        Requestsiegevendu = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                    const dp_progrep = document.querySelector('#adminprogramrep').value;
                    const dp_siegerep = document.querySelector('#adminnumsiegep').options[document.querySelector('#numsiegep').options.selectedIndex].value;
                    Requestsiegevendu.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${dp_progrep}/${dp_siegerep}`, true);
                    Requestsiegevendu.onload = () => 
                    {
                        
                            const donsieg = JSON.parse(Requestsiegevendu.responseText);
                            if (donsieg == '')
                                    {
                                        let httpSiegsrep;
                                        httpSiegsrep = new XMLHttpRequest();

                                        httpSiegsrep.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${dp_progrep}/${dp_siegerep}`, true);
                                        httpSiegsrep.onload = () => 
                                        {
                                            const dongrep= JSON.parse(httpSiegsrep.responseText);
                                            document.querySelector('#adminerreursieg').style.display = 'none';
                                            if (Object.entries(dongrep).length >= 1)
                                            {
                                                for (let key in Object.entries(dongrep)) {
                                                    document.querySelector('#adminidtamporep').value = `${dongrep[key].idtamp}`;                    
                                                    document.querySelector('#adminsiegselectrep').value = `${dongrep[key].numsieg}`;
                                                }

                                            }
                                        };
                                        httpSiegsrep.setRequestHeader('Content-Type', 'application/json');
                                        httpSiegsrep.send();
                                    }
                                    else {
                                        document.querySelector('#adminnumsiegep').value = '';     
                                        if (Object.entries(donsieg).length >= 1)
                                        {
                                            for (let key in Object.entries(donsieg)) {
                                                document.querySelector('#adminidtamporep').value = `${donsieg[key].idtamp}`;                    
                                                document.querySelector('#adminsiegselectrep').value = `${donsieg[key].numsieg}`;
                                            }

                                        }
                                        document.querySelector('#adminerreursieg').style.display = 'block';
                                        document.querySelector('#adminerreurSiege').innerHTML = `Siege déjà utilisé.`; 
                                    }
                    };
                    Requestsiegevendu.setRequestHeader('content-Type', 'text/json');
                    Requestsiegevendu.send();
                };

                butonclicrep = document.querySelector('#adminrese');
            if (butonclicrep !== null) {
                butonclicrep.onclick = () => 
                {
                    let httpSiegeselectrep;
                    httpSiegeselectrep = new XMLHttpRequest();
                    const siegselectrep= document.querySelector('#adminsiegselectrep').value;
                    const idtaprep = document.querySelector('#adminidtamporep').value;
                    httpSiegeselectrep.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtaprep}/${siegselectrep}`, true);
                    httpSiegeselectrep.onload = () => 
                    {
                        const donselectrep= JSON.parse(httpSiegeselectrep.responseText);
                        console.debug(`${typeof donselectrep} - ${donselectrep.attributes}`, console.memory);
                        document.querySelector('#adminerreursieg').style.display = 'none';
                        
                    };
                    httpSiegeselectrep.setRequestHeader('Content-Type', 'application/json');
                    httpSiegeselectrep.send();

                
                };
            }

            
        e.onclick = function () {
            let rForm = document.querySelector('#adminrForm');
            rForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/adupdate/${e.dataset.cle_compagnie}`);
        }
    })
});
;
/* --- addconfirmadmin.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addconfirmadmin').forEach(function (e) {
        document.querySelector('h3#admincTitle').innerHTML = `CONFIRMATION`;

        let c = document.querySelector('#adminconfirme_info');
        if (c !== null)
        c.onclick = () => {
            
            //verification code de confirmation
            let Request;
            
            if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                Request = new XMLHttpRequest();
            } else if (window.ActiveXObject) { // IE 6 and older
                Request = new ActiveXObject("Microsoft.XMLHTTP");
            }
            
            var codes = document.querySelector("#admincodeconfirm").value;

            Request.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifcodeconf/${codes}`, true);
            Request.onload = () => {
                const dons = JSON.parse(Request.responseText);
                    if (dons == null) {
                        document.querySelector('#adminmessagep').style.display = 'block';
                        document.querySelector('#adminerreurMessagep').innerHTML = `Cet ticket ne peut pas être confirmé ici.`;
                        document.querySelector('#adminheured').style.display = 'none';
                        document.querySelector('#admindepsieg').style.display = 'none';
                        document.querySelector('#adminquartconf').style.display = 'none';
                        document.querySelector('#adminnomp').innerText = ``;
                        document.querySelector('#adminprenomp').innerText = ``;
                        document.querySelector('#admincontactp').innerHTML = ``;
                        document.querySelector('#adminrefp').innerHTML = ``;
                        document.querySelector('#admindirectionp').innerHTML = ``;
                        document.querySelector('#admincodecp').innerHTML = ``;
                        document.querySelector('#axeconfirm').style.display = 'none';
                        document.querySelector('#ligneconflg').value = '';
                    }
                    else 
                    {
                        
                        if (Object.entries(dons).length >= 1){
                            document.querySelector('#adminerreurMessagep').innerHTML = '';
                            document.querySelector('#adminheured').style.display = 'block';
                            document.querySelector('#admindepsieg').style.display = 'block';
                            document.querySelector('#adminquartconf').style.display = 'block';
                            document.querySelector('#axeconfirm').style.display = 'block';
                            document.querySelector('#adminnomp').innerText = `NOM: ${dons.nom_client}`;
                            document.querySelector('#adminprenomp').innerText = `PRENOM: ${dons.prenom_client}`;
                            document.querySelector('#admincontactp').innerHTML = `CONTACT: ${dons.contact_client}`;
                            document.querySelector('#adminrefp').innerHTML = `REFERENCE CNIB: ${dons.num_CNIB}`;
                            document.querySelector('#admindirectionp').innerHTML = `AXE: ${dons.nom_ligne}`;
                            document.querySelector('#admincodecp').innerHTML = `CODE VENTE: ${dons.code_non_pass}`;
                            document.querySelector('#adminpassep').value = `${dons.code_non_pass}`;
                            document.querySelector('#adminpascodetick').value = `${dons.codeticket}`;
                            document.querySelector('#adminclientidp').value = `${dons.id_client_npass}`;
                            document.querySelector('#adminpasnomp').value = `${dons.nom_client}`;
                            document.querySelector('#adminpasprenomp').value = `${dons.prenom_client}`;
                            document.querySelector('#adminpascontactp').value = `${dons.contact_client}`;
                            document.querySelector('#adminpascnibp').value = `${dons.num_CNIB}`;
                            document.querySelector('#adminpasdatep').value = `${dons.date_delivre}`;
                            document.querySelector('#adcommentclient').value = `${dons.comment_client}`;
                            document.querySelector('#adminlieu').value = `${dons.lieu_delivre}`;
                            document.querySelector('#admimtype').value = `${dons.type_client}`;
                            document.querySelector('#dateventeconf').value = `${dons.datevente}`;
                            document.querySelector('#axeligneconf').value = `${dons.id_ligne_pass}`;
                            document.querySelector('#ligneconflg').value = `${dons.nom_ligne}`;
                            document.querySelector('#admincodecpas').value = `${dons.code_non_pass}`;
                            document.querySelector('#adlignehconf').value = `${dons.id_ligneheure}`;
                            document.querySelector('#admincodeconfi').value = `${dons.tamponcod}`;


                        } 
                        else 
                        {
                            document.querySelector('#adminheured').style.display = 'none';
                            document.querySelector('#admindepsieg').style.display = 'none';
                            document.querySelector('#adminquartconf').style.display = 'none';
                            document.querySelector('#axeconfirm').style.display = 'none';
                        }
                        
                                let Requestslg = new XMLHttpRequest();
                                    const confirheurelg = document.querySelector('#ligneconflg').value;
                                    var postmob = confirheurelg.split('-');
                                    var avmob = postmob[0];
                                    var apmob = postmob[1];
                                    Requestslg.open('GET', window.location.origin + `${APP_ROOT}/confirmation/veriflignelg/${apmob}-${avmob}`, true);
                                    Requestslg.onload = () => {
                                        const datas2lg = JSON.parse(Requestslg.responseText);
                                        if (Object.entries(datas2lg).length >= 1) {
                                    for (let key in Object.entries(datas2lg)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${datas2lg.ident_ligne}`;
                                        opt.innerHTML = `${datas2lg.nom_ligne}`;
                                        document.querySelector('#axeconfirm').add(opt);
                                        
                                        
                                    }
                                }else{
                                    document.querySelector('#axeconfirm').options.length = 1;
                                }
                            };
                            Requestslg.setRequestHeader('Content-Type', 'application/json');
                            Requestslg.send();
                       
                            
                                            
                            let axeselectconf = document.querySelector('#axeconfirm');
                            if (axeselectconf !== null)
                                axeselectconf.onchange = () => 
                                {
                                       
                                            var datdepart = document.querySelector('#dateventeconf').value;
                                            var datdepartactu = document.querySelector('#datactu').value;
                                            var date1  = new Date(datdepart);
                                            var date2 = new Date(datdepartactu);
                                            // différence des heures
                                            var time_diff = date2.getTime() - date1.getTime();
                                                // différence de jours
                                            const days_Diff = time_diff / (1000 * 3600 * 24);
                                            if(days_Diff < 28 || days_Diff < 29 || days_Diff < 30 || days_Diff < 31)    
                                            {
                                                const heureaxeconf = document.querySelector('#axeconfirm').options[document.querySelector('#axeconfirm').options.selectedIndex].value;
                                    
                                                let Requests = new XMLHttpRequest();
                                                const confirheure = document.querySelector('#axeconfirm').
                                                options[document.querySelector('#axeconfirm').options.selectedIndex].value;
                                                
                                                var dateactuel = document.querySelector('#datactu').value;
                                                Requests.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfprog/${confirheure}/${dateactuel}`, true);
                                                Requests.onload = () => {
                                                    const datas2 = JSON.parse(Requests.responseText);
                                                    if (Object.entries(datas2).length >= 1) {
                                                        for (let key in Object.entries(datas2)) {
                                                            let opt = document.createElement('option');
                                                            opt.value = `${datas2[key].code_progr}/${datas2[key].typetarif}`;
                                                            opt.innerHTML = `${datas2[key].heure}/${datas2[key].date_progr}`;
                                                            document.querySelector('#adminheured').add(opt);
                                                            
                                                            
                                                        }
                                                    }else{
                                                        document.querySelector('#adminheured').options.length = 1;
                                                    }
                                                };
                                                Requests.setRequestHeader('Content-Type', 'application/json');
                                                Requests.send();
                                            
                                                var dateactuel = document.querySelector('#datactu').value;
                                                
                                                let httpRequetesquart = new XMLHttpRequest();
                                                    httpRequetesquart.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfquart/${heureaxeconf}`, true);
                                                httpRequetesquart.onload = () => {
                                                    const dataq = JSON.parse(httpRequetesquart.responseText);
                                                    if(dataq == ''){
                                                        document.querySelector('#adminquartconf').options.length = 1;
                                                    }else{
                                                        if (Object.entries(dataq).length >= 1) {
                                                                    
                                                            for (let key in Object.entries(dataq)) {
                                                                let opt = document.createElement('option');
                                                                opt.value = `${dataq[key].nom_quartier}`;
                                                                opt.innerHTML = `${dataq[key].nom_quartier}`;
                                                                document.querySelector('#adminquartconf').add(opt);
                                                            }
                                                        } else {
                                                            document.querySelector('#adminquartconf').options.length = 1;
                                                        }
                                                    }
                                                        
                                                            
                                                };
                                                httpRequetesquart.setRequestHeader('Content-Type', 'application/json');
                                                httpRequetesquart.send();
                                            }
                                            else
                                            {
                                                document.querySelector('#adminheured').style.display = 'none';
                                                document.querySelector('#admindepsieg').style.display = 'none';
                                                document.querySelector('#adminquartconf').style.display = 'none';
                                                document.querySelector('#adminnomp').innerText = ``;
                                                document.querySelector('#adminprenomp').innerText = ``;
                                                document.querySelector('#admincontactp').innerHTML = ``;
                                                document.querySelector('#adminrefp').innerHTML = ``;
                                                document.querySelector('#admindirectionp').innerHTML = ``;
                                                document.querySelector('#admincodecp').innerHTML = ``;
                                                document.querySelector('#billet').style.display = 'block';
                                                document.querySelector('#billetSms').innerHTML = `Billet non valable, la durée de validité est dépassée.`;
            
                                            }
                                };
                                            
                                            
                    }
               
            };
            Request.setRequestHeader('Content-Type', 'application/json');
            Request.send(); 
        };

        let heurdeprt = document.querySelector('#adminheured');
        if (heurdeprt !== null)
            heurdeprt.onchange = () => {
                
                document.querySelector('#admindepsieg').options.length = 1;
                const Requeste = new XMLHttpRequest();
                const selectorp = document.querySelector('#adminheured').options[document.querySelector('#adminheured').
                options.selectedIndex].value;
                var selectorp1 = selectorp.split('/');
                var selectorp2 = selectorp1[0];
                var selectorp3 = selectorp1[1];
                Requeste.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/siegdispo/${selectorp2}`, true);
                Requeste.onload = () => {
                    const datasgc = JSON.parse(Requeste.responseText);
                    if (Object.entries(datasgc).length >= 1) {
                        for (let key in Object.entries(datasgc)) {
                            
                            document.querySelector('#adcaissepvend_').value = `${datasgc[key].intervalle1}`;
                            document.querySelector('#adcaissedpvend_').value = `${datasgc[key].intervalle2}`;
                            document.querySelector('#addirectid').value = `${datasgc[key].nom_ligne}`;
                            document.querySelector('#adconfheure').value = `${datasgc[key].heure}`;
                            document.querySelector('#addateconfirme').value = `${datasgc[key].date_progr}`;
                            document.querySelector('#adcatego').value = `${datasgc[key].categori}`;
                            document.querySelector('#adlignehconf').value = `${datasgc[key].id_ligneheure}`;
                            document.querySelector('#adprogramconf').value = `${datasgc[key].code_progr}`;
                        }
                    } 
                    const Requestbis = new XMLHttpRequest();
                            const pldebut = document.querySelector('#adcaissepvend_').value;
                            const plfin = document.querySelector('#adcaissedpvend_').value;
                            const cfdir = document.querySelector('#addirectid').value;
                            const hconfir = document.querySelector('#adconfheure').value;
                            const dconfirme = document.querySelector('#addateconfirme').value;
                    Requestbis.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${selectorp2}/${dconfirme}/${cfdir}/${hconfir}/${pldebut}/${plfin}`, true);
                    Requestbis.onload = () => {
                        const datasgcbis = JSON.parse(Requestbis.responseText);
                        if (Object.entries(datasgcbis).length >= 1) {
                            for (let key in Object.entries(datasgcbis)) {
                                let opt = document.createElement('option');
                                opt.value = `${datasgcbis[key].siege_num}`;
                                opt.innerHTML = `${datasgcbis[key].siege_num}`;
                                document.querySelector('#admindepsieg').add(opt);
                            }
                        } else {
                            document.querySelector('#admindepsieg').options.length = 1;
                        }
                    };
                    Requestbis.setRequestHeader('Content-Type', 'application/json');
                    Requestbis.send();
                };
                Requeste.setRequestHeader('Content-Type', 'application/json');
                Requeste.send();
            };

            let depsiegconf = document.querySelector('#admindepsieg');
            if (depsiegconf !== null)
            depsiegconf.onchange = () => {
                    
                    let Requestsiegevenduconf;
                    
                    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                        Requestsiegevenduconf = new XMLHttpRequest();
                    } else if (window.ActiveXObject) { // IE 6 and older
                        Requestsiegevenduconf = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                    const dp_progconf = document.querySelector('#adprogramconf').value;
                    const dp_siegeconf = document.querySelector('#admindepsieg').options[document.querySelector('#admindepsieg').options.selectedIndex].value;
                    Requestsiegevenduconf.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${dp_progconf}/${dp_siegeconf}`, true);
                    Requestsiegevenduconf.onload = () => 
                    {
                        
                            const confdonsieg = JSON.parse(Requestsiegevenduconf.responseText);
                            if (confdonsieg == '')
                                    {
                                        let httpSiegsconf;
                                        httpSiegsconf = new XMLHttpRequest();

                                        httpSiegsconf.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${dp_progconf}/${dp_siegeconf}`, true);
                                        httpSiegsconf.onload = () => 
                                        {
                                            const dongconf= JSON.parse(httpSiegsconf.responseText);
                                            document.querySelector('#adminmessconf').style.display = 'none';
                                            if (Object.entries(dongconf).length >= 1)
                                        {
                                            for (let key in Object.entries(dongconf)) {
                                                document.querySelector('#adminidtampoconf').value = `${dongconf[key].idtamp}`;                    
                                                document.querySelector('#adminsiegselectconf').value = `${dongconf[key].numsieg}`;
                                            }

                                        }
                                        
                                        };
                                        httpSiegsconf.setRequestHeader('Content-Type', 'application/json');
                                        httpSiegsconf.send();
                                    }
                                    else {
                                        document.querySelector('#admindepsieg').value = '';     
                                        if (Object.entries(confdonsieg).length >= 1)
                                        {
                                            for (let key in Object.entries(confdonsieg)) {
                                                document.querySelector('#adminidtampoconf').value = `${confdonsieg[key].idtamp}`;                    
                                                document.querySelector('#adminsiegselectconf').value = `${confdonsieg[key].numsieg}`;
                                            }

                                        }
                                        document.querySelector('#adminmessconf').style.display = 'block';
                                        document.querySelector('#adminerreurMessconf').innerHTML = `Siege déjà utilisé.`; 
                                    }
                    };
                    Requestsiegevenduconf.setRequestHeader('content-Type', 'text/json');
                    Requestsiegevenduconf.send();
                };
            //bouton annuler
                butoncliconf = document.querySelector('#adminconfreset');
                if (butoncliconf !== null) {
                    butoncliconf.onclick = () => 
                    {
                        let httpSiegeselectconf;
                        httpSiegeselectconf = new XMLHttpRequest();
                        const siegselectconf = document.querySelector('#adminsiegselectconf').value;
                        const idtapconf = document.querySelector('#adminidtampoconf').value;
                        httpSiegeselectconf.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtapconf}/${siegselectconf}`, true);
                        httpSiegeselectconf.onload = () => 
                        {
                            const donselectconf = JSON.parse(httpSiegeselectconf.responseText);
                            console.debug(`${typeof donselectconf} - ${donselectconf.attributes}`, console.memory);
                            document.querySelector('#adminmessconf').style.display = 'none';
                            
                        };
                        httpSiegeselectconf.setRequestHeader('Content-Type', 'application/json');
                        httpSiegeselectconf.send();
    
                    
                    };
                }       
                       
        e.onclick = function () {
            let adcForm = document.querySelector('#admincForm');
            adcForm.setAttribute('action', `${APP_ROOT}/Confirmation/adminconfirme/${e.dataset.ckey}`);
        }
    })
});
;
/* --- addconfirmadmintran.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.addconfirmadmintran').forEach(function (e) {
        
        document.querySelector('h3#admincTitletran').innerHTML = `CONFIRMATION`;

        let c = document.querySelector('#adminconfirme_infotran');
        if (c !== null)
        c.onclick = () => {
            
            //verification code de confirmation
            let Request;
            
            if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                Request = new XMLHttpRequest();
            } else if (window.ActiveXObject) { // IE 6 and older
                Request = new ActiveXObject("Microsoft.XMLHTTP");
            }
            
            var codes = document.querySelector("#admincodeconfirmtran").value;
            document.querySelector('#axeconfirmtran').options.length = 1;
            document.querySelector('#depargarestran').options.length = 1;
            Request.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifcodeconftran/${codes}`, true);
            Request.onload = () => {
                const dons = JSON.parse(Request.responseText);
                    if (dons == null) {
                        document.querySelector('#adminmessageptran').style.display = 'block';
                        document.querySelector('#adminerreurMessageptran').innerHTML = `Cet ticket ne peut pas être confirmé ici.`;
                        document.querySelector('#adminheuredtran').style.display = 'none';
                        document.querySelector('#admindepsiegtran').style.display = 'none';
                        document.querySelector('#adminquartconftran').style.display = 'none';
                        document.querySelector('#adminnomptran').innerText = ``;
                        document.querySelector('#adminprenomptran').innerText = ``;
                        document.querySelector('#admincontactptran').innerHTML = ``;
                        document.querySelector('#adminrefptran').innerHTML = ``;
                        document.querySelector('#admindirectionptran').innerHTML = ``;
                        document.querySelector('#admincodecptran').innerHTML = ``;
                        document.querySelector('#axeconfirmtran').style.display = 'none';
                        document.querySelector('#ligneconflgtran').value = '';
                    }
                    else 
                    {
                        
                        if (Object.entries(dons).length >= 1){
                            document.querySelector('#adminerreurMessageptran').innerHTML = '';
                            document.querySelector('#adminheuredtran').style.display = 'block';
                            document.querySelector('#admindepsiegtran').style.display = 'block';
                            document.querySelector('#adminquartconftran').style.display = 'block';
                            document.querySelector('#axeconfirmtran').style.display = 'block';
                            document.querySelector('#adminnomptran').innerText = `NOM: ${dons.nom_client}`;
                            document.querySelector('#adminprenomptran').innerText = `PRENOM: ${dons.prenom_client}`;
                            document.querySelector('#admincontactptran').innerHTML = `CONTACT: ${dons.contact_client}`;
                            document.querySelector('#adminrefptran').innerHTML = `REFERENCE CNIB: ${dons.num_CNIB}`;
                            document.querySelector('#admindirectionptran').innerHTML = `AXE: ${dons.nom_ligne}`;
                            document.querySelector('#admincodecptran').innerHTML = `CODE VENTE: ${dons.code_non_pass}`;
                            document.querySelector('#adminpasseptran').value = `${dons.code_non_pass}`;
                            document.querySelector('#adminpascodeticktran').value = `${dons.codeticket}`;
                            document.querySelector('#adminclientidptran').value = `${dons.id_client_npass}`;
                            document.querySelector('#adminpasnomptran').value = `${dons.nom_client}`;
                            document.querySelector('#adminpasprenomptran').value = `${dons.prenom_client}`;
                            document.querySelector('#adminpascontactptran').value = `${dons.contact_client}`;
                            document.querySelector('#adminpascnibptran').value = `${dons.num_CNIB}`;
                            document.querySelector('#adminpasdateptran').value = `${dons.date_delivre}`;
                            document.querySelector('#adcommentclienttran').value = `${dons.comment_client}`;
                            document.querySelector('#adminlieutran').value = `${dons.lieu_delivre}`;
                            document.querySelector('#admimtypetran').value = `${dons.type_client}`;
                            document.querySelector('#dateventeconftran').value = `${dons.datevente}`;
                            document.querySelector('#axeligneconftran').value = `${dons.id_ligne_pass}`;
                            document.querySelector('#ligneconflgtran').value = `${dons.nom_ligne}`;
                            document.querySelector('#admincodecpastran').value = `${dons.code_non_pass}`;
                            document.querySelector('#adlignehconftran').value = `${dons.id_ligneheure}`;
                            document.querySelector('#admincodeconfitran').value = `${dons.tamponcod}`;


                        } 
                        else 
                        {
                            document.querySelector('#adminheuredtran').style.display = 'none';
                            document.querySelector('#admindepsiegtran').style.display = 'none';
                            document.querySelector('#adminquartconftran').style.display = 'none';
                            document.querySelector('#axeconfirmtran').style.display = 'none';
                        }
                        
                            let Requestslg = new XMLHttpRequest();
                            const confirheurelg = document.querySelector('#ligneconflgtran').value;
                            var postmob = confirheurelg.split('-');
                            var avmob = postmob[0];
                            var apmob = postmob[1];
                            Requestslg.open('GET', window.location.origin + `${APP_ROOT}/confirmation/veriflignelg/${apmob}-${avmob}`, true);
                            Requestslg.onload = () => {
                                const datas2lg = JSON.parse(Requestslg.responseText);
                                if (Object.entries(datas2lg).length >= 1) {
                                    for (let key in Object.entries(datas2lg)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${datas2lg.ident_ligne}`;
                                        opt.innerHTML = `${datas2lg.nom_ligne}`;
                                        document.querySelector('#axeconfirmtran').add(opt);   
                                    }
                                }else{
                                    document.querySelector('#axeconfirmtran').options.length = 1;
                                }
                            };
                            Requestslg.setRequestHeader('Content-Type', 'application/json');
                            Requestslg.send();
                       
                            
                                            
                            let axeselectconf = document.querySelector('#axeconfirmtran');
                            if (axeselectconf !== null)
                                axeselectconf.onchange = () => 
                                {
                               
                                    var datdepart = document.querySelector('#dateventeconftran').value;
                                    var datdepartactu = document.querySelector('#datactutran').value;
                                    var date1  = new Date(datdepart);
                                    var date2 = new Date(datdepartactu);
                                    // différence des heures
                                    var time_diff = date2.getTime() - date1.getTime();
                                        // différence de jours
                                    const days_Diff = time_diff / (1000 * 3600 * 24);
                                    if(days_Diff < 28 || days_Diff < 29 || days_Diff < 30 || days_Diff < 31)    
                                    {
                                        const heureaxeconf = document.querySelector('#axeconfirmtran').options[document.querySelector('#axeconfirmtran').options.selectedIndex].value;
                            
                                        let Requests = new XMLHttpRequest();
                                        let Requests1 = new XMLHttpRequest();
                                        const confirheure = document.querySelector('#axeconfirmtran').
                                        options[document.querySelector('#axeconfirmtran').options.selectedIndex].value;
                                        
                                        var postmobt = confirheure.split('-');
                                        var confirh = postmobt[0];
                                        var apmobt = postmobt[1];
                                        var dateactuel = document.querySelector('#datactutran').value;
                                    
                                        Requests.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfprog/${confirheure}/${dateactuel}`, true);
                                        Requests.onload = () => {
                                            const datas2 = JSON.parse(Requests.responseText);
                                            if (Object.entries(datas2).length >= 1) {
                                                for (let key in Object.entries(datas2)) {
                                                    let opt = document.createElement('option');
                                                    opt.value = `${datas2[key].code_progr}/${datas2[key].typetarif}`;
                                                    opt.innerHTML = `${datas2[key].heure}/${datas2[key].date_progr}`;
                                                    document.querySelector('#adminheuredtran').add(opt);  
                                                }
                                            }else{
                                                document.querySelector('#adminheuredtran').options.length = 1;
                                            }
                                        };
                                        Requests.setRequestHeader('Content-Type', 'application/json');
                                        Requests.send();
                                        
                                        Requests1.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifsoug/${confirh}`, true);
                                        Requests1.onload = () => {

                                        const datasg2 = JSON.parse(Requests1.responseText);
                                            if (Object.entries(datasg2).length >= 1) {
        
                                                for (let key in Object.entries(datasg2)) {
                                                    let opt1 = document.createElement('option');
                                                    opt1.value = `${datasg2[key].code_gaexp}/${datasg2[key].idsousgare}`;
                                                    opt1.innerHTML = `${datasg2[key].nom_gaep}/${datasg2[key].nomsousgare}`;
                                                    document.querySelector('#depargarestran').add(opt1); 
                                                }
                                            }else{
                                                
                                                document.querySelector('#depargarestran').options.length = 1;
                                            }
                                        };
                                        Requests1.setRequestHeader('Content-Type', 'application/json');
                                        Requests1.send();
                                        
                                        let httpRequetesquart = new XMLHttpRequest();
                                            httpRequetesquart.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfquart/${heureaxeconf}`, true);
                                        httpRequetesquart.onload = () => {
                                            const dataq = JSON.parse(httpRequetesquart.responseText);
                                            if(dataq == ''){
                                                document.querySelector('#adminquartconftran').options.length = 1;
                                            }else{
                                                if (Object.entries(dataq).length >= 1) {
                                                            
                                                    for (let key in Object.entries(dataq)) {
                                                        let opt = document.createElement('option');
                                                        opt.value = `${dataq[key].nom_quartier}`;
                                                        opt.innerHTML = `${dataq[key].nom_quartier}`;
                                                        document.querySelector('#adminquartconftran').add(opt);
                                                    }
                                                } else {
                                                    document.querySelector('#adminquartconftran').options.length = 1;
                                                }
                                            }
                                                
                                                    
                                        };
                                        httpRequetesquart.setRequestHeader('Content-Type', 'application/json');
                                        httpRequetesquart.send();
                                    }
                                    else
                                    {
                                        document.querySelector('#adminheuredtran').style.display = 'none';
                                        document.querySelector('#admindepsiegtran').style.display = 'none';
                                        document.querySelector('#adminquartconftran').style.display = 'none';
                                        document.querySelector('#adminnomptran').innerText = ``;
                                        document.querySelector('#adminprenomptran').innerText = ``;
                                        document.querySelector('#admincontactptran').innerHTML = ``;
                                        document.querySelector('#adminrefptran').innerHTML = ``;
                                        document.querySelector('#admindirectionptran').innerHTML = ``;
                                        document.querySelector('#admincodecptran').innerHTML = ``;
                                        document.querySelector('#billettran').style.display = 'block';
                                        document.querySelector('#billetSmstran').innerHTML = `Billet non valable, la durée de validité est dépassée.`;
    
                                    }
                                };
                                        
                                            
                    }
               
            };
            Request.setRequestHeader('Content-Type', 'application/json');
            Request.send(); 
        };

        let heurdeprt = document.querySelector('#adminheuredtran');
        if (heurdeprt !== null)
            heurdeprt.onchange = () => {
                
                document.querySelector('#admindepsiegtran').options.length = 1;
                const Requeste = new XMLHttpRequest();
                const selectorp = document.querySelector('#adminheuredtran').options[document.querySelector('#adminheuredtran').
                options.selectedIndex].value;
                var selectorp1 = selectorp.split('/');
                var selectorp2 = selectorp1[0];
                var selectorp3 = selectorp1[1];
                Requeste.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/siegdispo/${selectorp2}`, true);
                Requeste.onload = () => {
                    const datasgc = JSON.parse(Requeste.responseText);
                    if (Object.entries(datasgc).length >= 1) {
                        for (let key in Object.entries(datasgc)) {
                            
                            document.querySelector('#adcaissepvend_tran').value = `${datasgc[key].intervalle1}`;
                            document.querySelector('#adcaissedpvend_tran').value = `${datasgc[key].intervalle2}`;
                            document.querySelector('#addirectidtran').value = `${datasgc[key].nom_ligne}`;
                            document.querySelector('#adconfheuretran').value = `${datasgc[key].heure}`;
                            document.querySelector('#addateconfirmetran').value = `${datasgc[key].date_progr}`;
                            document.querySelector('#adcategotran').value = `${datasgc[key].categori}`;
                            document.querySelector('#adlignehconftran').value = `${datasgc[key].id_ligneheure}`;
                            document.querySelector('#adprogramconftran').value = `${datasgc[key].code_progr}`;
                        }
                    } 
                    const Requestbis = new XMLHttpRequest();
                    const pldebut = document.querySelector('#adcaissepvend_tran').value;
                    const plfin = document.querySelector('#adcaissedpvend_tran').value;
                    const cfdir = document.querySelector('#addirectidtran').value;
                    const hconfir = document.querySelector('#adconfheuretran').value;
                    const dconfirme = document.querySelector('#addateconfirmetran').value;
                    Requestbis.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${selectorp2}/${dconfirme}/${cfdir}/${hconfir}/${pldebut}/${plfin}`, true);
                    Requestbis.onload = () => {
                        const datasgcbis = JSON.parse(Requestbis.responseText);
                        if (Object.entries(datasgcbis).length >= 1) {
                            for (let key in Object.entries(datasgcbis)) {
                                let opt = document.createElement('option');
                                opt.value = `${datasgcbis[key].siege_num}`;
                                opt.innerHTML = `${datasgcbis[key].siege_num}`;
                                document.querySelector('#admindepsiegtran').add(opt);
                            }
                        } else {
                            document.querySelector('#admindepsiegtran').options.length = 1;
                        }
                    };
                    Requestbis.setRequestHeader('Content-Type', 'application/json');
                    Requestbis.send();
                };
                Requeste.setRequestHeader('Content-Type', 'application/json');
                Requeste.send();
            };

            let depsiegconf = document.querySelector('#admindepsiegtran');
            if (depsiegconf !== null)
            depsiegconf.onchange = () => {
                    
                    let Requestsiegevenduconf;
                    
                    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                        Requestsiegevenduconf = new XMLHttpRequest();
                    } else if (window.ActiveXObject) { // IE 6 and older
                        Requestsiegevenduconf = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                    const dp_progconf = document.querySelector('#adprogramconftran').value;
                    const dp_siegeconf = document.querySelector('#admindepsiegtran').options[document.querySelector('#admindepsiegtran').options.selectedIndex].value;
                    Requestsiegevenduconf.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${dp_progconf}/${dp_siegeconf}`, true);
                    Requestsiegevenduconf.onload = () => 
                    {
                        
                            const confdonsieg = JSON.parse(Requestsiegevenduconf.responseText);
                            if (confdonsieg == '')
                                    {
                                        let httpSiegsconf;
                                        httpSiegsconf = new XMLHttpRequest();

                                        httpSiegsconf.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${dp_progconf}/${dp_siegeconf}`, true);
                                        httpSiegsconf.onload = () => 
                                        {
                                            const dongconf= JSON.parse(httpSiegsconf.responseText);
                                            document.querySelector('#adminmessconftran').style.display = 'none';
                                            if (Object.entries(dongconf).length >= 1)
                                        {
                                            for (let key in Object.entries(dongconf)) {
                                                document.querySelector('#adminidtampoconftran').value = `${dongconf[key].idtamp}`;                    
                                                document.querySelector('#adminsiegselectconftran').value = `${dongconf[key].numsieg}`;
                                            }

                                        }
                                        
                                        };
                                        httpSiegsconf.setRequestHeader('Content-Type', 'application/json');
                                        httpSiegsconf.send();
                                    }
                                    else {
                                        document.querySelector('#admindepsiegtran').value = '';     
                                        if (Object.entries(confdonsieg).length >= 1)
                                        {
                                            for (let key in Object.entries(confdonsieg)) {
                                                document.querySelector('#adminidtampoconftran').value = `${confdonsieg[key].idtamp}`;                    
                                                document.querySelector('#adminsiegselectconftran').value = `${confdonsieg[key].numsieg}`;
                                            }

                                        }
                                        document.querySelector('#adminmessconftran').style.display = 'block';
                                        document.querySelector('#adminerreurMessconftran').innerHTML = `Siege déjà utilisé.`; 
                                    }
                    };
                    Requestsiegevenduconf.setRequestHeader('content-Type', 'text/json');
                    Requestsiegevenduconf.send();
                };
            //bouton annuler
                butoncliconf = document.querySelector('#adminconfresettran');
                if (butoncliconf !== null) {
                    butoncliconf.onclick = () => 
                    {
                        let httpSiegeselectconf;
                        httpSiegeselectconf = new XMLHttpRequest();
                        const siegselectconf = document.querySelector('#adminsiegselectconftran').value;
                        const idtapconf = document.querySelector('#adminidtampoconftran').value;
                        httpSiegeselectconf.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtapconf}/${siegselectconf}`, true);
                        httpSiegeselectconf.onload = () => 
                        {
                            const donselectconf = JSON.parse(httpSiegeselectconf.responseText);
                            console.debug(`${typeof donselectconf} - ${donselectconf.attributes}`, console.memory);
                            document.querySelector('#adminmessconftran').style.display = 'none';
                            
                        };
                        httpSiegeselectconf.setRequestHeader('Content-Type', 'application/json');
                        httpSiegeselectconf.send();
    
                    
                    };
                }       
                       
        e.onclick = function () {
            let adcForm = document.querySelector('#admincFormtran');
            adcForm.setAttribute('action', `${APP_ROOT}/Confirmation/adminconfirmetran/${e.dataset.ckey}`);
        }
    })
});
;
/* --- addconfirme.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addconfirme').forEach(function (e) {
        document.querySelector('h3#confTitle').innerHTML = `CONFIRMATION`;

        let cod = document.querySelector('#confirmer_infos');
        if (cod !== null)
        cod.onclick = () => {
            
            //verification code de confirmation
            let Request;
            
            if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                Request = new XMLHttpRequest();
            } else if (window.ActiveXObject) { // IE 6 and older
                Request = new ActiveXObject("Microsoft.XMLHTTP");
            }
            
            var confir = document.querySelector("#codeconfirm").value;

            Request.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verificationcode/${confir}`, true);
            Request.onload = () => {
                
            const data = JSON.parse(Request.responseText);
            
            if (data == null) {
                        
                        document.querySelector('#pasnompconf').style.display = 'block';
                        document.querySelector('#pasprenompconf').style.display = 'block';
                        document.querySelector('#pascontactpconf').style.display = 'block';
                        document.querySelector('#pascnibpconf').style.display = 'block';
                        document.querySelector('#pasdatepconf').style.display = 'block';
                        document.querySelector('#delivrelieu').style.display = 'block';
                        document.querySelector('#heured').style.display = 'block';
                        document.querySelector('#depsieg').style.display = 'block';
                        document.querySelector('#valid').style.display = 'block';
                        document.querySelector('#validep').style.display = 'block';
                        document.querySelector('#messagep').style.display = 'none';

                } else {
                    if (Object.entries(data).length > 1) {
                        
                        document.querySelector('#messagep').style.display = 'block';
                        document.querySelector('#erreurMessagep').innerHTML = `Cet ticket ne peut pas être confirmé .`;
                        document.querySelector('#pasnompconf').style.display = 'none';
                        document.querySelector('#pasprenompconf').style.display = 'none';
                        document.querySelector('#pascontactpconf').style.display = 'none';
                        document.querySelector('#pascnibpconf').style.display = 'none';
                        document.querySelector('#pasdatepconf').style.display = 'none';
                        document.querySelector('#delivrelieu').style.display = 'none';
                        document.querySelector('#heured').style.display = 'none';
                        document.querySelector('#depsieg').style.display = 'none';
                        document.querySelector('#valid').style.display = 'none';
                        document.querySelector('#validep').style.display = 'none';
                    }
                      
                }
            };
            Request.setRequestHeader('Content-Type', 'application/json');
            Request.send();
        };
        let Requests;
        if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
            Requests = new XMLHttpRequest();
        } else if (window.ActiveXObject) { // IE 6 and older
            Requests = new ActiveXObject("Microsoft.XMLHTTP");
        }
        let axeselect = document.querySelector('#axeconf');
        if (axeselect !== null)
        axeselect.onchange = () => 
        {
            
                var heureaxep = document.querySelector('#axeconf').value;
                var dateactuel = document.querySelector('#actuel').value;
                document.querySelector('#heured').options.length = 1;
                document.querySelector('#depsieg').options.length = 1;
                document.querySelector('#quartconf').options.length = 1;
                document.querySelector('#hdepartitinecf').options.length = 1;
                document.querySelector('#psiegesitinescf').options.length = 1;
                document.querySelector('#idcheminsheurcf').options.length = 1;
                document.querySelector('#transitedepargarecf1').options.length = 1;
                document.querySelector('#transitedepargarecf2').options.length = 1;
                document.querySelector('#transitedepargarecf3').options.length = 1;
                document.querySelector('#iddeptranscf4').style.display = 'none';
                document.querySelector('#transitedepargarecf4').options.length = 1;
                document.querySelector('#idcheminscf').options.length = 1;
                document.querySelector('#idcheminscf1').options.length = 1;
                document.querySelector('#idcheminscf2').options.length = 1;
                document.querySelector('#idcompgcf').value = '';
                document.querySelector('#idcompgcf1').value = '';
                document.querySelector('#idcompgcf2').value = '';
                document.querySelector('#idcompgcf3').value = '';
                document.querySelector('#psiegesitinescf1').options.length = 1;
                document.querySelector('#idcheminsheurcf1').options.length = 1;
                document.querySelector('#psiegesitinescf2').options.length = 1;
                document.querySelector('#idcheminsheurcf1').options.length = 1;
                document.querySelector('#quartiercf1').options.length = 1;
                document.querySelector('#quartiercf2').options.length = 1;
                document.querySelector('#quartiercf3').options.length = 1;
                
                document.querySelector('#iddeptranscf1').style.display = 'none';
                document.querySelector('#transitedepargarecf1').style.display = 'none';
                document.querySelector('#iddeptranscf2').style.display = 'none';
                document.querySelector('#transitedepargarecf2').style.display = 'none';
                document.querySelector('#iddeptranscf3').style.display = 'none';
                document.querySelector('#transitedepargarecf3').style.display = 'none';
                document.querySelector('#arritincf1').style.display = 'none';
                document.querySelector('#heureitincf1').style.display = 'none';
                document.querySelector('#lignesitinerairecf').style.display = 'none';
                document.querySelector('#lignecf1').style.display = 'none';
                document.querySelector('#siegitinecf1').style.display = 'none';
                document.querySelector('#psiegesitinescf1').style.display = 'none';
                document.querySelector('#arritincf2').style.display = 'none';
                document.querySelector('#siegitinecf2').style.display = 'none';
                document.querySelector('#psiegesitinescf2').style.display = 'none';
                document.querySelector('#arritincf3').style.display = 'none';
                document.querySelector('#quartiercf1').style.display = 'none';
                document.querySelector('#quartiercf2').style.display = 'none';
                document.querySelector('#quartiercf3').style.display = 'none';
                document.querySelector('#idquartcf1').style.display = 'none';
                document.querySelector('#idquartcf2').style.display = 'none';
                document.querySelector('#idquartcf3').style.display = 'none';
                document.querySelector('#idquartcf2').style.display = 'none';
                document.querySelector('#idcheminscf2').style.display = 'none';
                document.querySelector('#idcheminsheurcf1').style.display = 'none';
                document.querySelector('#heureitincf2').style.display = 'none';
                document.querySelector('#idcheminsheurcf').style.display = 'none';
                document.querySelector('#idcheminscf1').style.display = 'none';
                
                document.querySelector('#transitedepargarecf4').style.display = 'none';
                document.querySelector('#trancf').style.display = 'none';
                document.querySelector('#heureitincf').style.display = 'none';
                document.querySelector('#hdepartitinecf').style.display = 'none';
                document.querySelector('#siegitinecf').style.display = 'none';
                document.querySelector('#psiegesitinescf').style.display = 'none';
                document.querySelector('#heured').style.display = 'block';
                document.querySelector('#depsieg').style.display = 'block';
                document.querySelector('#programcf').value = '';

                let httpRequetesquart = new XMLHttpRequest();
                httpRequetesquart.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfquart/${heureaxep}`, true);
                httpRequetesquart.onload = () => {
                const dataq = JSON.parse(httpRequetesquart.responseText);
                    if(dataq == ''){
                        document.querySelector('#quartconf').options.length = 1;
                    }else
                    {
                        if (Object.entries(dataq).length >= 1) {
                                    
                            for (let key in Object.entries(dataq)) {
                                let opt = document.createElement('option');
                                opt.value = `${dataq[key].nom_quartier}`;
                                opt.innerHTML = `${dataq[key].nom_quartier}`;
                                document.querySelector('#quartconf').add(opt);
                            }
                        } else {
                            document.querySelector('#quartconf').options.length = 1;
                        }
                    }  
                        
                };
                httpRequetesquart.setRequestHeader('Content-Type', 'application/json');
                httpRequetesquart.send();

                Requests.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfprog/${heureaxep}/${dateactuel}`, true);
                Requests.onload = () => {
                    const data2 = JSON.parse(Requests.responseText);
                    if (data2 == '') {
                        
                        var seltdepcf = document.querySelector('#axeconf').value;

                        //on verifit pour voir si elle n'a pas d'itineraire
                        let httpRequestitinecf;
                        httpRequestitinecf = new XMLHttpRequest();
                        httpRequestitinecf.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifitine/${seltdepcf}`, true);
                        httpRequestitinecf.onload = () => {
                        
                            const donitinescf = JSON.parse(httpRequestitinecf.responseText);
                            if(donitinescf === null)
                            {
                                document.querySelector('#iddeptranscf1').style.display = 'none';
                                document.querySelector('#transitedepargarecf1').style.display = 'none';
                                document.querySelector('#iddeptranscf2').style.display = 'none';
                                document.querySelector('#transitedepargarecf2').style.display = 'none';
                                document.querySelector('#iddeptranscf3').style.display = 'none';
                                document.querySelector('#transitedepargarecf3').style.display = 'none';
                                document.querySelector('#iddeptranscf4').style.display = 'none';
                                document.querySelector('#transitedepargarecf4').style.display = 'none';
                                document.querySelector('#arritincf1').style.display = 'none';
                                document.querySelector('#heureitincf1').style.display = 'none';
                                document.querySelector('#lignesitinerairecf').style.display = 'none';
                                document.querySelector('#lignecf1').style.display = 'none';
                                document.querySelector('#siegitinecf1').style.display = 'none';
                                document.querySelector('#psiegesitinescf1').style.display = 'none';
                                document.querySelector('#arritincf2').style.display = 'none';
                                document.querySelector('#heureitincf2').style.display = 'none';
                                document.querySelector('#hdepartitinecf2').style.display = 'none';
                                document.querySelector('#siegitinecf2').style.display = 'none';
                                document.querySelector('#psiegesitinescf2').style.display = 'none';
                                document.querySelector('#arritincf3').style.display = 'none';
                                document.querySelector('#quartiercf1').style.display = 'none';
                                document.querySelector('#quartiercf2').style.display = 'none';
                                document.querySelector('#quartiercf3').style.display = 'none';
                                document.querySelector('#idquartcf1').style.display = 'none';
                                document.querySelector('#idquartcf2').style.display = 'none';
                                document.querySelector('#idquartcf3').style.display = 'none';

                                document.querySelector('#trancf').style.display = 'none';
                                document.querySelector('#heureitincf').style.display = 'none';
                                document.querySelector('#hdepartitinecf').style.display = 'none';
                                document.querySelector('#siegitinecf').style.display = 'none';
                                document.querySelector('#psiegesitinescf').style.display = 'none';
                                document.querySelector('#heured').style.display = 'block';
                            }
                            else
                            {
                                if (Object.entries(donitinescf).length >= 1) 
                                {
                                    var i = Object.entries(donitinescf).length;
                                    
                                    for (let key in Object.entries(donitinescf)) 
                                    {
                                        
                                        document.querySelector('#nbrtranscf').value = Object.entries(donitinescf).length;
                                        if(i === 2){
                                            document.querySelector('#idcheminscf').style.display = 'block';
                                            document.querySelector('#heured').style.display = 'block';
                                            document.querySelector('#heureitincf').style.display = 'block';
                                            document.querySelector('#hdepartitinecf').style.display = 'block';
                                            document.querySelector('#siegitinecf').style.display = 'block';
                                            document.querySelector('#psiegesitinescf').style.display = 'block';
                                            document.querySelector('#depsieg').style.display = 'block';
                                            document.querySelector('#quartiercf1').style.display = 'block';
                                            document.querySelector('#idquartcf1').style.display = 'block';
                                            document.querySelector('#lignecf1').style.display = 'block';
                                            document.querySelector('#lignesitinerairecf').style.display = 'block';
                                            document.querySelector('#iddeptranscf1').style.display = 'block';
                                            document.querySelector('#arritincf1').style.display = 'block';
                                            document.querySelector('#transitedepargarecf1').style.display = 'block';
                                            document.querySelector('#iddeptranscf2').style.display = 'block';
                                            document.querySelector('#transitedepargarecf2').style.display = 'block';
                                        }
                                        
                                        if(i === 3){
                                            document.querySelector('#idcheminscf').style.display = 'block';
                                            document.querySelector('#heureitincf').style.display = 'block';
                                            document.querySelector('#hdepartitinecf').style.display = 'block';
                                            document.querySelector('#siegitinecf').style.display = 'block';
                                            document.querySelector('#psiegesitinescf').style.display = 'block';
                                            document.querySelector('#depsieg').style.display = 'block';
                                            document.querySelector('#quartiercf1').style.display = 'block';
                                            document.querySelector('#idquartcf1').style.display = 'block';
                                            document.querySelector('#lignecf1').style.display = 'block';
                                            document.querySelector('#lignesitinerairecf').style.display = 'block';
                                            document.querySelector('#heured').style.display = 'block';
                                            document.querySelector('#iddeptranscf1').style.display = 'block';
                                            document.querySelector('#arritincf1').style.display = 'block';
                                            document.querySelector('#transitedepargarecf1').style.display = 'block';
                                            
                                            document.querySelector('#arritincf2').style.display = 'block';
                                            document.querySelector('#idcheminscf1').style.display = 'block';
                                            document.querySelector('#idquartcf2').style.display = 'block';
                                            document.querySelector('#quartiercf2').style.display = 'block';
                                            document.querySelector('#heureitincf1').style.display = 'block';
                                            document.querySelector('#idcheminsheurcf').style.display = 'block';
                                            document.querySelector('#siegitinecf1').style.display = 'block';
                                            document.querySelector('#psiegesitinescf1').style.display = 'block';
                                            document.querySelector('#iddeptranscf2').style.display = 'block';
                                            document.querySelector('#transitedepargarecf2').style.display = 'block';
                                            document.querySelector('#iddeptranscf3').style.display = 'block';
                                            document.querySelector('#transitedepargarecf3').style.display = 'block';
                                        }if(i === 4){

                                            document.querySelector('#idcheminscf').style.display = 'block';
                                            document.querySelector('#heureitincf').style.display = 'block';
                                            document.querySelector('#hdepartitinecf').style.display = 'block';
                                            document.querySelector('#siegitinecf').style.display = 'block';
                                            document.querySelector('#psiegesitinescf').style.display = 'block';
                                            document.querySelector('#depsieg').style.display = 'block';
                                            document.querySelector('#quartiercf1').style.display = 'block';
                                            document.querySelector('#idquartcf1').style.display = 'block';
                                            document.querySelector('#lignecf1').style.display = 'block';
                                            document.querySelector('#lignesitinerairecf').style.display = 'block';
                                            document.querySelector('#heured').style.display = 'block';
                                            document.querySelector('#iddeptranscf1').style.display = 'block';
                                            document.querySelector('#arritincf1').style.display = 'block';
                                            document.querySelector('#transitedepargarecf1').style.display = 'block';


                                            document.querySelector('#arritincf2').style.display = 'block';
                                            document.querySelector('#idcheminscf1').style.display = 'block';
                                            
                                            document.querySelector('#idcheminscf2').style.display = 'block';
                                            document.querySelector('#idquartcf2').style.display = 'block';
                                            document.querySelector('#quartiercf2').style.display = 'block';
                                            document.querySelector('#heureitincf1').style.display = 'block';
                                            document.querySelector('#idcheminsheurcf').style.display = 'block';
                                            document.querySelector('#siegitinecf1').style.display = 'block';
                                            document.querySelector('#psiegesitinescf1').style.display = 'block';
                                            document.querySelector('#iddeptranscf2').style.display = 'block';
                                            document.querySelector('#transitedepargarecf2').style.display = 'block';


                                            document.querySelector('#arritincf3').style.display = 'block';
                                            document.querySelector('#idquartcf3').style.display = 'block';
                                            document.querySelector('#quartiercf3').style.display = 'block';
                                            document.querySelector('#heureitincf2').style.display = 'block';
                                            document.querySelector('#idcheminsheurcf1').style.display = 'block';
                                            document.querySelector('#siegitinecf2').style.display = 'block';
                                            document.querySelector('#psiegesitinescf2').style.display = 'block';
                                            document.querySelector('#iddeptranscf3').style.display = 'block';
                                            document.querySelector('#transitedepargarecf3').style.display = 'block';
                                            document.querySelector('#iddeptranscf4').style.display = 'block';
                                            document.querySelector('#transitedepargarecf4').style.display = 'block';
                                        }
                                        document.querySelector('#trancf').style.display = 'block';
                                        document.querySelector('#heureitincf').style.display = 'block';
                                        document.querySelector('#hdepartitinecf').style.display = 'block';
                                        document.querySelector('#lignesitinerairecf').style.display = 'block';
                                        document.querySelector('#lignecf1').style.display = 'block';
                                        document.querySelector('#siegitinecf').style.display = 'block';
                                        document.querySelector('#psiegesitinescf').style.display = 'block';
                                        document.querySelector('#heured').style.display = 'block';
                    
                                        document.querySelector('#itinecodecf').value = `${donitinescf[0].code_itineraires}`;

                                        document.querySelector('#idcompgcf').value = `${donitinescf[0].id_compaga}`;
                                        document.querySelector('#lignetinerairecf').value = `${donitinescf[0].nom_itineraires}`;
                                    }
                        
                                    if(i === 2)
                                    {
                                        let opt = document.createElement('option');
                                        opt.value = `${donitinescf[1].code_itineraires}`;
                                        opt.innerHTML = `${donitinescf[1].nom_itineraires}`;
                                        document.querySelector('#idcheminscf').add(opt);

                                        document.querySelector('#lignesitinerairecf').value = `${donitinescf[0].nom_itineraires}`;
                                        document.querySelector('#itinecodescf').value = `${donitinescf[0].id_lignes}`;
                                        document.querySelector('#idcompgcf').value = `${donitinescf[0].id_compaga}`;
                                        document.querySelector('#idcompgcf1').value = `${donitinescf[1].id_compaga}`;
                                        var typgarecf1 = document.querySelector('#itinecodecf').value;
                                        var post_typgarecf1 = typgarecf1.split('-');
                                        var seltypgarecf1 = post_typgarecf1[0];
                                        var typgareselcf = post_typgarecf1[1];
                                            let httptypequartcf1;
                                            httptypequartcf1 = new XMLHttpRequest();
                                            
                                            httptypequartcf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgareselcf}`, true);
                                            httptypequartcf1.onload = () => 
                                            {
                                                const donquacf1 = JSON.parse(httptypequartcf1.responseText);
                                                if (donquacf1 == '') {
                                                    document.querySelector('#quartiercf1').options.length = 1;
                                                }
                                                else{
                                                    if (Object.entries(donquacf1).length >= 1) {
                                                                    
                                                        for (let key in Object.entries(donquacf1)) {
                                                            let optq = document.createElement('option');
                                                            optq.value = `${donquacf1[key].nom_quartier}`;
                                                            optq.innerHTML = `${donquacf1[key].nom_quartier}`;
                                                            document.querySelector('#quartiercf1').add(optq);
                                                        }
                                                    } else {
                                                        document.querySelector('#quartiercf1').options.length = 1;
                                                    }
                                                }
                                                

                                            };
                                            httptypequartcf1.setRequestHeader('Content-Type', 'application/json');
                                            httptypequartcf1.send();

                                            let httptypequartitincf;
                                            httptypequartitincf = new XMLHttpRequest();
                                            var itinprocf = document.querySelector('#itinecodecf').value;
                                            var datedepartcf = document.querySelector('#actuel').value;
                                            httptypequartitincf.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfprog/${itinprocf}/${datedepartcf}`, true);
                                            httptypequartitincf.onload = () => 
                                            {
                                                const infositincf = JSON.parse(httptypequartitincf.responseText);
                                                if (infositincf == null) 
                                                {

                                                }
                                                if (Object.entries(infositincf).length >= 1) 
                                                {


                                                        for (let key in Object.entries(infositincf)) {
                                                            let opt = document.createElement('option');
                                                            opt.value = `${infositincf[key].code_progr}/${infositincf[key].typetarif}/${infositincf[key].id_ligneheure}`;
                                                            opt.innerHTML = `${infositincf[key].heure}/${infositincf[key].date_progr}`;
                                                            document.querySelector('#heured').add(opt);
                                                        }
                                                } else 
                                                {
                                                    document.querySelector('#heured').options.length = 1;
                                                }
                                            };
                                            httptypequartitincf.setRequestHeader('Content-Type', 'application/json');
                                            httptypequartitincf.send();
                                        let hrdepartinecf = document.querySelector('#heured');
                                        if (hrdepartinecf !== null) {
                                            hrdepartinecf.onchange = () => 
                                            {   
                                                
                                                const httpsousgarecf = new XMLHttpRequest();
                                                httpsousgarecf.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${seltypgarecf1}`, true);
                                                httpsousgarecf.onload = () => 
                                                {
                                                    const donsousgcf = JSON.parse(httpsousgarecf.responseText);
                                                    console.debug(`${typeof donsousgcf}-${donsousgcf.attributes}`, console.memory);
                                                    if (Object.entries(donsousgcf).length >= 1) {
                                                        for (let key in Object.entries(donsousgcf)) 
                                                        {
                                                            let opt = document.createElement('option');
                                                            opt.value = `${donsousgcf[key].idsousgare}`;
                                                            opt.innerHTML = `${donsousgcf[key].nomsousgare}`;
                                                            document.querySelector('#transitedepargarecf1').add(opt);

                                                        }
                                                    }
                                                };
                                                httpsousgarecf.setRequestHeader('Content-Type', 'application/json');
                                                httpsousgarecf.send();

                                                document.querySelector('#psiegesitinescf').options.length = 1;
                                                const httpRequestitcf = new XMLHttpRequest();
                                                const seleitinecf = document.querySelector('#heured')
                                                    .options[document.querySelector('#heured').options.selectedIndex].value;

                                                    var post_lhitinecf = seleitinecf.split('/');
                                                    var selitinecf = post_lhitinecf[0];
                                                    var lhselitinecf = post_lhitinecf[1];

                                                    const dpt_dateitinecf = document.querySelector('#actuel').value;
                                                    var itinproitcf = document.querySelector('#itinecodecf').value;
                                                httpRequestitcf.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${selitinecf}`, true);
                                                httpRequestitcf.onload = () => 
                                                {
                                                    const donitcf = JSON.parse(httpRequestitcf.responseText);
                                                        console.debug(`${typeof donitcf} - ${donitcf.attributes}`, console.memory);

                                                        if (donitcf == '') 
                                                        {
                                                            
                                                                let opt = document.createElement('option');
                                                                opt.value = '';                                                             
                                                            
                                                        } 
                                                        else 
                                                        {       
                                                            if (Object.entries(donitcf).length >= 1) {
                                                                for (let key in Object.entries(donitcf)) {
                                                                    document.querySelector('#programtranscf').value = `${donitcf[key].code_progr}`;
                                                                    document.querySelector('#tarifattribcf').value = `${donitcf[key].typetarif}`;
                                                                    document.querySelector('#dateprtranscf').value = `${donitcf[key].date_progr}`;
                                                                    document.querySelector('#deplignetranscf').value = `${donitcf[key].gareidentif}`;
                                                                    document.querySelector('#intertranscf1').value = `${donitcf[key].intervalle1}`;
                                                                    document.querySelector('#intertranscf2').value = `${donitcf[key].intervalle2}`;
                                                                    document.querySelector('#ligntranscf').value = `${donitcf[key].ident_ligne}`;
                                                                    document.querySelector('#nomitintranscf').value = `${donitcf[key].nom_ligne}`;
                                                                    document.querySelector('#hertranscf').value = `${donitcf[key].heure}`;
                                                                    document.querySelector('#catetranscf').value = `${donitcf[key].categori}`;
                                                                        
                                                                }
                                                            } 
                                                            
                                                            const httpPrixitcf = new XMLHttpRequest();
                                                            const seleitinecf = document.querySelector('#heured')
                                                            .options[document.querySelector('#heured').options.selectedIndex].value;

                                                            var post_lhitinecf = seleitinecf.split('/');
                                                            var selitinecf = post_lhitinecf[0];
                                                            var lhselitinecf = post_lhitinecf[1];
                                                                var tfbscf = document.querySelector('#tarifattribcf').value;
                                                            httpPrixitcf.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifpriprg/${selitinecf}/${tfbscf}`, true);
                                                            httpPrixitcf.onload = () => 
                                                            {

                                                                const donprixitcf = JSON.parse(httpPrixitcf.responseText);
                                                                console.debug(`${typeof donprixitcf}-${donprixitcf.attributes}`, console.memory);
                                                                if (Object.entries(donprixitcf).length >= 1) {
                                                                    for (let key in Object.entries(donprixitcf)) 
                                                                    {
                                                                        document.querySelector('#prix_axetranscf').value = `${donprixit[key].prix}`;
            
                                                                    }
                                                                }
                                                            };
                                                            httpPrixitcf.setRequestHeader('Content-Type', 'application/json');
                                                            httpPrixitcf.send();
                                                            
                                                            
                                                            
                                                            const httpRequetteitcf = new XMLHttpRequest();
                                                            const cdprogitcf = document.querySelector('#programtranscf').value;
                                                            const dbitcf = document.querySelector('#intertranscf1').value;
                                                            const fnitcf = document.querySelector('#intertranscf2').value;
                                                            const lgitcf = document.querySelector('#nomitintranscf').value;
                                                            const timitcf = document.querySelector('#hertranscf').value;
                                                            const dpt_dateitinecf = document.querySelector('#actuel').value;
                                                            const dpt_dateitineecf = document.querySelector('#dateprtranscf').value;
                                                            httpRequetteitcf.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${cdprogitcf}/${dpt_dateitineecf}/${lgitcf}/${timitcf}/${dbitcf}/${fnitcf}`, true);
                                                            httpRequetteitcf.onload = () => {
                                                                const dattaitcf = JSON.parse(httpRequetteitcf.responseText);
                                                                console.debug(`${typeof dattaitcf} - ${dattaitcf.attributes}`, console.memory);
                                                                if (Object.entries(dattaitcf).length >= 1) {
                                                                    for (let key in Object.entries(dattaitcf)) {
                                                                        
                                                                        let opt = document.createElement('option');
                                                                        opt.value = `${dattaitcf[key].siege_num}`;
                                                                        opt.innerHTML = `${dattaitcf[key].siege_num}`;
                                                                        document.querySelector('#depsieg').add(opt);
                                                                        
                                                                    }
                                                                    
                                                                } else {
                                                                    document.querySelector('#depsieg').options.length = 1;
                                                                }
                                                            };
                                                            httpRequetteitcf.setRequestHeader('Content-Type', 'application/json');
                                                            httpRequetteitcf.send();

                                                        }  
                                                        
                                                };
                                                httpRequestitcf.setRequestHeader('Content-Type', 'application/json');
                                                httpRequestitcf.send();
                                                 
                                            };
                                            
                                    
                                        }
                                        progsiegestranscf = document.querySelector('#depsieg');
                                        if (progsiegestranscf !== null) {
                                            progsiegestranscf.onchange = () => 
                                            {

                                                gareidentiftranscf = document.querySelector('#deplignetranscf').value;
                                                    const httpsousgarecf = new XMLHttpRequest();
                                                    httpsousgarecf.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftranscf}`, true);
                                                    httpsousgarecf.onload = () => 
                                                    {
                                                        const donsousgcf = JSON.parse(httpsousgarecf.responseText);
                                                        console.debug(`${typeof donsousgcf}-${donsousgcf.attributes}`, console.memory);
                                                        if (Object.entries(donsousgcf).length >= 1) {
                                                            for (let key in Object.entries(donsousgcf)) 
                                                            {
                                                                let opt = document.createElement('option');
                                                                opt.value = `${donsousgcf[key].idsousgare}`;
                                                                opt.innerHTML = `${donsousgcf[key].nomsousgare}`;
                                                                document.querySelector('#transitedepargarecf1').add(opt);

                                                            }
                                                        }
                                                    };
                                                    httpsousgarecf.setRequestHeader('Content-Type', 'application/json');
                                                    httpsousgarecf.send();
                                                let httpSiegestranscf;
                                                httpSiegestranscf = new XMLHttpRequest();
                                                const sigstranscf = document.querySelector('#depsieg')
                                                .options[document.querySelector('#depsieg').options.selectedIndex].value;
                                                const prostranscf = document.querySelector('#programtranscf').value;

                                                httpSiegestranscf.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${prostranscf}/${sigstranscf}`, true);
                                                httpSiegestranscf.onload = () => 
                                                {
                                                    const donsgetranscf = JSON.parse(httpSiegestranscf.responseText);
                                                    console.debug(`${typeof donsgetranscf} - ${donsgetranscf.attributes}`, console.memory);
                                                    if(donsgetranscf == '')
                                                    {
                                                        let httpSiegstranscf;
                                                        httpSiegstranscf = new XMLHttpRequest();

                                                        httpSiegstranscf.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${prostranscf}/${sigstranscf}`, true);
                                                        httpSiegstranscf.onload = () => 
                                                        {
                                                            const dongtranscf = JSON.parse(httpSiegstranscf.responseText);
                                                            document.querySelector('#messconf').style.display = 'none';
                                                            if (Object.entries(dongtranscf).length >= 1)
                                                                {
                                                                    for (let key in Object.entries(dongtranscf)) {
                                                                        document.querySelector('#idtampotranscf').value = `${dongtranscf[key].idtamp}`;                    
                                                                        document.querySelector('#siegselecttranscf').value = `${dongtranscf[key].numsieg}`;
                                                                    }
                                                                }
                                                        };
                                                        httpSiegstranscf.setRequestHeader('Content-Type', 'application/json');
                                                        httpSiegstranscf.send();
                                                    }
                                                    else {
                                                        document.querySelector('#depsieg').value = '';     
                                                        if (Object.entries(donsgetranscf).length >= 1)
                                                        {
                                                            for (let key in Object.entries(donsgetranscf)) {
                                                                document.querySelector('#idtampotranscf').value = `${donsgetranscf[key].idtamp}`;                    
                                                                document.querySelector('#siegselecttranscf').value = `${donsgetranscf[key].numsieg}`;
                                                            }

                                                        }
                                                        document.querySelector('#messconf').style.display = 'block';
                                                        document.querySelector('#erreurMessconf').innerHTML = `Siege déjà utilisé.`;
                                                    }
                                                };
                                                httpSiegestranscf.setRequestHeader('Content-Type', 'application/json');
                                                httpSiegestranscf.send();

                                            
                                            };
                                        }

                                        let progchemincf = document.querySelector('#idcheminscf');
                                        if (progchemincf !== null) 
                                        {
                                            progchemincf.onchange = () => 
                                            {
                                                let httpSiegeschemincf;
                                                httpSiegeschemincf = new XMLHttpRequest();
                                                
                                                const prostranschemincf = document.querySelector('#idcheminscf')
                                                .options[document.querySelector('#idcheminscf').options.selectedIndex].value;

                                                var post_typgarecf2 = prostranschemincf.split('-');
                                                var seltypgarecf2 = post_typgarecf2[0];
                                                var typgareselcf1 = post_typgarecf2[1];
                                                
                                                var datedepartcf = document.querySelector('#actuel').value;
                                                httpSiegeschemincf.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranschemincf}/${datedepartcf}`, true);
                                                httpSiegeschemincf.onload = () => 
                                                {
                                        
                                                    const dongtranschemcf = JSON.parse(httpSiegeschemincf.responseText);
                                                    if (Object.entries(dongtranschemcf).length >= 1)
                                                    {
                                                        for (let key in Object.entries(dongtranschemcf)) {
                                                            let opt = document.createElement('option');
                                                            opt.value = `${dongtranschemcf[key].code_progr}/${dongtranschemcf[key].intervalle1}/${dongtranschemcf[key].intervalle2}/${dongtranschemcf[key].id_ligneheure}/${dongtranschemcf[key].prix}`;
                                                            opt.innerHTML = `${dongtranschemcf[key].heure}/${dongtranschemcf[key].date_progr}`;
                                                            document.querySelector('#hdepartitinecf').add(opt);
                                                        }
                                                    }
                                                };
                                                httpSiegeschemincf.setRequestHeader('Content-Type', 'application/json');
                                                httpSiegeschemincf.send();

                                            };
                                                let prochemintracf = document.querySelector('#hdepartitinecf');
                                            if (prochemintracf !== null)
                                                prochemintracf.onchange = () => 
                                                {  
                                                    const httpPrixittransitecf = new XMLHttpRequest();
                                                    const transselitinecf = document.querySelector('#hdepartitinecf')
                                                    .options[document.querySelector('#hdepartitinecf').options.selectedIndex].value;
                                                    var post_transcf = transselitinecf.split('/');
                                                    var itinetrascf = post_transcf[0];
                                                    var dbitracf = post_transcf[1];
                                                    var fnitracf = post_transcf[2];
                                                    var lhertracf = post_transcf[3];
                                                    var prixtracf = post_transcf[4];

                                                        httpPrixittransitecf.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetrascf}`, true);
                                                        httpPrixittransitecf.onload = () => 
                                                        {
                                                            const donprixitrancf = JSON.parse(httpPrixittransitecf.responseText);
                                                            console.debug(`${typeof donprixitrancf}-${donprixitrancf.attributes}`, console.memory);
                                                            if (Object.entries(donprixitrancf).length >= 1) {
                                                                for (let key in Object.entries(donprixitrancf)) 
                                                                {
                                                                    document.querySelector('#prix_axetransitcf').value = `${prixtracf}`;
                                                                    document.querySelector('#catetransitcf').value = `${donprixitrancf[key].categori}`;
                                                                    document.querySelector('#gidtranscf').value =  `${donprixitrancf[key].gareidentif}`;
                                                                    document.querySelector('#nomitintranscf1').value = `${donprixitrancf[key].nom_ligne}`;
                                                                    document.querySelector('#ligntranscf1').value = `${donprixitrancf[key].ident_ligne}`;

                                                                }
                                                            }
                                                        };
                                                        httpPrixittransitecf.setRequestHeader('Content-Type', 'application/json');
                                                        httpPrixittransitecf.send();
                                                        
                                                              
                                                            
                                                        const httpRequetteitracf = new XMLHttpRequest();
                                                
                                                        httpRequetteitracf.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetrascf}/${dbitracf}/${fnitracf}`, true);
                                                        httpRequetteitracf.onload = () => {
                                                            const dattaitracf = JSON.parse(httpRequetteitracf.responseText);
                                                            console.debug(`${typeof dattaitracf} - ${dattaitracf.attributes}`, console.memory);
                                                            if (Object.entries(dattaitracf).length >= 1) {
                                                                for (let key in Object.entries(dattaitracf)) {
                                                                    
                                                                    let opt = document.createElement('option');
                                                                    opt.value = `${dattaitracf[key].siege_num}`;
                                                                    opt.innerHTML = `${dattaitracf[key].siege_num}`;
                                                                    document.querySelector('#psiegesitinescf').add(opt);
                                                                    
                                                                }
                                                                
                                                            } else {
                                                                document.querySelector('#psiegesitinescf').options.length = 1;
                                                            }
                                                        };
                                                        httpRequetteitracf.setRequestHeader('Content-Type', 'application/json');
                                                        httpRequetteitracf.send();
                                                };

                                                progsiegescf1 = document.querySelector('#psiegesitinescf');
                                                if (progsiegescf1 !== null) 
                                                {
                                                    progsiegescf1.onchange = () => 
                                                    {
                                                        
                                                        const transselitinecf1 = document.querySelector('#hdepartitinecf')
                                                        .options[document.querySelector('#hdepartitinecf').options.selectedIndex].value;
                                                        var post_transcf1 = transselitinecf1.split('/');
                                                        var itinetrascf1 = post_transcf1[0];
                                                        
                                                        gareidentiftranscf2 = document.querySelector('#gidtranscf').value;
                                                        const httpsousgarecf1 = new XMLHttpRequest();
                                                        httpsousgarecf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftranscf2}`, true);
                                                        httpsousgarecf1.onload = () => 
                                                        {
                                                            const donsousgcf1 = JSON.parse(httpsousgarecf1.responseText);
                                                            console.debug(`${typeof donsousgcf1}-${donsousgcf1.attributes}`, console.memory);
                                                            if (Object.entries(donsousgcf1).length >= 1) {
                                                                for (let key in Object.entries(donsousgcf1)) 
                                                                {
                                                                    let opt = document.createElement('option');
                                                                    opt.value = `${donsousgcf1[key].idsousgare}`;
                                                                    opt.innerHTML = `${donsousgcf1[key].nomsousgare}`;
                                                                    document.querySelector('#transitedepargarecf2').add(opt);
        
                                                                }
                                                            }
                                                        };
                                                        httpsousgarecf1.setRequestHeader('Content-Type', 'application/json');
                                                        httpsousgarecf1.send();
                                                      
                                                        let httpSiegescf1;
                                                        httpSiegescf1 = new XMLHttpRequest();
                                                        const sigscf1 = document.querySelector('#psiegesitinescf')
                                                        .options[document.querySelector('#psiegesitinescf').options.selectedIndex].value;

                                                        httpSiegescf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetrascf1}/${sigscf1}`, true);
                                                        httpSiegescf1.onload = () => 
                                                        {
                                                            const donsgecf1 = JSON.parse(httpSiegescf1.responseText);
                                                            console.debug(`${typeof donsgecf1} - ${donsgecf1.attributes}`, console.memory);
                                                            if(donsgecf1 == '')
                                                            {
                                                                let httpSiegscf1;
                                                                httpSiegscf1 = new XMLHttpRequest();

                                                                httpSiegscf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetrascf1}/${sigscf1}`, true);
                                                                httpSiegscf1.onload = () => 
                                                                {
                                                                    const dongcf1 = JSON.parse(httpSiegscf1.responseText);
                                                                    document.querySelector('#messconf').style.display = 'none';
                                                                    if (Object.entries(dongcf1).length >= 1)
                                                                    {
                                                                        for (let key in Object.entries(dongcf1)) {
                                                                            document.querySelector('#idtampocf1').value = `${dongcf1[key].idtamp}`;                    
                                                                            document.querySelector('#siegselectcf1').value = `${dongcf1[key].numsieg}`;
                                                                        }
                                                                    }
                                                                };
                                                                httpSiegscf1.setRequestHeader('Content-Type', 'application/json');
                                                                httpSiegscf1.send();
                                                            }
                                                            else {
                                                                document.querySelector('#psiegesitinescf').value = '';     
                                                                if (Object.entries(donsgecf1).length >= 1)
                                                                {
                                                                    for (let key in Object.entries(donsgecf1)) {
                                                                        document.querySelector('#idtampocf1').value = `${donsgecf1[key].idtamp}`;                    
                                                                        document.querySelector('#siegselectcf1').value = `${donsgecf1[key].numsieg}`;
                                                                    }

                                                                }
                                                                document.querySelector('#messconf').style.display = 'block';
                                                                document.querySelector('#erreurMessconf').innerHTML = `Siege déjà utilisé.`;
                                                            }
                                                        };
                                                        httpSiegescf1.setRequestHeader('Content-Type', 'application/json');
                                                        httpSiegescf1.send();

                                                    };
                                                }
                                        }               
                                    }
                                    //second itineraire
                                    if(i === 3)
                                    {
                                        let opt = document.createElement('option');
                                        opt.value = `${donitinescf[1].code_itineraires}`;
                                        opt.innerHTML = `${donitinescf[1].nom_itineraires}`;
                                        
                                        document.querySelector('#idcheminscf').add(opt);

                                        document.querySelector('#lignesitinerairecf').value = `${donitinescf[0].nom_itineraires}`;
                                        document.querySelector('#itinecodescf').value = `${donitinescf[0].id_lignes}`;
                                        document.querySelector('#idcompgcf').value = `${donitinescf[0].id_compaga}`;

                                        let opt1 = document.createElement('option');
                                        opt1.value = `${donitinescf[2].code_itineraires}`;
                                        opt1.innerHTML = `${donitinescf[2].nom_itineraires}`;
                                        document.querySelector('#idcheminscf1').add(opt1);

                                        document.querySelector('#idcompgcf1').value = `${donitinescf[1].id_compaga}`;
                                        document.querySelector('#idcompgcf2').value = `${donitinescf[2].id_compaga}`;
                                        var typgarecf1 = document.querySelector('#itinecodecf').value;
                                        var post_typgarecf1 = typgarecf1.split('-');
                                        var seltypgarecf1 = post_typgarecf1[0];
                                        var typgareselcf = post_typgarecf1[1];
                                            let httptypequartcf1;
                                            httptypequartcf1 = new XMLHttpRequest();
                                            
                                            httptypequartcf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgareselcf}`, true);
                                            httptypequartcf1.onload = () => 
                                            {
                                                const donquacf1 = JSON.parse(httptypequartcf1.responseText);
                                                if (donquacf1 == '') {
                                                    document.querySelector('#quartiercf1').options.length = 1;
                                                }
                                                else{
                                                    if (Object.entries(donquacf1).length >= 1) {
                                                                    
                                                        for (let key in Object.entries(donquacf1)) {
                                                            let optq = document.createElement('option');
                                                            optq.value = `${donquacf1[key].nom_quartier}`;
                                                            optq.innerHTML = `${donquacf1[key].nom_quartier}`;
                                                            document.querySelector('#quartiercf1').add(optq);
                                                        }
                                                    } else {
                                                        document.querySelector('#quartiercf1').options.length = 1;
                                                    }
                                                }
                                                

                                            };
                                            httptypequartcf1.setRequestHeader('Content-Type', 'application/json');
                                            httptypequartcf1.send();


                                            let httptypequartitincf1;
                                            httptypequartitincf1 = new XMLHttpRequest();
                                            var itinprocf1 = document.querySelector('#itinecodecf').value;
                                            var datedepartcf = document.querySelector('#actuel').value;
                                            httptypequartitincf1.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfprog/${itinprocf1}/${datedepartcf}`, true);
                                            httptypequartitincf1.onload = () => 
                                            {
                                                const infositincf1 = JSON.parse(httptypequartitincf1.responseText);
                                                if (infositincf1 == null) 
                                                {


                                                }
                                                if (Object.entries(infositincf1).length >= 1) 
                                                {
                                                        
                                                    
                                                    for (let key in Object.entries(infositincf1)) {
                                                            let opt = document.createElement('option');
                                                            opt.value = `${infositincf1[key].code_progr}/${infositincf1[key].typetarif}/${infositincf1[key].id_ligneheure}`;
                                                            opt.innerHTML = `${infositincf1[key].heure}/${infositincf1[key].date_progr}`;
                                                            document.querySelector('#heured').add(opt);
                                                        }
                                                } else {
                                                    document.querySelector('#heured').options.length = 1;
                                                }
                                            };
                                            httptypequartitincf1.setRequestHeader('Content-Type', 'application/json');
                                            httptypequartitincf1.send();
                                        let hrdepartinecf1 = document.querySelector('#heured');
                                        if (hrdepartinecf1 !== null) {
                                            hrdepartinecf1.onchange = () => 
                                            {
                                                var tfbscf1 = document.querySelector('#tarifattribcf').value;
                                                document.querySelector('#depsieg').options.length = 1;
                                                const httpRequestitcf1 = new XMLHttpRequest();
                                                const seleitinecf1 = document.querySelector('#heured')
                                                    .options[document.querySelector('#heured').options.selectedIndex].value;

                                                var post_lhitinecf1 = seleitinecf1.split('/');
                                                var selitinecf1 = post_lhitinecf1[0];
                                                var lhselitinecf1 = post_lhitinecf1[1];

                                                const dpt_dateitinecf1 = document.querySelector('#actuel').value;
                                                var itinproitcf1 = document.querySelector('#itinecodecf').value;
                                                httpRequestitcf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${selitinecf1}`, true);
                                                httpRequestitcf1.onload = () => 
                                                {
                                                    const donitcf1 = JSON.parse(httpRequestitcf1.responseText);
                                                        console.debug(`${typeof donitcf1} - ${donitcf1.attributes}`, console.memory);

                                                        if (donitcf1 == '') 
                                                        {
                                                            
                                                                let opt = document.createElement('option');
                                                                opt.value = '';                                                             
                                                                            
                                                        } 
                                                        else 
                                                        {       
                                                            if (Object.entries(donitcf1).length >= 1) {
                                                                for (let key in Object.entries(donitcf1)) {
                                                                    document.querySelector('#programtranscf').value = `${donitcf1[key].code_progr}`;
                                                                    document.querySelector('#dateprtranscf').value = `${donitcf1[key].date_progr}`;
                                                                    document.querySelector('#tarifattribcf').value = `${donitcf1[key].typetarif}`;
                                                                    document.querySelector('#deplignetranscf').value = `${donitcf1[key].gareidentif}`;
                                                                    document.querySelector('#intertranscf1').value = `${donitcf1[key].intervalle1}`;
                                                                    document.querySelector('#intertranscf2').value = `${donitcf1[key].intervalle2}`;
                                                                    document.querySelector('#ligntranscf').value = `${donitcf1[key].ident_ligne}`;
                                                                    document.querySelector('#nomitintranscf').value = `${donitcf1[key].nom_ligne}`;
                                                                    document.querySelector('#hertranscf').value = `${donitcf1[key].heure}`;
                                                                    document.querySelector('#catetranscf').value = `${donitcf1[key].categori}`;

                                                                }
                                                            } 
                                                            
                                                            const httpPrixitcf = new XMLHttpRequest();
                                                            const seleitinecf = document.querySelector('#heured')
                                                            .options[document.querySelector('#heured').options.selectedIndex].value;

                                                            var post_lhitinecf = seleitinecf.split('/');
                                                            var selitinecf = post_lhitinecf[0];
                                                            var lhselitinecf = post_lhitinecf[1];
                                                            var tfbscf2 = document.querySelector('#tarifattribcf').value;
                                                            httpPrixitcf.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifpriprg/${selitinecf}/${tfbscf2}`, true);
                                                            httpPrixitcf.onload = () => 
                                                            {
                                                                const donprixitcf = JSON.parse(httpPrixitcf.responseText);
                                                                console.debug(`${typeof donprixitcf}-${donprixitcf.attributes}`, console.memory);
                                                                if (Object.entries(donprixitcf).length >= 1) {
                                                                    for (let key in Object.entries(donprixitcf)) 
                                                                    {
                                                                        document.querySelector('#prix_axetranscf').value = `${donprixitcf[key].prix}`;
            
                                                                    }
                                                                }
                                                            };
                                                            httpPrixitcf.setRequestHeader('Content-Type', 'application/json');
                                                            httpPrixitcf.send();

                                                            

                                                            const httpRequetteitcf = new XMLHttpRequest();
                                                            const cdprogitcf = document.querySelector('#programtranscf').value;
                                                            const dbitcf = document.querySelector('#intertranscf1').value;
                                                            const fnitcf = document.querySelector('#intertranscf2').value;
                                                            const lgitcf = document.querySelector('#nomitintranscf').value;
                                                            const timitcf = document.querySelector('#hertranscf').value;
                                                            const dpt_dateitinecf = document.querySelector('#actuel').value;
                                                            const dpt_dateitineecf = document.querySelector('#dateprtranscf').value;
                                                            
                                                            httpRequetteitcf.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${cdprogitcf}/${dpt_dateitineecf}/${lgitcf}/${timitcf}/${dbitcf}/${fnitcf}`, true);
                                                            httpRequetteitcf.onload = () => {
                                                                const dattaitcf = JSON.parse(httpRequetteitcf.responseText);
                                                                console.debug(`${typeof dattaitcf} - ${dattaitcf.attributes}`, console.memory);
                                                                if (Object.entries(dattaitcf).length >= 1) {
                                                                    for (let key in Object.entries(dattaitcf)) {
                                                                        
                                                                        let opt = document.createElement('option');
                                                                        opt.value = `${dattaitcf[key].siege_num}`;
                                                                        opt.innerHTML = `${dattaitcf[key].siege_num}`;
                                                                        document.querySelector('#depsieg').add(opt);
                                                                        
                                                                    }
                                                                    
                                                                } else {
                                                                    document.querySelector('#depsieg').options.length = 1;
                                                                }
                                                            };
                                                            httpRequetteitcf.setRequestHeader('Content-Type', 'application/json');
                                                            httpRequetteitcf.send();

                                                        }  
                                                        
                                                };
                                                httpRequestitcf1.setRequestHeader('Content-Type', 'application/json');
                                                httpRequestitcf1.send();
                                                 
                                            }; 
                                    
                                        }
                                        let progsiegestranscf = document.querySelector('#depsieg');
                                        if (progsiegestranscf !== null) {
                                            progsiegestranscf.onchange = () => 
                                            {

                                                const gareidentiftranscf1 = document.querySelector('#deplignetranscf').value;
                                                const httpsousgarecf = new XMLHttpRequest();
                                                httpsousgarecf.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftranscf1}`, true);
                                                httpsousgarecf.onload = () => 
                                                {
                                                    const donsousgcf = JSON.parse(httpsousgarecf.responseText);
                                                    console.debug(`${typeof donsousgcf}-${donsousgcf.attributes}`, console.memory);
                                                    if (Object.entries(donsousgcf).length >= 1) {
                                                        for (let key in Object.entries(donsousgcf)) 
                                                        {
                                                            let opt = document.createElement('option');
                                                            opt.value = `${donsousgcf[key].idsousgare}`;
                                                            opt.innerHTML = `${donsousgcf[key].nomsousgare}`;
                                                            document.querySelector('#transitedepargarecf1').add(opt);

                                                        }
                                                    }
                                                };
                                                httpsousgarecf.setRequestHeader('Content-Type', 'application/json');
                                                httpsousgarecf.send();
                                                let httpSiegestranscf1;
                                                httpSiegestranscf1 = new XMLHttpRequest();
                                                const sigstranscf = document.querySelector('#depsieg')
                                                .options[document.querySelector('#depsieg').options.selectedIndex].value;
                                                const prostranscf = document.querySelector('#programtranscf').value;

                                                httpSiegestranscf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${prostranscf}/${sigstranscf}`, true);
                                                httpSiegestranscf1.onload = () => 
                                                {
                                                    const donsgetranscf = JSON.parse(httpSiegestranscf1.responseText);
                                                    console.debug(`${typeof donsgetranscf} - ${donsgetranscf.attributes}`, console.memory);
                                                    if(donsgetranscf == '')
                                                    {
                                                        let httpSiegstranscf;
                                                        httpSiegstranscf = new XMLHttpRequest();

                                                        httpSiegstranscf.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${prostranscf}/${sigstranscf}`, true);
                                                        httpSiegstranscf.onload = () => 
                                                        {
                                                            const dongtranscf = JSON.parse(httpSiegstranscf.responseText);
                                                            document.querySelector('#messconf').style.display = 'none';
                                                            if (Object.entries(dongtranscf).length >= 1)
                                                                {
                                                                    for (let key in Object.entries(dongtranscf)) {
                                                                        document.querySelector('#idtampotranscf').value = `${dongtranscf[key].idtamp}`;                    
                                                                        document.querySelector('#siegselecttranscf').value = `${dongtranscf[key].numsieg}`;
                                                                    }
                                                                }
                                                        };
                                                        httpSiegstranscf.setRequestHeader('Content-Type', 'application/json');
                                                        httpSiegstranscf.send();
                                                    }
                                                    else {
                                                        document.querySelector('#depsieg').value = '';     
                                                        if (Object.entries(donsgetranscf).length >= 1)
                                                        {
                                                            for (let key in Object.entries(donsgetranscf)) {
                                                                document.querySelector('#idtampotranscf').value = `${donsgetranscf[key].idtamp}`;                    
                                                                document.querySelector('#siegselecttranscf').value = `${donsgetranscf[key].numsieg}`;
                                                            }

                                                        }
                                                        document.querySelector('#messconf').style.display = 'block';
                                                        document.querySelector('#erreurMessconf').innerHTML = `Siege déjà utilisé.`;                                                                   
                                                    }
                                                };
                                                httpSiegestranscf1.setRequestHeader('Content-Type', 'application/json');
                                                httpSiegestranscf1.send();
                                            };                                                                
                                            
                                        }
                                        //premier transite
                                        let progchemincf = document.querySelector('#idcheminscf');
                                        if (progchemincf !== null) 
                                        {
                                            progchemincf.onchange = () => 
                                            {
                                                
                                                const prostranschemincf = document.querySelector('#idcheminscf')
                                                .options[document.querySelector('#idcheminscf').options.selectedIndex].value;

                                                var post_typgarecf2 = prostranschemincf.split('-');
                                                var seltypgarecf2 = post_typgarecf2[0];
                                                var typgareselcf1 = post_typgarecf2[1];
                                                let httptypequartcf2;
                                                httptypequartcf2 = new XMLHttpRequest();
                                                
                                                httptypequartcf2.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgareselcf1}`, true);
                                                httptypequartcf2.onload = () => 
                                                {
                                                    const donquacf2 = JSON.parse(httptypequartcf2.responseText);
                                                    if (donquacf2 == '') {
                                                        document.querySelector('#quartiercf2').options.length = 1;
                                                    }
                                                    else{
                                                        if (Object.entries(donquacf2).length >= 1) {
                                                                        
                                                            for (let key in Object.entries(donquacf2)) {
                                                                let optq1 = document.createElement('option');
                                                                optq1.value = `${donquacf2[key].nom_quartier}`;
                                                                optq1.innerHTML = `${donquacf2[key].nom_quartier}`;
                                                                document.querySelector('#quartiercf2').add(optq1);
                                                            }
                                                        } else {
                                                            document.querySelector('#quartiercf2').options.length = 1;
                                                        }
                                                    }
                                                    

                                                };
                                                httptypequartcf2.setRequestHeader('Content-Type', 'application/json');
                                                httptypequartcf2.send();

                                                let httpSiegeschemincf;
                                                httpSiegeschemincf = new XMLHttpRequest();

                                                var datedepartcf = document.querySelector('#actuel').value;
                                                
                                                httpSiegeschemincf.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranschemincf}/${datedepartcf}`, true);
                                                httpSiegeschemincf.onload = () => 
                                                {
                                        
                                                    const dongtranschemcf = JSON.parse(httpSiegeschemincf.responseText);
                                                        if (Object.entries(dongtranschemcf).length >= 1)
                                                        {
                                                            for (let key in Object.entries(dongtranschemcf)) {
                                                                let opt = document.createElement('option');
                                                                opt.value = `${dongtranschemcf[key].code_progr}/${dongtranschemcf[key].intervalle1}/${dongtranschemcf[key].intervalle2}/${dongtranschemcf[key].id_ligneheure}/${dongtranschemcf[key].prix}`;
                                                                opt.innerHTML = `${dongtranschemcf[key].heure}/${dongtranschemcf[key].date_progr}`;
                                                                document.querySelector('#hdepartitinecf').add(opt);
                                                            }
                                                        }
                                                };
                                                httpSiegeschemincf.setRequestHeader('Content-Type', 'application/json');
                                                httpSiegeschemincf.send();

                                            };
                                               let prochemintracf = document.querySelector('#hdepartitinecf');
                                            if (prochemintracf !== null)
                                                prochemintracf.onchange = () => 
                                                {  
                                                    const httpPrixittransitecf = new XMLHttpRequest();
                                                    const transselitinecf = document.querySelector('#hdepartitinecf')
                                                    .options[document.querySelector('#hdepartitinecf').options.selectedIndex].value;
                                                    var post_transcf = transselitinecf.split('/');
                                                    var itinetrascf = post_transcf[0];
                                                    var dbitracf = post_transcf[1];
                                                    var fnitracf = post_transcf[2];
                                                    var lhertracf = post_transcf[3];
                                                    var prixtracf = post_transcf[4];

                                                    httpPrixittransitecf.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetrascf}`, true);
                                                    httpPrixittransitecf.onload = () => 
                                                    {
                                                        const donprixitrancf = JSON.parse(httpPrixittransitecf.responseText);
                                                        console.debug(`${typeof donprixitrancf}-${donprixitrancf.attributes}`, console.memory);
                                                        if (Object.entries(donprixitrancf).length >= 1) {
                                                            for (let key in Object.entries(donprixitrancf)) 
                                                            {
                                                                document.querySelector('#prix_axetransitcf').value = `${prixtracf}`;
                                                                document.querySelector('#catetransitcf').value = `${donprixitrancf[key].categori}`;
                                                                document.querySelector('#gidtranscf').value =  `${donprixitrancf[key].gareidentif}`;
                                                                document.querySelector('#nomitintranscf1').value = `${donprixitrancf[key].nom_ligne}`; 
                                                                document.querySelector('#ligntranscf1').value = `${donprixitrancf[key].ident_ligne}`;
                                                            }
                                                        }
                                                    };
                                                    httpPrixittransitecf.setRequestHeader('Content-Type', 'application/json');
                                                    httpPrixittransitecf.send();

                                                    const httpRequetteitracf = new XMLHttpRequest();
                                            
                                                    httpRequetteitracf.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetrascf}/${dbitracf}/${fnitracf}`, true);
                                                    httpRequetteitracf.onload = () => {
                                                        const dattaitracf = JSON.parse(httpRequetteitracf.responseText);
                                                        console.debug(`${typeof dattaitracf} - ${dattaitracf.attributes}`, console.memory);
                                                        if (Object.entries(dattaitracf).length >= 1) {
                                                            for (let key in Object.entries(dattaitracf)) {
                                                                
                                                                let opt = document.createElement('option');
                                                                opt.value = `${dattaitracf[key].siege_num}`;
                                                                opt.innerHTML = `${dattaitracf[key].siege_num}`;
                                                                document.querySelector('#psiegesitinescf').add(opt);
                                                                
                                                            }
                                                            
                                                        } else {
                                                            document.querySelector('#psiegesitinescf').options.length = 1;
                                                        }
                                                    };
                                                    httpRequetteitracf.setRequestHeader('Content-Type', 'application/json');
                                                    httpRequetteitracf.send();
                                                };

                                                let progsiegescf1 = document.querySelector('#psiegesitinescf');
                                                if (progsiegescf1 !== null) 
                                                {
                                                    progsiegescf1.onchange = () => 
                                                    {

                                                        const  gareidentiftranscf2 = document.querySelector('#gidtranscf').value;
                                                            const httpsousgarecf1 = new XMLHttpRequest();
                                                            httpsousgarecf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftranscf2}`, true);
                                                            httpsousgarecf1.onload = () => 
                                                            {
                                                                const donsousgcf1 = JSON.parse(httpsousgarecf1.responseText);
                                                                console.debug(`${typeof donsousgcf1}-${donsousgcf1.attributes}`, console.memory);
                                                                if (Object.entries(donsousgcf1).length >= 1) {
                                                                    for (let key in Object.entries(donsousgcf1)) 
                                                                    {
                                                                        let opt = document.createElement('option');
                                                                        opt.value = `${donsousgcf1[key].idsousgare}`;
                                                                        opt.innerHTML = `${donsousgcf1[key].nomsousgare}`;
                                                                        document.querySelector('#transitedepargarecf2').add(opt);
            
                                                                    }
                                                                }
                                                            };
                                                            httpsousgarecf1.setRequestHeader('Content-Type', 'application/json');
                                                            httpsousgarecf1.send();
                                                         const transselitinecf1 = document.querySelector('#hdepartitinecf')
                                                        .options[document.querySelector('#hdepartitinecf').options.selectedIndex].value;
                                                        var post_transcf1 = transselitinecf1.split('/');
                                                        var itinetrascf1 = post_transcf1[0];
                                            
                                                        

                                                        let httpSiegescf1;
                                                        httpSiegescf1 = new XMLHttpRequest();
                                                        const sigscf1 = document.querySelector('#psiegesitinescf')
                                                        .options[document.querySelector('#psiegesitinescf').options.selectedIndex].value;

                                                        httpSiegescf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetrascf1}/${sigscf1}`, true);
                                                        httpSiegescf1.onload = () => 
                                                        {
                                                            const donsgecf1 = JSON.parse(httpSiegescf1.responseText);
                                                            console.debug(`${typeof donsgecf1} - ${donsgecf1.attributes}`, console.memory);
                                                            if(donsgecf1 == '')
                                                            {
                                                                let httpSiegscf1;
                                                                httpSiegscf1 = new XMLHttpRequest();

                                                                httpSiegscf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetrascf1}/${sigscf1}`, true);
                                                                httpSiegscf1.onload = () => 
                                                                {
                                                                    const dongcf1 = JSON.parse(httpSiegscf1.responseText);
                                                                    document.querySelector('#messconf').style.display = 'none';
                                                                    if (Object.entries(dongcf1).length >= 1)
                                                                        {
                                                                            for (let key in Object.entries(dongcf1)) {
                                                                                document.querySelector('#idtampocf1').value = `${dongcf1[key].idtamp}`;                    
                                                                                document.querySelector('#siegselectcf1').value = `${dongcf1[key].numsieg}`;
                                                                            }
                                                                        }
                                                                };
                                                                httpSiegscf1.setRequestHeader('Content-Type', 'application/json');
                                                                httpSiegscf1.send();
                                                            }
                                                            else {
                                                                document.querySelector('#psiegesitinescf').value = '';     
                                                                if (Object.entries(donsgecf1).length >= 1)
                                                                {
                                                                    for (let key in Object.entries(donsgecf1)) {
                                                                        document.querySelector('#idtampocf1').value = `${donsgecf1[key].idtamp}`;                    
                                                                        document.querySelector('#siegselectcf1').value = `${donsgecf1[key].numsieg}`;
                                                                    }

                                                                }
                                                                document.querySelector('#messconf').style.display = 'block';
                                                                document.querySelector('#erreurMessconf').innerHTML = `Siege déjà utilisé.`;
                                                            }
                                                        };
                                                        httpSiegescf1.setRequestHeader('Content-Type', 'application/json');
                                                        httpSiegescf1.send();

                                                    };
                                                }
                                        }
                                        let progchemincf1 = document.querySelector('#idcheminscf1');
                                        if (progchemincf1 !== null) 
                                        {
                                            progchemincf1.onchange = () => 
                                            {
                                               
                                                const prostranschemincf32 = document.querySelector('#idcheminscf1')
                                                .options[document.querySelector('#idcheminscf1').options.selectedIndex].value;

                                                var post_typgarecf32 = prostranschemincf32.split('-');
                                                var seltypgarecf32 = post_typgarecf32[0];
                                                var typgareselcf31 = post_typgarecf32[1];
                                                
                                                
                                                let httpSiegeschemincf1;
                                                httpSiegeschemincf1 = new XMLHttpRequest();

                                                var datedepartcf = document.querySelector('#actuel').value;
                                                const prostranschemincf1 = document.querySelector('#idcheminscf1')
                                                .options[document.querySelector('#idcheminscf1').options.selectedIndex].value;

                                                httpSiegeschemincf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranschemincf1}/${datedepartcf}`, true);
                                                httpSiegeschemincf1.onload = () => 
                                                {
                                        
                                                    const dongtranschemcf1 = JSON.parse(httpSiegeschemincf1.responseText);
                                                    if (Object.entries(dongtranschemcf1).length >= 1)
                                                    {
                                                        for (let key in Object.entries(dongtranschemcf1)) {
                                                            let opt = document.createElement('option');
                                                            opt.value = `${dongtranschemcf1[key].code_progr}/${dongtranschemcf1[key].intervalle1}/${dongtranschemcf1[key].intervalle2}/${dongtranschemcf1[key].id_ligneheure}/${dongtranschemcf1[key].prix}`;
                                                            opt.innerHTML = `${dongtranschemcf1[key].heure}/${dongtranschemcf1[key].date_progr}`;
                                                            document.querySelector('#idcheminsheurcf').add(opt);
                                                        }
                                                    }
                                                };
                                                httpSiegeschemincf1.setRequestHeader('Content-Type', 'application/json');
                                                httpSiegeschemincf1.send();

                                            };
                                              let prochemintracf1 = document.querySelector('#idcheminsheurcf');
                                            if (prochemintracf1 !== null)
                                                prochemintracf1.onchange = () => 
                                                {  
                                                    const httpPrixittransitecf1 = new XMLHttpRequest();
                                                    const transselitinecf1 = document.querySelector('#idcheminsheurcf')
                                                    .options[document.querySelector('#idcheminsheurcf').options.selectedIndex].value;
                                                    var post_transcf1 = transselitinecf1.split('/');
                                                    var itinetrascf1 = post_transcf1[0];
                                                    var dbitracf1 = post_transcf1[1];
                                                    var fnitracf1 = post_transcf1[2];
                                                    var lhertracf1 = post_transcf1[3];
                                                    var prixtracf1 = post_transcf1[4];

                                                        httpPrixittransitecf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetrascf1}`, true);
                                                        httpPrixittransitecf1.onload = () => 
                                                        {
                                                            const donprixitrancf1 = JSON.parse(httpPrixittransitecf1.responseText);
                                                            if (Object.entries(donprixitrancf1).length >= 1) {
                                                                for (let key in Object.entries(donprixitrancf1)) 
                                                                {
                                                                    document.querySelector('#prix_axetransitcf1').value = `${prixtracf1}`;
                                                                    document.querySelector('#catetransitcf1').value = `${donprixitrancf1[key].categori}`;
                                                                    document.querySelector('#gidtranscf1').value =  `${donprixitrancf1[key].gareidentif}`;
                                                                    document.querySelector('#nomitintranscf2').value = `${donprixitrancf1[key].nom_ligne}`;
                                                                    document.querySelector('#ligntranscf2').value = `${donprixitrancf1[key].ident_ligne}`;
                                                                }
                                                            }
                                                        };
                                                        httpPrixittransitecf1.setRequestHeader('Content-Type', 'application/json');
                                                        httpPrixittransitecf1.send();
                                              
                                                      
                                                       
                                                        const httpRequetteitracf1 = new XMLHttpRequest();
                                                
                                                        httpRequetteitracf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetrascf1}/${dbitracf1}/${fnitracf1}`, true);
                                                        httpRequetteitracf1.onload = () => {
                                                            const dattaitracf1 = JSON.parse(httpRequetteitracf1.responseText);
                                                            console.debug(`${typeof dattaitracf1} - ${dattaitracf1.attributes}`, console.memory);
                                                            if (Object.entries(dattaitracf1).length >= 1) {
                                                                for (let key in Object.entries(dattaitracf1)) {
                                                                    
                                                                    let opte = document.createElement('option');
                                                                    opte.value = `${dattaitracf1[key].siege_num}`;
                                                                    opte.innerHTML = `${dattaitracf1[key].siege_num}`;
                                                                    document.querySelector('#psiegesitinescf1').add(opte);
                                                                    
                                                                }
                                                                
                                                            } else {
                                                                document.querySelector('#psiegesitinescf1').options.length = 1;
                                                            }
                                                        };
                                                        httpRequetteitracf1.setRequestHeader('Content-Type', 'application/json');
                                                        httpRequetteitracf1.send();
                                                };

                                                let progsiegescf2 = document.querySelector('#psiegesitinescf1');
                                                if (progsiegescf2 !== null) 
                                                {
                                                    progsiegescf2.onchange = () => 
                                                    {
                                                            const transselitinecf2 = document.querySelector('#idcheminsheurcf')
                                                        .options[document.querySelector('#idcheminsheurcf').options.selectedIndex].value;
                                                        var post_transcf2 = transselitinecf2.split('/');
                                                        var itinetrascf2 = post_transcf2[0];
                                                            
                                                            const gareidentiftranscf4 = document.querySelector('#gidtranscf1').value;
                                                            const httpsousgarecf4 = new XMLHttpRequest();
                                                            httpsousgarecf4.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftranscf4}`, true);
                                                            httpsousgarecf4.onload = () => 
                                                            {
                                                                const donsousgcf4 = JSON.parse(httpsousgarecf4.responseText);
                                                                console.debug(`${typeof donsousgcf4}-${donsousgcf4.attributes}`, console.memory);
                                                                if (Object.entries(donsousgcf4).length >= 1) {
                                                                    for (let key in Object.entries(donsousgcf4)) 
                                                                    {
                                                                        let opt = document.createElement('option');
                                                                        opt.value = `${donsousgcf4[key].idsousgare}`;
                                                                        opt.innerHTML = `${donsousgcf4[key].nomsousgare}`;
                                                                        document.querySelector('#transitedepargarecf3').add(opt);
            
                                                                    }
                                                                }
                                                            };

                                                            httpsousgarecf4.setRequestHeader('Content-Type', 'application/json');
                                                            httpsousgarecf4.send();

                                                        let httpSiegescf2;
                                                        httpSiegescf2 = new XMLHttpRequest();
                                                        const sigscf2 = document.querySelector('#psiegesitinescf1')
                                                        .options[document.querySelector('#psiegesitinescf1').options.selectedIndex].value;

                                                        httpSiegescf2.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetrascf2}/${sigscf2}`, true);
                                                        httpSiegescf2.onload = () => 
                                                        {
                                                            const donsgecf2 = JSON.parse(httpSiegescf2.responseText);
                                                            if(donsgecf2 == '')
                                                            {
                                                                let httpSiegscf2;
                                                                httpSiegscf2 = new XMLHttpRequest();

                                                                httpSiegscf2.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetrascf2}/${sigscf2}`, true);
                                                                httpSiegscf2.onload = () => 
                                                                {
                                                                    const dongcf2 = JSON.parse(httpSiegscf2.responseText);
                                                                    document.querySelector('#messconf').style.display = 'none';
                                                                    if (Object.entries(dongcf2).length >= 1)
                                                                        {
                                                                            for (let key in Object.entries(dongcf2)) {
                                                                                document.querySelector('#idtampocf2').value = `${dongcf2[key].idtamp}`;                    
                                                                                document.querySelector('#siegselectcf2').value = `${dongcf2[key].numsieg}`;
                                                                            }
                                                                        }
                                                                };
                                                                httpSiegscf2.setRequestHeader('Content-Type', 'application/json');
                                                                httpSiegscf2.send();
                                                            }
                                                            else {
                                                                document.querySelector('#psiegesitinescf1').value = '';     
                                                                if (Object.entries(donsgecf2).length >= 1)
                                                                {
                                                                    for (let key in Object.entries(donsgecf2)) {
                                                                        document.querySelector('#idtampocf2').value = `${donsgecf2[key].idtamp}`;                    
                                                                        document.querySelector('#siegselectcf2').value = `${donsgecf2[key].numsieg}`;
                                                                    }

                                                                }
                                                                document.querySelector('#messconf').style.display = 'block';
                                                                document.querySelector('#erreurMessconf').innerHTML = `Siege déjà utilisé.`;                                                                   
                                                            }
                                                        };
                                                        httpSiegescf2.setRequestHeader('Content-Type', 'application/json');
                                                        httpSiegescf2.send();

                                                    };
                                                }
                                        }               
                                    }

                                    //troisieme itineraire
                                    if(i === 4)
                                    {
                                        let opt = document.createElement('option');
                                        opt.value = `${donitinescf[1].code_itineraires}`;
                                        opt.innerHTML = `${donitinescf[1].nom_itineraires}`;
                                        document.querySelector('#idcheminscf').add(opt);


                                        let opt1 = document.createElement('option');
                                        opt1.value = `${donitinescf[2].code_itineraires}`;
                                        opt1.innerHTML = `${donitinescf[2].nom_itineraires}`;
                                        document.querySelector('#idcheminscf1').add(opt1);

                                        let opt2 = document.createElement('option');
                                        opt2.value = `${donitinescf[3].code_itineraires}`;
                                        opt2.innerHTML = `${donitinescf[3].nom_itineraires}`;
                                        document.querySelector('#idcheminscf2').add(opt2);

                                        document.querySelector('#lignesitinerairecf').value = `${donitinescf[0].nom_itineraires}`;
                                       
                                        document.querySelector('#itinecodescf').value = `${donitinescf[0].id_lignes}`;
                                        document.querySelector('#idcompgcf').value = `${donitinescf[0].id_compaga}`;
                                        document.querySelector('#idcompgcf1').value = `${donitinescf[1].id_compaga}`;
                                        document.querySelector('#idcompgcf2').value = `${donitinescf[2].id_compaga}`;
                                        document.querySelector('#idcompgcf3').value = `${donitinescf[3].id_compaga}`;
                                        var typgarecf1 = document.querySelector('#itinecodecf').value;
                                        var post_typgarecf1 = typgarecf1.split('-');
                                        var seltypgarecf1 = post_typgarecf1[0];
                                        var typgareselcf = post_typgarecf1[1];
                                            let httptypequartcf1;
                                            httptypequartcf1 = new XMLHttpRequest();
                                            
                                            httptypequartcf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgareselcf}`, true);
                                            httptypequartcf1.onload = () => 
                                            {
                                                const donquacf1 = JSON.parse(httptypequartcf1.responseText);
                                                if (donquacf1 == '') {
                                                    document.querySelector('#quartiercf1').options.length = 1;
                                                }
                                                else{
                                                    if (Object.entries(donquacf1).length >= 1) {
                                                                    
                                                        for (let key in Object.entries(donquacf1)) {
                                                            let optq = document.createElement('option');
                                                            optq.value = `${donquacf1[key].nom_quartier}`;
                                                            optq.innerHTML = `${donquacf1[key].nom_quartier}`;
                                                            document.querySelector('#quartiercf1').add(optq);
                                                        }
                                                    } else {
                                                        document.querySelector('#quartiercf1').options.length = 1;
                                                    }
                                                }
                                                

                                            };
                                            httptypequartcf1.setRequestHeader('Content-Type', 'application/json');
                                            httptypequartcf1.send();


                                            let httptypequartitincf1;
                                            httptypequartitincf1 = new XMLHttpRequest();
                                            var datedepartcf = document.querySelector('#actuel').value;
                                            var itinprocf1 = document.querySelector('#itinecodecf').value;
                                            httptypequartitincf1.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfprog/${itinprocf1}/${datedepartcf}`, true);
                                            httptypequartitincf1.onload = () => 
                                            {
                                                const infositincf1 = JSON.parse(httptypequartitincf1.responseText);
                                                if (infositincf1 == null) 
                                                {


                                                }
                                                if (Object.entries(infositincf1).length >= 1) 
                                                {
                                                        
                                                    
                                                    for (let key in Object.entries(infositincf1)) {
                                                        let opt = document.createElement('option');
                                                        opt.value = `${infositincf1[key].code_progr}/${infositincf1[key].typetarif}/${infositincf1[key].id_ligneheure}`;
                                                        opt.innerHTML = `${infositincf1[key].heure}/${infositincf1[key].date_progr}`;
                                                        document.querySelector('#heured').add(opt);
                                                    }
                                                } else {
                                                    document.querySelector('#heured').options.length = 1;
                                                }
                                            };
                                            httptypequartitincf1.setRequestHeader('Content-Type', 'application/json');
                                            httptypequartitincf1.send();
                                        let hrdepartinecf1 = document.querySelector('#heured');
                                        if (hrdepartinecf1 !== null) {
                                            hrdepartinecf1.onchange = () => 
                                            {
                                                document.querySelector('#depsieg').options.length = 1;
                                                const httpRequestitcf1 = new XMLHttpRequest();
                                                const seleitinecf1 = document.querySelector('#heured')
                                                    .options[document.querySelector('#heured').options.selectedIndex].value;

                                                    var post_lhitinecf1 = seleitinecf1.split('/');
                                                    var selitinecf1 = post_lhitinecf1[0];
                                                    var lhselitinecf1 = post_lhitinecf1[1];
                                                    
                                                    const dpt_dateitinecf1 = document.querySelector('#actuel').value;
                                                    var itinproitcf1 = document.querySelector('#itinecodecf').value;
                                                httpRequestitcf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${selitinecf1}`, true);
                                                httpRequestitcf1.onload = () => 
                                                {
                                                    const donitcf1 = JSON.parse(httpRequestitcf1.responseText);
                                                        console.debug(`${typeof donitcf1} - ${donitcf1.attributes}`, console.memory);

                                                        if (donitcf1 == '') 
                                                        {
                                                            
                                                                let opt = document.createElement('option');
                                                                opt.value = '';                                                             
                                                           
                                                            
                                                            
                                                        } 
                                                        else 
                                                        {       
                                                            if (Object.entries(donitcf1).length >= 1) {
                                                                for (let key in Object.entries(donitcf1)) {
                                                                    document.querySelector('#programtranscf').value = `${donitcf1[key].code_progr}`;
                                                                    document.querySelector('#dateprtranscf').value = `${donitcf1[key].date_progr}`;
                                                                    document.querySelector('#tarifattribcf').value = `${donitcf1[key].typetarif}`;
                                                                    document.querySelector('#deplignetranscf').value = `${donitcf1[key].gareidentif}`;
                                                                    document.querySelector('#intertranscf1').value = `${donitcf1[key].intervalle1}`;
                                                                    document.querySelector('#intertranscf2').value = `${donitcf1[key].intervalle2}`;
                                                                    document.querySelector('#ligntranscf').value = `${donitcf1[key].ident_ligne}`;
                                                                    document.querySelector('#nomitintranscf').value = `${donitcf1[key].nom_ligne}`;
                                                                    document.querySelector('#hertranscf').value = `${donitcf1[key].heure}`;
                                                                    document.querySelector('#catetranscf').value = `${donitcf1[key].categori}`;

                                                                }
                                                            } 
                                                            
                                                            const httpPrixitcf = new XMLHttpRequest();
                                                            const seleitinecf = document.querySelector('#heured')
                                                            .options[document.querySelector('#heured').options.selectedIndex].value;

                                                            var post_lhitinecf = seleitinecf.split('/');
                                                            var selitinecf = post_lhitinecf[0];
                                                            var lhselitinecf = post_lhitinecf[1];
                                                                    var tfbscf1 = document.querySelector('#tarifattribcf').value;
                                                            httpPrixitcf.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifpriprg/${selitinecf}/${tfbscf1}`, true);
                                                            httpPrixitcf.onload = () => 
                                                            {
                                                                const donprixitcf = JSON.parse(httpPrixitcf.responseText);
                                                                console.debug(`${typeof donprixitcf}-${donprixitcf.attributes}`, console.memory);
                                                                if (Object.entries(donprixitcf).length >= 1) {
                                                                    for (let key in Object.entries(donprixitcf)) 
                                                                    {
                                                                        document.querySelector('#prix_axetranscf').value = `${donprixitcf[key].prix}`;
            
                                                                    }
                                                                }
                                                            };
                                                            httpPrixitcf.setRequestHeader('Content-Type', 'application/json');
                                                            httpPrixitcf.send();

                                                            

                                                            const httpRequetteitcf = new XMLHttpRequest();
                                                            const cdprogitcf = document.querySelector('#programtranscf').value;
                                                            const dbitcf = document.querySelector('#intertranscf1').value;
                                                            const fnitcf = document.querySelector('#intertranscf2').value;
                                                            const lgitcf = document.querySelector('#nomitintranscf').value;
                                                            const timitcf = document.querySelector('#hertranscf').value;
                                                            const dpt_dateitinecf = document.querySelector('#actuel').value;
                                                            const dpt_dateitinecef = document.querySelector('#dateprtranscf').value;
                                                                httpRequetteitcf.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${cdprogitcf}/${dpt_dateitinecef}/${lgitcf}/${timitcf}/${dbitcf}/${fnitcf}`, true);
                                                            httpRequetteitcf.onload = () => {
                                                                const dattaitcf = JSON.parse(httpRequetteitcf.responseText);
                                                                console.debug(`${typeof dattaitcf} - ${dattaitcf.attributes}`, console.memory);
                                                                if (Object.entries(dattaitcf).length >= 1) {
                                                                    for (let key in Object.entries(dattaitcf)) {
                                                                        
                                                                        let opt = document.createElement('option');
                                                                        opt.value = `${dattaitcf[key].siege_num}`;
                                                                        opt.innerHTML = `${dattaitcf[key].siege_num}`;
                                                                        document.querySelector('#depsieg').add(opt);
                                                                        
                                                                    }
                                                                    
                                                                } else {
                                                                    document.querySelector('#depsieg').options.length = 1;
                                                                }
                                                            };
                                                            httpRequetteitcf.setRequestHeader('Content-Type', 'application/json');
                                                            httpRequetteitcf.send();

                                                        }  
                                                        
                                                };
                                                httpRequestitcf1.setRequestHeader('Content-Type', 'application/json');
                                                httpRequestitcf1.send();
                                                 
                                            };
                                            
                                    
                                        }
                                        let progsiegestranscf = document.querySelector('#depsieg');
                                        if (progsiegestranscf !== null) {
                                            progsiegestranscf.onchange = () => 
                                            {

                                                const gareidentiftranscf1 = document.querySelector('#deplignetranscf').value;
                                                    const httpsousgarecf = new XMLHttpRequest();
                                                    httpsousgarecf.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftranscf1}`, true);
                                                    httpsousgarecf.onload = () => 
                                                    {
                                                        const donsousgcf = JSON.parse(httpsousgarecf.responseText);
                                                        console.debug(`${typeof donsousgcf}-${donsousgcf.attributes}`, console.memory);
                                                        if (Object.entries(donsousgcf).length >= 1) {
                                                            for (let key in Object.entries(donsousgcf)) 
                                                            {
                                                                let opt = document.createElement('option');
                                                                opt.value = `${donsousgcf[key].idsousgare}`;
                                                                opt.innerHTML = `${donsousgcf[key].nomsousgare}`;
                                                                document.querySelector('#transitedepargarecf1').add(opt);
    
                                                            }
                                                        }
                                                    };
                                                httpsousgarecf.setRequestHeader('Content-Type', 'application/json');
                                                httpsousgarecf.send();
                                                let httpSiegestranscf1;
                                                httpSiegestranscf1 = new XMLHttpRequest();
                                                const sigstranscf = document.querySelector('#depsieg')
                                                .options[document.querySelector('#depsieg').options.selectedIndex].value;
                                                const prostranscf = document.querySelector('#programtranscf').value;

                                                httpSiegestranscf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${prostranscf}/${sigstranscf}`, true);
                                                httpSiegestranscf1.onload = () => 
                                                {
                                                    const donsgetranscf = JSON.parse(httpSiegestranscf1.responseText);
                                                    console.debug(`${typeof donsgetranscf} - ${donsgetranscf.attributes}`, console.memory);
                                                    if(donsgetranscf == '')
                                                    {
                                                        let httpSiegstranscf;
                                                        httpSiegstranscf = new XMLHttpRequest();

                                                        httpSiegstranscf.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${prostranscf}/${sigstranscf}`, true);
                                                        httpSiegstranscf.onload = () => 
                                                        {
                                                            const dongtranscf = JSON.parse(httpSiegstranscf.responseText);
                                                            document.querySelector('#messconf').style.display = 'none';
                                                            if (Object.entries(dongtranscf).length >= 1)
                                                                {
                                                                    for (let key in Object.entries(dongtranscf)) {
                                                                        document.querySelector('#idtampotranscf').value = `${dongtranscf[key].idtamp}`;                    
                                                                        document.querySelector('#siegselecttranscf').value = `${dongtranscf[key].numsieg}`;
                                                                    }
                                                                }
                                                        };
                                                        httpSiegstranscf.setRequestHeader('Content-Type', 'application/json');
                                                        httpSiegstranscf.send();
                                                    }
                                                    else 
                                                    {
                                                        document.querySelector('#depsieg').value = '';     
                                                        if (Object.entries(donsgetranscf).length >= 1)
                                                        {
                                                            for (let key in Object.entries(donsgetranscf)) {
                                                                document.querySelector('#idtampotranscf').value = `${donsgetranscf[key].idtamp}`;                    
                                                                document.querySelector('#siegselecttranscf').value = `${donsgetranscf[key].numsieg}`;
                                                            }

                                                        }
                                                        document.querySelector('#messconf').style.display = 'block';
                                                        document.querySelector('#erreurMessconf').innerHTML = `Siege déjà utilisé.`; 
                                                    }
                                                };
                                                httpSiegestranscf1.setRequestHeader('Content-Type', 'application/json');
                                                httpSiegestranscf1.send();
                                            };
                                        }
                                        //premier transite
                                        let progchemincf = document.querySelector('#idcheminscf');
                                        if (progchemincf !== null) 
                                        {
                                            progchemincf.onchange = () => 
                                            {
                                                var datedepartcf = document.querySelector('#actuel').value;
                                                
                                                const prostranschemincf = document.querySelector('#idcheminscf')
                                                .options[document.querySelector('#idcheminscf').options.selectedIndex].value;

                                                var post_typgarecf2 = prostranschemincf.split('-');
                                                var seltypgarecf2 = post_typgarecf2[0];
                                                var typgareselcf1 = post_typgarecf2[1];
                                                let httptypequartcf2;
                                                httptypequartcf2 = new XMLHttpRequest();
                                                
                                                httptypequartcf2.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgareselcf1}`, true);
                                                httptypequartcf2.onload = () => 
                                                {
                                                    const donquacf2 = JSON.parse(httptypequartcf2.responseText);
                                                    if (donquacf2 == '') {
                                                        document.querySelector('#quartiercf2').options.length = 1;
                                                    }
                                                    else{
                                                        if (Object.entries(donquacf2).length >= 1) {
                                                                        
                                                            for (let key in Object.entries(donquacf2)) {
                                                                let optq1 = document.createElement('option');
                                                                optq1.value = `${donquacf2[key].nom_quartier}`;
                                                                optq1.innerHTML = `${donquacf2[key].nom_quartier}`;
                                                                document.querySelector('#quartiercf2').add(optq1);
                                                            }
                                                        } else {
                                                            document.querySelector('#quartiercf2').options.length = 1;
                                                        }
                                                    }
                                                    

                                                };
                                                httptypequartcf2.setRequestHeader('Content-Type', 'application/json');
                                                httptypequartcf2.send();
                                                
                                                let httpSiegeschemincf;
                                                httpSiegeschemincf = new XMLHttpRequest();
                                                
                                                httpSiegeschemincf.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranschemincf}/${datedepartcf}`, true);
                                                httpSiegeschemincf.onload = () => 
                                                {
                                        
                                                    const dongtranschemcf = JSON.parse(httpSiegeschemincf.responseText);
                                                    if (Object.entries(dongtranschemcf).length >= 1)
                                                    {
                                                        for (let key in Object.entries(dongtranschemcf)) {
                                                            let opt = document.createElement('option');
                                                            opt.value = `${dongtranschemcf[key].code_progr}/${dongtranschemcf[key].intervalle1}/${dongtranschemcf[key].intervalle2}/${dongtranschemcf[key].id_ligneheure}/${dongtranschemcf[key].prix}`;
                                                            opt.innerHTML = `${dongtranschemcf[key].heure}/${dongtranschemcf[key].date_progr}`;
                                                            document.querySelector('#hdepartitinecf').add(opt);
                                                        }
                                                    }
                                                };
                                                httpSiegeschemincf.setRequestHeader('Content-Type', 'application/json');
                                                httpSiegeschemincf.send();

                                            };
                                            let prochemintracf = document.querySelector('#hdepartitinecf');
                                            if (prochemintracf !== null){
                                                prochemintracf.onchange = () => 
                                                {  
                                                    const httpPrixittransitecf = new XMLHttpRequest();
                                                    const transselitinecf = document.querySelector('#hdepartitinecf')
                                                    .options[document.querySelector('#hdepartitinecf').options.selectedIndex].value;
                                                    var post_transcf = transselitinecf.split('/');
                                                    var itinetrascf = post_transcf[0];
                                                    var dbitracf = post_transcf[1];
                                                    var fnitracf = post_transcf[2];
                                                    var lhertracf = post_transcf[3];
                                                    var prixtracf = post_transcf[4];

                                                        httpPrixittransitecf.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetrascf}`, true);
                                                        httpPrixittransitecf.onload = () => 
                                                        {
                                                            const donprixitrancf = JSON.parse(httpPrixittransitecf.responseText);
                                                            console.debug(`${typeof donprixitrancf}-${donprixitrancf.attributes}`, console.memory);
                                                            if (Object.entries(donprixitrancf).length >= 1) {
                                                                for (let key in Object.entries(donprixitrancf)) 
                                                                {
                                                                    document.querySelector('#prix_axetransitcf').value = `${prixtracf}`;
                                                                    document.querySelector('#catetransitcf').value = `${donprixitrancf[key].categori}`;
                                                                    document.querySelector('#gidtranscf').value =  `${donprixitrancf[key].gareidentif}`;
                                                                    document.querySelector('#nomitintranscf1').value = `${donprixitrancf[key].nom_ligne}`;
                                                                    document.querySelector('#ligntranscf1').value = `${donprixitrancf[key].ident_ligne}`;
                                                                }
                                                            }
                                                        };
                                                        httpPrixittransitecf.setRequestHeader('Content-Type', 'application/json');
                                                        httpPrixittransitecf.send();
                                              

                                                        
                                                        const httpRequetteitracf = new XMLHttpRequest();
                                                
                                                            httpRequetteitracf.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetrascf}/${dbitracf}/${fnitracf}`, true);
                                                        httpRequetteitracf.onload = () => {
                                                            const dattaitracf = JSON.parse(httpRequetteitracf.responseText);
                                                            console.debug(`${typeof dattaitracf} - ${dattaitracf.attributes}`, console.memory);
                                                            if (Object.entries(dattaitracf).length >= 1) {
                                                                for (let key in Object.entries(dattaitracf)) {
                                                                    
                                                                    let opt = document.createElement('option');
                                                                    opt.value = `${dattaitracf[key].siege_num}`;
                                                                    opt.innerHTML = `${dattaitracf[key].siege_num}`;
                                                                    document.querySelector('#psiegesitinescf').add(opt);
                                                                    
                                                                }
                                                                
                                                            } else {
                                                                document.querySelector('#psiegesitinescf').options.length = 1;
                                                            }
                                                        };
                                                        httpRequetteitracf.setRequestHeader('Content-Type', 'application/json');
                                                        httpRequetteitracf.send();
                                                };
                                            }
                                            let progsiegescf1 = document.querySelector('#psiegesitinescf');
                                            if (progsiegescf1 !== null) 
                                            {
                                                progsiegescf1.onchange = () => 
                                                {

                                                   const gareidentiftranscf2 = document.querySelector('#gidtranscf').value;
                                                        const httpsousgarecf1 = new XMLHttpRequest();
                                                        httpsousgarecf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftranscf2}`, true);
                                                        httpsousgarecf1.onload = () => 
                                                        {
                                                            const donsousgcf1 = JSON.parse(httpsousgarecf1.responseText);
                                                            console.debug(`${typeof donsousgcf1}-${donsousgcf1.attributes}`, console.memory);
                                                            if (Object.entries(donsousgcf1).length >= 1) {
                                                                for (let key in Object.entries(donsousgcf1)) 
                                                                {
                                                                    let opt = document.createElement('option');
                                                                    opt.value = `${donsousgcf1[key].idsousgare}`;
                                                                    opt.innerHTML = `${donsousgcf1[key].nomsousgare}`;
                                                                    document.querySelector('#transitedepargarecf2').add(opt);
        
                                                                }
                                                            }
                                                        };
                                                        httpsousgarecf1.setRequestHeader('Content-Type', 'application/json');
                                                        httpsousgarecf1.send();
                                                    

                                                    const transselitinecf1 = document.querySelector('#hdepartitinecf')
                                                    .options[document.querySelector('#hdepartitinecf').options.selectedIndex].value;
                                                    var post_transcf1 = transselitinecf1.split('/');
                                                    var itinetrascf1 = post_transcf1[0];
                                        
                                                    let httpSiegescf1;
                                                    httpSiegescf1 = new XMLHttpRequest();
                                                    const sigscf1 = document.querySelector('#psiegesitinescf')
                                                    .options[document.querySelector('#psiegesitinescf').options.selectedIndex].value;
                                                    //const pros1 = document.querySelector('#program').value;

                                                    httpSiegescf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetrascf1}/${sigscf1}`, true);
                                                    httpSiegescf1.onload = () => 
                                                    {
                                                        const donsgecf1 = JSON.parse(httpSiegescf1.responseText);
                                                        console.debug(`${typeof donsgecf1} - ${donsgecf1.attributes}`, console.memory);
                                                        if(donsgecf1 == '')
                                                        {
                                                            let httpSiegscf1;
                                                            httpSiegscf1 = new XMLHttpRequest();

                                                            httpSiegscf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetrascf1}/${sigscf1}`, true);
                                                            httpSiegscf1.onload = () => 
                                                            {
                                                                const dongcf1 = JSON.parse(httpSiegscf1.responseText);
                                                                document.querySelector('#messconf').style.display = 'none';
                                                                if (Object.entries(dongcf1).length >= 1)
                                                                    {
                                                                        for (let key in Object.entries(dongcf1)) {
                                                                            document.querySelector('#idtampocf1').value = `${dongcf1[key].idtamp}`;                    
                                                                            document.querySelector('#siegselectcf1').value = `${dongcf1[key].numsieg}`;
                                                                        }
                                                                    }
                                                            };
                                                            httpSiegscf1.setRequestHeader('Content-Type', 'application/json');
                                                            httpSiegscf1.send();
                                                        }
                                                        else {
                                                            document.querySelector('#psiegesitinescf').value = '';     
                                                            if (Object.entries(donsgecf1).length >= 1)
                                                            {
                                                                for (let key in Object.entries(donsgecf1)) {
                                                                    document.querySelector('#idtampocf1').value = `${donsgecf1[key].idtamp}`;                    
                                                                    document.querySelector('#siegselectcf1').value = `${donsgecf1[key].numsieg}`;
                                                                }

                                                            }
                                                            document.querySelector('#messconf').style.display = 'block';
                                                            document.querySelector('#erreurMessconf').innerHTML = `Siege déjà utilisé.`;                                                                   
                                                        }
                                                    };
                                                    httpSiegescf1.setRequestHeader('Content-Type', 'application/json');
                                                    httpSiegescf1.send();

                                                };
                                            }
                                        }
                                        //deuxieme transite
                                        let progchemincf1 = document.querySelector('#idcheminscf1');
                                        if (progchemincf1 !== null) 
                                        {
                                            progchemincf1.onchange = () => 
                                            {

                                                const prostranschemincf32 = document.querySelector('#idcheminscf1')
                                                .options[document.querySelector('#idcheminscf1').options.selectedIndex].value;

                                                var post_typgarecf32 = prostranschemincf32.split('-');
                                                var seltypgarecf32 = post_typgarecf32[0];
                                                var typgareselcf31 = post_typgarecf32[1];
                                                let httptypequartcf32;
                                                httptypequartcf32 = new XMLHttpRequest();
                                                
                                                httptypequartcf32.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquartr/${typgareselcf31}`, true);
                                                httptypequartcf32.onload = () => 
                                                {
                                                    const donquacf32 = JSON.parse(httptypequartcf32.responseText);
                                                    if (donquacf32 == '') {
                                                        document.querySelector('#quartiercf3').options.length = 1;
                                                    }
                                                    else{
                                                        if (Object.entries(donquacf32).length >= 1) {
                                                                        
                                                            for (let key in Object.entries(donquacf32)) {
                                                                let optq31 = document.createElement('option');
                                                                optq31.value = `${donquacf32[key].nom_quartier}`;
                                                                optq31.innerHTML = `${donquacf32[key].nom_quartier}`;
                                                                document.querySelector('#quartiercf3').add(optq31);
                                                            }
                                                        } else {
                                                            document.querySelector('#quartiercf3').options.length = 1;
                                                        }
                                                    }
                                                    

                                                };
                                                httptypequartcf32.setRequestHeader('Content-Type', 'application/json');
                                                httptypequartcf32.send();
                                                
                                                let httpSiegeschemincf1;
                                                httpSiegeschemincf1 = new XMLHttpRequest();
                                                
                                                var datedepartcf = document.querySelector('#actuel').value;
                                                const prostranschemincf1 = document.querySelector('#idcheminscf1')
                                                .options[document.querySelector('#idcheminscf1').options.selectedIndex].value;

                                                httpSiegeschemincf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranschemincf1}/${datedepartcf}`, true);
                                                httpSiegeschemincf1.onload = () => 
                                                {
                                        
                                                    const dongtranschemcf1 = JSON.parse(httpSiegeschemincf1.responseText);
                                                    if (Object.entries(dongtranschemcf1).length >= 1)
                                                        {
                                                            for (let key in Object.entries(dongtranschemcf1)) {
                                                                let opt = document.createElement('option');
                                                                opt.value = `${dongtranschemcf1[key].code_progr}/${dongtranschemcf1[key].intervalle1}/${dongtranschemcf1[key].intervalle2}/${dongtranschemcf1[key].id_ligneheure}/${dongtranschemcf1[key].prix}`;
                                                                opt.innerHTML = `${dongtranschemcf1[key].heure}/${dongtranschemcf1[key].date_progr}`;
                                                                document.querySelector('#idcheminsheurcf').add(opt);
                                                            }
                                                        }
                                                };
                                                httpSiegeschemincf1.setRequestHeader('Content-Type', 'application/json');
                                                httpSiegeschemincf1.send();

                                            };
                                               let prochemintracf1 = document.querySelector('#idcheminsheurcf');
                                            if (prochemintracf1 !== null)
                                                prochemintracf1.onchange = () => 
                                                {  
                                                    const httpPrixittransitecf1 = new XMLHttpRequest();
                                                    const transselitinecf1 = document.querySelector('#idcheminsheurcf')
                                                    .options[document.querySelector('#idcheminsheurcf').options.selectedIndex].value;
                                                        var post_transcf1 = transselitinecf1.split('/');
                                                    var itinetrascf1 = post_transcf1[0];
                                                    var dbitracf1 = post_transcf1[1];
                                                    var fnitracf1 = post_transcf1[2];
                                                    var lhertracf1 = post_transcf1[3];
                                                    var prixtracf1 = post_transcf1[4];

                                                        httpPrixittransitecf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetrascf1}`, true);
                                                        httpPrixittransitecf1.onload = () => 
                                                        {
                                                            const donprixitrancf1 = JSON.parse(httpPrixittransitecf1.responseText);
                                                            if (Object.entries(donprixitrancf1).length >= 1) {
                                                                for (let key in Object.entries(donprixitrancf1)) 
                                                                {
                                                                    document.querySelector('#prix_axetransitcf1').value = `${prixtracf1}`;
                                                                    document.querySelector('#catetransitcf1').value = `${donprixitrancf1[key].categori}`;
                                                                    document.querySelector('#gidtranscf1').value =  `${donprixitrancf1[key].gareidentif}`;
                                                                    document.querySelector('#nomitintranscf2').value = `${donprixitrancf1[key].nom_ligne}`;
                                                                    document.querySelector('#ligntranscf2').value = `${donprixitrancf1[key].ident_ligne}`;
                                                                }
                                                            }
                                                        };
                                                        httpPrixittransitecf1.setRequestHeader('Content-Type', 'application/json');
                                                        httpPrixittransitecf1.send();
                                              
                                                        

                                                        const httpRequetteitracf1 = new XMLHttpRequest();
                                                
                                                        httpRequetteitracf1.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetrascf1}/${dbitracf1}/${fnitracf1}`, true);
                                                        httpRequetteitracf1.onload = () => {
                                                            const dattaitracf1 = JSON.parse(httpRequetteitracf1.responseText);
                                                            console.debug(`${typeof dattaitracf1} - ${dattaitracf1.attributes}`, console.memory);
                                                            if (Object.entries(dattaitracf1).length >= 1) {
                                                                for (let key in Object.entries(dattaitracf1)) {
                                                                    
                                                                    let opt = document.createElement('option');
                                                                    opt.value = `${dattaitracf1[key].siege_num}`;
                                                                    opt.innerHTML = `${dattaitracf1[key].siege_num}`;
                                                                    document.querySelector('#psiegesitinescf1').add(opt);
                                                                    
                                                                }
                                                                
                                                            } else {
                                                                document.querySelector('#psiegesitinescf1').options.length = 1;
                                                            }
                                                        };
                                                        httpRequetteitracf1.setRequestHeader('Content-Type', 'application/json');
                                                        httpRequetteitracf1.send();
                                                };

                                               let progsiegescf2 = document.querySelector('#psiegesitinescf1');
                                                if (progsiegescf2 !== null) 
                                                {
                                                    progsiegescf2.onchange = () => 
                                                    {

                                                       const gareidentiftranscf4 = document.querySelector('#gidtranscf1').value;
                                                        const httpsousgarecf4 = new XMLHttpRequest();
                                                        httpsousgarecf4.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftranscf4}`, true);
                                                        httpsousgarecf4.onload = () => 
                                                        {
                                                            const donsousgcf4 = JSON.parse(httpsousgarecf4.responseText);
                                                            console.debug(`${typeof donsousgcf4}-${donsousgcf4.attributes}`, console.memory);
                                                            if (Object.entries(donsousgcf4).length >= 1) {
                                                                for (let key in Object.entries(donsousgcf4)) 
                                                                {
                                                                    let opt23 = document.createElement('option');
                                                                    opt23.value = `${donsousgcf4[key].idsousgare}`;
                                                                    opt23.innerHTML = `${donsousgcf4[key].nomsousgare}`;
                                                                    document.querySelector('#transitedepargarecf3').add(opt23);
                                                                }
                                                            }
                                                        };
                                                        httpsousgarecf4.setRequestHeader('Content-Type', 'application/json');
                                                        httpsousgarecf4.send();

                                                        const transselitinecf2 = document.querySelector('#idcheminsheurcf')
                                                        .options[document.querySelector('#idcheminsheurcf').options.selectedIndex].value;
                                                        var post_transcf2 = transselitinecf2.split('/');
                                                        var itinetrascf2 = post_transcf2[0];
                                            
                                                        let httpSiegescf2;
                                                        httpSiegescf2 = new XMLHttpRequest();
                                                        const sigscf2 = document.querySelector('#psiegesitinescf1')
                                                        .options[document.querySelector('#psiegesitinescf1').options.selectedIndex].value;

                                                        httpSiegescf2.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetrascf2}/${sigscf2}`, true);
                                                        httpSiegescf2.onload = () => 
                                                        {
                                                            const donsgecf2 = JSON.parse(httpSiegescf2.responseText);
                                                            if(donsgecf2 == '')
                                                            {
                                                                let httpSiegscf2;
                                                                httpSiegscf2 = new XMLHttpRequest();

                                                                httpSiegscf2.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetrascf2}/${sigscf2}`, true);
                                                                httpSiegscf2.onload = () => 
                                                                {
                                                                    const dongcf2 = JSON.parse(httpSiegscf2.responseText);
                                                                    document.querySelector('#messconf').style.display = 'none';
                                                                    if (Object.entries(dongcf2).length >= 1)
                                                                    {
                                                                        for (let key in Object.entries(dongcf2)) {
                                                                            document.querySelector('#idtampocf2').value = `${dongcf2[key].idtamp}`;                    
                                                                            document.querySelector('#siegselectcf2').value = `${dongcf2[key].numsieg}`;
                                                                        }
                                                                    }
                                                                };
                                                                httpSiegscf2.setRequestHeader('Content-Type', 'application/json');
                                                                httpSiegscf2.send();
                                                            }
                                                            else 
                                                            {
                                                                document.querySelector('#psiegesitinescf1').value = '';     
                                                                if (Object.entries(donsgecf2).length >= 1)
                                                                {
                                                                    for (let key in Object.entries(donsgecf2)) {
                                                                        document.querySelector('#idtampocf2').value = `${donsgecf2[key].idtamp}`;                    
                                                                        document.querySelector('#siegselectcf2').value = `${donsgecf2[key].numsieg}`;
                                                                    }

                                                                }
                                                                document.querySelector('#messconf').style.display = 'block';
                                                                document.querySelector('#erreurMessconf').innerHTML = `Siege déjà utilisé.`;                                                                   
                                                            }
                                                        };
                                                        httpSiegescf2.setRequestHeader('Content-Type', 'application/json');
                                                        httpSiegescf2.send();

                                                    };
                                                }
                                        }   

                                        //troisieme transite
                                       let progchemincf2 = document.querySelector('#idcheminscf2');
                                        if (progchemincf2 !== null) 
                                        {
                                            progchemincf2.onchange = () => 
                                            {
                                                const prostranschemincf42 = document.querySelector('#idcheminscf2')
                                                .options[document.querySelector('#idcheminscf2').options.selectedIndex].value;

                                                var post_typgarecf42 = prostranschemincf42.split('-');
                                                var seltypgarecf42 = post_typgarecf42[0];
                                                var typgareselcf41 = post_typgarecf42[1];
                                                
                                                let httpSiegeschemincf2;
                                                httpSiegeschemincf2 = new XMLHttpRequest();
                                                
                                                var datedepartcf = document.querySelector('#actuel').value;
                                                const prostranschemincf2 = document.querySelector('#idcheminscf2')
                                                .options[document.querySelector('#idcheminscf2').options.selectedIndex].value;

                                                httpSiegeschemincf2.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranschemincf2}/${datedepartcf}`, true);
                                                httpSiegeschemincf2.onload = () => 
                                                {
                                        
                                                            const dongtranschemcf2 = JSON.parse(httpSiegeschemincf2.responseText);
                                                            if (Object.entries(dongtranschemcf2).length >= 1)
                                                                {
                                                                    for (let key in Object.entries(dongtranschemcf2)) {
                                                                        let opt = document.createElement('option');
                                                                        opt.value = `${dongtranschemcf2[key].code_progr}/${dongtranschemcf2[key].intervalle1}/${dongtranschemcf2[key].intervalle2}/${dongtranschemcf2[key].id_ligneheure}/${dongtranschemcf2[key].prix}`;
                                                                        opt.innerHTML = `${dongtranschemcf2[key].heure}/${dongtranschemcf2[key].date_progr}`;
                                                                        document.querySelector('#idcheminsheurcf1').add(opt);
                                                                    }
                                                                }
                                                };
                                                httpSiegeschemincf2.setRequestHeader('Content-Type', 'application/json');
                                                httpSiegeschemincf2.send();

                                            };
                                              let prochemintracf2 = document.querySelector('#idcheminsheurcf1');
                                            if (prochemintracf2 !== null)
                                                prochemintracf2.onchange = () => 
                                                {  
                                                    const httpPrixittransitecf2 = new XMLHttpRequest();
                                                        const transselitinecf2 = document.querySelector('#idcheminsheurcf1')
                                                    .options[document.querySelector('#idcheminsheurcf1').options.selectedIndex].value;
                                                        var post_transcf2 = transselitinecf2.split('/');
                                                    var itinetrascf2 = post_transcf2[0];
                                                    var dbitracf2 = post_transcf2[1];
                                                    var fnitracf2 = post_transcf2[2];
                                                    var lhertracf2 = post_transcf2[3];
                                                    var prixtracf2 = post_transcf2[4];

                                                        httpPrixittransitecf2.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotrans/${itinetrascf2}`, true);
                                                        httpPrixittransitecf2.onload = () => 
                                                        {
                                                            const donprixitrancf2 = JSON.parse(httpPrixittransitecf2.responseText);
                                                            if (Object.entries(donprixitrancf2).length >= 1) {
                                                                for (let key in Object.entries(donprixitrancf2)) 
                                                                {
                                                                    document.querySelector('#prix_axetransitcf2').value = `${prixtracf2}`;
                                                                    document.querySelector('#catetransitcf2').value = `${donprixitrancf2[key].categori}`;
                                                                    document.querySelector('#gidtranscf2').value =  `${donprixitrancf2[key].gareidentif}`;
                                                                    document.querySelector('#nomitintranscf3').value = `${donprixitrancf2[key].nom_ligne}`;
                                                                    document.querySelector('#ligntranscf3').value = `${donprixitrancf2[key].ident_ligne}`;
                                                                }
                                                            }
                                                        };
                                                        httpPrixittransitecf2.setRequestHeader('Content-Type', 'application/json');
                                                        httpPrixittransitecf2.send();

                                                        const httpRequetteitracf2 = new XMLHttpRequest();
                                                
                                                        httpRequetteitracf2.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletrans/${itinetrascf2}/${dbitracf2}/${fnitracf2}`, true);
                                                        httpRequetteitracf2.onload = () => {
                                                            const dattaitracf2 = JSON.parse(httpRequetteitracf2.responseText);
                                                            console.debug(`${typeof dattaitracf2} - ${dattaitracf2.attributes}`, console.memory);
                                                            if (Object.entries(dattaitracf2).length >= 1) {
                                                                for (let key in Object.entries(dattaitracf2)) {
                                                                    
                                                                    let opt = document.createElement('option');
                                                                    opt.value = `${dattaitracf2[key].siege_num}`;
                                                                    opt.innerHTML = `${dattaitracf2[key].siege_num}`;
                                                                    document.querySelector('#psiegesitinescf2').add(opt);
                                                                    
                                                                }
                                                                
                                                            } else {
                                                                document.querySelector('#psiegesitinescf2').options.length = 1;
                                                            }
                                                        };
                                                        httpRequetteitracf2.setRequestHeader('Content-Type', 'application/json');
                                                        httpRequetteitracf2.send();
                                                };

                                               let progsiegescf3 = document.querySelector('#psiegesitinescf2');
                                                if (progsiegescf3 !== null) 
                                                {
                                                    progsiegescf3.onchange = () => 
                                                    {

                                                       const gareidentiftranscf5 = document.querySelector('#gidtranscf2').value;
                                                        const httpsousgarecf5 = new XMLHttpRequest();
                                                        httpsousgarecf5.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifsousgares/${gareidentiftranscf5}`, true);
                                                        httpsousgarecf5.onload = () => 
                                                        {
                                                            const donsousgcf5 = JSON.parse(httpsousgarecf5.responseText);
                                                            console.debug(`${typeof donsousgcf5}-${donsousgcf5.attributes}`, console.memory);
                                                            if (Object.entries(donsousgcf5).length >= 1) {
                                                                for (let key in Object.entries(donsousgcf5)) 
                                                                {
                                                                    let opt = document.createElement('option');
                                                                    opt.value = `${donsousgcf5[key].idsousgare}`;
                                                                    opt.innerHTML = `${donsousgcf5[key].nomsousgare}`;
                                                                    document.querySelector('#transitedepargarecf4').add(opt);
        
                                                                }
                                                            }
                                                        };
                                                        httpsousgarecf5.setRequestHeader('Content-Type', 'application/json');
                                                        httpsousgarecf5.send();
                                                        const transselitinecf3 = document.querySelector('#idcheminsheurcf1')
                                                        .options[document.querySelector('#idcheminsheurcf1').options.selectedIndex].value;
                                                        var post_transcf3 = transselitinecf3.split('/');
                                                        var itinetrascf3 = post_transcf3[0];
                                            
                                                        let httpSiegescf3;
                                                        httpSiegescf3 = new XMLHttpRequest();
                                                        const sigscf3 = document.querySelector('#psiegesitinescf2')
                                                        .options[document.querySelector('#psiegesitinescf2').options.selectedIndex].value;

                                                        httpSiegescf3.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${itinetrascf3}/${sigscf3}`, true);
                                                        httpSiegescf3.onload = () => 
                                                        {
                                                            const donsgecf3 = JSON.parse(httpSiegescf3.responseText);
                                                            if(donsgecf3 == '')
                                                            {
                                                                let httpSiegscf3;
                                                                httpSiegscf3 = new XMLHttpRequest();

                                                                httpSiegscf3.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${itinetrascf3}/${sigscf3}`, true);
                                                                httpSiegscf3.onload = () => 
                                                                {
                                                                    const dongcf3 = JSON.parse(httpSiegscf3.responseText);
                                                                    document.querySelector('#messconf').style.display = 'none';
                                                                    if (Object.entries(dongcf3).length >= 1)
                                                                    {
                                                                        for (let key in Object.entries(dongcf3)) {
                                                                            document.querySelector('#idtampocf3').value = `${dongcf3[key].idtamp}`;                    
                                                                            document.querySelector('#siegselectcf3').value = `${dongcf3[key].numsieg}`;
                                                                        }
                                                                    }
                                                                };
                                                                httpSiegscf3.setRequestHeader('Content-Type', 'application/json');
                                                                httpSiegscf3.send();
                                                            }
                                                            else {
                                                                document.querySelector('#psiegesitinescf2').value = '';     
                                                                if (Object.entries(donsgecf3).length >= 1)
                                                                {
                                                                    for (let key in Object.entries(donsgecf3)) {
                                                                        document.querySelector('#idtampocf3').value = `${donsgecf3[key].idtamp}`;                    
                                                                        document.querySelector('#siegselectcf3').value = `${donsgecf3[key].numsieg}`;
                                                                    }

                                                                }
                                                                document.querySelector('#messconf').style.display = 'block';
                                                                document.querySelector('#erreurMessconf').innerHTML = `Siege déjà utilisé.`;                                                                   
                                                            }
                                                        };
                                                        httpSiegescf3.setRequestHeader('Content-Type', 'application/json');
                                                        httpSiegescf3.send();
                                                        
                                                    };
                                                }

                                        }            
                                    }
                                        
                                }
                            }
                        };
                        httpRequestitinecf.setRequestHeader('Content-Type', 'application/json');
                        httpRequestitinecf.send();
                    }
                    else
                    {
                        if (Object.entries(data2).length >= 1) {
                            for (let key in Object.entries(data2)) {
                                let opt = document.createElement('option');
                                opt.value = `${data2[key].code_progr}/${data2[key].typetarif}/${data2[key].id_ligneheure}`;
                                opt.innerHTML = `${data2[key].heure}/${data2[key].date_progr}`;
                                document.querySelector('#heured').add(opt);
                                
                            }
                        }else
                        {
                            document.querySelector('#heured').options.length = 1;
                        }

                        let heurdeprt = document.querySelector('#heured');
                        if (heurdeprt !== null)
                            heurdeprt.onchange = () => {
                                
                                document.querySelector('#depsieg').options.length = 1;
                                const Requeste = new XMLHttpRequest();
                                const selectorp = document.querySelector('#heured').options[document.querySelector('#heured').
                                options.selectedIndex].value;
                                var selectorp1 = selectorp.split('/');
                                var selectorp2 = selectorp1[0];
                                var selectorp3 = selectorp1[1];
                                Requeste.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/siegdispo/${selectorp2}`, true);
                                Requeste.onload = () => {
                                    const datasgc = JSON.parse(Requeste.responseText);
                                    if (Object.entries(datasgc).length >= 1) {
                                        for (let key in Object.entries(datasgc)) {
                                            
                                            document.querySelector('#caissepvend_').value = `${datasgc[key].intervalle1}`;
                                            document.querySelector('#caissedpvend_').value = `${datasgc[key].intervalle2}`;
                                            document.querySelector('#directid').value = `${datasgc[key].nom_ligne}`;
                                            document.querySelector('#confheure').value = `${datasgc[key].heure}`;
                                            document.querySelector('#gareid_dep').value = `${datasgc[key].gaexp_lg}`;
                                            document.querySelector('#dateconfirme').value = `${datasgc[key].date_progr}`;
                                            document.querySelector('#catconfirme').value = `${datasgc[key].categori}`;
                                            document.querySelector('#lignehconf').value = `${datasgc[key].id_ligneheure}`;
                                            document.querySelector('#programconf').value = `${datasgc[key].code_progr}`;
                                        }
                                    } 
                                    const Requestbis = new XMLHttpRequest();
                                            const pldebut = document.querySelector('#caissepvend_').value;
                                            const plfin = document.querySelector('#caissedpvend_').value;
                                            const cfdir = document.querySelector('#directid').value;
                                            const hconfir = document.querySelector('#confheure').value;
                                            const dconfirme = document.querySelector('#dateconfirme').value;
                                    Requestbis.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${selectorp2}/${dconfirme}/${cfdir}/${hconfir}/${pldebut}/${plfin}`, true);
                                    Requestbis.onload = () => {
                                        const datasgcbis = JSON.parse(Requestbis.responseText);
                                        if (Object.entries(datasgcbis).length >= 1) {
                                            for (let key in Object.entries(datasgcbis)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${datasgcbis[key].siege_num}`;
                                                opt.innerHTML = `${datasgcbis[key].siege_num}`;
                                                document.querySelector('#depsieg').add(opt);
                                            }
                                        } else {
                                            document.querySelector('#depsieg').options.length = 1;
                                        }
                                    };
                                    Requestbis.setRequestHeader('Content-Type', 'application/json');
                                    Requestbis.send();
                                };
                                Requeste.setRequestHeader('Content-Type', 'application/json');
                                Requeste.send();
                            };
                    }
                };
                Requests.setRequestHeader('Content-Type', 'application/json');
                Requests.send();
        };
        
        let depsiegconf = document.querySelector('#depsieg');
        if (depsiegconf !== null)
        depsiegconf.onchange = () => {
                
                let Requestsiegevenduconf;
                
                if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                    Requestsiegevenduconf = new XMLHttpRequest();
                } else if (window.ActiveXObject) { // IE 6 and older
                    Requestsiegevenduconf = new ActiveXObject("Microsoft.XMLHTTP");
                }
                
                const dp_progconf = document.querySelector('#programconf').value;
                const dp_siegeconf = document.querySelector('#depsieg').options[document.querySelector('#depsieg').options.selectedIndex].value;
                Requestsiegevenduconf.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${dp_progconf}/${dp_siegeconf}`, true);
                Requestsiegevenduconf.onload = () => 
                {
                    
                        const confdonsieg = JSON.parse(Requestsiegevenduconf.responseText);
                        if (confdonsieg == '')
                                {
                                    let httpSiegsconf;
                                    httpSiegsconf = new XMLHttpRequest();

                                    httpSiegsconf.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${dp_progconf}/${dp_siegeconf}`, true);
                                    httpSiegsconf.onload = () => 
                                    {
                                        const dongconf= JSON.parse(httpSiegsconf.responseText);
                                        document.querySelector('#messconf').style.display = 'none';
                                        if (Object.entries(dongconf).length >= 1)
                                        {
                                            for (let key in Object.entries(dongconf)) {
                                                document.querySelector('#idtampoconf').value = `${dongconf[key].idtamp}`;                    
                                                document.querySelector('#siegselectconf').value = `${dongconf[key].numsieg}`;
                                            }
                                        }
                                    };
                                    httpSiegsconf.setRequestHeader('Content-Type', 'application/json');
                                    httpSiegsconf.send();
                                }
                                else {
                                    document.querySelector('#depsieg').value = '';     
                                    if (Object.entries(confdonsieg).length >= 1)
                                    {
                                        for (let key in Object.entries(confdonsieg)) {
                                            document.querySelector('#idtampoconf').value = `${confdonsieg[key].idtamp}`;                    
                                            document.querySelector('#siegselectconf').value = `${confdonsieg[key].numsieg}`;
                                        }

                                    }
                                    document.querySelector('#messconf').style.display = 'block';
                                    document.querySelector('#erreurMessconf').innerHTML = `Siege déjà utilisé.`; 
                                }
                };
                Requestsiegevenduconf.setRequestHeader('content-Type', 'text/json');
                Requestsiegevenduconf.send();
            };
        //bouton annuler
        butoncliconf = document.querySelector('#confreset');
        if (butoncliconf !== null) {
            butoncliconf.onclick = () => 
            {
                let httpSiegeselectconf;
                httpSiegeselectconf = new XMLHttpRequest();
                const siegselectconf = document.querySelector('#siegselectconf').value;
                const idtapconf = document.querySelector('#idtampoconf').value;
                httpSiegeselectconf.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtapconf}/${siegselectconf}`, true);
                httpSiegeselectconf.onload = () => 
                {
                    const donselectconf = JSON.parse(httpSiegeselectconf.responseText);
                    console.debug(`${typeof donselectconf} - ${donselectconf.attributes}`, console.memory);
                    document.querySelector('#messconf').style.display = 'none';
                };
                httpSiegeselectconf.setRequestHeader('Content-Type', 'application/json');
                httpSiegeselectconf.send();
            };
        }
        //recherche d'information du client depart principal
        let infcontact = document.querySelector('#pascontactpconf');
        if (infcontact !== null)
        infcontact.onkeyup = () => {
                let httpInfosrequest;
                if (window.XMLHttpRequest) {
                    httpInfosrequest = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfosrequest = new ActiveXObject("Microsoft.XMLHTTP");
                }
                var verifict = document.querySelector('#pascontactpconf').value;
                httpInfosrequest.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifinfos/${verifict}`, true);
                httpInfosrequest.onload = () => {
                    const infosreq = JSON.parse(httpInfosrequest.responseText);
                    if (infosreq == null) {
                        document.querySelector('#pasnompconf').value = "";
                        document.querySelector('#pasprenompconf').value = "";
                        document.querySelector('#pascnibpconf').value = "";
                        document.querySelector('#pasdatepconf').value = "";
                        document.querySelector('#delivrelieu').value = "";
                        document.querySelector('#clientconfirmeid').value = "";
                    } else {
                        if (Object.entries(infosreq).length > 1) {
                            
                            if (infosreq.contact_client == verifict) {
                                document.querySelector('#pasnompconf').value = `${infosreq.nom_client}`;
                                document.querySelector('#pasprenompconf').value = `${infosreq.prenom_client}`;
                                document.querySelector('#pascnibpconf').value = `${infosreq.num_CNIB}`;
                                document.querySelector('#pasdatepconf').value = `${infosreq.date_delivre}`;
                                document.querySelector('#delivrelieu').value = `${infosreq.lieu_delivre}`;
                                document.querySelector('#clientconfirmeid').value = `${infosreq.id_client}`;

                                document.querySelector('#pasnompconfcp').value = `${infosreq.nom_client}`;
                                document.querySelector('#pasprenompconfcp').value = `${infosreq.prenom_client}`;
                                document.querySelector('#pascnibpconfcp').value = `${infosreq.num_CNIB}`;
                                document.querySelector('#pasdatepconfcp').value = `${infosreq.date_delivre}`;
                                document.querySelector('#lieucnibconf').value = `${infosreq.lieu_delivre}`;
                            } else {
                                document.querySelector('#pasnompconf').value = "";
                                document.querySelector('#pasprenompconf').value = "";
                                document.querySelector('#pascnibpconf').value = "";
                                document.querySelector('#pasdatepconf').value = "";
                                document.querySelector('#delivrelieu').value = "";
                                document.querySelector('#clientconfirmeid').value = "";
                            }
                        }
                    }
                };
                httpInfosrequest.setRequestHeader('Content-Type', 'application/json');
                httpInfosrequest.send();
        };
        e.onclick = function () {
            let confForm = document.querySelector('#confForm');
            confForm.setAttribute('action', `${APP_ROOT}/Confirmation/confirme/${e.dataset.cle_compagnie}`);
        }
    })
});
;
/* --- addreserve.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addreserve').forEach(function (e) 
    {
        document.querySelector('h3#reTitle').innerHTML = `RESERVATION`;
            
            let da = document.querySelector('#axereserve');
            if (da !== null){
                da.onchange = () => {
                
                document.querySelector('#heuredepart').options.length = 1;
                document.querySelector('#passgsieges').options.length = 1;
                document.querySelector('#tarifattribtime').value = '';
                let httpRequetes;
                
                if (window.XMLHttpRequest) {
                    httpRequetes = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpRequetes = new ActiveXObject("Microsoft.XMLHTTP");
                }
                
                var ax = document.querySelector('#axereserve').value;
                 var datedepart = document.querySelector('#actueldate').value;
                   
                            let httpRequetesq = new XMLHttpRequest();
                            httpRequetesq.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfquart/${ax}`, true);
                            httpRequetesq.onload = () => {
                            const qdata = JSON.parse(httpRequetesq.responseText);
                            if(qdata == ''){
                                document.querySelector('#quartreser').options.length = 1;
                            }else{
                                if (Object.entries(qdata).length >= 1) {
                                            
                                    for (let key in Object.entries(qdata)) {
                                        let opt = document.createElement('option');
                                        opt.value = `${qdata[key].nom_quartier}`;
                                        opt.innerHTML = `${qdata[key].nom_quartier}`;
                                        document.querySelector('#quartreser').add(opt);
                                    }
                                } else {
                                    document.querySelector('#quartreser').options.length = 1;
                                }
                            }
                                
                                    
                            };
                            httpRequetesq.setRequestHeader('Content-Type', 'application/json');
                            httpRequetesq.send();
                        httpRequetes.open('GET', window.location.origin + `${APP_ROOT}/confirmation/verifconfprog/${ax}/${datedepart}`, true);
                        httpRequetes.onload = () => {
                            const dataAxe = JSON.parse(httpRequetes.responseText);
                            
                                
                                    if (Object.entries(dataAxe).length >= 1) {
                                            
                                            for (let key in Object.entries(dataAxe)) {
                                                let opt = document.createElement('option');
                                                opt.value = `${dataAxe[key].code_progr}`;
                                                opt.innerHTML = `${dataAxe[key].heure}/${dataAxe[key].date_progr}`;
                                                document.querySelector('#heuredepart').add(opt);
                                            }
                                        } else {
                                            document.querySelector('#heuredepart').options.length = 1;
                                        }
                            };
                            httpRequetes.setRequestHeader('Content-Type', 'application/json');
                            httpRequetes.send();
                    
                };
                
            }
            let hrdepart = document.querySelector('#heuredepart');
            if (hrdepart !== null) {
                hrdepart.onchange = () => {
                    document.querySelector('#passgsieges').options.length = 1;
                    const httpRequest = new XMLHttpRequest();
                    const sel = document.querySelector('#heuredepart')
                        .options[document.querySelector('#heuredepart').options.selectedIndex].value;
                    httpRequest.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/siegdispo/${sel}`, true);
                    httpRequest.onload = () => {
                        const don = JSON.parse(httpRequest.responseText);
                        console.debug(`${typeof don} - ${don.attributes}`, console.memory);
                        if (Object.entries(don).length > 0) {
                            for (let key in Object.entries(don)) {
                                document.querySelector('#pfinvendable').value = `${don[key].intervalle2}`;
                                document.querySelector('#siegfinvendable').value = `${don[key].intervalle1}`;
                                document.querySelector('#reservetime').value = `${don[key].code_progr}`;
                                document.querySelector('#tarifattribtime').value = `${don[key].typetarif}`;
                                document.querySelector('#timeaxeid').value = `${don[key].ident_ligne}`;
                                document.querySelector('#directreserve').value = `${don[key].nom_ligne}`;
                                document.querySelector('#reserveheure').value = `${don[key].heure}`;
                                document.querySelector('#gareid_reserve').value = `${don[key].gaexp_lg}`;
                                document.querySelector('#datereserve').value = `${don[key].date_progr}`;
                                document.querySelector('#lhreserve').value = `${don[key].id_heur}`;
                                document.querySelector('#categbus').value=`${don[key].categori}`;

                                console.debug(`${don[key].intervalle1} - ${don[key].intervalle2}`, console.memory)
                                
                            }
                        }

                        const httpPrixres = new XMLHttpRequest();
                        const selh = document.querySelector('#lhreserve').value;
                        const selhtfb = document.querySelector('#tarifattribtime').value;
                        
                        httpPrixres.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifpriprg/${selh}/${selhtfb}`, true);
                        httpPrixres.onload = () => 
                        {
                            const donpr = JSON.parse(httpPrixres.responseText);
                            console.debug(`${typeof donpr}-${donpr.attributes}`, console.memory);
                            if (Object.entries(donpr).length >= 1) {
                                for (let key in Object.entries(donpr)) {
                                    document.querySelector('#prixtick').value = `${donpr[key].prix}`;

                                }
                            }
                        };
                        httpPrixres.setRequestHeader('Content-Type', 'application/json');
                        httpPrixres.send();

                        const httpRequestbis = new XMLHttpRequest();
    
                        const lp = document.querySelector('#pfinvendable').value;
                        const dbpl = document.querySelector('#siegfinvendable').value;
                        const direc = document.querySelector('#directreserve').value;
                        const he = document.querySelector('#reserveheure').value;
                        const datres = document.querySelector('#datereserve').value;
    
                        httpRequestbis.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponible/${sel}/${datres}/${direc}/${he}/${dbpl}/${lp}`, true);
                        httpRequestbis.onload = () => {
                            const donbis = JSON.parse(httpRequestbis.responseText);
                            console.debug(`${typeof donbis} - ${donbis.attributes}`, console.memory);
                            if (Object.entries(donbis).length >= 1) {
                                for (let key in Object.entries(donbis)) {
                                    
                                    let opt = document.createElement('option');
                                    opt.value = `${donbis[key].siege_num}`;
                                    opt.innerHTML = `${donbis[key].siege_num}`;
                                    document.querySelector('#passgsieges').add(opt);
                            
                                }
                                
                            } else {
                                document.querySelector('#passgsieges').options.length = 1;
                            }
                            
                        };
                        httpRequestbis.setRequestHeader('Content-Type', 'application/json');
                        httpRequestbis.send();
                          
                    };
                    httpRequest.setRequestHeader('Content-Type', 'application/json');
                    httpRequest.send();
                };
           
            }

            let depsiegreserve = document.querySelector('#passgsieges');
            if (depsiegreserve !== null)
            depsiegreserve.onchange = () => {
                    
                    let Requestsiegereserve;
                    
                    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
                        Requestsiegereserve = new XMLHttpRequest();
                    } else if (window.ActiveXObject) { // IE 6 and older
                        Requestsiegereserve = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                    const dp_progreserv = document.querySelector('#reservetime').value;
                    const dp_siegereserv = document.querySelector('#passgsieges').options[document.querySelector('#passgsieges').options.selectedIndex].value;
                                       
                    Requestsiegereserve.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisieges/${dp_progreserv}/${dp_siegereserv}`, true);
                    Requestsiegereserve.onload = () => 
                    {
                        
                            const reservdonsieg = JSON.parse(Requestsiegereserve.responseText);
                            if (reservdonsieg == '')
                                    {
                                        let httpSiegsreserv;
                                        httpSiegsreserv = new XMLHttpRequest();
                                        const dp_progconf = document.querySelector('#reservetime').value;
                                        const dp_siegeconf = document.querySelector('#passgsieges').options[document.querySelector('#passgsieges').options.selectedIndex].value;
                                        httpSiegsreserv.open('GET', window.location.origin + `${APP_ROOT}/programmes/creersiege/${dp_progconf}/${dp_siegeconf}`, true);
                                        httpSiegsreserv.onload = () => 
                                        {
                                            const dongreserv= JSON.parse(httpSiegsreserv.responseText);
                                            document.querySelector('#messreserv').style.display = 'none';
                                            if (Object.entries(dongreserv).length >= 1)
                                            {
                                                for (let key in Object.entries(dongreserv)) {
                                                    document.querySelector('#idtamporeserve').value = `${dongreserv[key].idtamp}`;                    
                                                    document.querySelector('#siegselectreserve').value = `${dongreserv[key].numsieg}`;
                                                }
                                            }
                                        
                                        };
                                        httpSiegsreserv.setRequestHeader('Content-Type', 'application/json');
                                        httpSiegsreserv.send();
                                    }
                                    else {
                                        document.querySelector('#passgsieges').value = '';     
                                        if (Object.entries(reservdonsieg).length >= 1)
                                        {
                                            for (let key in Object.entries(reservdonsieg)) {
                                                document.querySelector('#idtamporeserve').value = `${reservdonsieg[key].idtamp}`;                    
                                                document.querySelector('#siegselectreserve').value = `${reservdonsieg[key].numsieg}`;
                                            }

                                        }
                                        document.querySelector('#messreserv').style.display = 'block';
                                        document.querySelector('#erreurMessreserv').innerHTML = `Siege déjà utilisé.`; 
                                    }
                    };
                    Requestsiegereserve.setRequestHeader('content-Type', 'text/json');
                    Requestsiegereserve.send();
                };
//bouton annuler
                butonclireserv = document.querySelector('#idreserv');
                if (butonclireserv !== null) {
                    butonclireserv.onclick = () => 
                    {
                        let httpSiegeselectreserve;
                        httpSiegeselectreserve = new XMLHttpRequest();
                        const siegselectres = document.querySelector('#siegselectreserve').value;
                        const idtapres = document.querySelector('#idtamporeserve').value;
                        httpSiegeselectreserve.open('GET', window.location.origin + `${APP_ROOT}/programmes/deltamponsieg/${idtapres}/${siegselectres}`, true);
                        httpSiegeselectreserve.onload = () => 
                        {
                            const donselectconf = JSON.parse(httpSiegeselectreserve.responseText);
                            console.debug(`${typeof donselectconf} - ${donselectconf.attributes}`, console.memory);
                            document.querySelector('#messreserv').style.display = 'none';
                            
                        };
                        httpSiegeselectreserve.setRequestHeader('Content-Type', 'application/json');
                        httpSiegeselectreserve.send();
    
                    
                    };
                }
    
            let inform = document.querySelector('#idcontactcl');
            if (inform !== null)
                inform.onkeyup = () => {
                    let httpInfosre;
                    if (window.XMLHttpRequest) {
                        httpInfosre = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpInfosre = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    var verificatre = document.querySelector('#idcontactcl').value;
                    httpInfosre.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifinfos/${verificatre}`, true);
                    httpInfosre.onload = () => {
                        const infosre = JSON.parse(httpInfosre.responseText);
                        if (infosre == null) {
                            document.querySelector('#idnomcl').value = "";
                            document.querySelector('#idprenomcl').value = "";
                            document.querySelector('#idclientcomp').value = "";
                        } else {
                            if (Object.entries(infosre).length > 1) {
                                
                                if (infosre.contact_client == verificatre) {
                                    document.querySelector('#idnomcl').value = `${infosre.nom_client}`;
                                    document.querySelector('#idprenomcl').value = `${infosre.prenom_client}`;
                                    document.querySelector('#idclientcomp').value = `${infosre.id_client}`;
                                    document.querySelector('#cpidnomcl').value = `${infosre.nom_client}`;
                                    document.querySelector('#cpidprenomcl').value = `${infosre.prenom_client}`;
                                } else {
                                    document.querySelector('#idnomcl').value = "";
                                    document.querySelector('#idprenomcl').value = "";
                                    document.querySelector('#idclientcomp').value = "";
                                }
                            }
                        }
                    };
                    httpInfosre.setRequestHeader('Content-Type', 'application/json');
                    httpInfosre.send();
                };
            e.onclick = function () {   
                let reForm = document.querySelector('#reForm');

                reForm.setAttribute('action', `${APP_ROOT}/Reserves/addreserve/${e.dataset.cle_compagnie}`);   
            }
        
    })

});
;
/* --- addretour.js --- */
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
;
/* --- adreportjs.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adreportjs').forEach(function (e) 
    {
        document.querySelector('h3#Titlerep').innerHTML = `EXERCICE MENSUEL TICKET GUICHETIER`;

        let infgar = document.querySelector('#departgaridentif');
        
        if (infgar !== null) 
        infgar.onchange = () => {
            let httpInfosgar;
            if (window.XMLHttpRequest) {
                httpInfosgar = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgar = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idcaissiers').options.length = 1;

                    var verificatgar = document.querySelector('#departgaridentif').value;
                    
                    httpInfosgar.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgar}`, true);
                    httpInfosgar.onload = () => {
                        const infosgar = JSON.parse(httpInfosgar.responseText);
                        
                        if (Object.entries(infosgar).length > 0) {                            
                        
                                for (let key in Object.entries(infosgar)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgar[key].roleattribut}`;
                                    opt.innerHTML = `${infosgar[key].username}`;
                                    document.querySelector('#idcaissiers').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idcaissiers').options.length = 1;
                        }
                        
                    };
                    httpInfosgar.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgar.send();
                };
        e.onclick = function () {
        let tickForm = document.querySelector('#tickForm');
            tickForm.setAttribute('action', `${APP_ROOT}/Rapport/exoreports/${e.dataset.ekey}/${e.dataset.idgares}`);
        }

    })
});
;
/* --- adreportjsesc.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adreportjsesc').forEach(function (e) 
    {
        document.querySelector('h3#Titlerepesc').innerHTML = `EXERCICE MENSUEL TICKET GUICHETIER ESCAL`;

        let infgar = document.querySelector('#departgaridentifesc');
        
        if (infgar !== null) 
        infgar.onchange = () => {
            let httpInfosgar;
            if (window.XMLHttpRequest) {
                httpInfosgar = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgar = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idcaissiersesc').options.length = 1;

                    var verificatgar = document.querySelector('#departgaridentifesc').value;
                    
                    httpInfosgar.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeusesesc/${verificatgar}`, true);
                    httpInfosgar.onload = () => {
                        const infosgar = JSON.parse(httpInfosgar.responseText);
                        
                        if (Object.entries(infosgar).length > 0) {                            
                        
                                for (let key in Object.entries(infosgar)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgar[key].roleattribut}`;
                                    opt.innerHTML = `${infosgar[key].username}`;
                                    document.querySelector('#idcaissiersesc').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idcaissiersesc').options.length = 1;
                        }
                        
                    };
                    httpInfosgar.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgar.send();
                };
        e.onclick = function () {
        let tickForm = document.querySelector('#tickFormesc');
            tickForm.setAttribute('action', `${APP_ROOT}/Rapport/exoreportsesc/${e.dataset.ekey}/${e.dataset.idgares}`);
        }

    })
});
;
/* --- adreportgl.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adreportgl').forEach(function (e) 
    {
        document.querySelector('h3#Titlereps').innerHTML = `ETAT GLOBAL TICKET GUICHETIER`;

        let infgars = document.querySelector('#garidentifs');
        
        if (infgars !== null) 
        infgars.onchange = () => {
            let httpInfosgars;
            if (window.XMLHttpRequest) {
                httpInfosgars = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgars = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idscaissier').options.length = 1;

                    var verificatgars = document.querySelector('#garidentifs').value;
                    
                    httpInfosgars.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgars}`, true);
                    httpInfosgars.onload = () => {
                        const infosgars = JSON.parse(httpInfosgars.responseText);
                        
                        if (Object.entries(infosgars).length > 0) {                            
                        
                                for (let key in Object.entries(infosgars)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgars[key].roleattribut}`;
                                    opt.innerHTML = `${infosgars[key].username}`;
                                    document.querySelector('#idscaissier').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idscaissier').options.length = 1;
                        }
                        
                    };
                    httpInfosgars.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgars.send();
                };
        e.onclick = function () {
        let tickForms = document.querySelector('#tickForms');
            tickForms.setAttribute('action', `${APP_ROOT}/Rapport/reports/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
;
/* --- adreportglesc.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adreportglesc').forEach(function (e) 
    {
        document.querySelector('h3#Titlerepsesc').innerHTML = `ETAT GLOBAL TICKET GUICHETIER ESCAL`;

        let infgars = document.querySelector('#garidentifsesc');
        
        if (infgars !== null) 
        infgars.onchange = () => {
            let httpInfosgars;
            if (window.XMLHttpRequest) {
                httpInfosgars = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgars = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idscaissieresc').options.length = 1;

                    var verificatgars = document.querySelector('#garidentifsesc').value;
                    
                    httpInfosgars.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeusesesc/${verificatgars}`, true);
                    httpInfosgars.onload = () => {
                        const infosgars = JSON.parse(httpInfosgars.responseText);
                        
                        if (Object.entries(infosgars).length > 0) {                            
                        
                                for (let key in Object.entries(infosgars)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgars[key].roleattribut}`;
                                    opt.innerHTML = `${infosgars[key].username}`;
                                    document.querySelector('#idscaissieresc').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idscaissieresc').options.length = 1;
                        }
                        
                    };
                    httpInfosgars.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgars.send();
                };
        e.onclick = function () {
        let tickForms = document.querySelector('#tickFormsesc');
            tickForms.setAttribute('action', `${APP_ROOT}/Rapport/reportsesc/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
;
/* --- adreportglcours.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adreportglcours').forEach(function (e) 
    {
        document.querySelector('h3#Titlexpglobg').innerHTML = `ETAT GLOBAL COURRIER GUICHETIER`;

        let expinfosg = document.querySelector('#garesg');
        
        if (expinfosg !== null) 
        expinfosg.onchange = () => {
            let httpInforsgexpg;
            if (window.XMLHttpRequest) {
                httpInforsgexpg = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInforsgexpg = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idcaisseg').options.length = 1;

                    var expeverifivendg = document.querySelector('#garesg').value;
                    
                    httpInforsgexpg.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${expeverifivendg}`, true);
                    httpInforsgexpg.onload = () => {
                        const exinfosgsg = JSON.parse(httpInforsgexpg.responseText);
                        
                        if (Object.entries(exinfosgsg).length > 0) {                            
                        
                                for (let key in Object.entries(exinfosgsg)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${exinfosgsg[key].roleattribut}/${exinfosgsg[key].first_name} ${exinfosgsg[key].last_name}`;
                                    opt.innerHTML = `${exinfosgsg[key].username}`;
                                    document.querySelector('#idcaisseg').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idcaisseg').options.length = 1;
                        }
                        
                    };
                    httpInforsgexpg.setRequestHeader('Content-Type', 'application/json');
                    httpInforsgexpg.send();
                };
        e.onclick = function () {
            let expglobFormsg = document.querySelector('#expglobFormsg');
            expglobFormsg.setAttribute('action', `${APP_ROOT}/Rapport/etatsglcourrier/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
;
/* --- adreportglcoursesc.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adreportglcoursesc').forEach(function (e) 
    {
        document.querySelector('h3#Titlexpglobgesc').innerHTML = `ETAT GLOBAL COURRIERESCAL GUICHETIER`;

        let expinfosg = document.querySelector('#garesgesc');
        
        if (expinfosg !== null) 
        expinfosg.onchange = () => {
            let httpInforsgexpg;
            if (window.XMLHttpRequest) {
                httpInforsgexpg = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInforsgexpg = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idcaissegesc').options.length = 1;

                    var expeverifivendg = document.querySelector('#garesgesc').value;
                    
                    httpInforsgexpg.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${expeverifivendg}`, true);
                    httpInforsgexpg.onload = () => {
                        const exinfosgsg = JSON.parse(httpInforsgexpg.responseText);
                        
                        if (Object.entries(exinfosgsg).length > 0) {                            
                        
                                for (let key in Object.entries(exinfosgsg)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${exinfosgsg[key].roleattribut}/${exinfosgsg[key].first_name} ${exinfosgsg[key].last_name}`;
                                    opt.innerHTML = `${exinfosgsg[key].username}`;
                                    document.querySelector('#idcaissegesc').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idcaissegesc').options.length = 1;
                        }
                        
                    };
                    httpInforsgexpg.setRequestHeader('Content-Type', 'application/json');
                    httpInforsgexpg.send();
                };
        e.onclick = function () {
            let expglobFormsg = document.querySelector('#expglobFormsgesc');
            expglobFormsg.setAttribute('action', `${APP_ROOT}/Rapport/etatsglcourrieresc/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
;
/* --- adreportgldepcour.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adreportgldepcour').forEach(function (e) 
    {
        document.querySelector('h3#Titlerepscourdep').innerHTML = `RECAP DEPENSE COURRIER`;

        let infgarscrdep = document.querySelector('#garidentifscourdep');
        
        if (infgarscrdep !== null) 
        infgarscrdep.onchange = () => {
            let httpInfosgarscrdep;
            if (window.XMLHttpRequest) {
                httpInfosgarscrdep = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgarscrdep = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idscaissiercourdep').options.length = 1;

                    var verificatgarscrdep = document.querySelector('#garidentifscourdep').value;
                    
                    httpInfosgarscrdep.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgarscrdep}`, true);
                    httpInfosgarscrdep.onload = () => {
                        const infosgarscrdep = JSON.parse(httpInfosgarscrdep.responseText);
                        
                        if (Object.entries(infosgarscrdep).length > 0) {                            
                        
                                for (let key in Object.entries(infosgarscrdep)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgarscrdep[key].roleattribut}`;
                                    opt.innerHTML = `${infosgarscrdep[key].username}`;
                                    document.querySelector('#idscaissiercourdep').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idscaissiercourdep').options.length = 1;
                        }
                        
                    };
                    httpInfosgarscrdep.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgarscrdep.send();
                };
        e.onclick = function () {
        let tickFormscrdep = document.querySelector('#tickFormscourdep');
            tickFormscrdep.setAttribute('action', `${APP_ROOT}/Rapport/tridepensescour/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
;
/* --- adreportpli.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adreportpli').forEach(function (e) 
    {
        document.querySelector('h3#Titlexpglob').innerHTML = `EXERCICE MENSUEL COURRIER GUICHETIER`;

        let expinfos = document.querySelector('#gares');
        
        if (expinfos !== null) 
        expinfos.onchange = () => {
            let httpInforsgexp;
            if (window.XMLHttpRequest) {
                httpInforsgexp = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInforsgexp = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idcaisse').options.length = 1;

                    var expeverifivend = document.querySelector('#gares').value;
                    
                    httpInforsgexp.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${expeverifivend}`, true);
                    httpInforsgexp.onload = () => {
                        const exinfosgs = JSON.parse(httpInforsgexp.responseText);
                        
                        if (Object.entries(exinfosgs).length > 0) {                            
                        
                                for (let key in Object.entries(exinfosgs)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${exinfosgs[key].roleattribut}/${exinfosgs[key].first_name} ${exinfosgs[key].last_name}`;
                                    opt.innerHTML = `${exinfosgs[key].username}`;
                                    document.querySelector('#idcaisse').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idcaisse').options.length = 1;
                        }
                        
                    };
                    httpInforsgexp.setRequestHeader('Content-Type', 'application/json');
                    httpInforsgexp.send();
                };
        e.onclick = function () {
            let expglobForms = document.querySelector('#expglobForms');
            expglobForms.setAttribute('action', `${APP_ROOT}/Rapport/etatsplis1/${e.dataset.ekey}/${e.dataset.idgares}`);
        }

    })
});
;
/* --- adreportpliesc.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adreportpliesc').forEach(function (e) 
    {
        document.querySelector('h3#Titlexpglobesc').innerHTML = `EXERCICE MENSUEL COURRIERESCAL GUICHETIER`;

        let expinfos = document.querySelector('#garesesc');
        
        if (expinfos !== null) 
        expinfos.onchange = () => {
            let httpInforsgexp;
            if (window.XMLHttpRequest) {
                httpInforsgexp = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInforsgexp = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idcaisseesc').options.length = 1;

                    var expeverifivend = document.querySelector('#garesesc').value;
                    
                    httpInforsgexp.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${expeverifivend}`, true);
                    httpInforsgexp.onload = () => {
                        const exinfosgs = JSON.parse(httpInforsgexp.responseText);
                        
                        if (Object.entries(exinfosgs).length > 0) {                            
                        
                                for (let key in Object.entries(exinfosgs)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${exinfosgs[key].roleattribut}/${exinfosgs[key].first_name} ${exinfosgs[key].last_name}`;
                                    opt.innerHTML = `${exinfosgs[key].username}`;
                                    document.querySelector('#idcaisseesc').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idcaisseesc').options.length = 1;
                        }
                        
                    };
                    httpInforsgexp.setRequestHeader('Content-Type', 'application/json');
                    httpInforsgexp.send();
                };
        e.onclick = function () {
            let expglobForms = document.querySelector('#expglobFormsesc');
            expglobForms.setAttribute('action', `${APP_ROOT}/Rapport/etatsplis1/${e.dataset.ekey}/${e.dataset.idgares}`);
        }

    })
});
;
/* --- adreportversgljs.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adreportversgljs').forEach(function (e) 
    {
        document.querySelector('h3#Titlerepversgl').innerHTML = `TRI REPORT GLOBAL DES RECETTES`;

        let infgarvers = document.querySelector('#departgaridentifversgl');
        
        if (infgarvers !== null) 
        infgarvers.onchange = () => {
            let httpInfosgarvers;
            if (window.XMLHttpRequest) {
                httpInfosgarvers = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgarvers = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idcaissiersversgl').options.length = 1;

                    var verificatgarvers = document.querySelector('#departgaridentifversgl').value;
                    
                    httpInfosgarvers.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgarvers}`, true);
                    httpInfosgarvers.onload = () => {
                        const infosgarvers = JSON.parse(httpInfosgarvers.responseText);
                        
                        if (Object.entries(infosgarvers).length > 0) {                            
                        
                                for (let key in Object.entries(infosgarvers)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgarvers[key].roleattribut}`;
                                    opt.innerHTML = `${infosgarvers[key].username}`;
                                    document.querySelector('#idcaissiersversgl').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idcaissiersversgl').options.length = 1;
                        }
                        
                    };
                    httpInfosgarvers.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgarvers.send();
                };
        e.onclick = function () {
        let tickversForm = document.querySelector('#tickversglForm');
            tickversForm.setAttribute('action', `${APP_ROOT}/Rapport/exoreportsversgl/${e.dataset.ekey}/${e.dataset.idgares}`);
        }

    })
});
;
/* --- adtrio.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtrio').forEach(function (e) 
    {
        document.querySelector('h3#caisTitle').innerHTML = `VERSEMENT TICKET GUICHETIER`;
        let infgares = document.querySelector('#encaisgars');
        
        if (infgares !== null) 
        infgares.onchange = () => {
            let httpInfoss;
            if (window.XMLHttpRequest) {
                httpInfoss = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfoss = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeuses').options.length = 1;

                    var verificatgares = document.querySelector('#encaisgars').value;
                    
                    httpInfoss.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trioperateur/${verificatgares}`, true);
                    httpInfoss.onload = () => {
                        const infoss = JSON.parse(httpInfoss.responseText);
                        
                        if (Object.entries(infoss).length > 0) {                            
                        
                                for (let key in Object.entries(infoss)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infoss[key].roleattribut}`;
                                    opt.innerHTML = `${infoss[key].username}`;
                                    document.querySelector('#idvendeuses').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idvendeuses').options.length = 1;
                        }
                        
                    };
                    httpInfoss.setRequestHeader('Content-Type', 'application/json');
                    httpInfoss.send();
                };
        e.onclick = function () {
        let encaisForm = document.querySelector('#encaismentForm');
            encaisForm.setAttribute('action', `${APP_ROOT}/Rapport/triencaissements/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
;
/* --- adtriocour.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtriocour').forEach(function (e) 
    {
        document.querySelector('h3#caisTitlecour').innerHTML = `VERSEMENT COURRIER GUICHETIER`;
        let infgarescr = document.querySelector('#encaisgarscour');
        
        if (infgarescr !== null) 
        infgarescr.onchange = () => {
            let httpInfosscr;
            if (window.XMLHttpRequest) {
                httpInfosscr = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosscr = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeusescour').options.length = 1;

                    var verificatgarescr = document.querySelector('#encaisgarscour').value;
                    
                    httpInfosscr.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgarescr}`, true);
                    httpInfosscr.onload = () => {
                        const infosscr = JSON.parse(httpInfosscr.responseText);
                        
                        if (Object.entries(infosscr).length > 0) {                            
                    
                                for (let key in Object.entries(infosscr)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosscr[key].roleattribut}`;
                                    opt.innerHTML = `${infosscr[key].username}`;
                                    document.querySelector('#idvendeusescour').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idvendeusescour').options.length = 1;
                        }
                        
                    };
                    httpInfosscr.setRequestHeader('Content-Type', 'application/json');
                    httpInfosscr.send();
                };
        e.onclick = function () {
        let encaisFormcr = document.querySelector('#encaismentFormcour');
            encaisFormcr.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementscour/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
;
/* --- adtrioexo.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtrioexo').forEach(function (e) 
    {
        document.querySelector('h3#caisTitleexo').innerHTML = `BROUILLARD(EXERCICE) TICKET`;
        let infgares = document.querySelector('#encaisgarsexo');
        
        if (infgares !== null) 
        infgares.onchange = () => {
            let httpInfoss;
            if (window.XMLHttpRequest) {
                httpInfoss = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfoss = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeusesexo').options.length = 1;

                    var verificatgares = document.querySelector('#encaisgarsexo').value;
                    
                    httpInfoss.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgares}`, true);
                    httpInfoss.onload = () => {
                        const infoss = JSON.parse(httpInfoss.responseText);
                        
                        if (Object.entries(infoss).length > 0) {                            
                        
                                for (let key in Object.entries(infoss)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infoss[key].roleattribut}`;
                                    opt.innerHTML = `${infoss[key].username}`;
                                    document.querySelector('#idvendeusesexo').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idvendeusesexo').options.length = 1;
                        }
                        
                    };
                    httpInfoss.setRequestHeader('Content-Type', 'application/json');
                    httpInfoss.send();
                };
        e.onclick = function () {
        let encaisForm = document.querySelector('#encaismentFormexo');
            encaisForm.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementsexo/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
;
/* --- adtrioexoesc.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtrioexoesc').forEach(function (e) 
    {
        document.querySelector('h3#caisTitleexoesc').innerHTML = `BROUILLARD(EXERCICE)TICKET ESCAL`;
        let infgares = document.querySelector('#encaisgarsexoesc');
        
        if (infgares !== null) 
        infgares.onchange = () => {
            let httpInfoss;
            if (window.XMLHttpRequest) {
                httpInfoss = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfoss = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeusesexoesc').options.length = 1;

                    var verificatgares = document.querySelector('#encaisgarsexoesc').value;
                    
                    httpInfoss.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgares}`, true);
                    httpInfoss.onload = () => {
                        const infoss = JSON.parse(httpInfoss.responseText);
                        
                        if (Object.entries(infoss).length > 0) {                            
                        
                                for (let key in Object.entries(infoss)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infoss[key].roleattribut}`;
                                    opt.innerHTML = `${infoss[key].username}`;
                                    document.querySelector('#idvendeusesexoesc').add(opt);
                                }
                        } 
                        else {
                            document.querySelector('#idvendeusesexoesc').options.length = 1;
                        }
                        
                    };
                    httpInfoss.setRequestHeader('Content-Type', 'application/json');
                    httpInfoss.send();
                };
        e.onclick = function () {
        let encaisForm = document.querySelector('#encaismentFormexoesc');
            encaisForm.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementsexoesc/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
;
/* --- adtrioexoplis.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtrioexoplis').forEach(function (e) 
    {
        document.querySelector('h3#Titlexpglobvers').innerHTML = `BROUILLARD(EXERCICE) COURRIER`;
        let infgares = document.querySelector('#garesvers');
        
        if (infgares !== null) 
        infgares.onchange = () => {
            let httpInfoss;
            if (window.XMLHttpRequest) {
                httpInfoss = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfoss = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idcaissevers').options.length = 1;

                    var verificatgares = document.querySelector('#garesvers').value;
                    
                    httpInfoss.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgares}`, true);
                    httpInfoss.onload = () => {
                        const infoss = JSON.parse(httpInfoss.responseText);
                        
                        if (Object.entries(infoss).length > 0) {                            
                        
                                for (let key in Object.entries(infoss)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infoss[key].roleattribut}/${infoss[key].first_name} ${infoss[key].last_name}`;
                                    opt.innerHTML = `${infoss[key].username}`;
                                    document.querySelector('#idcaissevers').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idcaissevers').options.length = 1;
                        }
                        
                    };
                    httpInfoss.setRequestHeader('Content-Type', 'application/json');
                    httpInfoss.send();
                };
        e.onclick = function () {
        let encaisFormv = document.querySelector('#expglobFormsvers');
            encaisFormv.setAttribute('action', `${APP_ROOT}/Rapport/etatsverseplis/${e.dataset.ekey}/${e.dataset.idgare}`);
        }

    })
});
;
/* --- adtrioexoplisesc.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtrioexoplisesc').forEach(function (e) 
    {
        document.querySelector('h3#Titlexpglobversesc').innerHTML = `BROUILLARD(EXERCICE) COURRIERESCAL`;
        let infgares = document.querySelector('#garesversesc');
        
        if (infgares !== null) 
        infgares.onchange = () => {
            let httpInfoss;
            if (window.XMLHttpRequest) {
                httpInfoss = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfoss = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idcaisseversesc').options.length = 1;

                    var verificatgares = document.querySelector('#garesversesc').value;
                    
                    httpInfoss.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgares}`, true);
                    httpInfoss.onload = () => {
                        const infoss = JSON.parse(httpInfoss.responseText);
                        
                        if (Object.entries(infoss).length > 0) {                            
                        
                                for (let key in Object.entries(infoss)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infoss[key].roleattribut}/${infoss[key].first_name} ${infoss[key].last_name}`;
                                    opt.innerHTML = `${infoss[key].username}`;
                                    document.querySelector('#idcaisseversesc').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idcaisseversesc').options.length = 1;
                        }
                        
                    };
                    httpInfoss.setRequestHeader('Content-Type', 'application/json');
                    httpInfoss.send();
                };
        e.onclick = function () {
        let encaisFormv = document.querySelector('#expglobFormsversesc');
            encaisFormv.setAttribute('action', `${APP_ROOT}/Rapport/etatsverseplisesc/${e.dataset.ekey}/${e.dataset.idgare}`);
        }

    })
});
;
/* --- adtriobag.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtriobag').forEach(function (e) 
    {
        document.querySelector('h3#caisTitlebag').innerHTML = `VERSEMENT BAGAGES`;
        let infgares = document.querySelector('#encaisgarsbag');
        
        if (infgares !== null) 
        infgares.onchange = () => {
            let httpInfoss;
            if (window.XMLHttpRequest) {
                httpInfoss = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfoss = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeusesbag').options.length = 1;

                    var verificatgares = document.querySelector('#encaisgarsbag').value;
                    
                    httpInfoss.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeusesop/${verificatgares}`, true);
                    httpInfoss.onload = () => {
                        const infoss = JSON.parse(httpInfoss.responseText);
                        
                        if (Object.entries(infoss).length > 0) {                            
                        
                                for (let key in Object.entries(infoss)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infoss[key].roleattribut}`;
                                    opt.innerHTML = `${infoss[key].username}`;
                                    document.querySelector('#idvendeusesbag').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idvendeusesbag').options.length = 1;
                        }
                        
                    };
                    httpInfoss.setRequestHeader('Content-Type', 'application/json');
                    httpInfoss.send();
                };
        e.onclick = function () {
        let encaisForm = document.querySelector('#encaismentFormbag');
            encaisForm.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementsbag/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
;
/* --- adtrioexobag.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtrioexobag').forEach(function (e) 
    {
        document.querySelector('h3#Titlexpglobversbg').innerHTML = `BROUILLARD(EXERCICE) BAGAGES`;
        let infgaresb = document.querySelector('#departgarexobge');
        
        if (infgaresb !== null) 
        infgaresb.onchange = () => {
            let httpInfossb;
            if (window.XMLHttpRequest) {
                httpInfossb = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfossb = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#dvendeuseidexobg').options.length = 1;

                    var verificatgaresb = document.querySelector('#departgarexobge').value;
                    
                    httpInfossb.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgaresb}`, true);
                    httpInfossb.onload = () => {
                        const infossb = JSON.parse(httpInfossb.responseText);
                        
                        if (Object.entries(infossb).length > 0) {                            
                        
                                for (let key in Object.entries(infossb)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infossb[key].roleattribut}/${infossb[key].first_name} ${infossb[key].last_name}`;
                                    opt.innerHTML = `${infossb[key].username}`;
                                    document.querySelector('#dvendeuseidexobg').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#dvendeuseidexobg').options.length = 1;
                        }
                        
                    };
                    httpInfossb.setRequestHeader('Content-Type', 'application/json');
                    httpInfossb.send();
                };
        e.onclick = function () {
        let encaisFormvb = document.querySelector('#expglobFormsversbg');
            encaisFormvb.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementsexobag/${e.dataset.ekey}/${e.dataset.idgare}`);
        }

    })
});
;
/* --- adtrioexobagesc.js --- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adtrioexobagesc').forEach(function (e) 
    {
        document.querySelector('h3#Titlexpglobversbgesc').innerHTML = `BROUILLARD(EXERCICE) BAGAGESESCAL`;
        let infgaresbe = document.querySelector('#departgarexobgeesc');
        
        if (infgaresbe !== null) 
        infgaresbe.onchange = () => {
            let httpInfossbe;
            if (window.XMLHttpRequest) {
                httpInfossbe = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfossbe = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#dvendeuseidexobgesc').options.length = 1;

                    var verificatgaresbe = document.querySelector('#departgarexobgeesc').value;
                    
                    httpInfossbe.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeuses/${verificatgaresbe}`, true);
                    httpInfossbe.onload = () => {
                        const infossbe = JSON.parse(httpInfossbe.responseText);
                        
                        if (Object.entries(infossbe).length > 0) {                            
                        
                                for (let key in Object.entries(infossbe)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infossbe[key].roleattribut}/${infossbe[key].first_name} ${infossbe[key].last_name}`;
                                    opt.innerHTML = `${infossbe[key].username}`;
                                    document.querySelector('#dvendeuseidexobgesc').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#dvendeuseidexobgesc').options.length = 1;
                        }
                        
                    };
                    httpInfossbe.setRequestHeader('Content-Type', 'application/json');
                    httpInfossbe.send();
                };
        e.onclick = function () {
        let encaisFormvbe = document.querySelector('#expglobFormsversbgesc');
            encaisFormvbe.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementsexobagesc/${e.dataset.ekey}/${e.dataset.idgare}`);
        }

    })
});
;
/* --- advers.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.advers').forEach(function (e) 
    {
        document.querySelector('h3#caiTitle').innerHTML = `TRI DES ETATS DE VERSEMENT PAR AXE`;

        let infgares = document.querySelector('#encaisgar');
        
        if (infgares !== null) 
        infgares.onchange = () => {
            let httpInfoss;
            if (window.XMLHttpRequest) {
                httpInfoss = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfoss = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeuse').options.length = 1;

                    var verificatgares = document.querySelector('#encaisgar').value;
                    
                    httpInfoss.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trioperateur/${verificatgares}`, true);
                    httpInfoss.onload = () => {
                        const infoss = JSON.parse(httpInfoss.responseText);
                        
                        if (Object.entries(infoss).length > 0) {                            
                        
                                for (let key in Object.entries(infoss)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infoss[key].roleattribut}`;
                                    opt.innerHTML = `${infoss[key].username}`;
                                    document.querySelector('#idvendeuse').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idvendeuse').options.length = 1;
                        }
                        
                    };
                    httpInfoss.setRequestHeader('Content-Type', 'application/json');
                    httpInfoss.send();
                };
        e.onclick = function () {
        let encaisForms = document.querySelector('#encaisForms');
            encaisForms.setAttribute('action', `${APP_ROOT}/Rapport/triencaissement/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
;
/* --- adverssg.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.adverssg').forEach(function (e) 
    {
        document.querySelector('h3#caiTitlesg').innerHTML = `RECETTE GLOBALE TICKET PAR GARE`;

        let infgares = document.querySelector('#encaisgarsg');
        
        if (infgares !== null) 
        infgares.onchange = () => {
            let httpInfoss;
            if (window.XMLHttpRequest) {
                httpInfoss = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfoss = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeusesg').options.length = 1;

                    var verificatgares = document.querySelector('#encaisgarsg').value;
                    
                    httpInfoss.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trioperateur/${verificatgares}`, true);
                    httpInfoss.onload = () => {
                        const infoss = JSON.parse(httpInfoss.responseText);
                        
                        if (Object.entries(infoss).length > 0) {                            
                        
                                for (let key in Object.entries(infoss)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infoss[key].roleattribut}`;
                                    opt.innerHTML = `${infoss[key].username}`;
                                    document.querySelector('#idvendeusesg').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idvendeusesg').options.length = 1;
                        }
                        
                    };
                    httpInfoss.setRequestHeader('Content-Type', 'application/json');
                    httpInfoss.send();
                };
        e.onclick = function () {
        let encaisForms = document.querySelector('#encaisFormssg');
            encaisForms.setAttribute('action', `${APP_ROOT}/Rapport/triencaissementsg/${e.dataset.ekey}/${e.dataset.idsgare}/${e.dataset.idsggare}`);
        }

    })
});
;
/* --- recaptbagexop.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.recaptbagexop').forEach(function (e) 
    {
        document.querySelector('h3#optitle').innerHTML = `EXERCICE MENSUEL BAGAGE OPERATEUR`;

        let infgars = document.querySelector('#departgardpbagop');
        
        if (infgars !== null) 
        infgars.onchange = () => {
            let httpInfosgars;
            if (window.XMLHttpRequest) {
                httpInfosgars = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgars = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeuseop').options.length = 1;

                    var verificatgars = document.querySelector('#departgardpbagop').value;
                    
                    httpInfosgars.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeusesop/${verificatgars}`, true);
                    httpInfosgars.onload = () => {
                        const infosgars = JSON.parse(httpInfosgars.responseText);
                        
                        if (Object.entries(infosgars).length > 0) {                            
                        
                                for (let key in Object.entries(infosgars)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgars[key].roleattribut}`;
                                    opt.innerHTML = `${infosgars[key].username}`;
                                    document.querySelector('#idvendeuseop').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idvendeuseop').options.length = 1;
                        }
                        
                    };
                    httpInfosgars.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgars.send();
                };
        e.onclick = function () {
        let tickFormsgl = document.querySelector('#tickFormop');
            tickFormsgl.setAttribute('action', `${APP_ROOT}/Rapport/exercicesbagop/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
;
/* --- recaptbagexopesc.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.recaptbagexopesc').forEach(function (e) 
    {
        document.querySelector('h3#optitleesc').innerHTML = `EXERCICE MENSUEL BAGAGEESCAL OPERATEUR`;

        let infgars = document.querySelector('#departgardpbagopesc');
        
        if (infgars !== null) 
        infgars.onchange = () => {
            let httpInfosgars;
            if (window.XMLHttpRequest) {
                httpInfosgars = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgars = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeuseopesc').options.length = 1;

                    var verificatgars = document.querySelector('#departgardpbagopesc').value;
                    
                    httpInfosgars.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeusesesc/${verificatgars}`, true);
                    httpInfosgars.onload = () => {
                        const infosgars = JSON.parse(httpInfosgars.responseText);
                        
                        if (Object.entries(infosgars).length > 0) {                            
                        
                                for (let key in Object.entries(infosgars)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgars[key].roleattribut}`;
                                    opt.innerHTML = `${infosgars[key].username}`;
                                    document.querySelector('#idvendeuseopesc').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idvendeuseopesc').options.length = 1;
                        }
                        
                    };
                    httpInfosgars.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgars.send();
                };
        e.onclick = function () {
        let tickFormsgl = document.querySelector('#tickFormopesc');
            tickFormsgl.setAttribute('action', `${APP_ROOT}/Rapport/exercicesbagopesc/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
;
/* --- recaptbagglop.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.recaptbagglop').forEach(function (e) 
    {
        document.querySelector('h3#optitlegl').innerHTML = `ETAT GLOBAL BAGAGE OPERATEUR`;

        let infgars = document.querySelector('#departgardpbagopgl');
        
        if (infgars !== null) 
        infgars.onchange = () => {
            let httpInfosgars;
            if (window.XMLHttpRequest) {
                httpInfosgars = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgars = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeuseopgl').options.length = 1;

                    var verificatgars = document.querySelector('#departgardpbagopgl').value;
                    
                    httpInfosgars.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeusesop/${verificatgars}`, true);
                    httpInfosgars.onload = () => {
                        const infosgars = JSON.parse(httpInfosgars.responseText);
                        
                        if (Object.entries(infosgars).length > 0) {                            
                        
                                for (let key in Object.entries(infosgars)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgars[key].roleattribut}`;
                                    opt.innerHTML = `${infosgars[key].username}`;
                                    document.querySelector('#idvendeuseopgl').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idvendeuseopgl').options.length = 1;
                        }
                        
                    };
                    httpInfosgars.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgars.send();
                };
        e.onclick = function () {
        let tickFormsgl = document.querySelector('#tickFormopgl');
            tickFormsgl.setAttribute('action', `${APP_ROOT}/Rapport/reportbaggl/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
;
/* --- recaptbagglopesc.js --- */
document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.recaptbagglopesc').forEach(function (e) 
    {
        document.querySelector('h3#optitleglesc').innerHTML = `ETAT GLOBAL BAGAGEESCAL OPERATEUR`;

        let infgars = document.querySelector('#departgardpbagopglesc');
        
        if (infgars !== null) 
        infgars.onchange = () => {
            let httpInfosgars;
            if (window.XMLHttpRequest) {
                httpInfosgars = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpInfosgars = new ActiveXObject("Microsoft.XMLHTTP");
            }
                document.querySelector('#idvendeuseopglesc').options.length = 1;

                    var verificatgars = document.querySelector('#departgardpbagopglesc').value;
                    
                    httpInfosgars.open('GET', window.location.origin + `${APP_ROOT}/utilisateurs/trivendeusesesc/${verificatgars}`, true);
                    httpInfosgars.onload = () => {
                        const infosgars = JSON.parse(httpInfosgars.responseText);
                        
                        if (Object.entries(infosgars).length > 0) {                            
                        
                                for (let key in Object.entries(infosgars)) {
                                    let opt = document.createElement('option');
                                    opt.value = `${infosgars[key].roleattribut}`;
                                    opt.innerHTML = `${infosgars[key].username}`;
                                    document.querySelector('#idvendeuseopglesc').add(opt);
                                    
                                }
                        } 
                        else {
                            document.querySelector('#idvendeuseopglesc').options.length = 1;
                        }
                        
                    };
                    httpInfosgars.setRequestHeader('Content-Type', 'application/json');
                    httpInfosgars.send();
                };
        e.onclick = function () {
        let tickFormsgl = document.querySelector('#tickFormopglesc');
            tickFormsgl.setAttribute('action', `${APP_ROOT}/Rapport/reportbagglesc/${e.dataset.ekey}/${e.dataset.idsgare}`);
        }

    })
});
