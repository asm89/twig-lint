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

    protected $totalNotices;

    protected $totalWarnings;

    protected $totalErrors;

    public function __construct()
    {
        $this->messages       = [];
        $this->totalNotices   = 0;
        $this->totalWarnings  = 0;
        $this->totalErrors    = 0;
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

    public function getMessages()
    {
        return $this->messages;
    }

    public function getTotalFiles()
    {
        return count(array_count_values(array_map(function ($message) {
            return (string) $message->getFilename();
        }, $this->messages)));
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
