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
    public function testVisitor($filename, $expects)
    {
        $file     = __DIR__ . '/Fixtures/' . $filename;
        $template = file_get_contents($file);

        $sniffExtension = $this->env->getExtension('Asm89\Twig\Lint\Extension\SniffsExtension');
        $sniffExtension->addSniff(new TranslationSniff());

        $this->env->parse($this->env->tokenize($template, $file));

        $messages = $sniffExtension->getMessages();
        $messageStrings = array_map(function ($message) {
            return $message[1];
        }, $messages);

        $this->assertFalse(empty($messages));
        foreach ($expects as $expect) {
            $this->assertContains($expect, $messageStrings);
        }

    }

    public function templateFixtures()
    {
        return [
            ['Translation/lint_sniff_trans_no.twig', [
                'Missing lang parameter in trans() filter call',
                'Missing domain parameter in trans() filter call'
            ]],
            ['Translation/lint_sniff_trans.twig', ['Missing lang parameter in trans() filter call']],
            ['Translation/lint_sniff_transchoice.twig', ['Missing lang parameter in transchoice() filter call']],
        ];
    }
}
