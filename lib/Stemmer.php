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
        return $this->cache ? $this->doStemCached($word) : $this->doStemDirect($word);
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
     * @param string $sentence
     *
     * @return string[]
     */
    public function stemSentence(string $sentence): array
    {
        return array_filter($this->stemList(preg_split('{[\s\r\t\n\f,.]+}', $sentence)), function ($word) {
            return strlen($word) > 0;
        });
    }

    /**
     * @param string $word
     *
     * @return string
     */
    private function doStemDirect(string $word): string
    {
        return $this->driver->stem($word);
    }

    /**
     * @param string $word
     *
     * @return string
     */
    private function doStemCached(string $word): string
    {
        $item = $this->cache->getItem($this->generateCacheKey($word));

        if (!$item->isHit()) {
            $item->set($this->doStemDirect($word));
            $this->cache->save($item);
        }

        return $item->get();
    }

    /**
     * @param string $word
     *
     * @return string
     */
    private function generateCacheKey(string $word): string
    {
        return strtolower(sprintf('%s.%s', str_replace('\\', '.', __CLASS__), $word));
    }
}
