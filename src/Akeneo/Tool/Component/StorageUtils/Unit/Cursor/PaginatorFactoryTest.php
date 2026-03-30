<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\StorageUtils\Cursor;

use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\Paginator;
use Akeneo\Tool\Component\StorageUtils\Cursor\PaginatorFactory;
use Akeneo\Tool\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PaginatorFactoryTest extends TestCase
{
    private PaginatorFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new PaginatorFactory(Paginator::class, self::DEFAULT_BATCH_SIZE);
    }

    public function test_it_is_a_paginator_factory(): void
    {
        $this->assertInstanceOf(PaginatorFactory::class, $this->sut);
        $this->assertInstanceOf(PaginatorFactoryInterface::class, $this->sut);
    }

    public function test_it_creates_a_paginator(): void
    {
        $cursor = $this->createMock(CursorInterface::class);

        $paginator = $this->createPaginator($cursor);
        $paginator->shouldBeAnInstanceOf(PaginatorInterface::class);
    }
}
