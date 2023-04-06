<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model\Event;

/**
 * @phpstan-type SerializedJobEvent array{
 *     job: non-empty-string,
 *     sequence_number: positive-int,
 *     type: non-empty-string,
 *     body: array<mixed>,
 *     related_references?: array<array{label: non-empty-string, reference: non-empty-string}>,
 *     label: non-empty-string,
 *     reference: non-empty-string
 * }
 */
class JobEvent implements \JsonSerializable
{
    /**
     * @param non-empty-string $jobLabel
     */
    public function __construct(
        public readonly string $jobLabel,
        public readonly EventInterface $event,
    ) {
    }

    /**
     * @return SerializedJobEvent
     */
    public function jsonSerialize(): array
    {
        return array_merge(
            ['job' => $this->jobLabel],
            $this->event->toArray(),
        );
    }
}
