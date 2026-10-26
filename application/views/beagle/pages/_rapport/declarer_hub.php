<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Hub Déclarer — onglets année, cartes mois (boutons à l’intérieur au clic).
 */
$type = isset($hub_type) ? $hub_type : 'ticket';
$titre = isset($hub_titre) ? $hub_titre : 'Déclarer';
$annees = isset($hub_annees) && is_array($hub_annees) ? $hub_annees : array();
$moisMap = isset($hub_mois) && is_array($hub_mois) ? $hub_mois : array();
$anneeSel = isset($hub_annee_sel) ? (string) $hub_annee_sel : date('Y');
$moisSel = isset($hub_mois_sel) ? (string) $hub_mois_sel : '';
$compagnies = isset($hub_compagnies) ? $hub_compagnies : array();
$gares = isset($hub_gares) ? $hub_gares : array();
$nomsCie = isset($hub_noms_cie) ? $hub_noms_cie : array();
$nomsGare = isset($hub_noms_gare) ? $hub_noms_gare : array();
$compFilter = isset($hub_comp_filter) ? (string) $hub_comp_filter : '';
$gareFilter = isset($hub_gare_filter) ? (string) $hub_gare_filter : '';
$ckey = isset($hub_ckey) ? $hub_ckey : '';
$gid = isset($hub_gid) ? $hub_gid : '';
$canArchiver = !empty($hub_can_archiver);
$retour = isset($hub_retour) ? $hub_retour : '#';
$ctx = isset($hub_ctx) && is_array($hub_ctx) ? $hub_ctx : array();
$labelsMois = array(
    '01' => 'Janvier', '02' => 'Février', '03' => 'Mars', '04' => 'Avril',
    '05' => 'Mai', '06' => 'Juin', '07' => 'Juillet', '08' => 'Août',
    '09' => 'Septembre', '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre',
);
$moisParAnnee = array();
foreach ($moisMap as $m => $bloc) {
    $y = substr($m, 0, 4);
    if (!isset($moisParAnnee[$y])) {
        $moisParAnnee[$y] = array();
    }
    $moisParAnnee[$y][$m] = $bloc;
}
if (!$annees && $moisParAnnee) {
    $annees = array_keys($moisParAnnee);
    rsort($annees);
}
if ($anneeSel === '' && $annees) {
    $anneeSel = $annees[0];
}
$baseHub = site_url('Rapport/declarer_hub/' . rawurlencode($ckey) . '/' . rawurlencode($gid) . '/' . rawurlencode($type));
$qsCtx = http_build_query(array_filter(array(
    'compagnie' => $compFilter,
    'code_gare' => $gareFilter,
    'gareconnect' => isset($ctx['gareconnect']) ? $ctx['gareconnect'] : '',
    'userconnected' => isset($ctx['userconnected']) ? $ctx['userconnected'] : '',
    'sousgareconnect' => isset($ctx['sousgareconnect']) ? $ctx['sousgareconnect'] : '',
)));

/**
 * Formulaire POST avec jeton CSRF (form_open CI).
 */
$decl_action_form = function ($action, $fields, $label, $class = '') use ($ctx) {
    $html = form_open($action, array('class' => 'decl-action-form'));
    foreach ($ctx as $k => $v) {
        if ($v === '' || $v === null) {
            continue;
        }
        $html .= form_hidden($k, (string) $v);
    }
    foreach ($fields as $name => $val) {
        $html .= form_hidden($name, (string) $val);
    }
    $cls = trim('decl-action-btn ' . $class);
    $html .= '<button type="submit" class="' . htmlspecialchars($cls) . '">' . htmlspecialchars($label) . '</button>';
    $html .= form_close();
    return $html;
};

$decl_render_actions = function ($bloc, $cieDef, $gareDef) use (
    $type, $ckey, $gid, $canArchiver, $decl_action_form
) {
    $debut = $bloc['debut'];
    $fin = $bloc['fin'];
    $hasEscalTicket = in_array('ticket_escal', $bloc['familles'], true);
    $hasTicket = in_array('ticket_etats', $bloc['familles'], true);
    $out = '<div class="decl-actions">';

    if ($type === 'ticket') {
        if ($hasTicket || !$hasEscalTicket) {
            $out .= $decl_action_form(
                'Rapport/exoreports/' . rawurlencode($ckey) . '/' . rawurlencode($gid),
                array('_compag' => $cieDef, 'departgar' => $gareDef, 'datedebut' => $debut, 'datefin' => $fin),
                'EXERCICE MENSUEL'
            );
            $out .= $decl_action_form(
                'Rapport/triencaissementsexo/' . rawurlencode($ckey) . '/' . rawurlencode($gid),
                array('_compagexo' => $cieDef, 'departgarexo' => $gareDef, 'datedexo' => $debut, 'datefexo' => $fin),
                'BROUILLARD',
                'secondary'
            );
            $out .= $decl_action_form(
                'Rapport/manifesthebdo/' . rawurlencode($ckey) . '/' . rawurlencode($gid),
                array('_compag' => $cieDef, 'departgar' => $gareDef, 'datedebut' => $debut, 'datefin' => $fin),
                'MANIFEST',
                'secondary'
            );
            $out .= $decl_action_form(
                'Rapport/trinombrepass/' . rawurlencode($ckey),
                array('nomcomps' => $cieDef, 'nomgares' => $gareDef, 'dateps1' => $debut, 'dateps2' => $fin),
                'LISTE PASSAGERS',
                'secondary'
            );
        }
        if ($hasEscalTicket) {
            $out .= $decl_action_form(
                'Rapport/exoreportsesc/' . rawurlencode($ckey) . '/' . rawurlencode($gid),
                array('_compagesc' => $cieDef, 'departgaresc' => $gareDef, 'datedebutesc' => $debut, 'datefinesc' => $fin),
                'EXERCICE MENSUEL ESCAL'
            );
            $out .= $decl_action_form(
                'Rapport/triencaissementsexoesc/' . rawurlencode($ckey) . '/' . rawurlencode($gid),
                array('_compagexoesc' => $cieDef, 'departgarexoesc' => $gareDef, 'datedexoesc' => $debut, 'datefexoesc' => $fin),
                'BROUILLARD ESCAL',
                'secondary'
            );
            $out .= $decl_action_form(
                'Rapport/manifesthebdoesc/' . rawurlencode($ckey) . '/' . rawurlencode($gid),
                array('_compagesc' => $cieDef, 'departgaresc' => $gareDef, 'datedebutesc' => $debut, 'datefinesc' => $fin),
                'MANIFEST ESCAL',
                'secondary'
            );
            $out .= $decl_action_form(
                'Rapport/trinombrepassesc/' . rawurlencode($ckey),
                array('nomcompsesc' => $cieDef, 'nomgaresesc' => $gareDef, 'dateps1esc' => $debut, 'dateps2esc' => $fin),
                'LISTE PASSAGERS ESCAL',
                'secondary'
            );
        }
        if ($canArchiver) {
            $out .= $decl_action_form(
                'Historique_Passagers/archivre/' . rawurlencode($ckey) . '/' . rawurlencode($gid),
                array('archivrecompag' => $cieDef, 'archivredepargare' => $gareDef, 'debutenreg' => $debut, 'finenreg' => $fin),
                'TRI ARCHIVER',
                'warn'
            );
        }
    } elseif ($type === 'bagage') {
        $out .= $decl_action_form(
            'Rapport/exercicesbagop/' . rawurlencode($ckey) . '/' . rawurlencode($gid),
            array('_compagbagop' => $cieDef, 'departgarbagop' => $gareDef, 'datedebutbagop' => $debut, 'datefinbagop' => $fin),
            'EXERCICE MENSUEL'
        );
        $out .= $decl_action_form(
            'Rapport/exercicesbagopesc/' . rawurlencode($ckey) . '/' . rawurlencode($gid),
            array('_compagbagopesc' => $cieDef, 'departgarbagopesc' => $gareDef, 'datedebutbagopesc' => $debut, 'datefinbagopesc' => $fin),
            'EXERCICE MENSUEL ESCAL'
        );
        $out .= $decl_action_form(
            'Rapport/triencaissementsexobag/' . rawurlencode($ckey) . '/' . rawurlencode($gid),
            array('_compagexobg' => $cieDef, 'departgarexobg' => $gareDef, 'datedexobg' => $debut, 'datefexobg' => $fin),
            'BROUILLARD',
            'secondary'
        );
        $out .= $decl_action_form(
            'Rapport/triencaissementsexobagesc/' . rawurlencode($ckey) . '/' . rawurlencode($gid),
            array('_compagexobgesc' => $cieDef, 'departgarexobgesc' => $gareDef, 'datedexobgesc' => $debut, 'datefexobgesc' => $fin),
            'BROUILLARD ESCAL',
            'secondary'
        );
        $out .= $decl_action_form(
            'Rapport/bagagemanifestheb/' . rawurlencode($ckey) . '/' . rawurlencode($gid),
            array('_compaghebbg' => $cieDef, 'departgarhebbg' => $gareDef, 'datedebuthebbg' => $debut, 'datefinhebbg' => $fin),
            'MANIFEST',
            'secondary'
        );
        $out .= $decl_action_form(
            'Rapport/bagageescmanifestheb/' . rawurlencode($ckey) . '/' . rawurlencode($gid),
            array('_compaghebbge' => $cieDef, 'departgarhebbge' => $gareDef, 'datedebuthebbge' => $debut, 'datefinhebbge' => $fin),
            'MANIFEST ESCAL',
            'secondary'
        );
        if ($canArchiver) {
            $out .= $decl_action_form(
                'Historique_Passagers/archivre/' . rawurlencode($ckey) . '/' . rawurlencode($gid),
                array('archivrecompag' => $cieDef, 'archivredepargare' => $gareDef, 'debutenreg' => $debut, 'finenreg' => $fin),
                'TRI ARCHIVER',
                'warn'
            );
        }
    } else {
        $out .= $decl_action_form(
            'Rapport/etatsplis1/' . rawurlencode($ckey) . '/' . rawurlencode($gid),
            array('_compagnpli' => $cieDef, 'deptgaresidpli' => $gareDef, 'datesdebutspli' => $debut, 'datesfinspli' => $fin),
            'EXERCICE MENSUEL'
        );
        $out .= $decl_action_form(
            'Rapport/etatsplis1esc/' . rawurlencode($ckey) . '/' . rawurlencode($gid),
            array('_compagnpliesc' => $cieDef, 'deptgaresidpliesc' => $gareDef, 'datesdebutspliesc' => $debut, 'datesfinspliesc' => $fin),
            'EXERCICE MENSUEL ESCAL'
        );
        $out .= $decl_action_form(
            'Rapport/etatsverseplis/' . rawurlencode($ckey) . '/' . rawurlencode($gid),
            array('_compagnplivers' => $cieDef, 'deptgaresidplivers' => $gareDef, 'datesdebutsplivers' => $debut, 'datesfinsplivers' => $fin),
            'BROUILLARD',
            'secondary'
        );
        $out .= $decl_action_form(
            'Rapport/etatsverseplisesc/' . rawurlencode($ckey) . '/' . rawurlencode($gid),
            array('_compagnpliversesc' => $cieDef, 'deptgaresidpliversesc' => $gareDef, 'datesdebutspliversesc' => $debut, 'datesfinspliversesc' => $fin),
            'BROUILLARD ESCAL',
            'secondary'
        );
        $out .= $decl_action_form(
            'Rapport/courriermanifestheb/' . rawurlencode($ckey) . '/' . rawurlencode($gid),
            array('_compagheb' => $cieDef, 'departgarheb' => $gareDef, 'datedebutheb' => $debut, 'datefinheb' => $fin),
            'MANIFEST',
            'secondary'
        );
        $out .= $decl_action_form(
            'Rapport/courriermanifesthebesc/' . rawurlencode($ckey) . '/' . rawurlencode($gid),
            array('_compaghebesc' => $cieDef, 'departgarhebesc' => $gareDef, 'datedebuthebesc' => $debut, 'datefinhebesc' => $fin),
            'MANIFEST ESCAL',
            'secondary'
        );
        if ($canArchiver) {
            $out .= $decl_action_form(
                'Historique_Passagers/archivrecr/' . rawurlencode($ckey) . '/' . rawurlencode($gid),
                array('archivrecompagcr' => $cieDef, 'archivredepargarecr' => $gareDef, 'debutenregcr' => $debut, 'finenregcr' => $fin),
                'TRI ARCHIVER',
                'warn'
            );
        }
    }
    $out .= '</div>';
    return $out;
};
?>
<style>
.decl-hub { max-width: 1100px; margin: 0 auto; }
.decl-hub h2 { color: #0b3d91; margin-bottom: 8px; }
.decl-hub .muted { color: #5c6370; font-size: .92rem; }
.decl-filter { background: #fff; border: 1px solid rgba(0,0,0,.08); border-radius: 10px; padding: 14px; margin-bottom: 16px; }
.decl-years { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
.decl-years a {
  display: inline-block; padding: 8px 14px; border-radius: 8px;
  border: 1px solid #0b3d91; color: #0b3d91; text-decoration: none; font-weight: 600;
}
.decl-years a.active { background: #0b3d91; color: #fff; }
.decl-months { display: flex; flex-direction: column; gap: 12px; }
.decl-month-card {
  background: #fff; border: 1px solid rgba(0,0,0,.1); border-radius: 12px; overflow: hidden;
}
.decl-month-card.open { border-color: #0b3d91; box-shadow: 0 2px 10px rgba(11,61,145,.12); }
.decl-month-head {
  display: block; padding: 14px 16px; text-decoration: none; color: inherit;
}
.decl-month-head:hover { background: #f7f9fc; }
.decl-month-card.open .decl-month-head { background: #eef3fb; }
.decl-month-head strong { display: block; font-size: 1.1rem; color: #0b3d91; }
.decl-month-head span { font-size: .85rem; color: #5c6370; }
.decl-month-body { display: none; padding: 0 16px 16px; border-top: 1px solid rgba(0,0,0,.06); }
.decl-month-card.open .decl-month-body { display: block; }
.decl-actions { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; margin-top: 12px; }
.decl-action-form { margin: 0; }
.decl-action-btn {
  width: 100%; text-align: left; padding: 12px 14px; border-radius: 10px;
  border: 1px solid #0b3d91; background: #0b3d91; color: #fff; font-weight: 600; cursor: pointer;
}
.decl-action-btn.secondary { background: #fff; color: #0b3d91; }
.decl-action-btn.warn { background: #1a1c1e; border-color: #1a1c1e; color: #fff; }
.decl-empty { padding: 28px; text-align: center; color: #5c6370; background: #fff; border-radius: 12px; }
</style>

<div class="decl-hub container-fluid py-3">
  <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
    <div>
      <h2><?= htmlspecialchars($titre); ?></h2>
      <p class="muted mb-0">Cliquez un mois déclaré pour afficher ses actions.</p>
    </div>
    <a class="btn btn-secondary" href="<?= htmlspecialchars($retour); ?>">Retour</a>
  </div>

  <form class="decl-filter form-row" method="get" action="<?= htmlspecialchars($baseHub); ?>">
    <?php foreach ($ctx as $k => $v): if ($v === '' || $v === null) continue; ?>
      <input type="hidden" name="<?= htmlspecialchars($k); ?>" value="<?= htmlspecialchars((string) $v); ?>">
    <?php endforeach; ?>
    <div class="form-group col-md-4">
      <label>Compagnie</label>
      <select class="form-control form-control-sm" name="compagnie">
        <option value="">Toutes</option>
        <?php foreach ($compagnies as $c): ?>
          <option value="<?= htmlspecialchars($c->cle_compagnie); ?>" <?= ((string) $c->cle_compagnie === $compFilter) ? 'selected' : ''; ?>>
            <?= htmlspecialchars($c->nom_compagnie); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group col-md-4">
      <label>Gare</label>
      <select class="form-control form-control-sm" name="code_gare">
        <option value="">Toutes</option>
        <?php foreach ($gares as $gr): ?>
          <option value="<?= htmlspecialchars($gr->code_gaexp); ?>" <?= ((string) $gr->code_gaexp === $gareFilter) ? 'selected' : ''; ?>>
            <?= htmlspecialchars($gr->nom_gaep); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group col-md-4 d-flex align-items-end">
      <button type="submit" class="btn btn-primary btn-sm">Filtrer</button>
    </div>
  </form>

  <?php if (!$annees): ?>
    <div class="decl-empty">Aucun mois déclaré pour ce filtre. Utilisez d’abord le bouton <strong>DECLARATION</strong>.</div>
  <?php else: ?>
    <div class="decl-years">
      <?php foreach ($annees as $y):
        $url = $baseHub . '?' . $qsCtx . ($qsCtx !== '' ? '&' : '') . 'mois=' . rawurlencode(
          (isset($moisParAnnee[$y]) ? array_keys($moisParAnnee[$y])[0] : ($y . '-01'))
        );
      ?>
        <a class="<?= ((string) $y === (string) $anneeSel) ? 'active' : ''; ?>" href="<?= htmlspecialchars($url); ?>"><?= htmlspecialchars($y); ?></a>
      <?php endforeach; ?>
    </div>

    <div class="decl-months">
      <?php
      $liste = isset($moisParAnnee[$anneeSel]) ? $moisParAnnee[$anneeSel] : array();
      ksort($liste);
      foreach ($liste as $m => $bloc):
        $mm = substr($m, 5, 2);
        $label = isset($labelsMois[$mm]) ? $labelsMois[$mm] : $m;
        $url = $baseHub . '?' . $qsCtx . ($qsCtx !== '' ? '&' : '') . 'mois=' . rawurlencode($m);
        $open = ($m === $moisSel);
        $cieDef = $compFilter;
        $gareDef = $gareFilter;
        if ($cieDef === '' && !empty($bloc['compagnies'])) {
            $cieDef = $bloc['compagnies'][0];
        }
        if ($gareDef === '' && !empty($bloc['gares'])) {
            $gareDef = $bloc['gares'][0];
        }
        $meta = htmlspecialchars($m);
        if ($cieDef !== '' && isset($nomsCie[$cieDef])) {
            $meta .= ' · ' . htmlspecialchars($nomsCie[$cieDef]);
        }
        if ($gareDef !== '' && isset($nomsGare[$gareDef])) {
            $meta .= ' · ' . htmlspecialchars($nomsGare[$gareDef]);
        }
      ?>
        <div class="decl-month-card <?= $open ? 'open' : ''; ?>">
          <a class="decl-month-head" href="<?= htmlspecialchars($url); ?>">
            <strong><?= htmlspecialchars($label . ' ' . substr($m, 0, 4)); ?></strong>
            <span><?= $meta; ?> — cliquez pour les actions</span>
          </a>
          <?php if ($open): ?>
            <div class="decl-month-body">
              <?php if ($cieDef === '' || $gareDef === ''): ?>
                <p class="text-warning mb-0">Sélectionnez une compagnie et une gare (filtre ci-dessus) pour lancer les états.</p>
              <?php else: ?>
                <p class="muted mb-0">Période <?= htmlspecialchars($bloc['debut']); ?> → <?= htmlspecialchars($bloc['fin']); ?></p>
                <?= $decl_render_actions($bloc, $cieDef, $gareDef); ?>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
