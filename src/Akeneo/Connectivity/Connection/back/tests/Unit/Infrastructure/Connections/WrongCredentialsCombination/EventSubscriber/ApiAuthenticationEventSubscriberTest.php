<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Connections\WrongCredentialsCombination\EventSubscriber;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Model\Write\WrongCredentialsCombination;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Repository\WrongCredentialsCombinationRepositoryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\ConnectionContext;
use Akeneo\Connectivity\Connection\Infrastructure\Connections\WrongCredentialsCombination\EventSubscriber\ApiAuthenticationEventSubscriber;
use Akeneo\Tool\Component\Api\Event\ApiAuthenticationEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ApiAuthenticationEventSubscriberTest extends TestCase
{
    private ConnectionContext|MockObject $connectionContext;
    private WrongCredentialsCombinationRepositoryInterface|MockObject $repository;
    private ApiAuthenticationEventSubscriber $sut;

    protected function setUp(): void
    {
        $this->connectionContext = $this->createMock(ConnectionContext::class);
        $this->repository = $this->createMock(WrongCredentialsCombinationRepositoryInterface::class);
        $this->sut = new ApiAuthenticationEventSubscriber($this->connectionContext, $this->repository);
    }

    public function test_it_saves_a_wrong_credentials_combination_if_it_is_not_valid(): void
    {
        $event = new ApiAuthenticationEvent('magento_0123', '42');
        $this->connectionContext->method('areCredentialsValidCombination')->willReturn(false);
        $connection = new Connection('magento', 'magento', FlowType::DATA_DESTINATION, 42, 10);
        $this->connectionContext->method('getConnection')->willReturn($connection);
        $this->repository->expects($this->once())->method('create')->with($this->callback(fn ($arg): bool => $arg instanceof WrongCredentialsCombination
                    && 'magento_0123' === $arg->username()
                    && 'magento' === $arg->connectionCode()));
        $this->sut->checkCredentialsCombination($event);
    }

    public function test_it_does_nothing_if_combination_is_valid(): void
    {
        $event = new ApiAuthenticationEvent('magento_0123', '42');
        $this->connectionContext->method('areCredentialsValidCombination')->willReturn(true);
        $this->connectionContext->expects($this->never())->method('getConnection');
        $this->repository->expects($this->never())->method('create');
        $this->assertNull($this->sut->checkCredentialsCombination($event));
    }

    public function test_it_does_nothing_if_connection_is_null(): void
    {
        $event = new ApiAuthenticationEvent('magento_0123', '42');
        $this->connectionContext->method('areCredentialsValidCombination')->willReturn(true);
        $this->connectionContext->method('getConnection')->willReturn(null);
        $this->repository->expects($this->never())->method('create');
        $this->assertNull($this->sut->checkCredentialsCombination($event));
    }
}
