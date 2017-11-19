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

use Asm89\Twig\Lint\Report\ReportWatch;
use Asm89\Twig\Lint\Report\SniffViolation;
use Asm89\Twig\Lint\Tokenizer\TokenizerInterface;
use Asm89\Twig\Lint\Sniffs\SniffInterface;
use Asm89\Twig\Lint\Sniffs\PostParserSniffInterface;

use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Linter is the main class and will process twig files against a set of rules.
 *
 * @author Hussard <adrien.ricartnoblet@gmail.com>
 */
class Linter
{
    protected $env;

    protected $sniffExtension;

    protected $tokenizer;

    public function __construct(\Twig_Environment $env, TokenizerInterface $tokenizer)
    {
        $this->env = $env;
        $this->sniffExtension = $this->env->getExtension('Asm89\Twig\Lint\Extension\SniffsExtension');
        $this->tokenizer = $tokenizer;
    }

    /**
     * Run the linter on the given $files against the given $ruleset.
     *
     * @param  array   $files    List of files to process.
     * @param  Ruleset $ruleset  Set of rules to check.
     *
     * @return Report            an object with all violations and stats.
     */
    public function run($files, Ruleset $ruleset)
    {
        if (!is_array($files) && !$files instanceof \Traversable) {
            $files = array($files);
        }

        if (empty($files)) {
            throw new \Exception('No files to process, provide at least one file to be linted');
        }

        // setUp
        $stopwatch = new Stopwatch();

        $report = new Report();
        set_error_handler(function ($type, $msg) use ($report) {
            if (E_USER_DEPRECATED === $type) {
                $sniffViolation = new SniffViolation(
                    SniffInterface::MESSAGE_TYPE_NOTICE,
                    $msg,
                    '',
                    ''
                );

                $report->addMessage($sniffViolation);
            }
        });

        foreach ($ruleset->getSniffs() as $sniff) {
            if ($sniff instanceof PostParserSniffInterface) {
                $this->sniffExtension->addSniff($sniff);
            }

            $sniff->enable($report);
        }

        $stopwatch->start('lint');

        // Process
        foreach ($files as $file) {
            $this->processTemplate($file, $ruleset, $report);
            $stopwatch->lap('lint');

            // Add this file to the report.
            $report->addFile($file);
        }

        // tearDown
        restore_error_handler();
        foreach ($ruleset->getSniffs() as $sniff) {
            if ($sniff instanceof PostParserSniffInterface) {
                $this->sniffExtension->removeSniff($sniff);
            }

            $sniff->disable();
        }

        $report->setSummary(
            new ReportWatch($stopwatch->getEvent('lint')->getDuration(), $stopwatch->getEvent('lint')->getMemory())
        );

        return $report;
    }

    /**
     * Checks one template against the set of rules.
     *
     * @param  string  $file     File to check as a string.
     * @param  Ruleset $ruleset  Set of rules to check.
     * @param  Report  $report   Current report to fill.
     *
     * @return boolean
     */
    public function processTemplate($file, $ruleset, $report)
    {
        $twigSource = new \Twig_Source(file_get_contents($file), $file, $file);

        // Tokenize + Parse.
        try {
            $this->env->parse($this->env->tokenize($twigSource));
        } catch (\Twig_Error $e) {
            $sourceContext = $e->getSourceContext();

            $sniffViolation = new SniffViolation(
                SniffInterface::MESSAGE_TYPE_ERROR,
                $e->getRawMessage(),
                $e->getTemplateLine(),
                $e->getSourceContext()->getName()
            );
            $sniffViolation->setSeverity(SniffInterface::SEVERITY_MAX);

            $report->addMessage($sniffViolation);

            return false;
        }

        // Tokenizer.
        try {
            $stream = $this->tokenizer->tokenize($twigSource);
        } catch (\Exception $e) {
            $sniffViolation = new SniffViolation(
                SniffInterface::MESSAGE_TYPE_ERROR,
                sprintf('Unable to tokenize file "%s"', (string) $file),
                '',
                (string) $file
            );
            $sniffViolation->setSeverity(SniffInterface::SEVERITY_MAX);

            $report->addMessage($sniffViolation);

            return false;
        }

        $sniffs = $ruleset->getSniffs(SniffInterface::TYPE_PRE_PARSER);
        foreach ($sniffs as $sniff) {
            foreach ($stream as $index => $token) {
                $sniff->process($token, $index, $stream);
            }
        }

        return true;
    }
}
