<?php

/*
 * This file is part of the `src-run/cocoa-stemmer-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\Cocoa\Stemmer\Primitive;

class WordPrimitive
{
    /**
     * @var string
     */
    private $word;

    /**
     * @param string $word
     */
    public function __construct(string $word)
    {
        $this->word = $word;
    }

    public function replace($search, $replacement)
    {

    }

    public function consonantSequenceCount(string $string)
    {

    }
}
