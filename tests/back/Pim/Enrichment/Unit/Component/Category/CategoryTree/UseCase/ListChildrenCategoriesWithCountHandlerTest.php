<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Category\CategoryTree\UseCase;

use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query\ListChildrenCategoriesWithCountIncludingSubCategories;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query\ListChildrenCategoriesWithCountNotIncludingSubCategories;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel\ChildCategory;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase\ListChildrenCategoriesWithCount;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase\ListChildrenCategoriesWithCountHandler;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PHPUnit\Framework\TestCase;

class ListChildrenCategoriesWithCountHandlerTest extends TestCase
{
    private ListChildrenCategoriesWithCountHandler $sut;

    protected function setUp(): void
    {
        $this->sut = new ListChildrenCategoriesWithCountHandler();
    }

}
