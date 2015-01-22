<?php

/*
 * This file is part of twig-lint.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\Lint\Extension;

/**
 * Overridden core extension to stub tests.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class StubbedCore extends \Twig_Extension_Core
{
    /**
     * Return a class name for every test name.
     *
     * @param \Twig_Parser $parser
     * @param string       $name
     * @param integer      $line
     *
     * @return string
     */
    protected function getTestNodeClass(\Twig_Parser $parser, $name)
    {
        return 'Twig_Node_Expression_Test';
    }

    protected function getTestName(\Twig_Parser $parser, $line)
    {
        try {
            return parent::getTestName($parser, $line);
        } catch (\Twig_Error_Syntax $exception) {
            return 'null';
        }
    }
}
