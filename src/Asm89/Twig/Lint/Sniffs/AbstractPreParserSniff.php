<?php

namespace Asm89\Twig\Lint\Sniffs;

abstract class AbstractPreParserSniff implements PreParserSniffInterface
{
    public function __construct()
    {
        $this->messages = [];
    }

    public function isTokenMatching($token, $type, $value = null)
    {
        return $token->getType() === $type && (null === $value || (null !== $value && $token->getValue() === $value));
    }

    public function addMessage($messageType, $message, $line, $severity = null)
    {
        if (!$severity) {
            $severity = $this::SEVERITY_DEFAULT;
        }

        $this->messages[] = [
            $messageType,
            $message,
            $line,
            $severity,
        ];

        return $this;
    }

    public function getMessages($messageType = null)
    {
        return $this->messages;
    }
}
