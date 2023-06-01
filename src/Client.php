<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\ResultsClient\Exception\InvalidJobTokenException;
use SmartAssert\ResultsClient\Model\Event;
use SmartAssert\ResultsClient\Model\EventInterface;
use SmartAssert\ResultsClient\Model\Job;
use SmartAssert\ServiceClient\Authentication\BearerAuthentication;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\Exception\CurlExceptionInterface;
use SmartAssert\ServiceClient\Exception\HttpResponseExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Payload\JsonPayload;
use SmartAssert\ServiceClient\Request;
use SmartAssert\ServiceClient\Response\JsonResponse;

class Client
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly ServiceClient $serviceClient,
        private readonly EventFactory $eventFactory,
    ) {
    }

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
     */
    public function createJob(string $token, string $label): Job
    {
        $response = $this->serviceClient->sendRequest(
            (new Request('POST', $this->createUrl('/job/' . $label)))
                ->withAuthentication(new BearerAuthentication($token))
        );

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        if (!$response instanceof JsonResponse) {
            throw InvalidResponseTypeException::create($response, JsonResponse::class);
        }

        $responseDataInspector = new ArrayInspector($response->getData());

        $label = $responseDataInspector->getNonEmptyString('label');
        $token = $responseDataInspector->getNonEmptyString('token');

        if (null === $label || null === $token) {
            throw InvalidModelDataException::fromJsonResponse(Job::class, $response);
        }

        return new Job($label, $token);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws NetworkExceptionInterface
     * @throws HttpResponseExceptionInterface
     * @throws CurlExceptionInterface
     * @throws InvalidResponseDataException
     * @throws InvalidJobTokenException
     * @throws InvalidModelDataException
     * @throws InvalidResponseTypeException
     */
    public function addEvent(string $jobToken, EventInterface $event): EventInterface
    {
        $response = $this->serviceClient->sendRequest(
            (new Request('POST', $this->createUrl('/event/add/' . $jobToken)))
                ->withAuthentication(new BearerAuthentication($jobToken))
                ->withPayload(new JsonPayload($event->toArray()))
        );

        if (!$response->isSuccessful()) {
            if (404 === $response->getStatusCode()) {
                throw new InvalidJobTokenException($jobToken);
            }

            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        if (!$response instanceof JsonResponse) {
            throw InvalidResponseTypeException::create($response, JsonResponse::class);
        }

        $responseDataInspector = new ArrayInspector($response->getData());

        $createdEvent = $this->eventFactory->create($responseDataInspector);

        if (null === $createdEvent) {
            throw InvalidModelDataException::fromJsonResponse(Event::class, $response);
        }

        return $createdEvent;
    }

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
     */
    public function listEvents(string $token, string $jobLabel, ?string $reference, ?string $type): array
    {
        $url = $this->createUrl('/event/list/' . $jobLabel);

        $queryParameters = [];
        if (is_string($reference)) {
            $queryParameters['reference'] = $reference;
        }

        if (is_string($type)) {
            $queryParameters['type'] = $type;
        }

        if ([] !== $queryParameters) {
            $url .= '?' . http_build_query($queryParameters);
        }

        $response = $this->serviceClient->sendRequest(
            (new Request('GET', $url))->withAuthentication(new BearerAuthentication($token))
        );

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        if (!$response instanceof JsonResponse) {
            throw InvalidResponseTypeException::create($response, JsonResponse::class);
        }

        $events = [];

        $responseDataInspector = new ArrayInspector($response->getData());

        $responseDataInspector->each(function (int|string $key, mixed $value) use (&$events) {
            if (is_array($value)) {
                $event = $this->eventFactory->create(new ArrayInspector($value));
                if ($event instanceof EventInterface) {
                    $events[] = $event;
                }
            }
        });

        return $events;
    }

    /**
     * @param non-empty-string $path
     *
     * @return non-empty-string
     */
    private function createUrl(string $path): string
    {
        return rtrim($this->baseUrl, '/') . $path;
    }
}
