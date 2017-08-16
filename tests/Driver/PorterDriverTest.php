<?php

/*
 * This file is part of the `src-run/cocoa-word-stem-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\Cocoa\Word\Stem\Tests\Driver;

use PHPUnit\Framework\TestCase;
use SR\Cocoa\Word\Stem\Driver\PorterDriver;
use SR\Cocoa\Word\Stem\Tests\Fixtures\VocabularyLoader;

/**
 * @covers \SR\Cocoa\Word\Stem\Driver\PorterDriver
 */
class PorterDriverTest extends TestCase
{
    /**
     * @return VocabularyLoader
     */
    public static function getVocabularyLoader(): VocabularyLoader
    {
        static $loader;

        if ($loader === null) {
            $loader = new VocabularyLoader(__DIR__ . '/../Fixtures/Porter');
        }

        return $loader;
    }

    /**
     * @return \Generator
     */
    public static function providePorterDriverStemmingData(): \Generator
    {
        foreach (static::getVocabularyLoader()->getExpectations() as $word => $stem) {
            yield [$word, $stem];
        }
    }

    /**
     * @group stem-driver
     *
     * @dataProvider providePorterDriverStemmingData
     *
     * @param string $word
     * @param string $stem
     */
    public function testPorterDriverStemming(string $word, string $stem)
    {
        $this->assertSame($stem, (new PorterDriver())->stem($word));
    }
}
