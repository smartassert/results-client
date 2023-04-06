<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Integration;

use SmartAssert\ResultsClient\Exception\InvalidJobTokenException;
use SmartAssert\ResultsClient\Model\Event\Event;
use SmartAssert\ResultsClient\Model\Event\JobEvent;
use SmartAssert\ResultsClient\Model\Event\ResourceReference;
use SmartAssert\ResultsClient\Model\Event\ResourceReferenceCollection;

class AddEventTest extends AbstractIntegrationTest
{
    public function testAddInvalidJobToken(): void
    {
        $jobToken = 'invalid token';

        self::expectExceptionObject(new InvalidJobTokenException($jobToken));

        self::$client->addEvent(
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
        $jobEvent = self::$client->addEvent(self::$user1Job->token, $event);
        self::assertEquals($expectedJobEventCreator(self::$user1JobLabel), $jobEvent);
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
                    \assert('' !== $jobLabel);

                    return new JobEvent(
                        $jobLabel,
                        (new Event(
                            1,
                            'event_type',
                            new ResourceReference('event_label_1', 'event_reference_1'),
                            [],
                            null,
                        ))->withJob($jobLabel)
                    );
                },
            ],
            'without related references, non-empty body' => [
                'event' => new Event(
                    2,
                    'event_type',
                    new ResourceReference('event_label_2', 'event_reference_2'),
                    [
                        'key1' => 'value1',
                        'key2' => 'value2',
                    ]
                ),
                'expectedJobEventCreator' => function (string $jobLabel): JobEvent {
                    \assert('' !== $jobLabel);

                    return new JobEvent(
                        $jobLabel,
                        (new Event(
                            2,
                            'event_type',
                            new ResourceReference('event_label_2', 'event_reference_2'),
                            [
                                'key1' => 'value1',
                                'key2' => 'value2',
                            ],
                            null,
                        ))->withJob($jobLabel)
                    );
                },
            ],
            'with single related reference, empty body' => [
                'event' => new Event(
                    3,
                    'event_type',
                    new ResourceReference('event_label_3', 'event_reference_3'),
                    [],
                    new ResourceReferenceCollection([
                        new ResourceReference('event_label_1', 'event_reference_1'),
                    ])
                ),
                'expectedJobEventCreator' => function (string $jobLabel): JobEvent {
                    \assert('' !== $jobLabel);

                    return new JobEvent(
                        $jobLabel,
                        (new Event(
                            3,
                            'event_type',
                            new ResourceReference('event_label_3', 'event_reference_3'),
                            [],
                            new ResourceReferenceCollection([
                                new ResourceReference('event_label_1', 'event_reference_1'),
                            ]),
                        ))->withJob($jobLabel)
                    );
                },
            ],
            'with multiple related references, non-empty body' => [
                'event' => new Event(
                    4,
                    'event_type',
                    new ResourceReference('event_label_4', 'event_reference_4'),
                    [
                        'key3' => 'value3',
                        'key4' => 'value4',
                    ],
                    new ResourceReferenceCollection([
                        new ResourceReference('event_label_1', 'event_reference_1'),
                    ])
                ),
                'expectedJobEventCreator' => function (string $jobLabel): JobEvent {
                    \assert('' !== $jobLabel);

                    return new JobEvent(
                        $jobLabel,
                        (new Event(
                            4,
                            'event_type',
                            new ResourceReference('event_label_4', 'event_reference_4'),
                            [
                                'key3' => 'value3',
                                'key4' => 'value4',
                            ],
                            new ResourceReferenceCollection([
                                new ResourceReference('event_label_1', 'event_reference_1'),
                            ]),
                        ))->withJob($jobLabel)
                    );
                },
            ],
        ];
    }
}
