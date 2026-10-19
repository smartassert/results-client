<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model;

readonly class CreatedJob implements CreatedJobInterface
{
    public function __construct(
        private JobInterface $job,
        private string $eventNotifyUrl,
        private string $eventNotifyToken,
    ) {}

    public function getEventNotifyToken(): string
    {
        return $this->eventNotifyToken;
    }

    public function getEventNotifyUrl(): string
    {
        return $this->eventNotifyUrl;
    }

    public function getLabel(): string
    {
        return $this->job->getLabel();
    }

    public function getState(): JobState
    {
        return $this->job->getState();
    }

    public function hasEvents(): bool
    {
        return $this->job->hasEvents();
    }

    public function getPreviousStates(): array
    {
        return $this->job->getPreviousStates();
    }
}
