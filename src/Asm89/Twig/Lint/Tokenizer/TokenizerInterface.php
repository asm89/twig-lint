<?php

namespace Asm89\Twig\Lint\Tokenizer;

interface TokenizerInterface
{
    public function tokenize(\Twig_Source $code);
}
