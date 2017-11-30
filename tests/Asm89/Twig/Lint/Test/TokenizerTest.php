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

use Asm89\Twig\Lint\Tokenizer\Tokenizer;
use Asm89\Twig\Lint\Preprocessor\Token;
use PHPUnit\Framework\TestCase;
use \Twig_Environment;

class TokenizerTest extends TestCase
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
        return array(
            array('Tokenizer/tokenizer_1.twig', 52),
            array('Tokenizer/tokenizer_2.twig', 10),
            array('Tokenizer/tokenizer_3.twig', 15),
            array('Tokenizer/tokenizer_4.twig', 199),
            array('Tokenizer/tokenizer_5.twig', 46),
            array('mixed.twig', 385),
        );
    }
}
