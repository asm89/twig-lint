<?php

namespace Asm89\Twig\Lint\Standards\Generic\Sniffs;

use Asm89\Twig\Lint\Tokenizer\Token;
use Asm89\Twig\Lint\Sniffs\AbstractPreParserSniff;

class EnforceHashKeyQuotesSniff extends AbstractPreParserSniff
{
    /**
     * {@inheritdoc}
     */
    public function process(Token $token, $tokenPosition, $tokens)
    {
        if ($this->isTokenMatching($token, Token::PUNCTUATION_TYPE, '{')) {
            $i = $tokenPosition + 1;
            $first = true;
            while (count($tokens) > $i && !$this->isTokenMatching($tokens[$i], Token::PUNCTUATION_TYPE, '}')) {
                $key = $value = '';
                if (!$this->isTokenMatching($tokens[$i], Token::WHITESPACE_TYPE) && !$this->isTokenMatching($tokens[$i], Token::EOL_TYPE)) {
                    if ($this->isTokenMatching($tokens[$i], Token::PUNCTUATION_TYPE, '(')) {
                        // Ignore complex expression.

                        break;
                    } elseif (!$this->isTokenMatching($tokens[$i], Token::STRING_TYPE)) {
                        $this->addMessage($this::MESSAGE_TYPE_WARNING, sprintf('Hash key \'%s\' requires single quotes', $tokens[$i]->getValue()), $tokens[$i]);
                    }

                    // Advance until reaching new hash key eg. `,` or the end of the hash eg. `}`.
                    while (!$this->isTokenMatching($tokens[$i], Token::PUNCTUATION_TYPE, ',') && !$this->isTokenMatching($tokens[$i], Token::PUNCTUATION_TYPE, '}')) {
                        ++$i;
                    }
                }

                if (!$this->isTokenMatching($tokens[$i], Token::PUNCTUATION_TYPE, '}')) {
                    ++$i;
                }
            }
        }

        return $token;
    }
}
