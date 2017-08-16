<?php

/*
 * This file is part of the `src-run/cocoa-word-stem-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\Cocoa\Word\Stem\Driver;

interface DriverInterface
{
    /**
     * @param string $word
     *
     * @return string
     */
    public function stem(string $word): string;
}
