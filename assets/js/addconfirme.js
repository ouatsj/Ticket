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