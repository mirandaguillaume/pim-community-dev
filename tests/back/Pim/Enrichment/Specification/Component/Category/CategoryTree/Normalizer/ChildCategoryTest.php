<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Category\CategoryTree\Normalizer;

use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Normalizer\ChildCategory;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Normalizer\ChildCategory as NormalizerChildCategory;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel\ChildCategory as ReadModelChildCategory;
use PHPUnit\Framework\TestCase;

class ChildCategoryTest extends TestCase
{
    private ChildCategory $sut;

    protected function setUp(): void
    {
        $this->sut = new ChildCategory();
    }

}
