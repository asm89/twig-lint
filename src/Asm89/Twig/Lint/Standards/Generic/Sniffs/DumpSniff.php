<?php

/*
 * This file is part of twig-lint.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\Lint\Standards\Generic\Sniffs;

use Asm89\Twig\Lint\Sniffs\AbstractPostParserSniff;

class DumpSniff extends AbstractPostParserSniff
{
    /**
     * {@inheritdoc}
     */
    public function process(\Twig_Node $node, \Twig_Environment $env)
    {
        if ($this->isNodeMatching($node, 'tag', 'dump')) {
            $this->sniffDumpTag($node);
        } elseif ($this->isNodeMatching($node, 'function', 'dump') || $this->isNodeMatching($node, 'function', 'dump')) {
            $this->sniffDumpFunction($node);
        }

        return $node;
    }

    public function sniffDumpTag($node)
    {
        $this->addMessage($this::MESSAGE_TYPE_WARNING, 'Found {% dump %} tag', $node);
    }

    public function sniffDumpFunction($node)
    {
        $this->addMessage($this::MESSAGE_TYPE_WARNING, 'Found dump() function call', $node);
    }
}
