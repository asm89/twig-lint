<?php

namespace Asm89\Twig\Lint\NodeVisitor;

use Asm89\Twig\Lint\Sniffs\PostParserSniffInterface;

class SniffsNodeVisitor extends \Twig_BaseNodeVisitor
{
    protected $sniffs;

    protected $enabled;

    public function __construct($sniffs = [], $enabled = true)
    {
        $this->sniffs = $sniffs;
        $this->enabled = $enabled;
    }

    /**
     * {@inheritdoc}
     */
    protected function doEnterNode(\Twig_Node $node, \Twig_Environment $env)
    {
        if (!$this->enabled) {
            return $node;
        }

        foreach ($this->getSniffs() as $sniff) {
            $sniff->process($node, $env);
        }

        return $node;
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

    public function addSniff(PostParserSniffInterface $sniff)
    {
        $this->sniffs[] = $sniff;
    }

    public function getSniffs()
    {
        return $this->sniffs;
    }

    public function enable()
    {
        $this->enabled = true;
    }

    public function disable()
    {
        $this->enabled = false;
    }
}
