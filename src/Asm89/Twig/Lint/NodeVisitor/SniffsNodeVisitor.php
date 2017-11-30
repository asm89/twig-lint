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

/**
 * Node visitors provide a mechanism for manipulating nodes before a template is
 * compiled down to a PHP class.
 *
 * This class is using that mechanism to execute sniffs (rules) on all the twig
 * node during a template compilation; thanks to `Twig_Parser`.
 *
 * @author Hussard <adrien.ricartnoblet@gmail.com>
 */
class SniffsNodeVisitor extends \Twig_BaseNodeVisitor implements \Twig_NodeVisitorInterface
{
    /**
     * List of sniffs to be executed.
     *
     * @var array
     */
    protected $sniffs;

    /**
     * Is this node visitor enabled?
     *
     * @var bool
     */
    protected $enabled;

    public function __construct()
    {
        $this->sniffs = array();
        $this->enabled = true;
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

    /**
     * Register a sniff to be executed.
     *
     * @param PostParserSniffInterface $sniff
     */
    public function addSniff(PostParserSniffInterface $sniff)
    {
        $this->sniffs[] = $sniff;
    }

    /**
     * Remove a sniff from the node visitor.
     *
     * @param  PostParserSniffInterface $sniff
     *
     * @return self
     */
    public function removeSniff(PostParserSniffInterface $toBeRemovedSniff)
    {
        foreach ($this->sniffs as $index => $sniff) {
            if ($toBeRemovedSniff === $sniff) {
                unset($this->sniffs[$index]);
            }
        }

        return $this;
    }

    /**
     * Get all registered sniffs.
     *
     * @return array
     */
    public function getSniffs()
    {
        return $this->sniffs;
    }

    /**
     * Enable this node visitor.
     *
     * @return self
     */
    public function enable()
    {
        $this->enabled = true;

        return $this;
    }

    /**
     * Disable this node visitor.
     *
     * @return self
     */
    public function disable()
    {
        $this->enabled = false;
    }
}
