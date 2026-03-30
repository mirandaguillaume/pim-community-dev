<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Webhook\Exception;

use Akeneo\Connectivity\Connection\Domain\Webhook\Exception\ConnectionWebhookNotFoundException;
use PHPUnit\Framework\TestCase;

class ConnectionWebhookNotFoundExceptionTest extends TestCase
{
    private ConnectionWebhookNotFoundException $sut;

    protected function setUp(): void
    {
        $this->sut = new ConnectionWebhookNotFoundException();
    }

    public function test_it_is_a_connection_webhook_not_found_exception(): void
    {
        $this->assertInstanceOf(ConnectionWebhookNotFoundException::class, $this->sut);
        $this->assertInstanceOf(\DomainException::class, $this->sut);
    }

    public function test_it_provides_a_default_error_message(): void
    {
        $this->assertSame('akeneo_connectivity.connection.webhook.error.not_found', $this->sut->getMessage());
    }
}
