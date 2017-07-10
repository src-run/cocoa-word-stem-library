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

interface StemmerInterface
{
    /**
     * @param string $word
     *
     * @return string
     */
    public function stemWord(string $word): string;

    /**
     * @param string[] $words
     *
     * @return string[]
     */
    public function stemList(array $words): array;
}
