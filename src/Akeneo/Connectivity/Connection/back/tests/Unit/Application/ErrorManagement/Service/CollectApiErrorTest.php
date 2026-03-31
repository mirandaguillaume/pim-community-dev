<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\ErrorManagement\Service;

use Akeneo\Connectivity\Connection\Application\ConnectionContextInterface;
use Akeneo\Connectivity\Connection\Application\ErrorManagement\Command\UpdateConnectionErrorCountCommand;
use Akeneo\Connectivity\Connection\Application\ErrorManagement\Command\UpdateConnectionErrorCountHandler;
use Akeneo\Connectivity\Connection\Application\ErrorManagement\Service\CollectApiError;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\BusinessError;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\HourlyErrorCount;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Repository\BusinessErrorRepositoryInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Serializer\Serializer;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;

class CollectApiErrorTest extends TestCase
{
    private ConnectionContextInterface|MockObject $connectionContext;
    private BusinessErrorRepositoryInterface|MockObject $repository;
    private UpdateConnectionErrorCountHandler|MockObject $updateErrorCountHandler;
    private Serializer|MockObject $serializer;
    private CollectApiError $sut;

    protected function setUp(): void
    {
        $this->connectionContext = $this->createMock(ConnectionContextInterface::class);
        $this->repository = $this->createMock(BusinessErrorRepositoryInterface::class);
        $this->updateErrorCountHandler = $this->createMock(UpdateConnectionErrorCountHandler::class);
        $this->serializer = $this->createMock(Serializer::class);
        $this->sut = new CollectApiError(
            $this->connectionContext,
            $this->repository,
            $this->updateErrorCountHandler,
            $this->serializer
        );
    }

    public function test_it_collects_a_business_error_from_a_product_domain_error(): void
    {
        $connection = $this->createMock(Connection::class);
        $product = $this->createMock(ProductInterface::class);
        $error = $this->createMock(DomainErrorInterface::class);

        $this->connectionContext->method('getConnection')->willReturn($connection);
        $this->connectionContext->method('isCollectable')->willReturn(true);
        $context = (new Context())->setAttribute('product', $product);
        $connection->method('code')->willReturn(new ConnectionCode('erp'));
        $connection->method('flowType')->willReturn(new FlowType(FlowType::DATA_SOURCE));
        $this->serializer->method('serialize')->with($error, 'json', $this->anything())->willReturn('{"message":"business error"}');
        $this->updateErrorCountHandler->expects($this->once())->method('handle')->with($this->callback(function (UpdateConnectionErrorCountCommand $command): bool {
            $hourlyErrorCounts = $command->errorCounts();
            Assert::assertCount(2, $hourlyErrorCounts);

            Assert::assertInstanceOf(HourlyErrorCount::class, $hourlyErrorCounts[0]);
            Assert::assertSame('erp', (string) $hourlyErrorCounts[0]->connectionCode());
            Assert::assertEquals(ErrorTypes::BUSINESS, (string) $hourlyErrorCounts[0]->errorType());
            Assert::assertSame(1, $hourlyErrorCounts[0]->errorCount());

            Assert::assertInstanceOf(HourlyErrorCount::class, $hourlyErrorCounts[1]);
            Assert::assertSame('erp', (string) $hourlyErrorCounts[1]->connectionCode());
            Assert::assertEquals(ErrorTypes::TECHNICAL, $hourlyErrorCounts[1]->errorType());
            Assert::assertSame(0, $hourlyErrorCounts[1]->errorCount());

            return true;
        }));
        $this->repository->expects($this->once())->method('bulkInsert')->with(new ConnectionCode('erp'), $this->callback(function (array $businessErrors): bool {
            Assert::assertCount(1, $businessErrors);

            Assert::assertInstanceOf(BusinessError::class, $businessErrors[0]);
            Assert::assertSame('{"message":"business error"}', $businessErrors[0]->content());

            return true;
        }));
        $this->sut->collectFromProductDomainError($error, $context);
        $this->sut->flush();
    }

    public function test_it_collects_business_errors_from_a_product_validation_error(): void
    {
        $connection = $this->createMock(Connection::class);
        $product = $this->createMock(ProductInterface::class);
        $violation1 = $this->createMock(ConstraintViolationInterface::class);
        $violation2 = $this->createMock(ConstraintViolationInterface::class);

        $violationList = new ConstraintViolationList([
                    $violation1,
                    $violation2,
                ]);
        $context = (new Context())->setAttribute('product', $product);
        $this->connectionContext->method('getConnection')->willReturn($connection);
        $this->connectionContext->method('isCollectable')->willReturn(true);
        $connection->method('code')->willReturn(new ConnectionCode('erp'));
        $connection->method('flowType')->willReturn(new FlowType(FlowType::DATA_SOURCE));
        $this->serializer->method('serialize')->willReturnCallback(
            fn ($object, $format, $context) => match (true) {
                $object === $violation1 => '{"message":"business error 1"}',
                $object === $violation2 => '{"message":"business error 2"}',
                default => throw new \InvalidArgumentException('Unexpected serialize call'),
            }
        );
        $this->updateErrorCountHandler->expects($this->once())->method('handle')->with($this->callback(function (UpdateConnectionErrorCountCommand $command): bool {
            $hourlyErrorCounts = $command->errorCounts();
            Assert::assertCount(2, $hourlyErrorCounts);

            Assert::assertInstanceOf(HourlyErrorCount::class, $hourlyErrorCounts[0]);
            Assert::assertSame('erp', (string) $hourlyErrorCounts[0]->connectionCode());
            Assert::assertEquals(ErrorTypes::BUSINESS, (string) $hourlyErrorCounts[0]->errorType());
            Assert::assertSame(2, $hourlyErrorCounts[0]->errorCount());

            Assert::assertInstanceOf(HourlyErrorCount::class, $hourlyErrorCounts[1]);
            Assert::assertSame('erp', (string) $hourlyErrorCounts[1]->connectionCode());
            Assert::assertEquals(ErrorTypes::TECHNICAL, $hourlyErrorCounts[1]->errorType());
            Assert::assertSame(0, $hourlyErrorCounts[1]->errorCount());

            return true;
        }));
        $this->repository->expects($this->once())->method('bulkInsert')->with(new ConnectionCode('erp'), $this->callback(function (array $businessErrors): bool {
            Assert::assertCount(2, $businessErrors);

            Assert::assertInstanceOf(BusinessError::class, $businessErrors[0]);
            Assert::assertSame('{"message":"business error 1"}', $businessErrors[0]->content());

            Assert::assertInstanceOf(BusinessError::class, $businessErrors[1]);
            Assert::assertSame('{"message":"business error 2"}', $businessErrors[1]->content());

            return true;
        }));
        $this->sut->collectFromProductValidationError($violationList, $context);
        $this->sut->flush();
    }

    public function test_it_collects_a_technical_error(): void
    {
        $connection = $this->createMock(Connection::class);

        $this->connectionContext->method('getConnection')->willReturn($connection);
        $this->connectionContext->method('isCollectable')->willReturn(true);
        $connection->method('code')->willReturn(new ConnectionCode('erp'));
        $connection->method('flowType')->willReturn(new FlowType(FlowType::DATA_SOURCE));
        $this->updateErrorCountHandler->expects($this->once())->method('handle')->with($this->callback(function (UpdateConnectionErrorCountCommand $command): bool {
            $hourlyErrorCounts = $command->errorCounts();
            Assert::assertCount(2, $hourlyErrorCounts);

            Assert::assertInstanceOf(HourlyErrorCount::class, $hourlyErrorCounts[0]);
            Assert::assertSame('erp', (string) $hourlyErrorCounts[0]->connectionCode());
            Assert::assertEquals(ErrorTypes::BUSINESS, (string) $hourlyErrorCounts[0]->errorType());
            Assert::assertSame(0, $hourlyErrorCounts[0]->errorCount());

            Assert::assertInstanceOf(HourlyErrorCount::class, $hourlyErrorCounts[1]);
            Assert::assertSame('erp', (string) $hourlyErrorCounts[1]->connectionCode());
            Assert::assertEquals(ErrorTypes::TECHNICAL, $hourlyErrorCounts[1]->errorType());
            Assert::assertSame(1, $hourlyErrorCounts[1]->errorCount());

            return true;
        }));
        $this->repository->expects($this->once())->method('bulkInsert')->with(new ConnectionCode('erp'), $this->callback(function (array $businessErrors): bool {
            Assert::assertCount(0, $businessErrors);

            return true;
        }));
        $this->sut->collectFromTechnicalError(new \Exception());
        $this->sut->flush();
    }

    public function test_it_doesnt_collect_errors_when_the_api_connection_is_not_found(): void
    {
        $this->connectionContext->method('getConnection')->willReturn(null);
        $this->repository->expects($this->never())->method('bulkInsert')->with($this->anything());
        $this->updateErrorCountHandler->expects($this->never())->method('handle')->with($this->anything());
        $this->sut->collectFromTechnicalError(new \Exception());
        $this->sut->flush();
    }

    public function test_it_doesnt_collect_errors_when_the_api_connection_is_not_collectable(): void
    {
        $connection = $this->createMock(Connection::class);

        $this->connectionContext->method('getConnection')->willReturn($connection);
        $this->connectionContext->method('isCollectable')->willReturn(false);
        $this->repository->expects($this->never())->method('bulkInsert')->with($this->anything());
        $this->updateErrorCountHandler->expects($this->never())->method('handle')->with($this->anything());
        $this->sut->collectFromTechnicalError(new \Exception());
        $this->sut->flush();
    }

    public function test_it_doesnt_collect_errors_when_the_api_connection_has_not_the_data_source_flow_type(): void
    {
        $connection = $this->createMock(Connection::class);

        $this->connectionContext->method('getConnection')->willReturn($connection);
        $this->connectionContext->method('isCollectable')->willReturn(true);
        $connection->method('flowType')->willReturn(new FlowType(FlowType::OTHER));
        $this->repository->expects($this->never())->method('bulkInsert')->with($this->anything());
        $this->updateErrorCountHandler->expects($this->never())->method('handle')->with($this->anything());
        $this->sut->collectFromTechnicalError(new \Exception());
        $this->sut->flush();
    }
}
