<?php

/*
 * This file is part of twig-lint.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    public function getSniffs()
    {
        return $this->nodeVisitor->getSniffs();
    }

    public function removeSniff($sniff)
    {
        $this->nodeVisitor->removeSniff($sniff);

        return $this;
    }
}
