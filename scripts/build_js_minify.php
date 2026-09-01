<?php
/**
 * Minification JS via terser (scripts/node_modules).
 *
 * @param string $content
 * @param string $root  Racine projet ticket
 * @return string
 */
function build_js_minify($content, $root)
{
    $terser = $root . '/scripts/node_modules/.bin/terser';

    if (!is_executable($terser)) {
        return $content;
    }

    $tmpIn = tempnam(sys_get_temp_dir(), 'jsbin_');
    $tmpOut = tempnam(sys_get_temp_dir(), 'jsbout_');

    if ($tmpIn === false || $tmpOut === false) {
        return $content;
    }

    file_put_contents($tmpIn, $content);

    $cmd = escapeshellarg($terser) . ' ' . escapeshellarg($tmpIn)
        . ' -c -m --comments false -o ' . escapeshellarg($tmpOut) . ' 2>&1';

    exec($cmd, $output, $code);

    $minified = ($code === 0 && is_readable($tmpOut)) ? file_get_contents($tmpOut) : $content;

    @unlink($tmpIn);
    @unlink($tmpOut);

    if ($code !== 0 && !empty($output)) {
        fwrite(STDERR, 'AVERTISSEMENT minify: ' . implode("\n", $output) . "\n");
    }

    return $minified !== false && $minified !== '' ? $minified : $content;
}
