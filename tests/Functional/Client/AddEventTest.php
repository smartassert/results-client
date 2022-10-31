<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ResultsClient\Model\Event\Event;
use SmartAssert\ResultsClient\Model\Event\JobEvent;
use SmartAssert\ResultsClient\Model\Event\ResourceReference;
use SmartAssert\ResultsClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;
use SmartAssert\ResultsClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\ResultsClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;

class AddEventTest extends AbstractClientTest
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
    public function testAddEventThrowsException(
        ResponseInterface|ClientExceptionInterface $httpFixture,
        string $expectedExceptionClass,
    ): void {
        $this->mockHandler->append($httpFixture);

        $this->expectException($expectedExceptionClass);

        $this->client->addEvent(
            'job token',
            new Event(1, 'job/started', new ResourceReference('label', 'reference'), [])
        );
    }

    /**
     * @dataProvider commonNonSuccessResponseDataProvider
     */
    public function testAddEventThrowsNonSuccessResponseException(ResponseInterface $httpFixture): void
    {
        $this->mockHandler->append($httpFixture);

        try {
            $this->client->addEvent(
                'job token',
                new Event(1, 'job/started', new ResourceReference('label', 'reference'), [])
            );

            self::fail(NonSuccessResponseException::class . ' not thrown');
        } catch (NonSuccessResponseException $e) {
            self::assertSame($httpFixture, $e->response);
        }
    }

    public function testAddEventRequestProperties(): void
    {
        $this->mockHandler->append(new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode([])
        ));

        $jobToken = 'job token';

        $this->client->addEvent(
            $jobToken,
            new Event(1, 'job/started', new ResourceReference('label', 'reference'), [])
        );

        $request = $this->getLastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('Bearer ' . $jobToken, $request->getHeaderLine('authorization'));
    }

    /**
     * @dataProvider createApiTokenSuccessDataProvider
     */
    public function testAddEventSuccess(Event $event, ResponseInterface $httpFixture, JobEvent $expected): void
    {
        $this->mockHandler->append($httpFixture);

        $actual = $this->client->addEvent('job token', $event);
        self::assertEquals($expected, $actual);
    }

    /**
     * @return array<mixed>
     */
    public function createApiTokenSuccessDataProvider(): array
    {
        $jobLabel = md5((string) rand());
        $jobSequenceNumber = rand(1, 100);
        $jobType = md5((string) rand());
        $referenceLabel = md5((string) rand());
        $referenceReference = md5((string) rand());

        $event = new Event(
            $jobSequenceNumber,
            $jobType,
            new ResourceReference($referenceLabel, $referenceReference),
            []
        );

        return [
            'created' => [
                'event' => $event,
                'httpFixture' => new Response(
                    200,
                    [
                        'content-type' => 'application/json',
                    ],
                    (string) json_encode([
                        'job' => $jobLabel,
                        'sequence_number' => $jobSequenceNumber,
                        'type' => $jobType,
                        'label' => $referenceLabel,
                        'reference' => $referenceReference,
                        'body' => [],
                    ])
                ),
                'expected' => new JobEvent($jobLabel, $event),
            ],
        ];
    }
}
