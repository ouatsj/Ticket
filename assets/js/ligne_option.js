(function (win) {
    /**
     * Parse une valeur d'option ligne.
     * Format attendu : ident_ligne/code_gadest/nom_ligne
     * (ident_ligne peut contenir des tirets, ex. BOB1-BAM6).
     */
    function parseLigneOption(raw) {
        var parts = String(raw || '').split('/');

        if (parts.length >= 3) {
            return {
                ident: parts[0] || '',
                gareDest: parts[1] || '',
                nom: parts.slice(2).join('/') || ''
            };
        }

        if (parts.length === 2) {
            var head = parts[0] || '';
            var dash = head.lastIndexOf('-');
            if (dash > 0) {
                return {
                    ident: head.substring(0, dash),
                    gareDest: head.substring(dash + 1),
                    nom: parts[1] || ''
                };
            }

            return {
                ident: head,
                gareDest: '',
                nom: parts[1] || ''
            };
        }

        return {
            ident: parts[0] || '',
            gareDest: '',
            nom: ''
        };
    }

    win.parseLigneOption = parseLigneOption;
})(window);
