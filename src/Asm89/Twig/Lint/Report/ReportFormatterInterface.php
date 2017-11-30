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

use Asm89\Twig\Lint\Report;

interface ReportFormatterInterface
{
    /**
     * Format and display the given $report.
     *
     * @param  Report $report
     * @param  array  $options
     *
     * @return void
     */
    public function display(Report $report, array $options = array());
}
