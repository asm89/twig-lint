<?php

namespace Acme\Standards\TwigCS\Sniffs;

use Asm89\Twig\Lint\Sniffs\AbstractPreParserSniff;
use Asm89\Twig\Lint\Tokenizer\Token;

class DummySniff extends AbstractPreParserSniff
{
    public function process(Token $token, $tokenPosition, $stream)
    {
        // This is dummy.
        return $token;
    }
}
