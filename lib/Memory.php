<?php declare (strict_types=1);

namespace Sabre\Cache;

use DateInterval;
use DateTime;
use Psr\SimpleCache\CacheInterface;

/**
 * The Memory cache just stores everything in PHP memory. This cache is gone
 * once the process ends.
 *
 * This is useful as a test-double or for long-running processes that just need
 * a local fast cache.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (https://evertpot.com/)
 * @license http://sabre.io/license/
 */
class Memory implements CacheInterface {

    protected $cache = [];

    use MultipleTrait;

    /**
     * Fetches a value from the cache.
     *
     * @param string $key     The unique key of this item in the cache.
     * @param mixed  $default Default value to return if the key does not exist.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     * @return mixed The value of the item from the cache, or $default in case of cache miss.
     */
    function get($key, $default = null) {

        if (!is_string($key)) {
            throw new InvalidArgumentException('$key must be a string');
        }
        if (!isset($this->cache[$key])) {
            return $default;
        }
        list($expire, $value) = $this->cache[$key];
        if (!is_null($expire) && $expire < time()) {
            // If a ttl was set and it expired in the past, invalidate the
            // cache.
            $this->delete($key);
            return $default;
        }
        return $value;

    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an
     * optional expiration TTL time.
     *
     * @param string                $key   The key of the item to store.
     * @param mixed                 $value The value of the item to store, must
     *                                     be serializable.
     * @param null|int|DateInterval $ttl   Optional. The TTL value of this item.
     *                                     If no value is sent and the driver
     *                                     supports TTL then the library may set
     *                                     a default value for it or let the
     *                                     driver take care of that.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     * @return bool True on success and false on failure.
     */
    function set($key, $value, $ttl = null) {

        if (!is_string($key)) {
            throw new InvalidArgumentException('$key must be a string');
        }
        if ($ttl instanceof DateInterval) {
            $expire = (new DateTime('now'))->add($ttl)->getTimeStamp();
        } elseif (is_int($ttl) || ctype_digit($ttl)) {
            $expire = time() + $ttl;
        } else {
            $expire = null;
        }
        $this->cache[$key] = [$expire, $value];

        return true;

    }

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key The unique cache key of the item to delete.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     * @return bool True if the item was successfully removed. False if there was an error.
     */
    function delete($key) {

        if (!is_string($key)) {
            throw new InvalidArgumentException('$key must be a string');
        }
        unset($this->cache[$key]);
        return true;

    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    function clear() {

        $this->cache = [];
        return true;

    }

    /**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming
     * type purposes and not to be used within your live applications operations
     * for get/set, as this method is subject to a race condition where your
     * has() will return true and immediately after, another script can remove
     * it making the state of your app out of date.
     *
     * @param string $key The cache item key.
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     * @return bool
     */
    function has($key) {

        if (!is_string($key)) {
            throw new InvalidArgumentException('$key must be a string');
        }
        return isset($this->cache[$key]);

    }
}
