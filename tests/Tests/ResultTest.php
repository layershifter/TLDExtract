<?php
/**
 * PHP version 5
 *
 * @category Exceptions
 * @package  LayerShifter/TLDExtract/Tests
 * @author   Alexander Fedyashov <a@fedyashov.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/layershifter/TLDExtract
 */

namespace LayerShifter\TLDExtract\Tests;

use LayerShifter\TLDExtract\Result;

/**
 * Test that coverages all cases of LayerShifter\TLDExtract\Result
 */
class ResultTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Object for tests
     * @var Result
     */
    private $entity;

    /**
     * Method that setups test's environment
     * @return void
     */
    public function setUp()
    {
        $this->entity = new Result(null, '192.168.0.1', null);
    }

    /**
     * Test for __constructor
     * @return void
     */
    public function testConstruct()
    {
        $this->assertEquals(null, $this->entity->subdomain);
        $this->assertEquals('192.168.0.1', $this->entity->domain);
        $this->assertEquals(null, $this->entity->tld);

        $entity = new Result(null, 'domain', 'com');

        $this->assertEquals(null, $this->entity->subdomain);
        $this->assertEquals('domain', $entity->domain);
        $this->assertEquals('com', $entity->tld);

        unset($entity);

        $entity = new Result('www', 'domain', 'com');

        $this->assertEquals('www', $entity->subdomain);
        $this->assertEquals('domain', $entity->domain);
        $this->assertEquals('com', $entity->tld);

        $this->assertArrayHasKey('subdomain', $entity);
        $this->assertArrayHasKey('domain', $entity);
        $this->assertArrayHasKey('tld', $entity);
    }

    /**
     * Test for toJson()
     * @return void
     */
    public function testToJson()
    {
        $this->assertJsonStringEqualsJsonString(
            json_encode((object)[
                'subdomain' => null,
                'domain' => '192.168.0.1',
                'tld' => null
            ]),
            $this->entity->toJson()
        );
    }

    /**
     * Test for magic method __toString()
     * @return void
     */
    public function testToString()
    {
        $this->assertEquals(
            "LayerShifter\TLDExtract\Result(subdomain='', domain='192.168.0.1', tld='')",
            (string)$this->entity
        );
    }

    /**
     * Test for magic method __isset()
     * @return void
     */
    public function testIsset()
    {
        $this->assertEquals(true, isset($this->entity->subdomain));
        $this->assertEquals(true, isset($this->entity->domain));
        $this->assertEquals(true, isset($this->entity->tld));

        /** @noinspection PhpUndefinedFieldInspection
         * Test for not existing field
         */
        $this->assertEquals(false, isset($this->entity->test));
    }

    /**
     * Test for magic method __set()
     * @return void
     */
    public function testSet()
    {
        $this->setExpectedException('LogicException');
        $this->entity->domain = 'another-domain';
    }

    /**
     * Test for magic method __get()
     * @return void
     */
    public function testGet()
    {
        $this->setExpectedException('OutOfRangeException');

        /** @noinspection PhpUndefinedFieldInspection
         * Test for not existing field
         */
        $this->entity->domain1;
    }

    /**
     * Test for magic method __offsetSet()
     * @return void
     */
    public function testOffsetSet()
    {
        $this->setExpectedException('LogicException');
        $this->entity['domain'] = 'another-domain';
    }

    /**
     * Test for magic method __offsetGet()
     * @return void
     */
    public function testOffsetGet()
    {
        $this->assertEquals('192.168.0.1', $this->entity['domain']);
    }

    /**
     * Test for magic method __offsetUnset()
     * @return void
     */
    public function testOffsetUnset()
    {
        $this->setExpectedException('LogicException');
        unset($this->entity['domain']);
    }
}
