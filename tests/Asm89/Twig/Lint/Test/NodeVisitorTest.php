<?php

namespace Asm89\Twig\Lint\Test;

use Asm89\Twig\Lint\StubbedEnvironment;
use Twig_Error;

class NodeVisitorTest extends \PHPUnit_Framework_TestCase
{
    private $env;

    public function setUp()
    {
        $this->env = new StubbedEnvironment();
    }

    /**
     * @dataProvider templateFixtures
     */
    public function testVisitor($filename, $expect)
    {
        $file     = __DIR__ . '/Fixtures/' . $filename;
        $template = file_get_contents($file);

        $visitor = $this->env->getExtension('Asm89\Twig\Lint\Extension\StubbedCore')->getSnifferNodeVisitor();
        $visitor->enable();

        try {
            $this->env->parse($this->env->tokenize($template, $file));

        } catch (\Twig_Error $e) {
            $visitor->disable();

            return 1;
        }

        $messages = $visitor->getMessages();

        $this->assertCount(1, $messages);
        $this->assertEquals($messages[0][1], $expect);

        $visitor->disable();
    }

    public function templateFixtures()
    {
        return [
            ['lint_sniff_trans.twig', 'Missing lang parameter in trans() filter call'],
            ['lint_sniff_transchoice.twig', 'Missing lang parameter in transchoice() filter call'],
        ];
    }
}
