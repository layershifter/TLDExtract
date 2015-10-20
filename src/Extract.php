<?php

namespace LayerShifter\TLDExtract;

/**
 * TLDExtract accurately extracts subdomain, domain and TLD components from URLs.
 *
 * @see TLDExtractResult for more information on the returned data structure.
 */
class Extract
{

    /**
     * If $fetch is TRUE (the default) and no cached TLD set is found, the extractor will
     * fetch the Public Suffix List live over HTTP on first use. Set to FALSE to disable
     * this behaviour. Either way, if the TLD set can't be read, the extractor will fall
     * back to the included snapshot.
     *
     * @var bool
     */
    private static $fetch = true;
    /**
     * Specifying $cacheFile will override the location of the cached TLD set.
     * Defaults to /path/to/tldextract/cache/.tld_set.
     *
     * @var string
     */
    private static $cacheFile = __DIR__ . '/../cache/.tld_set';

    /**
     * Specifying $suffixFileUrl will override the URL from suffix list will be loaded.
     *
     * @var string
     */
    private static $suffixFileUrl = 'https://publicsuffix.org/list/effective_tld_names.dat';

    /**
     * @return boolean
     */
    public static function isFetch()
    {
        return self::$fetch;
    }

    /**
     * @param boolean $fetch
     */
    public static function setFetch($fetch)
    {
        self::$fetch = $fetch;
    }

    /**
     * @return string
     */
    public static function getCacheFile()
    {
        return self::$cacheFile;
    }

    /**
     * @param string $cacheFile
     */
    public static function setCacheFile($cacheFile)
    {
        self::$cacheFile = $cacheFile;
    }

    /**
     * @return string
     */
    public static function getSuffixFileUrl()
    {
        return self::$suffixFileUrl;
    }

    /**
     * @param string $suffixFileUrl
     * @return Extract
     */
    public static function setSuffixFileUrl($suffixFileUrl)
    {
        self::$suffixFileUrl = $suffixFileUrl;
    }

    /**
     * Extract the subdomain, domain, and gTLD/ccTLD components from a URL.
     *
     * @param string $url
     * @return Result
     */
    public static function extract($url)
    {
        $host = self::getHost($url);
        $extractor = SuffixExtractor::getInstance();

        list($registeredDomain, $tld) = $extractor->extract($host);
        //Check for IPv4 and IPv6 addresses.
        if (empty($tld) && $this->isIp($host)) {
            return new Result('', $host, '');
        }
        $lastDot = strrpos($registeredDomain, '.');
        if ($lastDot !== false) {
            $subdomain = substr($registeredDomain, 0, $lastDot);
            $domain = substr($registeredDomain, $lastDot + 1);
        } else {
            $subdomain = '';
            $domain = $registeredDomain;
        }
        return new Result($subdomain, $domain, $tld);
    }


    /**
     * Extract the hostname from a URL.
     *
     * @param string $url
     * @return string
     */
    private static function getHost($url)
    {
        /*
         * Removes scheme and path
         * i.e. http://github.com to github.com
         * */
        $parts = explode(
            '/',
            preg_replace('#^([a-zA-Z][a-zA-Z0-9+\-.]*:)?//#', '', $url),
            2);

        $host = reset($parts);

        /*
         * Removes username from URL
         * i.e. user@github.com to github.com
         * */
        if (($position = strpos($host, '@')) !== false) {
            $host = substr($host, $position + 1);
        }

        /*
         * Remove ports from hosts, also check for IPv6 literals like "[3ffe:2a00:100:7031::1]"
         * @see http://www.ietf.org/rfc/rfc2732.txt
         * */
        $closingBracketPosition = strrpos($host, ']');

        if (Helpers::startsWith($host, '[') && $closingBracketPosition !== false) {
            // This is IPv6 literal

            return substr($host, 0, $closingBracketPosition + 1);
        }

        // This is either a normal hostname or an IPv4 address
        // Just remove the port.

        $parts = explode(':', $host);
        return reset($parts);
    }

    /**
     * Check if the input is a valid IP address.
     * Recognizes both IPv4 and IPv6 addresses.
     *
     * @param string $host
     * @return bool
     */
    private function isIp($host)
    {
        //Strip the wrapping square brackets from IPv6 addresses
        if ($this->startsWith($host, '[') && $this->endsWith($host, ']')) {
            $host = substr($host, 1, -1);
        }
        return (bool)filter_var($host, FILTER_VALIDATE_IP);
    }

    private function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        return (substr($haystack, -$length) === $needle);
    }
}