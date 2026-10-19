<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Integration\Client;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\RequestInterface;
use SmartAssert\ResultsClient\Model\CreatedJobInterface;
use SmartAssert\ResultsClient\Model\Event;
use SmartAssert\ResultsClient\Model\EventInterface;
use SmartAssert\ResultsClient\Model\ResourceReference;
use SmartAssert\ResultsClient\Tests\Integration\AbstractBaseTestCase;
use SmartAssert\SymfonyRemoteEventRequestFactory\Factory;
use Symfony\Component\RemoteEvent\RemoteEvent;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Webhook\Server\HeadersConfigurator;
use Symfony\Component\Webhook\Server\HeaderSignatureConfigurator;
use Symfony\Component\Webhook\Server\JsonBodyConfigurator;
use Symfony\Component\Webhook\Server\NativeJsonPayloadSerializer;

class ListEventsTest extends AbstractBaseTestCase
{
    /**
     * @var non-empty-string
     */
    private static string $user2ApiToken;

    /**
     * @var non-empty-string
     */
    private static string $user2JobLabel;
    private static CreatedJobInterface $user2Job;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$user2ApiToken = self::createUserApiToken(self::USER2_EMAIL);

        $user2JobLabel = (string) new Ulid();
        self::$user2JobLabel = $user2JobLabel;

        self::$user2Job = self::$client->createJob(self::$user2ApiToken, self::$user2JobLabel, null);

        $user1Test1Reference = new ResourceReference('user1test1.yml', md5('user1test1.yml'));
        $user1Test2Reference = new ResourceReference('user1test2.yml', md5('user1test2.yml'));
        $user2Test1Reference = new ResourceReference('user2test1.yml', md5('user2test1.yml'));

        $httpClient = new HttpClient();

        $httpClient->send(self::createRemoteEventRequest(
            [
                'job' => self::$user1JobLabel,
                'sequence_number' => 1,
                'type' => 'type_1',
                'label' => $user1Test1Reference->label,
                'reference' => $user1Test1Reference->reference,
                'body' => [],
            ],
            self::$user1Job->getEventNotifyUrl(),
            self::$user1Job->getEventNotifyToken(),
        ));

        $httpClient->send(self::createRemoteEventRequest(
            [
                'job' => self::$user1JobLabel,
                'sequence_number' => 2,
                'type' => 'type_2',
                'label' => $user1Test1Reference->label,
                'reference' => $user1Test1Reference->reference,
                'body' => [],
            ],
            self::$user1Job->getEventNotifyUrl(),
            self::$user1Job->getEventNotifyToken(),
        ));

        $httpClient->send(self::createRemoteEventRequest(
            [
                'job' => self::$user1JobLabel,
                'sequence_number' => 3,
                'type' => 'type_1',
                'label' => $user1Test2Reference->label,
                'reference' => $user1Test2Reference->reference,
                'body' => [],
            ],
            self::$user1Job->getEventNotifyUrl(),
            self::$user1Job->getEventNotifyToken(),
        ));

        $httpClient->send(self::createRemoteEventRequest(
            [
                'job' => self::$user2JobLabel,
                'sequence_number' => 1,
                'type' => 'type_1',
                'label' => $user2Test1Reference->label,
                'reference' => $user2Test1Reference->reference,
                'body' => [],
            ],
            self::$user2Job->getEventNotifyUrl(),
            self::$user2Job->getEventNotifyToken(),
        ));
    }

    /**
     * @param callable(): non-empty-string $apiTokenCreator
     * @param callable(): non-empty-string $jobLabelCreator
     * @param ?non-empty-string            $eventReference
     * @param ?non-empty-string            $type
     * @param callable(): EventInterface[] $expectedEventsCreator
     */
    #[DataProvider('listDataProvider')]
    public function testList(
        callable $apiTokenCreator,
        callable $jobLabelCreator,
        ?string $eventReference,
        ?string $type,
        callable $expectedEventsCreator,
    ): void {
        $apiToken = $apiTokenCreator();

        self::assertEquals(
            $expectedEventsCreator(),
            self::$client->listEvents($apiToken, $jobLabelCreator(), $eventReference, $type)
        );
    }

    /**
     * @return array<mixed>
     */
    public static function listDataProvider(): array
    {
        return [
            'invalid job user' => [
                'apiTokenCreator' => function () {
                    return self::$user2ApiToken;
                },
                'jobLabelCreator' => function () {
                    return self::$user1JobLabel;
                },
                'eventReference' => 'event reference',
                'type' => null,
                'expectedEventsCreator' => function () {
                    return [];
                },
            ],
            'job does not exist' => [
                'apiTokenCreator' => function () {
                    return self::$user1ApiToken;
                },
                'jobLabelCreator' => function () {
                    return (string) new Ulid();
                },
                'eventReference' => md5('user1test1.yml'),
                'type' => null,
                'expectedEventsCreator' => function () {
                    return [];
                },
            ],
            'no matching events' => [
                'apiTokenCreator' => function () {
                    return self::$user1ApiToken;
                },
                'jobLabelCreator' => function () {
                    return self::$user1JobLabel;
                },
                'eventReference' => md5('user1test3.yml'),
                'type' => null,
                'expectedEventsCreator' => function () {
                    return [];
                },
            ],
            'single event, matches reference (user 1)' => [
                'apiTokenCreator' => function () {
                    return self::$user1ApiToken;
                },
                'jobLabelCreator' => function () {
                    return self::$user1JobLabel;
                },
                'eventReference' => md5('user1test2.yml'),
                'type' => null,
                'expectedEventsCreator' => function () {
                    return [
                        new Event(
                            self::$user1JobLabel,
                            3,
                            'type_1',
                            new ResourceReference('user1test2.yml', md5('user1test2.yml')),
                            [],
                        ),
                    ];
                },
            ],
            'single event, matches reference (user 2)' => [
                'apiTokenCreator' => function () {
                    return self::$user2ApiToken;
                },
                'jobLabelCreator' => function () {
                    return self::$user2JobLabel;
                },
                'eventReference' => md5('user2test1.yml'),
                'type' => null,
                'expectedEventsCreator' => function () {
                    return [
                        new Event(
                            self::$user2JobLabel,
                            1,
                            'type_1',
                            new ResourceReference('user2test1.yml', md5('user2test1.yml')),
                            [],
                        ),
                    ];
                },
            ],
            'two events match reference' => [
                'apiTokenCreator' => function () {
                    return self::$user1ApiToken;
                },
                'jobLabelCreator' => function () {
                    return self::$user1JobLabel;
                },
                'eventReference' => md5('user1test1.yml'),
                'type' => null,
                'expectedEventsCreator' => function () {
                    return [
                        new Event(
                            self::$user1JobLabel,
                            1,
                            'type_1',
                            new ResourceReference('user1test1.yml', md5('user1test1.yml')),
                            [],
                        ),
                        new Event(
                            self::$user1JobLabel,
                            2,
                            'type_2',
                            new ResourceReference('user1test1.yml', md5('user1test1.yml')),
                            [],
                        ),
                    ];
                },
            ],
            'single event, matches reference (user 1), excluded by type filter' => [
                'apiTokenCreator' => function () {
                    return self::$user1ApiToken;
                },
                'jobLabelCreator' => function () {
                    return self::$user1JobLabel;
                },
                'eventReference' => md5('user1test2.yml'),
                'type' => 'type_2',
                'expectedEventsCreator' => function () {
                    return [];
                },
            ],
            'two events match reference, one excluded by type filter' => [
                'apiTokenCreator' => function () {
                    return self::$user1ApiToken;
                },
                'jobLabelCreator' => function () {
                    return self::$user1JobLabel;
                },
                'eventReference' => md5('user1test1.yml'),
                'type' => 'type_1',
                'expectedEventsCreator' => function () {
                    return [
                        new Event(
                            self::$user1JobLabel,
                            1,
                            'type_1',
                            new ResourceReference('user1test1.yml', md5('user1test1.yml')),
                            [],
                        ),
                    ];
                },
            ],
            'no reference, no type' => [
                'apiTokenCreator' => function () {
                    return self::$user1ApiToken;
                },
                'jobLabelCreator' => function () {
                    return self::$user1JobLabel;
                },
                'eventReference' => null,
                'type' => null,
                'expectedEventsCreator' => function () {
                    return [
                        new Event(
                            self::$user1JobLabel,
                            1,
                            'type_1',
                            new ResourceReference('user1test1.yml', md5('user1test1.yml')),
                            [],
                        ),
                        new Event(
                            self::$user1JobLabel,
                            2,
                            'type_2',
                            new ResourceReference('user1test1.yml', md5('user1test1.yml')),
                            [],
                        ),
                        new Event(
                            self::$user1JobLabel,
                            3,
                            'type_1',
                            new ResourceReference('user1test2.yml', md5('user1test2.yml')),
                            [],
                        ),
                    ];
                },
            ],
        ];
    }

    /**
     * @param array<mixed> $payload
     */
    private static function createRemoteEventRequest(array $payload, string $uri, string $token): RequestInterface
    {
        $headersConfigurator = new HeadersConfigurator();

        $serializer = new NativeJsonPayloadSerializer();

        $jsonBodyConfigurator = new JsonBodyConfigurator($serializer);
        $headerSignatureConfigurator = new HeaderSignatureConfigurator();

        $remoteEventRequestFactory = new Factory(
            $headersConfigurator,
            $jsonBodyConfigurator,
            $headerSignatureConfigurator,
        );

        $remoteEvent = new RemoteEvent('worker.event', (string) new Ulid(), $payload);

        $addEventHeaders = $remoteEventRequestFactory->createHeaders($remoteEvent, $token);
        $addEventBody = $remoteEventRequestFactory->createBody($remoteEvent, $token);

        return new Request('POST', $uri, $addEventHeaders, $addEventBody);
    }
}
