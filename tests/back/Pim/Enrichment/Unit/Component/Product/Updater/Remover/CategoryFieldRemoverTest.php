<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Updater\Remover;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\CategoryFieldRemover;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\FieldRemoverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\TestCase;

class CategoryFieldRemoverTest extends TestCase
{
    private CategoryFieldRemover $sut;

    protected function setUp(): void
    {
        $this->sut = new CategoryFieldRemover();
    }

}
