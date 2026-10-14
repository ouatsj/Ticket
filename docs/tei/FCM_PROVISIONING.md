# Provisionnement Firebase / FCM

À faire **avant la Phase 5** (alertes push). Les secrets restent sur `tei_api`, jamais dans le dépôt Flutter versionné.

## Étapes

1. Créer un projet Firebase nommé par ex. `transport-executive-intelligence`.
2. Ajouter une app **Android** (package à fixer, ex. `com.rakieta.tei`).
3. Ajouter une app **iOS** (bundle id à fixer).
4. Télécharger `google-services.json` et `GoogleService-Info.plist` pour la machine de build mobile (hors git ou via CI secrets).
5. Activer **Cloud Messaging**.
6. Créer un **compte de service** Firebase Admin → JSON → stocker uniquement sur le serveur API (`FIREBASE_SERVICE_ACCOUNT` dans `.env`).
7. Lier une **clé APNs** Apple au projet Firebase (iOS).
8. Tester `POST /v1/devices/register` puis un envoi de test depuis la console Firebase.

## Ciblage

- Un token FCM = une ligne `tei_devices`.
- L’API filtrera les destinataires via `tei_decideur_scope` (ekey / compagnie) et `tei_notification_prefs`.
- Anti-doublon : table `tei_alert_dedupe` (une émission par `alert_key` / seuil).
- Historique : `tei_alerts_emises`.

## Mode sans Firebase (staging)

```
FCM_MOCK=1
```

Les alertes sont enregistrées et listables via `GET /v1/notifications`. Les push sont journalisés dans les logs API (`[FCM mock]`).

## Seuils

Table `tei_alert_thresholds` : défaut global 80 % / 90 %.  
Sur staging ekey 1000, des seuils de démo (10 % / 15 %) peuvent être présents pour tester le moteur sur des départs peu remplis — à retirer ou relever avant prod.
