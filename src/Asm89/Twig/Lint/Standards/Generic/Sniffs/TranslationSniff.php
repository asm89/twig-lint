<?php

namespace Asm89\Twig\Lint\Standards\Generic\Sniffs;

use Asm89\Twig\Lint\Sniffs\AbstractPostParserSniff;

class TranslationSniff extends AbstractPostParserSniff
{
    /**
     * {@inheritdoc}
     */
    public function process(\Twig_Node $node, \Twig_Environment $env)
    {
        if ($this->isNodeMatching($node, 'filter', 'trans')) {
            $this->sniffTransMissingDomain($node);
            $this->sniffTransMissingLang($node);
        } elseif ($this->isNodeMatching($node, 'filter', 'transchoice')) {
            $this->sniffTranschoiceMissingLang($node);
        }

        return $node;
    }

    public function sniffTransMissingDomain($node)
    {
        if (count($node->getNode('arguments')) < 2) {
            $this->getReport()->addMessage($this::MESSAGE_TYPE_WARNING, 'Missing domain parameter in trans() filter call', $node->getLine(), 0);
        }
    }

    public function sniffTransMissingLang($node)
    {
        if (count($node->getNode('arguments')) < 3) {
            $this->getReport()->addMessage($this::MESSAGE_TYPE_ERROR, 'Missing lang parameter in trans() filter call', $node->getLine(), 0);
        }
    }

    public function sniffTranschoiceMissingLang($node)
    {
        if (count($node->getNode('arguments')) < 4) {
            $this->getReport()->addMessage($this::MESSAGE_TYPE_ERROR, 'Missing lang parameter in transchoice() filter call', $node->getLine(), 0);
        }
    }
}
