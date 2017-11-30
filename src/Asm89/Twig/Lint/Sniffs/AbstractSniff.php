<?php

/*
 * This file is part of twig-lint.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\Lint\Sniffs;

use Asm89\Twig\Lint\Report;

/**
 * Base for all sniff.
 *
 * @author Hussard <adrien.ricartnoblet@gmail.com>
 */
abstract class AbstractSniff implements SniffInterface
{
    /**
     * Default options for all sniffs.
     *
     * @var array
     */
    protected static $defaultOptions = array(
        'severity' => self::SEVERITY_DEFAULT,
    );

    /**
     * Computed options of this sniffs.
     *
     * @var array
     */
    protected $options;

    /**
     * When process is called, it will fill this report with the potential violations.
     *
     * @var Report
     */
    protected $report;

    /**
     * Constructor.
     *
     * @param array $options    Each sniff can defined its options.
     */
    public function __construct($options = array())
    {
        $this->messages = array();
        $this->report   = null;
        $this->options  = array_merge(self::$defaultOptions, $options);

        $this->configure();
    }

    /**
     * Configure this sniff based on its options.
     *
     * @return void
     */
    public function configure()
    {
        // Nothing.
    }

    /**
     * {@inheritDoc}
     */
    public function enable(Report $report)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function disable()
    {
        $this->report = null;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getReport()
    {
        if (null === $this->report) {
            throw new \Exception('Sniff is disabled!');
        }

        return $this->report;
    }

    /**
     * {@inheritDoc}
     */
    abstract public function getType();
}
