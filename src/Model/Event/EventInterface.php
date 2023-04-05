<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model\Event;

/**
 * @phpstan-type SerializedEvent array{
 *     sequence_number: positive-int,
 *     type: non-empty-string,
 *     body: array<mixed>,
 *     related_references?: array<array{label: non-empty-string, reference: non-empty-string}>,
 *     label: non-empty-string,
 *     reference: non-empty-string
 * }
 */
interface EventInterface
{
    /**
     * @return SerializedEvent
     */
    public function toArray(): array;
}
