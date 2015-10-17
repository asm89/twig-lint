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

use Asm89\Twig\Lint\Extension\StubbedCore;
use Twig_LoaderInterface;

/**
 * Environment providing stubs for all filters, functions, tests and tags that
 * are not defined in twig's core.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class StubbedEnvironment extends \Twig_Environment
{
    private $stubFilters;
    private $stubFunctions;
    private $stubTests;
    protected $parsers;

    /**
     * {@inheritDoc}
     */
    public function __construct(Twig_LoaderInterface $loader = null, $options = array())
    {
        parent::__construct($loader, $options);

        $this->addExtension(new StubbedCore());
        $this->initExtensions();

        $broker = new StubbedTokenParserBroker();
        $this->parsers->addTokenParserBroker($broker);
    }

    /**
     * {@inheritDoc}
     */
    public function getFilter($name)
    {
        if (!isset($this->stubFilters[$name])) {
            $this->stubFilters[$name] = new \Twig_Filter_Function('stub');
        }

        return $this->stubFilters[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function getFunction($name)
    {
        if (!isset($this->stubFunctions[$name])) {
            $this->stubFunctions[$name] = new \Twig_Function_Function('stub');
        }

        return $this->stubFunctions[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function getTest($name)
    {
        if (!isset($this->stubTests[$name])) {
            $this->stubTests[$name] = new \Twig_SimpleTest('stub', function(){});
        }

        return $this->stubTests[$name];
    }
}
