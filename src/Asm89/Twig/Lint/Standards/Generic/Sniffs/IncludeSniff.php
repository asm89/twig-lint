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

class IncludeSniff extends AbstractPostParserSniff
{
    /**
     * {@inheritdoc}
     */
    public function process(\Twig_Node $node, \Twig_Environment $env)
    {
        if ($this->isNodeMatching($node, 'include')) {
            $this->sniffIncludeTag($node);
            $this->sniffTagTemplateFormat($node);
        } elseif ($this->isNodeMatching($node, 'function', 'include')) {
            $this->sniffIncludeFunction($node);
            $this->sniffFunctionTemplateFormat($node);
        }

        return $node;
    }

    public function sniffIncludeTag($node)
    {
        $this->addMessage($this::MESSAGE_TYPE_WARNING, 'Include tag is deprecated, prefer the include() function', $node);
    }

    public function sniffIncludeFunction($node)
    {
        $arguments = $node->getNode('arguments');
        if (0 === count($arguments)) {
            $this->addMessage(
                $this::MESSAGE_TYPE_ERROR,
                'Missing template (first argument) in include function call()',
                $node
            );
        } elseif (false == $arguments->getNode(0)->getAttribute('value')) {
            $this->addMessage(
                $this::MESSAGE_TYPE_ERROR,
                sprintf(
                    'Invalid template (first argument, found "%s") in include function call()',
                    $this->stringifyValue($arguments->getNode(0)->getAttribute('value'))
                ),
                $node
            );
        }
    }

    public function sniffTagTemplateFormat($node)
    {
        if (false === strpos($node->getNode('expr')->getAttribute('value'), '@')) {
            $this->addMessage(
                $this::MESSAGE_TYPE_WARNING,
                'Prefer to use template notation with "@" in include tag',
                $node
            );
        }
    }

    public function sniffFunctionTemplateFormat($node)
    {
        $arguments = $node->getNode('arguments');
        if (count($arguments) && $arguments->getNode(0)->getAttribute('value')) {
            if (false === strpos($arguments->getNode(0)->getAttribute('value'), '@')) {
                $this->addMessage(
                    $this::MESSAGE_TYPE_WARNING,
                    'Prefer to use template notation with "@" in include function call()',
                    $node
                );
            }
        }
    }
}
