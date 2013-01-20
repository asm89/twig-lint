<?php

/*
 * This file is part of twig-lint.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\Lint\Test;

use Asm89\Twig\Lint\StubbedEnvironment;
use Twig_Error;

/**
 * @author Alexander <iam.asm89@gmail.com>
 */
class StubbedEnvironmentTest extends \PHPUnit_Framework_TestCase
{
    private $env;

    public function setup()
    {
        $this->env = new StubbedEnvironment();
    }

    public function testGetFilterAlwaysReturnsAFilter()
    {
        $filter = $this->env->getFilter('foo');

        $this->assertInstanceOf('Twig_Filter', $filter);
    }

    public function testGetFunctionAlwaysReturnsAFunction()
    {
        $function = $this->env->getFunction('foo');

        $this->assertInstanceOf('Twig_Function', $function);
    }

    /**
     * @dataProvider templateFixtures
     */
    public function testParseTemplatesWithUndefinedElements($filename)
    {
        $file     = __DIR__ . '/Fixtures/' . $filename;
        $template = file_get_contents($file);
        try {
            $this->env->parse($this->env->tokenize($template, $file));
        } catch (Twig_Error $exception) {
            $this->assertTrue(false, "Was unable to parse the template.");
        }

        $this->assertTrue(true, "Was able to parse the template.");
    }

    public function templateFixtures()
    {
        return array(
            array('render_tag.twig'),
            array('assetic_stylesheet_tag.twig'),
            array('trans_tag.twig'),
            array('multiple_trans_tags.twig'),
            array('undefined_test.twig'),
            array('undefined_functions.twig'),
            array('mixed.twig'),
        );
    }
}
