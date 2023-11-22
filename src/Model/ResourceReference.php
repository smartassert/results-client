<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model;

readonly class ResourceReference implements ResourceReferenceInterface
{
    /**
     * @param non-empty-string $label
     * @param non-empty-string $reference
     */
    public function __construct(
        public string $label,
        public string $reference,
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
