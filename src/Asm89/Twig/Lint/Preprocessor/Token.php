<?php

namespace Asm89\Twig\Lint\Preprocessor;

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
    const EOL_TYPE = 13;
    const COMMENT_START_TYPE = 14;
    const COMMENT_END_TYPE = 15;
}
