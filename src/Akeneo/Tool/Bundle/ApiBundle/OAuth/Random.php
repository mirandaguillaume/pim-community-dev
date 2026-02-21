<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\OAuth;

/**
 * Replaces FOS\OAuthServerBundle\Util\Random.
 * Generates cryptographically secure random tokens.
 */
final class Random
{
    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
