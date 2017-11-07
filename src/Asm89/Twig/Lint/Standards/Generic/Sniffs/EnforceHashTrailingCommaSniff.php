<?php

namespace Asm89\Twig\Lint\Standards\Generic\Sniffs;

use Asm89\Twig\Lint\Tokenizer\Token;
use Asm89\Twig\Lint\Sniffs\AbstractPreParserSniff;

class EnforceHashTrailingCommaSniff extends AbstractPreParserSniff
{
    /**
     * {@inheritdoc}
     */
    public function process(Token $token, $tokenPosition, $tokens)
    {
        if ($this->isTokenMatching($token, Token::PUNCTUATION_TYPE, '}')) {
            $i = $tokenPosition - 1;
            while ($this->isTokenMatching($tokens[$i], Token::WHITESPACE_TYPE) || $this->isTokenMatching($tokens[$i], Token::EOL_TYPE)) {
                --$i;
            }

            if (1 < ($tokenPosition - $i) && !$this->isTokenMatching($tokens[$i], Token::PUNCTUATION_TYPE, ',')) {
                $this->addMessage($this::MESSAGE_TYPE_WARNING, sprintf('Hash requires trailing comma after %s',
                    (!$this->isTokenMatching($tokens[$i], Token::STRING_TYPE)) ? '\'' . $tokens[$i]->getValue() . '\'' : $tokens[$i]->getValue()), $tokens[$i]);
            }
        }

        return $token;
    }
}
