<?php

namespace Asm89\Twig\Lint\Standards\Generic\Sniffs;

use Asm89\Twig\Lint\Tokenizer\Token;
use Asm89\Twig\Lint\Sniffs\AbstractPreParserSniff;

class SimpleQuotesSniff extends AbstractPreParserSniff
{
    /**
     * {@inheritdoc}
     */
    public function process(Token $token, $tokenPosition, $tokens)
    {
        if ($this->isTokenMatching($token, Token::STRING_TYPE)) {
            $value = $token->getValue();
            if ($value[0] === '"' || $value[strlen($value) - 1] === '"') {
                $this->getReport()->addMessage($this::MESSAGE_TYPE_WARNING, sprintf('String \'%s\' does not require double quotes; use single quotes instead', $value), $token->getLine());
            }
        }
    }
}
