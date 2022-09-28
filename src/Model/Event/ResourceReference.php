<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model\Event;

/**
 * @phpstan-type SerializedResourceReference array{
 *     label: non-empty-string,
 *     reference: non-empty-string
 * }
 */
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
     * @return SerializedResourceReference
     */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'reference' => $this->reference,
        ];
    }
}
