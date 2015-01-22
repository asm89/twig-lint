<?php

/*
 * This file is part of twig-lint.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

error_reporting(E_ALL | E_STRICT);

if (file_exists($file = __DIR__.'/../vendor/autoload.php')) {
    $autoload = require_once $file;
} else {
    throw new RuntimeException('Install dependencies to run test suite.');
}
