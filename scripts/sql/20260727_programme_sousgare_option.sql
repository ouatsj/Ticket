-- Option départs par sous-gare (essaiticket)
-- idsousgare_prog NULL = départ commun gare (comportement historique)

ALTER TABLE programme
  ADD COLUMN idsousgare_prog INT NULL DEFAULT NULL
    COMMENT 'NULL=depart commun gare; sinon reserve a cette sous-gare'
    AFTER gareidentif;

ALTER TABLE programme
  ADD KEY idx_prog_sousgare (idsousgare_prog),
  ADD KEY idx_prog_resol (gareidentif, id_heur, date_progr, idsousgare_prog);

CREATE TABLE IF NOT EXISTS param_gare_depart (
  code_gaexp VARCHAR(50) NOT NULL,
  mode_depart ENUM('gare','sousgare') NOT NULL DEFAULT 'gare',
  updated_at INT NOT NULL DEFAULT 0,
  PRIMARY KEY (code_gaexp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
