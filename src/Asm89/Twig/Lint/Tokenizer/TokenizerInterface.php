<?php

namespace Asm89\Twig\Lint\Tokenizer;

interface TokenizerInterface
{
    public function tokenize($code, $filename = null);
}
