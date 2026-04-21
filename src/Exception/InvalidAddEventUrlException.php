<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Exception;

use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;

class InvalidAddEventUrlException extends \Exception
{
    public function __construct(
        public readonly string $url,
        public readonly NonSuccessResponseException $nonSuccessResponseException,
    ) {
        parent::__construct(sprintf('Add event url "%s" is not valid', $url));
    }
}
