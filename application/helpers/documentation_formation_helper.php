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
                'titre' => 'Documentation générale',
                'sous_titre' => 'Procédures d\'utilisation par cas d\'usage — destinée aux décideurs',
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
                'sous_titre' => 'Validation technique, solde et performance de clôture de caisse',
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
                'sous_titre' => 'Itinéraire → escale de départ → destinations (origine / escales / extrême)',
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
                    'Paramétrer lignes, tarifs, escales et compositions d\'itinéraire pour le catalogue commercial.',
                    'Contrôler les anomalies, assister les utilisateurs et protéger les données.',
                ),
                'autorise' => array(
                    array('Créer ou désactiver un compte', 'Oui, avec vérification et motif'),
                    array('Affecter un poste et une gare', 'Oui, selon la décision de la direction'),
                    array('Régler les programmes, tarifs, escales et référentiels', 'Oui, après contrôle'),
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
                    'Proposer une destination technique (ex. OUAGAESCAL) comme destination commerciale de vente.',
                ),
                'controles' => array(
                    'Revue des comptes, gares et postes actifs.',
                    'Suivi des changements importants et des corrections de données.',
                    'Contrôle des alertes de sécurité et des rapports quotidiens.',
                    'Vérifier que les escales et compositions d\'itinéraire restent cohérentes avec le plan de transport.',
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
                'finalite' => 'Contrôler techniquement les mouvements transmis par les chefs, tenir un solde exact et clôturer rapidement sans laisser de file en retard.',
                'responsable' => 'Responsable financier / superviseur',
                'missions' => array(
                    'Traiter la file VALIDATION avec méthode : contrôler montant, motif, date et pièce avant décision.',
                    'Accepter ou refuser les recettes, dépenses et dépôts de la même gare (piste principale).',
                    'Suivre le solde validé, détecter les écarts et effectuer la fermeture de caisse dans les délais.',
                    'Réduire les attentes : prioriser les chefs guichet en retard et utiliser la validation de masse seulement après contrôle.',
                ),
                'autorise' => array(
                    array('Accepter ou refuser une opération du chef guichet', 'Oui, après contrôle'),
                    array('Voir le solde et les états de sa caisse', 'Oui'),
                    array('Saisir sa propre opération de caisse', 'Oui, seulement si la procédure le prévoit'),
                    array('Changer le nom du chef guichet qui a saisi', 'Non'),
                    array('Valider en masse', 'Oui, si chaque ligne a été contrôlée'),
                ),
                'eventuel' => array(
                    'Travail dans plusieurs gares si chacune lui est affectée (une gare à la fois).',
                    'Saisie de certaines recettes ou dépenses propres à la caisse.',
                    'Rapports supplémentaires ou remplacement temporaire accordés par un responsable.',
                ),
                'interdits' => array(
                    'Se déclarer auteur d\'une opération saisie par un chef guichet.',
                    'Accepter une opération sans la contrôler (même en masse).',
                    'Travailler dans une gare non affectée ou avec le poste du caissier adjoint.',
                    'Laisser une file d\'attente sans traitement au-delà du délai fixé pour la gare.',
                ),
                'controles' => array(
                    'Comparer le solde de l\'application avec les pièces et l\'argent disponible.',
                    'Vérifier les opérations encore en attente et expliquer les refus.',
                    'Mesurer la cadence : attentes traitées / jour et délais de validation.',
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
                    'Choisir le bon voyage, la bonne destination et le bon tarif (y compris correspondance et escale).',
                    'Enregistrer les informations du voyageur et remettre le ticket.',
                    'Utiliser « Confirmer autre ticket » lorsque le cas l\'exige.',
                    'Faire son arrêt de vente.',
                ),
                'autorise' => array(
                    array('Vendre et imprimer un ticket', 'Oui'),
                    array('Vendre une correspondance / transit', 'Oui, selon les axes composés'),
                    array('Vendre à escale', 'Oui, si l\'escale est configurée sur le parent'),
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
                    'Forcer un hub ou une destination hors catalogue commercial.',
                ),
                'controles' => array(
                    'Comparer les tickets émis avec l\'arrêt de vente.',
                    'Justifier les annulations et réimpressions.',
                    'Vérifier les chemins de correspondance et les escales choisies.',
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
                    'Faire lier les correspondances de programmes (même jour ou lendemain) lorsque le plan de transport l\'exige.',
                    'Coordonner le personnel et les moyens de la gare.',
                    'Suivre les ventes et états utiles à l\'exploitation.',
                ),
                'autorise' => array(
                    array('Créer ou modifier un programme et ses horaires', 'Oui'),
                    array('Lier une correspondance de programmes', 'Oui, dans le périmètre de la gare'),
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
                    'Contrôler les liaisons principal → suite (date / sous-gares).',
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
                'finalite' => 'Aider à préparer et mettre à jour les programmes de voyage et leurs correspondances.',
                'responsable' => 'Chef de gare / responsable de programmation',
                'missions' => array(
                    'Préparer les programmes, horaires et informations de voyage.',
                    'Lier les programmes en correspondance (même jour ou J+1) selon consignes.',
                    'Contrôler les changements avant publication.',
                    'Aider le responsable dans les tâches de programmation confiées.',
                ),
                'autorise' => array(
                    array('Créer ou modifier un programme', 'Oui, dans le périmètre confié'),
                    array('Lier une correspondance de programmes', 'Oui, après choix de la suite et de la portée'),
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
                    'Publier une composition d\'itinéraire incohérente avec le plan de transport.',
                ),
                'controles' => array(
                    'Faire valider les changements importants.',
                    'Vérifier date de suite (J / J+1) et sous-gares de portée.',
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
                'finalite' => 'Servir les voyageurs depuis un point de départ choisi sur un itinéraire (origine, escale ou extrême).',
                'responsable' => 'Chef de gare / chef de guichet',
                'missions' => array(
                    'Entrer dans la gare, choisir un itinéraire, puis le point de départ (origine / escale / extrême).',
                    'Vendre vers les autres points de l\'itinéraire (origines, escales, extrêmes) avec le prix affiché.',
                    'Traiter bagages, courriers et réimpressions autorisés sur le périmètre escale.',
                ),
                'autorise' => array(
                    array('Choisir un itinéraire de la gare', 'Oui'),
                    array('Fixer l\'escale de départ', 'Oui, parmi origine / escales / extrême'),
                    array('Vendre vers les destinations de l\'itinéraire', 'Oui'),
                    array('Voir ses propres opérations', 'Oui'),
                    array('Réimprimer un ticket', 'Oui, seulement si l\'autorisation existe'),
                    array('Accepter une caisse', 'Non'),
                ),
                'eventuel' => array(
                    'Autre gare / escale après affectation.',
                    'Réimpression exceptionnelle accordée par un responsable.',
                ),
                'interdits' => array(
                    'Réimprimer sans demande ou sans autorisation.',
                    'Forcer une vente hors de l\'itinéraire choisi.',
                    'Utiliser le compte d\'un autre vendeur.',
                    'Vendre une destination technique hors catalogue comme escale.',
                ),
                'controles' => array(
                    'Vérifier itinéraire, point de départ et destination avant encaissement.',
                    'Contrôler le prix affiché (origine / escale / extrême).',
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
                'finalite' => 'Sécuriser la caisse par un contrôle rigoureux et une clôture performante de la file d\'attente dans les délais.',
                'responsable' => 'Responsable financier / superviseur / administrateur',
                'missions' => array(
                    'Contrôler le montant, le motif, la date, la gare et la caisse avant validation.',
                    'Valider ou refuser les recettes, dépenses et dépôts des chefs guichet de la même gare.',
                    'Traiter la file d\'attente avec priorisation (ancienneté / volume) et respecter les délais de gare.',
                    'Suivre son solde validé, effectuer l\'arrêt de caisse et signaler immédiatement tout écart.',
                    'Conserver la séparation entre auteur de la saisie et validateur (caissier).',
                ),
                'permissions' => array(
                    array('Validation recette', 'Autorisé', 'Enregistre le validateur ; l\'auteur de la saisie reste inchangé'),
                    array('Validation dépense', 'Autorisé', 'Enregistre le validateur ; l\'auteur de la dépense reste inchangé'),
                    array('Validation dépôt', 'Autorisé', 'Enregistre le validateur ; l\'auteur du dépôt reste inchangé'),
                    array('Refus', 'Autorisé', 'Avec contrôle et commentaire suffisamment explicite'),
                    array('Validation de masse', 'Autorisé après contrôle', 'Accélère une file déjà vérifiée ; ne remplace pas le contrôle'),
                    array('Consultation solde / rapports', 'Autorisé', 'Sur son compte et son périmètre de gare'),
                    array('Saisie d\'un mouvement propre', 'Autorisé si prévu par la procédure', 'Dans ce cas seulement, le caissier peut être l\'auteur de la ligne'),
                ),
                'permissions_eventuelles' => array(
                    'Validation sur plusieurs gares si chaque gare lui est formellement attribuée (une à la fois).',
                    'Saisie de recettes ou dépenses propres à la caisse selon la procédure interne.',
                    'Impression de rapports détaillés et consultation d\'historiques si le module est ouvert.',
                    'Délégation temporaire documentée par un administrateur, sans partage d\'identifiants.',
                ),
                'interdits' => array(
                    'Remplacer l\'auteur de la saisie par son propre compte pendant la validation.',
                    'Valider une ligne d\'une autre gare sans attribution correspondante.',
                    'Valider en masse sans contrôle des montants / motifs.',
                    'Utiliser le compte du caissier adjoint sans être en rôle adjoint.',
                    'Laisser une file d\'attente au-delà du délai sans motif ni alerte.',
                ),
                'controles' => array(
                    'File de validation restante, délais et refus motivés.',
                    'Rapprochement quotidien du solde système avec les justificatifs.',
                    'Indicateurs de cadence (attentes traitées / jour).',
                    'Alerte immédiate si auteur et validateur deviennent identiques après une validation non saisie par le caissier.',
                ),
            ),
            '18' => array(
                'intitule' => 'Fiche de poste — Caissier adjoint',
                'finalite' => 'Assurer les contrôles et validations délégués sur le compte adjoint, sans se substituer au caissier principal hors délégation.',
                'responsable' => 'Caissier principal / responsable financier',
                'missions' => array(
                    'Valider ou refuser les mouvements confiés au compte adjoint.',
                    'Contrôler les justificatifs et suivre le solde adjoint.',
                    'Rendre compte au caissier principal et signaler tout écart.',
                ),
                'permissions' => array(
                    array('Validation recette', 'Autorisé sur le compte adjoint', 'Validation sur le compte adjoint'),
                    array('Validation dépense', 'Autorisé sur le compte adjoint', 'Validation sur le compte adjoint'),
                    array('Validation dépôt', 'Autorisé sur le compte adjoint', 'Validation sur le compte adjoint'),
                    array('Refus', 'Autorisé sur son périmètre', 'Avec motif et traçabilité'),
                    array('Consultation solde', 'Autorisé', 'Solde du compte adjoint uniquement'),
                ),
                'permissions_eventuelles' => array(
                    'Accès à plusieurs gares si des attributions adjoint actives existent.',
                    'Remplacement temporaire encadré, après activation explicite du rôle approprié.',
                    'Consultation de rapports complémentaires selon délégation.',
                ),
                'interdits' => array(
                    'Utiliser le compte du caissier principal.',
                    'Modifier l\'auteur chef guichet lors de la validation.',
                    'Continuer à opérer avec une attribution adjoint désactivée.',
                    'Valider hors de la gare active.',
                ),
                'controles' => array(
                    'Rapprochement du compte adjoint.',
                    'Revue des délégations et des anciennes attributions adjoint.',
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
                    array('Saisie recette', 'Autorisé', 'Auteur = son compte chef guichet'),
                    array('Saisie dépense', 'Autorisé sous contrôle du solde', 'Auteur = son compte chef guichet'),
                    array('Saisie dépôt', 'Autorisé selon procédure', 'Auteur = son compte chef guichet'),
                    array('Arrêt de compte', 'Autorisé', 'Transmet les lignes au caissier'),
                    array('Consultation solde / états', 'Autorisé', 'Sur son guichet, sa gare et sa période'),
                    array('Validation caisse', 'Non autorisé', 'Réservée au caissier principal ou adjoint'),
                ),
                'permissions_eventuelles' => array(
                    'Gestion de plusieurs gares si chaque attribution chef guichet est active et sélectionnée séparément.',
                    'Consultation de rapports supplémentaires selon délégation du superviseur.',
                    'Supervision d\'un aide chef guichet ou de vendeurs identifiés sur la même gare.',
                    'Dérogation de vente temporaire uniquement si elle est accordée et motivée par un administrateur.',
                ),
                'interdits' => array(
                    'Valider ses propres lignes à la place du caissier.',
                    'Saisir sous le compte d\'un vendeur ou d\'un autre chef guichet.',
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
                    'Saisir les mouvements confiés avec son propre compte.',
                    'Contrôler les pièces et préparer l\'arrêt de compte.',
                    'Signaler au chef guichet toute anomalie avant transmission au caissier.',
                ),
                'permissions' => array(
                    array('Saisie recette / dépense / dépôt', 'Autorisé', 'Même logique d\'auteur que le chef guichet'),
                    array('Consultation solde', 'Autorisé', 'Sur son périmètre attribué'),
                    array('Arrêt de compte', 'Autorisé selon organisation', 'Sous responsabilité du chef guichet'),
                    array('Validation caisse', 'Non autorisé', 'Réservée au caissier'),
                ),
                'permissions_eventuelles' => array(
                    'Prise en charge temporaire d\'un périmètre chef avec attribution rôle 16 active.',
                    'Accès à plusieurs gares selon affectations explicites.',
                    'Consultation de rapports nécessaires à la préparation de l\'arrêt.',
                ),
                'interdits' => array(
                    'Utiliser le compte du chef guichet titulaire.',
                    'Valider ou refuser à la place du caissier.',
                    'Étendre de lui-même sa délégation à une autre gare.',
                    'Modifier une opération après arrêt sans procédure.',
                ),
                'controles' => array(
                    'Validation du travail par le chef guichet responsable.',
                    'Contrôle des soldes et justificatifs avant arrêt.',
                    'Revue régulière de la délégation.',
                ),
            ),
            '6' => array(
                'intitule' => 'Fiche de poste — Vendeur',
                'finalite' => 'Vendre les titres de transport correctement (simple, correspondance, escale) et assurer la traçabilité de sa vacation jusqu\'à l\'arrêt vendeur.',
                'responsable' => 'Chef de guichet / chef de gare',
                'missions' => array(
                    'Vendre, imprimer et remettre les tickets aux clients.',
                    'Contrôler programme, destination, tarif, chemin de correspondance et éventuelle escale.',
                    'Utiliser « Confirmer autre ticket » lorsque le client présente un titre à confirmer.',
                    'Effectuer son arrêt vendeur et remettre les éléments au chef.',
                ),
                'permissions' => array(
                    array('Vente ticket', 'Autorisé', 'Sur ses programmes, sa gare et sa vacation'),
                    array('Vente correspondance / transit', 'Autorisé', 'Selon compositions d\'itinéraire et programmes actifs'),
                    array('Vente escale', 'Autorisé', 'Si escale configurée sur l\'itinéraire parent'),
                    array('Confirmer autre ticket', 'Autorisé', 'Selon boutons ouverts au rôle'),
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
                    'Utiliser le compte d\'un collègue.',
                    'S\'inscrire comme auteur d\'une recette de caisse à la place d\'un autre.',
                    'Modifier librement une vente arrêtée.',
                    'Contourner un blocage d\'arrêt de compte.',
                    'Forcer un hub hors composition ou une destination technique hors catalogue.',
                ),
                'controles' => array(
                    'Concordance entre tickets émis et arrêt vendeur.',
                    'Annulations et réimpressions justifiées.',
                    'Chemins de correspondance et escales cohérents avec la demande client.',
                    'Arrêt réalisé à la fin de la vacation.',
                ),
            ),
            '17' => array(
                'intitule' => 'Fiche de poste — Vendeur escale',
                'finalite' => 'Traiter les ventes mobiles d\'escale : choisir l\'itinéraire, fixer le point de départ, puis vendre vers origine / escales / extrême.',
                'responsable' => 'Chef de gare / chef de guichet',
                'missions' => array(
                    'Parcourir gare → itinéraires → points (origine, escales, extrême) et figer le départ choisi.',
                    'Vendre via « Vente mobile escal » vers les destinations restantes, avec prix affiché.',
                    'Effectuer les réimpressions autorisées et conserver leur traçabilité.',
                    'Remonter les incohérences (itinéraire manquant, sous-gare absente) au responsable de gare.',
                ),
                'permissions' => array(
                    array('Choix d\'itinéraire / escale de départ', 'Autorisé', 'Sur la gare et les lignes liées'),
                    array('Vente mobile escale', 'Autorisé', 'Départ figé ; destinations = autres points de l\'itinéraire'),
                    array('Consultation de ses opérations', 'Autorisé', 'Périmètre de son compte'),
                    array('Réimpression', 'Conditionnelle', 'Uniquement si le ticket est éligible et le droit disponible'),
                    array('Validation caisse', 'Non autorisé', 'Réservée aux caissiers'),
                    array('Administration programme', 'Non autorisé', 'Sauf rôle complémentaire explicite'),
                ),
                'permissions_eventuelles' => array(
                    'Réimpression exceptionnelle autorisée par un responsable.',
                    'Accès à une autre gare / escale après affectation formelle.',
                    'Consultation élargie uniquement avec une délégation de supervision distincte.',
                ),
                'interdits' => array(
                    'Réimprimer sans demande ou sans droit disponible.',
                    'Utiliser le compte d\'un autre vendeur.',
                    'Forcer une vente hors de l\'itinéraire sélectionné.',
                    'Valider une recette ou une dépense de caisse.',
                    'Utiliser une destination technique (ex. OUAGAESCAL) comme escale commerciale.',
                ),
                'controles' => array(
                    'Journal des réimpressions et droits consommés.',
                    'Concordance départ figé, destination, prix et vendeur.',
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
                            'Ne pas confondre le login avec le compte / identité opérationnelle.',
                        ),
                        'bullets' => array(
                            'compte utilisateur — accès de connexion',
                            'compte / identité — identité opérationnelle dans une gare + un rôle (utilisé partout en caisse)',
                            'gare d\'affectation — gare de travail (ex. Banfora, Ouaga, Bobo)',
                            'une seule gare active à la fois pour le rôle connecté',
                            'attribution désactivée — ne doit plus servir',
                            'compte désactivé — ne doit plus être utilisé',
                        ),
                    ),
                    array(
                        'h' => '3. Chaîne caisse (résumé)',
                        'paras' => array(
                            'Le chef guichet (ou aide) saisit recettes/dépenses. Après arrêt de compte, le caissier valide ou refuse.',
                        ),
                        'table' => array(
                            'headers' => array('Étape', 'Qui', 'Effet'),
                            'rows' => array(
                                array('Saisie', 'Chef guichet / aide', 'Auteur = compte du saisisseur'),
                                array('Arrêt compte', 'Chef guichet', 'Lignes en file de validation'),
                                array('Validation', 'Caissier principal ou adjoint', 'Validateur = compte caissier + validation active'),
                                array('Solde caissier', 'Caissier', 'Uniquement les lignes validées sur son compte'),
                            ),
                        ),
                    ),
                    array(
                        'h' => '4. Caissier principal vs adjoint',
                        'paras' => array(
                            'Le caissier principal et l\'adjoint ont des comptes et des soldes séparés.',
                        ),
                        'bullets' => array(
                            'Caissier principal : validations sur son propre compte',
                            'Caissier adjoint : validations sur son propre compte (séparé du principal)',
                            'Un compte désactivé ne doit plus être utilisé pour valider',
                        ),
                    ),
                    array(
                        'h' => '5. Bonnes pratiques',
                        'bullets' => array(
                            'Toujours choisir la bonne gare au login',
                            'Ne pas partager les identifiants',
                            'Faire l\'arrêt de compte avant de quitter le poste',
                            'En cas d\'erreur de montant : faire refuser puis ressaisir, ne pas « inventer » une ligne',
                            'Contacter le superviseur / admin si message solde incohérent',
                        ),
                    ),
                    array(
                        'h' => '6. Vente avec correspondance (transit)',
                        'paras' => array(
                            'Certains axes commerciaux sont composés de plusieurs jambes (ex. Banfora → Bobo → Bamako).',
                            'Le système propose des chemins possibles : les compositions déclarées en administration sont prioritaires.',
                        ),
                        'bullets' => array(
                            'Choisir le chemin retenu parmi les propositions (Corr. 2/3/4 ne sont pas présélectionnées : l\'opérateur choisit)',
                            'Renseigner chaque jambe (horaire, siège) dans l\'ordre du voyage',
                            'Ne pas forcer un hub hors composition (ex. passer par Ouaga si la composition prévoit Bobo)',
                        ),
                    ),
                    array(
                        'h' => '7. Vente à escale',
                        'paras' => array(
                            'Une escale est une destination intermédiaire paramétrée sur un itinéraire parent, avec son propre prix.',
                            'Il n\'est pas nécessaire de créer un programme dédié à l\'escale.',
                        ),
                        'bullets' => array(
                            'Sélectionner d\'abord l\'arrivée finale (parent), puis cocher « Vente escale »',
                            'Choisir l\'escale dans la liste ; le prix d\'escale remplace le tarif terminus',
                            'Sur un transit à plusieurs jambes, l\'escale s\'applique sur la jambe concernée (quartiers correctement mappés)',
                        ),
                    ),
                    array(
                        'h' => '8. Destinations techniques exclues du catalogue',
                        'paras' => array(
                            'Certaines gares d\'arrivée techniques (ex. OUAGAESCAL) ne doivent pas être proposées à la vente ni comme hub de correspondance.',
                            'Les vraies escales commerciales passent par le module « Vente escale » / itineraire_escales.',
                        ),
                    ),
                    array(
                        'h' => '9. Correspondances de programmes (ops)',
                        'paras' => array(
                            'En administration programmes, on peut lier un programme principal à une suite le même jour ou le lendemain (J+1).',
                            'La portée (compagnie / sous-gares) se choisit après la suite, avec libellés dynamiques.',
                        ),
                    ),
                    array(
                        'h' => '10. Regroupement par compagnie d\'arrivée',
                        'paras' => array(
                            'Les listes de gares d\'arrivée, lignes et programmes sont souvent présentées par compagnie d\'arrivée pour faciliter le choix opérateur.',
                        ),
                    ),
                ),
            ),
            '4' => array(
                'titre' => 'Manuel — Caissier principal',
                'sections' => array(
                    array(
                        'h' => '1. Mission',
                        'paras' => array(
                            'Valider rapidement et correctement les arrêts des chefs guichet de la même gare, tenir le solde du compte principal, et ne laisser aucune file d\'attente sans motif dans les délais.',
                            'La performance se mesure à la cadence de traitement (attente → validé/refusé) ; la qualité, à la juste application des règles de solde et de compte.',
                        ),
                    ),
                    array(
                        'h' => '2. Connexion et gare',
                        'bullets' => array(
                            'Se connecter avec son compte caissier principal',
                            'Choisir la gare concernée (une seule gare active à la fois)',
                            'Ouvrir la caisse puis la file de validation / recettes / dépenses',
                            'Si multi-gares : traiter une gare complètement avant de changer',
                        ),
                    ),
                    array(
                        'h' => '3. Validation des lignes chef guichet',
                        'paras' => array(
                            'Depuis la file de validation : sélectionner le chef guichet, contrôler chaque ligne, puis valider ou refuser.',
                        ),
                        'bullets' => array(
                            'Valider → la ligne est acceptée en caisse et vous êtes enregistré comme validateur',
                            'Refuser → ligne hors solde ; le chef guichet doit ressaisir si besoin',
                            'Ne jamais modifier l\'auteur : c\'est le chef guichet qui a saisi',
                            'Validation de masse autorisée seulement après contrôle des montants / motifs',
                        ),
                    ),
                    array(
                        'h' => '4. Solde caisse',
                        'paras' => array(
                            'Solde = dépôts validés + recettes validées − versements − dépenses validées, filtrés sur votre compte caissier principal.',
                            'Les saisies chefs guichet non validées n\'entrent pas dans votre solde. Une dépense ne peut pas dépasser le solde affiché.',
                        ),
                    ),
                    array(
                        'h' => '5. File d\'attente et délais',
                        'bullets' => array(
                            'Prioriser les chefs guichet avec la plus ancienne attente ou le plus gros volume',
                            'Respecter le délai de validation fixé pour la gare',
                            'Ne pas partir en fin de journée avec une file importante sans motif documenté',
                            'Alerter le superviseur dès qu\'un solde ou une file devient incohérent',
                        ),
                    ),
                    array(
                        'h' => '6. Arrêt / fermeture de caisse',
                        'bullets' => array(
                            'Après validation journalière, procéder à l\'arrêt de caisse selon la procédure interne',
                            'Vérifier pièces vs solde à l\'écran avant clôture',
                            'Conserver les justificatifs des refus traités',
                        ),
                    ),
                    array(
                        'h' => '7. Erreurs fréquentes',
                        'bullets' => array(
                            'Mauvaise gare active → rien n\'apparaît ou mauvais soldes',
                            'Valider en masse sans lire → écarts et reprise coûteuse',
                            'Confondre saisie chef guichet (auteur) et validation caissier (validateur)',
                            'Utiliser le compte adjoint alors que le rôle actif est principal',
                        ),
                    ),
                ),
            ),
            '18' => array(
                'titre' => 'Manuel — Caissier adjoint',
                'sections' => array(
                    array(
                        'h' => '1. Mission',
                        'paras' => array(
                            'Même mission que le caissier principal, mais sur le compte adjoint (solde séparé).',
                        ),
                    ),
                    array(
                        'h' => '2. Différence avec le caissier principal',
                        'bullets' => array(
                            'Les soldes et listes sont séparés du compte principal',
                            'Ne pas utiliser un compte de caissier principal si votre rôle actif est adjoint',
                            'Si le compte adjoint est désactivé, se connecter uniquement avec le rôle actif autorisé',
                        ),
                    ),
                    array(
                        'h' => '3. Parcours type',
                        'bullets' => array(
                            'Login → choix gare → caisse → validation',
                            'Valider les chefs guichet de la gare',
                            'Contrôler le solde adjoint avant toute dépense / versement',
                        ),
                    ),
                    array(
                        'h' => '4. Points de vigilance',
                        'paras' => array(
                            'Historique validé en adjoint reste lié à l\'ancien compte adjoint même après un changement de rôle (ex. passage en principal).',
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
                            'En période ouverte, les lignes ne sont pas encore clôturées ; l\'auteur reste votre compte.',
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
                            'headers' => array('Élément', 'Signification'),
                            'rows' => array(
                                array('Auteur (saisie)', 'Vous — ne change pas à la validation'),
                                array('Validateur (caissier)', 'Caissier qui a validé'),
                                array('Validation active', 'Accepté en caisse caissier'),
                            ),
                        ),
                    ),
                ),
            ),
            '16' => array(
                'titre' => 'Manuel — Aide chef de guichet',
                'sections' => array(
                    array(
                        'h' => '1. Mission',
                        'paras' => array(
                            'Même logique métier que le chef de guichet (saisie, période ouverte, arrêt).',
                            'Travailler sous la responsabilité du chef guichet / consignes de gare.',
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
                        'h' => '2. Vente simple',
                        'bullets' => array(
                            'Sélectionner date, axe (ligne) et programme / horaire corrects',
                            'Vérifier identité et contacts passager, siège et quartier si demandé',
                            'Contrôler le prix, encaisser, imprimer / remettre le ticket',
                            'Ne pas vendre si le compte est bloqué (arrêt non fait / restrictions)',
                        ),
                    ),
                    array(
                        'h' => '3. Correspondance / transit',
                        'paras' => array(
                            'Pour un axe multi-segments, le système propose des chemins. Les compositions déclarées (ex. Banfora→Bobo→Bamako) sont prioritaires.',
                        ),
                        'bullets' => array(
                            'Lire les propositions de chemin et choisir explicitement celui demandé par le client',
                            'Compléter chaque jambe (horaire, siège) ; ne pas laisser de jambe vide',
                            'Si « Pas de départ… » s\'affiche, attendre l\'application du 1er chemin proposé ou en choisir un autre',
                        ),
                    ),
                    array(
                        'h' => '4. Vente à escale',
                        'bullets' => array(
                            'Choisir l\'arrivée finale (itinéraire parent), puis cocher « Vente escale »',
                            'Sélectionner l\'escale ; le prix d\'escale s\'applique automatiquement',
                            'Ne pas confondre escale commerciale et destination technique hors catalogue',
                        ),
                    ),
                    array(
                        'h' => '5. Confirmer autre ticket',
                        'bullets' => array(
                            'Utiliser le bouton dédié lorsque le client présente un autre ticket à confirmer',
                            'Choisir axe, quartier, horaire ; pour un transit, suivre les jambes comme en vente',
                            'La case escale fonctionne aussi en confirmation (attention au bon quartier sur chaque jambe)',
                        ),
                    ),
                    array(
                        'h' => '6. Arrêt vendeur',
                        'paras' => array(
                            'L\'arrêt clôture les ventes de la période. La suite (recette consolidée) est traitée côté chef / caissier selon le circuit gare.',
                        ),
                    ),
                    array(
                        'h' => '7. Interdits',
                        'bullets' => array(
                            'Ne pas modifier une vente déjà arrêtée sans procédure',
                            'Ne pas utiliser le compte d\'un collègue',
                            'Ne pas ignorer une alerte d\'arrêt obligatoire',
                            'Ne pas forcer un itinéraire hors composition déclarée sans consigne',
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
                            'Vendre depuis un point de l\'itinéraire (origine, escale ou extrême / terminus) vers les autres points, avec impression ticket TPE.',
                        ),
                    ),
                    array(
                        'h' => '2. Parcours de vente (obligatoire)',
                        'paras' => array(
                            'Le départ n\'est plus choisi dans le formulaire : il est figé à l\'entrée dans l\'escale.',
                        ),
                        'bullets' => array(
                            '1) Connexion → choisir la gare',
                            '2) Voir la liste des itinéraires liés à la gare (lignes au départ, en terminus ou passant par escale)',
                            '3) Ouvrir un itinéraire → liste origine + escales + extrême',
                            '4) Choisir le point de départ → écran boutons (vente, réimp, bagage…)',
                            '5) Vente mobile escal : destinations = autres escales + origine + extrême',
                        ),
                    ),
                    array(
                        'h' => '3. Ticket imprimé',
                        'bullets' => array(
                            'Le ticket thermique affiche compagnie, trajet, passager, téléphone, prix, code et code-barres',
                            'La bannière « Connecté en tant que… » ne doit pas apparaître à l\'impression',
                            'Caractères agrandis pour lecture TPE / 57 mm',
                        ),
                    ),
                    array(
                        'h' => '4. Réimpression',
                        'bullets' => array(
                            'La liste de réimpression est filtrée sur vos opérations',
                            'Après impression, le droit de réimpression peut être consommé',
                            'En cas de liste vide : pas de ticket éligible pour votre compte',
                        ),
                    ),
                    array(
                        'h' => '5. Bonnes pratiques',
                        'bullets' => array(
                            'Vérifier itinéraire et point de départ avant d\'ouvrir la vente',
                            'Contrôler le prix destination (libellé origine / escale / extrême)',
                            'Escalader au chef si aucun itinéraire / sous-gare n\'apparaît',
                            'Ne pas utiliser une destination technique type OUAGAESCAL',
                        ),
                    ),
                ),
            ),
            '15' => array(
                'titre' => 'Manuel — Aide-programmeur (rôle 15)',
                'sections' => array(
                    array(
                        'h' => '1. Mission',
                        'paras' => array(
                            'Préparer et mettre à jour les programmes, horaires et liaisons de correspondance dans le périmètre confié.',
                        ),
                    ),
                    array(
                        'h' => '2. Programmes',
                        'bullets' => array(
                            'Créer / activer les programmes pour les dates et sous-gares concernées',
                            'Contrôler statut actif avant ouverture des ventes',
                            'Informer le responsable avant toute annulation impactant des ventes',
                        ),
                    ),
                    array(
                        'h' => '3. Lier une correspondance de programmes',
                        'paras' => array(
                            'Depuis l\'écran programmes, lier un programme principal à une suite le même jour ou le lendemain (J+1).',
                        ),
                        'bullets' => array(
                            'Choisir d\'abord la suite proposée (libellés avec date ; badge lendemain si J+1)',
                            'Puis définir la portée : compagnie ou sous-gares (cases SG selon le mode choisi)',
                            'Les sous-gares pré-cochées reflètent la portée réelle du programme',
                        ),
                    ),
                    array(
                        'h' => '4. Compositions d\'itinéraire',
                        'paras' => array(
                            'Les axes multi-segments (conteneurs) doivent avoir une composition déclarée cohérente (ordre géographique des jambes).',
                            'Cette composition guide le guichet lors des ventes / confirmations en correspondance.',
                        ),
                    ),
                    array(
                        'h' => '5. Vigilance',
                        'bullets' => array(
                            'Ne pas publier un programme sans accord du responsable',
                            'Ne pas traiter une destination technique hors catalogue comme une arrivée commerciale',
                            'Faire valider les changements sensibles (tarifs / compositions) par l\'admin ou le chef de gare',
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
                'duree' => '25 minutes',
                'bareme' => '1 point par bonne réponse — Total /12 — Seuil indicatif : 9/12',
                'questions' => array(
                    array(
                        'q' => 'Votre solde de caisse intègre surtout :',
                        'choices' => array(
                            'A' => 'Toutes les saisies des chefs guichet, même non validées',
                            'B' => 'Uniquement les mouvements que vous avez validés sur votre compte',
                            'C' => 'Le chiffre d\'affaires tickets du vendeur',
                            'D' => 'Le solde de toutes les gares du pays',
                        ),
                        'answer' => 'B',
                        'tip' => 'Le solde ne compte que ce que vous avez validé, pas la file d\'attente.',
                    ),
                    array(
                        'q' => 'Après avoir validé une recette du chef guichet, quelle identité est correcte ?',
                        'choices' => array(
                            'A' => 'Votre compte caissier remplace l\'identité du chef guichet',
                            'B' => 'L\'identité du chef guichet reste ; votre compte s\'ajoute comme validateur',
                            'C' => 'L\'identité du chef guichet et celle du caissier sont effacées',
                            'D' => 'Le vendeur devient l\'auteur',
                        ),
                        'answer' => 'B',
                        'tip' => 'Qui a saisi garde son identité ; le caissier valide avec son compte.',
                    ),
                    array(
                        'q' => 'Si vous refusez une dépense :',
                        'choices' => array(
                            'A' => 'Elle entre quand même dans votre solde',
                            'B' => 'Elle reste hors de votre solde ; le chef guichet doit corriger ou ressaisir si besoin',
                            'C' => 'Elle change automatiquement de gare',
                            'D' => 'Elle devient une recette validée',
                        ),
                        'answer' => 'B',
                        'tip' => 'Un refus n\'entre pas dans le solde caissier.',
                    ),
                    array(
                        'q' => 'Indicateur de performance le plus pertinent pour un caissier principal :',
                        'choices' => array(
                            'A' => 'Nombre de tickets vendus par les vendeurs',
                            'B' => 'Cadence de traitement de la file d\'attente (validé / refusé) et respect des délais',
                            'C' => 'Nombre de programmes créés',
                            'D' => 'Taille du logo imprimé',
                        ),
                        'answer' => 'B',
                        'tip' => 'Performance = file traitée + délais, pas le volume des ventes guichet.',
                    ),
                    array(
                        'q' => 'Validation de plusieurs lignes en une fois : bonne pratique ?',
                        'choices' => array(
                            'A' => 'Toujours tout valider d\'un clic sans lire',
                            'B' => 'Après contrôle des montants et motifs, pour accélérer une file déjà vérifiée',
                            'C' => 'Uniquement le dimanche',
                            'D' => 'Interdite dans tous les cas',
                        ),
                        'answer' => 'B',
                        'tip' => 'Cela accélère, mais ne remplace pas le contrôle.',
                    ),
                    array(
                        'q' => 'Avant une dépense sur votre caisse, la règle est :',
                        'choices' => array(
                            'A' => 'Ignorer le solde si le chef guichet insiste',
                            'B' => 'Vérifier que le montant ne dépasse pas le solde validé affiché',
                            'C' => 'Utiliser le solde du chef guichet non validé',
                            'D' => 'Passer sur le compte adjoint sans droit',
                        ),
                        'answer' => 'B',
                        'tip' => 'Sinon le système indique que le montant dépasse le solde.',
                    ),
                    array(
                        'q' => 'Caissier principal et caissier adjoint :',
                        'choices' => array(
                            'A' => 'C\'est exactement le même compte, seul le libellé change',
                            'B' => 'Chacun a son propre compte et son propre solde ; on ne les mélange pas',
                            'C' => 'L\'adjoint remplace toujours le principal',
                            'D' => 'Le principal valide uniquement les bagages',
                        ),
                        'answer' => 'B',
                        'tip' => 'Principal et adjoint ont des comptes séparés.',
                    ),
                    array(
                        'q' => 'Face à une file d\'attente importante en fin de journée, la bonne conduite est :',
                        'choices' => array(
                            'A' => 'Partir sans traiter',
                            'B' => 'Prioriser les plus anciens / gros volumes, noter le reliquat, alerter si le délai est dépassé',
                            'C' => 'Supprimer les lignes des chefs guichet',
                            'D' => 'Donner son mot de passe au chef guichet pour qu\'il valide',
                        ),
                        'answer' => 'B',
                        'tip' => 'Prioriser, tracer et alerter si besoin.',
                    ),
                    array(
                        'q' => 'Si un délai de validation est imposé pour la gare :',
                        'choices' => array(
                            'A' => 'Vous pouvez ignorer les attentes du mois précédent',
                            'B' => 'Vous devez traiter avant la date limite, sinon blocage ou alerte',
                            'C' => 'Seul le vendeur est concerné',
                            'D' => 'Le délai s\'applique uniquement aux programmes',
                        ),
                        'answer' => 'B',
                        'tip' => 'Le délai de validation doit être respecté.',
                    ),
                    array(
                        'q' => 'Avec plusieurs gares à gérer, la bonne méthode est :',
                        'choices' => array(
                            'A' => 'Mélanger les validations de plusieurs gares sans choisir',
                            'B' => 'Entrer dans une gare, traiter sa file d\'attente, puis passer à la suivante',
                            'C' => 'Utiliser le compte d\'un collègue de l\'autre gare',
                            'D' => 'Valider toutes les gares sans ouvrir la caisse',
                        ),
                        'answer' => 'B',
                        'tip' => 'Une gare à la fois.',
                    ),
                    array(
                        'q' => 'Si le solde à l\'écran ne correspond pas aux espèces en caisse, première action :',
                        'choices' => array(
                            'A' => 'Forcer une dépense pour « rattraper »',
                            'B' => 'Arrêter les validations douteuses, recenser attentes et refus, alerter le superviseur',
                            'C' => 'Changer le compte du chef guichet',
                            'D' => 'Désactiver tous les vendeurs',
                        ),
                        'answer' => 'B',
                        'tip' => 'Diagnostiquer et alerter, ne pas contourner.',
                    ),
                    array(
                        'q' => 'Quel comportement dégrade à la fois la performance et la qualité ?',
                        'choices' => array(
                            'A' => 'Contrôler puis valider rapidement une file déjà triée',
                            'B' => 'Valider sans lire pour « faire du volume », puis corriger en catastrophe le lendemain',
                            'C' => 'Refuser une ligne incorrecte avec un motif clair',
                            'D' => 'Clôturer après rapprochement des pièces et du solde',
                        ),
                        'answer' => 'B',
                        'tip' => 'La fausse vitesse crée des reprises et des écarts.',
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
                            'B' => 'Valider les arrêts des chefs guichet et suivre le solde de son compte',
                            'C' => 'Créer les entreprises',
                            'D' => 'Modifier les programmes bus',
                        ),
                        'answer' => 'B',
                        'tip' => 'Même mission que le caissier principal, sur son propre compte.',
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
                        'tip' => 'Compte désactivé = inutilisable.',
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
                        'q' => 'Qui saisit une dépense de chef guichet avant votre validation ?',
                        'choices' => array(
                            'A' => 'Le caissier adjoint',
                            'B' => 'Le chef de guichet',
                            'C' => 'Le passager',
                            'D' => 'Le bus',
                        ),
                        'answer' => 'B',
                        'tip' => 'Le chef guichet saisit ; le caissier valide.',
                    ),
                    array(
                        'q' => 'Validation et refus se font :',
                        'choices' => array(
                            'A' => 'Sans regarder la gare',
                            'B' => 'Sur les chefs guichet de la même gare',
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
                            'D' => 'Le rôle est administrateur',
                        ),
                        'answer' => 'B',
                        'tip' => 'Réduire le montant ou vérifier le solde.',
                    ),
                    array(
                        'q' => 'Pour revenir à la liste des gares, on utilise en général :',
                        'choices' => array(
                            'A' => 'Retour gare / accueil gares',
                            'B' => 'Supprimer le compte',
                            'C' => 'Créer une entreprise',
                            'D' => 'Imprimer un ticket passager',
                        ),
                        'answer' => 'A',
                        'tip' => 'Navigation habituelle du caissier.',
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
                        'tip' => 'Contrôle avant validation.',
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
                        'tip' => 'Sécurité et traçabilité.',
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
                            'C' => 'Créer un compte',
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
                        'q' => 'Si le caissier refuse une ligne :',
                        'choices' => array(
                            'A' => 'Elle compte quand même dans sa caisse',
                            'B' => 'Elle ne compte pas dans le solde caissier',
                            'C' => 'Elle change de gare',
                            'D' => 'Elle devient un ticket',
                        ),
                        'answer' => 'B',
                        'tip' => 'Un refus n\'entre pas dans le solde caissier.',
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
                            'D' => 'Changer de compte collègue',
                        ),
                        'answer' => 'B',
                        'tip' => 'Ne pas contourner ; signaler.',
                    ),
                    array(
                        'q' => 'Partager son compte chef guichet :',
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
                        'q' => 'Le rôle d\'aide chef guichet est proche de :',
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
                        'q' => 'Pouvez-vous valider les arrêts des autres chefs guichet ?',
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
                        'tip' => 'Même règle que le chef guichet.',
                    ),
                    array(
                        'q' => 'En cas de doute sur un montant :',
                        'choices' => array(
                            'A' => 'Saisir quand même',
                            'B' => 'Demander au chef guichet / responsable avant l\'arrêt',
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
                            'B' => 'Suit la même logique que le chef guichet',
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
                'duree' => '20 minutes',
                'bareme' => '1 point par bonne réponse — Total /10 — Seuil indicatif : 7/10',
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
                    array(
                        'q' => 'Pour une vente à escale, la bonne procédure est :',
                        'choices' => array(
                            'A' => 'Choisir l\'arrivée finale, cocher « Vente escale », puis choisir l\'escale',
                            'B' => 'Créer un nouveau programme pour chaque escale',
                            'C' => 'Vendre toujours au tarif terminus',
                            'D' => 'Demander au caissier de saisir le ticket',
                        ),
                        'answer' => 'A',
                        'tip' => 'Parent + case Vente escale + choix escale.',
                    ),
                    array(
                        'q' => 'Sur une correspondance multi-jambes :',
                        'choices' => array(
                            'A' => 'On laisse les chemins sans choix et on valide',
                            'B' => 'On choisit le chemin proposé (composition déclarée prioritaire) puis on complète chaque jambe',
                            'C' => 'On force toujours le passage par Ouaga',
                            'D' => 'On ne vend jamais de transit',
                        ),
                        'answer' => 'B',
                        'tip' => 'Choix explicite du chemin + jambes complètes.',
                    ),
                ),
            ),
            '17' => array(
                'titre' => 'QCM fin de formation — Vendeur escale',
                'duree' => '20 minutes',
                'bareme' => '1 point par bonne réponse — Total /10 — Seuil indicatif : 7/10',
                'questions' => array(
                    array(
                        'q' => 'Ordre correct du parcours de vente escale :',
                        'choices' => array(
                            'A' => 'Vente directe sans choisir d\'itinéraire',
                            'B' => 'Gare → itinéraire → point de départ → boutons / vente destinations',
                            'C' => 'Caissier → validation → vente',
                            'D' => 'Programme national → admin → ticket',
                        ),
                        'answer' => 'B',
                        'tip' => 'Le départ est figé à l\'entrée dans l\'escale.',
                    ),
                    array(
                        'q' => 'Sur un itinéraire, les points de départ possibles sont :',
                        'choices' => array(
                            'A' => 'Uniquement le terminus',
                            'B' => 'Origine, escales tarifées et extrême (terminus)',
                            'C' => 'Uniquement les sous-gares techniques',
                            'D' => 'Toutes les gares du pays',
                        ),
                        'answer' => 'B',
                        'tip' => 'Origine + escales + extrême.',
                    ),
                    array(
                        'q' => 'Après choix du point de départ, les destinations proposées sont :',
                        'choices' => array(
                            'A' => 'Le même point uniquement',
                            'B' => 'Les autres escales, plus l\'origine et l\'extrême selon le cas',
                            'C' => 'Uniquement OUAGAESCAL',
                            'D' => 'Les soldes caissier',
                        ),
                        'answer' => 'B',
                        'tip' => 'Autres points de l\'itinéraire.',
                    ),
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
                        'q' => 'Sur le ticket imprimé, la bannière « Connecté en tant que… » :',
                        'choices' => array(
                            'A' => 'Doit apparaître pour contrôler l\'agent',
                            'B' => 'Ne doit pas s\'imprimer sur le papier ticket',
                            'C' => 'Remplace le prix',
                            'D' => 'Est obligatoire pour le client',
                        ),
                        'answer' => 'B',
                        'tip' => 'Chrome session masqué à l\'impression.',
                    ),
                    array(
                        'q' => 'En cas d\'itinéraire absent pour la gare :',
                        'choices' => array(
                            'A' => 'Forcer une vente hors liste',
                            'B' => 'Prévenir le chef guichet / admin (lignes, escales, sous-gare)',
                            'C' => 'Utiliser le compte caissier',
                            'D' => 'Désactiver la gare',
                        ),
                        'answer' => 'B',
                        'tip' => 'Escalade paramétrage.',
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
                        'q' => 'L\'agent bagage peut-il saisir une recette de caisse au nom du chef guichet ?',
                        'choices' => array(
                            'A' => 'Oui, toujours',
                            'B' => 'Non',
                            'C' => 'Oui le dimanche seulement',
                            'D' => 'Oui si le solde est à zéro',
                        ),
                        'answer' => 'B',
                        'tip' => 'Interdit : poste bagage ≠ saisie recette chef guichet.',
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
                'duree' => '20 minutes',
                'bareme' => '1 point par bonne réponse — Total /10 — Seuil indicatif : 7/10',
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
                            'A' => 'Caissier saisit → chef guichet valide',
                            'B' => 'Chef guichet saisit → caissier valide',
                            'C' => 'Vendeur valide la caisse',
                            'D' => 'Passager valide le solde',
                        ),
                        'answer' => 'B',
                        'tip' => 'Circuit standard.',
                    ),
                    array(
                        'q' => 'Caissier principal et adjoint :',
                        'choices' => array(
                            'A' => 'Font exactement le même compte sans distinction',
                            'B' => 'Ont des comptes et des soldes distincts',
                            'C' => 'Sont identiques au vendeur',
                            'D' => 'N\'existent pas',
                        ),
                        'answer' => 'B',
                        'tip' => 'Principal et adjoint ont des comptes séparés.',
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
                        'tip' => 'Qui a saisi reste l\'auteur ; le caissier valide.',
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
                    array(
                        'q' => 'Une vente à escale commerciale se fait :',
                        'choices' => array(
                            'A' => 'Via la case « Vente escale » sur un itinéraire parent',
                            'B' => 'En choisissant OUAGAESCAL comme axe',
                            'C' => 'Uniquement après validation caissier',
                            'D' => 'Sans programme parent',
                        ),
                        'answer' => 'A',
                        'tip' => 'Escale = parent + case Vente escale.',
                    ),
                    array(
                        'q' => 'Une correspondance de programmes (ops) peut lier :',
                        'choices' => array(
                            'A' => 'Un principal à une suite le même jour ou le lendemain (J+1)',
                            'B' => 'Uniquement des programmes d\'années différentes',
                            'C' => 'Un ticket à une caisse',
                            'D' => 'Un vendeur à un caissier',
                        ),
                        'answer' => 'A',
                        'tip' => 'Lien principal → suite J ou J+1.',
                    ),
                ),
            ),
        );

        $default = isset($qcms[$role_code]) ? $qcms[$role_code] : null;
        if ($default === null) {
            return null;
        }

        $override = documentation_formation_qcm_load_override($role_code);
        if ($override !== null) {
            $override['_source'] = 'custom';
            $override['_has_override'] = true;
            return $override;
        }

        $default['_source'] = 'default';
        $default['_has_override'] = false;
        return $default;
    }
}

if (!function_exists('documentation_formation_qcm_override_dir')) {
    function documentation_formation_qcm_override_dir()
    {
        $dir = APPPATH . 'cache/data/qcm';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir;
    }
}

if (!function_exists('documentation_formation_qcm_override_path')) {
    function documentation_formation_qcm_override_path($role_code)
    {
        $role_code = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $role_code);
        if ($role_code === '') {
            return null;
        }
        return documentation_formation_qcm_override_dir() . '/role_' . $role_code . '.json';
    }
}

if (!function_exists('documentation_formation_qcm_normalize')) {
    /**
     * Normalise / valide un QCM (titre, durée, barème, questions A–D).
     *
     * @param mixed $raw
     * @return array|null
     */
    function documentation_formation_qcm_normalize($raw)
    {
        if (!is_array($raw)) {
            return null;
        }

        $titre = isset($raw['titre']) ? trim((string) $raw['titre']) : '';
        $duree = isset($raw['duree']) ? trim((string) $raw['duree']) : '';
        $bareme = isset($raw['bareme']) ? trim((string) $raw['bareme']) : '';
        $questions_in = isset($raw['questions']) && is_array($raw['questions']) ? $raw['questions'] : array();

        if ($titre === '' || empty($questions_in)) {
            return null;
        }

        $questions = array();
        foreach ($questions_in as $item) {
            if (!is_array($item)) {
                continue;
            }
            $q = isset($item['q']) ? trim((string) $item['q']) : '';
            if ($q === '') {
                continue;
            }
            $choices_raw = isset($item['choices']) && is_array($item['choices']) ? $item['choices'] : array();
            $choices = array();
            foreach (array('A', 'B', 'C', 'D') as $letter) {
                $text = '';
                if (isset($choices_raw[$letter])) {
                    $text = trim((string) $choices_raw[$letter]);
                } elseif (isset($choices_raw[strtolower($letter)])) {
                    $text = trim((string) $choices_raw[strtolower($letter)]);
                }
                $choices[$letter] = $text;
            }
            // Au moins 2 choix non vides
            $filled = 0;
            foreach ($choices as $t) {
                if ($t !== '') {
                    $filled++;
                }
            }
            if ($filled < 2) {
                continue;
            }
            $answer = strtoupper(trim((string) (isset($item['answer']) ? $item['answer'] : 'A')));
            if (!isset($choices[$answer]) || $choices[$answer] === '') {
                // Première lettre non vide
                foreach ($choices as $letter => $t) {
                    if ($t !== '') {
                        $answer = $letter;
                        break;
                    }
                }
            }
            $tip = isset($item['tip']) ? trim((string) $item['tip']) : '';
            $questions[] = array(
                'q' => $q,
                'choices' => $choices,
                'answer' => $answer,
                'tip' => $tip,
            );
        }

        if (empty($questions)) {
            return null;
        }

        if ($duree === '') {
            $duree = '20 minutes';
        }
        if ($bareme === '') {
            $n = count($questions);
            $seuil = max(1, (int) ceil($n * 0.7));
            $bareme = '1 point par bonne réponse — Total /' . $n . ' — Seuil indicatif : ' . $seuil . '/' . $n;
        }

        return array(
            'titre' => $titre,
            'duree' => $duree,
            'bareme' => $bareme,
            'questions' => $questions,
        );
    }
}

if (!function_exists('documentation_formation_qcm_load_override')) {
    function documentation_formation_qcm_load_override($role_code)
    {
        $path = documentation_formation_qcm_override_path($role_code);
        if ($path === null || !is_file($path)) {
            return null;
        }
        $json = @file_get_contents($path);
        if ($json === false || $json === '') {
            return null;
        }
        $decoded = json_decode($json, true);
        return documentation_formation_qcm_normalize($decoded);
    }
}

if (!function_exists('documentation_formation_qcm_save_override')) {
    /**
     * @return array{ok:bool,error?:string,path?:string}
     */
    function documentation_formation_qcm_save_override($role_code, $raw)
    {
        $normalized = documentation_formation_qcm_normalize($raw);
        if ($normalized === null) {
            return array('ok' => false, 'error' => 'qcm_invalide');
        }
        $path = documentation_formation_qcm_override_path($role_code);
        if ($path === null) {
            return array('ok' => false, 'error' => 'role_invalide');
        }
        $dir = dirname($path);
        if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
            return array('ok' => false, 'error' => 'dossier_inaccessible');
        }
        $payload = $normalized;
        $payload['updated_at'] = date('c');
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        if ($json === false) {
            return array('ok' => false, 'error' => 'json_encode');
        }
        if (@file_put_contents($path, $json, LOCK_EX) === false) {
            return array('ok' => false, 'error' => 'ecriture_impossible');
        }
        return array('ok' => true, 'path' => $path);
    }
}

if (!function_exists('documentation_formation_qcm_delete_override')) {
    function documentation_formation_qcm_delete_override($role_code)
    {
        $path = documentation_formation_qcm_override_path($role_code);
        if ($path === null || !is_file($path)) {
            return true;
        }
        return @unlink($path);
    }
}

if (!function_exists('documentation_formation_qcm_export_payload')) {
    function documentation_formation_qcm_export_payload($role_code)
    {
        $qcm = documentation_formation_qcm($role_code);
        if (!$qcm) {
            return null;
        }
        unset($qcm['_source'], $qcm['_has_override']);
        return array(
            'role_code' => (string) $role_code,
            'role_titre' => ($meta = documentation_formation_role_meta($role_code)) ? $meta['titre'] : '',
            'exported_at' => date('c'),
            'titre' => $qcm['titre'],
            'duree' => $qcm['duree'],
            'bareme' => $qcm['bareme'],
            'questions' => $qcm['questions'],
        );
    }
}

if (!function_exists('documentation_generale_cas_utilisation')) {
    /**
     * Documentation générale pour décideurs : procédures par cas d'utilisation.
     *
     * @return array{
     *   titre:string,
     *   intro:array<int,string>,
     *   principes:array<int,string>,
     *   cas:array<int,array{
     *     id:string,
     *     titre:string,
     *     objectif:string,
     *     acteurs:array<int,string>,
     *     etapes:array<int,string>,
     *     resultat:string,
     *     controles:array<int,string>
     *   }>
     * }
     */
    function documentation_generale_cas_utilisation()
    {
        return array(
            'titre' => 'Documentation générale — procédures d\'utilisation',
            'intro' => array(
                'Ce document décrit comment utiliser Ticket Rakieta selon les principaux cas d\'usage métier.',
                'Il s\'adresse aux décideurs et responsables : chaque cas précise l\'objectif, les acteurs, les étapes, le résultat attendu et les points de contrôle.',
                'Les fiches détaillées par rôle (formation et QCM) restent disponibles dans l\'onglet « Formation par rôle ».',
            ),
            'principes' => array(
                'Un agent travaille toujours avec son propre compte, dans une gare explicitement affectée.',
                'La personne qui saisit n\'est pas forcément celle qui valide : la séparation des tâches protège les recettes.',
                'Toute vente, confirmation ou mouvement de caisse doit laisser une trace identifiable (auteur, gare, date).',
                'En cas d\'écart ou de doute, on alerte le responsable plutôt que de contourner une procédure.',
            ),
            'cas' => array(
                array(
                    'id' => 'connexion',
                    'titre' => '1. Connexion et choix de gare',
                    'objectif' => 'Permettre à un agent d\'accéder uniquement à l\'espace de travail qui lui a été confié.',
                    'acteurs' => array('Tout agent', 'Administrateur / superviseur (affectation des gares)'),
                    'etapes' => array(
                        'L\'agent se connecte avec son identifiant personnel.',
                        'Il sélectionne la gare active parmi celles qui lui sont affectées.',
                        'Le système ouvre le menu et les boutons correspondant à son rôle.',
                    ),
                    'resultat' => 'L\'agent travaille dans la bonne gare, avec le bon profil, sans accès aux autres postes.',
                    'controles' => array(
                        'Vérifier que chaque agent a les bonnes gares et le bon rôle.',
                        'Désactiver immédiatement un compte ou une affectation devenue inutile.',
                        'Interdire le partage de mots de passe.',
                    ),
                ),
                array(
                    'id' => 'vente-guichet',
                    'titre' => '2. Vente de ticket au guichet',
                    'objectif' => 'Émettre un billet payant pour un voyageur, sur un programme et un axe valides.',
                    'acteurs' => array('Vendeur', 'Chef de guichet / aide (organisation)', 'Caissier (contrôle des recettes)'),
                    'etapes' => array(
                        'Choisir la date, l\'axe (ligne) et l\'horaire / programme disponibles.',
                        'Renseigner le passager, le siège et, si besoin, le quartier de destination.',
                        'Contrôler le prix proposé par le système, encaisser, puis valider la vente.',
                        'Remettre le ticket imprimé au client.',
                    ),
                    'resultat' => 'Un ticket enregistré, un siège réservé, une recette traçable pour la gare.',
                    'controles' => array(
                        'Comparer ventes du jour et recettes saisies / validées.',
                        'Surveiller les ventes hors tarif ou à 0 F via les rapports dédiés.',
                        'Vérifier que le programme vendu était bien actif.',
                    ),
                ),
                array(
                    'id' => 'vente-fi',
                    'titre' => '3. Vente FI (facture / flux dédié)',
                    'objectif' => 'Traiter une vente selon le circuit FI prévu pour certains rôles ou contextes.',
                    'acteurs' => array('Agent autorisé FI', 'Responsable de gare'),
                    'etapes' => array(
                        'Ouvrir le module FI autorisé pour le rôle.',
                        'Sélectionner l\'axe, l\'horaire et les informations client comme pour une vente standard.',
                        'Valider l\'émission et conserver la pièce / trace FI.',
                    ),
                    'resultat' => 'Vente FI enregistrée, distincte ou complémentaire selon le paramétrage de l\'entreprise.',
                    'controles' => array(
                        'Limiter l\'accès FI aux seuls rôles concernés.',
                        'Rapprocher les ventes FI des justificatifs et des recettes.',
                    ),
                ),
                array(
                    'id' => 'confirmer-autre',
                    'titre' => '4. Confirmer un autre ticket',
                    'objectif' => 'Reprendre ou confirmer un voyage déjà connu (autre ticket) sur un axe et un horaire choisis.',
                    'acteurs' => array('Agent de confirmation / guichet autorisé', 'Superviseur (en cas d\'anomalie)'),
                    'etapes' => array(
                        'Ouvrir « Confirmer autre ticket ».',
                        'Choisir la gare de départ concernée, puis l\'axe et le quartier si demandé.',
                        'Sélectionner l\'horaire, renseigner ou retrouver le passager, puis confirmer.',
                        'En cas de correspondance, suivre les jambes proposées dans l\'ordre du voyage.',
                    ),
                    'resultat' => 'Le voyage est confirmé sur le bon programme, avec traçabilité de l\'opération.',
                    'controles' => array(
                        'Contrôler que l\'axe et l\'horaire correspondent au besoin client.',
                        'Vérifier les confirmations douteuses ou répétées dans les rapports.',
                    ),
                ),
                array(
                    'id' => 'correspondance',
                    'titre' => '5. Correspondance / transit (plusieurs jambes)',
                    'objectif' => 'Vendre ou confirmer un trajet composé de plusieurs segments (ex. Banfora → Bobo → Bamako).',
                    'acteurs' => array('Vendeur / agent de confirmation', 'Aide-programmeur (préparation des liaisons)'),
                    'etapes' => array(
                        'Partir d\'une ligne « conteneur » ou d\'un axe commercial prévu pour la correspondance.',
                        'Laisser le système proposer les chemins (compositions déclarées en priorité).',
                        'Choisir le chemin retenu, puis renseigner chaque jambe (horaire, siège, éventuelle escale).',
                        'Valider l\'ensemble avant encaissement / impression.',
                    ),
                    'resultat' => 'Un voyage multi-segments cohérent, avec places et prix par jambe selon les règles métier.',
                    'controles' => array(
                        'Vérifier que les compositions d\'itinéraire sont à jour.',
                        'Contrôler les temps d\'attente entre jambes et les gares de correspondance.',
                        'Ne pas vendre une destination technique hors catalogue commercial.',
                    ),
                ),
                array(
                    'id' => 'vente-escale',
                    'titre' => '6. Vente à escale (vendeur escale / mobile)',
                    'objectif' => 'Vendre depuis un point d\'un itinéraire (origine, escale ou extrême) vers les autres points, sans inventer un programme dédié.',
                    'acteurs' => array('Vendeur escale (rôle 17)', 'Administrateur (paramétrage lignes / escales)', 'Chef de gare (appui)'),
                    'etapes' => array(
                        'Entrer dans la gare : le système liste les itinéraires liés (départ, terminus ou passage).',
                        'Choisir un itinéraire, puis le point de départ (origine, escale ou extrême).',
                        'Ouvrir « Vente mobile escal » : le départ est figé ; choisir une destination parmi les autres points.',
                        'Contrôler le prix affiché, encaisser, imprimer le ticket TPE (sans bannière de session).',
                    ),
                    'resultat' => 'Ticket escale lisible, départ et destination cohérents avec l\'itinéraire, recette traçable au vendeur.',
                    'controles' => array(
                        'Maintenir à jour escales tarifées et lignes actives.',
                        'Vérifier qu\'une sous-gare technique existe pour les opérations bagage / caisse liées.',
                        'Contrôler les réimpressions et les droits consommés.',
                    ),
                ),
                array(
                    'id' => 'tri-passager',
                    'titre' => '6bis. Tri passager et recherche dans les résultats',
                    'objectif' => 'Retrouver rapidement un passager ou un ticket sur une période, pour impression ou contrôle.',
                    'acteurs' => array('Chef de guichet / admin / superviseur', 'Agent historique autorisé'),
                    'etapes' => array(
                        'Depuis l\'historique, lancer un tri passager sur la période voulue.',
                        'Consulter les onglets tickets directs et tickets transit.',
                        'Utiliser le champ de filtre instantané (nom, téléphone, code, siège, axe, date) pour réduire la liste.',
                        'Imprimer ou repositionner selon les droits.',
                    ),
                    'resultat' => 'Ticket localisé sans reparcourir toute la liste à la main.',
                    'controles' => array(
                        'Vérifier la cohérence de la période de recherche.',
                        'Limiter les repositionnements d\'impression aux rôles autorisés.',
                    ),
                ),
                array(
                    'id' => 'programmes',
                    'titre' => '7. Programmes et horaires',
                    'objectif' => 'Mettre à disposition des départs vendables (date, heure, ligne, sous-gare).',
                    'acteurs' => array('Aide-programmeur', 'Chef de gare / superviseur', 'Administrateur'),
                    'etapes' => array(
                        'Créer ou activer les lignes et heures nécessaires.',
                        'Générer / activer les programmes pour les dates concernées.',
                        'Lier les correspondances de programmes lorsque le métier l\'exige (même jour ou lendemain).',
                        'Contrôler le statut actif avant ouverture des ventes.',
                    ),
                    'resultat' => 'Des départs visibles et vendables au guichet, cohérents avec le plan de transport.',
                    'controles' => array(
                        'Revue quotidienne des programmes actifs / annulés.',
                        'Vérifier la cohérence sous-gare ↔ gare de vente.',
                    ),
                ),
                array(
                    'id' => 'tarifs-lignes',
                    'titre' => '8. Lignes, tarifs et compagnies d\'arrivée',
                    'objectif' => 'Définir les axes commerciaux, leurs prix et leur rattachement aux compagnies.',
                    'acteurs' => array('Administrateur', 'Direction commerciale'),
                    'etapes' => array(
                        'Créer ou mettre à jour les gares d\'arrivée et les lignes.',
                        'Paramétrer les tarifs (et escales le cas échéant).',
                        'S\'assurer que les listes guichet regroupent correctement les axes par compagnie d\'arrivée.',
                    ),
                    'resultat' => 'Catalogue commercial clair pour les opérateurs et les clients.',
                    'controles' => array(
                        'Contrôle périodique des tarifs et des axes vendables.',
                        'Exclure du catalogue les destinations techniques non commerciales.',
                    ),
                ),
                array(
                    'id' => 'caisse-saisie',
                    'titre' => '9. Caisse — saisie et arrêt de compte',
                    'objectif' => 'Enregistrer recettes / dépenses / dépôts de la gare, puis clôturer la vacation pour validation.',
                    'acteurs' => array('Chef de guichet', 'Aide chef de guichet'),
                    'etapes' => array(
                        'Saisir les mouvements de la vacation dans le module caisse.',
                        'Contrôler les totaux avant clôture.',
                        'Lancer l\'arrêt de compte pour envoyer les lignes en file de validation.',
                    ),
                    'resultat' => 'Une file claire de mouvements à valider par la caisse.',
                    'controles' => array(
                        'Un arrêt de compte doit précéder la fin de vacation.',
                        'Ne pas modifier a posteriori l\'auteur d\'une saisie.',
                    ),
                ),
                array(
                    'id' => 'caisse-validation',
                    'titre' => '10. Caisse — validation caissier',
                    'objectif' => 'Contrôler les mouvements des chefs guichet, tenir un solde exact et traiter la file d\'attente dans les délais.',
                    'acteurs' => array('Caissier principal', 'Caissier adjoint'),
                    'etapes' => array(
                        'Ouvrir la file de validation de la gare (compte principal ou adjoint selon le rôle).',
                        'Prioriser les attentes anciennes / volumineuses ; contrôler montant, motif et pièce.',
                        'Valider ou refuser (masse seulement après contrôle) ; ne pas modifier l\'auteur de la saisie.',
                        'Rapprocher solde à l\'écran et espèces, puis clôturer selon la procédure.',
                    ),
                    'resultat' => 'File traitée à temps, solde cohérent, refus traçables.',
                    'controles' => array(
                        'Séparer clairement les comptes caissier principal et adjoint.',
                        'Suivre les délais de validation.',
                        'Alerter dès qu\'un écart de solde apparaît ; ne pas contourner.',
                    ),
                ),
                array(
                    'id' => 'courrier-bagages',
                    'titre' => '11. Courrier et bagages',
                    'objectif' => 'Facturer et suivre les envois / bagages selon les modules ouverts au rôle.',
                    'acteurs' => array('Agent bagage', 'Superviseur courrier', 'Caissier (si rattachement recettes)'),
                    'etapes' => array(
                        'Ouvrir le module courrier ou bagage autorisé.',
                        'Saisir l\'expédition, la nature, les frais et les informations client.',
                        'Imprimer / remettre le justificatif et assurer le suivi jusqu\'à la remise.',
                    ),
                    'resultat' => 'Opération courrier/bagage tracée, avec frais associés correctement enregistrés.',
                    'controles' => array(
                        'Rapprocher les factures courrier/bagage des recettes associées.',
                        'Contrôler les remises et litiges.',
                    ),
                ),
                array(
                    'id' => 'supervision',
                    'titre' => '12. Supervision, audits et rapports',
                    'objectif' => 'Donner aux décideurs une vision de contrôle sur l\'activité et les anomalies.',
                    'acteurs' => array('Superviseur', 'Administrateur', 'Comptable', 'Direction'),
                    'etapes' => array(
                        'Consulter les rapports d\'activité (ventes, caisse, modifications de tickets, autres ventes).',
                        'Exploiter le rapport d\'audit quotidien lorsque disponible.',
                        'Décider des actions correctives (accès, procédures, formations).',
                    ),
                    'resultat' => 'Pilotage basé sur des faits mesurables et une chaîne de responsabilités claire.',
                    'controles' => array(
                        'Revue régulière des indicateurs et des alertes.',
                        'Suivi des formations (manuels / QCM) pour les rôles critiques.',
                    ),
                ),
            ),
        );
    }
}

