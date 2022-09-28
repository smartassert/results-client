<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\TestCase;
use SmartAssert\ResultsClient\Client;
use SmartAssert\ResultsClient\Model\Job;
use SmartAssert\ResultsClient\ObjectFactory;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\ResponseDecoder;
use SmartAssert\UsersClient\Client as UsersClient;
use SmartAssert\UsersClient\Model\ApiKey;
use SmartAssert\UsersClient\Model\Token;
use SmartAssert\UsersClient\Model\User;
use SmartAssert\UsersClient\ObjectFactory as UsersObjectFactory;
use Symfony\Component\Uid\Ulid;

abstract class AbstractIntegrationTest extends TestCase
{
    protected const USER1_EMAIL = 'user1@example.com';
    protected const USER1_PASSWORD = 'password';
    protected const USER2_EMAIL = 'user1@example.com';
    protected const USER2_PASSWORD = 'password';

    protected static Client $client;

    /**
     * @var non-empty-string
     */
    protected static string $jobLabel;
    protected static Job $job;

    public static function setUpBeforeClass(): void
    {
        $httpFactory = new HttpFactory();
        $serviceClient = new ServiceClient($httpFactory, $httpFactory, new HttpClient(), new ResponseDecoder());

        $usersClient = new UsersClient('http://localhost:9080', $serviceClient, new UsersObjectFactory());
        self::$client = new Client('http://localhost:9081', $serviceClient, new ObjectFactory());

        $frontendToken = $usersClient->createFrontendToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        \assert($frontendToken instanceof Token);

        $frontendTokenUser = $usersClient->verifyFrontendToken($frontendToken);
        \assert($frontendTokenUser instanceof User);

        $apiKeys = $usersClient->listUserApiKeys($frontendToken);
        $defaultApiKey = $apiKeys->getDefault();
        \assert($defaultApiKey instanceof ApiKey);

        $apiToken = $usersClient->createApiToken($defaultApiKey->key);
        \assert($apiToken instanceof Token);

        $apiTokenUser = $usersClient->verifyApiToken($apiToken);
        \assert($apiTokenUser instanceof User);

        $jobLabel = (string) new Ulid();
        \assert('' !== $jobLabel);
        self::$jobLabel = $jobLabel;

        $job = self::$client->createJob($apiToken->token, $jobLabel);
        \assert($job instanceof Job);
        self::$job = $job;
    }
}
