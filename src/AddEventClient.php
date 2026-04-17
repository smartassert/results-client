<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient;

use SmartAssert\ResultsClient\Exception\InvalidJobTokenException;
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

    public function add(string $baseUrl, string $jobToken, EventInterface $event): bool
    {
        $url = rtrim($baseUrl, '/') . '/event/add/' . $jobToken;

        $request = new Request('POST', $url)
            ->withPayload(new JsonPayload($event->toArray()))
        ;

        try {
            $this->serviceClient->sendRequest($request);
        } catch (NonSuccessResponseException $e) {
            if (404 === $e->getStatusCode()) {
                throw new InvalidJobTokenException($jobToken);
            }

            throw $e;
        }

        return true;
    }
}
