<?php
/**
 * TldExtractor.php
 *
 * @author Alexander Fedyashov <af@e42.guru>
 */

namespace LayerShifter\TLDExtract;


use GuzzleHttp\Client;
use LayerShifter\TLDExtract\Exceptions\IOException;
use LayerShifter\TLDExtract\Exceptions\ListException;

/**
 * Class SuffixExtractor
 * @package LayerShifter\TLDExtract
 *
 * This class splits domain names into the registered domain and public suffix components
 * using the TLD rule set from the Public Suffix List project.
 */
class SuffixExtractor
{
    /**
     * @var SuffixExtractor
     */
    private static $instance;

    /**
     * @var array The TLD set from Public Suffix List
     */
    private $tldList = [];

    /**
     * SuffixExtractor constructor, runs actions for filling list of TLDs
     *
     * @throws IOException
     * @throws ListException
     */
    private function __construct()
    {
        /*
         * If $fetch is TRUE of cache file not exists, try to fetch from remote URL
         * */

        if (Extract::isFetch() || !file_exists(Extract::getCacheFile())) {
            $tldList = $this->fetchTldList();

            if (is_array($tldList) && count($tldList) > 0) {
                $this->tldList = $tldList;

                try {
                    file_put_contents(
                        Extract::getCacheFile(), json_encode($this->tldList)
                    );

                    return true;
                } catch (\Exception $e) {
                    throw new IOException('Cannot put TLD list to cache', 0, null, Extract::getCacheFile());
                }
            }
        }

        /*
         * Try load the public suffix list from the cache, if possible
         * */

        if (file_exists(Extract::getCacheFile())) {
            $tldList = json_decode(
                file_get_contents(Extract::getCacheFile())
            );

            if (is_array($tldList) && count($tldList) > 0) {
                $this->tldList = $tldList;

                return true;
            }
        }

        throw new ListException('Cache file not exists & fetch from remote URL failed');
    }

    /**
     * Fetches TLD list from remote URL and parses it to array
     *
     * @return array|bool
     */
    private function fetchTldList()
    {
        $client = new Client();

        try {
            $response = $client->get(Extract::getSuffixFileUrl());
        } catch (\Exception $e) {
            return false;
        }

        $tlds = [];

        if (!empty($page) && preg_match_all('@^(?P<tld>[.*!]*\w[\S]*)@um', $page, $matches)) {
            $tlds = array_fill_keys($matches['tld'], true);
        }
        return $tlds;
    }

    /**
     * @param string $host
     * @return array An array with two items - the reg. domain (possibly with subdomains) and the public suffix.
     */
    public function extract($host)
    {
        $parts = explode('.', $host);

        for ($i = 0; $i < count($parts); $i++) {
            $maybeTld = join('.', array_slice($parts, $i));
            $exceptionTld = '!' . $maybeTld;

            if (array_key_exists($exceptionTld, $this->tldList)) {
                return [
                    join('.', array_slice($parts, 0, $i + 1)),
                    join('.', array_slice($parts, $i + 1)),
                ];
            }

            $wildcardTld = '*.' . join('.', array_slice($parts, $i + 1));
            if (array_key_exists($wildcardTld, $this->tldList) || array_key_exists($maybeTld, $this->tldList)) {
                return [
                    join('.', array_slice($parts, 0, $i)),
                    $maybeTld
                ];
            }
        }
        return [$host, ''];
    }


    private function __clone()
    {
    }

    /**
     * @return SuffixExtractor
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}