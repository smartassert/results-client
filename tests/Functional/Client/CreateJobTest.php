<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ResultsClient\Model\Job;
use SmartAssert\ResultsClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;
use SmartAssert\ResultsClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\ResultsClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;

class CreateJobTest extends AbstractClientTest
{
    use CommonNonSuccessResponseDataProviderTrait;
    use InvalidJsonResponseExceptionDataProviderTrait;
    use NetworkErrorExceptionDataProviderTrait;

    /**
     * @dataProvider networkErrorExceptionDataProvider
     * @dataProvider invalidJsonResponseExceptionDataProvider
     *
     * @param class-string<\Throwable> $expectedExceptionClass
     */
    public function testCreateJobThrowsException(
        ResponseInterface|ClientExceptionInterface $httpFixture,
        string $expectedExceptionClass,
    ): void {
        $this->mockHandler->append($httpFixture);

        $this->expectException($expectedExceptionClass);

        $this->client->createJob('user token', 'label');
    }

    /**
     * @dataProvider commonNonSuccessResponseDataProvider
     */
    public function testCreateJobThrowsNonSuccessResponseException(ResponseInterface $httpFixture): void
    {
        $this->mockHandler->append($httpFixture);

        try {
            $this->client->createJob('user token', 'label');
            self::fail(NonSuccessResponseException::class . ' not thrown');
        } catch (NonSuccessResponseException $e) {
            self::assertSame($httpFixture, $e->response);
        }
    }

    public function testCreateJobRequestProperties(): void
    {
        $this->mockHandler->append(new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode([])
        ));

        $apiKey = 'api key value';

        $this->client->createJob($apiKey, 'label');

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
}
