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

use Asm89\Twig\Lint\TokenParser\CatchAll;
use Twig_TokenParserBroker;

/**
 * Broker providing stubs for all tags that are not defined.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class StubbedTokenParserBroker extends Twig_TokenParserBroker
{
    protected $parser;
    protected $parsers;

    /**
     * {@inheritDoc}
     */
    public function getTokenParser($name)
    {
        if (!isset($this->parsers[$name])) {
            $this->parsers[$name] = new CatchAll($name);
            $this->parsers[$name]->setParser($this->parser);
        }

        return $this->parsers[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * {@inheritDoc}
     */
    public function setParser(\Twig_ParserInterface $parser)
    {
        $this->parser = $parser;
    }
}
