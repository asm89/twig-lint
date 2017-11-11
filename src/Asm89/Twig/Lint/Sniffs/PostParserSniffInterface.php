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

interface PostParserSniffInterface extends SniffInterface
{
    public function process(\Twig_Node $node, \Twig_Environment $env);
}
