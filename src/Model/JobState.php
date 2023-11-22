<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model;

readonly class JobState
{
    /**
     * @param non-empty-string  $state
     * @param ?non-empty-string $endState
     */
    public function __construct(
        public string $state,
        public ?string $endState,
    ) {
    }
}
