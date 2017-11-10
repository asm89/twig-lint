<?php

namespace Asm89\Twig\Lint;

class Config
{
    protected $options;

    public function __construct($options)
    {
        $this->options = $options;
    }

    public function get($key)
    {
        if (!isset($this->options[$key])) {
            return null;
        }

        return $this->options[$key];
    }
}
