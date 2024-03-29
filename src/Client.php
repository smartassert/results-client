<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\ResultsClient\Exception\InvalidJobTokenException;
use SmartAssert\ResultsClient\Model\Event;
use SmartAssert\ResultsClient\Model\EventInterface;
use SmartAssert\ResultsClient\Model\Job;
use SmartAssert\ResultsClient\Model\JobState;
use SmartAssert\ServiceClient\Authentication\BearerAuthentication;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\Exception\CurlExceptionInterface;
use SmartAssert\ServiceClient\Exception\HttpResponseExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\ServiceClient\Payload\JsonPayload;
use SmartAssert\ServiceClient\Request;
use SmartAssert\ServiceClient\Response\JsonResponse;

readonly class Client
{
    public function __construct(
        private string $baseUrl,
        private ServiceClient $serviceClient,
        private EventFactory $eventFactory,
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
     * @throws UnauthorizedException
     */
    public function createJob(string $token, string $label): Job
    {
        $response = $this->makeJobRequest('POST', $token, $label);

        $responseDataInspector = new ArrayInspector($response->getData());

        $label = $responseDataInspector->getNonEmptyString('label');
        $token = $responseDataInspector->getNonEmptyString('token');
        $state = $responseDataInspector->getNonEmptyString('state');

        if (null === $label || null === $token || null === $state) {
            throw InvalidModelDataException::fromJsonResponse(Job::class, $response);
        }

        $endState = $responseDataInspector->getNonEmptyString('endState');

        return new Job($label, $token, new JobState($state, $endState));
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
     * @throws UnauthorizedException
     */
    public function getJobStatus(string $token, string $label): JobState
    {
        $response = $this->makeJobRequest('GET', $token, $label);

        $responseDataInspector = new ArrayInspector($response->getData());

        $state = $responseDataInspector->getNonEmptyString('state');
        $endState = $responseDataInspector->getNonEmptyString('end_state');

        if (null === $state) {
            throw InvalidModelDataException::fromJsonResponse(JobState::class, $response);
        }

        return new JobState($state, $endState);
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
     * @throws UnauthorizedException
     */
    public function addEvent(string $jobToken, EventInterface $event): EventInterface
    {
        try {
            $response = $this->serviceClient->sendRequestForJson(
                (new Request('POST', $this->createUrl('/event/add/' . $jobToken)))
                    ->withAuthentication(new BearerAuthentication($jobToken))
                    ->withPayload(new JsonPayload($event->toArray()))
            );
        } catch (NonSuccessResponseException $e) {
            if (404 === $e->getStatusCode()) {
                throw new InvalidJobTokenException($jobToken);
            }

            throw $e;
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
     * @throws UnauthorizedException
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

        $response = $this->serviceClient->sendRequestForJson(
            (new Request('GET', $url))->withAuthentication(new BearerAuthentication($token))
        );

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
     * @param non-empty-string $method
     * @param non-empty-string $token
     * @param non-empty-string $label
     *
     * @throws CurlExceptionInterface
     * @throws NetworkExceptionInterface
     * @throws RequestExceptionInterface
     * @throws ClientExceptionInterface
     * @throws NonSuccessResponseException
     * @throws InvalidResponseTypeException
     * @throws UnauthorizedException
     */
    private function makeJobRequest(string $method, string $token, string $label): JsonResponse
    {
        return $this->serviceClient->sendRequestForJson(
            (new Request($method, $this->createUrl('/job/' . $label)))
                ->withAuthentication(new BearerAuthentication($token))
        );
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
