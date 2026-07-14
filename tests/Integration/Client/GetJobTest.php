<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Integration\Client;

use SmartAssert\ResultsClient\Model\JobState;
use SmartAssert\ResultsClient\Model\MetaState;
use SmartAssert\ResultsClient\Tests\Integration\AbstractBaseTestCase;
use Symfony\Component\Uid\Ulid;

class GetJobTest extends AbstractBaseTestCase
{
    public function testGetJobStatus(): void
    {
        $jobLabel = (string) new Ulid();
        self::$client->createJob(self::$user1ApiToken, $jobLabel, null);

        $job = self::$client->getJobStatus(self::$user1ApiToken, $jobLabel);

        self::assertSame($jobLabel, $job->label);
        self::assertEquals(
            new JobState(
                'awaiting-events',
                null,
                new MetaState(ended: false, succeeded: false, pending: true)
            ),
            $job->state
        );
    }
}
