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


use GuzzleHttp\Client;
use LayerShifter\TLDExtract\Exceptions\IOException;
use LayerShifter\TLDExtract\Exceptions\ListException;

/**
 * This class splits domain names into the registered domain and public
 * suffix components using the TLD rule set from the Public Suffix List project.
 *
 * @category Classes
 * @package  LayerShifter/TLDExtract
 * @author   Alexander Fedyashov <a@fedyashov.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/layershifter/TLDExtract
 */
class SuffixExtractor
{
    /**
     * Instance of class.
     * @var SuffixExtractor
     */
    private static $_instance;

    /**
     * The TLD set from Public Suffix List.
     * @var array
     */
    private $_tldList = [];

    /**
     * SuffixExtractor constructor, runs actions for filling list of TLDs.
     *
     * @throws IOException
     * @throws ListException
     */
    private function __construct()
    {
        // If $fetch is TRUE of cache file not exists, try to fetch from remote URL

        if (Extract::isFetch() || !file_exists(Extract::getCacheFile())) {
            $tldList = $this->_fetchTldList();

            if (is_array($tldList) && count($tldList) > 0) {
                $this->_tldList = $tldList;

                try {
                    file_put_contents(
                        Extract::getCacheFile(), json_encode($this->_tldList)
                    );

                    return true;
                } catch (\Exception $e) {
                    throw new IOException(
                        'Cannot put TLD list to cache', 0, null,
                        Extract::getCacheFile()
                    );
                }
            }
        }

        // Try load the public suffix list from the cache, if possible

        if (file_exists(Extract::getCacheFile())) {
            $tldList = json_decode(
                file_get_contents(Extract::getCacheFile()),
                true
            );

            if (is_array($tldList) && count($tldList) > 0) {
                $this->_tldList = $tldList;

                return true;
            }
        }

        throw new ListException(
            'Cache file not exists & fetch from remote URL failed'
        );
    }

    /**
     * Fetches TLD list from remote URL and parses it to array.
     *
     * @return array|bool
     */
    private function _fetchTldList()
    {
        $client = new Client();
        $response = $client->get(Extract::getSuffixFileUrl(), ['verify' => false]);

        $body = $response->getBody()->getContents();

        if (empty($body)) {
            return false;
        }

        if (!preg_match_all('@^(?P<tld>[.*!]*\w[\S]*)@um', $body, $matches)) {
            return false;
        }

        return array_fill_keys($matches['tld'], true);
    }

    /**
     * Extracts host & TLD from input string.
     * Based on algorithm described in https://publicsuffix.org/list/
     *
     * @param string $host Host for extraction
     *
     * @return array An array with two items - the reg. domain (possibly with
     *               subdomains) and the public suffix.
     */
    public function extract($host)
    {
        $parts = explode('.', $host);

        for ($i = 0, $count = count($parts); $i < $count; $i++) {
            $maybeTld = implode('.', array_slice($parts, $i));
            $exceptionTld = '!' . $maybeTld;

            if (isset($this->_tldList[$exceptionTld])) {
                return [
                    implode('.', array_slice($parts, 0, $i + 1)),
                    implode('.', array_slice($parts, $i + 1)),
                ];
            }

            $wildcardTld = '*.' . implode('.', array_slice($parts, $i + 1));

            if (isset($this->_tldList[$wildcardTld])
                || isset($this->_tldList[$maybeTld])
            ) {
                return [
                    implode('.', array_slice($parts, 0, $i)),
                    $maybeTld
                ];
            }
        }

        return [$host, ''];
    }


    /**
     * Disables class cloning
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Gets instance of current class
     *
     * @return SuffixExtractor
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
}
