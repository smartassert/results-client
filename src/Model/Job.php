<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model;

class Job
{
    public function __construct(
        public readonly string $label,
        public readonly string $token,
    ) {
    }
}
