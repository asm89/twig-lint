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

use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Human readable output with context.
 *
 * @author Marc Weistroff <marc.weistroff@sensiolabs.com>
 * @author Alexander <iam.asm89@gmail.com>
 * @author Hussard <adrien.ricartnoblet@gmail.com>
 */
class TextFormatter implements ReportFormatterInterface
{
    const ERROR_CURSOR_CHAR   = '>>';
    const ERROR_LINE_FORMAT   = '%-5s| %s';
    const ERROR_CONTEXT_LIMIT = 2;
    const ERROR_LINE_WIDTH    = 120;

    /**
     * Input-output helper object.
     *
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * Constructor.
     *
     * @param SymfonyStyle $output
     */
    public function __construct($input, $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * {@inheritdoc}
     */
    public function display(Report $report, array $options = array())
    {
        foreach ($report->getFiles() as $file) {
            $fileMessages = $report->getMessages(array(
                'file'      => $file,
                'level'     => isset($options['level']) ? $options['level'] : null,
                'severity'  => isset($options['severity']) ? $options['severity'] : null,
            ));

            $this->io->text((count($fileMessages) > 0 ? '<error>KO</>': '<info>OK</>') . ' ' . $file);

            $rows = array();
            foreach ($fileMessages as $message) {
                $lines = $this->getContext(file_get_contents($file), $message->getLine(), $this::ERROR_CONTEXT_LIMIT);

                $formattedText = [];
                foreach ($lines as $no => $code) {
                    $formattedText[] = sprintf($this::ERROR_LINE_FORMAT, $no, wordwrap($code, $this::ERROR_LINE_WIDTH));

                    if ($no === $message->getLine()) {
                        $formattedText[] = sprintf(
                            '<error>' . $this::ERROR_LINE_FORMAT . '</>',
                            $this::ERROR_CURSOR_CHAR,
                            wordwrap($message->getMessage(), $this::ERROR_LINE_WIDTH)
                        );
                    }
                }

                $rows[] = array(
                    new TableCell('<comment>' . $message->getLevelAsString() . '</>', array('rowspan' => 2)),
                    implode("\n", $formattedText),
                );
                $rows[] = new TableSeparator();
            }

            $this->io->table(array(), $rows);
        }

        $summaryString = sprintf(
            'Files linted: %d, notices: %d, warnings: %d, errors: %d',
            $report->getTotalFiles(),
            $report->getTotalNotices(),
            $report->getTotalWarnings(),
            $report->getTotalErrors()
        );

        if (0 === $report->getTotalWarnings() && 0 === $report->getTotalErrors()) {
            $this->io->success($summaryString);
        } elseif (0 < $report->getTotalWarnings() && 0 === $report->getTotalErrors()) {
            $this->io->warning($summaryString);
        } else {
            $this->io->error($summaryString);
        }
    }

    protected function getContext($template, $line, $context)
    {
        $lines = explode("\n", $template);

        $position = max(0, $line - $context);
        $max = min(count($lines), $line - 1 + $context);

        $result = array();
        $indentCount = null;
        while ($position < $max) {
            if (preg_match('/^([\s\t]+)/', $lines[$position], $match)) {
                if ($indentCount === null) {
                    $indentCount = strlen($match[1]);
                }

                if (strlen($match[1]) < $indentCount) {
                    $indentCount = strlen($match[1]);
                }
            } else {
                $indentCount = 0;
            }

            $result[$position + 1] = $lines[$position];
            $position++;
        }

        foreach ($result as $index => $code) {
            $result[$index] = substr($code, $indentCount);
        }

        return $result;
    }
}
