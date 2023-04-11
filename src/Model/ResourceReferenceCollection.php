<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model;

class ResourceReferenceCollection implements ResourceReferenceCollectionInterface
{
    /**
     * @param ResourceReferenceInterface[] $resourceReferences
     */
    public function __construct(
        private readonly array $resourceReferences = [],
    ) {
    }

    public function toArray(): array
    {
        $data = [];

        foreach ($this->resourceReferences as $resourceReference) {
            $data[] = $resourceReference->toArray();
        }

        return $data;
    }

    public function getReferences(): array
    {
        return $this->resourceReferences;
    }
}
