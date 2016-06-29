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

use LayerShifter\TLDDatabase\Store;
use LayerShifter\TLDExtract\Exceptions\RuntimeException;
use LayerShifter\TLDExtract\Extract;
use LayerShifter\TLDExtract\Result;

/**
 * Tests for Extract class.
 */
class ExtractTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Extract Object of Extract class
     */
    private $extract;

    /**
     * Bootstrap method.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->extract = new Extract();

        parent::setUp();
    }

    /**
     * Tests constructor().
     *
     * @void
     */
    public function testConstructor()
    {
        $defaultMode = Extract::MODE_ALLOW_ICCAN
            | Extract::MODE_ALLOW_PRIVATE
            | Extract::MODE_ALLOW_NOT_EXISTING_SUFFIXES;

        // Variant 1.

        $extract = new Extract();

        static::assertAttributeInstanceOf(Store::class, 'suffixStore', $extract);
        static::assertAttributeEquals($defaultMode, 'extractionMode', $extract);
        static::assertAttributeEquals(Result::class, 'resultClassName', $extract);

        // Variant 2.

        $extract = new Extract(__DIR__ . '/sample-database.php');

        static::assertAttributeInstanceOf(Store::class, 'suffixStore', $extract);
        static::assertAttributeEquals($defaultMode, 'extractionMode', $extract);
        static::assertAttributeEquals(Result::class, 'resultClassName', $extract);

        // Variant 3.

        $extract = new Extract(null, SampleResult::class);

        static::assertAttributeInstanceOf(Store::class, 'suffixStore', $extract);
        static::assertAttributeEquals($defaultMode, 'extractionMode', $extract);
        static::assertAttributeEquals(SampleResult::class, 'resultClassName', $extract);

        // Variant 4.

        $extract = new Extract(null, null, Extract::MODE_ALLOW_ICCAN);

        static::assertAttributeInstanceOf(Store::class, 'suffixStore', $extract);
        static::assertAttributeEquals(Extract::MODE_ALLOW_ICCAN, 'extractionMode', $extract);
        static::assertAttributeEquals(Result::class, 'resultClassName', $extract);
    }

    /**
     * Tests constructor() exception for non-existence result class.
     *
     * @void
     */
    public function testConstructorNonExistenceClass()
    {
        $this->setExpectedException(RuntimeException::class, 'is not defined');
        new Extract(null, 'NonExistenceClass');
    }

    /**
     * Tests constructor() exception for result class that not implements result interface.
     *
     * @void
     */
    public function testConstructorNotImplements()
    {
        $this->setExpectedException(RuntimeException::class, 'not implements ResultInterface');
        new Extract(null, Extract::class);
    }

    /**
     * Tests setExtractionMode() method.
     *
     * @void
     */
    public function testSetExtractionMode()
    {
        $extract = new Extract();

        // Variant 1.

        $extract->setExtractionMode(null);
        static::assertAttributeEquals(
            Extract::MODE_ALLOW_ICCAN | Extract::MODE_ALLOW_PRIVATE | Extract::MODE_ALLOW_NOT_EXISTING_SUFFIXES,
            'extractionMode',
            $extract
        );

        // Variant 2.

        $extract->setExtractionMode(Extract::MODE_ALLOW_ICCAN);
        static::assertAttributeEquals(Extract::MODE_ALLOW_ICCAN, 'extractionMode', $extract);

        // Variant 3.

        $extract->setExtractionMode(Extract::MODE_ALLOW_PRIVATE);
        static::assertAttributeEquals(Extract::MODE_ALLOW_PRIVATE, 'extractionMode', $extract);

        // Variant 4.

        $extract->setExtractionMode(Extract::MODE_ALLOW_NOT_EXISTING_SUFFIXES);
        static::assertAttributeEquals(Extract::MODE_ALLOW_NOT_EXISTING_SUFFIXES, 'extractionMode', $extract);

        // Variant 5.

        $extract->setExtractionMode(Extract::MODE_ALLOW_PRIVATE | Extract::MODE_ALLOW_NOT_EXISTING_SUFFIXES);
        static::assertAttributeEquals(
            Extract::MODE_ALLOW_PRIVATE | Extract::MODE_ALLOW_NOT_EXISTING_SUFFIXES,
            'extractionMode',
            $extract
        );
    }

    /**
     * Tests setExtractionMode() exception for invalid mode type.
     *
     * @void
     */
    public function testSetExtractionModeInvalidArgumentType()
    {
        $this->setExpectedException(RuntimeException::class, 'Invalid argument type, extractionMode must be integer');

        $extract = new Extract();
        $extract->setExtractionMode('a');
    }

    /**
     * Tests setExtractionMode() exception for invalid mode value.
     *
     * @void
     */
    public function testSetExtractionModeInvalidArgumentValue()
    {
        $this->setExpectedException(
            RuntimeException::class,
            'Invalid argument type, extractionMode must be one of defined constants'
        );

        $extract = new Extract();
        $extract->setExtractionMode(-10);
    }

    /**
     * Tests parsing result.
     *
     * @param string $hostname       Hostname for parsing
     * @param string $expectedResult Expected result of parsing
     *
     * @return void
     */
    private function checkPublicDomain($hostname, $expectedResult)
    {
        static::assertEquals($expectedResult, $this->extract->parse($hostname)->getRegistrableDomain());
    }

    /**
     * Real world test case. Uses official test data.
     *
     * @see       http://mxr.mozilla.org/mozilla-central/source/netwerk/test/unit/data/test_psl.txt?raw=1
     * @copyright Public Domain. https://creativecommons.org/publicdomain/zero/1.0/
     *
     * @return void
     */
    public function testParse()
    {
        // null input.

        $this->checkPublicDomain(null, null);

        // Mixed case.

        $this->checkPublicDomain('COM', null);
        $this->checkPublicDomain('example.COM', 'example.com');
        $this->checkPublicDomain('WwW.example.COM', 'example.com');

        // Leading dot.

        $this->checkPublicDomain('.com', null);
        $this->checkPublicDomain('.example', null);
        $this->checkPublicDomain('.example.com', null);
        $this->checkPublicDomain('.example.example', null);

        // Unlisted TLD.

        $this->checkPublicDomain('example', null);
        $this->checkPublicDomain('example.example', 'example.example');
        $this->checkPublicDomain('b.example.example', 'example.example');
        $this->checkPublicDomain('a.b.example.example', 'example.example');

        // TLD with only 1 rule.

        $this->checkPublicDomain('biz', null);
        $this->checkPublicDomain('domain.biz', 'domain.biz');
        $this->checkPublicDomain('b.domain.biz', 'domain.biz');
        $this->checkPublicDomain('a.b.domain.biz', 'domain.biz');

        // TLD with some 2-level rules.

        $this->checkPublicDomain('com', null);
        $this->checkPublicDomain('example.com', 'example.com');
        $this->checkPublicDomain('b.example.com', 'example.com');
        $this->checkPublicDomain('a.b.example.com', 'example.com');
        $this->checkPublicDomain('uk.com', null);
        $this->checkPublicDomain('example.uk.com', 'example.uk.com');
        $this->checkPublicDomain('b.example.uk.com', 'example.uk.com');
        $this->checkPublicDomain('a.b.example.uk.com', 'example.uk.com');
        $this->checkPublicDomain('test.ac', 'test.ac');

        // TLD with only 1 (wildcard) rule.

        $this->checkPublicDomain('mm', null);
        $this->checkPublicDomain('c.mm', null);
        $this->checkPublicDomain('b.c.mm', 'b.c.mm');
        $this->checkPublicDomain('a.b.c.mm', 'b.c.mm');

        // More complex TLD.

        $this->checkPublicDomain('jp', null);
        $this->checkPublicDomain('test.jp', 'test.jp');
        $this->checkPublicDomain('www.test.jp', 'test.jp');
        $this->checkPublicDomain('ac.jp', null);
        $this->checkPublicDomain('test.ac.jp', 'test.ac.jp');
        $this->checkPublicDomain('www.test.ac.jp', 'test.ac.jp');
        $this->checkPublicDomain('kyoto.jp', null);
        $this->checkPublicDomain('test.kyoto.jp', 'test.kyoto.jp');
        $this->checkPublicDomain('ide.kyoto.jp', null);
        $this->checkPublicDomain('b.ide.kyoto.jp', 'b.ide.kyoto.jp');
        $this->checkPublicDomain('a.b.ide.kyoto.jp', 'b.ide.kyoto.jp');
        $this->checkPublicDomain('c.kobe.jp', null);
        $this->checkPublicDomain('b.c.kobe.jp', 'b.c.kobe.jp');
        $this->checkPublicDomain('a.b.c.kobe.jp', 'b.c.kobe.jp');
        $this->checkPublicDomain('city.kobe.jp', 'city.kobe.jp');
        $this->checkPublicDomain('www.city.kobe.jp', 'city.kobe.jp');

        // TLD with a wildcard rule and exceptions.

        $this->checkPublicDomain('ck', null);
        $this->checkPublicDomain('test.ck', null);
        $this->checkPublicDomain('b.test.ck', 'b.test.ck');
        $this->checkPublicDomain('a.b.test.ck', 'b.test.ck');
        $this->checkPublicDomain('www.ck', 'www.ck');
        $this->checkPublicDomain('www.www.ck', 'www.ck');

        // US K12.

        $this->checkPublicDomain('us', null);
        $this->checkPublicDomain('test.us', 'test.us');
        $this->checkPublicDomain('www.test.us', 'test.us');
        $this->checkPublicDomain('ak.us', null);
        $this->checkPublicDomain('test.ak.us', 'test.ak.us');
        $this->checkPublicDomain('www.test.ak.us', 'test.ak.us');
        $this->checkPublicDomain('k12.ak.us', null);
        $this->checkPublicDomain('test.k12.ak.us', 'test.k12.ak.us');
        $this->checkPublicDomain('www.test.k12.ak.us', 'test.k12.ak.us');
    }

    /**
     * Real world test case for IDN. Uses official test data.
     *
     * @see       http://mxr.mozilla.org/mozilla-central/source/netwerk/test/unit/data/test_psl.txt?raw=1
     * @copyright Public Domain. https://creativecommons.org/publicdomain/zero/1.0/
     *
     * @return void
     */
    public function testParseIdn()
    {
        // IDN labels.

        $this->checkPublicDomain('食狮.com.cn', '食狮.com.cn');
        $this->checkPublicDomain('食狮.公司.cn', '食狮.公司.cn');
        $this->checkPublicDomain('www.食狮.公司.cn', '食狮.公司.cn');
        $this->checkPublicDomain('shishi.公司.cn', 'shishi.公司.cn');
        $this->checkPublicDomain('公司.cn', null);
        $this->checkPublicDomain('食狮.中国', '食狮.中国');
        $this->checkPublicDomain('www.食狮.中国', '食狮.中国');
        $this->checkPublicDomain('shishi.中国', 'shishi.中国');
        $this->checkPublicDomain('中国', null);

        // Same as above, but punycoded.

        $this->checkPublicDomain('xn--85x722f.com.cn', 'xn--85x722f.com.cn');
        $this->checkPublicDomain('xn--85x722f.xn--55qx5d.cn', 'xn--85x722f.xn--55qx5d.cn');
        $this->checkPublicDomain('www.xn--85x722f.xn--55qx5d.cn', 'xn--85x722f.xn--55qx5d.cn');
        $this->checkPublicDomain('shishi.xn--55qx5d.cn', 'shishi.xn--55qx5d.cn');
        $this->checkPublicDomain('xn--55qx5d.cn', null);
        $this->checkPublicDomain('xn--85x722f.xn--fiqs8s', 'xn--85x722f.xn--fiqs8s');
        $this->checkPublicDomain('www.xn--85x722f.xn--fiqs8s', 'xn--85x722f.xn--fiqs8s');
        $this->checkPublicDomain('shishi.xn--fiqs8s', 'shishi.xn--fiqs8s');
        $this->checkPublicDomain('xn--fiqs8s', null);
    }

    /**
     * Tests parsing result.
     *
     * @param string $hostname       Hostname for parsing
     * @param string $expectedResult Expected result of parsing
     *
     * @return void
     */
    private function checkPublicSuffix($hostname, $expectedResult)
    {
        static::assertEquals($expectedResult, $this->extract->parse($hostname)->getSuffix());
    }

    /**
     * Custom tests for URL's parsing.
     *
     * @return void
     */
    public function testParseUrls()
    {
        // Base tests.

        $this->checkPublicSuffix('com', null);
        $this->checkPublicSuffix('http://www.bbc.co.uk/news/business', 'co.uk');
        $this->checkPublicSuffix('http://ru.wikipedia.org/', 'org');
        $this->checkPublicSuffix('bcc.bccbcc', 'bccbcc');
        $this->checkPublicSuffix('svadba.net.ru', 'net.ru');
        $this->checkPublicSuffix('us.example.com', 'com');
        $this->checkPublicSuffix('us.example.org', 'org');

        // Test different schemas.

        $this->checkPublicDomain('//www.bbc.co.uk/news/business', 'bbc.co.uk');
        $this->checkPublicSuffix('//www.bbc.co.uk/news/business', 'co.uk');
        $this->checkPublicDomain('ftp://www.bbc.co.uk/news/business', 'bbc.co.uk');
        $this->checkPublicSuffix('ftp://www.bbc.co.uk/news/business', 'co.uk');
        $this->checkPublicSuffix('test.schema://example.com', 'com');

        // Test IDN.

        $this->checkPublicDomain('http://Яндекс.РФ', 'яндекс.рф');
        $this->checkPublicSuffix('http://Яндекс.РФ', 'рф');

        // Test non-existent suffixes.

        $this->checkPublicSuffix('http://localhost', null);
        $this->checkPublicSuffix('http://www.example.dev', 'dev');
        $this->checkPublicSuffix('http://example.faketld', 'faketld');

        // Test IP.

        $this->checkPublicSuffix('http://[::1]/', null);
        $this->checkPublicSuffix('http://192.168.1.1/', null);
    }

    /**
     * Tests parsing result.
     *
     * @param string $hostname       Hostname for parsing
     * @param string $expectedResult Expected result of parsing
     *
     * @return void
     */
    private function checkHost($hostname, $expectedResult)
    {
        static::assertEquals($expectedResult, $this->extract->parse($hostname)->getHostname());
    }

    /**
     * Custom tests for IP's parsing.
     *
     * @return void
     */
    public function testParseIp()
    {
        // Test IPv4.

        $this->checkHost('http://192.168.1.1/', '192.168.1.1');
        $this->checkHost('http://127.0.0.1:443', '127.0.0.1');

        // Test IPv6.

        $this->checkHost('http://[2001:0:9d38:6abd:3431:eb:3cbd:22ba]/', '2001:0:9d38:6abd:3431:eb:3cbd:22ba');
        $this->checkHost('https://[2001:0:9d38:6abd:3431:eb:3cbd:22ba]:443/', '2001:0:9d38:6abd:3431:eb:3cbd:22ba');

        // Test local.

        $this->checkHost('http://[fe80::3%25eth0]', 'fe80::3%25eth0');
        $this->checkHost('http://[fe80::1%2511]', 'fe80::1%2511');
    }

    /**
     * Test for parse() withExtract::MODE_ALLOW_ICCAN | Extract::MODE_ALLOW_PRIVATE options.
     *
     * @return void
     */
    public function testParseOnlyExisting()
    {
        $extract = new Extract(null, null, Extract::MODE_ALLOW_ICCAN | Extract::MODE_ALLOW_PRIVATE);

        static::assertNull($extract->parse('example.example')->getSuffix());
        static::assertNull($extract->parse('a.example.example')->getSuffix());
        static::assertNull($extract->parse('a.b.example.example')->getSuffix());
        static::assertNull($extract->parse('localhost')->getSuffix());
        static::assertNull($extract->parse('example.localhost')->getSuffix());

        static::assertEquals('com', $extract->parse('example.com')->getSuffix());
        static::assertEquals('com', $extract->parse('a.example.com')->getSuffix());
        static::assertEquals('example.com', $extract->parse('a.example.com')->getRegistrableDomain());
    }

    /**
     * Test for parse() with Extract::MODE_ALLOW_ICCAN | Extract::MODE_ALLOW_PRIVATE options.
     *
     * @return void
     */
    public function testParseDisablePrivate()
    {
        $extract = new Extract(null, null, Extract::MODE_ALLOW_ICCAN | Extract::MODE_ALLOW_NOT_EXISTING_SUFFIXES);

        static::assertEquals('example', $extract->parse('example.example')->getSuffix());
        static::assertEquals('example', $extract->parse('a.example.example')->getSuffix());
        static::assertEquals('example', $extract->parse('a.b.example.example')->getSuffix());
        static::assertEquals('localhost', $extract->parse('example.localhost')->getSuffix());
        static::assertNull($extract->parse('localhost')->getSuffix());

        static::assertEquals('com', $extract->parse('example.com')->getSuffix());
        static::assertEquals('com', $extract->parse('a.example.com')->getSuffix());
        static::assertEquals('example.com', $extract->parse('a.example.com')->getRegistrableDomain());

        static::assertEquals('com', $extract->parse('a.blogspot.com')->getSuffix());
        static::assertEquals('com', $extract->parse('a.b.blogspot.com')->getSuffix());
        static::assertEquals('blogspot.com', $extract->parse('a.blogspot.com')->getRegistrableDomain());
    }

    /**
     * Test for parse() with MODE_ALLOW_ICCAN option.
     *
     * @return void
     */
    public function testParseICCANOption()
    {
        $extract = new Extract(null, null, Extract::MODE_ALLOW_ICCAN);

        static::assertNull($extract->parse('example.example')->getSuffix());
        static::assertNull($extract->parse('a.example.example')->getSuffix());
        static::assertNull($extract->parse('a.b.example.example')->getSuffix());
        static::assertNull($extract->parse('localhost')->getSuffix());
        static::assertNull($extract->parse('example.localhost')->getSuffix());

        static::assertEquals('com', $extract->parse('example.com')->getSuffix());
        static::assertEquals('com', $extract->parse('a.example.com')->getSuffix());
        static::assertEquals('example.com', $extract->parse('a.example.com')->getRegistrableDomain());
        static::assertEquals('com', $extract->parse('a.blogspot.com')->getSuffix());
        static::assertEquals('com', $extract->parse('a.b.blogspot.com')->getSuffix());
        static::assertEquals('blogspot.com', $extract->parse('a.blogspot.com')->getRegistrableDomain());
    }
}
