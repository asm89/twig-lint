<?php

namespace Asm89\Twig\Lint\Command;

use Asm89\Twig\Lint\Config;
use Asm89\Twig\Lint\Linter;
use Asm89\Twig\Lint\RulesetFactory;
use Asm89\Twig\Lint\StubbedEnvironment;
use Asm89\Twig\Lint\Output\OutputInterface;
use Asm89\Twig\Lint\Tokenizer\Tokenizer;
use Symfony\Component\Console\Command\Command;
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
                    'Excludes, based on regex, paths of files and folders from parsing'
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
        $currentDir = $input->getOption('working-dir') ?: getcwd();
        $exitCode   = 0;

        $filename = $input->getArgument('filename');
        if (!$filename) {
            if (0 !== ftell(STDIN)) {
                throw new \RuntimeException('Please provide a filename or pipe template content to stdin.');
            }

            while (!feof(STDIN)) {
                $template .= fread(STDIN, 1024);
            }

            // return $this->validateTemplate($twig, $output, $template);
        }

        $config = new Config();

        $ruleset = $factory->createRulesetFromFile($config->get('filename'), $currentDir);
        $report = $linter->run($config->findFiles($filename), $ruleset);

        $this->display($input, $output, '', $report);

        if ($report->getTotalErrors()) {
            $exitCode = 1;
        }

        return $exitCode;
    }

    public function display($input, $output, $format, $report)
    {
        $io = new SymfonyStyle($input, $output);

        $io->table(
            array('Type', 'Message', 'Line', 'Position', 'File', 'Severity'),
            array_map(function ($message) {
                return [
                    $message[0],
                    strlen($message[1]) > 90 ? substr($message[1], 0, 90) . '...' : $message[1],
                    $message[2],
                    $message[3],
                    basename(dirname($message[4])) . '/' . basename($message[4]),
                    $message[5],
                ];
            }, $report->getMessages())
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
