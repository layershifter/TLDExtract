<?php
/**
 * Helpers.php
 *
 * @author Alexander Fedyashov <af@e42.guru>
 */

namespace LayerShifter\TLDExtract;


/**
 * Class Helpers
 * @package LayerShifter\TLDExtract
 *
 * Includes static functions (helpers) for package
 */
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

    /**
     * Implementation endsWith() from http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
     *
     * @param string $haystack
     * @param string $needle
     *
     * @return bool
     */
    public static function endsWith($haystack, $needle)
    {
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }
}