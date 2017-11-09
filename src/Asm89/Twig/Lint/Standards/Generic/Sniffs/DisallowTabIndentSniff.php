<?php

namespace Asm89\Twig\Lint\Standards\Generic\Sniffs;

use Asm89\Twig\Lint\Tokenizer\Token;
use Asm89\Twig\Lint\Sniffs\AbstractPreParserSniff;

class DisallowTabIndentSniff extends AbstractPreParserSniff
{
    /**
     * {@inheritdoc}
     */
    public function process(Token $token, $tokenPosition, $tokens)
    {
        if ($this->isTokenMatching($token, Token::TAB_TYPE)) {
            $this->addMessage($this::MESSAGE_TYPE_WARNING, 'Indentation using tabs is not allowed; use spaces instead', $token);
        }

        return $token;
    }
}
