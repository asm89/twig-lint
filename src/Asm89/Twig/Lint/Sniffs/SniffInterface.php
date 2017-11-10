<?php

namespace Asm89\Twig\Lint\Sniffs;

use Asm89\Twig\Lint\Report;

interface SniffInterface
{
    const MESSAGE_TYPE_ALL       = 0;
    const MESSAGE_TYPE_WARNING   = 1;
    const MESSAGE_TYPE_ERROR     = 2;

    const SEVERITY_MIN     = 0;
    const SEVERITY_DEFAULT = 5;
    const SEVERITY_MAX     = 10;

    const TYPE = [
        'PRE_PARSER'    => 'lint.pre_parser',
        'POST_PARSER'   => 'lint.post_parser',
    ];

    /**
     * Enable the sniff.
     *
     * Once the sniff is enabled, it will be registered and executed when a template is tokenized or parsed. Messages
     * will be added to the given `$report` object.
     *
     * @param  Report $report
     *
     * @return self
     */
    public function enable(Report $report);

    /**
     * Disable the sniff.
     *
     * It usually is disabled when the processing is over, it will reset the sniff internal values for next check.
     *
     * @return self
     */
    public function disable();

    /**
     * Get the current report.
     *
     * @return Report
     * @throws \Exception   A disabled sniff has no current report.
     */
    public function getReport();

    /**
     * Get the type of sniff.
     *
     * @return string       One of `TYPE` constants.
     */
    public function getType();
}
