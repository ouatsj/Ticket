-- Mode départs par sous-gare pour toutes les gares
INSERT INTO param_gare_depart (code_gaexp, mode_depart, updated_at)
SELECT ge.code_gaexp, 'sousgare', UNIX_TIMESTAMP()
FROM gare_exp ge
ON DUPLICATE KEY UPDATE mode_depart = 'sousgare', updated_at = VALUES(updated_at);
