<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
.guichet-accueil-toolbar .nav-tabs {
    flex-wrap: wrap;
    border-bottom: 2px solid #dee2e6;
}
.guichet-accueil-toolbar .nav-tabs .nav-link {
    font-size: 0.82rem;
    font-weight: 600;
    padding: 0.45rem 0.75rem;
    white-space: nowrap;
}
.guichet-accueil-toolbar .guichet-btn-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    justify-content: flex-start;
    text-align: left;
}
.guichet-accueil-toolbar .guichet-btn-grid .btn {
    margin: 0;
    white-space: normal;
    text-align: left;
}
.guichet-accueil-kpis .badge {
    font-size: 0.95rem;
    font-weight: 600;
}
.guichet-accueil-toolbar .guichet-retour-row {
    margin-bottom: 0.5rem;
    text-align: left;
}
@media (max-width: 767px) {
    .guichet-accueil-toolbar .guichet-btn-grid .btn {
        flex: 1 1 100%;
    }
}
</style>
