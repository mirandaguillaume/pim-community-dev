<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Audit\EventSubscriber;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataDestinationProductEventCountCommand;
use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataDestinationProductEventCountHandler;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use Akeneo\Connectivity\Connection\Infrastructure\Audit\EventSubscriber\ReadProductsEventSubscriber;
use Akeneo\Connectivity\Connection\Infrastructure\ConnectionContext;
use Akeneo\Pim\Enrichment\Component\Product\Event\Connector\ReadProductsEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ReadProductsEventSubscriberTest extends TestCase
{
    private ConnectionContext|MockObject $connectionContext;
    private UpdateDataDestinationProductEventCountHandler|MockObject $updateDataDestinationProductEventCountHandler;
    private ConnectionRepositoryInterface|MockObject $connectionRepository;
    private ReadProductsEventSubscriber $sut;

    protected function setUp(): void
    {
        $this->connectionContext = $this->createMock(ConnectionContext::class);
        $this->updateDataDestinationProductEventCountHandler = $this->createMock(UpdateDataDestinationProductEventCountHandler::class);
        $this->connectionRepository = $this->createMock(ConnectionRepositoryInterface::class);
        $this->sut = new ReadProductsEventSubscriber(
            $this->connectionContext,
            $this->updateDataDestinationProductEventCountHandler,
            $this->connectionRepository
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ReadProductsEventSubscriber::class, $this->sut);
    }

    public function test_it_saves_read_products_events_without_connection_code_from_the_rest_api(): void
    {
        $connection = $this->createMock(Connection::class);

        $readProductsEvent = new ReadProductsEvent(3);
        $connection->method('auditable')->willReturn(true);
        $connection->method('flowType')->willReturn(new FlowType(FlowType::DATA_DESTINATION));
        $connection->method('code')->willReturn(new ConnectionCode('connection_code'));
        $this->connectionContext->method('areCredentialsValidCombination')->willReturn(true);
        $this->connectionContext->method('getConnection')->willReturn($connection);
        $this->updateDataDestinationProductEventCountHandler->expects($this->exactly(1))->method('handle')->with(new UpdateDataDestinationProductEventCountCommand(
            'connection_code',
            HourlyInterval::createFromDateTime(new \DateTimeImmutable('now', new \DateTimeZone('UTC'))),
            3
        ));
        $this->sut->saveReadProducts($readProductsEvent);
    }

    public function test_it_saves_read_products_events_with_connection_code_from_the_events_api(): void
    {
        $connection = $this->createMock(Connection::class);

        $readProductsEvent = new ReadProductsEvent(3, 'connection_code');
        $this->connectionRepository->method('findOneByCode')->with('connection_code')->willReturn($connection);
        $connection->method('auditable')->willReturn(true);
        $connection->method('flowType')->willReturn(new FlowType(FlowType::DATA_DESTINATION));
        $connection->method('code')->willReturn(new ConnectionCode('connection_code'));
        $this->updateDataDestinationProductEventCountHandler->expects($this->exactly(1))->method('handle')->with(new UpdateDataDestinationProductEventCountCommand(
            'connection_code',
            HourlyInterval::createFromDateTime(new \DateTimeImmutable('now', new \DateTimeZone('UTC'))),
            3
        ));
        $this->sut->saveReadProducts($readProductsEvent);
    }

    public function test_it_does_not_save_read_products_events_when_the_connection_is_not_using_valid_credentials(): void
    {
        $readProductsEvent = new ReadProductsEvent(3);
        $this->connectionContext->method('areCredentialsValidCombination')->willReturn(false);
        $this->updateDataDestinationProductEventCountHandler->expects($this->never())->method('handle')->with($this->anything());
        $this->sut->saveReadProducts($readProductsEvent);
    }

    public function test_it_does_not_save_read_products_events_when_the_connection_is_not_auditable(): void
    {
        $connection = $this->createMock(Connection::class);

        $readProductsEvent = new ReadProductsEvent(3);
        $connection->method('auditable')->willReturn(false);
        $this->connectionContext->method('areCredentialsValidCombination')->willReturn(true);
        $this->connectionContext->method('getConnection')->willReturn($connection);
        $this->updateDataDestinationProductEventCountHandler->expects($this->never())->method('handle')->with($this->anything());
        $this->sut->saveReadProducts($readProductsEvent);
    }

    public function test_it_does_not_save_read_products_events_when_the_connection_is_not_a_destination(): void
    {
        $connection = $this->createMock(Connection::class);

        $readProductsEvent = new ReadProductsEvent(3);
        $connection->method('flowType')->willReturn(new FlowType(FlowType::DATA_SOURCE));
        $connection->method('auditable')->willReturn(true);
        $this->connectionContext->method('areCredentialsValidCombination')->willReturn(true);
        $this->connectionContext->method('getConnection')->willReturn($connection);
        $this->updateDataDestinationProductEventCountHandler->expects($this->never())->method('handle')->with($this->anything());
        $this->sut->saveReadProducts($readProductsEvent);
    }
}
