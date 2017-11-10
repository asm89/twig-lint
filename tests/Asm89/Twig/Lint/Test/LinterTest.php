<?php

namespace Asm89\Twig\Lint\Test;

use Asm89\Twig\Lint\Config\Loader;
use Asm89\Twig\Lint\Linter;
use Asm89\Twig\Lint\Ruleset;
use Asm89\Twig\Lint\RulesetFactory;
use Asm89\Twig\Lint\Sniffs\SniffInterface;
use Asm89\Twig\Lint\StubbedEnvironment;
use Asm89\Twig\Lint\Tokenizer\Tokenizer;
use Symfony\Component\Config\FileLocator;

class LinterTest extends \PHPUnit_Framework_TestCase
{
    private $env;
    private $lint;
    private $workingDirectory;
    private $rulesetFactory;

    public function setUp()
    {
        $this->env = new StubbedEnvironment();
        $this->lint = new Linter($this->env, new Tokenizer($this->env));
        $this->workingDirectory = __DIR__ . '/Fixtures';
        $this->rulesetFactory = new RulesetFactory();
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
            ->addSniff(new \Asm89\Twig\Lint\Standards\Generic\Sniffs\WhitespaceBeforeAfterExpression())
            ->addSniff(new \Asm89\Twig\Lint\Standards\Generic\Sniffs\SimpleQuotesSniff())
            ->addSniff(new \Asm89\Twig\Lint\Standards\Generic\Sniffs\IncludeSniff())
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
     * @dataProvider dataBySniff
     */
    public function testLinter2($isFile, $filename, $sniff, $expects)
    {
        $this->checkGenericSniff($filename, $sniff, $expects);
    }

    /**
     * @dataProvider dataConfigYml1
     */
    public function testConfigYml1($filename, $expectLoadingError, $expectCount = 0, $expectAddErrors = true, $expectAdded = 0)
    {
        $loader = new Loader(new FileLocator(__DIR__ . '/Fixtures/config'));
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
     * @dataProvider dataConfigYml2
     */
    public function testConfigYml2($configFilename, $filename, $expectSeverity)
    {
        $ruleset = $this->rulesetFactory->createRulesetFromFile($configFilename, [$this->workingDirectory . '/config']);

        $report = $this->lint->run($this->workingDirectory . '/' . $filename, $ruleset);
        $messages = $report->getMessages();
        foreach ($messages as $message) {
            $this->assertEquals($expectSeverity, $message[5]);
        }
    }

    public function templateFixtures()
    {
        return [
            ['mixed.twig', 4, 0, 1, 30, 55],
        ];
    }

    public function dataBySniff()
    {
        return [
            [true, 'Tokenizer/tokenizer_5.twig', new \Asm89\Twig\Lint\Standards\Generic\Sniffs\DisallowTabIndentSniff(), [
                'Indentation using tabs is not allowed; use spaces instead',
                'Indentation using tabs is not allowed; use spaces instead',
                'Indentation using tabs is not allowed; use spaces instead',
            ]],
            [true, 'Linter/dump_tag.twig', new \Asm89\Twig\Lint\Standards\Generic\Sniffs\DumpSniff(), [
                'Found {% dump %} tag',
            ]],
            [true, 'Linter/dump_function.twig', new \Asm89\Twig\Lint\Standards\Generic\Sniffs\DumpSniff(), [
                'Found dump() function call',
            ]],
            [true, 'Linter/include_tag.twig', new \Asm89\Twig\Lint\Standards\Generic\Sniffs\IncludeSniff(), [
                'Include tag is deprecated, prefer the include() function',
                'Prefer to use template notation with "@" in include tag',
            ]],
            [true, 'Linter/include_function.twig', new \Asm89\Twig\Lint\Standards\Generic\Sniffs\IncludeSniff(), [
                'Prefer to use template notation with "@" in include function call()',
            ]],
            [true, 'Linter/include_no.twig', new \Asm89\Twig\Lint\Standards\Generic\Sniffs\IncludeSniff(), [
                'Missing template (first argument) in include function call()',
                'Invalid template (first argument, found "") in include function call()',
                'Invalid template (first argument, found "null") in include function call()',
                'Invalid template (first argument, found "false") in include function call()',
            ]],
            [true, 'Linter/trans_no.twig', new \Asm89\Twig\Lint\Standards\Generic\Sniffs\TranslationSniff(), [
                'Missing lang parameter in trans() filter call',
                'Missing domain parameter in trans() filter call'
            ]],
            [true, 'Linter/trans.twig', new \Asm89\Twig\Lint\Standards\Generic\Sniffs\TranslationSniff(), [
                'Missing lang parameter in trans() filter call'
            ]],
            [true, 'Linter/transchoice.twig', new \Asm89\Twig\Lint\Standards\Generic\Sniffs\TranslationSniff(), [
                'Missing lang parameter in transchoice() filter call'
            ]],
            [true, 'Linter/hash_1.twig', new \Asm89\Twig\Lint\Standards\Generic\Sniffs\EnforceHashKeyQuotesSniff(), [
                'Hash key \'4\' requires quotes; use single quotes',
                'Hash key \'isX\' requires quotes; use single quotes',
                'Hash key \'isY\' requires quotes; use single quotes',
            ]],
            [true, 'Linter/hash_2.twig', new \Asm89\Twig\Lint\Standards\Generic\Sniffs\EnforceHashKeyQuotesSniff(), [
                'Hash key \'is_true\' requires quotes; use single quotes',
                'Hash key \'display_errors\' requires quotes; use single quotes',
                'Hash key \'name\' requires quotes; use single quotes',
            ]],
            [true, 'Linter/hash_3.twig', new \Asm89\Twig\Lint\Standards\Generic\Sniffs\EnforceHashKeyQuotesSniff(), []],
            [true, 'Linter/hash_4.twig', new \Asm89\Twig\Lint\Standards\Generic\Sniffs\EnforceHashKeyQuotesSniff(), [
                'Hash key \'lvl1_x\' requires quotes; use single quotes',
                'Hash key \'lvl1_y\' requires quotes; use single quotes',
                'Hash key \'lvl1_y\' requires quotes; use single quotes',
                'Hash key \'lvl2_x\' requires quotes; use single quotes',
                'Hash key \'lvl3_z\' requires quotes; use single quotes',
            ]],
            [true, 'Linter/hash_1.twig', new \Asm89\Twig\Lint\Standards\Generic\Sniffs\EnforceHashTrailingCommaSniff(), [
                'Hash requires trailing comma after \'azerty\''
            ]],
            [true, 'Linter/hash_2.twig', new \Asm89\Twig\Lint\Standards\Generic\Sniffs\EnforceHashTrailingCommaSniff(), [
                'Hash requires trailing comma after \'lastname\''
            ]],
            [true, 'Linter/hash_3.twig', new \Asm89\Twig\Lint\Standards\Generic\Sniffs\EnforceHashTrailingCommaSniff(), []],
            [true, 'Linter/hash_4.twig', new \Asm89\Twig\Lint\Standards\Generic\Sniffs\EnforceHashTrailingCommaSniff(), [
                'Hash requires trailing comma after \'45.5\'',
                'Hash requires trailing comma after \'Oyoyo\'',
                'Hash requires trailing comma after \'}\'',
                'Hash requires trailing comma after \']\'',
            ]],
        ];
    }

    public function dataConfigYml1()
    {
        return [
            ['twigcs_0.yml', 'File "twigcs_0.yml" not found.'],
            ['twigcs_1.yml', false, 2, false, 2],
            ['twigcs_2.yml', false, 1, true, 0],
            ['twigcs_3.yml', 'Missing "class" key'],
            ['twigcs_4.yml', 'Missing "ruleset" key'],
        ];
    }

    public function dataConfigYml2()
    {
        return [
            ['twigcs_1.yml', 'Linter/dump_function.twig', 10],
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

        $this->assertEquals(count($expects), $report->getTotalWarnings() + $report->getTotalErrors());
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
