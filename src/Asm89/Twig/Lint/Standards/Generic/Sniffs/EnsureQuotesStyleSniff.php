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
 * Check a style of quotes for strings (single or double quotes).
 *
 * This will not ensure that quotes are present and will only check that quotes
 * being used match the right style.
 */
class EnsureQuotesStyleSniff extends AbstractPreParserSniff
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        // Default: 'TYPE_SINGLE_QUOTES'
        $this->disallowedQuoteChar = '"';
        $this->violationMsg = 'String %s does not require double quotes; use single quotes instead';

        if (isset($this->options['style']) && 'TYPE_DOUBLE_QUOTES' === $this->options['style']) {
            $this->disallowedQuoteChar = '\'';
            $this->violationMsg = 'String %s uses single quotes; use double quotes instead';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function process(Token $token, $tokenPosition, $tokens)
    {
        if ($this->isTokenMatching($token, Token::STRING_TYPE)) {
            $value = $token->getValue();
            if ($this->disallowedQuoteChar === $value[0] || $this->disallowedQuoteChar === $value[strlen($value) - 1]) {
                $this->addMessage($this::MESSAGE_TYPE_WARNING, sprintf($this->violationMsg, $this->stringifyValue($token)), $token);
            }
        }
    }
}
