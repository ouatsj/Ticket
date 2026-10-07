<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Reçu courrier escale — POSPrinter 57×40 mm, 3 exemplaires.
 */
$this->load->helper(array('ticket_escale_libre_print', 'url_safe', 'ticket_prix'));

$single = !empty($single) ? $single : null;
$exped = !empty($exped) ? $exped : null;
$destin = !empty($destin) ? $destin : null;

$accueil_url = (!empty($role17_mode) && function_exists('role17_accueil_url'))
    ? role17_accueil_url($bus_stop, $conex)
    : site_url(
        'confirmation/courrierescales/' . $this->session->company->ekey
        . '/' . $conex->roleattribut
        . '/' . $bus_stop->idengare
        . '/' . $bus_stop->idsousgare
    );

if (!$single) {
    if (method_exists($this->session, 'set_flashdata')) {
        $this->session->set_flashdata(
            'error',
            'Reçu courrier introuvable — réessayez l’envoi ou la réimpression depuis la liste du jour.'
        );
    }
    echo '<p style="padding:16px;font-family:Arial,sans-serif;">Reçu introuvable — retour…</p>';
    echo '<script>setTimeout(function(){location.replace(' . json_encode($accueil_url) . ');},1200);</script>';
    return;
}

// Départ reçu = escale de vente (rôle 17), pas la sous-gare d’affectation guichet.
$dep = '';
if (!empty($escale_depart_label)) {
    $dep = trim((string) $escale_depart_label);
    // Ex. « BOROMO (escale) » / « BOROMO (origine) »
    $dep = trim((string) preg_replace('/\s*\([^)]*\)\s*$/u', '', $dep));
    // Ancien fallback inject « GARE/SOUSGARE » → garder le libellé escale (1er segment)
    if (strpos($dep, '/') !== false) {
        $parts = array_map('trim', explode('/', $dep, 2));
        if ($parts[0] !== '') {
            $dep = $parts[0];
        }
    }
}
if ($dep === '' && !empty($single->nom_escale)) {
    $dep = trim((string) $single->nom_escale);
}
if ($dep === '' && !empty($single->nom_gaep)) {
    $dep = trim((string) $single->nom_gaep);
}
if ($dep === '' && !empty($bus_stop)) {
    if (!empty($bus_stop->garenom)) {
        $dep = trim((string) $bus_stop->garenom);
    } elseif (!empty($bus_stop->nom_gaep)) {
        $dep = trim((string) $bus_stop->nom_gaep);
    } elseif (!empty($bus_stop->nomsousgare)) {
        $dep = trim((string) $bus_stop->nomsousgare);
    }
}
if ($dep === '' && !empty($single->nomsousgare)) {
    $dep = trim((string) $single->nomsousgare);
}
$arrGare = trim((string) (isset($single->nom_dest_choisie) ? $single->nom_dest_choisie : ''));
$arrEsc = trim((string) (isset($single->nom_escale_arrivee) ? $single->nom_escale_arrivee : ''));
if ($arrGare === '') {
    $arrGare = trim((string) (isset($single->nom_gadest) ? $single->nom_gadest : ''));
}
$arr = $arrGare;
$idArr = isset($single->id_escale_arrivee) ? (int) $single->id_escale_arrivee : 0;
$idPrinc = isset($single->id_sg_principale) ? (int) $single->id_sg_principale : 0;
if ($arrEsc !== '' && $idArr > 0 && $idPrinc > 0 && $idArr !== $idPrinc) {
    $arr = $arrEsc;
}
$od = ticket_escale_libre_pos_text($dep . ' - ' . $arr, true);

$exp_name = '';
$exp_tel = '';
if ($exped) {
    if (!empty($exped->nom_client) || !empty($exped->prenom_client)) {
        $exp_name = trim((string) $exped->nom_client . ' ' . (string) $exped->prenom_client);
        $exp_tel = !empty($exped->contact_client) ? (string) $exped->contact_client : '';
    } elseif (!empty($exped->nomprenom_perso)) {
        $exp_name = (string) $exped->nomprenom_perso;
        $exp_tel = !empty($exped->contact_perso) ? (string) $exped->contact_perso : '';
    }
}
$dest_name = '';
$dest_tel = '';
if ($destin) {
    if (!empty($destin->nom_client) || !empty($destin->prenom_client)) {
        $dest_name = trim((string) $destin->nom_client . ' ' . (string) $destin->prenom_client);
        $dest_tel = !empty($destin->contact_client) ? (string) $destin->contact_client : '';
    } elseif (!empty($destin->nomprenom_perso)) {
        $dest_name = (string) $destin->nomprenom_perso;
        $dest_tel = !empty($destin->contact_perso) ? (string) $destin->contact_perso : '';
    }
}
$exp_name = ticket_escale_libre_pos_text($exp_name, true);
$dest_name = ticket_escale_libre_pos_text($dest_name, true);
$exp_tel = ticket_escale_libre_pos_text($exp_tel, false);
$dest_tel = ticket_escale_libre_pos_text($dest_tel, false);

$compagnie = !empty($single->nom_compagnie)
    ? ticket_escale_libre_pos_text((string) $single->nom_compagnie, true)
    : '';
$code = (string) $single->num_couresc;
$prix = number_format((float) $single->prixcolisesc, 0, '', ' ');
$contenu = trim(
    (isset($single->nombrecolis) ? (string) $single->nombrecolis . ' ' : '')
    . (isset($single->naturecourrieresc) ? (string) $single->naturecourrieresc : (isset($single->naturecoli) ? (string) $single->naturecoli : ''))
);
$contenu = ticket_escale_libre_pos_text($contenu, true);

$emis_ts = !empty($single->dateenvoicour_atesc) ? (int) $single->dateenvoicour_atesc : now();
$emis_raw = mdate('%Y-%m-%d %H:%i:%s', $emis_ts);
$emis = ticket_emis_texte($single, $emis_raw, isset($conex) ? $conex : null);

$dep_date = '';
if ($exped && !empty($exped->dateexpedition)) {
    $dep_ts = strtotime((string) $exped->dateexpedition);
    $dep_date = $dep_ts ? date('d-m-Y', $dep_ts) : trim((string) $exped->dateexpedition);
}
$dep_heure = '';
if (!empty($single->heure)) {
    $hhmm = ticket_heure_hhmm($single->heure);
    if ($hhmm !== '' && strpos($hhmm, ':') !== false) {
        $dep_heure = str_replace(':', 'H', $hhmm);
    }
}
$mouvement = '';
if ($dep_date !== '' || $dep_heure !== '') {
    $mouvement = 'Départ';
    if ($dep_date !== '') {
        $mouvement .= ' ' . $dep_date;
    }
    if ($dep_heure !== '') {
        $mouvement .= ' à ' . $dep_heure;
    }
}
$logo = !empty($single->logo) ? site_url($single->logo) : '';

$copies = array(
    array('n' => '1/3', 'label' => 'CLIENT'),
    array('n' => '2/3', 'label' => 'COURRIER'),
    array('n' => '3/3', 'label' => 'ARCHIVE'),
);
?>
<?php $this->load->view('beagle/pages/_tickets/_pos_escale_plein'); ?>
<script type="text/javascript">
posEscalePrintCopies(<?= json_encode($accueil_url); ?>);
</script>

<div id="printStatus">
    <p class="msg">Impression en cours…</p>
    <p class="sub">3 exemplaires · POSPrinter · retour automatique</p>
</div>

<div id="recuEpsonStack">
<?php foreach ($copies as $copy): ?>
    <div class="recu-copy">
        <?php if ($logo !== ''): ?>
            <img class="t-logo" src="<?= htmlspecialchars($logo, ENT_QUOTES, 'UTF-8'); ?>" alt="">
        <?php endif; ?>
        <?php if ($compagnie !== ''): ?>
            <div class="t-company"><?= htmlspecialchars($compagnie, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <div class="t-title">RECU COURRIER <?= htmlspecialchars($copy['n'] . ' ' . $copy['label'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="t-od"><?= htmlspecialchars($od, ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="t-line">EXP <?= htmlspecialchars($exp_name, ENT_QUOTES, 'UTF-8'); ?><?= $exp_tel !== '' ? (' ' . htmlspecialchars($exp_tel, ENT_QUOTES, 'UTF-8')) : ''; ?></div>
        <div class="t-line">DEST <?= htmlspecialchars($dest_name, ENT_QUOTES, 'UTF-8'); ?><?= $dest_tel !== '' ? (' ' . htmlspecialchars($dest_tel, ENT_QUOTES, 'UTF-8')) : ''; ?></div>
        <?php if ($contenu !== ''): ?>
            <div class="t-line"><?= htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <?php if ($mouvement !== ''): ?>
            <div class="t-dep"><?= htmlspecialchars($mouvement, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <div class="t-prix"><?= $prix; ?> FCFA</div>
        <div class="t-code"><?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?></div>
        <?= ticket_barcode_img($code, 260, 28); ?>
        <div class="t-emis"><?= htmlspecialchars($emis, ENT_QUOTES, 'UTF-8'); ?></div>
    </div>
<?php endforeach; ?>
</div>
