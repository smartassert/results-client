<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient;

use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\ResultsClient\Model\Event\Event;
use SmartAssert\ResultsClient\Model\Event\EventInterface;
use SmartAssert\ResultsClient\Model\Event\ResourceReference;
use SmartAssert\ResultsClient\Model\Event\ResourceReferenceCollection;

class EventFactory
{
    public function __construct(
        private readonly ResourceReferenceFactory $resourceReferenceFactory,
    ) {
    }

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
}
