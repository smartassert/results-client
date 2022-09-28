<?php

declare(strict_types=1);

namespace SmartAssert\ResultsClient\Exception;

class InvalidJobTokenException extends \Exception
{
    public function __construct(
        public readonly string $label
    ) {
        parent::__construct(sprintf('Job token "%s" is not valid', $label));
    }
}
