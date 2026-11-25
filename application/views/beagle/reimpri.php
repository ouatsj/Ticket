<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?php
        $companyName = ($this->session->userdata('company') && isset($this->session->company->nom_entreprise))
            ? $this->session->company->nom_entreprise
            : 'Rakieta Bus';
        echo htmlspecialchars($companyName . ' • ' . (isset($title) ? $title : 'Réimpression'), ENT_QUOTES, 'UTF-8');
    ?></title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            background: #f1f5f9;
            color: #0f172a;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            -webkit-text-size-adjust: 100%;
        }
        .reimpri-shell {
            max-width: 720px;
            margin: 0 auto;
            padding: 0.75rem 0.85rem 1.5rem;
            box-sizing: border-box;
            min-height: 100vh;
            background: #fff;
            color: #0f172a;
        }
        .reimpri-fallback {
            margin: 1rem 0;
            padding: 1rem;
            border: 2px dashed #f59e0b;
            border-radius: 8px;
            background: #fffbeb;
            color: #92400e;
            font-size: 1rem;
            font-weight: 700;
        }
    </style>
</head>
<body class="reimpri-standalone">
<div class="reimpri-shell">
<?php
$__cfl = isset($cfl) ? (string) $cfl : '';
if (trim(strip_tags($__cfl)) === '') {
    echo '<div class="reimpri-fallback">Réimpression : contenu indisponible. Revenez à l’accueil puis rouvrez le menu.</div>';
} else {
    echo $__cfl;
}
?>
</div>
</body>
</html>
