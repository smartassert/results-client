<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ResultsClient\Model\Job;

class CreateJobTest extends AbstractClientModelCreationTest
{
    public function testCreateJobRequestProperties(): void
    {
        $jobLabel = 'job label';

        $this->mockHandler->append(new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode([
                'label' => $jobLabel,
                'token' => 'job token',
            ])
        ));

        $apiKey = 'api key value';

        $this->client->createJob($apiKey, $jobLabel);

        $request = $this->getLastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('Bearer ' . $apiKey, $request->getHeaderLine('authorization'));
    }

    /**
     * @dataProvider createApiTokenSuccessDataProvider
     */
    public function testCreateJobSuccess(ResponseInterface $httpFixture, Job $expected): void
    {
        $this->mockHandler->append($httpFixture);

        $actual = $this->client->createJob('api key', 'label');
        self::assertEquals($expected, $actual);
    }

    /**
     * @return array<mixed>
     */
    public function createApiTokenSuccessDataProvider(): array
    {
        $label = md5((string) rand());
        $token = md5((string) rand());

        return [
            'created' => [
                'httpFixture' => new Response(
                    200,
                    [
                        'content-type' => 'application/json',
                    ],
                    (string) json_encode([
                        'label' => $label,
                        'token' => $token,
                    ])
                ),
                'expected' => new Job($label, $token),
            ],
        ];
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->createJob('api token', 'label');
        };
    }

    protected function getExpectedModelClass(): string
    {
        return Job::class;
    }
}
