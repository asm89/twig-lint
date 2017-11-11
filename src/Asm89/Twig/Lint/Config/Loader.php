<?php

/*
 * This file is part of twig-lint.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\Lint\Config;

use Symfony\Component\Config\Loader\FileLoader as BaseFileLoader;
use Symfony\Component\Yaml\Parser;

/**
 * Load a twigcs.yml file and validate its content.
 *
 * @author Hussard <adrien.ricartnoblet@gmail.com>
 */
class Loader extends BaseFileLoader
{
    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        if (!stream_is_local($resource)) {
            throw new \InvalidArgumentException(sprintf('This is not a local file "%s".', $resource));
        }

        // Try to find the path to the resource.
        try {
            $path = $this->locator->locate($resource);
        } catch (\InvalidArgumentException $e) {
            throw new \Exception(sprintf('File "%s" not found.', $resource), null, $e);
        }

        // Load and parse the resource.
        $content = $this->loadResource($path);
        if (!$content) {
            // Empty resource, always return an array.
            $content = [];
        }

        return $this->validate($content, $path);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        $validTypes = ['yaml', 'yml'];
        return is_string($resource) && in_array(pathinfo($resource, PATHINFO_EXTENSION), $validTypes, true) && (!$type || in_array($type, $validTypes));
    }

    /**
     * Load a resource and returns the parsed content.
     *
     * @param string $resource
     *
     * @return array
     *
     * @throws InvalidResourceException If stream content has an invalid format.
     */
    public function loadResource($file)
    {
        $parser = new Parser();
        try {
            return $parser->parse(file_get_contents($file));
        } catch (\Exception $e) {
            throw new \Exception(sprintf('Error parsing YAML, invalid file "%s"', $file), 0, $e);
        }
    }

    /**
     * Validates the content $content parsed from $file.
     *
     * This default method, returns the content, as is, without any form of
     * validation.
     *
     * @param mixed  $content
     * @param string $file
     *
     * @return array
     */
    protected function validate($content, $file)
    {
        if (!isset($content['ruleset'])) {
            throw new \Exception(sprintf('Missing "%s" key', 'ruleset'));
        }

        foreach ($content['ruleset'] as $rule) {
            if (!isset($rule['class'])) {
                throw new \Exception(sprintf('Missing "%s" key', 'class'));
            }
        }

        return $content;
    }
}
