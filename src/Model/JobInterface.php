<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model;

interface JobInterface
{
    /**
     * @return non-empty-string
     */
    public function getLabel(): string;

    /**
     * @return ?non-empty-string
     */
    public function getToken(): ?string;

    /**
     * @return non-empty-string
     */
    public function getAuthenticator(): string;

    public function getState(): JobState;

    public function hasEvents(): bool;

    /**
     * @return non-empty-string[]
     */
    public function getPreviousStates(): array;

    public function getEventNotifyUrl(): ?string;
}
