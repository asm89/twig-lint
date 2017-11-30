<?php

/*
 * This file is part of twig-lint.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\Lint\Tokenizer;

/**
 * Represents a token from a twig template.
 *
 * This is inspired by \Twig_Token but this is not meant to be an exact match.
 *
 * @author Hussard <adrien.ricartnoblet@gmail.com>
 */
class Token
{
    const EOF_TYPE = -1;
    const TEXT_TYPE = 0;
    const BLOCK_START_TYPE = 1;
    const VAR_START_TYPE = 2;
    const BLOCK_END_TYPE = 3;
    const VAR_END_TYPE = 4;
    const NAME_TYPE = 5;
    const NUMBER_TYPE = 6;
    const STRING_TYPE = 7;
    const OPERATOR_TYPE = 8;
    const PUNCTUATION_TYPE = 9;
    const INTERPOLATION_START_TYPE = 10;
    const WHITESPACE_TYPE = 12;
    const TAB_TYPE = 13;
    const EOL_TYPE = 14;
    const COMMENT_START_TYPE = 15;
    const COMMENT_END_TYPE = 16;

    public function __construct($type, $lineno, $position, $filename, $value = null)
    {
        $this->type = $type;
        $this->lineno = $lineno;
        $this->position = $position;
        $this->filename = $filename;
        $this->value = $value;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getLine()
    {
        return $this->lineno;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function getValue()
    {
        return $this->value;
    }
}
