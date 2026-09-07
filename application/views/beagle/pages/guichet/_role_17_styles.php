<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
/* Accueil vendeur escale (rôle 17) — dense sur TPE, confortable sur desktop */
.r17-shell {
    --r17-gap: 0.45rem;
    --r17-radius: 8px;
    --r17-primary: #0d6efd;
    --r17-card: #ffffff;
    --r17-text: #1f2937;
    --r17-muted: #6b7280;
    --r17-touch: 40px;
    max-width: 960px;
    margin: 0 auto;
    padding: 0.45rem;
    padding-left: max(0.45rem, env(safe-area-inset-left));
    padding-right: max(0.45rem, env(safe-area-inset-right));
    padding-bottom: max(0.45rem, env(safe-area-inset-bottom));
    box-sizing: border-box;
}
.r17-shell .r17-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.35rem;
    margin-bottom: 0.4rem;
}
.r17-shell .r17-header .btn {
    min-height: 34px;
    padding: 0.3rem 0.65rem;
    font-size: 0.8rem;
    font-weight: 600;
}
.r17-shell .r17-gare {
    color: var(--r17-muted);
    font-size: 0.78rem;
    margin: 0;
    line-height: 1.25;
}
.r17-shell .r17-solde {
    background: linear-gradient(135deg, #0ea5e9, #0369a1);
    color: #fff;
    border-radius: var(--r17-radius);
    padding: 0.45rem 0.7rem;
    margin-bottom: 0.5rem;
    box-shadow: 0 3px 10px rgba(3, 105, 161, 0.18);
}
.r17-shell .r17-solde .label {
    display: block;
    font-size: 0.65rem;
    opacity: 0.9;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}
.r17-shell .r17-solde .amount {
    display: block;
    font-size: 1.15rem;
    font-weight: 700;
    line-height: 1.15;
    margin-top: 0.05rem;
}
.r17-shell .r17-grid {
    display: grid;
    /* 2 colonnes dès le téléphone : moins de scroll */
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--r17-gap);
}
.r17-shell .r17-btn {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    width: 100%;
    min-height: 42px;
    padding: 0.45rem 0.5rem;
    margin: 0;
    border: 1px solid #e5e7eb;
    border-radius: var(--r17-radius);
    background: var(--r17-card);
    color: var(--r17-text);
    text-align: left;
    text-decoration: none !important;
    font-weight: 650;
    font-size: 0.8rem;
    line-height: 1.15;
    box-shadow: none;
    -webkit-tap-highlight-color: transparent;
    touch-action: manipulation;
}
.r17-shell .r17-btn:hover,
.r17-shell .r17-btn:focus {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: var(--r17-text);
}
.r17-shell .r17-btn:active {
    transform: scale(0.99);
}
.r17-shell .r17-btn .r17-ico {
    flex: 0 0 1.55rem;
    width: 1.55rem;
    height: 1.55rem;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #eff6ff;
    color: var(--r17-primary);
    font-size: 0.78rem;
}
.r17-shell .r17-btn.is-primary {
    background: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
    grid-column: 1 / -1;
    min-height: 44px;
    box-shadow: 0 4px 10px rgba(13, 110, 253, 0.2);
}
.r17-shell .r17-btn.is-primary .r17-ico {
    background: rgba(255,255,255,0.18);
    color: #fff;
}
.r17-shell .r17-btn.is-primary:hover,
.r17-shell .r17-btn.is-primary:focus {
    background: #0b5ed7;
    color: #fff;
}
.r17-shell .r17-btn .r17-txt {
    flex: 1 1 auto;
    min-width: 0;
}
/* Sous-titres masqués sur TPE pour gagner de la place */
.r17-shell .r17-btn .r17-sub {
    display: none;
}

.be-minimal-chrome .be-content {
    margin-left: 0 !important;
}
.be-minimal-chrome .be-top-header {
    min-height: 44px;
}
.be-minimal-chrome .be-top-header .page-title {
    max-width: 42vw;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 0.8rem;
}
.be-minimal-chrome .be-top-header .user-name {
    max-width: 6rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: inline-block;
    vertical-align: middle;
}
.be-minimal-chrome .be-content .main-content.container-fluid {
    padding-left: 0.35rem !important;
    padding-right: 0.35rem !important;
    padding-top: 0.35rem !important;
    max-width: 100%;
}

/* Modale vente — compacte TPE (tout visible sans scroll) */
#ticketescal-0.modal-container {
    z-index: 1050;
}
#ticketescal-0 .modal-content.r17-vente-modal,
#ticketescal-0 .modal-content {
    width: 100%;
    max-width: 420px;
    min-width: 0 !important;
    margin: 0.35rem auto;
    border-radius: 10px;
    overflow: hidden;
    box-sizing: border-box;
}
#ticketescal-0 .modal-header {
    padding: 0.4rem 0.65rem !important;
    min-height: 0 !important;
}
#ticketescal-0 .modal-title {
    font-size: 0.9rem !important;
    font-weight: 700;
    margin: 0 !important;
}
#ticketescal-0 .modal-body {
    padding: 0.55rem 0.65rem 0.65rem !important;
    max-height: none;
    overflow: visible;
}
#ticketescal-0 .r17-vente-form .form-group,
#ticketescal-0 .form-group {
    margin-bottom: 0.4rem !important;
}
#ticketescal-0 .r17-vente-form label,
#ticketescal-0 label {
    font-weight: 600;
    font-size: 0.72rem;
    margin-bottom: 0.12rem;
    display: block;
}
#ticketescal-0 .form-control,
#ticketescal-0 select.form-control,
#ticketescal-0 input.form-control {
    min-height: 36px !important;
    height: 36px !important;
    font-size: 16px;
    padding: 0.25rem 0.5rem !important;
    border-radius: 6px;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}
#ticketescal-0 .r17-depart-chip {
    font-size: 0.78rem;
    line-height: 1.25;
    padding: 0.3rem 0.45rem;
    margin-bottom: 0.4rem;
    background: #f1f5f9;
    border-radius: 6px;
    color: #334155;
}
#ticketescal-0 .r17-name-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.4rem;
}
#ticketescal-0 .r17-name-row .form-group {
    margin-bottom: 0.35rem !important;
}
#ticketescal-0 .modal-footer {
    display: flex;
    flex-wrap: nowrap;
    gap: 0.35rem;
    justify-content: stretch;
    padding: 0.35rem 0 0 !important;
    margin: 0 !important;
    border: 0 !important;
}
#ticketescal-0 .modal-footer .btn,
#ticketescal-0 .modal-footer input.btn {
    flex: 1 1 50%;
    min-height: 38px !important;
    height: 38px !important;
    margin: 0;
    padding: 0.35rem 0.5rem !important;
    font-size: 0.85rem;
    font-weight: 700;
    border-radius: 6px;
}
#ticketescal-0 #prix_escale_hint {
    display: block;
    margin-top: 0.15rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: #0369a1;
    min-height: 0.9rem;
}

@media (min-width: 576px) {
    .r17-shell {
        --r17-gap: 0.65rem;
        padding: 0.85rem 1.1rem;
    }
    .r17-shell .r17-btn {
        min-height: 48px;
        font-size: 0.9rem;
        padding: 0.65rem 0.75rem;
        gap: 0.55rem;
    }
    .r17-shell .r17-btn .r17-ico {
        flex-basis: 1.9rem;
        width: 1.9rem;
        height: 1.9rem;
        font-size: 0.9rem;
    }
    .r17-shell .r17-btn .r17-sub {
        display: block;
        font-size: 0.72rem;
        font-weight: 500;
        opacity: 0.8;
        margin-top: 0.1rem;
    }
    .r17-shell .r17-solde .amount {
        font-size: 1.55rem;
    }
}

@media (min-width: 992px) {
    .r17-shell .r17-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .r17-shell .r17-btn.is-primary {
        grid-column: auto;
        min-height: 56px;
    }
}

@media (max-width: 575.98px) {
    .r17-shell .alert {
        padding: 0.4rem 0.55rem;
        font-size: 0.78rem;
        margin-bottom: 0.4rem;
    }
    #ticketescal-0.modal-container {
        padding: 0;
        align-items: flex-start;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }
    #ticketescal-0.modal-container .modal-content.r17-vente-modal,
    #ticketescal-0.modal-container .modal-content {
        max-width: 100%;
        width: 100%;
        min-width: 0 !important;
        min-height: 0 !important;
        height: auto !important;
        border-radius: 0;
        margin: 0;
        max-height: 100dvh;
        display: flex;
        flex-direction: column;
    }
    #ticketescal-0 .modal-body {
        padding: 0.45rem 0.55rem 0.55rem !important;
        overflow: visible !important;
        max-height: none !important;
        flex: 0 0 auto;
    }
    #ticketescal-0 .form-control,
    #ticketescal-0 select.form-control,
    #ticketescal-0 input.form-control {
        min-height: 34px !important;
        height: 34px !important;
    }
    #ticketescal-0 .modal-footer .btn,
    #ticketescal-0 .modal-footer input.btn {
        flex: 1 1 50%;
        min-height: 36px !important;
        height: 36px !important;
    }
}
</style>
