<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Integration;

use SmartAssert\ResultsClient\Model\JobState;
use Symfony\Component\Uid\Ulid;

class GetJobTest extends AbstractIntegrationTest
{
    public function testGetJobStatus(): void
    {
        $jobLabel = (string) new Ulid();
        \assert('' !== $jobLabel);

        self::$client->createJob(self::$user1ApiToken->token, $jobLabel);

        $jobStatus = self::$client->getJobStatus(self::$user1ApiToken->token, $jobLabel);
        self::assertEquals(new JobState('awaiting-events', null), $jobStatus);
    }
}
