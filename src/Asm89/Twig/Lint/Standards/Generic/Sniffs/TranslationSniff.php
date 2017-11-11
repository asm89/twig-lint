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
            $this->addMessage($this::MESSAGE_TYPE_WARNING, 'Missing domain parameter in trans() filter call', $node);
        }
    }

    public function sniffTransMissingLang($node)
    {
        if (count($node->getNode('arguments')) < 3) {
            $this->addMessage($this::MESSAGE_TYPE_ERROR, 'Missing lang parameter in trans() filter call', $node);
        }
    }

    public function sniffTranschoiceMissingLang($node)
    {
        if (count($node->getNode('arguments')) < 4) {
            $this->addMessage($this::MESSAGE_TYPE_ERROR, 'Missing lang parameter in transchoice() filter call', $node);
        }
    }
}
