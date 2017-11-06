<?php

namespace Asm89\Twig\Lint\Sniffs;

abstract class AbstractPreParserSniff implements PreParserSniffInterface
{
    protected $report;

    public function __construct()
    {
        $this->messages = [];
        $this->report = null;
    }

    public function isTokenMatching($token, $type, $value = null)
    {
        return $token->getType() === $type && (null === $value || (null !== $value && $token->getValue() === $value));
    }

    public function enable($report)
    {
        $this->report = $report;

        return $this;
    }

    public function disable()
    {
        $this->report = null;

        return $this;
    }


    public function getReport()
    {
        if (null === $this->report) {
            throw new \Exception('Sniff is disabled!');
        }

        return $this->report;
    }
}
