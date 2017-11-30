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

use Aura\Autoload\Loader as Autoloader;
use Symfony\Component\Finder\Finder;

/**
 * TwigCS configuration data.
 *
 * @author Hussard <adrien.ricartnoblet@gmail.com>
 */
class Config
{
    /**
     * Default configuration.
     *
     * @var array
     */
    public static $defaultConfig = array(
        'exclude'          => array(),
        'pattern'          => '*.twig',
        'paths'            => array(),
        'standardPaths'    => array(),
        'stub'             => array(),
        'workingDirectory' => '',
    );

    /**
     * Current configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * Autoloader for sniffs.
     *
     * @var Autoloader\Loader
     */
    protected $autoloader;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $args = func_get_args();

        $this->config = $this::$defaultConfig;
        foreach ($args as $arg) {
            $this->config = array_merge($this->config, $arg);
        }

        $this->autoloader = new Autoloader();
        $this->autoloader->register();

        $standardPaths = $this->get('standardPaths');
        if ($standardPaths) {
            $this->autoloader->setPrefixes($standardPaths);
        }
    }

    /**
     * Find all files to process, based on a file or directory and exclude patterns.
     *
     * @param  string $fileOrDirectory a file or a directory.
     * @param  array  $exclude         array of exclude patterns.
     *
     * @return array
     */
    public function findFiles($fileOrDirectory = null, $exclude = null)
    {
        $files = array();

        if (is_file($fileOrDirectory)) {
            // Early return with the given file. Should we exclude things to here?
            return array($fileOrDirectory);
        }

        if (is_dir($fileOrDirectory)) {
            $fileOrDirectory = array($fileOrDirectory);
        }

        if (!$fileOrDirectory) {
            $fileOrDirectory = $this->get('paths');
            $exclude = $this->get('exclude');
        }

        // Build the finder.
        $files = Finder::create()
            ->in($this->get('workingDirectory'))
            ->name($this->config['pattern'])
            ->files()
        ;

        // Include all matching paths.
        foreach ($fileOrDirectory as $path) {
            $files->path($path);
        }

        // Exclude all matching paths.
        if ($exclude) {
            $files->exclude($exclude);
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
