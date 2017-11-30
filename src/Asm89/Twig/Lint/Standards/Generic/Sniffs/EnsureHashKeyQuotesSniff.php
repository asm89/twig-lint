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

class EnsureHashKeyQuotesSniff extends AbstractPreParserSniff
{
    protected $processedToken;

    /**
     * {@inheritdoc}
     */
    public function process(Token $token, $tokenPosition, $tokens)
    {
        if (
            !$this->isTokenMatching($token, Token::PUNCTUATION_TYPE, '{')
            || (null !== $this->getProcessedToken()
            && $this->getProcessedToken() >= $tokenPosition)
        ) {
            return $token;
        }

        list($startPosition, $endPosition) = $this->findHashPositions($tokenPosition, $tokens);

        $j = $startPosition + 1;
        while ($j < $endPosition) {
            if (
                !$this->isTokenMatching($tokens[$j], Token::WHITESPACE_TYPE)
                && !$this->isTokenMatching($tokens[$j], Token::EOL_TYPE)
            ) {
                $keyTokens = array();
                while (!$this->isTokenMatching($tokens[$j], Token::PUNCTUATION_TYPE, ':') && $j < $endPosition) {
                    $keyTokens[] = $tokens[$j];
                    ++$j;
                }


                // Not a string with quotes ?
                if (1 === count($keyTokens) && !$this->isTokenMatching($keyTokens[0], Token::STRING_TYPE)) {
                    $this->addMessage(
                        $this::MESSAGE_TYPE_WARNING,
                        sprintf('Hash key \'%s\' requires quotes; use single quotes', $keyTokens[0]->getValue()),
                        $keyTokens[0]
                    );
                }

                // Skip until the end of the key: value pair eg. `,` or the start of a sub-hash eg. `{`.
                while (
                    !$this->isTokenMatching($tokens[$j], Token::PUNCTUATION_TYPE, ',')
                    && !$this->isTokenMatching($tokens[$j], Token::PUNCTUATION_TYPE, '{')
                    && $j < $endPosition
                ) {
                    ++$j;
                }
            }

            ++$j;
        }

        $this->setProcessedToken($endPosition);

        return $token;
    }

    public function findHashPositions($tokenPosition, $tokens)
    {
        $hashStarts = $hashEnds = array();

        $hashStarts[] = $tokenPosition;

        $i = $tokenPosition + 1;
        while (count($tokens) > $i && count($hashStarts) > count($hashEnds)) {
            if ($this->isTokenMatching($tokens[$i], Token::PUNCTUATION_TYPE, '{')) {
                array_push($hashStarts, $i);
            }

            if ($this->isTokenMatching($tokens[$i], Token::PUNCTUATION_TYPE, '}')) {
                array_unshift($hashEnds, $i);
            }

            ++$i;
        }

        return array($hashStarts[0], $hashEnds[count($hashEnds) - 1]);
    }

    public function setProcessedToken($processedToken)
    {
        $this->processedToken = $processedToken;
    }

    public function getProcessedToken()
    {
        return $this->processedToken;
    }

    public function disable()
    {
        parent::disable();

        $this->processedToken = null;
    }
}
