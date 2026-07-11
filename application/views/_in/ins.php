<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="RAKIETA • Connexion">
    <meta name="author" content="NET SOLUTIONS">

    <title>RAKIETA </title>

    <link rel="stylesheet" type="text/css"
          href="<?= base_url('assets/lib/perfect-scrollbar/css/perfect-scrollbar.css'); ?>"/>
    <link rel="stylesheet" type="text/css"
          href="<?= base_url('assets/lib/material-design-icons/css/material-design-iconic-font.min.css') ?>"/>
    <link rel="stylesheet" type="text/css"
          href="<?= base_url('assets/css/app.css'); ?>"/>

<body class="be-splash-screen">
    <div class="main-content container-fluid">
        <div class="row justify-content-md-center">
            <div class="col-12 col col-lg-3 col-xl-3">
                <div class="card">
                    <div class="card-header"></div>
                    <div class="card-body text-center">
                    
                        <span class="splash-description">Entrez vos informations de connexion.</span>
                        <?php if (!empty($login_error)): ?>
                        <div class="alert alert-danger mt-2">Identifiants incorrects. Réessayez.</div>
                        <?php endif; ?>
                        <div class="card-body">
                            
                            <?= form_open('Login/lin_s/')?>

                            <div class="login-form">
                                <div class="form-group">
                                    <input class="form-control" type="text"
                                        placeholder="Nom utilisateur"
                                        name="username"
                                        autocomplete="off">
                                </div>
                                
                                <div class="form-group">
                                <input class="form-control form-control-sm" id="upassword" type="password"
                                       name="upassword"
                                       placeholder="Mot de passe">
                           
                                </div>
                            </div>
                                <div class="form-group row login-submit">
                                    <div class="col-6">
                                        <button class="btn btn-rounded btn-outline-success btn-space"
                                                type="submit">
                                            <i class="icon icon-left mdi mdi-check-all"></i>
                                            Connexion
                                        </button>
                                    </div>
                                    
                                </div>
                            </div>
                            
                            <?= form_close(); ?>

                        </div>
            
                    </div>
                </div>
            </div>
        </div>
    </div>
<script src="<?= base_url('assets/lib/jquery/jquery.min.js'); ?>" type="text/javascript"></script>
<script src="<?= base_url('assets/lib/perfect-scrollbar/js/perfect-scrollbar.min.js'); ?>"
        type="text/javascript"></script>
<script src="<?= base_url('assets/lib/bootstrap/dist/js/bootstrap.bundle.min.js'); ?>" type="text/javascript"></script>
<script src="<?= base_url('assets/js/app.js'); ?>" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function () {
        //-initialize the javascript
        App.init();
    });

</script>
</body>

</html>
