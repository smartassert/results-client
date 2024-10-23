<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Integration;

use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\ResultsClient\Exception\InvalidJobTokenException;
use SmartAssert\ResultsClient\Model\Event;
use SmartAssert\ResultsClient\Model\EventInterface;
use SmartAssert\ResultsClient\Model\ResourceReference;
use SmartAssert\ResultsClient\Model\ResourceReferenceCollection;

class AddEventTest extends AbstractIntegrationTestCase
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
     * @param callable(string): EventInterface $expectedEventCreator
     */
    #[DataProvider('addSuccessDataProvider')]
    public function testAddSuccess(Event $event, callable $expectedEventCreator): void
    {
        $jobEvent = self::$client->addEvent(self::$user1Job->token, $event);
        self::assertEquals($expectedEventCreator(self::$user1JobLabel), $jobEvent);
    }

    /**
     * @return array<mixed>
     */
    public static function addSuccessDataProvider(): array
    {
        return [
            'without related references, empty body' => [
                'event' => new Event(
                    1,
                    'event_type',
                    new ResourceReference('event_label_1', 'event_reference_1'),
                    []
                ),
                'expectedEventCreator' => function (string $jobLabel): EventInterface {
                    \assert('' !== $jobLabel);

                    return (new Event(
                        1,
                        'event_type',
                        new ResourceReference('event_label_1', 'event_reference_1'),
                        [],
                    ))->withJob($jobLabel);
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
                'expectedEventCreator' => function (string $jobLabel): EventInterface {
                    \assert('' !== $jobLabel);

                    return (new Event(
                        2,
                        'event_type',
                        new ResourceReference('event_label_2', 'event_reference_2'),
                        [
                            'key1' => 'value1',
                            'key2' => 'value2',
                        ],
                    ))->withJob($jobLabel);
                },
            ],
            'with single related reference, empty body' => [
                'event' => (new Event(
                    3,
                    'event_type',
                    new ResourceReference('event_label_3', 'event_reference_3'),
                    [],
                ))->withRelatedReferences(
                    new ResourceReferenceCollection([
                        new ResourceReference('event_label_1', 'event_reference_1'),
                    ])
                ),
                'expectedEventCreator' => function (string $jobLabel): EventInterface {
                    \assert('' !== $jobLabel);

                    return (new Event(
                        3,
                        'event_type',
                        new ResourceReference('event_label_3', 'event_reference_3'),
                        [],
                    ))
                        ->withJob($jobLabel)
                        ->withRelatedReferences(
                            new ResourceReferenceCollection([
                                new ResourceReference('event_label_1', 'event_reference_1'),
                            ])
                        )
                    ;
                },
            ],
            'with multiple related references, non-empty body' => [
                'event' => (new Event(
                    4,
                    'event_type',
                    new ResourceReference('event_label_4', 'event_reference_4'),
                    [
                        'key3' => 'value3',
                        'key4' => 'value4',
                    ],
                ))->withRelatedReferences(new ResourceReferenceCollection([
                    new ResourceReference('event_label_1', 'event_reference_1'),
                ])),
                'expectedEventCreator' => function (string $jobLabel): EventInterface {
                    \assert('' !== $jobLabel);

                    return (new Event(
                        4,
                        'event_type',
                        new ResourceReference('event_label_4', 'event_reference_4'),
                        [
                            'key3' => 'value3',
                            'key4' => 'value4',
                        ],
                    ))
                        ->withJob($jobLabel)
                        ->withRelatedReferences(
                            new ResourceReferenceCollection([
                                new ResourceReference('event_label_1', 'event_reference_1'),
                            ])
                        )
                    ;
                },
            ],
        ];
    }
}
