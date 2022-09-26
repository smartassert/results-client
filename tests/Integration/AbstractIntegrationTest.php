<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Integration;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\TestCase;
use SmartAssert\ResultsClient\Client;
use SmartAssert\ResultsClient\ObjectFactory;
use SmartAssert\ServiceClient\Client as ServiceClient;

abstract class AbstractIntegrationTest extends TestCase
{
    protected const USER_EMAIL = 'user@example.com';
    protected const USER_PASSWORD = 'password';

    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $httpFactory = new HttpFactory();

        $this->client = new Client(
            'http://localhost:9081',
            new ServiceClient($httpFactory, $httpFactory, new HttpClient()),
            new ObjectFactory(),
        );
    }
}
