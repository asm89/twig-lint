<?php

namespace Asm89\Twig\Lint\Sniffs;

abstract class AbstractPostParserSniff implements PostParserSniffInterface
{
    protected $report;

    public function __construct()
    {
        $this->messages = [];
        $this->report = null;
    }

    public function enable($report)
    {
        $this->report = $report;

        return $this;
    }

    public function disable()
    {
        $this->report = null;

        return $this;
    }

    public function getReport()
    {
        if (null === $this->report) {
            throw new \Exception('Sniff is disabled!');
        }

        return $this->report;
    }

    public function addMessage($messageType, $message, $node, $severity = null)
    {
        $this->getReport()->addMessage($messageType, $message, $this->getTemplateLine($node), null, $this->getTemplateName($node), $severity);

        return $this;
    }

    public function getTemplateLine($node)
    {
        if (method_exists($node, 'getTemplateLine')) {
            return $node->getTemplateLine();
        }

        if (method_exists($node, 'getLine')) {
            return $node->getLine();
        }

        return '';
    }

    public function getTemplateName($node)
    {
        if (method_exists($node, 'getTemplateName')) {
            return $node->getTemplateName();
        }

        if (method_exists($node, 'getFilename')) {
            return $node->getFilename();
        }

        if ($node->hasAttribute('filename')) {
            return $node->getAttribute('filename');
        }

        return '';
    }

    public function isNodeMatching($node, $type, $name = null)
    {
        $typeToClass = [
            'filter' => function ($node, $type, $name) {
                return $node instanceof \Twig_Node_Expression_Filter && $name === $node->getNode($type)->getAttribute('value');
            },
            'function' => function ($node, $type, $name) {
                return $node instanceof \Twig_Node_Expression_Function && $name === $node->getAttribute('name');
            },
            'include' => function ($node, $type, $name) {
                return $node instanceof \Twig_Node_Include;
            },
            'tag' => function ($node, $type, $name) {
                return $node->getNodeTag() === $name && $node->hasAttribute('name') && $name === $node->getAttribute('name');
            },
        ];

        if (!isset($typeToClass[$type])) {
            return false;
        }

        return $typeToClass[$type]($node, $type, $name);
    }

    public function stringifyValue($value)
    {
        if (null === $value) {
            return 'null';
        }

        if (is_bool($value)) {
            return ($value) ? 'true': 'false';
        }

        return (string) $value;
    }

    public function stringifyNode($node) {
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
