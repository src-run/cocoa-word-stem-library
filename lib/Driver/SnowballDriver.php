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

class SnowballDriver implements DriverInterface
{
    /**
     * @var string[]
     */
    protected static $vowels = ['a', 'e', 'i', 'o', 'u', 'y'];

    /**
     * @var string[]
     */
    protected static $doubles = ['bb', 'dd', 'ff', 'gg', 'mm', 'nn', 'pp', 'rr', 'tt'];

    /**
     * @var string[]
     */
    protected static $endings = ['c', 'd', 'e', 'g', 'h', 'k', 'm', 'n', 'r', 't'];

    /**
     * @param string $word
     *
     * @return string
     */
    public function stem(string $word): string
    {
        return $word;
    }
}
