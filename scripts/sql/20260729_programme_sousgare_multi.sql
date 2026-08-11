CREATE TABLE IF NOT EXISTS programme_sousgare (
  code_progr VARCHAR(80) NOT NULL,
  idsousgare INT NOT NULL,
  PRIMARY KEY (code_progr, idsousgare),
  KEY idx_psg_sg (idsousgare)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3
COMMENT='Sous-gares autorisees sur un depart; aucune ligne = legacy idsousgare_prog';
