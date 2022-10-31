<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use SmartAssert\ResultsClient\Model\Event\Event;
use SmartAssert\ResultsClient\Model\Event\ResourceReference;

class AddEventTest extends AbstractClientTest
{
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

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->addEvent(
                'job token',
                new Event(1, 'job/started', new ResourceReference('label', 'reference'), [])
            );
        };
    }
}
