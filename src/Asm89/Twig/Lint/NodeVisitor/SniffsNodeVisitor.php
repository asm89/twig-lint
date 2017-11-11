<?php

/*
 * This file is part of twig-lint.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    public function removeSniff(PostParserSniffInterface $toBeRemovedSniff)
    {
        foreach ($this->sniffs as $index => $sniff) {
            if ($toBeRemovedSniff === $sniff) {
                unset($this->sniffs[$index]);
            }
        }
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
