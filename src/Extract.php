<?php
/**
 * PHP version 5
 *
 * @category Classes
 * @package  LayerShifter/TLDExtract
 * @author   Alexander Fedyashov <a@fedyashov.com>
 * @author   W-Shadow <whiteshadow@w-shadow.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/layershifter/TLDExtract
 */

namespace LayerShifter\TLDExtract;

/**
 * TLDExtract accurately extracts subdomain, domain and TLD components from URLs.
 *
 * @category Classes
 * @package  LayerShifter/TLDExtract
 * @author   Alexander Fedyashov <a@fedyashov.com>
 * @author   W-Shadow <whiteshadow@w-shadow.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/layershifter/TLDExtract
 * @see      Result for more information on the returned data structure.
 */
class Extract
{

    /**
     * If $fetch is TRUE (the default) and no cached TLD set is found, the
     * extractor will fetch the Public Suffix List live over HTTP on first use.
     * Set to FALSE to disable this behaviour.
     *
     * @var bool
     */
    private static $_fetch = true;
    /**
     * Specifying $cacheFile will override the location of the cached TLD set.
     * Defaults to /path/to/tldextract/cache/.tld_set.
     *
     * @var string
     */
    private static $_cacheFile = __DIR__ . '/../cache/.tld_set';

    /**
     * Specifying $suffixFileUrl will override the URL from suffix list will be
     * loaded.
     *
     * @var string
     */
    private static $_suffixFileUrl = 'https://publicsuffix.org/list/effective_tld_names.dat';

    /**
     * Gets states of $_fetch.
     *
     * @return boolean
     */
    public static function isFetch()
    {
        return self::$_fetch;
    }

    /**
     * Sets $_fetch param.
     *
     * @param boolean $_fetch @see $_fetch
     *
     * @return void
     */
    public static function setFetch($_fetch)
    {
        self::$_fetch = $_fetch;
    }

    /**
     * Gets cache filename.
     *
     * @return string
     */
    public static function getCacheFile()
    {
        return self::$_cacheFile;
    }

    /**
     * Sets cache filename.
     *
     * @param string $_cacheFile Filename where cache will be stored
     *
     * @return void
     */
    public static function setCacheFile($_cacheFile)
    {
        self::$_cacheFile = $_cacheFile;
    }

    /**
     * Gets URL of suffix list
     *
     * @return string
     */
    public static function getSuffixFileUrl()
    {
        return self::$_suffixFileUrl;
    }

    /**
     * Sets URL of suffix list
     *
     * @param string $_suffixFileUrl URL where stored valid suffix list
     *
     * @return Extract
     */
    public static function setSuffixFileUrl($_suffixFileUrl)
    {
        self::$_suffixFileUrl = $_suffixFileUrl;
    }

    /**
     * Extract the subdomain, domain, and gTLD/ccTLD components from a URL.
     *
     * @param string $url URL that will be extracted
     *
     * @return Result
     */
    public static function get($url)
    {
        $host = self::_getHost($url);
        $extractor = SuffixExtractor::getInstance();

        list($domain, $tld) = $extractor->extract($host);

        // Check for IPv4 and IPv6 addresses.

        if (empty($tld) && Helpers::isIp($host)) {
            return new Result($host);
        }

        $lastDot = strrpos($domain, '.');

        // If $lastDot not FALSE, there is subdomain in domain

        if ($lastDot !== false) {
            return new Result(
                substr($domain, 0, $lastDot),
                substr($domain, $lastDot + 1),
                $tld
            );
        }

        return new Result(
            $domain,
            $tld
        );
    }

    /**
     * Extract the hostname from a URL.
     *
     * @param string $url Extracts host from URL
     *
     * @return string
     */
    private static function _getHost($url)
    {
        /*
         * Removes scheme and path
         * i.e. http://github.com to github.com
         * */
        $parts = explode(
            '/', preg_replace('#^([a-zA-Z][a-zA-Z0-9+\-.]*:)?//#', '', $url), 2
        );

        $host = reset($parts);

        /*
         * Removes username from URL
         * i.e. user@github.com to github.com
         * */
        if (($position = strpos($host, '@')) !== false) {
            $host = substr($host, $position + 1);
        }

        /**
         * Remove ports from hosts, also check for IPv6 literals like
         * "[3ffe:2a00:100:7031::1]"
         *
         * @see http://www.ietf.org/rfc/rfc2732.txt
         * */
        $closingBracketPosition = strrpos($host, ']');

        if (Helpers::startsWith($host, '[') && $closingBracketPosition !== false) {
            // This is IPv6 literal

            return substr($host, 0, $closingBracketPosition + 1);
        }

        /*
         * This is either a normal hostname or an IPv4 address
         * Just remove the port.
         * */

        $parts = explode(':', $host);
        return reset($parts);
    }
}
