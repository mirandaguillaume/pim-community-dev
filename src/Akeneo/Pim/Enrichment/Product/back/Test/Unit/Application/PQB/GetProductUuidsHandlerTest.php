<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Application\PQB;

use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ApplyProductSearchQueryParametersToPQB;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface as LegacyProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuidsQuery;
use Akeneo\Pim\Enrichment\Product\Application\PQB\GetProductUuidsHandler;
use Akeneo\Pim\Enrichment\Product\Domain\PQB\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Product\Domain\PQB\ProductUuidCursor;
use Akeneo\Pim\Enrichment\Product\Domain\PQB\ProductUuidQueryFetcher;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GetProductUuidsHandlerTest extends TestCase
{
    private IdentifiableObjectRepositoryInterface|MockObject $channelRepository;
    private ProductUuidQueryFetcher|MockObject $productUuidQueryFetcher;
    private ValidatorInterface|MockObject $validator;
    private GetProductUuidsHandler $sut;

    protected function setUp(): void
    {
        $this->channelRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->productUuidQueryFetcher = $this->createMock(ProductUuidQueryFetcher::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $pqb = new class implements ProductQueryBuilderInterface, LegacyProductQueryBuilderInterface {
            public function buildQuery(?int $userId, ?UuidInterface $searchAfterUuid = null): array
            {
                return ['the query'];
            }
            public function addFilter($field, $operator, $value, array $context = [])
            {
            }
            public function addSorter($field, $direction, array $context = [])
            {
            }
            public function getRawFilters()
            {
            }
            public function getQueryBuilder()
            {
            }
            public function setQueryBuilder($queryBuilder)
            {
            }
            public function execute()
            {
            }
        };
        $applyProductSearchQueryParametersToPQB = new ApplyProductSearchQueryParametersToPQB(
            $this->channelRepository
        );
        $this->sut = new GetProductUuidsHandler($pqb, $applyProductSearchQueryParametersToPQB, $this->productUuidQueryFetcher, $this->validator);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GetProductUuidsHandler::class, $this->sut);
    }

    public function test_it_returns_a_cursor(): void
    {
        $constraintViolationList = $this->createMock(ConstraintViolationList::class);

        $query = new GetProductUuidsQuery([], 1);
        $constraintViolationList->method('count')->willReturn(0);
        $this->validator->method('validate')->with($query)->willReturn($constraintViolationList);
        $this->productUuidQueryFetcher->expects($this->once())->method('initialize')->with(['the query']);
        $result = ($this->sut)($query);
        $this->assertInstanceOf(ProductUuidCursor::class, $result);
    }

    public function test_it_throws_an_exception_when_query_is_not_valid(): void
    {
        $constraintViolationList = $this->createMock(ConstraintViolationList::class);

        $query = new GetProductUuidsQuery([], 1);
        $constraintViolationList->method('count')->willReturn(5);
        $constraintViolationList->method('__toString')->willReturn('message');
        $this->validator->method('validate')->with($query)->willReturn($constraintViolationList);
        $this->expectException(ViolationsException::class);
        ($this->sut)($query);
    }
}
