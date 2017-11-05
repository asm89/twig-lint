<?php

namespace Asm89\Twig\Lint\Sniffs;

interface PostParserSniffInterface extends SniffInterface
{
    public function process(\Twig_Node $node, \Twig_Environment $env);
}
