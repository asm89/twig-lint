<?php

namespace Asm89\Twig\Lint\Sniffs;

use Asm89\Twig\Lint\Tokenizer\Token;

interface PreParserSniffInterface extends SniffInterface
{
    public function process(Token $token, $tokenPosition, $stream);
}
