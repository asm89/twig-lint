<?php

namespace Asm89\Twig\Lint\Extension;

use Asm89\Twig\Lint\NodeVisitor\SniffsNodeVisitor;

class SniffsExtension extends \Twig_Extension_Core
{
    protected $nodeVisitor;

    protected $sniffs;

    public function __construct($sniffs = [])
    {
        $this->nodeVisitor = new SniffsNodeVisitor();
        $this->sniffs = $sniffs;
    }

    public function getNodeVisitors()
    {
        return [$this->nodeVisitor];
    }

    public function addSniff($sniff)
    {
        $this->nodeVisitor->addSniff($sniff);

        return $this;
    }

    public function getMessages()
    {
        return $this->nodeVisitor->getMessages();
    }

    // public function getNodeVisitor()
    // {
    //     if (!$this->nodeVisitor) {
    //         $this->nodeVisitor = new SniffsNodeVisitor();
    //     }

    //     return $this->nodeVisitor;
    // }
}
