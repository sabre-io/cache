<?php

namespace Sabre\Cache;

use Psr\SimpleCache\CacheInterface;

/**
 * Abstract PSR-16 tester.
 *
 * Because all cache implementations should mostly behave the same way, they
 * can all extend this test.
 */
abstract class AbstractCacheTest extends \PHPUnit_Framework_TestCase {

    abstract function getCache() : CacheInterface;

    function testSetGet() {

        $cache = $this->getCache();
        $cache->set('foo', 'bar');
        $this->assertEquals('bar', $cache->get('foo'));

    }

    /**
     * @depends testSetGet
     */
    function testDelete() {

        $cache = $this->getCache();
        $cache->set('foo', 'bar');
        $this->assertEquals('bar', $cache->get('foo'));
        $cache->delete('foo');
        $this->assertNull($cache->get('foo'));

    }

    /**
     * @expectedException \Psr\SimpleCache\InvalidArgumentException
     */
    function testGetInvalidArg() {

        $cache = $this->getCache();
        $cache->get(null);

    }

    /**
     * @depends testDelete
     */
    function testGetNotFound() {

        $cache = $this->getCache();
        $this->assertNull($cache->get('notfound'));

    }

    /**
     * @depends testDelete
     */
    function testGetNotFoundDefault() {

        $cache = $this->getCache();
        $default = 'chickpeas';
        $this->assertEquals(
            $default,
            $cache->get('notfound', $default)
        );

    }

    /**
     * @depends testSetGet
     * @slow
     */
    function testSetExpire() {

        $cache = $this->getCache();
        $cache->set('foo', 'bar', 1);
        $this->assertEquals('bar', $cache->get('foo'));

        // Wait 2 seconds so the cache expires
        usleep(2000000);
        $this->assertNull($cache->get('foo'));

    }

    /**
     * @depends testSetGet
     * @slow
     */
    function testSetExpireDTInterval() {

        $cache = $this->getCache();
        $cache->set('foo', 'bar', new \DateInterval('PT1S'));
        $this->assertEquals('bar', $cache->get('foo'));

        // Wait 2 seconds so the cache expires
        usleep(2000000);
        $this->assertNull($cache->get('foo'));

    }

    /**
     * @expectedException \Psr\SimpleCache\InvalidArgumentException
     */
    function testSetInvalidArg() {

        $cache = $this->getCache();
        $cache->set(null, 'bar');

    }


    /**
     * @expectedException \Psr\SimpleCache\InvalidArgumentException
     */
    function testDeleteInvalidArg() {

        $cache = $this->getCache();
        $cache->delete(null);

    }

    /**
     * @depends testSetGet
     */
    function testClearCache() {

        $cache = $this->getCache();
        $cache->set('foo', 'bar');
        $cache->clear();
        $this->assertNull($cache->get('foo'));

    }

    /**
     * @depends testSetGet
     */
    function testHas() {

        $cache = $this->getCache();
        $cache->set('foo', 'bar');
        $this->assertTrue($cache->has('foo'));

    }

    /**
     * @expectedException \Psr\SimpleCache\InvalidArgumentException
     */
    function testHasInvalidArg() {

        $cache = $this->getCache();
        $cache->has(null);

    }

    /**
     * @depends testSetGet
     */
    function testSetGetMultiple() {

        $values = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $cache = $this->getCache();
        $cache->setMultiple($values);

        $result = $cache->getMultiple(array_keys($values));
        foreach ($result as $key => $value) {
            $this->assertTrue(isset($values[$key]));
            $this->assertEquals($values[$key], $value);
            unset($values[$key]);
        }

        // The list of values should now be empty
        $this->assertEquals([], $values);

    }

    /**
     * @expectedException \Psr\SimpleCache\InvalidArgumentException
     */
    function testSetMultipleInvalidArg() {

        $cache = $this->getCache();
        $cache->setMultiple(null);

    }

    /**
     * @expectedException \Psr\SimpleCache\InvalidArgumentException
     */
    function testGetMultipleInvalidArg() {

        $cache = $this->getCache();
        $result = $cache->getMultiple(null);
        // If $result was a generator, the generator will only error once the
        // first value is requested.
        //
        // This extra line is just a precaution for that
        if ($result instanceof \Traversable) $result->current();

    }

    /**
     * @depends testSetGetMultiple
     */
    function testDeleteMultipleDefaultGet() {

        $values = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $cache = $this->getCache();
        $cache->setMultiple($values);

        $cache->deleteMultiple(['key1', 'key3']);

        $result = $cache->getMultiple(array_keys($values), 'tea');

        $expected = [
            'key1' => 'tea',
            'key2' => 'value2',
            'key3' => 'tea',
        ];

        foreach ($result as $key => $value) {
            $this->assertTrue(isset($expected[$key]));
            $this->assertEquals($expected[$key], $value);
            unset($expected[$key]);
        }

        // The list of values should now be empty
        $this->assertEquals([], $expected);

    }
}
