<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Integration;

use SmartAssert\ResultsClient\Model\JobState;
use SmartAssert\ResultsClient\Model\MetaState;
use Symfony\Component\Uid\Ulid;

class CreateJobTest extends AbstractIntegrationTestCase
{
    public function testCreateJob(): void
    {
        $jobLabel = (string) new Ulid();
        $job = self::$client->createJob(self::$user1ApiToken, $jobLabel);

        self::assertSame($jobLabel, $job->label);
        self::assertEquals(
            new JobState('awaiting-events', null, new MetaState(false, false)),
            $job->state
        );
    }
}
