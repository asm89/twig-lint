<?php

namespace Asm89\Twig\Lint\Command;

use Asm89\Twig\Lint\Config;
use Asm89\Twig\Lint\Linter;
use Asm89\Twig\Lint\RulesetFactory;
use Asm89\Twig\Lint\StubbedEnvironment;
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

use Symfony\Component\Finder\Finder;

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
                    'full, csv',
                    'full'
                ),
                new InputOption(
                    'working-dir',
                    '',
                    InputOption::VALUE_OPTIONAL,
                    'Run as if this was started in <working-dir> instead of the current working directory'
                ),
            ))
            ->addArgument('filename', InputArgument::OPTIONAL)
        ;
    }

    protected function execute(InputInterface $input, CliOutputInterface $output)
    {
        $twig       = new StubbedEnvironment(new \Twig_Loader_String());
        $linter     = new Linter($twig, new Tokenizer($twig));
        $factory    = new RulesetFactory();
        $exitCode   = 0;

        $filename   = $input->getArgument('filename');
        $exclude    = $input->getOption('exclude');
        $format     = $input->getOption('format');
        $currentDir = $input->getOption('working-dir') ?: getcwd();

        // Compute the config.
        $config     = new Config();

        // Get the rules to apply.
        $ruleset = $factory->createRulesetFromFile($config->get('filename'), $currentDir);

        // Execute the linter.
        $report = $linter->run($config->findFiles($filename, $exclude), $ruleset);

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

        $rows = [];
        foreach ($report->getMessages() as $message) {
            $rows[] = [
                $message->getLevelAsString(),
                $message->getLine(),
                $message->getLinePosition() ?: '-',
                $message->getFilename(),
                $message->getSeverity(),
            ];
            $rows[] = [new TableCell($message->getMessage(), array('colspan' => 5))];
            $rows[] = new TableSeparator();
        }

        $io->table(
            array('Level', 'Line', 'Position', 'File', 'Severity'),
            $rows
        );

        $summaryString = sprintf('Files linted: %d, notices: %d, warnings: %d, errors: %d',
            $report->getTotalFiles(), $report->getTotalNotices(), $report->getTotalWarnings(), $report->getTotalErrors());

        if (0 === $report->getTotalWarnings() && 0 === $report->getTotalErrors()) {
            $io->success($summaryString);
        } elseif (0 < $report->getTotalWarnings() && 0 === $report->getTotalErrors()) {
            $io->warning($summaryString);
        } else {
            $io->error($summaryString);
        }
    }
}
