<?php

/*
 * This file is part of twig-lint.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\Lint\Standards\Generic\Sniffs;

use Asm89\Twig\Lint\Tokenizer\Token;
use Asm89\Twig\Lint\Sniffs\AbstractPreParserSniff;

class WhitespaceBeforeAfterExpression extends AbstractPreParserSniff
{
    /**
     * {@inheritdoc}
     */
    public function process(Token $token, $tokenPosition, $tokens)
    {
        if ($this->isTokenMatching($token, Token::VAR_START_TYPE) || $this->isTokenMatching($token, Token::BLOCK_START_TYPE)) {
            if (!$this->isTokenMatching($tokens[$tokenPosition + 1], Token::WHITESPACE_TYPE)) {
                $this->addMessage($this::MESSAGE_TYPE_WARNING, 'Missing whitespace BEFORE start of expression eg. "}}" or "%}"', $token);
            }
        }

        if ($this->isTokenMatching($token, Token::VAR_END_TYPE) || $this->isTokenMatching($token, Token::BLOCK_END_TYPE)) {
            if (!$this->isTokenMatching($tokens[$tokenPosition - 1], Token::WHITESPACE_TYPE)) {
                $this->addMessage($this::MESSAGE_TYPE_WARNING, 'Missing whitespace AFTER start of expression eg. "{{" or "{%"', $token);
            }
        }

        if ($this->isTokenMatching($token, Token::PUNCTUATION_TYPE, '{')) {
            if ($this->isTokenMatching($tokens[$tokenPosition + 1], Token::WHITESPACE_TYPE)) {
                $this->addMessage($this::MESSAGE_TYPE_WARNING, 'No whitespace allowed AFTER "{" token', $token);
            }
        }

        if ($this->isTokenMatching($token, Token::PUNCTUATION_TYPE, '}')) {
            if ($this->isTokenMatching($tokens[$tokenPosition - 1], Token::WHITESPACE_TYPE)) {
                $this->addMessage($this::MESSAGE_TYPE_WARNING, 'No whitespace allowed BEFORE "}" token', $token);
            }
        }

        return $token;
    }
}
