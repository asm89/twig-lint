<?php

namespace Asm89\Twig\Lint\Standards\Generic\Sniffs;

use Asm89\Twig\Lint\Sniffs\AbstractSniff;

class TranslationSniff extends AbstractSniff
{
    /**
     * {@inheritdoc}
     */
    public function process(\Twig_Node $node, \Twig_Environment $env)
    {
        if ($this->isNodeMatching($node, 'filter', 'trans')) {
            $this->sniffTransMissingLang($node);
        } elseif ($this->isNodeMatching($node, 'filter', 'transchoice')) {
            $this->sniffTranschoiceMissingLang($node);
        }

        return $node;
    }

    public function sniffTransMissingLang($node)
    {
        if (count($node->getNode('arguments')) < 3) {
            $this->addMessage($this::MESSAGE_TYPE_ERROR, 'Missing lang parameter in trans() filter call', $node->getLine(), 0);
        }
    }

    public function sniffTranschoiceMissingLang($node)
    {
        if (count($node->getNode('arguments')) < 4) {
            $this->addMessage($this::MESSAGE_TYPE_ERROR, 'Missing lang parameter in transchoice() filter call', $node->getLine(), 0);
        }
    }
}
