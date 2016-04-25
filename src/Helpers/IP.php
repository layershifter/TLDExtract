<?php
/**
 * TLDExtract: Domain parser library.
 *
 * @link      https://github.com/layershifter/TLDExtract
 *
 * @copyright Copyright (c) 2016, Alexander Fedyashov
 * @license   https://raw.githubusercontent.com/layershifter/TLDExtract/master/LICENSE MIT License
 */

namespace LayerShifter\TLDExtract\Helpers;

class IP
{

    /**
     * Check if the input is a valid IP address. Recognizes both IPv4 and IPv6 addresses.
     *
     * @param string $hostname Hostname that will be checked
     *
     * @return boolean
     */
    public static function isValid($hostname)
    {
        // Strip the wrapping square brackets from IPv6 addresses

        if (Str::startsWith($hostname, '[') && Str::endsWith($hostname, ']')) {
            $hostname = substr($hostname, 1, -1);
        }

        return (bool)filter_var($hostname, FILTER_VALIDATE_IP);
    }
}
