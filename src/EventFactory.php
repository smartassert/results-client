<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient;

use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\ResultsClient\Model\Event;
use SmartAssert\ResultsClient\Model\EventInterface;
use SmartAssert\ResultsClient\Model\JobEventInterface;
use SmartAssert\ResultsClient\Model\ResourceReferenceCollection;
use SmartAssert\ResultsClient\Model\ResourceReferenceInterface;

readonly class EventFactory
{
    public function __construct(
        private ResourceReferenceFactory $resourceReferenceFactory,
    ) {}

    public function create(ArrayInspector $data): ?EventInterface
    {
        $sequenceNumber = $data->getPositiveInteger('sequence_number');
        $type = $data->getNonEmptyString('type');
        $resourceReference = $this->resourceReferenceFactory->create($data);
        $body = $data->getArray('body');
        $relatedReferencesData = $data->getArray('related_references');

        $references = [];
        foreach ($relatedReferencesData as $relatedReferenceData) {
            if (is_array($relatedReferenceData)) {
                $reference = $this->resourceReferenceFactory->create(new ArrayInspector($relatedReferenceData));

                if ($reference instanceof ResourceReferenceInterface) {
                    $references[] = $reference;
                }
            }
        }

        if (null === $sequenceNumber || null === $type || null === $resourceReference) {
            return null;
        }

        $event = new Event($sequenceNumber, $type, $resourceReference, $body);

        if ([] !== $references) {
            $event = $event->withRelatedReferences(new ResourceReferenceCollection($references));
        }

        $job = $data->getNonEmptyString('job');
        if (null !== $job && $event instanceof JobEventInterface) {
            $event = $event->withJob($job);
        }

        return $event;
    }
}
