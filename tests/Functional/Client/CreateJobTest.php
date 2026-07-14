<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ResultsClient\Model\Job;
use SmartAssert\ResultsClient\Model\JobState;
use SmartAssert\ResultsClient\Model\MetaState;

class CreateJobTest extends AbstractClientModelCreationTestCase
{
    /**
     * @param non-empty-string  $apiKey
     * @param non-empty-string  $jobLabel
     * @param ?non-empty-string $notifyUrl
     * @param array<mixed>      $expectedRequestHeaders
     */
    #[DataProvider('createJobRequestPropertiesDataProvider')]
    public function testCreateJobRequestProperties(
        string $apiKey,
        string $jobLabel,
        ?string $notifyUrl,
        array $expectedRequestHeaders,
        string $expectedRequestBody
    ): void {
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

        $this->client->createJob($apiKey, $jobLabel, $notifyUrl);

        $request = $this->getLastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('Bearer ' . $apiKey, $request->getHeaderLine('authorization'));

        $requestHeaders = $request->getHeaders();
        unset($requestHeaders['Host'], $requestHeaders['User-Agent'], $requestHeaders['Content-Length']);

        self::assertEquals($expectedRequestHeaders, $requestHeaders);
        self::assertSame($expectedRequestBody, (string) $request->getBody());
    }

    /**
     * @return array<mixed>
     */
    public static function createJobRequestPropertiesDataProvider(): array
    {
        return [
            'without notify url' => [
                'apiKey' => 'api key value',
                'jobLabel' => 'job label',
                'notifyUrl' => null,
                'expectedRequestHeaders' => [
                    'Authorization' => ['Bearer api key value'],
                ],
                'expectedRequestBody' => '',
            ],
            'with notify url' => [
                'apiKey' => 'api key value',
                'jobLabel' => 'job label',
                'notifyUrl' => 'https://example.com/notify',
                'expectedRequestHeaders' => [
                    'Authorization' => ['Bearer api key value'],
                    'Content-Type' => ['application/x-www-form-urlencoded'],
                ],
                'expectedRequestBody' => http_build_query(['notify_url' => 'https://example.com/notify']),
            ],
        ];
    }

    #[DataProvider('createApiTokenSuccessDataProvider')]
    public function testCreateJobSuccess(ResponseInterface $httpFixture, Job $expected): void
    {
        $this->mockHandler->append($httpFixture);

        $actual = $this->client->createJob('api key', 'label', null);
        self::assertEquals($expected, $actual);
    }

    /**
     * @return array<mixed>
     */
    public static function createApiTokenSuccessDataProvider(): array
    {
        $label = md5((string) rand());
        $addEventUrl = 'https://example.com/event/add/' . md5((string) rand());

        return [
            'created' => [
                'httpFixture' => new Response(
                    200,
                    [
                        'content-type' => 'application/json',
                    ],
                    (string) json_encode([
                        'label' => $label,
                        'event_add_url' => $addEventUrl,
                        'state' => 'awaiting-events',
                        'end_state' => null,
                        'has_events' => false,
                    ])
                ),
                'expected' => new Job(
                    $label,
                    $addEventUrl,
                    new JobState(
                        'awaiting-events',
                        null,
                        new MetaState(ended: false, succeeded: false, pending: true)
                    ),
                    false,
                    [],
                ),
            ],
        ];
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->createJob('api token', 'label', null);
        };
    }

    protected function getExpectedModelClass(): string
    {
        return Job::class;
    }
}
