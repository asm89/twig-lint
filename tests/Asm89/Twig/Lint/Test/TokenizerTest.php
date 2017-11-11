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

        $stream = $tokenizer->tokenize(new \Twig_Source($template, $filename, $file));

        $this->assertCount($expectedTokenCount, $stream);
    }

    public function templateFixtures()
    {
        return [
            ['Tokenizer/tokenizer_1.twig', 52],
            ['Tokenizer/tokenizer_2.twig', 10],
            ['Tokenizer/tokenizer_3.twig', 15],
            ['Tokenizer/tokenizer_4.twig', 199],
            ['Tokenizer/tokenizer_5.twig', 46],
            ['mixed.twig', 385],
        ];
    }
}
