<?php

namespace Asm89\Twig\Lint\Sniffs;

use Asm89\Twig\Lint\Tokenizer\Token;

abstract class AbstractPreParserSniff extends AbstractSniff implements PreParserSniffInterface
{
    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return $this::TYPE['PRE_PARSER'];
    }

    public function isTokenMatching($token, $type, $value = null)
    {
        return $token->getType() === $type && (null === $value || (null !== $value && $token->getValue() === $value));
    }

    public function addMessage($messageType, $message, Token $token, $severity = null)
    {
        if (null === $severity) {
            $severity = $this->options['severity'];
        }

        $this->getReport()->addMessage(
            $messageType,
            $message,
            $token->getLine(),
            $token->getPosition(),
            $token->getFilename(),
            $severity
        );

        return $this;
    }
}
