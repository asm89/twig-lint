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

use Asm89\Twig\Lint\Config;
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
    private $debug;

    public function setUp()
    {
        $this->env = new StubbedEnvironment(
            $this->getMockBuilder('Twig_LoaderInterface')->getMock(),
            array(
                'stub_tags' => array('dump', 'meh', 'render', 'some_other_block', 'stylesheets', 'trans'),
            )
        );
        $this->lint = new Linter($this->env, new Tokenizer($this->env));
        $this->workingDirectory = __DIR__ . '/Fixtures';
        $this->rulesetFactory = new RulesetFactory();

        // https://stackoverflow.com/questions/12610605
        $this->debug = in_array('--debug', $_SERVER['argv'], true);
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
            ->addSniff(new \Asm89\Twig\Lint\Standards\Generic\Sniffs\DisallowIncludeTagSniff())
            ->addSniff(new \Asm89\Twig\Lint\Standards\Generic\Sniffs\EnsureQuotesStyleSniff())
            ->addSniff(new \Asm89\Twig\Lint\Standards\Generic\Sniffs\EnsureWhitespaceExpressionSniff())
        ;

        $report = $this->lint->run($file, $ruleset);
        if ($this->debug) {
            $this->dump($report);
        }

        $this->assertEquals($expectErrors, $report->getTotalErrors(), 'Number of errors');
        $this->assertEquals($expectWarnings, $report->getTotalWarnings(), 'Number of warnings');
        $this->assertEquals($expectFiles, $report->getTotalFiles(), 'Number of files');

        $messages = $report->getMessages();
        $lastMessage = $messages[count($messages) - 1];

        $this->assertEquals($expectLastMessageLine, $lastMessage->getLine(), 'Line number of the error');
        $this->assertEquals($expectLastMessagePosition, $lastMessage->getLinePosition(), 'Line position of the error (if any)');
    }

    /**
     * @dataProvider dataDeprecatedTemplateNotationSniff
     */
    public function testDeprecatedTemplateNotationSniff($isFile, $filename, $sniff, $expects)
    {
        $this->checkGenericSniff($filename, $sniff, $expects);
    }

    /**
     * @dataProvider dataDisallowDumpSniff
     */
    public function testDisallowDumpSniff($isFile, $filename, $sniff, $expects)
    {
        $this->checkGenericSniff($filename, $sniff, $expects);
    }

    /**
     * @dataProvider dataDisallowIncludeSniff
     */
    public function testDisallowIncludeSniff($isFile, $filename, $sniff, $expects)
    {
        $this->checkGenericSniff($filename, $sniff, $expects);
    }

    /**
     * @dataProvider dataDisallowCommentedCodeSniff
     */
    public function testDisallowCommentedCodeSniff($isFile, $filename, $sniff, $expects)
    {
        $this->checkGenericSniff($filename, $sniff, $expects);
    }

    /**
     * @dataProvider dataEnsureHashAllSniff
     */
    public function testEnsureHashAllSniff($isFile, $filename, $sniff, $expects)
    {
        $this->checkGenericSniff($filename, $sniff, $expects);
    }

    /**
     * @dataProvider dataEnsureHashSpacingSniff
     */
    public function testEnsureHashSpacingSniff($isFile, $filename, $sniff, $expects)
    {
        $this->checkGenericSniff($filename, $sniff, $expects);
    }

    /**
     * @dataProvider dataEnsureTranslationArgumentsSniff
     */
    public function testEnsureTranslationArgumentsSniff($isFile, $filename, $sniff, $expects)
    {
        $this->checkGenericSniff($filename, $sniff, $expects);
    }

    /**
     * @dataProvider dataEnsureBlankAtEOFSniff
     */
    public function testEnsureBlankAtEOFSniff($isFile, $filename, $sniff, $expects)
    {
        $this->checkGenericSniff($filename, $sniff, $expects);
    }

    /**
     * @dataProvider dataEnsureQuotesStyleSniff
     */
    public function testEnsureQuotesStyleSniff($isFile, $filename, $sniff, $expects)
    {
        $this->checkGenericSniff($filename, $sniff, $expects);
    }

    /**
     * @dataProvider dataEnsureWhitespaceExpressionSniff
     */
    public function testEnsureWhitespaceExpressionSniff($isFile, $filename, $sniff, $expects)
    {
        $this->checkGenericSniff($filename, $sniff, $expects);
    }

    /**
     * @dataProvider dataConfig1
     */
    public function testConfig1($filename, $expectLoadingError, $expectCount = 0, $expectAddErrors = true, $expectAdded = 0)
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
            } catch (\Exception $e) {
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
     * @dataProvider dataConfig2
     */
    public function testConfig2($configFilename, $filename, $expectSeverity)
    {
        $loader        = new Loader(new FileLocator($this->workingDirectory . '/config'));
        $config        = new Config(array('workingDirectory' => $this->workingDirectory . '/config'), $loader->load($configFilename));
        $ruleset       = $this->rulesetFactory->createRulesetFromConfig($config);

        $report = $this->lint->run($this->workingDirectory . '/' . $filename, $ruleset);
        $messages = $report->getMessages();
        foreach ($messages as $message) {
            $this->assertEquals($expectSeverity, $message->getSeverity());
        }
    }

    /**
     * @dataProvider dataConfig3
     */
    public function testConfig3($configArray)
    {
        $config  = new Config($configArray);
        $ruleset = $this->rulesetFactory->createRulesetFromConfig($config);

        $this->assertCount(1, $ruleset->getSniffs());
    }

    public function templateFixtures()
    {
        return array(
            array('Linter/error_1.twig', 0, 1, 1, 1, null),
            array('mixed.twig', 2, 0, 1, 1, 11),
        );
    }

    public function dataDeprecatedTemplateNotationSniff()
    {
        $sniff = new \Asm89\Twig\Lint\Standards\Generic\Sniffs\DeprecatedTemplateNotationSniff();
        return array(
            array(true, 'Linter/include_function.twig', $sniff, array(
                'Deprecated template notation "AcmeOyoBundle:Front:index.html.twig"; use Symfony 3+ template notation with "@" instead',
            )),
            array(true, 'Linter/include_tag.twig', $sniff, array(
                'Deprecated template notation "AcmeOyoBundle:Front:index.html.twig"; use Symfony 3+ template notation with "@" instead',
                'Deprecated template notation "AcmeOyoBundle:Front:index@legacy.html.twig"; use Symfony 3+ template notation with "@" instead',
            )),
            array(true, 'Linter/extends_tag.twig', $sniff, array(
                'Deprecated template notation "AcmeOyoBundle:Front:extend.html.twig"; use Symfony 3+ template notation with "@" instead',
            )),
            array(true, 'Linter/embed_tag.twig', $sniff, array(
                'Deprecated template notation "AcmeOyoBundle:Front:index.html.twig"; use Symfony 3+ template notation with "@" instead',
                'Deprecated template notation "AcmeOyoBundle/Front/index@legacy.html.twig"; use Symfony 3+ template notation with "@" instead',
            )),
        );
    }

    public function dataDisallowDumpSniff()
    {
        $sniff = new \Asm89\Twig\Lint\Standards\Generic\Sniffs\DisallowDumpSniff();
        return array(
            array(true, 'Linter/dump_tag.twig', $sniff, array(
                'Call to debug tag dump() must be removed',
            )),
            array(true, 'Linter/dump_function.twig', $sniff, array(
                'Call to debug function dump() must be removed',
            )),
        );
    }

    public function dataDisallowIncludeSniff()
    {
        $sniff = new \Asm89\Twig\Lint\Standards\Generic\Sniffs\DisallowIncludeTagSniff();
        return array(
            array(true, 'Linter/include_function.twig', $sniff, array(
            )),
            array(true, 'Linter/include_tag.twig', $sniff, array(
                'Include tag is deprecated; use the include() function instead',
                'Include tag is deprecated; use the include() function instead',
                'Include tag is deprecated; use the include() function instead',
                'Include tag is deprecated; use the include() function instead',
            )),
            array(true, 'Linter/include_no.twig', $sniff, array(
            )),
        );
    }

    public function dataDisallowCommentedCodeSniff()
    {
        $sniff = new \Asm89\Twig\Lint\Standards\Generic\Sniffs\DisallowCommentedCodeSniff();
        return array(
            array(true, 'Linter/comment_1.twig', $sniff, array(
            )),
            array(true, 'Linter/comment_2.twig', $sniff, array(
                'Probable commented code found; keeping commented code is usually not advised',
            )),
        );
    }

    public function dataEnsureTranslationArgumentsSniff()
    {
        $sniff = new \Asm89\Twig\Lint\Standards\Generic\Sniffs\EnsureTranslationArgumentsSniff();
        return array(
            array(true, 'Linter/trans_no.twig', $sniff, array(
                'Call to filter trans() requires parameter "domain"; expected 2 parameters, found 0',
                'Call to filter trans() requires parameter "lang"; expected 3 parameters, found 0',
            )),
            array(true, 'Linter/trans.twig', $sniff, array(
                'Call to filter trans() requires parameter "lang"; expected 3 parameters, found 2',
            )),
            array(true, 'Linter/transchoice.twig', $sniff, array(
                'Call to filter transchoice() requires parameter "lang"; expected 4 parameters, found 3',
            )),
        );
    }

    public function dataEnsureBlankAtEOFSniff()
    {
        $sniff = new \Asm89\Twig\Lint\Standards\Generic\Sniffs\EnsureBlankAtEOFSniff();
        return array(
            array(true, 'Linter/eof_0.twig', $sniff, array(
                'A file must end with 1 blank line; found 0',
            )),
            array(true, 'Linter/eof_2.twig', $sniff, array(
                'A file must end with 1 blank line; found 3',
            )),
            array(true, 'Linter/eof_3.twig', $sniff, array(
                'A file must end with 1 blank line; found 0',
            )),
            array(true, 'Linter/empty.twig', $sniff, array(
                'A file must end with 1 blank line; found 0'
            )),
        );
    }

    public function dataEnsureHashAllSniff()
    {
        $sniff = new \Asm89\Twig\Lint\Standards\Generic\Sniffs\EnsureHashKeyQuotesSniff();
        $sniff2 = new \Asm89\Twig\Lint\Standards\Generic\Sniffs\EnsureHashTrailingCommaSniff();
        return array(
            array(true, 'Tokenizer/tokenizer_5.twig', new \Asm89\Twig\Lint\Standards\Generic\Sniffs\DisallowTabIndentSniff(), array(
                'Indentation using tabs is not allowed; use spaces instead',
                'Indentation using tabs is not allowed; use spaces instead',
                'Indentation using tabs is not allowed; use spaces instead',
            )),
            array(true, 'Linter/hash_1.twig', $sniff, array(
                'Hash key \'4\' requires quotes; use single quotes',
                'Hash key \'isX\' requires quotes; use single quotes',
                'Hash key \'isY\' requires quotes; use single quotes',
            )),
            array(true, 'Linter/hash_2.twig', $sniff, array(
                'Hash key \'is_true\' requires quotes; use single quotes',
                'Hash key \'display_errors\' requires quotes; use single quotes',
                'Hash key \'name\' requires quotes; use single quotes',
            )),
            array(true, 'Linter/hash_3.twig', $sniff, array()),
            array(true, 'Linter/hash_4.twig', $sniff, array(
                'Hash key \'lvl1_x\' requires quotes; use single quotes',
                'Hash key \'lvl1_y\' requires quotes; use single quotes',
                'Hash key \'lvl1_y\' requires quotes; use single quotes',
                'Hash key \'lvl2_x\' requires quotes; use single quotes',
                'Hash key \'lvl3_z\' requires quotes; use single quotes',
            )),
            array(true, 'Linter/hash_1.twig', $sniff2, array(
                'Hash requires trailing comma after \'azerty\''
            )),
            array(true, 'Linter/hash_2.twig', $sniff2, array(
                'Hash requires trailing comma after \'lastname\''
            )),
            array(true, 'Linter/hash_3.twig', $sniff2, array()),
            array(true, 'Linter/hash_4.twig', $sniff2, array(
                'Hash requires trailing comma after \'45.5\'',
                'Hash requires trailing comma after \'Oyoyo\'',
                'Hash requires trailing comma after \'}\'',
                'Hash requires trailing comma after \']\'',
            )),
        );
    }

    public function dataEnsureQuotesStyleSniff()
    {
        $sniff1 = new \Asm89\Twig\Lint\Standards\Generic\Sniffs\EnsureQuotesStyleSniff();
        $sniff2 = new \Asm89\Twig\Lint\Standards\Generic\Sniffs\EnsureQuotesStyleSniff(array('style' => 'TYPE_DOUBLE_QUOTES'));
        return array(
            array(true, 'Linter/quotes_1.twig', $sniff1, array(
                'String "@Acme/Some/base.html.twig" does not require double quotes; use single quotes instead',
                'String "some" does not require double quotes; use single quotes instead',
                'String "default" does not require double quotes; use single quotes instead',
                'String "some_value" does not require double quotes; use single quotes instead',
                'String "Value: " does not require double quotes; use single quotes instead',
                'String "." does not require double quotes; use single quotes instead',
                'String "front" does not require double quotes; use single quotes instead',
            )),
            array(true, 'Linter/quotes_1.twig', $sniff2, array(
                'String \'TYPE_1\' uses single quotes; use double quotes instead',
            )),
        );
    }

    public function dataEnsureWhitespaceExpressionSniff()
    {
        $sniff1 = new \Asm89\Twig\Lint\Standards\Generic\Sniffs\EnsureWhitespaceExpressionSniff();
        $sniff2 = new \Asm89\Twig\Lint\Standards\Generic\Sniffs\EnsureWhitespaceExpressionSniff(array('count' => 0));
        return array(
            array(true, 'Linter/whitespace_1.twig', $sniff1, array(
                'Expecting 1 whitespace AFTER start of expression eg. "{{" or "{%"; found 0',
                'Expecting 1 whitespace BEFORE end of expression eg. "}}" or "%}"; found 0',
            )),
            array(true, 'Linter/whitespace_1.twig', $sniff2, array(
            )),
        );
    }

    public function dataEnsureHashSpacingSniff()
    {
        $sniff1 = new \Asm89\Twig\Lint\Standards\Generic\Sniffs\EnsureHashSpacingSniff();
        return array(
            array(true, 'Linter/whitespace_2.twig', $sniff1, array(
                'Expecting 0 whitespace AFTER "{"; found 3',
                'Expecting 0 whitespace AFTER "{"; found 1',
                'Expecting 0 whitespace BEFORE "}"; found 1',
                'Expecting 0 whitespace AFTER "["; found 1',
                'Expecting 0 whitespace BEFORE "]"; found 1',
            )),
        );
    }

    public function dataConfig1()
    {
        return array(
            array('twigcs_0.yml', 'File "twigcs_0.yml" not found.'),
            array('twigcs_1.yml', false, 2, false, 2),
            array('twigcs_2.yml', false, 1, true, 0),
            array('twigcs_3.yml', 'Missing "class" key'),
            array('twigcs_4.yml', 'Missing "ruleset" key'),
        );
    }

    public function dataConfig2()
    {
        return array(
            array('twigcs_1.yml', 'Linter/dump_function.twig', 10),
        );
    }

    public function dataConfig3()
    {
        return array(
            array(array(
                'ruleset' => array(
                    array('class' => '\Acme\Standards\TwigCS\Sniffs\DummySniff'),
                ),
                'standardPaths' => array(
                    '\Acme\Standards\TwigCS' => array(__DIR__ . '/Fixtures/Standards/'),
                ),
            )),
        );
    }

    protected function checkGenericSniff($filename, $sniff, $expects)
    {
        $file = __DIR__ . '/Fixtures/' . $filename;

        $ruleset = new Ruleset();
        $ruleset
            ->addSniff($sniff)
        ;

        $report = $this->lint->run($file, $ruleset);
        if ($this->debug) {
            $this->dump($report);
        }

        $this->assertEquals(count($expects), $report->getTotalWarnings() + $report->getTotalErrors());
        if ($expects) {
            $messageStrings = array_map(function ($message) {
                return $message->getMessage();
            }, $report->getMessages());

            foreach ($expects as $expect) {
                $this->assertContains($expect, $messageStrings);
            }
        }
    }

    protected function dump($any)
    {
        if (function_exists('dump')) {
            return dump($any);
        }

        return var_dump($any);
    }
}
