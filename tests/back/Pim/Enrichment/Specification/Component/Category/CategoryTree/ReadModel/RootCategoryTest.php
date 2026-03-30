<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Category\CategoryTree\ReadModel;

use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel\RootCategory;
use PHPUnit\Framework\TestCase;

class RootCategoryTest extends TestCase
{
    private RootCategory $sut;

    protected function setUp(): void
    {
        $this->sut = new RootCategory();
    }

}
