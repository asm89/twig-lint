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

use Asm89\Twig\Lint\Extension\SniffsExtension;
use Asm89\Twig\Lint\TokenParser\CatchAll;
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
    private $stubCallable;

    /**
     * {@inheritDoc}
     */
    public function __construct(Twig_LoaderInterface $loader = null, $options = array())
    {
        parent::__construct($loader, $options);

        $this->stubCallable  = function () {
            /* This will be used as stub filter, function or test */
        };

        $this->stubFilters   = array();
        $this->stubFunctions = array();

        if (isset($options['stub_tags'])) {
            foreach ($options['stub_tags'] as $tag) {
                $this->addTokenParser(new CatchAll($tag));
            }
        }

        $this->stubTests = array();
        if (isset($options['stub_tests'])) {
            foreach ($options['stub_tests'] as $test) {
                $this->stubTests[$test] = new \Twig_SimpleTest('stub', $this->stubCallable);
            }
        }

        $this->addExtension(new SniffsExtension());
    }

    /**
     * {@inheritDoc}
     */
    public function getFilter($name)
    {
        if (!isset($this->stubFilters[$name])) {
            $this->stubFilters[$name] = new \Twig_SimpleFilter('stub', $this->stubCallable);
        }

        return $this->stubFilters[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function getFunction($name)
    {
        if (!isset($this->stubFunctions[$name])) {
            $this->stubFunctions[$name] = new \Twig_SimpleFunction('stub', $this->stubCallable);
        }

        return $this->stubFunctions[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function getTest($name)
    {
        $test = parent::getTest($name);
        if ($test) {
            return $test;
        }

        if (isset($this->stubTests[$name])) {
            return $this->stubTests[$name];
        }

        return false;
    }
}
