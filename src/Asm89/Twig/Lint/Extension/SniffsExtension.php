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
use Asm89\Twig\Lint\Sniffs\PostParserSniffInterface;

/**
 * This extension is responsible of loading the sniffs into the twig environment.
 *
 * This class is only a bridge between the linter and the `SniffsNodeVisitor` that is
 * actually doing the work when Twig parser is compiling a template.
 *
 * @author Hussard <adrien.ricartnoblet@gmail.com>
 */
class SniffsExtension extends \Twig_Extension_Core
{
    /**
     * The actual node visitor.
     *
     * @var SniffsNodeVisitor
     */
    protected $nodeVisitor;

    public function __construct()
    {
        $this->nodeVisitor = new SniffsNodeVisitor();
    }

    /**
     * {@inheritDoc}
     */
    public function getNodeVisitors()
    {
        return array($this->nodeVisitor);
    }

    /**
     * Register a sniff in the node visitor.
     *
     * @param PostParserSniffInterface $sniff
     */
    public function addSniff(PostParserSniffInterface $sniff)
    {
        $this->nodeVisitor->addSniff($sniff);

        return $this;
    }

    /**
     * Remove a sniff from the node visitor.
     *
     * @param  PostParserSniffInterface $sniff
     *
     * @return self
     */
    public function removeSniff(PostParserSniffInterface $sniff)
    {
        $this->nodeVisitor->removeSniff($sniff);

        return $this;
    }
}
