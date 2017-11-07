<?php

namespace Asm89\Twig\Lint\Test;

use Asm89\Twig\Lint\Linter;
use Asm89\Twig\Lint\Ruleset;
use Asm89\Twig\Lint\StubbedEnvironment;
use Asm89\Twig\Lint\Standards\Generic\Sniffs\EnforceHashKeyQuotesSniff;
use Asm89\Twig\Lint\Standards\Generic\Sniffs\IncludeSniff;
use Asm89\Twig\Lint\Standards\Generic\Sniffs\SimpleQuotesSniff;
use Asm89\Twig\Lint\Standards\Generic\Sniffs\WhitespaceBeforeAfterExpression;
use Asm89\Twig\Lint\Tokenizer\Tokenizer;

class LinterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->env = new StubbedEnvironment();
        $this->lint = new Linter($this->env, new Tokenizer($this->env));
    }

    public function testNewEngine()
    {
        $this->assertNotNull($this->lint);
    }

    /**
     * @dataProvider templateFixtures
     */
    public function testLinter1($filename, $expectWarnings, $expectErrors, $expectFiles, $expectLastMessageLine, $expectLastMessagePosition)
    {
        $file     = __DIR__ . '/Fixtures/' . $filename;
        $template = file_get_contents($file);

        $ruleset = new Ruleset();
        $ruleset
            ->addSniff($ruleset::EVENT['PRE_PARSER'], new WhitespaceBeforeAfterExpression())
            ->addSniff($ruleset::EVENT['PRE_PARSER'], new SimpleQuotesSniff())
            ->addSniff($ruleset::EVENT['POST_PARSER'], new IncludeSniff())
        ;

        $report = $this->lint->run([[$template, $file]], $ruleset);

        $this->assertEquals($expectErrors, $report->getTotalErrors());
        $this->assertEquals($expectWarnings, $report->getTotalWarnings());
        $this->assertEquals($expectFiles, $report->getTotalFiles());

        $messages = $report->getMessages();
        $lastMessage = $messages[count($messages) - 1];

        $this->assertEquals($expectLastMessageLine, $lastMessage[2]);
        $this->assertEquals($expectLastMessagePosition, $lastMessage[3]);
    }

    /**
     * @dataProvider dataProviderLintHash
     */
    public function testLintHash($filename)
    {
        $file     = __DIR__ . '/Fixtures/' . $filename;
        $template = file_get_contents($file);

        $ruleset = new Ruleset();
        $ruleset
            ->addSniff($ruleset::EVENT['PRE_PARSER'], new EnforceHashKeyQuotesSniff())
        ;

        $report = $this->lint->run([[$template, $file]], $ruleset);

        dump($report);
    }

    public function templateFixtures()
    {
        return [
            ['mixed.twig', 4, 0, 1, 30, 55],
        ];
    }

    public function dataProviderLintHash()
    {
        return [
            ['Hash/lint_hash_1.twig', 4, 0, 1, 30, 55],
            ['Hash/lint_hash_2.twig', 4, 0, 1, 30, 55],
            ['Hash/lint_hash_3.twig', 4, 0, 1, 30, 55],
            ['Hash/lint_hash_4.twig', 4, 0, 1, 30, 55],
        ];
    }
}
