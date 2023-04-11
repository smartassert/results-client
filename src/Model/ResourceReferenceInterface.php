<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model;

/**
 * @phpstan-type SerializedResourceReference array{
 *     label: non-empty-string,
 *     reference: non-empty-string
 * }
 */
interface ResourceReferenceInterface
{
    /**
     * @return SerializedResourceReference
     */
    public function toArray(): array;
}
