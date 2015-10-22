<?php
/**
 * Helpers.php
 *
 * @author Alexander Fedyashov <a@fedyashov.com>
 * @author Salman A <http://stackoverflow.com/users/87015/salman-a>
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

    /**
     * Check if the input is a valid IP address.
     * Recognizes both IPv4 and IPv6 addresses.
     *
     * @param string $host
     * @return bool
     */
    public static function isIp($host)
    {
        // Strip the wrapping square brackets from IPv6 addresses

        if (Helpers::startsWith($host, '[') && Helpers::endsWith($host, ']')) {
            $host = substr($host, 1, -1);
        }

        return (bool)filter_var($host, FILTER_VALIDATE_IP);
    }
}
