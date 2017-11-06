<?php

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
        $this->getReport()->addMessage($this::MESSAGE_TYPE_WARNING, 'Include tag is deprecated, prefer the include() function', $node->getLine());
    }

    public function sniffIncludeFunction($node)
    {
        $arguments = $node->getNode('arguments');
        if (0 === count($arguments)) {
            $this->getReport()->addMessage($this::MESSAGE_TYPE_ERROR, 'Missing template (first argument) in include function call()', $node->getLine());
        } elseif (false == $arguments->getNode(0)->getAttribute('value')) {
            $this->getReport()->addMessage($this::MESSAGE_TYPE_ERROR, 'Invalid template (first argument, found "' . ($this->stringifyValue($arguments->getNode(0)->getAttribute('value'))) . '") in include function call()', $node->getLine());
        }
    }


    public function sniffTagTemplateFormat($node)
    {
        if (false === strpos($node->getNode('expr')->getAttribute('value'), '@')) {
            $this->getReport()->addMessage($this::MESSAGE_TYPE_WARNING, 'Prefer to use template notation with "@" in include tag', $node->getLine());
        }
    }

    public function sniffFunctionTemplateFormat($node)
    {
        $arguments = $node->getNode('arguments');
        if (count($arguments) && $arguments->getNode(0)->getAttribute('value')) {
            if (false === strpos($arguments->getNode(0)->getAttribute('value'), '@')) {
                $this->getReport()->addMessage($this::MESSAGE_TYPE_WARNING, 'Prefer to use template notation with "@" in include function call()', $node->getLine());
            }
        }
    }
}
