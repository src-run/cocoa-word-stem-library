<?php

/*
 * This file is part of the `src-run/cocoa-word-stem-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\Cocoa\Word\Stem;

use Psr\Cache\CacheItemPoolInterface;
use SR\Cocoa\Word\Stem\Driver\DriverInterface;

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
     * {@inheritdoc}
     */
    public function stem($value): array
    {
        return is_array($value) ? $this->stemArray($value) : $this->stemSentence($value);
    }

    /**
     * {@inheritdoc}
     */
    public function stemWord(string $word): string
    {
        return $this->cache ? $this->processCached($word) : $this->processDirect($word);
    }

    /**
     * {@inheritdoc}
     */
    public function stemArray(array $words): array
    {
        return array_map(function (string $word) {
            return $this->stemWord($word);
        }, $this->sanitizeWords($words));
    }

    /**
     * {@inheritdoc}
     */
    public function stemSentence(string $sentence): array
    {
        return $this->stemArray(
            $this->sanitizeWords(preg_split('{[\s\r\t\n\f,.]+}', $sentence))
        );
    }

    /**
     * @param string[] $words
     *
     * @return array
     */
    private function sanitizeWords(array $words): array
    {
        return array_filter($words, function ($word) {
            return strlen($word) > 0;
        });
    }

    /**
     * @param string $word
     *
     * @return string
     */
    private function processCached(string $word): string
    {
        $item = $this->cache->getItem($this->getWordCacheKey($word));

        if (!$item->isHit()) {
            $item->set($this->processDirect($word));
            $this->cache->save($item);
        }

        return $item->get();
    }

    /**
     * @param string $word
     *
     * @return string
     */
    private function processDirect(string $word): string
    {
        return $this->driver->stem($word);
    }

    /**
     * @param string $word
     *
     * @return string
     */
    private function getWordCacheKey(string $word): string
    {
        static $context;

        if ($context === null) {
            $context = sprintf('sr-word-stem_%s-%s', spl_object_id($this), spl_object_id($this->driver));
        }

        return sprintf('%s_%s', $context, $word);
    }
}
