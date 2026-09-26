-- Lettres des états, par compagnie et par mois.
-- Le site crée aussi ces tables au premier usage (etat_lettre_ensure).

CREATE TABLE IF NOT EXISTS etat_lettre_regle (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    entreprise_ekey INT NOT NULL DEFAULT 0,
    cle_compagnie VARCHAR(32) NOT NULL DEFAULT '0',
    famille VARCHAR(32) NOT NULL,
    lettres VARCHAR(32) NOT NULL DEFAULT '',
    toutes TINYINT(1) NOT NULL DEFAULT 0,
    applicable_depuis DATE NOT NULL,
    created_at DATETIME NOT NULL,
    created_by INT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_etat_lettre_regle (entreprise_ekey, cle_compagnie, famille, applicable_depuis)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS etat_lettre_declaration (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    entreprise_ekey INT NOT NULL,
    cle_compagnie VARCHAR(32) NOT NULL,
    famille VARCHAR(32) NOT NULL,
    mois CHAR(7) NOT NULL,
    lettres VARCHAR(32) NOT NULL DEFAULT '',
    toutes TINYINT(1) NOT NULL DEFAULT 0,
    declared_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_etat_lettre_declaration (entreprise_ekey, cle_compagnie, famille, mois)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Règle d'origine (entreprise_ekey 0, applicable_depuis 1970-01-01).
-- ticket_etats / ticket_liste / ticket_escal / courrier : A,C,D
-- bagage : A,C
-- compagnie 5002 + ticket_etats : toutes les lettres (pas de filtre)
INSERT IGNORE INTO etat_lettre_regle
    (entreprise_ekey, cle_compagnie, famille, lettres, toutes, applicable_depuis, created_at, created_by)
VALUES
    (0, '0', 'ticket_etats', 'A,C,D', 0, '1970-01-01', NOW(), NULL),
    (0, '0', 'ticket_liste', 'A,C,D', 0, '1970-01-01', NOW(), NULL),
    (0, '0', 'ticket_escal', 'A,C,D', 0, '1970-01-01', NOW(), NULL),
    (0, '0', 'bagage', 'A,C', 0, '1970-01-01', NOW(), NULL),
    (0, '0', 'courrier', 'A,C,D', 0, '1970-01-01', NOW(), NULL),
    (0, '5002', 'ticket_etats', '', 1, '1970-01-01', NOW(), NULL);
