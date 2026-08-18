<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ProductCategoryRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCategoryRepositoryTest extends TestCase
{
    private EntityManager|MockObject $entityManager;
    private ProductCategoryRepository $sut;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManager::class);
        $this->sut = new ProductCategoryRepository($this->entityManager, 'AProductClass', 'ACategoryClass');
    }

    public function test_it_identifies_categories_by_their_code(): void
    {
        $this->assertSame(['code'], $this->sut->getIdentifierProperties());
    }

    public function test_it_finds_a_category_by_its_code(): void
    {
        $category = new \stdClass();
        $query = $this->createMock(Query::class);
        $query->method('getOneOrNullResult')->willReturn($category);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->once())->method('select')->with('c')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('from')->with('ACategoryClass', 'c', 'c.id')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('where')->with('c.code = :code')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('setParameter')->with('code', 'a_category')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        $this->entityManager->method('createQueryBuilder')->willReturn($queryBuilder);

        $this->assertSame($category, $this->sut->findOneByIdentifier('a_category'));
    }

    public function test_it_returns_null_when_no_category_matches_the_code(): void
    {
        $query = $this->createMock(Query::class);
        $query->method('getOneOrNullResult')->willReturn(null);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        $this->entityManager->method('createQueryBuilder')->willReturn($queryBuilder);

        $this->assertNull($this->sut->findOneByIdentifier('unknown_category'));
    }
}
