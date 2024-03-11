<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model;

readonly class Job implements JobInterface
{
    /**
     * @param non-empty-string $label
     * @param non-empty-string $token
     */
    public function __construct(
        private string $label,
        private string $token,
        private JobState $state,
    ) {
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getState(): JobState
    {
        return $this->state;
    }
}
