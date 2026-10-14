#!/usr/bin/env bash
# Compile l'APK Rakieta Lab et le publie sous assets/tei/app/
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
export GIT_CONFIG_COUNT=1
export GIT_CONFIG_KEY_0=safe.directory
export GIT_CONFIG_VALUE_0=/opt/flutter
export PATH="/opt/flutter/bin:/opt/android-sdk/cmdline-tools/latest/bin:/opt/android-sdk/platform-tools:${PATH:-}"
export ANDROID_HOME="${ANDROID_HOME:-/opt/android-sdk}"
export ANDROID_SDK_ROOT="${ANDROID_SDK_ROOT:-$ANDROID_HOME}"
export JAVA_HOME="${JAVA_HOME:-/usr/lib/jvm/java-17-openjdk-amd64}"
API_BASE="${TEI_API_BASE:-https://essaiticket.rakietabus.com/tei-api/v1}"
BOOT_USER="${TEI_BOOTSTRAP_USER:-tei_decideur}"
BOOT_PASS="${TEI_BOOTSTRAP_PASS:-TeiDecideur!2026}"
PUBLIC_BASE="${TEI_PUBLIC_BASE:-https://essaiticket.rakietabus.com}"

cd "$ROOT/apps/tei_mobile"
flutter pub get
flutter build apk --release \
  --target-platform android-arm64 \
  --dart-define="TEI_API_BASE=$API_BASE" \
  --dart-define="TEI_BOOTSTRAP_USER=$BOOT_USER" \
  --dart-define="TEI_BOOTSTRAP_PASS=$BOOT_PASS"

mkdir -p "$ROOT/assets/tei/app"
cp -f build/app/outputs/flutter-apk/app-release.apk "$ROOT/assets/tei/app/rakieta-lab.apk"
# Compat anciens liens
cp -f "$ROOT/assets/tei/app/rakieta-lab.apk" "$ROOT/assets/tei/app/tei-decideur.apk"
sha256sum "$ROOT/assets/tei/app/rakieta-lab.apk" | tee "$ROOT/assets/tei/app/rakieta-lab.apk.sha256"
cp -f "$ROOT/assets/tei/app/rakieta-lab.apk.sha256" "$ROOT/assets/tei/app/tei-decideur.apk.sha256"

# Manifest de mise à jour (lu automatiquement par l'app)
VERSION_NAME="$(grep -E '^version:' pubspec.yaml | head -1 | awk '{print $2}' | cut -d+ -f1)"
VERSION_CODE="$(grep -E '^version:' pubspec.yaml | head -1 | awk '{print $2}' | cut -d+ -f2)"
cat > "$ROOT/assets/tei/app/version.json" <<EOF
{
  "versionName": "${VERSION_NAME}",
  "versionCode": ${VERSION_CODE},
  "apkUrl": "${PUBLIC_BASE}/assets/tei/app/rakieta-lab.apk",
  "notes": "Ouverture directe sur l'accueil, marque Rakieta Lab, détection automatique des mises à jour.",
  "publishedAt": "$(date -u +%Y-%m-%dT%H:%M:%SZ)"
}
EOF

echo "OK → ${PUBLIC_BASE}/assets/tei/"
echo "version.json → versionName=${VERSION_NAME} versionCode=${VERSION_CODE}"
