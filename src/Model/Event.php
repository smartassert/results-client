<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model;

/**
 * @phpstan-import-type SerializedEvent from EventInterface
 */
class Event implements EventInterface
{
    /**
     * @var non-empty-string
     */
    private string $job;

    private ResourceReferenceCollectionInterface $relatedReferences;

    /**
     * @param positive-int     $sequenceNumber
     * @param non-empty-string $type
     * @param array<mixed>     $body
     */
    public function __construct(
        public readonly int $sequenceNumber,
        public readonly string $type,
        public readonly ResourceReference $resourceReference,
        public readonly array $body,
    ) {
    }

    /**
     * @param non-empty-string $job
     */
    public function withJob(string $job): EventInterface
    {
        $event = new Event($this->sequenceNumber, $this->type, $this->resourceReference, $this->body);
        $event->job = $job;

        if (isset($this->relatedReferences)) {
            $event->relatedReferences = $this->relatedReferences;
        }

        return $event;
    }

    public function withRelatedReferences(ResourceReferenceCollectionInterface $relatedReferences): EventInterface
    {
        $event = new Event($this->sequenceNumber, $this->type, $this->resourceReference, $this->body);
        $event->relatedReferences = $relatedReferences;

        if (isset($this->job)) {
            $event->job = $this->job;
        }

        return $event;
    }

    public function toArray(): array
    {
        $data = array_merge(
            [
                'sequence_number' => $this->sequenceNumber,
                'type' => $this->type,
                'body' => $this->body,
            ],
            $this->resourceReference->toArray(),
        );

        if (isset($this->relatedReferences)) {
            $data['related_references'] = $this->relatedReferences->toArray();
        }

        if (isset($this->job)) {
            $data['job'] = $this->job;
        }

        return $data;
    }
}
