<?php
/**
 * TLDExtract: Library for extraction of domain parts e.g. TLD. Domain parser that uses Public Suffix List.
 *
 * @link      https://github.com/layershifter/TLDExtract
 *
 * @copyright Copyright (c) 2016, Alexander Fedyashov
 * @license   https://raw.githubusercontent.com/layershifter/TLDExtract/master/LICENSE Apache 2.0 License
 */

namespace LayerShifter\TLDExtract\Tests;

use LayerShifter\TLDExtract\Extract;

/**
 * Tests for static.php.
 */
class StaticTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Tests for tld_extract() function.
     *
     * @return void
     */
    public function testExtract()
    {
        $result = tld_extract('http://www.domain.com');

        static::assertEquals('www.domain.com', $result->getFullHost());
        static::assertEquals('domain.com', $result->getRegistrableDomain());

        $result = tld_extract('a.b.blogspot.com', Extract::MODE_ALLOW_ICANN);

        static::assertEquals('a.b.blogspot.com', $result->getFullHost());
        static::assertEquals('blogspot.com', $result->getRegistrableDomain());
        static::assertEquals('a.b', $result->getSubdomain());
        static::assertEquals('com', $result->getSuffix());
    }
}
