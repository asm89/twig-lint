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

            $this->addMessage($this::MESSAGE_TYPE_WARNING, 'Extra EOL or whitespace (x' . ($i - 1) . ') after start of expression', $token);
        }

        if ($this->isTokenMatching($token, Token::EOF_TYPE)) {
            $i = 1;
            while ($this->isTokenMatching($tokens[$tokenPosition - $i], Token::EOL_TYPE)) {
                ++$i;
            }

            $this->addMessage($this::MESSAGE_TYPE_WARNING, 'Extra EOL (x' . ($i - 1) . ') at end of file', $token);
        }

        return $token;
    }
}
