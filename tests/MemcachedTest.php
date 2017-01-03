<?php declare (strict_types=1);

namespace Sabre\Cache;

use Psr\SimpleCache\CacheInterface;

class MemcachedTest extends AbstractCacheTest {

    function getCache() : CacheInterface {

        if (!class_exists('Memcached')) {
            $this->markTestSkipped('Memcached extension is not loaded');
        }

        if (!isset($_SERVER['MEMCACHED_SERVER'])) {
            $this->markTestSkipped('MEMCACHED_SERVER environment variable is not set');
        }

        $memcached = new \Memcached();
        $memcached->addServer($_SERVER['MEMCACHED_SERVER'], 11211);

        return new Memcached($memcached);

    }

}
