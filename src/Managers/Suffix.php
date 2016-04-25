<?php
/**
 * TLDExtract: Domain parser library.
 *
 * @link      https://github.com/layershifter/TLDExtract
 *
 * @copyright Copyright (c) 2016, Alexander Fedyashov
 * @license   https://raw.githubusercontent.com/layershifter/TLDExtract/master/LICENSE MIT License
 */

namespace LayerShifter\TLDExtract\Managers;

use GuzzleHttp\Client;
use LayerShifter\TLDExtract\Exceptions\IOException;
use LayerShifter\TLDExtract\Exceptions\ListException;

/**
 * Manager class for working with suffix list.
 */
class Suffix
{
    /**
     * @var string Complete path to file with suffix cache.
     */
    private $cacheFile;
    /**
     * @var boolean If this option is set to TRUE, suffix list will be forced fetch from defined URL.
     */
    private $forceFetch;
    /**
     * @var string HTTP-link to remote file with suffix list.
     */
    private $suffixUrl;
    /**
     * @var array Array of suffixes from Public Suffix List.
     */
    private $suffixList = [];

    /**
     * Suffix constructor.
     *
     * @param string  $cacheFile  Complete path to file with suffix cache
     * @param string  $suffixUrl  HTTP-link to remote file with suffix list
     * @param boolean $forceFetch If this option is set to TRUE, suffix list will be forced fetch from defined URL
     *
     * @throws ListException
     * @throws IOException
     *
     * @TODO: Cache TTL
     */
    public function __construct($cacheFile, $suffixUrl, $forceFetch)
    {
        $this->cacheFile = $cacheFile;
        $this->suffixUrl = $suffixUrl;

        $this->forceFetch = $forceFetch;

        /*
         * If $fetch is TRUE of cache file not exists, try to fetch from remote URL.
         * */

        $needFetch = $this->forceFetch || !file_exists($this->cacheFile);

        if ($needFetch && $this->fetchSuffixList()) {
            return;
        }

        /*
         * Try load the public suffix list from the cache, if it's possible.
         * */

        if ($this->loadFromCache()) {
            return;
        }

        throw new ListException(
            sprintf('Cache file "%s" not exists and fetch from remote URL failed', $this->cacheFile)
        );
    }

    /**
     * Method that tries load file with suffixes from local cache.
     *
     * @return boolean Returns TRUE is cache was loaded successfully.
     */
    private function loadFromCache()
    {
        if (!file_exists($this->cacheFile)) {
            return false;
        }

        $suffixList = json_decode(
            file_get_contents($this->cacheFile),
            true
        );

        if (is_array($suffixList) && count($suffixList) > 0) {
            $this->suffixList = $suffixList;

            return true;
        }

        return false;
    }

    /**
     * Method that fetches suffix list from remote URL and parses it to array.
     *
     * @throws IOException
     *
     * @return boolean Returns TRUE if fetch was completed successfully
     *
     * @TODO Remove guzzle
     */
    public function fetchSuffixList()
    {
        $client = new Client();
        $response = $client->get($this->suffixUrl, ['verify' => false]);

        $body = $response->getBody()->getContents();

        if ($body === '') {
            return false;
        }

        if (!preg_match_all('@^(?P<suffix>[.*!]*\w[\S]*)@um', $body, $matches)) {
            return false;
        }

        if (count($matches['suffix']) === 0) {
            return false;
        }

        $this->suffixList = array_fill_keys($matches['suffix'], true);

        if (@file_put_contents($this->cacheFile, json_encode($this->suffixList))) {
            return true;
        }

        throw new IOException(
            sprintf('Cannot put list to cache file %s, check write rights on directory of file', $this->cacheFile),
            0,
            null,
            $this->cacheFile
        );
    }

    /**
     * Method that checks existence of provided suffix in suffix list.
     *
     * @param string $suffix Suffix that will be checked
     *
     * @return boolean Returns TRUE if suffix exists
     */
    public function isValid($suffix)
    {
        return array_key_exists($suffix, $this->suffixList);
    }
}
