document.addEventListener('DOMContentLoaded', () => {
    // Retirer les options dont le déclencheur n’existe pas sur la page.
    ['choix-recette-type', 'choix-versement-type', 'choix-modif-versement-type'].forEach((selectId) => {
        const select = document.getElementById(selectId);
        if (!select) {
            return;
        }
        Array.from(select.options).forEach((opt) => {
            if (opt.value && !document.getElementById(opt.value)) {
                opt.remove();
            }
        });
    });

    function closeChoixModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) {
            return;
        }
        const closer = modal.querySelector('.modal-close');
        if (closer) {
            closer.click();
        } else {
            modal.classList.remove('modal-show');
        }
    }

    function openTrigger(triggerId) {
        const trig = document.getElementById(triggerId);
        if (!trig) {
            return;
        }
        window.setTimeout(() => {
            trig.click();
        }, 180);
    }

    document.querySelectorAll('[data-choix-go]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const selectId = btn.getAttribute('data-choix-go');
            const modalId = btn.getAttribute('data-choix-modal');
            const select = document.getElementById(selectId);
            if (!select || !select.value) {
                return;
            }
            closeChoixModal(modalId);
            openTrigger(select.value);
        });
    });
});
