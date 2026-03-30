<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\GenerateWebhookSecretCommand;
use PHPUnit\Framework\TestCase;

class GenerateWebhookSecretCommandTest extends TestCase
{
    private GenerateWebhookSecretCommand $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_a_generate_webhook_secret_command(): void
    {
        $this->sut = new GenerateWebhookSecretCommand('magento');
        $this->assertTrue(\is_a(GenerateWebhookSecretCommand::class, GenerateWebhookSecretCommand::class, true));
    }

    public function test_it_provides_a_connection_code(): void
    {
        $this->sut = new GenerateWebhookSecretCommand('magento');
        $this->assertSame('magento', $this->sut->connectionCode());
    }
}
