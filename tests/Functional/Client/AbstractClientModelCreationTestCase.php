<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;

abstract class AbstractClientModelCreationTestCase extends AbstractClientTestCase
{
    public function testClientActionThrowsInvalidModelDataException(): void
    {
        $responsePayload = ['key' => 'value'];
        $response = new Response(200, ['content-type' => 'application/json'], (string) json_encode($responsePayload));

        $this->mockHandler->append($response);

        try {
            ($this->createClientActionCallable())();
            self::fail(InvalidModelDataException::class . ' not thrown');
        } catch (InvalidModelDataException $e) {
            self::assertSame($this->getExpectedModelClass(), $e->class);
            self::assertSame($response, $e->response);
            self::assertSame($responsePayload, $e->payload);
        }
    }

    /**
     * @return class-string
     */
    abstract protected function getExpectedModelClass(): string;
}
