<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="description" content="RAKIETA • Connexion">
    <meta name="author" content="NET SOLUTIONS">

    <title>RAKIETA </title>

    <link rel="stylesheet" type="text/css"
          href="<?= base_url('assets/lib/perfect-scrollbar/css/perfect-scrollbar.css'); ?>?v=<?= (int) date('Ymd'); ?>"/>
    <link rel="stylesheet" type="text/css"
          href="<?= base_url('assets/lib/material-design-icons/css/material-design-iconic-font.min.css'); ?>?v=<?= (int) date('Ymd'); ?>"/>
    <link rel="stylesheet" type="text/css"
          href="<?= base_url('assets/css/app.css'); ?>?v=<?= (int) date('Ymd'); ?>"/>

<body class="be-splash-screen">
    <div class="main-content container-fluid">
        <div class="row justify-content-md-center">
            <div class="col-12 col col-lg-3 col-xl-3">
                <div class="card">
                    <div class="card-header"></div>
                    <div class="card-body text-center">
                    
                        <span class="splash-description">Entrez vos informations de connexion.</span>
                        <?php if (!empty($login_fresh) && empty($login_error)) : ?>
                        <div class="alert alert-info mt-2 small">
                            Session réinitialisée. Vous pouvez vous connecter.
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($login_error)): ?>
                        <div class="alert alert-danger mt-2">
                            <?= !empty($login_error_msg)
                                ? htmlspecialchars($login_error_msg, ENT_QUOTES, 'UTF-8')
                                : 'Identifiants incorrects. Réessayez.'; ?>
                            <div class="mt-2">
                                <a class="btn btn-sm btn-warning" href="<?= site_url('login/reset'); ?>">
                                    Réinitialiser la connexion
                                </a>
                            </div>
                        </div>
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
<script src="<?= base_url('assets/lib/jquery/jquery.min.js'); ?>?v=<?= (int) date('Ymd'); ?>" type="text/javascript"></script>
<script src="<?= base_url('assets/lib/perfect-scrollbar/js/perfect-scrollbar.min.js'); ?>?v=<?= (int) date('Ymd'); ?>"
        type="text/javascript"></script>
<script src="<?= base_url('assets/lib/bootstrap/dist/js/bootstrap.bundle.min.js'); ?>?v=<?= (int) date('Ymd'); ?>" type="text/javascript"></script>
<script src="<?= base_url('assets/js/app.js'); ?>?v=<?= (int) date('Ymd'); ?>" type="text/javascript"></script>
<script type="text/javascript">
(function () {
    try {
        if (window.sessionStorage) {
            sessionStorage.clear();
        }
        if (window.localStorage) {
            var keep = ['']; 
            var remove = [];
            for (var i = 0; i < localStorage.length; i++) {
                var k = localStorage.key(i);
                if (k && (k.indexOf('rakieta') !== -1 || k.indexOf('RAKIETA') !== -1 || k.indexOf('ci_') === 0)) {
                    remove.push(k);
                }
            }
            remove.forEach(function (k) { localStorage.removeItem(k); });
        }
        if ('caches' in window) {
            caches.keys().then(function (keys) {
                keys.forEach(function (k) { caches.delete(k); });
            }).catch(function () {});
        }
    } catch (e) {}

    $(document).ready(function () {
        App.init();
    });
})();
</script>
</body>

</html>
