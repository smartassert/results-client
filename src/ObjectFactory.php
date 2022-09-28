<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient;

use SmartAssert\ResultsClient\Model\Event\Event;
use SmartAssert\ResultsClient\Model\Event\JobEvent;
use SmartAssert\ResultsClient\Model\Event\ResourceReference;
use SmartAssert\ResultsClient\Model\Event\ResourceReferenceCollection;
use SmartAssert\ResultsClient\Model\Job;

class ObjectFactory
{
    /**
     * @param array<mixed> $data
     */
    public function createJobFromArray(array $data): ?Job
    {
        $label = $this->getNonEmptyStringValue('label', $data);
        $token = $this->getNonEmptyStringValue('token', $data);

        return null === $label || null === $token ? null : new Job($label, $token);
    }

    /**
     * @param array<mixed> $data
     */
    public function createJobEventFromArray(array $data): ?JobEvent
    {
        $jobLabel = $this->getNonEmptyStringValue('job', $data);
        $event = $this->createEventFromArray($data);

        return null === $jobLabel || null === $event ? null : new JobEvent($jobLabel, $event);
    }

    /**
     * @param array<mixed> $data
     */
    public function createEventFromArray(array $data): ?Event
    {
        $sequenceNumber = $this->getPositiveIntegerValue('sequence_number', $data);
        $type = $this->getNonEmptyStringValue('type', $data);
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
        $label = $this->getNonEmptyStringValue('label', $data);
        $reference = $this->getNonEmptyStringValue('reference', $data);

        return null === $label || null === $reference ? null : new ResourceReference($label, $reference);
    }

    /**
     * @param non-empty-string $key
     * @param array<mixed>     $data
     */
    private function getStringValue(string $key, array $data): ?string
    {
        $value = $data[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * @param non-empty-string $key
     * @param array<mixed>     $data
     *
     * @return null|non-empty-string
     */
    private function getNonEmptyStringValue(string $key, array $data): ?string
    {
        $value = trim((string) $this->getStringValue($key, $data));

        return '' === $value ? null : $value;
    }

    /**
     * @param array<mixed> $data
     *
     * @return null|positive-int
     */
    private function getPositiveIntegerValue(string $key, array $data): ?int
    {
        $value = $data[$key] ?? null;

        return is_int($value) && $value > 0 ? $value : null;
    }
}
