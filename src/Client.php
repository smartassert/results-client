<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\ResultsClient\Model\EventInterface;
use SmartAssert\ResultsClient\Model\Job;
use SmartAssert\ResultsClient\Model\JobState;
use SmartAssert\ResultsClient\Model\MetaState;
use SmartAssert\ServiceClient\Authentication\BearerAuthentication;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\Exception\CurlExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\ServiceClient\Request;
use SmartAssert\ServiceClient\Response\JsonResponse;

readonly class Client implements ClientInterface
{
    public function __construct(
        private string $baseUrl,
        private ServiceClient $serviceClient,
        private EventFactory $eventFactory,
    ) {}

    public function createJob(string $token, string $label): Job
    {
        $response = $this->makeJobRequest('POST', $token, $label);

        return $this->createJobModel($response);
    }

    public function getJobStatus(string $token, string $label): Job
    {
        $response = $this->makeJobRequest('GET', $token, $label);

        return $this->createJobModel($response);
    }

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
     * @throws InvalidModelDataException
     * @throws InvalidResponseDataException
     */
    private function createJobModel(JsonResponse $response): Job
    {
        $responseDataInspector = new ArrayInspector($response->getData());

        $label = $responseDataInspector->getNonEmptyString('label');
        $state = $responseDataInspector->getNonEmptyString('state');
        $eventAddUrl = $responseDataInspector->getNonEmptyString('event_add_url');
        $hasEvents = $responseDataInspector->getBoolean('has_events');

        if (null === $label || null === $state || null === $eventAddUrl || null === $hasEvents) {
            throw InvalidModelDataException::fromJsonResponse(Job::class, $response);
        }

        $endState = $responseDataInspector->getNonEmptyString('end_state');
        $metaState = $this->getJobMetaState($responseDataInspector);

        $previousStates = $responseDataInspector->getNonEmptyStringArray('previous_states');

        return new Job($label, $eventAddUrl, new JobState($state, $endState, $metaState), $hasEvents, $previousStates);
    }

    private function getJobMetaState(ArrayInspector $inspector): MetaState
    {
        $metaStateInspector = new ArrayInspector(
            $inspector->getArray('meta_state')
        );

        return new MetaState(
            $metaStateInspector->getBoolean('ended') ?? false,
            $metaStateInspector->getBoolean('succeeded') ?? false,
            $metaStateInspector->getBoolean('pending') ?? true,
        );
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
