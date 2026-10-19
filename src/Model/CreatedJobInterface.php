<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Model;

interface CreatedJobInterface extends JobInterface
{
    public function getEventNotifyToken(): string;

    public function getEventNotifyUrl(): string;
}
