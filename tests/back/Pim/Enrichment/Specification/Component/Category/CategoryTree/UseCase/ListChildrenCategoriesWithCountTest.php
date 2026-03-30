<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Category\CategoryTree\UseCase;

use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase\ListChildrenCategoriesWithCount;
use PHPUnit\Framework\TestCase;

class ListChildrenCategoriesWithCountTest extends TestCase
{
    private ListChildrenCategoriesWithCount $sut;

    protected function setUp(): void
    {
        $this->sut = new ListChildrenCategoriesWithCount();
    }

}
