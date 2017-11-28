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
use Asm89\Twig\Lint\Report\TextFormatter;
use Asm89\Twig\Lint\Tokenizer\Tokenizer;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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
                    'level',
                    '',
                    InputOption::VALUE_OPTIONAL,
                    'Allowed values are: warning, error',
                    'warning'
                ),
                new InputOption(
                    'severity',
                    '',
                    InputOption::VALUE_OPTIONAL,
                    'Allowed values are: 0 - 10',
                    ''
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename   = $input->getArgument('filename');
        $exclude    = $input->getOption('exclude');
        $format     = $input->getOption('format');
        $level      = $input->getOption('level');
        $severity   = $input->getOption('severity');
        $currentDir = $input->getOption('working-dir');

        // Load config files.
        $globalLoader = new Loader(new FileLocator(getenv('HOME') . '/.twigcs'));
        $loader       = new Loader(new FileLocator($currentDir));

        $globalConfig = array();
        try {
            $globalConfig = $globalLoader->load('twigcs_global.yml');
        } catch (\Exception $e) {
            // The global config file may not exist but it's ok.
        }

        // Compute the final config object.
        $config = new Config(
            $globalConfig,
            $loader->load('twigcs.yml'),
            array('workingDirectory' => $currentDir)
        );

        $twig          = new StubbedEnvironment(new \Twig_Loader_Array(), array('stub_tags' => $config->get('stub')));
        $linter        = new Linter($twig, new Tokenizer($twig));
        $factory       = new RulesetFactory();
        $reporter      = $this->getReportFormatter($input, $output, $format);
        $exitCode      = 0;

        // Get the rules to apply.
        $ruleset = $factory->createRulesetFromConfig($config);

        // Execute the linter.
        $report = $linter->run($config->findFiles(), $ruleset);

        // Format the output.
        $reporter->display($report, array(
            'level'     => $level,
            'severity'  => $severity,
        ));

        // Return a meaningful error code.
        if ($report->getTotalErrors()) {
            $exitCode = 1;
        }

        return $exitCode;
    }

    protected function getReportFormatter($input, $output, $format)
    {
        switch ($format) {
            case 'full':
                return new TextFormatter($input, $output, array('explain' => true));
            case 'text':
                return new TextFormatter($input, $output);
            default:
                throw new \Exception(sprintf('Unknown format "%s"', $format));
        }
    }
}
