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

    public function isNodeMatching($node, $type, $name = null)
    {
        $typeToClass = [
            'filter' => function ($node) use ($type, $name) {
                return $node instanceof \Twig_Node_Expression_Filter && $name === $node->getNode($type)->getAttribute('value');
            },
            'function' => function ($node) use ($type, $name) {
                return $node instanceof \Twig_Node_Expression_Function && $name === $node->getAttribute('name');
            },
            'include' => function ($node) {
                return $node instanceof \Twig_Node_Include;
            },
        ];

        return $typeToClass[$type]($node);
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
