<?php

/*
 * This file is part of twig-lint.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\Lint\Sniffs;

use Asm89\Twig\Lint\Report\SniffViolation;
use Asm89\Twig\Lint\Tokenizer\Token;

/**
 * Base for all pre-parser sniff.
 *
 * A post parser sniff should be useful to check code formatting mainly such as:
 * whitespaces, too many blank lines or trailing commas;
 *
 * Use `AbstractPostParserSniff` for higher-order checks.
 *
 * @author Hussard <adrien.ricartnoblet@gmail.com>
 */
abstract class AbstractPreParserSniff extends AbstractSniff implements PreParserSniffInterface
{
    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return $this::TYPE_PRE_PARSER;
    }

    /**
     * Helper method to match a token of a given $type and $value.
     *
     * @param  Token  $token
     * @param  int    $type
     * @param  string $value
     *
     * @return boolean
     */
    public function isTokenMatching(Token $token, $type, $value = null)
    {
        return $token->getType() === $type && (null === $value || (null !== $value && $token->getValue() === $value));
    }

    /**
     * Adds a violation to the current report for the given token.
     *
     * @param int    $messageType
     * @param string $message
     * @param Token  $token
     * @param int    $severity
     *
     * @return self
     */
    public function addMessage($messageType, $message, Token $token, $severity = null)
    {
        if (null === $severity) {
            $severity = $this->options['severity'];
        }

        $sniffViolation = new SniffViolation($messageType, $message, $token->getLine(), $token->getFilename());
        $sniffViolation->setSeverity($severity);
        $sniffViolation->setLinePosition($token->getPosition());

        $this->getReport()->addMessage($sniffViolation);

        return $this;
    }

    public function stringifyValue($token)
    {
        if ($token->getType() === Token::STRING_TYPE) {
            return $token->getValue();
        } else {
            return '\'' . $token->getValue() . '\'';
        }
    }
}
