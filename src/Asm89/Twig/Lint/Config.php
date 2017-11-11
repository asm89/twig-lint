<?php

/*
 * This file is part of twig-lint.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\Lint;

use Symfony\Component\Finder\Finder;

class Config
{
    public static $defaultConfig = array(
        'filename' => 'twigcs.yml',
        'pattern'  => '*.twig',
    );

    protected $config;

    public function __construct($config = array())
    {
        $this->config = array_merge($this::$defaultConfig, $config);
    }

    public function findFiles($fileOrDirectory, $exclude = null)
    {
        $files = [];
        if (is_file($fileOrDirectory)) {
            $files = [$fileOrDirectory];
        } elseif (is_dir($fileOrDirectory)) {
            $files = Finder::create()->files()->in($fileOrDirectory)->name($this->config['pattern']);
            if (null !== $exclude) {
                $files->filter(
                    // pass in the list of excludes
                    function (\SplFileInfo $file) use ($exclude) {
                        foreach ($exclude as $excludeItem) {
                            if (1 === preg_match('#' . $excludeItem . '#', $file->getRealPath())) {
                                return false;
                            }
                        }
                        return true;
                    }
                );
            }
        }

        return $files;
    }

    /**
     * Get a configuration value for the given $key.
     *
     * @param  string $key
     *
     * @return any
     */
    public function get($key)
    {
        if (!isset($this->config[$key])) {
            throw new \Exception(sprintf('Configuration key "%s" does not exist', $key));
        }

        return $this->config[$key];
    }
}
