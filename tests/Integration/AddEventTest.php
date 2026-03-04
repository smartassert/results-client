<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Integration;

use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\ResultsClient\Exception\InvalidJobTokenException;
use SmartAssert\ResultsClient\Model\Event;
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

    #[DataProvider('addSuccessDataProvider')]
    public function testAddSuccess(Event $event): void
    {
        self::assertTrue(
            self::$client->addEvent(self::$user1Job->token, $event),
        );
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
            ],
            'with single related reference, empty body' => [
                'event' => new Event(
                    3,
                    'event_type',
                    new ResourceReference('event_label_3', 'event_reference_3'),
                    [],
                )->withRelatedReferences(
                    new ResourceReferenceCollection([
                        new ResourceReference('event_label_1', 'event_reference_1'),
                    ])
                ),
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
                )->withRelatedReferences(new ResourceReferenceCollection([
                    new ResourceReference('event_label_1', 'event_reference_1'),
                ])),
            ],
        ];
    }
}
