<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\GenerateWebhookSecretCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\GenerateWebhookSecretHandler;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\GenerateSecretInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\Exception\ConnectionWebhookNotFoundException;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SaveWebhookSecretQueryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GenerateWebhookSecretHandlerTest extends TestCase
{
    private GenerateSecretInterface|MockObject $generateSecret;
    private SaveWebhookSecretQueryInterface|MockObject $saveQuery;
    private GenerateWebhookSecretHandler $sut;

    protected function setUp(): void
    {
        $this->generateSecret = $this->createMock(GenerateSecretInterface::class);
        $this->saveQuery = $this->createMock(SaveWebhookSecretQueryInterface::class);
        $this->sut = new GenerateWebhookSecretHandler($this->generateSecret, $this->saveQuery);
    }

    public function test_it_is_a_generate_webhook_secret_handler(): void
    {
        $this->assertInstanceOf(GenerateWebhookSecretHandler::class, $this->sut);
    }

    public function test_it_generates_a_new_secret_for_a_connection(): void
    {
        $command = new GenerateWebhookSecretCommand('magento');
        $this->generateSecret->method('generate')->willReturn('1234_secret');
        $this->saveQuery->expects($this->once())->method('execute')->with('magento', '1234_secret')->willReturn(true);
        $this->assertSame('1234_secret', $this->sut->handle($command));
    }

    public function test_it_throws_an_exception_if_the_connection_does_not_exist(): void
    {
        $command = new GenerateWebhookSecretCommand('magento');
        $this->generateSecret->method('generate')->willReturn('1234_secret');
        $this->saveQuery->expects($this->once())->method('execute')->with('magento', '1234_secret')->willReturn(false);
        $this->expectException(ConnectionWebhookNotFoundException::class);
        $this->sut->handle($command);
    }
}
