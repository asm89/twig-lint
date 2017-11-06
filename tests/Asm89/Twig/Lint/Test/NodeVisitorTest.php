<?php

namespace Asm89\Twig\Lint\Test;

use Asm89\Twig\Lint\Report;
use Asm89\Twig\Lint\StubbedEnvironment;
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
    public function testVisitor($isFile, $filename, $expects)
    {
        if ($isFile) {
            $file     = __DIR__ . '/Fixtures/' . $filename;
            $template = file_get_contents($file);
        } else {
            $file = null;
            $template = $filename;
        }

        $sniffExtension = $this->env->getExtension('Asm89\Twig\Lint\Extension\SniffsExtension');
        $report = new Report();
        foreach ([
            '\Asm89\Twig\Lint\Standards\Generic\Sniffs\IncludeSniff',
            '\Asm89\Twig\Lint\Standards\Generic\Sniffs\TranslationSniff',
            '\Asm89\Twig\Lint\Standards\Generic\Sniffs\DumpSniff'
        ] as $sniffClass) {
            $sniffs[] = $sniffExtension->addSniff((new $sniffClass())->enable($report));
        }

        $this->env->parse($this->env->tokenize($template, $file));

        $messages = $report->getMessages();
        $messageStrings = array_map(function ($message) {
            return $message[1];
        }, $messages);

        $this->assertEquals(count($expects), count($messages));
        if ($expects) {
            foreach ($expects as $expect) {
                $this->assertContains($expect, $messageStrings);
            }
        }
    }

    public function templateFixtures()
    {
        return [
            [true, 'Dump/lint_sniff_dump_tag.twig', [
                'Found {% dump %} tag',
            ]],
            [true, 'Dump/lint_sniff_dump_function.twig', [
                'Found dump() function call',
            ]],
            [true, 'Include/lint_sniff_include_tag.twig', [
                'Include tag is deprecated, prefer the include() function',
                'Prefer to use template notation with "@" in include tag',
            ]],
            [true, 'Include/lint_sniff_include_function.twig', [
                'Prefer to use template notation with "@" in include function call()',
            ]],
            [true, 'Include/lint_sniff_include_no.twig', [
                'Missing template (first argument) in include function call()',
                'Invalid template (first argument, found "") in include function call()',
                'Invalid template (first argument, found "null") in include function call()',
                'Invalid template (first argument, found "false") in include function call()',
            ]],
            [true, 'Translation/lint_sniff_trans_no.twig', [
                'Missing lang parameter in trans() filter call',
                'Missing domain parameter in trans() filter call'
            ]],
            [true, 'Translation/lint_sniff_trans.twig', ['Missing lang parameter in trans() filter call']],
            [true, 'Translation/lint_sniff_transchoice.twig', ['Missing lang parameter in transchoice() filter call']],
        ];
    }
}
