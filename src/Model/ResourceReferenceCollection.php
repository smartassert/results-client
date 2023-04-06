<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model;

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
     * @return array<array{label: non-empty-string, reference: non-empty-string}>
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
