<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;

class ListEventsTest extends AbstractClientTestCase
{
    public function testListEventsRequestProperties(): void
    {
        $this->mockHandler->append(new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode([])
        ));

        $apiKey = 'api key value';

        $this->client->listEvents($apiKey, 'job label', 'event reference', 'event type');

        $request = $this->getLastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('Bearer ' . $apiKey, $request->getHeaderLine('authorization'));
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->listEvents('api token', 'job label', 'event reference', 'event type');
        };
    }
}
