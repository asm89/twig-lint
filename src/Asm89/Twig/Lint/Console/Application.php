<?php

/*
 * This file is part of twig-lint.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\Lint\Console;

use Asm89\Twig\Lint\Command\LintCommand;
use Symfony\Component\Console\Application as BaseApplication;

/**
 * The console application that handles the commands.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class Application extends BaseApplication
{
    /**
     * {@inheritDoc}
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new LintCommand();

        return $commands;
    }
}
