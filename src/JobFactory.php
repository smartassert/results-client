<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient;

use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\ResultsClient\Model\CreatedJob;
use SmartAssert\ResultsClient\Model\CreatedJobInterface;
use SmartAssert\ResultsClient\Model\Job;
use SmartAssert\ResultsClient\Model\JobInterface;
use SmartAssert\ResultsClient\Model\JobState;
use SmartAssert\ResultsClient\Model\MetaState;

readonly class JobFactory
{
    /**
     * @param array<mixed> $data
     */
    public function createForNewJob(array $data): ?CreatedJobInterface
    {
        $job = $this->createForExistingJob($data);
        if (null === $job) {
            return null;
        }

        $responseDataInspector = new ArrayInspector($data);

        $eventNotifyData = $responseDataInspector->getArray('event_notify');
        $eventNotifyDataInspector = new ArrayInspector($eventNotifyData);

        $eventNotifyUrl = $eventNotifyDataInspector->getNonEmptyString('url');
        $eventNotifyToken = $eventNotifyDataInspector->getNonEmptyString('token');

        if (null === $eventNotifyUrl || null === $eventNotifyToken) {
            return null;
        }

        return new CreatedJob($job, $eventNotifyUrl, $eventNotifyToken);
    }

    /**
     * @param array<mixed> $data
     */
    public function createForExistingJob(array $data): ?JobInterface
    {
        $responseDataInspector = new ArrayInspector($data);

        $label = $responseDataInspector->getNonEmptyString('label');
        $state = $responseDataInspector->getNonEmptyString('state');
        $hasEvents = $responseDataInspector->getBoolean('has_events');

        if (null === $label || null === $state || null === $hasEvents) {
            return null;
        }

        $endState = $responseDataInspector->getNonEmptyString('end_state');
        $metaState = $this->getJobMetaState($responseDataInspector);
        $previousStates = $responseDataInspector->getNonEmptyStringArray('previous_states');

        return new Job(
            $label,
            new JobState($state, $endState, $metaState),
            $hasEvents,
            $previousStates,
        );
    }

    private function getJobMetaState(ArrayInspector $inspector): MetaState
    {
        $metaStateInspector = new ArrayInspector(
            $inspector->getArray('meta_state')
        );

        return new MetaState(
            $metaStateInspector->getBoolean('ended') ?? false,
            $metaStateInspector->getBoolean('succeeded') ?? false,
            $metaStateInspector->getBoolean('pending') ?? true,
        );
    }
}
