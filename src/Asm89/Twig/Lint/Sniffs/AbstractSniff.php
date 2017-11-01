<?php

namespace Asm89\Twig\Lint\Sniffs;

abstract class AbstractSniff implements SniffInterface
{
    public function addMessage($messageType, $message, $line, $severity)
    {
        $this->messages[] = [
            $messageType,
            $message,
            $line,
            $severity,
        ];

        return $this;
    }

    public function getMessages($messageType = null)
    {
        return $this->messages;
    }

    public function isNodeMatching($node, $type, $name = null)
    {
        $typeToClass = [
            'filter' => function ($node) {
                return $node instanceof \Twig_Node_Expression_Filter;
            },
        ];

        return $typeToClass[$type]($node) && $name === $node->getNode($type)->getAttribute('value');
    }

    public function stringifyNode($node) {
        // dump($node);
        $stringValue = '';

        if ($node instanceof \Twig_Node_Expression_GetAttr) {
            return $node->getNode('node')->getAttribute('name') . '.' . $this->stringifyNode($node->getNode('attribute'));
        } elseif ($node instanceof \Twig_Node_Expression_Binary_Concat) {
            return $this->stringifyNode($node->getNode('left')) . ' ~ ' . $this->stringifyNode($node->getNode('right'));
        } elseif($node instanceof \Twig_Node_Expression_Constant) {
            return $node->getAttribute('value');
        }

        return $stringValue;
    }
}
