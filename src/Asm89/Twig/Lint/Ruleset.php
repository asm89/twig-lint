<?php

namespace Asm89\Twig\Lint;

use Asm89\Twig\Lint\Sniffs\PostParserSniffInterface;
use Asm89\Twig\Lint\Sniffs\PreParserSniffInterface;
use Asm89\Twig\Lint\Sniffs\SniffInterface;

class Ruleset
{
    const EVENT = [
        'PRE_PARSER'    => 'lint.pre_parser',
        'POST_PARSER'   => 'lint.post_parser',
    ];

    protected $sniffs;

    public function __construct()
    {
        foreach (self::EVENT as $eventName => $eventSlug) {
            $this->sniffs[$eventSlug] = [];
        }
    }

    public function getSniffs($events = null)
    {
        if (null === $events) {
            $events = array_values($this::EVENT);
        }

        if (null !== $events && !is_array($events)) {
            $events = [$events];
        }

        $sniffs = [];
        foreach ($events as $eventSlug) {
            $sniffs = array_merge($sniffs, $this->sniffs[$eventSlug]);
        }

        return $sniffs;
    }

    public function addPreParserSniff(PreParserSniffInterface $sniff)
    {
        $this->sniffs[self::EVENT['PRE_PARSER']][] = $sniff;

        return $this;
    }

    public function addPostParserSniff(PostParserSniffInterface $sniff)
    {
        $this->sniffs[self::EVENT['POST_PARSER']][] = $sniff;

        return $this;
    }

    public function addSniff($event, SniffInterface $sniff)
    {
        if (self::EVENT['PRE_PARSER'] === $event) {
            // Store this type of sniff locally.
            $this->addPreParserSniff($sniff);

            return $this;
        }

        if (self::EVENT['POST_PARSER'] === $event) {
            // Store this type of sniff locally.
            $this->addPostParserSniff($sniff);

            // // Delegate to twig parser visitor through our sniff twig extension.
            // $this->sniffExtension->addSniff($sniff);

            return $this;
        }

        throw new \Exception('Unknown type of sniff "' . $event . '", expected one of: "' . implode(', ', array_values(self::EVENT)) . "'");
    }
}
