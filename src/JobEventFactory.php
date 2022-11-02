<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient;

use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\ResultsClient\Model\Event\JobEvent;

class JobEventFactory
{
    public function __construct(
        private readonly EventFactory $eventFactory,
    ) {
    }

    public function create(ArrayInspector $data): ?JobEvent
    {
        $jobLabel = $data->getNonEmptyString('job');
        $event = $this->eventFactory->create($data);

        return null === $jobLabel || null === $event ? null : new JobEvent($jobLabel, $event);
    }
}
