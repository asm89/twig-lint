<?php

namespace Asm89\Twig\Lint\Sniffs;

interface SniffInterface
{
    const MESSAGE_TYPE_ALL       = 0;
    const MESSAGE_TYPE_WARNING   = 1;
    const MESSAGE_TYPE_ERROR     = 2;

    const SEVERITY_DEFAULT = 5;

    public function enable($report);

    public function disable();

    public function getReport();
}
