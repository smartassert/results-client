<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient;

use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\ResultsClient\Model\Event\Event;
use SmartAssert\ResultsClient\Model\Event\JobEvent;
use SmartAssert\ResultsClient\Model\Event\ResourceReference;
use SmartAssert\ResultsClient\Model\Event\ResourceReferenceCollection;

class ObjectFactory
{
    public function createJobEvent(ArrayInspector $data): ?JobEvent
    {
        $jobLabel = $data->getNonEmptyString('job');
        $event = $this->createEvent($data);

        return null === $jobLabel || null === $event ? null : new JobEvent($jobLabel, $event);
    }

    public function createEvent(ArrayInspector $data): ?Event
    {
        $sequenceNumber = $data->getPositiveInteger('sequence_number');
        $type = $data->getNonEmptyString('type');
        $resourceReference = $this->createResourceReference($data);
        $body = $data->getArray('body');
        $relatedReferencesData = $data->getArray('related_references');

        $references = [];
        foreach ($relatedReferencesData as $relatedReferenceData) {
            if (is_array($relatedReferenceData)) {
                $reference = $this->createResourceReference(new ArrayInspector($relatedReferenceData));

                if ($reference instanceof ResourceReference) {
                    $references[] = $reference;
                }
            }
        }

        $relatedReferences = [] === $references ? null : new ResourceReferenceCollection($references);

        return null === $sequenceNumber || null === $type || null === $resourceReference
            ? null
            : new Event($sequenceNumber, $type, $resourceReference, $body, $relatedReferences);
    }

    public function createResourceReference(ArrayInspector $data): ?ResourceReference
    {
        $label = $data->getNonEmptyString('label');
        $reference = $data->getNonEmptyString('reference');

        return null === $label || null === $reference ? null : new ResourceReference($label, $reference);
    }
}
