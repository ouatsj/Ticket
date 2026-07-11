<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('ticket_impression_prix')) {
    /**
     * Prix affiché sur le ticket : prix réellement encaissé (prixvente), pas le tarif catalogue.
     */
    function ticket_impression_prix($row, $fallback = 0)
    {
        if (!$row || !is_object($row)) {
            return $fallback;
        }
        if (isset($row->prixvente) && $row->prixvente !== null && $row->prixvente !== '') {
            return (float) $row->prixvente;
        }
        if (isset($row->prix) && $row->prix !== null && $row->prix !== '') {
            return (float) $row->prix;
        }
        return $fallback;
    }
}

if (!function_exists('ticket_impression_prix_row')) {
    function ticket_impression_prix_row($row)
    {
        if (!$row || !is_object($row)) {
            return $row;
        }
        if (isset($row->prixvente) && $row->prixvente !== null && $row->prixvente !== '') {
            $row->prix = (float) $row->prixvente;
        }
        return $row;
    }
}

if (!function_exists('ticket_impression_prix_rows')) {
    function ticket_impression_prix_rows($rows)
    {
        if (!$rows) {
            return $rows;
        }
        foreach ($rows as $row) {
            ticket_impression_prix_row($row);
        }
        return $rows;
    }
}
