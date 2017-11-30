<?php

/*
 * This file is part of twig-lint.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\Lint;

use Asm89\Twig\Lint\Config\Loader;
use Symfony\Component\Config\FileLocator;

/**
 * Factory to help create set of rules.
 *
 * @author Hussard <adrien.ricartnoblet@gmail.com>
 */
class RulesetFactory
{
    /**
     * Create a new set of rule with the given $sniffs.
     *
     * @param  array  $sniffs
     *
     * @return Ruleset
     */
    public function createRuleset(array $sniffs = array())
    {
        $ruleset = new Ruleset();

        foreach ($sniffs as $sniff) {
            $ruleset->addSniff($sniff);
        }

        return $ruleset;
    }

    /**
     * Create a new set of rule with the given $config.
     *
     * @param  Config  $config
     *
     * @return Ruleset
     */
    public function createRulesetFromConfig(Config $config)
    {
        $rules = $config->get('ruleset');

        $sniffs = array();
        foreach ($rules as $rule) {
            $sniffOptions = array();
            if (isset($rule['options'])) {
                $sniffOptions = $rule['options'];
            }

            $sniffs[] = new $rule['class']($sniffOptions);
        }

        return $this->createRuleset($sniffs);
    }
}
