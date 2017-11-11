<?php

namespace Asm89\Twig\Lint;

use Asm89\Twig\Lint\Report\SniffViolation;
use Asm89\Twig\Lint\Tokenizer\TokenizerInterface;
use Asm89\Twig\Lint\Sniffs\SniffInterface;
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

    public function run($files, $ruleset)
    {
        if (!is_array($files) && !$files instanceof \Traversable) {
            $files = array($files);
        }

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

    public function processTemplate($file, $ruleset, $report)
    {
        $twigSource = new \Twig_Source(file_get_contents($file), basename($file), $file);

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

        $stream = $this->tokenizer->tokenize($twigSource);

        $sniffs = $ruleset->getSniffs(SniffInterface::TYPE['PRE_PARSER']);
        foreach ($stream as $index => $token) {
            foreach ($sniffs as $sniff) {
                $sniff->process($token, $index, $stream);
            }
        }

        return true;
    }
}
