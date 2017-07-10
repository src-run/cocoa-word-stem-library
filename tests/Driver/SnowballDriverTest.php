<?php

/*
 * This file is part of the `src-run/cocoa-stemmer-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\Cocoa\Stemmer\Tests\Driver;

use PHPUnit\Framework\TestCase;
use SR\Cocoa\Stemmer\Driver\SnowballDriver;
use SR\Cocoa\Stemmer\Tests\Fixtures\VocabularyLoader;

/**
 * @covers \SR\Cocoa\Stemmer\Driver\SnowballDriver
 */
class SnowballDriverTest extends TestCase
{
    /**
     * @return VocabularyLoader
     */
    public static function getVocabularyLoader(): VocabularyLoader
    {
        return new VocabularyLoader(__DIR__.'/../Fixtures/Snowball');
    }

    /**
     * @return \Generator
     */
    public static function provideData(): \Generator
    {
        return static::getVocabularyLoader()->get();
    }

    /**
     * @param string $word
     * @param string $stem
     *
     * @dataProvider provideData
     */
    public function test(string $word, string $stem)
    {
        $this->assertSame($stem, (new SnowballDriver())->stem($word));
    }
}
