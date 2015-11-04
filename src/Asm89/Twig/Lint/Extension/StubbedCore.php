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

use \Twig_Parser;
use \Twig_Token;

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

    protected function getTest(Twig_Parser $parser, $line)
    {
        $stream = $parser->getStream();
        $name = $stream->expect(Twig_Token::NAME_TYPE)->getValue();
        $env = $parser->getEnvironment();

        if ($stream->test(Twig_Token::NAME_TYPE)) {
            // try 2-words tests
            $name = $name.' '.$parser->getCurrentToken()->getValue();

            if ($test = $env->getTest($name)) {
                $parser->getStream()->next();

                return array($name, $test);
            }
        }

        if ($test = $env->getTest($name)) {
            return array($name, $test);
        }

        $e = new Twig_Error_Syntax(sprintf('Unknown "%s" test.', $name), $line, $parser->getFilename());
        $e->addSuggestions($name, array_keys($env->getTests()));

        throw $e;
    }
}
