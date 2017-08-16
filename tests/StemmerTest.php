<?php

/*
 * This file is part of the `src-run/cocoa-word-stem-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\Cocoa\Word\Stem\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use SR\Cocoa\Word\Stem\Driver\DriverInterface;
use SR\Cocoa\Word\Stem\Stemmer;
use SR\Cocoa\Word\Stem\Tests\Driver\PorterDriverTest;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\CacheItem;

/**
 * @covers \SR\Cocoa\Word\Stem\Stemmer
 */
class StemmerTest extends TestCase
{
    /**
     * @return \Generator
     */
    public static function provideStemWordData(): \Generator
    {
        foreach (PorterDriverTest::getVocabularyLoader()->getExpectationsSubset() as $word => $stem) {
            yield [$word, $stem];
        }
    }

    /**
     * @group stemmer-word
     *
     * @dataProvider provideStemWordData
     *
     * @param string $word
     * @param string $stem
     */
    public function testStemWord(string $word, string $stem)
    {
        $driver = $this->getDriverMockForStemWord($word, $stem);

        $this->assertSame($stem, static::getStemmer($driver)->stemWord($word));
    }

    /**
     * @group stemmer-word
     *
     * @dataProvider provideStemWordData
     *
     * @param string $word
     * @param string $stem
     */
    public function testStemAsWord(string $word, string $stem)
    {
        $driver = $this->getDriverMockForStemWord($word, $stem);

        $this->assertSame([$stem], static::getStemmer($driver)->stem($word));
    }

    /**
     * @return \Generator
     */
    public static function provideStemArrayData(): \Generator
    {
        $loader = PorterDriverTest::getVocabularyLoader();

        for ($i = 0; $i < 10; $i++) {
            yield $loader->getExpectationsSubsetArray();
        }
    }

    /**
     * @group stemmer-words
     *
     * @dataProvider provideStemArrayData
     *
     * @param string[] $words
     * @param string[] $stems
     */
    public function testStemArray(array $words, array $stems)
    {
        $driver = $this->getDriverMockForStemMultiple($words, $stems);

        $this->assertSame($stems, static::getStemmer($driver)->stemArray($words));
    }

    /**
     * @group stemmer-words
     *
     * @dataProvider provideStemArrayData
     *
     * @param string[] $words
     * @param string[] $stems
     */
    public function testStemAsArray(array $words, array $stems)
    {
        $driver = $this->getDriverMockForStemMultiple($words, $stems);

        $this->assertSame($stems, static::getStemmer($driver)->stem($words));
    }

    /**
     * @return \Generator
     */
    public static function provideStemSentenceData(): \Generator
    {
        $loader = PorterDriverTest::getVocabularyLoader();

        for ($i = 0; $i < 10; $i++) {
            list($words, $stems) = $loader->getExpectationsSubsetArray();
            yield [implode(' ', $words), $stems];
        }
    }

    /**
     * @group stemmer-sentence
     *
     * @dataProvider provideStemSentenceData
     *
     * @param string   $sentence
     * @param string[] $stems
     */
    public function testStemSentence(string $sentence, array $stems)
    {
        $driver = $this->getDriverMockForStemMultiple(explode(' ', $sentence), $stems);

        $this->assertSame($stems, static::getStemmer($driver)->stemSentence($sentence));
    }

    /**
     * @group stemmer-sentence
     *
     * @dataProvider provideStemSentenceData
     *
     * @param string   $sentence
     * @param string[] $stems
     */
    public function testStemAsSentence(string $sentence, array $stems)
    {
        $driver = $this->getDriverMockForStemMultiple(explode(' ', $sentence), $stems);

        $this->assertSame($stems, static::getStemmer($driver)->stem($sentence));
    }

    /**
     * @return \Generator
     */
    public static function provideStemmerWithCacheData(): \Generator
    {
        foreach (PorterDriverTest::getVocabularyLoader()->getExpectationsSubset() as $word => $stem) {
            yield [$word, $stem];
        }
    }

    /**
     * @group stemmer-cached
     *
     * @dataProvider provideStemmerWithCacheData
     *
     * @param string $word
     * @param string $stem
     */
    public function testStemmerWithCache(string $word, string $stem)
    {
        $driver = $this
            ->getMockBuilder(DriverInterface::class)
            ->getMock();

        $driver
            ->expects($this->atLeastOnce())
            ->method('stem')
            ->with($word)
            ->willReturn($stem);

        $cache = $this
            ->getMockBuilder(ArrayAdapter::class)
            ->getMock();

        $cache
            ->expects($this->atLeastOnce())
            ->method('getItem')
            ->willReturn(new CacheItem());

        $cache
            ->expects($this->atLeastOnce())
            ->method('save');

        $stemmer = static::getStemmer($driver, $cache);

        $this->assertSame($stem, $stemmer->stemWord($word));
        $this->assertSame([$stem], $stemmer->stem($word));
        $this->assertSame([$stem], $stemmer->stemSentence($word));
        $this->assertSame([$stem], $stemmer->stemArray([$word]));
    }

    /**
     * @param string $word
     * @param string $stem
     *
     * @return DriverInterface
     */
    private function getDriverMockForStemWord(string $word, string $stem): DriverInterface
    {
        $driver = $this
            ->getMockBuilder(DriverInterface::class)
            ->getMock();

        $driver
            ->expects($this->atLeastOnce())
            ->method('stem')
            ->with($word)
            ->willReturn($stem);

        return $driver;
    }

    /**
     * @param array $words
     * @param array $stems
     *
     * @return DriverInterface
     */
    private function getDriverMockForStemMultiple(array $words, array $stems): DriverInterface
    {
        $driver = $this
            ->getMockBuilder(DriverInterface::class)
            ->getMock();

        $count = count($words);

        for($i = 0; $i < $count; $i++) {
            $driver
                ->expects($this->at($i))
                ->method('stem')
                ->with($words[$i])
                ->willReturn($stems[$i]);
        }

        return $driver;
    }

    /**
     * @param DriverInterface             $driver
     * @param CacheItemPoolInterface|null $cache
     *
     * @return Stemmer
     */
    private static function getStemmer(DriverInterface $driver, CacheItemPoolInterface $cache = null): Stemmer
    {
        return new Stemmer($driver, $cache);
    }
}
