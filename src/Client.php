<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient;

use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ResultsClient\Exception\InvalidJobTokenException;
use SmartAssert\ResultsClient\Model\Event\Event;
use SmartAssert\ResultsClient\Model\Event\JobEvent;
use SmartAssert\ResultsClient\Model\Job;
use SmartAssert\ServiceClient\Authentication\BearerAuthentication;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\Exception\InvalidResponseContentException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Payload\JsonPayload;
use SmartAssert\ServiceClient\Request;

class Client
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly ServiceClient $serviceClient,
        private readonly ObjectFactory $objectFactory,
    ) {
    }

    /**
     * @param non-empty-string $token
     * @param non-empty-string $label
     *
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     */
    public function createJob(string $token, string $label): ?Job
    {
        $responseData = $this->serviceClient->sendRequestForJsonEncodedData(
            (new Request('POST', $this->createUrl('/job/' . $label)))
                ->withAuthentication(new BearerAuthentication($token))
        );

        return $this->objectFactory->createJobFromArray($responseData);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidJobTokenException
     */
    public function addEvent(string $jobToken, Event $request): ?JobEvent
    {
        try {
            $responseData = $this->serviceClient->sendRequestForJsonEncodedData(
                (new Request('POST', $this->createUrl('/event/add/' . $jobToken)))
                    ->withAuthentication(new BearerAuthentication($jobToken))
                    ->withPayload(new JsonPayload($request))
            );
        } catch (NonSuccessResponseException $nonSuccessResponseException) {
            if (404 === $nonSuccessResponseException->getCode()) {
                throw new InvalidJobTokenException($jobToken);
            }

            throw $nonSuccessResponseException;
        }

        return $this->objectFactory->createJobEventFromArray($responseData);
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
