<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\GenerateWebhookSecretCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\GenerateWebhookSecretHandler;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\UpdateWebhookCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\UpdateWebhookHandler;
use Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException;
use Akeneo\Connectivity\Connection\Domain\ValueObject\Url;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Write\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectWebhookSecretQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\UpdateConnectionWebhookQueryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateWebhookHandlerTest extends TestCase
{
    private UpdateConnectionWebhookQueryInterface|MockObject $updateConnectionWebhookQuery;
    private ValidatorInterface|MockObject $validator;
    private SelectWebhookSecretQueryInterface|MockObject $selectWebhookSecretQuery;
    private GenerateWebhookSecretHandler|MockObject $generateWebhookSecretHandler;
    private UpdateWebhookHandler $sut;

    protected function setUp(): void
    {
        $this->updateConnectionWebhookQuery = $this->createMock(UpdateConnectionWebhookQueryInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->selectWebhookSecretQuery = $this->createMock(SelectWebhookSecretQueryInterface::class);
        $this->generateWebhookSecretHandler = $this->createMock(GenerateWebhookSecretHandler::class);
        $this->sut = new UpdateWebhookHandler(
            $this->updateConnectionWebhookQuery,
            $this->validator,
            $this->selectWebhookSecretQuery,
            $this->generateWebhookSecretHandler
        );
    }

    public function test_it_is_an_update_webhook_handler(): void
    {
        $this->assertInstanceOf(UpdateWebhookHandler::class, $this->sut);
    }

    public function test_it_updates_a_webhook_and_create_a_secret_with_validated_data(): void
    {
        $violationList = $this->createMock(ConstraintViolationListInterface::class);

        $code = 'magento';
        $url = 'http://valid-url.com';
        $enabled = true;
        $isUsingUuid = true;
        $secret = 'secret';
        $command = new UpdateWebhookCommand($code, $enabled, $url, $isUsingUuid);
        $isAValidWriteModel = fn (ConnectionWebhook $webhook): bool => $webhook->code() === $code
                    && $webhook->enabled() === $enabled
                    && $webhook->isUsingUuid() === $isUsingUuid
                    && $webhook->url() instanceof Url
                    && (string) $webhook->url() === $url;
        $this->validator->expects($this->once())->method('validate')->with($this->callback($isAValidWriteModel))->willReturn($violationList);
        $violationList->method('count')->willReturn(0);
        $this->updateConnectionWebhookQuery->expects($this->once())->method('execute')->with($this->callback($isAValidWriteModel));
        $this->selectWebhookSecretQuery->method('execute')->with($code)->willReturn(null);
        $this->generateWebhookSecretHandler->expects($this->once())->method('handle')->with($this->callback(fn (GenerateWebhookSecretCommand $command): bool => $command->connectionCode() === $code))->willReturn($secret);
        $this->sut->handle($command);
    }

    public function test_it_updates_a_webhook_with_validated_data(): void
    {
        $violationList = $this->createMock(ConstraintViolationListInterface::class);

        $code = 'magento';
        $url = 'http://valid-url.com';
        $enabled = true;
        $isUsingUuid = true;
        $secret = 'secret';
        $command = new UpdateWebhookCommand($code, $enabled, $url, $isUsingUuid);
        $isAValidWriteModel = fn (ConnectionWebhook $webhook): bool => $webhook->code() === $code
                    && $webhook->enabled() === $enabled
                    && $webhook->isUsingUuid() === $isUsingUuid
                    && $webhook->url() instanceof Url
                    && (string) $webhook->url() === $url;
        $this->validator->expects($this->once())->method('validate')->with($this->callback($isAValidWriteModel))->willReturn($violationList);
        $violationList->method('count')->willReturn(0);
        $this->updateConnectionWebhookQuery->expects($this->once())->method('execute')->with($this->callback($isAValidWriteModel));
        $this->selectWebhookSecretQuery->method('execute')->with($code)->willReturn($secret);
        $this->generateWebhookSecretHandler->expects($this->never())->method('handle');
        $this->sut->handle($command);
    }

    public function test_it_does_not_update_a_webhook_with_invalid_data(): void
    {
        $violationList = $this->createMock(ConstraintViolationListInterface::class);

        $code = 'magento';
        $enabled = true;
        $isUsingUuid = true;
        $url = null;
        $command = new UpdateWebhookCommand($code, $enabled, $url, $isUsingUuid);
        $isAValidWriteModel = fn (ConnectionWebhook $webhook): bool => $webhook->code() === $code
                    && $webhook->enabled() === $enabled
                    && $webhook->isUsingUuid() === $isUsingUuid
                    && $webhook->url() === null;
        $this->validator->expects($this->once())->method('validate')->with($this->callback($isAValidWriteModel))->willReturn($violationList);
        $violationList->method('count')->willReturn(1);
        $this->updateConnectionWebhookQuery->expects($this->never())->method('execute');
        $this->generateWebhookSecretHandler->expects($this->never())->method('handle');
        $this->expectException(ConstraintViolationListException::class);
        $this->sut->handle($command);
    }
}
