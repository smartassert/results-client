<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient;

use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\ResultsClient\Model\Job;
use SmartAssert\ResultsClient\Model\JobState;
use SmartAssert\ResultsClient\Model\MetaState;

readonly class JobFactory
{
    /**
     * @param array<mixed> $data
     */
    public function create(array $data): ?Job
    {
        $responseDataInspector = new ArrayInspector($data);

        $label = $responseDataInspector->getNonEmptyString('label');
        $state = $responseDataInspector->getNonEmptyString('state');
        $eventAddUrl = $responseDataInspector->getNonEmptyString('event_add_url');
        $hasEvents = $responseDataInspector->getBoolean('has_events');

        if (null === $label || null === $state || null === $eventAddUrl || null === $hasEvents) {
            return null;
        }

        $endState = $responseDataInspector->getNonEmptyString('end_state');
        $metaState = $this->getJobMetaState($responseDataInspector);

        $previousStates = $responseDataInspector->getNonEmptyStringArray('previous_states');

        return new Job($label, $eventAddUrl, new JobState($state, $endState, $metaState), $hasEvents, $previousStates);
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
