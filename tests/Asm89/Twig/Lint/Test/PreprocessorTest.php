<?php

namespace Asm89\Twig\Lint\Test;

use Asm89\Twig\Lint\StubbedEnvironment;
use Asm89\Twig\Lint\Preprocessor\Preprocessor;
use Asm89\Twig\Lint\Preprocessor\TwigToken;

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
    public function testLexer($filename)
    {
        $file     = __DIR__ . '/Fixtures/' . $filename;
        $template = file_get_contents($file);

        $preprocessor = new Preprocessor(new Twig_Environment($this->getMockBuilder('Twig_LoaderInterface')->getMock()));
        // $x = $this->env->parse($this->env->tokenize($template, $file));
        // dump($x);
        $stream = $preprocessor->tokenize($template);

        while (!$stream->isEOF()) {
            switch ($stream->getCurrent()->getType()) {
                case TwigToken::VAR_START_TYPE:
                    $next = $stream->next();

                    if ($next->getType() !== TwigToken::WHITESPACE_TYPE) {
                        // $messages[] = ['Missing whitespace at line']
                    }

                    break;
            }
            dump((string) $stream->next());
        }

        // $stream->expect(Twig_Token::BLOCK_START_TYPE);

        // $this->assertSame('§', $stream->expect(Twig_Token::NAME_TYPE)->getValue());

        // dump((string) $stream);
    }

    public function templateFixtures()
    {
        return [
            ['Lexer/lint_sniff_extra_whitespace_var.twig'],
        ];
    }
}
