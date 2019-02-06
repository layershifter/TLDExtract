<?php

namespace LayerShifter\TLDExtract;

use TrueBV\Punycode;

/**
 * Class that transforms IDN domains, if `intl` extension present uses it.
 */
class IDN
{
    /**
     * Converts domain name from Unicode to IDNA ASCII.
     *
     * @param string $domain Domain to convert in IDNA ASCII-compatible format.
     *
     * @return string
     */
    public function toASCII($domain)
    {
        if (defined('INTL_IDNA_VARIANT_UTS46')) {
            return idn_to_ascii($domain, 0, INTL_IDNA_VARIANT_UTS46);
        }

        return idn_to_ascii($domain);
    }

    /**
     * Converts domain name from IDNA ASCII to Unicode.
     *
     * @param string $domain Domain to convert in Unicode format.
     *
     * @return string
     */
    public function toUTF8($domain)
    {
        if (defined('INTL_IDNA_VARIANT_UTS46')) {
            return idn_to_utf8($domain, 0, INTL_IDNA_VARIANT_UTS46);
        }

        return idn_to_utf8($domain);
    }
}
