<?php

namespace Asm89\Twig\Lint;

use Asm89\Twig\Lint\Tokenizer\TokenizerInterface;
use Asm89\Twig\Lint\Sniffs\PostParserSniffInterface;
use Symfony\Component\Finder\Finder;

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

    public function run($fileOrDirectory, $ruleset)
    {
        $files = $this->findFiles($fileOrDirectory);

        $report = new Report();
        foreach ($ruleset->getSniffs() as $sniff) {
            if ($sniff instanceof PostParserSniffInterface) {
                $this->sniffExtension->addSniff($sniff);
            }

            $sniff->enable($report);
        }

        foreach ($files as $file) {
            $this->processTemplate($file, $ruleset, $report);
        }

        foreach ($ruleset->getSniffs() as $sniff) {
            if ($sniff instanceof PostParserSniffInterface) {
                $this->sniffExtension->removeSniff($sniff);
            }

            $sniff->disable();
        }

        return $report;
    }

    public function processTemplate($file, $ruleset, $report = null)
    {
        $template = [file_get_contents($file), $file];

        try {
            $this->env->parse($this->env->tokenize($template[0], $template[1]));
        } catch (\Twig_Error $e) {
            dump($e);

            return false;
        }

        $stream = $this->tokenizer->tokenize($template[0], $template[1]);

        $sniffs = $ruleset->getSniffs($ruleset::EVENT['PRE_PARSER']);
        foreach ($stream as $index => $token) {
            foreach ($sniffs as $sniff) {
                $sniff->process($token, $index, $stream);
            }
        }

        return true;
    }

    public function findFiles($filename, $exclude = null)
    {
        $files = [];
        if (is_file($filename)) {
            $files = [$filename];
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

        return $files;
    }
}
