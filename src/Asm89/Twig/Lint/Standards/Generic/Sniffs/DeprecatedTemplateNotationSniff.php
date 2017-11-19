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

/**
 * Locates the template notation in include, extends and embed tags.
 *
 * Twig template notation has changed:
 *   - `AcmeBundle:Some/Controller:index.html.twig` (Symfony 2.8 or earlier, deprecated)
 *   - `@Acme/Some/Controller:index.html.twig` (Symfony 3+)
 */
class DeprecatedTemplateNotationSniff extends AbstractPostParserSniff
{
    /**
     * {@inheritdoc}
     */
    public function process(\Twig_Node $node, \Twig_Environment $env)
    {

        if ($this->isNodeMatching($node, 'tag', 'include')) {
            $this->processIncludeTag($node);
        } elseif ($this->isNodeMatching($node, 'function', 'include')) {
            $this->processIncludeFunction($node);
        } elseif ($node instanceof \Twig_Node_Module && $node->hasNode('parent')) {
            $this->processExtendsTag($node);
        }

        return $node;
    }

    public function processIncludeTag($node)
    {
        $exprNode = $node->getNode('expr');
        if ($exprNode instanceof \Twig_Node_Expression_Constant) {
            // Only works with constant expression and not concatenation or function calls.
            $this->processTemplateFormat($exprNode->getAttribute('value'), $node);
        }
    }

    public function processIncludeFunction($node)
    {
        $arguments = $node->getNode('arguments');
        if (0 < $arguments->count() && $arguments->getNode(0) instanceof \Twig_Node_Expression_Constant) {
            $this->processTemplateFormat($arguments->getNode(0)->getAttribute('value'), $node);
        }
    }

    public function processExtendsTag($node)
    {
        $parent = $node->getNode('parent');
        if ('__parent__' !== $parent->getAttribute('value')) {
            $this->processTemplateFormat($parent->getAttribute('value'), $node);
        }
    }

    public function processTemplateFormat($templateName, $node)
    {
        $check = strpos($templateName, '@');
        if (false === $check || 0 !== $check) {
            $this->addMessage(
                $this::MESSAGE_TYPE_WARNING,
                sprintf(
                    'Deprecated template notation "%s"; use Symfony 3+ template notation with "@" instead',
                    $templateName
                ),
                $node
            );
        }
    }
}
