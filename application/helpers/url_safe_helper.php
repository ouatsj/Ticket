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
