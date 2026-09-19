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
    <link rel="stylesheet" href="<?= base_url('assets/lib/fa/css/all.min.css'); ?>">
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
        }
        .reimpri-shell .fas { font-style: normal; }
        /* Pas de Bootstrap ici : styles boutons Accueil du chrome r17 */
        .reimpri-shell a.btn {
            display: inline-block;
            text-decoration: none;
            color: #fff;
            background: #64748b;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }
        .reimpri-shell a.btn-secondary { background: #475569; color: #fff; }
        .reimpri-shell .alert {
            padding: 0.55rem 0.75rem;
            border-radius: 8px;
            margin: 0 0 0.75rem;
        }
        .reimpri-shell .alert-danger { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .reimpri-shell .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
    </style>
</head>
<body class="reimpri-standalone">
<div class="reimpri-shell">
<?= isset($cfl) ? $cfl : ''; ?>
</div>
</body>
</html>
