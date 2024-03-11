<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model;

interface JobInterface
{
    /**
     * @return non-empty-string
     */
    public function getLabel(): string;

    /**
     * @return non-empty-string
     */
    public function getToken(): string;

    public function getState(): JobState;
}
