<?php

namespace LayerShifter\TLDExtract\Tests;

use LayerShifter\TLDExtract\IDN;
use TrueBV\Punycode;

/**
 * Tests for IDN class.
 */
class IDNTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var IDN Object for tests
     */
    private $idn;

    /**
     * Method that setups test's environment.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->idn = new IDN();
    }

    /**
     * Tests constructor(), ensures that transformer isn't loaded when `intl` extension present.
     *
     * @void
     */
    public function testConstructor()
    {
        if (function_exists('\idn_to_utf8')) {
            $this->assertAttributeInternalType('null', 'transformer', $this->idn);

            return;
        }

        $this->assertAttributeInstanceOf(Punycode::class, 'transformer', $this->idn);
    }

    /**
     * Tests toASCII() method.
     *
     * @return void
     */
    public function testToASCII()
    {
        $this->assertEquals('xn--tst-qla.de', $this->idn->toASCII('täst.de'));
    }

    /**
     * Tests toUTF8() method.
     *
     * @return void
     */
    public function testToUTF8()
    {
        $this->assertEquals('täst.de', $this->idn->toUTF8('xn--tst-qla.de'));
    }
}
