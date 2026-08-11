<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * URLs CI avec segments sûrs (espaces, :, /, accents → pas de 403 Apache AH10411).
 */

/**
 * Encode chaque segment puis construit une site_url.
 *
 * @param mixed ...$segments Chaîne unique "a/b/c", liste de segments, ou un tableau
 * @return string
 */
function site_url_segments()
{
	$args = func_get_args();
	if (count($args) === 1 && is_array($args[0])) {
		$parts = $args[0];
	} elseif (count($args) === 1 && is_string($args[0]) && strpos($args[0], '/') !== false) {
		$parts = explode('/', $args[0]);
	} else {
		$parts = $args;
	}

	$encoded = array();
	foreach ($parts as $part) {
		if ($part === null || $part === false) {
			continue;
		}
		$encoded[] = rawurlencode((string) $part);
	}

	return site_url(implode('/', $encoded));
}

/**
 * Décode un segment d’URL (ex. 05%3A00%3A00) et affiche l’heure en 05h00.
 *
 * @param string $hr
 * @return string
 */
function url_segment_heure_affiche($hr)
{
	$hr = rawurldecode((string) $hr);
	$hr = trim($hr);
	if (preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?$/', $hr, $m)) {
		return sprintf('%02dh%s', (int) $m[1], $m[2]);
	}

	return $hr;
}

/**
 * URL image code-barres ticket (segment encodé).
 *
 * @param string $code tamponcod / code passager
 * @return string
 */
function ticket_barcode_url($code)
{
	return site_url('render/Barcode/' . rawurlencode((string) $code));
}

/**
 * Alias URL (compat vues qui appellent ticket_barcode_src).
 *
 * @param string $code
 * @return string
 */
function ticket_barcode_src($code)
{
	return ticket_barcode_url($code);
}

/**
 * Balise <img> code-barres pour tickets HTML / Epson.
 *
 * @param string $code
 * @param int $width
 * @param int $height
 * @return string
 */
function ticket_barcode_img($code, $width = 250, $height = 40)
{
	$src = htmlspecialchars(ticket_barcode_url($code), ENT_QUOTES, 'UTF-8');
	$w = (int) $width;
	$h = (int) $height;

	return '<img class="ticket-barcode" src="' . $src . '" alt="'
		. htmlspecialchars((string) $code, ENT_QUOTES, 'UTF-8')
		. '" width="' . $w . '" height="' . $h
		. '" style="display:block;max-width:100%;height:auto;">';
}
