<?php

namespace Sabre\Cache;

use Psr\SimpleCache\CacheInterface;

/**
 * Abstract PSR-16 tester.
 *
 * Because all cache implementations should mostly behave the same way, they
 * can all extend this test.
 */
abstract class AbstractCacheTest extends \PHPUnit\Framework\TestCase
{
    abstract public function getCache(): CacheInterface;

    public function testSetGet(): void
    {
        $cache = $this->getCache();
        $cache->set('foo', 'bar');
        self::assertEquals('bar', $cache->get('foo'));
    }

    /**
     * @depends testSetGet
     */
    public function testDelete(): void
    {
        $cache = $this->getCache();
        $cache->set('foo', 'bar');
        self::assertEquals('bar', $cache->get('foo'));
        $cache->delete('foo');
        self::assertNull($cache->get('foo'));
    }

    public function testGetInvalidArg(): void
    {
        $this->expectException(\Psr\SimpleCache\InvalidArgumentException::class);
        $cache = $this->getCache();
        $cache->get(null);
    }

    /**
     * @depends testDelete
     */
    public function testGetNotFound(): void
    {
        $cache = $this->getCache();
        self::assertNull($cache->get('notfound'));
    }

    /**
     * @depends testDelete
     */
    public function testGetNotFoundDefault(): void
    {
        $cache = $this->getCache();
        $default = 'chickpeas';
        self::assertEquals(
            $default,
            $cache->get('notfound', $default)
        );
    }

    /**
     * @depends testSetGet
     *
     * @slow
     */
    public function testSetExpire(): void
    {
        $cache = $this->getCache();
        $cache->set('foo', 'bar', 1);
        self::assertEquals('bar', $cache->get('foo'));

        // Wait 2 seconds so the cache expires
        usleep(2000000);
        self::assertNull($cache->get('foo'));
    }

    /**
     * @depends testSetGet
     *
     * @slow
     */
    public function testSetExpireDateInterval(): void
    {
        $cache = $this->getCache();
        $cache->set('foo', 'bar', new \DateInterval('PT1S'));
        self::assertEquals('bar', $cache->get('foo'));

        // Wait 2 seconds so the cache expires
        usleep(2000000);
        self::assertNull($cache->get('foo'));
    }

    public function testSetInvalidArg(): void
    {
        $this->expectException(\Psr\SimpleCache\InvalidArgumentException::class);
        $cache = $this->getCache();
        $cache->set(null, 'bar');
    }

    public function testDeleteInvalidArg(): void
    {
        $this->expectException(\Psr\SimpleCache\InvalidArgumentException::class);
        $cache = $this->getCache();
        $cache->delete(null);
    }

    /**
     * @depends testSetGet
     */
    public function testClearCache(): void
    {
        $cache = $this->getCache();
        $cache->set('foo', 'bar');
        $cache->clear();
        self::assertNull($cache->get('foo'));
    }

    /**
     * @depends testSetGet
     */
    public function testHas(): void
    {
        $cache = $this->getCache();
        $cache->set('foo', 'bar');
        self::assertTrue($cache->has('foo'));
    }

    /**
     * @depends testHas
     */
    public function testHasNot(): void
    {
        $cache = $this->getCache();
        self::assertFalse($cache->has('not-found'));
    }

    public function testHasInvalidArg(): void
    {
        $this->expectException(\Psr\SimpleCache\InvalidArgumentException::class);
        $cache = $this->getCache();
        $cache->has(null);
    }

    public function testHasWithTtl(): void
    {
        $cache = $this->getCache();
        $cache->set('foo', 'bar', 1);
        self::assertTrue($cache->has('foo'));

        // Wait 2 seconds so the cache expires
        usleep(2000000);
        self::assertFalse($cache->has('foo'));
    }

    /**
     * @depends testSetGet
     */
    public function testSetGetMultiple(): void
    {
        $values = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $cache = $this->getCache();
        $cache->setMultiple($values);

        $result = $cache->getMultiple(array_keys($values));
        foreach ($result as $key => $value) {
            self::assertTrue(isset($values[$key]));
            self::assertEquals($values[$key], $value);
            unset($values[$key]);
        }

        // The list of values should now be empty
        self::assertEquals([], $values);
    }

    /**
     * @depends testSetGet
     */
    public function testSetGetMultipleGenerator(): void
    {
        $values = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $gen = function () use ($values) {
            foreach ($values as $key => $value) {
                yield $key => $value;
            }
        };

        $cache = $this->getCache();
        $cache->setMultiple($gen());

        $result = $cache->getMultiple(array_keys($values));
        foreach ($result as $key => $value) {
            self::assertTrue(isset($values[$key]));
            self::assertEquals($values[$key], $value);
            unset($values[$key]);
        }

        // The list of values should now be empty
        self::assertEquals([], $values);
    }

    /**
     * @depends testSetGet
     */
    public function testSetGetMultipleGenerator2(): void
    {
        $values = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $gen = function () use ($values) {
            foreach ($values as $key => $value) {
                yield $key;
            }
        };

        $cache = $this->getCache();
        $cache->setMultiple($values);

        $result = $cache->getMultiple($gen());
        foreach ($result as $key => $value) {
            self::assertTrue(isset($values[$key]));
            self::assertEquals($values[$key], $value);
            unset($values[$key]);
        }

        // The list of values should now be empty
        self::assertEquals([], $values);
    }

    /**
     * @depends testSetGetMultiple
     * @depends testSetExpire
     *
     * @slow
     */
    public function testSetMultipleExpireDateIntervalNotExpired(): void
    {
        $values = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $cache = $this->getCache();
        $cache->setMultiple($values, new \DateInterval('PT5S'));

        $result = $cache->getMultiple(array_keys($values));

        $count = 0;
        foreach ($result as $key => $value) {
            ++$count;
            self::assertTrue(isset($values[$key]));
            self::assertEquals($values[$key], $value);
            unset($values[$key]);
        }
        self::assertEquals(3, $count);

        // The list of values should now be empty
        self::assertEquals([], $values);
    }

    /**
     * @slow
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

        // Wait 2 seconds so the cache expires
        sleep(2);

        $result = $cache->getMultiple(array_keys($values), 'not-found');
        $count = 0;

        $expected = [
            'key1' => 'not-found',
            'key2' => 'not-found',
            'key3' => 'not-found',
        ];

        foreach ($result as $key => $value) {
            ++$count;
            self::assertTrue(isset($expected[$key]));
            self::assertEquals($expected[$key], $value);
            unset($expected[$key]);
        }
        self::assertEquals(3, $count);

        // The list of values should now be empty
        self::assertEquals([], $expected);
    }

    /**
     * @slow
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
        sleep(2);

        $result = $cache->getMultiple(array_keys($values), 'not-found');
        $count = 0;

        $expected = [
            'key1' => 'not-found',
            'key2' => 'not-found',
            'key3' => 'not-found',
        ];

        foreach ($result as $key => $value) {
            ++$count;
            self::assertTrue(isset($expected[$key]));
            self::assertEquals($expected[$key], $value);
            unset($expected[$key]);
        }
        self::assertEquals(3, $count);

        // The list of values should now be empty
        self::assertEquals([], $expected);
    }

    public function testSetMultipleInvalidArg(): void
    {
        $this->expectException(\Psr\SimpleCache\InvalidArgumentException::class);
        $cache = $this->getCache();
        $cache->setMultiple(null);
    }

    public function testGetMultipleInvalidArg(): void
    {
        $this->expectException(\Psr\SimpleCache\InvalidArgumentException::class);
        $cache = $this->getCache();
        $result = $cache->getMultiple(null);
        // If $result was a generator, the generator will only error once the
        // first value is requested.
        //
        // This extra line is just a precaution for that
        if ($result instanceof \Traversable) {
            $result->current();
        }
    }

    /**
     * @depends testSetGetMultiple
     */
    public function testDeleteMultipleDefaultGet(): void
    {
        $values = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $cache = $this->getCache();
        $cache->setMultiple($values);

        $cache->deleteMultiple(['key1', 'key3']);

        $result = $cache->getMultiple(array_keys($values), 'tea');

        $expected = [
            'key1' => 'tea',
            'key2' => 'value2',
            'key3' => 'tea',
        ];

        foreach ($result as $key => $value) {
            self::assertTrue(isset($expected[$key]));
            self::assertEquals($expected[$key], $value);
            unset($expected[$key]);
        }

        // The list of values should now be empty
        self::assertEquals([], $expected);
    }

    /**
     * @depends testSetGetMultiple
     */
    public function testDeleteMultipleGenerator(): void
    {
        $values = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $cache = $this->getCache();
        $cache->setMultiple($values);

        $gen = function () {
            yield 'key1';
            yield 'key3';
        };

        $cache->deleteMultiple($gen());

        $result = $cache->getMultiple(array_keys($values), 'tea');

        $expected = [
            'key1' => 'tea',
            'key2' => 'value2',
            'key3' => 'tea',
        ];

        foreach ($result as $key => $value) {
            self::assertTrue(isset($expected[$key]));
            self::assertEquals($expected[$key], $value);
            unset($expected[$key]);
        }

        // The list of values should now be empty
        self::assertEquals([], $expected);
    }

    public function testDeleteMultipleInvalidArg(): void
    {
        $this->expectException(\Psr\SimpleCache\InvalidArgumentException::class);
        $cache = $this->getCache();
        $cache->deleteMultiple(null);
    }
}
