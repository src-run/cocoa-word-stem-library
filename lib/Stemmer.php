<?php

/*
 * This file is part of the `src-run/cocoa-stemmer-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\Cocoa\Stemmer;

use Psr\Cache\CacheItemPoolInterface;
use SR\Cocoa\Stemmer\Driver\DriverInterface;

class Stemmer implements StemmerInterface
{
    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var null|CacheItemPoolInterface
     */
    private $cache;

    /**
     * @param DriverInterface             $driver
     * @param CacheItemPoolInterface|null $cache
     */
    public function __construct(DriverInterface $driver, CacheItemPoolInterface $cache = null)
    {
        $this->driver = $driver;
        $this->cache = $cache;
    }

    /**
     * @param string $word
     *
     * @return string
     */
    public function stemWord(string $word): string
    {
        return $this->cache ? $this->askDriverCached($word) : $this->askDriver($word);
    }

    /**
     * @param string[] $words
     *
     * @return string[]
     */
    public function stemList(array $words): array
    {
        return array_map(function (string $word) {
            return $this->stemWord($word);
        }, $words);
    }

    /**
     * @param string $word
     *
     * @return string
     */
    private function askDriver(string $word): string
    {
        return $this->driver->stem($word);
    }

    /**
     * @param string $word
     *
     * @return string
     */
    private function askDriverCached(string $word): string
    {
        $item = $this->cache->getItem(sprintf('%s.%s', __CLASS__, strtolower($word)));

        if (!$item->isHit()) {
            $item->set($this->driver->stem($word));
            $this->cache->save($item);
        }

        return $item->get();
    }
}
