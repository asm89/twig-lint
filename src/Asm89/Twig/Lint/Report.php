<?php

namespace Asm89\Twig\Lint;

class Report
{
    const MESSAGE_TYPE_NOTICE    = 0;
    const MESSAGE_TYPE_WARNING   = 1;
    const MESSAGE_TYPE_ERROR     = 2;

    const SEVERITY_DEFAULT = 5;

    protected $messages;

    protected $totalNotices;

    protected $totalWarnings;

    protected $totalErrors;

    public function __construct()
    {
        $this->messages = [];
        $this->totalNotices = 0;
        $this->totalWarnings = 0;
        $this->totalErrors = 0;
    }

    public function addMessage($messageType, $message, $line, $position = null, $filename = null, $severity = null)
    {
        if (!$severity) {
            $severity = $this::SEVERITY_DEFAULT;
        }

        // Update stats
        switch ($messageType) {
            case self::MESSAGE_TYPE_NOTICE:
                ++$this->totalNotices;

                break;
            case self::MESSAGE_TYPE_WARNING:
                ++$this->totalWarnings;

                break;
            case self::MESSAGE_TYPE_ERROR:
                ++$this->totalErrors;

                break;
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
        return count(array_count_values(array_map(function ($message) {
            return $message[4];
        }, $this->messages)));
    }

    public function getTotalMessages()
    {
        return count($this->messages());
    }

    public function getTotalNotices()
    {
        return $this->totalNotices;
    }

    public function getTotalWarnings()
    {
        return $this->totalWarnings;
    }

    public function getTotalErrors()
    {
        return $this->totalErrors;
    }
}
