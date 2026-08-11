<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Contenus formation Ticket Rakieta : manuels + QCM par rôle.
 */

if (!function_exists('documentation_formation_roles')) {
    function documentation_formation_roles()
    {
        return array(
            'general' => array(
                'code' => 'general',
                'titre' => 'Vue d\'ensemble',
                'sous_titre' => 'Comprendre simplement les postes et les responsabilités',
            ),
            '1' => array(
                'code' => '1',
                'titre' => 'Administrateur',
                'sous_titre' => 'Organisation, réglages et contrôle général de l\'application',
            ),
            '2' => array(
                'code' => '2',
                'titre' => 'Superviseur',
                'sous_titre' => 'Contrôle des activités et accompagnement des équipes',
            ),
            '3' => array(
                'code' => '3',
                'titre' => 'Agent d\'appel',
                'sous_titre' => 'Information des clients et consultation des programmes',
            ),
            '4' => array(
                'code' => '4',
                'titre' => 'Caissier principal',
                'sous_titre' => 'Contrôle, validation et suivi de la caisse',
            ),
            '5' => array(
                'code' => '5',
                'titre' => 'Chef de guichet',
                'sous_titre' => 'Organisation du guichet, saisie et remise des comptes',
            ),
            '6' => array(
                'code' => '6',
                'titre' => 'Vendeur',
                'sous_titre' => 'Vente des tickets et clôture de sa vacation',
            ),
            '7' => array(
                'code' => '7',
                'titre' => 'Comptable',
                'sous_titre' => 'Contrôle des chiffres et préparation des états',
            ),
            '8' => array(
                'code' => '8',
                'titre' => 'Chef de gare',
                'sous_titre' => 'Organisation et suivi de l\'activité de la gare',
            ),
            '9' => array(
                'code' => '9',
                'titre' => 'Superviseur courrier',
                'sous_titre' => 'Contrôle des opérations et factures courrier',
            ),
            '10' => array(
                'code' => '10',
                'titre' => 'Vendeur mobile',
                'sous_titre' => 'Vente mobile et opérations associées',
            ),
            '11' => array(
                'code' => '11',
                'titre' => 'Ressources humaines',
                'sous_titre' => 'Gestion des informations relatives au personnel',
            ),
            '12' => array(
                'code' => '12',
                'titre' => 'Agent bagage',
                'sous_titre' => 'Facturation, suivi et remise des bagages',
            ),
            '13' => array(
                'code' => '13',
                'titre' => 'Superviseur d\'agence',
                'sous_titre' => 'Suivi financier et opérationnel de l\'agence',
            ),
            '14' => array(
                'code' => '14',
                'titre' => 'Superviseur de site',
                'sous_titre' => 'Vue d\'ensemble et contrôle des activités du site',
            ),
            '15' => array(
                'code' => '15',
                'titre' => 'Aide-programmeur',
                'sous_titre' => 'Préparation et mise à jour des programmes de voyage',
            ),
            '16' => array(
                'code' => '16',
                'titre' => 'Aide chef de guichet',
                'sous_titre' => 'Appui au chef pour la saisie et la préparation des comptes',
            ),
            '17' => array(
                'code' => '17',
                'titre' => 'Vendeur escale',
                'sous_titre' => 'Ventes et services aux voyageurs en escale',
            ),
            '18' => array(
                'code' => '18',
                'titre' => 'Caissier adjoint',
                'sous_titre' => 'Contrôles et validations confiés par le caissier principal',
            ),
            '19' => array(
                'code' => '19',
                'titre' => 'Tableau de bord',
                'sous_titre' => 'Consultation des indicateurs — écran métier à finaliser',
            ),
            '20' => array(
                'code' => '20',
                'titre' => 'Livraison',
                'sous_titre' => 'Suivi des remises — écran métier à finaliser',
            ),
        );
    }
}

if (!function_exists('documentation_formation_role_meta')) {
    function documentation_formation_role_meta($role_code)
    {
        $roles = documentation_formation_roles();
        $role_code = (string) $role_code;

        return isset($roles[$role_code]) ? $roles[$role_code] : null;
    }
}

if (!function_exists('documentation_formation_fiche_poste_simple')) {
    /**
     * Fiches rédigées avec des mots métier, sans noms de colonnes ni codes internes.
     *
     * @return array|null
     */
    function documentation_formation_fiche_poste_simple($role_code)
    {
        $role_code = (string) $role_code;
        $postes = array(
            'general' => array(
                'finalite' => 'Expliquer qui fait quoi afin que chaque opération soit faite par la bonne personne et puisse être contrôlée.',
                'responsable' => 'Direction et responsables désignés',
                'missions' => array(
                    'Utiliser son propre compte et choisir la bonne gare avant de travailler.',
                    'Respecter la séparation entre la personne qui saisit et celle qui contrôle.',
                    'Signaler rapidement toute erreur ou possibilité d\'accès anormale.',
                ),
                'autorise' => array(
                    array('Travailler dans une gare', 'Oui, si cette gare est affectée au compte'),
                    array('Voir ou modifier des informations', 'Uniquement selon le poste occupé'),
                    array('Imprimer un document', 'Oui, si le document est utile au poste'),
                    array('Travailler dans plusieurs gares', 'Oui, seulement avec une affectation pour chacune'),
                ),
                'eventuel' => array(
                    'Une autre gare peut être ajoutée par un responsable.',
                    'Des rapports ou écrans complémentaires peuvent être ouverts selon les besoins du poste.',
                    'Un remplacement temporaire doit être accordé nominativement ; il ne faut jamais partager un mot de passe.',
                ),
                'interdits' => array(
                    'Utiliser le compte d\'un collègue.',
                    'Travailler dans une gare ou sur un écran qui n\'a pas été confié.',
                    'Changer le nom de la personne qui a réellement fait une opération.',
                    'Contourner un refus, un blocage ou une fermeture de compte.',
                ),
                'controles' => array(
                    'Vérifier régulièrement les accès accordés à chaque personne.',
                    'Comparer les opérations, leurs auteurs, leurs contrôleurs et leurs gares.',
                ),
            ),
            '1' => array(
                'finalite' => 'Faire fonctionner l\'application, organiser les accès et garantir la sécurité des données.',
                'responsable' => 'Direction générale',
                'missions' => array(
                    'Créer et organiser les entreprises, gares, compagnies, comptes et postes.',
                    'Accorder uniquement les accès nécessaires à chaque agent.',
                    'Contrôler les anomalies, assister les utilisateurs et protéger les données.',
                ),
                'autorise' => array(
                    array('Créer ou désactiver un compte', 'Oui, avec vérification et motif'),
                    array('Affecter un poste et une gare', 'Oui, selon la décision de la direction'),
                    array('Régler les programmes, tarifs et référentiels', 'Oui, après contrôle'),
                    array('Consulter les rapports et audits', 'Oui, pour les besoins de contrôle'),
                    array('Corriger une donnée sensible', 'Seulement avec preuve, sauvegarde et trace écrite'),
                ),
                'eventuel' => array(
                    'Intervention temporaire dans une gare pour assistance.',
                    'Accès à de nouveaux modules après validation de la direction.',
                    'Délégation de certaines tâches à un superviseur, sans céder les actions les plus sensibles.',
                ),
                'interdits' => array(
                    'Accorder un accès sans besoin professionnel.',
                    'Modifier ou supprimer une donnée financière sans preuve et sans trace.',
                    'Utiliser les droits d\'un administrateur pour effectuer le travail quotidien d\'un autre agent.',
                ),
                'controles' => array(
                    'Revue des comptes, gares et postes actifs.',
                    'Suivi des changements importants et des corrections de données.',
                    'Contrôle des alertes de sécurité et des rapports quotidiens.',
                ),
            ),
            '2' => array(
                'finalite' => 'Suivre les activités, aider les équipes et faire appliquer les procédures.',
                'responsable' => 'Direction / administrateur',
                'missions' => array(
                    'Contrôler les opérations des gares et les rapports disponibles.',
                    'Aider les responsables à comprendre et corriger les écarts.',
                    'Vérifier que les agents utilisent le bon compte et la bonne gare.',
                ),
                'autorise' => array(
                    array('Consulter les activités de plusieurs gares', 'Oui, dans le périmètre confié'),
                    array('Consulter les comptes et leurs affectations', 'Oui, pour le contrôle'),
                    array('Produire des états et rapports', 'Oui'),
                    array('Faire les réglages réservés à l\'administrateur', 'Non'),
                ),
                'eventuel' => array(
                    'Gestion de gares ou d\'agences supplémentaires sur décision de la direction.',
                    'Droit temporaire de traiter une anomalie précise.',
                ),
                'interdits' => array(
                    'Changer les règles générales sans autorisation.',
                    'Effectuer une opération financière à la place de son auteur ou de son contrôleur.',
                    'Partager les informations confidentielles consultées.',
                ),
                'controles' => array(
                    'Suivi des écarts, retards de validation et comptes inactifs.',
                    'Compte rendu régulier à la direction.',
                ),
            ),
            '3' => array(
                'finalite' => 'Donner aux clients des informations fiables sur les départs et les programmes.',
                'responsable' => 'Chef de gare / responsable clientèle',
                'missions' => array(
                    'Consulter les programmes et horaires.',
                    'Informer correctement les clients.',
                    'Imprimer une liste utile à l\'information lorsque cela est prévu.',
                ),
                'autorise' => array(
                    array('Voir les programmes', 'Oui'),
                    array('Imprimer une liste de programme', 'Oui, si nécessaire'),
                    array('Créer ou changer un programme', 'Non'),
                    array('Vendre ou valider une caisse', 'Non'),
                ),
                'eventuel' => array(
                    'Consultation d\'une autre gare après affectation.',
                    'Accès à une liste d\'information supplémentaire sur décision du responsable.',
                ),
                'interdits' => array(
                    'Changer un horaire, un tarif ou un départ.',
                    'Promettre une place ou un départ non confirmé dans l\'application.',
                ),
                'controles' => array(
                    'Vérifier la gare, la date et le programme avant de répondre.',
                    'Signaler toute information incohérente au chef de gare.',
                ),
            ),
            '4' => array(
                'finalite' => 'Contrôler les mouvements d\'argent transmis par les chefs et tenir une caisse juste.',
                'responsable' => 'Responsable financier / superviseur',
                'missions' => array(
                    'Vérifier les montants, motifs, dates et pièces avant décision.',
                    'Accepter ou refuser les recettes, dépenses et dépôts de la même gare.',
                    'Suivre le solde et effectuer la fermeture de caisse.',
                ),
                'autorise' => array(
                    array('Accepter ou refuser une opération du chef', 'Oui, après contrôle'),
                    array('Voir le solde et les états de sa caisse', 'Oui'),
                    array('Saisir sa propre opération de caisse', 'Oui, seulement si la procédure le prévoit'),
                    array('Changer le nom du chef qui a saisi', 'Non'),
                ),
                'eventuel' => array(
                    'Travail dans plusieurs gares si chacune lui est affectée.',
                    'Saisie de certaines recettes ou dépenses propres à la caisse.',
                    'Rapports supplémentaires ou remplacement temporaire accordés par un responsable.',
                ),
                'interdits' => array(
                    'Se déclarer auteur d\'une opération saisie par un chef.',
                    'Accepter une opération sans la contrôler.',
                    'Travailler dans une gare non affectée ou avec le poste du caissier adjoint.',
                ),
                'controles' => array(
                    'Comparer le solde de l\'application avec les pièces et l\'argent disponible.',
                    'Vérifier les opérations encore en attente et expliquer les refus.',
                ),
            ),
            '5' => array(
                'finalite' => 'Organiser le guichet, enregistrer les mouvements et remettre un compte exact au caissier.',
                'responsable' => 'Chef de gare / superviseur',
                'missions' => array(
                    'Saisir les recettes, dépenses et dépôts du guichet.',
                    'Contrôler les ventes et les justificatifs remis par l\'équipe.',
                    'Faire l\'arrêt de compte et traiter les refus avec le caissier.',
                ),
                'autorise' => array(
                    array('Saisir une recette, une dépense ou un dépôt', 'Oui'),
                    array('Voir le solde et les états de son guichet', 'Oui'),
                    array('Faire l\'arrêt de compte', 'Oui'),
                    array('Accepter définitivement sa propre saisie', 'Non, le caissier contrôle'),
                ),
                'eventuel' => array(
                    'Responsabilité de plusieurs gares, utilisées séparément.',
                    'Suivi d\'un aide-chef ou de vendeurs de la même gare.',
                    'Rapports supplémentaires ou autorisation temporaire accordés par un responsable.',
                ),
                'interdits' => array(
                    'Utiliser le compte ou le poste d\'un vendeur, d\'un autre chef ou du caissier.',
                    'Saisir une dépense sans solde ou sans justificatif.',
                    'Saisir deux fois la même opération après l\'arrêt.',
                ),
                'controles' => array(
                    'Comparer le solde affiché avec les mouvements et pièces.',
                    'Vérifier les refus et faire l\'arrêt à la fréquence prévue.',
                ),
            ),
            '6' => array(
                'finalite' => 'Vendre les tickets correctement et remettre un compte exact à la fin de la vacation.',
                'responsable' => 'Chef de guichet / chef de gare',
                'missions' => array(
                    'Choisir le bon voyage, la bonne destination et le bon tarif.',
                    'Enregistrer les informations du voyageur et remettre le ticket.',
                    'Faire son arrêt de vente.',
                ),
                'autorise' => array(
                    array('Vendre et imprimer un ticket', 'Oui'),
                    array('Voir ses propres ventes', 'Oui'),
                    array('Faire son arrêt de vente', 'Oui'),
                    array('Saisir ou accepter une recette de caisse', 'Non'),
                ),
                'eventuel' => array(
                    'Vente dans une autre gare après affectation.',
                    'Réimpression d\'un ticket lorsqu\'un responsable l\'autorise.',
                    'Vente mobile avec un poste complémentaire prévu.',
                ),
                'interdits' => array(
                    'Utiliser le compte d\'un collègue.',
                    'Modifier librement une vente déjà arrêtée.',
                    'Continuer à vendre lorsqu\'un arrêt est obligatoire.',
                ),
                'controles' => array(
                    'Comparer les tickets émis avec l\'arrêt de vente.',
                    'Justifier les annulations et réimpressions.',
                ),
            ),
            '7' => array(
                'finalite' => 'Contrôler les chiffres et préparer des états fiables pour la direction.',
                'responsable' => 'Direction financière',
                'missions' => array(
                    'Consulter les opérations et préparer les états comptables.',
                    'Rapprocher les recettes, dépenses, versements et justificatifs.',
                    'Signaler les écarts aux responsables.',
                ),
                'autorise' => array(
                    array('Voir et imprimer les états comptables', 'Oui'),
                    array('Faire les rapprochements et déclarations', 'Oui'),
                    array('Vendre, saisir ou accepter une opération de caisse', 'Non'),
                    array('Changer une opération déjà enregistrée', 'Non, sauf procédure de correction'),
                ),
                'eventuel' => array(
                    'Consultation de plusieurs gares ou périodes selon le travail confié.',
                    'Export ou rapport complémentaire accordé par la direction.',
                ),
                'interdits' => array(
                    'Modifier les données pour faire disparaître un écart.',
                    'Cumuler contrôle comptable et opération quotidienne sans autorisation.',
                ),
                'controles' => array(
                    'Conserver les preuves des rapprochements.',
                    'Documenter et suivre chaque écart jusqu\'à sa résolution.',
                ),
            ),
            '8' => array(
                'finalite' => 'Organiser les départs, les programmes et le bon fonctionnement de la gare.',
                'responsable' => 'Direction de l\'exploitation',
                'missions' => array(
                    'Préparer et suivre les programmes, horaires et départs.',
                    'Coordonner le personnel et les moyens de la gare.',
                    'Suivre les ventes et états utiles à l\'exploitation.',
                ),
                'autorise' => array(
                    array('Créer ou modifier un programme et ses horaires', 'Oui'),
                    array('Voir les listes, départs, tarifs et états locaux', 'Oui'),
                    array('Organiser les bus et le personnel de gare', 'Oui'),
                    array('Accepter une caisse à la place du caissier', 'Non'),
                ),
                'eventuel' => array(
                    'Gestion d\'une autre gare après affectation.',
                    'Rapports d\'exploitation supplémentaires.',
                ),
                'interdits' => array(
                    'Changer un programme sans vérifier les conséquences sur les ventes.',
                    'Effectuer une opération financière avec le compte d\'un autre poste.',
                ),
                'controles' => array(
                    'Vérifier horaires, bus, destinations et personnel avant publication.',
                    'Informer les équipes de tout changement.',
                ),
            ),
            '9' => array(
                'finalite' => 'Contrôler les opérations et factures liées au courrier.',
                'responsable' => 'Responsable courrier / direction',
                'missions' => array(
                    'Suivre les envois et les factures courrier.',
                    'Comparer les données des gares et signaler les écarts.',
                    'Produire les états demandés.',
                ),
                'autorise' => array(
                    array('Consulter les opérations courrier', 'Oui, selon les écrans disponibles'),
                    array('Établir ou contrôler des factures courrier', 'Oui, dans le périmètre confié'),
                    array('Voir plusieurs gares de départ', 'Oui, si l\'accès est ouvert'),
                    array('Vendre un ticket ou accepter une caisse', 'Non'),
                ),
                'eventuel' => array(
                    'Accès à toutes les gares courrier sur décision de la direction.',
                    'Rapports ou écrans courrier supplémentaires lorsqu\'ils sont raccordés au poste.',
                ),
                'interdits' => array(
                    'Changer une facture ou un envoi sans preuve.',
                    'Utiliser une route non visible comme un droit automatique.',
                ),
                'controles' => array(
                    'Rapprocher envois, factures et paiements.',
                    'Signaler les écrans manquants ou les accès trop larges.',
                ),
            ),
            '10' => array(
                'finalite' => 'Effectuer les ventes mobiles et rendre compte des encaissements associés.',
                'responsable' => 'Chef de gare / responsable commercial',
                'missions' => array(
                    'Vendre depuis le point mobile autorisé.',
                    'Traiter les bagages prévus dans son parcours.',
                    'Faire son arrêt de compte.',
                ),
                'autorise' => array(
                    array('Faire une vente mobile', 'Oui'),
                    array('Traiter un bagage prévu par le poste', 'Oui'),
                    array('Voir son compte et son rapport mobile', 'Oui'),
                    array('Accepter une caisse principale', 'Non'),
                ),
                'eventuel' => array(
                    'Autre point de vente ou gare après affectation.',
                    'Impressions bagage complémentaires selon le besoin.',
                ),
                'interdits' => array(
                    'Utiliser l\'identité d\'un autre vendeur.',
                    'Continuer après l\'arrêt obligatoire.',
                ),
                'controles' => array(
                    'Comparer ventes mobiles, encaissements et arrêt.',
                    'Vérifier la gare et le point de vente actifs.',
                ),
            ),
            '11' => array(
                'finalite' => 'Tenir à jour les informations relatives au personnel.',
                'responsable' => 'Direction / responsable des ressources humaines',
                'missions' => array(
                    'Enregistrer et consulter les informations du personnel.',
                    'Organiser les catégories de personnel.',
                    'Protéger les informations confidentielles.',
                ),
                'autorise' => array(
                    array('Voir et gérer les fiches du personnel', 'Oui'),
                    array('Gérer les catégories de personnel', 'Oui'),
                    array('Utiliser les écrans commerciaux visibles', 'Seulement avec autorisation écrite'),
                    array('Gérer les comptes et postes des utilisateurs', 'Non, sauf autre poste accordé'),
                ),
                'eventuel' => array(
                    'Rapports RH ou consultation d\'une autre agence.',
                    'Un poste commercial distinct peut être accordé si la personne cumule réellement les fonctions.',
                ),
                'interdits' => array(
                    'Divulguer les informations du personnel.',
                    'Utiliser un écran de vente simplement parce qu\'il apparaît dans le menu.',
                ),
                'controles' => array(
                    'Revoir régulièrement les accès commerciaux encore visibles pour ce poste.',
                    'Contrôler l\'exactitude et la confidentialité des fiches.',
                ),
            ),
            '12' => array(
                'finalite' => 'Facturer, identifier et suivre les bagages jusqu\'à leur traitement.',
                'responsable' => 'Chef de gare / responsable bagage',
                'missions' => array(
                    'Enregistrer et facturer les bagages.',
                    'Produire les reçus et bordereaux.',
                    'Suivre les bagages envoyés, reçus ou non facturés.',
                ),
                'autorise' => array(
                    array('Facturer et imprimer un document bagage', 'Oui'),
                    array('Voir l\'historique et les bordereaux bagage', 'Oui'),
                    array('Faire son arrêt de compte bagage', 'Oui'),
                    array('Saisir une recette de caisse au nom du chef', 'Non'),
                ),
                'eventuel' => array(
                    'Traitement d\'une autre gare après affectation.',
                    'Impression ou suivi complémentaire selon le circuit bagage.',
                ),
                'interdits' => array(
                    'Inscrire son poste bagage comme auteur d\'une recette de caisse.',
                    'Remettre un bagage sans contrôle du reçu et de l\'identité.',
                ),
                'controles' => array(
                    'Comparer bagages facturés, bordereaux et arrêt de compte.',
                    'Suivre les bagages non facturés ou non remis.',
                ),
            ),
            '13' => array(
                'finalite' => 'Donner une vue complète de l\'agence et contrôler ses résultats.',
                'responsable' => 'Direction',
                'missions' => array(
                    'Suivre les caisses, ventes, bagages, courriers et versements de l\'agence.',
                    'Produire les états globaux.',
                    'Alerter les responsables en cas d\'écart.',
                ),
                'autorise' => array(
                    array('Voir les états globaux de l\'agence', 'Oui'),
                    array('Voir la caisse principale et les versements', 'Oui, en consultation'),
                    array('Vendre ou saisir une opération quotidienne', 'Non'),
                    array('Modifier une donnée contrôlée', 'Non, sauf procédure autorisée'),
                ),
                'eventuel' => array(
                    'Supervision de plusieurs agences après affectation.',
                    'Rapports complémentaires demandés par la direction.',
                ),
                'interdits' => array(
                    'Modifier une donnée pour masquer un écart.',
                    'Utiliser le poste de supervision comme un poste de vente.',
                ),
                'controles' => array(
                    'Comparer les résultats des différents services.',
                    'Documenter et suivre les anomalies.',
                ),
            ),
            '14' => array(
                'finalite' => 'Suivre les activités de plusieurs services d\'un site et présenter une vue d\'ensemble.',
                'responsable' => 'Direction',
                'missions' => array(
                    'Consulter les états globaux et la caisse principale.',
                    'Suivre les recettes, dépenses, versements, bagages et courriers.',
                    'Coordonner le traitement des anomalies du site.',
                ),
                'autorise' => array(
                    array('Voir les états globaux du site', 'Oui'),
                    array('Voir plusieurs gares courrier', 'Oui, si prévu'),
                    array('Vendre ou accepter une opération', 'Non'),
                    array('Changer les réglages généraux', 'Non'),
                ),
                'eventuel' => array(
                    'Extension à d\'autres sites ou gares.',
                    'Rapports supplémentaires accordés par la direction.',
                ),
                'interdits' => array(
                    'Effectuer une opération quotidienne à la place d\'un agent.',
                    'Modifier les chiffres observés sans procédure.',
                ),
                'controles' => array(
                    'Suivre les écarts et les actions correctives.',
                    'Vérifier que chaque service travaille avec son propre poste.',
                ),
            ),
            '15' => array(
                'finalite' => 'Aider à préparer et mettre à jour les programmes de voyage.',
                'responsable' => 'Chef de gare / responsable de programmation',
                'missions' => array(
                    'Préparer les programmes, horaires et informations de voyage.',
                    'Contrôler les changements avant publication.',
                    'Aider le responsable dans les tâches de programmation confiées.',
                ),
                'autorise' => array(
                    array('Créer ou modifier un programme', 'Oui, dans le périmètre confié'),
                    array('Voir les listes, horaires et tarifs', 'Oui'),
                    array('Utiliser les nombreux écrans commerciaux visibles', 'Seulement avec autorisation écrite'),
                    array('Accepter une caisse', 'Non'),
                ),
                'eventuel' => array(
                    'Programmation d\'une autre gare après affectation.',
                    'Une fonction commerciale distincte peut être ajoutée si elle fait réellement partie du poste.',
                ),
                'interdits' => array(
                    'Utiliser tous les boutons visibles comme s\'ils étaient automatiquement autorisés.',
                    'Changer un programme sans accord du responsable.',
                ),
                'controles' => array(
                    'Faire valider les changements importants.',
                    'Revoir les accès commerciaux trop larges de ce poste.',
                ),
            ),
            '16' => array(
                'finalite' => 'Aider le chef à saisir et préparer les comptes, sous sa responsabilité.',
                'responsable' => 'Chef de guichet',
                'missions' => array(
                    'Saisir les mouvements qui lui sont confiés.',
                    'Vérifier les pièces et préparer l\'arrêt de compte.',
                    'Signaler les erreurs au chef avant transmission.',
                ),
                'autorise' => array(
                    array('Saisir une recette, dépense ou dépôt confié', 'Oui, avec son propre compte'),
                    array('Voir le solde de son périmètre', 'Oui'),
                    array('Préparer ou faire l\'arrêt selon l\'organisation', 'Oui'),
                    array('Accepter une opération à la place du caissier', 'Non'),
                ),
                'eventuel' => array(
                    'Remplacement temporaire dans une autre gare après affectation.',
                    'Rapports utiles à la préparation du compte.',
                ),
                'interdits' => array(
                    'Utiliser le compte du chef.',
                    'Étendre seul son travail à une autre gare.',
                    'Accepter ou refuser à la place du caissier.',
                ),
                'controles' => array(
                    'Faire contrôler le travail par le chef.',
                    'Comparer solde, mouvements et pièces avant l\'arrêt.',
                ),
            ),
            '17' => array(
                'finalite' => 'Servir les voyageurs et traiter les opérations prévues dans une escale.',
                'responsable' => 'Chef de gare / chef de guichet',
                'missions' => array(
                    'Vendre dans l\'escale affectée.',
                    'Traiter les bagages ou courriers d\'escale prévus.',
                    'Réimprimer un ticket uniquement lorsqu\'il est autorisé.',
                ),
                'autorise' => array(
                    array('Vendre dans son escale', 'Oui'),
                    array('Voir ses propres opérations', 'Oui'),
                    array('Réimprimer un ticket', 'Oui, seulement si l\'autorisation existe'),
                    array('Accepter une caisse', 'Non'),
                ),
                'eventuel' => array(
                    'Autre escale après affectation.',
                    'Réimpression exceptionnelle accordée par un responsable.',
                ),
                'interdits' => array(
                    'Réimprimer sans demande ou sans autorisation.',
                    'Forcer une vente sur une mauvaise escale ou un mauvais voyage.',
                    'Utiliser le compte d\'un autre vendeur.',
                ),
                'controles' => array(
                    'Vérifier l\'escale, le voyage et le client.',
                    'Justifier chaque réimpression.',
                ),
            ),
            '18' => array(
                'finalite' => 'Aider le caissier principal à contrôler les opérations qui lui sont confiées.',
                'responsable' => 'Caissier principal / responsable financier',
                'missions' => array(
                    'Contrôler et accepter ou refuser les mouvements confiés.',
                    'Suivre séparément le solde de sa caisse adjointe.',
                    'Rendre compte au caissier principal.',
                ),
                'autorise' => array(
                    array('Accepter ou refuser une opération confiée', 'Oui'),
                    array('Voir le solde de la caisse adjointe', 'Oui'),
                    array('Voir la caisse principale comme si elle était la sienne', 'Non'),
                    array('Changer le nom de l\'auteur d\'une opération', 'Non'),
                ),
                'eventuel' => array(
                    'Autre gare ou remplacement temporaire après affectation.',
                    'Rapports complémentaires accordés par le responsable.',
                ),
                'interdits' => array(
                    'Utiliser le compte ou le poste du caissier principal.',
                    'Continuer à travailler avec une affectation désactivée.',
                    'Accepter une opération d\'une autre gare.',
                ),
                'controles' => array(
                    'Comparer le solde adjoint avec les pièces.',
                    'Suivre les opérations en attente et les refus.',
                ),
            ),
            '19' => array(
                'finalite' => 'Consulter des chiffres résumés pour aider à la décision.',
                'responsable' => 'Direction',
                'missions' => array(
                    'Consulter les indicateurs qui seront confiés au poste.',
                    'Signaler les écarts aux responsables.',
                    'Ne pas modifier les opérations utilisées pour les calculs.',
                ),
                'autorise' => array(
                    array('Ouvrir un tableau de bord dédié', 'Pas encore disponible dans l\'application'),
                    array('Voir les gares affectées', 'Possible à l\'accueil'),
                    array('Vendre, saisir ou accepter une opération', 'Non'),
                    array('Modifier un chiffre source', 'Non'),
                ),
                'eventuel' => array(
                    'Des tableaux de bord précis pourront être ouverts lorsque le module sera raccordé.',
                    'Le périmètre pourra être limité à une agence, une gare ou une activité.',
                ),
                'interdits' => array(
                    'Considérer une page accessible par adresse comme une permission accordée.',
                    'Utiliser ce poste avant la mise en service officielle de son écran.',
                ),
                'controles' => array(
                    'Faire valider la liste des indicateurs et des personnes autorisées avant mise en service.',
                ),
            ),
            '20' => array(
                'finalite' => 'Suivre et confirmer la remise des colis ou courriers lorsque le module de livraison sera disponible.',
                'responsable' => 'Responsable livraison / courrier',
                'missions' => array(
                    'Identifier les éléments à remettre.',
                    'Contrôler le destinataire et conserver une preuve de remise.',
                    'Signaler les éléments non remis ou litigieux.',
                ),
                'autorise' => array(
                    array('Ouvrir un écran de livraison dédié', 'Pas encore disponible dans l\'application'),
                    array('Confirmer une remise', 'Non disponible actuellement'),
                    array('Vendre ou accepter une caisse', 'Non'),
                    array('Modifier un courrier ou un colis', 'Non'),
                ),
                'eventuel' => array(
                    'Scan, preuve de remise et suivi de tournée après développement du module.',
                    'Affectation à une zone ou une agence précise.',
                ),
                'interdits' => array(
                    'Utiliser ce poste comme s\'il était déjà opérationnel.',
                    'Confirmer une remise sans contrôle du destinataire et sans preuve.',
                ),
                'controles' => array(
                    'Définir et tester la procédure de livraison avant ouverture du poste.',
                ),
            ),
        );

        if (!isset($postes[$role_code])) {
            return null;
        }

        $fiche = $postes[$role_code];
        $meta = documentation_formation_role_meta($role_code);
        $fiche['intitule'] = $role_code === 'general'
            ? 'Référentiel simple des postes'
            : 'Fiche de poste — ' . ($meta ? $meta['titre'] : ('Rôle ' . $role_code));

        return $fiche;
    }
}

if (!function_exists('documentation_formation_fiche_poste')) {
    /**
     * Fiche de poste et matrice de permissions des rôles documentés.
     *
     * Les permissions conditionnelles ne sont jamais acquises par défaut :
     * elles dépendent d'une attribution active, de la gare et des modules
     * effectivement ouverts par l'administrateur.
     *
     * @return array|null
     */
    function documentation_formation_fiche_poste($role_code)
    {
        $role_code = (string) $role_code;
        $fiches = array(
            'general' => array(
                'intitule' => 'Référentiel des postes et permissions',
                'finalite' => 'Présenter la séparation des responsabilités dans Ticket Rakieta et rappeler qu\'un rôle n\'autorise que les actions prévues dans sa gare active.',
                'responsable' => 'Administrateur / superviseur fonctionnel',
                'missions' => array(
                    'Garantir la traçabilité : chaque action est réalisée avec le compte et le rôle de son auteur.',
                    'Séparer la saisie, la validation et la supervision.',
                    'Appliquer le principe du moindre privilège : uniquement les droits nécessaires au poste.',
                ),
                'permissions' => array(
                    array('Administrateur / superviseur', 'Paramétrage, contrôle et consultation élargie selon délégation', 'Les actions sensibles restent nominatives et justifiées'),
                    array('Chef / aide chef', 'Saisie des mouvements et arrêt de compte', 'Ne valide pas ses propres lignes à la place du caissier'),
                    array('Caissier principal / adjoint', 'Validation, rejet et suivi de sa piste caisse', 'Ne remplace pas l\'auteur de la saisie'),
                    array('Vendeur / vendeur escale', 'Vente et opérations clients de son périmètre', 'Pas de validation de caisse'),
                ),
                'permissions_eventuelles' => array(
                    'Accès à plusieurs gares, avec une seule attribution active à la fois.',
                    'Consultation ou impression de rapports supplémentaires sur décision administrateur.',
                    'Accès à un module complémentaire uniquement si une attribution correspondante est active.',
                ),
                'interdits' => array(
                    'Partager un mot de passe ou travailler sous le compte d\'un collègue.',
                    'Utiliser un rôle, une gare ou un module non attribué.',
                    'Modifier l\'auteur d\'une opération lors de sa validation.',
                    'Contourner un rejet, un blocage de solde ou une désactivation de compte.',
                ),
                'controles' => array(
                    'Contrôle périodique des comptes, rôles et gares actifs.',
                    'Rapprochement entre auteur, validateur, gare et caisse.',
                    'Revue des anomalies et des permissions exceptionnelles.',
                ),
            ),
            '4' => array(
                'intitule' => 'Fiche de poste — Caissier principal',
                'finalite' => 'Sécuriser les mouvements de caisse en contrôlant et en validant les opérations transmises par les chefs de guichet.',
                'responsable' => 'Responsable financier / superviseur / administrateur',
                'missions' => array(
                    'Contrôler le montant, le motif, la date, la gare et la caisse avant validation.',
                    'Valider ou rejeter les recettes, dépenses et dépôts des chefs de la même gare.',
                    'Suivre son solde, effectuer l\'arrêt de caisse et signaler les écarts.',
                    'Conserver la séparation entre auteur de la saisie et validateur.',
                ),
                'permissions' => array(
                    array('Validation recette', 'Autorisé', 'Renseigne operavalid et les indicateurs de validation ; idopera reste inchangé'),
                    array('Validation dépense', 'Autorisé', 'Renseigne opevalid ; idop_dep reste inchangé'),
                    array('Validation dépôt', 'Autorisé', 'Renseigne opvalid ; idop_depot reste inchangé'),
                    array('Rejet', 'Autorisé', 'Avec contrôle et commentaire suffisamment explicite'),
                    array('Consultation solde / rapports', 'Autorisé', 'Sur sa piste et son périmètre de gare'),
                    array('Saisie d\'un mouvement propre', 'Autorisé si prévu par la procédure', 'Dans ce cas seulement, le caissier peut être l\'auteur de la ligne'),
                ),
                'permissions_eventuelles' => array(
                    'Validation sur plusieurs gares si chaque gare lui est formellement attribuée.',
                    'Saisie de recettes ou dépenses propres à la caisse selon la procédure interne.',
                    'Impression de rapports détaillés et consultation d\'historiques si le module est ouvert.',
                    'Délégation temporaire documentée par un administrateur, sans partage d\'identifiants.',
                ),
                'interdits' => array(
                    'Remplacer idopera, idop_dep ou idop_depot par son propre roleattribut pendant la validation.',
                    'Valider une ligne d\'une autre gare sans attribution correspondante.',
                    'Valider sans pièce, motif ou contrôle du montant.',
                    'Utiliser la piste du caissier adjoint sans rôle 18 actif.',
                ),
                'controles' => array(
                    'File de validation restante et rejets motivés.',
                    'Rapprochement quotidien du solde système avec les justificatifs.',
                    'Alerte immédiate si auteur et validateur deviennent identiques après une validation non saisie par le caissier.',
                ),
            ),
            '18' => array(
                'intitule' => 'Fiche de poste — Caissier adjoint',
                'finalite' => 'Assurer les contrôles et validations délégués sur la piste adjoint, sans se substituer au caissier principal hors délégation.',
                'responsable' => 'Caissier principal / responsable financier',
                'missions' => array(
                    'Valider ou rejeter les mouvements confiés à la piste adjoint.',
                    'Contrôler les justificatifs et suivre le solde adjoint.',
                    'Rendre compte au caissier principal et signaler tout écart.',
                ),
                'permissions' => array(
                    array('Validation recette', 'Autorisé sur piste adjoint', 'operavalidad et is_actifrecetad'),
                    array('Validation dépense', 'Autorisé sur piste adjoint', 'opevalidad et is_actifdepad'),
                    array('Validation dépôt', 'Autorisé sur piste adjoint', 'opvalidad et is_actifdepoad'),
                    array('Rejet', 'Autorisé sur son périmètre', 'Avec motif et traçabilité'),
                    array('Consultation solde', 'Autorisé', 'Solde de la piste adjoint uniquement'),
                ),
                'permissions_eventuelles' => array(
                    'Accès à plusieurs gares si des attributions rôle 18 actives existent.',
                    'Remplacement temporaire encadré, après activation explicite du rôle approprié.',
                    'Consultation de rapports complémentaires selon délégation.',
                ),
                'interdits' => array(
                    'Utiliser les colonnes ou le roleattribut du caissier principal.',
                    'Modifier l\'auteur chef lors de la validation.',
                    'Continuer à opérer avec une attribution adjoint désactivée.',
                    'Valider hors de la gare active.',
                ),
                'controles' => array(
                    'Rapprochement de la piste adjoint.',
                    'Revue des délégations et des anciennes attributions rôle 18.',
                    'Contrôle des opérations laissées en attente.',
                ),
            ),
            '5' => array(
                'intitule' => 'Fiche de poste — Chef de guichet',
                'finalite' => 'Organiser l\'activité du guichet, enregistrer fidèlement les mouvements et transmettre un compte contrôlable au caissier.',
                'responsable' => 'Chef de gare / superviseur / responsable financier',
                'missions' => array(
                    'Saisir les recettes, dépenses et dépôts relevant de son guichet.',
                    'Contrôler les ventes consolidées, les justificatifs et le solde disponible.',
                    'Effectuer l\'arrêt de compte et traiter les rejets avec le caissier.',
                    'Superviser les vendeurs rattachés à son périmètre sans utiliser leurs comptes.',
                ),
                'permissions' => array(
                    array('Saisie recette', 'Autorisé', 'idopera = son roleattribut chef'),
                    array('Saisie dépense', 'Autorisé sous contrôle du solde', 'idop_dep = son roleattribut chef'),
                    array('Saisie dépôt', 'Autorisé selon procédure', 'idop_depot = son roleattribut chef'),
                    array('Arrêt de compte', 'Autorisé', 'Transmet les lignes au caissier'),
                    array('Consultation solde / états', 'Autorisé', 'Sur son guichet, sa gare et sa période'),
                    array('Validation caisse', 'Non autorisé', 'Réservée au caissier 4/18'),
                ),
                'permissions_eventuelles' => array(
                    'Gestion de plusieurs gares si chaque attribution chef est active et sélectionnée séparément.',
                    'Consultation de rapports supplémentaires selon délégation du superviseur.',
                    'Supervision d\'un aide chef ou de vendeurs identifiés sur la même gare.',
                    'Dérogation de vente temporaire uniquement si elle est accordée et motivée par un administrateur.',
                ),
                'interdits' => array(
                    'Valider ses propres lignes à la place du caissier.',
                    'Saisir sous le roleattribut d\'un vendeur ou d\'un autre chef.',
                    'Créer une dépense supérieure au solde ou sans justificatif.',
                    'Dupliquer une ligne après l\'arrêt de compte.',
                ),
                'controles' => array(
                    'Concordance entre solde carte et formulaire.',
                    'Arrêt de compte réalisé selon la fréquence prévue.',
                    'Suivi des rejets, doublons et opérations sans justificatif.',
                ),
            ),
            '16' => array(
                'intitule' => 'Fiche de poste — Aide chef de guichet',
                'finalite' => 'Assister le chef dans la saisie et la préparation des arrêts de compte, dans les limites de la délégation reçue.',
                'responsable' => 'Chef de guichet',
                'missions' => array(
                    'Saisir les mouvements confiés avec son propre roleattribut.',
                    'Contrôler les pièces et préparer l\'arrêt de compte.',
                    'Signaler au chef toute anomalie avant transmission au caissier.',
                ),
                'permissions' => array(
                    array('Saisie recette / dépense / dépôt', 'Autorisé', 'Même logique d\'auteur que le chef, avec rôle 16'),
                    array('Consultation solde', 'Autorisé', 'Sur son périmètre attribué'),
                    array('Arrêt de compte', 'Autorisé selon organisation', 'Sous responsabilité du chef'),
                    array('Validation caisse', 'Non autorisé', 'Réservée au caissier'),
                ),
                'permissions_eventuelles' => array(
                    'Prise en charge temporaire d\'un périmètre chef avec attribution rôle 16 active.',
                    'Accès à plusieurs gares selon affectations explicites.',
                    'Consultation de rapports nécessaires à la préparation de l\'arrêt.',
                ),
                'interdits' => array(
                    'Utiliser le roleattribut du chef titulaire.',
                    'Valider ou rejeter à la place du caissier.',
                    'Étendre de lui-même sa délégation à une autre gare.',
                    'Modifier une opération après arrêt sans procédure.',
                ),
                'controles' => array(
                    'Validation du travail par le chef responsable.',
                    'Contrôle des soldes et justificatifs avant arrêt.',
                    'Revue régulière de la délégation.',
                ),
            ),
            '6' => array(
                'intitule' => 'Fiche de poste — Vendeur',
                'finalite' => 'Vendre les titres de transport correctement et assurer la traçabilité de sa vacation jusqu\'à l\'arrêt vendeur.',
                'responsable' => 'Chef de guichet / chef de gare',
                'missions' => array(
                    'Vendre, imprimer et remettre les tickets aux clients.',
                    'Contrôler programme, destination, tarif et informations passager.',
                    'Effectuer son arrêt vendeur et remettre les éléments au chef.',
                ),
                'permissions' => array(
                    array('Vente ticket', 'Autorisé', 'Sur ses programmes, sa gare et sa vacation'),
                    array('Consultation de ses ventes', 'Autorisé', 'Périmètre personnel'),
                    array('Impression ticket', 'Autorisé', 'Selon l\'état de la vente'),
                    array('Arrêt vendeur', 'Autorisé / obligatoire', 'Selon les règles de la gare'),
                    array('Saisie recette de caisse', 'Non autorisé', 'La recette consolidée appartient au chef ou au caissier saisisseur'),
                    array('Validation caisse', 'Non autorisé', 'Réservée aux rôles 4/18'),
                ),
                'permissions_eventuelles' => array(
                    'Vente sur une autre gare après affectation et activation explicites.',
                    'Réimpression limitée si le droit est accordé pour le ticket concerné.',
                    'Vente mobile uniquement avec une attribution prévue à cet effet.',
                ),
                'interdits' => array(
                    'Utiliser le compte ou le roleattribut d\'un collègue.',
                    'Inscrire son roleattribut vendeur dans idopera d\'une recette de caisse.',
                    'Modifier librement une vente arrêtée.',
                    'Contourner un blocage d\'arrêt de compte.',
                ),
                'controles' => array(
                    'Concordance entre tickets émis et arrêt vendeur.',
                    'Annulations et réimpressions justifiées.',
                    'Arrêt réalisé à la fin de la vacation.',
                ),
            ),
            '17' => array(
                'intitule' => 'Fiche de poste — Vendeur escale',
                'finalite' => 'Traiter les ventes et services clients d\'escale dans le périmètre de la sous-gare attribuée.',
                'responsable' => 'Chef de gare / chef de guichet',
                'missions' => array(
                    'Traiter les ventes liées à l\'escale et contrôler le programme concerné.',
                    'Effectuer les réimpressions autorisées et conserver leur traçabilité.',
                    'Remonter les incohérences au responsable de gare.',
                ),
                'permissions' => array(
                    array('Vente escale', 'Autorisé', 'Sur la sous-gare et les programmes attribués'),
                    array('Consultation de ses opérations', 'Autorisé', 'Périmètre de son roleattribut'),
                    array('Réimpression', 'Conditionnelle', 'Uniquement si le ticket est éligible et le droit disponible'),
                    array('Validation caisse', 'Non autorisé', 'Réservée aux caissiers'),
                    array('Administration programme', 'Non autorisé', 'Sauf rôle complémentaire explicite'),
                ),
                'permissions_eventuelles' => array(
                    'Réimpression exceptionnelle autorisée par un responsable.',
                    'Accès à une autre escale après affectation formelle.',
                    'Consultation élargie uniquement avec une délégation de supervision distincte.',
                ),
                'interdits' => array(
                    'Réimprimer sans demande ou sans droit disponible.',
                    'Utiliser le compte d\'un autre vendeur.',
                    'Forcer une vente sur un programme ou une escale incohérents.',
                    'Valider une recette ou une dépense de caisse.',
                ),
                'controles' => array(
                    'Journal des réimpressions et droits consommés.',
                    'Concordance escale, programme et vendeur.',
                    'Suivi des anomalies remontées au responsable.',
                ),
            ),
        );

        return isset($fiches[$role_code]) ? $fiches[$role_code] : null;
    }
}

if (!function_exists('documentation_formation_manuel')) {
    /**
     * @return array{titre:string,sections:array<int,array{h:string,paras:array<int,string>,bullets?:array<int,string>,table?:array}>}|null
     */
    function documentation_formation_manuel($role_code)
    {
        $role_code = (string) $role_code;
        $manuels = array(
            'general' => array(
                'titre' => 'Documentation générale — Ticket Rakieta',
                'sections' => array(
                    array(
                        'h' => '1. Objet du système',
                        'paras' => array(
                            'Ticket Rakieta gère la vente de billets, les recettes et dépenses de gare, la validation caissier et les arrêts de compte.',
                            'Chaque agent travaille avec un compte utilisateur, une ou plusieurs gares, et un rôle métier (attribution).',
                        ),
                    ),
                    array(
                        'h' => '2. Identifiants importants',
                        'paras' => array(
                            'Ne pas confondre le login (username) avec le roleattribut.',
                        ),
                        'bullets' => array(
                            'cpuser_id — compte utilisateur',
                            'roleattribut — identité opérationnelle dans une gare + un rôle (utilisé partout en caisse)',
                            'guser / code gare — gare d\'affectation (ex. BAN3, OUA1, BOB1)',
                            'activeattrib = 1 — une seule gare active à la fois pour le rôle connecté',
                            'activer_role = 0 — attribution utilisable ; 1 = désactivée (ne doit plus servir)',
                            'activer = 0 sur le compte — compte utilisable ; 1 = compte désactivé',
                        ),
                    ),
                    array(
                        'h' => '3. Chaîne caisse (résumé)',
                        'paras' => array(
                            'Le chef (ou aide) saisit recettes/dépenses. Après arrêt de compte, le caissier valide ou rejette.',
                        ),
                        'table' => array(
                            'headers' => array('Étape', 'Qui', 'Effet'),
                            'rows' => array(
                                array('Saisie', 'Chef 5/16', 'idopera / idop_dep = roleattribut du saisisseur'),
                                array('Arrêt compte', 'Chef', 'Lignes en file VALIDATION'),
                                array('Validation', 'Caissier 4 ou 18', 'operavalid* / opevalid* + flags is_actif*'),
                                array('Solde caissier', 'Caissier', 'Uniquement les lignes validées sur sa piste'),
                            ),
                        ),
                    ),
                    array(
                        'h' => '4. Pistes caissier 4 vs 18',
                        'paras' => array(
                            'Le caissier principal (4) et l\'adjoint (18) n\'utilisent pas les mêmes colonnes.',
                        ),
                        'bullets' => array(
                            'Rôle 4 : operavalid, opevalid, opvalid + is_actifrecet / is_actifdep / is_actifdepo',
                            'Rôle 18 : operavalidad, opevalidad, opvalidad + is_actif*ad',
                            'Un compte désactivé (activer_role=1) ne doit plus être utilisé pour valider',
                        ),
                    ),
                    array(
                        'h' => '5. Bonnes pratiques',
                        'bullets' => array(
                            'Toujours choisir la bonne gare au login (activeattrib)',
                            'Ne pas partager les identifiants',
                            'Faire l\'arrêt de compte avant de quitter le poste',
                            'En cas d\'erreur de montant : faire rejeter puis ressaisir, ne pas « inventer » une ligne',
                            'Contacter le superviseur / admin si message solde incohérent',
                        ),
                    ),
                ),
            ),
            '4' => array(
                'titre' => 'Manuel — Caissier principal (rôle 4)',
                'sections' => array(
                    array(
                        'h' => '1. Mission',
                        'paras' => array(
                            'Valider les arrêts des chefs de la même gare, suivre le solde de la caisse principale, saisir éventuellement des mouvements propres (ex. courrier).',
                        ),
                    ),
                    array(
                        'h' => '2. Connexion et gare',
                        'bullets' => array(
                            'Se connecter avec son username caissier',
                            'Choisir la gare concernée (une seule activeattrib=1)',
                            'Ouvrir VOIR CAISSE puis le module VALIDATION / recettes / dépenses',
                        ),
                    ),
                    array(
                        'h' => '3. Validation des lignes chef',
                        'paras' => array(
                            'Depuis la file VALIDATION : sélectionner le chef, puis VALIDER ou REJETER chaque recette / dépense / dépôt.',
                        ),
                        'bullets' => array(
                            'VALIDER → is_actifrecet (ou dep) = 1 et operavalid (opevalid) = votre roleattribut',
                            'REJETER → ligne hors solde, flags rejet',
                            'Ne jamais modifier idopera / idop_dep (auteur) : c\'est le chef qui a saisi',
                            'Validation masse possible via les boutons d\'arrêt compte caissier',
                        ),
                    ),
                    array(
                        'h' => '4. Solde caisse',
                        'paras' => array(
                            'Le solde affiché repose sur : dépôts + recettes validés − versements − dépenses validés, filtrés sur votre roleattribut (piste 4).',
                            'Une dépense ne peut pas dépasser le solde de votre caisse.',
                        ),
                    ),
                    array(
                        'h' => '5. Arrêt / fermeture de caisse',
                        'bullets' => array(
                            'Après validation journalière, procéder à l\'arrêt de caisse selon la procédure interne',
                            'Vérifier qu\'il ne reste plus de file pending importante sans motif',
                        ),
                    ),
                    array(
                        'h' => '6. Erreurs fréquentes',
                        'bullets' => array(
                            'Mauvaise gare active → rien n\'apparaît ou mauvais soldes',
                            'Confondre saisie chef et validation caissier',
                            'Valider sans contrôler le montant / le motif',
                        ),
                    ),
                ),
            ),
            '18' => array(
                'titre' => 'Manuel — Caissier adjoint (rôle 18)',
                'sections' => array(
                    array(
                        'h' => '1. Mission',
                        'paras' => array(
                            'Même mission que le caissier principal, mais sur la piste adjoint : operavalidad / opevalidad / flags is_actif*ad.',
                        ),
                    ),
                    array(
                        'h' => '2. Différence avec le rôle 4',
                        'bullets' => array(
                            'Les soldes et listes utilisent les colonnes *ad',
                            'Ne pas utiliser un roleattribut de rôle 4 si votre attribution active est 18',
                            'Si le compte adjoint est désactivé (activer_role=1), se connecter uniquement avec le rôle actif autorisé',
                        ),
                    ),
                    array(
                        'h' => '3. Parcours type',
                        'bullets' => array(
                            'Login → choix gare → VOIR CAISSE → VALIDATION',
                            'Valider les chefs de la gare',
                            'Contrôler le solde adjoint avant toute dépense / versement',
                        ),
                    ),
                    array(
                        'h' => '4. Points de vigilance',
                        'paras' => array(
                            'Historique validé en adjoint reste lié aux anciens roleattribut 18 même après un changement de rôle (ex. passage en 4).',
                        ),
                    ),
                ),
            ),
            '5' => array(
                'titre' => 'Manuel — Chef de guichet (rôle 5)',
                'sections' => array(
                    array(
                        'h' => '1. Mission',
                        'paras' => array(
                            'Saisir les recettes et dépenses de la période ouverte, suivre son solde guichet, faire l\'arrêt de compte pour envoi au caissier.',
                        ),
                    ),
                    array(
                        'h' => '2. Solde période ouverte',
                        'paras' => array(
                            'Le solde affiché sur la carte caisse = (dépôts + recettes ouverts) − (versements + dépenses ouverts).',
                            'Flags typiques : active_recet = 0 (pas encore clos / en période ouverte), idopera = votre roleattribut.',
                        ),
                        'bullets' => array(
                            'Le solde du formulaire de dépense doit correspondre à la carte',
                            'Si message « dépasse le solde » alors que la carte montre de l\'argent : alerter le support (ne pas forcer)',
                        ),
                    ),
                    array(
                        'h' => '3. Saisie recette / dépense',
                        'bullets' => array(
                            'Renseigner compagnie, type, genre, montant, date, commentaire',
                            'Contrôler le solde avant toute dépense',
                            'Vous n\'êtes pas validateur : le caissier valide après votre arrêt',
                        ),
                    ),
                    array(
                        'h' => '4. Arrêt de compte',
                        'paras' => array(
                            'L\'arrêt envoie (ou prépare) les lignes pour la file VALIDATION du caissier de la même gare.',
                        ),
                        'bullets' => array(
                            'Faire l\'arrêt en fin de vacation / selon consignes',
                            'Après arrêt, ne pas ressaisir les mêmes mouvements',
                            'Suivre avec le caissier en cas de rejet',
                        ),
                    ),
                    array(
                        'h' => '5. Identifiants sur une ligne',
                        'table' => array(
                            'headers' => array('Colonne', 'Signification'),
                            'rows' => array(
                                array('idopera / idop_dep', 'Vous (auteur) — ne change pas à la validation'),
                                array('operavalid / opevalid', 'Caissier qui a validé'),
                                array('is_actif*', '1 = accepté en caisse caissier'),
                            ),
                        ),
                    ),
                ),
            ),
            '16' => array(
                'titre' => 'Manuel — Aide chef de guichet (rôle 16)',
                'sections' => array(
                    array(
                        'h' => '1. Mission',
                        'paras' => array(
                            'Même logique métier que le chef de guichet (saisie idopera / idop_dep, période ouverte, arrêt).',
                            'Travailler sous la responsabilité du chef / consignes de gare.',
                        ),
                    ),
                    array(
                        'h' => '2. Règles',
                        'bullets' => array(
                            'Respecter le même contrôle de solde avant dépense',
                            'Ne pas valider à la place du caissier',
                            'Signaler toute anomalie au chef avant l\'arrêt',
                        ),
                    ),
                    array(
                        'h' => '3. Parcours',
                        'bullets' => array(
                            'Login → gare → caisse adjoint/chef → recettes / dépenses',
                            'Arrêt de compte selon planning',
                        ),
                    ),
                ),
            ),
            '6' => array(
                'titre' => 'Manuel — Vendeur (rôle 6)',
                'sections' => array(
                    array(
                        'h' => '1. Mission',
                        'paras' => array(
                            'Vendre les tickets, gérer les passagers du jour, faire l\'arrêt de compte vendeur pour validation / consolidation.',
                        ),
                    ),
                    array(
                        'h' => '2. Vente',
                        'bullets' => array(
                            'Sélectionner programme / destination / tarif corrects',
                            'Vérifier identité et contacts passager',
                            'Imprimer / remettre le ticket',
                            'Ne pas vendre si le compte est bloqué (arrêt non fait / restrictions)',
                        ),
                    ),
                    array(
                        'h' => '3. Arrêt vendeur',
                        'paras' => array(
                            'L\'arrêt clôture les ventes de la période. La suite (recette consolidée) est traitée côté chef / caissier selon le circuit gare.',
                        ),
                    ),
                    array(
                        'h' => '4. Interdits',
                        'bullets' => array(
                            'Ne pas modifier une vente déjà arrêtée sans procédure',
                            'Ne pas utiliser le compte d\'un collègue',
                            'Ne pas ignorer une alerte d\'arrêt obligatoire',
                        ),
                    ),
                ),
            ),
            '17' => array(
                'titre' => 'Manuel — Vendeur escale (rôle 17)',
                'sections' => array(
                    array(
                        'h' => '1. Mission',
                        'paras' => array(
                            'Gérer les ventes et opérations liées aux escales (passagers escale, réimpression selon droits).',
                        ),
                    ),
                    array(
                        'h' => '2. Réimpression',
                        'bullets' => array(
                            'La liste de réimpression est filtrée sur vos opérations (iduseescal)',
                            'Après impression, le droit de réimpression peut être consommé (reimpr)',
                            'En cas de liste vide : pas de ticket éligible pour votre compte',
                        ),
                    ),
                    array(
                        'h' => '3. Bonnes pratiques',
                        'bullets' => array(
                            'Contrôler escale / sous-gare avant validation client',
                            'Escalader au chef de gare si incohérence programme',
                        ),
                    ),
                ),
            ),
        );

        if (isset($manuels[$role_code])) {
            return $manuels[$role_code];
        }

        // Les nouveaux rôles disposent au minimum d'un guide simple construit
        // depuis leur fiche de poste, même si aucun QCM spécialisé n'existe encore.
        $fiche = documentation_formation_fiche_poste_simple($role_code);
        $meta = documentation_formation_role_meta($role_code);
        if (!$fiche || !$meta) {
            return null;
        }

        return array(
            'titre' => 'Guide pratique — ' . $meta['titre'],
            'sections' => array(
                array(
                    'h' => '1. Avant de commencer',
                    'bullets' => array(
                        'Se connecter uniquement avec son propre compte.',
                        'Choisir la gare ou le lieu de travail réellement concerné.',
                        'Vérifier que le poste affiché correspond bien au travail confié.',
                    ),
                ),
                array(
                    'h' => '2. Travail attendu',
                    'bullets' => $fiche['missions'],
                ),
                array(
                    'h' => '3. En cas de doute',
                    'bullets' => array(
                        'Ne pas utiliser un bouton simplement parce qu\'il est visible.',
                        'Ne pas forcer une opération refusée ou bloquée.',
                        'Demander au responsable avant d\'agir et lui indiquer la gare, la date et l\'opération concernées.',
                    ),
                ),
            ),
        );
    }
}

if (!function_exists('documentation_formation_qcm')) {
    /**
     * QCM fin de formation. Chaque question : q, choices[A..D], answer (lettre), tip (explication corrigé).
     *
     * @return array{titre:string,duree:string,bareme:string,questions:array}|null
     */
    function documentation_formation_qcm($role_code)
    {
        $role_code = (string) $role_code;
        $qcms = array(
            '4' => array(
                'titre' => 'QCM fin de formation — Caissier principal',
                'duree' => '20 minutes',
                'bareme' => '1 point par bonne réponse — Total /10 — Seuil indicatif : 7/10',
                'questions' => array(
                    array(
                        'q' => 'Après connexion, que devez-vous faire avant de travailler sur une gare ?',
                        'choices' => array(
                            'A' => 'Rien, toutes les gares sont ouvertes',
                            'B' => 'Choisir / entrer dans la gare concernée',
                            'C' => 'Changer le mot de passe du chef',
                            'D' => 'Supprimer les recettes du jour',
                        ),
                        'answer' => 'B',
                        'tip' => 'On travaille toujours dans une gare précise.',
                    ),
                    array(
                        'q' => 'Qui doit valider l\'arrêt de compte d\'un chef de guichet ?',
                        'choices' => array(
                            'A' => 'Le vendeur',
                            'B' => 'Le caissier de la même gare',
                            'C' => 'Le passager',
                            'D' => 'N\'importe quel utilisateur',
                        ),
                        'answer' => 'B',
                        'tip' => 'Le chef saisit ; le caissier valide.',
                    ),
                    array(
                        'q' => 'Que faire face à une ligne en file VALIDATION douteuse ?',
                        'choices' => array(
                            'A' => 'Toujours valider sans lire',
                            'B' => 'Contrôler le montant et le motif, puis VALIDER ou REJETER',
                            'C' => 'Demander au passager de valider',
                            'D' => 'Effacer la gare',
                        ),
                        'answer' => 'B',
                        'tip' => 'La validation engage le solde caisse.',
                    ),
                    array(
                        'q' => 'Que se passe-t-il si vous REJETEZ une dépense ?',
                        'choices' => array(
                            'A' => 'Elle entre quand même dans votre solde',
                            'B' => 'Elle n\'entre pas dans votre solde caisse',
                            'C' => 'Elle change automatiquement de gare',
                            'D' => 'Elle devient une recette',
                        ),
                        'answer' => 'B',
                        'tip' => 'Rejet = hors solde caissier.',
                    ),
                    array(
                        'q' => 'Votre solde caisse dépend surtout de :',
                        'choices' => array(
                            'A' => 'Toutes les saisies non validées des chefs',
                            'B' => 'Les mouvements que vous avez validés',
                            'C' => 'Le nombre de bus du jour',
                            'D' => 'Le nom de l\'entreprise seul',
                        ),
                        'answer' => 'B',
                        'tip' => 'Seules les lignes validées comptent pour le caissier.',
                    ),
                    array(
                        'q' => 'Avant une dépense sur votre caisse, vous devez :',
                        'choices' => array(
                            'A' => 'Ignorer le solde',
                            'B' => 'Vérifier que le montant ne dépasse pas le solde',
                            'C' => 'Appeler le passager',
                            'D' => 'Changer de username',
                        ),
                        'answer' => 'B',
                        'tip' => 'Sinon message « dépasse le solde ».',
                    ),
                    array(
                        'q' => 'Un compte désactivé :',
                        'choices' => array(
                            'A' => 'Peut encore valider normalement',
                            'B' => 'Ne doit plus être utilisé',
                            'C' => 'Double le solde',
                            'D' => 'Ouvre toutes les gares',
                        ),
                        'answer' => 'B',
                        'tip' => 'Compte / rôle désactivé = inutilisable.',
                    ),
                    array(
                        'q' => 'Si vous avez plusieurs gares, comment travailler correctement ?',
                        'choices' => array(
                            'A' => 'Sans choisir de gare',
                            'B' => 'Entrer dans une gare puis opérer',
                            'C' => 'Utiliser le compte d\'un collègue',
                            'D' => 'Valider toutes les gares d\'un clic sans contrôle',
                        ),
                        'answer' => 'B',
                        'tip' => 'Une gare à la fois.',
                    ),
                    array(
                        'q' => 'Après validation, l\'auteur de la saisie (le chef) :',
                        'choices' => array(
                            'A' => 'Doit devenir le caissier',
                            'B' => 'Reste le chef qui a saisi',
                            'C' => 'Disparaît',
                            'D' => 'Passe au vendeur',
                        ),
                        'answer' => 'B',
                        'tip' => 'Qui a saisi ≠ qui a validé.',
                    ),
                    array(
                        'q' => 'En fin de journée, une bonne pratique est :',
                        'choices' => array(
                            'A' => 'Partir sans regarder la file',
                            'B' => 'Vérifier qu\'il ne reste pas trop de pending sans motif, puis suivre l\'arrêt de caisse',
                            'C' => 'Supprimer les recettes',
                            'D' => 'Donner son mot de passe au chef',
                        ),
                        'answer' => 'B',
                        'tip' => 'Contrôle + clôture selon procédure.',
                    ),
                ),
            ),
            '18' => array(
                'titre' => 'QCM fin de formation — Caissier adjoint',
                'duree' => '20 minutes',
                'bareme' => '1 point par bonne réponse — Total /10 — Seuil indicatif : 7/10',
                'questions' => array(
                    array(
                        'q' => 'Le caissier adjoint sert surtout à :',
                        'choices' => array(
                            'A' => 'Vendre les tickets à la place du vendeur',
                            'B' => 'Valider les arrêts des chefs et suivre le solde (piste adjoint)',
                            'C' => 'Créer les entreprises',
                            'D' => 'Modifier les programmes bus',
                        ),
                        'answer' => 'B',
                        'tip' => 'Même mission que le caissier, piste adjoint.',
                    ),
                    array(
                        'q' => 'Si votre compte adjoint est désactivé :',
                        'choices' => array(
                            'A' => 'Vous validez quand même en adjoint',
                            'B' => 'Vous ne devez plus utiliser ce rôle',
                            'C' => 'Le solde double',
                            'D' => 'Toutes les gares s\'ouvrent',
                        ),
                        'answer' => 'B',
                        'tip' => 'Rôle désactivé = inutilisable.',
                    ),
                    array(
                        'q' => 'Après un changement de rôle (ex. adjoint → principal), l\'ancien travail :',
                        'choices' => array(
                            'A' => 'Disparaît',
                            'B' => 'Reste dans l\'historique de l\'ancien rôle',
                            'C' => 'Est effacé chaque nuit',
                            'D' => 'Passe au passager',
                        ),
                        'answer' => 'B',
                        'tip' => 'L\'historique ne change pas tout seul.',
                    ),
                    array(
                        'q' => 'Qui saisit une dépense de chef avant votre validation ?',
                        'choices' => array(
                            'A' => 'Le caissier adjoint',
                            'B' => 'Le chef de guichet',
                            'C' => 'Le passager',
                            'D' => 'Le bus',
                        ),
                        'answer' => 'B',
                        'tip' => 'Chef saisit ; caissier valide.',
                    ),
                    array(
                        'q' => 'Validation et rejet se font :',
                        'choices' => array(
                            'A' => 'Sans regarder la gare',
                            'B' => 'Sur les chefs de la même gare',
                            'C' => 'Uniquement le dimanche',
                            'D' => 'Par le vendeur',
                        ),
                        'answer' => 'B',
                        'tip' => 'Toujours la même gare.',
                    ),
                    array(
                        'q' => 'Avec plusieurs gares, vous devez :',
                        'choices' => array(
                            'A' => 'Tout mélanger',
                            'B' => 'Travailler gare par gare',
                            'C' => 'Ignorer le choix de gare',
                            'D' => 'Utiliser le compte du principal sans droit',
                        ),
                        'answer' => 'B',
                        'tip' => 'Une gare active à la fois.',
                    ),
                    array(
                        'q' => 'Le message « dépasse le solde » signifie :',
                        'choices' => array(
                            'A' => 'Le ticket est expiré',
                            'B' => 'Le montant est supérieur au solde de la caisse',
                            'C' => 'La gare est fermée définitivement',
                            'D' => 'Le rôle est admin',
                        ),
                        'answer' => 'B',
                        'tip' => 'Réduire le montant ou vérifier le solde.',
                    ),
                    array(
                        'q' => 'Pour revenir à la liste des gares, on utilise en général :',
                        'choices' => array(
                            'A' => 'RETOUR GARE / accueil gares',
                            'B' => 'Supprimer le compte',
                            'C' => 'Créer une entreprise',
                            'D' => 'Imprimer un ticket passager',
                        ),
                        'answer' => 'A',
                        'tip' => 'Navigation standard caissier.',
                    ),
                    array(
                        'q' => 'Avant de valider un montant important :',
                        'choices' => array(
                            'A' => 'Valider sans lire',
                            'B' => 'Contrôler motif, montant et cohérence',
                            'C' => 'Demander au client final',
                            'D' => 'Changer de gare au hasard',
                        ),
                        'answer' => 'B',
                        'tip' => 'Contrôle avant engagement.',
                    ),
                    array(
                        'q' => 'Partager son mot de passe caissier :',
                        'choices' => array(
                            'A' => 'Est recommandé',
                            'B' => 'Est interdit',
                            'C' => 'Est obligatoire le lundi',
                            'D' => 'Remplace la validation',
                        ),
                        'answer' => 'B',
                        'tip' => 'Traçabilité et sécurité.',
                    ),
                ),
            ),
            '5' => array(
                'titre' => 'QCM fin de formation — Chef de guichet',
                'duree' => '20 minutes',
                'bareme' => '1 point par bonne réponse — Total /10 — Seuil indicatif : 7/10',
                'questions' => array(
                    array(
                        'q' => 'En tant que chef de guichet, votre rôle principal est de :',
                        'choices' => array(
                            'A' => 'Valider la caisse à la place du caissier',
                            'B' => 'Saisir recettes / dépenses et faire l\'arrêt de compte',
                            'C' => 'Créer les utilisateurs',
                            'D' => 'Imprimer uniquement les tickets escale',
                        ),
                        'answer' => 'B',
                        'tip' => 'Saisie + arrêt ; validation = caissier.',
                    ),
                    array(
                        'q' => 'Avant une dépense, vous devez :',
                        'choices' => array(
                            'A' => 'Ignorer le solde',
                            'B' => 'Vérifier que le montant ne dépasse pas votre solde',
                            'C' => 'Demander au passager',
                            'D' => 'Changer de gare',
                        ),
                        'answer' => 'B',
                        'tip' => 'Contrôle solde obligatoire.',
                    ),
                    array(
                        'q' => 'L\'arrêt de compte sert à :',
                        'choices' => array(
                            'A' => 'Supprimer les recettes',
                            'B' => 'Envoyer / préparer vos lignes pour le caissier',
                            'C' => 'Créer un username',
                            'D' => 'Fermer l\'entreprise',
                        ),
                        'answer' => 'B',
                        'tip' => 'Handoff vers VALIDATION caissier.',
                    ),
                    array(
                        'q' => 'Qui valide vos recettes après l\'arrêt ?',
                        'choices' => array(
                            'A' => 'Vous-même',
                            'B' => 'Le caissier de la gare',
                            'C' => 'Le vendeur',
                            'D' => 'Personne',
                        ),
                        'answer' => 'B',
                        'tip' => 'Circuit standard.',
                    ),
                    array(
                        'q' => 'Si le caissier REJETTE une ligne :',
                        'choices' => array(
                            'A' => 'Elle compte quand même dans sa caisse',
                            'B' => 'Elle ne compte pas dans le solde caissier',
                            'C' => 'Elle change de gare',
                            'D' => 'Elle devient un ticket',
                        ),
                        'answer' => 'B',
                        'tip' => 'Rejet = hors solde caissier.',
                    ),
                    array(
                        'q' => 'Après validation, vous restez l\'auteur de la saisie :',
                        'choices' => array(
                            'A' => 'Faux — le caissier devient l\'auteur',
                            'B' => 'Vrai — le caissier est seulement le validateur',
                            'C' => 'Faux — l\'auteur disparaît',
                            'D' => 'Vrai seulement le dimanche',
                        ),
                        'answer' => 'B',
                        'tip' => 'Auteur ≠ validateur.',
                    ),
                    array(
                        'q' => 'Un compte désactivé :',
                        'choices' => array(
                            'A' => 'Peut encore saisir',
                            'B' => 'Ne doit plus être utilisé',
                            'C' => 'Augmente le solde',
                            'D' => 'Active toutes les gares',
                        ),
                        'answer' => 'B',
                        'tip' => 'Compte off = pas d\'usage.',
                    ),
                    array(
                        'q' => 'En fin de vacation, la bonne pratique est :',
                        'choices' => array(
                            'A' => 'Partir sans arrêt',
                            'B' => 'Faire l\'arrêt et informer le caissier',
                            'C' => 'Supprimer les dépenses',
                            'D' => 'Donner le mot de passe du caissier',
                        ),
                        'answer' => 'B',
                        'tip' => 'Clôture + handoff.',
                    ),
                    array(
                        'q' => 'Si le formulaire dit « dépasse le solde » alors que la carte montre de l\'argent :',
                        'choices' => array(
                            'A' => 'Forcer plusieurs fois',
                            'B' => 'Alerter le responsable / support',
                            'C' => 'Inventer une recette',
                            'D' => 'Changer de username collègue',
                        ),
                        'answer' => 'B',
                        'tip' => 'Ne pas contourner ; signaler.',
                    ),
                    array(
                        'q' => 'Partager son compte chef :',
                        'choices' => array(
                            'A' => 'Est autorisé',
                            'B' => 'Est interdit',
                            'C' => 'Est obligatoire',
                            'D' => 'Remplace l\'arrêt',
                        ),
                        'answer' => 'B',
                        'tip' => 'Chaque agent a sa traçabilité.',
                    ),
                ),
            ),
            '16' => array(
                'titre' => 'QCM fin de formation — Aide chef de guichet',
                'duree' => '15 minutes',
                'bareme' => '1 point par bonne réponse — Total /8 — Seuil indicatif : 6/8',
                'questions' => array(
                    array(
                        'q' => 'Le rôle d\'aide chef est proche de :',
                        'choices' => array(
                            'A' => 'Caissier',
                            'B' => 'Chef de guichet (saisie)',
                            'C' => 'Administrateur',
                            'D' => 'Vendeur escale seulement',
                        ),
                        'answer' => 'B',
                        'tip' => 'Même logique de saisie.',
                    ),
                    array(
                        'q' => 'Pouvez-vous valider les arrêts des autres chefs ?',
                        'choices' => array(
                            'A' => 'Oui toujours',
                            'B' => 'Non — c\'est le caissier',
                            'C' => 'Oui le week-end',
                            'D' => 'Oui si solde = 0',
                        ),
                        'answer' => 'B',
                        'tip' => 'Pas de validation caissier.',
                    ),
                    array(
                        'q' => 'Avant une dépense :',
                        'choices' => array(
                            'A' => 'Contrôler le solde',
                            'B' => 'Rien',
                            'C' => 'Appeler le passager',
                            'D' => 'Modifier le compte caissier',
                        ),
                        'answer' => 'A',
                        'tip' => 'Même règle que le chef.',
                    ),
                    array(
                        'q' => 'En cas de doute sur un montant :',
                        'choices' => array(
                            'A' => 'Saisir quand même',
                            'B' => 'Demander au chef / responsable avant l\'arrêt',
                            'C' => 'Rejeter à la place du caissier',
                            'D' => 'Effacer le programme',
                        ),
                        'answer' => 'B',
                        'tip' => 'Escalade avant clôture.',
                    ),
                    array(
                        'q' => 'L\'arrêt de compte :',
                        'choices' => array(
                            'A' => 'Est inutile',
                            'B' => 'Suit la même logique que le chef',
                            'C' => 'Crée un caissier',
                            'D' => 'Imprime les tickets',
                        ),
                        'answer' => 'B',
                        'tip' => 'Même filière.',
                    ),
                    array(
                        'q' => 'Qui valide ensuite vos lignes ?',
                        'choices' => array(
                            'A' => 'Vous-même',
                            'B' => 'Le caissier',
                            'C' => 'Le bus',
                            'D' => 'Le quartier',
                        ),
                        'answer' => 'B',
                        'tip' => 'Validateur = caissier.',
                    ),
                    array(
                        'q' => 'Un compte désactivé :',
                        'choices' => array(
                            'A' => 'Doit être utilisé',
                            'B' => 'Ne doit plus être utilisé',
                            'C' => 'Double le solde',
                            'D' => 'Active toutes les gares',
                        ),
                        'answer' => 'B',
                        'tip' => 'Compte off.',
                    ),
                    array(
                        'q' => 'Partager son mot de passe :',
                        'choices' => array(
                            'A' => 'Recommandé',
                            'B' => 'Interdit',
                            'C' => 'Obligatoire',
                            'D' => 'Sans importance',
                        ),
                        'answer' => 'B',
                        'tip' => 'Sécurité.',
                    ),
                ),
            ),
            '6' => array(
                'titre' => 'QCM fin de formation — Vendeur',
                'duree' => '15 minutes',
                'bareme' => '1 point par bonne réponse — Total /8 — Seuil indicatif : 6/8',
                'questions' => array(
                    array(
                        'q' => 'Votre activité principale est :',
                        'choices' => array(
                            'A' => 'Valider la caisse',
                            'B' => 'Vendre les tickets / gérer les passagers',
                            'C' => 'Créer les entreprises',
                            'D' => 'Paramétrer les rôles',
                        ),
                        'answer' => 'B',
                        'tip' => 'Rôle vente.',
                    ),
                    array(
                        'q' => 'L\'arrêt vendeur sert à :',
                        'choices' => array(
                            'A' => 'Clôturer les ventes de la période',
                            'B' => 'Changer le tarif national',
                            'C' => 'Désactiver le caissier',
                            'D' => 'Créer une gare',
                        ),
                        'answer' => 'A',
                        'tip' => 'Clôture vacation vente.',
                    ),
                    array(
                        'q' => 'Si une alerte impose l\'arrêt avant de vendre :',
                        'choices' => array(
                            'A' => 'Continuer sans arrêt',
                            'B' => 'Faire l\'arrêt puis reprendre',
                            'C' => 'Changer de siège seulement',
                            'D' => 'Supprimer les passagers',
                        ),
                        'answer' => 'B',
                        'tip' => 'Respecter la procédure.',
                    ),
                    array(
                        'q' => 'Partager son mot de passe :',
                        'choices' => array(
                            'A' => 'Est recommandé',
                            'B' => 'Est interdit',
                            'C' => 'Est obligatoire le lundi',
                            'D' => 'Remplace l\'arrêt',
                        ),
                        'answer' => 'B',
                        'tip' => 'Sécurité compte.',
                    ),
                    array(
                        'q' => 'Avant d\'émettre un ticket, vérifier :',
                        'choices' => array(
                            'A' => 'Programme, destination, tarif',
                            'B' => 'Le solde du caissier adjoint',
                            'C' => 'Le QCM admin',
                            'D' => 'Rien',
                        ),
                        'answer' => 'A',
                        'tip' => 'Contrôles vente.',
                    ),
                    array(
                        'q' => 'Après arrêt, modifier une vente librement :',
                        'choices' => array(
                            'A' => 'Oui sans contrôle',
                            'B' => 'Non — suivre la procédure / responsable',
                            'C' => 'Oui via Paramètres',
                            'D' => 'Oui en changeant de gare',
                        ),
                        'answer' => 'B',
                        'tip' => 'Période clôturée.',
                    ),
                    array(
                        'q' => 'En cas d\'erreur ticket :',
                        'choices' => array(
                            'A' => 'Ignorer',
                            'B' => 'Suivre la procédure gare (responsable)',
                            'C' => 'Créer un compte caissier',
                            'D' => 'Valider en caisse soi-même',
                        ),
                        'answer' => 'B',
                        'tip' => 'Escalade métier.',
                    ),
                    array(
                        'q' => 'Utiliser le compte d\'un collègue vendeur :',
                        'choices' => array(
                            'A' => 'Autorisé',
                            'B' => 'Interdit',
                            'C' => 'Obligatoire',
                            'D' => 'Recommandé',
                        ),
                        'answer' => 'B',
                        'tip' => 'Traçabilité.',
                    ),
                ),
            ),
            '17' => array(
                'titre' => 'QCM fin de formation — Vendeur escale',
                'duree' => '15 minutes',
                'bareme' => '1 point par bonne réponse — Total /8 — Seuil indicatif : 6/8',
                'questions' => array(
                    array(
                        'q' => 'En réimpression, vous voyez en général :',
                        'choices' => array(
                            'A' => 'Tous les tickets du pays',
                            'B' => 'Surtout vos opérations',
                            'C' => 'Uniquement la caisse',
                            'D' => 'Rien jamais',
                        ),
                        'answer' => 'B',
                        'tip' => 'Scope vendeur escale.',
                    ),
                    array(
                        'q' => 'Si la liste de réimpression est vide :',
                        'choices' => array(
                            'A' => 'Bug obligatoire',
                            'B' => 'Aucun ticket éligible pour votre compte',
                            'C' => 'Solde insuffisant',
                            'D' => 'Gare fermée forcément',
                        ),
                        'answer' => 'B',
                        'tip' => 'Souvent normal.',
                    ),
                    array(
                        'q' => 'Après une réimpression, le droit peut :',
                        'choices' => array(
                            'A' => 'Rester illimité',
                            'B' => 'Être consommé',
                            'C' => 'Créer un caissier',
                            'D' => 'Changer le programme national',
                        ),
                        'answer' => 'B',
                        'tip' => 'Une réimpression peut être limitée.',
                    ),
                    array(
                        'q' => 'Avant de traiter un client escale, vérifier :',
                        'choices' => array(
                            'A' => 'Escale / sous-gare / programme',
                            'B' => 'Le solde caissier du mois',
                            'C' => 'Le QCM admin',
                            'D' => 'Rien',
                        ),
                        'answer' => 'A',
                        'tip' => 'Contrôle opérationnel.',
                    ),
                    array(
                        'q' => 'En cas d\'incohérence programme :',
                        'choices' => array(
                            'A' => 'Forcer la vente',
                            'B' => 'Prévenir le chef de gare / responsable',
                            'C' => 'Désactiver le caissier',
                            'D' => 'Utiliser un autre compte',
                        ),
                        'answer' => 'B',
                        'tip' => 'Escalade.',
                    ),
                    array(
                        'q' => 'Le vendeur escale remplace-t-il le caissier ?',
                        'choices' => array(
                            'A' => 'Oui',
                            'B' => 'Non',
                            'C' => 'Oui le dimanche',
                            'D' => 'Oui si solde = 0',
                        ),
                        'answer' => 'B',
                        'tip' => 'Missions distinctes.',
                    ),
                    array(
                        'q' => 'Utiliser le compte d\'un autre vendeur escale :',
                        'choices' => array(
                            'A' => 'Autorisé',
                            'B' => 'Interdit',
                            'C' => 'Obligatoire',
                            'D' => 'Recommandé',
                        ),
                        'answer' => 'B',
                        'tip' => 'Traçabilité.',
                    ),
                    array(
                        'q' => 'Un admin / superviseur en réimpression peut voir :',
                        'choices' => array(
                            'A' => 'Uniquement ses tickets personnels',
                            'B' => 'Un périmètre gare plus large',
                            'C' => 'Rien',
                            'D' => 'Seulement les dépenses',
                        ),
                        'answer' => 'B',
                        'tip' => 'Supervision plus large.',
                    ),
                ),
            ),
            '12' => array(
                'titre' => 'QCM fin de formation — Agent bagage',
                'duree' => '20 minutes',
                'bareme' => '1 point par bonne réponse — Total /10 — Seuil indicatif : 7/10',
                'questions' => array(
                    array(
                        'q' => 'La mission principale de l\'agent bagage est :',
                        'choices' => array(
                            'A' => 'Valider les dépenses de caisse du chef de guichet',
                            'B' => 'Facturer, identifier et suivre les bagages',
                            'C' => 'Créer les programmes de voyage',
                            'D' => 'Vendre les tickets passagers',
                        ),
                        'answer' => 'B',
                        'tip' => 'Rôle bagage : facturation et suivi.',
                    ),
                    array(
                        'q' => '« Facturation bagages avec ticket » sert à :',
                        'choices' => array(
                            'A' => 'Facturer un bagage lié à un passager / ticket',
                            'B' => 'Clôturer la caisse principale',
                            'C' => 'Créer une sous-gare',
                            'D' => 'Valider une recette du caissier',
                        ),
                        'answer' => 'A',
                        'tip' => 'Bagage accompagnant un voyageur.',
                    ),
                    array(
                        'q' => '« Facturation bagages envoi » concerne plutôt :',
                        'choices' => array(
                            'A' => 'Uniquement les tickets gratuits',
                            'B' => 'Un bagage expédié / suivi d\'envoi (hors simple accompagnement)',
                            'C' => 'L\'arrêt de compte vendeur',
                            'D' => 'La validation des dépenses',
                        ),
                        'answer' => 'B',
                        'tip' => 'Circuit envoi / suivi bagage.',
                    ),
                    array(
                        'q' => 'Le menu « Bagages avec ticket non facturés » permet de :',
                        'choices' => array(
                            'A' => 'Supprimer tous les tickets du jour',
                            'B' => 'Traiter les bagages encore non facturés liés à un ticket',
                            'C' => 'Changer le rôle d\'un collègue',
                            'D' => 'Ouvrir une nouvelle entreprise',
                        ),
                        'answer' => 'B',
                        'tip' => 'Rattrapage des bagages non facturés.',
                    ),
                    array(
                        'q' => 'Le bordereau suivi bagages sert à :',
                        'choices' => array(
                            'A' => 'Remplacer l\'arrêt de compte caissier',
                            'B' => 'Lister / formaliser le suivi des bagages (remise, départ…)',
                            'C' => 'Modifier les tarifs nationaux',
                            'D' => 'Créer un compte administrateur',
                        ),
                        'answer' => 'B',
                        'tip' => 'Document de suivi opérationnel.',
                    ),
                    array(
                        'q' => 'Avant de remettre un bagage, vous devez :',
                        'choices' => array(
                            'A' => 'Rien contrôler si le client presse',
                            'B' => 'Contrôler le reçu et l\'identité',
                            'C' => 'Utiliser le compte du caissier',
                            'D' => 'Valider une dépense',
                        ),
                        'answer' => 'B',
                        'tip' => 'Contrôle reçu + identité obligatoire.',
                    ),
                    array(
                        'q' => 'L\'agent bagage peut-il saisir une recette de caisse au nom du chef ?',
                        'choices' => array(
                            'A' => 'Oui, toujours',
                            'B' => 'Non',
                            'C' => 'Oui le dimanche seulement',
                            'D' => 'Oui si le solde est à zéro',
                        ),
                        'answer' => 'B',
                        'tip' => 'Interdit : poste bagage ≠ saisie recette chef.',
                    ),
                    array(
                        'q' => 'L\'arrêt / compte bagage sert à :',
                        'choices' => array(
                            'A' => 'Clôturer la période de facturation bagage de l\'agent',
                            'B' => 'Créer un programme',
                            'C' => 'Désactiver tous les vendeurs',
                            'D' => 'Changer le mot de passe admin',
                        ),
                        'answer' => 'A',
                        'tip' => 'Clôture du poste bagage.',
                    ),
                    array(
                        'q' => 'Pour contrôler son activité, l\'agent bagage doit surtout comparer :',
                        'choices' => array(
                            'A' => 'Les rôles admin et les QCM',
                            'B' => 'Bagages facturés, bordereaux et arrêt de compte',
                            'C' => 'Uniquement le nombre de bus',
                            'D' => 'Les mots de passe des collègues',
                        ),
                        'answer' => 'B',
                        'tip' => 'Cohérence facturation / bordereaux / compte.',
                    ),
                    array(
                        'q' => 'Utiliser le compte d\'un autre agent bagage :',
                        'choices' => array(
                            'A' => 'Autorisé pour gagner du temps',
                            'B' => 'Interdit',
                            'C' => 'Obligatoire en fin de journée',
                            'D' => 'Recommandé par la procédure',
                        ),
                        'answer' => 'B',
                        'tip' => 'Traçabilité et responsabilité individuelle.',
                    ),
                ),
            ),
            'general' => array(
                'titre' => 'QCM fin de formation — Vue d\'ensemble',
                'duree' => '15 minutes',
                'bareme' => '1 point par bonne réponse — Total /8 — Seuil indicatif : 6/8',
                'questions' => array(
                    array(
                        'q' => 'Chaque agent travaille avec :',
                        'choices' => array(
                            'A' => 'Un compte et un rôle dans une gare',
                            'B' => 'Uniquement un numéro de ticket',
                            'C' => 'Le mot de passe du collègue',
                            'D' => 'Sans gare',
                        ),
                        'answer' => 'A',
                        'tip' => 'Compte + rôle + gare.',
                    ),
                    array(
                        'q' => 'Un compte désactivé :',
                        'choices' => array(
                            'A' => 'Peut encore être utilisé',
                            'B' => 'Ne doit plus être utilisé',
                            'C' => 'Augmente le solde',
                            'D' => 'Imprime plus vite',
                        ),
                        'answer' => 'B',
                        'tip' => 'Désactivé = inutilisable.',
                    ),
                    array(
                        'q' => 'Chaîne correcte en caisse :',
                        'choices' => array(
                            'A' => 'Caissier saisit → chef valide',
                            'B' => 'Chef saisit → caissier valide',
                            'C' => 'Vendeur valide la caisse',
                            'D' => 'Passager valide le solde',
                        ),
                        'answer' => 'B',
                        'tip' => 'Circuit standard.',
                    ),
                    array(
                        'q' => 'Caissier principal et adjoint :',
                        'choices' => array(
                            'A' => 'Font exactement la même piste sans distinction',
                            'B' => 'Ont des pistes / soldes distincts',
                            'C' => 'Sont identiques au vendeur',
                            'D' => 'N\'existent pas',
                        ),
                        'answer' => 'B',
                        'tip' => 'Principal ≠ adjoint.',
                    ),
                    array(
                        'q' => 'Avant d\'opérer, il faut :',
                        'choices' => array(
                            'A' => 'Choisir la bonne gare',
                            'B' => 'Ignorer la gare',
                            'C' => 'Désactiver le compte',
                            'D' => 'Supprimer les recettes',
                        ),
                        'answer' => 'A',
                        'tip' => 'Contexte gare obligatoire.',
                    ),
                    array(
                        'q' => 'Qui a saisi une ligne reste :',
                        'choices' => array(
                            'A' => 'Toujours le caissier',
                            'B' => 'L\'auteur de la saisie',
                            'C' => 'Le bus',
                            'D' => 'Le quartier',
                        ),
                        'answer' => 'B',
                        'tip' => 'Auteur ≠ validateur.',
                    ),
                    array(
                        'q' => 'Partager son identifiant :',
                        'choices' => array(
                            'A' => 'Autorisé',
                            'B' => 'Interdit',
                            'C' => 'Obligatoire',
                            'D' => 'Sans effet',
                        ),
                        'answer' => 'B',
                        'tip' => 'Sécurité.',
                    ),
                    array(
                        'q' => 'En cas d\'anomalie solde :',
                        'choices' => array(
                            'A' => 'Inventer une recette',
                            'B' => 'Alerter le responsable avec la gare concernée',
                            'C' => 'Supprimer des données',
                            'D' => 'Changer de rôle au hasard',
                        ),
                        'answer' => 'B',
                        'tip' => 'Escalade propre.',
                    ),
                ),
            ),
        );

        return isset($qcms[$role_code]) ? $qcms[$role_code] : null;
    }
}
