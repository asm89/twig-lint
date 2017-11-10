<?php

namespace Asm89\Twig\Lint;

use Asm89\Twig\Lint\Config\Loader;
use Symfony\Component\Config\FileLocator;

class RulesetFactory
{
    public function __construct()
    {
    }

    public function createRuleset(array $sniffs = array())
    {
        $ruleset = new Ruleset();

        foreach ($sniffs as $sniff) {
            $ruleset->addSniff($sniff);
        }

        return $ruleset;
    }

    public function createRulesetFromFile($file, $paths)
    {
        $loader = new Loader(new FileLocator($paths));
        $value = $loader->load($file);

        $sniffs = [];
        foreach ($value['ruleset'] as $rule) {
            $sniffOptions = [];
            if (isset($rule['options'])) {
                $sniffOptions = $rule['options'];
            }

            $sniffs[] = new $rule['class']($sniffOptions);
        }

        return $this->createRuleset($sniffs);
    }


}
