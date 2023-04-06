<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model\Event;

/**
 * @phpstan-import-type SerializedEvent from EventInterface
 */
class Event implements EventInterface
{
    /**
     * @param positive-int          $sequenceNumber
     * @param non-empty-string      $type
     * @param array<mixed>          $body
     * @param null|non-empty-string $job
     */
    public function __construct(
        public readonly int $sequenceNumber,
        public readonly string $type,
        public readonly ResourceReference $resourceReference,
        public readonly array $body,
        public readonly ?ResourceReferenceCollection $relatedReferences = null,
        public readonly ?string $job = null,
    ) {
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

        if (is_string($this->job)) {
            $data['job'] = $this->job;
        }

        return $data;
    }
}
