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

interface ResultInterface
{
    /**
     * Class that implements ResultInterface must have following constructor.
     *
     * @param $subdomain
     * @param $domain
     * @param $tld
     */
    public function __construct($subdomain, $domain, $tld);
}
