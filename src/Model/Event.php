<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model;

class Event implements EventInterface
{
    private ResourceReferenceCollectionInterface $relatedReferences;

    /**
     * @param non-empty-string $job
     * @param positive-int     $sequenceNumber
     * @param non-empty-string $type
     * @param array<mixed>     $body
     */
    public function __construct(
        public readonly string $job,
        public readonly int $sequenceNumber,
        public readonly string $type,
        public readonly ResourceReferenceInterface $resourceReference,
        public readonly array $body,
    ) {}

    public function withRelatedReferences(ResourceReferenceCollectionInterface $relatedReferences): EventInterface
    {
        $event = new Event($this->job, $this->sequenceNumber, $this->type, $this->resourceReference, $this->body);
        $event->relatedReferences = $relatedReferences;

        return $event;
    }

    public function toArray(): array
    {
        $data = array_merge(
            [
                'job' => $this->job,
                'sequence_number' => $this->sequenceNumber,
                'type' => $this->type,
                'body' => $this->body,
            ],
            $this->resourceReference->toArray(),
        );

        if (isset($this->relatedReferences)) {
            $data['related_references'] = $this->relatedReferences->toArray();
        }

        return $data;
    }
}
