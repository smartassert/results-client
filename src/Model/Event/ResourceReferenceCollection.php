<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model\Event;

/**
 * @phpstan-import-type SerializedResourceReference from ResourceReference
 */
class ResourceReferenceCollection
{
    /**
     * @param ResourceReference[] $resourceReferences
     */
    public function __construct(
        public readonly array $resourceReferences = [],
    ) {
    }

    /**
     * @return SerializedResourceReference[]
     */
    public function toArray(): array
    {
        $data = [];

        foreach ($this->resourceReferences as $resourceReference) {
            $data[] = $resourceReference->toArray();
        }

        return $data;
    }
}
