<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Storage\ElasticsearchAndSql\CategoryTree;

use Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\CategoryTree\ListRootCategoriesWithCountNotIncludingSubCategories;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;

class ListRootCategoriesWithCountNotIncludingSubCategoriesTest extends TestCase
{
    private ListRootCategoriesWithCountNotIncludingSubCategories $sut;

    protected function setUp(): void
    {
        $this->sut = new ListRootCategoriesWithCountNotIncludingSubCategories();
    }

}
