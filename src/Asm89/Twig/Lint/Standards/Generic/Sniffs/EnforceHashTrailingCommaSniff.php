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
                $this->addMessage(
                    $this::MESSAGE_TYPE_WARNING,
                    sprintf('Hash requires trailing comma after %s', $this->stringifyValue($tokens[$i])),
                    $tokens[$i]
                );
            }
        }

        return $token;
    }
}
