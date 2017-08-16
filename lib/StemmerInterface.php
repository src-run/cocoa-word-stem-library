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

interface StemmerInterface
{
    /**
     * Expects either a string containing one or more words or an array of words and returns the corresponding stems array.
     *
     * @param mixed $value The word/words/sentence to stem
     *
     * @return string[] The stems of the passed words
     */
    public function stem($value): array;

    /**
     * Expects a single word string and returns the corresponding stem string.
     *
     * @param string $word The word to stem
     *
     * @return string The stem of the passed word
     */
    public function stemWord(string $word): string;

    /**
     * Expects an array of words and returns the corresponding stems array.
     *
     * @param string[] $words The words to stem
     *
     * @return string[] The stems of the passed words
     */
    public function stemArray(array $words): array;

    /**
     * Expects a string containing any number of words (for example a sentence) and returns the corresponding stems array.
     *
     * @param string $sentence The word(s) to stem as a string sentence
     *
     * @return string[] The stems of the passed words
     */
    public function stemSentence(string $sentence): array;
}
