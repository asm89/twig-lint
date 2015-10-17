<?php

/*
 * This file is part of twig-lint.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\Lint\Output;

use Symfony\Component\Console\Output\OutputInterface as CliOutputInterface;
use Twig_Error;

/**
 * Simple csv output for script consumption.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class CsvOutput implements OutputInterface
{
    private $output;

    /**
     * @param CliOutputInterface $output
     */
    public function __construct(CliOutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * {@inheritDoc}
     */
    public function ok($template, $file = null)
    {
        $this->output->writeln(($file ? '"' . (string) $file . '",' : '') . ',');
    }

    /**
     * {@inheritDoc}
     */
    public function error($template, Twig_Error $exception, $file = null)
    {
        $filename = ($file ? '"' . (string) $file . '",' : '');
        $this->output->writeln($filename . $exception->getTemplateLine() . ',' . $exception->getRawMessage());
    }

    /**
     * {@inheritDoc}
     */
    public function summary(array $stats)
    {
        // no-op for CSV
    }
}
