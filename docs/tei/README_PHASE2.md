# Transport Executive Intelligence — Phase 2

## Livrables

| Élément | Chemin |
|--------|--------|
| API séparée (Node) | `services/tei_api/` |
| Spec OpenAPI | `docs/tei/openapi-v1.yaml` |
| Migration SQL | `scripts/db/migrate_tei_phase2_additive.sql` |
| App Flutter | `apps/tei_mobile/` |
| Checklist FCM | `docs/tei/FCM_PROVISIONING.md` |

## Décisions

- Rôle **Decideur** (`user_roles.id_rols = 21`) — pas de guichet web.
- Auth **uniquement dans l’app** mobile (Bearer + refresh).
- CA dashboard = **ventes** ; **écart caisse** = ventes − `recette`.
- Partition multi-cie = `gare_dest.id_compaga`.

## Démarrer l’API (staging)

```bash
cd /var/www/essaiticket/services/tei_api
cp .env.example .env
# Remplir DB_* depuis application/config/database.php (ne pas committer .env)
# Générer JWT_* : openssl rand -hex 32
export PATH="/tmp/node-v20.19.0-linux-x64/bin:$PATH"   # si besoin
npm install
npm run migrate
npm start   # http://127.0.0.1:8091/v1/health
```

## Compte décideur de test (staging)

```bash
php7.2 scripts/db/seed_tei_decideur_test.php
```

Identifiants (staging uniquement) :

- utilisateur : `tei_decideur`
- mot de passe : `TeiDecideur!2026`
- ekey : `1000` (RAKIETA)
- fichier local : `services/tei_api/.decideur_test_credentials` (non versionné)

## Créer un autre décideur

1. Compte `compte_user` + attribution `userole = 21` (actif, `activer_role = 0`).
2. Périmètre :

```sql
INSERT INTO tei_decideur_scope (cpuser_id, ekey, cle_compagnie, actif)
VALUES (<cpuser_id>, <ekey>, NULL, 1);  -- NULL = toutes compagnies de ekey
```

## Flutter

Flutter SDK non installé sur ce serveur. Sur une machine de build :

```bash
cd apps/tei_mobile
flutter pub get
flutter run --dart-define=TEI_API_BASE=https://votre-api/v1
```

## Phase 3/4 (fait)

- Navigation Flutter : Accueil, Départs, Finances, Analyses (placeholder), Profil
- API `GET /v1/departures` (remplissage + alertes 80/90/100)
- API `GET /v1/finance/sales` (billets, bagages, courrier, écart caisse, par cie/ligne)

## Non fait (phases suivantes)

- Conseiller intelligent (Phase 6)
- Moteur d’alertes push + FCM (Phase 5)
- Page admin distribution APK
- Tests automatisés complets

## Téléchargement mobile (Phase 8 partielle)

- Page : https://essaiticket.rakietabus.com/assets/tei/
- APK Android : https://essaiticket.rakietabus.com/assets/tei/app/tei-decideur.apk
- Doc : https://essaiticket.rakietabus.com/assets/tei/INSTALLATION.md
- Rebuild : `bash scripts/tei_build_apk.sh`
