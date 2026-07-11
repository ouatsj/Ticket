<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helpers de hachage des mots de passe.
 *
 * Stratégie A (migration transparente) :
 *  - Les nouveaux mots de passe sont hachés avec password_hash() (bcrypt).
 *  - Les anciens mots de passe (SHA-1, 40 caractères hexadécimaux) restent
 *    valides à la connexion et sont ré-hachés automatiquement en bcrypt
 *    lors du prochain login réussi.
 */

if ( ! function_exists('password_is_legacy_sha1'))
{
    /**
     * Détecte un ancien hash SHA-1 (40 caractères hexadécimaux).
     */
    function password_is_legacy_sha1($stored)
    {
        $stored = (string) $stored;
        return (bool) preg_match('/^[0-9a-f]{40}$/i', $stored);
    }
}

if ( ! function_exists('password_make'))
{
    /**
     * Produit un hash bcrypt du mot de passe en clair.
     */
    function password_make($plain)
    {
        return password_hash((string) $plain, PASSWORD_DEFAULT);
    }
}

if ( ! function_exists('password_check'))
{
    /**
     * Vérifie un mot de passe en clair contre le hash stocké,
     * qu'il soit au format bcrypt (nouveau) ou SHA-1 (ancien).
     */
    function password_check($plain, $stored)
    {
        $plain  = (string) $plain;
        $stored = (string) $stored;

        if ($stored === '') {
            return false;
        }

        if (password_is_legacy_sha1($stored)) {
            $digest = sha1($plain);
            if (hash_equals($stored, $digest)) {
                return true;
            }
            // Comptes corrompus par le formulaire d'édition qui renvoyait
            // le hash SHA-1 existant puis le re-hachait à l'enregistrement.
            return hash_equals($stored, sha1($digest));
        }

        return password_verify($plain, $stored);
    }
}

if ( ! function_exists('password_should_rehash'))
{
    /**
     * Indique si le hash stocké doit être régénéré :
     *  - ancien SHA-1, ou
     *  - bcrypt dont le coût a changé.
     */
    function password_should_rehash($stored)
    {
        $stored = (string) $stored;

        if (password_is_legacy_sha1($stored)) {
            return true;
        }

        return password_needs_rehash($stored, PASSWORD_DEFAULT);
    }
}
