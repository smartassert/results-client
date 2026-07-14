<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model;

readonly class Job
{
    /**
     * @param non-empty-string   $label
     * @param non-empty-string   $authenticator
     * @param non-empty-string[] $previousStates
     */
    public function __construct(
        public string $label,
        public string $authenticator,
        public JobState $state,
        public bool $hasEvents,
        public array $previousStates,
    ) {}
}
