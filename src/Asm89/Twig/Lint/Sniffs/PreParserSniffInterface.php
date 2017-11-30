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

use Asm89\Twig\Lint\Tokenizer\Token;

interface PreParserSniffInterface extends SniffInterface
{
    public function process(Token $token, $tokenPosition, $stream);
}
