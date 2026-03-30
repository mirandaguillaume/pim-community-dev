<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\ORM\Cursor;

use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\ORM\Cursor\Cursor;
use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\ORM\Cursor\CursorFactory;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CursorFactoryTest extends TestCase
{
    private EntityManager|MockObject $entityManager;
    private CursorFactory $sut;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManager::class);
        $this->sut = new CursorFactory(
            Cursor::class,
            $this->entityManager,
            self::DEFAULT_BATCH_SIZE
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(CursorFactory::class, $this->sut);
        $this->assertInstanceOf(CursorFactoryInterface::class, $this->sut);
    }

    public function test_it_creates_a_cursor(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);
        $from = $this->createMock(From::class);

        $queryBuilder->method('getRootAliases')->willReturn(['a']);
        $queryBuilder->method('getDQLPart')->with('from')->willReturn([$from]);
        $from->method('getFrom')->willReturn('SomeEntity');
        $from->method('getAlias')->willReturn('a');
        $queryBuilder->method('select')->with('a.id')->willReturn($queryBuilder);
        $queryBuilder->method('resetDQLPart')->with('from')->willReturn($queryBuilder);
        $queryBuilder->method('from')->with($this->anything(), $this->anything(), 'a.id')->willReturn($queryBuilder);
        $queryBuilder->method('distinct')->with(true)->willReturn($queryBuilder);
        $queryBuilder->method('getQuery')->willReturn($query);
        $query->expects($this->once())->method('useQueryCache')->with(false);
        $query->method('getArrayResult')->willReturn([]);
        $this->assertEquals(new Cursor($queryBuilder, $this->entityManager, 100), $this->sut->createCursor($queryBuilder));
    }
}
