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

use Asm89\Twig\Lint\Config;
use Asm89\Twig\Lint\Linter;
use Asm89\Twig\Lint\RulesetFactory;
use Asm89\Twig\Lint\StubbedEnvironment;
use Asm89\Twig\Lint\Config\Loader;
use Asm89\Twig\Lint\Output\OutputInterface;
use Asm89\Twig\Lint\Tokenizer\Tokenizer;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface as CliOutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Config\FileLocator;

use Symfony\Component\Finder\Finder;

/**
 * TwigCS stands for "Twig Code Sniffer" and will check twig template againt all
 * rules which have been defined in the twigcs.yml of your project.
 *
 * This is heavily inspired by the symfony lint command and PHP_CodeSniffer tool
 * (https://github.com/squizlabs/PHP_CodeSniffer).
 *
 * @author Hussard <adrien.ricartnoblet@gmail.com>
 */
class TwigCSCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('twigcs')
            ->setDescription('Lints a template and outputs encountered errors')
            ->setDefinition(array(
                new InputOption(
                    'exclude',
                    '',
                    InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                    'Excludes, based on regex, paths of files and folders from parsing',
                    array()
                ),
                new InputOption(
                    'format',
                    '',
                    InputOption::VALUE_OPTIONAL,
                    'Implemented formats are: full',
                    'full'
                ),
                new InputOption(
                    'working-dir',
                    '',
                    InputOption::VALUE_OPTIONAL,
                    'Run as if this was started in <working-dir> instead of the current working directory',
                    getcwd()
                ),
            ))
            ->addArgument('filename', InputArgument::OPTIONAL)
            ->setHelp(<<<EOF
The <info>%command.name%</info> will check twig templates against a set of rules defined in
a "twigcs.yml".

<info>php %command.full_name% filename</info>

The command gets the contents of <comment>filename</comment> and outputs violations of the rules to stdout.

<info>php %command.full_name% dirname</info>

The command finds all twig templates in <comment>dirname</comment> and validates the syntax
of each Twig templates.

EOF
            )
        ;
    }

    /**
     * @{inheritDoc}
     */
    protected function execute(InputInterface $input, CliOutputInterface $output)
    {
        $filename   = $input->getArgument('filename');
        $exclude    = $input->getOption('exclude');
        $format     = $input->getOption('format');
        $currentDir = $input->getOption('working-dir');

        $loader        = new Loader(new FileLocator($currentDir));
        $config        = new Config(array('workingDirectory' => $currentDir), $loader->load('twigcs.yml'));
        $twig          = new StubbedEnvironment(new \Twig_Loader_Array(), array('stub_tags' => $config->get('stub')));
        $linter        = new Linter($twig, new Tokenizer($twig));
        $factory       = new RulesetFactory();
        $exitCode      = 0;

        // Get the rules to apply.
        $ruleset = $factory->createRulesetFromConfig($config);

        // Execute the linter.
        $report = $linter->run($config->findFiles(), $ruleset);

        // Format the output.
        $this->display($input, $output, $format, $report);

        // Return a meaningful error code.
        if ($report->getTotalErrors()) {
            $exitCode = 1;
        }

        return $exitCode;
    }

    public function display($input, $output, $format, $report)
    {
        $io = new SymfonyStyle($input, $output);

        $rows = array();
        foreach ($report->getMessages() as $message) {
            $rows[] = array(
                $message->getLevelAsString(),
                $message->getLine(),
                $message->getLinePosition() ?: '-',
                $message->getFilename(),
                $message->getSeverity(),
            );
            $rows[] = array(new TableCell('<comment>' . $message->getMessage() . '</>', array('colspan' => 5)));
            $rows[] = new TableSeparator();
        }

        $io->table(
            array('Level', 'Line', 'Position', 'File', 'Severity'),
            $rows
        );

        $summaryString = sprintf(
            'Files linted: %d, notices: %d, warnings: %d, errors: %d',
            $report->getTotalFiles(),
            $report->getTotalNotices(),
            $report->getTotalWarnings(),
            $report->getTotalErrors()
        );

        if (0 === $report->getTotalWarnings() && 0 === $report->getTotalErrors()) {
            $io->success($summaryString);
        } elseif (0 < $report->getTotalWarnings() && 0 === $report->getTotalErrors()) {
            $io->warning($summaryString);
        } else {
            $io->error($summaryString);
        }
    }
}
