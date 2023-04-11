<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model;

class ResourceReference implements ResourceReferenceInterface
{
    /**
     * @param non-empty-string $label
     * @param non-empty-string $reference
     */
    public function __construct(
        public readonly string $label,
        public readonly string $reference,
    ) {
    }

    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'reference' => $this->reference,
        ];
    }
}
