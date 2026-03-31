<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\StorageUtils\Cursor;

use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\Paginator;
use Akeneo\Tool\Component\StorageUtils\Cursor\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class Entity
{
    public function __construct(private readonly int $id)
    {
    }
}

class PaginatorTest extends TestCase
{
    private const PAGE_SIZE = 10;

    private CursorInterface|MockObject $cursor;
    private Paginator $sut;

    protected function setUp(): void
    {
        $this->cursor = $this->createMock(CursorInterface::class);
        $this->sut = new Paginator($this->cursor, self::PAGE_SIZE);
    }

    public function test_it_is_a_paginator(): void
    {
        $this->assertInstanceOf(Paginator::class, $this->sut);
        $this->assertInstanceOf(PaginatorInterface::class, $this->sut);
    }

    public function test_it_iterate_by_page_over_cursor(): void
    {
        $page1 = [];
        for ($i = 1; $i <= 10; $i++) {
            $page1[] = new Entity($i);
        }
        $page2 = [new Entity(11), new Entity(12), new Entity(13)];
        $allData = [...$page1, ...$page2];

        $this->cursor->method('count')->willReturn(13);

        $dataForIteration = $allData;
        $currentItem = null;

        $this->cursor->method('rewind')->willReturnCallback(function () use (&$dataForIteration, &$currentItem, $allData) {
            $dataForIteration = $allData;
            $currentItem = array_shift($dataForIteration);
        });
        $this->cursor->method('next')->willReturnCallback(function () use (&$dataForIteration, &$currentItem) {
            $currentItem = array_shift($dataForIteration);
        });
        $this->cursor->method('current')->willReturnCallback(function () use (&$currentItem) {
            return $currentItem ?? false;
        });
        $this->cursor->method('valid')->willReturnCallback(function () use (&$currentItem) {
            return $currentItem !== null;
        });

        $pages = [];
        foreach ($this->sut as $key => $page) {
            $pages[$key] = $page;
        }

        $this->assertCount(2, $pages);
        $this->assertSame($page1, $pages[0]);
        $this->assertSame($page2, $pages[1]);
    }

    public function test_it_is_countable(): void
    {
        $this->assertInstanceOf(\Countable::class, $this->sut);
        $this->cursor->method('count')->willReturn(13);
        // page size is 10 : so 1 page of 10 elements and a second of 3
        $this->assertSame(2, $this->sut->count());
    }
}
