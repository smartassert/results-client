<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Integration;

use SmartAssert\ResultsClient\Model\Event\Event;
use SmartAssert\ResultsClient\Model\Event\JobEvent;
use SmartAssert\ResultsClient\Model\Event\ResourceReference;
use SmartAssert\ResultsClient\Model\Job;
use SmartAssert\UsersClient\Model\Token;
use Symfony\Component\Uid\Ulid;

class ListEventsTest extends AbstractIntegrationTest
{
    private static Token $user2ApiToken;

    /**
     * @var non-empty-string
     */
    private static string $user2JobLabel;
    private static Job $user2Job;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$user2ApiToken = self::createUserApiToken(self::USER2_EMAIL, self::USER2_PASSWORD);

        $user2JobLabel = (string) new Ulid();
        \assert('' !== $user2JobLabel);
        self::$user2JobLabel = $user2JobLabel;

        $job = self::$client->createJob(self::$user2ApiToken->token, self::$user2JobLabel);
        \assert($job instanceof Job);
        self::$user2Job = $job;

        $user1Test1Reference = new ResourceReference('user1test1.yml', md5('user1test1.yml'));
        $user1Test2Reference = new ResourceReference('user1test2.yml', md5('user1test2.yml'));
        $user2Test1Reference = new ResourceReference('user2test1.yml', md5('user2test1.yml'));

        self::$client->addEvent(
            self::$user1Job->token,
            new Event(1, 'type_1', $user1Test1Reference, [])
        );

        self::$client->addEvent(
            self::$user1Job->token,
            new Event(2, 'type_2', $user1Test1Reference, [])
        );

        self::$client->addEvent(
            self::$user1Job->token,
            new Event(3, 'type_1', $user1Test2Reference, [])
        );

        self::$client->addEvent(
            self::$user2Job->token,
            new Event(1, 'type_1', $user2Test1Reference, [])
        );
    }

    /**
     * @param callable(): Token            $apiTokenCreator
     * @param callable(): non-empty-string $jobLabelCreator
     * @param ?non-empty-string            $eventReference
     * @param ?non-empty-string            $type
     * @param callable(): Event[]          $expectedEventsCreator
     *
     * @dataProvider listDataProvider
     */
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
            self::$client->listEvents($apiToken->token, $jobLabelCreator(), $eventReference, $type)
        );
    }

    /**
     * @return array<mixed>
     */
    public function listDataProvider(): array
    {
        return [
            'invalid job user' => [
                'apiToken' => function () {
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
                'apiToken' => function () {
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
                'apiToken' => function () {
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
                'apiToken' => function () {
                    return self::$user1ApiToken;
                },
                'jobLabelCreator' => function () {
                    return self::$user1JobLabel;
                },
                'eventReference' => md5('user1test2.yml'),
                'type' => null,
                'expectedEventsCreator' => function () {
                    return [
                        new JobEvent(
                            self::$user1JobLabel,
                            new Event(
                                3,
                                'type_1',
                                new ResourceReference('user1test2.yml', md5('user1test2.yml')),
                                []
                            )
                        ),
                    ];
                },
            ],
            'single event, matches reference (user 2)' => [
                'apiToken' => function () {
                    return self::$user2ApiToken;
                },
                'jobLabelCreator' => function () {
                    return self::$user2JobLabel;
                },
                'eventReference' => md5('user2test1.yml'),
                'type' => null,
                'expectedEventsCreator' => function () {
                    return [
                        new JobEvent(
                            self::$user2JobLabel,
                            new Event(
                                1,
                                'type_1',
                                new ResourceReference('user2test1.yml', md5('user2test1.yml')),
                                []
                            )
                        ),
                    ];
                },
            ],
            'two events match reference' => [
                'apiToken' => function () {
                    return self::$user1ApiToken;
                },
                'jobLabelCreator' => function () {
                    return self::$user1JobLabel;
                },
                'eventReference' => md5('user1test1.yml'),
                'type' => null,
                'expectedEventsCreator' => function () {
                    return [
                        new JobEvent(
                            self::$user1JobLabel,
                            new Event(
                                1,
                                'type_1',
                                new ResourceReference('user1test1.yml', md5('user1test1.yml')),
                                []
                            )
                        ),
                        new JobEvent(
                            self::$user1JobLabel,
                            new Event(
                                2,
                                'type_2',
                                new ResourceReference('user1test1.yml', md5('user1test1.yml')),
                                []
                            )
                        ),
                    ];
                },
            ],
            'single event, matches reference (user 1), excluded by type filter' => [
                'apiToken' => function () {
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
                'apiToken' => function () {
                    return self::$user1ApiToken;
                },
                'jobLabelCreator' => function () {
                    return self::$user1JobLabel;
                },
                'eventReference' => md5('user1test1.yml'),
                'type' => 'type_1',
                'expectedEventsCreator' => function () {
                    return [
                        new JobEvent(
                            self::$user1JobLabel,
                            new Event(
                                1,
                                'type_1',
                                new ResourceReference('user1test1.yml', md5('user1test1.yml')),
                                []
                            )
                        ),
                    ];
                },
            ],
            'no reference, no type' => [
                'apiToken' => function () {
                    return self::$user1ApiToken;
                },
                'jobLabelCreator' => function () {
                    return self::$user1JobLabel;
                },
                'eventReference' => null,
                'type' => null,
                'expectedEventsCreator' => function () {
                    return [
                        new JobEvent(
                            self::$user1JobLabel,
                            new Event(
                                1,
                                'type_1',
                                new ResourceReference('user1test1.yml', md5('user1test1.yml')),
                                []
                            )
                        ),
                        new JobEvent(
                            self::$user1JobLabel,
                            new Event(
                                2,
                                'type_2',
                                new ResourceReference('user1test1.yml', md5('user1test1.yml')),
                                []
                            )
                        ),
                        new JobEvent(
                            self::$user1JobLabel,
                            new Event(
                                3,
                                'type_1',
                                new ResourceReference('user1test2.yml', md5('user1test2.yml')),
                                []
                            )
                        ),
                    ];
                },
            ],
        ];
    }
}
