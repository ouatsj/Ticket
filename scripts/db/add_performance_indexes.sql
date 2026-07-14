-- Index performance — ticket.rakietabus.com
-- Exécuter une fois : mysql -u USER -p DATABASE < scripts/db/add_performance_indexes.sql

-- verifinfos : recherche client par téléphone
ALTER TABLE `client` ADD INDEX `idx_client_contact` (`contact_client`);

-- compteurs passager guichet / caisse
ALTER TABLE `passager` ADD INDEX `idx_passager_date_cpt` (`datep_create`, `idcptuser`);
ALTER TABLE `passager` ADD INDEX `idx_passager_pro_siege` (`code_pro`, `num_siege_categorie`);

-- login
ALTER TABLE `compte_user` ADD INDEX `idx_compte_username` (`username`);
