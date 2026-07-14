<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ResultsClient\Model\Job;
use SmartAssert\ResultsClient\Model\JobState;
use SmartAssert\ResultsClient\Model\MetaState;

class GetJobTest extends AbstractClientModelCreationTestCase
{
    public function testGetJobStatusRequestProperties(): void
    {
        $jobLabel = 'job label';
        $addEventUrl = 'https://example.com/event/add/' . md5((string) rand());

        $this->mockHandler->append(new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode([
                'label' => $jobLabel,
                'event_add_url' => $addEventUrl,
                'state' => 'awaiting-events',
                'end_state' => null,
                'has_events' => false,
            ])
        ));

        $apiKey = 'api key value';

        $this->client->getJobStatus($apiKey, $jobLabel);

        $request = $this->getLastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('Bearer ' . $apiKey, $request->getHeaderLine('authorization'));
    }

    #[DataProvider('getJobStatusSuccessDataProvider')]
    public function testGetJobStatusSuccess(ResponseInterface $httpFixture, Job $expected): void
    {
        $this->mockHandler->append($httpFixture);

        $actual = $this->client->getJobStatus('api key', 'label');
        self::assertEquals($expected, $actual);
    }

    /**
     * @return array<mixed>
     */
    public static function getJobStatusSuccessDataProvider(): array
    {
        $addEventUrl = 'https://example.com/event/add/' . md5((string) rand());

        return [
            'state=started, no end_state, no pending meta state, previous states not present' => [
                'httpFixture' => new Response(
                    200,
                    [
                        'content-type' => 'application/json',
                    ],
                    (string) json_encode([
                        'label' => 'label',
                        'event_add_url' => $addEventUrl,
                        'state' => 'started',
                        'meta_state' => [
                            'ended' => false,
                            'succeeded' => false,
                        ],
                        'has_events' => false,
                    ])
                ),
                'expected' => new Job(
                    'label',
                    $addEventUrl,
                    new JobState(
                        'started',
                        null,
                        new MetaState(ended: false, succeeded: false, pending: true),
                    ),
                    false,
                    [],
                ),
            ],
            'state=started, no end_state, no pending meta state, no previous states' => [
                'httpFixture' => new Response(
                    200,
                    [
                        'content-type' => 'application/json',
                    ],
                    (string) json_encode([
                        'label' => 'label',
                        'event_add_url' => $addEventUrl,
                        'state' => 'started',
                        'meta_state' => [
                            'ended' => false,
                            'succeeded' => false,
                        ],
                        'has_events' => false,
                        'previous_states' => [],
                    ])
                ),
                'expected' => new Job(
                    'label',
                    $addEventUrl,
                    new JobState(
                        'started',
                        null,
                        new MetaState(ended: false, succeeded: false, pending: true),
                    ),
                    false,
                    [],
                ),
            ],
            'state=started, no end_state, no pending meta state, has previous states' => [
                'httpFixture' => new Response(
                    200,
                    [
                        'content-type' => 'application/json',
                    ],
                    (string) json_encode([
                        'label' => 'label',
                        'event_add_url' => $addEventUrl,
                        'state' => 'started',
                        'meta_state' => [
                            'ended' => false,
                            'succeeded' => false,
                        ],
                        'has_events' => false,
                        'previous_states' => [
                            'previous_state_1',
                            'previous_state_2',
                            'previous_state_3',
                        ],
                    ])
                ),
                'expected' => new Job(
                    'label',
                    $addEventUrl,
                    new JobState(
                        'started',
                        null,
                        new MetaState(ended: false, succeeded: false, pending: true),
                    ),
                    false,
                    [
                        'previous_state_1',
                        'previous_state_2',
                        'previous_state_3',
                    ],
                ),
            ],
            'state=started, no end_state' => [
                'httpFixture' => new Response(
                    200,
                    [
                        'content-type' => 'application/json',
                    ],
                    (string) json_encode([
                        'label' => 'label',
                        'event_add_url' => $addEventUrl,
                        'state' => 'started',
                        'meta_state' => [
                            'ended' => false,
                            'succeeded' => false,
                            'pending' => true,
                        ],
                        'has_events' => true,
                    ])
                ),
                'expected' => new Job(
                    'label',
                    $addEventUrl,
                    new JobState(
                        'started',
                        null,
                        new MetaState(ended: false, succeeded: false, pending: true),
                    ),
                    true,
                    [],
                ),
            ],
            'state=complete,end_state=ended' => [
                'httpFixture' => new Response(
                    200,
                    [
                        'content-type' => 'application/json',
                    ],
                    (string) json_encode([
                        'label' => 'label',
                        'event_add_url' => $addEventUrl,
                        'state' => 'complete',
                        'end_state' => 'ended',
                        'meta_state' => [
                            'ended' => true,
                            'succeeded' => true,
                            'pending' => false,
                        ],
                        'has_events' => true,
                    ])
                ),
                'expected' => new Job(
                    'label',
                    $addEventUrl,
                    new JobState(
                        'complete',
                        'ended',
                        new MetaState(ended: true, succeeded: true, pending: false)
                    ),
                    true,
                    [],
                ),
            ],
        ];
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->getJobStatus('api token', 'label');
        };
    }

    protected function getExpectedModelClass(): string
    {
        return Job::class;
    }
}
