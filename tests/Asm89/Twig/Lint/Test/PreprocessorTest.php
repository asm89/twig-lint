<?php

namespace Asm89\Twig\Lint\Test;

use Asm89\Twig\Lint\StubbedEnvironment;
use Asm89\Twig\Lint\Tokenizer\Tokenizer;
use Asm89\Twig\Lint\Preprocessor\Token;

use \Twig_Environment;
use \Twig_Error;
use \Twig_Source;

class PreprocessorTest extends \PHPUnit_Framework_TestCase
{
    // private $env;

    public function setUp()
    {
        $this->env = new StubbedEnvironment();
    }

    /**
     * @dataProvider templateFixtures
     */
    public function testLexer($filename, $expectedTokenCount)
    {
        $file     = __DIR__ . '/Fixtures/' . $filename;
        $template = file_get_contents($file);

        $tokenizer = new Tokenizer(new Twig_Environment($this->getMockBuilder('Twig_LoaderInterface')->getMock()));

        $stream = $tokenizer->tokenize($template);

        $this->assertCount($expectedTokenCount, $stream);
    }

    public function templateFixtures()
    {
        return [
            ['Lexer/lint_sniff_extra_eol.twig', 15],
            ['Lexer/lint_sniff_extra_whitespace_var.twig', 10],
            ['Lexer/lint_sniff_complete.twig', 212],
        ];
    }
}
