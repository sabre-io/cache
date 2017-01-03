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
     * @depends testSetGet
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

}
