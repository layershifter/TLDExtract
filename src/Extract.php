<?php
/**
 * TLDExtract: Library for extraction of domain parts e.g. TLD. Domain parser that uses Public Suffix List.
 *
 * @link      https://github.com/layershifter/TLDExtract
 *
 * @copyright Copyright (c) 2016, Alexander Fedyashov
 * @license   https://raw.githubusercontent.com/layershifter/TLDExtract/master/LICENSE Apache 2.0 License
 */

namespace LayerShifter\TLDExtract;

use LayerShifter\TLDDatabase\Store;
use LayerShifter\TLDExtract\Exceptions;
use LayerShifter\TLDSupport\Helpers\Arr;
use LayerShifter\TLDSupport\Helpers\IP;
use LayerShifter\TLDSupport\Helpers\Str;

/**
 * Extract class accurately extracts subdomain, domain and TLD components from URLs.
 *
 * @see Result for more information on the returned data structure.
 */
class Extract
{

    /**
     * @const string RFC 3986 compliant scheme regex pattern.
     *
     * @see   https://tools.ietf.org/html/rfc3986#section-3.1
     */
    const SCHEMA_PATTERN = '#^([a-zA-Z][a-zA-Z0-9+\-.]*:)?//#';

    private $allowPrivateSuffixes;
    private $resultClassName;
    private $suffixStore;

    /**
     * Factory constructor.
     *
     * @param null|string $databaseFile
     * @param null|string $resultClassName
     * @param boolean     $allowPrivateSuffixes
     */
    public function __construct($databaseFile = null, $resultClassName = null, $allowPrivateSuffixes = false)
    {
        $this->allowPrivateSuffixes = (bool)$allowPrivateSuffixes;
        $this->resultClassName = Result::class;

        // TODO: Checks

        $this->suffixStore = new Store($databaseFile);
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
        $hostname = $this->extractHostname($url);

        // If received hostname is valid IP address, result will be formed from it.

        if (IP::isValid($hostname)) {
            return new $this->resultClassName(null, $hostname, null);
        }

        return new $this->resultClassName(...$this->extract($hostname));
    }

    /**
     * Method that extracts the hostname or IP address from a URL.
     *
     * @param string $url URL for extraction
     *
     * @return null|string Hostname or IP address
     */
    private function extractHostname($url)
    {
        $url = trim(Str::lower($url));

        // Removes scheme and path i.e. http://github.com to github.com.

        $parts = explode('/', preg_replace(self::SCHEMA_PATTERN, '', $url), 2);
        $hostname = Arr::first($parts);

        // Removes username from URL i.e. user@github.com to github.com.

        $hostname = Arr::last(explode('@', $hostname));

        // Remove ports from hosts, also check for IPv6 literals like "[3ffe:2a00:100:7031::1]".
        //
        // @see http://www.ietf.org/rfc/rfc2732.txt

        if (Str::startsWith($hostname, '[') && Str::endsWith($hostname, ']')) {
            return Str::substr($hostname, 1, -1);
        }

        // This is either a normal hostname or an IPv4 address, just remove the port.

        $hostname = Arr::first(explode(':', $hostname));

        // If string is empty, null will be returned.

        return '' === $hostname ? null : $hostname;
    }

    /**
     * Extracts host & TLD from input string. Based on algorithm described in https://publicsuffix.org/list/.
     *
     * @param string $hostname Hostname for extraction
     *
     * @return array|string[] An array with two items - the reg. domain (possibly with subdomains) and the public
     *                        suffix.
     */
    public function extract($hostname)
    {
        $suffix = $this->extractSuffix($hostname);

        if ($suffix === $hostname) {
            return [null, $hostname, null];
        }

        if (null !== $suffix) {
            $hostname = Str::substr($hostname, 0, -Str::length($suffix) - 1);
        }

        $lastDot = Str::strrpos($hostname, '.');

        if (false === $lastDot) {
            return [null, $hostname, $suffix];
        }

        $subDomain = Str::substr($hostname, 0, $lastDot);
        $domain = Str::substr($hostname, $lastDot + 1);

        return [
            $subDomain,
            $domain,
            $suffix
        ];
    }

    private function extractSuffix($hostname)
    {
        // If hostname starts with dot, it's invalid.

        if (Str::startsWith($hostname, '.')) {
            return null;
        }

        // If a single label domain makes it this far (e.g., localhost, foo, etc.), this stops it from incorrectly
        // being set as the public suffix.

        if (Str::strpos($hostname, '.') === false) {
            return null;
        }

        // If domain is in punycode, it will be converted to IDN.

        $isPunycoded = Str::strpos($hostname, 'xn--') !== false;

        if ($isPunycoded) {
            $hostname = idn_to_utf8($hostname);
        }

        $suffix = null;
        $parts = explode('.', $hostname);

        for ($i = 0, $count = count($parts); $i < $count; $i++) {
            $possibleSuffix = implode('.', array_slice($parts, $i));

            $exceptionSuffix = '!' . $possibleSuffix;

            if ($this->suffixStore->isExists($exceptionSuffix)) {
                $suffix = implode('.', array_slice($parts, $i + 1));

                break;
            }

            $wildcardTld = '*.' . implode('.', array_slice($parts, $i + 1));

            if ($this->suffixStore->isExists($possibleSuffix) || $this->suffixStore->isExists($wildcardTld)) {
                $suffix = $possibleSuffix;

                break;
            }
        }

        if (null === $suffix) {
            $suffix = Str::substr($hostname, Str::strrpos($hostname, '.') + 1);
        }

        if (!$isPunycoded) {
            return $suffix;
        }

        return idn_to_ascii($suffix);

//        $suffix = implode('.', array_filter($publicSuffix, 'strlen'));
//        return $this->denormalize($suffix);

        //   $suffix = implode('.', $suffixParts);

//        if ($this->suffixStore->isExists('*.' . $suffix)) {
//            return null;
//        }

        return $suffix;
    }
}
