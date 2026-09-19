<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="bagage-facturation-r17" style="perspective: none;">
    <div class="modal-content r17-vente-modal">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">Facturation bagage</h3>
            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true">
                <span class="mdi mdi-close text-white"></span>
            </button>
        </div>
        <div class="modal-body">
            <?php $this->load->view('beagle/pages/guichet/_partial_bagescal_form'); ?>
        </div>
    </div>
</div>
<style>
#bagage-facturation-r17.modal-container { max-width: 520px; width: 96%; }
#bagage-facturation-r17 .r17-vente-modal { max-width: 520px; max-height: 94vh; overflow: auto; }
#bagage-facturation-r17 .form-group { margin-bottom: 0.55rem; }
#bagage-facturation-r17 .form-control { min-height: 42px; font-size: 0.95rem; }

.r17-bag-ticket-card {
    margin: 0.35rem 0 0.85rem;
    border: 2px solid #0f766e;
    border-radius: 10px;
    background: linear-gradient(180deg, #ecfdf5 0%, #f8fffc 100%);
    box-shadow: 0 1px 0 rgba(15, 118, 110, 0.12);
    overflow: hidden;
}
.r17-bag-ticket-card[hidden] { display: none !important; }
.r17-bag-ticket-card__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    padding: 0.55rem 0.75rem;
    background: #0f766e;
    color: #fff;
}
.r17-bag-ticket-card__badge {
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}
.r17-bag-ticket-card__code {
    font-size: 0.85rem;
    font-weight: 700;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
}
.r17-bag-ticket-card__body { padding: 0.55rem 0.75rem 0.65rem; }
.r17-bag-ticket-card__row {
    display: grid;
    grid-template-columns: 6.2rem 1fr;
    gap: 0.35rem 0.65rem;
    padding: 0.28rem 0;
    border-bottom: 1px dashed #cce7e2;
    font-size: 0.92rem;
}
.r17-bag-ticket-card__row:last-child { border-bottom: 0; }
.r17-bag-ticket-card__row .lbl {
    color: #475569;
    font-weight: 700;
}
.r17-bag-ticket-card__row .val {
    color: #0f172a;
    font-weight: 700;
    word-break: break-word;
}
.r17-bag-ticket-card__row .val.is-ligne {
    color: #0f766e;
    font-size: 1rem;
}
</style>
<script>
(function () {
    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }
    ready(function () {
        var root = document.querySelector('#bagage-facturation-r17 .adbagescale');
        if (!root || root.dataset.r17BagBound === '1') return;
        root.dataset.r17BagBound = '1';

        var btn = document.getElementById('infocodeticketesc');
        var form = document.getElementById('escalFormbag');
        if (form) {
            form.setAttribute('action', (typeof APP_ROOT !== 'undefined' ? APP_ROOT : '')
                + '/Reprogrammes/savebagesc/' + (root.getAttribute('data-cle_compagnie') || ''));
        }
        if (!btn) return;

        function setText(id, v) {
            var el = document.getElementById(id);
            if (el) el.textContent = v == null || v === '' ? '—' : String(v);
        }
        function setVal(id, v) {
            var el = document.getElementById(id);
            if (el) el.value = v == null ? '' : String(v);
        }
        function showCard(on) {
            var card = document.getElementById('r17_bag_ticket_card');
            if (!card) return;
            if (on) card.removeAttribute('hidden');
            else card.setAttribute('hidden', 'hidden');
        }
        function clearTicket() {
            ['pascontactbagsansescbg','rclientcpescalbag','nclientcpescalbag','prnclientcpescalbag',
             'id_lgeheurescalbag','codtickbagsansesc','idcompagaescbag','lignescalbag','quartpasseesc',
             'infobagasansesc','nomligneescalbag'
            ].forEach(function (id) { setVal(id, ''); });
            setText('r17_bag_card_code', '');
            setText('r17_bag_card_client', '');
            setText('r17_bag_card_trajet', '');
            setText('r17_bag_card_heure', '');
            showCard(false);
        }

        btn.addEventListener('click', function () {
            var code = (document.getElementById('codeticketbagesc') || {}).value || '';
            var gid = (document.getElementById('codebaggidesc') || {}).value || '';
            var sgid = (document.getElementById('codebagsousgidesc') || {}).value || '';
            var err = document.getElementById('r17_bag_verif_err');
            if (err) { err.style.display = 'none'; err.textContent = ''; }
            if (!code.trim()) {
                clearTicket();
                if (err) {
                    err.textContent = 'Saisissez le code du ticket.';
                    err.style.display = 'block';
                }
                return;
            }
            var xhr = new XMLHttpRequest();
            xhr.open('GET', window.location.origin
                + (typeof APP_ROOT !== 'undefined' ? APP_ROOT : '')
                + '/reprogrammes/codeclientverifesc/'
                + encodeURIComponent(code) + '/'
                + encodeURIComponent(gid) + '/'
                + encodeURIComponent(sgid), true);
            xhr.onload = function () {
                var donneesbag = null;
                try { donneesbag = JSON.parse(xhr.responseText); } catch (e) { donneesbag = null; }
                if (!donneesbag || typeof donneesbag !== 'object' || !Object.keys(donneesbag).length) {
                    clearTicket();
                    if (err) {
                        err.textContent = 'Ticket introuvable sur votre escale / ligne attribuée.';
                        err.style.display = 'block';
                    }
                    return;
                }
                var forcedLigne = (document.getElementById('role17_bag_ligne') || {}).value || '';
                var ligneId = donneesbag.ident_ligne || donneesbag.lignintescal || forcedLigne || '';
                if (forcedLigne && String(ligneId) !== String(forcedLigne)) {
                    clearTicket();
                    if (err) {
                        err.textContent = 'Ce ticket n’appartient pas à l’itinéraire de votre escale.';
                        err.style.display = 'block';
                    }
                    return;
                }
                var ligneNom = donneesbag.nom_ligne || ligneId;
                var client = [donneesbag.nom_client, donneesbag.prenom_client].filter(Boolean).join(' ');
                var dest = donneesbag.nom_gadest || '';
                var quart = (donneesbag.quartier_escal || '').replace(/^\[LIBRE\]\s*/i, '').trim();
                var heure = donneesbag.heure || '';
                var codeTk = donneesbag.idclescal || code;
                var escaleLabel = (document.getElementById('role17_bag_escale_label')
                    && document.getElementById('role17_bag_escale_label').value)
                    || (typeof R17_ESCALE_LABEL !== 'undefined' ? R17_ESCALE_LABEL : '')
                    || '';
                escaleLabel = String(escaleLabel).replace(/\s*\([^)]*\)\s*$/, '').trim();
                var trajet = '';
                var mOd = quart.match(/^(.+?)\s*[-–—]\s*(.+?)(?:\s*\([^)]*\))?\s*$/);
                if (mOd) {
                    trajet = (mOd[1].trim() + ' → ' + mOd[2].trim());
                } else if (escaleLabel || dest) {
                    trajet = [escaleLabel, dest].filter(Boolean).join(' → ');
                } else {
                    trajet = dest || quart;
                }

                setVal('pascontactbagsansescbg', donneesbag.contact_client);
                setVal('rclientcpescalbag', donneesbag.clientescal);
                setVal('nclientcpescalbag', donneesbag.nom_client);
                setVal('prnclientcpescalbag', donneesbag.prenom_client);
                setVal('id_lgeheurescalbag', donneesbag.id_ligneheure || donneesbag.id_lgeheur);
                setVal('codtickbagsansesc', codeTk);
                setVal('idcompagaescbag', donneesbag.id_compaga);
                setVal('lignescalbag', ligneId);
                setVal('quartpasseesc', donneesbag.quartier_escal || '');
                setVal('nomligneescalbag', ligneNom);
                setVal('infobagasansesc', [client, trajet, heure].filter(Boolean).join(' · '));

                setText('r17_bag_card_code', codeTk);
                setText('r17_bag_card_client', client);
                setText('r17_bag_card_trajet', trajet);
                setText('r17_bag_card_heure', heure);
                showCard(true);
            };
            xhr.onerror = function () {
                clearTicket();
                if (err) {
                    err.textContent = 'Erreur réseau lors de la vérification.';
                    err.style.display = 'block';
                }
            };
            xhr.send();
        });

        window.updateContenu = function () {
            var contenuField = document.querySelector('textarea[name="naturebagagesansesc"]');
            if (!contenuField) return;
            var selected = [];
            document.querySelectorAll('input[name="types_bagsansesc[]"]:checked').forEach(function (cb) {
                selected.push(cb.value);
            });
            contenuField.value = selected.join(', ');
        };
    });
})();
</script>
