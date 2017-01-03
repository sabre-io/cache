<?php declare (strict_types=1);

namespace Sabre\Cache;

use Psr\SimpleCache\CacheInterface;
use Traversable;

class ApcuTest extends AbstractCacheTest {

    function getCache() : CacheInterface {

        if (!function_exists('apcu_store')) {
            $this->markTestSkipped('Apcu extension is not loaded');
        }
        if (!ini_get('apc.enabled')) {
            $this->markTestSkipped('apc.enabled is set to 0. Enable it via php.ini');
        }

        if (php_sapi_name() === 'cli' && !ini_get('apc.enable_cli')) {
            $this->markTestSkipped('apc.enable_cli is set to 0. Enable it via php.ini');
        }

        return new Apcu();

    }

    /**
     * APC will only remove expired items from the cache during the next test,
     * so we can't fully test these.
     *
     * Instead, we test if the parameter is set but then don't check for a
     * result.
     *
     * So this test is not complete, but that's the best we can do.
     */
    function testSetExpire() {

        $cache = $this->getCache();
        $cache->set('foo', 'bar', 1);
        $this->assertEquals('bar', $cache->get('foo'));

        // Wait 2 seconds so the cache expires
        // usleep(2000000);
        // $this->assertNull($cache->get('foo'));

    }

    /**
     * APC will only remove expired items from the cache during the next test,
     * so we can't fully test these.
     *
     * Instead, we test if the parameter is set but then don't check for a
     * result.
     *
     * So this test is not complete, but that's the best we can do.
     */
    function testSetExpireDateInterval() {

        $cache = $this->getCache();
        $cache->set('foo', 'bar', new \DateInterval('PT1S'));
        $this->assertEquals('bar', $cache->get('foo'));

        // Wait 2 seconds so the cache expires
        // usleep(2000000);
        // $this->assertNull($cache->get('foo'));

    }


    /**
     * APC will only remove expired items from the cache during the next test,
     * so we can't fully test these.
     *
     * Instead, we test if the parameter is set but then don't check for a
     * result.
     *
     * So this test is not complete, but that's the best we can do.
     */
    function testSetMultipleExpireDateIntervalExpired() {

        $values = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $cache = $this->getCache();
        $cache->setMultiple($values, new \DateInterval('PT1S'));

        //// Wait 2 seconds so the cache expires
        //sleep(2);

        $result = $cache->getMultiple(array_keys($values), 'not-found');
        $this->assertTrue($result instanceof Traversable || is_array($result));
        //$count = 0;

        //$expected = [
        //    'key1' => 'not-found',
        //    'key2' => 'not-found',
        //    'key3' => 'not-found',
        //];

        //foreach ($result as $key => $value) {
        //    $count++;
        //    $this->assertTrue(isset($expected[$key]));
        //    $this->assertEquals($expected[$key], $value);
        //    unset($expected[$key]);
        //}
        //$this->assertEquals(3, $count);

        //// The list of values should now be empty
        //$this->assertEquals([], $expected);

    }

    /**
     * APC will only remove expired items from the cache during the next test,
     * so we can't fully test these.
     *
     * Instead, we test if the parameter is set but then don't check for a
     * result.
     *
     * So this test is not complete, but that's the best we can do.
     */
    function testSetMultipleExpireDateIntervalInt() {

        $values = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $cache = $this->getCache();
        $cache->setMultiple($values, 1);

        // Wait 2 seconds so the cache expires
        //sleep(2);

        $result = $cache->getMultiple(array_keys($values), 'not-found');
        $this->assertTrue($result instanceof Traversable || is_array($result));
        //$count = 0;

        //$expected = [
        //    'key1' => 'not-found',
        //    'key2' => 'not-found',
        //    'key3' => 'not-found',
        //];

        //foreach ($result as $key => $value) {
        //    $count++;
        //    $this->assertTrue(isset($expected[$key]));
        //    $this->assertEquals($expected[$key], $value);
        //    unset($expected[$key]);
        //}
        //$this->assertEquals(3, $count);

        //// The list of values should now be empty
        //$this->assertEquals([], $expected);

    }
}
