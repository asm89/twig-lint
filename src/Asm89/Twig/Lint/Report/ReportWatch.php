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

use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Utility for reporting stats while linting.
 *
 * @author Hussard <adrien.ricartnoblet@gmail.com>
 */
class ReportWatch
{
    /**
     * Duration of the lint.
     *
     * @var int
     */
    protected $duration;

    /**
     * Memory consumption.
     *
     * @var int
     */
    protected $memory;

    /**
     * File
     * @var string|\SplFileInfo
     */
    protected $file;

    /**
     * Constructor.
     *
     * @param int                 $duration
     * @param int                 $memory
     * @param string|\SplFileInfo $file
     */
    public function __construct($duration, $memory, $file = null)
    {
        $this->duration = $duration;
        $this->memory   = $memory;
        $this->file     = $file;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function getMemory()
    {
        return $this->memory;
    }
}
