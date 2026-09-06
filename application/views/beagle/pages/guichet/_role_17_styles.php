<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
/* Accueil vendeur escale (rôle 17) — téléphone Android + desktop */
.r17-shell {
    --r17-gap: 0.75rem;
    --r17-radius: 12px;
    --r17-primary: #0d6efd;
    --r17-bg: #f3f5f8;
    --r17-card: #ffffff;
    --r17-text: #1f2937;
    --r17-muted: #6b7280;
    --r17-touch: 48px;
    max-width: 960px;
    margin: 0 auto;
    padding: 0.75rem;
}
.r17-shell .r17-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}
.r17-shell .r17-header .btn {
    min-height: var(--r17-touch);
    padding: 0.55rem 0.9rem;
    font-weight: 600;
}
.r17-shell .r17-gare {
    color: var(--r17-muted);
    font-size: 0.9rem;
    margin: 0;
}
.r17-shell .r17-solde {
    background: linear-gradient(135deg, #0ea5e9, #0369a1);
    color: #fff;
    border-radius: var(--r17-radius);
    padding: 1rem 1.1rem;
    margin-bottom: 1rem;
    box-shadow: 0 6px 18px rgba(3, 105, 161, 0.22);
}
.r17-shell .r17-solde .label {
    display: block;
    font-size: 0.8rem;
    opacity: 0.9;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}
.r17-shell .r17-solde .amount {
    display: block;
    font-size: 1.7rem;
    font-weight: 700;
    line-height: 1.2;
    margin-top: 0.15rem;
}
.r17-shell .r17-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--r17-gap);
}
.r17-shell .r17-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
    min-height: 56px;
    padding: 0.85rem 1rem;
    margin: 0;
    border: 1px solid #e5e7eb;
    border-radius: var(--r17-radius);
    background: var(--r17-card);
    color: var(--r17-text);
    text-align: left;
    text-decoration: none !important;
    font-weight: 650;
    font-size: 0.98rem;
    line-height: 1.2;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
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
    flex: 0 0 2.25rem;
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #eff6ff;
    color: var(--r17-primary);
    font-size: 1.05rem;
}
.r17-shell .r17-btn.is-primary {
    background: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
    box-shadow: 0 8px 18px rgba(13, 110, 253, 0.28);
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
}
.r17-shell .r17-btn .r17-sub {
    display: block;
    font-size: 0.78rem;
    font-weight: 500;
    opacity: 0.8;
    margin-top: 0.15rem;
}

/* Modale vente — plein écran téléphone, carte desktop */
#ticketescal-0.modal-container {
    z-index: 1050;
}
#ticketescal-0 .modal-content {
    width: 100%;
    max-width: 520px;
    margin: 0 auto;
    border-radius: 14px;
    overflow: hidden;
}
#ticketescal-0 .modal-header {
    padding: 0.85rem 1rem;
}
#ticketescal-0 .modal-title {
    font-size: 1.05rem;
    font-weight: 700;
}
#ticketescal-0 .modal-body {
    padding: 1rem;
    max-height: calc(100vh - 120px);
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
}
#ticketescal-0 .form-group {
    margin-bottom: 0.9rem;
}
#ticketescal-0 label {
    font-weight: 600;
    font-size: 0.88rem;
    margin-bottom: 0.35rem;
}
#ticketescal-0 .form-control,
#ticketescal-0 select.form-control,
#ticketescal-0 input.form-control {
    min-height: var(--r17-touch);
    font-size: 16px; /* évite zoom iOS/Android */
    padding: 0.65rem 0.75rem;
    border-radius: 10px;
}
#ticketescal-0 .modal-footer {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    justify-content: stretch;
    padding-top: 0.5rem !important;
}
#ticketescal-0 .modal-footer .btn,
#ticketescal-0 .modal-footer input.btn {
    flex: 1 1 calc(50% - 0.25rem);
    min-height: var(--r17-touch);
    margin: 0;
    font-weight: 700;
    border-radius: 10px;
}
#ticketescal-0 #prix_escale_hint {
    display: block;
    margin-top: 0.35rem;
    font-size: 0.9rem;
    font-weight: 600;
    color: #0369a1;
}

@media (min-width: 576px) {
    .r17-shell {
        padding: 1rem 1.25rem;
    }
    .r17-shell .r17-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .r17-shell .r17-btn.is-primary {
        grid-column: 1 / -1;
    }
    .r17-shell .r17-solde .amount {
        font-size: 2rem;
    }
}

@media (min-width: 992px) {
    .r17-shell .r17-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .r17-shell .r17-btn.is-primary {
        grid-column: auto;
        min-height: 72px;
    }
}

@media (max-width: 575.98px) {
    .r17-shell {
        padding: 0.5rem;
    }
    /* Modale quasi plein écran sur téléphone */
    #ticketescal-0.modal-container .modal-content {
        max-width: 100%;
        width: 100%;
        min-height: 100%;
        border-radius: 0;
        margin: 0;
    }
    #ticketescal-0 .modal-body {
        max-height: none;
        padding: 1rem 0.85rem 1.25rem;
    }
    #ticketescal-0 .modal-footer .btn,
    #ticketescal-0 .modal-footer input.btn {
        flex: 1 1 100%;
    }
    /* Réduire chrome Beagle autour de l'accueil agent */
    body.be-animate .be-content .main-content.container-fluid {
        padding-left: 0.35rem !important;
        padding-right: 0.35rem !important;
        padding-top: 0.5rem !important;
    }
}
</style>
