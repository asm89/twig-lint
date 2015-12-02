<?php

/*
 * This file is part of twig-lint.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\Lint\Command;

use Asm89\Twig\Lint\Output\CsvOutput;
use Asm89\Twig\Lint\Output\FullOutput;
use Asm89\Twig\Lint\Output\OutputInterface;
use Asm89\Twig\Lint\StubbedEnvironment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface as CliOutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Command that will validate your template syntax and output encountered errors.
 *
 * Original source from https://github.com/symfony/symfony/blob/6b66bc3226fae6e0416039de75f47f91db56bfd9/src/Symfony/Bundle/TwigBundle/Command/LintCommand.php.
 *
 * @author Marc Weistroff <marc.weistroff@sensiolabs.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
class LintCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('lint')
            ->setDescription('Lints a template and outputs encountered errors')
            ->setDefinition(array(
                new InputOption(
                    'format',
                    '',
                    InputOption::VALUE_OPTIONAL,
                    'full, csv',
                    'full'
                ),
                new InputOption(
                    'exclude',
                    '',
                    InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                    'Excludes, based on regex, paths of files and folders from parsing'
                ),
                new InputOption('only-print-errors', '', InputOption::VALUE_NONE),
                new InputOption('summary', '', InputOption::VALUE_NONE)
            ))
            ->addArgument('filename')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command lints a template and outputs to stdout
the first encountered syntax error.

<info>php %command.full_name% filename</info>

The command gets the contents of <comment>filename</comment> and validates its syntax.

<info>php %command.full_name% dirname</info>

The command finds all twig templates in <comment>dirname</comment> and validates the syntax
of each Twig template.

<info>cat filename | php %command.full_name%</info>

The command gets the template contents from stdin and validates its syntax.

<info>php %command.full_name% filename --exclude path/to/dir --exclude path/.*/file\.twig --exclude some_folder/file</info>

The command excludes the files that match the exclusion (regex) and lists them as skipped.
EOF
            )
        ;
    }

    protected function execute(InputInterface $input, CliOutputInterface $output)
    {
        $twig     = new StubbedEnvironment(new \Twig_Loader_String());
        $template = null;
        $filename = $input->getArgument('filename');
        $exclude  = $input->getOption('exclude');
        $summary  = $input->getOption('summary');
        $output   = $this->getOutput($output, $input->getOption('format'));

        if (!$filename) {
            if (0 !== ftell(STDIN)) {
                throw new \RuntimeException("Please provide a filename or pipe template content to stdin.");
            }

            while (!feof(STDIN)) {
                $template .= fread(STDIN, 1024);
            }

            return $this->validateTemplate($twig, $output, $template);
        }

        if (!is_readable($filename)) {
            throw new \RuntimeException(sprintf('File or directory "%s" is not readable', $filename));
        }

        $files = array();
        if (is_file($filename)) {
            $files = array($filename);
        } elseif (is_dir($filename)) {
            $files = Finder::create()->files()->in($filename)->name('*.twig')->filter(
                // pass in the list of excludes
                function (\SplFileInfo $file) use ($exclude) {
                    foreach ($exclude as $excludeItem) {
                        if (1 === preg_match('#' . $excludeItem . '#', $file->getRealPath())) {
                            return false;
                        }
                    }
                    return true;
                }
            );
        }

        $onlyPrintErrors = $input->getOption('only-print-errors');
        $errors = 0;
        $linted = 0;
        foreach ($files as $file) {
            $linted++;
            $errors += $this->validateTemplate($twig, $output, file_get_contents($file), $file, $onlyPrintErrors);
        }

        $stats = array(
            'total' => count($files),
            'linted' => $linted,
            'errors' => $errors,
        );

        if ($summary) {
            $output->summary($stats);
        }

        return $errors > 0 ? 1 : 0;
    }

    protected function validateTemplate(
        \Twig_Environment $twig,
        OutputInterface $output,
        $template,
        $file = null,
        $onlyPrintErrors = false
    )
    {
        try {
            $twig->parse($twig->tokenize($template, $file ? (string) $file : null));
            if (false === $onlyPrintErrors) {
                $output->ok($template, $file);
            }
        } catch (\Twig_Error $e) {
            $output->error($template, $e, $file);

            return 1;
        }

        return 0;
    }

    protected function getOutput(CliOutputInterface $output, $format)
    {
        switch ($format) {
            case 'csv':
                return new CsvOutput($output);
                break;
            case 'full':
                return new FullOutput($output);
                break;
            default:
                throw new \RuntimeException(sprintf("Unknown output format '%s'.", $format));
        }
    }
}
