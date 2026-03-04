<?php

declare(strict_types=1);

namespace Functional\Client;

use GuzzleHttp\Psr7\Response;
use SmartAssert\ResultsClient\Model\Event;
use SmartAssert\ResultsClient\Model\ResourceReference;
use SmartAssert\ResultsClient\Tests\Functional\Client\AbstractClientModelCreationTestCase;

class AddAndGetEventTest extends AbstractClientModelCreationTestCase
{
    public function testAddAndGetEventRequestProperties(): void
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

        $this->client->addAndGetEvent(
            $jobToken,
            new Event(1, 'job/started', new ResourceReference('label', 'reference'), [])
        );

        $request = $this->getLastRequest();
        self::assertSame('POST', $request->getMethod());
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->addAndGetEvent(
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
