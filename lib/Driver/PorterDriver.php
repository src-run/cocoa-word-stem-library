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
        $this->word = $word;
        $this->runSteps();

        return $this->word;
    }

    /**
     * @return void
     */
    private function runSteps(): void
    {
        if (strlen($this->word) <= 2) {
            return;
        }

        $this->algorithmStep1a();
        $this->algorithmStep1b();
        $this->algorithmStep1c();
        $this->algorithmStep2();
        $this->algorithmStep3();
        $this->algorithmStep4();
        $this->algorithmStep5a();
        $this->algorithmStep5b();
    }

    /**
     * @param \Closure[] ...$closures
     *
     * @return bool
     */
    private function runClosuresAsLogicalOr(\Closure ...$closures): bool
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
    private function runClosuresAsLogicalAnd(\Closure ...$closures): bool
    {
        foreach ($closures as $c) {
            if (!$c()) {
                return false;
            }
        }

        return true;
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
     * @param string   $comparison
     * @param int      $start
     * @param int|null $length
     *
     * @return bool
     */
    private function subStrEquals(string $comparison, int $start, int $length = null): bool
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
    private function subStrNotEquals(string $comparison, int $start, int $length = null): bool
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
        return preg_match($pattern, $string ?: $this->word, $matches) ? true : false;
    }

    /**
     * @param string      $pattern
     * @param string|null $string
     *
     * @return array|null
     */
    private function matchReturn(string $pattern, string $string = null): ?array
    {
        $matches = [];

        if ($this->match($pattern, $string, $matches)) {
            return $matches;
        }

        return null;
    }

    /**
     * @return void
     */
    private function algorithmStep1a(): void
    {
        if ($this->subStrNotEquals('s', -1)) {
            return;
        }

        $this->runClosuresAsLogicalOr(
            function () { return $this->replace('sses', 'ss'); },
            function () { return $this->replace('ies', 'i'); },
            function () { return $this->replace('ss', 'ss'); },
            function () { return $this->replace('s', ''); }
        );
    }

    /**
     * @return void
     */
    private function algorithmStep1b(): void
    {
        if ($this->subStrNotEquals('e', -2, 1) || !$this->replace('eed', 'ee', 0)) {
            if ((false !== $this->match(sprintf('{%s+}', self::REGEX_VOWELS), $this->subStr(0, -3)) && $this->replace('ing', '')) ||
                (false !== $this->match(sprintf('{%s+}', self::REGEX_VOWELS), $this->subStr(0, -2)) && $this->replace('ed', '')))
            {
                if (!$this->replace('at', 'ate') &&
                    !$this->replace('bl', 'ble') &&
                    !$this->replace('iz', 'ize'))
                {
                    if ($this->hasDoubleConsonant($this->word) &&
                        substr($this->word, -2) != 'll' &&
                        substr($this->word, -2) != 'ss' &&
                        substr($this->word, -2) != 'zz')
                    {
                        $this->word = substr($this->word, 0, -1);
                    } else if (self::consonantSequenceCount($this->word) == 1 && $this->hasCvcSequence($this->word)) {
                        $this->word .= 'e';
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
        $v = self::REGEX_VOWELS;

        if (substr($this->word, -1) == 'y' && preg_match("#$v+#", substr($this->word, 0, -1))) {
            $this->replace('y', 'i');
        }
    }

    /**
     * @return void
     */
    private function algorithmStep2(): void
    {
        switch (substr($this->word, -2, 1)) {
            case 'a':
                $this->replace('ational', 'ate', 0) ||
                $this->replace('tional', 'tion', 0);
                break;

            case 'c':
                $this->replace('enci', 'ence', 0) ||
                $this->replace('anci', 'ance', 0);
                break;

            case 'e':
                $this->replace('izer', 'ize', 0);
                break;

            case 'g':
                $this->replace('logi', 'log', 0);
                break;

            case 'l':
                $this->replace('entli', 'ent', 0) ||
                $this->replace('ousli', 'ous', 0) ||
                $this->replace('alli', 'al', 0) ||
                $this->replace('bli', 'ble', 0) ||
                $this->replace('eli', 'e', 0);
                break;

            case 'o':
                $this->replace('ization', 'ize', 0) ||
                $this->replace('ation', 'ate', 0) ||
                $this->replace('ator', 'ate', 0);
                break;

            case 's':
                $this->replace('iveness', 'ive', 0) ||
                $this->replace('fulness', 'ful', 0) ||
                $this->replace('ousness', 'ous', 0) ||
                $this->replace('alism', 'al', 0);
                break;

            case 't':
                $this->replace('biliti', 'ble', 0) ||
                $this->replace('aliti', 'al', 0) ||
                $this->replace('iviti', 'ive', 0);
                break;
        }
    }

    /**
     * @return void
     */
    private function algorithmStep3(): void
    {
        switch (substr($this->word, -2, 1)) {
            case 'a':
                $this->replace('ical', 'ic', 0);
                break;

            case 's':
                $this->replace('ness', '', 0);
                break;

            case 't':
                $this->replace('icate', 'ic', 0) ||
                $this->replace('iciti', 'ic', 0);
                break;

            case 'u':
                $this->replace('ful', '', 0);
                break;

            case 'v':
                $this->replace('ative', '', 0);
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
        switch (substr($this->word, -2, 1)) {
            case 'a':
                $this->replace('al', '', 1);
                break;

            case 'c':
                $this->replace('ance', '', 1) ||
                $this->replace('ence', '', 1);
                break;

            case 'e':
                $this->replace('er', '', 1);
                break;

            case 'i':
                $this->replace('ic', '', 1);
                break;

            case 'l':
                $this->replace('able', '', 1) ||
                $this->replace('ible', '', 1);
                break;

            case 'n':
                $this->replace('ant', '', 1) ||
                $this->replace('ement', '', 1) ||
                $this->replace('ment', '', 1) ||
                $this->replace('ent', '', 1);
                break;

            case 'o':
                if (substr($this->word, -4) == 'tion' || substr($this->word, -4) == 'sion') {
                    $this->replace('ion', '', 1);
                } else {
                    $this->replace('ou', '', 1);
                }
                break;

            case 's':
                $this->replace('ism', '', 1);
                break;

            case 't':
                $this->replace('ate', '', 1) ||
                $this->replace('iti', '', 1);
                break;

            case 'u':
                $this->replace('ous', '', 1);
                break;

            case 'v':
                $this->replace('ive', '', 1);
                break;

            case 'z':
                $this->replace('ize', '', 1);
                break;
        }
    }

    /**
     * @return void
     */
    private function algorithmStep5a(): void
    {
        if (substr($this->word, -1) == 'e') {
            if (self::consonantSequenceCount(substr($this->word, 0, -1)) > 1) {
                $this->replace('e', '');
            }
            elseif (self::consonantSequenceCount(substr($this->word, 0, -1)) == 1 && !$this->hasCvcSequence(substr($this->word, 0, -1))) {
                $this->replace('e', '');
            }
        }
    }

    /**
     * @return void
     */
    private function algorithmStep5b(): void
    {
        if (self::consonantSequenceCount($this->word) > 1 && $this->hasDoubleConsonant($this->word) && substr($this->word, -1) == 'l') {
            $this->word = substr($this->word, 0, -1);
        }
    }


    /**
     * Replaces the first string with the second, at the end of the string. If third
     * arg is given, then the preceding string must match that m count at least.
     *
     * @param  string $str   String to check
     * @param  string $search Ending to check for
     * @param  string $replace  Replacement string
     * @param  int    $consonantSeqMin     Optional minimum number of m() to meet
     * @return bool          Whether the $check string was at the end
     *                       of the $str string. True does not necessarily mean
     *                       that it was replaced.
     */
    private function replace($search, $replace, $consonantSeqMin = null)
    {
        $searchPosition = 0 - strlen($search);

        if (substr($this->word, $searchPosition) == $search) {
            $subString = substr($this->word, 0, $searchPosition);

            if (null === $consonantSeqMin || self::consonantSequenceCount($subString) > $consonantSeqMin) {
                $this->word = $subString . $replace;
            }

            return true;
        }

        return false;
    }


    /**
     * What, you mean it's not obvious from the name?
     *
     * m() measures the number of consonant sequences in $str. if c is
     * a consonant sequence and v a vowel sequence, and <..> indicates arbitrary
     * presence,
     *
     * <c><v>       gives 0
     * <c>vc<v>     gives 1
     * <c>vcvc<v>   gives 2
     * <c>vcvcvc<v> gives 3
     *
     * @param  string $string The string to return the m count for
     * @return int         The m count
     */
    private static function consonantSequenceCount(string $string): int
    {
        $c = self::REGEX_CONSONANTS;
        $v = self::REGEX_VOWELS;

        $string = preg_replace(sprintf('{^%s+}', self::REGEX_CONSONANTS), '', $string);
        $string = preg_replace(sprintf('{%s+$}', self::REGEX_VOWELS), '', $string);

        preg_match_all(sprintf('{(?<groups>%s+%s+)}', self::REGEX_VOWELS, self::REGEX_CONSONANTS), $string, $matches);

        return count($matches['groups']);
    }


    /**
     * Determines if the passed string contains two of the same consonants at the end of it.
     *
     * @param string $string
     *
     * @return bool
     */
    private function hasDoubleConsonant(string $string): bool
    {
        if (null === $matches = $this->matchReturn(sprintf('{%s{2}$}', self::REGEX_CONSONANTS), $string)) {
            return false;
        }

        return $matches[0]{0} === $matches[0]{1};
    }


    /**
     * Determines if passed string has a "CVC" sequence, where a second C is not W, X, or Y.
     *
     * @param string $string
     *
     * @return bool
     */
    private function hasCvcSequence(string $string): bool
    {
        $matches = [];

        return $this->match(sprintf('{(%1$s%2$s%1$s)$}', self::REGEX_CONSONANTS, self::REGEX_VOWELS), $string, $matches) && $this->runClosuresAsLogicalAnd(
            function () use ($matches) {
                return strlen($matches[1]) === 3;
            },
            function () use ($matches) {
                return $matches[1]{2} !== 'w';
            },
            function () use ($matches) {
                return $matches[1]{2} !== 'x';
            },
            function () use ($matches) {
                return $matches[1]{2} !== 'y';
            }
        );
    }
}
