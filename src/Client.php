<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient;

use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\ResultsClient\Exception\InvalidJobTokenException;
use SmartAssert\ResultsClient\Model\Event\Event;
use SmartAssert\ResultsClient\Model\Event\JobEvent;
use SmartAssert\ResultsClient\Model\Job;
use SmartAssert\ServiceClient\Authentication\BearerAuthentication;
use SmartAssert\ServiceClient\Client as ServiceClient;
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
        private readonly JobEventFactory $jobEventFactory,
    ) {
    }

    /**
     * @param non-empty-string $token
     * @param non-empty-string $label
     *
     * @throws ClientExceptionInterface
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
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
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidJobTokenException
     * @throws InvalidModelDataException
     * @throws InvalidResponseTypeException
     */
    public function addEvent(string $jobToken, Event $event): ?JobEvent
    {
        $response = $this->serviceClient->sendRequest(
            (new Request('POST', $this->createUrl('/event/add/' . $jobToken)))
                ->withAuthentication(new BearerAuthentication($jobToken))
                ->withPayload(new JsonPayload($event))
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

        $jobEvent = $this->jobEventFactory->create($responseDataInspector);

        if (null === $jobEvent) {
            throw InvalidModelDataException::fromJsonResponse(JobEvent::class, $response);
        }

        return $jobEvent;
    }

    /**
     * @param non-empty-string $token
     * @param non-empty-string $jobLabel
     * @param non-empty-string $eventReference
     *
     * @return JobEvent[]
     *
     * @throws ClientExceptionInterface
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidResponseTypeException
     */
    public function listEvents(string $token, string $jobLabel, string $eventReference, ?string $type = null): array
    {
        $url = $this->createUrl('/event/list/' . $jobLabel . '/' . $eventReference);
        if (is_string($type)) {
            $url .= '/' . $type;
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
                $eventDataInspector = new ArrayInspector($value);

                $jobEvent = $this->jobEventFactory->create($eventDataInspector);

                if ($jobEvent instanceof JobEvent) {
                    $events[] = $jobEvent;
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
