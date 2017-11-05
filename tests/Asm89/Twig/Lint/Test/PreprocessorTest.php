<?php

namespace Asm89\Twig\Lint\Test;

use Asm89\Twig\Lint\StubbedEnvironment;
use Asm89\Twig\Lint\Preprocessor\Preprocessor;
use Asm89\Twig\Lint\Preprocessor\Token;
use Asm89\Twig\Lint\Standards\Generic\Sniffs\WhitespaceBeforeAfterExpression;

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
        $stream = $preprocessor->tokenize($template);

        $sniff = new WhitespaceBeforeAfterExpression();

        dump($stream);
        foreach ($stream as $index => $token) {
            $sniff->process($token, $index, $stream);
        }

        dump($sniff->getMessages());

        // while (!$stream->isEOF()) {
        //     switch ($stream->getCurrent()->getType()) {
        //         case Token::VAR_START_TYPE:
        //             $next = $stream->next();

        //             if ($next->getType() !== Token::WHITESPACE_TYPE) {
        //                 // $messages[] = ['Missing whitespace at line']
        //             }

        //             break;
        //     }
        //     dump((string) $stream->next());
        // }
    }

    public function templateFixtures()
    {
        return [
            ['Lexer/lint_sniff_extra_whitespace_var.twig'],
        ];
    }
}
