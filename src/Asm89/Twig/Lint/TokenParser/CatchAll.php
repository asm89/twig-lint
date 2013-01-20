<?php

/*
 * This file is part of twig-lint.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\Lint\TokenParser;

use \Twig_Token;
use \Twig_TokenParser;
use \Twig_TokenStream;

/**
 * Token parser for any block.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class CatchAll extends Twig_TokenParser
{
    private $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return boolean
     */
    public function decideEnd(Twig_Token $token)
    {
        return $token->test('end' . $this->name);
    }

    /**
     * {@inheritDoc}
     */
    public function parse(Twig_Token $token)
    {
        $stream = $this->parser->getStream();

        while ($stream->getCurrent()->getType() !== Twig_Token::BLOCK_END_TYPE) {
            $stream->next();
        }

        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        if ($this->hasBody($stream)) {
            $this->parser->subparse(array($this, 'decideEnd'), true);
            $stream->expect(Twig_Token::BLOCK_END_TYPE);
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getTag()
    {
        return $this->name;
    }

    private function hasBody(Twig_TokenStream $stream)
    {
        $look = 0;
        while ($token = $stream->look($look)) {
            if ($token->getType() === Twig_Token::EOF_TYPE) {
                return false;
            }

            if ($token->getType() === Twig_Token::NAME_TYPE
                && $token->getValue() === 'end' . $this->name
            ) {
                return true;
            }

            $look++;
        }

        return false;
    }
}
