<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <?php if (!empty($no_cache)): ?>
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <?php endif; ?>
    <meta name="description" content="">
    <meta name="author" content="">
    <?php if (function_exists('get_instance') && config_item('csrf_protection')): ?>
    <meta name="csrf-token" content="<?= htmlspecialchars($this->security->get_csrf_hash(), ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="csrf-param" content="<?= htmlspecialchars($this->security->get_csrf_token_name(), ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
    <meta name="cdn-watch-endpoint" content="<?= htmlspecialchars(base_url('cdnhealth/report'), ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="cdn-watch-script-warn" content="20">
    <link rel="preconnect" href="<?= base_url(); ?>" crossorigin>
    <link rel="dns-prefetch" href="<?= base_url(); ?>">
    <link rel="shortcut icon" href="<?= base_url('assets/img/avatar3.png'); ?>">
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/avatar3.png'); ?>">
    <title><?php
        $companyName = ($this->session->userdata('company') && isset($this->session->company->nom_entreprise))
            ? $this->session->company->nom_entreprise
            : 'Rakieta Bus';
        echo htmlspecialchars($companyName . ' • ' . (isset($title) ? $title : ''), ENT_QUOTES, 'UTF-8');
    ?></title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,400,700,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css"
          href="<?= base_url('assets/lib/perfect-scrollbar/css/perfect-scrollbar.css'); ?>"/>
    <link rel="stylesheet" type="text/css"
          href="<?= base_url('assets/lib/material-design-icons/css/material-design-iconic-font.min.css'); ?>"/>
    <?php if (!empty($bundle_datatables)): ?>
    <link rel="stylesheet" type="text/css"
          href="<?= base_url('assets/lib/datatables/datatables.net-bs4/css/dataTables.bootstrap4.css'); ?>"/>
    <link rel="stylesheet" type="text/css"
          href="<?= base_url('assets/lib/datatables/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css'); ?>"/>
    <?php endif; ?>
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css'); ?>" type="text/css"/>
    <link rel="stylesheet" href="<?= base_url('assets/css/remove.css'); ?>" type="text/css"/>
    <link rel="stylesheet" href="<?= base_url('assets/lib/sweetalert2/sweetalert2.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/lib/mprogress/css/mprogress.min.css'); ?>">
</head>
