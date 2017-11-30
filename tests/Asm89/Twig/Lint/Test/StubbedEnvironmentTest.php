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
use PHPUnit\Framework\TestCase;
use \Twig_Error;

/**
 * @author Alexander <iam.asm89@gmail.com>
 */
class StubbedEnvironmentTest extends TestCase
{
    private $env;

    public function setup()
    {
        $this->env = new StubbedEnvironment(
            $this->getMockBuilder('Twig_LoaderInterface')->getMock(),
            array(
                'stub_tags'  => array('meh', 'render', 'some_other_block', 'stylesheets', 'trans'),
                'stub_tests' => array('created by', 'sometest', 'some_undefined_test', 'some_undefined_test_with_args'),
            )
        );
    }

    public function testGetFilterAlwaysReturnsAFilter()
    {
        $filter = $this->env->getFilter('foo');

        $this->assertInstanceOf('Twig_SimpleFilter', $filter);
    }

    public function testGetFunctionAlwaysReturnsAFunction()
    {
        $function = $this->env->getFunction('foo');

        $this->assertInstanceOf('Twig_SimpleFunction', $function);
    }

    /**
     * @dataProvider templateFixtures
     */
    public function testParseTemplatesWithUndefinedElements($filename)
    {
        $file     = __DIR__ . '/Fixtures/' . $filename;
        $template = file_get_contents($file);
        try {
            $this->env->parse($this->env->tokenize(new \Twig_Source($template, $file)));
        } catch (Twig_Error $exception) {
            $this->assertTrue(false, sprintf('Was unable to parse the template: "%s"', $exception->getMessage()));
        }

        $this->assertTrue(true, 'Was able to parse the template.');
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
