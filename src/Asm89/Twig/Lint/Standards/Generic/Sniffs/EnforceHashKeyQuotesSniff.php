<?php

namespace Asm89\Twig\Lint\Standards\Generic\Sniffs;

use Asm89\Twig\Lint\Tokenizer\Token;
use Asm89\Twig\Lint\Sniffs\AbstractPreParserSniff;

class EnforceHashKeyQuotesSniff extends AbstractPreParserSniff
{
    protected $processedToken;

    /**
     * {@inheritdoc}
     */
    public function process(Token $token, $tokenPosition, $tokens)
    {
        if (!$this->isTokenMatching($token, Token::PUNCTUATION_TYPE, '{') || (null !== $this->getProcessedToken() && $this->getProcessedToken() >= $tokenPosition)) {

            return $token;
        }

        list($startPosition, $endPosition) = $this->findHashPositions($tokenPosition, $tokens);

        $j = $startPosition + 1;
        while ($j < $endPosition) {
            if (!$this->isTokenMatching($tokens[$j], Token::WHITESPACE_TYPE) && !$this->isTokenMatching($tokens[$j], Token::EOL_TYPE)) {
                if ($this->isTokenMatching($tokens[$j], Token::PUNCTUATION_TYPE, '(')) {
                    // Ignore keys that are complex expressions.
                    continue;
                }

                // Not a string with quotes ?
                if (!$this->isTokenMatching($tokens[$j], Token::STRING_TYPE)) {
                    $this->addMessage($this::MESSAGE_TYPE_WARNING, sprintf('Hash key \'%s\' requires quotes; use single quotes', $tokens[$j]->getValue()), $tokens[$j]);
                }

                // Skip until the end of the key: value pair eg. `,` or the start of a sub-hash eg. `{`.
                while (!$this->isTokenMatching($tokens[$j], Token::PUNCTUATION_TYPE, ',')
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
        $hashStarts = $hashEnds = [];

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

        return [$hashStarts[0], $hashEnds[count($hashEnds) - 1]];
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
