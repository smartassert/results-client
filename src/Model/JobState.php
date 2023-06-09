<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model;

class JobState
{
    /**
     * @param non-empty-string  $state
     * @param ?non-empty-string $endState
     */
    public function __construct(
        public readonly string $state,
        public readonly ?string $endState,
    ) {
    }
}
