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
                'sous_titre' => 'Architecture comptes, gares et caisse',
            ),
            '4' => array(
                'code' => '4',
                'titre' => 'Caissier principal',
                'sous_titre' => 'Rôle 4 — validation et solde caisse',
            ),
            '18' => array(
                'code' => '18',
                'titre' => 'Caissier adjoint',
                'sous_titre' => 'Rôle 18 — piste adjoint (*ad)',
            ),
            '5' => array(
                'code' => '5',
                'titre' => 'Chef de guichet',
                'sous_titre' => 'Rôle 5 — saisie, solde ouvert, arrêt de compte',
            ),
            '16' => array(
                'code' => '16',
                'titre' => 'Aide chef de guichet',
                'sous_titre' => 'Rôle 16 — même logique que le chef (saisie)',
            ),
            '6' => array(
                'code' => '6',
                'titre' => 'Vendeur',
                'sous_titre' => 'Rôle 6 — vente tickets et arrêt vendeur',
            ),
            '17' => array(
                'code' => '17',
                'titre' => 'Vendeur escale',
                'sous_titre' => 'Rôle 17 — ventes escales / réimpression',
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

        return isset($manuels[$role_code]) ? $manuels[$role_code] : null;
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
                'titre' => 'QCM fin de formation — Caissier principal (rôle 4)',
                'duree' => '20 minutes',
                'bareme' => '1 point par bonne réponse — Total /10 — Seuil indicatif : 7/10',
                'questions' => array(
                    array(
                        'q' => 'Que signifie activeattrib = 1 pour un caissier ?',
                        'choices' => array(
                            'A' => 'Le compte est désactivé',
                            'B' => 'La gare actuellement active pour le rôle connecté',
                            'C' => 'Toutes les gares sont ouvertes en même temps',
                            'D' => 'Le solde est à zéro',
                        ),
                        'answer' => 'B',
                        'tip' => 'Une seule gare active à la fois pour le rôle.',
                    ),
                    array(
                        'q' => 'Lors de la validation d\'une recette chef, quelle colonne reçoit le roleattribut du caissier 4 ?',
                        'choices' => array(
                            'A' => 'idopera',
                            'B' => 'operavalidad',
                            'C' => 'operavalid',
                            'D' => 'cpuser_id',
                        ),
                        'answer' => 'C',
                        'tip' => 'idopera reste l\'auteur (chef) ; operavalid = validateur 4.',
                    ),
                    array(
                        'q' => 'Que fait un REJET sur une dépense en file ?',
                        'choices' => array(
                            'A' => 'Elle entre dans le solde caissier',
                            'B' => 'Elle est hors solde / hors file de validation',
                            'C' => 'Elle change automatiquement d\'auteur',
                            'D' => 'Elle passe sur la piste adjoint',
                        ),
                        'answer' => 'B',
                        'tip' => 'Rejet = pas de prise en compte dans le solde caissier.',
                    ),
                    array(
                        'q' => 'Le solde caisse principal se calcule principalement sur :',
                        'choices' => array(
                            'A' => 'Toutes les saisies chefs non validées',
                            'B' => 'Les mouvements validés sur la piste 4 (is_actif* + operavalid/opevalid)',
                            'C' => 'Uniquement les courriers',
                            'D' => 'Le username du chef',
                        ),
                        'answer' => 'B',
                        'tip' => 'Piste 4 = flags is_actif sans suffixe ad.',
                    ),
                    array(
                        'q' => 'activer_role = 1 sur une attribution signifie :',
                        'choices' => array(
                            'A' => 'Attribution utilisable',
                            'B' => 'Attribution désactivée — ne plus utiliser',
                            'C' => 'Gare active exclusive',
                            'D' => 'Session connectée',
                        ),
                        'answer' => 'B',
                        'tip' => 'Flags inversés : 0 = OK, 1 = off.',
                    ),
                    array(
                        'q' => 'Qui doit valider l\'arrêt de compte d\'un chef de guichet ?',
                        'choices' => array(
                            'A' => 'Le vendeur',
                            'B' => 'Le caissier de la même gare',
                            'C' => 'N\'importe quel utilisateur',
                            'D' => 'Le passager',
                        ),
                        'answer' => 'B',
                        'tip' => 'Circuit gare : chef saisit → caissier valide.',
                    ),
                    array(
                        'q' => 'Peut-on modifier idopera au moment de VALIDER une ligne ?',
                        'choices' => array(
                            'A' => 'Oui, pour mettre le caissier',
                            'B' => 'Oui, pour corriger la date',
                            'C' => 'Non — idopera doit rester l\'auteur',
                            'D' => 'Oui, uniquement le dimanche',
                        ),
                        'answer' => 'C',
                        'tip' => 'Correction appliquée : validation ne réécrit plus idopera.',
                    ),
                    array(
                        'q' => 'Avant une dépense caissier, le système vérifie :',
                        'choices' => array(
                            'A' => 'Le solde de la caisse',
                            'B' => 'Le numéro de bus uniquement',
                            'C' => 'Le code PIN du passager',
                            'D' => 'Rien',
                        ),
                        'answer' => 'A',
                        'tip' => 'Message « dépasse le solde » si montant > solde.',
                    ),
                    array(
                        'q' => 'Si deux gares apparaissent pour le même caissier, comment travailler ?',
                        'choices' => array(
                            'A' => 'Les deux en parallèle sans choisir',
                            'B' => 'Activer une gare (activeattrib) puis opérer',
                            'C' => 'Supprimer une gare chaque matin',
                            'D' => 'Changer de username',
                        ),
                        'answer' => 'B',
                        'tip' => 'Accueil multi-gares → entrée exclusive dans une gare.',
                    ),
                    array(
                        'q' => 'Une recette validée apparaît dans le solde caissier quand :',
                        'choices' => array(
                            'A' => 'active_recet = 0 seulement',
                            'B' => 'is_actifrecet = 1 et operavalid = caissier',
                            'C' => 'Le chef est déconnecté',
                            'D' => 'Le type est Courrier uniquement',
                        ),
                        'answer' => 'B',
                        'tip' => 'Validation = flags actifs + id validateur.',
                    ),
                ),
            ),
            '18' => array(
                'titre' => 'QCM fin de formation — Caissier adjoint (rôle 18)',
                'duree' => '20 minutes',
                'bareme' => '1 point par bonne réponse — Total /10 — Seuil indicatif : 7/10',
                'questions' => array(
                    array(
                        'q' => 'La piste adjoint utilise principalement :',
                        'choices' => array(
                            'A' => 'operavalid / is_actifrecet',
                            'B' => 'operavalidad / is_actifrecetad',
                            'C' => 'idopera uniquement',
                            'D' => 'cpuser_id',
                        ),
                        'answer' => 'B',
                        'tip' => 'Suffixe ad = adjoint.',
                    ),
                    array(
                        'q' => 'Si l\'attribution 18 est désactivée (activer_role=1) :',
                        'choices' => array(
                            'A' => 'On peut encore valider en 18',
                            'B' => 'Il ne faut plus utiliser ce rôle',
                            'C' => 'Le solde double',
                            'D' => 'Toutes les gares s\'activent',
                        ),
                        'answer' => 'B',
                        'tip' => 'Compte / rôle désactivé = inutilisable.',
                    ),
                    array(
                        'q' => 'Après passage d\'un adjoint en caissier 4, l\'historique 18 :',
                        'choices' => array(
                            'A' => 'Disparaît',
                            'B' => 'Reste lié aux anciens roleattribut 18',
                            'C' => 'Est recopié automatiquement en 4',
                            'D' => 'Passe au chef',
                        ),
                        'answer' => 'B',
                        'tip' => 'Les IDs d\'historique ne migrent pas seuls.',
                    ),
                    array(
                        'q' => 'Pour une dépense validée en adjoint, la colonne validateur est :',
                        'choices' => array(
                            'A' => 'opevalid',
                            'B' => 'opevalidad',
                            'C' => 'idop_dep',
                            'D' => 'idopera',
                        ),
                        'answer' => 'B',
                        'tip' => 'opevalidad pour rôle 18.',
                    ),
                    array(
                        'q' => 'Le solde adjoint sur l\'accueil doit utiliser les flags :',
                        'choices' => array(
                            'A' => 'is_actifrecet / is_actifdep',
                            'B' => 'is_actifrecetad / is_actifdepad',
                            'C' => 'actif_rect seulement',
                            'D' => 'Aucun flag',
                        ),
                        'answer' => 'B',
                        'tip' => 'Alignement soldes_accueil *ad.',
                    ),
                    array(
                        'q' => 'Qui saisit idop_dep sur une dépense chef ?',
                        'choices' => array(
                            'A' => 'Le caissier adjoint à la validation',
                            'B' => 'Le chef à la saisie',
                            'C' => 'L\'admin uniquement',
                            'D' => 'Le système après rejet',
                        ),
                        'answer' => 'B',
                        'tip' => 'Auteur = saisie ; validateur = validation.',
                    ),
                    array(
                        'q' => 'Validation et rejet se font :',
                        'choices' => array(
                            'A' => 'Sans regarder la gare',
                            'B' => 'Sur la file du chef de la même gare',
                            'C' => 'Uniquement le dimanche',
                            'D' => 'Par le vendeur',
                        ),
                        'answer' => 'B',
                        'tip' => 'Même gare que le chef.',
                    ),
                    array(
                        'q' => 'activeattrib pour un adjoint multi-gares :',
                        'choices' => array(
                            'A' => 'Plusieurs à 1 en même temps',
                            'B' => 'Au plus une gare à 1',
                            'C' => 'Toujours 0',
                            'D' => 'Inutile',
                        ),
                        'answer' => 'B',
                        'tip' => 'Règle exclusive.',
                    ),
                    array(
                        'q' => 'Un message « dépasse le solde » indique :',
                        'choices' => array(
                            'A' => 'Le ticket est expiré',
                            'B' => 'Le montant saisi est supérieur au solde caisse',
                            'C' => 'Le rôle est admin',
                            'D' => 'La gare est fermée définitivement',
                        ),
                        'answer' => 'B',
                        'tip' => 'Contrôle JS / métier sur monttcaisse.',
                    ),
                    array(
                        'q' => 'RETOUR GARE pour un caissier doit utiliser :',
                        'choices' => array(
                            'A' => 'Le cpuser_id seul',
                            'B' => 'Le roleattribut',
                            'C' => 'Le numéro de téléphone',
                            'D' => 'L\'id de la recette',
                        ),
                        'answer' => 'B',
                        'tip' => 'Navigation basée sur roleattribut.',
                    ),
                ),
            ),
            '5' => array(
                'titre' => 'QCM fin de formation — Chef de guichet (rôle 5)',
                'duree' => '20 minutes',
                'bareme' => '1 point par bonne réponse — Total /10 — Seuil indicatif : 7/10',
                'questions' => array(
                    array(
                        'q' => 'En tant que chef, vous êtes principalement :',
                        'choices' => array(
                            'A' => 'Validateur des recettes caissier',
                            'B' => 'Saisisseur (idopera / idop_dep)',
                            'C' => 'Administrateur système',
                            'D' => 'Imprimeur escale uniquement',
                        ),
                        'answer' => 'B',
                        'tip' => 'Le caissier valide ; le chef saisit.',
                    ),
                    array(
                        'q' => 'Avant une dépense, vous devez :',
                        'choices' => array(
                            'A' => 'Ignorer le solde',
                            'B' => 'Vérifier que le montant ≤ solde période ouverte',
                            'C' => 'Demander au passager',
                            'D' => 'Changer de gare',
                        ),
                        'answer' => 'B',
                        'tip' => 'Contrôle solde carte / formulaire.',
                    ),
                    array(
                        'q' => 'L\'arrêt de compte sert à :',
                        'choices' => array(
                            'A' => 'Supprimer les recettes',
                            'B' => 'Préparer / envoyer les lignes à la validation caissier',
                            'C' => 'Créer un username',
                            'D' => 'Fermer l\'entreprise',
                        ),
                        'answer' => 'B',
                        'tip' => 'File VALIDATION caissier.',
                    ),
                    array(
                        'q' => 'Après validation caissier, idopera d\'une recette chef doit :',
                        'choices' => array(
                            'A' => 'Devenir le caissier',
                            'B' => 'Rester le roleattribut du chef',
                            'C' => 'Passer à NULL',
                            'D' => 'Être égal à operavalid obligatoirement',
                        ),
                        'answer' => 'B',
                        'tip' => 'Auteur ≠ validateur.',
                    ),
                    array(
                        'q' => 'Le solde « période ouverte » s\'appuie surtout sur :',
                        'choices' => array(
                            'A' => 'operavalid',
                            'B' => 'Flags active_* / lignes encore ouvertes pour idopera',
                            'C' => 'Le mot de passe',
                            'D' => 'Le rôle 1',
                        ),
                        'answer' => 'B',
                        'tip' => 'Pas la piste caissier.',
                    ),
                    array(
                        'q' => 'Qui valide vos recettes après arrêt ?',
                        'choices' => array(
                            'A' => 'Vous-même en rôle 5',
                            'B' => 'Le caissier 4 ou 18 de la gare',
                            'C' => 'Le vendeur 6',
                            'D' => 'Personne',
                        ),
                        'answer' => 'B',
                        'tip' => 'Circuit standard.',
                    ),
                    array(
                        'q' => 'Si le caissier REJETTE une ligne :',
                        'choices' => array(
                            'A' => 'Elle compte quand même dans son solde',
                            'B' => 'Elle ne compte pas dans le solde caissier',
                            'C' => 'Elle change de gare',
                            'D' => 'Elle devient Courrier',
                        ),
                        'answer' => 'B',
                        'tip' => 'Rejet = hors solde caissier.',
                    ),
                    array(
                        'q' => 'activer = 1 sur votre compte utilisateur signifie :',
                        'choices' => array(
                            'A' => 'Compte utilisable',
                            'B' => 'Compte désactivé',
                            'C' => 'Gare active',
                            'D' => 'Solde positif',
                        ),
                        'answer' => 'B',
                        'tip' => 'Flag inversé compte.',
                    ),
                    array(
                        'q' => 'Vous travailz quelle clé dans les URLs caisse ?',
                        'choices' => array(
                            'A' => 'roleattribut',
                            'B' => 'Adresse e-mail',
                            'C' => 'IMEI du téléphone',
                            'D' => 'Nom de la compagnie seule',
                        ),
                        'answer' => 'A',
                        'tip' => 'Identifiant opérationnel.',
                    ),
                    array(
                        'q' => 'En fin de vacation, la bonne pratique est :',
                        'choices' => array(
                            'A' => 'Partir sans arrêt',
                            'B' => 'Faire l\'arrêt de compte et informer le caissier',
                            'C' => 'Supprimer les dépenses',
                            'D' => 'Changer le mot de passe du caissier',
                        ),
                        'answer' => 'B',
                        'tip' => 'Clôture + handoff.',
                    ),
                ),
            ),
            '16' => array(
                'titre' => 'QCM fin de formation — Aide chef de guichet (rôle 16)',
                'duree' => '15 minutes',
                'bareme' => '1 point par bonne réponse — Total /8 — Seuil indicatif : 6/8',
                'questions' => array(
                    array(
                        'q' => 'Le rôle 16 est proche de :',
                        'choices' => array(
                            'A' => 'Caissier 4',
                            'B' => 'Chef guichet 5 (saisie)',
                            'C' => 'Admin 1',
                            'D' => 'Vendeur escale 17',
                        ),
                        'answer' => 'B',
                        'tip' => 'Saisie idopera / idop_dep.',
                    ),
                    array(
                        'q' => 'Vous pouvez valider les arrêts des autres chefs :',
                        'choices' => array(
                            'A' => 'Oui toujours',
                            'B' => 'Non — réservé caissier',
                            'C' => 'Oui le week-end',
                            'D' => 'Oui si solde = 0',
                        ),
                        'answer' => 'B',
                        'tip' => 'Pas de validation auto rôle 16.',
                    ),
                    array(
                        'q' => 'Avant dépense :',
                        'choices' => array(
                            'A' => 'Contrôler le solde ouvert',
                            'B' => 'Rien',
                            'C' => 'Appeler le passager',
                            'D' => 'Changer activeattrib du caissier',
                        ),
                        'answer' => 'A',
                        'tip' => 'Même règle que le chef.',
                    ),
                    array(
                        'q' => 'idop_dep à la création d\'une dépense vaut :',
                        'choices' => array(
                            'A' => 'Le roleattribut du saisisseur',
                            'B' => 'Toujours 0',
                            'C' => 'Le cpuser_id caissier',
                            'D' => 'L\'id de la gare',
                        ),
                        'answer' => 'A',
                        'tip' => 'Auteur = vous.',
                    ),
                    array(
                        'q' => 'En cas de doute sur un montant :',
                        'choices' => array(
                            'A' => 'Saisir quand même',
                            'B' => 'Demander au chef / responsable avant l\'arrêt',
                            'C' => 'Rejeter côté caissier soi-même',
                            'D' => 'Effacer le programme',
                        ),
                        'answer' => 'B',
                        'tip' => 'Escalade avant clôture.',
                    ),
                    array(
                        'q' => 'L\'arrêt de compte :',
                        'choices' => array(
                            'A' => 'Est inutile pour le 16',
                            'B' => 'Suit la même logique que le chef',
                            'C' => 'Crée un rôle 4',
                            'D' => 'Imprime les tickets',
                        ),
                        'answer' => 'B',
                        'tip' => 'Même filière saisie.',
                    ),
                    array(
                        'q' => 'operavalid sur une ligne validée désigne :',
                        'choices' => array(
                            'A' => 'Le chef',
                            'B' => 'Le caissier validateur',
                            'C' => 'Le bus',
                            'D' => 'Le quartier',
                        ),
                        'answer' => 'B',
                        'tip' => 'Validateur ≠ auteur.',
                    ),
                    array(
                        'q' => 'Un compte avec activer_role = 1 :',
                        'choices' => array(
                            'A' => 'Doit être utilisé',
                            'B' => 'Ne doit plus être utilisé',
                            'C' => 'Double le solde',
                            'D' => 'Active toutes les gares',
                        ),
                        'answer' => 'B',
                        'tip' => 'Attribution off.',
                    ),
                ),
            ),
            '6' => array(
                'titre' => 'QCM fin de formation — Vendeur (rôle 6)',
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
                            'C' => 'Changer de siège',
                            'D' => 'Supprimer les passagers',
                        ),
                        'answer' => 'B',
                        'tip' => 'Respecter le soft-lock / procédure.',
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
                            'B' => 'Le solde caissier adjoint',
                            'C' => 'operavalidad',
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
                        'q' => 'Le roleattribut sert à :',
                        'choices' => array(
                            'A' => 'Identifier votre session opérationnelle gare/rôle',
                            'B' => 'Nommer le bus',
                            'C' => 'Calculer le carburant',
                            'D' => 'Imprimer le logo',
                        ),
                        'answer' => 'A',
                        'tip' => 'Clé métier.',
                    ),
                    array(
                        'q' => 'En cas d\'erreur ticket :',
                        'choices' => array(
                            'A' => 'Ignorer',
                            'B' => 'Suivre la procédure gare (annulation / responsable)',
                            'C' => 'Créer un rôle 18',
                            'D' => 'Valider en caisse',
                        ),
                        'answer' => 'B',
                        'tip' => 'Escalade métier.',
                    ),
                ),
            ),
            '17' => array(
                'titre' => 'QCM fin de formation — Vendeur escale (rôle 17)',
                'duree' => '15 minutes',
                'bareme' => '1 point par bonne réponse — Total /8 — Seuil indicatif : 6/8',
                'questions' => array(
                    array(
                        'q' => 'La réimpression escale est en général filtrée sur :',
                        'choices' => array(
                            'A' => 'Toutes les gares du pays',
                            'B' => 'Vos opérations (iduseescal)',
                            'C' => 'Uniquement le caissier',
                            'D' => 'Les rôles 1 et 2 seulement sans exception',
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
                            'D' => 'Gare désactivée forcément',
                        ),
                        'answer' => 'B',
                        'tip' => 'Souvent normal.',
                    ),
                    array(
                        'q' => 'Après une réimpression, le droit peut :',
                        'choices' => array(
                            'A' => 'Rester illimité',
                            'B' => 'Être consommé (reimpr)',
                            'C' => 'Créer un caissier',
                            'D' => 'Changer idopera',
                        ),
                        'answer' => 'B',
                        'tip' => 'Flag réimpression.',
                    ),
                    array(
                        'q' => 'Avant de traiter un client escale, vérifier :',
                        'choices' => array(
                            'A' => 'Escale / sous-gare / programme',
                            'B' => 'operavalid du jour',
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
                            'B' => 'Escalader au chef de gare / responsable',
                            'C' => 'Désactiver le compte caissier',
                            'D' => 'Modifier activeattrib d\'un collègue',
                        ),
                        'answer' => 'B',
                        'tip' => 'Escalade.',
                    ),
                    array(
                        'q' => 'Le rôle 17 remplace-t-il le caissier ?',
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
                        'q' => 'Admin 1/2 en réimpression voit en général :',
                        'choices' => array(
                            'A' => 'Uniquement ses tickets',
                            'B' => 'Un scope gare plus large',
                            'C' => 'Rien',
                            'D' => 'Seulement les dépenses',
                        ),
                        'answer' => 'B',
                        'tip' => 'Scope élargi supervision.',
                    ),
                ),
            ),
            'general' => array(
                'titre' => 'QCM fin de formation — Vue d\'ensemble',
                'duree' => '15 minutes',
                'bareme' => '1 point par bonne réponse — Total /8 — Seuil indicatif : 6/8',
                'questions' => array(
                    array(
                        'q' => 'roleattribut représente :',
                        'choices' => array(
                            'A' => 'Le mot de passe',
                            'B' => 'L\'opérateur dans une gare + un rôle',
                            'C' => 'Le nom de l\'entreprise',
                            'D' => 'Le numéro de ticket',
                        ),
                        'answer' => 'B',
                        'tip' => 'Clé métier caisse / vente.',
                    ),
                    array(
                        'q' => 'activer_role = 0 signifie :',
                        'choices' => array(
                            'A' => 'Attribution utilisable',
                            'B' => 'Attribution coupée',
                            'C' => 'Solde négatif',
                            'D' => 'Impression interdite',
                        ),
                        'answer' => 'A',
                        'tip' => 'Flag inversé.',
                    ),
                    array(
                        'q' => 'Chaîne correcte :',
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
                        'q' => 'Rôle 4 vs 18 :',
                        'choices' => array(
                            'A' => 'Mêmes colonnes DB',
                            'B' => 'Pistes distinctes (avec / sans *ad)',
                            'C' => 'Identiques au rôle 6',
                            'D' => 'Sans importance',
                        ),
                        'answer' => 'B',
                        'tip' => 'Pistes séparées.',
                    ),
                    array(
                        'q' => 'Une gare active exclusive se voit par :',
                        'choices' => array(
                            'A' => 'activeattrib = 1',
                            'B' => 'is_conect = 0',
                            'C' => 'activer = 1',
                            'D' => 'montant = 0',
                        ),
                        'answer' => 'A',
                        'tip' => 'Une seule à la fois.',
                    ),
                    array(
                        'q' => 'idopera désigne :',
                        'choices' => array(
                            'A' => 'Toujours le caissier',
                            'B' => 'L\'auteur de la saisie',
                            'C' => 'Le bus',
                            'D' => 'Le quartier',
                        ),
                        'answer' => 'B',
                        'tip' => 'Auteur.',
                    ),
                    array(
                        'q' => 'Compte utilisateur activer = 1 :',
                        'choices' => array(
                            'A' => 'Login autorisé',
                            'B' => 'Compte désactivé',
                            'C' => 'Multi-gare forcé',
                            'D' => 'QCM réussi',
                        ),
                        'answer' => 'B',
                        'tip' => 'Inversé.',
                    ),
                    array(
                        'q' => 'En cas d\'anomalie solde :',
                        'choices' => array(
                            'A' => 'Inventer une recette',
                            'B' => 'Alerter support / responsable avec gare et roleattribut',
                            'C' => 'Supprimer la base',
                            'D' => 'Changer le rôle en 17',
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
