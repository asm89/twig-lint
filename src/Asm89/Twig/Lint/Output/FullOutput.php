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
 * Human readable output with context.
 *
 * @author Marc Weistroff <marc.weistroff@sensiolabs.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
class FullOutput implements OutputInterface
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
        $this->output->writeln('<info>OK</info>'.($file ? sprintf(' in %s', $file) : ''));
    }

    /**
     * {@inheritDoc}
     */
    public function error($template, Twig_Error $exception, $file = null)
    {
        $line =  $exception->getTemplateLine();
        $lines = $this->getContext($template, $line);

        if ($file) {
            $this->output->writeln(sprintf("<error>KO</error> in %s (line %s)", $file, $line));
        } else {
            $this->output->writeln(sprintf("<error>KO</error> (line %s)", $line));
        }

        foreach ($lines as $no => $code) {
            $this->output->writeln(
                sprintf(
                    "%s %-6s %s",
                    $no == $line ? '<error>>></error>' : '  ',
                    $no,
                    $code
                )
            );
            if ($no == $line) {
                $this->output->writeln(sprintf('<error>>> %s</error> ', $exception->getRawMessage()));
            }
        }
    }

    protected function getContext($template, $line, $context = 3)
    {
        $lines = explode("\n", $template);

        $position = max(0, $line - $context);
        $max = min(count($lines), $line - 1 + $context);

        $result = array();
        while ($position < $max) {
            $result[$position + 1] = $lines[$position];
            $position++;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function summary(array $stats)
    {
        $summary = 'Total files: %d, files linted: %d, files with errors: %d';
        $summary = sprintf($summary, $stats['total'], $stats['linted'], $stats['errors']);
        $this->output->writeln($summary);
    }
}
