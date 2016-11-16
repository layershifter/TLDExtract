<?php
/**
 * TLDExtract: Library for extraction of domain parts e.g. TLD. Domain parser that uses Public Suffix List.
 *
 * @link      https://github.com/layershifter/TLDExtract
 *
 * @copyright Copyright (c) 2016, Alexander Fedyashov
 * @license   https://raw.githubusercontent.com/layershifter/TLDExtract/master/LICENSE Apache 2.0 License
 */

namespace
{
    use TrueBV\Punycode;

    if (!function_exists('idn_to_utf8')) {

        /**
         * Polyfill for idn_to_utf8.
         *
         * @link http://php.net/manual/de/function.idn-to-utf8.php
         *
         * @param $domain
         *
         * @return string
         */
        function idn_to_utf8($domain)
        {
            $punyCode = new Punycode();

            return $punyCode->encode($domain);
        }
    }

    if (!function_exists('idn_to_ascii')) {

        /**
         * Polyfill for idn_to_ascii.
         *
         * @link http://php.net/manual/de/function.idn-to-ascii.php
         *
         * @param string $domain
         *
         * @return string
         */
        function idn_to_ascii($domain)
        {
            $punyCode = new Punycode();

            return $punyCode->decode($domain);
        }
    }
}
