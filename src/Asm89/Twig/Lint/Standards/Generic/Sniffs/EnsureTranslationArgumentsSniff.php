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
 * Ensure that `|trans()` and `|transchoice()` have all their arguments.
 */
class EnsureTranslationArgumentsSniff extends CheckArgumentsSniff
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->options['nodes'] = array(
            array(
                'message' => 'Call to %s %s() requires parameter "domain"; expected %d parameters, found %d',
                'min'     => 2,
                'name'    => 'trans',
                'type'    => 'filter',
            ),
            array(
                'message' => 'Call to %s %s() requires parameter "lang"; expected %d parameters, found %d',
                'min'     => 3,
                'name'    => 'trans',
                'type'    => 'filter',
            ),
            array(
                'message' => 'Call to %s %s() requires parameter "domain"; expected %d parameters, found %d',
                'min'     => 3,
                'name'    => 'transchoice',
                'type'    => 'filter',
            ),
            array(
                'message' => 'Call to %s %s() requires parameter "lang"; expected %d parameters, found %d',
                'min'     => 4,
                'name'    => 'transchoice',
                'type'    => 'filter',
            ),
        );
    }
}
