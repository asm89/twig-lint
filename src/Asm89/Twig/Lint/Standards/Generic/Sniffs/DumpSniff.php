<?php

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
