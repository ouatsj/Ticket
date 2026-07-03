<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!DOCTYPE html>
<html lang="en">

<? $this->load->view('_layouts/head'); ?>

<body class="be-splash-screen">

<div class="be-wrapper be-login">

    <div class="be-content">

        <div class="main-content container-fluid">

            <div class="splash-container">

                <div class="card card-border-color card-border-color-primary">

                    <div class="card-header"><img class="logo-img"
                                                  src="<?//= base_url                                             ('assets/img/cmtentete.JPG') ?>"
                                                  alt="logo" width="102"
                                                  height="102"><span
                                class="splash-description">
                            Entrez vos informations de connexion.</span>
                    </div>

                    <div class="card-body">
                        <form>
                            <div class="form-group">
                                <input class="form-control" name="uname"
                                       id="username" type="text"
                                       placeholder="Nom d'utilisateur"
                                       autocomplete="off">
                            </div>
                            <div class="form-group">
                                <input class="form-control" name="upassword"
                                       id="password" type="password"
                                       placeholder="Mot de passe">
                            </div>
                            <div class="form-group row login-tools">
                                <div class="col-6 login-remember">
                                    <label class="custom-control custom-checkbox">
                                        <input class="custom-control-input"
                                               type="checkbox" checked><span
                                                class="custom-control-label">Se souvenir de moi</span>
                                    </label>
                                </div>
                                <div class="col-6 login-forgot-password">
                                    <a href="?!pages-forgot-password.html">
                                        Mot de passe oublié?</a>
                                </div>
                            </div>
                            <div class="form-group login-submit"><a
                                        class="btn btn-primary btn-xl"
                                        href="<?= base_url('company/index/100001'); ?>"
                                        data-dismiss="modal">Me connecter</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="splash-footer">
                    <span>Si vous n'avez pas de compte, contactez votre
                        administrateur.
                        <a href="?!pages-sign-up.html">Contactez.</a>
                    </span>
                </div>

            </div>

        </div>

    </div>

</div>

<? $this->load->view('_layouts/scripts'); ?>
</body>

</html>
