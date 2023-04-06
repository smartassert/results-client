<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use SmartAssert\ResultsClient\Model\Event;
use SmartAssert\ResultsClient\Model\ResourceReference;

class AddEventTest extends AbstractClientModelCreationTest
{
    public function testAddEventRequestProperties(): void
    {
        $this->mockHandler->append(new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode([
                'job' => 'job label content',
                'sequence_number' => 1,
                'type' => 'job/started',
                'label' => 'reference label',
                'reference' => 'reference reference',
                'body' => [],
            ])
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

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->addEvent(
                'job token',
                new Event(1, 'job/started', new ResourceReference('label', 'reference'), [])
            );
        };
    }

    protected function getExpectedModelClass(): string
    {
        return Event::class;
    }
}
