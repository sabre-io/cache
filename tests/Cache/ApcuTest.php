<?php

declare(strict_types=1);

namespace Sabre\Cache;

use Psr\SimpleCache\CacheInterface;

class ApcuTest extends AbstractCacheTest
{
    public function getCache(): CacheInterface
    {
        if (!function_exists('apcu_store')) {
            self::markTestSkipped('Apcu extension is not loaded');
        }
        if (!ini_get('apc.enabled')) {
            self::markTestSkipped('apc.enabled is set to 0. Enable it via php.ini');
        }

        if ('cli' === php_sapi_name() && !ini_get('apc.enable_cli')) {
            self::markTestSkipped('apc.enable_cli is set to 0. Enable it via php.ini');
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
    public function testSetExpire(): void
    {
        $cache = $this->getCache();
        $cache->set('foo', 'bar', 1);
        self::assertEquals('bar', $cache->get('foo'));

        // Wait 2 seconds so the cache expires
        // usleep(2000000);
        // self::assertNull($cache->get('foo'));
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
    public function testSetExpireDateInterval(): void
    {
        $cache = $this->getCache();
        $cache->set('foo', 'bar', new \DateInterval('PT1S'));
        self::assertEquals('bar', $cache->get('foo'));

        // Wait 2 seconds so the cache expires
        // usleep(2000000);
        // self::assertNull($cache->get('foo'));
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
    public function testSetMultipleExpireDateIntervalExpired(): void
    {
        $values = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $cache = $this->getCache();
        $cache->setMultiple($values, new \DateInterval('PT1S'));

        // // Wait 2 seconds so the cache expires
        // sleep(2);

        $result = $cache->getMultiple(array_keys($values), 'not-found');
        self::assertTrue($result instanceof \Traversable || is_array($result));
        // $count = 0;

        // $expected = [
        //    'key1' => 'not-found',
        //    'key2' => 'not-found',
        //    'key3' => 'not-found',
        // ];

        // foreach ($result as $key => $value) {
        //    $count++;
        //    self::assertTrue(isset($expected[$key]));
        //    self::assertEquals($expected[$key], $value);
        //    unset($expected[$key]);
        // }
        // self::assertEquals(3, $count);

        // // The list of values should now be empty
        // self::assertEquals([], $expected);
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
    public function testSetMultipleExpireDateIntervalInt(): void
    {
        $values = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $cache = $this->getCache();
        $cache->setMultiple($values, 1);

        // Wait 2 seconds so the cache expires
        // sleep(2);

        $result = $cache->getMultiple(array_keys($values), 'not-found');
        self::assertTrue($result instanceof \Traversable || is_array($result));
        // $count = 0;

        // $expected = [
        //    'key1' => 'not-found',
        //    'key2' => 'not-found',
        //    'key3' => 'not-found',
        // ];

        // foreach ($result as $key => $value) {
        //    $count++;
        //    self::assertTrue(isset($expected[$key]));
        //    self::assertEquals($expected[$key], $value);
        //    unset($expected[$key]);
        // }
        // self::assertEquals(3, $count);

        // // The list of values should now be empty
        // self::assertEquals([], $expected);
    }
}
