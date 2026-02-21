<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\OAuth;

/**
 * Replaces OAuth2\OAuth2ServerException.
 */
class OAuth2ServerException extends \Exception
{
    public function __construct(
        private readonly int $httpCode,
        private readonly string $errorType,
        private readonly string $errorDescription = '',
        array $errorData = []
    ) {
        parent::__construct($errorType);
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    public function getDescription(): string
    {
        return $this->errorDescription;
    }

    public function getErrorType(): string
    {
        return $this->errorType;
    }
}
