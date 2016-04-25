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

use LayerShifter\TLDExtract\Exceptions;
use LayerShifter\TLDExtract\Interfaces\ResultInterface;
use LayerShifter\TLDExtract\Managers\Suffix;

/**
 * TLDExtract accurately extracts subdomain, domain and TLD components from URLs.
 *
 * @see Result for more information on the returned data structure.
 */
class Factory
{
    /**
     * Specifying $cacheFile will override the location of the cached suffix list.
     * Defaults to /path/to/TLDExtract/cache/.tld_set.
     *
     * @var string
     */
    private $cacheFile;
    /**
     * If $fetch is TRUE and no cached TLD set is found, the extractor will fetch the Public Suffix List live over
     * HTTP on first use.
     *
     * Set to FALSE to disable this behaviour.
     *
     * @var boolean
     */
    private $forceFetch = false;
    /**
     * @var string Specifying $resultClass will override object of result's class.
     */
    private $resultClass = '\\LayerShifter\\TLDExtract\\Result';
    /**
     * @var string Specifying $suffixFileUrl will override the URL from suffix list will be loaded.
     */
    private $suffixFileUrl = 'https://publicsuffix.org/list/effective_tld_names.dat';
    /**
     * @var Suffix Object of SuffixManager class.
     */
    private $suffixManager;

    /**
     * Sets $fetch param.
     *
     * @param boolean $forceFetch
     *
     * @return void
     */
    public function setForceFetch($forceFetch)
    {
        $this->forceFetch = $forceFetch;
    }

    /**
     * Sets cache filename.
     *
     * @param string $cacheFile Filename where cache will be stored
     *
     * @return void
     */
    public function setCacheFile($cacheFile)
    {
        $this->cacheFile = $cacheFile;
    }

    /**
     * Sets $resultClass param.
     *
     * @param string $resultClass
     *
     * @throws Exceptions\RuntimeException
     *
     * @return void
     */
    public function setResultClass($resultClass)
    {
        if (!class_exists($resultClass)) {
            throw new Exceptions\RuntimeException(sprintf('Class %s not exists', $resultClass));
        }

        $this->resultClass = $resultClass;
    }

    /**
     * Sets URL of suffix list.
     *
     * @param string $suffixFileUrl URL where stored valid suffix list
     *
     * @return void
     */
    public function setSuffixFileUrl($suffixFileUrl)
    {
        $this->suffixFileUrl = $suffixFileUrl;
    }

    /**
     * Factory constructor.
     */
    public function __construct()
    {
        $this->cacheFile = __DIR__ . '/../cache/.tld_set';
    }

    /**
     * Extract the sub domain, domain and gTLD/ccTLD components from a URL.
     *
     * @param string $url URL that will be extracted
     *
     * @throws Exceptions\IOException
     * @throws Exceptions\ListException
     *
     * @return ResultInterface
     */
    public function parse($url)
    {
        /*
         *
         * */

        if (null === $this->suffixManager) {
            $this->suffixManager = new Suffix($this->cacheFile, $this->suffixFileUrl, $this->forceFetch);
        }

        $host = $this->extractHost($url);
        list($domain, $tld) = $this->extract($host);

        // Check for IPv4 and IPv6 addresses.

        if ($tld === null && Helpers::isIp($host)) {
            return new $this->resultClass(null, $host, null);
        }

        $lastDot = strrpos($domain, '.');

        // If $lastDot not FALSE, there is subdomain in domain

        if ($lastDot !== false) {
            return new $this->resultClass(
                substr($domain, 0, $lastDot),
                substr($domain, $lastDot + 1),
                $tld
            );
        }

        return new $this->resultClass(null, $domain, $tld);
    }

    /**
     * Method that extracts the hostname from a URL.
     *
     * @param string $url Extracts host from URL
     *
     * @return string
     */
    private function extractHost($url)
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

    /**
     * Extracts host & TLD from input string. Based on algorithm described in https://publicsuffix.org/list/.
     *
     * @param string $host Host for extraction
     *
     * @return string[] An array with two items - the reg. domain (possibly with subdomains) and the public suffix.
     */
    public function extract($host)
    {
        $parts = explode('.', $host);

        for ($i = 0, $count = count($parts); $i < $count; $i++) {
            $maybeTld = implode('.', array_slice($parts, $i));
            $exceptionTld = '!' . $maybeTld;

            if (array_key_exists($exceptionTld, $this->tldList)) {
                return [
                    implode('.', array_slice($parts, 0, $i + 1)),
                    implode('.', array_slice($parts, $i + 1)),
                ];
            }

            $wildcardTld = '*.' . implode('.', array_slice($parts, $i + 1));

            if (array_key_exists($wildcardTld, $this->tldList) || array_key_exists($maybeTld, $this->tldList)) {
                return [
                    implode('.', array_slice($parts, 0, $i)),
                    $maybeTld,
                ];
            }
        }

        return [$host, null];
    }
}
