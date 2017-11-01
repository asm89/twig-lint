<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\Lint\Sniffs;

/**
 * TranslationNodeVisitor extracts translation messages.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TwigNodeVisitor extends AbstractSniff
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
