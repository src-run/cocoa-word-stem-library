<?php

/*
 * This file is part of the `src-run/cocoa-stemmer-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\Cocoa\Stemmer\Driver;

class PorterDriver implements DriverInterface
{
    /**
     * @var string
     */
    private const REGEX_VOWELS = '(?:[aeiou]|(?<![aeiou])y)';

    /**
     * @var string
     */
    private const REGEX_CONSONANTS = '(?:[bcdfghjklmnpqrstvwxz]|(?<=[aeiou])y|^y)';

    /**
     * @var string[]
     */
    private const ALGORITHM_STEPS = ['1a', '1b', '1c', '2', '3', '4', '5a', '5b'];

    /**
     * @var string
     */
    private $word;

    /**
     * @param string $word
     *
     * @return string
     */
    public function stem(string $word): string
    {
        if (strlen($this->word = $word) > 2) {
            foreach (self::ALGORITHM_STEPS as $s) {
                $this->{sprintf('algorithmStep%s', $s)}();
            }
        }

        return $this->word;
    }

    /**
     * @return void
     */
    private function algorithmStep1a(): void
    {
        if ($this->isEqualTo('s', -1)) {
            $this->runAsLogicalOr(
                function () { return $this->replace('sses', 'ss'); },
                function () { return $this->replace('ies', 'i'); },
                function () { return $this->replace('ss', 'ss'); },
                function () { return $this->remove('s'); }
            );
        }
    }

    /**
     * @return void
     */
    private function algorithmStep1b(): void
    {
        $hasEndingIng = function () {
            return $this->hasVowel($this->subStr(0, -3)) && $this->replace('ing');
        };

        $hasEndingEd = function () {
            return $this->hasVowel($this->subStr(0, -2)) && $this->replace('ed');
        };

        $hasRemovableDoubleConsonant = function () {
            return $this->runAsLogicalAnd(
                function () { return $this->hasDoubleConsonant(); },
                function () { return $this->isNotEqualTo('ll', -2); },
                function () { return $this->isNotEqualTo('ss', -2); },
                function () { return $this->isNotEqualTo('zz', -2); }
            );
        };

        if ($this->isNotEqualTo('e', -2, 1) || !$this->replace('eed', 'ee', 0)) {
            if ($hasEndingIng() || $hasEndingEd()) {
                if (!$this->replace('at', 'ate') &&
                    !$this->replace('bl', 'ble') &&
                    !$this->replace('iz', 'ize'))
                {
                    if ($hasRemovableDoubleConsonant()) {
                        $this->applySubStr(0, -1);
                    } elseif ($this->isMeasureSingular() && $this->hasCvcSequence()) {
                        $this->append('e');
                    }
                }
            }
        }
    }

    /**
     * @return void
     */
    private function algorithmStep1c(): void
    {
        if ($this->isEqualTo('y', -1) && $this->hasVowel($this->subStr(0, -1))) {
            $this->replace('y', 'i');
        }
    }

    /**
     * @return void
     */
    private function algorithmStep2(): void
    {
        switch ($this->subStr(-2, 1)) {
            case 'a':
                $this->runAsLogicalOr(
                    function () { return $this->replace('ational', 'ate', 0); },
                    function () { return $this->replace('tional', 'tion', 0); }
                );
                break;

            case 'c':
                $this->runAsLogicalOr(
                    function () { return $this->replace('enci', 'ence', 0); },
                    function () { return $this->replace('anci', 'ance', 0); }
                );
                break;

            case 'e':
                $this->replace('izer', 'ize', 0);
                break;

            case 'g':
                $this->replace('logi', 'log', 0);
                break;

            case 'l':
                $this->runAsLogicalOr(
                    function () { return $this->replace('entli', 'ent', 0); },
                    function () { return $this->replace('ousli', 'ous', 0); },
                    function () { return $this->replace('alli', 'al', 0); },
                    function () { return $this->replace('bli', 'ble', 0); },
                    function () { return $this->replace('eli', 'e', 0); }
                );
                break;

            case 'o':
                $this->runAsLogicalOr(
                    function () { return $this->replace('ization', 'ize', 0); },
                    function () { return $this->replace('ation', 'ate', 0); },
                    function () { return $this->replace('ator', 'ate', 0); }
                );
                break;

            case 's':
                $this->runAsLogicalOr(
                    function () { return $this->replace('iveness', 'ive', 0); },
                    function () { return $this->replace('fulness', 'ful', 0); },
                    function () { return $this->replace('ousness', 'ous', 0); },
                    function () { return $this->replace('alism', 'al', 0); }
                );
                break;

            case 't':
                $this->runAsLogicalOr(
                    function () { return $this->replace('biliti', 'ble', 0); },
                    function () { return $this->replace('aliti', 'al', 0); },
                    function () { return $this->replace('iviti', 'ive', 0); }
                );
                break;
        }
    }

    /**
     * @return void
     */
    private function algorithmStep3(): void
    {
        switch ($this->subStr(-2, 1)) {
            case 'a':
                $this->replace('ical', 'ic', 0);
                break;

            case 's':
                $this->remove('ness', 0);
                break;

            case 't':
                $this->runAsLogicalOr(
                    function () { return $this->replace('icate', 'ic', 0); },
                    function () { return $this->replace('iciti', 'ic', 0); }
                );
                break;

            case 'u':
                $this->remove('ful', 0);
                break;

            case 'v':
                $this->remove('ative', 0);
                break;

            case 'z':
                $this->replace('alize', 'al', 0);
                break;
        }
    }

    /**
     * @return void
     */
    private function algorithmStep4(): void
    {
        switch ($this->subStr(-2, 1)) {
            case 'a':
                $this->remove('al', 1);
                break;

            case 'c':
                $this->runAsLogicalOr(
                    function () { return $this->remove('ance', 1); },
                    function () { return $this->remove('ence', 1); }
                );
                break;

            case 'e':
                $this->remove('er', 1);
                break;

            case 'i':
                $this->remove('ic', 1);
                break;

            case 'l':
                $this->runAsLogicalOr(
                    function () { return $this->remove('able', 1); },
                    function () { return $this->remove('ible', 1); }
                );
                break;

            case 'n':
                $this->runAsLogicalOr(
                    function () { return $this->remove('ant', 1); },
                    function () { return $this->remove('ement', 1); },
                    function () { return $this->remove('ment', 1); },
                    function () { return $this->remove('ent', 1); }
                );
                break;

            case 'o':
                if ($this->isEqualTo('tion', -4) || $this->isEqualTo('sion', -4)) {
                    $this->remove('ion', 1);
                } else {
                    $this->remove('ou', 1);
                }
                break;

            case 's':
                $this->remove('ism', 1);
                break;

            case 't':
                $this->runAsLogicalOr(
                    function () { return $this->remove('ate', 1); },
                    function () { return $this->remove('iti', 1); }
                );
                break;

            case 'u':
                $this->remove('ous', 1);
                break;

            case 'v':
                $this->remove('ive', 1);
                break;

            case 'z':
                $this->remove('ize', 1);
                break;
        }
    }

    /**
     * @return void
     */
    private function algorithmStep5a(): void
    {
        if ($this->isNotEqualTo('e', -1)) {
            return;
        }

        $string = $this->subStr(0, -1);

        if ($this->isMeasureMultiple($string) || ($this->isMeasureSingular($string) && !$this->hasCvcSequence($string))) {
            $this->replace('e');
        }
    }

    /**
     * @return void
     */
    private function algorithmStep5b(): void
    {
        if ($this->isMeasureMultiple() && $this->hasDoubleConsonant() && $this->isEqualTo('l', -1)) {
            $this->applySubStr(0, -1);
        }
    }

    /**
     * Replaces a search for a replacement string at the end of the active word, optionally
     * limiting action by the consonant sequence count.
     *
     * @param string      $search
     * @param string|null $replace
     * @param int|null    $consonantSeqMin
     *
     * @return bool
     */
    private function replace(string $search, string $replace = null, int $consonantSeqMin = null): bool
    {
        $position = 0 - strlen($search);
        $replace = $replace === null ? '' : $replace;

        if (substr($this->word, $position) == $search) {
            $subString = substr($this->word, 0, $position);

            if (null === $consonantSeqMin || $this->measure($subString) > $consonantSeqMin) {
                $this->word = $subString . $replace;
            }

            return true;
        }

        return false;
    }

    /**
     * @param string   $search
     * @param int|null $consonantSeqMin
     *
     * @return bool
     */
    private function remove(string $search, int $consonantSeqMin = null): bool
    {
        return $this->replace($search, null, $consonantSeqMin);
    }

    /**
     * @param string $string
     */
    private function append(string $string): void
    {
        $this->word .= $string;
    }

    /**
     * Returns the number of consonant/vowel sequences found within the string.
     *
     * @param string|null $string
     *
     * @return int
     */
    private function measure(string $string = null): int
    {
        $string = null !== $string ? $string : $this->word;
        $string = preg_replace(sprintf('{^%s+}', self::REGEX_CONSONANTS), '', $string);
        $string = preg_replace(sprintf('{%s+$}', self::REGEX_VOWELS), '', $string);

        preg_match_all(sprintf('{(?<cv>%s+%s+)}', self::REGEX_VOWELS, self::REGEX_CONSONANTS), $string, $matches);

        return count($matches['cv'] ?? []);
    }

    /**
     * @param string|null $string
     *
     * @return bool
     */
    private function isMeasureSingular(string $string = null): bool
    {
        return $this->measure($string) === 1;
    }

    /**
     * @param string|null $string
     *
     * @return bool
     */
    private function isMeasureMultiple(string $string = null): bool
    {
        return $this->measure($string) > 1;
    }

    /**
     * Determines if the passed string contains two of the same consonants at the end of it.
     *
     * @return bool
     */
    private function hasDoubleConsonant(): bool
    {
        if (null === $matches = $this->getMatches(sprintf('{%s{2}$}', self::REGEX_CONSONANTS), $this->word)) {
            return false;
        }

        return $matches[0]{0} === $matches[0]{1};
    }

    /**
     * Determines if passed string has a "CVC" sequence, where a second C is not W, X, or Y.
     *
     * @param string|null $string
     *
     * @return bool
     */
    private function hasCvcSequence(string $string = null): bool
    {
        $string = null !== $string ? $string : $this->word;
        $matches = $this->getMatches(sprintf('{(?<cvc>%1$s%2$s%1$s)$}', self::REGEX_CONSONANTS, self::REGEX_VOWELS), $string);

        return $this->runAsLogicalAnd(
            function () use ($matches) { return null !== $matches && isset($matches['cvc']); },
            function () use ($matches) { return strlen($matches['cvc']) === 3; },
            function () use ($matches) { return $matches['cvc']{2} !== 'w'; },
            function () use ($matches) { return $matches['cvc']{2} !== 'x'; },
            function () use ($matches) { return $matches['cvc']{2} !== 'y'; }
        );
    }

    /**
     * @param string|null $string
     *
     * @return bool
     */
    private function hasVowel(string $string = null): bool
    {
        return $this->match(sprintf('{%s+}', self::REGEX_VOWELS), null !== $string ? $string : $this->word);
    }

    /**
     * @param int      $start
     * @param int|null $length
     *
     * @return string
     */
    private function subStr(int $start, int $length = null): string
    {
        return null === $length ? substr($this->word, $start) : substr($this->word, $start, $length);
    }

    /**
     * @param int      $start
     * @param int|null $length
     *
     * @return void
     */
    private function applySubStr(int $start, int $length = null): void
    {
        $this->word = $this->subStr($start, $length);
    }

    /**
     * @param string   $comparison
     * @param int      $start
     * @param int|null $length
     *
     * @return bool
     */
    private function isEqualTo(string $comparison, int $start, int $length = null): bool
    {
        return $this->subStr($start, $length) === $comparison;
    }

    /**
     * @param string   $comparison
     * @param int      $start
     * @param int|null $length
     *
     * @return bool
     */
    private function isNotEqualTo(string $comparison, int $start, int $length = null): bool
    {
        return $this->subStr($start, $length) !== $comparison;
    }

    /**
     * @param string      $pattern
     * @param string|null $string
     * @param array       $matches
     *
     * @return bool
     */
    private function match(string $pattern, string $string = null, array &$matches = []): bool
    {
        return 1 === preg_match($pattern, null !== $string ? $string : $this->word, $matches) ? true : false;
    }

    /**
     * @param string      $pattern
     * @param string|null $string
     *
     * @return array|null
     */
    private function getMatches(string $pattern, string $string = null): ?array
    {
        $matches = [];

        if ($this->match($pattern, $string, $matches)) {
            return $matches;
        }

        return null;
    }

    /**
     * @param \Closure[] ...$closures
     *
     * @return bool
     */
    private function runAsLogicalOr(\Closure ...$closures): bool
    {
        foreach ($closures as $c) {
            if ($c()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Closure[] ...$closures
     *
     * @return bool
     */
    private function runAsLogicalAnd(\Closure ...$closures): bool
    {
        foreach ($closures as $c) {
            if (!$c()) {
                return false;
            }
        }

        return true;
    }
}
