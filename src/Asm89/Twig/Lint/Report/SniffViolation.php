<?php

namespace Asm89\Twig\Lint\Report;

use Asm89\Twig\Lint\Sniffs\SniffInterface;

class SniffViolation
{
    /**
     * Level of the message among `notice`, `warning`, `error`
     *
     * @var int
     */
    protected $level;

    /**
     * Text message associated with the violation.
     *
     * @var string
     */
    protected $message;

    /**
     * Line number for the violation.
     *
     * @var int
     */
    protected $line;

    /**
     * Position of the violation on the current line.
     *
     * @var int|null
     */
    protected $linePosition;

    /**
     * File in which the violation has been found.
     *
     * @var \SplFileInfo|string
     */
    protected $filename;

    /**
     * Severity is an indication for finer filtering of violation.
     *
     * @var int
     */
    protected $severity;

    /**
     * Sniff that has produce this violation.
     *
     * @var SniffInterface
     */
    protected $sniff;

    public function __construct($level, $message, $line, $filename)
    {
        $this->level        = $level;
        $this->message      = $message;
        $this->line         = $line;
        $this->filename     = $filename;

        $this->sniff        = null;
        $this->linePosition = null;
        $this->severity     = SniffInterface::SEVERITY_DEFAULT;
    }

    public function setSniff(SniffInterface $sniff)
    {
        $this->sniff = $sniff;

        return $this;
    }

    public function getSniff()
    {
        return $this->sniff;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function getLevelAsString()
    {
        switch ($this->level) {
            case SniffInterface::MESSAGE_TYPE_NOTICE:
                return 'NOTICE';
            case SniffInterface::MESSAGE_TYPE_WARNING:
                return 'WARNING';
            case SniffInterface::MESSAGE_TYPE_ERROR:
                return 'ERROR';
        }

        throw new \Exception(sprintf('Unknown level "%s"', $this->level));
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getLine()
    {
        return $this->line;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function setLinePosition($linePosition)
    {
        $this->linePosition = $linePosition;

        return $this;
    }

    public function getLinePosition()
    {
        return $this->linePosition;
    }

    public function setSeverity($severity)
    {
        $this->severity = $severity;

        return $this;
    }

    public function getSeverity()
    {
        return $this->severity;
    }
}
