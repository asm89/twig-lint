<?php

namespace Asm89\Twig\Lint;

use Asm89\Twig\Lint\Preprocessor\Preprocessor;

class Linter
{
    protected $env;

    public function __construct(\Twig_Environment $env)
    {
        $this->env = $env;
    }

    public function run($template, $ruleset)
    {
        $preprocessor = new Preprocessor($this->env);
        $stream = $preprocessor->tokenize($template);

        $sniffs = $ruleset->getSniffs($ruleset::EVENT['PRE_PARSER']);
        foreach ($stream as $index => $token) {
            foreach ($sniffs as $sniff) {
                $sniff->process($token, $index, $stream);
            }
        }

        try {
            $this->env->parse($this->env->tokenize($template, null));
        } catch (\Twig_Error $e) {
            dump($e);

            return false;
        }

        foreach ($ruleset->getSniffs() as $sniff) {
            dump($sniff->getMessages());
        }

        return true;
    }
}
