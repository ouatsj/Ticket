
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
            <div class="col-12 col col-lg-4 col-xl-4">
                <div class="card">
                    <div class="card-header"></div>
                    <div class="card-body text-center">

                        <span class="splash-description">Choisissez la gare sur laquelle vous vous connectez</span>
                        <?php if (!empty($type_rols)) : ?>
                        <p class="text-muted small mb-3"><?= htmlspecialchars($type_rols, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>

                        <?= form_open('Login/pick_gare_s/')?>
                            <input type="hidden" name="ekey" value="<?= htmlspecialchars($ekey, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="cpuser_id" value="<?= (int) $cpuser_id; ?>">
                            <input type="hidden" name="userole" value="<?= (int) $userole; ?>">

                            <div class="form-group">
                                <select class="form-control form-control-sm" name="gare_id" required>
                                    <option value="">Choisissez une gare</option>
                                    <?php foreach ($gares as $gare) : ?>
                                    <option value="<?= htmlspecialchars($gare->guser, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?= htmlspecialchars($gare->garenom, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group row login-submit">
                                <div class="col-12">
                                    <button class="btn btn-rounded btn-outline-success btn-space"
                                            type="submit">
                                        <i class="icon icon-left mdi mdi-check-all"></i>
                                        Continuer
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>

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
            App.init();
        });
    </script>
</body>

</html>
