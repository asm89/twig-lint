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
 * Detects use of `{% include %}` instead of `{{ include() }}`
 *
 * @see  https://github.com/twigphp/Twig/issues/1899
 */
class DisallowIncludeTagSniff extends DisallowNodeSniff
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->options['nodes'] = array(
            array(
                'message' => 'Include tag is deprecated; use the include() function instead',
                'name'    => 'include',
                'type'    => 'tag',
            ),
        );
    }
}
