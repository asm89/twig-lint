<?php

namespace Asm89\Twig\Lint\Test;

use Asm89\Twig\Lint\StubbedEnvironment;
use Asm89\Twig\Lint\Standards\Generic\Sniffs\TranslationSniff;
use Twig_Error;

class NodeVisitorTest extends \PHPUnit_Framework_TestCase
{
    private $env;

    private $sniffsExtension;

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

        $sniffExtension = $this->env->getExtension('Asm89\Twig\Lint\Extension\SniffsExtension');
        $sniffExtension->addSniff(new TranslationSniff());

        try {
            $this->env->parse($this->env->tokenize($template, $file));
        } catch (\Twig_Error $e) {

            return 1;
        }

        $messages = $sniffExtension->getMessages();

        $this->assertCount(1, $messages);
        $this->assertEquals($messages[0][1], $expect);
    }

    public function templateFixtures()
    {
        return [
            ['Translation/lint_sniff_trans.twig', 'Missing lang parameter in trans() filter call'],
            ['Translation/lint_sniff_transchoice.twig', 'Missing lang parameter in transchoice() filter call'],
        ];
    }
}
