<?php declare (strict_types=1);

namespace Sabre\Cache;

use DateInterval;
use DateTime;
use Psr\SimpleCache\CacheInterface;
use Traversable;

/**
 * The Apcu Cache uses the apcu_* functions from PHP to manage the cache.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (https://evertpot.com/)
 * @license http://sabre.io/license/
 */
class Apcu implements CacheInterface {

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

        $value = apcu_fetch($key, $success);
        if (!$success) {
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
            // Converting to a TTL in seconds
            $ttl = (new DateTime('now'))->add($ttl)->getTimeStamp() - time();
        }
        return apcu_store($key, $value, (int)$ttl);

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
        return apcu_delete($key);

    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    function clear() {

        return apcu_clear_cache();

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
        return apcu_exists($key);

    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable              $values A list of key => value pairs for a
     *                                      multiple-set operation.
     * @param null|int|DateInterval $ttl    Optional. The TTL value of this
     *                                      item. If no value is sent and the
     *                                      driver supports TTL then the library
     *                                      may set a default value for it or
     *                                      let the driver take care of that.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $values is neither an array nor a Traversable,
     *   or if any of the $values are not a legal value.
     * @return bool True on success and false on failure.
     */
    function setMultiple($values, $ttl = null) {

        if (!is_array($values) && !$values instanceof Traversable) {
            throw new InvalidArgumentException('$values must be traversable');
        }

        if ($ttl instanceof DateInterval) {
            // Converting to a TTL in seconds
            $ttl = (new DateTime('now'))->add($ttl)->getTimeStamp() - time();
        }
    
        if ($values instanceof Traversable) {
            $values = iterator_to_array($values);
        }

        return apcu_store($values, null, (int)$ttl);

    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys A list of string-based keys to be deleted.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     * @return bool True if the items were successfully removed. False if there
     * was an error.
     */
    function deleteMultiple($keys) {

        if ($keys instanceof Traversable) {
            $keys = iterator_to_array($keys);
        } elseif (!is_array($keys)) {
            throw new InvalidArgumentException('$keys must be iterable');
        }

        return apcu_delete($keys);

    }
}
