<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Integration;

use SmartAssert\ResultsClient\Exception\InvalidJobTokenException;
use SmartAssert\ResultsClient\Model\Event\Event;
use SmartAssert\ResultsClient\Model\Event\JobEvent;
use SmartAssert\ResultsClient\Model\Event\ResourceReference;
use SmartAssert\ResultsClient\Model\Event\ResourceReferenceCollection;
use SmartAssert\ResultsClient\Model\Job;
use SmartAssert\UsersClient\Client as UsersClient;
use SmartAssert\UsersClient\Model\ApiKey;
use SmartAssert\UsersClient\Model\Token;
use SmartAssert\UsersClient\Model\User;
use SmartAssert\UsersClient\ObjectFactory as UsersObjectFactory;
use Symfony\Component\Uid\Ulid;

class AddEventTest extends AbstractIntegrationTest
{
    private UsersClient $usersClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usersClient = new UsersClient('http://localhost:9080', $this->serviceClient, new UsersObjectFactory());
    }

    public function testAddInvalidJobToken(): void
    {
        $jobToken = 'invalid token';

        self::expectExceptionObject(new InvalidJobTokenException($jobToken));

        $this->client->addEvent(
            $jobToken,
            new Event(
                1,
                'event_type',
                new ResourceReference('event_label', 'event_reference'),
                []
            )
        );
    }

    /**
     * @dataProvider addSuccessDataProvider
     *
     * @param callable(string): JobEvent $expectedJobEventCreator
     */
    public function testAddSuccess(Event $event, callable $expectedJobEventCreator): void
    {
        $frontendToken = $this->usersClient->createFrontendToken(self::USER_EMAIL, self::USER_PASSWORD);
        \assert($frontendToken instanceof Token);

        $frontendTokenUser = $this->usersClient->verifyFrontendToken($frontendToken);
        \assert($frontendTokenUser instanceof User);

        $apiKeys = $this->usersClient->listUserApiKeys($frontendToken);
        $defaultApiKey = $apiKeys->getDefault();
        \assert($defaultApiKey instanceof ApiKey);

        $apiToken = $this->usersClient->createApiToken($defaultApiKey->key);
        \assert($apiToken instanceof Token);

        $apiTokenUser = $this->usersClient->verifyApiToken($apiToken);
        \assert($apiTokenUser instanceof User);

        $label = (string) new Ulid();
        \assert('' !== $label);

        $job = $this->client->createJob($apiToken->token, $label);
        \assert($job instanceof Job);

        $jobEvent = $this->client->addEvent($job->token, $event);
        self::assertEquals($expectedJobEventCreator($label), $jobEvent);
    }

    /**
     * @return array<mixed>
     */
    public function addSuccessDataProvider(): array
    {
        return [
            'without related references, empty body' => [
                'event' => new Event(
                    1,
                    'event_type',
                    new ResourceReference('event_label_1', 'event_reference_1'),
                    []
                ),
                'expectedJobEventCreator' => function (string $jobLabel): JobEvent {
                    return new JobEvent(
                        $jobLabel,
                        new Event(
                            1,
                            'event_type',
                            new ResourceReference('event_label_1', 'event_reference_1'),
                            []
                        )
                    );
                },
            ],
            'without related references, non-empty body' => [
                'event' => new Event(
                    1,
                    'event_type',
                    new ResourceReference('event_label_1', 'event_reference_1'),
                    [
                        'key1' => 'value1',
                        'key2' => 'value2',
                    ]
                ),
                'expectedJobEventCreator' => function (string $jobLabel): JobEvent {
                    return new JobEvent(
                        $jobLabel,
                        new Event(
                            1,
                            'event_type',
                            new ResourceReference('event_label_1', 'event_reference_1'),
                            [
                                'key1' => 'value1',
                                'key2' => 'value2',
                            ]
                        )
                    );
                },
            ],
            'with single related reference, empty body' => [
                'event' => new Event(
                    1,
                    'event_type',
                    new ResourceReference('event_label_1', 'event_reference_1'),
                    [],
                    new ResourceReferenceCollection([
                        new ResourceReference('event_label_1', 'event_reference_1'),
                    ])
                ),
                'expectedJobEventCreator' => function (string $jobLabel): JobEvent {
                    return new JobEvent(
                        $jobLabel,
                        new Event(
                            1,
                            'event_type',
                            new ResourceReference('event_label_1', 'event_reference_1'),
                            [],
                            new ResourceReferenceCollection([
                                new ResourceReference('event_label_1', 'event_reference_1'),
                            ])
                        )
                    );
                },
            ],
            'with multiple related references, non-empty body' => [
                'event' => new Event(
                    1,
                    'event_type',
                    new ResourceReference('event_label_1', 'event_reference_1'),
                    [
                        'key3' => 'value3',
                        'key4' => 'value4',
                    ],
                    new ResourceReferenceCollection([
                        new ResourceReference('event_label_1', 'event_reference_1'),
                    ])
                ),
                'expectedJobEventCreator' => function (string $jobLabel): JobEvent {
                    return new JobEvent(
                        $jobLabel,
                        new Event(
                            1,
                            'event_type',
                            new ResourceReference('event_label_1', 'event_reference_1'),
                            [
                                'key3' => 'value3',
                                'key4' => 'value4',
                            ],
                            new ResourceReferenceCollection([
                                new ResourceReference('event_label_1', 'event_reference_1'),
                            ])
                        )
                    );
                },
            ],
        ];
    }
}
