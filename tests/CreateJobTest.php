<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests;

use Psr\Http\Message\RequestInterface;
use SmartAssert\ResultsClient\Model\Job;
use Symfony\Component\Uid\Ulid;

class CreateJobTest extends AbstractIntegrationTest
{
    public function testCreateWithAuthenticationFromHttpRequest(): void
    {
        $jobLabel = (string) new Ulid();
        \assert('' !== $jobLabel);

        $httpRequest = \Mockery::mock(RequestInterface::class);
        $httpRequest
            ->shouldReceive('getHeaderLine')
            ->with('Authorization')
            ->andReturn('Bearer ' . self::$user1ApiToken->token)
        ;

        $job = self::$client->createJobWithAuthenticationFromHttpRequest($httpRequest, $jobLabel);
        self::assertInstanceOf(Job::class, $job);
    }
}
