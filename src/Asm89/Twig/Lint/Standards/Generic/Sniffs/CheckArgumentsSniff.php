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
 * Generic sniff for checking arguments in calls.
 */
class CheckArgumentsSniff extends AbstractPostParserSniff
{
    /**
     * {@inheritdoc}
     */
    public function process(\Twig_Node $node, \Twig_Environment $env)
    {
        foreach ($this->options['nodes'] as $search) {
            if (!$this->isNodeMatching($node, $search['type'], isset($search['name']) ? $search['name'] : null)) {
                continue;
            }

            $arguments = $node->getNode('arguments');
            if (count($arguments) < $search['min']) {
                $this->addMessage(
                    $this::MESSAGE_TYPE_ERROR,
                    sprintf(
                        isset($search['message']) ? $search['message'] : 'Call to %s %s() requires at least %d parameters; only %d found',
                        $search['type'],
                        isset($search['name']) ? $search['name'] : '',
                        $search['min'],
                        count($arguments)
                    ),
                    $node
                );
            }
        }
    }
}
