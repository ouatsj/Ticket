-- Délai désactivation auto : 7 jours (après réactivation = fenêtre pleine via derniere_activite_at).
UPDATE param_compte_restriction
SET valeur = '7', updated_at = NOW()
WHERE cle = 'compte_desactivation_jours';

INSERT INTO param_compte_restriction (cle, valeur, updated_at)
SELECT 'compte_desactivation_jours', '7', NOW()
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM param_compte_restriction WHERE cle = 'compte_desactivation_jours'
);
