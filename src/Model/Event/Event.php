<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model\Event;

/**
 * @phpstan-import-type SerializedResourceReference from ResourceReference
 *
 * @phpstan-type SerializedEvent array{
 *     sequence_number: positive-int,
 *     type: non-empty-string,
 *     body: array<mixed>,
 *     related_references?: SerializedResourceReference[],
 *     label: non-empty-string,
 *     reference: non-empty-string
 * }
 */
class Event implements \JsonSerializable
{
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
     * @return SerializedEvent
     */
    public function jsonSerialize(): array
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

        return $data;
    }
}
