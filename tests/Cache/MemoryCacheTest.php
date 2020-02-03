<?php

declare(strict_types=1);

namespace Sabre\Cache;

use Psr\SimpleCache\CacheInterface;

class MemoryCacheTest extends AbstractCacheTest
{
    public function getCache(): CacheInterface
    {
        return new Memory();
    }
}
