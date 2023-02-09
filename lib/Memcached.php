<?php

declare(strict_types=1);

namespace Sabre\Cache;

use Psr\SimpleCache\CacheInterface;
use Traversable;

/**
 * The Memcached cache uses Memcache to store values.
 *
 * This is a simple PSR-16 wrapper around memcached. To get it going, pass a
 * fully instantiated Memcached object to its constructor.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (https://evertpot.com/)
 * @license http://sabre.io/license/
 */
class Memcached implements CacheInterface
{
    use MultipleTrait;
    protected \Memcached $memcached;

    /**
     * Creates the PSR-16 Memcache implementation.
     */
    public function __construct(\Memcached $memcached)
    {
        $this->memcached = $memcached;
    }

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
    public function get($key, $default = null)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('$key must be a string');
        }
        $result = $this->memcached->get($key);
        if (false === $result) {
            // Note: result can be false because the key exists and has the value boolean false,
            //       or because something went wrong (cache miss, memcached server timeout...)
            // So we need to check the result code to work out what to do.
            $resultCode = $this->memcached->getResultCode();
            if (\Memcached::RES_BAD_KEY_PROVIDED === $resultCode) {
                // The key was a string but is invalid. Maybe it was too long, contained whitespace etc.
                throw new InvalidArgumentException('$key was not valid');
            }
            if (\Memcached::RES_SUCCESS !== $resultCode) {
                // The result might have been RES_NOTFOUND, or some other problem looking up the key.
                // The memcached server might be down or...
                // In any of these cases we want to return the specified default.
                return $default;
            }
        }

        return $result;
    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an
     * optional expiration TTL time.
     *
     * @param string                        $key   the key of the item to store
     * @param mixed                         $value the value of the item to store, must
     *                                             be serializable
     * @param int|string|\DateInterval|null $ttl   Optional. The TTL value of this item.
     *                                             If no value is sent and the driver
     *                                             supports TTL then the library may set
     *                                             a default value for it or let the
     *                                             driver take care of that.
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

        $expire = 0;
        if (isset($ttl)) {
            if ($ttl instanceof \DateInterval) {
                $expire = (new \DateTime('now'))->add($ttl)->getTimeStamp();
            } elseif (is_int($ttl) || (is_string($ttl) && ctype_digit($ttl))) {
                $expire = time() + $ttl;
            }
        }

        return $this->memcached->set($key, $value, $expire);
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

        return $this->memcached->delete($key);
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool true on success and false on failure
     */
    public function clear(): bool
    {
        return $this->memcached->flush();
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
        $result = $this->memcached->get($key);
        if (false === $result && \Memcached::RES_NOTFOUND === $this->memcached->getResultCode()) {
            return false;
        }

        return true;
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * This particular implementation returns its result as a generator.
     *
     * @param iterable $keys    a list of keys that can obtained in a single
     *                          operation
     * @param mixed    $default default value to return for keys that do not
     *                          exist
     *
     * @return iterable A list of key => value pairs. Cache keys that do not
     *                  exist or are stale will have $default as value.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *                                                   MUST be thrown if $keys is neither an array nor a Traversable,
     *                                                   or if any of the $keys are not a legal value
     */
    public function getMultiple($keys, $default = null): iterable
    {
        if ($keys instanceof \Traversable) {
            $keys = iterator_to_array($keys);
        } elseif (!is_array($keys)) {
            throw new InvalidArgumentException('$keys must be iterable');
        }

        $result = $this->memcached->getMulti($keys);
        foreach ($keys as $key) {
            if (!isset($result[$key])) {
                $result[$key] = $default;
            }
        }

        return $result;
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable                      $values a list of key => value pairs for a
     *                                              multiple-set operation
     * @param int|string|\DateInterval|null $ttl    Optional. The TTL value of this
     *                                              item. If no value is sent and the
     *                                              driver supports TTL then the library
     *                                              may set a default value for it or
     *                                              let the driver take care of that.
     *
     * @return bool true on success and false on failure
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *                                                   MUST be thrown if $values is neither an array nor a Traversable,
     *                                                   or if any of the $values are not a legal value
     */
    public function setMultiple($values, $ttl = null): bool
    {
        if ($values instanceof \Traversable) {
            $values = iterator_to_array($values);
        } elseif (!is_array($values)) {
            throw new InvalidArgumentException('$values must be iterable');
        }

        $expire = 0;
        if (isset($ttl)) {
            if ($ttl instanceof \DateInterval) {
                $expire = (new \DateTime('now'))->add($ttl)->getTimeStamp();
            } elseif (is_int($ttl) || (is_string($ttl) && ctype_digit($ttl))) {
                $expire = time() + $ttl;
            }
        }

        return $this->memcached->setMulti(
            $values,
            $expire
        );
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys a list of string-based keys to be deleted
     *
     * @return bool True if the items were successfully removed. False if there
     *              was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *                                                   MUST be thrown if $keys is neither an array nor a Traversable,
     *                                                   or if any of the $keys are not a legal value
     */
    public function deleteMultiple($keys): bool
    {
        if ($keys instanceof \Traversable) {
            $keys = iterator_to_array($keys);
        } elseif (!is_array($keys)) {
            throw new InvalidArgumentException('$keys must be iterable');
        }
        $this->memcached->deleteMulti($keys);

        return true;
    }
}
