<?php

namespace spec\Akeneo\Tool\Component\StorageUtils\Cursor;

use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\Paginator;
use Akeneo\Tool\Component\StorageUtils\Cursor\PaginatorFactory;
use Akeneo\Tool\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\PaginatorInterface;
use PhpSpec\ObjectBehavior;

class PaginatorFactorySpec extends ObjectBehavior
{
    final public const DEFAULT_BATCH_SIZE = 100;

    public function let()
    {
        $this->beConstructedWith(Paginator::class, self::DEFAULT_BATCH_SIZE);
    }

    public function it_is_a_paginator_factory()
    {
        $this->shouldHaveType(PaginatorFactory::class);
        $this->shouldImplement(PaginatorFactoryInterface::class);
    }

    public function it_creates_a_paginator(CursorInterface $cursor)
    {
        $paginator = $this->createPaginator($cursor);
        $paginator->shouldBeAnInstanceOf(PaginatorInterface::class);
    }
}
