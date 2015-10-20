<?php
/**
 * TldExtractor.php
 *
 * @author Alexander Fedyashov <af@e42.guru>
 */

namespace LayerShifter\TLDExtract;


use GuzzleHttp\Client;

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

    private function __construct()
    {
        // Try load the public suffix list from the cache, if possible

        if (file_exists(Extract::getCacheFile())) {
            $jsonData = file_get_contents(Extract::getCacheFile());
            $this->tldList = json_decode($jsonData);

            return true;
        }

        if (Extract::isFetch()) {
            $tlds = $this->fetchTldList();
        }

        if (empty($tlds)) {
            //If all else fails, try the local snapshot.
            $snapshotFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . '.tld_set_snapshot';
            $serializedTlds = @file_get_contents($snapshotFile);
            if (!empty($serializedTlds)) {
                $this->extractor = new PublicSuffixExtractor(unserialize($serializedTlds));
                return $this->extractor;
            }
        } else {
            //Update the cache.
            @file_put_contents($this->cacheFile, serialize($tlds));
        }
        $this->extractor = new PublicSuffixExtractor($tlds);
        return $this->extractor;
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

    private function fetchPage($url)
    {
        if (ini_get('allow_url_fopen')) {
            return @file_get_contents($url);
        } else if (is_callable('curl_exec')) {
            $handle = curl_init($url);
            curl_setopt_array($handle, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => false,
                CURLOPT_FAILONERROR => true,
            ));
            $content = curl_exec($handle);
            curl_close($handle);
            return $content;
        }
        return '';
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