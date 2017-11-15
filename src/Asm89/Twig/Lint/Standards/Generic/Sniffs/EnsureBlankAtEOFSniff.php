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

/**
 * Ensure that files ends with one blank line.
 */
class EnsureBlankAtEOFSniff extends AbstractPreParserSniff
{
    /**
     * {@inheritdoc}
     */
    public function process(Token $token, $tokenPosition, $tokens)
    {
        // if ($this->isTokenMatching($token, Token::VAR_START_TYPE)) {
        //     $i = 1;
        //     while ($this->isTokenMatching($tokens[$tokenPosition + $i], Token::WHITESPACE_TYPE) || $this->isTokenMatching($tokens[$tokenPosition + $i], Token::EOL_TYPE)) {
        //         ++$i;
        //     }

        //     $this->addMessage(
        //         $this::MESSAGE_TYPE_WARNING,
        //         'Extra EOL or whitespace (x' . ($i - 1) . ') after start of expression',
        //         $token
        //     );
        // }

        if ($this->isTokenMatching($token, Token::EOF_TYPE)) {
            $i = 0;
            while ($this->isTokenMatching($tokens[$tokenPosition - ($i + 1)], Token::EOL_TYPE)) {
                ++$i;
            }

            if (1 !== $i) {
                // Either 0 or 2+ blank lines.
                $this->addMessage(
                    $this::MESSAGE_TYPE_WARNING,
                    sprintf('A file must end with 1 blank line; found %d', $i),
                    $token
                );
            }


        }

        return $token;
    }
}
