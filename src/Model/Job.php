<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model;

class Job
{
    /**
     * @param non-empty-string $label
     * @param non-empty-string $token
     */
    public function __construct(
        public readonly string $label,
        public readonly string $token,
        public readonly JobState $state,
    ) {
    }
}
