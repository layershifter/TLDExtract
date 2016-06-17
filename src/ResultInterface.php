<?php
/**
 * PHP version 5.
 *
 * @category Interfaces
 *
 * @author   Alexander Fedyashov <a@fedyashov.com>
 * @license  MIT https://opensource.org/licenses/MIT
 *
 * @link     https://github.com/layershifter/TLDExtract
 * @since    Version 0.2.0
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
