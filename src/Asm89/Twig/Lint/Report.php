<?php

namespace Asm89\Twig\Lint;

class Report
{
    const MESSAGE_TYPE_ALL       = 0;
    const MESSAGE_TYPE_WARNING   = 1;
    const MESSAGE_TYPE_ERROR     = 2;

    const SEVERITY_DEFAULT = 5;

    protected $messages;

    public function __construct()
    {
        $this->messages = [];
    }

    public function addMessage($messageType, $message, $line, $position = null, $filename = null, $severity = null)
    {
        if (!$severity) {
            $severity = $this::SEVERITY_DEFAULT;
        }

        $this->messages[] = [
            $messageType,
            $message,
            $line,
            $position,
            $filename,
            $severity,
        ];

        return $this;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function getTotalFiles()
    {
        return 0;
    }

    public function getTotalErrors()
    {
        return 0;
    }

    public function getTotalWarnings()
    {
        return 0;
    }

    public function getReport()
    {
        return [];
    }
}
