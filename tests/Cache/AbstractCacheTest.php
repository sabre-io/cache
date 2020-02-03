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

    public function testSetGet()
    {
        $cache = $this->getCache();
        $cache->set('foo', 'bar');
        $this->assertEquals('bar', $cache->get('foo'));
    }

    /**
     * @depends testSetGet
     */
    public function testDelete()
    {
        $cache = $this->getCache();
        $cache->set('foo', 'bar');
        $this->assertEquals('bar', $cache->get('foo'));
        $cache->delete('foo');
        $this->assertNull($cache->get('foo'));
    }

    public function testGetInvalidArg()
    {
        $this->expectException(\Psr\SimpleCache\InvalidArgumentException::class);
        $cache = $this->getCache();
        $cache->get(null);
    }

    /**
     * @depends testDelete
     */
    public function testGetNotFound()
    {
        $cache = $this->getCache();
        $this->assertNull($cache->get('notfound'));
    }

    /**
     * @depends testDelete
     */
    public function testGetNotFoundDefault()
    {
        $cache = $this->getCache();
        $default = 'chickpeas';
        $this->assertEquals(
            $default,
            $cache->get('notfound', $default)
        );
    }

    /**
     * @depends testSetGet
     * @slow
     */
    public function testSetExpire()
    {
        $cache = $this->getCache();
        $cache->set('foo', 'bar', 1);
        $this->assertEquals('bar', $cache->get('foo'));

        // Wait 2 seconds so the cache expires
        usleep(2000000);
        $this->assertNull($cache->get('foo'));
    }

    /**
     * @depends testSetGet
     * @slow
     */
    public function testSetExpireDateInterval()
    {
        $cache = $this->getCache();
        $cache->set('foo', 'bar', new \DateInterval('PT1S'));
        $this->assertEquals('bar', $cache->get('foo'));

        // Wait 2 seconds so the cache expires
        usleep(2000000);
        $this->assertNull($cache->get('foo'));
    }

    public function testSetInvalidArg()
    {
        $this->expectException(\Psr\SimpleCache\InvalidArgumentException::class);
        $cache = $this->getCache();
        $cache->set(null, 'bar');
    }

    public function testDeleteInvalidArg()
    {
        $this->expectException(\Psr\SimpleCache\InvalidArgumentException::class);
        $cache = $this->getCache();
        $cache->delete(null);
    }

    /**
     * @depends testSetGet
     */
    public function testClearCache()
    {
        $cache = $this->getCache();
        $cache->set('foo', 'bar');
        $cache->clear();
        $this->assertNull($cache->get('foo'));
    }

    /**
     * @depends testSetGet
     */
    public function testHas()
    {
        $cache = $this->getCache();
        $cache->set('foo', 'bar');
        $this->assertTrue($cache->has('foo'));
    }

    /**
     * @depends testHas
     */
    public function testHasNot()
    {
        $cache = $this->getCache();
        $this->assertFalse($cache->has('not-found'));
    }

    public function testHasInvalidArg()
    {
        $this->expectException(\Psr\SimpleCache\InvalidArgumentException::class);
        $cache = $this->getCache();
        $cache->has(null);
    }

    /**
     * @depends testSetGet
     */
    public function testSetGetMultiple()
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
            $this->assertTrue(isset($values[$key]));
            $this->assertEquals($values[$key], $value);
            unset($values[$key]);
        }

        // The list of values should now be empty
        $this->assertEquals([], $values);
    }

    /**
     * @depends testSetGet
     */
    public function testSetGetMultipleGenerator()
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
            $this->assertTrue(isset($values[$key]));
            $this->assertEquals($values[$key], $value);
            unset($values[$key]);
        }

        // The list of values should now be empty
        $this->assertEquals([], $values);
    }

    /**
     * @depends testSetGet
     */
    public function testSetGetMultipleGenerator2()
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
            $this->assertTrue(isset($values[$key]));
            $this->assertEquals($values[$key], $value);
            unset($values[$key]);
        }

        // The list of values should now be empty
        $this->assertEquals([], $values);
    }

    /**
     * @depends testSetGetMultiple
     * @depends testSetExpire
     * @slow
     */
    public function testSetMultipleExpireDateIntervalNotExpired()
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
            $this->assertTrue(isset($values[$key]));
            $this->assertEquals($values[$key], $value);
            unset($values[$key]);
        }
        $this->assertEquals(3, $count);

        // The list of values should now be empty
        $this->assertEquals([], $values);
    }

    /**
     * @slow
     */
    public function testSetMultipleExpireDateIntervalExpired()
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
            $this->assertTrue(isset($expected[$key]));
            $this->assertEquals($expected[$key], $value);
            unset($expected[$key]);
        }
        $this->assertEquals(3, $count);

        // The list of values should now be empty
        $this->assertEquals([], $expected);
    }

    /**
     * @slow
     */
    public function testSetMultipleExpireDateIntervalInt()
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
            $this->assertTrue(isset($expected[$key]));
            $this->assertEquals($expected[$key], $value);
            unset($expected[$key]);
        }
        $this->assertEquals(3, $count);

        // The list of values should now be empty
        $this->assertEquals([], $expected);
    }

    public function testSetMultipleInvalidArg()
    {
        $this->expectException(\Psr\SimpleCache\InvalidArgumentException::class);
        $cache = $this->getCache();
        $cache->setMultiple(null);
    }

    public function testGetMultipleInvalidArg()
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
    public function testDeleteMultipleDefaultGet()
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
            $this->assertTrue(isset($expected[$key]));
            $this->assertEquals($expected[$key], $value);
            unset($expected[$key]);
        }

        // The list of values should now be empty
        $this->assertEquals([], $expected);
    }

    /**
     * @depends testSetGetMultiple
     */
    public function testDeleteMultipleGenerator()
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
            $this->assertTrue(isset($expected[$key]));
            $this->assertEquals($expected[$key], $value);
            unset($expected[$key]);
        }

        // The list of values should now be empty
        $this->assertEquals([], $expected);
    }

    public function testDeleteMultipleInvalidArg()
    {
        $this->expectException(\Psr\SimpleCache\InvalidArgumentException::class);
        $cache = $this->getCache();
        $cache->deleteMultiple(null);
    }
}
