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

use Asm89\Twig\Lint\Sniffs\PostParserSniffInterface;
use Asm89\Twig\Lint\Sniffs\PreParserSniffInterface;
use Asm89\Twig\Lint\Sniffs\SniffInterface;

/**
 * Set of rules to be used by TwigCS and contains all sniffs (pre or post).
 *
 * @author Hussard <adrien.ricartnoblet@gmail.com>
 */
class Ruleset
{
    protected $sniffs;

    public function __construct()
    {
        $this->sniffs = [];
    }

    public function getSniffs($types = null)
    {
        if (null === $types) {
            $types = array_values(SniffInterface::TYPE);
        }

        if (null !== $types && !is_array($types)) {
            $types = [$types];
        }

        return array_filter($this->sniffs, function($sniff) use ($types) {
            return in_array($sniff->getType(), $types);
        });
    }

    public function addPreParserSniff(PreParserSniffInterface $sniff)
    {
        $this->sniffs[get_class($sniff)] = $sniff;

        return $this;
    }

    public function addPostParserSniff(PostParserSniffInterface $sniff)
    {
        $this->sniffs[get_class($sniff)] = $sniff;

        return $this;
    }

    public function addSniff(SniffInterface $sniff)
    {
        if (SniffInterface::TYPE['PRE_PARSER'] === $sniff->getType()) {
            // Store this type of sniff locally.
            $this->addPreParserSniff($sniff);

            return $this;
        }

        if (SniffInterface::TYPE['POST_PARSER'] === $sniff->getType()) {
            // Store this type of sniff locally.
            $this->addPostParserSniff($sniff);

            return $this;
        }

        throw new \Exception('Unknown type of sniff "' . $sniff->getType() . '", expected one of: "' . implode(', ', array_values(SniffInterface::TYPE)) . "'");
    }

    public function removeSniff($sniffClass)
    {
        if (isset($this->sniffs[$sniffClass])) {
            unset($this->sniffs[$sniffClass]);
        }

        return $this;
    }
}
