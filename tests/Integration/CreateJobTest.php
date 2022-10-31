<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Integration;

use SmartAssert\ResultsClient\Model\Job;
use Symfony\Component\Uid\Ulid;

class CreateJobTest extends AbstractIntegrationTest
{
    public function testCreateJob(): void
    {
        $jobLabel = (string) new Ulid();
        \assert('' !== $jobLabel);

        $job = self::$client->createJob(self::$user1ApiToken->token, $jobLabel);
        self::assertInstanceOf(Job::class, $job);
    }
}
