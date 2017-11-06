<?php

namespace Asm89\Twig\Lint\Standards\Generic\Sniffs;

use Asm89\Twig\Lint\Tokenizer\Token;
use Asm89\Twig\Lint\Sniffs\AbstractPreParserSniff;

class EnforceEOLatEOFSniff extends AbstractPreParserSniff
{
    /**
     * {@inheritdoc}
     */
    public function process(Token $token, $tokenPosition, $tokens)
    {
        if ($this->isTokenMatching($token, Token::VAR_START_TYPE)) {
            $i = 1;
            while ($this->isTokenMatching($tokens[$tokenPosition + $i], Token::WHITESPACE_TYPE) || $this->isTokenMatching($tokens[$tokenPosition + $i], Token::EOL_TYPE)) {
                ++$i;
            }

            $this->getReport()->addMessage($this::MESSAGE_TYPE_WARNING, 'Extra EOL or whitespace (x' . ($i - 1) . ') after start of expression', $token->getLine());
        }

        if ($this->isTokenMatching($token, Token::EOF_TYPE)) {
            $i = 1;
            while ($this->isTokenMatching($tokens[$tokenPosition - $i], Token::EOL_TYPE)) {
                ++$i;
            }

            $this->getReport()->addMessage($this::MESSAGE_TYPE_WARNING, 'Extra EOL (x' . ($i - 1) . ') at end of file', $token->getLine());
        }

        return $token;
    }
}
