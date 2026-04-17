<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Functional\AddEventClient;

use GuzzleHttp\Psr7\Response;
use SmartAssert\ResultsClient\Model\Event;
use SmartAssert\ResultsClient\Model\ResourceReference;

class AddEventTest extends AbstractClientTestCase
{
    public function testAddEventRequestProperties(): void
    {
        $baseUrl = 'https://' . md5((string) rand());

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

        $jobToken = md5((string) rand());

        $this->client->add(
            $baseUrl,
            $jobToken,
            new Event(1, 'job/started', new ResourceReference('label', 'reference'), [])
        );

        $request = $this->getLastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame($baseUrl . '/event/add/' . $jobToken, $request->getUri()->__toString());
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->add(
                'https://' . md5((string) rand()),
                'job token',
                new Event(1, 'job/started', new ResourceReference('label', 'reference'), [])
            );
        };
    }
}
