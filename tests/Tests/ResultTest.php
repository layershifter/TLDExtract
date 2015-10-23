<?php
/**
 * ResultTest.php
 *
 * @author Alexander Fedyashov <af@e42.guru>
 */

namespace Tests;

use LayerShifter\TLDExtract\Result;

class ResultTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Result
     */
    private $resultTest1;

    public function setUp()
    {
        $this->resultTest1 = new Result(null, 'IP', null);
    }

    public function testConstruct()
    {
        $this->assertEquals(null, $this->resultTest1->subdomain);
        $this->assertEquals('IP', $this->resultTest1->domain);
        $this->assertEquals(null, $this->resultTest1->tld);

        $resultTest2 = new Result(null, 'DOMAIN', 'TLD');

        $this->assertEquals(null, $this->resultTest1->subdomain);
        $this->assertEquals('DOMAIN', $resultTest2->domain);
        $this->assertEquals('TLD', $resultTest2->tld);

        $resultTest3 = new Result('SUBDOMAIN', 'DOMAIN', 'TLD');

        $this->assertEquals('SUBDOMAIN', $resultTest3->subdomain);
        $this->assertEquals('DOMAIN', $resultTest3->domain);
        $this->assertEquals('TLD', $resultTest3->tld);

        $this->assertArrayHasKey('subdomain', $resultTest3);
        $this->assertArrayHasKey('domain', $resultTest3);
        $this->assertArrayHasKey('tld', $resultTest3);
    }

    public function testToJson()
    {
        $this->assertJsonStringEqualsJsonString(
            $this->resultTest1->toJson(),
            json_encode((object)[
                'subdomain' => null,
                'domain' => 'IP',
                'tld' => null
            ])
        );
    }
}
