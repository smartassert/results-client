<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model;

class ResourceReference
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

    /**
     * @return array{label: non-empty-string, reference: non-empty-string}
     */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'reference' => $this->reference,
        ];
    }
}
