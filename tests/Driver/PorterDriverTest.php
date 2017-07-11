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
use SR\Cocoa\Stemmer\Driver\PorterDriver;
use SR\Cocoa\Stemmer\Tests\Fixtures\VocabularyLoader;

/**
 * @covers \SR\Cocoa\Stemmer\Driver\PorterDriver
 */
class PorterDriverTest extends TestCase
{
    /**
     * @return VocabularyLoader
     */
    public static function getVocabularyLoader(): VocabularyLoader
    {
        return new VocabularyLoader(__DIR__.'/../Fixtures/Porter');
    }

    /**
     * @return \Generator
     */
    public static function provideData(): \Generator
    {
        foreach (static::getVocabularyLoader()->get() as $data) {
            yield $data;
        }
    }

    /**
     * @param string $word
     * @param string $stem
     *
     * @dataProvider provideData
     */
    public function test(string $word, string $stem)
    {
        $this->assertSame($stem, (new PorterDriver())->stem($word));
    }
}
