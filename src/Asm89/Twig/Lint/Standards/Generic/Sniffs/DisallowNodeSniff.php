<?php

/*
 * This file is part of twig-lint.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\Lint\Standards\Generic\Sniffs;

use Asm89\Twig\Lint\Sniffs\AbstractPostParserSniff;

/**
 * Generic sniff to find and disallow certain type of functions, filters or tags.
 *
 * Use options to set what this sniff is looking for.
 */
class DisallowNodeSniff extends AbstractPostParserSniff
{
    /**
     * {@inheritdoc}
     */
    public function process(\Twig_Node $node, \Twig_Environment $env)
    {
        if (!isset($this->options['nodes']) || empty($this->options['nodes'])) {
            return;
        }

        foreach ($this->options['nodes'] as $search) {
            $name = isset($search['name']) ? $search['name'] : null;

            if ($this->isNodeMatching($node, $search['type'], $name)) {
                $this->addMessage(
                    $this::MESSAGE_TYPE_WARNING,
                    sprintf(
                        isset($search['message']) ? $search['message'] : 'Call to %s %s() must be removed',
                        $search['type'],
                        $name ?: ''
                    ),
                    $node
                );
            }
        }

        return $node;
    }
}
