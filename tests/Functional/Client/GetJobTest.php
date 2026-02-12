<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ResultsClient\Model\JobState;
use SmartAssert\ResultsClient\Model\MetaState;

class GetJobTest extends AbstractClientModelCreationTestCase
{
    public function testGetJobStatusRequestProperties(): void
    {
        $jobLabel = 'job label';

        $this->mockHandler->append(new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode([
                'state' => 'ended',
                'token' => 'complete',
            ])
        ));

        $apiKey = 'api key value';

        $this->client->getJobStatus($apiKey, $jobLabel);

        $request = $this->getLastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('Bearer ' . $apiKey, $request->getHeaderLine('authorization'));
    }

    #[DataProvider('getJobStatusSuccessDataProvider')]
    public function testGetJobStatusSuccess(ResponseInterface $httpFixture, JobState $expected): void
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
        return [
            'state=started, no end_state' => [
                'httpFixture' => new Response(
                    200,
                    [
                        'content-type' => 'application/json',
                    ],
                    (string) json_encode([
                        'state' => 'started',
                        'meta_state' => [
                            'ended' => false,
                            'succeeded' => false,
                        ],
                    ])
                ),
                'expected' => new JobState('started', null, new MetaState(false, false)),
            ],
            'state=complete,end_state=ended' => [
                'httpFixture' => new Response(
                    200,
                    [
                        'content-type' => 'application/json',
                    ],
                    (string) json_encode([
                        'state' => 'complete',
                        'end_state' => 'ended',
                        'meta_state' => [
                            'ended' => true,
                            'succeeded' => true,
                        ],
                    ])
                ),
                'expected' => new JobState('complete', 'ended', new MetaState(true, true)),
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
        return JobState::class;
    }
}
