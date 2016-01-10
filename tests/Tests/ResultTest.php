<?php
/**
 * PHP version 5.
 *
 * @category Exceptions
 *
 * @author   Alexander Fedyashov <a@fedyashov.com>
 * @license  MIT https://opensource.org/licenses/MIT
 *
 * @link     https://github.com/layershifter/TLDExtract
 */
namespace LayerShifter\TLDExtract\Tests;

use LayerShifter\TLDExtract\Result;

/**
 * Test that coverages all cases of LayerShifter\TLDExtract\Result.
 */
class ResultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object for tests.
     *
     * @var Result
     */
    private $entity;

    /**
     * Method that setups test's environment.
     *
     * @return void
     */
    public function setUp()
    {
        $this->entity = new Result(null, '192.168.0.1', null);
    }

    /**
     * Test for __constructor.
     *
     * @return void
     */
    public function testConstruct()
    {
        self::assertNull($this->entity->subdomain);
        self::assertEquals('192.168.0.1', $this->entity->domain);
        self::assertNull($this->entity->tld);

        $entity = new Result(null, 'domain', 'com');

        self::assertNull($this->entity->subdomain);
        self::assertEquals('domain', $entity->domain);
        self::assertEquals('com', $entity->tld);

        unset($entity);

        $entity = new Result('www', 'domain', 'com');

        self::assertEquals('www', $entity->subdomain);
        self::assertEquals('domain', $entity->domain);
        self::assertEquals('com', $entity->tld);

        self::assertArrayHasKey('subdomain', $entity);
        self::assertArrayHasKey('domain', $entity);
        self::assertArrayHasKey('tld', $entity);
    }

    /**
     * Test for toJson().
     *
     * @return void
     */
    public function testToJson()
    {
        self::assertJsonStringEqualsJsonString(
            json_encode((object) [
                'subdomain' => null,
                'domain'    => '192.168.0.1',
                'tld'       => null,
            ]),
            $this->entity->toJson()
        );
    }

    /**
     * Test for magic method __toString().
     *
     * @return void
     */
    public function testToString()
    {
        self::assertEquals('192.168.0.1', (string) $this->entity);
    }

    /**
     * Test for magic method __isset().
     *
     * @return void
     */
    public function testIsset()
    {
        self::assertEquals(true, $this->entity->subdomain === null);
        self::assertEquals(true, $this->entity->domain !== null);
        self::assertEquals(true, $this->entity->tld === null);

        /* @noinspection PhpUndefinedFieldInspection
         * Test for not existing field
         */
        self::assertEquals(false, isset($this->entity->test));
    }

    /**
     * Test for magic method __set().
     *
     * @return void
     */
    public function testSet()
    {
        self::setExpectedException('LogicException');

        $this->entity->offsetSet('domain', 'another-domain');
    }

    /**
     * Test for magic method __get().
     *
     * @return void
     */
    public function testGet()
    {
        self::setExpectedException('OutOfRangeException');

        /* @noinspection PhpUndefinedFieldInspection
         * Test for not existing field
         */
        $this->entity->domain1;
    }

    /**
     * Test for magic method __offsetSet().
     *
     * @return void
     */
    public function testOffsetSet()
    {
        self::setExpectedException('LogicException');

        $this->entity['domain'] = 'another-domain';
    }

    /**
     * Test for magic method __offsetGet().
     *
     * @return void
     */
    public function testOffsetGet()
    {
        self::assertEquals('192.168.0.1', $this->entity['domain']);
    }

    /**
     * Test for magic method __offsetUnset().
     *
     * @return void
     */
    public function testOffsetUnset()
    {
        self::setExpectedException('LogicException');

        unset($this->entity['domain']);
    }
}
