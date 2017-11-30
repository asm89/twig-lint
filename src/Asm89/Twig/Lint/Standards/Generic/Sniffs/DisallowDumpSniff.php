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
 * Detects and complain about use of `{{ dump() }}` or `{% dump() %}`.
 *
 * Calls to the dump function would not appear on production but keeping calls to
 * debug functions is never a good thing.
 */
class DisallowDumpSniff extends DisallowNodeSniff
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->options['nodes'] = array(
            array(
                'message' => 'Call to debug %s %s() must be removed',
                'name'    => 'dump',
                'type'    => 'function',
            ),
            array(
                'message' => 'Call to debug %s %s() must be removed',
                'name'    => 'dump',
                'type'    => 'tag',
            ),
        );
    }
}
