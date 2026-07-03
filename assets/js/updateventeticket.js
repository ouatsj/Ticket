document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.addventeticket').forEach(function (e) 
    {
        document.querySelector('h3#taTitle').innerHTML = `VENTE DE TICKET`;

            let ar= document.querySelector('#arrsgare');
            if (ar !== null)
            ar.onchange = () => {
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
                document.querySelector('#psiegesitines1').options.length = 1;
                document.querySelector('#idcheminsheur1').options.length = 1;
                document.querySelector('#psiegesitines2').options.length = 1;
                document.querySelector('#idcheminsheur2').options.length = 1;
                document.querySelector('#psiegesitines3').options.length = 1;
                document.querySelector('#quartier1').options.length = 1;
                document.querySelector('#quartier2').options.length = 1;
                document.querySelector('#quartier3').options.length = 1;
                document.querySelector('#quartier4').options.length = 1;
		          document.querySelector('#prix_axe').value = '';
                  document.querySelector('#program').value = '';
                  document.querySelector('#program1').value = '';
                  document.querySelector('#tarifattrib').value = '';
                  
                    const typgare = document.querySelector('#arrsgare').value;
                    let httptypequart;
                    httptypequart = new XMLHttpRequest();
                    
                    httptypequart.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${typgare}`, true);
                    httptypequart.onload = () => 
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
                        

                    };
                    httptypequart.setRequestHeader('Content-Type', 'application/json');
                    httptypequart.send();
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
                    document.querySelector('#psiegesitines1').options.length = 1;
                    document.querySelector('#idcheminsheur1').options.length = 1;
                    document.querySelector('#psiegesitines2').options.length = 1;
                    document.querySelector('#idcheminsheur2').options.length = 1;
                    document.querySelector('#psiegesitines3').options.length = 1;
                    document.querySelector('#quartier1').options.length = 1;
                    document.querySelector('#quartier2').options.length = 1;
                    document.querySelector('#quartier3').options.length = 1;
                    document.querySelector('#quartier4').options.length = 1;


                    let httpRequetes;
                    
                    if (window.XMLHttpRequest) {
                        httpRequetes = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {
                        httpRequetes = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    
                        var depa = document.querySelector('#depargare').value;
                        var arr = document.querySelector('#arrsgare').value;
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
                                                        document.querySelector('#quartier3').style.display = 'none';
                                                        document.querySelector('#quartier4').style.display = 'none';
                                                        document.querySelector('#idquart1').style.display = 'none';
                                                        document.querySelector('#idquart2').style.display = 'none';
                                                        document.querySelector('#idquart3').style.display = 'none';
                                                        document.querySelector('#idquart4').style.display = 'none';

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
                                                        document.querySelector('#idquart').style.display = 'block';
                                                        document.querySelector('#quartier').style.display = 'block';

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
                                                                    document.querySelector('#quartier2').style.display = 'block';
                                                                    document.querySelector('#idquart2').style.display = 'block';
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
                                                                    document.querySelector('#idquart3').style.display = 'block';

                                                                    document.querySelector('#arritin2').style.display = 'block';
                                                                    document.querySelector('#idchemins1').style.display = 'block';
                                                                    document.querySelector('#heureitin2').style.display = 'block';
                                                                    document.querySelector('#idcheminsheur1').style.display = 'block';
                                                                    document.querySelector('#siegitine2').style.display = 'block';
                                                                    document.querySelector('#psiegesitines2').style.display = 'block';
                                                                    document.querySelector('#quartier1').style.display = 'block';
                                                                    document.querySelector('#quartier2').style.display = 'block';
                                                                    document.querySelector('#quartier3').style.display = 'block';
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
                                                                    document.querySelector('#quartier4').style.display = 'block';    
                                                                    document.querySelector('#idquart1').style.display = 'block';
                                                                    document.querySelector('#idquart2').style.display = 'block';
                                                                    document.querySelector('#idquart3').style.display = 'block';
                                                                    document.querySelector('#idquart4').style.display = 'block';

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
                                                                document.querySelector('#idquart').style.display = 'none';
                                                                document.querySelector('#quartier').style.display = 'none';


                                                                document.querySelector('#itinecode').value = `${donitines[0].code_itineraires}`;

                                                                
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
                                                                    

                                                                var typgare1 = document.querySelector('#itinecode').value;
                                                                var post_typgare1 = typgare1.split('-');
                                                                var seltypgare1 = post_typgare1[0];
                                                                var typgaresel = post_typgare1[1];
                                                                    let httptypequart1;
                                                                    httptypequart1 = new XMLHttpRequest();
                                                                    
                                                                    httptypequart1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${typgaresel}`, true);
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
                                                                                        httpRequetteit.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponiblebus/${cdprogit}/${dpt_dateitine}/${lgit}/${timit}/${dbit}/${fnit}`, true);
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
                                                                            const prostrans1 = document.querySelector('#programtrans1').value;
                                                                        httpSiegestrans.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisiegesnr/${prostrans1}/${sigstrans}`, true);
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
                                                                                document.querySelector('#erreurMess').innerHTML = `Siege déjà utilisé.`;                                                                   }
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
                                                                        let httpSiegeschemin;
                                                                        httpSiegeschemin = new XMLHttpRequest();
                                                                        
                                                                        const prostranschemin = document.querySelector('#idchemins')
                                                                        .options[document.querySelector('#idchemins').options.selectedIndex].value;

                                                                        var post_typgare2 = prostranschemin.split('-');
                                                                        var seltypgare2 = post_typgare2[0];
                                                                        var typgaresel1 = post_typgare2[1];
                                                                        let httptypequart2;
                                                                        httptypequart2 = new XMLHttpRequest();
                                                                        
                                                                        httptypequart2.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${typgaresel1}`, true);
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
                                                                        var datedepart = document.querySelector('#date_depheure').value;
                                                                        httpSiegeschemin.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranschemin}/${datedepart}`, true);
                                                                        httpSiegeschemin.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschem = JSON.parse(httpSiegeschemin.responseText);
                                                                                    if (Object.entries(dongtranschem).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtranschem)) {
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dongtranschem[key].code_progr}/${dongtranschem[key].intervalle1}/${dongtranschem[key].intervalle2}/${dongtranschem[key].id_ligneheure}/${dongtranschem[key].prix}/${dongtranschem[key].depart_code}`;
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
                                                                                const transselitine = document.querySelector('#idcheminsheur')
                                                                            .options[document.querySelector('#idcheminsheur').options.selectedIndex].value;
                                                                                var post_trans = transselitine.split('/');
                                                                            var itinetras = post_trans[0];
                                                                            var dbitra = post_trans[1];
                                                                            var fnitra = post_trans[2];
                                                                            var lhertra = post_trans[3];
                                                                            var prixtra = post_trans[4];
                                                                                var itinetrasnr = post_trans[5];
                                                                                httpPrixittransite.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotransnr/${itinetrasnr}`, true);
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
                                                                        
                                                                                    httpRequetteitra.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletransnr/${itinetrasnr}/${dbitra}/${fnitra}`, true);
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
                                                                                var itinetrasnr1 = post_transnr1[0];
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

                                                                                httpSieges1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisiegesnr/${itinetrasnr1}/${sigs1}`, true);
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
                                                               

                                                                let opt1 = document.createElement('option');
                                                                opt1.value = `${donitines[2].code_itineraires}`;
                                                                opt1.innerHTML = `${donitines[2].nom_itineraires}`;
                                                                document.querySelector('#idchemins1').add(opt1);


                                                                var typgare1 = document.querySelector('#itinecode').value;
                                                                var post_typgare1 = typgare1.split('-');
                                                                var seltypgare1 = post_typgare1[0];
                                                                var typgaresel = post_typgare1[1];
                                                                    let httptypequart1;
                                                                    httptypequart1 = new XMLHttpRequest();
                                                                    
                                                                    httptypequart1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${typgaresel}`, true);
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
                                                                                        httpRequetteit.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponiblebus/${cdprogit}/${dpt_dateitine}/${lgit}/${timit}/${dbit}/${fnit}`, true);
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
                                                                            const prostrans1 = document.querySelector('#programtrans1').value;
                                                                        httpSiegestrans1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisiegesnr/${prostrans1}/${sigstrans}`, true);
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
                                                                        
                                                                        const prostranschemin = document.querySelector('#idchemins')
                                                                        .options[document.querySelector('#idchemins').options.selectedIndex].value;

                                                                        var post_typgare2 = prostranschemin.split('-');
                                                                        var seltypgare2 = post_typgare2[0];
                                                                        var typgaresel1 = post_typgare2[1];
                                                                        let httptypequart2;
                                                                        httptypequart2 = new XMLHttpRequest();
                                                                        
                                                                        httptypequart2.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${typgaresel1}`, true);
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

                                                                        var datedepart = document.querySelector('#date_depheure').value;
                                                                        
                                                                        httpSiegeschemin.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranschemin}/${datedepart}`, true);
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
                                                                                const transselitine = document.querySelector('#idcheminsheur')
                                                                            .options[document.querySelector('#idcheminsheur').options.selectedIndex].value;
                                                                                var post_trans = transselitine.split('/');
                                                                            var itinetras = post_trans[0];
                                                                            var dbitra = post_trans[1];
                                                                            var fnitra = post_trans[2];
                                                                            var lhertra = post_trans[3];
                                                                            var prixtra = post_trans[4];
                                                                                    var itinetrasnr = post_trans[5];
                                                                                httpPrixittransite.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotransnr/${itinetrasnr}`, true);
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
                                                                        
                                                                                    httpRequetteitra.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletransnr/${itinetrasnr}/${dbitra}/${fnitra}`, true);
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

                                                                                httpSieges1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisiegesnr/${itinetras1}/${sigs1}`, true);
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
                                                                       
                                                                        const prostranschemin32 = document.querySelector('#idchemins1')
                                                                        .options[document.querySelector('#idchemins1').options.selectedIndex].value;

                                                                        var post_typgare32 = prostranschemin32.split('-');
                                                                        var seltypgare32 = post_typgare32[0];
                                                                        var typgaresel31 = post_typgare32[1];
                                                                        let httptypequart32;
                                                                        httptypequart32 = new XMLHttpRequest();
                                                                        
                                                                        httptypequart32.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${typgaresel31}`, true);
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
                                                                        const prostranschemin1 = document.querySelector('#idchemins1')
                                                                        .options[document.querySelector('#idchemins1').options.selectedIndex].value;

                                                                        httpSiegeschemin1.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranschemin1}/${datedepart}`, true);
                                                                        httpSiegeschemin1.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschem1 = JSON.parse(httpSiegeschemin1.responseText);
                                                                                    if (Object.entries(dongtranschem1).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtranschem1)) {
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dongtranschem1[key].code_progr}/${dongtranschem1[key].intervalle1}/${dongtranschem1[key].intervalle2}/${dongtranschem1[key].id_ligneheure}/${dongtranschem1[key].prix}/${dongtranschem1[key].depart_code}`;
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
                                                                            const httpPrixittransite1 = new XMLHttpRequest();
                                                                                const transselitine1 = document.querySelector('#idcheminsheur1')
                                                                            .options[document.querySelector('#idcheminsheur1').options.selectedIndex].value;
                                                                                var post_trans1 = transselitine1.split('/');
                                                                            var itinetras1 = post_trans1[0];
                                                                            var dbitra1 = post_trans1[1];
                                                                            var fnitra1 = post_trans1[2];
                                                                            var lhertra1 = post_trans1[3];
                                                                            var prixtra1 = post_trans1[4];
                                                                                var itinetrasnr1 = post_trans1[5];
                                                                                httpPrixittransite1.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotransnr/${itinetrasnr1}`, true);
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
                                                                        
                                                                                    httpRequetteitra1.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletransnr/${itinetrasnr1}/${dbitra1}/${fnitra1}`, true);
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
                                                                                        document.querySelector('#psiegesitines1').options.length = 1;
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

                                                                                httpSieges2.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisiegesnr/${itinetras2}/${sigs2}`, true);
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

                                                                    var typgare1 = document.querySelector('#itinecode').value;
                                                                var post_typgare1 = typgare1.split('-');
                                                                var seltypgare1 = post_typgare1[0];
                                                                var typgaresel = post_typgare1[1];
                                                                    let httptypequart1;
                                                                    httptypequart1 = new XMLHttpRequest();
                                                                    
                                                                    httptypequart1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${typgaresel}`, true);
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
                                                                                        httpRequetteit.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponiblebus/${cdprogit}/${dpt_dateitine}/${lgit}/${timit}/${dbit}/${fnit}`, true);
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
                                                                            const prostrans1 = document.querySelector('#programtrans1').value;
                                                                        httpSiegestrans1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisiegesnr/${prostrans1}/${sigstrans}`, true);
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
                                                                        var datedepart = document.querySelector('#date_depheure').value;
                                                                        
                                                                        const prostranschemin = document.querySelector('#idchemins')
                                                                        .options[document.querySelector('#idchemins').options.selectedIndex].value;

                                                                        var post_typgare2 = prostranschemin.split('-');
                                                                        var seltypgare2 = post_typgare2[0];
                                                                        var typgaresel1 = post_typgare2[1];
                                                                        let httptypequart2;
                                                                        httptypequart2 = new XMLHttpRequest();
                                                                        
                                                                        httptypequart2.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${typgaresel1}`, true);
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
                                                                        
                                                                        httpSiegeschemin.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranschemin}/${datedepart}`, true);
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
                                                                                const httpPrixittransite = new XMLHttpRequest();
                                                                                    const transselitine = document.querySelector('#idcheminsheur')
                                                                                .options[document.querySelector('#idcheminsheur').options.selectedIndex].value;
                                                                                    var post_trans = transselitine.split('/');
                                                                                var itinetras = post_trans[0];
                                                                                var dbitra = post_trans[1];
                                                                                var fnitra = post_trans[2];
                                                                                var lhertra = post_trans[3];
                                                                                var prixtra = post_trans[4];
                                                                                        var itinetrasnr = post_trans[5];
                                                                                    httpPrixittransite.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotransnr/${itinetrasnr}`, true);
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
                                                                            
                                                                                        httpRequetteitra.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletransnr/${itinetrasnr}/${dbitra}/${fnitra}`, true);
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
                                                                                var itinetrasnr1 = post_trans1[5];
                                                                                let httpSieges1;
                                                                                httpSieges1 = new XMLHttpRequest();
                                                                                const sigs1 = document.querySelector('#psiegesitines1')
                                                                                .options[document.querySelector('#psiegesitines1').options.selectedIndex].value;
                                                                                //const pros1 = document.querySelector('#program').value;

                                                                                httpSieges1.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisiegesnr/${itinetrasnr1}/${sigs1}`, true);
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

                                                                        const prostranschemin32 = document.querySelector('#idchemins1')
                                                                        .options[document.querySelector('#idchemins1').options.selectedIndex].value;

                                                                        var post_typgare32 = prostranschemin32.split('-');
                                                                        var seltypgare32 = post_typgare32[0];
                                                                        var typgaresel31 = post_typgare32[1];
                                                                        let httptypequart32;
                                                                        httptypequart32 = new XMLHttpRequest();
                                                                        
                                                                        httptypequart32.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${typgaresel31}`, true);
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
                                                                        const prostranschemin1 = document.querySelector('#idchemins1')
                                                                        .options[document.querySelector('#idchemins1').options.selectedIndex].value;

                                                                        httpSiegeschemin1.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranschemin1}/${datedepart}`, true);
                                                                        httpSiegeschemin1.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschem1 = JSON.parse(httpSiegeschemin1.responseText);
                                                                                    if (Object.entries(dongtranschem1).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtranschem1)) {
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dongtranschem1[key].code_progr}/${dongtranschem1[key].intervalle1}/${dongtranschem1[key].intervalle2}/${dongtranschem1[key].id_ligneheure}/${dongtranschem1[key].prix}/${dongtranschem1[key].depart_code}`;
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
                                                                            const httpPrixittransite1 = new XMLHttpRequest();
                                                                                const transselitine1 = document.querySelector('#idcheminsheur1')
                                                                            .options[document.querySelector('#idcheminsheur1').options.selectedIndex].value;
                                                                                var post_trans1 = transselitine1.split('/');
                                                                            var itinetras1 = post_trans1[0];
                                                                            var dbitra1 = post_trans1[1];
                                                                            var fnitra1 = post_trans1[2];
                                                                            var lhertra1 = post_trans1[3];
                                                                            var prixtra1 = post_trans1[4];
                                                                            var itinetrasnr1 = post_trans1[5];
                                                                                httpPrixittransite1.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotransnr/${itinetrasnr1}`, true);
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
                                                                        
                                                                                    httpRequetteitra1.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletransnr/${itinetrasnr1}/${dbitra1}/${fnitra1}`, true);
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
                                                                                var itinetrasnr2 = post_trans2[5];
                                                                                let httpSieges2;
                                                                                httpSieges2 = new XMLHttpRequest();
                                                                                const sigs2 = document.querySelector('#psiegesitines2')
                                                                                .options[document.querySelector('#psiegesitines2').options.selectedIndex].value;

                                                                                httpSieges2.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisiegesnr/${itinetrasnr2}/${sigs2}`, true);
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
                                                                        const prostranschemin42 = document.querySelector('#idchemins2')
                                                                        .options[document.querySelector('#idchemins2').options.selectedIndex].value;

                                                                        var post_typgare42 = prostranschemin42.split('-');
                                                                        var seltypgare42 = post_typgare42[0];
                                                                        var typgaresel41 = post_typgare42[1];
                                                                        let httptypequart42;
                                                                        httptypequart42 = new XMLHttpRequest();
                                                                        
                                                                        httptypequart42.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifquart/${typgaresel41}`, true);
                                                                        httptypequart42.onload = () => 
                                                                        {
                                                                            const donqua42 = JSON.parse(httptypequart42.responseText);
                                                                            if (donqua42 == '') {
                                                                                document.querySelector('#quartier4').options.length = 1;
                                                                            }
                                                                            else{
                                                                                if (Object.entries(donqua42).length >= 1) {
                                                                                                
                                                                                    for (let key in Object.entries(donqua42)) {
                                                                                        let optq41 = document.createElement('option');
                                                                                        optq41.value = `${donqua42[key].nom_quartier}`;
                                                                                        optq41.innerHTML = `${donqua42[key].nom_quartier}`;
                                                                                        document.querySelector('#quartier4').add(optq41);
                                                                                    }
                                                                                } else {
                                                                                    document.querySelector('#quartier4').options.length = 1;
                                                                                }
                                                                            }
                                                                            

                                                                        };
                                                                        httptypequart42.setRequestHeader('Content-Type', 'application/json');
                                                                        httptypequart42.send();
                                                                        

                                                                        let httpSiegeschemin2;
                                                                        httpSiegeschemin2 = new XMLHttpRequest();
                                                                        
                                                                        var datedepart = document.querySelector('#date_depheure').value;
                                                                        const prostranschemin2 = document.querySelector('#idchemins2')
                                                                        .options[document.querySelector('#idchemins2').options.selectedIndex].value;

                                                                        httpSiegeschemin2.open('GET', window.location.origin + `${APP_ROOT}/programmes/chemin/${prostranschemin2}/${datedepart}`, true);
                                                                        httpSiegeschemin2.onload = () => 
                                                                        {
                                                                
                                                                                    const dongtranschem2 = JSON.parse(httpSiegeschemin2.responseText);
                                                                                    if (Object.entries(dongtranschem2).length >= 1)
                                                                                        {
                                                                                            for (let key in Object.entries(dongtranschem2)) {
                                                                                                let opt = document.createElement('option');
                                                                                                opt.value = `${dongtranschem2[key].code_progr}/${dongtranschem2[key].intervalle1}/${dongtranschem2[key].intervalle2}/${dongtranschem2[key].id_ligneheure}/${dongtranschem2[key].prix}/${dongtranschem2[key].depart_code}`;
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
                                                                            const httpPrixittransite2 = new XMLHttpRequest();
                                                                                const transselitine2 = document.querySelector('#idcheminsheur2')
                                                                            .options[document.querySelector('#idcheminsheur2').options.selectedIndex].value;
                                                                                var post_trans2 = transselitine2.split('/');
                                                                            var itinetras2 = post_trans2[0];
                                                                            var dbitra2 = post_trans2[1];
                                                                            var fnitra2 = post_trans2[2];
                                                                            var lhertra2 = post_trans2[3];
                                                                            var prixtra2 = post_trans2[4];
                                                                                    var itinetrasnr2 = post_trans2[5];
                                                                                httpPrixittransite2.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdispotransnr/${itinetrasnr2}`, true);
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
                                                                        
                                                                                    httpRequetteitra2.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponibletransnr/${itinetrasnr2}/${dbitra2}/${fnitra2}`, true);
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
                                                                                var itinetrasnr3 = post_trans3[5];
                                                                    
                                                                                let httpSieges3;
                                                                                httpSieges3 = new XMLHttpRequest();
                                                                                const sigs3 = document.querySelector('#psiegesitines3')
                                                                                .options[document.querySelector('#psiegesitines3').options.selectedIndex].value;

                                                                                httpSieges3.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisiegesnr/${itinetrasnr3}/${sigs3}`, true);
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
                                                    var typgare = document.querySelector('#arrsgare').value;
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
                                                                                        document.querySelector('#program1').value = `${dons[key].depart_code}`;
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
                                                                                        const pro1 = document.querySelector('#program1').value;
                                                                                        httpSiege.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisiegesnr/${pro1}/${sig}`, true);
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
                                                                    document.querySelector('#program1').value = `${don[key].depart_code}`;
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
                                                            const cdprog1 = document.querySelector('#program1').value;
                                                            const db = document.querySelector('#inter1').value;
                                                            const fn = document.querySelector('#inter2').value;
                                                            const lg = document.querySelector('#nomitin').value;
                                                            const tim = document.querySelector('#her').value;
                                                                httpRequette.open('GET', window.location.origin + `${APP_ROOT}/programmes/siegdisponiblebus/${cdprog1}/${dpt_date}/${lg}/${tim}/${db}/${fn}`, true);
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
                    const pros1 = document.querySelector('#program1').value;
                    httpSieges.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifisiegesnr/${pros1}/${sigs}`, true);
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
        if (inf !== null)
            inf.onkeyup = () => {
                let httpInfos;
                if (window.XMLHttpRequest) {
                    httpInfos = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    httpInfos = new ActiveXObject("Microsoft.XMLHTTP");
                }
                var verificat = document.querySelector('#rnclient_contact').value;
                
                httpInfos.open('GET', window.location.origin + `${APP_ROOT}/programmes/verifinfos/${verificat}`, true);
                httpInfos.onload = () => {
                    const infos = JSON.parse(httpInfos.responseText);
                    if (infos == null) {
                        document.querySelector('#rclient').value = "";
                        document.querySelector('#prnclient').value = "";
                        document.querySelector('#cnib').value = "";
                        document.querySelector('#date_cnib').value = "";
                        document.querySelector('#lieudelivre').value = "";
                        document.querySelector('#pascompagnie').value = "";
                    } else {
                        if (Object.entries(infos).length > 1) {
                            
                            if (infos.contact_client == verificat) {
                                document.querySelector('#rclient').value = `${infos.nom_client}`;
                                document.querySelector('#prnclient').value = `${infos.prenom_client}`;
                                document.querySelector('#cnib').value = `${infos.num_CNIB}`;
                                document.querySelector('#date_cnib').value = `${infos.date_delivre}`;
                                document.querySelector('#lieudelivre').value = `${infos.lieu_delivre}`;
                                document.querySelector('#pascompagnie').value = `${infos.id_client}`;
                                document.querySelector('#rclientcp').value = `${infos.nom_client}`;
                                document.querySelector('#prnclientcp').value = `${infos.prenom_client}`;
                                document.querySelector('#cnibcp').value = `${infos.num_CNIB}`;
                                document.querySelector('#date_cnibcp').value = `${infos.date_delivre}`;
                                document.querySelector('#lieudelivrecp').value = `${infos.lieu_delivre}`;
                            } else {
                                document.querySelector('#rclient').value = "";
                                document.querySelector('#prnclient').value = "";
                                document.querySelector('#cnib').value = "";
                                document.querySelector('#date_cnib').value = "";
                                document.querySelector('#lieudelivre').value = "";
                                document.querySelector('#pascompagnie').value = "";
                            }
                        }
                    }
                };
                httpInfos.setRequestHeader('Content-Type', 'application/json');
                httpInfos.send();
            };
            
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
                }
                
    })

});