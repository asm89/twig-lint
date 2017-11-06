<?php

namespace Asm89\Twig\Lint;

use Asm89\Twig\Lint\Tokenizer\TokenizerInterface;
use Asm89\Twig\Lint\Sniffs\PostParserSniffInterface;

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

    public function run($templates, $ruleset)
    {
        $report = new Report();
        foreach ($ruleset->getSniffs() as $sniff) {
            if ($sniff instanceof PostParserSniffInterface) {
                $this->sniffExtension->addSniff($sniff);
            }

            $sniff->enable($report);
        }

        foreach ($templates as $template) {
            $this->processTemplate($template, $ruleset, $report);
        }

        foreach ($ruleset->getSniffs() as $sniff) {
            if ($sniff instanceof PostParserSniffInterface) {
                $this->sniffExtension->removeSniff($sniff);
            }

            $sniff->disable();
        }

        return $report;
    }

    public function processTemplate($template, $ruleset, $report = null)
    {
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
}
