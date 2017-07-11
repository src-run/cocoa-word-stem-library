<?php

/*
 * This file is part of the `src-run/cocoa-stemmer-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\Cocoa\Stemmer\Tests;

use PHPUnit\Framework\TestCase;
use SR\Cocoa\Stemmer\Driver\PorterDriver;
use SR\Cocoa\Stemmer\Driver\SnowballDriver;
use SR\Cocoa\Stemmer\Stemmer;
use SR\Cocoa\Stemmer\Tests\Driver\PorterDriverTest;
use SR\Cocoa\Stemmer\Tests\Driver\SnowballDriverTest;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * @covers \SR\Cocoa\Stemmer\Stemmer
 */
class StemmerTest extends TestCase
{
    /**
     * @return \Generator
     */
    public static function providePorterData(): \Generator
    {
        foreach (PorterDriverTest::getVocabularyLoader()->get() as $data) {
            yield $data;
        }
    }

    /**
     * @param string $word
     * @param string $stem
     *
     * @dataProvider providePorterData
     */
    public function testPorter(string $word, string $stem)
    {
        $this->assertSame($stem, static::getPorterStemmer()->stemWord($word));
    }

    /**
     * @return \Generator
     */
    public static function providePorterListData(): \Generator
    {
        $loader = PorterDriverTest::getVocabularyLoader();

        yield [$loader->words(), $loader->stems()];
    }

    /**
     * @param array $words
     * @param array $stems
     *
     * @dataProvider providePorterListData
     */
    public function testPorterList(array $words, array $stems)
    {
        $this->assertSame($stems, static::getPorterStemmer()->stemList($words));
    }

    /**
     * @return \Generator
     */
    public static function providePorterSentenceData(): \Generator
    {
        $loader = PorterDriverTest::getVocabularyLoader();

        yield [implode(' ', $loader->words()), $loader->stems()];
    }

    /**
     * @param string   $sentence
     * @param string[] $expected
     *
     * @dataProvider providePorterSentenceData
     */
    public function testPorterSentence(string $sentence, array $expected)
    {
        $this->assertSame($expected, static::getPorterStemmer()->stemSentence($sentence));
    }

    /**
     * @return \Generator
     */
    public static function provideSnowballData(): \Generator
    {
        foreach (SnowballDriverTest::getVocabularyLoader()->get() as $data) {
            yield $data;
        }
    }

    /**
     * @param string $word
     * @param string $stem
     *
     * @dataProvider provideSnowballData
     */
    public function testSnowball(string $word, string $stem)
    {
        static::markTestSkipped('Snowball implementation not completed yet!');

        $this->assertSame($stem, static::getSnowballStemmer()->stemWord($word));
    }

    /**
     * @return \Generator
     */
    public static function provideSnowballListData(): \Generator
    {
        $loader = PorterDriverTest::getVocabularyLoader();

        yield [$loader->words(), $loader->stems()];
    }

    /**
     * @param array $words
     * @param array $stems
     *
     * @dataProvider providePorterListData
     */
    public function testSnowballList(array $words, array $stems)
    {
        static::markTestSkipped('Snowball implementation not completed yet!');

        $this->assertSame($stems, static::getSnowballStemmer()->stemList($words));
    }

    /**
     * @return Stemmer
     */
    private static function getPorterStemmer(): Stemmer
    {
        static $instance;

        if ($instance === null) {
            $instance = new Stemmer(new PorterDriver());
        }

        return $instance;
    }

    /**
     * @return Stemmer
     */
    private static function getPorterCachedStemmer(): Stemmer
    {
        static $instance;

        if ($instance === null) {
            $instance = new Stemmer(new PorterDriver(), new ArrayAdapter());
        }

        return $instance;
    }

    /**
     * @return Stemmer
     */
    private static function getSnowballStemmer(): Stemmer
    {
        static $instance;

        if ($instance === null) {
            $instance = new Stemmer(new SnowballDriver());
        }

        return $instance;
    }

    /**
     * @return Stemmer
     */
    private static function getSnowballCachedStemmer(): Stemmer
    {
        static $instance;

        if ($instance === null) {
            $instance = new Stemmer(new SnowballDriver(), new ArrayAdapter());
        }

        return $instance;
    }
}
