# TEI — Phases 5 (FCM) et 6 (Conseiller)

## Phase 5 — Alertes et push

| Élément | Détail |
|--------|--------|
| Scan | `POST /v1/alerts/scan` ou `npm run scan:alerts -- 1000 2026-09-13` |
| Anti-doublon | `tei_alert_dedupe` (une émission par `alert_key` / seuil) |
| Centre | `GET /v1/notifications?ekey=&categorie=&niveau=` |
| Préférences | `GET/PUT /v1/notifications/prefs` |
| Devices | `POST /v1/devices/register` |

### Firebase

1. Suivre `docs/tei/FCM_PROVISIONING.md`.
2. Placer le JSON service account hors git.
3. Dans `services/tei_api/.env` :

```
FIREBASE_SERVICE_ACCOUNT=/chemin/vers/service-account.json
FCM_MOCK=0
```

4. `npm install` (installe `firebase-admin` si disponible).

Sans projet Firebase : `FCM_MOCK=1` — les alertes sont **enregistrées** et listables ; les push sont journalisés.

### Cron recommandé

```bash
*/5 * * * * cd /var/www/essaiticket/services/tei_api && /path/to/node scripts/scan_alerts.js 1000 >> /var/log/tei-alerts.log 2>&1
```

## Phase 6 — Conseiller

`GET /v1/insights?ekey=1000&date=2026-09-13`

Règles déterministes :

1. Départs complets / ≥ seuil prioritaire  
2. Plusieurs départs sous objectif de remplissage  
3. Baisse CA vs même jour J−7  
4. Écart ventes ↔ caisse ≥ 20 %  
5. Colis sans recettes courrier  
6. Top compagnie (info)

Chaque conseil contient : problème, chiffres, période, priorité, action, lien écran. **Aucun chiffre inventé.** Aucune action opérationnelle automatique.
