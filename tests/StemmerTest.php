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
        return PorterDriverTest::getVocabularyLoader()->get();
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
    public static function provideSnowballData(): \Generator
    {
        return SnowballDriverTest::getVocabularyLoader()->get();
    }

    /**
     * @param string $word
     * @param string $stem
     *
     * @dataProvider provideSnowballData
     */
    public function testSnowball(string $word, string $stem)
    {
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
        $this->assertSame($stems, static::getSnowballStemmer()->stemList($words));
    }

    /**
     * @return Stemmer
     */
    private static function getPorterStemmer(): Stemmer
    {
        return new Stemmer(new PorterDriver());
    }

    /**
     * @return Stemmer
     */
    private static function getSnowballStemmer(): Stemmer
    {
        return new Stemmer(new SnowballDriver());
    }
}
