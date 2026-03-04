<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ResultsClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;

abstract class AbstractClientModelCreationTestCase extends AbstractClientTestCase
{
    use InvalidJsonResponseExceptionDataProviderTrait;

    /**
     * @param class-string<\Throwable> $expectedExceptionClass
     */
    #[DataProvider('invalidJsonResponseExceptionDataProvider')]
    public function testClientActionThrowsResponseException(
        ClientExceptionInterface|ResponseInterface $httpFixture,
        string $expectedExceptionClass,
    ): void {
        $this->mockHandler->append($httpFixture);

        $this->expectException($expectedExceptionClass);

        ($this->createClientActionCallable())();
    }

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
            self::assertSame($response, $e->getHttpResponse());
            self::assertSame($responsePayload, $e->payload);
        }
    }

    /**
     * @return class-string
     */
    abstract protected function getExpectedModelClass(): string;
}
