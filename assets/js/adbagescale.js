document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adbagescale').forEach(function (e)
    {
        // Modal r17 a son propre handler (évite double XHR + clear croisé).
        if (e.dataset.r17BagBound === '1' || e.closest('#bagage-facturation-r17')) {
            return;
        }

        let baginfos = document.querySelector('#infocodeticketesc');
        if (baginfos !== null)
            baginfos.onclick = () => {

            let httpRequestBag;

            if (window.XMLHttpRequest) {
                httpRequestBag = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                httpRequestBag = new ActiveXObject("Microsoft.XMLHTTP");
            }

            var bagcocl = document.querySelector("#codeticketbagesc").value;
            var baggid = document.querySelector("#codebaggidesc").value;
            var bagsgid = document.querySelector("#codebagsousgidesc").value;
            httpRequestBag.open('GET', window.location.origin + `${APP_ROOT}/reprogrammes/codeclientverifesc/${encodeURIComponent(bagcocl)}/${encodeURIComponent(baggid)}/${encodeURIComponent(bagsgid)}`, true);
            httpRequestBag._rgSkipGuard = true;
            httpRequestBag.onload = () => {

                let donneesbag = null;
                try { donneesbag = JSON.parse(httpRequestBag.responseText); } catch (err) { donneesbag = null; }

                function setVal(id, v) {
                    var el = document.querySelector('#' + id);
                    if (el) el.value = v == null ? '' : String(v);
                }

                if (!donneesbag || typeof donneesbag !== 'object' || !Object.keys(donneesbag).length) {
                    setVal('pascontactbagsansescbg', '');
                    setVal('rclientcpescalbag', '');
                    setVal('nclientcpescalbag', '');
                    setVal('prnclientcpescalbag', '');
                    setVal('id_lgeheurescalbag', '');
                    setVal('codtickbagsansesc', '');
                    setVal('idcompagaescbag', '');
                    setVal('lignescalbag', '');
                    setVal('quartpasseesc', '');
                    setVal('infobagasansesc', '');
                    return;
                }

                var ligneId = donneesbag.ident_ligne || donneesbag.lignintescal || '';
                var lh = donneesbag.id_ligneheure || donneesbag.id_lgeheur || '';
                setVal('pascontactbagsansescbg', donneesbag.contact_client);
                setVal('rclientcpescalbag', donneesbag.clientescal);
                setVal('nclientcpescalbag', donneesbag.nom_client);
                setVal('prnclientcpescalbag', donneesbag.prenom_client);
                setVal('id_lgeheurescalbag', lh);
                setVal('codtickbagsansesc', donneesbag.idclescal || bagcocl);
                setVal('idcompagaescbag', donneesbag.id_compaga);
                setVal('lignescalbag', ligneId);
                setVal('quartpasseesc', donneesbag.quartier_escal || '');
                setVal('infobagasansesc',
                    [donneesbag.nom_client, donneesbag.prenom_client, donneesbag.nom_gadest,
                     donneesbag.quartier_escal, donneesbag.heure].filter(Boolean).join(' '));
            };
            httpRequestBag.send();
        };

        window.updateContenu = function ()
        {
            var contenuField = document.querySelector('textarea[name="naturebagagesansesc"]');
            if (!contenuField) return;
            var checkboxes = document.querySelectorAll('input[name="types_bagsansesc[]"]:checked');
            var selectedValues = [];
            checkboxes.forEach(function(checkbox) {
                selectedValues.push(checkbox.value);
            });
            contenuField.value = selectedValues.join(', ');
        };

        e.onclick = function () {
            let bagsansForm = document.querySelector('#escalFormbag');
            if (bagsansForm) {
                bagsansForm.setAttribute('action', `${APP_ROOT}/Reprogrammes/savebagesc/${e.dataset.cle_compagnie}`);
            }
        };

        var clique = true;
        $('#bottonbagesc').click(function()
        {
            if (clique) {
                clique = false;
                return true;
            }
            return false;
        });
    });

});
