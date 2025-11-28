<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use SmartAssert\ResultsClient\Exception\InvalidJobTokenException;
use SmartAssert\ResultsClient\Model\EventInterface;
use SmartAssert\ResultsClient\Model\Job;
use SmartAssert\ResultsClient\Model\JobState;
use SmartAssert\ServiceClient\Exception\CurlExceptionInterface;
use SmartAssert\ServiceClient\Exception\HttpResponseExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;

interface ClientInterface
{
    /**
     * @param non-empty-string $token
     * @param non-empty-string $label
     *
     * @throws ClientExceptionInterface
     * @throws NetworkExceptionInterface
     * @throws HttpResponseExceptionInterface
     * @throws CurlExceptionInterface
     * @throws InvalidResponseDataException
     * @throws InvalidModelDataException
     * @throws InvalidResponseTypeException
     * @throws UnauthorizedException
     */
    public function createJob(string $token, string $label): Job;

    /**
     * @param non-empty-string $token
     * @param non-empty-string $label
     *
     * @throws ClientExceptionInterface
     * @throws NetworkExceptionInterface
     * @throws HttpResponseExceptionInterface
     * @throws CurlExceptionInterface
     * @throws InvalidResponseDataException
     * @throws InvalidModelDataException
     * @throws InvalidResponseTypeException
     * @throws UnauthorizedException
     */
    public function getJobStatus(string $token, string $label): JobState;

    /**
     * @throws ClientExceptionInterface
     * @throws NetworkExceptionInterface
     * @throws HttpResponseExceptionInterface
     * @throws CurlExceptionInterface
     * @throws InvalidResponseDataException
     * @throws InvalidJobTokenException
     * @throws InvalidModelDataException
     * @throws InvalidResponseTypeException
     * @throws UnauthorizedException
     */
    public function addEvent(string $jobToken, EventInterface $event): EventInterface;

    /**
     * @param non-empty-string      $token
     * @param non-empty-string      $jobLabel
     * @param null|non-empty-string $reference
     * @param null|non-empty-string $type
     *
     * @return EventInterface[]
     *
     * @throws ClientExceptionInterface
     * @throws NetworkExceptionInterface
     * @throws HttpResponseExceptionInterface
     * @throws CurlExceptionInterface
     * @throws InvalidResponseDataException
     * @throws InvalidResponseTypeException
     * @throws UnauthorizedException
     */
    public function listEvents(string $token, string $jobLabel, ?string $reference, ?string $type): array;
}
