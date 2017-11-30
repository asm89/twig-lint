<?php

/*
 * This file is part of twig-lint.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\Lint\Sniffs;

/**
 * Base for all post-parser sniff.
 *
 * A post parser sniff should be useful to check actual values of twig functions, filters
 * and tags such as: ensure that a given function has at least 3 arguments or if the template
 * contains an {% include %} tag.
 *
 * Use `PreParserSniffInterface` sniff if you want to check syntax and code formatting.
 *
 * @author Hussard <adrien.ricartnoblet@gmail.com>
 */
interface PostParserSniffInterface extends SniffInterface
{
    public function process(\Twig_Node $node, \Twig_Environment $env);
}
