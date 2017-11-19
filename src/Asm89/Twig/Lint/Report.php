<?php

/*
 * This file is part of twig-lint.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\Lint;

use Asm89\Twig\Lint\Report\SniffViolation;

/**
 * Report contains all violations with stats.
 *
 * @author Hussard <adrien.ricartnoblet@gmail.com>
 */
class Report
{
    const MESSAGE_TYPE_NOTICE    = 0;
    const MESSAGE_TYPE_WARNING   = 1;
    const MESSAGE_TYPE_ERROR     = 2;

    protected $messages;

    protected $files;

    protected $totalNotices;

    protected $totalWarnings;

    protected $totalErrors;

    public function __construct()
    {
        $this->messages       = array();
        $this->files          = array();
        $this->totalNotices   = 0;
        $this->totalWarnings  = 0;
        $this->totalErrors    = 0;
    }

    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    public function getSummary()
    {
        return $this->summary;
    }

    public function addMessage(SniffViolation $SniffViolation)
    {
        // Update stats
        switch ($SniffViolation->getLevel()) {
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

        $this->messages[] = $SniffViolation;

        return $this;
    }

    public function getMessages($filters = array())
    {
        if (!$filters) {
            // Return all messages, without filtering.
            return $this->messages;
        }

        return array_filter($this->messages, function ($message) use ($filters) {
            $fileFilter = $levelFilter = $severityFilter = true;

            if (isset($filters['file']) && $filters['file']) {
                $fileFilter = (string) $message->getFilename() === (string) $filters['file'];
            }

            if (isset($filters['level']) && $filters['level']) {
                $levelFilter = $message->getLevel() >= $message::getLevelAsInt($filters['level']);
            }

            if (isset($filters['severity']) && $filters['severity']) {
                $severityFilter = $message->getSeverity() >= $filters['severity'];
            }

            return $fileFilter && $levelFilter && $severityFilter;
        });
    }

    public function addFile(\SplFileInfo $file)
    {
        $this->files[] = $file;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function getTotalFiles()
    {
        return count($this->files);
    }

    public function getTotalMessages()
    {
        return count($this->messages);
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
