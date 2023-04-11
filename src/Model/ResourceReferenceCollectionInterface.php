<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model;

/**
 * @phpstan-import-type SerializedResourceReference from ResourceReferenceInterface
 */
interface ResourceReferenceCollectionInterface
{
    /**
     * @return SerializedResourceReference[]
     */
    public function toArray(): array;
}
