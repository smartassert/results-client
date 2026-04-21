<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use SmartAssert\ResultsClient\Exception\InvalidAddEventUrlException;
use SmartAssert\ResultsClient\Model\EventInterface;
use SmartAssert\ServiceClient\Exception\CurlExceptionInterface;
use SmartAssert\ServiceClient\Exception\HttpResponseExceptionInterface;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;

interface AddEventClientInterface
{
    /**
     * @throws ClientExceptionInterface
     * @throws NetworkExceptionInterface
     * @throws HttpResponseExceptionInterface
     * @throws CurlExceptionInterface
     * @throws InvalidAddEventUrlException
     * @throws UnauthorizedException
     */
    public function add(string $addEventUrl, EventInterface $event): bool;
}
