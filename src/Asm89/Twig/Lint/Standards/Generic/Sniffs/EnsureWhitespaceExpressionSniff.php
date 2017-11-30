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
 * Ensure that the number of space is correct before and after an expression, eg.
 * any after `{{` or `{%` and before `}}` or `%}`.
 */
class EnsureWhitespaceExpressionSniff extends AbstractPreParserSniff
{
    /**
     * Number of whitespace expected before/after an expression.
     *
     * @var int
     */
    protected $expected;

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->expected = 1;
        if (isset($this->options['count'])) {
            $this->expected = $this->options['count'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function process(Token $token, $tokenPosition, $tokens)
    {
        if ($this->isTokenMatching($token, Token::VAR_START_TYPE) || $this->isTokenMatching($token, Token::BLOCK_START_TYPE)) {
            $this->processStart($token, $tokenPosition, $tokens);
        }

        if ($this->isTokenMatching($token, Token::VAR_END_TYPE) || $this->isTokenMatching($token, Token::BLOCK_END_TYPE)) {
            $this->processEnd($token, $tokenPosition, $tokens);
        }

        return $token;
    }

    public function processStart(Token $token, $tokenPosition, $tokens)
    {
        $offset = 1;
        while (
            $this->isTokenMatching($tokens[$tokenPosition + $offset], Token::WHITESPACE_TYPE)
            || $this->isTokenMatching($tokens[$tokenPosition + $offset], Token::EOL_TYPE)
        ) {
            ++$offset;
        }

        $count = $offset - 1;
        if ($this->expected !== $count) {
            $this->addMessage(
                $this::MESSAGE_TYPE_WARNING,
                sprintf('Expecting %d whitespace AFTER start of expression eg. "{{" or "{%%"; found %d', $this->expected, $count),
                $token
            );
        }
    }

    public function processEnd(Token $token, $tokenPosition, $tokens)
    {
        $offset = 1;
        while (
            $this->isTokenMatching($tokens[$tokenPosition - $offset], Token::WHITESPACE_TYPE)
            || $this->isTokenMatching($tokens[$tokenPosition - $offset], Token::EOL_TYPE)
        ) {
            ++$offset;
        }

        $count = $offset - 1;
        if ($this->expected !== $count) {
            $this->addMessage(
                $this::MESSAGE_TYPE_WARNING,
                sprintf('Expecting %d whitespace BEFORE end of expression eg. "}}" or "%%}"; found %d', $this->expected, $count),
                $token
            );
        }
    }
}
