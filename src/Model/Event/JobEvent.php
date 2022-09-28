<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model\Event;

class JobEvent
{
    public function __construct(
        public readonly string $jobLabel,
        public readonly Event $event,
    ) {
    }
}
