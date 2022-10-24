<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient;

use SmartAssert\ResultsClient\Model\Event\Event;
use SmartAssert\ResultsClient\Model\Event\JobEvent;
use SmartAssert\ResultsClient\Model\Event\ResourceReference;
use SmartAssert\ResultsClient\Model\Event\ResourceReferenceCollection;
use SmartAssert\ResultsClient\Model\Job;
use SmartAssert\ServiceClient\ArrayAccessor;

class ObjectFactory
{
    public function __construct(
        private readonly ArrayAccessor $arrayAccessor,
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public function createJobFromArray(array $data): ?Job
    {
        $label = $this->arrayAccessor->getNonEmptyString('label', $data);
        $token = $this->arrayAccessor->getNonEmptyString('token', $data);

        return null === $label || null === $token ? null : new Job($label, $token);
    }

    /**
     * @param array<mixed> $data
     */
    public function createJobEventFromArray(array $data): ?JobEvent
    {
        $jobLabel = $this->arrayAccessor->getNonEmptyString('job', $data);
        $event = $this->createEventFromArray($data);

        return null === $jobLabel || null === $event ? null : new JobEvent($jobLabel, $event);
    }

    /**
     * @param array<mixed> $data
     */
    public function createEventFromArray(array $data): ?Event
    {
        $sequenceNumber = $this->arrayAccessor->getPositiveInteger('sequence_number', $data);
        $type = $this->arrayAccessor->getNonEmptyString('type', $data);
        $resourceReference = $this->createResourceReferenceFromArray($data);
        $body = $data['body'] ?? [];
        $body = is_array($body) ? $body : [];
        $relatedReferencesData = $data['related_references'] ?? null;
        $relatedReferencesData = is_array($relatedReferencesData) ? $relatedReferencesData : null;

        $relatedReferences = null;
        if (is_array($relatedReferencesData)) {
            $references = [];

            foreach ($relatedReferencesData as $relatedReferenceData) {
                if (is_array($relatedReferenceData)) {
                    $reference = $this->createResourceReferenceFromArray($relatedReferenceData);

                    if ($reference instanceof ResourceReference) {
                        $references[] = $reference;
                    }
                }
            }

            $relatedReferences = new ResourceReferenceCollection($references);
        }

        return null === $sequenceNumber || null === $type || null === $resourceReference
            ? null
            : new Event($sequenceNumber, $type, $resourceReference, $body, $relatedReferences);
    }

    /**
     * @param array<mixed> $data
     */
    public function createResourceReferenceFromArray(array $data): ?ResourceReference
    {
        $label = $this->arrayAccessor->getNonEmptyString('label', $data);
        $reference = $this->arrayAccessor->getNonEmptyString('reference', $data);

        return null === $label || null === $reference ? null : new ResourceReference($label, $reference);
    }
}
