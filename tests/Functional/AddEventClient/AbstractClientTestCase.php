<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Functional\AddEventClient;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ResultsClient\AddEventClient;
use SmartAssert\ResultsClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;
use SmartAssert\ResultsClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\ExceptionFactory\CurlExceptionFactory;
use SmartAssert\ServiceClient\ResponseFactory\ResponseFactory;
use webignition\HttpHistoryContainer\Container as HttpHistoryContainer;
use webignition\HttpHistoryContainer\MiddlewareFactory;

abstract class AbstractClientTestCase extends TestCase
{
    use CommonNonSuccessResponseDataProviderTrait;
    use NetworkErrorExceptionDataProviderTrait;

    protected MockHandler $mockHandler;
    protected AddEventClient $client;
    private HttpHistoryContainer $httpHistoryContainer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockHandler = new MockHandler();

        $httpFactory = new HttpFactory();

        $handlerStack = HandlerStack::create($this->mockHandler);

        $this->httpHistoryContainer = new HttpHistoryContainer();
        $handlerStack->push(MiddlewareFactory::create($this->httpHistoryContainer));

        $this->client = new AddEventClient(
            new ServiceClient(
                $httpFactory,
                $httpFactory,
                new HttpClient(['handler' => $handlerStack]),
                ResponseFactory::createFactory(),
                new CurlExceptionFactory(),
            )
        );
    }

    /**
     * @param class-string<\Throwable> $expectedExceptionClass
     */
    #[DataProvider('networkErrorExceptionDataProvider')]
    public function testClientActionThrowsNetworkException(
        ClientExceptionInterface|ResponseInterface $httpFixture,
        string $expectedExceptionClass,
    ): void {
        $this->mockHandler->append($httpFixture);

        $this->expectException($expectedExceptionClass);

        ($this->createClientActionCallable())();
    }

    #[DataProvider('commonNonSuccessResponseDataProvider')]
    public function testClientActionThrowsNonSuccessResponseException(ResponseInterface $httpFixture): void
    {
        $this->mockHandler->append($httpFixture);

        try {
            ($this->createClientActionCallable())();

            self::fail(NonSuccessResponseException::class . ' not thrown');
        } catch (NonSuccessResponseException $e) {
            self::assertSame($httpFixture, $e->getHttpResponse());
        }
    }

    protected function getLastRequest(): RequestInterface
    {
        $request = $this->httpHistoryContainer->getTransactions()->getRequests()->getLast();
        \assert($request instanceof RequestInterface);

        return $request;
    }

    abstract protected function createClientActionCallable(): callable;
}
