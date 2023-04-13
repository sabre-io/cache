<?php

declare(strict_types=1);

namespace Sabre\Cache;

use Psr\SimpleCache\CacheInterface;
use Traversable;

/**
 * The Apcu Cache uses the apcu_* functions from PHP to manage the cache.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (https://evertpot.com/)
 * @license http://sabre.io/license/
 */
class Apcu implements CacheInterface
{
    use MultipleTrait;

    /**
     * Fetches a value from the cache.
     *
     * @param string $key     the unique key of this item in the cache
     * @param mixed  $default default value to return if the key does not exist
     *
     * @return mixed the value of the item from the cache, or $default in case of cache miss
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *                                                   MUST be thrown if the $key string is not a legal value
     */
    public function get(string $key, mixed $default = null): mixed
    {
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
     * @param string                 $key   the key of the item to store
     * @param mixed                  $value the value of the item to store, must
     *                                      be serializable
     * @param int|\DateInterval|null $ttl   Optional. The TTL value of this item.
     *                                      If no value is sent and the driver
     *                                      supports TTL then the library may set
     *                                      a default value for it or let the
     *                                      driver take care of that.
     *
     * @return bool true on success and false on failure
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *                                                   MUST be thrown if the $key string is not a legal value
     */
    public function set($key, $value, $ttl = null): bool
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('$key must be a string');
        }
        if ($ttl instanceof \DateInterval) {
            // Converting to a TTL in seconds
            $ttl = (new \DateTime('now'))->add($ttl)->getTimeStamp() - time();
        }

        return apcu_store($key, $value, (int) $ttl);
    }

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key the unique cache key of the item to delete
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *                                                   MUST be thrown if the $key string is not a legal value
     */
    public function delete($key): bool
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('$key must be a string');
        }

        return apcu_delete($key);
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool true on success and false on failure
     */
    public function clear(): bool
    {
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
     * @param string $key the cache item key
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *                                                   MUST be thrown if the $key string is not a legal value
     */
    public function has($key): bool
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('$key must be a string');
        }

        return apcu_exists($key);
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable               $values a list of key => value pairs for a
     *                                       multiple-set operation
     * @param int|\DateInterval|null $ttl    Optional. The TTL value of this
     *                                       item. If no value is sent and the
     *                                       driver supports TTL then the library
     *                                       may set a default value for it or
     *                                       let the driver take care of that.
     *
     * @return bool|array true on success and false on failure (or array of true-false)
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *                                                   MUST be thrown if $values is neither an array nor a Traversable,
     *                                                   or if any of the $values are not a legal value
     */
    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        if (!is_array($values) && !$values instanceof \Traversable) {
            throw new InvalidArgumentException('$values must be traversable');
        }

        if ($ttl instanceof \DateInterval) {
            // Converting to a TTL in seconds
            $ttl = (new \DateTime('now'))->add($ttl)->getTimeStamp() - time();
        }

        if ($values instanceof \Traversable) {
            $values = iterator_to_array($values);
        }

        return apcu_store($values, null, (int) $ttl);
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys a list of string-based keys to be deleted
     *
     * @return bool|array True if the items were successfully removed. False if there
     *                    was an error. (or array of true-false)
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *                                                   MUST be thrown if $keys is neither an array nor a Traversable,
     *                                                   or if any of the $keys are not a legal value
     */
    public function deleteMultiple(iterable $keys): bool
    {
        if ($keys instanceof \Traversable) {
            $keys = iterator_to_array($keys);
        } elseif (!is_array($keys)) {
            throw new InvalidArgumentException('$keys must be iterable');
        }

        return apcu_delete($keys);
    }
}
