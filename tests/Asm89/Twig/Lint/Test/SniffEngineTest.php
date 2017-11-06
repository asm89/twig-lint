<?php

namespace Asm89\Twig\Lint\Test;

use Asm89\Twig\Lint\Linter;
use Asm89\Twig\Lint\Ruleset;
use Asm89\Twig\Lint\StubbedEnvironment;
use Asm89\Twig\Lint\Standards\Generic\Sniffs\IncludeSniff;
use Asm89\Twig\Lint\Standards\Generic\Sniffs\WhitespaceBeforeAfterExpression;

class SniffEngineTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->env = new StubbedEnvironment();
        $this->lint = new Linter($this->env);
    }

    public function testNewEngine()
    {
        $this->assertNotNull($this->lint);
    }

    /**
     * @dataProvider templateFixtures
     */
    public function testLinter1($filename)
    {
        $file     = __DIR__ . '/Fixtures/' . $filename;
        $template = file_get_contents($file);

        $ruleset = new Ruleset($this->env);
        $ruleset
            ->addSniff($ruleset::EVENT['PRE_PARSER'], new WhitespaceBeforeAfterExpression())
            ->addSniff($ruleset::EVENT['POST_PARSER'], new IncludeSniff())
        ;

        dump($this->lint->run($template, $ruleset));
    }

    public function templateFixtures()
    {
        return [
            ['mixed.twig'],
        ];
    }
}
