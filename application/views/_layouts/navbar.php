<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<nav class="navbar navbar-expand fixed-top be-top-header">
<div class="be-right-navbar">
    <? switch ($this->session->agent->userole) {
    case 1:
    case 2:
    case 3:
    case 4:
    case 5:
    case 6:
    case 7:
    case 8:
    case 9:
    case 10:
    case 11:
    case 12:
    case 13:
    case 14:
    case 15:
    case 16:
    case 17:
    case 18: ?>
        

            <ul class="nav navbar-nav float-right be-user-nav">

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle"
                        href="#" data-toggle="dropdown" role="button" aria-expanded="false">
                        <i class="fas fa-user fa-2x"></i>&nbsp;
                        <span class="user-name"><?= "{$this->session->agent->username}"; ?></span>
                    </a>

                    <div class="dropdown-menu" role="menu">

                        <div class="user-info">

                        <div class="user-name"><?= $this->session->agent->username; ?></div>

                            <div class="user-position online">Connecté</div>

                        </div>

                        <a class="dropdown-item" href="#"><span class="icon mdi mdi-face"></span>Compte</a>
                        <a class="dropdown-item" href="#"><span class="icon mdi mdi-settings"></span>Paramètres</a>
                        <a class="dropdown-item"
                            href="<?= site_url('Login/lout/' . $this->session->session_id . '/' . $this->session->agent->cpuser_id); ?>"><span
                                    class="fas fa-power-off"></span>&nbsp;&nbsp;Déconnexion</a>

                    </div>

                </li>

            </ul>

        <div class="page-title"><span><?= $pagetitle; ?></span></div>
            
    <? break;
    default:
    break;
    } ?>
    <? if ($this->session->agent->userole === '1'): ?>

        <ul class="nav navbar-nav float-right be-icons-nav">

            <li class="nav-item dropdown">

                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" role="button"
                    aria-expanded="true" title="Paramètres">
                    <i class="fas fa-cogs"></i>
                </a>

                <ul class="dropdown-menu be-notifications">

                    <li>
                        <div class="text-body">
                            <a class="dropdown-item"
                            href="<?= site_url("entreprises/{$this->session->company->ekey}"); ?>">
                            <i class="fas fa-edit text-success"></i>
                                Entreprises
                            </a>
                            <a class="dropdown-item"
                            href="<?= site_url("compagnies/{$this->session->company->ekey}"); ?>">
                            <i class="fas fa-business-time"></i>
                                Compagnies
                            </a>
                            <a class="dropdown-item"
                            href="<?= site_url("banques/{$this->session->company->ekey}"); ?>">
                                <i class="fas fa-edit text-danger"></i>
                                Banques
                            </a>
                            <a class="dropdown-item"
                            href="<?= site_url("villes/{$this->session->company->ekey}"); ?>">
                                <i class="fas fa-edit text-dark"></i>
                                Villes
                            </a>
                            <a class="dropdown-item"
                            href="<?= site_url("gares/gare/{$this->session->company->ekey}"); ?>">
                                <i class="fas fa-edit text-info"></i>
                                Gares
                            </a>
                            <a class="dropdown-item"
                            href="<?= site_url("villes/quart/{$this->session->company->ekey}"); ?>">
                                <i class="fas fa-edit text-info"></i>
                                Quartiers
                            </a>
                            <a class="dropdown-item"
                                href="<?= site_url("statut_gares/{$this->session->company->ekey}"); ?>">
                                <i class="fas fa-edit text-info"></i>
                                Statut_gare
                            </a>
                            <a class="dropdown-item"
                                href="<?= site_url("gares/position/{$this->session->company->ekey}"); ?>">
                                <i class="fas fa-edit text-info"></i>
                                Position
                            </a>
                            <a class="dropdown-item"
                                href="<?= site_url("role_user/{$this->session->company->ekey}"); ?>">
                                <i class="fas fa-pencil-ruler"></i>
                                    Rôles
                                </a>
                            <a class="dropdown-item"
                                href="<?= site_url("utilisateurs/{$this->session->company->ekey}"); ?>">
                                    <i class="fas fa-users"></i>&nbsp;Utilisateurs
                            </a>
                            <a class="dropdown-item"
                                href="<?= site_url("menus/{$this->session->company->ekey}"); ?>">
                                <i class="fas fa-edit text-info"></i>
                                Boutons
                            </a>
                            <a class="dropdown-item"
                                href="<?= site_url("pages/{$this->session->company->ekey}"); ?>">
                                <i class="fas fa-edit text-success"></i>
                                    Pages
                            </a>
                        </div>
                    </li>
                </ul>
            </li>
        </ul>
    <? endif; ?>
    <? if ($this->session->agent->userole === '2'): ?>

        <ul class="nav navbar-nav float-right be-icons-nav">

            <li class="nav-item dropdown">

                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" role="button"
                    aria-expanded="true" title="Paramètres">
                    <i class="fas fa-cogs"></i>
                </a>

                <ul class="dropdown-menu be-notifications">

                    <li>
                        <div class="text-body">
                            <a class="dropdown-item"
                            href="<?= site_url("entreprises/{$this->session->company->ekey}"); ?>">
                                    <i class="fas fa-edit text-success"></i>
                                Entreprises
                            </a>
                            <a class="dropdown-item"
                            href="<?= site_url("compagnies/{$this->session->company->ekey}"); ?>">
                            <i class="fas fa-business-time"></i>
                                Compagnies
                            </a>
                            <a class="dropdown-item"
                            href="<?= site_url("banques/{$this->session->company->ekey}"); ?>">
                                <i class="fas fa-edit text-danger"></i>
                                Banques
                            </a>
                            <a class="dropdown-item"
                            href="<?= site_url("villes/{$this->session->company->ekey}"); ?>">
                                <i class="fas fa-edit text-dark"></i>
                                Villes
                            </a>
                            <a class="dropdown-item" href="<?= site_url("gares/gare/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-edit text-info"></i>
                                Gares
                            </a>
                            <a class="dropdown-item"
                            href="<?= site_url("villes/quart/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-edit text-info"></i>
                                Quartiers
                            </a>
                            <a class="dropdown-item"
                                href="<?= site_url("statut_gares/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-edit text-info"></i>
                                Statut_gare
                            </a>
                            <a class="dropdown-item"
                                href="<?= site_url("gares/position/{$this->session->company->ekey}"); ?>">
                                <i class="fas fa-edit text-info"></i>
                                Position
                            </a>
                            <a class="dropdown-item"
                                href="<?= site_url("role_user/{$this->session->company->ekey}"); ?>">
                                    <i class="fas fa-pencil-ruler"></i>
                                    Rôles
                                </a>
                            <a class="dropdown-item"
                                href="<?= site_url("utilisateurs/{$this->session->company->ekey}"); ?>">
                                    <i class="fas fa-users"></i>&nbsp;Utilisateurs
                            </a>
                            
                        </div>
                    </li>

                </ul>

            </li>

        </ul>
    <? endif; ?>
	<? if ($this->session->agent->userole === '4'): ?>

        <ul class="nav navbar-nav float-right be-icons-nav">

            <li class="nav-item dropdown">

                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" role="button"
                    aria-expanded="true" title="Paramètres">
                    <i class="fas fa-cogs"></i>
                </a>

                <ul class="dropdown-menu be-notifications">

                    <li>
                        <div class="text-body">
                           
                            <a class="dropdown-item"
                            href="<?= site_url("banques/{$this->session->company->ekey}"); ?>">
                                <i class="fas fa-edit text-danger"></i>
                                Banques
                            </a>
                          
                            
                        </div>
                    </li>

                </ul>

            </li>

        </ul>
    <? endif; ?>
    <? if ($this->session->agent->userole === '5'): ?>
        <ul class="nav navbar-nav float-right be-icons-nav">

            <li class="nav-item dropdown">

                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" role="button"
                    aria-expanded="true" title="Paramètres">
                    <i class="fas fa-cogs"></i>
                </a>

                <ul class="dropdown-menu be-notifications">

                    <li>
                        <div class="text-body">
                        
                            <a class="dropdown-item"
                                href="<?= site_url("villes/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-edit text-dark"></i>
                                Villes
                            </a>
                            <a class="dropdown-item"
                                href="<?= site_url("villes/quart/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-edit text-info"></i>
                                Quartiers
                            </a>
                            <a class="dropdown-item"
                                href="<?= site_url("statut_gares/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-edit text-info"></i>
                                Statut_gare
                            </a>
                            <a class="dropdown-item"
                                href="<?= site_url("gares/position/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-edit text-info"></i>
                                Position
                            </a>                            
                            
                        </div>
                    </li>

                </ul>

            </li>

        </ul>
    <? endif; ?>
    <? if ($this->session->agent->userole === '9'): ?>
        <ul class="nav navbar-nav float-right be-icons-nav">

            <li class="nav-item dropdown">

                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" role="button"
                    aria-expanded="true" title="Paramètres">
                    <i class="fas fa-cogs"></i>
                </a>

                <ul class="dropdown-menu be-notifications">

                    <li>
                        <div class="text-body">
                        
                            <a class="dropdown-item"
                                href="<?= site_url("villes/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-edit text-dark"></i>
                                Villes
                            </a>
                            <a class="dropdown-item"
                                href="<?= site_url("villes/quart/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-edit text-info"></i>
                                Quartiers
                            </a>
                            
                        </div>
                    </li>

                </ul>

            </li>

        </ul>
    <? endif; ?>
</div>
</nav>