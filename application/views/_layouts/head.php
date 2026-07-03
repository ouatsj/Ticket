<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="/assets/img/">
    <title><?= "{$this->session->company->nom_entreprise} • {$title} "; ?></title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,400,700,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css"
          href="<?= base_url('assets/lib/perfect-scrollbar/css/perfect-scrollbar.css'); ?>"/>
    <link rel="stylesheet" type="text/css"
          href="<?= base_url('assets/lib/material-design-icons/css/material-design-iconic-font.min.css'); ?>"/>
    <link rel="stylesheet" type="text/css"
          href="<?= base_url('assets/lib/datatables/datatables.net-bs4/css/dataTables.bootstrap4.css'); ?>"/>
    <link rel="stylesheet" type="text/css"
          href="<?= base_url('assets/lib/datatables/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css'); ?>"/>
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css'); ?>" type="text/css"/>
    <link rel="stylesheet" href="<?= base_url('assets/css/remove.css'); ?>" type="text/css"/>
    <link rel="stylesheet" href="<?= base_url('assets/lib/sweetalert2/sweetalert2.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/lib/mprogress/css/mprogress.min.css'); ?>">
</head>
