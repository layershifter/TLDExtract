<?php
/**
 * Helpers.php
 *
 * @author Alexander Fedyashov <af@e42.guru>
 */

namespace LayerShifter\TLDExtract;


class Helpers
{

    /**
     * Implementation startsWith() from http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
     *
     * @param string $haystack
     * @param string $needle
     *
     * @return bool
     */
    public static function startsWith($haystack, $needle)
    {
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }
}