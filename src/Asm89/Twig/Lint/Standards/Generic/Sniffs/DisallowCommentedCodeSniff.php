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
 * Disallow keeping commented code.
 *
 * This will be triggered if `{{` or `{%` is found inside a comment.
 */
class DisallowCommentedCodeSniff extends AbstractPreParserSniff
{
    /**
     * {@inheritdoc}
     */
    public function process(Token $token, $tokenPosition, $tokens)
    {
        if ($this->isTokenMatching($token, Token::COMMENT_START_TYPE)) {
            $i = $tokenPosition;
            $found = false;
            while (
                !$this->isTokenMatching($tokens[$i], Token::COMMENT_END_TYPE) || $this->isTokenMatching($tokens[$i], Token::EOF_TYPE)) {
                if (
                    $this->isTokenMatching($tokens[$i], Token::TEXT_TYPE, '{{')
                    || $this->isTokenMatching($tokens[$i], Token::TEXT_TYPE, '{%')
                ) {
                    $found = true;

                    break;
                }

                ++$i;
            }

            if ($found) {
                $this->addMessage(
                    $this::MESSAGE_TYPE_WARNING,
                    'Probable commented code found; keeping commented code is usually not advised',
                    $token
                );
            }
        }

        return $token;
    }
}
