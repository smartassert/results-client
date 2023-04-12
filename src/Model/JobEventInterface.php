<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model;

/**
 * @phpstan-type SerializedJobEvent array{
 *     job?: non-empty-string,
 *     sequence_number: positive-int,
 *     type: non-empty-string,
 *     body: array<mixed>,
 *     related_references?: array<array{label: non-empty-string, reference: non-empty-string}>,
 *     label: non-empty-string,
 *     reference: non-empty-string
 * }
 */
interface JobEventInterface extends EventInterface
{
    /**
     * @return SerializedJobEvent
     */
    public function toArray(): array;

    /**
     * @param non-empty-string $job
     */
    public function withJob(string $job): JobEventInterface;
}
