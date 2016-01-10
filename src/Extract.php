<?php
/**
 * PHP version 5.
 *
 * @category Classes
 *
 * @author   Alexander Fedyashov <a@fedyashov.com>
 * @author   W-Shadow <whiteshadow@w-shadow.com>
 * @license  MIT https://opensource.org/licenses/MIT
 *
 * @link     https://github.com/layershifter/TLDExtract
 */
namespace LayerShifter\TLDExtract;

use LayerShifter\TLDExtract\Exceptions\IOException;
use LayerShifter\TLDExtract\Exceptions\ListException;

/**
 * TLDExtract accurately extracts subdomain, domain and TLD components from URLs.
 *
 * @see Result for more information on the returned data structure.
 */
class Extract
{
    /**
     * If $fetch is TRUE and no cached TLD set is found, the extractor will fetch the Public Suffix List live over
     * HTTP on first use.
     *
     * Set to FALSE to disable this behaviour.
     *
     * @var bool
     */
    private static $fetch = false;
    /**
     * Specifying $cacheFile will override the location of the cached TLD set.
     * Defaults to /path/to/TLDExtract/cache/.tld_set.
     *
     * @var string
     */
    private static $cacheFile;

    /**
     * Specifying $suffixFileUrl will override the URL from suffix list will be loaded.
     *
     * @var string
     */
    private static $suffixFileUrl = 'https://publicsuffix.org/list/effective_tld_names.dat';

    /**
     * Specifying $resultClass will override object of result's class.
     *
     * @var string
     */
    private static $resultClass = '\\LayerShifter\\TLDExtract\\Result';

    /**
     * Sets $resultClass param.
     *
     * @param string $resultClass
     *
     * @throws \RuntimeException
     *
     * @return void
     */
    public static function setResultClass($resultClass)
    {
        if (!class_exists($resultClass)) {
            throw new \RuntimeException(sprintf('Class %s not exists', $resultClass));
        }

        self::$resultClass = $resultClass;
    }

    /**
     * Gets states of $fetch.
     *
     * @return bool
     */
    public static function isFetch()
    {
        return self::$fetch;
    }

    /**
     * Sets $fetch param.
     *
     * @param bool $fetch
     *
     * @return void
     */
    public static function setFetch($fetch)
    {
        self::$fetch = $fetch;
    }

    /**
     * Gets cache filename.
     *
     * @return string
     */
    public static function getCacheFile()
    {
        return self::$cacheFile;
    }

    /**
     * Sets cache filename.
     *
     * @param string $cacheFile Filename where cache will be stored
     *
     * @return void
     */
    public static function setCacheFile($cacheFile)
    {
        self::$cacheFile = $cacheFile;
    }

    /**
     * Gets URL of suffix list.
     *
     * @return string
     */
    public static function getSuffixFileUrl()
    {
        return self::$suffixFileUrl;
    }

    /**
     * Sets URL of suffix list.
     *
     * @param string $suffixFileUrl URL where stored valid suffix list
     *
     * @return Extract
     */
    public static function setSuffixFileUrl($suffixFileUrl)
    {
        self::$suffixFileUrl = $suffixFileUrl;
    }

    /**
     * Extract the subdomain, domain, and gTLD/ccTLD components from a URL.
     *
     * @param string $url URL that will be extracted
     *
     * @throws IOException
     * @throws ListException
     * @throws \RuntimeException
     *
     * @return Result
     */
    public static function get($url)
    {
        if (self::$cacheFile === null) {
            self::$cacheFile = __DIR__ . '/../cache/.tld_set';
        }

        $host = self::getHost($url);
        $extractor = SuffixExtractor::getInstance();

        list($domain, $tld) = $extractor->extract($host);

        // Check for IPv4 and IPv6 addresses.

        if ($tld === null && Helpers::isIp($host)) {
            return new self::$resultClass(null, $host, null);
        }

        $lastDot = strrpos($domain, '.');

        // If $lastDot not FALSE, there is subdomain in domain

        if ($lastDot !== false) {
            return new self::$resultClass(
                substr($domain, 0, $lastDot),
                substr($domain, $lastDot + 1),
                $tld
            );
        }

        return new self::$resultClass(null, $domain, $tld);
    }

    /**
     * Method for manually updating of TLD list's cache.
     *
     * @throws IOException
     * @throws ListException
     * @throws \RuntimeException
     *
     * @return bool
     */
    public static function updateCache()
    {
        $extractor = SuffixExtractor::getInstance();

        return $extractor->fetchTldList();
    }

    /**
     * Extract the hostname from a URL.
     *
     * @param string $url Extracts host from URL
     *
     * @return string
     */
    private static function getHost($url)
    {
        /*
         * Removes scheme and path
         * i.e. http://github.com to github.com
         * */
        $parts = explode('/', preg_replace('#^([a-zA-Z][a-zA-Z0-9+\-.]*:)?//#', '', $url), 2);
        $host = reset($parts);

        /*
         * Removes username from URL
         * i.e. user@github.com to github.com
         * */
        if (($position = strpos($host, '@')) !== false) {
            $host = substr($host, $position + 1);
        }

        /*
         * Remove ports from hosts, also check for IPv6 literals like
         * "[3ffe:2a00:100:7031::1]"
         *
         * @see http://www.ietf.org/rfc/rfc2732.txt
         * */
        $bracketPosition = strrpos($host, ']');

        if ($bracketPosition !== false && Helpers::startsWith($host, '[')) {
            // This is IPv6 literal

            return substr($host, 0, $bracketPosition + 1);
        }

        /*
         * This is either a normal hostname or an IPv4 address
         * Just remove the port.
         * */

        $parts = explode(':', $host);

        return reset($parts);
    }
}
