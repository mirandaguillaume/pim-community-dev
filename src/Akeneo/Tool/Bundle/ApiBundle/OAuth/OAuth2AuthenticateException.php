<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\OAuth;

/**
 * Replaces OAuth2\OAuth2AuthenticateException.
 * Thrown when access token verification fails.
 */
class OAuth2AuthenticateException extends OAuth2ServerException
{
    public function __construct(
        int $httpCode,
        string $errorType,
        string $errorDescription = '',
    ) {
        parent::__construct($httpCode, $errorType, $errorDescription);
    }
}
