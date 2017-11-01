<?php

namespace Asm89\Twig\Lint\Sniffs;

abstract class AbstractSniff extends \Twig_BaseNodeVisitor implements SniffInterface
{
    protected $enabled;

    protected $messages;

    public function __construct($enabled = false)
    {
        $this->enabled = $enabled;
        $this->messages = [];
    }

    /**
     * {@inheritdoc}
     */
    protected function doEnterNode(\Twig_Node $node, \Twig_Environment $env)
    {
        if (!$this->enabled) {
            return $node;
        }

        return $this->process($node, $env);
    }

    /**
     * {@inheritdoc}
     */
    protected function doLeaveNode(\Twig_Node $node, \Twig_Environment $env)
    {
        return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return 0;
    }

    abstract public function process(\Twig_Node $node, \Twig_Environment $env);

    public function enable()
    {
        $this->enabled = true;
        $this->messages = array();
    }

    public function disable()
    {
        $this->enabled = false;
        $this->messages = array();
    }


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
