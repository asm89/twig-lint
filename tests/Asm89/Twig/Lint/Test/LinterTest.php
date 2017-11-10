<?php

namespace Asm89\Twig\Lint\Test;

use Asm89\Twig\Lint\Config\Loader;
use Asm89\Twig\Lint\Linter;
use Asm89\Twig\Lint\Ruleset;
use Asm89\Twig\Lint\Sniffs\SniffInterface;
use Asm89\Twig\Lint\StubbedEnvironment;
use Asm89\Twig\Lint\Standards\Generic\Sniffs\DisallowTabIndentSniff;
use Asm89\Twig\Lint\Standards\Generic\Sniffs\EnforceHashKeyQuotesSniff;
use Asm89\Twig\Lint\Standards\Generic\Sniffs\EnforceHashTrailingCommaSniff;
use Asm89\Twig\Lint\Standards\Generic\Sniffs\IncludeSniff;
use Asm89\Twig\Lint\Standards\Generic\Sniffs\SimpleQuotesSniff;
use Asm89\Twig\Lint\Standards\Generic\Sniffs\WhitespaceBeforeAfterExpression;
use Asm89\Twig\Lint\Tokenizer\Tokenizer;
use Symfony\Component\Config\FileLocator;

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
        $file = __DIR__ . '/Fixtures/' . $filename;

        $ruleset = new Ruleset();
        $ruleset
            ->addSniff(new WhitespaceBeforeAfterExpression())
            ->addSniff(new SimpleQuotesSniff())
            ->addSniff(new IncludeSniff())
        ;

        $report = $this->lint->run($file, $ruleset);

        $this->assertEquals($expectErrors, $report->getTotalErrors());
        $this->assertEquals($expectWarnings, $report->getTotalWarnings());
        $this->assertEquals($expectFiles, $report->getTotalFiles());

        $messages = $report->getMessages();
        $lastMessage = $messages[count($messages) - 1];

        $this->assertEquals($expectLastMessageLine, $lastMessage[2]);
        $this->assertEquals($expectLastMessagePosition, $lastMessage[3]);
    }

    /**
     * @dataProvider dataConfigYml
     */
    public function testConfigYml1($filename, $expectLoadingError, $expectCount = 0, $expectAddErrors = true, $expectAdded = 0)
    {
        $loader = new Loader(new FileLocator(__DIR__ . '/Fixtures/'));
        try {
            $value = $loader->load($filename);

            $this->assertFalse($expectLoadingError);
        } catch (\Exception $e) {
            $this->assertEquals($expectLoadingError, $e->getMessage());

            return;
        }

        $this->assertCount($expectCount, $value['ruleset']);

        $ruleset = new Ruleset();
        foreach ($value['ruleset'] as $rule) {
            try {
                $ruleset->addSniff(new $rule['class']);

                $this->assertFalse($expectAddErrors);
            } catch (\Exception $e) {;
                $this->assertTrue($expectAddErrors);
            }
        }

        $this->assertCount($expectAdded, $ruleset->getSniffs());

        foreach ($value['ruleset'] as $rule) {
            $ruleset->removeSniff($rule['class']);
        }

        $this->assertCount(0, $ruleset->getSniffs());
    }

    /**
     * @dataProvider dataTabIndent
     */
    public function testTabIndent($filename, $sniff, $expects)
    {
        $this->checkGenericSniff($filename, $sniff, $expects);
    }

    /**
     * @dataProvider dataProviderLintHash1
     */
    public function testLintHash1($filename, $sniff, $expects)
    {
        $this->checkGenericSniff($filename, $sniff, $expects);
    }

    /**
     * @dataProvider dataProviderLintHash2
     */
    public function testLintHash2($filename, $sniff, $expects)
    {
        $this->checkGenericSniff($filename, $sniff, $expects);
    }

    public function templateFixtures()
    {
        return [
            ['mixed.twig', 4, 0, 1, 30, 55],
        ];
    }

    public function dataTabIndent()
    {
        return [
            ['Lexer/lint_sniff_tabs_indent.twig', new DisallowTabIndentSniff(), [
                'Indentation using tabs is not allowed; use spaces instead',
                'Indentation using tabs is not allowed; use spaces instead',
                'Indentation using tabs is not allowed; use spaces instead',
            ]],
        ];
    }

    public function dataConfigYml()
    {
        return [
            ['config/twigcs_0.yml', 'File "config/twigcs_0.yml" not found.'],
            ['config/twigcs_1.yml', false, 2, false, 2],
            ['config/twigcs_2.yml', false, 1, true, 0],
            ['config/twigcs_3.yml', 'Missing "class" key'],
            ['config/twigcs_4.yml', 'Missing "ruleset" key'],
        ];
    }

    public function dataProviderLintHash1()
    {
        return [
            ['Hash/lint_hash_1.twig', new EnforceHashKeyQuotesSniff(), [
                'Hash key \'4\' requires quotes; use single quotes',
                'Hash key \'isX\' requires quotes; use single quotes',
                'Hash key \'isY\' requires quotes; use single quotes',
            ]],
            ['Hash/lint_hash_2.twig', new EnforceHashKeyQuotesSniff(), [
                'Hash key \'is_true\' requires quotes; use single quotes',
                'Hash key \'display_errors\' requires quotes; use single quotes',
                'Hash key \'name\' requires quotes; use single quotes',
            ]],
            ['Hash/lint_hash_3.twig', new EnforceHashKeyQuotesSniff(), []],
            ['Hash/lint_hash_4.twig', new EnforceHashKeyQuotesSniff(), [
                'Hash key \'lvl1_x\' requires quotes; use single quotes',
                'Hash key \'lvl1_y\' requires quotes; use single quotes',
                'Hash key \'lvl1_y\' requires quotes; use single quotes',
                'Hash key \'lvl2_x\' requires quotes; use single quotes',
                'Hash key \'lvl3_z\' requires quotes; use single quotes',
            ]],
        ];
    }

    public function dataProviderLintHash2()
    {
        return [
            ['Hash/lint_hash_1.twig', new EnforceHashTrailingCommaSniff(), [
                'Hash requires trailing comma after \'azerty\''
            ]],
            ['Hash/lint_hash_2.twig', new EnforceHashTrailingCommaSniff(), [
                'Hash requires trailing comma after \'lastname\''
            ]],
            ['Hash/lint_hash_3.twig', new EnforceHashTrailingCommaSniff(), []],
            ['Hash/lint_hash_4.twig', new EnforceHashTrailingCommaSniff(), [
                'Hash requires trailing comma after \'45.5\'',
                'Hash requires trailing comma after \'Oyoyo\'',
                'Hash requires trailing comma after \'}\'',
                'Hash requires trailing comma after \']\'',
            ]],
        ];
    }

    protected function checkGenericSniff($filename, $sniff, $expects)
    {
        $file = __DIR__ . '/Fixtures/' . $filename;

        $ruleset = new Ruleset();
        $ruleset
            ->addSniff($sniff)
        ;

        $report = $this->lint->run($file, $ruleset);

        dump($report);
        $this->assertEquals(count($expects), $report->getTotalWarnings());
        if ($expects) {
            $messageStrings = array_map(function ($message) {
                return $message[1];
            }, $report->getMessages());

            foreach ($expects as $expect) {
                $this->assertContains($expect, $messageStrings);
            }
        }
    }
}
