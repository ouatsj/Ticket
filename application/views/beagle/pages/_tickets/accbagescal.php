<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$role17_mode = !empty($role17_mode) && role17_is_agent();
$accueil = role17_is_agent()
    ? role17_accueil_url($bus_stop, $conex)
    : site_url('gares/'.$this->session->company->ekey.'/gTc/'. $bus_stop->idengare.'/compte/'. $conex->roleattribut .'/'. $bus_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC')));
?>
<div class="<?= $role17_mode ? 'r17-ops r17-shell' : ''; ?>">
<?php if ($role17_mode): ?>
    <?php $this->load->view('beagle/pages/guichet/_role17_ops_chrome'); ?>
    <?php
    $rt = 0;
    if (!empty($cptbages) && isset($cptbages->bagtot)) {
        $rt = (float) $cptbages->bagtot;
    }
    ?>
    <div class="r17-solde" style="margin-bottom:0.65rem;">
        <span class="label">Recette bagage</span>
        <span class="amount"><?= number_format($rt, 0, '', ' '); ?> FCFA</span>
    </div>
    <div class="r17-grid">
        <?php if ($cptbagescd == ''): ?>
            <a href="#" class="r17-btn is-primary md-trigger"
               data-modal="bagage-facturation-r17">
                <span class="r17-ico"><i class="fas fa-suitcase"></i></span>
                <span class="r17-txt">
                    Facturer bagage
                    <span class="r17-sub">Vérifier le ticket de votre escale</span>
                </span>
            </a>
        <?php endif; ?>
        <a href="<?= site_url("confirmation/voirbagageescales/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
           class="r17-btn">
            <span class="r17-ico"><i class="fas fa-list"></i></span>
            <span class="r17-txt">
                Voir bagages
                <span class="r17-sub">Liste du jour</span>
            </span>
        </a>
    </div>
    <?php if ($cptbagescd == ''): ?>
        <?php $this->load->view('beagle/pages/guichet/_modal_bagage_facturation_r17'); ?>
    <?php endif; ?>
<?php else: ?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= htmlspecialchars($accueil, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ACCUEIL&nbsp;
        </a>

        <? if ($cptbagescd == ''):?>
            <a href="<?= site_url("confirmation/bagageescal/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                class="btn btn-secondary btn-space md-trigger" data-modal="">
                <i class="fas fa-print text-success"></i>&nbsp;BAGAGE&nbsp;
            </a>

        <? endif; ?>
            <a href="<?= site_url("comptecaisses/arcompteescalbag/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                class="btn btn-secondary btn-space md-trigger" data-modal="">
                <i class="fas fa-puzzle-piece text-info"></i>
                &nbsp;COMPTE ESCAL BAGAGE&nbsp;
            </a>
    </p>
</div>
    <? $rt = 0; $mt = 0; ?>
    <? if ($cptbages==''): ?><? $rt=0;?><? else:?> &nbsp;
                        
            <? $rt = $cptbages->bagtot;
                $mt = $rt;?>

        <div><span>RECETTE BAGAGE&nbsp;:&nbsp;<?= number_format($mt, 0, '', ' '); ?></span>                   

        </div>
    <?endif;?>
<?php endif; ?>
</div>
