<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient;

use SmartAssert\ResultsClient\Exception\InvalidAddEventUrlException;
use SmartAssert\ResultsClient\Model\EventInterface;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Payload\JsonPayload;
use SmartAssert\ServiceClient\Request;

readonly class AddEventClient implements AddEventClientInterface
{
    public function __construct(
        private ServiceClient $serviceClient,
    ) {}

    public function add(string $addEventUrl, EventInterface $event): bool
    {
        $request = new Request('POST', $addEventUrl)
            ->withPayload(new JsonPayload($event->toArray()))
        ;

        try {
            $this->serviceClient->sendRequest($request);
        } catch (NonSuccessResponseException $e) {
            throw new InvalidAddEventUrlException($addEventUrl, $e);
        }

        return true;
    }
}
