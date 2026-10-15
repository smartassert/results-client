<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model;

readonly class Job implements JobInterface
{
    /**
     * @param non-empty-string   $label
     * @param ?non-empty-string  $token
     * @param non-empty-string   $authenticator
     * @param non-empty-string[] $previousStates
     */
    public function __construct(
        private string $label,
        private ?string $token,
        private string $authenticator,
        private JobState $state,
        private bool $hasEvents,
        private array $previousStates,
        private ?string $eventNotifyUrl,
    ) {}

    /**
     * @return non-empty-string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return ?non-empty-string
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @return non-empty-string
     */
    public function getAuthenticator(): string
    {
        return $this->authenticator;
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

    public function getEventNotifyUrl(): ?string
    {
        return $this->eventNotifyUrl;
    }
}
