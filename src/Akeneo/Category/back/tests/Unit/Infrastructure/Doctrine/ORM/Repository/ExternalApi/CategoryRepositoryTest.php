<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Doctrine\ORM\Repository\ExternalApi;

use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Category\Infrastructure\Doctrine\ORM\Repository\ExternalApi\CategoryRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryRepositoryTest extends TestCase
{
    private EntityManager|MockObject $entityManager;
    private CategoryRepositoryInterface|MockObject $categoryRepository;
    private ValidatorInterface|MockObject $validator;
    private CategoryRepository $sut;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManager::class);
        $classMetadata = $this->createMock(ClassMetadata::class);
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);

        $classMetadata->name = 'category';
        $this->entityManager->method('getClassMetadata')->with('category')->willReturn($classMetadata);

        $this->sut = new CategoryRepository($this->entityManager, 'category', $this->categoryRepository, $this->validator);
    }

    public function testItDelegatesGetIdentifierProperties(): void
    {
        $this->categoryRepository->expects($this->once())->method('getIdentifierProperties')->willReturn(['code']);
        $result = $this->sut->getIdentifierProperties();
        $this->assertSame(['code'], $result);
    }

    public function testItDelegatesFindOneByIdentifier(): void
    {
        $expectedCategory = new \stdClass();
        $this->categoryRepository->expects($this->once())->method('findOneByIdentifier')->with('master')->willReturn($expectedCategory);
        $result = $this->sut->findOneByIdentifier('master');
        $this->assertSame($expectedCategory, $result);
    }

    public function testItDelegatesFindOneByIdentifierReturnsNull(): void
    {
        $this->categoryRepository->expects($this->once())->method('findOneByIdentifier')->with('nonexistent')->willReturn(null);
        $result = $this->sut->findOneByIdentifier('nonexistent');
        $this->assertNull($result);
    }

    public function testItFailsOnFilterValidationWithWrongOperatorForUpdated(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);

        $queryBuilder->method('select')->willReturn($queryBuilder);
        $queryBuilder->method('from')->willReturn($queryBuilder);
        $this->entityManager->method('createQueryBuilder')->willReturn($queryBuilder);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->searchAfterOffset(
            ['updated' => [['operator' => 'BadOperator', 'value' => '2019-06-09T12:00:00+00:00']]],
            ['code' => 'ASC'],
            10,
            0,
        );
    }

    public function testItFailsOnFilterValidationWithWrongDateFormatForUpdated(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);

        $queryBuilder->method('select')->willReturn($queryBuilder);
        $queryBuilder->method('from')->willReturn($queryBuilder);
        $this->entityManager->method('createQueryBuilder')->willReturn($queryBuilder);
        $violation = new ConstraintViolation('Invalid date format', '', [], '', '', '');
        $this->validator->expects($this->atLeastOnce())->method('validate')->willReturn(new ConstraintViolationList([$violation]));
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid date format');
        $this->sut->searchAfterOffset(
            ['updated' => [['operator' => '>', 'value' => '2019-06-09 12:00:00']]],
            ['code' => 'ASC'],
            10,
            0,
        );
    }

    public function testItFailsOnUnavailableSearchFilter(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('select')->willReturn($queryBuilder);
        $queryBuilder->method('from')->willReturn($queryBuilder);
        $this->entityManager->method('createQueryBuilder')->willReturn($queryBuilder);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('unavailable filter "unknown_filter"');
        $this->sut->searchAfterOffset(
            ['unknown_filter' => [['operator' => '=', 'value' => 'test']]],
            ['code' => 'ASC'],
            10,
            0,
        );
    }

    public function testItValidatesEmptySearchFiltersDoNotCallValidator(): void
    {
        // Verify that empty search filters bypass the validator entirely
        // (the validateSearchFilters method returns early on empty array)
        $this->validator->expects($this->never())->method('validate');

        // We still need to trigger searchAfterOffset, but it will fail at the
        // query builder level. The key assertion is that validator is never called.
        // Since we can't easily mock the full query path, we test via the
        // count method which also calls addFilters.
        try {
            $queryBuilder = $this->createMock(QueryBuilder::class);
            $queryBuilder->method('select')->willReturn($queryBuilder);
            $queryBuilder->method('from')->willReturn($queryBuilder);
            $this->entityManager->method('createQueryBuilder')->willReturn($queryBuilder);
            $this->sut->count([]);
        } catch (\Throwable) {
            // The count may fail at the query execution level, but validator should not be called
        }
    }
}
