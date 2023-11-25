<?php

declare(strict_types=1);

namespace Sabre\Cache;

use DateInterval;
use Traversable;

/**
 * This trait implements the 'multiple' functions of PSR-16.
 *
 * Caches that don't natively support 'multiple' operations can use this trait
 * for easy implementation.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (https://evertpot.com/)
 * @license http://sabre.io/license/
 */
trait MultipleTrait
{
    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable<string> $keys    A list of keys that can be obtained in a single operation.
     * @param mixed            $default Default value to return for keys that do not exist.
     *
     * @return iterable<string, mixed> A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        if (!is_array($keys) && !$keys instanceof \Traversable) {
            throw new InvalidArgumentException('$keys must be traversable');
        }

        foreach ($keys as $key) {
            yield $key => $this->get($key, $default);
        }
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable               $values A list of key => value pairs for a multiple-set operation.
     * @param null|int|\DateInterval $ttl    Optional. The TTL value of this item. If no value is sent and
     *                                       the driver supports TTL then the library may set a default value
     *                                       for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $values is neither an array nor a Traversable,
     *   or if any of the $values are not a legal value.
     */
    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        if (!is_array($values) && !$values instanceof \Traversable) {
            throw new InvalidArgumentException('$values must be traversable');
        }

        $result = true;
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable<string> $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function deleteMultiple(iterable $keys): bool
    {
        if (!is_array($keys) && !$keys instanceof \Traversable) {
            throw new InvalidArgumentException('$keys must be traversable');
        }

        $result = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $result = false;
            }
        }

        return $result;
    }
}
