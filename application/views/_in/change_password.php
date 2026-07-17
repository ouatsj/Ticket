<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RAKIETA • Nouveau mot de passe</title>
    <link rel="shortcut icon" href="<?= base_url('assets/img/favicon-rakieta.png'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css'); ?>">
</head>
<body class="be-splash-screen">
<div class="main-content container-fluid">
    <div class="row justify-content-md-center">
        <div class="col-12 col-lg-5 col-xl-4">
            <div class="card">
                <div class="card-header text-center">
                    <h3>Première connexion</h3>
                </div>
                <div class="card-body">
                    <p>Le mot de passe temporaire doit être remplacé avant d’ouvrir l’administration.</p>
                    <p class="text-muted">
                        Au moins 10 caractères, avec majuscule, minuscule, chiffre et caractère spécial.
                    </p>
                    <?php if (!empty($password_error)): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($password_error, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>

                    <?= form_open('login/change_password_submit'); ?>
                    <div class="form-group">
                        <label for="new_password">Nouveau mot de passe</label>
                        <input class="form-control" id="new_password" name="new_password"
                               type="password" autocomplete="new-password" required minlength="10">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                        <input class="form-control" id="confirm_password" name="confirm_password"
                               type="password" autocomplete="new-password" required minlength="10">
                    </div>
                    <button class="btn btn-success btn-block" type="submit">
                        Enregistrer le nouveau mot de passe
                    </button>
                    <?= form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
