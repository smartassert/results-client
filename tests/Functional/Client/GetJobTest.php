<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ResultsClient\Model\JobState;

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

    /**
     * @dataProvider getJobStatusSuccessDataProvider
     */
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
                    ])
                ),
                'expected' => new JobState('started', null),
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
                    ])
                ),
                'expected' => new JobState('complete', 'ended'),
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
