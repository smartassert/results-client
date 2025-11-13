<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Integration;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\TestCase;
use SmartAssert\ResultsClient\Client;
use SmartAssert\ResultsClient\EventFactory;
use SmartAssert\ResultsClient\Model\Job;
use SmartAssert\ResultsClient\ResourceReferenceFactory;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\ExceptionFactory\CurlExceptionFactory;
use SmartAssert\ServiceClient\ResponseFactory\ResponseFactory;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use SmartAssert\TestAuthenticationProviderBundle\ApiTokenProvider;
use SmartAssert\TestAuthenticationProviderBundle\FrontendTokenProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractIntegrationTestCase extends TestCase
{
    protected const USER1_EMAIL = 'user1@example.com';
    protected const USER1_PASSWORD = 'password';
    protected const USER2_EMAIL = 'user2@example.com';
    protected const USER2_PASSWORD = 'password';

    protected static Client $client;

    /**
     * @var non-empty-string
     */
    protected static string $user1ApiToken;

    /**
     * @var non-empty-string
     */
    protected static string $user1JobLabel;
    protected static Job $user1Job;

    public static function setUpBeforeClass(): void
    {
        self::$client = new Client(
            'http://localhost:9081',
            self::createServiceClient(),
            new EventFactory(
                new ResourceReferenceFactory()
            ),
        );
        self::$user1ApiToken = self::createUserApiToken(self::USER1_EMAIL);
        self::$user1JobLabel = (string) new Ulid();
        self::$user1Job = self::$client->createJob(self::$user1ApiToken, self::$user1JobLabel);
    }

    /**
     * @param non-empty-string $email
     *
     * @return non-empty-string
     */
    protected static function createUserApiToken(string $email): string
    {
        $usersBaseUrl = 'http://localhost:9080';
        $httpClient = new HttpClient();

        $frontendTokenProvider = new FrontendTokenProvider(
            [
                self::USER1_EMAIL => self::USER1_PASSWORD,
                self::USER2_EMAIL => self::USER2_PASSWORD,
            ],
            $usersBaseUrl,
            $httpClient
        );
        $apiKeyProvider = new ApiKeyProvider($usersBaseUrl, $httpClient, $frontendTokenProvider);
        $apiTokenProvider = new ApiTokenProvider($usersBaseUrl, $httpClient, $apiKeyProvider);

        return $apiTokenProvider->get($email);
    }

    private static function createServiceClient(): ServiceClient
    {
        $httpFactory = new HttpFactory();

        return new ServiceClient(
            $httpFactory,
            $httpFactory,
            new HttpClient(),
            ResponseFactory::createFactory(),
            new CurlExceptionFactory(),
        );
    }
}
