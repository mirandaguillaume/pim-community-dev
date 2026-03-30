<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\PropertyRemover;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\AttributeRemoverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\FieldRemoverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\RemoverRegistryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\TestCase;

class PropertyRemoverTest extends TestCase
{
    private PropertyRemover $sut;

    protected function setUp(): void
    {
        $this->sut = new PropertyRemover();
    }

}
