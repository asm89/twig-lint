<?php

namespace Asm89\Twig\Lint\Sniffs;

use Asm89\Twig\Lint\Report;

abstract class AbstractSniff implements SniffInterface
{
    protected $options;

    protected $messages;

    protected $report;

    public function __construct($options = array())
    {
        $this->messages = [];
        $this->report   = null;

        $this->options  = array_merge(array(
            'severity' => $this::SEVERITY_DEFAULT,
        ), $options);
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
