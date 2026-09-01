<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cache fichier léger pour données de référence (évite timeouts sur pages lourdes).
 */

/**
 * Lit l'entrée de cache brute et valide (expiration + structure).
 * Distingue une valeur stockée `null` d'une absence de cache.
 *
 * @param string $key
 * @return array|false ['expires' => int, 'value' => mixed] ou false si absent/expiré
 */
function app_cache_entry($key)
{
    $path = APPPATH . 'cache/data/' . md5($key) . '.cache';

    if (!is_file($path)) {
        return false;
    }

    $raw = @file_get_contents($path);
    if ($raw === false) {
        return false;
    }

    $data = @unserialize($raw);
    if (!is_array($data)
        || !isset($data['expires'])
        || !array_key_exists('value', $data)
        || $data['expires'] < time()
    ) {
        @unlink($path);

        return false;
    }

    return $data;
}

/**
 * @param string $key
 * @return mixed|null
 */
function app_cache_get($key)
{
    $entry = app_cache_entry($key);

    return $entry === false ? null : $entry['value'];
}

/**
 * @param string $key
 * @param mixed $value
 * @param int $ttl secondes
 */
function app_cache_set($key, $value, $ttl = 300)
{
    $dir = APPPATH . 'cache/data';

    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    @file_put_contents(
        $dir . '/' . md5($key) . '.cache',
        serialize(array(
            'expires' => time() + (int) $ttl,
            'value' => $value,
        )),
        LOCK_EX
    );
}

/**
 * @param string $key
 * @param int $ttl
 * @param callable $callback
 * @return mixed
 */
function app_cache_remember($key, $ttl, $callback)
{
    $entry = app_cache_entry($key);

    if ($entry !== false) {
        return $entry['value'];
    }

    $value = call_user_func($callback);
    app_cache_set($key, $value, $ttl);

    return $value;
}

/**
 * @param string $key
 */
function app_cache_delete($key)
{
    $path = APPPATH . 'cache/data/' . md5($key) . '.cache';
    if (is_file($path)) {
        @unlink($path);
    }
}
