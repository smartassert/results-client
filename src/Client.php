<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\ResultsClient\Model\EventInterface;
use SmartAssert\ResultsClient\Model\Job;
use SmartAssert\ServiceClient\Authentication\BearerAuthentication;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\Exception\CurlExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\ServiceClient\Payload\UrlEncodedPayload;
use SmartAssert\ServiceClient\Request;
use SmartAssert\ServiceClient\Response\JsonResponse;

readonly class Client implements ClientInterface
{
    public function __construct(
        private string $baseUrl,
        private ServiceClient $serviceClient,
        private EventFactory $eventFactory,
        private JobFactory $jobFactory,
    ) {}

    public function createJob(string $token, string $label, ?string $notifyUrl): Job
    {
        $response = $this->makeJobCreationRequest($token, $label, $notifyUrl);

        return $this->createJobModel($response);
    }

    public function getJobStatus(string $token, string $label): Job
    {
        $response = $this->makeJobRetrievalRequest($token, $label);

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
        $job = $this->jobFactory->create($response->getData());
        if (null === $job) {
            throw InvalidModelDataException::fromJsonResponse(Job::class, $response);
        }

        return $job;
    }

    /**
     * @param non-empty-string  $token
     * @param non-empty-string  $label
     * @param ?non-empty-string $notifyUrl
     *
     * @throws CurlExceptionInterface
     * @throws NetworkExceptionInterface
     * @throws RequestExceptionInterface
     * @throws ClientExceptionInterface
     * @throws NonSuccessResponseException
     * @throws InvalidResponseTypeException
     * @throws UnauthorizedException
     */
    private function makeJobCreationRequest(string $token, string $label, ?string $notifyUrl): JsonResponse
    {
        $request = new Request('POST', $this->createUrl('/job/' . $label))
            ->withAuthentication(new BearerAuthentication($token))
        ;

        if (is_string($notifyUrl)) {
            $request = $request->withPayload(
                new UrlEncodedPayload([
                    'notify_url' => $notifyUrl,
                ])
            );
        }

        return $this->serviceClient->sendRequestForJson($request);
    }

    /**
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
    private function makeJobRetrievalRequest(string $token, string $label): JsonResponse
    {
        return $this->serviceClient->sendRequestForJson(
            new Request('GET', $this->createUrl('/job/' . $label))
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
