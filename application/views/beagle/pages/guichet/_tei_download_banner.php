<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
/**
 * Bannière téléchargement app Rakieta (Android APK).
 * Visible sur l'accueil guichet admin / superviseur.
 */
$tei_url = base_url('assets/tei/');
$tei_apk = base_url('assets/tei/app/rakieta.apk');
$tei_icon = base_url('assets/tei/icon-app.png');
?>
<style>
.tei-dl-banner {
  display: flex;
  align-items: center;
  gap: 14px;
  flex-wrap: wrap;
  background: #eef3fb;
  border: 1px solid #0b3d91;
  border-radius: 10px;
  padding: 12px 16px;
  margin: 0 0 12px 0;
}
.tei-dl-banner img {
  width: 48px;
  height: 48px;
  flex-shrink: 0;
  object-fit: contain;
  border-radius: 10px;
  background: #fff;
}
.tei-dl-banner .tei-dl-text {
  flex: 1 1 200px;
  min-width: 180px;
}
.tei-dl-banner .tei-dl-text strong {
  display: block;
  color: #0b3d91;
  font-size: 1rem;
}
.tei-dl-banner .tei-dl-text span {
  color: #5c6370;
  font-size: 0.88rem;
}
.tei-dl-banner .tei-dl-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}
.tei-dl-banner .btn-tei-dl {
  background: #0b3d91;
  color: #fff !important;
  border: none;
  font-weight: 600;
  padding: 10px 16px;
  border-radius: 8px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-decoration: none !important;
}
.tei-dl-banner .btn-tei-dl:hover {
  background: #082d6b;
  color: #fff !important;
}
.tei-dl-banner .btn-tei-page {
  background: #fff;
  color: #0b3d91 !important;
  border: 1px solid #0b3d91;
  font-weight: 600;
  padding: 10px 14px;
  border-radius: 8px;
  text-decoration: none !important;
}
</style>
<div class="tei-dl-banner" id="tei-download-banner">
  <img src="<?= html_escape($tei_icon); ?>" width="48" height="48" alt="Rakieta" />
  <div class="tei-dl-text">
    <strong>Rakieta (Android)</strong>
    <span>Application décideur — ouverture directe sur l’accueil, mises à jour automatiques.</span>
  </div>
  <div class="tei-dl-actions">
    <a class="btn-tei-dl" href="<?= html_escape($tei_apk); ?>" download="rakieta.apk">
      <i class="fas fa-download"></i> Télécharger Rakieta
    </a>
    <a class="btn-tei-page" href="<?= html_escape($tei_url); ?>" target="_blank" rel="noopener">
      Page &amp; guide
    </a>
  </div>
</div>
