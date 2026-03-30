<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Category\CategoryTree\UseCase;

use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase\ListRootCategoriesWithCount;
use PHPUnit\Framework\TestCase;

class ListRootCategoriesWithCountTest extends TestCase
{
    private ListRootCategoriesWithCount $sut;

    protected function setUp(): void
    {
        $this->sut = new ListRootCategoriesWithCount();
    }

}
