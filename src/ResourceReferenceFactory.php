<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient;

use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\ResultsClient\Model\ResourceReference;
use SmartAssert\ResultsClient\Model\ResourceReferenceInterface;

class ResourceReferenceFactory
{
    public function create(ArrayInspector $data): ?ResourceReferenceInterface
    {
        $label = $data->getNonEmptyString('label');
        $reference = $data->getNonEmptyString('reference');

        return null === $label || null === $reference ? null : new ResourceReference($label, $reference);
    }
}
