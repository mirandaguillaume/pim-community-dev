<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Category\CategoryTree\ReadModel;

use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel\ChildCategory;
use PHPUnit\Framework\TestCase;

class ChildCategoryTest extends TestCase
{
    private ChildCategory $sut;

    protected function setUp(): void
    {
        $this->sut = new ChildCategory();
    }

}
