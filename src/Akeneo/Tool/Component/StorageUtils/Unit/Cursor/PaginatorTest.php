<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\StorageUtils\Cursor;

use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\Paginator;
use Akeneo\Tool\Component\StorageUtils\Cursor\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PaginatorTest extends TestCase
{
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
        $page1 = [
                    new Entity(1),
                    new Entity(2),
                    new Entity(3),
                    new Entity(4),
                    new Entity(5),
                    new Entity(6),
                    new Entity(7),
                    new Entity(8),
                    new Entity(9),
                    new Entity(10),
                ];
        $page2 = [new Entity(11), new Entity(12), new Entity(13)];
        $data = [...$page1, ...$page2];
        $this->cursor->expects($this->once())->method('count')->willReturn(13);
        // TODO: manual conversion needed — complex .will() callback
        // $cursor->next()->shouldBeCalled()->will(function () use ($cursor, &$data) {
        //             $item = array_shift($data);
        //             if ($item === null) {
        //                 $item = false;
        //             }
        //             $cursor->current()->willReturn($item);
        //         });
        // TODO: manual conversion needed — complex .will() callback
        // $cursor->rewind()->shouldBeCalled()->will(function () use ($cursor, &$data, $page1, $page2) {
        //             $data = [...$page1, ...$page2];
        //             $item = array_shift($data);
        //             if ($item === null) {
        //                 $item = false;
        //             }
        //             $cursor->current()->willReturn($item);
        //         });
        // for each call sequence
        $this->rewind()->shouldReturn(null);
        $this->assertSame(true, $this->sut->valid());
        $this->assertSame($page1, $this->sut->current());
        $this->assertSame(0, $this->sut->key());
        $this->assertNull($this->sut->next());
        $this->assertSame(true, $this->sut->valid());
        $this->assertSame($page2, $this->sut->current());
        $this->assertSame(1, $this->sut->key());
        $this->assertNull($this->sut->next());
        $this->assertSame(false, $this->sut->valid());
        // check behaviour after the end of data
        $this->current()->shouldReturn(false);
        $this->assertNull($this->sut->key());
        // methods that not iterate can be called twice
        $this->rewind()->shouldReturn(null);
        $this->assertSame(true, $this->sut->valid());
        $this->assertSame(true, $this->sut->valid());
        $this->assertSame($page1, $this->sut->current());
        $this->assertSame($page1, $this->sut->current());
        $this->assertSame(0, $this->sut->key());
        $this->assertSame(0, $this->sut->key());
    }

    public function test_it_is_countable(): void
    {
        $this->assertInstanceOf('\Countable', $this->sut);
        $this->cursor->expects($this->once())->method('count')->willReturn(13);
        // page size is 10 : so 1 page of 10 elements and a second of 3
        $this->count()->shouldReturn(2);
    }
}
