<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model;

readonly class Job implements JobInterface
{
    /**
     * @param non-empty-string   $label
     * @param non-empty-string[] $previousStates
     */
    public function __construct(
        private string $label,
        private JobState $state,
        private bool $hasEvents,
        private array $previousStates,
    ) {}

    /**
     * @return non-empty-string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    public function getState(): JobState
    {
        return $this->state;
    }

    public function hasEvents(): bool
    {
        return $this->hasEvents;
    }

    /**
     * @return non-empty-string[]
     */
    public function getPreviousStates(): array
    {
        return $this->previousStates;
    }
}
