<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Integration;

use SmartAssert\ResultsClient\Model\JobState;
use SmartAssert\ResultsClient\Model\MetaState;
use Symfony\Component\Uid\Ulid;

class GetJobTest extends AbstractIntegrationTestCase
{
    public function testGetJobStatus(): void
    {
        $jobLabel = (string) new Ulid();
        self::$client->createJob(self::$user1ApiToken, $jobLabel);

        $jobStatus = self::$client->getJobStatus(self::$user1ApiToken, $jobLabel);
        self::assertEquals(
            new JobState('awaiting-events', null, new MetaState(false, false)),
            $jobStatus
        );
    }
}
