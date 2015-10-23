<?php
/**
     * PHP version 5
     *
     * @category Classes
     * @package  LayerShifter/TLDExtract
     * @author   Alexander Fedyashov <a@fedyashov.com>
     * @license  MIT https://opensource.org/licenses/MIT
     * @link     https://github.com/layershifter/TLDExtract
     */

namespace LayerShifter\TLDExtract;


/**
 * Includes static functions (helpers) for package
 *
 * @category Classes
 * @package  LayerShifter/TLDExtract
 * @author   Alexander Fedyashov <a@fedyashov.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/layershifter/TLDExtract
 */
class Helpers
{

    /**
     * Implementation startsWith()
     *
     * @param string $haystack String where $needle will be searched
     * @param string $needle   String that will be searched
     *
     * @return bool
     */
    public static function startsWith($haystack, $needle)
    {
        return $needle === ""
            || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

    /**
     * Implementation endsWith() from @url
     *
     * @param string $haystack String where $needle will be searched
     * @param string $needle   String that will be searched
     *
     * @return bool
     */
    public static function endsWith($haystack, $needle)
    {
        return $needle === "" || (
            ($temp = strlen($haystack) - strlen($needle)) >= 0
            && strpos($haystack, $needle, $temp) !== false);
    }

    /**
     * Check if the input is a valid IP address.
     * Recognizes both IPv4 and IPv6 addresses.
     *
     * @param string $host Host that will be checked
     *
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
