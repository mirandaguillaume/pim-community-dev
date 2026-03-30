<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Test\Acceptance\Category;

use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Test\Acceptance\Category\InMemoryCategoryRepository;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PHPUnit\Framework\TestCase;

class InMemoryCategoryRepositoryTest extends TestCase
{
    private InMemoryCategoryRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryCategoryRepository();
    }

}
