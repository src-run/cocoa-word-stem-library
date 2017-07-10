<?php

/*
 * This file is part of the `src-run/cocoa-stemmer-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\Cocoa\Stemmer\Tests\Fixtures;

use SR\Exception\Logic\InvalidArgumentException;

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
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @return string[]
     */
    public function words(): array
    {
        return $this->fetchVocabularyList(static::$wordFile);
    }

    /**
     * @return string[]
     */
    public function stems(): array
    {
        return $this->fetchVocabularyList(static::$stemFile);
    }

    /**
     * @return \Generator
     */
    public function get(): \Generator
    {
        $words = $this->words();
        $stems = $this->stems();

        for ($i = 0; $i < count($words); $i++) {
            yield [$words[$i], $stems[$i]];
        }
    }

    /**
     * @param string $file
     *
     * @return string[]
     */
    private function fetchVocabularyList(string $file): array
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
