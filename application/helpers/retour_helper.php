<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Navigation RETOUR rapide : mémorise l'URL d'origine en session + history.back() côté JS.
 */

/**
 * @param string $url
 * @return bool
 */
function retour_is_internal_url($url)
{
    if (!is_string($url) || $url === '') {
        return false;
    }

    $CI =& get_instance();
    $base = rtrim(base_url(), '/');

    if ($base !== '' && strpos($url, $base) === 0) {
        return true;
    }

    return strpos($url, '/') === 0;
}

/**
 * @param string $url
 * @return string
 */
function retour_normalize_path($url)
{
    $path = parse_url($url, PHP_URL_PATH);

    return $path ? rtrim(strtolower($path), '/') : '';
}

/**
 * @param string $referer
 * @param string $current
 * @return bool
 */
function retour_is_usable_referer($referer, $current)
{
    if (!retour_is_internal_url($referer)) {
        return false;
    }

    if (retour_normalize_path($referer) === retour_normalize_path($current)) {
        return false;
    }

    if (preg_match('#/login(?:/|$)#i', $referer)) {
        return false;
    }

    return true;
}

/**
 * Mémorise l'URL de retour (référent interne ou repli) en session.
 *
 * @param string $fallback URL explicite si pas de référent utilisable
 * @return string URL à utiliser pour le bouton RETOUR
 */
function retour_url_remember($fallback)
{
    $CI =& get_instance();
    $fallback = trim((string) $fallback);
    $current = current_url();
    $referer = $CI->input->server('HTTP_REFERER');
    $url = $fallback;

    if ($referer && retour_is_usable_referer($referer, $current)) {
        $url = $referer;
    }

    if ($url !== '') {
        $CI->session->set_userdata('app_retour_url', $url);
    }

    return $url;
}

/**
 * @param string $fallback
 * @return string
 */
function retour_url($fallback = '')
{
    $CI =& get_instance();
    $referer = $CI->input->server('HTTP_REFERER');
    $current = current_url();

    if ($referer && retour_is_usable_referer($referer, $current)) {
        return $referer;
    }

    $stored = $CI->session->userdata('app_retour_url');

    if ($stored && retour_is_internal_url($stored)) {
        return $stored;
    }

    return $fallback;
}

/**
 * Mémorise le référent HTTP sur chaque chargement de page (layout).
 * Ne réécrit plus la session ici (évite le blocage sur le verrou PHP).
 */
function retour_page_remember()
{
    // Le bouton RETOUR utilise retour_url() + HTTP_REFERER en priorité.
}

/**
 * @param string $ekey
 * @param int|string $idengare
 * @param int|string $cpuser_id
 * @return string
 */
function retour_sousgare_url($ekey, $idengare, $cpuser_id)
{
    return site_url(
        'gares/' . $ekey . '/gTs/' . $idengare . '/sousgare/' . $cpuser_id
        . '/' . mdate('%d/%m/%Y', now('UTC'))
    );
}

/**
 * @param string $roleattribut
 * @param int|string $idsousgare
 * @return string
 */
function retour_caisse_url($ekey, $gexp_caiss, $roleattribut, $idsousgare)
{
    return site_url(
        'gares/' . $ekey . '/gTv/' . ($gexp_caiss ?: 0)
        . '/cais/' . $roleattribut . '/' . $idsousgare
        . '/' . mdate('%d/%m/%Y', now('UTC'))
    );
}

/**
 * @param string $ekey
 * @param string $roleattribut
 * @param int|string $idengare
 * @param int|string $idsousgare
 * @return string
 */
function retour_bagage_mobile_url($ekey, $roleattribut, $idengare, $idsousgare)
{
    return site_url("confirmation/bagagemobile/{$ekey}/{$roleattribut}/{$idengare}/{$idsousgare}");
}

/**
 * @param string $ekey
 * @param string $roleattribut
 * @param int|string $idengare
 * @param int|string $idsousgare
 * @return string
 */
function retour_bagage_suivi_url($ekey, $roleattribut, $idengare, $idsousgare)
{
    return site_url("confirmation/bagagesuivimobile/{$ekey}/{$roleattribut}/{$idengare}/{$idsousgare}");
}

/**
 * @param string $ekey
 * @param string $roleattribut
 * @param int|string $idengare
 * @param int|string $idsousgare
 * @return string
 */
function retour_bagage_nonfact_url($ekey, $roleattribut, $idengare, $idsousgare)
{
    return site_url("confirmation/bagagenonfact/{$ekey}/{$roleattribut}/{$idengare}/{$idsousgare}");
}

/**
 * @param string $ekey
 * @param string $roleattribut
 * @param int|string $idengare
 * @param int|string $idsousgare
 * @return string
 */
function retour_bagage_escal_url($ekey, $roleattribut, $idengare, $idsousgare)
{
    return site_url("confirmation/bagageescal/{$ekey}/{$roleattribut}/{$idengare}/{$idsousgare}");
}

/**
 * @param string $ekey
 * @param int|string $idengare
 * @param string $roleattribut
 * @param int|string $idsousgare
 * @return string
 */
function retour_guichet_url($ekey, $idengare, $roleattribut, $idsousgare)
{
    return site_url(
        'gares/' . $ekey . '/gTc/' . $idengare . '/compte/' . $roleattribut . '/' . $idsousgare
        . '/' . mdate('%d/%m/%Y', now('UTC'))
    );
}
