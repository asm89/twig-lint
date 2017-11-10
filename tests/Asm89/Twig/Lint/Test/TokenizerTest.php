<?php

namespace Asm89\Twig\Lint\Test;

use Asm89\Twig\Lint\StubbedEnvironment;
use Asm89\Twig\Lint\Tokenizer\Tokenizer;
use Asm89\Twig\Lint\Preprocessor\Token;
use \Twig_Environment;

class TokenizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider templateFixtures
     */
    public function testTokenizer($filename, $expectedTokenCount)
    {
        $file     = __DIR__ . '/Fixtures/' . $filename;
        $template = file_get_contents($file);

        $tokenizer = new Tokenizer(new Twig_Environment($this->getMockBuilder('Twig_LoaderInterface')->getMock()));

        $stream = $tokenizer->tokenize($template, $file);

        $this->assertCount($expectedTokenCount, $stream);
    }

    public function templateFixtures()
    {
        return [
            ['Lexer/tokenizer_1.twig', 52],
            ['Lexer/tokenizer_2.twig', 10],
            ['Lexer/tokenizer_3.twig', 15],
            ['Lexer/tokenizer_4.twig', 212],
            ['Lexer/tokenizer_5.twig', 46],
            ['mixed.twig', 385],
        ];
    }
}
