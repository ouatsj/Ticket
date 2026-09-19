<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Impression</title>
    <style>
        @page {
            size: 57mm 40mm;
            margin: 0;
        }
        html, body {
            margin: 0;
            padding: 0;
            background: #fff;
            color: #000;
            -webkit-text-size-adjust: 100%;
        }
        @media print {
            /* Hauteur laissée aux vues ticket/reçu (1 page 57×40 à la fois). */
            html, body {
                width: 57mm !important;
                height: auto !important;
                min-height: 0 !important;
                max-height: none !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                overflow: visible !important;
            }
        }
    </style>
</head>
<body>
<?= isset($cfl) ? $cfl : ''; ?>
</body>
</html>
