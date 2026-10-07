<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Reçu bagage escale — POSPrinter 57×40 mm, 2 exemplaires (CLIENT + ARCHIVE).
 */
$this->load->helper(array('ticket_escale_libre_print', 'url_safe', 'ticket_prix'));

$item = !empty($itemescbag) ? $itemescbag : null;

$accueil_url = (!empty($role17_mode) && function_exists('role17_accueil_url') && !empty($bus_stop) && !empty($conex))
    ? role17_accueil_url($bus_stop, $conex)
    : site_url(
        'confirmation/bagageescales/' . $this->session->company->ekey
        . '/' . (!empty($conex->roleattribut) ? $conex->roleattribut : '')
        . '/' . (!empty($bus_stop->idengare) ? $bus_stop->idengare : '')
        . '/' . (!empty($bus_stop->idsousgare) ? $bus_stop->idsousgare : '')
    );

if (!$item || !is_object($item)) {
    if (method_exists($this->session, 'set_flashdata')) {
        $this->session->set_flashdata(
            'error',
            'Reçu bagage introuvable — revérifiez le code ticket puis FACTURER.'
        );
    }
    echo '<p style="padding:16px;font-family:Arial,sans-serif;">Reçu bagage introuvable — retour…</p>';
    echo '<script>setTimeout(function(){location.replace(' . json_encode($accueil_url) . ');},1200);</script>';
    return;
}

$quart_raw = trim((string) (isset($item->quartier_escal) ? $item->quartier_escal : ''));
$is_libre = (stripos($quart_raw, '[LIBRE]') === 0);
$quart = $is_libre ? trim((string) preg_replace('/^\[LIBRE\]\s*/i', '', $quart_raw)) : $quart_raw;

// Uniquement « escale → destination » du ticket vérifié (jamais le nom de ligne).
$escale_ticket = '';
$dest_ticket = '';

// Ex. « BOROMO - OUAGA (origine) » / « BOROMO - BOBO (extrême) »
if ($quart !== ''
    && preg_match('/^(.+?)\s*[-–—]\s*(.+?)(?:\s*\([^)]*\))?\s*$/u', $quart, $m)
) {
    $escale_ticket = trim($m[1]);
    $dest_ticket = trim($m[2]);
}

if ($escale_ticket === '') {
    if (!empty($escale_depart_label)) {
        $escale_ticket = trim((string) preg_replace('/\s*\([^)]*\)\s*$/', '', (string) $escale_depart_label));
    } elseif (!empty($bus_stop->garenom)) {
        $escale_ticket = trim((string) $bus_stop->garenom);
    } elseif (!empty($item->nomsousgare)) {
        $escale_ticket = trim((string) $item->nomsousgare);
    }
}

if ($dest_ticket === '') {
    // Fallback : terminus ligne seulement si le ticket n'a pas d'OD parseable.
    if (!empty($item->nom_dest_ticket)) {
        $dest_ticket = trim((string) $item->nom_dest_ticket);
    } elseif (!empty($item->nom_gadest)) {
        $dest_ticket = trim((string) $item->nom_gadest);
    }
}

$escale_ticket = ticket_escale_libre_pos_text($escale_ticket, true);
$dest_ticket = ticket_escale_libre_pos_text($dest_ticket, true);
$od = trim($escale_ticket . ($escale_ticket !== '' && $dest_ticket !== '' ? ' - ' : '') . $dest_ticket);
$od = ticket_escale_libre_pos_text($od, true);

$client = ticket_escale_libre_pos_text(
    trim((string) (isset($item->nom_client) ? $item->nom_client : '') . ' ' . (isset($item->prenom_client) ? $item->prenom_client : '')),
    true
);
$tel = ticket_escale_libre_pos_text(
    !empty($item->contactexpediesc) ? (string) $item->contactexpediesc : '',
    false
);
$nb = isset($item->nombrebagageesc) ? (string) $item->nombrebagageesc : '';
$type = isset($item->typebagagesesc) ? (string) $item->typebagagesesc : '';
$contenu = ticket_escale_libre_pos_text(
    trim($nb . ($type !== '' ? (' (' . $type . ')') : '') . ' ' . (isset($item->contenubagageesc) ? $item->contenubagageesc : '')),
    true
);
$compagnie = !empty($item->nom_compagnie)
    ? ticket_escale_libre_pos_text((string) $item->nom_compagnie, true)
    : '';
$code = !empty($item->codebagesc) ? (string) $item->codebagesc : (string) $item->id_bagageesc;
$prix = number_format((float) $item->prix_bagageesc, 0, '', ' ');
$recu_no = str_pad((string) $item->id_bagageesc, 3, '0', STR_PAD_LEFT);

$emis_raw = mdate('%Y-%m-%d %H:%i:%s', now('UTC'));
if (!empty($item->date_createesc)) {
    $emis_raw = (string) $item->date_createesc
        . (!empty($item->heure) ? (' ' . $item->heure) : '');
}
$emis = ticket_emis_texte($item, $emis_raw, isset($conex) ? $conex : null);
$logo = !empty($item->logo) ? site_url($item->logo) : '';

$copies = array(
    array('n' => '1/2', 'label' => 'CLIENT'),
    array('n' => '2/2', 'label' => 'ARCHIVE'),
);
?>
<?php $this->load->view('beagle/pages/_tickets/_pos_escale_plein'); ?>
<script>
posEscalePrintCopies(<?= json_encode($accueil_url); ?>);
</script>

<div id="printStatus">
    <p class="msg">Impression en cours…</p>
    <p class="sub">2 exemplaires · CLIENT + ARCHIVE · POSPrinter</p>
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
        <div class="t-title">RECU BAGAGE <?= htmlspecialchars($copy['n'] . ' ' . $copy['label'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="t-od"><?= htmlspecialchars($od, ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="t-line">N° <?= htmlspecialchars($recu_no, ENT_QUOTES, 'UTF-8'); ?> · <?= htmlspecialchars($client, ENT_QUOTES, 'UTF-8'); ?><?= $tel !== '' ? (' ' . htmlspecialchars($tel, ENT_QUOTES, 'UTF-8')) : ''; ?></div>
        <?php if ($contenu !== ''): ?>
            <div class="t-line"><?= htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <div class="t-prix"><?= $prix; ?> FCFA</div>
        <div class="t-code"><?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?></div>
        <?= ticket_barcode_img($code, 260, 36); ?>
        <div class="t-emis"><?= htmlspecialchars($emis, ENT_QUOTES, 'UTF-8'); ?></div>
    </div>
<?php endforeach; ?>
</div>
