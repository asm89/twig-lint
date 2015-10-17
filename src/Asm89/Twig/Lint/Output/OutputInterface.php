<?php

/*
 * This file is part of twig-lint.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\Lint\Output;

use Twig_Error;

/**
 * Simple interface for output formatters.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
interface OutputInterface
{
    /**
     * @param string $template
     * @param string $file
     */
    public function ok($template, $file = null);

    /**
     * @param string     $template
     * @param Twig_Error $error
     * @param string     $file
     */
    public function error($template, Twig_Error $error, $file = null);

    /**
     * @param array $stats Array with statistics about the linting run
     */
    public function summary(array $stats);
}
