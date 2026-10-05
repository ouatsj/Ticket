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
    --r17-touch: 72px;
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
    min-height: 56px;
    padding: 0.7rem 1.1rem;
    font-size: 1.05rem;
    font-weight: 700;
}
.r17-shell .r17-gare {
    color: var(--r17-muted);
    font-size: 0.78rem;
    margin: 0;
    line-height: 1.25;
}
.r17-shell .r17-context {
    text-align: right;
    min-width: 0;
    flex: 1 1 auto;
}
.r17-shell .r17-escale {
    color: var(--r17-text);
    font-size: 0.82rem;
    margin: 0.1rem 0 0;
    line-height: 1.25;
}
.r17-shell .r17-ctx-k {
    display: inline-block;
    font-size: 0.62rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--r17-muted);
    margin-right: 0.25rem;
}
.r17-shell .r17-badge-fixed,
#ticketescal-0 .r17-badge-fixed {
    display: inline-block;
    margin-left: 0.35rem;
    padding: 0.05rem 0.35rem;
    border-radius: 999px;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    background: #dbeafe;
    color: #1d4ed8;
    vertical-align: middle;
}
.r17-shell .r17-alert {
    margin: 0 0 0.5rem;
    padding: 0.45rem 0.65rem;
    font-size: 0.8rem;
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
    /* Une colonne : la largeur suit l'écran du téléphone. */
    grid-template-columns: 1fr;
    gap: var(--r17-gap);
}
.r17-shell .r17-btn {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    width: 100%;
    min-height: var(--r17-touch);
    padding: 0.85rem 0.9rem;
    margin: 0;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: var(--r17-card);
    color: var(--r17-text);
    text-align: left;
    text-decoration: none !important;
    font-weight: 700;
    font-size: 1.15rem;
    line-height: 1.25;
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
    flex: 0 0 2.75rem;
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #eff6ff;
    color: var(--r17-primary);
    font-size: 1.25rem;
}
.r17-shell .r17-btn.is-primary {
    background: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
    grid-column: 1 / -1;
    min-height: 80px;
    font-size: 1.3rem;
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
.r17-shell .r17-btn .r17-sub {
    display: block;
    font-size: 0.9rem;
    font-weight: 600;
    opacity: 0.85;
    margin-top: 0.15rem;
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
    max-width: 640px;
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
    font-size: 1.25rem !important;
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
    font-weight: 700;
    font-size: 1rem;
    margin-bottom: 0.3rem;
    display: block;
}
#ticketescal-0 .form-control,
#ticketescal-0 select.form-control,
#ticketescal-0 input.form-control {
    min-height: 56px !important;
    height: 56px !important;
    font-size: 1.125rem;
    padding: 0.55rem 0.75rem !important;
    border-radius: 8px;
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
#ticketescal-0 .r17-depart-chip.is-fixed {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1e3a8a;
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
    min-height: 64px !important;
    height: 64px !important;
    margin: 0;
    padding: 0.7rem 0.85rem !important;
    font-size: 1.15rem;
    font-weight: 700;
    border-radius: 10px;
    letter-spacing: 0.02em;
}
#ticketescal-0 #prix_escale_hint {
    display: block;
    margin-top: 0.25rem;
    font-size: 1rem;
    font-weight: 700;
    color: #0369a1;
    min-height: 1.2rem;
}

@media (min-width: 768px) {
    .r17-shell {
        --r17-gap: 0.75rem;
        padding: 0.85rem 1.1rem;
    }
    .r17-shell .r17-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .r17-shell .r17-solde .amount {
        font-size: 1.7rem;
    }
}

@media (min-width: 992px) {
    .r17-shell .r17-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .r17-shell .r17-btn.is-primary {
        grid-column: auto;
        min-height: 80px;
    }
}

@media (max-width: 575.98px) {
    .r17-shell {
        --r17-gap: 0.7rem;
        --r17-touch: 84px;
    }
    .r17-shell .r17-grid {
        grid-template-columns: 1fr;
    }
    .r17-shell .r17-btn {
        min-height: 84px;
        padding: 0.85rem 0.9rem;
        font-size: 1.15rem;
        border-radius: 12px;
        gap: 0.7rem;
    }
    .r17-shell .r17-btn.is-primary {
        min-height: 92px;
        font-size: 1.25rem;
    }
    .r17-shell .r17-btn .r17-ico {
        flex-basis: 2.75rem;
        width: 2.75rem;
        height: 2.75rem;
        font-size: 1.25rem;
        border-radius: 10px;
    }
    .r17-shell .r17-btn .r17-sub {
        display: block;
        font-size: 0.82rem;
        font-weight: 600;
        opacity: 0.85;
        margin-top: 0.15rem;
    }
    .r17-shell .r17-header .btn {
        min-height: 52px;
        padding: 0.65rem 1rem;
        font-size: 1.05rem;
    }
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
        min-height: 56px !important;
        height: 56px !important;
        font-size: 1.125rem;
    }
    #ticketescal-0 .modal-footer .btn,
    #ticketescal-0 .modal-footer input.btn {
        flex: 1 1 50%;
        min-height: 64px !important;
        height: 64px !important;
        font-size: 1.15rem;
    }
}

/* Téléphone : chaque page rôle 17 suit la largeur réelle de l'écran. */
.r17-shell,
.r17-ops {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    overflow-x: hidden;
}
.r17-shell *,
.r17-ops * {
    box-sizing: border-box;
}
.r17-ops .row {
    margin-left: 0;
    margin-right: 0;
    max-width: 100%;
}
.r17-ops .card,
.r17-ops .card-table,
.r17-ops .card-body,
.r17-ops .dataTables_wrapper {
    max-width: 100%;
}
.r17-ops .card-body,
.r17-ops .dataTables_wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.r17-ops img,
.r17-ops video,
.r17-ops canvas,
.r17-ops svg {
    max-width: 100%;
    height: auto;
}
.r17-shell .r17-solde .amount,
.r17-ops .r17-solde .amount {
    overflow-wrap: anywhere;
}

@media (max-width: 767.98px) {
    .r17-shell .r17-header {
        flex-direction: column;
        align-items: stretch;
    }
    .r17-shell .r17-context,
    .r17-shell .r17-escale,
    .r17-shell .r17-gare {
        text-align: left;
        max-width: 100%;
        overflow-wrap: anywhere;
    }
    .r17-shell .r17-grid,
    .r17-ops .r17-grid {
        grid-template-columns: 1fr !important;
    }
    .r17-shell .r17-btn,
    .r17-ops .r17-btn {
        width: 100%;
        max-width: 100%;
    }
    .r17-ops-banner {
        flex-direction: column;
        align-items: stretch;
        width: 100%;
    }
    .r17-ops-banner > a,
    .r17-ops-banner .btn {
        width: 100%;
        text-align: center;
    }
    .r17-tabs {
        flex-direction: column;
    }
    .r17-tabs a {
        width: 100%;
        flex: 1 1 auto;
    }
    .r17-ops .row > [class*="col-"],
    .r17-ops .form-group[class*="col-"],
    #bagage-facturation-r17 .row > [class*="col-"],
    #courrier-envoi-r17 .row > [class*="col-"],
    #ticketescal-0 .row > [class*="col-"] {
        flex: 0 0 100% !important;
        max-width: 100% !important;
        width: 100% !important;
        padding-left: 0;
        padding-right: 0;
    }
    #ticketescal-0 .r17-name-row {
        grid-template-columns: 1fr;
    }
    .r17-ops p > .btn,
    .r17-ops p > a.btn,
    .r17-ops .modal-footer .btn,
    .r17-ops .modal-footer input.btn,
    .r17-ops .r17-wiz-nav .btn,
    .r17-ops .r17-wiz-nav input.btn,
    #courrier-envoi-r17 .r17-wiz-nav .btn,
    #courrier-envoi-r17 .r17-wiz-nav input.btn,
    #bagage-facturation-r17 .btn,
    #bagage-facturation-r17 input.btn,
    #ticketescal-0 .modal-footer .btn,
    #ticketescal-0 .modal-footer input.btn {
        width: 100%;
        max-width: 100%;
        white-space: normal;
        height: auto !important;
    }
    .r17-ops .r17-wiz-nav,
    #courrier-envoi-r17 .r17-wiz-nav,
    #ticketescal-0 .modal-footer,
    #bagage-facturation-r17 .modal-footer {
        flex-wrap: wrap;
    }
    .modal-content,
    .custom-width .modal-content,
    .custom-width .modal-dialog,
    #ticketescal-0 .modal-content,
    #bagage-facturation-r17 .modal-content,
    #bagage-facturation-r17.modal-container,
    #courrier-envoi-r17 .modal-content,
    #courrier-envoi-r17.modal-container {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        box-sizing: border-box;
    }
    .modal-container.modal-show,
    #ticketescal-0.modal-container,
    #bagage-facturation-r17.modal-container,
    #courrier-envoi-r17.modal-container {
        padding: 0;
        overflow-x: hidden;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }
    .modal-content,
    #ticketescal-0 .modal-content,
    #bagage-facturation-r17 .r17-vente-modal,
    #courrier-envoi-r17 .r17-courrier-modal {
        max-height: 100dvh;
        overflow-y: auto;
        border-radius: 0;
    }
    .r17-ops table {
        font-size: 0.95rem;
    }
    .r17-ops td,
    .r17-ops th {
        white-space: normal;
        overflow-wrap: anywhere;
    }
}
</style>
