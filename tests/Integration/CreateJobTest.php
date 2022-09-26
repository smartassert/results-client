<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Tests\Integration;

use SmartAssert\ResultsClient\Model\Job;
use SmartAssert\UsersClient\Client as UsersClient;
use SmartAssert\UsersClient\Model\ApiKey;
use SmartAssert\UsersClient\Model\Token;
use SmartAssert\UsersClient\Model\User;
use SmartAssert\UsersClient\ObjectFactory as UsersObjectFactory;
use Symfony\Component\Uid\Ulid;

class CreateJobTest extends AbstractIntegrationTest
{
    private UsersClient $usersClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usersClient = new UsersClient('http://localhost:9080', $this->serviceClient, new UsersObjectFactory());
    }

    public function testCreateSuccess(): void
    {
        $frontendToken = $this->usersClient->createFrontendToken(self::USER_EMAIL, self::USER_PASSWORD);
        \assert($frontendToken instanceof Token);

        $frontendTokenUser = $this->usersClient->verifyFrontendToken($frontendToken);
        \assert($frontendTokenUser instanceof User);

        $apiKeys = $this->usersClient->listUserApiKeys($frontendToken);
        $defaultApiKey = $apiKeys->getDefault();
        \assert($defaultApiKey instanceof ApiKey);

        $apiToken = $this->usersClient->createApiToken($defaultApiKey->key);
        \assert($apiToken instanceof Token);

        $apiTokenUser = $this->usersClient->verifyApiToken($apiToken);
        \assert($apiTokenUser instanceof User);

        $label = (string) new Ulid();
        \assert('' !== $label);

        $job = $this->client->createJob($apiToken->token, $label);
        self::assertInstanceOf(Job::class, $job);
        self::assertSame($label, $job->label);
        self::assertTrue(Ulid::isValid($job->token));
    }
}
