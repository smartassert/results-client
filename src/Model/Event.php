<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model;

/**
 * @phpstan-import-type SerializedEvent from EventInterface
 */
class Event implements EventInterface
{
    /**
     * @var null|non-empty-string
     */
    private ?string $job;

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
        public readonly ?ResourceReferenceCollection $relatedReferences = null,
    ) {
    }

    /**
     * @param non-empty-string $job
     */
    public function withJob(string $job): EventInterface
    {
        $event = new Event(
            $this->sequenceNumber,
            $this->type,
            $this->resourceReference,
            $this->body,
            $this->relatedReferences,
        );

        $event->job = $job;

        return $event;
    }

    /**
     * @return SerializedEvent
     */
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

        if ($this->relatedReferences instanceof ResourceReferenceCollection) {
            $data['related_references'] = $this->relatedReferences->toArray();
        }

        if (isset($this->job)) {
            $data['job'] = $this->job;
        }

        return $data;
    }
}
