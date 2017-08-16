<?php

/*
 * This file is part of the `src-run/cocoa-word-stem-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\Cocoa\Word\Stem\Tests\Fixtures;

use SR\Exception\Logic\InvalidArgumentException;
use SR\Exception\Runtime\RuntimeException;

final class VocabularyLoader
{
    /**
     * @var string
     */
    private static $wordFile = 'words.txt';

    /**
     * @var string
     */
    private static $stemFile = 'stems.txt';

    /**
     * @var string
     */
    private $path;

    /**
     * @var string[]
     */
    private $words = [];

    /**
     * @var string[]
     */
    private $stems = [];

    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @return string[]
     */
    public function getWords(): array
    {
        return $this->lazyInitialize()->words;
    }

    /**
     * @return string[]
     */
    public function getStems(): array
    {
        return $this->lazyInitialize()->stems;
    }

    /**
     * @return \Generator
     */
    public function getExpectations(): \Generator
    {
        $size = count($this->lazyInitialize()->words);

        for ($i = 0; $i < $size; $i++) {
            yield $this->words[$i] => $this->stems[$i];
        }
    }

    /**
     * @return \Generator
     */
    public function getExpectationsSubset(): \Generator
    {
        list($words, $stems) = $this->getExpectationsSubsetArray();
        $size = count($words);

        for ($i = 0; $i < $size; $i++) {
            yield $words[$i] => $stems[$i];
        }
    }

    /**
     * @return array[]
     */
    public function getExpectationsSubsetArray(): array
    {
        $words = $this->lazyInitialize()->words;
        $stems = $this->stems;

        $this->assignRandomSubset($words, $stems, 1, 20);

        return [$words, $stems];
    }

    /**
     * @return self
     */
    private function lazyInitialize(): self
    {
        if (empty($this->words)) {
            $this->words = $this->getListing(static::$wordFile);
            $this->stems = $this->getListing(static::$stemFile);

            if (count($this->words) !== count($this->stems)) {
                throw new RuntimeException('Number of words does not match number of stems!');
            }

            if (getenv('STEM_TEST_COMPREHENSIVE') === false) {
                $this->assignRandomSubset($this->words, $this->stems, 10, 50);
            }
        }

        return $this;
    }

    /**
     * @param string[] $words
     * @param string[] $stems
     * @param int      $randomMin
     * @param int      $randomMax
     */
    private function assignRandomSubset(array &$words, array &$stems, int $randomMin, int $randomMax): void
    {
        $c = count($words);
        $w = $s = [];

        for ($i = mt_rand(0, $randomMax); $i < $c; $i += mt_rand($randomMin, $randomMax)) {
            $w[] = $words[$i];
            $s[] = $stems[$i];
        }

        $words = $w;
        $stems = $s;
    }

    /**
     * @param string $file
     *
     * @return array
     */
    private function getListing(string $file): array
    {
        return $this->sanitizeListing($this->fetchListing($file));
    }

    /**
     * @param array $entries
     *
     * @return array
     */
    private function sanitizeListing(array $entries): array
    {
        return array_values(array_filter(array_map(function (string $w) {
            return trim($w);
        }, $entries), function (string $w) {
            return strlen($w) > 0;
        }));
    }

    /**
     * @param string $file
     *
     * @return array
     */
    private function fetchListing(string $file): array
    {
        if (false === $contents = file_get_contents(sprintf('%s/%s', $this->path, $file))) {
            throw new InvalidArgumentException(sprintf('Unable to fetch vocabulary file "%s" from "%s".', $file, $this->path));
        }

        if (0 === count($list = explode("\n", $contents))) {
            throw new InvalidArgumentException('Unable to create array from fetched vocabulary.');
        }

        return $list;
    }
}
