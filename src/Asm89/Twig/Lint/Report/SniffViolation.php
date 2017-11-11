<?php

/*
 * This file is part of twig-lint.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\Lint\Report;

use Asm89\Twig\Lint\Sniffs\SniffInterface;

/**
 * Wrapper class that represents a violation to a sniff with context.
 *
 * @author Hussard <adrien.ricartnoblet@gmail.com>
 */
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

    /**
     * Get the level of this violation.
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Get a human-readable of the level of this violation.
     *
     * @return string
     */
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

    /**
     * Get the text message of this violation.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get the line number where this violation occured.
     *
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * Get the filename (and path) where this violation occured.
     *
     * @return string
     */
    public function getFilename()
    {
        return (string) $this->filename;
    }

    /**
     * Set the position in the line where this violation occured.
     *
     * @param int $linePosition
     *
     * @return self
     */
    public function setLinePosition($linePosition)
    {
        $this->linePosition = $linePosition;

        return $this;
    }

    /**
     * Get the position in the line, if any.
     *
     * @return int
     */
    public function getLinePosition()
    {
        return $this->linePosition;
    }

    /**
     * Set the severity.
     *
     * @param int $severity
     */
    public function setSeverity($severity)
    {
        $this->severity = $severity;

        return $this;
    }

    /**
     * Get the severity level.
     *
     * @return int
     */
    public function getSeverity()
    {
        return (int) $this->severity;
    }

    /**
     * Set the sniff that was not met.
     *
     * @param SniffInterface $sniff
     */
    public function setSniff(SniffInterface $sniff)
    {
        $this->sniff = $sniff;

        return $this;
    }

    /**
     * Get the sniff that was not met.
     *
     * @return SniffInterface
     */
    public function getSniff()
    {
        return $this->sniff;
    }
}
