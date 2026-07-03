<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="be-left-sidebar">
    <div class="left-sidebar-wrapper">
        <a class="left-sidebar-toggle" href="#">Dashboard</a>
        <div class="left-sidebar-spacer">
            <div class="left-sidebar-scroll">
                <div class="left-sidebar-content">
                    <ul class="sidebar-elements">
                        <li class="divider">Menu</li>
                    <? if ($this->session->agent->userole === '1'): ?>

                        <li class="parent"><a href="#"><i class="fas fa-cog"></i><span>CONFIGURATIONS</span></a>

                            <ul class="sub-menu">
                                <li class="parent"><a href="#"><i class="icon mdi mdi-dot-circle"></i><span> PERSONNEL</span></a>
                                    <ul class="sub-menu">
                                        <li class="<?= ($this->uri->segment(1, 0) === 'type' ? 'active' : ''); ?>">

                                            <a href="<?= site_url("types/{$this->session->company->ekey}"); ?>">
                                                <i class="fas fa-edit text-succes"></i>&nbsp;
                                                <span class="">TYPE_PERSONNEL</span>
                                            </a>

                                        </li>
                                        <li class="<?= ($this->uri->segment(1, 0) === 'personnels' ? 'active' : ''); ?>">

                                            <a href="<?= site_url("personnels/{$this->session->company->ekey}"); ?>#personnel">
                                                <i class="fas fa-user-astronaut text-danger"></i>&nbsp;
                                                <span class="">PERSONNELS</span>
                                            </a>

                                        </li>
                                        <li class="<?= ($this->uri->segment(1, 0) === 'parto' ? 'active' : ''); ?>">

                                            <a href="<?= site_url("personnels/partenaire/{$this->session->company->ekey}"); ?>#partenaire">
                                                <i class="fas fa-user-astronaut text-info"></i>&nbsp;
                                                <span class="">FOURNISSEURS</span>
                                            </a>

                                        </li>
                                    </ul>
                                </li>
                                <li class="<?= ($this->uri->segment(1, 0) === 'garesda' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("gares/expedit/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-home text-success"></i>&nbsp;
                                        <span>GARES DEPART</span>
                                    </a>

                                </li>
                                <li class="<?= ($this->uri->segment(1, 0) === 'gares' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("gares/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-home text-danger"></i>&nbsp;
                                        <span>GARES D'ARRIVEE</span>
                                    </a>

                                </li>

                                        
                                <li class="<?=($this->uri->segment(1, 0) === 'axes' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("lignes/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-directions text-dark"></i>&nbsp;
                                        <span class="">LIGNES</span>
                                    </a>

                                </li>

                                <li class="<?= ($this->uri->segment(1, 0) === 'lignes' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("lignes/itineraires/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-directions text-info"></i>&nbsp;
                                        <span class="">ITINERAIRES</span>
                                    </a>

                                </li>

                                <!-- Les heures -->
                                <li class="<?=($this->uri->segment(1, 0) === 'heure' ? 'active' : '');?>">

                                    <a href="<?= site_url("heures/{$this->session->company->ekey}"); ?>">
                                    <i class="fas fa-edit text-success"></i>&nbsp;
                                    <span>HEURES</span>
                                    </a>

                                </li>
                                
                                <li class="<?= ($this->uri->segment(1, 0) === 'categorie' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("categories/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-edit text-success"></i>&nbsp;
                                        <span>CATEGORIE</span>
                                    </a>

                                </li>
                                <!-- Les BUS -->
                                <li class="<?= ($this->uri->segment(1, 0) === 'bus' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("bus/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-bus text-success"></i>&nbsp;
                                        <span>BUS</span>
                                    </a>

                                </li>

                                <li class="parent"><a href="#"><i class="icon mdi mdi-dot-circle"></i><span> TARIFICATION</span></a>
                                    <ul class="sub-menu">
                                        <li class="<?= ($this->uri->segment(1, 0) === 'client' ? 'active' : ''); ?>">

                                            <a href="<?= site_url("tarifs/type/{$this->session->company->ekey}"); ?>">
                                                <i class="fas fa-edit text-success"></i>&nbsp;
                                                <span class="">TYPE_TARIF</span>
                                            </a>

                                        </li>
                                        
                                    </ul>
                                </li>
                                
                                
                                <li class="<?= ($this->uri->segment(1, 0) === 'client' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("types/client/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-edit text-danger"></i>&nbsp;
                                        <span class="">TYPE_CLIENT</span>
                                    </a>

                                </li>
                                <li class="<?= ($this->uri->segment(1, 0) === 'document' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("types/documents/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-edit text-succes"></i>&nbsp;
                                        <span class="">TYPE_DOCUMENT</span>
                                    </a>

                                </li>
                                      
                                <li class="parent"><a href="#"><i class="icon mdi mdi-chart-donut"></i><span> GENRE</span></a>

                                    <ul class="sub-menu">
                                        <li class="<?= ($this->uri->segment(1, 0) === 'genrerecette' ? 'active' : ''); ?>">

                                            <a href="<?= site_url("genres/genre_recettes/{$this->session->company->ekey}"); ?>">
                                                <i class="fas fa-edit text-danger"></i>&nbsp;
                                                <span class="">RECETTE</span>
                                            </a>

                                        </li>
                                        <li class="<?= ($this->uri->segment(1, 0) === 'genredepense' ? 'active' : ''); ?>">

                                            <a href="<?= site_url("genres/genre_depenses/{$this->session->company->ekey}"); ?>">
                                                <i class="fas fa-edit text-danger"></i>&nbsp;
                                                <span class="">DEPENSE</span>
                                            </a>

                                        </li>
                                        <li class="<?= ($this->uri->segment(1, 0) === 'genredepot' ? 'active' : ''); ?>">

                                            <a href="<?= site_url("genres/genre_depots/{$this->session->company->ekey}"); ?>">
                                                <i class="fas fa-edit text-danger"></i>&nbsp;
                                                <span class="">DEPOT</span>
                                            </a>

                                        </li>
                                    </ul>
                                </li>         
                            </ul>
                        </li>
                        
                             
                        <li class="<?= ($this->uri->segment(1, 0) === 'retour' ? 'active' : ''); ?>">

                            <a href="<?= site_url("home/{$this->session->company->ekey}/{$this->session->agent->cpuser_id}/{$this->session->agent->userole}"); ?>">
                                    <i class="fas fa-arrow-circle-left text-info"></i>&nbsp; 
                                    <span>RETOUR A L'ACCUEIL&nbsp;</span>
                                </a>
                        </li>

                    <?endif;?>
                    
                    </ul>
                </div>

            </div>

        </div>
        <div class="left-sidebar-spacer">
            <div class="left-sidebar-scroll">
                <div class="left-sidebar-content">
                    <ul class="sidebar-elements">
                        <li class="divider">Menu</li>
                    <? if ($this->session->agent->userole === '2'): ?>
                        <li class="parent"><a href="#"><i class="fas fa-cog"></i><span>CONFIGURATIONS</span></a>

                            <ul class="sub-menu">
                                <li class="parent">
                                    <a href="#"><i class="icon mdi mdi-dot-circle"></i><span> PERSONNEL</span></a>
                                    <ul class="sub-menu">
                                        <li class="<?= ($this->uri->segment(1, 0) === 'type' ? 'active' : ''); ?>">

                                            <a href="<?= site_url("types/{$this->session->company->ekey}"); ?>">
                                                <i class="fas fa-edit text-succes"></i>&nbsp;
                                                <span class="">TYPE_PERSONNEL</span>
                                            </a>

                                        </li>
                                        <li class="<?= ($this->uri->segment(1, 0) === 'personnels' ? 'active' : ''); ?>">

                                            <a href="<?= site_url("personnels/{$this->session->company->ekey}"); ?>#personnel">
                                                <i class="fas fa-user-astronaut text-danger"></i>&nbsp;
                                                <span class="">PERSONNELS</span>
                                            </a>

                                        </li>
                                        <li class="<?= ($this->uri->segment(1, 0) === 'parto' ? 'active' : ''); ?>">

                                            <a href="<?= site_url("personnels/partenaire/{$this->session->company->ekey}"); ?>#personnel">
                                                <i class="fas fa-user-astronaut text-info"></i>&nbsp;
                                                <span class="">FOURNISSEURS</span>
                                            </a>

                                        </li>
                                    </ul>
                                </li>
                                <li class="<?= ($this->uri->segment(1, 0) === 'garesda' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("gares/expedit/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-home text-success"></i>&nbsp;
                                        <span>GARES DEPART</span>
                                    </a>

                                </li>
                                <li class="<?= ($this->uri->segment(1, 0) === 'gares' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("gares/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-home text-danger"></i>&nbsp;
                                        <span>GARES D'ARRIVEE</span>
                                    </a>

                                </li>

                                        
                                <li class="<?= ($this->uri->segment(1, 0) === 'axes' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("lignes/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-directions text-dark"></i>&nbsp;
                                        <span class="">LIGNES</span>
                                    </a>

                                </li>

                                <!-- Les heures -->
                                <li class="<?= ($this->uri->segment(1, 0) === 'heure' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("heures/{$this->session->company->ekey}"); ?>">
                                    <i class="fas fa-edit text-success"></i>&nbsp;
                                    <span>HEURES</span>
                                    </a>

                                </li>
                                <li class="<?= ($this->uri->segment(1, 0) === 'lignes' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("lignes/itineraires/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-directions text-info"></i>&nbsp;
                                        <span class="">ITINERAIRES</span>
                                    </a>

                                </li>
                                
                                <li class="<?= ($this->uri->segment(1, 0) === 'categorie' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("categories/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-edit text-success"></i>&nbsp;
                                        <span>CATEGORIE</span>
                                    </a>

                                </li>
                                <!-- Les BUS -->
                                <li class="<?= ($this->uri->segment(1, 0) === 'bus' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("bus/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-bus text-success"></i>&nbsp;
                                        <span>BUS</span>
                                    </a>

                                </li>

                                <li class="parent"><a href="#"><i class="icon mdi mdi-dot-circle"></i><span> TARIFICATION</span></a>
                                    <ul class="sub-menu">
                                        <li class="<?= ($this->uri->segment(1, 0) === 'client' ? 'active' : ''); ?>">

                                            <a href="<?= site_url("tarifs/type/{$this->session->company->ekey}"); ?>">
                                                <i class="fas fa-edit text-success"></i>&nbsp;
                                                <span class="">TYPE_TARIF</span>
                                            </a>

                                        </li>
                                        
                                    </ul>
                                </li>
                                
                                <li class="<?= ($this->uri->segment(1, 0) === 'client' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("types/client/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-edit text-danger"></i>&nbsp;
                                        <span class="">TYPE_CLIENT</span>
                                    </a>

                                </li>
                                <li class="<?= ($this->uri->segment(1, 0) === 'document' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("types/documents/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-edit text-succes"></i>&nbsp;
                                        <span class="">TYPE_DOCUMENT</span>
                                    </a>

                                </li>
                                    
                                <li class="parent"><a href="#"><i class="icon mdi mdi-chart-donut"></i><span> GENRE</span></a>

                                    <ul class="sub-menu">
                                        <li class="<?= ($this->uri->segment(1, 0) === 'genrerecette' ? 'active' : ''); ?>">

                                            <a href="<?= site_url("genres/genre_recettes/{$this->session->company->ekey}"); ?>">
                                                <i class="fas fa-edit text-danger"></i>&nbsp;
                                                <span class="">RECETTE</span>
                                            </a>

                                        </li>
                                        <li class="<?= ($this->uri->segment(1, 0) === 'genredepense' ? 'active' : ''); ?>">

                                            <a href="<?= site_url("genres/genre_depenses/{$this->session->company->ekey}"); ?>">
                                                <i class="fas fa-edit text-danger"></i>&nbsp;
                                                <span class="">DEPENSE</span>
                                            </a>

                                        </li>
                                        <li class="<?= ($this->uri->segment(1, 0) === 'genredepot' ? 'active' : ''); ?>">

                                            <a href="<?= site_url("genres/genre_depots/{$this->session->company->ekey}"); ?>">
                                                <i class="fas fa-edit text-danger"></i>&nbsp;
                                                <span class="">DEPOT</span>
                                            </a>

                                        </li>
                                    </ul>
                                </li>         
                            </ul>
                            </li>

                            <li class="<?= ($this->uri->segment(1, 0) === 'retour' ? 'active' : ''); ?>">

                                <a href="<?= site_url("home/{$this->session->company->ekey}/{$this->session->agent->cpuser_id}/{$this->session->agent->userole}"); ?>">
                                    <i class="fas fa-arrow-circle-left text-info"></i>&nbsp; 
                                    <span>RETOUR A L'ACCUEIL&nbsp;</span>
                                </a>
                            </li>    
                            
                    <?endif;?>

                    </ul>
                </div>

            </div>

        </div>
        <div class="left-sidebar-spacer">
            <div class="left-sidebar-scroll">
                <div class="left-sidebar-content">
                    <ul class="sidebar-elements">
                        <li class="divider">Menu</li>
                    <? if ($this->session->agent->userole === '11'): ?>
                        <li class="parent"><a href="#"><i class="fas fa-cog"></i><span>CONFIGURATIONS</span></a>

                            <ul class="sub-menu">
                                <li class="parent">
                                    <a href="#"><i class="icon mdi mdi-dot-circle"></i><span> PERSONNEL</span></a>
                                    <ul class="sub-menu">
                                        <li class="<?= ($this->uri->segment(1, 0) === 'type' ? 'active' : ''); ?>">

                                            <a href="<?= site_url("types/{$this->session->company->ekey}"); ?>">
                                                <i class="fas fa-edit text-succes"></i>&nbsp;
                                                <span class="">TYPE_PERSONNEL</span>
                                            </a>

                                        </li>
                                        <li class="<?= ($this->uri->segment(1, 0) === 'personnels' ? 'active' : ''); ?>">

                                            <a href="<?= site_url("personnels/{$this->session->company->ekey}"); ?>#personnel">
                                                <i class="fas fa-user-astronaut text-danger"></i>&nbsp;
                                                <span class="">PERSONNELS</span>
                                            </a>

                                        </li>
                                        
                                    </ul>
                                </li>
                                
                                         
                            </ul>
                        </li>

                        <li class="<?= ($this->uri->segment(1, 0) === 'retour' ? 'active' : ''); ?>">

                            <a href="<?= site_url("home/{$this->session->company->ekey}/{$this->session->agent->cpuser_id}/{$this->session->agent->userole}"); ?>">
                                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp; 
                                <span>RETOUR A L'ACCUEIL&nbsp;</span>
                            </a>
                        </li>   
                            
                    <?endif;?>

                    </ul>
                </div>

            </div>

        </div>

        <div class="left-sidebar-spacer">
            <div class="left-sidebar-scroll">
                <div class="left-sidebar-content">
                    <ul class="sidebar-elements">
                        <li class="divider">Menu</li>
                    <? if ($this->session->agent->userole === '4'): ?>
                        <li class="<?= ($this->uri->segment(1, 0) === 'retour' ? 'active' : ''); ?>">

                            <a href="<?= site_url("home/{$this->session->company->ekey}/{$this->session->agent->cpuser_id}/{$this->session->agent->userole}"); ?>">
                                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp; 
                                <span>RETOUR A L'ACCUEIL&nbsp;</span>
                            </a>
                        </li>
                                
                        <li class="parent">
                            <a href="#"><i class="icon mdi mdi-chart-donut"></i>
                                <span>GENRE</span>
                            </a>

                            <ul class="sub-menu">
                                <li class="<?= ($this->uri->segment(1, 0) === 'genrerecette' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("genres/genre_recettes/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-edit text-danger"></i>&nbsp;
                                        <span class="">RECETTE</span>
                                    </a>

                                </li>
                                <li class="<?= ($this->uri->segment(1, 0) === 'genredepense' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("genres/genre_depenses/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-edit text-danger"></i>&nbsp;
                                        <span class="">DEPENSE</span>
                                    </a>

                                </li>
                                <li class="<?= ($this->uri->segment(1, 0) === 'genredepot' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("genres/genre_depots/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-edit text-danger"></i>&nbsp;
                                        <span class="">DEPOT</span>
                                    </a>
                                </li>
                            </ul>
                        </li>         

                        <li class="parent">
                                <a href="#"><i class="fas fa-database">   </i><span>DONNEES</span>
                                </a>
                            <ul class="sub-menu">
                                <li class="<?= ($this->uri->segment(1, 0) === 'personnels' ? 'active' : ''); ?>">

                                            <a href="<?= site_url("personnels/{$this->session->company->ekey}"); ?>#personnel">
                                                <i class="fas fa-user-astronaut text-danger"></i>&nbsp;
                                                <span class="">PERSONNELS</span>
                                            </a>

                                        </li>
                                        <li class="<?= ($this->uri->segment(1, 0) === 'parto' ? 'active' : ''); ?>">

                                            <a href="<?= site_url("personnels/partenaire/{$this->session->company->ekey}"); ?>#personnel">
                                                <i class="fas fa-user-astronaut text-info"></i>&nbsp;
                                                <span class="">FOURNISSEURS</span>
                                            </a>

                                        </li>
                                
                                <li class="<?= ($this->uri->segment(1, 0) === 'document' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("types/documents/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-edit text-succes"></i>&nbsp;
                                        <span class="">TYPE_DOCUMENT</span>
                                    </a>

                                </li>
                            </ul>
                        </li>   
                        <?endif;?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="left-sidebar-spacer">
            <div class="left-sidebar-scroll">
                <div class="left-sidebar-content">
                    <ul class="sidebar-elements">
                        <li class="divider">Menu</li>
                        <? if ($this->session->agent->userole === '5'): ?>
                            <li class="<?= ($this->uri->segment(1, 0) === 'retour' ? 'active' : ''); ?>">

                                <a href="<?= site_url("home/{$this->session->company->ekey}/{$this->session->agent->cpuser_id}/{$this->session->agent->userole}"); ?>">
                                    <i class="fas fa-arrow-circle-left text-info"></i>&nbsp; 
                                    <span>RETOUR A L'ACCUEIL&nbsp;</span>
                                </a>
                            </li>
                            <li class="<?= ($this->uri->segment(1, 0) === 'bus' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("bus/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-bus text-success"></i>&nbsp;
                                        <span>BUS</span>
                                    </a>

                                </li>
                            
                                
                            <li class="parent"><a href="#"><i class="icon mdi mdi-chart-donut"></i><span> GENRE</span></a>

                                <ul class="sub-menu">
                                    <li class="<?= ($this->uri->segment(1, 0) === 'genrerecette' ? 'active' : ''); ?>">

                                        <a href="<?= site_url("genres/genre_recettes/{$this->session->company->ekey}"); ?>">
                                            <i class="fas fa-edit text-danger"></i>&nbsp;
                                            <span class="">RECETTE</span>
                                        </a>

                                    </li>
                                    <li class="<?= ($this->uri->segment(1, 0) === 'genredepense' ? 'active' : ''); ?>">

                                        <a href="<?= site_url("genres/genre_depenses/{$this->session->company->ekey}"); ?>">
                                            <i class="fas fa-edit text-danger"></i>&nbsp;
                                            <span class="">DEPENSE</span>
                                        </a>

                                    </li>
                                    <li class="<?= ($this->uri->segment(1, 0) === 'genredepot' ? 'active' : ''); ?>">

                                        <a href="<?= site_url("genres/genre_depots/{$this->session->company->ekey}"); ?>">
                                            <i class="fas fa-edit text-danger"></i>&nbsp;
                                            <span class="">DEPOT</span>
                                        </a>

                                    </li>
                                </ul>
                            </li>         
                            <li class="parent"><a href="#"><i class="fas fa-database"></i><span>DONNEES</span></a>

                            <ul class="sub-menu">
                                <li class="<?= ($this->uri->segment(1, 0) === 'personnels' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("personnels/{$this->session->company->ekey}"); ?>#personnel">
                                        <i class="fas fa-user-astronaut text-danger"></i>&nbsp;
                                        <span class="">PERSONNELS</span>
                                    </a>

                                </li>
                                <li class="<?= ($this->uri->segment(1, 0) === 'parto' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("personnels/partenaire/{$this->session->company->ekey}"); ?>#personnel">
                                        <i class="fas fa-user-astronaut text-info"></i>&nbsp;
                                        <span class="">FOURNISSEURS</span>
                                    </a>

                                </li>
                                
                                <li class="<?= ($this->uri->segment(1, 0) === 'heure' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("heures/{$this->session->company->ekey}"); ?>">
                                    <i class="fas fa-edit text-success"></i>&nbsp;
                                    <span>HEURES</span>
                                    </a>

                                </li>
                                
                                <li class="<?= ($this->uri->segment(1, 0) === 'document' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("types/documents/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-edit text-succes"></i>&nbsp;
                                        <span class="">TYPE_DOCUMENT</span>
                                    </a>

                                </li>
                            </ul>
                        </li>   

                    <?endif;?>

                    </ul>
                </div>

            </div>

        </div>

        <div class="left-sidebar-spacer">
            <div class="left-sidebar-scroll">
                <div class="left-sidebar-content">
                    <ul class="sidebar-elements">
                        <li class="divider">Menu</li>
                        <? if ($this->session->agent->userole === '8'): ?>
                            <li class="<?= ($this->uri->segment(1, 0) === 'retour' ? 'active' : ''); ?>">

                                <a href="<?= site_url("home/{$this->session->company->ekey}/{$this->session->agent->cpuser_id}/{$this->session->agent->userole}"); ?>">
                                    <i class="fas fa-arrow-circle-left text-info"></i>&nbsp; 
                                    <span>RETOUR A L'ACCUEIL&nbsp;</span>
                                </a>
                            </li>
                            <li class="<?= ($this->uri->segment(1, 0) === 'bus' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("bus/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-bus text-success"></i>&nbsp;
                                        <span>BUS</span>
                                    </a>

                            </li>
                           
                                  
                            <li class="parent"><a href="#"><i class="fas fa-database"></i><span>DONNEES</span></a>

                            <ul class="sub-menu">
                                <li class="<?= ($this->uri->segment(1, 0) === 'personnels' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("personnels/{$this->session->company->ekey}"); ?>#personnel">
                                        <i class="fas fa-user-astronaut text-danger"></i>&nbsp;
                                        <span class="">PERSONNELS</span>
                                    </a>

                                </li>
                                
                                <li class="<?= ($this->uri->segment(1, 0) === 'heure' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("heures/{$this->session->company->ekey}"); ?>">
                                    <i class="fas fa-edit text-success"></i>&nbsp;
                                    <span>HEURES</span>
                                    </a>

                                </li>
                                
                            </ul>
                        </li>   

                    <?endif;?>

                    </ul>
                </div>

            </div>

        </div>
        <div class="left-sidebar-spacer">
            <div class="left-sidebar-scroll">
                <div class="left-sidebar-content">
                    <ul class="sidebar-elements">
                        <li class="divider">Menu</li>
                    <? if ($this->session->agent->userole === '3'): ?>
                        <li class="<?= ($this->uri->segment(1, 0) === 'retour' ? 'active' : ''); ?>">

                                <a href="<?= site_url("home/{$this->session->company->ekey}/{$this->session->agent->cpuser_id}/{$this->session->agent->userole}"); ?>">
                                    <i class="fas fa-arrow-circle-left text-info"></i>&nbsp; 
                                    <span>RETOUR A L'ACCUEIL&nbsp;</span>
                                </a>
                            </li>
                               

                        <?endif;?>

                    </ul>
                </div>

            </div>

        </div>

        <div class="left-sidebar-spacer">
            <div class="left-sidebar-scroll">
                <div class="left-sidebar-content">
                    <ul class="sidebar-elements">
                        <li class="divider">Menu</li>
                    <? if ($this->session->agent->userole === '6'): ?>
                        <li class="<?= ($this->uri->segment(1, 0) === 'retour' ? 'active' : ''); ?>">

                                <a href="<?= site_url("home/{$this->session->company->ekey}/{$this->session->agent->cpuser_id}/{$this->session->agent->userole}"); ?>">
                                    <i class="fas fa-arrow-circle-left text-info"></i>&nbsp; 
                                    <span>RETOUR A L'ACCUEIL&nbsp;</span>
                                </a>
                            </li>
                               
                        <?endif;?>

                    </ul>
                </div>

            </div>
        </div>
        <div class="left-sidebar-spacer">
            <div class="left-sidebar-scroll">
                <div class="left-sidebar-content">
                    <ul class="sidebar-elements">
                        <li class="divider">Menu</li>
                    <? if ($this->session->agent->userole === '9'): ?>
                        <li class="parent"><a href="#"><i class="fas fa-cog"></i><span>CONFIGURATIONS</span></a>

                            <ul class="sub-menu">
                                <li class="parent">
                                    <a href="#"><i class="icon mdi mdi-dot-circle"></i><span> PERSONNEL</span></a>
                                    <ul class="sub-menu">
                                        <li class="<?= ($this->uri->segment(1, 0) === 'type' ? 'active' : ''); ?>">

                                            <a href="<?= site_url("types/{$this->session->company->ekey}"); ?>">
                                                <i class="fas fa-edit text-succes"></i>&nbsp;
                                                <span class="">TYPE_PERSONNEL</span>
                                            </a>

                                        </li>
                                        <li class="<?= ($this->uri->segment(1, 0) === 'personnels' ? 'active' : ''); ?>">

                                            <a href="<?= site_url("personnels/{$this->session->company->ekey}"); ?>#personnel">
                                                <i class="fas fa-user-astronaut text-danger"></i>&nbsp;
                                                <span class="">PERSONNELS</span>
                                            </a>

                                        </li>
                                        
                                    </ul>
                                </li>
                                
                            </ul>
                        </li>

                        <li class="<?= ($this->uri->segment(1, 0) === 'retour' ? 'active' : ''); ?>">

                            <a href="<?= site_url("home/{$this->session->company->ekey}/{$this->session->agent->cpuser_id}/{$this->session->agent->userole}"); ?>">
                                    <i class="fas fa-arrow-circle-left text-info"></i>&nbsp; 
                                    <span>RETOUR A L'ACCUEIL&nbsp;</span>
                                </a>
                            </li>    
                            
                        <?endif;?>

                    </ul>
                </div>
            </div>
        </div>
        <div class="left-sidebar-spacer">
            <div class="left-sidebar-scroll">
                <div class="left-sidebar-content">
                    <ul class="sidebar-elements">
                        <li class="divider">Menu</li>
                    <? if ($this->session->agent->userole === '10'): ?>
                        <li class="<?= ($this->uri->segment(1, 0) === 'retour' ? 'active' : ''); ?>">

                                <a href="<?= site_url("home/{$this->session->company->ekey}/{$this->session->agent->cpuser_id}/{$this->session->agent->userole}"); ?>">
                                    <i class="fas fa-arrow-circle-left text-info"></i>&nbsp; 
                                    <span>RETOUR A L'ACCUEIL&nbsp;</span>
                                </a>
                            </li>
                               
                        <?endif;?>

                    </ul>
                </div>

            </div>
        </div>
        <div class="left-sidebar-spacer">
            <div class="left-sidebar-scroll">
                <div class="left-sidebar-content">
                    <ul class="sidebar-elements">
                        <li class="divider">Menu</li>
                    <? if ($this->session->agent->userole === '12'): ?>
                        <li class="<?= ($this->uri->segment(1, 0) === 'retour' ? 'active' : ''); ?>">

                                <a href="<?= site_url("home/{$this->session->company->ekey}/{$this->session->agent->cpuser_id}/{$this->session->agent->userole}"); ?>">
                                    <i class="fas fa-arrow-circle-left text-info"></i>&nbsp; 
                                    <span>RETOUR A L'ACCUEIL&nbsp;</span>
                                </a>
                            </li>
                               
                        <?endif;?>

                    </ul>
                </div>

            </div>
        </div>
        <div class="left-sidebar-spacer">
            <div class="left-sidebar-scroll">
                <div class="left-sidebar-content">
                    <ul class="sidebar-elements">
                        <li class="divider">Menu</li>
                    <? if ($this->session->agent->userole === '13'): ?>
                        <li class="<?= ($this->uri->segment(1, 0) === 'retour' ? 'active' : ''); ?>">

                                <a href="<?= site_url("home/{$this->session->company->ekey}/{$this->session->agent->cpuser_id}/{$this->session->agent->userole}"); ?>">
                                    <i class="fas fa-arrow-circle-left text-info"></i>&nbsp; 
                                    <span>RETOUR A L'ACCUEIL&nbsp;</span>
                                </a>
                            </li>
                               
                        <?endif;?>

                    </ul>
                </div>

            </div>
        </div>
        <div class="left-sidebar-spacer">
            <div class="left-sidebar-scroll">
                <div class="left-sidebar-content">
                    <ul class="sidebar-elements">
                        <li class="divider">Menu</li>
                    <? if ($this->session->agent->userole === '14'): ?>
                        <li class="<?= ($this->uri->segment(1, 0) === 'retour' ? 'active' : ''); ?>">

                                <a href="<?= site_url("home/{$this->session->company->ekey}/{$this->session->agent->cpuser_id}/{$this->session->agent->userole}"); ?>">
                                    <i class="fas fa-arrow-circle-left text-info"></i>&nbsp; 
                                    <span>RETOUR A L'ACCUEIL&nbsp;</span>
                                </a>
                            </li>
                               
                        <?endif;?>

                    </ul>
                </div>

            </div>
        </div>

        <div class="left-sidebar-spacer">
            <div class="left-sidebar-scroll">
                <div class="left-sidebar-content">
                    <ul class="sidebar-elements">
                        <li class="divider">Menu</li>
                        <? if ($this->session->agent->userole === '15'): ?>
                            <li class="<?= ($this->uri->segment(1, 0) === 'retour' ? 'active' : ''); ?>">

                                <a href="<?= site_url("home/{$this->session->company->ekey}/{$this->session->agent->cpuser_id}/{$this->session->agent->userole}"); ?>">
                                    <i class="fas fa-arrow-circle-left text-info"></i>&nbsp; 
                                    <span>RETOUR A L'ACCUEIL&nbsp;</span>
                                </a>
                            </li>
                            <li class="<?= ($this->uri->segment(1, 0) === 'bus' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("bus/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-bus text-success"></i>&nbsp;
                                        <span>BUS</span>
                                    </a>

                            </li>
                                     
                            <li class="parent"><a href="#"><i class="fas fa-database"></i><span>DONNEES</span></a>

                            <ul class="sub-menu">
                                <li class="<?= ($this->uri->segment(1, 0) === 'personnels' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("personnels/{$this->session->company->ekey}"); ?>#personnel">
                                        <i class="fas fa-user-astronaut text-danger"></i>&nbsp;
                                        <span class="">PERSONNELS</span>
                                    </a>

                                </li>
                                <li class="<?= ($this->uri->segment(1, 0) === 'parto' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("personnels/partenaire/{$this->session->company->ekey}"); ?>#personnel">
                                        <i class="fas fa-user-astronaut text-info"></i>&nbsp;
                                        <span class="">FOURNISSEURS</span>
                                    </a>

                                </li>
                                
                                <li class="<?= ($this->uri->segment(1, 0) === 'heure' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("heures/{$this->session->company->ekey}"); ?>">
                                    <i class="fas fa-edit text-success"></i>&nbsp;
                                    <span>HEURES</span>
                                    </a>

                                </li>
                                
                            </ul>
                        </li>   

                    <?endif;?>

                    </ul>
                </div>

            </div>

        </div>
        <div class="left-sidebar-spacer">
            <div class="left-sidebar-scroll">
                <div class="left-sidebar-content">
                    <ul class="sidebar-elements">
                        <li class="divider">Menu</li>
                        <? if ($this->session->agent->userole === '16'): ?>
                            <li class="<?= ($this->uri->segment(1, 0) === 'retour' ? 'active' : ''); ?>">

                                <a href="<?= site_url("home/{$this->session->company->ekey}/{$this->session->agent->cpuser_id}/{$this->session->agent->userole}"); ?>">
                                    <i class="fas fa-arrow-circle-left text-info"></i>&nbsp; 
                                    <span>RETOUR A L'ACCUEIL&nbsp;</span>
                                </a>
                            </li>
                            
                            <li class="parent"><a href="#"><i class="icon mdi mdi-chart-donut"></i><span> GENRE</span></a>

                                <ul class="sub-menu">
                                    <li class="<?= ($this->uri->segment(1, 0) === 'genrerecette' ? 'active' : ''); ?>">

                                        <a href="<?= site_url("genres/genre_recettes/{$this->session->company->ekey}"); ?>">
                                            <i class="fas fa-edit text-danger"></i>&nbsp;
                                            <span class="">RECETTE</span>
                                        </a>

                                    </li>
                                    <li class="<?= ($this->uri->segment(1, 0) === 'genredepense' ? 'active' : ''); ?>">

                                        <a href="<?= site_url("genres/genre_depenses/{$this->session->company->ekey}"); ?>">
                                            <i class="fas fa-edit text-danger"></i>&nbsp;
                                            <span class="">DEPENSE</span>
                                        </a>

                                    </li>
                                    <li class="<?= ($this->uri->segment(1, 0) === 'genredepot' ? 'active' : ''); ?>">

                                        <a href="<?= site_url("genres/genre_depots/{$this->session->company->ekey}"); ?>">
                                            <i class="fas fa-edit text-danger"></i>&nbsp;
                                            <span class="">DEPOT</span>
                                        </a>

                                    </li>
                                </ul>
                            </li>         
                            <li class="parent"><a href="#"><i class="fas fa-database"></i><span>DONNEES</span></a>

                            <ul class="sub-menu">
                                <li class="<?= ($this->uri->segment(1, 0) === 'personnels' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("personnels/{$this->session->company->ekey}"); ?>#personnel">
                                        <i class="fas fa-user-astronaut text-danger"></i>&nbsp;
                                        <span class="">PERSONNELS</span>
                                    </a>

                                </li>
                                <li class="<?= ($this->uri->segment(1, 0) === 'parto' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("personnels/partenaire/{$this->session->company->ekey}"); ?>#personnel">
                                        <i class="fas fa-user-astronaut text-info"></i>&nbsp;
                                        <span class="">FOURNISSEURS</span>
                                    </a>

                                </li>
                                
                                <li class="<?= ($this->uri->segment(1, 0) === 'document' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("types/documents/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-edit text-succes"></i>&nbsp;
                                        <span class="">TYPE_DOCUMENT</span>
                                    </a>

                                </li>
                            </ul>
                        </li>   

                    <?endif;?>

                    </ul>
                </div>

            </div>

        </div>
        <div class="left-sidebar-spacer">
            <div class="left-sidebar-scroll">
                <div class="left-sidebar-content">
                    <ul class="sidebar-elements">
                        <li class="divider">Menu</li>
                    <? if ($this->session->agent->userole === '17'): ?>
                        <li class="<?= ($this->uri->segment(1, 0) === 'retour' ? 'active' : ''); ?>">

                                <a href="<?= site_url("home/{$this->session->company->ekey}/{$this->session->agent->cpuser_id}/{$this->session->agent->userole}"); ?>">
                                    <i class="fas fa-arrow-circle-left text-info"></i>&nbsp; 
                                    <span>RETOUR A L'ACCUEIL&nbsp;</span>
                                </a>
                            </li>
                               
                        <?endif;?>

                    </ul>
                </div>

            </div>
        </div>
        <div class="left-sidebar-spacer">
            <div class="left-sidebar-scroll">
                <div class="left-sidebar-content">
                    <ul class="sidebar-elements">
                        <li class="divider">Menu</li>
                    <? if ($this->session->agent->userole === '18'): ?>
                        <li class="<?= ($this->uri->segment(1, 0) === 'retour' ? 'active' : ''); ?>">

                                <a href="<?= site_url("home/{$this->session->company->ekey}/{$this->session->agent->cpuser_id}/{$this->session->agent->userole}"); ?>">
                                    <i class="fas fa-arrow-circle-left text-info"></i>&nbsp; 
                                    <span>RETOUR A L'ACCUEIL&nbsp;</span>
                                </a>
                                <li class="parent"><a href="#"><i class="icon mdi mdi-chart-donut"></i><span> GENRE</span></a>

                                <ul class="sub-menu">
                                    <li class="<?= ($this->uri->segment(1, 0) === 'genrerecette' ? 'active' : ''); ?>">

                                        <a href="<?= site_url("genres/genre_recettes/{$this->session->company->ekey}"); ?>">
                                            <i class="fas fa-edit text-danger"></i>&nbsp;
                                            <span class="">RECETTE</span>
                                        </a>

                                    </li>
                                    <li class="<?= ($this->uri->segment(1, 0) === 'genredepense' ? 'active' : ''); ?>">

                                        <a href="<?= site_url("genres/genre_depenses/{$this->session->company->ekey}"); ?>">
                                            <i class="fas fa-edit text-danger"></i>&nbsp;
                                            <span class="">DEPENSE</span>
                                        </a>

                                    </li>
                                    <li class="<?= ($this->uri->segment(1, 0) === 'genredepot' ? 'active' : ''); ?>">

                                        <a href="<?= site_url("genres/genre_depots/{$this->session->company->ekey}"); ?>">
                                            <i class="fas fa-edit text-danger"></i>&nbsp;
                                            <span class="">DEPOT</span>
                                        </a>

                                    </li>
                                </ul>
                            </li>         
                            <li class="parent"><a href="#"><i class="fas fa-database"></i><span>DONNEES</span></a>

                            <ul class="sub-menu">
                                <li class="<?= ($this->uri->segment(1, 0) === 'personnels' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("personnels/{$this->session->company->ekey}"); ?>#personnel">
                                        <i class="fas fa-user-astronaut text-danger"></i>&nbsp;
                                        <span class="">PERSONNELS</span>
                                    </a>

                                </li>
                                <li class="<?= ($this->uri->segment(1, 0) === 'parto' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("personnels/partenaire/{$this->session->company->ekey}"); ?>#personnel">
                                        <i class="fas fa-user-astronaut text-info"></i>&nbsp;
                                        <span class="">FOURNISSEURS</span>
                                    </a>

                                </li>
                                
                                <li class="<?= ($this->uri->segment(1, 0) === 'document' ? 'active' : ''); ?>">

                                    <a href="<?= site_url("types/documents/{$this->session->company->ekey}"); ?>">
                                        <i class="fas fa-edit text-succes"></i>&nbsp;
                                        <span class="">TYPE_DOCUMENT</span>
                                    </a>

                                </li>
                            </ul>
                            </li>
                               
                        <?endif;?>

                    </ul>
                </div>

            </div>
        </div>
    </div>
</div>