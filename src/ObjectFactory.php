<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient;

use SmartAssert\ResultsClient\Model\Job;

class ObjectFactory
{
    /**
     * @param array<mixed> $data
     */
    public function createJobFromArray(array $data): ?Job
    {
        $label = $this->getNonEmptyStringValue('label', $data);
        $token = $this->getNonEmptyStringValue('token', $data);

        return null === $label || null === $token ? null : new Job($label, $token);
    }

    /**
     * @param non-empty-string $key
     * @param array<mixed>     $data
     */
    private function getStringValue(string $key, array $data): ?string
    {
        $value = $data[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * @param non-empty-string $key
     * @param array<mixed>     $data
     *
     * @return null|non-empty-string
     */
    private function getNonEmptyStringValue(string $key, array $data): ?string
    {
        $value = trim((string) $this->getStringValue($key, $data));

        return '' === $value ? null : $value;
    }
}
