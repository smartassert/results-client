<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Integration;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\TestCase;
use SmartAssert\ResultsClient\Client;
use SmartAssert\ResultsClient\ObjectFactory;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\ResponseDecoder;

abstract class AbstractIntegrationTest extends TestCase
{
    protected const USER_EMAIL = 'user@example.com';
    protected const USER_PASSWORD = 'password';

    protected Client $client;
    protected ServiceClient $serviceClient;

    protected function setUp(): void
    {
        parent::setUp();

        $httpFactory = new HttpFactory();
        $this->serviceClient = new ServiceClient($httpFactory, $httpFactory, new HttpClient(), new ResponseDecoder());

        $this->client = new Client('http://localhost:9081', $this->serviceClient, new ObjectFactory());
    }
}
