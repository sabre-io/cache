<?php

declare(strict_types=1);

namespace Sabre\Cache;

use Psr\SimpleCache\CacheInterface;

class MemcachedTest extends AbstractCacheTest
{
    public function getCache(): CacheInterface
    {
        if (!class_exists('Memcached')) {
            self::markTestSkipped('Memcached extension is not loaded');
        }

        if (!isset($_SERVER['MEMCACHED_SERVER'])) {
            self::markTestSkipped('MEMCACHED_SERVER environment variable is not set');
        }

        $memcached = new \Memcached();
        $memcached->addServer($_SERVER['MEMCACHED_SERVER'], 11211);

        return new Memcached($memcached);
    }

    public function testGetWithWhitespaceInKey(): void
    {
        $this->expectException(\Psr\SimpleCache\InvalidArgumentException::class);
        $cache = $this->getCache();
        $cache->get('contains white space');
    }
}
